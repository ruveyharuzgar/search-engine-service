<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:load-test',
    description: 'Run load test against the search API'
)]
class LoadTestCommand extends Command
{
    private const KEYWORDS = [
        'docker', 'php', 'symfony', 'redis', 'mysql',
        'kubernetes', 'api', 'microservices', 'testing',
        'architecture', 'design patterns', 'clean code'
    ];

    private const TYPES = ['video', 'article'];
    private const SORT_OPTIONS = ['score', 'date'];
    private const PER_PAGE_OPTIONS = [10, 20, 50];

    private array $results = [];
    private int $successCount = 0;
    private int $failureCount = 0;
    private array $responseTimes = [];
    private int $cacheHits = 0;
    private int $cacheMisses = 0;

    public function __construct(
        private HttpClientInterface $httpClient
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('url', 'u', InputOption::VALUE_OPTIONAL, 'Base URL', 'http://nginx:80')
            ->addOption('requests', 'r', InputOption::VALUE_OPTIONAL, 'Total number of requests', 1000)
            ->addOption('concurrent', 'c', InputOption::VALUE_OPTIONAL, 'Concurrent requests', 10)
            ->addOption('duration', 'd', InputOption::VALUE_OPTIONAL, 'Test duration in seconds (overrides requests)', null)
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file for results', null)
            ->addOption('scenario', 's', InputOption::VALUE_OPTIONAL, 'Test scenario: basic|stress|spike', 'basic');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $baseUrl = $input->getOption('url');
        $totalRequests = (int) $input->getOption('requests');
        $concurrent = (int) $input->getOption('concurrent');
        $duration = $input->getOption('duration');
        $scenario = $input->getOption('scenario');

        $io->title('🚀 Search Engine Load Test');
        $io->section('Configuration');
        $io->table(
            ['Parameter', 'Value'],
            [
                ['Base URL', $baseUrl],
                ['Scenario', $scenario],
                ['Total Requests', $duration ? 'Duration-based' : $totalRequests],
                ['Concurrent', $concurrent],
                ['Duration', $duration ? "{$duration}s" : 'Request-based'],
            ]
        );

        // Health check
        if (!$this->healthCheck($baseUrl, $io)) {
            return Command::FAILURE;
        }

        // Run test based on scenario
        $startTime = microtime(true);
        
        match($scenario) {
            'stress' => $this->runStressTest($baseUrl, $io),
            'spike' => $this->runSpikeTest($baseUrl, $io),
            default => $this->runBasicTest($baseUrl, $totalRequests, $concurrent, $io),
        };

        $totalTime = microtime(true) - $startTime;

        // Display results
        $this->displayResults($io, $totalTime);

        // Save to file if requested
        if ($outputFile = $input->getOption('output')) {
            $this->saveResults($outputFile, $totalTime);
            $io->success("Results saved to: {$outputFile}");
        }

        return Command::SUCCESS;
    }

    private function healthCheck(string $baseUrl, SymfonyStyle $io): bool
    {
        $io->section('Health Check');
        
        try {
            $response = $this->httpClient->request('GET', "{$baseUrl}/api/search?keyword=test");
            
            if ($response->getStatusCode() === 200) {
                $io->success('✅ Service is healthy');
                return true;
            }
            
            $io->error('❌ Service returned status: ' . $response->getStatusCode());
            return false;
            
        } catch (\Exception $e) {
            $io->error('❌ Service is not responding: ' . $e->getMessage());
            return false;
        }
    }

    private function runBasicTest(string $baseUrl, int $totalRequests, int $concurrent, SymfonyStyle $io): void
    {
        $io->section('Running Basic Load Test');
        $io->progressStart($totalRequests);

        $batches = (int) ceil($totalRequests / $concurrent);
        $completed = 0;

        for ($batch = 0; $batch < $batches; $batch++) {
            $batchSize = min($concurrent, $totalRequests - $completed);
            $this->executeBatch($baseUrl, $batchSize);
            $completed += $batchSize;
            $io->progressAdvance($batchSize);
        }

        $io->progressFinish();
    }

    private function runStressTest(string $baseUrl, SymfonyStyle $io): void
    {
        $io->section('Running Stress Test');
        
        $stages = [
            ['users' => 10, 'duration' => 30, 'label' => 'Warm-up (10 users)'],
            ['users' => 50, 'duration' => 60, 'label' => 'Normal load (50 users)'],
            ['users' => 100, 'duration' => 60, 'label' => 'High load (100 users)'],
            ['users' => 200, 'duration' => 30, 'label' => 'Peak load (200 users)'],
        ];

        foreach ($stages as $stage) {
            $io->writeln("\n<info>{$stage['label']}</info>");
            $this->runStage($baseUrl, $stage['users'], $stage['duration'], $io);
        }
    }

    private function runSpikeTest(string $baseUrl, SymfonyStyle $io): void
    {
        $io->section('Running Spike Test');
        
        $io->writeln('<info>Normal load (20 users, 30s)</info>');
        $this->runStage($baseUrl, 20, 30, $io);
        
        $io->writeln('<info>SPIKE! (200 users, 10s)</info>');
        $this->runStage($baseUrl, 200, 10, $io);
        
        $io->writeln('<info>Recovery (20 users, 30s)</info>');
        $this->runStage($baseUrl, 20, 30, $io);
    }

    private function runStage(string $baseUrl, int $users, int $duration, SymfonyStyle $io): void
    {
        $io->progressStart($duration);
        
        $endTime = time() + $duration;
        
        while (time() < $endTime) {
            $this->executeBatch($baseUrl, $users);
            $io->progressAdvance(1);
            usleep(1000000); // 1 second
        }
        
        $io->progressFinish();
    }

    private function executeBatch(string $baseUrl, int $size): void
    {
        $promises = [];
        
        for ($i = 0; $i < $size; $i++) {
            $url = $this->generateRandomUrl($baseUrl);
            $startTime = microtime(true);
            
            try {
                $response = $this->httpClient->request('GET', $url, [
                    'timeout' => 10,
                ]);
                
                $statusCode = $response->getStatusCode();
                $responseTime = (microtime(true) - $startTime) * 1000;
                
                $this->responseTimes[] = $responseTime;
                
                if ($statusCode === 200) {
                    $this->successCount++;
                    
                    // Check if cached (fast response)
                    if ($responseTime < 100) {
                        $this->cacheHits++;
                    } else {
                        $this->cacheMisses++;
                    }
                    
                    // Validate response
                    $data = $response->toArray();
                    if (!isset($data['success']) || !$data['success']) {
                        $this->failureCount++;
                    }
                } else {
                    $this->failureCount++;
                }
                
            } catch (\Exception $e) {
                $this->failureCount++;
                $this->responseTimes[] = (microtime(true) - $startTime) * 1000;
            }
        }
    }

    private function generateRandomUrl(string $baseUrl): string
    {
        $keyword = self::KEYWORDS[array_rand(self::KEYWORDS)];
        $type = self::TYPES[array_rand(self::TYPES)];
        $sortBy = self::SORT_OPTIONS[array_rand(self::SORT_OPTIONS)];
        $page = rand(1, 5);
        $perPage = self::PER_PAGE_OPTIONS[array_rand(self::PER_PAGE_OPTIONS)];

        return sprintf(
            '%s/api/search?keyword=%s&type=%s&sortBy=%s&page=%d&perPage=%d',
            $baseUrl,
            urlencode($keyword),
            $type,
            $sortBy,
            $page,
            $perPage
        );
    }

    private function displayResults(SymfonyStyle $io, float $totalTime): void
    {
        $io->section('📊 Test Results');

        $totalRequests = $this->successCount + $this->failureCount;
        $successRate = $totalRequests > 0 ? ($this->successCount / $totalRequests) * 100 : 0;
        $requestsPerSecond = $totalTime > 0 ? $totalRequests / $totalTime : 0;

        sort($this->responseTimes);
        $avgResponseTime = count($this->responseTimes) > 0 
            ? array_sum($this->responseTimes) / count($this->responseTimes) 
            : 0;
        $minResponseTime = count($this->responseTimes) > 0 ? min($this->responseTimes) : 0;
        $maxResponseTime = count($this->responseTimes) > 0 ? max($this->responseTimes) : 0;
        $p50 = $this->percentile($this->responseTimes, 50);
        $p95 = $this->percentile($this->responseTimes, 95);
        $p99 = $this->percentile($this->responseTimes, 99);

        $totalCacheRequests = $this->cacheHits + $this->cacheMisses;
        $cacheHitRate = $totalCacheRequests > 0 
            ? ($this->cacheHits / $totalCacheRequests) * 100 
            : 0;

        $io->table(
            ['Metric', 'Value'],
            [
                ['Total Requests', number_format($totalRequests)],
                ['Successful', number_format($this->successCount)],
                ['Failed', number_format($this->failureCount)],
                ['Success Rate', sprintf('%.2f%%', $successRate)],
                ['Total Time', sprintf('%.2fs', $totalTime)],
                ['Requests/sec', sprintf('%.2f', $requestsPerSecond)],
                ['', ''],
                ['Avg Response Time', sprintf('%.2fms', $avgResponseTime)],
                ['Min Response Time', sprintf('%.2fms', $minResponseTime)],
                ['Max Response Time', sprintf('%.2fms', $maxResponseTime)],
                ['50th Percentile (Median)', sprintf('%.2fms', $p50)],
                ['95th Percentile', sprintf('%.2fms', $p95)],
                ['99th Percentile', sprintf('%.2fms', $p99)],
                ['', ''],
                ['Cache Hits', number_format($this->cacheHits)],
                ['Cache Misses', number_format($this->cacheMisses)],
                ['Cache Hit Rate', sprintf('%.2f%%', $cacheHitRate)],
            ]
        );

        // Performance assessment
        $io->section('🎯 Performance Assessment');
        
        $issues = [];
        $recommendations = [];

        if ($successRate < 95) {
            $issues[] = "❌ High error rate: {$this->failureCount} failures";
            $recommendations[] = "Check application logs for errors";
        }

        if ($p95 > 1000) {
            $issues[] = "⚠️  95th percentile > 1000ms";
            $recommendations[] = "Consider adding more cache or database optimization";
        }

        if ($cacheHitRate < 50) {
            $issues[] = "⚠️  Low cache hit rate: " . sprintf('%.2f%%', $cacheHitRate);
            $recommendations[] = "Review cache strategy and TTL settings";
        }

        if (empty($issues)) {
            $io->success('✅ All metrics look good!');
        } else {
            $io->warning('Issues detected:');
            $io->listing($issues);
            
            if (!empty($recommendations)) {
                $io->note('Recommendations:');
                $io->listing($recommendations);
            }
        }
    }

    private function percentile(array $values, float $percentile): float
    {
        if (empty($values)) {
            return 0;
        }

        $index = (count($values) - 1) * ($percentile / 100);
        $lower = floor($index);
        $upper = ceil($index);
        $weight = $index - $lower;

        return $values[(int)$lower] * (1 - $weight) + $values[(int)$upper] * $weight;
    }

    private function saveResults(string $filename, float $totalTime): void
    {
        $totalRequests = $this->successCount + $this->failureCount;
        $successRate = $totalRequests > 0 ? ($this->successCount / $totalRequests) * 100 : 0;
        $requestsPerSecond = $totalTime > 0 ? $totalRequests / $totalTime : 0;
        
        sort($this->responseTimes);
        $avgResponseTime = count($this->responseTimes) > 0 
            ? array_sum($this->responseTimes) / count($this->responseTimes) 
            : 0;

        $results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'summary' => [
                'total_requests' => $totalRequests,
                'successful' => $this->successCount,
                'failed' => $this->failureCount,
                'success_rate' => round($successRate, 2),
                'total_time' => round($totalTime, 2),
                'requests_per_second' => round($requestsPerSecond, 2),
            ],
            'response_times' => [
                'avg' => round($avgResponseTime, 2),
                'min' => round(min($this->responseTimes), 2),
                'max' => round(max($this->responseTimes), 2),
                'p50' => round($this->percentile($this->responseTimes, 50), 2),
                'p95' => round($this->percentile($this->responseTimes, 95), 2),
                'p99' => round($this->percentile($this->responseTimes, 99), 2),
            ],
            'cache' => [
                'hits' => $this->cacheHits,
                'misses' => $this->cacheMisses,
                'hit_rate' => round(($this->cacheHits / ($this->cacheHits + $this->cacheMisses)) * 100, 2),
            ],
        ];

        file_put_contents($filename, json_encode($results, JSON_PRETTY_PRINT));
    }
}

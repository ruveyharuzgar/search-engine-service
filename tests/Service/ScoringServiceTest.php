<?php

namespace App\Tests\Service;

use App\DTO\ContentDTO;
use App\Service\ScoringService;
use PHPUnit\Framework\TestCase;

class ScoringServiceTest extends TestCase
{
    private ScoringService $scoringService;

    protected function setUp(): void
    {
        $this->scoringService = new ScoringService();
    }

    public function testCalculateVideoScore(): void
    {
        $content = new ContentDTO(
            id: 'v1',
            title: 'Test Video',
            type: 'video',
            metrics: [
                'views' => 25000,
                'likes' => 2100,
                'duration' => '22:45'
            ],
            publishedAt: new \DateTime('2024-03-15'),
            tags: ['test']
        );

        $score = $this->scoringService->calculateScore($content);

        $this->assertIsFloat($score);
        $this->assertGreaterThan(0, $score);
        
        // Base score: (25000/1000) + (2100/100) = 25 + 21 = 46
        // Type coefficient: 46 * 1.5 = 69
        // Freshness: depends on current date
        // Engagement: (2100/25000) * 10 = 0.84
        // Expected: ~69 + freshness + 0.84
        $this->assertGreaterThan(60, $score);
    }

    public function testCalculateArticleScore(): void
    {
        $content = new ContentDTO(
            id: 'a1',
            title: 'Test Article',
            type: 'article',
            metrics: [
                'reading_time' => 10,
                'reactions' => 500,
                'comments' => 25
            ],
            publishedAt: new \DateTime('2024-03-14'),
            tags: ['test']
        );

        $score = $this->scoringService->calculateScore($content);

        $this->assertIsFloat($score);
        $this->assertGreaterThan(0, $score);
        
        // Base score: 10 + (500/50) = 10 + 10 = 20
        // Type coefficient: 20 * 1.0 = 20
        // Freshness: depends on current date
        // Engagement: (500/10) * 5 = 250
        // Expected: ~20 + freshness + 250 = ~270+
        $this->assertGreaterThan(250, $score);
    }

    public function testFreshnessScoreLastWeek(): void
    {
        $content = new ContentDTO(
            id: 'v1',
            title: 'Recent Video',
            type: 'video',
            metrics: ['views' => 1000, 'likes' => 100, 'duration' => '10:00'],
            publishedAt: new \DateTime('-3 days'),
            tags: ['test']
        );

        $score = $this->scoringService->calculateScore($content);

        $this->assertGreaterThan(5, $score);
    }

    public function testFreshnessScoreLastMonth(): void
    {
        $content = new ContentDTO(
            id: 'v1',
            title: 'Recent Video',
            type: 'video',
            metrics: ['views' => 1000, 'likes' => 100, 'duration' => '10:00'],
            publishedAt: new \DateTime('-15 days'),
            tags: ['test']
        );

        $score = $this->scoringService->calculateScore($content);

        $this->assertGreaterThan(3, $score);
    }

    public function testFreshnessScoreOldContent(): void
    {
        $content = new ContentDTO(
            id: 'v1',
            title: 'Old Video',
            type: 'video',
            metrics: ['views' => 1000, 'likes' => 100, 'duration' => '10:00'],
            publishedAt: new \DateTime('-1 year'),
            tags: ['test']
        );

        $score = $this->scoringService->calculateScore($content);

        $this->assertGreaterThan(0, $score);
        // Base: (1000/1000) + (100/100) = 2
        // Type: 2 * 1.5 = 3
        // Engagement: (100/1000) * 10 = 1
        // Total: ~4
        $this->assertLessThan(10, $score);
    }

    public function testVideoTypeCoefficient(): void
    {
        $video = new ContentDTO(
            id: 'v1',
            title: 'Video',
            type: 'video',
            metrics: ['views' => 10000, 'likes' => 1000, 'duration' => '10:00'],
            publishedAt: new \DateTime('-1 year'),
            tags: ['test']
        );

        $article = new ContentDTO(
            id: 'a1',
            title: 'Article',
            type: 'article',
            metrics: ['reading_time' => 10, 'reactions' => 500, 'comments' => 25],
            publishedAt: new \DateTime('-1 year'),
            tags: ['test']
        );

        $videoScore = $this->scoringService->calculateScore($video);
        $articleScore = $this->scoringService->calculateScore($article);

        $this->assertIsFloat($videoScore);
        $this->assertIsFloat($articleScore);
    }

    public function testEngagementScoreVideo(): void
    {
        $highEngagement = new ContentDTO(
            id: 'v1',
            title: 'Popular Video',
            type: 'video',
            metrics: ['views' => 10000, 'likes' => 5000, 'duration' => '10:00'], // 50% like ratio
            publishedAt: new \DateTime('-1 year'),
            tags: ['test']
        );

        $lowEngagement = new ContentDTO(
            id: 'v2',
            title: 'Unpopular Video',
            type: 'video',
            metrics: ['views' => 10000, 'likes' => 100, 'duration' => '10:00'], // 1% like ratio
            publishedAt: new \DateTime('-1 year'),
            tags: ['test']
        );

        $highScore = $this->scoringService->calculateScore($highEngagement);
        $lowScore = $this->scoringService->calculateScore($lowEngagement);

        $this->assertGreaterThan($lowScore, $highScore);
    }

    public function testZeroMetricsDoesNotCrash(): void
    {
        $content = new ContentDTO(
            id: 'v1',
            title: 'Zero Metrics Video',
            type: 'video',
            metrics: ['views' => 0, 'likes' => 0, 'duration' => '10:00'],
            publishedAt: new \DateTime(),
            tags: ['test']
        );

        $score = $this->scoringService->calculateScore($content);

        $this->assertIsFloat($score);
        $this->assertGreaterThanOrEqual(0, $score);
    }

    public function testMissingMetricsHandledGracefully(): void
    {
        $content = new ContentDTO(
            id: 'v1',
            title: 'Incomplete Video',
            type: 'video',
            metrics: ['duration' => '10:00'], // Missing views and likes
            publishedAt: new \DateTime(),
            tags: ['test']
        );

        $score = $this->scoringService->calculateScore($content);

        $this->assertIsFloat($score);
        $this->assertGreaterThanOrEqual(0, $score);
    }
}

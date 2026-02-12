<?php

namespace App\Provider;

use App\DTO\ContentDTO;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class JsonProvider implements ProviderInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $apiUrl
    ) {}

    public function fetchContents(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiUrl);
            $data = $response->toArray();

            $contents = [];
            foreach ($data['contents'] ?? [] as $item) {
                $contents[] = new ContentDTO(
                    id: $item['id'],
                    title: $item['title'],
                    type: $item['type'],
                    metrics: $item['metrics'],
                    publishedAt: new \DateTime($item['published_at']),
                    tags: $item['tags'] ?? []
                );
            }

            return $contents;
        } catch (\Exception $e) {
            $this->logger->error('JSON Provider hatası: ' . $e->getMessage());
            return [];
        }
    }

    public function getName(): string
    {
        return 'json_provider';
    }
}

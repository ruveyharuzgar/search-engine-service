<?php

namespace App\Provider;

use App\DTO\ContentDTO;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class XmlProvider implements ProviderInterface
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
            $xmlContent = $response->getContent();
            
            $xml = simplexml_load_string($xmlContent);
            if ($xml === false) {
                throw new \Exception('XML parse hatası');
            }

            $contents = [];
            foreach ($xml->items->item as $item) {
                $type = (string)$item->type;
                
                // Metrikleri türe göre hazırla
                $metrics = [];
                if ($type === 'video') {
                    $metrics = [
                        'views' => (int)$item->stats->views,
                        'likes' => (int)$item->stats->likes,
                        'duration' => (string)$item->stats->duration
                    ];
                } else {
                    $metrics = [
                        'reading_time' => (int)$item->stats->reading_time,
                        'reactions' => (int)$item->stats->reactions,
                        'comments' => (int)$item->stats->comments
                    ];
                }

                // Kategorileri tags olarak dönüştür
                $tags = [];
                if (isset($item->categories->category)) {
                    foreach ($item->categories->category as $category) {
                        $tags[] = (string)$category;
                    }
                }

                $contents[] = new ContentDTO(
                    id: (string)$item->id,
                    title: (string)$item->headline,
                    type: $type,
                    metrics: $metrics,
                    publishedAt: new \DateTime((string)$item->publication_date),
                    tags: $tags
                );
            }

            return $contents;
        } catch (\Exception $e) {
            $this->logger->error('XML Provider hatası: ' . $e->getMessage());
            return [];
        }
    }

    public function getName(): string
    {
        return 'xml_provider';
    }
}

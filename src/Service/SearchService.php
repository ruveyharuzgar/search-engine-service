<?php

namespace App\Service;

use App\DTO\ContentDTO;
use App\DTO\SearchRequestDTO;
use App\Repository\ContentRepository;

class SearchService
{
    public function __construct(
        private ContentRepository $contentRepository,
        private ScoringService $scoringService,
        private CacheManager $cacheManager,
        private ProviderManager $providerManager,
        private NotificationManager $notificationManager
    ) {}

    public function search(SearchRequestDTO $request): array
    {
        // Cache key oluştur
        $cacheKey = $this->cacheManager->generateKey('search', [
            'keyword' => $request->keyword,
            'type' => $request->type,
            'sortBy' => $request->sortBy,
            'page' => $request->page,
            'perPage' => $request->perPage
        ]);

        // Cache'den kontrol et
        return $this->cacheManager->get($cacheKey, function () use ($request) {
            return $this->performSearch($request);
        });
    }

    private function performSearch(SearchRequestDTO $request): array
    {
        // Veritabanından içerikleri al
        $contents = $this->contentRepository->search(
            $request->keyword,
            $request->type
        );

        // Skorları hesapla
        foreach ($contents as $content) {
            $content->score = $this->scoringService->calculateScore($content);
        }

        // Sıralama
        $contents = $this->sortContents($contents, $request->sortBy);

        // Sayfalama
        $total = count($contents);
        $offset = ($request->page - 1) * $request->perPage;
        $paginatedContents = array_slice($contents, $offset, $request->perPage);

        return [
            'data' => array_map(fn($c) => $c->toArray(), $paginatedContents),
            'pagination' => [
                'total' => $total,
                'page' => $request->page,
                'per_page' => $request->perPage,
                'total_pages' => ceil($total / $request->perPage)
            ]
        ];
    }

    private function sortContents(array $contents, string $sortBy): array
    {
        usort($contents, function (ContentDTO $a, ContentDTO $b) use ($sortBy) {
            return match ($sortBy) {
                'score' => $b->score <=> $a->score,
                'date' => $b->publishedAt <=> $a->publishedAt,
                default => 0
            };
        });

        return $contents;
    }

    /**
     * Provider'lardan verileri çekip veritabanına kaydeder
     */
    public function syncContents(): int
    {
        try {
            $this->notificationManager->info('Starting content synchronization from providers');
            
            $contents = $this->providerManager->fetchAllContents();
            
            $savedCount = 0;
            foreach ($contents as $content) {
                $this->contentRepository->save($content);
                $savedCount++;
            }

            // Cache'i temizle
            $this->cacheManager->clear();
            
            $this->notificationManager->success(
                "Successfully synchronized {$savedCount} contents from providers"
            );

            return $savedCount;
        } catch (\Exception $e) {
            $this->notificationManager->error(
                'Failed to synchronize contents: ' . $e->getMessage(),
                ['exception' => $e]
            );
            throw $e;
        }
    }
}

<?php

namespace App\Service;

use App\DTO\ContentDTO;

class ScoringService
{
    private const TYPE_COEFFICIENTS = [
        'video' => 1.5,
        'article' => 1.0,
    ];

    /**
     * Final Skor = (Temel Puan * İçerik Türü Katsayısı) + Güncellik Puanı + Etkileşim Puanı
     */
    public function calculateScore(ContentDTO $content): float
    {
        $baseScore = $this->calculateBaseScore($content);
        $typeCoefficient = $this->getTypeCoefficient($content->type);
        $freshnessScore = $this->calculateFreshnessScore($content->publishedAt);
        $engagementScore = $this->calculateEngagementScore($content);

        return ($baseScore * $typeCoefficient) + $freshnessScore + $engagementScore;
    }

    /**
     * Temel Puan:
     * - Video: views / 1000 + (likes / 100)
     * - Metin: reading_time + (reactions / 50)
     */
    private function calculateBaseScore(ContentDTO $content): float
    {
        if ($content->type === 'video') {
            $views = $content->metrics['views'] ?? 0;
            $likes = $content->metrics['likes'] ?? 0;
            return ($views / 1000) + ($likes / 100);
        }

        // article/metin
        $readingTime = $content->metrics['reading_time'] ?? 0;
        $reactions = $content->metrics['reactions'] ?? 0;
        return $readingTime + ($reactions / 50);
    }

    /**
     * İçerik Türü Katsayısı:
     * - Video: 1.5
     * - Metin: 1.0
     */
    private function getTypeCoefficient(string $type): float
    {
        return self::TYPE_COEFFICIENTS[$type] ?? 1.0;
    }

    /**
     * Güncellik Puanı:
     * - 1 hafta içinde: +5
     * - 1 ay içinde: +3
     * - 3 ay içinde: +1
     * - Daha eski: +0
     */
    private function calculateFreshnessScore(\DateTime $publishedAt): float
    {
        $now = new \DateTime();
        $diff = $now->diff($publishedAt);
        $days = $diff->days;

        if ($days <= 7) return 5.0;
        if ($days <= 30) return 3.0;
        if ($days <= 90) return 1.0;
        return 0.0;
    }

    /**
     * Etkileşim Puanı:
     * - Video: (likes / views) * 10
     * - Metin: (reactions / reading_time) * 5
     */
    private function calculateEngagementScore(ContentDTO $content): float
    {
        if ($content->type === 'video') {
            $views = $content->metrics['views'] ?? 0;
            $likes = $content->metrics['likes'] ?? 0;
            
            // Prevent division by zero
            if ($views === 0) {
                return 0.0;
            }
            
            return ($likes / $views) * 10;
        }

        // article/metin
        $readingTime = $content->metrics['reading_time'] ?? 0;
        $reactions = $content->metrics['reactions'] ?? 0;
        
        // Prevent division by zero
        if ($readingTime === 0) {
            return 0.0;
        }
        return ($reactions / $readingTime) * 5;
    }
}

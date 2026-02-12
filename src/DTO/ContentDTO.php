<?php

namespace App\DTO;

/**
 * Immutable Data Transfer Object for Content
 * 
 * Uses PHP 8.1+ readonly properties to ensure immutability.
 * No getters/setters needed - properties are public and readonly.
 */
class ContentDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $type,
        public readonly array $metrics,
        public readonly \DateTime $publishedAt,
        public readonly array $tags,
        public float $score = 0.0  // Mutable - calculated after construction
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'metrics' => $this->metrics,
            'published_at' => $this->publishedAt->format('Y-m-d H:i:s'),
            'tags' => $this->tags,
            'score' => round($this->score, 2)
        ];
    }
}

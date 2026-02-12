<?php

namespace App\DTO;

class ContentDTO
{
    public function __construct(
        public string $id,
        public string $title,
        public string $type,
        public array $metrics,
        public \DateTime $publishedAt,
        public array $tags,
        public float $score = 0.0
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

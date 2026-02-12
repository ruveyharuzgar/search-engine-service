<?php

namespace App\DTO;

class SearchRequestDTO
{
    public function __construct(
        public ?string $keyword = null,
        public ?string $type = null,
        public string $sortBy = 'score',
        public int $page = 1,
        public int $perPage = 10
    ) {}
}

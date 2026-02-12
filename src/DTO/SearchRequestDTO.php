<?php

namespace App\DTO;

/**
 * Immutable Data Transfer Object for Search Request
 * 
 * Represents search parameters from HTTP request.
 * All properties are readonly to ensure immutability.
 */
class SearchRequestDTO
{
    public function __construct(
        public readonly ?string $keyword = null,
        public readonly ?string $type = null,
        public readonly string $sortBy = 'score',
        public readonly int $page = 1,
        public readonly int $perPage = 10
    ) {}
}

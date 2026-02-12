<?php

namespace App\Provider;

interface ProviderInterface
{
    /**
     * Provider'dan içerikleri çeker
     * 
     * @return array ContentDTO dizisi
     */
    public function fetchContents(): array;

    /**
     * Provider adını döner
     */
    public function getName(): string;
}

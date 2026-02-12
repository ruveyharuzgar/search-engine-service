<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheManager
{
    public function __construct(
        private TagAwareCacheInterface $cache,
        private CacheItemPoolInterface $cachePool,
        private int $ttl = 3600
    ) {}

    public function get(string $key, callable $callback): mixed
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter($this->ttl);
            return $callback();
        });
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function clear(): bool
    {
        return $this->cachePool->clear();
    }

    public function generateKey(string $prefix, array $params): string
    {
        return $prefix . '_' . md5(serialize($params));
    }
}

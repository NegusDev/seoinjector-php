<?php

namespace SEOInjector\Cache;

/**
 * Null cache implementation (no caching)
 */
class NullCache implements CacheInterface
{
    public function get(string $key): ?array
    {
        return null;
    }

    public function set(string $key, array $data, int $duration): void
    {
        // Do nothing
    }

    public function clear(string $key): void
    {
        // Do nothing
    }

    public function clearAll(): void
    {
        // Do nothing
    }
}
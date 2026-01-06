<?php

namespace SEOInjector\Cache;

interface CacheInterface
{
    /**
     * Get cached data
     *
     * @param string $key Cache key
     * @return array|null Cached data or null if not found/expired
     */
    public function get(string $key): ?array;

    /**
     * Set cached data
     *
     * @param string $key Cache key
     * @param array $data Data to cache
     * @param int $duration Cache duration in seconds
     * @return void
     */
    public function set(string $key, array $data, int $duration): void;

    /**
     * Clear specific cache entry
     *
     * @param string $key Cache key
     * @return void
     */
    public function clear(string $key): void;

    /**
     * Clear all cache entries
     *
     * @return void
     */
    public function clearAll(): void;
}
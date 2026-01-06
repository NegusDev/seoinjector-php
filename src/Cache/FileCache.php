<?php

namespace SEOInjector\Cache;

class FileCache implements CacheInterface
{
    private string $cacheDir;

    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/seoinjector';
        
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $key): ?array
    {
        $cacheFile = $this->getCacheFilePath($key);

        if (!file_exists($cacheFile)) {
            return null;
        }

        $content = @file_get_contents($cacheFile);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        // Check if expired
        if (isset($data['expires_at']) && time() > $data['expires_at']) {
            $this->clear($key);
            return null;
        }

        return $data['data'] ?? null;
    }

    public function set(string $key, array $data, int $duration): void
    {
        $cacheFile = $this->getCacheFilePath($key);
        
        $cacheData = [
            'data' => $data,
            'expires_at' => time() + $duration,
            'created_at' => time(),
        ];

        @file_put_contents($cacheFile, json_encode($cacheData), LOCK_EX);
    }

    public function clear(string $key): void
    {
        $cacheFile = $this->getCacheFilePath($key);
        
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    public function clearAll(): void
    {
        $files = glob($this->cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    private function getCacheFilePath(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}
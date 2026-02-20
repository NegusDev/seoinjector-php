<?php

namespace SEOInjector\Cache;

class FileCache implements CacheInterface
{
    private string $cacheDir;

    /**
     * FileCache constructor.
     *
     * @param string|null $cacheDir Optional directory for cache files. Defaults to system temp directory.
     */
    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/seoinjector';
        
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * get
     *
     * @param  string $key
     * @return array|null
     */
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

    /**
     * set
     *
     * @param string $key
     * @param array  $data
     * @param int    $duration
     */
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


    /**
     * clear
     *
     * @param string $key
     */
    public function clear(string $key): void
    {
        $cacheFile = $this->getCacheFilePath($key);
        
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    /**
     * clearAll
     */
    public function clearAll(): void
    {
        $files = glob($this->cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    /**
     * getCacheFilePath
     *
     * @param  string $key
     * @return string
     */
    private function getCacheFilePath(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}
<?php

namespace SEOInjector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SEOInjector\SEOInjector;
use ReflectionClass;

class FileCacheTest extends TestCase
{
    private SEOInjector $seo;
    private string $apiKey = 'TEST_API_KEY';

    protected function setUp(): void
    {
        $this->seo = new SEOInjector(
            $this->apiKey, [
            'cache' => true,
            'cache_duration' => 3600,
            'debug' => false,
            ]
        );
    }

    private function callPrivateMethod(object $obj, string $method, array $args = [])
    {
        $ref = new ReflectionClass($obj);
        $m = $ref->getMethod($method);
        return $m->invokeArgs($obj, $args);
    }

    public function testCacheSaveAndRetrieve(): void
    {
        $key = 'test_cache';
        $data = ['title' => 'Cached Title'];
        $lang = 'en';
        $cacheKey = "seoinjector_{$this->apiKey}_{$key}_{$lang}";

        $this->callPrivateMethod($this->seo, 'setCachedData', [$cacheKey, $data]);

        $cached = $this->callPrivateMethod($this->seo, 'getCachedData', [$cacheKey]);
        $this->assertSame($data, $cached);
    }

    public function testClearCache(): void
    {
        $key = 'test_clear_cache';
        $data = ['title' => 'To be cleared'];
        $lang = 'en';
        $cacheKey = "seoinjector_{$this->apiKey}_{$key}_{$lang}";

        $this->callPrivateMethod($this->seo, 'setCachedData', [$cacheKey, $data]);

        // Ensure cached first
        $cached = $this->callPrivateMethod($this->seo, 'getCachedData', [$cacheKey]);
        $this->assertSame($data, $cached);

        // Clear cache
        $this->seo->clearCache($key);

        // Cache should now be null
        $cachedAfter = $this->callPrivateMethod($this->seo, 'getCachedData', [$cacheKey]);
        $this->assertNull($cachedAfter);
    }

    public function testClearAllCache(): void
    {
        $keys = ['one', 'two', 'three'];
        $lang = 'en';
        foreach ($keys as $key) {
            $cacheKey = "seoinjector_{$this->apiKey}_{$key}_{$lang}";
            $this->callPrivateMethod($this->seo, 'setCachedData', [$cacheKey, ['data' => $key]]);
        }

        $this->seo->clearAllCache();

        foreach ($keys as $key) {
            $cacheKey = "seoinjector_{$this->apiKey}_{$key}_{$lang}";
            $cached = $this->callPrivateMethod($this->seo, 'getCachedData', [$cacheKey]);
            $this->assertNull($cached);
        }
    }

    public function testLanguageSpecificCache(): void
    {
        $key = 'lang_cache';
        $dataEn = ['title' => 'English'];
        $dataFr = ['title' => 'French'];

        $cacheKeyEn = "seoinjector_{$this->apiKey}_{$key}_en";
        $cacheKeyFr = "seoinjector_{$this->apiKey}_{$key}_fr";

        $this->callPrivateMethod($this->seo, 'setCachedData', [$cacheKeyEn, $dataEn]);
        $this->callPrivateMethod($this->seo, 'setCachedData', [$cacheKeyFr, $dataFr]);

        $cachedEn = $this->callPrivateMethod($this->seo, 'getCachedData', [$cacheKeyEn]);
        $cachedFr = $this->callPrivateMethod($this->seo, 'getCachedData', [$cacheKeyFr]);

        $this->assertSame($dataEn, $cachedEn);
        $this->assertSame($dataFr, $cachedFr);

        // Clear only English cache
        $this->seo->clearCache($key);
        $cachedEnAfter = $this->callPrivateMethod($this->seo, 'getCachedData', [$cacheKeyEn]);
        $cachedFrAfter = $this->callPrivateMethod($this->seo, 'getCachedData', [$cacheKeyFr]);

        $this->assertNull($cachedEnAfter);
        $this->assertSame($dataFr, $cachedFrAfter);
    }
}

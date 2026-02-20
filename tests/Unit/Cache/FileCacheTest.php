<?php

namespace SEOInjector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SEOInjector\SEOInjector;

class FileCacheTest extends TestCase
{
    private SEOInjector $seo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seo = new SEOInjector('TEST_API_KEY', [
            'cache' => true,
            'cache_duration' => 1, // short TTL for testing
            'debug' => false,
        ]);
    }

    private function setCachedData(string $key, array $data): void
    {
        $setter = \Closure::bind(function($k, $d) {
            $this->setCachedData($k, $d);
        }, $this->seo, SEOInjector::class);

        $setter($key, $data);
    }

    private function getCachedData(string $key): ?array
    {
        $getter = \Closure::bind(function($k) {
            return $this->getCachedData($k);
        }, $this->seo, SEOInjector::class);

        return $getter($key);
    }

    public function testCacheSaveAndRetrieve(): void
    {
        $key = 'test_cache_key';
        $data = ['title' => 'Hello World'];

        $this->setCachedData($key, $data);
        $cached = $this->getCachedData($key);

        $this->assertSame($data, $cached, 'Cache should return the same data.');
    }

    public function testClearCache(): void
    {
        $key = 'test_clear_cache';
        $data = ['title' => 'To be cleared'];

        $this->setCachedData($key, $data);

        // Clear cache using public method
        $this->seo->clearCache($key);

        $cached = $this->getCachedData($key);
        $this->assertNull($cached, 'Cache should be cleared and return null.');
    }

    public function testClearAllCache(): void
    {
        $key1 = 'key1';
        $key2 = 'key2';
        $data = ['title' => 'Some data'];

        $this->setCachedData($key1, $data);
        $this->setCachedData($key2, $data);

        $this->seo->clearAllCache();

        $this->assertNull($this->getCachedData($key1), 'All cache should be cleared.');
        $this->assertNull($this->getCachedData($key2), 'All cache should be cleared.');
    }

    public function testLanguageSpecificCache(): void
    {
        $url = '/test-url';
        $keyEn = "seoinjector_TEST_API_KEY_{$url}_en";
        $keyFr = "seoinjector_TEST_API_KEY_{$url}_fr";

        $dataEn = ['title' => 'Hello'];
        $dataFr = ['title' => 'Bonjour'];

        // Set English cache
        $this->seo->setLanguage('en');
        $this->setCachedData($keyEn, $dataEn);

        // Set French cache
        $this->seo->setLanguage('fr');
        $this->setCachedData($keyFr, $dataFr);

        $this->seo->setLanguage('en');
        $this->assertSame($dataEn, $this->getCachedData($keyEn));

        $this->seo->setLanguage('fr');
        $this->assertSame($dataFr, $this->getCachedData($keyFr));
    }
}

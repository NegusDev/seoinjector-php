<?php

namespace SEOInjector\Tests\Unit;

use SEOInjector\SEOInjector;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    private string $apiKey = 'test-api-key';
    private string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure temp cache directory is clean
        $this->cacheDir = sys_get_temp_dir() . '/seoinjector';
        if (is_dir($this->cacheDir)) {
            array_map('unlink', glob($this->cacheDir . '/*.cache'));
        } else {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up cache after each test
        if (is_dir($this->cacheDir)) {
            array_map('unlink', glob($this->cacheDir . '/*.cache'));
        }
        parent::tearDown();
    }

    public function testCacheSaveAndRetrieve(): void
    {
        $seo = new SEOInjector($this->apiKey, ['cache' => true, 'cache_duration' => 3600, 'debug' => true]);

        $url = '/test-page';
        $data = ['metaTags' => [['name' => 'title', 'content' => 'Test Title']]];

        // Access private setCachedData via reflection
        $reflection = new \ReflectionClass($seo);
        $method = $reflection->getMethod('setCachedData');
        $method->invoke($seo, "seoinjector_{$this->apiKey}_{$url}_en", $data);

        $getMethod = $reflection->getMethod('getCachedData');
        $cached = $getMethod->invoke($seo, "seoinjector_{$this->apiKey}_{$url}_en");

        $this->assertNotNull($cached, 'Cache should exist');
        $this->assertEquals($data, $cached, 'Cached data should match original data');
    }

    public function testClearCache(): void
    {
        $seo = new SEOInjector($this->apiKey, ['cache' => true]);

        $url = '/clear-test';
        $data = ['metaTags' => [['name' => 'title', 'content' => 'Clear Test']]];

        $reflection = new \ReflectionClass($seo);
        $setMethod = $reflection->getMethod('setCachedData');
        $setMethod->invoke($seo, "seoinjector_{$this->apiKey}_{$url}_en", $data);

        // Clear cache
        $seo->clearCache($url);

        $getMethod = $reflection->getMethod('getCachedData');
        $cached = $getMethod->invoke($seo, "seoinjector_{$this->apiKey}_{$url}_en");

        $this->assertNull($cached, 'Cache should be cleared and return null');
    }

    public function testClearAllCache(): void
    {
        $seo = new SEOInjector($this->apiKey, ['cache' => true]);

        // Save multiple cache entries
        $reflection = new \ReflectionClass($seo);
        $setMethod = $reflection->getMethod('setCachedData');

        $setMethod->invoke($seo, "seoinjector_{$this->apiKey}_page1_en", ['metaTags' => [['name' => 'title', 'content' => 'Page1']]]);
        $setMethod->invoke($seo, "seoinjector_{$this->apiKey}_page2_en", ['metaTags' => [['name' => 'title', 'content' => 'Page2']]]);

        $seo->clearAllCache();

        $getMethod = $reflection->getMethod('getCachedData');

        $this->assertNull($getMethod->invoke($seo, "seoinjector_{$this->apiKey}_page1_en"));
        $this->assertNull($getMethod->invoke($seo, "seoinjector_{$this->apiKey}_page2_en"));
    }

    public function testLanguageSpecificCache(): void
    {
        $seo = new SEOInjector($this->apiKey, ['cache' => true]);
        $seo->setLanguage('fr-CA');

        $url = '/lang-test';
        $data = ['metaTags' => [['name' => 'title', 'content' => 'Bonjour']]];

        $reflection = new \ReflectionClass($seo);
        $setMethod = $reflection->getMethod('setCachedData');
        $setMethod->invoke($seo, "seoinjector_{$this->apiKey}_{$url}_fr-CA", $data);

        $getMethod = $reflection->getMethod('getCachedData');
        $cached = $getMethod->invoke($seo, "seoinjector_{$this->apiKey}_{$url}_fr-CA");

        $this->assertNotNull($cached);
        $this->assertEquals($data, $cached);
    }
}

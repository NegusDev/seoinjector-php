<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SEOInjector\SEOInjector;

class DynamicSeoTest extends TestCase
{
    public function test_dynamic_context_can_be_set(): void
    {
        $seo = new SEOInjector('test-site-key');
        $result = $seo->setContext([
            'product' => [
                'name' => 'iPhone 16 Pro',
                'slug' => 'iphone-16-pro',
            ],
        ]);
        $this->assertSame($seo, $result);
    }

    public function test_dynamic_metadata_is_resolved(): void
    {
        $seo = new TestSEOInjector('test-site-key', [
            'metaTags' => [
                [
                    'name' => 'title',
                    'content' => 'iPhone 16 Pro | Store',
                ],
                [
                    'name' => 'description',
                    'content' => 'Latest iPhone',
                ],
            ],
            'canonical' => 'https://example.com/products/iphone-16-pro',
        ]);
        $seo
            ->setUrl('/products/iphone-16-pro')
            ->setContext([
                'product' => [
                    'name' => 'iPhone 16 Pro',
                    'description' => 'Latest iPhone',
                ],
            ]);
        $metadata = $seo->getDynamic();
        $this->assertIsArray($metadata);
        // getDynamic() returns the converted array from convertToArray()
        // which has a flat structure with meta tag names as keys
        $this->assertSame(
            'iPhone 16 Pro | Store',
            $metadata['title']
        );
        $this->assertSame(
            'Latest iPhone',
            $metadata['description']
        );
    }

    public function test_dynamic_metadata_is_rendered_as_html(): void
    {
        $seo = new TestSEOInjector('test-site-key', [
            'metaTags' => [
                [
                    'name' => 'title',
                    'content' => 'iPhone 16 Pro | Store',
                ],
                [
                    'name' => 'description',
                    'content' => 'Latest iPhone',
                ],
            ],
        ]);
        $seo
            ->setUrl('/products/iphone-16-pro')
            ->setContext([
                'product' => [
                    'name' => 'iPhone 16 Pro',
                ],
            ]);
        $html = $seo->renderDynamic();
        $this->assertStringContainsString(
            '<meta',
            $html
        );
        $this->assertStringContainsString(
            'iPhone 16 Pro',
            $html
        );
    }

    public function test_dynamic_api_failure_returns_empty_result(): void
    {
        $seo = new TestSEOInjector(
            'test-site-key',
            null
        );
        $seo
            ->setUrl('/products/unknown')
            ->setContext([
                'product' => [
                    'name' => 'Unknown',
                ],
            ]);
        $result = $seo->getDynamic();
        $this->assertNull($result);
    }

    public function test_dynamic_api_failure_does_not_render_html(): void
    {
        $seo = new TestSEOInjector(
            'test-site-key',
            null
        );
        $seo
            ->setUrl('/products/unknown')
            ->setContext([
                'product' => [
                    'name' => 'Unknown',
                ],
            ]);
        $html = $seo->renderDynamic();
        $this->assertSame('', $html);
    }
}

final class TestSEOInjector extends SEOInjector
{
    /**
     * @param string $siteKey
     * @param array<string, mixed>|null $fakeResponse
     */
    public function __construct(
        string $siteKey,
        private ?array $fakeResponse = null,
    ) {
        parent::__construct($siteKey);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>|null
     */
    protected function fetchDynamicMetadata(
        string $url,
        array $context,
    ): ?array {
        return $this->fakeResponse;
    }
}

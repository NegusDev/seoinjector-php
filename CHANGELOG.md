# Changelog

## [0.5.0] - 2026-08-25

### Added

#### Dynamic SEO Handling

SEO Injector now supports **context-aware dynamic SEO resolution**,
enabling templated metadata for URLs with dynamic content (products,
articles, etc.).

**New Methods:**

* `setContext(array $context): self` — Pass application data (product
  details, article info) that SEO Injector uses to resolve dynamic
  templates
* `getDynamic(): ?array` — Retrieve dynamically resolved SEO metadata as
  an array
* `renderDynamic(): string` — Render dynamically resolved SEO as HTML
  meta tags

**How It Works:**

Instead of static metadata per URL, you can now define SEO templates on
the backend that interpolate your application's context at render time.

```php
$seo = new SEOInjector('your_api_key');

// Pass your product/article data
$seo->setContext([
    'product' => [
        'name' => 'iPhone 16 Pro',
        'description' => 'Latest flagship device',
        'image' => 'https://example.com/img/iphone.jpg',
        'price' => 999,
    ],
]);

// Get or render dynamically resolved metadata
$metadata = $seo->setUrl('/products/iphone-16-pro')->getDynamic();
echo $seo->renderDynamic();
// Renders <title>, <meta>, OG tags, etc.
```

**Business Value:**

* **Scalability**: No need to manage individual SEO entries per
  product/article—use templates
* **Consistency**: All products share the same SEO structure but with
  personalized content
* **Freshness**: Metadata updates reflect real-time product/article data
* **SEO Optimization**: Dynamic titles, descriptions, and Open Graph
  tags for better search ranking and social sharing

**Technical Details:**

* Context is securely hashed (MD5) for cache key generation,
  preventing collision
* Supports multi-language context resolution via `setLanguage()`
* API endpoint: `/dynamic-meta/` (POST with context payload)
* Full caching support for context-based variants

#### Multi-Language Support

* `setLanguage(string $language): self` — Explicitly set the language
  for SEO resolution (e.g., 'en', 'fr', 'de')
* Auto-detection from `Accept-Language` HTTP header if not explicitly
  set

### Changed

#### Method Visibility

* `fetchMetadata()` — Changed from `private` to `protected` to support
  test doubles and subclass customization
* `fetchDynamicMetadata()` — Changed from `private` to `protected` to
  support test doubles and subclass customization

### Improved

#### Test Compliance

* Full PHPUnit 10 compliance with strict type checking
* Enhanced test coverage for dynamic metadata handling
* Added 5 new unit tests for dynamic SEO scenarios

#### Error Handling

* Dynamic API errors (4xx, 5xx status codes) are not cached, allowing
  for automatic recovery
* Improved logging for dynamic metadata fetch failures (debug mode)

### Fixed

* Undefined variable in `clearCache()` method (language detection)

---

## [0.4.5] - Previous Release

[Previous changelog entries...]

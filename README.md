# SEO Injector - PHP Library

[![Latest Version](https://img.shields.io/packagist/v/negusdev/seoinjector-php.svg)](https://packagist.org/packages/negusdev/seoinjector-php)
[![Total Downloads](https://img.shields.io/packagist/dt/negusdev/seoinjector-php.svg)](https://packagist.org/packages/negusdev/seoinjector-php)
[![License](https://img.shields.io/packagist/l/negusdev/seoinjector-php.svg)](https://packagist.org/packages/negusdev/seoinjector-php)
[![PHP Version](https://img.shields.io/packagist/php-v/negusdev/seoinjector-php.svg)](https://packagist.org/packages/negusdev/seoinjector-php)

Universal PHP library for managing SEO metadata from a centralized dashboard. Works with **Laravel, WordPress, Symfony, CodeIgniter, or any PHP project**.

## ✨ Features

- 🚀 Works with any PHP framework or vanilla PHP
- 📦 Zero dependencies
- ⚡ Built-in file caching with intelligent expiration
- 🎨 WordPress integration included
- 🔧 Laravel service provider included
- 🌍 Symfony bundle ready
- **✨ NEW** 🧬 Dynamic SEO handling with context-aware metadata resolution
- 🗣️ Multi-language support with auto-detection
- 🧪 Fully tested with PHPUnit 10 compliance
- 📖 Comprehensive documentation

## 🆕 What's New in v0.5.0: Dynamic SEO Handling

SEO Injector now supports **context-aware dynamic SEO resolution**. Instead of managing individual SEO entries per product or article, you can define templates that automatically populate with your application's data.

**Perfect for:**

- E-commerce product pages
- Blog articles with dynamic metadata
- User profiles or portfolio pages
- Any content-driven application

### Why Dynamic SEO?

| Challenge                                         | Solution                                    |
| ------------------------------------------------- | ------------------------------------------- |
| Thousands of products, each needs unique SEO      | Define one template, supply product context |
| SEO metadata gets stale                           | Uses real-time product/article data         |
| Managing individual meta tags per page is tedious | Centralized templates + automatic rendering |
| Social sharing needs different OG tags per item   | Dynamic Open Graph generation from context  |

## 📦 Installation

```bash
composer require negusdev/seoinjector-php
```

## 🚀 Quick Start

### Basic Static SEO (Vanilla PHP)

```php
<?php
require 'vendor/autoload.php';
use SEOInjector\SEOInjector;

$seo = new SEOInjector('your_api_key');
?>
<!DOCTYPE html>
<html>
<head>
    <?php echo $seo->render(); ?>
</head>
<body>
    <!-- Your content -->
</body>
</html>
```

### Dynamic SEO with Context (NEW!)

```php
<?php
require 'vendor/autoload.php';
use SEOInjector\SEOInjector;

// Get your product/article data
$product = [
    'name' => 'iPhone 16 Pro',
    'description' => 'Latest flagship with A18 Pro chip',
    'image' => 'https://example.com/iphone16pro.jpg',
    'price' => 999,
    'rating' => 4.8,
];

$seo = new SEOInjector('your_api_key');

// Pass your data to SEO Injector
$seo
    ->setUrl('/products/iphone-16-pro')
    ->setContext(['product' => $product])
    ->setLanguage('en');

// Render dynamically resolved metadata
echo $seo->renderDynamic();
// Outputs: <title>iPhone 16 Pro - $999 | Your Store</title>
//          <meta name="description" content="...">
//          <meta property="og:image" content="https://example.com/iphone16pro.jpg">
//          ...etc
?>
```

#### Get as Array Instead of HTML

```php
$metadata = $seo->getDynamic();

echo $metadata['title'];           // "iPhone 16 Pro - $999 | Your Store"
echo $metadata['description'];     // "Latest flagship with A18 Pro chip"
echo $metadata['og_image'];        // "https://example.com/iphone16pro.jpg"
echo $metadata['og_price_amount']; // "999"
```

### Laravel Integration

```php
// In your controller
Route::get('/products/{slug}', function($slug) {
    $product = Product::where('slug', $slug)->firstOrFail();

    $seo = app('seoinjector')
        ->setUrl(request()->path())
        ->setContext(['product' => $product->toArray()])
        ->setLanguage(app()->getLocale());

    return view('products.show', ['product' => $product, 'seo' => $seo]);
});
```

```blade
<!-- In your blade template -->
<head>
    {!! $seo->renderDynamic() !!}
</head>
```

### WordPress Integration

```php
// In functions.php or plugin file
add_action('wp_head', function() {
    // For single posts/pages
    if (is_singular()) {
        $post = get_queried_object();

        $seo = new \SEOInjector\SEOInjector(
            get_option('seoinjector_api_key')
        );

        $seo->setContext([
            'post' => [
                'title' => $post->post_title,
                'excerpt' => get_the_excerpt($post),
                'image' => get_the_post_thumbnail_url($post),
            ]
        ]);

        echo $seo->renderDynamic();
    }
}, 1);
```

## 🎯 API Reference

### Core Methods

#### `setUrl(string $url): self`

Set the URL/path to fetch SEO metadata for.

```php
$seo->setUrl('/products/iphone-16-pro');
```

#### `setContext(array $context): self` (NEW!)

Pass application data for dynamic template resolution.

```php
$seo->setContext([
    'product' => ['name' => '...', 'price' => '...'],
    'user' => ['name' => '...', 'avatar' => '...'],
]);
```

#### `setLanguage(string $language): self`

Explicitly set the language for metadata resolution (auto-detected from Accept-Language header if not set).

```php
$seo->setLanguage('en'); // or 'fr', 'de', etc.
```

#### `render(): string`

Render static SEO metadata as HTML meta tags.

```php
echo $seo->render();
```

#### `renderDynamic(): string` (NEW!)

Render context-aware dynamic SEO metadata as HTML.

```php
echo $seo->setContext($data)->renderDynamic();
```

#### `get(): ?array`

Get static metadata as an associative array.

```php
$metadata = $seo->get();
echo $metadata['title'];
```

#### `getDynamic(): ?array` (NEW!)

Get dynamically resolved metadata as an associative array.

```php
$metadata = $seo->setContext($data)->getDynamic();
echo $metadata['og_image'];
```

#### `clearCache(string $url): void`

Clear cached metadata for a specific URL.

```php
$seo->clearCache('/products/iphone-16-pro');
```

#### `clearAllCache(): void`

Clear all cached metadata.

```php
$seo->clearAllCache();
```

## ⚙️ Configuration

```php
$seo = new SEOInjector('your_api_key', [
    'api_url' => 'https://api.seoinjector.com/api',  // Custom API endpoint
    'cache' => true,                                   // Enable file caching
    'cache_duration' => 3600,                          // Cache TTL in seconds
    'debug' => false,                                  // Enable debug logging
]);
```

## 🧪 Testing

```bash
composer test
```

## 📖 Full Documentation

- [Full Documentation](https://docs.seoinjector.com/php)
- [Dynamic SEO Guide](https://docs.seoinjector.com/php/dynamic-seo)
- [Laravel Integration](https://docs.seoinjector.com/php/laravel)
- [WordPress Integration](https://docs.seoinjector.com/php/wordpress)
- [API Reference](https://docs.seoinjector.com/php/api)

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for release notes and version history.

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## 📄 License

MIT License - see [LICENSE](LICENSE) file.

## 🔗 Links

- [SEO Injector Dashboard](https://seoinjector.com)
- [Documentation](https://docs.seoinjector.com)
- [Support](https://seoinjector.com/support)
- [GitHub](https://github.com/NegusDev/seoinjector-php)
- [Packagist](https://packagist.org/packages/negusdev/seoinjector-php)

# SEO Injector - PHP Library

[![Latest Version](https://img.shields.io/packagist/v/seoinjector/php.svg)](https://packagist.org/packages/seoinjector/php)
[![Total Downloads](https://img.shields.io/packagist/dt/seoinjector/php.svg)](https://packagist.org/packages/seoinjector/php)
[![License](https://img.shields.io/packagist/l/seoinjector/php.svg)](https://packagist.org/packages/seoinjector/php)
[![PHP Version](https://img.shields.io/packagist/php-v/seoinjector/php.svg)](https://packagist.org/packages/seoinjector/php)

Universal PHP library for managing SEO metadata from a centralized dashboard. Works with **Laravel, WordPress, Symfony, CodeIgniter, or any PHP project**.

## ✨ Features

- 🚀 Works with any PHP framework or vanilla PHP
- 📦 Zero dependencies
- ⚡ Built-in file caching
- 🎨 WordPress integration included
- 🔧 Laravel service provider included
- 🌍 Symfony bundle ready
- 🧪 Fully tested
- 📖 Comprehensive documentation

## 📦 Installation
```bash
composer require seoinjector/php
```

## 🚀 Quick Start

### Vanilla PHP
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

### Laravel
```php
// In your blade template
{!! app('seoinjector')->render() !!}
```

### WordPress
```php
// In functions.php or plugin file
add_action('wp_head', function() {
    $seo = new \SEOInjector\SEOInjector(get_option('seoinjector_api_key'));
    echo $seo->render();
}, 1);
```

## 📖 Documentation

- [Full Documentation](https://docs.seoinjector.com/php)
- [Laravel Integration](https://docs.seoinjector.com/php/laravel)
- [WordPress Integration](https://docs.seoinjector.com/php/wordpress)
- [API Reference](https://docs.seoinjector.com/php/api)

## 🧪 Testing
```bash
composer test
```

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for details.

## 🤝 Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 📄 License

MIT License - see [LICENSE](LICENSE) file.

## 🔗 Links

- [SEO Injector Dashboard](https://seoinjector.com)
- [Documentation](https://docs.seoinjector.com)
- [Support](https://seoinjector.com/support)
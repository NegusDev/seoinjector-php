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

?>
<!DOCTYPE html>
<html>
<head>
    <!-- Render dynamically resolved metadata -->
    <?= $seo->renderDynamic(); ?>
    <!--Outputs: <title>iPhone 16 Pro - $999 | Your Store</title>
             <meta name="description" content="...">
             <meta property="og:image" content="https://example.com/iphone16pro.jpg">
             ...etc-->
</head>
<body>
    <!-- Your content -->
</body>
</html>

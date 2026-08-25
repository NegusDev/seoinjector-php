<?php
require 'vendor/autoload.php';
use SEOInjector\SEOInjector;

$seo = new SEOInjector('your_api_key');
?>
<!DOCTYPE html>
<html>
<head>
    <?= $seo->render(); ?>
</head>
<body>
    <!-- Your content -->
</body>
</html>

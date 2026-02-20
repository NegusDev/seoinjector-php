<?php
require 'vendor/autoload.php';

use SEOInjector\SEOInjector;

$seo = new SEOInjector('your_api_key', [
    'cache' => true,
    'cache_duration' => 3600,
    'debug' => false,
]);
?>
<!DOCTYPE html>
<html>

<head>
    <?php echo $seo->render(); ?>
    <!-- Your other head tags -->
</head>

<body>
    <!-- Your content -->
</body>

</html>
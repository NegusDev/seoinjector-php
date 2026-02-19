<?php
require 'vendor/autoload.php';

use SEOInjector\SEOInjector;

$seo = new SEOInjector('your_api_key');
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
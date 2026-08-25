<?php

namespace SEOInjector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SEOInjector\SEOInjector;

class SEOInjectorTest extends TestCase
{
    public function test_can_instantiate()
    {
        $seo = new SEOInjector('test_key');
        $this->assertInstanceOf(SEOInjector::class, $seo);
    }

    public function test_render_returns_string()
    {
        $seo = new SEOInjector('test_key', ['cache' => false]);
        $result = $seo->render();
        $this->assertIsString($result);
    }
}

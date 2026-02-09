<?php

namespace SEOInjector\Integrations\Laravel;

use Illuminate\Support\Facades\Facade as BaseFacade;

/**
 * @method static string render()
 * @method static array|null get()
 * @method static \SEOInjector\SEOInjector setUrl(string $url)
 * 
 * @see \SEOInjector\SEOInjector
 */
class Facade extends BaseFacade
{
    protected static function getFacadeAccessor(): string
    {
        return 'seoinjector';
    }
}
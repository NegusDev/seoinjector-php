<?php

// namespace SEOInjector\Integrations\Laravel;

// use Illuminate\Support\ServiceProvider;
// use SEOInjector\SEOInjector;

// class SEOInjectorServiceProvider extends ServiceProvider
// {
//     public function register(): void
//     {
//         $this->app->singleton('seoinjector', function ($app) {
//             $apiKey = config('services.seoinjector.api_key') 
//                    ?? env('SEO_INJECTOR_KEY');

//             return new SEOInjector($apiKey, [
//                 'cache' => config('services.seoinjector.cache', true),
//                 'cache_duration' => config('services.seoinjector.cache_duration', 3600),
//                 'debug' => config('services.seoinjector.debug', config('app.debug')),
//             ]);
//         });
//     }

//     public function boot(): void
//     {
//         // Publish config file
//         $this->publishes([
//             __DIR__ . '/config/seoinjector.php' => config_path('seoinjector.php'),
//         ], 'config');
//     }
// }
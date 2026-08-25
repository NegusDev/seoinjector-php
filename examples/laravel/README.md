# Laravel Integration Guide - SEO Injector Dynamic SEO

## Installation & Setup

### Step 1: Install the Package

```bash
composer require negusdev/seoinjector-php
````

### Step 2: Create a Config File

Create `config/seoinjector.php`:

```php
<?php

return [
    'api_key' => env('SEOINJECTOR_API_KEY'),
    'api_url' => env(
        'SEOINJECTOR_API_URL',
        'https://api.seoinjector.com/api'
    ),
    'cache' => env('SEOINJECTOR_CACHE', true),
    'cache_duration' => env('SEOINJECTOR_CACHE_DURATION', 3600),
    'debug' => env('SEOINJECTOR_DEBUG', false),
];
```

### Step 3: Create a Service Provider

Create
`app/Providers/SEOInjectorServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SEOInjector\SEOInjector;

class SEOInjectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('seoinjector', function ($app) {
            return new SEOInjector(
                config('seoinjector.api_key'),
                [
                    'api_url' => config('seoinjector.api_url'),
                    'cache' => config('seoinjector.cache'),
                    'cache_duration' => config(
                        'seoinjector.cache_duration'
                    ),
                    'debug' => config('seoinjector.debug'),
                ]
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
```

Add to `config/app.php` in the `providers` array:

```php
<?php

'providers' => [
    // ...
    App\Providers\SEOInjectorServiceProvider::class,
],
```

### Step 4: Add Environment Variables

Add to `.env`:

```dotenv
SEOINJECTOR_API_KEY=your_api_key_here
SEOINJECTOR_CACHE=true
SEOINJECTOR_CACHE_DURATION=3600
SEOINJECTOR_DEBUG=false
```

---

## Examples

### Example 1: E-Commerce Product Pages

#### Model: `app/Models/Product.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'image',
        'category_id',
    ];

    public function getSeoContext(): array
    {
        return [
            'product' => [
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'image' => $this->image,
                'url' => route('products.show', $this->slug),
                'rating' => $this->avgRating(),
                'reviews' => $this->reviews()->count(),
            ],
        ];
    }

    public function avgRating(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }
}
```

#### Controller: `app/Http/Controllers/ProductController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(string $slug): View
    {
        $product = Product::where('slug', $slug)
            ->firstOrFail();

        // Get SEO Injector instance
        $seo = app('seoinjector');

        // Configure dynamic SEO
        $seo
            ->setUrl(route('products.show', $slug))
            ->setContext($product->getSeoContext())
            ->setLanguage(app()->getLocale());

        return view('products.show', [
            'product' => $product,
            'seo' => $seo,
        ]);
    }
}
```

#### Blade Template:

`resources/views/products/show.blade.php`

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    {{-- Render dynamic SEO metadata --}}
    {!! $seo->renderDynamic() !!}
</head>
<body>
    <div class="container">
        <h1>{{ $product->name }}</h1>

        <div class="product-image">
            <img
                src="{{ $product->image }}"
                alt="{{ $product->name }}"
            >
        </div>

        <div class="product-details">
            <p class="price">
                ${{ number_format($product->price, 2) }}
            </p>

            <p class="description">
                {{ $product->description }}
            </p>

            <button class="btn btn-primary">
                Add to Cart
            </button>
        </div>
    </div>
</body>
</html>
```

---

### Example 2: Blog/Article Pages

#### Model: `app/Models/Article.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_id',
        'published_at',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getSeoContext(): array
    {
        $wordCount = str_word_count($this->content);

        return [
            'article' => [
                'title' => $this->title,
                'excerpt' => $this->excerpt,
                'content' => strip_tags($this->content),
                'image' => $this->featured_image,
                'author' => $this->author->name,
                'publishedAt' => $this->published_at?->toIso8601String(),
                'wordCount' => $wordCount,
                'readTime' => ceil($wordCount / 200),
            ],
        ];
    }
}
```

#### Controller: `app/Http/Controllers/ArticleController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(string $slug): View
    {
        $article = Article::where('slug', $slug)
            ->published()
            ->firstOrFail();

        $seo = app('seoinjector')
            ->setUrl(route('articles.show', $slug))
            ->setContext($article->getSeoContext())
            ->setLanguage(app()->getLocale());

        return view(
            'articles.show',
            compact('article', 'seo')
        );
    }
}
```

#### Blade Template:

`resources/views/articles/show.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    {!! $seo->renderDynamic() !!}
</head>
<body>
    <article class="article">
        <header class="article-header">
            <h1>{{ $article->title }}</h1>

            <div class="meta">
                <span class="author">
                    By {{ $article->author->name }}
                </span>

                <span class="date">
                    {{ $article->published_at->format('M d, Y') }}
                </span>

                <span class="read-time">
                    {{ ceil(
                        str_word_count($article->content) / 200
                    ) }} min read
                </span>
            </div>
        </header>

        @if($article->featured_image)
            <img
                src="{{ $article->featured_image }}"
                alt="{{ $article->title }}"
                class="featured-image"
            >
        @endif

        <div class="article-content">
            {!! $article->content !!}
        </div>
    </article>
</body>
</html>
```

---

### Example 3: User Profile Pages

#### Model: `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'bio',
        'username',
    ];

    public function getSeoContext(): array
    {
        return [
            'user' => [
                'name' => $this->name,
                'bio' => $this->bio,
                'avatar' => $this->avatar,
                'username' => $this->username,
                'postsCount' => $this->articles()->count(),
                'followersCount' => $this->followers()->count(),
            ],
        ];
    }
}
```

#### Controller: `app/Http/Controllers/ProfileController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(string $username): View
    {
        $user = User::where('username', $username)
            ->firstOrFail();

        $seo = app('seoinjector')
            ->setUrl(route('profile.show', $username))
            ->setContext($user->getSeoContext())
            ->setLanguage(app()->getLocale());

        $articles = $user->articles()
            ->published()
            ->latest()
            ->paginate(10);

        return view(
            'profile.show',
            compact('user', 'seo', 'articles')
        );
    }
}
```

---

### Example 4: Middleware for Automatic SEO Injection

Create `app/Http/Middleware/InjectSEO.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectSEO
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        // Get SEO instance
        $seo = app('seoinjector');

        // Auto-set URL from current route
        $seo->setUrl($request->getPathInfo());

        // Set language from app locale
        $seo->setLanguage(app()->getLocale());

        // Make it available to all views
        view()->share('seo', $seo);

        return $response;
    }
}
```

Register in `app/Http/Kernel.php`:

```php
<?php

protected $routeMiddleware = [
    // ...
    'inject-seo' => \App\Http\Middleware\InjectSEO::class,
];
```

Use in routes:

```php
<?php

Route::get(
    '/products/{slug}',
    [ProductController::class, 'show']
)
    ->middleware('inject-seo')
    ->name('products.show');
```

Then in your controller, just set the context:

```php
<?php

public function show(string $slug): View
{
    $product = Product::where('slug', $slug)
        ->firstOrFail();

    // Context is set, middleware handles URL and language
    app('seoinjector')
        ->setContext($product->getSeoContext());

    return view(
        'products.show',
        compact('product')
    );
}
```

---

### Example 5: Getting Metadata as Array (Headless)

For APIs or where you need structured data instead of HTML:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->firstOrFail();

        $seo = app('seoinjector')
            ->setUrl(route('products.show', $slug))
            ->setContext($product->getSeoContext());

        // Get metadata as array instead of HTML
        $metadata = $seo->getDynamic();

        return response()->json([
            'product' => $product,
            'seo' => $metadata,
        ]);
    }
}
```

Frontend (Vue/React) can use this data:

```javascript
// client.js
async function loadProduct(slug) {
  const response = await fetch(`/api/products/${slug}`);
  const data = await response.json();

  // Set meta tags dynamically
  document.title = data.seo.title;

  // Set Open Graph tags
  setMetaTag("og:image", data.seo.og_image);
  setMetaTag("og:description", data.seo.og_description);

  return data;
}
```

---

### Example 6: Multi-language Support

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function show(string $locale, string $slug)
    {
        // Validate locale
        if (!in_array(
            $locale,
            config('app.supported_locales')
        )) {
            abort(404);
        }

        $product = Product::where('slug', $slug)
            ->firstOrFail();

        $seo = app('seoinjector')
            ->setUrl("/{$locale}/products/{$slug}")
            ->setContext($product->getSeoContext())
            ->setLanguage($locale);

        return view(
            'products.show',
            compact('product', 'seo')
        );
    }
}
```

Route:

```php
Route::group(['prefix' => '{locale}'], function () {
    Route::get(
        '/products/{slug}',
        [ProductController::class, 'show']
    )
        ->where('locale', 'en|fr|de|es')
        ->name('products.show');
});
```

---

### Example 7: Clearing Cache

```php
<?php

// Clear cache for a specific URL
Route::post(
    '/admin/cache/clear/{url}',
    function (Request $request) {
        app('seoinjector')->clearCache($request->url);

        return response()->json([
            'message' => 'Cache cleared',
        ]);
    }
)->middleware('auth', 'admin');

// Clear all SEO cache
Route::post(
    '/admin/cache/clear-all',
    function () {
        app('seoinjector')->clearAllCache();

        return response()->json([
            'message' => 'All SEO cache cleared',
        ]);
    }
)->middleware('auth', 'admin');
```

---

## Testing SEO Output

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

class ProductSeoTest extends TestCase
{
    public function test_product_page_renders_dynamic_seo(): void
    {
        $product = Product::factory()->create([
            'name' => 'iPhone 16 Pro',
            'slug' => 'iphone-16-pro',
        ]);

        $response = $this->get(
            route('products.show', $product->slug)
        );

        $response->assertSuccessful();
        $response->assertSee('iPhone 16 Pro');
        $response->assertSee('og:image');
        $response->assertSee('og:price:amount');
    }
}
```

---

## Summary

The Laravel integration provides:

* Service provider for easy access via `app('seoinjector')`
* Config file for environment-based settings
* Model methods to encapsulate SEO context
* Controller examples for products, articles, and profiles
* Middleware for automatic URL/language injection
* Support for both traditional views and headless APIs
* Multi-language support with explicit language setting
* Cache management endpoints for admin panels
* Testable architecture with proper separation of concerns

```
```

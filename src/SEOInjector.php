<?php

/**
 * SEO Injector - Universal PHP Library
 * Works with Laravel, WordPress, Symfony, or any PHP project
 * 
 * @package SEOInjector
 * @version 1.0.0
 * @author  SEO Injector
 * @license MIT
 */

namespace SEOInjector;

class SEOInjector
{
    private string $apiKey;
    private string $apiUrl;
    private ?string $url = null;
    private bool $cache;
    private int $cacheDuration;
    private bool $debug;
    private array $cacheStore = [];

    private ?string $language = null;

    /**
     * Initialize SEO Injector
     * 
     * @param string $apiKey  Your SEO Injector API key
     * @param array  $options Configuration options
     * 
     * @example
     * $seo = new SEOInjector('your_api_key', [
     *     'cache' => true,
     *     'cache_duration' => 3600
     * ]);
     */
    public function __construct(string $apiKey, array $options = [])
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = $options['api_url'] ?? 'https://api.seoinjector.com/api';
        $this->cache = $options['cache'] ?? true;
        $this->cacheDuration = $options['cache_duration'] ?? 3600;
        $this->debug = $options['debug'] ?? false;
    }

    /**
     * Set the URL to fetch metadata for
     * 
     * @param  string $url Page URL or path
     * @return self
     * 
     * @example
     * $seo->setUrl('/about')->render();
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;
        return $this;
    }


    /**
     * Render meta tags as HTML
     * 
     * @return string HTML meta tags
     * 
     * @example
     * echo $seo->render();
     */
    public function render(): string
    {
        $url = $this->url ?? $this->getCurrentUrl();
        $metadata = $this->fetchMetadata($url);

        if (!$metadata) {
            return '';
        }

        return $this->convertToHtml($metadata);
    }

    /**
     * Get metadata as array
     * 
     * @return array|null Metadata array or null if not found
     * 
     * @example
     * $metadata = $seo->get();
     * $title = $metadata['title'] ?? 'Default Title';
     */
    public function get(): ?array
    {
        $url = $this->url ?? $this->getCurrentUrl();
        $metadata = $this->fetchMetadata($url);

        if (!$metadata || !isset($metadata['metaTags'])) {
            return null;
        }

        return $this->convertToArray($metadata);
    }

    /**
     * Get current page URL from server variables
     * 
     * @return string Current URL path
     */
    private function getCurrentUrl(): string
    {
        // Try to get from server variables
        $url = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        if (($pos = strpos($url, '?')) !== false) {
            $url = substr($url, 0, $pos);
        }

        return $url;
    }

    /**
     * Fetch metadata from API with caching
     * 
     * @param  string $url Page URL or path
     * @return array|null API response or null
     */
    private function fetchMetadata(string $url): ?array
    {
        $lang = $this->language
            ?? $this->detectLanguage()
            ?? 'en';

        $cacheKey = "seoinjector_{$this->apiKey}_{$url}_{$lang}";

        // Check in-memory cache first
        if (isset($this->cacheStore[$cacheKey])) {
            return $this->cacheStore[$cacheKey];
        }

        // Check file cache if enabled
        if ($this->cache) {
            $cached = $this->getCachedData($cacheKey);
            if ($cached !== null) {
                $this->cacheStore[$cacheKey] = $cached;
                return $cached;
            }
        }

        // Fetch from API
        try {
            $apiUrl = $this->apiUrl . '/meta/' . urlencode($this->apiKey) . '?url=' . urlencode($url);

            $language = $this->language
                ?? $this->detectLanguage()
                ?? 'en';

            $headers = "Accept: application/json\r\n";
            $headers .= "Accept-Language: {$language}\r\n";

            $headers .= "X-SEO-Cache: " . ($this->cache ? '1' : '0') . "\r\n";
            $headers .= "X-SEO-Cache-TTL: {$this->cacheDuration}\r\n";

            $context = stream_context_create(
                [
                    'http' => [
                        'method' => 'GET',
                        'header' => $headers,
                        'timeout' => 5,
                    ],
                ]
            );

            $response = @file_get_contents($apiUrl, false, $context);

            if ($response === false) {
                if ($this->debug) {
                    error_log("SEO Injector: Failed to fetch metadata for {$url}");
                }
                return null;
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                if ($this->debug) {
                    error_log("SEO Injector: Invalid JSON response for {$url}");
                }
                return null;
            }

            // Cache the result
            if ($this->cache) {
                $this->setCachedData($cacheKey, $data);
            }

            $this->cacheStore[$cacheKey] = $data;

            return $data;
        } catch (\Exception $e) {
            if ($this->debug) {
                error_log('SEO Injector Error: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Convert API response to HTML meta tags
     * 
     * @param  array $data API response
     * @return string HTML meta tags
     */
    private function convertToHtml(array $data): string
    {
        if (!isset($data['metaTags']) || !is_array($data['metaTags'])) {
            return '';
        }

        $html = "\n<!-- SEO Injector -->\n";

        foreach ($data['metaTags'] as $tag) {
            if (!is_array($tag)) {
                continue;
            }

            // Handle title tag
            if (isset($tag['name']) && $tag['name'] === 'title' && isset($tag['content'])) {
                $html .= "<title>" . htmlspecialchars($tag['content'], ENT_QUOTES, 'UTF-8') . "</title>\n";
                continue;
            }

            // Handle meta tags with name attribute
            if (isset($tag['name']) && isset($tag['content'])) {
                $html .= '<meta name="' . htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8') .
                    '" content="' . htmlspecialchars($tag['content'], ENT_QUOTES, 'UTF-8') . '">' . "\n";
                continue;
            }

            // Handle meta tags with property attribute (Open Graph)
            if (isset($tag['property']) && isset($tag['content'])) {
                $html .= '<meta property="' . htmlspecialchars($tag['property'], ENT_QUOTES, 'UTF-8') .
                    '" content="' . htmlspecialchars($tag['content'], ENT_QUOTES, 'UTF-8') . '">' . "\n";
                continue;
            }

            // Handle link tags (canonical, etc.)
            if (isset($tag['rel']) && isset($tag['href'])) {
                $html .= '<link rel="' . htmlspecialchars($tag['rel'], ENT_QUOTES, 'UTF-8') .
                    '" href="' . htmlspecialchars($tag['href'], ENT_QUOTES, 'UTF-8') . '">' . "\n";
                continue;
            }
        }

        // Handle link tags (canonical, etc.)
        if (isset($data['hreflangTags'])) {
            foreach ($data['hreflangTags'] as $tag) {
                $html .= '<link hreflang="' . htmlspecialchars($tag['hreflang'] ?? '', ENT_QUOTES, 'UTF-8') . '" rel="' . htmlspecialchars($tag['rel'], ENT_QUOTES, 'UTF-8') .
                    '" href="' . htmlspecialchars($tag['href'], ENT_QUOTES, 'UTF-8') . '">' . "\n";
                continue;
            }
        }

        // Add JSON-LD schema if present
        if (isset($data['schemaJson'])) {
            $schema = is_string($data['schemaJson'])
                ? $data['schemaJson']
                : json_encode($data['schemaJson']);
            $html .= '<script type="application/ld+json">' . $schema . '</script>' . "\n";
        }

        $html .= "<!-- /SEO Injector -->\n";

        return $html;
    }

    /**
     * Convert API response to associative array
     * 
     * @param  array $data API response
     * @return array Metadata as key-value pairs
     */
    private function convertToArray(array $data): array
    {
        $metaTags = $data['metaTags'] ?? [];
        $result = [];
        $hreflangTags = $data['hreflangTags'] ?? [];

        foreach ($metaTags as $tag) {
            if (!is_array($tag)) {
                continue;
            }

            // Extract title
            if (isset($tag['name']) && $tag['name'] === 'title') {
                $result['title'] = $tag['content'];
                continue;
            }

            // Extract other meta tags
            if (isset($tag['name'])) {
                $result[$tag['name']] = $tag['content'];
            }

            // Extract Open Graph tags
            if (isset($tag['property'])) {
                $key = str_replace(':', '_', $tag['property']);
                $result[$key] = $tag['content'];
            }
        }

        foreach ($hreflangTags as $tag) {
            if (!is_array($tag)) {
                continue;
            }
            if (isset($tag['href'])) {
                if (isset($tag['hreflang'])) {
                    // Hreflang links
                    $result['hreflang_' . $tag['hreflang']] = [
                        "rel" => $tag['rel'] ?? 'alternate',
                        "hreflang" => $tag['hreflang'],
                        "href" => $tag["href"],
                    ];
                } else {
                    // Regular links (canonical, etc.) without hreflang
                    $rel = $tag['rel'] ?? 'link';
                    $result[$rel] = $tag['href'];
                }
            }
        }

        // Add schema if present
        if (isset($data['schemaJson'])) {
            $result['schema'] = $data['schemaJson'];
        }

        return $result;
    }

    /**
     * Get cached data from file system
     * 
     * @param  string $key Cache key
     * @return array|null Cached data or null
     */
    private function getCachedData(string $key): ?array
    {
        $cacheDir = sys_get_temp_dir() . '/seoinjector';
        $cacheFile = $cacheDir . '/' . md5($key) . '.cache';

        if (!file_exists($cacheFile)) {
            return null;
        }

        // Check if cache is expired
        if (time() - filemtime($cacheFile) > $this->cacheDuration) {
            @unlink($cacheFile);
            return null;
        }

        $content = @file_get_contents($cacheFile);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        return json_last_error() === JSON_ERROR_NONE ? $data : null;
    }

    /**
     * Save data to file system cache
     * 
     * @param string $key  Cache key
     * @param array  $data Data to cache
     */
    private function setCachedData(string $key, array $data): void
    {
        $cacheDir = sys_get_temp_dir() . '/seoinjector';

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $cacheFile = $cacheDir . '/' . md5($key) . '.cache';
        @file_put_contents($cacheFile, json_encode($data), LOCK_EX);
    }

    /**
     * Clear cached metadata for a specific URL
     * 
     * @param string $url Page URL or path
     */
    public function clearCache(string $url): void
    {
        $lang = $language ?? $this->language ?? $this->detectLanguage() ?? 'en';
        $cacheKey = "seoinjector_{$this->apiKey}_{$url}_{$lang}";

        // Clear in-memory cache
        unset($this->cacheStore[$cacheKey]);

        // Clear file cache
        $cacheDir = sys_get_temp_dir() . '/seoinjector';
        $cacheFile = $cacheDir . '/' . md5($cacheKey) . '.cache';

        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    /**
     * Clear all cached metadata
     */
    public function clearAllCache(): void
    {
        // Clear in-memory cache
        $this->cacheStore = [];

        // Clear file cache directory
        $cacheDir = sys_get_temp_dir() . '/seoinjector';

        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*.cache');
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }

    private function detectLanguage(): ?string
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }

        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        // Split by comma (priority order)
        $parts = explode(',', $header);

        if (empty($parts[0])) {
            return null;
        }

        // Remove optional ;q= value
        $primary = explode(';', $parts[0])[0];

        return trim($primary);
    }

    protected function _testSetCachedData(string $key, array $data): void
    {
        $this->setCachedData($key, $data);
    }

    protected function _testGetCachedData(string $key): ?array
    {
        return $this->getCachedData($key);
    }
}

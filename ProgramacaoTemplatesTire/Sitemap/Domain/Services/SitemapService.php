<?php

namespace Src\Sitemap\Domain\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;
use Carbon\Carbon;

class SitemapService
{
    private const MAX_URLS_PER_SITEMAP = 1000;
    private const CACHE_DURATION = 3600; // 1 hora

    /**
     * Gera todos os sitemaps
     */
    public function generateAll(): array
    {
        $generated = [];

        // Gerar sitemaps de artigos
        $generated['articles'] = $this->generateArticleSitemaps();

        // Gerar sitemaps de artigos AMP
        $generated['articles_amp'] = $this->generateArticleAmpSitemaps();

        // Gerar sitemap de categorias
        $generated['categories'] = $this->generateCategorySitemap();

        // Gerar sitemap de páginas estáticas
        $generated['pages'] = $this->generatePagesSitemap();

        // Gerar sitemap index
        $generated['index'] = $this->generateSitemapIndex();

        return $generated;
    }

    /**
     * Gera sitemaps de artigos divididos por quantidade
     */
    private function generateArticleSitemaps(): array
    {
        $cacheKey = 'sitemap_articles_count';
        $totalArticles = Cache::remember($cacheKey, self::CACHE_DURATION, function () {
            return Article::where('status', 'published')->count();
        });

        $totalSitemaps = ceil($totalArticles / self::MAX_URLS_PER_SITEMAP);
        $generated = [];

        for ($i = 1; $i <= $totalSitemaps; $i++) {
            $filename = "sitemap-articles-{$i}.xml";
            $content = $this->generateArticleSitemap($i);

            Storage::disk('public')->put("sitemaps/{$filename}", $content);
            $generated[] = $filename;
        }

        return $generated;
    }

    /**
     * Gera sitemaps de artigos AMP divididos por quantidade
     */
    private function generateArticleAmpSitemaps(): array
    {
        $cacheKey = 'sitemap_articles_amp_count';
        $totalArticles = Cache::remember($cacheKey, self::CACHE_DURATION, function () {
            return Article::where('status', 'published')->count();
        });

        $totalSitemaps = ceil($totalArticles / self::MAX_URLS_PER_SITEMAP);
        $generated = [];

        for ($i = 1; $i <= $totalSitemaps; $i++) {
            $filename = "sitemap-articles-amp-{$i}.xml";
            $content = $this->generateArticleAmpSitemap($i);

            Storage::disk('public')->put("sitemaps/{$filename}", $content);
            $generated[] = $filename;
        }

        return $generated;
    }

    /**
     * Gera um sitemap específico de artigos AMP
     */
    private function generateArticleAmpSitemap(int $page): string
    {
        $offset = ($page - 1) * self::MAX_URLS_PER_SITEMAP;

        $cacheKey = "sitemap_articles_amp_page_{$page}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($offset) {
            $articles = Article::where('status', 'published')
                ->select(['slug', 'updated_at', 'created_at'])
                ->orderBy('updated_at', 'desc')
                ->offset($offset)
                ->limit(self::MAX_URLS_PER_SITEMAP)
                ->get();

            return $this->buildXmlSitemap($articles, 'article_amp');
        });
    }
    private function generateArticleSitemap(int $page): string
    {
        $offset = ($page - 1) * self::MAX_URLS_PER_SITEMAP;

        $cacheKey = "sitemap_articles_page_{$page}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($offset) {
            $articles = Article::where('status', 'published')
                ->select(['slug', 'updated_at', 'created_at'])
                ->orderBy('updated_at', 'desc')
                ->offset($offset)
                ->limit(self::MAX_URLS_PER_SITEMAP)
                ->get();

            return $this->buildXmlSitemap($articles, 'article');
        });
    }

    /**
     * Gera sitemap de categorias
     */
    private function generateCategorySitemap(): string
    {
        $filename = 'sitemap-categories-1.xml';

        $content = Cache::remember('sitemap_categories', self::CACHE_DURATION, function () {
            $categories = MaintenanceCategory::where('to_follow', true)
                ->select(['slug', 'updated_at', 'created_at'])
                ->orderBy('updated_at', 'desc')
                ->get();

            return $this->buildXmlSitemap($categories, 'category');
        });

        Storage::disk('public')->put("sitemaps/{$filename}", $content);
        return $filename;
    }

    /**
     * Gera sitemap de páginas estáticas
     */
    private function generatePagesSitemap(): string
    {
        $filename = 'sitemap-paginas.xml';

        $content = Cache::remember('sitemap_pages', self::CACHE_DURATION, function () {
            $pages = collect([
                ['slug' => route('about'), 'updated_at' => now(), 'priority' => '0.8'],
                ['slug' => route('privacy-policy'), 'updated_at' => now(), 'priority' => '0.5'],
                ['slug' => route('terms-of-use'), 'updated_at' => now(), 'priority' => '0.5'],
                ['slug' => route('cookie-policy'), 'updated_at' => now(), 'priority' => '0.5'],
                ['slug' => route('cancellation-refund-policy'), 'updated_at' => now(), 'priority' => '0.5'],
                ['slug' => route('interest-based-ads'), 'updated_at' => now(), 'priority' => '0.5'],
                ['slug' => route('contact-us'), 'updated_at' => now(), 'priority' => '0.7'],
                ['slug' => route('help'), 'updated_at' => now(), 'priority' => '0.6'],
                ['slug' => route('terms-of-purchase'), 'updated_at' => now(), 'priority' => '0.5'],
                ['slug' => route('checkout.help'), 'updated_at' => now(), 'priority' => '0.4'],
                ['slug' => route('editorial-team'), 'updated_at' => now(), 'priority' => '0.6'],
                ['slug' => route('editorial-methodology'), 'updated_at' => now(), 'priority' => '0.6'],
            ]);

            return $this->buildXmlSitemap($pages, 'page');
        });

        Storage::disk('public')->put("sitemaps/{$filename}", $content);
        return $filename;
    }

    /**
     * Gera o sitemap index principal
     */
    private function generateSitemapIndex(): string
    {
        $content = Cache::remember('sitemap_index', self::CACHE_DURATION, function () {
            $sitemapFiles = [];

            // Coletar todos os arquivos de sitemap
            $files = Storage::disk('public')->files('sitemaps');

            foreach ($files as $file) {
                if (str_ends_with($file, '.xml') && !str_contains($file, 'sitemap.xml')) {
                    $filename = basename($file);
                    $lastmod = Carbon::createFromTimestamp(Storage::disk('public')->lastModified($file));

                    $sitemapFiles[] = [
                        'loc' => url("storage/sitemaps/{$filename}"),
                        'lastmod' => $lastmod->utc()->toAtomString()
                    ];
                }
            }

            return $this->buildSitemapIndex($sitemapFiles);
        });

        Storage::disk('public')->put('sitemaps/sitemap.xml', $content);
        return 'sitemap.xml';
    }

    /**
     * Constrói XML do sitemap
     */
    private function buildXmlSitemap(Collection $items, string $type): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($items as $item) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . $this->generateUrl($item, $type) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . Carbon::parse($item['updated_at'] ?? $item->updated_at)->utc()->toAtomString() . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>' . $this->getChangefreq($type) . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $this->getPriority($type, $item) . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Constrói XML do sitemap index
     */
    private function buildSitemapIndex(array $sitemaps): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($sitemaps as $sitemap) {
            $xml .= '  <sitemap>' . PHP_EOL;
            $xml .= '    <loc>' . $sitemap['loc'] . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $sitemap['lastmod'] . '</lastmod>' . PHP_EOL;
            $xml .= '  </sitemap>' . PHP_EOL;
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    /**
     * Gera URL baseado no tipo
     */
    private function generateUrl($item, string $type): string
    {
        $slug = $item['slug'] ?? $item->slug;

        return match ($type) {
            'article' => route("info.article.show", $slug),        // /info/{slug}
            'article_amp' => route("info.article.show.amp", $slug), // /info/{slug}/amp
            'category' => route("info.category.show", $slug),
            'page' => $slug,
            default => url("/{$slug}")
        };
    }

    /**
     * Define frequência de mudança
     */
    private function getChangefreq(string $type): string
    {
        return match ($type) {
            'article' => 'weekly',
            'article_amp' => 'weekly',
            'category' => 'monthly',
            'page' => 'yearly',
            default => 'monthly'
        };
    }

    /**
     * Define prioridade da URL
     */
    private function getPriority(string $type, $item): string
    {
        if (isset($item['priority'])) {
            return $item['priority'];
        }

        return match ($type) {
            'article' => '0.9',
            'article_amp' => '0.85', // Ligeiramente menor que a versão normal
            'category' => '0.8',
            'page' => '0.6',
            default => '0.5'
        };
    }

    /**
     * Limpa cache dos sitemaps
     */
    public function clearCache(): void
    {
        $keys = [
            'sitemap_index',
            'sitemap_categories',
            'sitemap_pages',
            'sitemap_articles_count',
            'sitemap_articles_amp_count'
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Limpar cache de páginas de artigos
        for ($i = 1; $i <= 100; $i++) { // Assumindo máximo 100 páginas
            Cache::forget("sitemap_articles_page_{$i}");
            Cache::forget("sitemap_articles_amp_page_{$i}");
        }
    }

    /**
     * Submete sitemaps para motores de busca
     */
    public function submitToSearchEngines(): array
    {
        $sitemapUrl = url('storage/sitemaps/sitemap.xml');
        $results = [];

        // Google
        $googleUrl = "https://www.google.com/ping?sitemap=" . urlencode($sitemapUrl);
        $results['google'] = $this->pingUrl($googleUrl);

        // Bing
        $bingUrl = "https://www.bing.com/ping?sitemap=" . urlencode($sitemapUrl);
        $results['bing'] = $this->pingUrl($bingUrl);

        return $results;
    }

    /**
     * Faz ping para URL
     */
    private function pingUrl(string $url): bool
    {
        try {
            $response = file_get_contents($url);
            return $response !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

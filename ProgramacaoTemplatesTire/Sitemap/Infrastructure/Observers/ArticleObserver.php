<?php

namespace Src\Sitemap\Infrastructure\Observers;

use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\Sitemap\Domain\Services\SitemapService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArticleObserver
{
    private SitemapService $sitemapService;
    
    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }
    
    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        if ($article->status === 'published') {
            $this->invalidateSitemapCache('created');
        }
    }
    
    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        // Verificar se mudou para published ou deixou de ser published
        if ($article->isDirty('status') || $article->status === 'published') {
            $this->invalidateSitemapCache('updated');
        }
    }
    
    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        if ($article->status === 'published') {
            $this->invalidateSitemapCache('deleted');
        }
    }
    
    /**
     * Invalida cache dos sitemaps
     */
    private function invalidateSitemapCache(string $action): void
    {
        try {
            // Limpar cache específico dos sitemaps
            $this->sitemapService->clearCache();
            
            // Log da ação para debug
            Log::info("Sitemap cache invalidated due to article {$action}");
            
            // Opcional: Regenerar automaticamente em background
            if (config('sitemap.auto_regenerate', false)) {
                dispatch(function () {
                    $this->sitemapService->generateAll();
                })->afterResponse();
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to invalidate sitemap cache: ' . $e->getMessage());
        }
    }
}
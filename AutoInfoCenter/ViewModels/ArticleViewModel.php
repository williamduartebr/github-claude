<?php

namespace Src\AutoInfoCenter\ViewModels;

use Src\AutoInfoCenter\Domain\Services\ArticleService;
use Src\AutoInfoCenter\Domain\Services\ArticleCacheService;
use Src\AutoInfoCenter\Domain\Services\TemplateDetectorService;
use Src\AutoInfoCenter\Factories\TemplateViewModelFactory;
use Illuminate\Database\Eloquent\Collection;

class ArticleViewModel
{
    public function __construct(
        private readonly ArticleService $articleService,
        private readonly ArticleCacheService $cacheService,
        private readonly TemplateDetectorService $templateDetector,
        private readonly TemplateViewModelFactory $viewModelFactory
    ) {}

    /**
     * Obtém os dados de um artigo processados para seu template específico
     */
    public function getArticleBySlug(string $slug): mixed
    {
        return $this->cacheService->rememberArticle($slug, function () use ($slug) {
            $article = $this->articleService->findBySlug($slug);

            if (!$article) {
                return null;
            }

            $templateType = $this->templateDetector->detectTemplate($article);
            $templateViewModel = $this->viewModelFactory->make($templateType, $article);

            return $templateViewModel->processArticleData();
        });
    }

    /**
     * Obtém os artigos mais recentes
     */
    public function getRecentArticles(int $limit = 6): Collection
    {
        return $this->articleService->getRecentArticles($limit);
    }

    /**
     * Invalida o cache de um artigo
     */
    public function invalidateArticleCache(string $slug): bool
    {
        return $this->cacheService->forgetArticle($slug);
    }
}
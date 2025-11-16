<?php

namespace Src\AutoInfoCenter\ViewModels;

use Src\AutoInfoCenter\Domain\Repositories\ArticleRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class RecentArticlesViewModel
{
    /**
     * @var int Tempo de cache em minutos (1 hora)
     */
    private const CACHE_MINUTES = 60;

    public function __construct(
        private ArticleRepositoryInterface $articleRepository
    ) {}

    /**
     * Obtém artigos recentes com paginação
     *
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getRecentArticles(int $page = 1, int $perPage = 12): array
    {
        $cacheKey = "recent_articles_view_page_{$page}_per_{$perPage}";

        return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($page, $perPage) {
            $offset = ($page - 1) * $perPage;
            
            $articles = $this->articleRepository->getRecentPaginated($perPage, $offset);
            $total = $this->articleRepository->countRecent();
            
            $totalPages = (int) ceil($total / $perPage);
            
            return [
                'articles' => $articles,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_prev' => $page > 1,
                    'has_next' => $page < $totalPages,
                    'prev_page' => $page > 1 ? $page - 1 : null,
                    'next_page' => $page < $totalPages ? $page + 1 : null,
                ],
            ];
        });
    }
}

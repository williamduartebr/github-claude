<?php

namespace Src\AutoInfoCenter\ViewModels;

use Src\AutoInfoCenter\Domain\Services\MaintenanceCategoryService;
use Src\AutoInfoCenter\Domain\Repositories\ArticleRepositoryInterface;

class InfoCategoryViewModel
{
    public function __construct(
        private MaintenanceCategoryService $categoryService,
        private ArticleRepositoryInterface $articleRepository
    ) {
        //
    }

    public function findBySlug($slug)
    {
        $category = $this->categoryService->findBySlug($slug);
        
        if (!$category) {
            return null;
        }

        return $category;
    }

    /**
     * Busca artigos paginados para uma categoria
     */
    public function getArticlesByCategory($categorySlug, $page = 1, $perPage = 12)
    {
        $offset = ($page - 1) * $perPage;
        
        // Busca artigos da categoria
        $articles = $this->articleRepository->findByCategory($categorySlug, $perPage, $offset);
        
        // Conta total de artigos para paginação
        $totalArticles = $this->articleRepository->countByCategory($categorySlug);
        
        // Calcula dados da paginação
        $totalPages = ceil($totalArticles / $perPage);
        $hasNextPage = $page < $totalPages;
        $hasPrevPage = $page > 1;
        
        return [
            'articles' => $articles,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalArticles,
                'total_pages' => $totalPages,
                'has_next' => $hasNextPage,
                'has_prev' => $hasPrevPage,
                'next_page' => $hasNextPage ? $page + 1 : null,
                'prev_page' => $hasPrevPage ? $page - 1 : null,
            ]
        ];
    }

    /**
     * Busca artigos filtrados por veículo e categoria
     */
    public function getFilteredArticles($categorySlug, $filters = [], $page = 1, $perPage = 12)
    {
        $offset = ($page - 1) * $perPage;
        
        // Se há filtros de veículo, usa método específico
        if (!empty($filters['marca'])) {
            $articles = $this->articleRepository->findByCategoryAndVehicle(
                $categorySlug,
                $filters['marca'],
                $filters['modelo'] ?? null,
                $filters['ano'] ?? null,
                $perPage,
                $offset
            );
            
            $totalArticles = $this->articleRepository->countByCategoryAndVehicle(
                $categorySlug,
                $filters['marca'],
                $filters['modelo'] ?? null,
                $filters['ano'] ?? null
            );
        } else {
            // Sem filtros, busca normal
            $articles = $this->articleRepository->findByCategory($categorySlug, $perPage, $offset);
            $totalArticles = $this->articleRepository->countByCategory($categorySlug);
        }
        
        // Calcula dados da paginação
        $totalPages = ceil($totalArticles / $perPage);
        $hasNextPage = $page < $totalPages;
        $hasPrevPage = $page > 1;
        
        return [
            'articles' => $articles,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalArticles,
                'total_pages' => $totalPages,
                'has_next' => $hasNextPage,
                'has_prev' => $hasPrevPage,
                'next_page' => $hasNextPage ? $page + 1 : null,
                'prev_page' => $hasPrevPage ? $page - 1 : null,
            ]
        ];
    }
}
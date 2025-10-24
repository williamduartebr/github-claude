<?php

namespace Src\AutoInfoCenter\ViewModels;

use Src\AutoInfoCenter\Domain\Services\MaintenanceCategoryService;
use Src\AutoInfoCenter\Domain\Repositories\ArticleRepositoryInterface;

class PopularCategoriesViewModel
{
    protected $categoryService;
    protected $articleRepository;

    public function __construct(
        MaintenanceCategoryService $categoryService,
        ArticleRepositoryInterface $articleRepository
    ) {
        $this->categoryService = $categoryService;
        $this->articleRepository = $articleRepository;
    }

    public function getCategories($limit = 6, $withArticles = false)
    {
        $categories = $this->categoryService->getPopularCategories($limit);
        
        if (!$withArticles) {
            return $categories;
        }

        // Para cada categoria, busca alguns artigos
        $categoriesWithArticles = $categories->shuffle()->take($limit)->map(function ($category) {
            $articles = $this->articleRepository->findByCategory($category->slug, 5);
            
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'icon_svg' => $category->icon_svg,
                'icon_bg_color' => $category->icon_bg_color,
                'icon_text_color' => $category->icon_text_color,
                'to_follow' => $category->to_follow,
                'articles' => $articles->map(function ($article) {
                    return [
                        'title' => $article->title,
                        'slug' => $article->slug,
                        'vehicle_info' => $article->vehicle_info ?? [],
                        'created_at' => $article->created_at
                    ];
                })
            ];
        });

        return $categoriesWithArticles;
    }

    /**
     * Método específico para o info-center com artigos
     */
    public function getCategoriesWithArticles($limit = 6)
    {
        return $this->getCategories($limit, true);
    }
}
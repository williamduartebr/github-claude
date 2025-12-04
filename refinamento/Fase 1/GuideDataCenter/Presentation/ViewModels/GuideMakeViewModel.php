<?php

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;

/**
 * ViewModel para página de marca (todos os guias de uma marca)
 * 
 * Rota: /guias/marca/{make}
 * View: make.index
 * Exemplo: /guias/marca/toyota
 * 
 * ✅ REFINADO - Sprint 4
 */
class GuideMakeViewModel
{
    private $make;
    private Collection $guides;
    private Collection $categories;
    private GuideRepositoryInterface $guideRepo;

    public function __construct(
        $make,
        Collection $guides,
        Collection $categories,
        ?GuideRepositoryInterface $guideRepo = null
    ) {
        $this->make = $make;
        $this->guides = $guides;
        $this->categories = $categories;
        $this->guideRepo = $guideRepo ?? app(GuideRepositoryInterface::class);
    }

    /**
     * Retorna dados da marca
     */
    public function getMake(): array
    {
        return [
            'id' => $this->make->id ?? null,
            'name' => $this->make->name ?? 'Marca',
            'slug' => $this->make->slug ?? 'marca',
            'logo' => $this->make->logo_url ?? "/images/logos/{$this->make->slug}.svg",
            'description' => $this->make->description ?? null,
        ];
    }

    /**
     * Retorna categorias disponíveis com contagem de guias
     */
    public function getCategories(): array
    {
        $makeSlug = $this->make->slug;

        return $this->categories->map(function($category) use ($makeSlug) {
            // Contar guias desta categoria + marca
            $guidesCount = $this->guides
                ->where('category_slug', $category->slug)
                ->count();

            return [
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon ?? '📋',
                'description' => $category->description ?? null,
                'guides_count' => $guidesCount,
                'url' => route('guide.category.make', [
                    'category' => $category->slug,
                    'make' => $makeSlug
                ]),
            ];
        })
        ->sortByDesc('guides_count')
        ->values()
        ->toArray();
    }

    /**
     * Retorna modelos populares da marca (top 8)
     */
    public function getPopularModels(): array
    {
        // Agrupar por modelo e contar
        $modelCounts = $this->guides->groupBy('model_slug')
            ->map(function($group) {
                $first = $group->first();
                return [
                    'name' => $first->model,
                    'slug' => $first->model_slug,
                    'count' => $group->count(),
                    'categories' => $group->pluck('category_slug')->unique()->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(8);

        $makeSlug = $this->make->slug;

        return $modelCounts->map(function($model) use ($makeSlug) {
            return [
                'name' => $model['name'],
                'slug' => $model['slug'],
                'guides_count' => $model['count'],
                'categories_count' => $model['categories'],
                'url' => route('guide.category.make.model', [
                    'category' => 'oleo', // Categoria padrão para link
                    'make' => $makeSlug,
                    'model' => $model['slug']
                ]),
            ];
        })->values()->toArray();
    }

    /**
     * Retorna todos os modelos ordenados alfabeticamente
     */
    public function getAllModels(): array
    {
        $modelGroups = $this->guides->groupBy('model_slug')
            ->map(function($group) {
                $first = $group->first();
                return [
                    'name' => $first->model,
                    'slug' => $first->model_slug,
                    'guides_count' => $group->count(),
                ];
            })
            ->sortBy('name');

        $makeSlug = $this->make->slug;

        return $modelGroups->map(function($model) use ($makeSlug) {
            return [
                'name' => $model['name'],
                'slug' => $model['slug'],
                'guides_count' => $model['guides_count'],
                'url' => route('guide.category.make.model', [
                    'category' => 'oleo',
                    'make' => $makeSlug,
                    'model' => $model['slug']
                ]),
            ];
        })->values()->toArray();
    }

    /**
     * Retorna estatísticas da marca
     */
    public function getStats(): array
    {
        return [
            'total_guides' => $this->guides->count(),
            'total_categories' => $this->categories->count(),
            'total_models' => $this->guides->pluck('model_slug')->unique()->count(),
        ];
    }

    /**
     * Retorna dados SEO
     */
    public function getSeoData(): array
    {
        $make = $this->getMake();
        
        return [
            'title' => "Guias {$make['name']} – Especificações por Modelo e Categoria | Mercado Veículos",
            'description' => "Encontre guias completos de {$make['name']}: óleo, calibragem, pneus, consumo e mais. Especificações técnicas por modelo e ano.",
            'canonical' => route('guide.make', ['make' => $make['slug']]),
            'og_image' => $make['logo'],
        ];
    }

    /**
     * Retorna breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        $make = $this->getMake();
        
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $make['name'], 'url' => null],
        ];
    }
}
<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;

/**
 * ViewModel para página de marca (todos os guias de uma marca)
 * 
 * Rota: /guias/marca/{make}
 * View: guide-data-center::guide.make
 * Exemplo: /guias/marca/toyota
 * 
 * ✅ CORRIGIDO - Links apontam para /guias/marca/{make}/{model}
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
                'icon_svg' => $category->icon_svg ?? null,
                'icon_bg_color' => $category->icon_bg_color ?? null,
                'icon_text_color' => $category->icon_text_color ?? null,
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
     * ✅ CORRIGIDO: Retorna modelos populares da marca (top 8)
     * Agora usa a rota correta: /guias/marca/{make}/{model}
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
                // ✅ CORRIGIDO: Rota sem categoria
                'url' => route('guide.make.model', [
                    'make' => $makeSlug,
                    'model' => $model['slug']
                ]),
            ];
        })->values()->toArray();
    }

    /**
     * ✅ CORRIGIDO: Retorna todos os modelos ordenados alfabeticamente
     * Agora usa a rota correta: /guias/marca/{make}/{model}
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
                // ✅ CORRIGIDO: Rota sem categoria
                'url' => route('guide.make.model', [
                    'make' => $makeSlug,
                    'model' => $model['slug']
                ]),
            ];
        })->values()->toArray();
    }

    /**
     * Retorna estatísticas
     */
    public function getStats(): array
    {
        $totalModels = $this->guides->pluck('model_slug')->unique()->count();
        $totalCategories = $this->guides->pluck('category_slug')->unique()->count();

        return [
            'total_guides' => $this->guides->count(),
            'total_models' => $totalModels,
            'total_categories' => $totalCategories,
        ];
    }

    /**
     * Retorna dados de SEO
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
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $make['name'], 'url' => null],
        ];
    }
}
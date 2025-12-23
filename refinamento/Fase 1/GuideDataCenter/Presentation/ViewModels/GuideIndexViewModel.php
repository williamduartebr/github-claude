<?php

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;

/**
 * ViewModel para página inicial de guias
 * 
 * Rota: /guias
 * View: guides.index
 * 
 * ✅ REFINADO: Remove mocks e usa dados reais do MongoDB
 */
class GuideIndexViewModel
{
    private Collection $categories;
    private Collection $makes;
    private Collection $recentGuides;

    public function __construct()
    {
        $categoryRepo = app(GuideCategoryRepositoryInterface::class);
        $guideRepo = app(GuideRepositoryInterface::class);

        // Buscar categorias ativas
        $this->categories = $categoryRepo->getAllActive();

        // Buscar marcas únicas (distinct make_slug)
        $this->makes = $this->getUniqueMakes($guideRepo);

        // Buscar guias recentes
        $this->recentGuides = $guideRepo->findByFilters([
            'limit' => 12,
            'order_by' => 'created_at',
            'order_direction' => 'desc'
        ]);
    }

    /**
     * ✅ REFINADO: Retorna categorias REAIS do MongoDB
     */
    public function getCategories(): array
    {
        return $this->categories->map(function ($category) {
            return [
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon ?? '📄',
                'description' => $category->description ?? '',
                'icon_svg' => $category->icon_svg ?? '',
                'icon_bg_color' => $category->icon_bg_color ?? '',
                'icon_text_color' => $category->icon_text_color ?? '',
                'seo_info' => $category->seo_info ?? '',
                'url' => route('guide.category', ['category' => $category->slug]),
            ];
        })->toArray();
    }

    /**
     * ✅ REFINADO: Retorna marcas únicas REAIS do MongoDB
     */
    public function getMakes(): array
    {
        return $this->makes->map(function ($make) {
            return [
                'slug' => $make->slug,
                'name' => $make->name,
                'make_logo' => $make->make_logo,
                'url' => route('guide.make', $make->slug), // URL de busca por marca
            ];
        })->toArray();
    }

    /**
     * Retorna guias recentes formatados
     */
    public function getRecentGuides(): array
    {
        return $this->recentGuides->map(function ($guide) {
            return [
                'title' => $guide->payload['title'] ?? $guide->full_title,
                'slug' => $guide->slug,
                'url' => $guide->url ?? route('guide.show', ['slug' => $guide->slug]),
                'category' => $guide->category->name ?? '',
                'make' => $guide->make,
                'model' => $guide->model,
            ];
        })->toArray();
    }

    /**
     * Retorna modelos populares
     * Extrai os modelos mais comuns dos guias recentes
     */
    public function getPopularModels(): array
    {
        // Usar a collection original de guias, não o método getRecentGuides()
        // Agrupar guias por make+model e contar
        $modelCounts = $this->recentGuides
            ->groupBy(function ($guide) {
                return $guide->make_slug . '|' . $guide->model_slug;
            })
            ->map(function ($guides) {
                $first = $guides->first();

                $makeName = $first->make;
                $modelName = $first->model;

                return [
                    'name' => "{$makeName} {$modelName}",
                    'make' => $makeName,
                    'make_slug' => $first->make_slug,
                    'model' => $modelName,
                    'model_slug' => $first->model_slug,
                    'count' => $guides->count(),
                    'image' => "/images/placeholder/{$first->model_slug}-hero.jpg",
                    'description' => "Veja todos os guias técnicos do {$makeName} {$modelName}",
                    'url' => route('guide.make.model', [
                        'make' => $first->make_slug,
                        'model' => $first->model_slug
                    ])
                ];
            })
            ->sortByDesc('count')
            ->take(6)
            ->values();

        return $modelCounts->toArray();
    }

    /**
     * Retorna estatísticas
     */
    public function getStats(): array
    {
        $guideRepo = app(GuideRepositoryInterface::class);

        return [
            'total_categories' => $this->categories->count(),
            'total_makes' => $this->makes->count(),
            'total_guides' => $guideRepo->count(),
        ];
    }

    /**
     * Busca marcas únicas dos guias
     * 
     * @param GuideRepositoryInterface $guideRepo
     * @return Collection
     */
    private function getUniqueMakes(GuideRepositoryInterface $guideRepo): Collection
    {
        // Buscar todos os guias (limitado para performance)
        $guides = $guideRepo->findByFilters(['limit' => 1000]);

        // Extrair make_slug únicos
        $uniqueMakes = $guides->pluck('make_slug')->unique()->sort()->values();

        // Criar collection de objetos com name e slug
        return $uniqueMakes->map(function ($makeSlug) {
            // Buscar o primeiro guia desta marca para pegar o nome completo
            $guide = app(GuideRepositoryInterface::class)->findByFilters([
                'make_slug' => $makeSlug,
                'limit' => 1
            ])->first();

            return (object) [
                'slug' => $makeSlug,
                'name' => $guide ? $guide->make : ucfirst($makeSlug),
                'make_logo' => $guide->make_logo_url,
            ];
        });
    }

    /**
     * ✅ WEB PAGE SCHEMA (para blade)
     */
    public function getWebPageSchema(): array
    {
        $seo = $this->getSeoData();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Guias Automotivos - Especificações Técnicas | Mercado Veículos',
            'description' => $seo['description'],
            'url' => route('guide.index'),

            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Mercado Veículos',
                'url' => url('/'),
            ],

            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => ['h1', 'h2'],
            ],

            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $this->buildCategoryItemList(),
            ],

            'breadcrumb' => $this->getBreadcrumbStructuredData(),
        ];
    }

    /**
     * Breadcrumb em formato Schema.org
     */
    private function getBreadcrumbStructuredData(): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Início',
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Guias',
                    'item' => route('guide.index'),
                ],
            ],
        ];
    }


    /**
     * Lista de categorias em formato Schema.org
     */
    private function buildCategoryItemList(): array
    {
        return $this->categories
            ->map(function ($category, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $category->name,
                    'url' => route('guide.category', ['category' => $category->slug]),
                ];
            })
            ->toArray();
    }

    /**
     * Retorna SEO data
     */
    public function getSeoData(): array
    {
        return [
            'title' => 'Guias Automotivos — Especificações Técnicas Completas | Mercado Veículos',
            'description' => 'Guias técnicos completos para todos os veículos: óleo, pneus, calibragem, revisões, consumo, problemas comuns e muito mais. Base de conhecimento automotivo.',
            'canonical' => route('guide.index'),
            'og_image' => asset('images/og-guias-default.jpg'),
        ];
    }

    /**
     * Retorna breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => null],
        ];
    }
}

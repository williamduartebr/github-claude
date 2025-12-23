<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;

/**
 * GuideMakeModelViewModel
 * 
 * Responsabilidade: Preparar dados para visão geral de um modelo (todas categorias)
 * Rota: GET /guias/marca/{make}/{model}
 * Exemplo: /guias/marca/toyota/corolla
 * 
 * ✅ CHAVES DE SEO PADRONIZADAS
 * 
 * @package Src\GuideDataCenter\Presentation\ViewModels
 */
class GuideMakeModelViewModel
{
    private ?VehicleMake $make;
    private ?VehicleModel $model;
    private Collection $guides;
    private Collection $categories;
    private Collection $categoriesWithGuides;
    private Collection $yearsList;
    private Collection $relatedModels;

    public function __construct(
        ?VehicleMake $make,
        ?VehicleModel $model,
        Collection $guides,
        Collection $categories
    ) {
        $this->make = $make;
        $this->model = $model;
        $this->guides = $guides;
        $this->categories = $categories;
        
        // Processar dados
        $this->categoriesWithGuides = $this->buildCategoriesWithGuides();
        $this->yearsList = $this->extractYearsList();
        $this->relatedModels = $this->loadRelatedModels();
    }

    // ============================================================
    // GETTERS PÚBLICOS
    // ============================================================

    /**
     * Retorna dados da marca
     */
    public function getMake(): array
    {
        if (!$this->make) {
            return [
                'id' => 0,
                'name' => 'Marca',
                'slug' => '',
                'logo' => '',
                'url' => '',
            ];
        }

        return [
            'id' => $this->make->id,
            'name' => $this->make->name,
            'slug' => $this->make->slug,
            'logo' => $this->make->logo_url ?? asset('images/logos/default.png'),
            'url' => route('guide.make', ['make' => $this->make->slug]),
        ];
    }

    /**
     * Retorna dados do modelo
     */
    public function getModel(): array
    {
        if (!$this->model) {
            return [
                'id' => 0,
                'name' => 'Modelo',
                'slug' => '',
                'full_name' => '',
                'description' => '',
                'category' => '',
            ];
        }

        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'slug' => $this->model->slug,
            'full_name' => "{$this->make->name} {$this->model->name}",
            'description' => $this->buildModelDescription(),
            'category' => $this->model->category ?? 'Veículo',
        ];
    }

    /**
     * Retorna categorias com guias formatadas
     */
    public function getCategoriesWithGuides(): array
    {
        return $this->categoriesWithGuides
            ->map(function ($categoryData) {
                return [
                    'name' => $categoryData['name'],
                    'slug' => $categoryData['slug'],
                    'has_guides' => $categoryData['has_guides'],
                    'guides_count' => $categoryData['guides_count'],
                    'latest_year' => $categoryData['latest_year'],
                    'url' => $categoryData['url'],
                    'icon_svg' => $this->getCategoryIconSvg($categoryData['slug']),
                    'icon_bg_color' => $this->getCategoryBgColor($categoryData['slug']),
                    'icon_text_color' => $this->getCategoryTextColor($categoryData['slug']),
                ];
            })
            ->toArray();
    }

    /**
     * Retorna lista de anos
     */
    public function getYearsList(): array
    {
        return $this->yearsList
            ->sortByDesc('year')
            ->values()
            ->map(function ($yearData) {
                return [
                    'year' => $yearData['year'],
                    'has_guides' => $yearData['guides_count'] > 0,
                    'guides_count' => $yearData['guides_count'],
                    'url' => $this->buildYearUrl($yearData['year']),
                ];
            })
            ->toArray();
    }

    /**
     * Retorna modelos relacionados
     */
    public function getRelatedModels(): array
    {
        return $this->relatedModels
            ->map(function ($relatedModel) {
                return [
                    'name' => $relatedModel->name,
                    'slug' => $relatedModel->slug,
                    'guides_count' => $relatedModel->guides_count ?? 0,
                    'url' => route('guide.make.model', [
                        'make' => $this->make->slug,
                        'model' => $relatedModel->slug,
                    ]),
                ];
            })
            ->toArray();
    }

    /**
     * Retorna estatísticas
     */
    public function getStats(): array
    {
        $years = $this->yearsList->pluck('year');
        
        return [
            'total_guides' => $this->guides->count(),
            'total_categories' => $this->categories->count(),
            'categories_with_guides' => $this->categoriesWithGuides->where('has_guides', true)->count(),
            'total_versions' => $this->guides->unique('version_slug')->count(),
            'years_range' => $this->formatYearsRange($years->min(), $years->max()),
        ];
    }

    /**
     * ✅ DADOS DE SEO OTIMIZADOS (CHAVES PADRONIZADAS)
     */
    public function getSeoData(): array
    {
        $make = $this->getMake();
        $model = $this->getModel();

        return [
            // ✅ CHAVES PADRONIZADAS
            'title' => "Guias Técnicos {$make['name']} {$model['name']} | Mercado Veículos",
            'h1' => "Guias Técnicos {$model['full_name']}",
            'description' => $this->buildMetaDescription(),
            'keywords' => $this->buildKeywords(),
            'canonical' => $this->buildCanonicalUrl(),
            'robots' => 'index,follow',
            
            // ✅ Open Graph (chaves diretas)
            'og_title' => "Guias Técnicos {$make['name']} {$model['name']}",
            'og_description' => $this->buildMetaDescription(),
            'og_image' => $this->buildOgImage(),
            'og_url' => $this->buildCanonicalUrl(),
            'og_type' => 'website',
            'og_site_name' => 'Mercado Veículos',
            'og_locale' => 'pt_BR',
            
            // ✅ Twitter Cards (chaves diretas)
            'twitter_card' => 'summary_large_image',
            'twitter_title' => "Guias Técnicos {$make['name']} {$model['name']}",
            'twitter_description' => $this->buildMetaDescription(),
            'twitter_image' => $this->buildOgImage(),
        ];
    }

    /**
     * ✅ BREADCRUMBS
     */
    public function getBreadcrumbs(): array
    {
        $make = $this->getMake();
        $model = $this->getModel();

        return [
            [
                'name' => 'Início',
                'url' => route('home'),
            ],
            [
                'name' => 'Guias',
                'url' => route('guide.index'),
            ],
            [
                'name' => $make['name'],
                'url' => route('guide.make', ['make' => $make['slug']]),
            ],
            [
                'name' => $model['name'],
                'url' => null, // Página atual
            ],
        ];
    }

    /**
     * ✅ STRUCTURED DATA (Schema.org)
     */
    public function getStructuredData(): array
    {
        $seo = $this->getSeoData();
        $make = $this->getMake();
        $model = $this->getModel();
        $stats = $this->getStats();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            
            'name' => $seo['h1'],
            'description' => $seo['description'],
            'url' => $seo['canonical'],
            
            'about' => [
                '@type' => 'Car',
                'name' => $model['full_name'],
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $make['name'],
                ],
                'model' => $model['name'],
            ],
            
            'breadcrumb' => $this->getBreadcrumbStructuredData(),
            
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $stats['categories_with_guides'],
                'itemListElement' => $this->buildItemListStructuredData(),
            ],
            
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Mercado Veículos',
                'url' => 'https://mercadoveiculos.com.br',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://mercadoveiculos.s3.amazonaws.com/statics/logos/logo-mercadoveiculos.png',
                ],
            ],
        ];
    }

    // ============================================================
    // MÉTODOS PRIVADOS
    // ============================================================

    /**
     * Constrói categorias com guias
     */
    private function buildCategoriesWithGuides(): Collection
    {
        return $this->categories->map(function ($category) {
            $guidesInCategory = $this->guides->where('category_slug', $category->slug);
            $hasGuides = $guidesInCategory->isNotEmpty();
            
            return [
                'name' => $category->name,
                'slug' => $category->slug,
                'has_guides' => $hasGuides,
                'guides_count' => $guidesInCategory->count(),
                'latest_year' => $hasGuides ? $guidesInCategory->max('year_end') : null,
                'url' => route('guide.category.make.model', [
                    'category' => $category->slug,
                    'make' => $this->make->slug,
                    'model' => $this->model->slug,
                ]),
            ];
        });
    }

    /**
     * Extrai lista de anos
     */
    private function extractYearsList(): Collection
    {
        $yearsMap = [];

        foreach ($this->guides as $guide) {
            $yearStart = $guide->year_start;
            $yearEnd = $guide->year_end ?? $yearStart;

            if (!$yearStart) {
                continue;
            }

            for ($year = $yearStart; $year <= $yearEnd; $year++) {
                if (!isset($yearsMap[$year])) {
                    $yearsMap[$year] = [
                        'year' => $year,
                        'guides_count' => 0,
                    ];
                }
                $yearsMap[$year]['guides_count']++;
            }
        }

        return collect($yearsMap)->values();
    }

    /**
     * Carrega modelos relacionados
     */
    private function loadRelatedModels(): Collection
    {
        if (!$this->make) {
            return collect();
        }

        $modelRepo = app(\Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface::class);
        
        return $modelRepo->getByMake($this->make->id)
            ->where('id', '!=', $this->model->id)
            ->take(8);
    }

    /**
     * Constrói descrição do modelo
     */
    private function buildModelDescription(): string
    {
        $make = $this->make->name ?? '';
        $model = $this->model->name ?? '';
        $stats = $this->getStats();

        return "Acesse guias técnicos completos do {$make} {$model}. "
            . "Informações sobre óleo, pneus, calibragem, bateria, fluidos e manutenção. "
            . "{$stats['total_guides']} guias disponíveis.";
    }

    /**
     * Formata range de anos
     */
    private function formatYearsRange(?int $start, ?int $end): string
    {
        if (!$start || !$end) {
            return 'N/A';
        }

        if ($start === $end) {
            return (string) $start;
        }

        return "{$start}–{$end}";
    }

    /**
     * Constrói URL do ano
     */
    private function buildYearUrl(int $year): string
    {
        // URL genérica para ano (primeira categoria disponível)
        $firstCategory = $this->categoriesWithGuides->where('has_guides', true)->first();
        
        if (!$firstCategory) {
            return '#';
        }

        return route('guide.year', [
            'category' => $firstCategory['slug'],
            'make' => $this->make->slug,
            'model' => $this->model->slug,
            'year' => $year,
        ]);
    }

    /**
     * Retorna ícone SVG da categoria
     */
    private function getCategoryIconSvg(string $slug): string
    {
        $icons = [
            'oleo' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />',
            'pneus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />',
            'calibragem' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
        ];

        return $icons[$slug] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />';
    }

    /**
     * Retorna cor de fundo do ícone
     */
    private function getCategoryBgColor(string $slug): string
    {
        $colors = [
            'oleo' => 'bg-blue-100',
            'pneus' => 'bg-purple-100',
            'calibragem' => 'bg-green-100',
        ];

        return $colors[$slug] ?? 'bg-gray-100';
    }

    /**
     * Retorna cor do texto do ícone
     */
    private function getCategoryTextColor(string $slug): string
    {
        $colors = [
            'oleo' => 'text-blue-600',
            'pneus' => 'text-purple-600',
            'calibragem' => 'text-green-600',
        ];

        return $colors[$slug] ?? 'text-gray-600';
    }

    /**
     * Constrói meta description
     */
    private function buildMetaDescription(): string
    {
        $make = $this->getMake();
        $model = $this->getModel();
        $stats = $this->getStats();

        return "Guias técnicos completos do {$make['name']} {$model['name']}. "
            . "Óleo, pneus, calibragem, bateria, fluidos e manutenção. "
            . "{$stats['total_guides']} guias em {$stats['categories_with_guides']} categorias.";
    }

    /**
     * Constrói keywords
     */
    private function buildKeywords(): string
    {
        $make = $this->getMake();
        $model = $this->getModel();

        $keywords = [
            "{$make['name']} {$model['name']} guia",
            "{$make['name']} {$model['name']} manual",
            "{$make['name']} {$model['name']} especificações",
            "{$make['name']} {$model['name']} óleo",
            "{$make['name']} {$model['name']} pneus",
            "{$model['name']} manutenção",
        ];

        return implode(', ', $keywords);
    }

    /**
     * Constrói canonical URL
     */
    private function buildCanonicalUrl(): string
    {
        return route('guide.make.model', [
            'make' => $this->make->slug ?? '',
            'model' => $this->model->slug ?? '',
        ]);
    }

    /**
     * Constrói OG Image
     */
    private function buildOgImage(): string
    {
        $makeSlug = $this->make->slug ?? 'default';
        $modelSlug = $this->model->slug ?? 'default';
        
        return asset("images/og/{$makeSlug}-{$modelSlug}.jpg");
    }

    /**
     * Breadcrumb em formato Schema.org
     */
    private function getBreadcrumbStructuredData(): array
    {
        $breadcrumbs = $this->getBreadcrumbs();
        
        $itemList = [];
        foreach ($breadcrumbs as $index => $crumb) {
            if (!is_array($crumb)) {
                continue;
            }
            
            $name = $crumb['name'] ?? '';
            $url = $crumb['url'] ?? '';
            
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $name,
                'item' => $url,
            ];
        }
        
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemList,
        ];
    }

    /**
     * Lista de categorias em formato Schema.org
     */
    private function buildItemListStructuredData(): array
    {
        return $this->categoriesWithGuides
            ->where('has_guides', true)
            ->values()
            ->map(function ($category, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $category['name'],
                    'url' => $category['url'],
                ];
            })
            ->toArray();
    }
}
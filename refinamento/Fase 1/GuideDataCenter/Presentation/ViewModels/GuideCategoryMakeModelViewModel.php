<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * GuideCategoryMakeModelViewModel
 * 
 * Responsabilidade: Preparar dados para listagem de anos disponíveis
 * Rota: GET /guias/{category}/{make}/{model}
 * Exemplo: /guias/oleo/toyota/corolla
 * 
 * ✅ VERSÃO CORRIGIDA - Inclui 'engine' e 'versions' para view
 * 
 * @package Src\GuideDataCenter\Presentation\ViewModels
 */
class GuideCategoryMakeModelViewModel
{
    private ?GuideCategory $category;
    private ?VehicleMake $make;
    private ?VehicleModel $model;
    private Collection $guides;
    private Collection $availableYears;
    private Collection $complementaryCategories;

    public function __construct(
        ?GuideCategory $category,
        ?VehicleMake $make,
        ?VehicleModel $model,
        Collection $guides
    ) {
        $this->category = $category;
        $this->make = $make;
        $this->model = $model;
        $this->guides = $guides;
        
        // Processar anos disponíveis
        $this->availableYears = $this->extractAvailableYears();
        
        // Buscar categorias complementares
        $this->complementaryCategories = $this->loadComplementaryCategories();
    }

    // ============================================================
    // GETTERS PÚBLICOS
    // ============================================================

    /**
     * Retorna dados da categoria
     */
    public function getCategory(): array
    {
        if (!$this->category) {
            return [
                'id' => '',
                'name' => 'Categoria',
                'slug' => '',
                'description' => '',
            ];
        }

        return [
            'id' => (string) $this->category->_id,
            'name' => $this->category->name,
            'slug' => $this->category->slug,
            'description' => $this->category->description ?? '',
            'icon' => $this->category->icon ?? '',
        ];
    }

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
            ];
        }

        return [
            'id' => $this->make->id,
            'name' => $this->make->name,
            'slug' => $this->make->slug,
            'logo' => $this->make->logo_url ?? '',
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
            ];
        }

        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'slug' => $this->model->slug,
            'full_name' => "{$this->make->name} {$this->model->name}",
        ];
    }

    /**
     * ✅ CORRIGIDO: Retorna anos disponíveis com 'engine' e 'versions'
     */
    public function getAvailableYears(): array
    {
        return $this->availableYears
            ->sortByDesc('year')
            ->values()
            ->map(function ($yearData) {
                // Buscar motores únicos deste ano
                $engines = $this->getEnginesForYear($yearData['year']);
                
                return [
                    'year' => $yearData['year'],
                    'versions_count' => $yearData['versions_count'],
                    'versions' => $yearData['versions_count'], // ✅ Para view
                    'engine' => $engines, // ✅ Para view
                    'has_guides' => $yearData['versions_count'] > 0,
                    'url' => $this->buildYearUrl($yearData['year']),
                ];
            })
            ->toArray();
    }

    /**
     * Retorna categorias complementares
     */
    public function getComplementaryCategories(): array
    {
        $currentCategoryId = $this->category ? (string) $this->category->_id : '';
        
        return $this->complementaryCategories
            ->filter(fn($cat) => (string) $cat->_id !== $currentCategoryId)
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon ?? '',
                    'url' => route('guide.category.make.model', [
                        'category' => $category->slug,
                        'make' => $this->make->slug ?? '',
                        'model' => $this->model->slug ?? '',
                    ]),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Retorna estatísticas
     */
    public function getStats(): array
    {
        return [
            'total_years' => $this->availableYears->count(),
            'total_versions' => $this->availableYears->sum('versions_count'),
            'oldest_year' => $this->availableYears->min('year'),
            'newest_year' => $this->availableYears->max('year'),
            'category' => $this->category->name ?? '',
            'vehicle' => $this->getModel()['full_name'] ?? '',
        ];
    }

    /**
     * ✅ DADOS DE SEO OTIMIZADOS (CHAVES PADRONIZADAS)
     */
    public function getSeoData(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();

        return [
            // ✅ CHAVES PADRONIZADAS
            'title' => "{$category['name']} {$make['name']} {$model['name']} | Mercado Veículos",
            'h1' => "{$category['name']} para {$make['name']} {$model['name']}",
            'description' => $this->buildMetaDescription(),
            'keywords' => $this->buildKeywords(),
            'canonical' => $this->buildCanonicalUrl(),
            'robots' => 'index,follow',
            
            // ✅ Open Graph (chaves diretas)
            'og_title' => "{$category['name']} {$make['name']} {$model['name']}",
            'og_description' => $this->buildMetaDescription(),
            'og_image' => $this->buildOgImage(),
            'og_url' => $this->buildCanonicalUrl(),
            'og_type' => 'website',
            'og_site_name' => 'Mercado Veículos',
            'og_locale' => 'pt_BR',
            
            // ✅ Twitter Cards (chaves diretas)
            'twitter_card' => 'summary_large_image',
            'twitter_title' => "{$category['name']} {$make['name']} {$model['name']}",
            'twitter_description' => $this->buildMetaDescription(),
            'twitter_image' => $this->buildOgImage(),
        ];
    }

    /**
     * ✅ BREADCRUMBS
     */
    public function getBreadcrumbs(): array
    {
        $category = $this->getCategory();
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
                'name' => $category['name'],
                'url' => route('guide.category', ['category' => $category['slug']]),
            ],
            [
                'name' => $make['name'],
                'url' => route('guide.category.make', [
                    'category' => $category['slug'],
                    'make' => $make['slug'],
                ]),
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
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            
            'name' => $seo['h1'],
            'description' => $seo['description'],
            'url' => $seo['canonical'],
            
            'about' => [
                '@type' => 'Car',
                'name' => "{$make['name']} {$model['name']}",
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $make['name'],
                ],
                'model' => $model['name'],
            ],
            
            'breadcrumb' => $this->getBreadcrumbStructuredData(),
            
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $this->availableYears->count(),
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
     * Extrai anos disponíveis dos guias
     */
    private function extractAvailableYears(): Collection
    {
        $yearsMap = [];

        foreach ($this->guides as $guide) {
            $yearStart = $guide->year_start;
            $yearEnd = $guide->year_end ?? $yearStart;

            if (!$yearStart) {
                continue;
            }

            // Expandir range de anos
            for ($year = $yearStart; $year <= $yearEnd; $year++) {
                if (!isset($yearsMap[$year])) {
                    $yearsMap[$year] = [
                        'year' => $year,
                        'versions_count' => 0,
                    ];
                }
                $yearsMap[$year]['versions_count']++;
            }
        }

        return collect($yearsMap)->values();
    }

    /**
     * ✅ NOVO: Busca motores disponíveis para um ano específico
     */
    private function getEnginesForYear(int $year): string
    {
        $engines = $this->guides
            ->filter(function ($guide) use ($year) {
                return $guide->year_start <= $year && $guide->year_end >= $year;
            })
            ->pluck('motor')
            ->filter()
            ->unique()
            ->values();
        
        if ($engines->isEmpty()) {
            return 'Vários';
        }
        
        if ($engines->count() === 1) {
            return $engines->first();
        }
        
        // Se tem múltiplos motores
        if ($engines->count() <= 3) {
            return $engines->implode(', ');
        }
        
        return 'Vários';
    }

    /**
     * Carrega categorias complementares
     */
    private function loadComplementaryCategories(): Collection
    {
        $categoryRepo = app(\Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface::class);
        return $categoryRepo->getActive();
    }

    /**
     * Constrói URL do ano
     */
    private function buildYearUrl(int $year): string
    {
        return route('guide.year', [
            'category' => $this->category->slug ?? '',
            'make' => $this->make->slug ?? '',
            'model' => $this->model->slug ?? '',
            'year' => $year,
        ]);
    }

    /**
     * Constrói meta description
     */
    private function buildMetaDescription(): string
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();
        $stats = $this->getStats();

        $yearsText = $stats['oldest_year'] && $stats['newest_year']
            ? "({$stats['oldest_year']} a {$stats['newest_year']})"
            : '';

        return "Guia completo de {$category['name']} para {$make['name']} {$model['name']} {$yearsText}. "
            . "Escolha o ano do seu veículo para ver especificações técnicas detalhadas. "
            . "{$stats['total_years']} anos disponíveis com {$stats['total_versions']} versões.";
    }

    /**
     * Constrói keywords
     */
    private function buildKeywords(): string
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();

        $keywords = [
            "{$category['name']} {$make['name']} {$model['name']}",
            "{$make['name']} {$model['name']} anos",
            "{$make['name']} {$model['name']} versões",
            "guia {$category['name']} {$make['name']}",
            "{$model['name']} especificações",
        ];

        return implode(', ', $keywords);
    }

    /**
     * Constrói canonical URL
     */
    private function buildCanonicalUrl(): string
    {
        return route('guide.category.make.model', [
            'category' => $this->category->slug ?? '',
            'make' => $this->make->slug ?? '',
            'model' => $this->model->slug ?? '',
        ]);
    }

    /**
     * Constrói OG Image
     */
    private function buildOgImage(): string
    {
        $make = $this->make->slug ?? 'default';
        $model = $this->model->slug ?? 'default';
        
        return asset("images/og/{$make}-{$model}.jpg");
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
     * Lista de anos em formato Schema.org
     */
    private function buildItemListStructuredData(): array
    {
        return collect($this->getAvailableYears())
            ->map(function ($yearData, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => (string) $yearData['year'],
                    'url' => $yearData['url'],
                ];
            })
            ->toArray();
    }
}
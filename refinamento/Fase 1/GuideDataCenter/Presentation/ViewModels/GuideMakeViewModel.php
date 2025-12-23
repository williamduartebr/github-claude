<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;

/**
 * GuideMakeViewModel
 * 
 * Responsabilidade: Preparar dados para listagem de modelos de uma marca
 * Rota: GET /guias/marca/{make}
 * Exemplo: /guias/marca/toyota
 * 
 * ✅ CHAVES DE SEO PADRONIZADAS
 * 
 * @package Src\GuideDataCenter\Presentation\ViewModels
 */
class GuideMakeViewModel
{
    private ?VehicleMake $make;
    private Collection $guides;
    private Collection $categories;
    private Collection $allModels;
    private Collection $popularModels;
    private Collection $categoriesWithCounts;

    public function __construct(
        ?VehicleMake $make,
        Collection $guides,
        Collection $categories
    ) {
        $this->make = $make;
        $this->guides = $guides;
        $this->categories = $categories;
        
        // Processar dados
        $modelsData = $this->extractModels();
        $this->allModels = $modelsData['all'];
        $this->popularModels = $modelsData['popular'];
        $this->categoriesWithCounts = $this->buildCategoriesWithCounts();
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
            ];
        }

        return [
            'id' => $this->make->id,
            'name' => $this->make->name,
            'slug' => $this->make->slug,
            'logo' => $this->make->logo ?? 'default.png',
        ];
    }

    /**
     * Retorna categorias com contagem de guias
     */
    public function getCategories(): array
    {
        return $this->categoriesWithCounts
            ->map(function ($categoryData) {
                return [
                    'name' => $categoryData['name'],
                    'slug' => $categoryData['slug'],
                    'guides_count' => $categoryData['guides_count'],
                    'url' => $categoryData['url'],
                    'icon_svg' => $this->getCategoryIconSvg($categoryData['slug']),
                    'icon_bg_color' => $this->getCategoryBgColor($categoryData['slug']),
                    'icon_text_color' => $this->getCategoryTextColor($categoryData['slug']),
                ];
            })
            ->toArray();
    }

    /**
     * Retorna modelos populares (top 8)
     */
    public function getPopularModels(): array
    {
        return $this->popularModels
            ->take(8)
            ->map(function ($modelData) {
                return [
                    'name' => $modelData['name'],
                    'slug' => $modelData['slug'],
                    'guides_count' => $modelData['guides_count'],
                    'url' => $modelData['url'],
                ];
            })
            ->toArray();
    }

    /**
     * Retorna todos os modelos
     */
    public function getAllModels(): array
    {
        return $this->allModels
            ->map(function ($modelData) {
                return [
                    'name' => $modelData['name'],
                    'slug' => $modelData['slug'],
                    'guides_count' => $modelData['guides_count'],
                    'url' => $modelData['url'],
                ];
            })
            ->toArray();
    }

    /**
     * Retorna estatísticas
     */
    public function getStats(): array
    {
        return [
            'total_guides' => $this->guides->count(),
            'total_models' => $this->allModels->count(),
            'total_categories' => $this->categoriesWithCounts->where('guides_count', '>', 0)->count(),
        ];
    }

    /**
     * ✅ DADOS DE SEO OTIMIZADOS (CHAVES PADRONIZADAS)
     */
    public function getSeoData(): array
    {
        $make = $this->getMake();
        $stats = $this->getStats();

        return [
            // ✅ CHAVES PADRONIZADAS
            'title' => "Guias Técnicos {$make['name']} | Mercado Veículos",
            'h1' => "Guias {$make['name']}",
            'description' => $this->buildMetaDescription(),
            'keywords' => $this->buildKeywords(),
            'canonical' => $this->buildCanonicalUrl(),
            'robots' => 'index,follow',
            
            // ✅ Open Graph (chaves diretas)
            'og_title' => "Guias Técnicos {$make['name']}",
            'og_description' => $this->buildMetaDescription(),
            'og_image' => $this->buildOgImage(),
            'og_url' => $this->buildCanonicalUrl(),
            'og_type' => 'website',
            'og_site_name' => 'Mercado Veículos',
            'og_locale' => 'pt_BR',
            
            // ✅ Twitter Cards (chaves diretas)
            'twitter_card' => 'summary_large_image',
            'twitter_title' => "Guias Técnicos {$make['name']}",
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
        $stats = $this->getStats();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            
            'name' => $seo['h1'],
            'description' => $seo['description'],
            'url' => $seo['canonical'],
            
            'about' => [
                '@type' => 'Brand',
                'name' => $make['name'],
            ],
            
            'breadcrumb' => $this->getBreadcrumbStructuredData(),
            
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $stats['total_models'],
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
     * Extrai modelos dos guias
     */
    private function extractModels(): array
    {
        $modelsMap = [];

        foreach ($this->guides as $guide) {
            $modelSlug = $guide->model_slug;
            $modelName = $guide->model;

            if (!isset($modelsMap[$modelSlug])) {
                $modelsMap[$modelSlug] = [
                    'slug' => $modelSlug,
                    'name' => $modelName,
                    'guides_count' => 0,
                    'url' => route('guide.make.model', [
                        'make' => $this->make->slug,
                        'model' => $modelSlug,
                    ]),
                ];
            }

            $modelsMap[$modelSlug]['guides_count']++;
        }

        $allModels = collect($modelsMap)->values()->sortBy('name');
        $popularModels = collect($modelsMap)->values()->sortByDesc('guides_count');

        return [
            'all' => $allModels,
            'popular' => $popularModels,
        ];
    }

    /**
     * Constrói categorias com contagem
     */
    private function buildCategoriesWithCounts(): Collection
    {
        return $this->categories->map(function ($category) {
            $guidesInCategory = $this->guides->where('category_slug', $category->slug);
            
            return [
                'name' => $category->name,
                'slug' => $category->slug,
                'guides_count' => $guidesInCategory->count(),
                'url' => route('guide.category.make', [
                    'category' => $category->slug,
                    'make' => $this->make->slug,
                ]),
            ];
        });
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
            'bateria' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
            'fluidos' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />',
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
            'bateria' => 'bg-yellow-100',
            'fluidos' => 'bg-red-100',
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
            'bateria' => 'text-yellow-600',
            'fluidos' => 'text-red-600',
        ];

        return $colors[$slug] ?? 'text-gray-600';
    }

    /**
     * Constrói meta description
     */
    private function buildMetaDescription(): string
    {
        $make = $this->getMake();
        $stats = $this->getStats();

        return "Guias técnicos completos para veículos {$make['name']}. "
            . "Especificações de óleo, pneus, calibragem, bateria, fluidos e manutenção. "
            . "{$stats['total_guides']} guias em {$stats['total_models']} modelos. "
            . "Informações baseadas em manuais oficiais.";
    }

    /**
     * Constrói keywords
     */
    private function buildKeywords(): string
    {
        $make = $this->getMake();

        $keywords = [
            "{$make['name']} guias",
            "{$make['name']} especificações",
            "{$make['name']} óleo motor",
            "{$make['name']} pneus",
            "{$make['name']} calibragem",
            "{$make['name']} manutenção",
            "manual {$make['name']}",
        ];

        return implode(', ', $keywords);
    }

    /**
     * Constrói canonical URL
     */
    private function buildCanonicalUrl(): string
    {
        return route('guide.make', [
            'make' => $this->make->slug ?? '',
        ]);
    }

    /**
     * Constrói OG Image
     */
    private function buildOgImage(): string
    {
        $makeSlug = $this->make->slug ?? 'default';
        return asset("images/og/{$makeSlug}.jpg");
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
     * Lista de modelos em formato Schema.org
     */
    private function buildItemListStructuredData(): array
    {
        return $this->allModels
            ->map(function ($modelData, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $modelData['name'],
                    'url' => $modelData['url'],
                ];
            })
            ->toArray();
    }
}
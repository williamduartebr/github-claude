<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * GuideYearViewModel
 * 
 * Responsabilidade: Preparar dados para listagem de versões de um ano específico
 * Rota: GET /guias/{category}/{make}/{model}/{year}
 * Exemplo: /guias/oleo/toyota/corolla/2024
 * 
 * ✅ VERSÃO CORRIGIDA - Chaves de SEO padronizadas
 * 
 * @package Src\GuideDataCenter\Presentation\ViewModels
 */
class GuideYearViewModel
{
    private ?GuideCategory $category;
    private ?VehicleMake $make;
    private ?VehicleModel $model;
    private string $year;
    private Collection $versions;
    private Collection $complementaryCategories;

    public function __construct(
        ?GuideCategory $category,
        ?VehicleMake $make,
        ?VehicleModel $model,
        string $year
    ) {
        $this->category = $category;
        $this->make = $make;
        $this->model = $model;
        $this->year = $year;
        
        // Buscar versões disponíveis para este ano
        $this->versions = $this->loadAvailableVersions();
        
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
     * Retorna ano
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * Retorna versões disponíveis formatadas
     */
    public function getVersions(): array
    {
        return $this->versions->map(function ($guide) {
            return [
                'slug' => $guide->version_slug ?? '',
                'version' => strtoupper($guide->version ?? ''), // ✅ CHAVE 'version' para view
                'name' => $guide->version ?? '',
                'full_name' => $this->buildVersionFullName($guide),
                'motor' => $guide->motor ?? '',
                'engine' => $guide->motor ?? '', // ✅ CHAVE 'engine' para view
                'fuel' => $guide->fuel ?? '',
                'transmission' => $guide->transmission ?? '',
                'url' => $this->buildVersionUrl($guide),
                'has_guide' => true,
            ];
        })->toArray();
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
                    'url' => route('guide.year', [
                        'category' => $category->slug,
                        'make' => $this->make->slug ?? '',
                        'model' => $this->model->slug ?? '',
                        'year' => $this->year,
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
            'total_versions' => $this->versions->count(),
            'year' => $this->year,
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
        $year = $this->year;

        return [
            // ✅ CHAVES PADRONIZADAS PARA AS VIEWS
            'title' => "{$category['name']} {$make['name']} {$model['name']} {$year} | Mercado Veículos",
            'h1' => "{$category['name']} para {$make['name']} {$model['name']} {$year}",
            'description' => $this->buildMetaDescription(), // ✅ 'description' não 'meta_description'
            'keywords' => $this->buildKeywords(),
            'canonical' => $this->buildCanonicalUrl(), // ✅ 'canonical' não 'canonical_url'
            'robots' => 'index,follow',
            
            // ✅ Open Graph (chaves diretas)
            'og_title' => "{$category['name']} {$make['name']} {$model['name']} {$year}",
            'og_description' => $this->buildMetaDescription(),
            'og_image' => $this->buildOgImage(),
            'og_url' => $this->buildCanonicalUrl(),
            'og_type' => 'website',
            'og_site_name' => 'Mercado Veículos',
            'og_locale' => 'pt_BR',
            
            // ✅ Twitter Cards (chaves diretas)
            'twitter_card' => 'summary_large_image',
            'twitter_title' => "{$category['name']} {$make['name']} {$model['name']} {$year}",
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
                'url' => route('guide.category.make.model', [
                    'category' => $category['slug'],
                    'make' => $make['slug'],
                    'model' => $model['slug'],
                ]),
            ],
            [
                'name' => $this->year,
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
            'description' => $seo['description'], // ✅ CORRIGIDO
            'url' => $seo['canonical'], // ✅ CORRIGIDO
            
            'about' => [
                '@type' => 'Car',
                'name' => "{$make['name']} {$model['name']} {$this->year}",
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $make['name'],
                ],
                'model' => $model['name'],
                'productionDate' => $this->year,
            ],
            
            'breadcrumb' => $this->getBreadcrumbStructuredData(),
            
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $this->versions->count(),
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
     * Carrega versões disponíveis do MongoDB
     */
    private function loadAvailableVersions(): Collection
    {
        if (!$this->category || !$this->make || !$this->model) {
            return collect();
        }

        $guideModel = app(Guide::class);
        
        return $guideModel::where('category_slug', $this->category->slug)
            ->where('make_slug', $this->make->slug)
            ->where('model_slug', $this->model->slug)
            ->where('year_start', '<=', (int) $this->year)
            ->where('year_end', '>=', (int) $this->year)
            ->get();
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
     * Constrói nome completo da versão
     */
    private function buildVersionFullName($guide): string
    {
        $parts = [];
        
        if (isset($guide->version)) {
            $parts[] = strtoupper($guide->version);
        }
        
        if (isset($guide->motor)) {
            $parts[] = $guide->motor;
        }
        
        if (isset($guide->fuel)) {
            $parts[] = $guide->fuel;
        }
        
        return implode(' ', $parts);
    }

    /**
     * Constrói URL da versão
     */
    private function buildVersionUrl($guide): string
    {
        return route('guide.version', [
            'category' => $this->category->slug ?? '',
            'make' => $this->make->slug ?? '',
            'model' => $this->model->slug ?? '',
            'year' => $this->year,
            'version' => $guide->version_slug ?? $guide->slug ?? '',
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
        $versionsCount = $this->versions->count();

        return "Escolha a versão do {$make['name']} {$model['name']} {$this->year} "
            . "para ver o guia completo de {$category['name']}. "
            . "{$versionsCount} versões disponíveis com especificações técnicas detalhadas.";
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
            "{$category['name']} {$make['name']} {$model['name']} {$this->year}",
            "{$make['name']} {$model['name']} {$this->year} versões",
            "{$make['name']} {$model['name']} {$this->year} especificações",
            "guia {$category['name']} {$this->year}",
        ];

        return implode(', ', $keywords);
    }

    /**
     * Constrói canonical URL
     */
    private function buildCanonicalUrl(): string
    {
        return route('guide.year', [
            'category' => $this->category->slug ?? '',
            'make' => $this->make->slug ?? '',
            'model' => $this->model->slug ?? '',
            'year' => $this->year,
        ]);
    }

    /**
     * Constrói OG Image
     */
    private function buildOgImage(): string
    {
        $make = $this->make->slug ?? 'default';
        $model = $this->model->slug ?? 'default';
        
        // TODO: Implementar geração dinâmica de imagens
        return asset("images/og/{$make}-{$model}-{$this->year}.jpg");
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
     * Lista de versões em formato Schema.org
     */
    private function buildItemListStructuredData(): array
    {
        return collect($this->getVersions())
            ->map(function ($version, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $version['full_name'],
                    'url' => $version['url'],
                ];
            })
            ->toArray();
    }
}
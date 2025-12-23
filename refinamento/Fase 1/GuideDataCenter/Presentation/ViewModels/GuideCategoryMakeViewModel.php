<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * GuideCategoryMakeViewModel
 * 
 * Responsabilidade: Preparar dados para listagem de modelos de uma marca
 * Rota: GET /guias/{category}/{make}
 * Exemplo: /guias/oleo/toyota
 * 
 * ✅ CHAVES DE SEO PADRONIZADAS
 * 
 * @package Src\GuideDataCenter\Presentation\ViewModels
 */
class GuideCategoryMakeViewModel
{
    private ?GuideCategory $category;
    private ?VehicleMake $make;
    private Collection $guides;
    private Collection $popularModels;
    private Collection $allModels;
    private Collection $complementaryCategories;

    public function __construct(
        ?GuideCategory $category,
        ?VehicleMake $make,
        Collection $guides
    ) {
        $this->category = $category;
        $this->make = $make;
        $this->guides = $guides;
        
        // Processar modelos
        $modelsData = $this->extractModels();
        $this->allModels = $modelsData['all'];
        $this->popularModels = $modelsData['popular'];
        
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
                'logo' => '',
            ];
        }

        return [
            'id' => $this->make->id,
            'name' => $this->make->name,
            'slug' => $this->make->slug,
            'logo' => $this->make->logo_url ?? asset('images/logos/default.png'),
        ];
    }

    /**
     * Retorna modelos populares formatados
     */
    public function getPopularModels(): array
    {
        return $this->popularModels
            ->take(6)
            ->map(function ($modelData) {
                return [
                    'name' => $modelData['name'],
                    'slug' => $modelData['slug'],
                    'url' => $modelData['url'],
                    'image' => $this->buildModelImage($modelData['slug']),
                    'description' => "Guias de {$this->category->name} para {$this->make->name} {$modelData['name']}",
                ];
            })
            ->toArray();
    }

    /**
     * Retorna todos os modelos formatados
     */
    public function getAllModels(): array
    {
        return $this->allModels
            ->map(function ($modelData) {
                return [
                    'name' => $modelData['name'],
                    'slug' => $modelData['slug'],
                    'segment' => $this->getSegmentName($modelData['slug']),
                    'years' => $this->formatYearRange($modelData['year_start'], $modelData['year_end']),
                    'versions_count' => $modelData['versions_count'],
                    'url' => $modelData['url'],
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
                    'url' => route('guide.category.make', [
                        'category' => $category->slug,
                        'make' => $this->make->slug ?? '',
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
            'total_models' => $this->allModels->count(),
            'total_versions' => $this->allModels->sum('versions_count'),
            'category' => $this->category->name ?? '',
            'make' => $this->make->name ?? '',
        ];
    }

    /**
     * ✅ DADOS DE SEO OTIMIZADOS (CHAVES PADRONIZADAS)
     */
    public function getSeoData(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();

        return [
            // ✅ CHAVES PADRONIZADAS
            'title' => "{$category['name']} {$make['name']} | Mercado Veículos",
            'h1' => "{$category['name']} – {$make['name']}",
            'description' => $this->buildMetaDescription(),
            'keywords' => $this->buildKeywords(),
            'canonical' => $this->buildCanonicalUrl(),
            'robots' => 'index,follow',
            
            // ✅ Open Graph (chaves diretas)
            'og_title' => "{$category['name']} {$make['name']}",
            'og_description' => $this->buildMetaDescription(),
            'og_image' => $this->buildOgImage(),
            'og_url' => $this->buildCanonicalUrl(),
            'og_type' => 'website',
            'og_site_name' => 'Mercado Veículos',
            'og_locale' => 'pt_BR',
            
            // ✅ Twitter Cards (chaves diretas)
            'twitter_card' => 'summary_large_image',
            'twitter_title' => "{$category['name']} {$make['name']}",
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
                'url' => null, // Página atual
            ],
        ];
    }

    /**
     * ✅ STRUCTURED DATA (Schema.org)
     * 
     * Tipo: CollectionPage com ItemList de modelos
     */
    public function getStructuredData(): array
    {
        $seo = $this->getSeoData();
        $category = $this->getCategory();
        $make = $this->getMake();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            
            'name' => $seo['h1'],
            'description' => $seo['description'],
            'url' => $seo['canonical'],
            
            // Sobre o que é a página (a marca)
            'about' => [
                '@type' => 'Brand',
                'name' => $make['name'],
            ],
            
            // Breadcrumb
            'breadcrumb' => $this->getBreadcrumbStructuredData(),
            
            // Lista de modelos
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $this->allModels->count(),
                'itemListElement' => $this->buildItemListStructuredData(),
            ],
            
            // Publicador
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
                    'versions_count' => 0,
                    'year_start' => $guide->year_start ?? null,
                    'year_end' => $guide->year_end ?? null,
                    'url' => route('guide.category.make.model', [
                        'category' => $this->category->slug ?? '',
                        'make' => $this->make->slug ?? '',
                        'model' => $modelSlug,
                    ]),
                ];
            }

            // Atualizar contagem
            $modelsMap[$modelSlug]['versions_count']++;

            // Atualizar range de anos
            if ($guide->year_start && (!$modelsMap[$modelSlug]['year_start'] || $guide->year_start < $modelsMap[$modelSlug]['year_start'])) {
                $modelsMap[$modelSlug]['year_start'] = $guide->year_start;
            }
            if ($guide->year_end && (!$modelsMap[$modelSlug]['year_end'] || $guide->year_end > $modelsMap[$modelSlug]['year_end'])) {
                $modelsMap[$modelSlug]['year_end'] = $guide->year_end;
            }
        }

        $allModels = collect($modelsMap)->values();
        
        // Modelos populares = os que têm mais versões
        $popularModels = $allModels->sortByDesc('versions_count');

        return [
            'all' => $allModels->sortBy('name'),
            'popular' => $popularModels,
        ];
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
     * Formata range de anos
     */
    private function formatYearRange(?int $start, ?int $end): string
    {
        if (!$start) {
            return 'N/A';
        }

        if (!$end || $start === $end) {
            return (string) $start;
        }

        return "{$start}–{$end}";
    }

    /**
     * Retorna nome do segmento (mock - deve vir do banco)
     */
    private function getSegmentName(string $modelSlug): string
    {
        // TODO: Implementar lookup real no banco
        $segments = [
            'corolla' => 'Sedã',
            'hilux' => 'Picape',
            'yaris' => 'Hatch',
            'civic' => 'Sedã',
            'fit' => 'Hatch',
            'hr-v' => 'SUV',
            'gol' => 'Hatch',
            'polo' => 'Sedã',
            't-cross' => 'SUV',
        ];

        return $segments[$modelSlug] ?? 'Veículo';
    }

    /**
     * Constrói imagem do modelo
     */
    private function buildModelImage(string $modelSlug): string
    {
        $makeSlug = $this->make->slug ?? 'default';
        return asset("images/vehicles/{$makeSlug}-{$modelSlug}.jpg");
    }

    /**
     * Constrói meta description
     */
    private function buildMetaDescription(): string
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $stats = $this->getStats();

        return "Guias completos de {$category['name']} para todos os modelos {$make['name']}. "
            . "Escolha seu modelo e acesse especificações técnicas, recomendações e informações detalhadas. "
            . "{$stats['total_models']} modelos disponíveis.";
    }

    /**
     * Constrói keywords
     */
    private function buildKeywords(): string
    {
        $category = $this->getCategory();
        $make = $this->getMake();

        $keywords = [
            "{$category['name']} {$make['name']}",
            "{$make['name']} modelos",
            "guia {$category['name']} {$make['name']}",
            "{$make['name']} especificações",
            "{$category['name']} carros {$make['name']}",
        ];

        return implode(', ', $keywords);
    }

    /**
     * Constrói canonical URL
     */
    private function buildCanonicalUrl(): string
    {
        return route('guide.category.make', [
            'category' => $this->category->slug ?? '',
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
        return collect($this->getAllModels())
            ->map(function ($model, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $model['name'],
                    'url' => $model['url'],
                ];
            })
            ->toArray();
    }
}
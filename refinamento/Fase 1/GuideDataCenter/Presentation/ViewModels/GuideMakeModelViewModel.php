<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;

/**
 * ViewModel para página de Marca + Modelo
 * 
 * Rota: /guias/marca/{make}/{model}
 * View: guide-data-center::guide.make-model
 * Exemplo: /guias/marca/honda/civic
 * 
 * ✅ VERSÃO CORRIGIDA: Fix filter() on array error
 */
class GuideMakeModelViewModel
{
    private $make;
    private $model;
    private Collection $categories;
    private Collection $guides;
    private Collection $versions;

    public function __construct(
        $make,
        $model,
        Collection $categories,
        Collection $guides,
        Collection $versions
    ) {
        $this->make = $make;
        $this->model = $model;
        $this->categories = $categories;
        $this->guides = $guides;
        $this->versions = $versions;
    }

    /**
     * Retorna dados da marca
     */
    public function getMake(): array
    {
        return [
            'id' => $this->make->id,
            'name' => $this->make->name,
            'slug' => $this->make->slug,
            'logo' => $this->make->logo_url ?? '/images/brands/default-logo.svg',
            'url' => route('guide.make', ['make' => $this->make->slug]),
        ];
    }

    /**
     * Retorna dados do modelo
     */
    public function getModel(): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'slug' => $this->model->slug,
            'year_start' => $this->model->year_start ?? 'N/A',
            'year_end' => $this->model->year_end ?? 'Presente',
            'category' => ucfirst($this->model->category ?? 'sedan'),
            'full_name' => "{$this->make->name} {$this->model->name}",
            'description' => $this->buildDescription(),
        ];
    }

    /**
     * Retorna categorias COM guias disponíveis para este modelo
     * Prioriza categorias que têm guias cadastrados
     */
    public function getCategoriesWithGuides(): array
    {
        $categoriesArray = [];

        foreach ($this->categories as $category) {
            // Contar guias desta categoria para este modelo
            $guidesCount = $this->guides->filter(function ($guide) use ($category) {
                return $guide->guide_category_id === $category->_id;
            })->count();

            // Pegar o guia mais recente desta categoria (se houver)
            $latestGuide = $this->guides
                ->filter(fn($g) => $g->guide_category_id === $category->_id)
                ->sortByDesc('year_end')
                ->first();

            $categoriesArray[] = [
                'id' => $category->_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description ?? '',
                'icon_svg' => $category->icon_svg ?? '',
                'icon_bg_color' => $category->icon_bg_color ?? 'bg-blue-100',
                'icon_text_color' => $category->icon_text_color ?? 'text-blue-600',
                'guides_count' => $guidesCount,
                'has_guides' => $guidesCount > 0,
                'url' => route('guide.category.make.model', [
                    'category' => $category->slug,
                    'make' => $this->make->slug,
                    'model' => $this->model->slug,
                ]),
                'latest_year' => $latestGuide ? $latestGuide->year_end ?? $latestGuide->year_start : null,
            ];
        }

        // Ordenar: categorias com guias primeiro
        return collect($categoriesArray)
            ->sortByDesc('guides_count')
            ->values()
            ->toArray();
    }

    /**
     * Retorna TODAS as categorias (mesmo sem guias)
     */
    public function getAllCategories(): array
    {
        return $this->categories->map(function ($category) {
            return [
                'id' => $category->_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon_svg' => $category->icon_svg ?? '',
                'icon_bg_color' => $category->icon_bg_color ?? 'bg-blue-100',
                'icon_text_color' => $category->icon_text_color ?? 'text-blue-600',
                'url' => route('guide.category.make.model', [
                    'category' => $category->slug,
                    'make' => $this->make->slug,
                    'model' => $this->model->slug,
                ]),
            ];
        })->toArray();
    }

    /**
     * Retorna lista de anos disponíveis para este modelo
     */
    public function getYearsList(): array
    {
        if ($this->versions->isEmpty()) {
            // Se não houver versões, usar year_start/year_end do modelo
            return $this->buildYearsFromModel();
        }

        // Pegar anos únicos das versões - DO MAIS RECENTE PARA O MAIS ANTIGO
        $years = $this->versions->pluck('year')->unique()->sortDesc()->values();

        return $years->map(function ($year) {
            $guidesCount = $this->guides->filter(function ($guide) use ($year) {
                return $year >= ($guide->year_start ?? 0)
                    && $year <= ($guide->year_end ?? 9999);
            })->count();

            return [
                'year' => $year,
                'guides_count' => $guidesCount,
                'has_guides' => $guidesCount > 0,
                'url' => route('vehicles.year', [
                    'make' => $this->make->slug,
                    'model' => $this->model->slug,
                    'year' => $year,
                ]),
            ];
        })->toArray();
    }

    /**
     * Retorna estatísticas
     */
    public function getStats(): array
    {
        $yearsRange = $this->getYearsRange();

        return [
            'total_guides' => $this->guides->count(),
            'total_categories' => $this->categories->count(),
            'categories_with_guides' => $this->guides->pluck('guide_category_id')->unique()->count(),
            'total_versions' => $this->versions->count(),
            'years_range' => $yearsRange,
            'oldest_year' => $this->model->year_start ?? $this->versions->min('year'),
            'newest_year' => $this->model->year_end ?? $this->versions->max('year') ?? 'Presente',
        ];
    }

    /**
     * Retorna modelos relacionados (mesma marca)
     */
    public function getRelatedModels(): array
    {
        // TODO: Implementar busca real de modelos relacionados
        // Por enquanto retorna array vazio
        return [];
    }

    /**
     * Retorna dados para SEO
     */
    public function getSeoData(): array
    {
        $fullName = $this->getModel()['full_name'];

        return [
            'title' => "Guias Técnicos {$fullName} - Óleo, Pneus, Manutenção | Mercado Veículos",
            'description' => "Guias técnicos completos para {$fullName}: especificações de óleo, pneus, calibragem, bateria, fluidos, consumo e manutenção. Informações para todos os anos e versões.",
            'canonical' => route('guide.make.model', [
                'make' => $this->make->slug,
                'model' => $this->model->slug,
            ]),
            'og_image' => $this->buildOgImage(),
            'keywords' => $this->buildKeywords(),
        ];
    }

    /**
     * Retorna breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $this->make->name, 'url' => route('guide.make', ['make' => $this->make->slug])],
            ['name' => $this->model->name, 'url' => null],
        ];
    }

    /**
     * Retorna structured data Schema.org
     */
    public function getStructuredData(): array
    {
        $fullName = $this->getModel()['full_name'];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => "Guias Técnicos {$fullName}",
            'description' => $this->getSeoData()['description'],
            'url' => $this->getSeoData()['canonical'],
            'breadcrumb' => $this->buildBreadcrumbStructuredData(),
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $this->guides->count(),
                'itemListElement' => $this->buildItemListStructuredData(),
            ],
        ];
    }

    // ============================================================
    // MÉTODOS PRIVADOS
    // ============================================================

    private function buildDescription(): string
    {
        $fullName = "{$this->make->name} {$this->model->name}";
        $yearsRange = $this->getYearsRange();

        return "Guias técnicos completos para {$fullName} ({$yearsRange}). "
            . "Especificações de óleo, pneus, calibragem, bateria, fluidos, consumo e manutenção preventiva. "
            . "Informações oficiais baseadas em manuais do fabricante.";
    }

    private function getYearsRange(): string
    {
        $start = $this->model->year_start ?? $this->versions->min('year') ?? 'N/A';
        $end = $this->model->year_end ?? $this->versions->max('year') ?? 'Presente';

        if ($start === 'N/A') {
            return 'Todos os anos';
        }

        return $start === $end ? (string)$start : "{$start} - {$end}";
    }

    private function buildYearsFromModel(): array
    {
        $start = $this->model->year_start;
        $end = $this->model->year_end ?? date('Y');

        if (!$start) {
            return [];
        }

        $years = [];
        for ($year = $start; $year <= $end; $year++) {
            $years[] = [
                'year' => $year,
                'guides_count' => 0,
                'has_guides' => false,
                'url' => '#',
            ];
        }

        return $years;
    }

    private function buildOgImage(): string
    {
        $makeSlug = $this->make->slug;
        $modelSlug = $this->model->slug;

        // TODO: Implementar geração dinâmica de imagens OG
        return url("/images/og/{$makeSlug}-{$modelSlug}.jpg");
    }

    private function buildKeywords(): string
    {
        $fullName = $this->getModel()['full_name'];

        return implode(', ', [
            "{$fullName} guia",
            "{$fullName} óleo",
            "{$fullName} pneus",
            "{$fullName} manutenção",
            "{$fullName} especificações",
            "{$this->make->name} {$this->model->name}",
        ]);
    }

    private function buildBreadcrumbStructuredData(): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($this->getBreadcrumbs())
                ->filter(fn($crumb) => $crumb['url'] !== null)
                ->values()
                ->map(function ($crumb, $index) {
                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $crumb['name'],
                        'item' => $crumb['url'],
                    ];
                })
                ->toArray(),
        ];
    }

    /**
     * ✅ CORRIGIDO: Usar collect() antes de filter()
     */
    private function buildItemListStructuredData(): array
    {
        return collect($this->getCategoriesWithGuides())
            ->filter(fn($cat) => $cat['has_guides'])
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

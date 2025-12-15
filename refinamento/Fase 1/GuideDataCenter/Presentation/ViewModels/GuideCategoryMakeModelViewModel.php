<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Mongo\Guide;

/**
 * ViewModel para página de categoria + marca + modelo
 * Rota: /guias/{category}/{make}/{model}
 * Exemplo: /guias/oleo/toyota/corolla
 */
class GuideCategoryMakeModelViewModel
{
    private $category;
    private $make;
    private $model;
    private Collection $guides;

    public function __construct($category, $make, $model, Collection $guides)
    {
        $this->category = $category;
        $this->make = $make;
        $this->model = $model;
        $this->guides = $guides;
    }

    public function getCategory(): array
    {
        return [
            'name' => $this->category->name ?? null,
            'slug' => $this->category->slug ?? null,
            'icon' => $this->category->icon ?? '🛢️',
        ];
    }

    public function getMake(): array
    {
        return [
            'name' => $this->make->name ?? null,
            'slug' => $this->make->slug ?? null,
            'logo' => "/images/makes/{$this->make->slug}.svg",
        ];
    }

    public function getModel(): array
    {
        return [
            'name' => $this->model->name ?? null,
            'slug' => $this->model->slug ?? null,
        ];
    }

    /**
     * Retorna lista de anos disponíveis (DADOS REAIS DO MONGODB)
     */
    public function getAvailableYears(): array
    {
        if ($this->guides->isEmpty()) {
            return [];
        }

        $categorySlug = $this->category->slug;
        $makeSlug = $this->make->slug;
        $modelSlug = $this->model->slug;

        return $this->guides
            ->filter(fn($guide) => isset($guide->year_start) && is_numeric($guide->year_start))
            ->groupBy('year_start')
            ->map(function($guidesForYear) use ($categorySlug, $makeSlug, $modelSlug) {
                $firstGuide = $guidesForYear->first();
                $year = (int) $firstGuide->year_start;
                
                $engines = $guidesForYear->pluck('payload.engine_specs.engine_name')
                    ->filter()
                    ->unique()
                    ->implode(' / ');
                
                $versions = $guidesForYear->pluck('version')
                    ->filter()
                    ->unique()
                    ->implode(', ');

                return [
                    'year' => $year,
                    'engine' => $engines ?: 'Motor não especificado',
                    'versions' => $versions ?: 'Versões disponíveis',
                    'guides_count' => $guidesForYear->count(),
                    'url' => route('guide.year', [
                        'category' => $categorySlug,
                        'make' => $makeSlug,
                        'model' => $modelSlug,
                        'year' => $year
                    ]),
                ];
            })
            ->sortByDesc('year')
            ->values()
            ->toArray();
    }

    public function getStats(): array
    {
        $years = $this->getAvailableYears();
        $totalGuides = $this->guides->count();

        if (empty($years)) {
            return [
                'total_years' => 0,
                'total_guides' => 0,
                'oldest_year' => null,
                'newest_year' => null,
            ];
        }

        return [
            'total_years' => count($years),
            'total_guides' => $totalGuides,
            'oldest_year' => end($years)['year'],
            'newest_year' => $years[0]['year'],
        ];
    }

    /**
     * Busca categorias complementares que têm guias para este modelo
     */
    public function getComplementaryCategories(): array
    {
        $currentCategorySlug = $this->category->slug;
        $makeSlug = $this->make->slug;
        $modelSlug = $this->model->slug;

        $guideModel = app(Guide::class);
        
        $otherCategories = $guideModel::query()
            ->where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('category_slug', '!=', $currentCategorySlug)
            ->get()
            ->groupBy('category_slug')
            ->map(function($guides, $catSlug) use ($makeSlug, $modelSlug, $currentCategorySlug) {
                $firstGuide = $guides->first();
                
                return [
                    'name' => $firstGuide->category ?? ucfirst($catSlug),
                    'slug' => $catSlug ?: $currentCategorySlug,
                    'icon' => $this->getCategoryIcon($catSlug),
                    'guides_count' => $guides->count(),
                    'url' => route('guide.category.make.model', [
                        'category' => $catSlug ?: $currentCategorySlug,
                        'make' => $makeSlug,
                        'model' => $modelSlug
                    ]),
                ];
            })
            ->sortByDesc('guides_count')
            ->values()
            ->toArray();

        return $otherCategories;
    }

    public function getSeoData(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();
        $stats = $this->getStats();

        $totalYears = $stats['total_years'];
        $newestYear = $stats['newest_year'];
        $oldestYear = $stats['oldest_year'];

        $yearRange = $totalYears > 0 ? "({$oldestYear}-{$newestYear})" : "";

        return [
            'title' => "{$category['name']} {$make['name']} {$model['name']} {$yearRange} – Todos os anos | Mercado Veículos",
            'description' => "Guias completos de {$category['name']} para {$make['name']} {$model['name']}: {$totalYears} anos disponíveis. Escolha o ano do seu veículo e veja as especificações completas.",
            'canonical' => route('guide.category.make.model', [
                'category' => $category['slug'],
                'make' => $make['slug'],
                'model' => $model['slug']
            ]),
            'og_image' => "/images/models/{$model['slug']}-hero.jpg",
            'keywords' => "{$category['name']}, {$make['name']}, {$model['name']}, especificações, manual",
        ];
    }

    public function getBreadcrumbs(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();

        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $category['name'], 'url' => route('guide.category', ['category' => $category['slug']])],
            ['name' => $make['name'], 'url' => route('guide.category.make', ['category' => $category['slug'], 'make' => $make['slug']])],
            ['name' => $model['name'], 'url' => null],
        ];
    }

    private function getCategoryIcon(string $categorySlug): string
    {
        $icons = [
            'oleo' => '🛢️',
            'calibragem' => '🎯',
            'pneus' => '🛞',
            'problemas' => '⚠️',
            'revisao' => '🔧',
            'consumo' => '⛽',
            'bateria' => '🔋',
            'cambio' => '⚙️',
            'arrefecimento' => '❄️',
            'torque' => '🔩',
            'fluidos' => '💧',
            'motores' => '🏎️',
            'manutencao' => '🛠️',
        ];

        return $icons[$categorySlug] ?? '📋';
    }
}
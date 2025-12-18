<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Mongo\Guide;

/**
 * ViewModel para página de categoria + marca
 * Rota: /guias/{category}/{make}
 * Exemplo: /guias/oleo/toyota
 */
class GuideCategoryMakeViewModel
{
    private $category;
    private $make;
    private Collection $guides;

    public function __construct($category, $make, Collection $guides)
    {
        $this->category = $category;
        $this->make = $make;
        $this->guides = $guides;
    }

    public function getCategory(): array
    {
        return [
            'id' => $this->category->_id ?? null,
            'name' => $this->category->name ?? null,
            'slug' => $this->category->slug ?? null,
            'icon' => $this->category->icon ?? '🛢️',
        ];
    }

    public function getMake(): array
    {
        return [
            'id' => $this->make->id ?? null,
            'name' => $this->make->name ?? null,
            'slug' => $this->make->slug ?? null,
            'logo' => "/images/makes/{$this->make->slug}.svg",
        ];
    }

    /**
     * Retorna modelos populares (top 6)
     * BUSCA DADOS REAIS DO MONGODB
     */
    public function getPopularModels(): array
    {
        if ($this->guides->isEmpty()) {
            return [];
        }

        $categorySlug = $this->category->slug;
        $makeSlug = $this->make->slug;

        return $this->guides
            ->groupBy('model_slug')
            ->map(function ($modelGuides) use ($categorySlug, $makeSlug) {
                $first = $modelGuides->first();

                return [
                    'name' => $first->model,
                    'slug' => $first->model_slug,
                    'guides_count' => $modelGuides->count(),
                    'years_count' => $modelGuides->pluck('year_start')->unique()->count(),
                    'image' => "/images/placeholder/{$first->model_slug}-hero.jpg",
                    'url' => route('guide.category.make.model', [
                        'category' => $categorySlug,
                        'make' => $makeSlug,
                        'model' => $first->model_slug
                    ]),
                ];
            })
            ->sortByDesc('guides_count')
            ->take(6)
            ->values()
            ->toArray();
    }

    /**
     * Retorna lista completa de modelos
     * ✅ CORRIGIDO: Usa segment do MongoDB (vem do VehicleModel.category)
     */
    public function getAllModels(): array
    {
        if ($this->guides->isEmpty()) {
            return [];
        }

        $categorySlug = $this->category->slug;
        $makeSlug = $this->make->slug;

        return $this->guides
            ->groupBy('model_slug')
            ->map(function ($modelGuides) use ($categorySlug, $makeSlug) {
                $first = $modelGuides->first();
                $years = $modelGuides->pluck('year_start')->filter()->unique()->sort();

                return [
                    'name' => $first->model,
                    'slug' => $first->model_slug,
                    'segment' => $this->formatSegment($first->segment),
                    'guides_count' => $modelGuides->count(),
                    'year_range' => $years->isEmpty()
                        ? '-'
                        : $years->min() . '-' . $years->max(),
                    'years' => $this->formatYears($years),
                    'url' => route('guide.category.make.model', [
                        'category' => $categorySlug,
                        'make' => $makeSlug,
                        'model' => $first->model_slug
                    ]),
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    /**
     * Formata anos
     */
    private function formatYears($years): string
    {
        if ($years->isEmpty()) return '-';
        $min = $years->min();
        $max = $years->max();
        return $min === $max ? (string)$min : "{$min}-{$max}";
    }

    private function formatSegment(?string $segment): string
    {
        if (!$segment) return 'N/A';

        return match (strtolower($segment)) {
            'suv' => 'SUV',
            default => ucfirst($segment),
        };
    }

    /**
     * Retorna categorias complementares
     */
    public function getComplementaryCategories(): array
    {
        $currentSlug = $this->category->slug;
        $makeSlug = $this->make->slug;

        $guideModel = app(Guide::class);

        return $guideModel::where('make_slug', $makeSlug)
            ->where('category_slug', '!=', $currentSlug)
            ->get()
            ->groupBy('category_slug')
            ->map(function ($guides, $catSlug) use ($makeSlug, $currentSlug) {
                $first = $guides->first();

                return [
                    'name' => $first->category ?? ucfirst($catSlug),
                    'slug' => $catSlug ?: $currentSlug,
                    'icon' => $this->getCategoryIcon($catSlug),
                    'guides_count' => $guides->count(),
                    'url' => route('guide.category.make', [
                        'category' => $catSlug ?: $currentSlug,
                        'make' => $makeSlug
                    ]),
                ];
            })
            ->sortByDesc('guides_count')
            ->values()
            ->toArray();
    }

    public function getSeoData(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();

        return [
            'title' => "{$category['name']} {$make['name']} – Guias por modelo e ano | Mercado Veículos",
            'description' => "Guias completos de {$category['name']} para veículos {$make['name']}: especificações, recomendações por modelo e ano.",
            'canonical' => route('guide.category.make', [
                'category' => $category['slug'],
                'make' => $make['slug'],
            ]),
            'og_image' => "/images/makes/{$make['slug']}-hero.jpg",
        ];
    }

    public function getBreadcrumbs(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();

        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $category['name'], 'url' => route('guide.category', ['category' => $category['slug']])],
            ['name' => $make['name'], 'url' => null],
        ];
    }

    public function getStats(): array
    {
        return [
            'total_models' => count($this->getAllModels()),
            'total_guides' => $this->guides->count(),
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

    /**
     * Retorna o segmento do modelo
     */
    private function getSegmentByModel(string $modelSlug): string
    {
        $segments = [
            'hb20' => 'Hatch',
            'creta' => 'SUV',
            'tucson' => 'SUV',
            'civic' => 'Sedan',
            'hr-v' => 'SUV',
            'corolla' => 'Sedan',
            'hilux' => 'Picape',
            'gol' => 'Hatch',
        ];

        return $segments[$modelSlug] ?? 'N/A';
    }
}

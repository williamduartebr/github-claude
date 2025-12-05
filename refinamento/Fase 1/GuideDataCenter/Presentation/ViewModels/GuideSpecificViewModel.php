<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Mongo\Guide;

/**
 * ViewModel para página de guia específico
 * Rota: /guias/{category}/{make}/{model}/{year}/{version}
 * Exemplo: /guias/fluidos/toyota/corolla/2023/gli
 */
class GuideSpecificViewModel
{
    private $guide;
    private $category;
    private $make;
    private $model;
    private int $year;
    private string $version;

    public function __construct($guide, $category, $make, $model, int $year, ?string $version = null)
    {
        $this->guide = $guide;
        $this->category = $category;
        $this->make = $make;
        $this->model = $model;
        $this->year = $year;
        $this->version = $version ?? 'base';
    }

    public function getGuide(): array
    {
        if (!$this->guide) {
            return [
                'id' => null,
                'title' => $this->generateTitle(),
                'description' => $this->generateDescription(),
                'content' => null,
            ];
        }

        return [
            'id' => $this->guide->_id ?? null,
            'title' => $this->guide->title ?? $this->generateTitle(),
            'description' => $this->guide->description ?? $this->generateDescription(),
            'content' => $this->guide->payload['content'] ?? null,
            'payload' => $this->guide->payload ?? [],
        ];
    }

    public function getCategory(): array
    {
        return [
            'name' => $this->category->name ?? 'Categoria',
            'slug' => $this->category->slug ?? 'categoria',
        ];
    }

    public function getMake(): array
    {
        return [
            'name' => $this->make->name ?? 'Marca',
            'slug' => $this->make->slug ?? 'marca',
        ];
    }

    public function getModel(): array
    {
        return [
            'name' => $this->model->name ?? 'Modelo',
            'slug' => $this->model->slug ?? 'modelo',
        ];
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * BUSCA GUIAS RELACIONADOS REAIS (outras categorias, mesmo veículo)
     */
    public function getRelatedGuides(): array
    {
        $currentCategorySlug = $this->category->slug;
        $makeSlug = $this->make->slug;
        $modelSlug = $this->model->slug;
        $year = $this->year;

        $guideModel = app(Guide::class);
        
        $otherGuides = $guideModel::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('year_start', '<=', $year)
            ->where('year_end', '>=', $year)
            ->where('category_slug', '!=', $currentCategorySlug)
            ->get()
            ->groupBy('category_slug')
            ->map(function($guides, $catSlug) use ($makeSlug, $modelSlug, $year) {
                $first = $guides->first();
                
                return [
                    'name' => $first->category ?? ucfirst($catSlug),
                    'slug' => $catSlug,
                    'icon' => $this->getCategoryIcon($catSlug),
                    'url' => route('guide.year', [
                        'category' => $catSlug,
                        'make' => $makeSlug,
                        'model' => $modelSlug,
                        'year' => $year
                    ]),
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        return $otherGuides;
    }

    /**
     * CLUSTER ESSENCIAL (anos próximos + ficha técnica)
     */
    public function getEssentialCluster(): array
    {
        $makeSlug = $this->make->slug;
        $modelSlug = $this->model->slug;
        $year = $this->year;
        $categorySlug = $this->category->slug;

        $cluster = [];

        // Link para ficha técnica do veículo
        $cluster[] = [
            'icon' => '📋',
            'title' => "Ficha técnica – {$this->model->name} {$year}",
            'url' => route('vehicles.year', [
                'make' => $makeSlug,
                'model' => $modelSlug,
                'year' => $year
            ]),
            'type' => 'vehicle',
        ];

        // Anos próximos (buscar do banco)
        $guideModel = app(Guide::class);
        
        $nearYears = $guideModel::where('category_slug', $categorySlug)
            ->where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('year_start', '>=', $year - 2)
            ->where('year_start', '<=', $year + 2)
            ->where('year_start', '!=', $year)
            ->get()
            ->pluck('year_start')
            ->unique()
            ->sort()
            ->take(4);

        foreach ($nearYears as $nearYear) {
            $cluster[] = [
                'icon' => '📅',
                'title' => "{$this->category->name} {$nearYear}",
                'url' => route('guide.year', [
                    'category' => $categorySlug,
                    'make' => $makeSlug,
                    'model' => $modelSlug,
                    'year' => $nearYear
                ]),
                'type' => 'year',
            ];
        }

        return $cluster;
    }

    /**
     * ⚠️ MOCK TEMPORÁRIO - Ajustar no seeder depois
     * Especificações oficiais do óleo recomendado
     */
    public function getOfficialSpecs(): array
    {
        // TODO: Buscar do $this->guide->payload['oil_specs'] quando houver dados reais
        return [
            ['label' => 'Viscosidade', 'value' => '5W-30'],
            ['label' => 'Especificação', 'value' => 'API SN / ILSAC GF-5'],
            ['label' => 'Capacidade', 'value' => '4.2 litros (com filtro)'],
        ];
    }

    /**
     * ⚠️ MOCK TEMPORÁRIO - Ajustar no seeder depois
     * Óleos compatíveis e equivalentes
     */
    public function getCompatibleOils(): array
    {
        // TODO: Buscar do $this->guide->payload['compatible_oils'] quando houver dados reais
        return [
            ['name' => 'Mobil 1 5W-30', 'spec' => 'Sintético - API SN Plus'],
            ['name' => 'Castrol Edge 5W-30', 'spec' => 'Sintético - API SN Plus'],
            ['name' => 'Shell Helix Ultra 5W-30', 'spec' => 'Sintético - API SN'],
            ['name' => 'Petronas Syntium 5W-30', 'spec' => 'Sintético - API SN'],
        ];
    }

    /**
     * ⚠️ MOCK TEMPORÁRIO - Ajustar no seeder depois
     * Intervalos de troca de óleo
     */
    public function getChangeIntervals(): array
    {
        // TODO: Buscar do $this->guide->payload['change_intervals'] quando houver dados reais
        return [
            ['label' => 'Uso normal', 'value' => '10.000 km ou 12 meses'],
            ['label' => 'Uso severo', 'value' => '5.000 km ou 6 meses'],
        ];
    }

    /**
     * ⚠️ MOCK TEMPORÁRIO - Ajustar no seeder depois
     * Nota sobre uso severo
     */
    public function getSevereUseNote(): string
    {
        // TODO: Buscar do $this->guide->payload['severe_use_note'] quando houver dados reais
        return 'Uso severo: trajetos curtos frequentes, trânsito intenso, reboque, áreas empoeiradas.';
    }

    public function getBadges(): array
    {
        return [
            ['icon' => '🔧', 'text' => 'Informação Oficial', 'color' => 'green'],
            ['icon' => '✓', 'text' => 'Revisado', 'color' => 'blue'],
            ['icon' => '📅', 'text' => date('Y'), 'color' => 'blue'],
        ];
    }

    public function getDisclaimer(): string
    {
        $make = $this->getMake();
        return "As especificações apresentadas são baseadas nos manuais oficiais da {$make['name']}. Sempre consulte o manual do proprietário para informações específicas do seu veículo.";
    }

    public function getEditorialInfo(): array
    {
        return [
            'title' => 'Equipe Editorial Mercado Veículos',
            'description' => "Guia técnico desenvolvido com base em especificações oficiais.",
            'methodology' => 'Revisão por especialistas automotivos.',
            'link_text' => 'Conheça nossa metodologia',
            'link_url' => '/sobre/metodologia',
        ];
    }

    public function getSeoData(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();
        $version = strtoupper($this->version);

        return [
            'title' => "{$category['name']} {$make['name']} {$model['name']} {$this->year} {$version} | Mercado Veículos",
            'description' => "Guia completo: {$category['name']} para {$make['name']} {$model['name']} {$this->year} {$version}. Especificações, recomendações e intervalos.",
            'canonical' => route('guide.version', [
                'category' => $category['slug'],
                'make' => $make['slug'],
                'model' => $model['slug'],
                'year' => $this->year,
                'version' => $this->version
            ]),
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
            ['name' => $category['name'], 'url' => route('guide.category', $category['slug'])],
            ['name' => $make['name'], 'url' => route('guide.category.make', ['category' => $category['slug'], 'make' => $make['slug']])],
            ['name' => $model['name'], 'url' => route('guide.category.make.model', ['category' => $category['slug'], 'make' => $make['slug'], 'model' => $model['slug']])],
            ['name' => $this->year, 'url' => route('guide.year', ['category' => $category['slug'], 'make' => $make['slug'], 'model' => $model['slug'], 'year' => $this->year])],
            ['name' => strtoupper($this->version), 'url' => null],
        ];
    }

    private function generateTitle(): string
    {
        $category = $this->category->name ?? 'Guia';
        $make = $this->make->name ?? 'Marca';
        $model = $this->model->name ?? 'Modelo';
        $version = strtoupper($this->version);

        return "{$category} {$make} {$model} {$this->year} {$version}";
    }

    private function generateDescription(): string
    {
        return "Guia completo com especificações e recomendações.";
    }

    private function getCategoryIcon(string $slug): string
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
            'fluidos' => '💧',
            'motores' => '🏎️',
        ];

        return $icons[$slug] ?? '📋';
    }
}
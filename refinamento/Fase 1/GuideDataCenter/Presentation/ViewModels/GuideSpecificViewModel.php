<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;

/**
 * ViewModel para página de guia específico
 * Rota: /guias/{category}/{make}/{model}/{year}/{version}
 * Exemplo: /guias/fluidos/toyota/corolla/2023/gli
 * 
 * ✅ REFINADO V3: Cluster completo com versões, consumo, problemas, motores
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
     * ✅ REFINADO: Busca guias relacionados REAIS (outras categorias, mesmo veículo)
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
     * ✅ REFINADO V3: Cluster essencial COMPLETO
     * 
     * Tipos de conteúdo:
     * 1. 🚗 Fichas técnicas por versão (GLi 1.8, XEi 2.0)
     * 2. 📘 Ficha técnica geral do modelo
     * 3. ⛽ Consumo real por motor
     * 4. ⚠️ Problemas comuns por geração
     * 5. 💧 Fluidos e capacidades
     * 6. 🔧 Motores alternativos
     * 7. 🔄 Óleo/guias de anos próximos
     */
    public function getEssentialCluster(): array
    {
        $makeSlug = $this->make->slug;
        $modelSlug = $this->model->slug;
        $year = $this->year;
        $categorySlug = $this->category->slug;

        $cluster = [];

        // ===================================================================
        // 1. 📘 FICHA TÉCNICA GERAL (sempre primeiro)
        // ===================================================================
        $cluster[] = [
            'icon' => '📘',
            'title' => "Ficha Técnica do {$this->model->name} {$year}",
            'url' => route('vehicles.year', [
                'make' => $makeSlug,
                'model' => $modelSlug,
                'year' => $year
            ]),
            'type' => 'vehicle_general',
            'priority' => 1,
        ];

        // ===================================================================
        // 2. 🚗 FICHAS TÉCNICAS POR VERSÃO (GLi, XEi, etc)
        // ===================================================================
        $versions = VehicleVersion::whereHas('model', function($q) use ($makeSlug, $modelSlug) {
            $q->where('slug', $modelSlug)
              ->whereHas('make', fn($q2) => $q2->where('slug', $makeSlug));
        })
        ->where('year', $year)
        ->orderBy('name')
        ->limit(3) // Top 3 versões
        ->get();

        foreach ($versions as $version) {
            $cluster[] = [
                'icon' => '🚗',
                'title' => "Ficha técnica – {$this->model->name} {$year} {$version->name}",
                'url' => route('vehicles.version', [
                    'make' => $makeSlug,
                    'model' => $modelSlug,
                    'year' => $year,
                    'version' => $version->slug ?? str_slug($version->name)
                ]),
                'type' => 'vehicle_version',
                'priority' => 2,
            ];
        }

        // ===================================================================
        // 3. ⛽ CONSUMO REAL POR MOTOR
        // ===================================================================
        $guideModel = app(Guide::class);
        
        $consumoGuides = $guideModel::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('category_slug', 'consumo')
            ->where('year_start', '<=', $year)
            ->where('year_end', '>=', $year)
            ->get();

        foreach ($consumoGuides as $consumo) {
            // Extrair motor do payload ou title
            $motor = $consumo->payload['motor'] ?? $consumo->motor ?? 'Motor';
            
            $cluster[] = [
                'icon' => '⛽',
                'title' => "Consumo Real — {$motor}",
                'url' => route('guide.year', [
                    'category' => 'consumo',
                    'make' => $makeSlug,
                    'model' => $modelSlug,
                    'year' => $year
                ]),
                'type' => 'consumo',
                'priority' => 3,
            ];
        }

        // ===================================================================
        // 4. ⚠️ PROBLEMAS COMUNS POR GERAÇÃO
        // ===================================================================
        $problemasGuides = $guideModel::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('category_slug', 'problemas')
            ->where('year_start', '<=', $year)
            ->where('year_end', '>=', $year)
            ->get();

        foreach ($problemasGuides as $problema) {
            // Extrair range de anos
            $yearRange = "{$problema->year_start}–{$problema->year_end}";
            
            $cluster[] = [
                'icon' => '⚠️',
                'title' => "Problemas comuns (Geração {$yearRange})",
                'url' => route('guide.year', [
                    'category' => 'problemas',
                    'make' => $makeSlug,
                    'model' => $modelSlug,
                    'year' => $year
                ]),
                'type' => 'problemas',
                'priority' => 4,
            ];
        }

        // ===================================================================
        // 5. 💧 FLUIDOS E CAPACIDADES
        // ===================================================================
        $fluidosGuides = $guideModel::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('category_slug', 'fluidos')
            ->where('year_start', '<=', $year)
            ->where('year_end', '>=', $year)
            ->first();

        if ($fluidosGuides) {
            $cluster[] = [
                'icon' => '💧',
                'title' => "Fluidos e capacidades",
                'url' => route('guide.year', [
                    'category' => 'fluidos',
                    'make' => $makeSlug,
                    'model' => $modelSlug,
                    'year' => $year
                ]),
                'type' => 'fluidos',
                'priority' => 5,
            ];
        }

        // ===================================================================
        // 6. 🔧 MOTORES ALTERNATIVOS
        // ===================================================================
        $motorsAlternativos = VehicleVersion::whereHas('model', function($q) use ($makeSlug, $modelSlug) {
            $q->where('slug', $modelSlug)
              ->whereHas('make', fn($q2) => $q2->where('slug', $makeSlug));
        })
        ->where('year', $year)
        ->whereNotNull('engine_code')
        ->get()
        ->pluck('engine_code')
        ->unique()
        ->take(2); // Top 2 motores alternativos

        foreach ($motorsAlternativos as $motor) {
            if (!$motor) continue;
            
            $cluster[] = [
                'icon' => '🔧',
                'title' => "Motor alternativo — {$motor}",
                'url' => route('vehicles.year', [
                    'make' => $makeSlug,
                    'model' => $modelSlug,
                    'year' => $year
                ]) . "?motor={$motor}",
                'type' => 'motor_alternativo',
                'priority' => 6,
            ];
        }

        // ===================================================================
        // 7. 🔄 GUIAS DE ANOS PRÓXIMOS (±2 anos)
        // ===================================================================
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
                'icon' => '🔄',
                'title' => "{$this->category->name} do {$this->model->name} {$nearYear}",
                'url' => route('guide.year', [
                    'category' => $categorySlug,
                    'make' => $makeSlug,
                    'model' => $modelSlug,
                    'year' => $nearYear
                ]),
                'type' => 'year_near',
                'priority' => 7,
            ];
        }

        // ===================================================================
        // ORDENAR POR PRIORIDADE
        // ===================================================================
        usort($cluster, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $cluster;
    }

    /**
     * ⚠️ MOCK TEMPORÁRIO - Será substituído por dados reais do payload
     * Especificações oficiais do óleo recomendado
     */
    public function getOfficialSpecs(): array
    {
        // TODO: Buscar do $this->guide->payload['oil_specs'] quando seeder criar
        return [
            ['label' => 'Viscosidade', 'value' => '5W-30'],
            ['label' => 'Especificação', 'value' => 'API SN / ILSAC GF-5'],
            ['label' => 'Capacidade', 'value' => '4.2 litros (com filtro)'],
        ];
    }

    /**
     * ⚠️ MOCK TEMPORÁRIO - Será substituído por dados reais do payload
     * Óleos compatíveis e equivalentes
     */
    public function getCompatibleOils(): array
    {
        // TODO: Buscar do $this->guide->payload['compatible_oils'] quando seeder criar
        return [
            ['name' => 'Mobil 1 5W-30', 'spec' => 'Sintético - API SN Plus'],
            ['name' => 'Castrol Edge 5W-30', 'spec' => 'Sintético - API SN Plus'],
            ['name' => 'Shell Helix Ultra 5W-30', 'spec' => 'Sintético - API SN'],
            ['name' => 'Petronas Syntium 5W-30', 'spec' => 'Sintético - API SN'],
        ];
    }

    /**
     * ⚠️ MOCK TEMPORÁRIO - Será substituído por dados reais do payload
     * Intervalos de troca de óleo
     */
    public function getChangeIntervals(): array
    {
        // TODO: Buscar do $this->guide->payload['change_intervals'] quando seeder criar
        return [
            ['label' => 'Uso normal', 'value' => '10.000 km ou 12 meses'],
            ['label' => 'Uso severo', 'value' => '5.000 km ou 6 meses'],
        ];
    }

    /**
     * ⚠️ MOCK TEMPORÁRIO - Será substituído por dados reais do payload
     * Nota sobre uso severo
     */
    public function getSevereUseNote(): string
    {
        // TODO: Buscar do $this->guide->payload['severe_use_note'] quando seeder criar
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

    /**
     * ✅ CORRIGIDO V2: Adiciona og_type e og_image ao SEO
     */
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
            // ✅ ADICIONADOS V2
            'og_type' => 'article',
            'og_image' => asset("images/vehicles/{$make['slug']}/{$model['slug']}-{$this->year}.jpg"),
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
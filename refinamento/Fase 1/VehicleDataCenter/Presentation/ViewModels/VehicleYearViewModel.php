<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;

/**
 * ViewModel para página de um ano específico do modelo
 * 
 * Rota: /veiculos/{make}/{model}/{year}
 * View: vehicles.year
 * Exemplo: /veiculos/toyota/corolla/2023
 * 
 * @author Mercado Veículos Team
 * @version 1.0.0
 */
class VehicleYearViewModel
{
    private $make;
    private $model;
    private int $year;
    private Collection $versions;

    /**
     * Constructor
     * 
     * @param mixed $make VehicleMake Eloquent Model
     * @param mixed $model VehicleModel Eloquent Model
     * @param int $year Ano do veículo
     * @param Collection $versions Collection de VehicleVersion
     */
    public function __construct($make, $model, int $year, Collection $versions)
    {
        $this->make = $make;
        $this->model = $model;
        $this->year = $year;
        $this->versions = $versions;
    }

    /**
     * Retorna dados da marca
     * 
     * @return array
     */
    public function getMake(): array
    {
        return [
            'id' => $this->make->id,
            'name' => $this->make->name,
            'slug' => $this->make->slug,
            'logo' => $this->make->logo_url,
            'country_origin' => $this->make->country_origin,
        ];
    }

    /**
     * Retorna dados do modelo
     * 
     * @return array
     */
    public function getModel(): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'slug' => $this->model->slug,
            'category' => $this->translateCategory($this->model->category),
            'category_slug' => $this->model->category,
        ];
    }

    /**
     * Retorna ano
     * 
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Retorna título completo
     * 
     * @return string
     */
    public function getFullTitle(): string
    {
        return "{$this->make->name} {$this->model->name} {$this->year}";
    }

    /**
     * Retorna descrição otimizada para SEO
     * 
     * @return string
     */
    public function getDescription(): string
    {
        $count = $this->versions->count();
        $versionsText = $count === 1 ? 'versão disponível' : 'versões disponíveis';

        return "Conheça as {$count} {$versionsText} do {$this->make->name} {$this->model->name} {$this->year}: fichas técnicas completas, especificações, motores, consumo e guias de manutenção.";
    }

    /**
     * Retorna versões formatadas
     * 
     * @return array
     */
    public function getVersions(): array
    {
        return $this->versions->map(function ($version) {
            return [
                'id' => $version->id,
                'name' => $version->name,
                'slug' => $version->slug,
                'engine_code' => $version->engine_code,
                'fuel_type' => $this->translateFuelType($version->fuel_type),
                'fuel_type_slug' => $version->fuel_type,
                'transmission' => $this->translateTransmission($version->transmission),
                'transmission_slug' => $version->transmission,
                'price_msrp' => $version->price_msrp,
                'price_formatted' => $this->formatPrice($version->price_msrp),
                'url' => route('vehicles.version', [
                    'make' => $this->make->slug,
                    'model' => $this->model->slug,
                    'year' => $this->year,
                    'version' => $version->slug,
                ]),

                // Dados de specs se existirem
                'power_hp' => $version->specs->power_hp ?? null,
                'torque_nm' => $version->specs->torque_nm ?? null,
                'engine_info' => $this->buildEngineInfo($version),
            ];
        })->toArray();
    }

    /**
     * Retorna versões agrupadas por tipo de combustível
     * 
     * @return array
     */
    public function getVersionsByFuel(): array
    {
        $grouped = $this->versions->groupBy('fuel_type');
        $result = [];

        foreach ($grouped as $fuelType => $versions) {
            $result[] = [
                'fuel_type' => $this->translateFuelType($fuelType),
                'fuel_type_slug' => $fuelType,
                'count' => $versions->count(),
                'versions' => $versions->map(function ($version) {
                    return [
                        'name' => $version->name,
                        'slug' => $version->slug,
                        'transmission' => $this->translateTransmission($version->transmission),
                        'url' => route('vehicles.version', [
                            'make' => $this->make->slug,
                            'model' => $this->model->slug,
                            'year' => $this->year,
                            'version' => $version->slug,
                        ]),
                        'engine_info' => $this->buildEngineInfo($version),
                        'power_hp' => $version->specs->power_hp ?? null,
                        'price_formatted' => $this->formatPrice($version->price_msrp),
                    ];
                })->toArray(),
            ];
        }

        return $result;
    }

    /**
     * Retorna estatísticas do ano
     * 
     * @return array
     */
    public function getStats(): array
    {
        // Buscar tipos de combustível únicos E TRADUZIDOS
        $fuelTypes = $this->versions
            ->pluck('fuel_type')
            ->unique()
            ->map(function ($fuelType) {
                return $this->translateFuelType($fuelType);
            })
            ->filter()
            ->values()
            ->toArray();

        return [
            'versions_count' => $this->versions->count(), // ✅ Era 'total_versions'
            'fuel_types' => $fuelTypes, // ✅ Array de strings, era count()
            'transmission_types' => $this->versions->pluck('transmission')->unique()->count(),
            'price_range' => $this->getPriceRange(), // ✅ Este método já existe
        ];
    }

    /**
     * Retorna anos próximos (anterior e posterior)
     * Verifica se existem versões nestes anos
     * 
     * @return array
     */
    public function getNearbyYears(): array
    {
        return [
            'previous' => [
                'year' => $this->year - 1,
                'url' => route('vehicles.year', [
                    'make' => $this->make->slug,
                    'model' => $this->model->slug,
                    'year' => $this->year - 1,
                ]),
                'exists' => $this->checkYearExists($this->year - 1),
            ],
            'next' => [
                'year' => $this->year + 1,
                'url' => route('vehicles.year', [
                    'make' => $this->make->slug,
                    'model' => $this->model->slug,
                    'year' => $this->year + 1,
                ]),
                'exists' => $this->checkYearExists($this->year + 1),
            ],
        ];
    }

    /**
     * Retorna guias técnicos específicos do ano
     * 
     * @return array
     */
    public function getQuickGuides(): array
    {
        return [
            [
                'name' => 'Óleo',
                'icon' => '🛢️',
                'description' => 'Especificações de óleo',
                'url' => "/guias/oleo/{$this->make->slug}/{$this->model->slug}-{$this->year}",
            ],
            [
                'name' => 'Pneus',
                'icon' => '🚗',
                'description' => 'Medidas originais',
                'url' => "/guias/pneus/{$this->make->slug}/{$this->model->slug}-{$this->year}",
            ],
            [
                'name' => 'Calibragem',
                'icon' => '🔧',
                'description' => 'Pressão recomendada',
                'url' => "/guias/calibragem/{$this->make->slug}/{$this->model->slug}-{$this->year}",
            ],
            [
                'name' => 'Consumo',
                'icon' => '⛽',
                'description' => 'Médias reais',
                'url' => "/guias/consumo/{$this->make->slug}/{$this->model->slug}-{$this->year}",
            ],
            [
                'name' => 'Problemas',
                'icon' => '⚠️',
                'description' => 'Falhas conhecidas',
                'url' => "/guias/problemas/{$this->make->slug}/{$this->model->slug}-{$this->year}",
            ],
            [
                'name' => 'Revisão',
                'icon' => '📋',
                'description' => 'Plano de manutenção',
                'url' => "/guias/revisao/{$this->make->slug}/{$this->model->slug}-{$this->year}",
            ],
        ];
    }

    /**
     * Retorna dados para SEO
     * 
     * @return array
     */
    public function getSeoData(): array
    {
        $fullTitle = $this->getFullTitle();
        $count = $this->versions->count();

        return [
            'title' => "{$fullTitle} — {$count} " . ($count === 1 ? 'Versão' : 'Versões') . " e Fichas Técnicas | Mercado Veículos",
            'description' => $this->getDescription(),
            'canonical' => route('vehicles.year', [
                'make' => $this->make->slug,
                'model' => $this->model->slug,
                'year' => $this->year,
            ]),
            'og_image' => "/images/vehicles/{$this->make->slug}/{$this->model->slug}/{$this->year}/og-image.jpg",
            'keywords' => $this->buildKeywords(),
        ];
    }

    /**
     * Retorna breadcrumbs estruturados
     * 
     * @return array
     */
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Veículos', 'url' => route('vehicles.index')],
            ['name' => $this->make->name, 'url' => route('vehicles.make', ['make' => $this->make->slug])],
            ['name' => $this->model->name, 'url' => route('vehicles.model', ['make' => $this->make->slug, 'model' => $this->model->slug])],
            ['name' => (string) $this->year, 'url' => null],
        ];
    }

    /**
     * Retorna Schema.org estruturado para a página
     * 
     * @return array
     */
    public function getSchemaOrg(): array
    {
        $fullTitle = $this->getFullTitle();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => "{$fullTitle} - Versões",
            'description' => $this->getDescription(),
            'numberOfItems' => $this->versions->count(),
            'itemListElement' => $this->versions->map(function ($version, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'item' => [
                        '@type' => 'Car',
                        'name' => "{$this->make->name} {$this->model->name} {$version->name} {$this->year}",
                        'brand' => [
                            '@type' => 'Brand',
                            'name' => $this->make->name,
                        ],
                        'model' => $this->model->name,
                        'modelDate' => $this->year,
                        'vehicleModelDate' => $this->year,
                        'url' => route('vehicles.version', [
                            'make' => $this->make->slug,
                            'model' => $this->model->slug,
                            'year' => $this->year,
                            'version' => $version->slug,
                        ]),
                    ],
                ];
            })->toArray(),
        ];
    }

    // ========================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ========================================

    /**
     * Constrói informação do motor
     * 
     * @param mixed $version
     * @return string
     */
    private function buildEngineInfo($version): string
    {
        $parts = [];

        if ($version->engine_code) {
            $parts[] = $version->engine_code;
        }

        if (isset($version->engineSpecs) && $version->engineSpecs && $version->engineSpecs->displacement_cc) {
            $displacement = number_format($version->engineSpecs->displacement_cc / 1000, 1);
            $parts[] = "{$displacement}L";
        }

        if (empty($parts)) {
            return 'Motor ' . $this->translateFuelType($version->fuel_type);
        }

        return implode(' • ', $parts);
    }

    /**
     * Verifica se existe versão no ano especificado
     * 
     * @param int $year
     * @return bool
     */
    private function checkYearExists(int $year): bool
    {
        // Query simples para verificar se existe versão no ano
        $modelClass = get_class($this->model);
        $versionClass = 'Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion';

        try {
            return $versionClass::where('model_id', $this->model->id)
                ->where('year', $year)
                ->where('is_active', true)
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Retorna faixa de preço das versões
     * 
     * @return array|null
     */
    private function getPriceRange(): ?array
    {
        $prices = $this->versions->pluck('price_msrp')->filter();

        if ($prices->isEmpty()) {
            return null;
        }

        return [
            'min' => $this->formatPrice($prices->min()),
            'max' => $this->formatPrice($prices->max()),
        ];
    }

    /**
     * Constrói keywords para SEO
     * 
     * @return array
     */
    private function buildKeywords(): array
    {
        return [
            "{$this->make->name} {$this->model->name} {$this->year}",
            "ficha técnica {$this->make->name} {$this->model->name} {$this->year}",
            "{$this->make->name} {$this->model->name} {$this->year} versões",
            "{$this->make->name} {$this->model->name} {$this->year} especificações",
            "{$this->make->name} {$this->model->name} {$this->year} consumo",
            "{$this->make->name} {$this->model->name} {$this->year} preço",
            "{$this->make->name} {$this->model->name} {$this->year} ficha",
            "quanto custa {$this->make->name} {$this->model->name} {$this->year}",
        ];
    }

    /**
     * Traduz categoria para português
     * 
     * @param string $category
     * @return string
     */
    private function translateCategory(string $category): string
    {
        $translations = [
            'sedan' => 'Sedã',
            'sedan_compact' => 'Sedã compacto',
            'sedan_medium' => 'Sedã médio',
            'sedan_large' => 'Sedã grande',
            'hatch' => 'Hatchback',
            'hatchback' => 'Hatchback',
            'suv' => 'SUV',
            'suv_compact' => 'SUV compacto',
            'suv_medium' => 'SUV médio',
            'suv_large' => 'SUV grande',
            'pickup' => 'Picape',
            'van' => 'Van',
            'minivan' => 'Minivan',
            'coupe' => 'Cupê',
            'convertible' => 'Conversível',
            'wagon' => 'Perua',
            'sport' => 'Esportivo',
        ];

        return $translations[$category] ?? ucfirst($category);
    }

    /**
     * Traduz tipo de combustível para português
     * 
     * @param string|null $fuelType
     * @return string
     */
    private function translateFuelType(?string $fuelType): string
    {
        $translations = [
            'gasoline' => 'Gasolina',
            'diesel' => 'Diesel',
            'ethanol' => 'Etanol',
            'flex' => 'Flex',
            'electric' => 'Elétrico',
            'hybrid' => 'Híbrido',
            'plugin_hybrid' => 'Híbrido Plug-in',
            'cng' => 'GNV',
        ];

        return $translations[$fuelType] ?? 'N/A';
    }

    /**
     * Traduz tipo de transmissão para português
     * 
     * @param string|null $transmission
     * @return string
     */
    private function translateTransmission(?string $transmission): string
    {
        $translations = [
            'manual' => 'Manual',
            'automatic' => 'Automático',
            'cvt' => 'CVT',
            'dct' => 'DCT',
            'amt' => 'AMT',
        ];

        return $translations[$transmission] ?? 'N/A';
    }

    /**
     * Formata preço em reais
     * 
     * @param float|null $price
     * @return string
     */
    private function formatPrice(?float $price): string
    {
        if (!$price) {
            return 'Consulte';
        }

        return 'R$ ' . number_format($price, 2, ',', '.');
    }
    /**
     * Retorna categorias de guias disponíveis para este veículo
     * Usa o VehicleGuideIntegrationService
     * 
     * @return array
     */
    public function getGuideCategories(): array
    {
        try {
            $guideIntegration = app(\Src\VehicleDataCenter\Domain\Services\VehicleGuideIntegrationService::class);

            $categories = $guideIntegration->getGuideCategoriesByMake($this->make->slug);

            return $categories->map(function ($category) {
                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon ?? '📄',
                    'url' => route('guides.make', [
                        'category' => $category->slug,
                        'make' => $this->make->slug
                    ])
                ];
            })->toArray();
        } catch (\Exception $e) {
            // Se falhar, retorna array vazio (graceful degradation)
            return [];
        }
    }
}

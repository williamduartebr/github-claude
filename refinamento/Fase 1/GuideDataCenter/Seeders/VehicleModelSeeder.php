<?php

declare(strict_types=1);

namespace Src\VehicleDataCenter\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;

/**
 * Seeder de Modelos de Veículos - Mercado Brasileiro
 *
 * Este seeder contém os modelos mais vendidos no Brasil por marca,
 * estruturado para ser escalável via API Claude (Sonnet 4.5 / Haiku 4.5).
 *
 * Estrutura preparada para geração automática de conteúdo:
 * - /guias (técnicos): 1.300 artigos/semana
 * - /veiculos (fichas): 800 artigos/semana
 *
 * @author Claude AI Assistant
 * @version 1.0.0
 */
class VehicleModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modelsData = $this->getModelsData();
        $insertedCount = 0;

        foreach ($modelsData as $makeSlug => $models) {
            $make = VehicleMake::where('slug', $makeSlug)->first();

            if (!$make) {
                $this->command->warn("⚠️  Marca '{$makeSlug}' não encontrada. Pulando modelos...");
                continue;
            }

            foreach ($models as $modelData) {
                VehicleModel::updateOrCreate(
                    [
                        'make_id' => $make->id,
                        'slug' => $modelData['slug'],
                    ],
                    [
                        'name' => $modelData['name'],
                        'slug' => $modelData['slug'],
                        'category' => $modelData['category'],
                        'year_start' => $modelData['year_start'],
                        'year_end' => $modelData['year_end'] ?? null,
                        'is_active' => $modelData['is_active'] ?? true,
                        'current_generation' => $modelData['current_generation'] ?? null,
                        'metadata' => $modelData['metadata'] ?? [],
                    ]
                );
                $insertedCount++;
            }
        }

        $this->command->info("✅ {$insertedCount} modelos de veículos inseridos com sucesso!");
    }

    /**
     * Retorna dados dos modelos por marca
     *
     * Organizado por slug da marca -> array de modelos
     * Inclui informações de geração, segmento e histórico
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function getModelsData(): array
    {
        return [
            // ═══════════════════════════════════════════════════════════════
            // FIAT - LÍDER DE MERCADO NO BRASIL
            // ═══════════════════════════════════════════════════════════════
            'fiat' => [
                [
                    'name' => 'Strada',
                    'slug' => 'strada',
                    'category' => 'pickup',
                    'year_start' => 1998,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '1998-2020', 'platform' => 'Palio'],
                            ['gen' => 2, 'years' => '2020-atual', 'platform' => 'MLA'],
                        ],
                        'best_sellers' => ['Freedom', 'Endurance', 'Volcano', 'Ranch'],
                        'engines' => ['1.3 Firefly', '1.4 Fire'],
                        'segment_position' => 1,
                    ],
                ],
                [
                    'name' => 'Argo',
                    'slug' => 'argo',
                    'category' => 'hatch',
                    'year_start' => 2017,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2017-atual', 'platform' => 'MP1'],
                        ],
                        'best_sellers' => ['Drive', 'Trekking', 'HGT'],
                        'engines' => ['1.0 Firefly', '1.3 Firefly', '1.8 E.torQ'],
                        'segment_position' => 3,
                        'replaced' => 'Palio/Punto',
                    ],
                ],
                [
                    'name' => 'Mobi',
                    'slug' => 'mobi',
                    'category' => 'hatch',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2016-atual', 'platform' => 'MP1'],
                        ],
                        'best_sellers' => ['Like', 'Trekking'],
                        'engines' => ['1.0 Firefly'],
                        'segment_position' => 1,
                        'segment' => 'entry-level',
                    ],
                ],
                [
                    'name' => 'Pulse',
                    'slug' => 'pulse',
                    'category' => 'suv',
                    'year_start' => 2021,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2021-atual', 'platform' => 'MLA'],
                        ],
                        'best_sellers' => ['Drive', 'Audace', 'Impetus', 'Abarth'],
                        'engines' => ['1.0 Turbo', '1.3 Turbo'],
                        'segment_position' => 2,
                        'segment' => 'suv-compacto',
                    ],
                ],
                [
                    'name' => 'Fastback',
                    'slug' => 'fastback',
                    'category' => 'suv',
                    'year_start' => 2022,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2022-atual', 'platform' => 'MLA'],
                        ],
                        'best_sellers' => ['Audace', 'Impetus', 'Limited', 'Abarth'],
                        'engines' => ['1.0 Turbo', '1.3 Turbo'],
                        'segment_position' => 5,
                        'segment' => 'suv-coupe',
                    ],
                ],
                [
                    'name' => 'Toro',
                    'slug' => 'toro',
                    'category' => 'pickup',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2016-atual', 'platform' => 'Small Wide'],
                        ],
                        'best_sellers' => ['Endurance', 'Freedom', 'Volcano', 'Ranch', 'Ultra'],
                        'engines' => ['1.3 Turbo', '2.0 Turbo Diesel', '2.4 Tigershark'],
                        'segment_position' => 1,
                        'segment' => 'pickup-media',
                    ],
                ],
                [
                    'name' => 'Cronos',
                    'slug' => 'cronos',
                    'category' => 'sedan',
                    'year_start' => 2018,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2018-atual', 'platform' => 'MP1'],
                        ],
                        'best_sellers' => ['Drive', 'Precision'],
                        'engines' => ['1.0 Firefly', '1.3 Firefly'],
                        'segment_position' => 2,
                        'replaced' => 'Grand Siena',
                    ],
                ],
                [
                    'name' => 'Uno',
                    'slug' => 'uno',
                    'category' => 'hatch',
                    'year_start' => 1984,
                    'year_end' => 2021,
                    'is_active' => false,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '1984-2010', 'platform' => 'Uno Original'],
                            ['gen' => 2, 'years' => '2010-2021', 'platform' => 'Novo Uno'],
                        ],
                        'best_sellers' => ['Mille', 'Fire', 'Way', 'Attractive'],
                        'engines' => ['1.0 Fire', '1.4 Fire'],
                        'historic' => true,
                        'total_produced' => '3.500.000+',
                    ],
                ],
                [
                    'name' => 'Palio',
                    'slug' => 'palio',
                    'category' => 'hatch',
                    'year_start' => 1996,
                    'year_end' => 2017,
                    'is_active' => false,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '1996-2012', 'platform' => 'Projeto 178'],
                            ['gen' => 2, 'years' => '2012-2017', 'platform' => 'Novo Palio'],
                        ],
                        'best_sellers' => ['ELX', 'Attractive', 'Fire', 'Weekend'],
                        'engines' => ['1.0 Fire', '1.4 Fire', '1.6 E.torQ', '1.8 E.torQ'],
                        'historic' => true,
                    ],
                ],
                [
                    'name' => 'Siena',
                    'slug' => 'siena',
                    'category' => 'sedan',
                    'year_start' => 1997,
                    'year_end' => 2018,
                    'is_active' => false,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '1997-2012', 'platform' => 'Projeto 178'],
                            ['gen' => 2, 'years' => '2012-2018', 'platform' => 'Grand Siena'],
                        ],
                        'best_sellers' => ['ELX', 'Attractive', 'Essence'],
                        'engines' => ['1.0 Fire', '1.4 Fire', '1.6 E.torQ'],
                        'historic' => true,
                    ],
                ],
                [
                    'name' => 'Fiorino',
                    'slug' => 'fiorino',
                    'category' => 'van',
                    'year_start' => 1987,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '1987-2013', 'platform' => 'Uno'],
                            ['gen' => 2, 'years' => '2013-atual', 'platform' => 'Novo Fiorino'],
                        ],
                        'best_sellers' => ['Hard Working', 'Endurance'],
                        'engines' => ['1.4 Fire', '1.4 Evo'],
                        'segment' => 'comercial-leve',
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // VOLKSWAGEN - 2º MAIOR VOLUME
            // ═══════════════════════════════════════════════════════════════
            'volkswagen' => [
                [
                    'name' => 'Polo',
                    'slug' => 'polo',
                    'category' => 'hatch',
                    'year_start' => 2002,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2002-2017', 'platform' => 'PQ24'],
                            ['gen' => 2, 'years' => '2017-atual', 'platform' => 'MQB'],
                        ],
                        'best_sellers' => ['Highline', 'Comfortline', 'GTS', 'Track'],
                        'engines' => ['1.0 MPI', '1.0 TSI', '1.4 TSI'],
                        'segment_position' => 2,
                    ],
                ],
                [
                    'name' => 'T-Cross',
                    'slug' => 't-cross',
                    'category' => 'suv',
                    'year_start' => 2019,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2019-atual', 'platform' => 'MQB-A0'],
                        ],
                        'best_sellers' => ['Sense', 'Comfortline', 'Highline'],
                        'engines' => ['1.0 TSI', '1.4 TSI'],
                        'segment_position' => 1,
                        'segment' => 'suv-compacto',
                    ],
                ],
                [
                    'name' => 'Virtus',
                    'slug' => 'virtus',
                    'category' => 'sedan',
                    'year_start' => 2018,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2018-atual', 'platform' => 'MQB-A0'],
                        ],
                        'best_sellers' => ['Comfortline', 'Highline', 'GTS'],
                        'engines' => ['1.0 TSI', '1.4 TSI'],
                        'segment_position' => 1,
                        'replaced' => 'Voyage',
                    ],
                ],
                [
                    'name' => 'Nivus',
                    'slug' => 'nivus',
                    'category' => 'suv',
                    'year_start' => 2020,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2020-atual', 'platform' => 'MQB-A0'],
                        ],
                        'best_sellers' => ['Comfortline', 'Highline'],
                        'engines' => ['1.0 TSI'],
                        'segment_position' => 4,
                        'segment' => 'suv-coupe',
                    ],
                ],
                [
                    'name' => 'Saveiro',
                    'slug' => 'saveiro',
                    'category' => 'pickup',
                    'year_start' => 1982,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 3,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '1982-1996', 'platform' => 'Gol G1'],
                            ['gen' => 2, 'years' => '1997-2009', 'platform' => 'Gol G2/G3/G4'],
                            ['gen' => 3, 'years' => '2010-atual', 'platform' => 'Gol G5/G6/G7'],
                        ],
                        'best_sellers' => ['Robust', 'Trendline', 'Cross'],
                        'engines' => ['1.6 MSI'],
                        'segment_position' => 2,
                    ],
                ],
                [
                    'name' => 'Gol',
                    'slug' => 'gol',
                    'category' => 'hatch',
                    'year_start' => 1980,
                    'year_end' => 2023,
                    'is_active' => false,
                    'current_generation' => 7,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '1980-1994', 'codename' => 'G1/Quadrado'],
                            ['gen' => 2, 'years' => '1994-1999', 'codename' => 'G2/Bola'],
                            ['gen' => 3, 'years' => '1999-2005', 'codename' => 'G3'],
                            ['gen' => 4, 'years' => '2005-2008', 'codename' => 'G4'],
                            ['gen' => 5, 'years' => '2008-2012', 'codename' => 'G5'],
                            ['gen' => 6, 'years' => '2012-2016', 'codename' => 'G6'],
                            ['gen' => 7, 'years' => '2016-2023', 'codename' => 'G7'],
                        ],
                        'best_sellers' => ['CL', 'GLi', 'GTi', 'Trendline', 'Track'],
                        'engines' => ['1.0 8V', '1.0 16V', '1.6 8V', '1.6 MSI', '2.0 GTi'],
                        'historic' => true,
                        'total_produced' => '8.000.000+',
                    ],
                ],
                [
                    'name' => 'Voyage',
                    'slug' => 'voyage',
                    'category' => 'sedan',
                    'year_start' => 1981,
                    'year_end' => 2023,
                    'is_active' => false,
                    'current_generation' => 4,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '1981-1996', 'platform' => 'Gol G1'],
                            ['gen' => 2, 'years' => '2008-2012', 'platform' => 'Gol G5'],
                            ['gen' => 3, 'years' => '2012-2016', 'platform' => 'Gol G6'],
                            ['gen' => 4, 'years' => '2016-2023', 'platform' => 'Gol G7'],
                        ],
                        'best_sellers' => ['Trendline', 'Comfortline'],
                        'engines' => ['1.0 MPI', '1.6 MSI'],
                        'historic' => true,
                    ],
                ],
                [
                    'name' => 'Taos',
                    'slug' => 'taos',
                    'category' => 'suv',
                    'year_start' => 2021,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2021-atual', 'platform' => 'MQB'],
                        ],
                        'best_sellers' => ['Comfortline', 'Highline', 'Launch Edition'],
                        'engines' => ['1.4 TSI'],
                        'segment_position' => 6,
                        'segment' => 'suv-medio',
                    ],
                ],
                [
                    'name' => 'Tiguan',
                    'slug' => 'tiguan',
                    'category' => 'suv',
                    'year_start' => 2009,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2009-2016', 'platform' => 'PQ46'],
                            ['gen' => 2, 'years' => '2017-atual', 'platform' => 'MQB'],
                        ],
                        'best_sellers' => ['Allspace', 'Comfortline', 'R-Line'],
                        'engines' => ['1.4 TSI', '2.0 TSI'],
                        'segment_position' => 8,
                        'segment' => 'suv-grande',
                    ],
                ],
                [
                    'name' => 'Amarok',
                    'slug' => 'amarok',
                    'category' => 'pickup',
                    'year_start' => 2010,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2010-2022', 'platform' => 'VW própria'],
                            ['gen' => 2, 'years' => '2023-atual', 'platform' => 'Ford T6.2'],
                        ],
                        'best_sellers' => ['Highline', 'Extreme', 'V6'],
                        'engines' => ['2.0 TDI', '3.0 V6 TDI'],
                        'segment_position' => 3,
                        'segment' => 'pickup-grande',
                    ],
                ],
                [
                    'name' => 'Jetta',
                    'slug' => 'jetta',
                    'category' => 'sedan',
                    'year_start' => 2006,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 3,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2006-2010', 'platform' => 'A5'],
                            ['gen' => 2, 'years' => '2011-2018', 'platform' => 'A6'],
                            ['gen' => 3, 'years' => '2019-atual', 'platform' => 'MQB'],
                        ],
                        'best_sellers' => ['Comfortline', 'R-Line', 'GLi'],
                        'engines' => ['1.4 TSI', '2.0 TSI'],
                        'segment_position' => 3,
                        'segment' => 'sedan-medio',
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // CHEVROLET (GM) - 3º MAIOR VOLUME
            // ═══════════════════════════════════════════════════════════════
            'chevrolet' => [
                [
                    'name' => 'Onix',
                    'slug' => 'onix',
                    'category' => 'hatch',
                    'year_start' => 2012,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2012-2019', 'platform' => 'Gamma II'],
                            ['gen' => 2, 'years' => '2019-atual', 'platform' => 'GEM'],
                        ],
                        'best_sellers' => ['LT', 'LTZ', 'Premier', 'RS'],
                        'engines' => ['1.0 Turbo', '1.0 Aspirado'],
                        'segment_position' => 1,
                    ],
                ],
                [
                    'name' => 'Onix Plus',
                    'slug' => 'onix-plus',
                    'category' => 'sedan',
                    'year_start' => 2019,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2019-atual', 'platform' => 'GEM'],
                        ],
                        'best_sellers' => ['LT', 'LTZ', 'Premier', 'Midnight'],
                        'engines' => ['1.0 Turbo', '1.0 Aspirado'],
                        'segment_position' => 1,
                        'replaced' => 'Prisma',
                    ],
                ],
                [
                    'name' => 'Tracker',
                    'slug' => 'tracker',
                    'category' => 'suv',
                    'year_start' => 2013,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2013-2020', 'platform' => 'Gamma II'],
                            ['gen' => 2, 'years' => '2020-atual', 'platform' => 'GEM'],
                        ],
                        'best_sellers' => ['LT', 'LTZ', 'Premier', 'Midnight', 'RS'],
                        'engines' => ['1.0 Turbo', '1.2 Turbo'],
                        'segment_position' => 3,
                        'segment' => 'suv-compacto',
                    ],
                ],
                [
                    'name' => 'S10',
                    'slug' => 's10',
                    'category' => 'pickup',
                    'year_start' => 1995,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 3,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '1995-2011', 'platform' => 'GMT325'],
                            ['gen' => 2, 'years' => '2012-2024', 'platform' => 'GMT31XX'],
                            ['gen' => 3, 'years' => '2024-atual', 'platform' => 'ISV'],
                        ],
                        'best_sellers' => ['LT', 'LTZ', 'High Country', 'Z71'],
                        'engines' => ['2.8 Turbo Diesel', '2.5 Flex'],
                        'segment_position' => 2,
                        'segment' => 'pickup-grande',
                    ],
                ],
                [
                    'name' => 'Montana',
                    'slug' => 'montana',
                    'category' => 'pickup',
                    'year_start' => 2003,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2003-2022', 'platform' => 'Gamma I/II'],
                            ['gen' => 2, 'years' => '2023-atual', 'platform' => 'GEM'],
                        ],
                        'best_sellers' => ['LT', 'LTZ', 'Premier', 'RS'],
                        'engines' => ['1.2 Turbo'],
                        'segment_position' => 3,
                        'segment' => 'pickup-compacta',
                    ],
                ],
                [
                    'name' => 'Spin',
                    'slug' => 'spin',
                    'category' => 'mpv',
                    'year_start' => 2012,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2012-atual', 'platform' => 'Gamma II'],
                        ],
                        'best_sellers' => ['LT', 'LTZ', 'Premier', 'Activ'],
                        'engines' => ['1.8 Econo'],
                        'segment_position' => 1,
                        'segment' => 'minivan',
                        'seats' => [5, 7],
                    ],
                ],
                [
                    'name' => 'Equinox',
                    'slug' => 'equinox',
                    'category' => 'suv',
                    'year_start' => 2017,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2017-2024', 'platform' => 'D2XX'],
                            ['gen' => 2, 'years' => '2024-atual', 'platform' => 'E2XX'],
                        ],
                        'best_sellers' => ['LT', 'Premier', 'RS'],
                        'engines' => ['1.5 Turbo', '2.0 Turbo'],
                        'segment_position' => 7,
                        'segment' => 'suv-medio',
                    ],
                ],
                [
                    'name' => 'Trailblazer',
                    'slug' => 'trailblazer',
                    'category' => 'suv',
                    'year_start' => 2012,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2012-2016', 'platform' => 'GMT31XX'],
                            ['gen' => 2, 'years' => '2017-atual', 'platform' => 'GMT31XX facelift'],
                        ],
                        'best_sellers' => ['LTZ', 'Premier'],
                        'engines' => ['2.8 Turbo Diesel'],
                        'segment_position' => 4,
                        'segment' => 'suv-grande',
                        'seats' => 7,
                    ],
                ],
                [
                    'name' => 'Prisma',
                    'slug' => 'prisma',
                    'category' => 'sedan',
                    'year_start' => 2006,
                    'year_end' => 2019,
                    'is_active' => false,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2006-2012', 'platform' => 'Gamma I'],
                            ['gen' => 2, 'years' => '2012-2019', 'platform' => 'Gamma II'],
                        ],
                        'best_sellers' => ['LT', 'LTZ', 'Joy'],
                        'engines' => ['1.0 VHC', '1.4 Econo'],
                        'historic' => true,
                        'replaced_by' => 'Onix Plus',
                    ],
                ],
                [
                    'name' => 'Celta',
                    'slug' => 'celta',
                    'category' => 'hatch',
                    'year_start' => 2000,
                    'year_end' => 2015,
                    'is_active' => false,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2000-2015', 'platform' => 'Gamma I'],
                        ],
                        'best_sellers' => ['Spirit', 'Life', 'LT'],
                        'engines' => ['1.0 VHC', '1.4 VHC'],
                        'historic' => true,
                        'total_produced' => '1.800.000+',
                    ],
                ],
                [
                    'name' => 'Classic',
                    'slug' => 'classic',
                    'category' => 'sedan',
                    'year_start' => 2003,
                    'year_end' => 2016,
                    'is_active' => false,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2003-2016', 'platform' => 'Corsa Sedan continuado'],
                        ],
                        'best_sellers' => ['LS', 'Spirit'],
                        'engines' => ['1.0 VHC'],
                        'historic' => true,
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // TOYOTA - 4º MAIOR VOLUME
            // ═══════════════════════════════════════════════════════════════
            'toyota' => [
                [
                    'name' => 'Corolla',
                    'slug' => 'corolla',
                    'category' => 'sedan',
                    'year_start' => 1998,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 4,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '1998-2002', 'codename' => 'E110'],
                            ['gen' => 2, 'years' => '2003-2007', 'codename' => 'E120'],
                            ['gen' => 3, 'years' => '2008-2014', 'codename' => 'E140/E150'],
                            ['gen' => 4, 'years' => '2014-2019', 'codename' => 'E170'],
                            ['gen' => 5, 'years' => '2020-atual', 'codename' => 'E210', 'platform' => 'TNGA-C'],
                        ],
                        'best_sellers' => ['GLi', 'XEi', 'Altis', 'GR-S'],
                        'engines' => ['1.8 VVT-i', '2.0 Dynamic Force', 'Híbrido'],
                        'segment_position' => 1,
                        'segment' => 'sedan-medio',
                    ],
                ],
                [
                    'name' => 'Corolla Cross',
                    'slug' => 'corolla-cross',
                    'category' => 'suv',
                    'year_start' => 2021,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2021-atual', 'platform' => 'TNGA-C'],
                        ],
                        'best_sellers' => ['XRE', 'XRX', 'GR-S', 'Híbrido'],
                        'engines' => ['2.0 Dynamic Force', 'Híbrido'],
                        'segment_position' => 4,
                        'segment' => 'suv-compacto',
                    ],
                ],
                [
                    'name' => 'Hilux',
                    'slug' => 'hilux',
                    'category' => 'pickup',
                    'year_start' => 2005,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2005-2015', 'codename' => 'AN10/AN20/AN30'],
                            ['gen' => 2, 'years' => '2016-atual', 'codename' => 'AN110/AN120/AN130'],
                        ],
                        'best_sellers' => ['STD', 'SRV', 'SRX', 'GR-S'],
                        'engines' => ['2.7 VVT-i', '2.8 Turbo Diesel'],
                        'segment_position' => 1,
                        'segment' => 'pickup-grande',
                    ],
                ],
                [
                    'name' => 'SW4',
                    'slug' => 'sw4',
                    'category' => 'suv',
                    'year_start' => 2005,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2005-2015', 'platform' => 'Hilux AN10'],
                            ['gen' => 2, 'years' => '2016-atual', 'platform' => 'Hilux AN110'],
                        ],
                        'best_sellers' => ['SRV', 'SRX', 'Diamond', 'GR-S'],
                        'engines' => ['2.7 VVT-i', '2.8 Turbo Diesel'],
                        'segment_position' => 1,
                        'segment' => 'suv-grande',
                        'seats' => 7,
                    ],
                ],
                [
                    'name' => 'Yaris',
                    'slug' => 'yaris',
                    'category' => 'hatch',
                    'year_start' => 2018,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2018-atual', 'platform' => 'TNGA-B'],
                        ],
                        'best_sellers' => ['XL', 'XS', 'XLS'],
                        'engines' => ['1.3 VVT-i', '1.5 VVT-i'],
                        'segment_position' => 5,
                        'variants' => ['Hatch', 'Sedan'],
                    ],
                ],
                [
                    'name' => 'RAV4',
                    'slug' => 'rav4',
                    'category' => 'suv',
                    'year_start' => 2013,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2013-2018', 'codename' => 'XA40'],
                            ['gen' => 2, 'years' => '2019-atual', 'codename' => 'XA50', 'platform' => 'TNGA-K'],
                        ],
                        'best_sellers' => ['S', 'SX', 'Híbrido'],
                        'engines' => ['2.5 Dynamic Force', 'Híbrido'],
                        'segment_position' => 6,
                        'segment' => 'suv-medio',
                    ],
                ],
                [
                    'name' => 'Camry',
                    'slug' => 'camry',
                    'category' => 'sedan',
                    'year_start' => 2018,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2018-atual', 'codename' => 'XV70', 'platform' => 'TNGA-K'],
                        ],
                        'best_sellers' => ['XLE', 'Híbrido'],
                        'engines' => ['3.5 V6', 'Híbrido'],
                        'segment_position' => 1,
                        'segment' => 'sedan-grande',
                    ],
                ],
                [
                    'name' => 'Etios',
                    'slug' => 'etios',
                    'category' => 'hatch',
                    'year_start' => 2012,
                    'year_end' => 2021,
                    'is_active' => false,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2012-2021', 'platform' => 'Etios'],
                        ],
                        'best_sellers' => ['X', 'XS', 'XLS', 'Platinum'],
                        'engines' => ['1.3 VVT-i', '1.5 VVT-i'],
                        'historic' => true,
                        'variants' => ['Hatch', 'Sedan'],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // HYUNDAI - 5º MAIOR VOLUME
            // ═══════════════════════════════════════════════════════════════
            'hyundai' => [
                [
                    'name' => 'HB20',
                    'slug' => 'hb20',
                    'category' => 'hatch',
                    'year_start' => 2012,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2012-2019', 'platform' => 'BA'],
                            ['gen' => 2, 'years' => '2019-atual', 'platform' => 'K2'],
                        ],
                        'best_sellers' => ['Sense', 'Vision', 'Evolution', 'Platinum', 'Sport'],
                        'engines' => ['1.0 MPI', '1.0 TGDI', '1.6 MPI'],
                        'segment_position' => 2,
                        'variants' => ['Hatch', 'HB20S (sedan)'],
                    ],
                ],
                [
                    'name' => 'HB20S',
                    'slug' => 'hb20s',
                    'category' => 'sedan',
                    'year_start' => 2013,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2013-2019', 'platform' => 'BA'],
                            ['gen' => 2, 'years' => '2019-atual', 'platform' => 'K2'],
                        ],
                        'best_sellers' => ['Vision', 'Evolution', 'Platinum'],
                        'engines' => ['1.0 MPI', '1.0 TGDI', '1.6 MPI'],
                        'segment_position' => 3,
                    ],
                ],
                [
                    'name' => 'Creta',
                    'slug' => 'creta',
                    'category' => 'suv',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2016-2021', 'platform' => 'ix25'],
                            ['gen' => 2, 'years' => '2022-atual', 'platform' => 'K2'],
                        ],
                        'best_sellers' => ['Action', 'Comfort', 'Limited', 'Platinum', 'Ultimate', 'N Line'],
                        'engines' => ['1.0 TGDI', '1.6 MPI', '2.0 MPI'],
                        'segment_position' => 5,
                        'segment' => 'suv-compacto',
                    ],
                ],
                [
                    'name' => 'Tucson',
                    'slug' => 'tucson',
                    'category' => 'suv',
                    'year_start' => 2006,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 3,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2006-2016', 'platform' => 'JM'],
                            ['gen' => 2, 'years' => '2016-2021', 'platform' => 'TL'],
                            ['gen' => 3, 'years' => '2022-atual', 'platform' => 'NX4'],
                        ],
                        'best_sellers' => ['GLS', 'Limited', 'Ultimate', 'N Line'],
                        'engines' => ['1.6 TGDI', '2.0 MPI', 'Híbrido'],
                        'segment_position' => 5,
                        'segment' => 'suv-medio',
                    ],
                ],
                [
                    'name' => 'Santa Fe',
                    'slug' => 'santa-fe',
                    'category' => 'suv',
                    'year_start' => 2006,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 3,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2006-2012', 'codename' => 'CM'],
                            ['gen' => 2, 'years' => '2013-2018', 'codename' => 'DM'],
                            ['gen' => 3, 'years' => '2019-atual', 'codename' => 'TM'],
                        ],
                        'best_sellers' => ['GLS', 'Limited'],
                        'engines' => ['3.5 V6', '2.2 CRDi', 'Híbrido'],
                        'segment_position' => 3,
                        'segment' => 'suv-grande',
                        'seats' => 7,
                    ],
                ],
                [
                    'name' => 'i30',
                    'slug' => 'i30',
                    'category' => 'hatch',
                    'year_start' => 2009,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2009-2012', 'codename' => 'FD'],
                            ['gen' => 2, 'years' => '2013-atual', 'codename' => 'PD'],
                        ],
                        'best_sellers' => ['GLS', 'N Line'],
                        'engines' => ['2.0 MPI', '2.0 TGDI N'],
                        'segment_position' => 5,
                        'segment' => 'hatch-medio',
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // JEEP - 6º MAIOR VOLUME (SUVs)
            // ═══════════════════════════════════════════════════════════════
            'jeep' => [
                [
                    'name' => 'Compass',
                    'slug' => 'compass',
                    'category' => 'suv',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2016-2021', 'platform' => 'Small Wide'],
                            ['gen' => 2, 'years' => '2022-atual', 'platform' => 'Small Wide facelift'],
                        ],
                        'best_sellers' => ['Sport', 'Longitude', 'Limited', 'Trailhawk', 'S', '80 Anos'],
                        'engines' => ['1.3 Turbo', '2.0 Turbo Diesel'],
                        'segment_position' => 1,
                        'segment' => 'suv-compacto-premium',
                    ],
                ],
                [
                    'name' => 'Renegade',
                    'slug' => 'renegade',
                    'category' => 'suv',
                    'year_start' => 2015,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2015-atual', 'platform' => 'Small Wide'],
                        ],
                        'best_sellers' => ['Sport', 'Longitude', 'Limited', 'Trailhawk', 'Sahara', '80 Anos'],
                        'engines' => ['1.3 Turbo', '2.0 Turbo Diesel'],
                        'segment_position' => 6,
                        'segment' => 'suv-compacto',
                    ],
                ],
                [
                    'name' => 'Commander',
                    'slug' => 'commander',
                    'category' => 'suv',
                    'year_start' => 2021,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2021-atual', 'platform' => 'Small Wide alongado'],
                        ],
                        'best_sellers' => ['Limited', 'Overland', 'Blackhawk'],
                        'engines' => ['1.3 Turbo', '2.0 Turbo Diesel'],
                        'segment_position' => 2,
                        'segment' => 'suv-medio',
                        'seats' => 7,
                    ],
                ],
                [
                    'name' => 'Wrangler',
                    'slug' => 'wrangler',
                    'category' => 'suv',
                    'year_start' => 2018,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2018-atual', 'codename' => 'JL'],
                        ],
                        'best_sellers' => ['Sport', 'Sahara', 'Rubicon', 'Willys'],
                        'engines' => ['2.0 Turbo'],
                        'segment_position' => 1,
                        'segment' => 'off-road',
                        'iconic' => true,
                    ],
                ],
                [
                    'name' => 'Grand Cherokee',
                    'slug' => 'grand-cherokee',
                    'category' => 'suv',
                    'year_start' => 2022,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2022-atual', 'codename' => 'WL'],
                        ],
                        'best_sellers' => ['Limited', 'Overland', 'Summit'],
                        'engines' => ['2.0 Turbo', 'Híbrido 4xe'],
                        'segment_position' => 3,
                        'segment' => 'suv-grande-premium',
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // HONDA - 8º MAIOR VOLUME
            // ═══════════════════════════════════════════════════════════════
            'honda' => [
                [
                    'name' => 'HR-V',
                    'slug' => 'hr-v',
                    'category' => 'suv',
                    'year_start' => 2015,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2015-2022', 'codename' => 'RU'],
                            ['gen' => 2, 'years' => '2023-atual', 'codename' => 'RV'],
                        ],
                        'best_sellers' => ['EX', 'EXL', 'Touring', 'Advance'],
                        'engines' => ['1.5 i-VTEC', '1.5 Turbo'],
                        'segment_position' => 8,
                        'segment' => 'suv-compacto',
                    ],
                ],
                [
                    'name' => 'City',
                    'slug' => 'city',
                    'category' => 'sedan',
                    'year_start' => 2009,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2009-2020', 'codename' => 'GM'],
                            ['gen' => 2, 'years' => '2021-atual', 'codename' => 'GN'],
                        ],
                        'best_sellers' => ['LX', 'EX', 'EXL', 'Touring'],
                        'engines' => ['1.5 i-VTEC'],
                        'segment_position' => 4,
                        'variants' => ['Sedan', 'Hatchback'],
                    ],
                ],
                [
                    'name' => 'Civic',
                    'slug' => 'civic',
                    'category' => 'sedan',
                    'year_start' => 1997,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 4,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '1997-2000', 'codename' => 'EK'],
                            ['gen' => 2, 'years' => '2001-2006', 'codename' => 'ES/EM'],
                            ['gen' => 3, 'years' => '2006-2011', 'codename' => 'FA/FG'],
                            ['gen' => 4, 'years' => '2012-2016', 'codename' => 'FB'],
                            ['gen' => 5, 'years' => '2017-2021', 'codename' => 'FC/FK'],
                            ['gen' => 6, 'years' => '2022-atual', 'codename' => 'FE/FL'],
                        ],
                        'best_sellers' => ['LX', 'EX', 'EXL', 'Touring', 'Si', 'Type R'],
                        'engines' => ['1.5 Turbo', '2.0 i-VTEC'],
                        'segment_position' => 2,
                        'segment' => 'sedan-medio',
                    ],
                ],
                [
                    'name' => 'Fit',
                    'slug' => 'fit',
                    'category' => 'hatch',
                    'year_start' => 2003,
                    'year_end' => 2021,
                    'is_active' => false,
                    'current_generation' => 3,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2003-2008', 'codename' => 'GD'],
                            ['gen' => 2, 'years' => '2009-2014', 'codename' => 'GE'],
                            ['gen' => 3, 'years' => '2015-2021', 'codename' => 'GK'],
                        ],
                        'best_sellers' => ['LX', 'EX', 'EXL', 'Personal'],
                        'engines' => ['1.5 i-VTEC'],
                        'historic' => true,
                    ],
                ],
                [
                    'name' => 'WR-V',
                    'slug' => 'wr-v',
                    'category' => 'suv',
                    'year_start' => 2017,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2017-2023', 'platform' => 'Fit/City'],
                            ['gen' => 2, 'years' => '2024-atual', 'platform' => 'N7X'],
                        ],
                        'best_sellers' => ['LX', 'EX', 'EXL', 'Touring'],
                        'engines' => ['1.5 i-VTEC'],
                        'segment_position' => 9,
                        'segment' => 'suv-entry',
                    ],
                ],
                [
                    'name' => 'ZR-V',
                    'slug' => 'zr-v',
                    'category' => 'suv',
                    'year_start' => 2024,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2024-atual', 'platform' => 'Civic'],
                        ],
                        'best_sellers' => ['EXL', 'Touring'],
                        'engines' => ['2.0 i-VTEC'],
                        'segment_position' => 7,
                        'segment' => 'suv-medio',
                    ],
                ],
                [
                    'name' => 'CR-V',
                    'slug' => 'cr-v',
                    'category' => 'suv',
                    'year_start' => 2007,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 3,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2007-2011', 'codename' => 'RE'],
                            ['gen' => 2, 'years' => '2012-2016', 'codename' => 'RM'],
                            ['gen' => 3, 'years' => '2017-atual', 'codename' => 'RW'],
                        ],
                        'best_sellers' => ['EX', 'EXL', 'Touring'],
                        'engines' => ['1.5 Turbo', '2.0 i-VTEC', 'Híbrido'],
                        'segment_position' => 5,
                        'segment' => 'suv-medio-grande',
                    ],
                ],
                [
                    'name' => 'Accord',
                    'slug' => 'accord',
                    'category' => 'sedan',
                    'year_start' => 2008,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2008-2017', 'codename' => 'CP/CU'],
                            ['gen' => 2, 'years' => '2018-atual', 'codename' => 'CV'],
                        ],
                        'best_sellers' => ['EX', 'EXL', 'Touring'],
                        'engines' => ['2.0 Turbo', 'Híbrido'],
                        'segment_position' => 2,
                        'segment' => 'sedan-grande',
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // RENAULT - 7º MAIOR VOLUME
            // ═══════════════════════════════════════════════════════════════
            'renault' => [
                [
                    'name' => 'Kwid',
                    'slug' => 'kwid',
                    'category' => 'hatch',
                    'year_start' => 2017,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2017-atual', 'platform' => 'CMF-A'],
                        ],
                        'best_sellers' => ['Zen', 'Intense', 'Outsider'],
                        'engines' => ['1.0 SCe'],
                        'segment_position' => 2,
                        'segment' => 'entry-level',
                    ],
                ],
                [
                    'name' => 'Sandero',
                    'slug' => 'sandero',
                    'category' => 'hatch',
                    'year_start' => 2007,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2007-2014', 'platform' => 'B0'],
                            ['gen' => 2, 'years' => '2014-atual', 'platform' => 'B0+'],
                        ],
                        'best_sellers' => ['Zen', 'Intense', 'R.S.'],
                        'engines' => ['1.0 SCe', '1.6 SCe'],
                        'segment_position' => 6,
                    ],
                ],
                [
                    'name' => 'Logan',
                    'slug' => 'logan',
                    'category' => 'sedan',
                    'year_start' => 2007,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2007-2014', 'platform' => 'B0'],
                            ['gen' => 2, 'years' => '2014-atual', 'platform' => 'B0+'],
                        ],
                        'best_sellers' => ['Zen', 'Intense'],
                        'engines' => ['1.0 SCe', '1.6 SCe'],
                        'segment_position' => 5,
                    ],
                ],
                [
                    'name' => 'Duster',
                    'slug' => 'duster',
                    'category' => 'suv',
                    'year_start' => 2011,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2011-2020', 'platform' => 'B0'],
                            ['gen' => 2, 'years' => '2020-atual', 'platform' => 'CMF-B'],
                        ],
                        'best_sellers' => ['Zen', 'Intense', 'Iconic'],
                        'engines' => ['1.3 TCe', '1.6 SCe'],
                        'segment_position' => 10,
                        'segment' => 'suv-compacto',
                    ],
                ],
                [
                    'name' => 'Captur',
                    'slug' => 'captur',
                    'category' => 'suv',
                    'year_start' => 2017,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2017-2022', 'platform' => 'B0'],
                            ['gen' => 2, 'years' => '2022-atual', 'platform' => 'CMF-B'],
                        ],
                        'best_sellers' => ['Zen', 'Intense', 'Iconic'],
                        'engines' => ['1.3 TCe', '1.6 SCe'],
                        'segment_position' => 11,
                        'segment' => 'suv-compacto',
                    ],
                ],
                [
                    'name' => 'Oroch',
                    'slug' => 'oroch',
                    'category' => 'pickup',
                    'year_start' => 2015,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2015-2023', 'platform' => 'B0'],
                            ['gen' => 2, 'years' => '2023-atual', 'platform' => 'CMF-B'],
                        ],
                        'best_sellers' => ['Zen', 'Intense', 'Outsider'],
                        'engines' => ['1.3 TCe', '1.6 SCe'],
                        'segment_position' => 4,
                        'segment' => 'pickup-compacta',
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // NISSAN - 9º MAIOR VOLUME
            // ═══════════════════════════════════════════════════════════════
            'nissan' => [
                [
                    'name' => 'Kicks',
                    'slug' => 'kicks',
                    'category' => 'suv',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2016-2022', 'platform' => 'V'],
                            ['gen' => 2, 'years' => '2022-atual', 'platform' => 'CMF-B'],
                        ],
                        'best_sellers' => ['Active', 'Sense', 'Advance', 'Exclusive', 'e-Power'],
                        'engines' => ['1.6 16V', 'e-Power'],
                        'segment_position' => 7,
                        'segment' => 'suv-compacto',
                    ],
                ],
                [
                    'name' => 'Versa',
                    'slug' => 'versa',
                    'category' => 'sedan',
                    'year_start' => 2011,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 3,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2011-2014', 'codename' => 'N17'],
                            ['gen' => 2, 'years' => '2015-2019', 'codename' => 'N17 facelift'],
                            ['gen' => 3, 'years' => '2020-atual', 'codename' => 'N18'],
                        ],
                        'best_sellers' => ['Sense', 'Advance', 'Exclusive'],
                        'engines' => ['1.6 16V'],
                        'segment_position' => 6,
                    ],
                ],
                [
                    'name' => 'Sentra',
                    'slug' => 'sentra',
                    'category' => 'sedan',
                    'year_start' => 2007,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2007-2013', 'codename' => 'B16'],
                            ['gen' => 2, 'years' => '2013-atual', 'codename' => 'B17/B18'],
                        ],
                        'best_sellers' => ['S', 'SV', 'SR'],
                        'engines' => ['2.0 16V'],
                        'segment_position' => 4,
                        'segment' => 'sedan-medio',
                    ],
                ],
                [
                    'name' => 'Frontier',
                    'slug' => 'frontier',
                    'category' => 'pickup',
                    'year_start' => 2002,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 3,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2002-2007', 'codename' => 'D22'],
                            ['gen' => 2, 'years' => '2008-2016', 'codename' => 'D40'],
                            ['gen' => 3, 'years' => '2017-atual', 'codename' => 'D23'],
                        ],
                        'best_sellers' => ['S', 'SE', 'LE', 'Attack', 'X-Gear'],
                        'engines' => ['2.3 Turbo Diesel'],
                        'segment_position' => 4,
                        'segment' => 'pickup-grande',
                    ],
                ],
                [
                    'name' => 'March',
                    'slug' => 'march',
                    'category' => 'hatch',
                    'year_start' => 2011,
                    'year_end' => 2021,
                    'is_active' => false,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2011-2021', 'codename' => 'K13'],
                        ],
                        'best_sellers' => ['S', 'SV', 'SL'],
                        'engines' => ['1.0 12V', '1.6 16V'],
                        'historic' => true,
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // RAM - PICKUPS GRANDES
            // ═══════════════════════════════════════════════════════════════
            'ram' => [
                [
                    'name' => 'Rampage',
                    'slug' => 'rampage',
                    'category' => 'pickup',
                    'year_start' => 2023,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2023-atual', 'platform' => 'Small Wide'],
                        ],
                        'best_sellers' => ['Laramie', 'Rebel', 'R/T'],
                        'engines' => ['2.0 Turbo', '2.0 Turbo Diesel'],
                        'segment_position' => 2,
                        'segment' => 'pickup-media',
                    ],
                ],
                [
                    'name' => '1500',
                    'slug' => '1500',
                    'category' => 'pickup',
                    'year_start' => 2019,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2019-atual', 'codename' => 'DT'],
                        ],
                        'best_sellers' => ['Laramie', 'Limited', 'Rebel'],
                        'engines' => ['5.7 V8 HEMI'],
                        'segment_position' => 1,
                        'segment' => 'pickup-full-size',
                    ],
                ],
                [
                    'name' => '2500',
                    'slug' => '2500',
                    'category' => 'pickup',
                    'year_start' => 2012,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'generations' => [
                            ['gen' => 1, 'years' => '2012-atual', 'codename' => 'DJ'],
                        ],
                        'best_sellers' => ['Laramie', 'Limited'],
                        'engines' => ['6.7 Cummins Turbo Diesel'],
                        'segment_position' => 1,
                        'segment' => 'pickup-heavy-duty',
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MOTOS - HONDA MOTOS
            // ═══════════════════════════════════════════════════════════════
            'honda-motos' => [
                [
                    'name' => 'CG 160',
                    'slug' => 'cg-160',
                    'category' => 'street',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'versions' => ['Start', 'Fan', 'Titan', 'Cargo'],
                        'engines' => ['160cc OHC'],
                        'segment_position' => 1,
                        'segment' => 'street-entrada',
                    ],
                ],
                [
                    'name' => 'Biz 125',
                    'slug' => 'biz-125',
                    'category' => 'scooter',
                    'year_start' => 2005,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'versions' => ['ES', 'EX'],
                        'engines' => ['125cc OHC'],
                        'segment_position' => 1,
                        'segment' => 'underbone',
                    ],
                ],
                [
                    'name' => 'Pop 110i',
                    'slug' => 'pop-110i',
                    'category' => 'scooter',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'versions' => [''],
                        'engines' => ['110cc OHC'],
                        'segment_position' => 1,
                        'segment' => 'entry-scooter',
                    ],
                ],
                [
                    'name' => 'CB 500F',
                    'slug' => 'cb-500f',
                    'category' => 'naked',
                    'year_start' => 2013,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'versions' => ['ABS'],
                        'engines' => ['500cc bicilíndrico'],
                        'segment_position' => 1,
                        'segment' => 'naked-media',
                    ],
                ],
                [
                    'name' => 'CB 650R',
                    'slug' => 'cb-650r',
                    'category' => 'naked',
                    'year_start' => 2019,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'versions' => ['ABS'],
                        'engines' => ['649cc 4 cilindros'],
                        'segment_position' => 1,
                        'segment' => 'neo-retro',
                    ],
                ],
                [
                    'name' => 'PCX 160',
                    'slug' => 'pcx-160',
                    'category' => 'scooter',
                    'year_start' => 2021,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'versions' => ['DLX', 'Sport'],
                        'engines' => ['160cc eSP+'],
                        'segment_position' => 1,
                        'segment' => 'scooter-premium',
                    ],
                ],
                [
                    'name' => 'XRE 300',
                    'slug' => 'xre-300',
                    'category' => 'trail',
                    'year_start' => 2009,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'versions' => ['ABS', 'Adventure'],
                        'engines' => ['291cc OHC'],
                        'segment_position' => 1,
                        'segment' => 'trail-média',
                    ],
                ],
                [
                    'name' => 'Africa Twin',
                    'slug' => 'africa-twin',
                    'category' => 'adventure',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'versions' => ['CRF1100L', 'Adventure Sports'],
                        'engines' => ['1084cc bicilíndrico'],
                        'segment_position' => 1,
                        'segment' => 'big-trail',
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // YAMAHA - MOTOS
            // ═══════════════════════════════════════════════════════════════
            'yamaha' => [
                [
                    'name' => 'Factor 150',
                    'slug' => 'factor-150',
                    'category' => 'street',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'versions' => ['ED', 'UBS'],
                        'engines' => ['150cc Blue Core'],
                        'segment_position' => 2,
                    ],
                ],
                [
                    'name' => 'Fazer 250',
                    'slug' => 'fazer-250',
                    'category' => 'street',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'versions' => ['ABS'],
                        'engines' => ['249cc Blue Core'],
                        'segment_position' => 1,
                        'segment' => 'street-media',
                    ],
                ],
                [
                    'name' => 'MT-03',
                    'slug' => 'mt-03',
                    'category' => 'naked',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'versions' => ['ABS'],
                        'engines' => ['321cc bicilíndrico'],
                        'segment_position' => 2,
                    ],
                ],
                [
                    'name' => 'MT-07',
                    'slug' => 'mt-07',
                    'category' => 'naked',
                    'year_start' => 2015,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'versions' => ['ABS'],
                        'engines' => ['689cc bicilíndrico CP2'],
                        'segment_position' => 2,
                        'segment' => 'naked-media',
                    ],
                ],
                [
                    'name' => 'MT-09',
                    'slug' => 'mt-09',
                    'category' => 'naked',
                    'year_start' => 2015,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'versions' => ['ABS', 'SP'],
                        'engines' => ['889cc 3 cilindros CP3'],
                        'segment_position' => 1,
                        'segment' => 'hyper-naked',
                    ],
                ],
                [
                    'name' => 'XTZ 250 Lander',
                    'slug' => 'xtz-250-lander',
                    'category' => 'trail',
                    'year_start' => 2006,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'versions' => ['ABS'],
                        'engines' => ['249cc SOHC'],
                        'segment_position' => 2,
                    ],
                ],
                [
                    'name' => 'Ténéré 700',
                    'slug' => 'tenere-700',
                    'category' => 'adventure',
                    'year_start' => 2020,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 1,
                    'metadata' => [
                        'versions' => ['ABS'],
                        'engines' => ['689cc bicilíndrico CP2'],
                        'segment_position' => 2,
                        'segment' => 'middle-weight-adventure',
                    ],
                ],
                [
                    'name' => 'NMAX 160',
                    'slug' => 'nmax-160',
                    'category' => 'scooter',
                    'year_start' => 2016,
                    'year_end' => null,
                    'is_active' => true,
                    'current_generation' => 2,
                    'metadata' => [
                        'versions' => ['ABS', 'Connected'],
                        'engines' => ['155cc Blue Core'],
                        'segment_position' => 2,
                        'segment' => 'scooter-premium',
                    ],
                ],
            ],
        ];
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;

/**
 * Seeder de marcas, modelos e versões de veículos
 * 
 * Baseado nas 12 marcas identificadas nos mockups HTML
 */
class VehicleMakesAndModelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $makes = $this->getMakesData();

        foreach ($makes as $makeData) {
            // Criar ou atualizar marca
            $make = VehicleMake::updateOrCreate(
                ['slug' => Str::slug($makeData['name'])],
                [
                    'name' => $makeData['name'],
                    'slug' => Str::slug($makeData['name']),
                    'country_origin' => $makeData['country'],
                    'type' => $makeData['type'],
                    'is_active' => true
                ]
            );

            // Criar modelos
            if (isset($makeData['models'])) {
                foreach ($makeData['models'] as $modelData) {
                    $model = VehicleModel::updateOrCreate(
                        [
                            'make_id' => $make->id,
                            'slug' => Str::slug($modelData['name'])
                        ],
                        [
                            'make_id' => $make->id,
                            'name' => $modelData['name'],
                            'slug' => Str::slug($modelData['name']),
                            'category' => $modelData['category'],
                            'year_start' => $modelData['year_start'] ?? null,
                            'year_end' => $modelData['year_end'] ?? null,
                            'is_active' => true
                        ]
                    );

                    // Criar versões de exemplo
                    if (isset($modelData['versions'])) {
                        foreach ($modelData['versions'] as $versionData) {
                            VehicleVersion::updateOrCreate(
                                [
                                    'model_id' => $model->id,
                                    'slug' => Str::slug($versionData['name']),
                                    'year' => $versionData['year']
                                ],
                                [
                                    'model_id' => $model->id,
                                    'name' => $versionData['name'],
                                    'slug' => Str::slug($versionData['name']),
                                    'year' => $versionData['year'],
                                    'engine_code' => $versionData['engine_code'] ?? null,
                                    'fuel_type' => $versionData['fuel_type'] ?? 'flex',
                                    'transmission' => $versionData['transmission'] ?? 'manual',
                                    'is_active' => true
                                ]
                            );
                        }
                    }
                }
            }
        }

        $this->command->info('✓ Marcas, modelos e versões criados com sucesso!');
        $this->command->info('  → 12 marcas identificadas nos mocks');
    }

    /**
     * Dados das marcas extraídos dos mockups
     */
    private function getMakesData(): array
    {
        return [
            // 1. Toyota (destaque nos mocks)
            [
                'name' => 'Toyota',
                'country' => 'JP',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Corolla',
                        'category' => 'sedan',
                        'year_start' => 1966,
                        'versions' => [
                            ['name' => 'GLi 1.8', 'year' => 2003, 'engine_code' => '1ZZ-FE', 'fuel_type' => 'gasoline'],
                            ['name' => 'XEi 2.0', 'year' => 2023, 'fuel_type' => 'flex'],
                        ],
                    ],
                    [
                        'name' => 'Hilux',
                        'category' => 'pickup',
                        'year_start' => 1968,
                        'versions' => [
                            ['name' => '2.8 Diesel 4x4', 'year' => 2015, 'fuel_type' => 'diesel'],
                        ],
                    ],
                    [
                        'name' => 'Yaris',
                        'category' => 'hatch',
                        'year_start' => 2018,
                        'versions' => [
                            ['name' => '1.5 XLS', 'year' => 2019, 'fuel_type' => 'flex'],
                        ],
                    ],
                ],
            ],

            // 2. Honda
            [
                'name' => 'Honda',
                'country' => 'JP',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Civic',
                        'category' => 'sedan',
                        'year_start' => 1992,
                        'versions' => [
                            ['name' => '2.0 Sport', 'year' => 2017, 'fuel_type' => 'gasoline'],
                        ],
                    ],
                    [
                        'name' => 'HR-V',
                        'category' => 'suv',
                        'year_start' => 2015,
                        'versions' => [
                            ['name' => 'EX 1.8', 'year' => 2022, 'fuel_type' => 'flex'],
                        ],
                    ],
                ],
            ],

            // 3. Chevrolet
            [
                'name' => 'Chevrolet',
                'country' => 'US',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Onix',
                        'category' => 'hatch',
                        'year_start' => 2012,
                        'versions' => [
                            ['name' => '1.0 Turbo', 'year' => 2020, 'fuel_type' => 'gasoline'],
                        ],
                    ],
                    [
                        'name' => 'S10',
                        'category' => 'pickup',
                        'year_start' => 1995,
                        'versions' => [
                            ['name' => '2.8 Diesel 4x4', 'year' => 2022, 'fuel_type' => 'diesel'],
                        ],
                    ],
                ],
            ],

            // 4. Volkswagen
            [
                'name' => 'Volkswagen',
                'country' => 'DE',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Gol',
                        'category' => 'hatch',
                        'year_start' => 1980,
                        'versions' => [
                            ['name' => '1.6 Total Flex', 'year' => 2016, 'fuel_type' => 'flex'],
                        ],
                    ],
                    [
                        'name' => 'Polo',
                        'category' => 'hatch',
                        'year_start' => 2002,
                        'versions' => [
                            ['name' => '1.0 TSI', 'year' => 2023, 'fuel_type' => 'gasoline'],
                        ],
                    ],
                ],
            ],

            // 5. Fiat
            [
                'name' => 'Fiat',
                'country' => 'IT',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Uno',
                        'category' => 'hatch',
                        'year_start' => 1984,
                        'versions' => [
                            ['name' => '1.0 Fire', 'year' => 2010, 'fuel_type' => 'flex'],
                        ],
                    ],
                    [
                        'name' => 'Argo',
                        'category' => 'hatch',
                        'year_start' => 2017,
                        'versions' => [
                            ['name' => '1.3', 'year' => 2023, 'fuel_type' => 'flex'],
                        ],
                    ],
                ],
            ],

            // 6. Hyundai
            [
                'name' => 'Hyundai',
                'country' => 'KR',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'HB20',
                        'category' => 'hatch',
                        'year_start' => 2012,
                        'versions' => [
                            ['name' => '1.0 Turbo', 'year' => 2022, 'fuel_type' => 'gasoline'],
                        ],
                    ],
                    [
                        'name' => 'Creta',
                        'category' => 'suv',
                        'year_start' => 2016,
                        'versions' => [
                            ['name' => '2.0 Prestige', 'year' => 2023, 'fuel_type' => 'flex'],
                        ],
                    ],
                ],
            ],

            // 7. Ford
            [
                'name' => 'Ford',
                'country' => 'US',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Ranger',
                        'category' => 'pickup',
                        'year_start' => 1998,
                        'versions' => [
                            ['name' => '3.2 Diesel 4x4', 'year' => 2020, 'fuel_type' => 'diesel'],
                        ],
                    ],
                ],
            ],

            // 8. Renault
            [
                'name' => 'Renault',
                'country' => 'FR',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Kwid',
                        'category' => 'hatch',
                        'year_start' => 2017,
                        'versions' => [
                            ['name' => '1.0', 'year' => 2022, 'fuel_type' => 'flex'],
                        ],
                    ],
                ],
            ],

            // 9. Nissan
            [
                'name' => 'Nissan',
                'country' => 'JP',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Kicks',
                        'category' => 'suv',
                        'year_start' => 2016,
                        'versions' => [
                            ['name' => '1.6 SL', 'year' => 2022, 'fuel_type' => 'flex'],
                        ],
                    ],
                ],
            ],

            // 10. Jeep
            [
                'name' => 'Jeep',
                'country' => 'US',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Compass',
                        'category' => 'suv',
                        'year_start' => 2017,
                        'versions' => [
                            ['name' => '2.0 Turbo Diesel', 'year' => 2022, 'fuel_type' => 'diesel'],
                        ],
                    ],
                    [
                        'name' => 'Renegade',
                        'category' => 'suv',
                        'year_start' => 2015,
                        'versions' => [
                            ['name' => '1.8 Flex', 'year' => 2022, 'fuel_type' => 'flex'],
                        ],
                    ],
                ],
            ],

            // 11. BMW
            [
                'name' => 'BMW',
                'country' => 'DE',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'X5',
                        'category' => 'suv',
                        'year_start' => 1999,
                        'versions' => [
                            ['name' => 'xDrive40i', 'year' => 2023, 'fuel_type' => 'gasoline'],
                        ],
                    ],
                    [
                        'name' => '320i',
                        'category' => 'sedan',
                        'year_start' => 2012,
                        'versions' => [
                            ['name' => 'Sport GP', 'year' => 2023, 'fuel_type' => 'gasoline'],
                        ],
                    ],
                ],
            ],

            // 12. Mercedes-Benz
            [
                'name' => 'Mercedes-Benz',
                'country' => 'DE',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Classe A',
                        'category' => 'sedan',
                        'year_start' => 2013,
                        'versions' => [
                            ['name' => 'A 200', 'year' => 2023, 'fuel_type' => 'gasoline'],
                        ],
                    ],
                ],
            ],
        ];
    }
}

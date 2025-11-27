<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;

class VehicleMakesAndModelsSeeder extends Seeder
{
    public function run(): void
    {
        $makes = $this->getMakesData();

        foreach ($makes as $makeData) {
            $make = VehicleMake::create([
                'name' => $makeData['name'],
                'slug' => Str::slug($makeData['name']),
                'country_origin' => $makeData['country'],
                'type' => $makeData['type'],
                'is_active' => true
            ]);

            if (isset($makeData['models'])) {
                foreach ($makeData['models'] as $modelData) {
                    $model = VehicleModel::create([
                        'make_id' => $make->id,
                        'name' => $modelData['name'],
                        'slug' => Str::slug($modelData['name']),
                        'category' => $modelData['category'],
                        'year_start' => $modelData['year_start'] ?? null,
                        'year_end' => $modelData['year_end'] ?? null,
                        'is_active' => true
                    ]);

                    // Create sample versions
                    if (isset($modelData['sample_years'])) {
                        foreach ($modelData['sample_years'] as $year) {
                            VehicleVersion::create([
                                'model_id' => $model->id,
                                'name' => '1.0 Flex',
                                'slug' => Str::slug('1.0 Flex'),
                                'year' => $year,
                                'fuel_type' => 'flex',
                                'transmission' => 'manual',
                                'is_active' => true
                            ]);
                        }
                    }
                }
            }
        }
    }

    private function getMakesData(): array
    {
        return [
            [
                'name' => 'Toyota',
                'country' => 'JP',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Corolla',
                        'category' => 'sedan',
                        'year_start' => 1990,
                        'sample_years' => [2020, 2021, 2022, 2023, 2024]
                    ],
                    [
                        'name' => 'Hilux',
                        'category' => 'pickup',
                        'year_start' => 2005,
                        'sample_years' => [2022, 2023, 2024]
                    ],
                    [
                        'name' => 'Corolla Cross',
                        'category' => 'suv',
                        'year_start' => 2021,
                        'sample_years' => [2022, 2023, 2024]
                    ]
                ]
            ],
            [
                'name' => 'Volkswagen',
                'country' => 'DE',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Gol',
                        'category' => 'hatch',
                        'year_start' => 1980,
                        'sample_years' => [2020, 2021, 2022]
                    ],
                    [
                        'name' => 'Polo',
                        'category' => 'hatch',
                        'year_start' => 2002,
                        'sample_years' => [2022, 2023, 2024]
                    ],
                    [
                        'name' => 'T-Cross',
                        'category' => 'suv',
                        'year_start' => 2019,
                        'sample_years' => [2022, 2023, 2024]
                    ]
                ]
            ],
            [
                'name' => 'Fiat',
                'country' => 'IT',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Uno',
                        'category' => 'hatch',
                        'year_start' => 1984,
                        'sample_years' => [2020, 2021, 2022]
                    ],
                    [
                        'name' => 'Argo',
                        'category' => 'hatch',
                        'year_start' => 2017,
                        'sample_years' => [2022, 2023, 2024]
                    ],
                    [
                        'name' => 'Toro',
                        'category' => 'pickup',
                        'year_start' => 2016,
                        'sample_years' => [2022, 2023, 2024]
                    ]
                ]
            ],
            [
                'name' => 'Chevrolet',
                'country' => 'US',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Onix',
                        'category' => 'hatch',
                        'year_start' => 2012,
                        'sample_years' => [2022, 2023, 2024]
                    ],
                    [
                        'name' => 'S10',
                        'category' => 'pickup',
                        'year_start' => 1995,
                        'sample_years' => [2022, 2023, 2024]
                    ]
                ]
            ],
            [
                'name' => 'Honda',
                'country' => 'JP',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Civic',
                        'category' => 'sedan',
                        'year_start' => 1996,
                        'sample_years' => [2022, 2023, 2024]
                    ],
                    [
                        'name' => 'HR-V',
                        'category' => 'suv',
                        'year_start' => 2015,
                        'sample_years' => [2022, 2023, 2024]
                    ]
                ]
            ],
            [
                'name' => 'Yamaha',
                'country' => 'JP',
                'type' => 'motorcycle',
                'models' => [
                    [
                        'name' => 'YBR 125',
                        'category' => 'motorcycle',
                        'year_start' => 2000,
                        'sample_years' => [2022, 2023, 2024]
                    ],
                    [
                        'name' => 'MT-07',
                        'category' => 'motorcycle',
                        'year_start' => 2014,
                        'sample_years' => [2022, 2023, 2024]
                    ]
                ]
            ],
            [
                'name' => 'BMW',
                'country' => 'DE',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'X5',
                        'category' => 'suv',
                        'year_start' => 1999,
                        'sample_years' => [2022, 2023, 2024]
                    ],
                    [
                        'name' => '320i',
                        'category' => 'sedan',
                        'year_start' => 2012,
                        'sample_years' => [2022, 2023, 2024]
                    ]
                ]
            ],
            [
                'name' => 'Mercedes-Benz',
                'country' => 'DE',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Classe A',
                        'category' => 'sedan',
                        'year_start' => 2013,
                        'sample_years' => [2022, 2023, 2024]
                    ]
                ]
            ],
            [
                'name' => 'Hyundai',
                'country' => 'KR',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'HB20',
                        'category' => 'hatch',
                        'year_start' => 2012,
                        'sample_years' => [2022, 2023, 2024]
                    ],
                    [
                        'name' => 'Creta',
                        'category' => 'suv',
                        'year_start' => 2016,
                        'sample_years' => [2022, 2023, 2024]
                    ]
                ]
            ],
            [
                'name' => 'Jeep',
                'country' => 'US',
                'type' => 'car',
                'models' => [
                    [
                        'name' => 'Compass',
                        'category' => 'suv',
                        'year_start' => 2017,
                        'sample_years' => [2022, 2023, 2024]
                    ],
                    [
                        'name' => 'Renegade',
                        'category' => 'suv',
                        'year_start' => 2015,
                        'sample_years' => [2022, 2023, 2024]
                    ]
                ]
            ]
        ];
    }
}

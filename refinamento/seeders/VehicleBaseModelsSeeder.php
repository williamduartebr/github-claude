<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;

class VehicleBaseModelsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->models() as $model) {
            VehicleModel::updateOrCreate(
                [
                    'id' => $model['id'],  // IDs fixos para referência futura
                ],
                [
                    'make_id' => $model['make_id'],
                    'name' => $model['name'],
                    'slug' => Str::slug($model['name']),
                    'category' => $model['category'],
                    'year_start' => $model['year_start'],
                    'year_end' => $model['year_end'] ?? null,
                    'is_active' => $model['is_active'],
                    'image_url' => Str::slug($model['name']).'.jpg', // futuro S3
                ]
            );
        }

        $this->command->info('✓ Modelos base cadastrados. Prontos para ingestão via API Claude.');
    }

    private function models(): array
    {
        return [

            // =======================
            // Automóveis principais
            // =======================

            ['id' => 1, 'make_id' => 1, 'name' => 'Onix', 'category' => 'hatch', 'year_start' => 2012, 'is_active' => true],
            ['id' => 2, 'make_id' => 2, 'name' => 'Gol', 'category' => 'hatch', 'year_start' => 1980, 'is_active' => true],
            ['id' => 3, 'make_id' => 3, 'name' => 'Argo', 'category' => 'hatch', 'year_start' => 2017, 'is_active' => true],
            ['id' => 4, 'make_id' => 4, 'name' => 'Corolla', 'category' => 'sedan', 'year_start' => 1966, 'is_active' => true],
            ['id' => 5, 'make_id' => 5, 'name' => 'HB20', 'category' => 'hatch', 'year_start' => 2012, 'is_active' => true],
            ['id' => 6, 'make_id' => 6, 'name' => 'Civic', 'category' => 'sedan', 'year_start' => 1992, 'is_active' => true],

            // SUVs
            ['id' => 7, 'make_id' => 5, 'name' => 'Creta', 'category' => 'suv', 'year_start' => 2016, 'is_active' => true],
            ['id' => 8, 'make_id' => 10, 'name' => 'Compass', 'category' => 'suv', 'year_start' => 2017, 'is_active' => true],

            // Caminhões
            ['id' => 20, 'make_id' => 40, 'name' => 'FH', 'category' => 'truck', 'year_start' => 1993, 'is_active' => true],
            ['id' => 21, 'make_id' => 43, 'name' => 'Delivery', 'category' => 'truck', 'year_start' => 2006, 'is_active' => true],
            // MAN (ID 44)
            ['id' => 22, 'make_id' => 44, 'name' => 'TGX', 'category' => 'truck', 'year_start' => 2007, 'is_active' => true],
            ['id' => 23, 'make_id' => 44, 'name' => 'TGS', 'category' => 'truck', 'year_start' => 2007, 'is_active' => true],

            // IVECO (ID 45)
            ['id' => 24, 'make_id' => 45, 'name' => 'Hi-Way', 'category' => 'truck', 'year_start' => 2012, 'is_active' => true],
            ['id' => 25, 'make_id' => 45, 'name' => 'Eurocargo', 'category' => 'truck', 'year_start' => 1991, 'is_active' => true],

            // DAF (ID 46)
            ['id' => 26, 'make_id' => 46, 'name' => 'XF', 'category' => 'truck', 'year_start' => 1997, 'is_active' => true],
            ['id' => 27, 'make_id' => 46, 'name' => 'CF', 'category' => 'truck', 'year_start' => 1992, 'is_active' => true],



            // **ELÉTRICOS**
            ['id' => 30, 'make_id' => 50, 'name' => 'BYD Dolphin', 'category' => 'electric-car', 'year_start' => 2023, 'is_active' => true],
            ['id' => 31, 'make_id' => 52, 'name' => 'GWM Ora 03', 'category' => 'electric-car', 'year_start' => 2023, 'is_active' => true],
            ['id' => 32, 'make_id' => 53, 'name' => 'Volvo EX30', 'category' => 'electric-car', 'year_start' => 2023, 'is_active' => true],

            // Motos
            ['id' => 40, 'make_id' => 20, 'name' => 'CG 160', 'category' => 'motorcycle', 'year_start' => 2015, 'is_active' => true],
            ['id' => 41, 'make_id' => 21, 'name' => 'Fazer 250', 'category' => 'motorcycle', 'year_start' => 2018, 'is_active' => true],

        ];
    }
}

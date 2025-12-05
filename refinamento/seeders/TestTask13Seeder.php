<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Mongo\Guide;


/**
 * Seeder de teste para Tarefa 1.3
 * Cria 15 guias: Toyota Corolla 2020-2024 em 3 categorias
 * 
 * Executar: php artisan db:seed --class=TestTask13Seeder
 */
class TestTask13Seeder extends Seeder
{
    public function run(): void
    {
        echo "🚀 Criando guias de teste...\n";

        $categories = [
            ['slug' => 'oleo', 'name' => 'Óleo de Motor'],
            ['slug' => 'calibragem', 'name' => 'Calibragem de Pneus'],
            ['slug' => 'arrefecimento', 'name' => 'Sistema de Arrefecimento'],
        ];

        $years = [
            2024 => ['engine' => '2.0 Dynamic Force', 'versions' => ['GLi', 'XEi', 'Altis']],
            2023 => ['engine' => '2.0 Dynamic Force', 'versions' => ['GLi', 'XEi', 'Altis']],
            2022 => ['engine' => '2.0 Dynamic Force', 'versions' => ['GLi', 'XEi', 'Altis']],
            2021 => ['engine' => '1.8 VVT-i', 'versions' => ['GLi', 'XEi', 'Altis', 'XRS']],
            2020 => ['engine' => '1.8 VVT-i', 'versions' => ['GLi', 'XEi']],
        ];

        $count = 0;
        foreach ($categories as $category) {
            foreach ($years as $year => $data) {
                Guide::create([
                    'title' => "{$category['name']} Toyota Corolla {$year}",
                    'slug' => "{$category['slug']}-toyota-corolla-{$year}",
                    'category' => $category['name'],
                    'category_slug' => $category['slug'],
                    'make' => 'Toyota',
                    'make_slug' => 'toyota',
                    'model' => 'Corolla',
                    'model_slug' => 'corolla',
                    'year_start' => $year,
                    'year_end' => $year,
                    'version' => implode(', ', $data['versions']),
                    'template' => 'oil-guide',
                    'payload' => [
                        'engine_specs' => [
                            'engine_name' => $data['engine'],
                            'displacement' => $year >= 2022 ? '2.0L' : '1.8L',
                            'power_hp' => $year >= 2022 ? 177 : 140,
                        ],
                        'oil_specs' => [
                            'viscosity' => '5W-30',
                            'type' => 'Sintético',
                            'capacity' => '4.2 litros',
                        ],
                    ],
                ]);
                $count++;
                echo "  ✅ {$category['name']} {$year}\n";
            }
        }

        echo "\n✅ {$count} guias criados\n";
        echo "🧪 Teste: curl http://localhost/guias/oleo/toyota/corolla\n\n";
    }
}
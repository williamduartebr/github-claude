<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\GuideDataCenter\Domain\Enums\BlockType;

class GuideConsumoSeeder extends Seeder
{
    public function run(): void
    {
        $category = GuideCategory::where('slug', 'consumo')->first();
        if (!$category) {
            $this->command->error('❌ Categoria "consumo" não encontrada!');
            return;
        }

        $guidesData = [
            ['make_slug' => 'toyota', 'model_slug' => 'corolla', 'version_slug' => 'gli', 'year' => 2025],
        ];

        $created = 0;
        foreach ($guidesData as $data) {
            $make = VehicleMake::where('slug', $data['make_slug'])->first();
            if (!$make) continue;

            $model = VehicleModel::where('slug', $data['model_slug'])->where('make_id', $make->id)->first();
            if (!$model) continue;

            $version = VehicleVersion::where('model_id', $model->id)->where('year', $data['year'])->first();

            Guide::updateOrCreate(
                ['slug' => "{$make->slug}-{$model->slug}-{$data['version_slug']}-{$data['year']}-{$category->slug}"],
                [
                    'vehicle_make_id' => $make->id,
                    'vehicle_model_id' => $model->id,
                    'vehicle_version_id' => $version?->id,
                    'make' => $make->name,
                    'make_slug' => $make->slug,
                    'model' => $model->name,
                    'model_slug' => $model->slug,
                    'version' => $version?->name,
                    'version_slug' => $version?->slug,
                    'year_start' => $data['year'],
                    'guide_category_id' => $category->_id,
                    'category' => $category->name,
                    'category_slug' => $category->slug,
                    'template' => 'vehicle_guide',
                    'full_title' => "Consumo — {$make->name} {$model->name} {$data['year']}",
                    'url' => "/guias/{$category->slug}/{$make->slug}/{$model->slug}/{$data['year']}/$version?->slug}",
                    'is_active' => true,
                    'content_blocks' => [
                        [
                            'type' => BlockType::HERO->value,
                            'order' => 1,
                            'data' => [
                                'title' => "Consumo do {$make->name} {$model->name} {$data['year']}",
                                'description' => 'Consumo oficial (Inmetro) versus consumo real.',
                                'badges' => [['text' => 'Motor 2.0 Flex', 'color' => 'blue']]
                            ]
                        ],
                        [
                            'type' => BlockType::TABLE->value,
                            'order' => 2,
                            'data' => [
                                'heading' => 'Consumo Médio: Oficial vs Real',
                                'headers' => ['Condição', 'Combustível', 'Inmetro', 'Real (Proprietários)'],
                                'rows' => [
                                    ['Cidade', 'Gasolina', '11.2 km/L', '9.5 - 10.5 km/L'],
                                    ['Estrada', 'Gasolina', '14.8 km/L', '13.0 - 14.0 km/L'],
                                    ['Média', 'Gasolina', '12.7 km/L', '11.0 - 12.0 km/L'],
                                    ['Cidade', 'Etanol', '7.8 km/L', '6.8 - 7.5 km/L'],
                                    ['Estrada', 'Etanol', '10.3 km/L', '9.0 - 10.0 km/L'],
                                ],
                                'caption' => 'Dados baseados em média de proprietários',
                            ]
                        ],
                        [
                            'type' => BlockType::SPECS_GRID->value,
                            'order' => 3,
                            'data' => [
                                'heading' => 'Capacidade do Tanque',
                                'specs' => [
                                    ['label' => 'Capacidade total', 'value' => '50 litros'],
                                    ['label' => 'Autonomia (gasolina)', 'value' => '550 - 600 km'],
                                    ['label' => 'Autonomia (etanol)', 'value' => '390 - 425 km'],
                                ]
                            ]
                        ],
                        [
                            'type' => BlockType::LIST->value,
                            'order' => 4,
                            'data' => [
                                'heading' => 'Dicas para Economizar Combustível',
                                'items' => [
                                    'Mantenha os pneus calibrados',
                                    'Evite acelerar e frear bruscamente',
                                    'Velocidade constante na estrada',
                                    'Manutenção regular',
                                    'Use ar-condicionado com moderação',
                                ]
                            ]
                        ],
                    ],
                ]
            );
            $created++;
        }

        $this->command->info("✅ {$created} guias de consumo criados!");
    }
}

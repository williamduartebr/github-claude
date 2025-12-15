<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\GuideDataCenter\Domain\Enums\BlockType;

class GuideFluidsSeeder extends Seeder
{
    public function run(): void
    {
        $category = GuideCategory::where('slug', 'fluidos')->first();
        if (!$category) {
            $this->command->error('❌ Categoria "fluidos" não encontrada!');
            return;
        }

        $guidesData = [
            ['make_slug' => 'toyota', 'model_slug' => 'corolla', 'version_slug' => 'gli', 'year' => 2025],
            ['make_slug' => 'honda', 'model_slug' => 'civic', 'version_slug' => 'exl', 'year' => 2024],
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
                    'full_title' => "Fluidos — {$make->name} {$model->name} {$data['year']}",
                    'url' => "/guias/{$category->slug}/{$make->slug}/{$model->slug}/{$data['year']}/{$version?->slug}",
                    'is_active' => true,
                    'content_blocks' => [
                        [
                            'type' => BlockType::HERO->value,
                            'order' => 1,
                            'data' => [
                                'title' => "Fluidos do {$make->name} {$model->name} {$data['year']}",
                                'description' => 'Guia completo: freio, arrefecimento, direção, transmissão.',
                                'badges' => [['text' => 'Especificações Oficiais', 'color' => 'green']]
                            ]
                        ],
                        [
                            'type' => BlockType::SPECS_GRID->value,
                            'order' => 2,
                            'data' => [
                                'heading' => 'Fluido de Freio',
                                'specs' => [
                                    ['label' => 'Tipo', 'value' => 'DOT 4'],
                                    ['label' => 'Capacidade', 'value' => '0.6 litros'],
                                    ['label' => 'Troca', 'value' => '2 anos ou 40.000 km'],
                                ]
                            ]
                        ],
                        [
                            'type' => BlockType::SPECS_GRID->value,
                            'order' => 3,
                            'data' => [
                                'heading' => 'Fluido de Arrefecimento',
                                'specs' => [
                                    ['label' => 'Tipo', 'value' => 'Etilenoglicol Long Life'],
                                    ['label' => 'Capacidade', 'value' => '6.5 litros'],
                                    ['label' => 'Proporção', 'value' => '50% água + 50% aditivo'],
                                ]
                            ]
                        ],
                    ],
                ]
            );
            $created++;
        }

        $this->command->info("✅ {$created} guias de fluidos criados!");
    }
}

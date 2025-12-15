<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\GuideDataCenter\Domain\Enums\BlockType;

class GuidePneusSeeder extends Seeder
{
    public function run(): void
    {
        $category = GuideCategory::where('slug', 'pneus')->first();
        if (!$category) {
            $this->command->error('❌ Categoria "pneus" não encontrada!');
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
                    'full_title' => "Pneus — {$make->name} {$model->name} {$data['year']}",
                    'url' => "/guias/{$category->slug}/{$make->slug}/{$model->slug}/{$data['year']}/{$version->slug}",
                    'is_active' => true,
                    'content_blocks' => [
                        [
                            'type' => BlockType::HERO->value,
                            'order' => 1,
                            'data' => [
                                'title' => "Pneus do {$make->name} {$model->name} {$data['year']}",
                                'description' => 'Medidas, rodas e especificações técnicas.',
                                'badges' => [['text' => 'Original', 'color' => 'green']]
                            ]
                        ],
                        [
                            'type' => BlockType::SPECS_GRID->value,
                            'order' => 2,
                            'data' => [
                                'heading' => 'Medidas de Pneus e Rodas',
                                'specs' => [
                                    ['label' => 'Medida original', 'value' => '205/55 R16'],
                                    ['label' => 'Índice de carga', 'value' => '91H'],
                                    ['label' => 'Roda', 'value' => '6.5J x 16'],
                                ]
                            ]
                        ],
                        [
                            'type' => BlockType::COMPATIBLE_ITEMS->value,
                            'order' => 3,
                            'data' => [
                                'heading' => 'Pneus Recomendados',
                                'items' => [
                                    ['name' => 'Michelin Primacy 4', 'spec' => '205/55 R16 91H'],
                                    ['name' => 'Bridgestone Turanza T005', 'spec' => '205/55 R16 91H'],
                                    ['name' => 'Continental ContiPremiumContact', 'spec' => '205/55 R16 91H'],
                                ]
                            ]
                        ],
                        [
                            'type' => BlockType::INTERVALS->value,
                            'order' => 4,
                            'data' => [
                                'heading' => 'Vida Útil e Manutenção',
                                'conditions' => [
                                    ['label' => 'Rodízio', 'value' => 'A cada 10.000 km'],
                                    ['label' => 'Alinhamento', 'value' => 'A cada 10.000 km'],
                                    ['label' => 'Troca recomendada', 'value' => '3 mm de sulco'],
                                ]
                            ]
                        ],
                    ],
                ]
            );
            $created++;
        }

        $this->command->info("✅ {$created} guias de pneus criados!");
    }
}

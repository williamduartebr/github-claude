<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\GuideDataCenter\Domain\Enums\BlockType;

class GuideBateriaSeeder extends Seeder
{
    public function run(): void
    {
        $category = GuideCategory::where('slug', 'bateria')->first();
        if (!$category) {
            $this->command->error('❌ Categoria "bateria" não encontrada!');
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
                    'full_title' => "Bateria — {$make->name} {$model->name} {$data['year']}",
                    'url' => "/guias/{$category->slug}/{$make->slug}/{$model->slug}/{$data['year']}/{$version?->slug}",
                    'is_active' => true,
                    'content_blocks' => [
                        [
                            'type' => BlockType::HERO->value,
                            'order' => 1,
                            'data' => [
                                'title' => "Bateria do {$make->name} {$model->name} {$data['year']}",
                                'description' => 'Amperagem, CCA e especificações.',
                                'badges' => [['text' => 'Sistema 12V', 'color' => 'blue']]
                            ]
                        ],
                        [
                            'type' => BlockType::SPECS_GRID->value,
                            'order' => 2,
                            'data' => [
                                'heading' => 'Especificações da Bateria',
                                'specs' => [
                                    ['label' => 'Amperagem (Ah)', 'value' => '60 Ah'],
                                    ['label' => 'Corrente de partida (CCA)', 'value' => '500 A'],
                                    ['label' => 'Tensão', 'value' => '12 Volts'],
                                    ['label' => 'Tipo', 'value' => 'Livre de manutenção'],
                                ]
                            ]
                        ],
                        [
                            'type' => BlockType::COMPATIBLE_ITEMS->value,
                            'order' => 3,
                            'data' => [
                                'heading' => 'Baterias Compatíveis',
                                'items' => [
                                    ['name' => 'Moura M60GD', 'spec' => '60 Ah / 500 A'],
                                    ['name' => 'Heliar HG60HD', 'spec' => '60 Ah / 540 A'],
                                    ['name' => 'Bosch S5X60D', 'spec' => '60 Ah / 520 A'],
                                ],
                                'note' => 'Verificar dimensões e polaridade'
                            ]
                        ],
                        [
                            'type' => BlockType::INTERVALS->value,
                            'order' => 4,
                            'data' => [
                                'heading' => 'Vida Útil',
                                'conditions' => [
                                    ['label' => 'Vida útil média', 'value' => '2 a 4 anos'],
                                    ['label' => 'Teste de carga', 'value' => 'Anualmente após 2 anos'],
                                ]
                            ]
                        ],
                    ],
                ]
            );
            $created++;
        }

        $this->command->info("✅ {$created} guias de bateria criados!");
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\GuideDataCenter\Domain\Enums\BlockType;

class GuideCalibragemSeeder extends Seeder
{
    public function run(): void
    {
        $category = GuideCategory::where('slug', 'calibragem')->first();
        
        if (!$category) {
            $this->command->error('❌ Categoria "calibragem" não encontrada!');
            return;
        }

        $guidesData = [
            [
                'make_slug' => 'toyota',
                'model_slug' => 'corolla',
                'version_slug' => 'gli',
                'year' => 2025,
                'pressures' => ['dianteira' => '32 PSI', 'traseira' => '30 PSI', 'estepe' => '60 PSI'],
            ],
            [
                'make_slug' => 'honda',
                'model_slug' => 'civic',
                'version_slug' => 'exl',
                'year' => 2024,
                'pressures' => ['dianteira' => '33 PSI', 'traseira' => '33 PSI', 'estepe' => '60 PSI'],
            ],
        ];

        $created = 0;
        foreach ($guidesData as $guideData) {
            $make = VehicleMake::where('slug', $guideData['make_slug'])->first();
            if (!$make) continue;

            $model = VehicleModel::where('slug', $guideData['model_slug'])
                ->where('make_id', $make->id)
                ->first();
            if (!$model) continue;

            $version = VehicleVersion::where('model_id', $model->id)
                ->where('year', $guideData['year'])
                ->first();

            $slug = "{$make->slug}-{$model->slug}-{$guideData['version_slug']}-{$guideData['year']}-{$category->slug}";
            
            Guide::updateOrCreate(
                ['slug' => $slug],
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
                    'year_start' => $guideData['year'],
                    'guide_category_id' => $category->_id,
                    'category' => $category->name,
                    'category_slug' => $category->slug,
                    'template' => 'vehicle_guide',
                    'full_title' => "Calibragem — {$make->name} {$model->name} {$guideData['year']}",
                    'short_title' => "Calibragem {$guideData['year']}",
                    'url' => "/guias/{$category->slug}/{$make->slug}/{$model->slug}/{$guideData['year']}/{$version?->slug}",
                    'is_active' => true,
                    'content_blocks' => $this->getBlocks($guideData['pressures']),
                ]
            );

            $created++;
        }

        $this->command->info("✅ {$created} guias de calibragem criados!");
    }

    private function getBlocks(array $pressures): array
    {
        return [
            [
                'type' => BlockType::HERO->value,
                'order' => 1,
                'data' => [
                    'title' => 'Calibragem - Pressões Corretas',
                    'description' => 'Pressões recomendadas para todas as condições.',
                    'badges' => [['text' => 'Especificação Oficial', 'color' => 'green']]
                ]
            ],
            [
                'type' => BlockType::SPECS_GRID->value,
                'order' => 2,
                'data' => [
                    'heading' => 'Pressões Recomendadas',
                    'specs' => [
                        ['label' => 'Dianteira', 'value' => $pressures['dianteira']],
                        ['label' => 'Traseira', 'value' => $pressures['traseira']],
                        ['label' => 'Estepe', 'value' => $pressures['estepe']],
                    ]
                ]
            ],
        ];
    }
}

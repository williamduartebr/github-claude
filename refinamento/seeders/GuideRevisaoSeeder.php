<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\GuideDataCenter\Domain\Enums\BlockType;

class GuideRevisaoSeeder extends Seeder
{
    public function run(): void
    {
        $category = GuideCategory::where('slug', 'revisao')->first();
        if (!$category) {
            $this->command->error('❌ Categoria "revisao" não encontrada!');
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
                    'full_title' => "Revisão — {$make->name} {$model->name} {$data['year']}",
                    'url' => "/guias/{$category->slug}/{$make->slug}/{$model->slug}/{$data['year']}/{$version->slug}",
                    'is_active' => true,
                    'content_blocks' => [
                        [
                            'type' => BlockType::HERO->value,
                            'order' => 1,
                            'data' => [
                                'title' => "Revisão do {$make->name} {$model->name} {$data['year']}",
                                'description' => 'Plano completo de manutenção preventiva.',
                                'badges' => [['text' => 'Plano Oficial', 'color' => 'green']]
                            ]
                        ],
                        [
                            'type' => BlockType::TABLE->value,
                            'order' => 2,
                            'data' => [
                                'heading' => 'Plano de Manutenção Preventiva',
                                'headers' => ['Km', 'Itens Principais', 'Custo Médio'],
                                'rows' => [
                                    ['10.000', 'Óleo + Filtro + Inspeções', 'R$ 300 - R$ 400'],
                                    ['20.000', 'Óleo + Filtros (ar/óleo/cabine)', 'R$ 500 - R$ 700'],
                                    ['30.000', 'Óleo + Velas + Fluido freio', 'R$ 800 - R$ 1.000'],
                                    ['40.000', 'Óleo + Alinhamento', 'R$ 600 - R$ 800'],
                                    ['60.000', 'Óleo + Velas + Bateria', 'R$ 1.200 - R$ 1.500'],
                                ],
                                'caption' => 'Valores aproximados para concessionária oficial'
                            ]
                        ],
                        [
                            'type' => BlockType::INTERVALS->value,
                            'order' => 3,
                            'data' => [
                                'heading' => 'Intervalos de Revisão',
                                'conditions' => [
                                    ['label' => 'Primeira revisão', 'value' => '10.000 km ou 12 meses'],
                                    ['label' => 'Revisões seguintes', 'value' => 'A cada 10.000 km ou 12 meses'],
                                ],
                                'note' => 'O que ocorrer primeiro'
                            ]
                        ],
                        [
                            'type' => BlockType::LIST->value,
                            'order' => 4,
                            'data' => [
                                'heading' => 'Itens Verificados em Toda Revisão',
                                'items' => [
                                    'Nível e condição do óleo do motor',
                                    'Filtros (ar, óleo, combustível, cabine)',
                                    'Sistema de freios',
                                    'Suspensão e amortecedores',
                                    'Pneus (calibragem e rodízio)',
                                    'Bateria e sistema elétrico',
                                ]
                            ]
                        ],
                    ],
                ]
            );
            $created++;
        }

        $this->command->info("✅ {$created} guias de revisão criados!");
    }
}

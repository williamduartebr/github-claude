<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\GuideDataCenter\Domain\Enums\BlockType;

/**
 * Seeder de Guias de Óleo - CORRIGIDO
 * 
 * Busca IDs do MySQL antes de criar guias
 */
class GuideOleoSeeder extends Seeder
{
    public function run(): void
    {
        $category = GuideCategory::where('slug', 'oleo')->first();
        
        if (!$category) {
            $this->command->error('❌ Categoria "oleo" não encontrada!');
            return;
        }

        $guidesData = [
            // TOYOTA COROLLA GLi 2025
            [
                'make_slug' => 'toyota',
                'model_slug' => 'corolla',
                'version_slug' => 'gli',
                'year' => 2025,
                'blocks' => $this->getOleoBlocksCorolla(),
            ],
            // HONDA CIVIC EXL 2024
            [
                'make_slug' => 'honda',
                'model_slug' => 'civic',
                'version_slug' => 'exl',
                'year' => 2024,
                'blocks' => $this->getOleoBlocksCivic(),
            ],
            // VW GOLF TSI 2023
            [
                'make_slug' => 'volkswagen',
                'model_slug' => 'golf',
                'version_slug' => 'tsi',
                'year' => 2023,
                'blocks' => $this->getOleoBlocksGolf(),
            ],
        ];

        $created = 0;
        foreach ($guidesData as $guideData) {
            // ✅ Buscar entidades do MySQL
            $make = VehicleMake::where('slug', $guideData['make_slug'])->first();
            if (!$make) {
                $this->command->warn("⚠️  Marca '{$guideData['make_slug']}' não encontrada no MySQL");
                continue;
            }

            $model = VehicleModel::where('slug', $guideData['model_slug'])
                ->where('make_id', $make->id)
                ->first();
            if (!$model) {
                $this->command->warn("⚠️  Modelo '{$guideData['model_slug']}' não encontrado no MySQL");
                continue;
            }

            $version = VehicleVersion::where('model_id', $model->id)->where('year', $guideData['year'])->first();

            // ✅ Criar guia com IDs corretos
            $slug = $this->generateSlug($make->slug, $model->slug, $guideData['version_slug'] ?? 'base', $guideData['year'], $category->slug);
            
            Guide::updateOrCreate(
                ['slug' => $slug],
                [
                    // ✅ IDs do MySQL
                    'vehicle_make_id' => $make->id,
                    'vehicle_model_id' => $model->id,
                    'vehicle_version_id' => $version?->id,
                    
                    // Dados textuais
                    'make' => $make->name,
                    'make_slug' => $make->slug,
                    'model' => $model->name,
                    'model_slug' => $model->slug,
                    'version' => $version?->name,
                    'version_slug' => $version?->slug,
                    'motor' => $version?->engine_code,
                    'fuel' => $version?->fuel_type,
                    'year_start' => $guideData['year'],
                    'year_end' => $guideData['year'],
                    
                    // ✅ ID da categoria (MongoDB)
                    'guide_category_id' => $category->_id,
                    'category' => $category->name,
                    'category_slug' => $category->slug,
                    
                    // Template e URLs
                    'template' => 'vehicle_guide',
                    'full_title' => "Óleo — {$make->name} {$model->name} " . ($version ? $version->name : '') . " {$guideData['year']}",
                    'short_title' => "Óleo {$guideData['year']}",
                    'url' => "/guias/{$category->slug}/{$make->slug}/{$model->slug}/{$guideData['year']}/{$version?->slug}",
                    'is_active' => true,
                    
                    // ✅ Blocos
                    'content_blocks' => $guideData['blocks'],
                ]
            );

            $created++;
        }

        $this->command->info("✅ {$created} guias de óleo criados/atualizados!");
    }

    private function getOleoBlocksCorolla(): array
    {
        return array_merge([
            [
                'type' => BlockType::HERO->value,
                'order' => 1,
                'data' => [
                    'title' => 'Óleo do Toyota Corolla GLi 2025',
                    'description' => 'Guia completo sobre o óleo recomendado para o motor 2.0 Flex.',
                    'badges' => [
                        ['text' => 'Info Oficial Toyota', 'color' => 'green'],
                        ['text' => 'Atualizado 2024', 'color' => 'blue']
                    ]
                ]
            ],
            [
                'type' => BlockType::DISCLAIMER->value,
                'order' => 2,
                'data' => [
                    'text' => 'Sempre consulte o manual do proprietário para informações específicas do seu veículo.',
                    'type' => 'warning'
                ]
            ],
            [
                'type' => BlockType::SPECS_GRID->value,
                'order' => 3,
                'data' => [
                    'heading' => 'Óleo Oficial Recomendado',
                    'specs' => [
                        ['label' => 'Viscosidade', 'value' => '0W-20'],
                        ['label' => 'Especificação', 'value' => 'API SN Plus / ILSAC GF-5'],
                        ['label' => 'Capacidade c/ filtro', 'value' => '4.2 litros'],
                    ],
                    'note' => 'Óleo sintético recomendado pela Toyota'
                ]
            ],
            [
                'type' => BlockType::COMPATIBLE_ITEMS->value,
                'order' => 4,
                'data' => [
                    'heading' => 'Óleos Compatíveis',
                    'items' => [
                        ['name' => 'Castrol Edge 0W-20', 'spec' => 'API SN Plus'],
                        ['name' => 'Mobil 1 ESP 0W-20', 'spec' => 'ACEA C5'],
                        ['name' => 'Shell Helix Ultra 0W-20', 'spec' => 'API SN Plus'],
                    ]
                ]
            ],
            [
                'type' => BlockType::INTERVALS->value,
                'order' => 5,
                'data' => [
                    'heading' => 'Intervalos de Troca',
                    'conditions' => [
                        ['label' => 'Uso normal', 'value' => '10.000 km ou 12 meses'],
                        ['label' => 'Uso severo', 'value' => '5.000 km ou 6 meses'],
                    ]
                ]
            ],
        ], $this->getRelatedGuidesBlocks(), $this->getClusterBlocks());
    }

    private function getRelatedGuidesBlocks(): array
    {
        return [
            [
                'type' => 'related_guides',
                'order' => 6,
                'data' => [
                    'heading' => 'Outros guias do mesmo veículo',
                    'guides' => [
                        ['name' => 'Calibragem', 'icon' => '🎯', 'url' => '#'],
                        ['name' => 'Fluidos', 'icon' => '💧', 'url' => '#'],
                        ['name' => 'Pneus', 'icon' => '🛞', 'url' => '#'],
                        ['name' => 'Bateria', 'icon' => '🔋', 'url' => '#'],
                    ]
                ]
            ],
        ];
    }

    private function getClusterBlocks(): array
    {
        return [
            [
                'type' => 'cluster',
                'order' => 7,
                'data' => [
                    'heading' => 'Conteúdos Essenciais',
                    'items' => [
                        ['icon' => '📘', 'title' => 'Ficha Técnica Completa', 'url' => '#'],
                        ['icon' => '⛽', 'title' => 'Consumo Real', 'url' => '#'],
                        ['icon' => '🔧', 'title' => 'Revisão e Manutenção', 'url' => '#'],
                    ]
                ]
            ],
        ];
    }

    private function getOleoBlocksCivic(): array
    {
        return [
            [
                'type' => BlockType::HERO->value,
                'order' => 1,
                'data' => [
                    'title' => 'Óleo do Honda Civic EXL 2024',
                    'description' => 'Especificações de óleo para o motor 2.0 i-VTEC Flex.',
                    'badges' => [
                        ['text' => 'Honda Original', 'color' => 'green']
                    ]
                ]
            ],
            [
                'type' => BlockType::SPECS_GRID->value,
                'order' => 2,
                'data' => [
                    'heading' => 'Especificações',
                    'specs' => [
                        ['label' => 'Viscosidade', 'value' => '0W-20 ou 5W-30'],
                        ['label' => 'Especificação', 'value' => 'API SN ou superior'],
                        ['label' => 'Capacidade', 'value' => '3.7 litros'],
                    ]
                ]
            ],
            [
                'type' => BlockType::COMPATIBLE_ITEMS->value,
                'order' => 3,
                'data' => [
                    'heading' => 'Óleos Recomendados',
                    'items' => [
                        ['name' => 'Honda Genuine Oil 0W-20', 'spec' => 'API SN Plus'],
                        ['name' => 'Castrol Magnatec 0W-20', 'spec' => 'API SN'],
                    ]
                ]
            ],
        ];
    }

    private function getOleoBlocksGolf(): array
    {
        return [
            [
                'type' => BlockType::HERO->value,
                'order' => 1,
                'data' => [
                    'title' => 'Óleo do Volkswagen Golf TSI 2023',
                    'description' => 'Guia de óleo para o motor 1.4 TSI turbo.',
                    'badges' => [
                        ['text' => 'VW Original', 'color' => 'green']
                    ]
                ]
            ],
            [
                'type' => BlockType::SPECS_GRID->value,
                'order' => 2,
                'data' => [
                    'heading' => 'Óleo Recomendado',
                    'specs' => [
                        ['label' => 'Viscosidade', 'value' => '5W-30'],
                        ['label' => 'Especificação VW', 'value' => 'VW 502.00'],
                        ['label' => 'Capacidade', 'value' => '4.3 litros'],
                    ]
                ]
            ],
        ];
    }

    private function generateSlug(string $makeSlug, string $modelSlug, string $versionSlug, int $year, string $categorySlug): string
    {
        return "{$makeSlug}-{$modelSlug}-{$versionSlug}-{$year}-{$categorySlug}";
    }
}

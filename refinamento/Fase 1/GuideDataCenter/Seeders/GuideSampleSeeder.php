<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * Seeder de Guias de Exemplo - Por Versão Específica
 *
 * @author Claude AI Assistant
 * @version 1.0.0
 */
class GuideSampleSeeder extends Seeder
{
    public function run(): void
    {
        $guides = $this->getGuidesData();
        $insertedCount = 0;

        foreach ($guides as $guideData) {
            $category = GuideCategory::where('slug', $guideData['category_slug'])->first();

            if (!$category) {
                $this->command->warn("⚠️  Categoria '{$guideData['category_slug']}' não encontrada.");
                continue;
            }

            Guide::updateOrCreate(
                ['slug' => $guideData['slug']],
                [
                    'guide_category_id' => $category->_id,
                    'make' => $guideData['make'],
                    'make_slug' => Str::slug($guideData['make']),
                    'model' => $guideData['model'],
                    'model_slug' => Str::slug($guideData['model']),
                    'version' => $guideData['version'],
                    'year' => $guideData['year'],
                    'template' => $guideData['template'],
                    'slug' => $guideData['slug'],
                    'url' => $guideData['url'],
                    'payload' => $guideData['payload'],
                    'seo' => $guideData['seo'],
                ]
            );
            $insertedCount++;
        }

        $this->command->info("✅ {$insertedCount} guias inseridos!");
    }

    private function getGuidesData(): array
    {
        return [
            // ═══════════════════════════════════════════════════════════════
            // ÓLEO - POR VERSÃO
            // ═══════════════════════════════════════════════════════════════
            
            // Toyota Corolla XEi 2024
            [
                'category_slug' => 'oleo',
                'make' => 'Toyota',
                'model' => 'Corolla',
                'version' => 'XEi 2.0',
                'year' => 2024,
                'template' => 'oleo-motor',
                'slug' => 'oleo-toyota-corolla-xei-2-0-2024',
                'url' => '/guias/oleo/toyota/corolla/xei-2-0/2024',
                'payload' => [
                    'viscosity' => '0W-20',
                    'api' => 'SP',
                    'capacity_with_filter' => 4.4,
                    'capacity_without_filter' => 4.0,
                    'type' => 'sintetico',
                    'interval_km' => 10000,
                    'filter_code' => '90915-YZZS2',
                ],
                'seo' => [
                    'title' => 'Óleo Toyota Corolla XEi 2.0 2024',
                    'meta_description' => 'Óleo do Corolla XEi 2024: 0W-20 API SP, 4.4L',
                ],
            ],
            
            // Toyota Corolla GLi 2024
            [
                'category_slug' => 'oleo',
                'make' => 'Toyota',
                'model' => 'Corolla',
                'version' => 'GLi 2.0',
                'year' => 2024,
                'template' => 'oleo-motor',
                'slug' => 'oleo-toyota-corolla-gli-2-0-2024',
                'url' => '/guias/oleo/toyota/corolla/gli-2-0/2024',
                'payload' => [
                    'viscosity' => '0W-20',
                    'api' => 'SP',
                    'capacity_with_filter' => 4.4,
                    'type' => 'sintetico',
                    'interval_km' => 10000,
                ],
                'seo' => [
                    'title' => 'Óleo Toyota Corolla GLi 2.0 2024',
                    'meta_description' => 'Óleo do Corolla GLi 2024: 0W-20 API SP, 4.4L',
                ],
            ],

            // Fiat Strada Freedom 1.3 2024
            [
                'category_slug' => 'oleo',
                'make' => 'Fiat',
                'model' => 'Strada',
                'version' => 'Freedom 1.3',
                'year' => 2024,
                'template' => 'oleo-motor',
                'slug' => 'oleo-fiat-strada-freedom-1-3-2024',
                'url' => '/guias/oleo/fiat/strada/freedom-1-3/2024',
                'payload' => [
                    'viscosity' => '5W-30',
                    'api' => 'SN Plus',
                    'capacity_with_filter' => 3.3,
                    'type' => 'sintetico',
                    'interval_km' => 10000,
                ],
                'seo' => [
                    'title' => 'Óleo Fiat Strada Freedom 1.3 2024',
                    'meta_description' => 'Óleo da Strada Freedom 2024: 5W-30 API SN+, 3.3L',
                ],
            ],

            // Fiat Strada Volcano 1.3 2024
            [
                'category_slug' => 'oleo',
                'make' => 'Fiat',
                'model' => 'Strada',
                'version' => 'Volcano 1.3',
                'year' => 2024,
                'template' => 'oleo-motor',
                'slug' => 'oleo-fiat-strada-volcano-1-3-2024',
                'url' => '/guias/oleo/fiat/strada/volcano-1-3/2024',
                'payload' => [
                    'viscosity' => '5W-30',
                    'api' => 'SN Plus',
                    'capacity_with_filter' => 3.3,
                    'type' => 'sintetico',
                    'interval_km' => 10000,
                ],
                'seo' => [
                    'title' => 'Óleo Fiat Strada Volcano 1.3 2024',
                    'meta_description' => 'Óleo da Strada Volcano 2024: 5W-30 API SN+, 3.3L',
                ],
            ],

            // VW Polo Highline 1.0 TSI 2024
            [
                'category_slug' => 'oleo',
                'make' => 'Volkswagen',
                'model' => 'Polo',
                'version' => 'Highline 1.0 TSI',
                'year' => 2024,
                'template' => 'oleo-motor',
                'slug' => 'oleo-vw-polo-highline-1-0-tsi-2024',
                'url' => '/guias/oleo/volkswagen/polo/highline-1-0-tsi/2024',
                'payload' => [
                    'viscosity' => '0W-20',
                    'api' => 'SP',
                    'vw_norm' => '508.00/509.00',
                    'capacity_with_filter' => 4.0,
                    'type' => 'sintetico',
                    'interval_km' => 15000,
                ],
                'seo' => [
                    'title' => 'Óleo VW Polo Highline 1.0 TSI 2024',
                    'meta_description' => 'Óleo do Polo TSI 2024: 0W-20 VW 508/509, 4.0L',
                ],
            ],

            // Chevrolet Onix LTZ 1.0 Turbo 2024
            [
                'category_slug' => 'oleo',
                'make' => 'Chevrolet',
                'model' => 'Onix',
                'version' => 'LTZ 1.0 Turbo',
                'year' => 2024,
                'template' => 'oleo-motor',
                'slug' => 'oleo-chevrolet-onix-ltz-1-0-turbo-2024',
                'url' => '/guias/oleo/chevrolet/onix/ltz-1-0-turbo/2024',
                'payload' => [
                    'viscosity' => '0W-20',
                    'api' => 'SP',
                    'dexos' => 'dexos1 Gen3',
                    'capacity_with_filter' => 4.0,
                    'type' => 'sintetico',
                    'interval_km' => 10000,
                ],
                'seo' => [
                    'title' => 'Óleo Chevrolet Onix LTZ Turbo 2024',
                    'meta_description' => 'Óleo do Onix Turbo 2024: 0W-20 dexos1, 4.0L',
                ],
            ],

            // Hyundai HB20 Platinum 1.0 Turbo 2024
            [
                'category_slug' => 'oleo',
                'make' => 'Hyundai',
                'model' => 'HB20',
                'version' => 'Platinum 1.0 Turbo',
                'year' => 2024,
                'template' => 'oleo-motor',
                'slug' => 'oleo-hyundai-hb20-platinum-1-0-turbo-2024',
                'url' => '/guias/oleo/hyundai/hb20/platinum-1-0-turbo/2024',
                'payload' => [
                    'viscosity' => '5W-30',
                    'api' => 'SN',
                    'capacity_with_filter' => 3.6,
                    'type' => 'sintetico',
                    'interval_km' => 10000,
                ],
                'seo' => [
                    'title' => 'Óleo Hyundai HB20 Platinum Turbo 2024',
                    'meta_description' => 'Óleo do HB20 Turbo 2024: 5W-30 API SN, 3.6L',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // CALIBRAGEM - POR VERSÃO
            // ═══════════════════════════════════════════════════════════════

            // Toyota Corolla XEi 2024
            [
                'category_slug' => 'calibragem',
                'make' => 'Toyota',
                'model' => 'Corolla',
                'version' => 'XEi 2.0',
                'year' => 2024,
                'template' => 'calibragem-pneus',
                'slug' => 'calibragem-toyota-corolla-xei-2-0-2024',
                'url' => '/guias/calibragem/toyota/corolla/xei-2-0/2024',
                'payload' => [
                    'tire_size' => '215/55 R17',
                    'pressure_front_psi' => 33,
                    'pressure_rear_psi' => 33,
                    'pressure_front_bar' => 2.3,
                    'pressure_rear_bar' => 2.3,
                    'spare_psi' => 60,
                ],
                'seo' => [
                    'title' => 'Calibragem Toyota Corolla XEi 2024',
                    'meta_description' => 'Pressão dos pneus Corolla XEi 2024: 33 PSI (2.3 bar)',
                ],
            ],

            // Fiat Strada Freedom 1.3 2024
            [
                'category_slug' => 'calibragem',
                'make' => 'Fiat',
                'model' => 'Strada',
                'version' => 'Freedom 1.3',
                'year' => 2024,
                'template' => 'calibragem-pneus',
                'slug' => 'calibragem-fiat-strada-freedom-1-3-2024',
                'url' => '/guias/calibragem/fiat/strada/freedom-1-3/2024',
                'payload' => [
                    'tire_size' => '185/65 R15',
                    'pressure_front_psi' => 35,
                    'pressure_rear_psi' => 35,
                    'pressure_front_bar' => 2.4,
                    'pressure_rear_bar' => 2.4,
                    'pressure_loaded_rear_psi' => 41,
                ],
                'seo' => [
                    'title' => 'Calibragem Fiat Strada Freedom 2024',
                    'meta_description' => 'Pressão dos pneus Strada Freedom 2024: 35 PSI',
                ],
            ],

            // VW Polo Highline 2024
            [
                'category_slug' => 'calibragem',
                'make' => 'Volkswagen',
                'model' => 'Polo',
                'version' => 'Highline 1.0 TSI',
                'year' => 2024,
                'template' => 'calibragem-pneus',
                'slug' => 'calibragem-vw-polo-highline-1-0-tsi-2024',
                'url' => '/guias/calibragem/volkswagen/polo/highline-1-0-tsi/2024',
                'payload' => [
                    'tire_size' => '195/55 R16',
                    'pressure_front_psi' => 32,
                    'pressure_rear_psi' => 32,
                    'pressure_front_bar' => 2.2,
                    'pressure_rear_bar' => 2.2,
                ],
                'seo' => [
                    'title' => 'Calibragem VW Polo Highline 2024',
                    'meta_description' => 'Pressão dos pneus Polo TSI 2024: 32 PSI (2.2 bar)',
                ],
            ],

            // Chevrolet Onix LTZ Turbo 2024
            [
                'category_slug' => 'calibragem',
                'make' => 'Chevrolet',
                'model' => 'Onix',
                'version' => 'LTZ 1.0 Turbo',
                'year' => 2024,
                'template' => 'calibragem-pneus',
                'slug' => 'calibragem-chevrolet-onix-ltz-1-0-turbo-2024',
                'url' => '/guias/calibragem/chevrolet/onix/ltz-1-0-turbo/2024',
                'payload' => [
                    'tire_size' => '195/55 R16',
                    'pressure_front_psi' => 35,
                    'pressure_rear_psi' => 35,
                    'pressure_front_bar' => 2.4,
                    'pressure_rear_bar' => 2.4,
                ],
                'seo' => [
                    'title' => 'Calibragem Chevrolet Onix Turbo 2024',
                    'meta_description' => 'Pressão dos pneus Onix Turbo 2024: 35 PSI',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // REVISÃO - POR VERSÃO
            // ═══════════════════════════════════════════════════════════════

            // Toyota Corolla XEi 2024
            [
                'category_slug' => 'revisao',
                'make' => 'Toyota',
                'model' => 'Corolla',
                'version' => 'XEi 2.0',
                'year' => 2024,
                'template' => 'revisao-programada',
                'slug' => 'revisao-toyota-corolla-xei-2-0-2024',
                'url' => '/guias/revisao/toyota/corolla/xei-2-0/2024',
                'payload' => [
                    'interval_km' => 10000,
                    'interval_months' => 12,
                    'revisions' => [
                        ['km' => 10000, 'items' => ['Óleo', 'Filtro óleo', 'Inspeção'], 'cost' => 450],
                        ['km' => 20000, 'items' => ['Óleo', 'Filtros', 'Fluido freio'], 'cost' => 650],
                        ['km' => 30000, 'items' => ['Óleo', 'Filtros', 'Velas'], 'cost' => 850],
                        ['km' => 40000, 'items' => ['Óleo', 'Filtros', 'Pastilhas'], 'cost' => 1200],
                    ],
                ],
                'seo' => [
                    'title' => 'Revisão Toyota Corolla XEi 2024',
                    'meta_description' => 'Tabela de revisão Corolla XEi 2024: intervalos e custos',
                ],
            ],

            // Fiat Strada Freedom 2024
            [
                'category_slug' => 'revisao',
                'make' => 'Fiat',
                'model' => 'Strada',
                'version' => 'Freedom 1.3',
                'year' => 2024,
                'template' => 'revisao-programada',
                'slug' => 'revisao-fiat-strada-freedom-1-3-2024',
                'url' => '/guias/revisao/fiat/strada/freedom-1-3/2024',
                'payload' => [
                    'interval_km' => 10000,
                    'interval_months' => 12,
                    'revisions' => [
                        ['km' => 10000, 'items' => ['Óleo', 'Filtro óleo'], 'cost' => 350],
                        ['km' => 20000, 'items' => ['Óleo', 'Filtros ar/cabine'], 'cost' => 500],
                        ['km' => 30000, 'items' => ['Óleo', 'Velas', 'Correia'], 'cost' => 750],
                        ['km' => 40000, 'items' => ['Óleo', 'Filtros', 'Fluidos'], 'cost' => 900],
                    ],
                ],
                'seo' => [
                    'title' => 'Revisão Fiat Strada Freedom 2024',
                    'meta_description' => 'Tabela de revisão Strada Freedom 2024',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MOTOS - ÓLEO
            // ═══════════════════════════════════════════════════════════════

            // Honda CG 160 Titan 2024
            [
                'category_slug' => 'oleo',
                'make' => 'Honda',
                'model' => 'CG 160',
                'version' => 'Titan',
                'year' => 2024,
                'template' => 'oleo-motor',
                'slug' => 'oleo-honda-cg-160-titan-2024',
                'url' => '/guias/oleo/honda/cg-160/titan/2024',
                'payload' => [
                    'viscosity' => '10W-30',
                    'api' => 'SL',
                    'jaso' => 'MA',
                    'capacity' => 1.0,
                    'type' => 'mineral-4t',
                    'interval_km' => 3000,
                ],
                'seo' => [
                    'title' => 'Óleo Honda CG 160 Titan 2024',
                    'meta_description' => 'Óleo da CG 160 Titan 2024: 10W-30 JASO MA, 1.0L',
                ],
            ],

            // Yamaha Fazer 250 2024
            [
                'category_slug' => 'oleo',
                'make' => 'Yamaha',
                'model' => 'Fazer 250',
                'version' => 'ABS',
                'year' => 2024,
                'template' => 'oleo-motor',
                'slug' => 'oleo-yamaha-fazer-250-abs-2024',
                'url' => '/guias/oleo/yamaha/fazer-250/abs/2024',
                'payload' => [
                    'viscosity' => '10W-40',
                    'api' => 'SL',
                    'jaso' => 'MA',
                    'capacity' => 1.2,
                    'type' => 'sintetico-4t',
                    'interval_km' => 5000,
                ],
                'seo' => [
                    'title' => 'Óleo Yamaha Fazer 250 2024',
                    'meta_description' => 'Óleo da Fazer 250 2024: 10W-40 JASO MA, 1.2L',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MOTOS - CALIBRAGEM
            // ═══════════════════════════════════════════════════════════════

            // Honda CG 160 Titan 2024
            [
                'category_slug' => 'calibragem',
                'make' => 'Honda',
                'model' => 'CG 160',
                'version' => 'Titan',
                'year' => 2024,
                'template' => 'calibragem-pneus',
                'slug' => 'calibragem-honda-cg-160-titan-2024',
                'url' => '/guias/calibragem/honda/cg-160/titan/2024',
                'payload' => [
                    'tire_front' => '110/70-17',
                    'tire_rear' => '130/70-17',
                    'pressure_front_psi' => 25,
                    'pressure_rear_psi' => 29,
                    'pressure_front_bar' => 1.7,
                    'pressure_rear_bar' => 2.0,
                ],
                'seo' => [
                    'title' => 'Calibragem Honda CG 160 Titan 2024',
                    'meta_description' => 'Pressão pneus CG 160 Titan: D:25 T:29 PSI',
                ],
            ],
        ];
    }
}
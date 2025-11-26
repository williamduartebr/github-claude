<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * Seeder para guias de exemplo
 */
class GuideSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca categorias
        $oleoCategory = GuideCategory::where('slug', 'oleo')->first();
        $pneusCategory = GuideCategory::where('slug', 'pneus')->first();
        $calibragemCategory = GuideCategory::where('slug', 'calibragem')->first();

        if (!$oleoCategory || !$pneusCategory || !$calibragemCategory) {
            $this->command->error('Categories not found. Please run GuideCategorySeeder first.');
            return;
        }

        $guides = [
            // Guia 1: Óleo Fiat Uno
            [
                'guide_category_id' => $oleoCategory->_id,
                'make' => 'Fiat',
                'make_slug' => 'fiat',
                'model' => 'Uno',
                'model_slug' => 'uno',
                'version' => '1.0 Fire',
                'motor' => '1.0 Fire',
                'fuel' => 'Gasolina',
                'year_start' => 2010,
                'year_end' => 2020,
                'template' => 'oleo-motor',
                'slug' => 'fiat-uno-oleo-2010-2020',
                'url' => config('app.url') . '/guias/fiat-uno-oleo-2010-2020',
                'payload' => [
                    'title' => 'Óleo do Motor Fiat Uno 1.0 Fire 2010-2020',
                    'tipo_oleo' => '10W-30 ou 10W-40',
                    'capacidade' => '3.5 litros',
                    'intervalo_troca' => '10.000 km ou 1 ano',
                    'especificacao' => 'API SN, ACEA A3/B3',
                    'recomendacoes' => [
                        'Usar óleo sintético ou semissintético',
                        'Trocar filtro a cada troca de óleo',
                        'Verificar nível semanalmente',
                    ],
                ],
                'seo' => [],
                'links_internal' => [],
                'links_related' => [],
            ],

            // Guia 2: Pneus VW Gol
            [
                'guide_category_id' => $pneusCategory->_id,
                'make' => 'Volkswagen',
                'make_slug' => 'volkswagen',
                'model' => 'Gol',
                'model_slug' => 'gol',
                'version' => '1.6 Total Flex',
                'motor' => '1.6',
                'fuel' => 'Flex',
                'year_start' => 2013,
                'year_end' => 2022,
                'template' => 'pneus',
                'slug' => 'volkswagen-gol-pneus-2013-2022',
                'url' => config('app.url') . '/guias/volkswagen-gol-pneus-2013-2022',
                'payload' => [
                    'title' => 'Pneus e Rodas VW Gol 1.6 Total Flex 2013-2022',
                    'medida_original' => '175/70 R14',
                    'medida_opcional' => '185/60 R15',
                    'tipo_roda' => 'Aro 14 ou 15',
                    'offset' => 'ET 38',
                    'furo' => '4x100',
                    'recomendacoes' => [
                        'Verificar balanceamento a cada 10.000 km',
                        'Fazer rodízio a cada 10.000 km',
                        'Verificar alinhamento anualmente',
                    ],
                ],
                'seo' => [],
                'links_internal' => [],
                'links_related' => [],
            ],

            // Guia 3: Calibragem Honda Civic
            [
                'guide_category_id' => $calibragemCategory->_id,
                'make' => 'Honda',
                'make_slug' => 'honda',
                'model' => 'Civic',
                'model_slug' => 'civic',
                'version' => '2.0 Sport',
                'motor' => '2.0 i-VTEC',
                'fuel' => 'Gasolina',
                'year_start' => 2017,
                'year_end' => 2021,
                'template' => 'calibragem',
                'slug' => 'honda-civic-calibragem-2017-2021',
                'url' => config('app.url') . '/guias/honda-civic-calibragem-2017-2021',
                'payload' => [
                    'title' => 'Calibragem de Pneus Honda Civic 2.0 Sport 2017-2021',
                    'pressao_dianteira' => '33 PSI (vazio) / 35 PSI (cheio)',
                    'pressao_traseira' => '33 PSI (vazio) / 35 PSI (cheio)',
                    'pressao_estepe' => '60 PSI',
                    'medida_pneu' => '235/40 R18',
                    'observacoes' => [
                        'Calibrar com pneus frios',
                        'Verificar pressão mensalmente',
                        'Não exceder pressão máxima do pneu',
                    ],
                ],
                'seo' => [],
                'links_internal' => [],
                'links_related' => [],
            ],
        ];

        foreach ($guides as $guideData) {

            Guide::updateOrCreate(
                ['slug' => $guideData['slug']],
                $guideData
            );
        }

        $this->command->info('Sample guides seeded successfully!');
    }
}

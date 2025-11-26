<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * Seeder de guias de exemplo
 * 
 * Baseado nos mockups HTML reais, incluindo o guia principal:
 * Toyota Corolla 2003 - Óleo do Motor
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
        $calibragemCategory = GuideCategory::where('slug', 'calibragem')->first();
        $pneusCategory = GuideCategory::where('slug', 'pneus')->first();

        if (!$oleoCategory || !$calibragemCategory || !$pneusCategory) {
            $this->command->error('✗ Categorias não encontradas. Execute GuideCategorySeeder primeiro.');
            return;
        }

        $guides = [
            // Guia Principal do Mock: Toyota Corolla 2003 - Óleo
            [
                'guide_category_id' => $oleoCategory->_id,
                'make' => 'Toyota',
                'make_slug' => 'toyota',
                'model' => 'Corolla',
                'model_slug' => 'corolla',
                'version' => 'GLi 1.8',
                'motor' => '1.8 VVT-i',
                'fuel' => 'Gasolina',
                'year_start' => 2003,
                'year_end' => 2003,
                'template' => 'oleo-motor',
                'slug' => 'toyota-corolla-oleo-2003',
                'full_title' => 'Óleo Toyota Corolla 2003 – Qual usar, Quantidade e Especificações',
                'short_title' => 'Óleo Corolla 2003',
                'url' => '/guias/oleo/toyota/corolla-2003',
                'payload' => [
                    // Dados extraídos do mock page_guia_oleo_toyota_corolla_2003.html
                    'viscosidade_manual' => '5W-30',
                    'tipo_oleo' => 'Sintético ou Semissintético',
                    'especificacao_api' => 'API SL / SM+',
                    'volume_total' => '4.2 litros (com filtro)',
                    'volume_sem_filtro' => '3.7 litros',
                    'intervalo_troca' => '10.000 km ou 12 meses',
                    'intervalo_severo' => '5.000 km',
                    'filtro' => 'Trocar sempre junto com óleo',
                    
                    // Alternativas
                    'alternativas' => [
                        [
                            'viscosidade' => '5W-30',
                            'tipo' => 'Sintético',
                            'uso' => 'Recomendado - Todas as temperaturas',
                        ],
                        [
                            'viscosidade' => '5W-40',
                            'tipo' => 'Sintético',
                            'uso' => 'Climas quentes',
                        ],
                        [
                            'viscosidade' => '10W-30',
                            'tipo' => 'Semissintético',
                            'uso' => 'Uso moderado',
                        ],
                    ],
                    
                    // Tabela de capacidades (do mock)
                    'capacidades_motor' => [
                        [
                            'motor' => '1.8 VVT-i 16V',
                            'especificacao' => '5W-30 API SL',
                            'quantidade' => '4.2L (com filtro)',
                        ],
                    ],
                    
                    // Observações do manual
                    'observacoes' => [
                        'Usar sempre óleo que atenda especificação API SL ou superior',
                        'Preferir óleos sintéticos para melhor proteção',
                        'Trocar filtro a cada troca de óleo',
                        'Verificar nível semanalmente com motor frio',
                        'Em uso severo, reduzir intervalo para 5.000 km',
                    ],
                ],
                'is_active' => true,
                'published_at' => now(),
            ],

            // Guia 2: Volkswagen Gol - Calibragem (mencionado nos mocks)
            [
                'guide_category_id' => $calibragemCategory->_id,
                'make' => 'Volkswagen',
                'make_slug' => 'volkswagen',
                'model' => 'Gol',
                'model_slug' => 'gol',
                'version' => '1.6 Total Flex',
                'motor' => '1.6',
                'fuel' => 'Flex',
                'year_start' => 2016,
                'year_end' => 2016,
                'template' => 'calibragem',
                'slug' => 'volkswagen-gol-calibragem-2016',
                'full_title' => 'Calibragem Volkswagen Gol 2016 – Pressão Correta dos Pneus',
                'short_title' => 'Calibragem Gol 2016',
                'url' => '/guias/calibragem/volkswagen/gol-2016',
                'payload' => [
                    'pressao_dianteira_vazio' => '30 PSI',
                    'pressao_traseira_vazio' => '30 PSI',
                    'pressao_dianteira_cheio' => '32 PSI',
                    'pressao_traseira_cheio' => '35 PSI',
                    'pressao_estepe' => '60 PSI',
                    'medida_pneu' => '175/70 R14',
                    'observacoes' => [
                        'Calibrar com pneus frios',
                        'Verificar pressão mensalmente',
                        'Ajustar pressão conforme carga',
                    ],
                ],
                'is_active' => true,
                'published_at' => now(),
            ],

            // Guia 3: Chevrolet Onix - Óleo (mencionado nos mocks)
            [
                'guide_category_id' => $oleoCategory->_id,
                'make' => 'Chevrolet',
                'make_slug' => 'chevrolet',
                'model' => 'Onix',
                'model_slug' => 'onix',
                'version' => '1.0 Turbo',
                'motor' => '1.0 Turbo',
                'fuel' => 'Gasolina',
                'year_start' => 2020,
                'year_end' => 2020,
                'template' => 'oleo-motor',
                'slug' => 'chevrolet-onix-oleo-2020',
                'full_title' => 'Óleo Chevrolet Onix 2020 – Especificações e Quantidade',
                'short_title' => 'Óleo Onix 2020',
                'url' => '/guias/oleo/chevrolet/onix-2020',
                'payload' => [
                    'viscosidade_manual' => '5W-30',
                    'tipo_oleo' => 'Sintético',
                    'especificacao_api' => 'API SN Plus',
                    'especificacao_acea' => 'ACEA C3',
                    'volume_total' => '4.0 litros (com filtro)',
                    'intervalo_troca' => '10.000 km ou 1 ano',
                    'observacoes' => [
                        'Motor turbo exige óleo sintético',
                        'Trocar filtro sempre junto',
                        'Verificar nível regularmente',
                    ],
                ],
                'is_active' => true,
                'published_at' => now(),
            ],

            // Guia 4: Toyota Hilux - Óleo (mencionado nos mocks)
            [
                'guide_category_id' => $oleoCategory->_id,
                'make' => 'Toyota',
                'make_slug' => 'toyota',
                'model' => 'Hilux',
                'model_slug' => 'hilux',
                'version' => '2.8 Diesel',
                'motor' => '2.8 Turbodiesel',
                'fuel' => 'Diesel',
                'year_start' => 2015,
                'year_end' => 2015,
                'template' => 'oleo-motor',
                'slug' => 'toyota-hilux-oleo-2015',
                'full_title' => 'Óleo Toyota Hilux 2015 Diesel – Especificações Completas',
                'short_title' => 'Óleo Hilux 2015',
                'url' => '/guias/oleo/toyota/hilux-2015',
                'payload' => [
                    'viscosidade_manual' => '5W-30 ou 10W-40',
                    'tipo_oleo' => 'Sintético ou Mineral',
                    'especificacao_api' => 'API CF-4 ou CJ-4',
                    'volume_total' => '6.5 litros (com filtro)',
                    'intervalo_troca' => '10.000 km ou 6 meses',
                    'observacoes' => [
                        'Diesel exige óleo específico',
                        'Trocar filtro de óleo e combustível',
                        'Drenar água do filtro separador',
                    ],
                ],
                'is_active' => true,
                'published_at' => now(),
            ],

            // Guia 5: Toyota Yaris - Óleo (mencionado nos mocks)
            [
                'guide_category_id' => $oleoCategory->_id,
                'make' => 'Toyota',
                'make_slug' => 'toyota',
                'model' => 'Yaris',
                'model_slug' => 'yaris',
                'version' => '1.5 XLS',
                'motor' => '1.5 VVT-i',
                'fuel' => 'Flex',
                'year_start' => 2019,
                'year_end' => 2019,
                'template' => 'oleo-motor',
                'slug' => 'toyota-yaris-oleo-2019',
                'full_title' => 'Óleo Toyota Yaris 2019 – Especificações para 1.3 e 1.5',
                'short_title' => 'Óleo Yaris 2019',
                'url' => '/guias/oleo/toyota/yaris-2019',
                'payload' => [
                    'viscosidade_manual' => '5W-30',
                    'tipo_oleo' => 'Sintético ou Semissintético',
                    'especificacao_api' => 'API SN ou SM',
                    'volume_total' => '3.9 litros (com filtro)',
                    'intervalo_troca' => '10.000 km ou 12 meses',
                    'observacoes' => [
                        'Motor 1.3 e 1.5 usam mesma especificação',
                        'Preferir óleo sintético',
                        'Trocar filtro junto',
                    ],
                ],
                'is_active' => true,
                'published_at' => now(),
            ],
        ];

        foreach ($guides as $guideData) {
            Guide::updateOrCreate(
                ['slug' => $guideData['slug']],
                $guideData
            );
        }

        $this->command->info('✓ 5 guias de exemplo criados com sucesso!');
        $this->command->info('  → Incluindo guia principal: Toyota Corolla 2003');
    }
}

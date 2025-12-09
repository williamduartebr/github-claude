<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * Seeder de categorias de guias - VERSÃO COMPLETA
 * 
 * Estrutura completa com:
 * - icon_svg (paths SVG)
 * - icon_bg_color e icon_text_color
 * - seo_info (JSON)
 * - info_sections (JSON)
 * - display_order
 * 
 * Baseado em MaintenanceCategoriesSeeder
 */
class GuideCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [

            // ====================================================================
            // CATEGORIA 1: ÓLEO
            // ====================================================================
            [
                'name' => 'Óleo',
                'slug' => 'oleo',
                'description' => 'Especificações e recomendações de óleos para diferentes motores e modelos.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />',
                'icon_bg_color' => 'bg-yellow-100',
                'icon_text_color' => 'text-yellow-600',
                'order' => 1,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Óleo Recomendado - Escolha o Melhor para seu Veículo | Info Center',
                    'description' => 'Guia completo de óleos recomendados para todos os modelos de veículos. Informações técnicas para manutenção adequada do motor.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'Por que o Óleo Correto é Essencial?',
                    'sections' => [
                        [
                            'title' => 'Lubrificação',
                            'content' => 'O óleo adequado garante a correta lubrificação das partes móveis do motor, reduzindo o atrito e o desgaste prematuro dos componentes.'
                        ],
                        [
                            'title' => 'Resfriamento',
                            'content' => 'O óleo ajuda a dissipar o calor do motor, mantendo a temperatura de operação ideal e evitando superaquecimento das peças internas.'
                        ],
                        [
                            'title' => 'Limpeza',
                            'content' => 'Os óleos modernos possuem aditivos detergentes que mantêm o motor limpo, prevenindo o acúmulo de borra e contaminantes.'
                        ],
                        [
                            'title' => 'Tipos de Óleo',
                            'content' => 'Minerais (troca 5-8 mil km), semissintéticos (7,5-10 mil km) e sintéticos (até 15 mil km). A escolha depende do tipo de motor e condições de uso.'
                        ]
                    ],
                    'alert' => 'Sempre verifique no manual do proprietário a especificação exata do óleo recomendado para seu veículo. O intervalo de troca varia de acordo com o tipo de óleo e condições de uso.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 2: FLUIDOS
            // ====================================================================
            [
                'name' => 'Fluidos',
                'slug' => 'fluidos',
                'description' => 'Especificações de todos os fluidos do veículo: freio, arrefecimento, direção, transmissão.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />',
                'icon_bg_color' => 'bg-blue-100',
                'icon_text_color' => 'text-blue-600',
                'order' => 2,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Fluidos Automotivos - Guia Completo | Info Center',
                    'description' => 'Confira todos os fluidos recomendados (freio, arrefecimento, direção) para manutenção adequada do seu veículo.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'A Importância dos Fluidos Corretos',
                    'sections' => [
                        [
                            'title' => 'Fluido de Freio',
                            'content' => 'Essencial para o funcionamento do sistema de freios. Deve ser trocado a cada 2 anos, pois absorve umidade e perde eficiência.'
                        ],
                        [
                            'title' => 'Fluido de Arrefecimento',
                            'content' => 'Mantém o motor na temperatura ideal. A troca varia entre 24 e 60 meses conforme especificação do fabricante.'
                        ],
                        [
                            'title' => 'Fluido de Direção',
                            'content' => 'Para sistemas hidráulicos de direção. Verifique o nível regularmente e troque conforme manual do proprietário.'
                        ],
                        [
                            'title' => 'Fluido de Transmissão',
                            'content' => 'Específico para cada tipo de transmissão (ATF, CVT, DCT). O uso do fluido incorreto pode causar falhas graves.'
                        ]
                    ],
                    'alert' => 'Nunca misture diferentes tipos de fluidos. Sempre utilize as especificações recomendadas pelo fabricante do veículo.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 3: CALIBRAGEM
            // ====================================================================
            [
                'name' => 'Calibragem',
                'slug' => 'calibragem',
                'description' => 'Pressão recomendada dos pneus para diferentes condições de carga e uso.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
                'icon_bg_color' => 'bg-cyan-100',
                'icon_text_color' => 'text-cyan-600',
                'order' => 3,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Calibragem de Pneus - Guia Completo | Info Center',
                    'description' => 'Guia completo sobre calibragem de pneus para todos os modelos. Pressão correta para segurança e economia.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'Por que a Calibragem Correta é Importante?',
                    'sections' => [
                        [
                            'title' => 'Segurança',
                            'content' => 'Pneus com pressão inadequada comprometem a estabilidade, aumentam a distância de frenagem e reduzem a aderência.'
                        ],
                        [
                            'title' => 'Economia',
                            'content' => 'A calibragem correta pode reduzir o consumo de combustível em até 3%.'
                        ],
                        [
                            'title' => 'Durabilidade',
                            'content' => 'Pneus corretamente calibrados desgastam-se uniformemente e duram até 20% mais.'
                        ],
                        [
                            'title' => 'Frequência',
                            'content' => 'Verifique a pressão pelo menos uma vez por mês e sempre antes de viagens longas, com os pneus frios.'
                        ]
                    ],
                    'alert' => 'A calibragem deve ser feita com os pneus frios. A pressão aumenta naturalmente com o aquecimento durante o uso.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 4: PNEUS
            // ====================================================================
            [
                'name' => 'Pneus',
                'slug' => 'pneus',
                'description' => 'Medidas de pneus, rodas recomendadas e especificações técnicas para diferentes modelos.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM12 10C10.9 10 10 10.9 10 12C10 13.1 10.9 14 12 14C13.1 14 14 13.1 14 12C14 10.9 13.1 10 12 10Z"></path>',
                'icon_bg_color' => 'bg-gray-100',
                'icon_text_color' => 'text-gray-600',
                'order' => 4,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Pneus Recomendados - Escolha os Melhores | Info Center',
                    'description' => 'Guia completo de pneus recomendados para todos os modelos. Medidas, especificações e dicas de segurança.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'Por que os Pneus Corretos são Essenciais?',
                    'sections' => [
                        [
                            'title' => 'Segurança',
                            'content' => 'Pneus adequados garantem melhor aderência em diferentes condições climáticas e reduzem riscos de acidentes.'
                        ],
                        [
                            'title' => 'Medidas Corretas',
                            'content' => 'As inscrições laterais indicam largura, altura, diâmetro, índice de carga e velocidade. Siga as especificações do fabricante.'
                        ],
                        [
                            'title' => 'Rodízio',
                            'content' => 'Faça o rodízio a cada 10.000 km para equilibrar o desgaste e aumentar a vida útil do conjunto.'
                        ],
                        [
                            'title' => 'Substituição',
                            'content' => 'Substitua quando a profundidade dos sulcos atingir 1,6mm (mínimo legal) ou apresentar desgaste irregular.'
                        ]
                    ],
                    'alert' => 'Verifique regularmente a pressão e o estado dos pneus. Sulcos rasos, rachaduras ou deformações exigem substituição imediata.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 5: BATERIA
            // ====================================================================
            [
                'name' => 'Bateria',
                'slug' => 'bateria',
                'description' => 'Especificações da bateria, amperagem e sistema elétrico para diferentes veículos.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
                'icon_bg_color' => 'bg-indigo-100',
                'icon_text_color' => 'text-indigo-600',
                'order' => 5,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Bateria Automotiva - Guia Completo | Info Center',
                    'description' => 'Tudo sobre baterias automotivas: tipos, especificações, manutenção e quando fazer a troca.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'Cuidados Essenciais com a Bateria',
                    'sections' => [
                        [
                            'title' => 'Vida Útil',
                            'content' => 'A vida útil média é de 2 a 5 anos, dependendo das condições de uso e clima da região.'
                        ],
                        [
                            'title' => 'Sinais de Desgaste',
                            'content' => 'Dificuldade na partida, luzes que enfraquecem ao dar partida, necessidade frequente de auxílio.'
                        ],
                        [
                            'title' => 'Amperagem Correta',
                            'content' => 'Ao substituir, escolha uma bateria com amperagem idêntica à recomendada pelo fabricante.'
                        ],
                        [
                            'title' => 'Manutenção',
                            'content' => 'Mantenha os terminais limpos e bem conectados. Verifique regularmente se há corrosão.'
                        ]
                    ],
                    'alert' => 'Evite deixar equipamentos ligados com o motor desligado por longos períodos. Em caso de não utilização por mais de 15 dias, considere desconectar o terminal negativo.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 6: REVISÃO
            // ====================================================================
            [
                'name' => 'Revisão',
                'slug' => 'revisao',
                'description' => 'Plano de manutenção preventiva, revisões programadas e itens verificados.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                'icon_bg_color' => 'bg-green-100',
                'icon_text_color' => 'text-green-600',
                'order' => 6,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Revisões Programadas - Guia Completo | Info Center',
                    'description' => 'Informações sobre revisões periódicas e manutenção preventiva para manter seu veículo sempre em perfeitas condições.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'A Importância das Revisões Programadas',
                    'sections' => [
                        [
                            'title' => 'Intervalos',
                            'content' => 'Geralmente a cada 10.000 km ou anualmente (o que ocorrer primeiro). Consulte o manual do proprietário.'
                        ],
                        [
                            'title' => 'Itens Verificados',
                            'content' => 'Lubrificação, freios, suspensão, direção, arrefecimento, elétrico, transmissão e sistemas de segurança.'
                        ],
                        [
                            'title' => 'Garantia',
                            'content' => 'Realizar as revisões na periodicidade recomendada é fundamental para manter a garantia do fabricante.'
                        ],
                        [
                            'title' => 'Economia',
                            'content' => 'A manutenção preventiva é sempre mais econômica que a corretiva. Detecta problemas antes que se agravem.'
                        ]
                    ],
                    'alert' => 'Mantenha um registro detalhado de todas as revisões. Este histórico valoriza o veículo na revenda.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 7: CONSUMO
            // ====================================================================
            [
                'name' => 'Consumo',
                'slug' => 'consumo',
                'description' => 'Médias de consumo em cidade, estrada e misto. Dicas de economia de combustível.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />',
                'icon_bg_color' => 'bg-orange-100',
                'icon_text_color' => 'text-orange-600',
                'order' => 7,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Consumo de Combustível - Guia Completo | Info Center',
                    'description' => 'Consumo real, dicas de economia e comparativos de combustível para todos os modelos de veículos.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'Entendendo o Consumo de Combustível',
                    'sections' => [
                        [
                            'title' => 'Consumo Real vs. Oficial',
                            'content' => 'Os valores oficiais são medidos em condições controladas. O consumo real varia conforme estilo de condução e condições de uso.'
                        ],
                        [
                            'title' => 'Fatores que Afetam',
                            'content' => 'Peso do veículo, pressão dos pneus, manutenção, qualidade do combustível, trânsito, ar-condicionado e estilo de condução.'
                        ],
                        [
                            'title' => 'Gasolina vs. Etanol',
                            'content' => 'Em veículos flex, o etanol rende cerca de 30% menos. Para ser vantajoso, deve custar no máximo 70% do preço da gasolina.'
                        ],
                        [
                            'title' => 'Dicas de Economia',
                            'content' => 'Mantenha pneus calibrados, evite arrancadas bruscas, antecipe frenagens, use ar-condicionado com moderação.'
                        ]
                    ],
                    'alert' => 'Aumento repentino no consumo pode indicar problemas mecânicos. Procure um profissional para diagnóstico.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 8: CÂMBIO
            // ====================================================================
            [
                'name' => 'Câmbio',
                'slug' => 'cambio',
                'description' => 'Informações sobre câmbio, tipo de transmissão, óleo e manutenção preventiva.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />',
                'icon_bg_color' => 'bg-purple-100',
                'icon_text_color' => 'text-purple-600',
                'order' => 8,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Transmissão Automotiva - Guia Completo | Info Center',
                    'description' => 'Informações sobre transmissões manuais, automáticas e CVT. Manutenção e fluidos corretos.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'Manutenção da Transmissão',
                    'sections' => [
                        [
                            'title' => 'Transmissão Manual',
                            'content' => 'Troca do óleo a cada 30-60 mil km. Sintomas de problemas: dificuldade para engrenar marchas e rangidos.'
                        ],
                        [
                            'title' => 'Transmissão Automática',
                            'content' => 'Fluido ATF deve ser trocado entre 40-100 mil km. Fluido escuro ou com cheiro queimado indica problema.'
                        ],
                        [
                            'title' => 'Transmissão CVT',
                            'content' => 'Exige fluidos específicos. O uso de fluido incorreto pode causar danos irreparáveis.'
                        ],
                        [
                            'title' => 'Embreagem',
                            'content' => 'Vida útil média de 60-150 mil km. Patinagem, vibração ou ruídos são sinais de desgaste.'
                        ]
                    ],
                    'alert' => 'A transmissão é um dos componentes mais caros para reparo. Manutenção preventiva regular é essencial.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 9: ARREFECIMENTO
            // ====================================================================
            [
                'name' => 'Arrefecimento',
                'slug' => 'arrefecimento',
                'description' => 'Sistema de arrefecimento, radiador, líquido de arrefecimento e prevenção de superaquecimento.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
                'icon_bg_color' => 'bg-teal-100',
                'icon_text_color' => 'text-teal-600',
                'order' => 9,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Sistema de Arrefecimento - Guia Completo | Info Center',
                    'description' => 'Informações sobre sistema de arrefecimento, radiador e líquido. Evite superaquecimento do motor.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'Componentes do Sistema de Arrefecimento',
                    'sections' => [
                        [
                            'title' => 'Radiador',
                            'content' => 'Responsável pela troca de calor. Deve ser mantido limpo externamente e inspecionado quanto a vazamentos.'
                        ],
                        [
                            'title' => 'Bomba d\'Água',
                            'content' => 'Circula o líquido pelo motor e radiador. Sua falha causa superaquecimento rápido e danos graves.'
                        ],
                        [
                            'title' => 'Líquido de Arrefecimento',
                            'content' => 'Deve ser trocado entre 24 e 60 meses. Fluido escurecido indica contaminação.'
                        ],
                        [
                            'title' => 'Ventoinha',
                            'content' => 'Força a passagem de ar pelo radiador em baixas velocidades. Sua falha causa superaquecimento em tráfego.'
                        ]
                    ],
                    'alert' => 'NUNCA abra a tampa do radiador com o motor quente! A pressão pode causar graves queimaduras.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 10: SUSPENSÃO
            // ====================================================================
            [
                'name' => 'Suspensão',
                'slug' => 'suspensao',
                'description' => 'Informações sobre sistemas de suspensão, amortecedores, molas e componentes.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />',
                'icon_bg_color' => 'bg-pink-100',
                'icon_text_color' => 'text-pink-600',
                'order' => 10,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Suspensão e Amortecedores - Guia Completo | Info Center',
                    'description' => 'Tudo sobre suspensão automotiva, amortecedores e componentes para conforto e segurança.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'Sistema de Suspensão e Amortecedores',
                    'sections' => [
                        [
                            'title' => 'Função Principal',
                            'content' => 'Os amortecedores controlam oscilações, mantendo pneus em contato com o solo. A suspensão absorve impactos e garante estabilidade.'
                        ],
                        [
                            'title' => 'Sinais de Problemas',
                            'content' => 'Veículo "pula" em irregularidades, balança excessivamente em curvas, apresenta ruídos e batidas secas.'
                        ],
                        [
                            'title' => 'Tipos de Suspensão',
                            'content' => 'MacPherson, duplo A, multilink, eixo rígido. Cada sistema possui características específicas.'
                        ],
                        [
                            'title' => 'Manutenção',
                            'content' => 'Verificação regular das buchas, bandejas, pivôs. Amortecedores devem ser substituídos em pares no mesmo eixo.'
                        ]
                    ],
                    'alert' => 'Amortecedores desgastados podem aumentar a distância de frenagem em até 20%. Verifique a cada 20.000 km.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 11: PROBLEMAS
            // ====================================================================
            [
                'name' => 'Problemas Comuns',
                'slug' => 'problemas',
                'description' => 'Problemas conhecidos do modelo, soluções e custos estimados de reparo.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
                'icon_bg_color' => 'bg-red-100',
                'icon_text_color' => 'text-red-600',
                'order' => 11,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Problemas Comuns - Soluções e Custos | Info Center',
                    'description' => 'Conheça os problemas mais comuns do seu modelo, soluções possíveis e custos estimados de reparo.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'Identificando e Solucionando Problemas',
                    'sections' => [
                        [
                            'title' => 'Problemas Recorrentes',
                            'content' => 'Alguns modelos apresentam falhas conhecidas em determinados componentes. Conhecê-las ajuda na prevenção.'
                        ],
                        [
                            'title' => 'Diagnóstico',
                            'content' => 'Identifique sintomas, verifique códigos de falha e busque padrões relatados por outros proprietários.'
                        ],
                        [
                            'title' => 'Soluções',
                            'content' => 'Algumas falhas têm soluções definitivas, outras exigem monitoramento constante. Entenda as opções disponíveis.'
                        ],
                        [
                            'title' => 'Custos',
                            'content' => 'Tenha noção dos custos médios de reparo para planejar manutenções e evitar surpresas financeiras.'
                        ]
                    ],
                    'alert' => 'Se seu modelo possui problemas recorrentes conhecidos, a manutenção preventiva redobrada é essencial.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 12: RECALLS
            // ====================================================================
            [
                'name' => 'Recalls',
                'slug' => 'recalls',
                'description' => 'Informações sobre campanhas de recall, verificação e atualização do veículo.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
                'icon_bg_color' => 'bg-yellow-100',
                'icon_text_color' => 'text-yellow-600',
                'order' => 12,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Recall e Campanhas - Guia Completo | Info Center',
                    'description' => 'Informações sobre campanhas de recall, como verificar pendências e garantir segurança do veículo.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'Entendendo os Recalls Automotivos',
                    'sections' => [
                        [
                            'title' => 'O que é Recall',
                            'content' => 'Convocação para reparo gratuito de defeitos que apresentam risco à segurança ou meio ambiente. É obrigação do fabricante.'
                        ],
                        [
                            'title' => 'Como Verificar',
                            'content' => 'Consulte pelo chassi no site do fabricante, Portal de Recalls do Ministério da Justiça ou concessionárias.'
                        ],
                        [
                            'title' => 'Consequências de Ignorar',
                            'content' => 'Riscos à segurança, problemas com seguro, dificuldade na venda e possível impedimento do licenciamento.'
                        ],
                        [
                            'title' => 'Prazo',
                            'content' => 'Recalls não têm prazo de validade e podem ser realizados gratuitamente mesmo fora da garantia.'
                        ]
                    ],
                    'alert' => 'Se você adquiriu um veículo usado, verifique recalls pendentes, pois a comunicação pode não ter chegado até você.'
                ])
            ],

            // ====================================================================
            // CATEGORIA 13: COMPARAÇÕES
            // ====================================================================
            [
                'name' => 'Comparações',
                'slug' => 'comparacoes',
                'description' => 'Comparativos entre versões do mesmo modelo ou modelos similares no mercado.',
                'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                'icon_bg_color' => 'bg-blue-100',
                'icon_text_color' => 'text-blue-600',
                'order' => 13,
                'is_active' => true,
                'seo_info' => json_encode([
                    'title' => 'Comparações de Veículos - Guia Completo | Info Center',
                    'description' => 'Comparativos detalhados entre versões e modelos. Ajudamos você a escolher o melhor veículo.'
                ]),
                'info_sections' => json_encode([
                    'title' => 'Como Comparar Veículos Corretamente',
                    'sections' => [
                        [
                            'title' => 'Preço e Equipamentos',
                            'content' => 'Compare o custo-benefício considerando itens de série, opcionais e diferenças de valor entre versões.'
                        ],
                        [
                            'title' => 'Desempenho',
                            'content' => 'Avalie potência, torque, aceleração e retomadas. Considere o uso principal do veículo.'
                        ],
                        [
                            'title' => 'Consumo',
                            'content' => 'Médias reais de consumo impactam diretamente no custo de manutenção mensal do veículo.'
                        ],
                        [
                            'title' => 'Custo de Manutenção',
                            'content' => 'Pesquise valores de revisões, peças e disponibilidade de serviços para os modelos comparados.'
                        ]
                    ],
                    'alert' => 'Não considere apenas o preço de compra. O custo total de propriedade inclui manutenção, seguro e desvalorização.'
                ])
            ],

        ];

        // ====================================================================
        // CRIAÇÃO DAS CATEGORIAS
        // ====================================================================
        foreach ($categories as $key => $category) {
            $category['display_order'] = $key + 1;
            
            GuideCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('✓ ' . count($categories) . ' categorias de guias criadas/atualizadas com sucesso!');
    }
}
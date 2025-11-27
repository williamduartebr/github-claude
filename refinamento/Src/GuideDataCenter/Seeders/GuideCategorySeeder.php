<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * Seeder de Categorias de Guias - Portal Automotivo
 *
 * Este seeder contém as categorias de guias técnicos e informativos,
 * estruturado para ser escalável via API Claude (Sonnet 4.5 / Haiku 4.5).
 *
 * Distribuição semanal planejada (2.100 artigos):
 * - /guias (técnicos): 1.300 artigos/semana (62%)
 * - /veiculos (fichas): 800 artigos/semana (38%)
 *
 * Prioridade de categorias (por volume):
 * 1. Óleo recomendado: 400/semana
 * 2. Calibragem pneus: 300/semana
 * 3. Revisões: 250/semana
 * 4. Problemas comuns: 150/semana
 * 5. Consumo: 100/semana
 * 6. Outros guias: 100/semana
 *
 * @author Claude AI Assistant
 * @version 1.0.0
 */
class GuideCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = $this->getCategoriesData();

        foreach ($categories as $category) {
            GuideCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('✅ ' . count($categories) . ' categorias de guias inseridas com sucesso!');
    }

    /**
     * Retorna dados das categorias de guias
     *
     * Estruturado com metadados para geração automática via API:
     * - priority: ordem de prioridade na geração (1 = mais importante)
     * - weekly_target: meta semanal de artigos
     * - templates: templates disponíveis para a categoria
     * - ai_prompt_hints: dicas para prompts de geração via Claude
     *
     * @return array<int, array<string, mixed>>
     */
    private function getCategoriesData(): array
    {
        return [
            // ═══════════════════════════════════════════════════════════════
            // CATEGORIA PRINCIPAL - ÓLEO (400 artigos/semana)
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Óleo do Motor',
                'slug' => 'oleo',
                'description' => 'Guias completos sobre óleo do motor: tipo, viscosidade, quantidade, intervalo de troca e especificações por veículo.',
                'icon' => 'fa-oil-can',
                'color' => '#F59E0B', // Amber
                'order' => 1,
                'is_active' => true,
                'priority' => 1,
                'weekly_target' => 400,
                'seo' => [
                    'title_template' => 'Óleo {make} {model} {year} – Qual usar, Quantidade e Especificações',
                    'meta_description_template' => 'Guia completo do óleo do {make} {model} {year}: viscosidade recomendada, volume correto, especificações API/ACEA, melhores marcas e intervalos de troca.',
                    'h1_template' => 'Óleo do Motor {make} {model} {year}',
                ],
                'templates' => [
                    'oleo-motor' => [
                        'name' => 'Guia de Óleo do Motor',
                        'fields' => [
                            'viscosidade_recomendada',
                            'especificacao_api',
                            'especificacao_acea',
                            'volume_com_filtro',
                            'volume_sem_filtro',
                            'intervalo_troca_km',
                            'intervalo_troca_meses',
                            'tipo_oleo', // mineral, semi, sintetico
                            'marcas_recomendadas',
                            'oleos_alternativos',
                            'filtro_oleo_codigo',
                            'observacoes',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'especificações técnicas precisas',
                    'sources' => 'manual do proprietário, fabricante',
                    'tone' => 'técnico mas acessível',
                    'include' => ['tabela de capacidades', 'alternativas', 'dicas de troca'],
                ],
                'metadata' => [
                    'related_categories' => ['fluidos', 'revisao', 'motor'],
                    'search_keywords' => ['óleo motor', 'troca óleo', 'viscosidade', 'API', 'ACEA', 'sintético'],
                    'schema_type' => 'HowTo',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // CALIBRAGEM DE PNEUS (300 artigos/semana)
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Calibragem de Pneus',
                'slug' => 'calibragem',
                'description' => 'Pressão correta dos pneus por modelo de veículo. Calibragem dianteira, traseira, com carga e estepe.',
                'icon' => 'fa-gauge-high',
                'color' => '#3B82F6', // Blue
                'order' => 2,
                'is_active' => true,
                'priority' => 2,
                'weekly_target' => 300,
                'seo' => [
                    'title_template' => 'Calibragem {make} {model} {year} – Pressão dos Pneus (PSI/BAR)',
                    'meta_description_template' => 'Pressão correta dos pneus do {make} {model} {year}: calibragem dianteira, traseira, estepe e com carga. Tabela completa em PSI e BAR.',
                    'h1_template' => 'Calibragem de Pneus {make} {model} {year}',
                ],
                'templates' => [
                    'calibragem-pneus' => [
                        'name' => 'Guia de Calibragem',
                        'fields' => [
                            'pressao_dianteira_psi',
                            'pressao_dianteira_bar',
                            'pressao_traseira_psi',
                            'pressao_traseira_bar',
                            'pressao_com_carga_psi',
                            'pressao_com_carga_bar',
                            'pressao_estepe_psi',
                            'pressao_estepe_bar',
                            'medida_pneu_original',
                            'medidas_alternativas',
                            'tipo_estepe', // temporário, convencional
                            'observacoes',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'valores precisos de pressão',
                    'sources' => 'etiqueta da porta, manual',
                    'tone' => 'direto e prático',
                    'include' => ['tabela PSI/BAR', 'variações com carga', 'dicas de calibragem'],
                ],
                'metadata' => [
                    'related_categories' => ['pneus', 'rodas', 'suspensao'],
                    'search_keywords' => ['calibragem', 'pressão pneu', 'PSI', 'BAR', 'pneu murcho'],
                    'schema_type' => 'HowTo',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // PNEUS E RODAS
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Pneus e Rodas',
                'slug' => 'pneus',
                'description' => 'Medidas de pneus originais e alternativos, especificações de rodas, índice de carga e velocidade.',
                'icon' => 'fa-circle',
                'color' => '#1F2937', // Gray Dark
                'order' => 3,
                'is_active' => true,
                'priority' => 4,
                'weekly_target' => 50,
                'seo' => [
                    'title_template' => 'Pneus {make} {model} {year} – Medidas Originais e Alternativas',
                    'meta_description_template' => 'Medidas de pneus do {make} {model} {year}: pneus originais, alternativos permitidos, especificações de rodas e índices de carga/velocidade.',
                    'h1_template' => 'Pneus e Rodas {make} {model} {year}',
                ],
                'templates' => [
                    'pneus-rodas' => [
                        'name' => 'Guia de Pneus e Rodas',
                        'fields' => [
                            'medida_original_dianteiro',
                            'medida_original_traseiro',
                            'medidas_alternativas',
                            'aro_original',
                            'aros_alternativos',
                            'largura_roda',
                            'offset_et',
                            'furação',
                            'centro_roda_cb',
                            'indice_carga',
                            'indice_velocidade',
                            'tipo_pneu_recomendado',
                            'marcas_recomendadas',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'medidas e compatibilidades',
                    'sources' => 'manual, fabricante de pneus',
                    'tone' => 'técnico com explicações',
                    'include' => ['tabela de medidas', 'alternativas', 'explicação dos índices'],
                ],
                'metadata' => [
                    'related_categories' => ['calibragem', 'suspensao', 'alinhamento'],
                    'search_keywords' => ['pneu', 'roda', 'aro', 'medida pneu', 'offset', 'furação'],
                    'schema_type' => 'Product',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // REVISÕES (250 artigos/semana)
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Revisões e Manutenção',
                'slug' => 'revisao',
                'description' => 'Tabelas de revisão, intervalos de manutenção, itens a verificar e custos estimados por quilometragem.',
                'icon' => 'fa-wrench',
                'color' => '#10B981', // Emerald
                'order' => 4,
                'is_active' => true,
                'priority' => 3,
                'weekly_target' => 250,
                'seo' => [
                    'title_template' => 'Revisão {make} {model} {year} – Tabela Completa de Manutenção',
                    'meta_description_template' => 'Tabela de revisões do {make} {model} {year}: intervalos, itens a trocar, peças necessárias e custos estimados por quilometragem.',
                    'h1_template' => 'Revisões e Manutenção {make} {model} {year}',
                ],
                'templates' => [
                    'revisao-programada' => [
                        'name' => 'Tabela de Revisão Programada',
                        'fields' => [
                            'revisoes' => [
                                'km',
                                'itens',
                                'custo_estimado',
                                'tempo_servico',
                            ],
                            'intervalos_basicos' => [
                                'troca_oleo_km',
                                'troca_filtro_ar_km',
                                'troca_filtro_combustivel_km',
                                'troca_filtro_cabine_km',
                                'troca_velas_km',
                                'troca_correia_dentada_km',
                                'troca_fluido_freio_km',
                                'troca_fluido_arrefecimento_km',
                            ],
                            'observacoes',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'tabela detalhada de revisões',
                    'sources' => 'manual de manutenção, concessionárias',
                    'tone' => 'organizado e completo',
                    'include' => ['tabela por km', 'custos médios', 'dicas de economia'],
                ],
                'metadata' => [
                    'related_categories' => ['oleo', 'fluidos', 'filtros'],
                    'search_keywords' => ['revisão', 'manutenção', 'tabela revisão', 'km troca', 'custo revisão'],
                    'schema_type' => 'HowTo',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // PROBLEMAS COMUNS (150 artigos/semana)
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Problemas Comuns',
                'slug' => 'problemas',
                'description' => 'Defeitos conhecidos, recalls, problemas frequentes e soluções para cada modelo de veículo.',
                'icon' => 'fa-triangle-exclamation',
                'color' => '#EF4444', // Red
                'order' => 5,
                'is_active' => true,
                'priority' => 4,
                'weekly_target' => 150,
                'seo' => [
                    'title_template' => 'Problemas {make} {model} {year} – Defeitos Comuns e Soluções',
                    'meta_description_template' => 'Principais problemas do {make} {model} {year}: defeitos conhecidos, recalls, falhas frequentes, custos de reparo e soluções comprovadas.',
                    'h1_template' => 'Problemas Comuns {make} {model} {year}',
                ],
                'templates' => [
                    'problemas-comuns' => [
                        'name' => 'Guia de Problemas Comuns',
                        'fields' => [
                            'problemas' => [
                                'titulo',
                                'descricao',
                                'sintomas',
                                'causa',
                                'solucao',
                                'custo_estimado',
                                'gravidade', // baixa, media, alta, critica
                                'frequencia', // raro, comum, muito_comum
                            ],
                            'recalls' => [
                                'numero',
                                'descricao',
                                'data',
                                'solucao',
                            ],
                            'pontos_fracos',
                            'pontos_fortes',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'problemas reais relatados por proprietários',
                    'sources' => 'fóruns, reclame aqui, recalls oficiais',
                    'tone' => 'informativo e útil',
                    'include' => ['lista de problemas', 'soluções', 'custos', 'recalls'],
                ],
                'metadata' => [
                    'related_categories' => ['revisao', 'motor', 'eletrica'],
                    'search_keywords' => ['problema', 'defeito', 'recall', 'falha', 'quebra', 'barulho'],
                    'schema_type' => 'FAQPage',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // CONSUMO (100 artigos/semana)
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Consumo de Combustível',
                'slug' => 'consumo',
                'description' => 'Consumo médio na cidade, estrada e combinado. Comparações e dicas para economizar combustível.',
                'icon' => 'fa-gas-pump',
                'color' => '#8B5CF6', // Violet
                'order' => 6,
                'is_active' => true,
                'priority' => 5,
                'weekly_target' => 100,
                'seo' => [
                    'title_template' => 'Consumo {make} {model} {year} – Média km/l Cidade e Estrada',
                    'meta_description_template' => 'Consumo real do {make} {model} {year}: média km/l na cidade, estrada e combinado. Etanol vs gasolina, autonomia e dicas de economia.',
                    'h1_template' => 'Consumo de Combustível {make} {model} {year}',
                ],
                'templates' => [
                    'consumo-combustivel' => [
                        'name' => 'Guia de Consumo',
                        'fields' => [
                            'consumo_cidade_gasolina',
                            'consumo_estrada_gasolina',
                            'consumo_combinado_gasolina',
                            'consumo_cidade_etanol',
                            'consumo_estrada_etanol',
                            'consumo_combinado_etanol',
                            'autonomia_tanque_cheio',
                            'capacidade_tanque',
                            'consumo_inmetro',
                            'consumo_real_proprietarios',
                            'dicas_economia',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'consumo real vs declarado',
                    'sources' => 'INMETRO, proprietários, testes',
                    'tone' => 'comparativo e prático',
                    'include' => ['tabela consumo', 'autonomia', 'dicas economia', 'etanol vs gasolina'],
                ],
                'metadata' => [
                    'related_categories' => ['motor', 'combustivel'],
                    'search_keywords' => ['consumo', 'km/l', 'economia', 'gasolina', 'etanol', 'autonomia'],
                    'schema_type' => 'Product',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // FLUIDOS E CAPACIDADES
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Fluidos e Capacidades',
                'slug' => 'fluidos',
                'description' => 'Especificações de todos os fluidos: arrefecimento, freio, direção, câmbio. Capacidades e tipos.',
                'icon' => 'fa-droplet',
                'color' => '#06B6D4', // Cyan
                'order' => 7,
                'is_active' => true,
                'priority' => 6,
                'weekly_target' => 30,
                'seo' => [
                    'title_template' => 'Fluidos {make} {model} {year} – Capacidades e Especificações',
                    'meta_description_template' => 'Guia completo de fluidos do {make} {model} {year}: arrefecimento, freio, direção hidráulica, câmbio. Tipos, capacidades e intervalos de troca.',
                    'h1_template' => 'Fluidos e Capacidades {make} {model} {year}',
                ],
                'templates' => [
                    'fluidos-capacidades' => [
                        'name' => 'Tabela de Fluidos',
                        'fields' => [
                            'fluido_arrefecimento' => [
                                'tipo',
                                'cor',
                                'capacidade',
                                'intervalo_troca',
                            ],
                            'fluido_freio' => [
                                'especificacao', // DOT3, DOT4, DOT5.1
                                'capacidade',
                                'intervalo_troca',
                            ],
                            'fluido_direcao' => [
                                'tipo',
                                'capacidade',
                            ],
                            'fluido_cambio_manual' => [
                                'tipo',
                                'capacidade',
                                'intervalo_troca',
                            ],
                            'fluido_cambio_automatico' => [
                                'tipo',
                                'capacidade',
                                'intervalo_troca',
                            ],
                            'fluido_diferencial' => [
                                'tipo',
                                'capacidade',
                            ],
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'tabela completa de fluidos',
                    'sources' => 'manual de manutenção',
                    'tone' => 'técnico e organizado',
                    'include' => ['tabela completa', 'especificações', 'capacidades', 'intervalos'],
                ],
                'metadata' => [
                    'related_categories' => ['oleo', 'revisao', 'cambio', 'arrefecimento'],
                    'search_keywords' => ['fluido', 'capacidade', 'arrefecimento', 'freio', 'câmbio', 'direção'],
                    'schema_type' => 'HowTo',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // BATERIA
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Bateria',
                'slug' => 'bateria',
                'description' => 'Especificações da bateria original, amperagem, dimensões, polaridade e modelos compatíveis.',
                'icon' => 'fa-car-battery',
                'color' => '#FBBF24', // Yellow
                'order' => 8,
                'is_active' => true,
                'priority' => 7,
                'weekly_target' => 20,
                'seo' => [
                    'title_template' => 'Bateria {make} {model} {year} – Amperagem e Modelos Compatíveis',
                    'meta_description_template' => 'Bateria do {make} {model} {year}: amperagem original (Ah), dimensões, polaridade, CCA e modelos de bateria compatíveis.',
                    'h1_template' => 'Bateria {make} {model} {year}',
                ],
                'templates' => [
                    'bateria' => [
                        'name' => 'Guia de Bateria',
                        'fields' => [
                            'amperagem_ah',
                            'cca',
                            'voltagem',
                            'dimensoes' => [
                                'comprimento_mm',
                                'largura_mm',
                                'altura_mm',
                            ],
                            'polaridade', // direita, esquerda
                            'tipo_terminal',
                            'modelo_original',
                            'modelos_compativeis',
                            'vida_util_estimada',
                            'preco_medio',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'especificações precisas',
                    'sources' => 'fabricantes de bateria, manual',
                    'tone' => 'direto e informativo',
                    'include' => ['specs originais', 'compatíveis', 'dicas de manutenção'],
                ],
                'metadata' => [
                    'related_categories' => ['eletrica', 'partida'],
                    'search_keywords' => ['bateria', 'amperagem', 'Ah', 'CCA', 'troca bateria'],
                    'schema_type' => 'Product',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // SISTEMA ELÉTRICO
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Sistema Elétrico',
                'slug' => 'eletrica',
                'description' => 'Fusíveis, relés, lâmpadas, sensores e componentes elétricos. Localização e especificações.',
                'icon' => 'fa-bolt',
                'color' => '#F59E0B', // Amber
                'order' => 9,
                'is_active' => true,
                'priority' => 8,
                'weekly_target' => 20,
                'seo' => [
                    'title_template' => 'Sistema Elétrico {make} {model} {year} – Fusíveis, Lâmpadas e Relés',
                    'meta_description_template' => 'Guia elétrico do {make} {model} {year}: mapa de fusíveis, especificações de lâmpadas, relés e códigos de sensores.',
                    'h1_template' => 'Sistema Elétrico {make} {model} {year}',
                ],
                'templates' => [
                    'sistema-eletrico' => [
                        'name' => 'Guia do Sistema Elétrico',
                        'fields' => [
                            'fusíveis' => [
                                'localizacao_caixa_motor',
                                'localizacao_caixa_painel',
                                'mapa_fusíveis',
                            ],
                            'lampadas' => [
                                'farol_baixo',
                                'farol_alto',
                                'luz_posicao',
                                'seta_dianteira',
                                'seta_traseira',
                                'luz_freio',
                                'luz_re',
                                'luz_placa',
                                'luz_interna',
                            ],
                            'reles_principais',
                            'sensores_importantes',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'mapa de fusíveis e lâmpadas',
                    'sources' => 'manual, esquemas elétricos',
                    'tone' => 'técnico com ilustrações descritivas',
                    'include' => ['tabela fusíveis', 'tabela lâmpadas', 'localização'],
                ],
                'metadata' => [
                    'related_categories' => ['bateria', 'lampadas'],
                    'search_keywords' => ['fusível', 'lâmpada', 'relé', 'elétrica', 'sensor'],
                    'schema_type' => 'HowTo',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // CÂMBIO E TRANSMISSÃO
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Câmbio e Transmissão',
                'slug' => 'cambio',
                'description' => 'Especificações do câmbio manual e automático, fluidos, relação de marchas e manutenção.',
                'icon' => 'fa-gears',
                'color' => '#64748B', // Slate
                'order' => 10,
                'is_active' => true,
                'priority' => 9,
                'weekly_target' => 15,
                'seo' => [
                    'title_template' => 'Câmbio {make} {model} {year} – Especificações e Manutenção',
                    'meta_description_template' => 'Câmbio do {make} {model} {year}: tipo, número de marchas, óleo do câmbio, relação de marchas e intervalos de manutenção.',
                    'h1_template' => 'Câmbio e Transmissão {make} {model} {year}',
                ],
                'templates' => [
                    'cambio-transmissao' => [
                        'name' => 'Guia de Câmbio',
                        'fields' => [
                            'tipo_cambio', // manual, automatico, cvt, dct, amt
                            'numero_marchas',
                            'fabricante_cambio',
                            'modelo_cambio',
                            'oleo_cambio' => [
                                'tipo',
                                'capacidade',
                                'intervalo_troca',
                            ],
                            'relacao_marchas',
                            'relacao_diferencial',
                            'problemas_conhecidos',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'especificações técnicas do câmbio',
                    'sources' => 'manual técnico, fabricante',
                    'tone' => 'técnico',
                    'include' => ['tipo câmbio', 'fluido', 'manutenção', 'relação marchas'],
                ],
                'metadata' => [
                    'related_categories' => ['fluidos', 'embreagem'],
                    'search_keywords' => ['câmbio', 'transmissão', 'marcha', 'óleo câmbio', 'CVT', 'automático'],
                    'schema_type' => 'HowTo',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // SISTEMA DE ARREFECIMENTO
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Sistema de Arrefecimento',
                'slug' => 'arrefecimento',
                'description' => 'Fluido de arrefecimento, radiador, válvula termostática, bomba d\'água e mangueiras.',
                'icon' => 'fa-temperature-low',
                'color' => '#0EA5E9', // Sky
                'order' => 11,
                'is_active' => true,
                'priority' => 10,
                'weekly_target' => 15,
                'seo' => [
                    'title_template' => 'Arrefecimento {make} {model} {year} – Fluido, Radiador e Manutenção',
                    'meta_description_template' => 'Sistema de arrefecimento do {make} {model} {year}: tipo de fluido, capacidade, válvula termostática, temperatura normal e manutenção.',
                    'h1_template' => 'Sistema de Arrefecimento {make} {model} {year}',
                ],
                'templates' => [
                    'arrefecimento' => [
                        'name' => 'Guia de Arrefecimento',
                        'fields' => [
                            'fluido' => [
                                'tipo', // orgânico, inorgânico, híbrido
                                'cor',
                                'especificacao',
                                'capacidade_sistema',
                                'intervalo_troca',
                            ],
                            'termostato' => [
                                'temperatura_abertura',
                                'codigo_peca',
                            ],
                            'temperatura_normal_operacao',
                            'pressao_tampa_radiador',
                            'componentes_sistema',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'manutenção do sistema',
                    'sources' => 'manual técnico',
                    'tone' => 'técnico mas acessível',
                    'include' => ['fluido correto', 'temperatura', 'manutenção preventiva'],
                ],
                'metadata' => [
                    'related_categories' => ['fluidos', 'motor', 'revisao'],
                    'search_keywords' => ['arrefecimento', 'radiador', 'água', 'aditivo', 'termostato', 'superaquecimento'],
                    'schema_type' => 'HowTo',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // TORQUE DE PARAFUSOS
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Torque de Parafusos',
                'slug' => 'torque',
                'description' => 'Tabela de torque: rodas, cabeçote, cárter, suspensão e demais componentes do veículo.',
                'icon' => 'fa-screwdriver-wrench',
                'color' => '#78716C', // Stone
                'order' => 12,
                'is_active' => true,
                'priority' => 11,
                'weekly_target' => 10,
                'seo' => [
                    'title_template' => 'Torque {make} {model} {year} – Tabela de Aperto (Nm/kgf.m)',
                    'meta_description_template' => 'Tabela de torque do {make} {model} {year}: rodas, cabeçote, cárter, bielas, mancais e suspensão. Valores em Nm e kgf.m.',
                    'h1_template' => 'Torque de Parafusos {make} {model} {year}',
                ],
                'templates' => [
                    'torque-parafusos' => [
                        'name' => 'Tabela de Torque',
                        'fields' => [
                            'torque_rodas_nm',
                            'torque_rodas_kgfm',
                            'torque_cabeçote',
                            'torque_biela',
                            'torque_mancal',
                            'torque_carter',
                            'torque_velas',
                            'torque_suspensao',
                            'torque_freio',
                            'outros_torques',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'valores precisos de torque',
                    'sources' => 'manual de serviço',
                    'tone' => 'técnico e preciso',
                    'include' => ['tabela completa', 'sequência aperto cabeçote', 'conversão Nm/kgf.m'],
                ],
                'metadata' => [
                    'related_categories' => ['motor', 'suspensao', 'freio'],
                    'search_keywords' => ['torque', 'aperto', 'Nm', 'kgf', 'parafuso roda', 'cabeçote'],
                    'schema_type' => 'HowTo',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // ESPECIFICAÇÕES DO MOTOR
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Motor e Especificações',
                'slug' => 'motor',
                'description' => 'Especificações técnicas do motor: cilindrada, potência, torque, alimentação, comando de válvulas.',
                'icon' => 'fa-car-side',
                'color' => '#DC2626', // Red
                'order' => 13,
                'is_active' => true,
                'priority' => 12,
                'weekly_target' => 20,
                'seo' => [
                    'title_template' => 'Motor {make} {model} {year} – Ficha Técnica Completa',
                    'meta_description_template' => 'Especificações do motor do {make} {model} {year}: cilindrada, potência, torque, combustível, número de cilindros e tecnologias.',
                    'h1_template' => 'Motor {make} {model} {year}',
                ],
                'templates' => [
                    'motor-especificacoes' => [
                        'name' => 'Ficha do Motor',
                        'fields' => [
                            'codigo_motor',
                            'cilindrada_cc',
                            'cilindros',
                            'disposicao', // linha, V, boxer
                            'valvulas_cilindro',
                            'comando_valvulas', // SOHC, DOHC
                            'alimentacao', // aspirado, turbo
                            'injecao', // direta, indireta, multiponto
                            'potencia_cv_gasolina',
                            'potencia_cv_etanol',
                            'torque_nm_gasolina',
                            'torque_nm_etanol',
                            'rpm_potencia_maxima',
                            'rpm_torque_maximo',
                            'taxa_compressao',
                            'tecnologias', // VVT, VVL, etc
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'especificações técnicas detalhadas',
                    'sources' => 'ficha técnica oficial, fabricante',
                    'tone' => 'técnico e completo',
                    'include' => ['specs completas', 'curva de potência', 'tecnologias'],
                ],
                'metadata' => [
                    'related_categories' => ['oleo', 'consumo', 'problemas'],
                    'search_keywords' => ['motor', 'potência', 'torque', 'cilindrada', 'cv', 'hp'],
                    'schema_type' => 'Product',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // FICHA TÉCNICA COMPLETA
            // ═══════════════════════════════════════════════════════════════
            [
                'name' => 'Ficha Técnica',
                'slug' => 'ficha-tecnica',
                'description' => 'Ficha técnica completa: dimensões, peso, capacidades, desempenho e equipamentos do veículo.',
                'icon' => 'fa-file-lines',
                'color' => '#4F46E5', // Indigo
                'order' => 14,
                'is_active' => true,
                'priority' => 13,
                'weekly_target' => 50,
                'seo' => [
                    'title_template' => 'Ficha Técnica {make} {model} {year} – Especificações Completas',
                    'meta_description_template' => 'Ficha técnica completa do {make} {model} {year}: motor, dimensões, peso, consumo, desempenho, equipamentos e opcionais.',
                    'h1_template' => 'Ficha Técnica {make} {model} {year}',
                ],
                'templates' => [
                    'ficha-tecnica-completa' => [
                        'name' => 'Ficha Técnica Completa',
                        'fields' => [
                            'motor',
                            'transmissao',
                            'desempenho',
                            'consumo',
                            'dimensoes' => [
                                'comprimento_mm',
                                'largura_mm',
                                'altura_mm',
                                'entre_eixos_mm',
                                'porta_malas_litros',
                                'tanque_litros',
                            ],
                            'peso' => [
                                'peso_vazio_kg',
                                'capacidade_carga_kg',
                                'peso_bruto_kg',
                                'capacidade_reboque_kg',
                            ],
                            'suspensao',
                            'freios',
                            'direcao',
                            'equipamentos_serie',
                            'opcionais',
                            'preco_tabela',
                        ],
                    ],
                ],
                'ai_prompt_hints' => [
                    'focus' => 'dados completos e organizados',
                    'sources' => 'ficha técnica oficial',
                    'tone' => 'organizado em categorias',
                    'include' => ['todas as specs', 'equipamentos', 'comparativo versões'],
                ],
                'metadata' => [
                    'related_categories' => ['motor', 'consumo', 'dimensoes'],
                    'search_keywords' => ['ficha técnica', 'especificações', 'dados técnicos'],
                    'schema_type' => 'Product',
                ],
            ],
        ];
    }
}
<?php

namespace Src\ContentGeneration\IdealPressure\Application\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * TestArticleService - Geração de artigos mock para validação
 * 
 * Serviço para:
 * - Gerar artigos de teste em formato JSON
 * - Validar estrutura de artigos antes da implementação
 * - Testar templates diferentes
 * - Debug da arquitetura sem dados reais
 * 
 * ⚠️ IMPORTANTE: Apenas para desenvolvimento e testes
 * 
 * @author Claude Sonnet 4
 * @version 1.0
 */
class TestArticleService
{
    /**
     * Veículos de teste por categoria
     */
    private const TEST_VEHICLES = [
        'sedan' => [
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => '2023',
            'category' => 'sedan',
            'tire_size' => '215/55R16',
            'pressure_front' => 32,
            'pressure_rear' => 30,
        ],
        'suv' => [
            'make' => 'Toyota',
            'model' => 'RAV4',
            'year' => '2024',
            'category' => 'suv',
            'tire_size' => '225/65R17',
            'pressure_front' => 35,
            'pressure_rear' => 33,
        ],
        'hatch' => [
            'make' => 'Volkswagen',
            'model' => 'Golf',
            'year' => '2022',
            'category' => 'hatch',
            'tire_size' => '205/55R16',
            'pressure_front' => 30,
            'pressure_rear' => 28,
        ],
        'pickup' => [
            'make' => 'Ford',
            'model' => 'Ranger',
            'year' => '2023',
            'category' => 'pickup',
            'tire_size' => '255/70R16',
            'pressure_front' => 35,
            'pressure_rear' => 40,
        ],
        'motorcycle' => [
            'make' => 'Yamaha',
            'model' => 'MT-07',
            'year' => '2023',
            'category' => 'motorcycle',
            'tire_size' => 'Diant: 120/70-17 / Tras: 180/55-17',
            'pressure_front' => 33,
            'pressure_rear' => 42,
        ],
        'car_electric' => [
            'make' => 'Tesla',
            'model' => 'Model 3',
            'year' => '2024',
            'category' => 'car_electric',
            'tire_size' => '235/45R18',
            'pressure_front' => 42,
            'pressure_rear' => 42,
        ],
    ];

    /**
     * Templates por categoria
     */
    private const TEMPLATE_MAPPING = [
        'sedan' => 'tire_calibration_car',
        'suv' => 'tire_calibration_car',
        'hatch' => 'tire_calibration_car',
        'pickup' => 'tire_calibration_pickup',
        'motorcycle' => 'tire_calibration_motorcycle',
        'car_electric' => 'tire_calibration_electric',
    ];

    /**
     * Gerar artigo de teste para categoria específica
     */
    public function generateTestArticle(string $category): array
    {
        if (!isset(self::TEST_VEHICLES[$category])) {
            throw new \InvalidArgumentException("Categoria de teste '{$category}' não disponível. Disponíveis: " . implode(', ', array_keys(self::TEST_VEHICLES)));
        }

        $vehicle = self::TEST_VEHICLES[$category];

        try {
            Log::info('TestArticleService: Gerando artigo de teste', [
                'category' => $category,
                'vehicle' => "{$vehicle['make']} {$vehicle['model']} {$vehicle['year']}"
            ]);

            $article = [
                // ===== ESTRUTURA BASE =====
                'title' => $this->generateTitle($vehicle),
                'slug' => $this->generateSlug($vehicle),
                'template' => self::TEMPLATE_MAPPING[$category],
                'category_id' => 1,
                'category_name' => 'Calibragem de Pneus',
                'category_slug' => 'calibragem-pneus',

                // ===== SEO DATA =====
                'seo_data' => $this->generateSeoData($vehicle),

                // ===== TECHNICAL CONTENT =====
                'technical_content' => $this->generateTechnicalContent($vehicle),

                // ===== BENEFITS CONTENT =====
                'benefits_content' => $this->generateBenefitsContent($vehicle),

                // ===== MAINTENANCE TIPS =====
                'maintenance_tips' => $this->generateMaintenanceTips($vehicle),

                // ===== CRITICAL ALERTS =====
                'critical_alerts' => $this->generateCriticalAlerts($vehicle),

                // ===== TEST METADATA =====
                'test_metadata' => [
                    'generated_at' => now()->toISOString(),
                    'test_version' => '1.0',
                    'vehicle_category' => $category,
                    'template_used' => self::TEMPLATE_MAPPING[$category],
                    'is_test_data' => true,
                    'data_quality_score' => 95, // Mock score
                    'word_count' => 0, // Será calculado
                ]
            ];

            // Calcular contagem de palavras
            $article['test_metadata']['word_count'] = $this->countArticleWords($article);

            Log::info('TestArticleService: Artigo de teste gerado', [
                'category' => $category,
                'vehicle' => "{$vehicle['make']} {$vehicle['model']} {$vehicle['year']}",
                'template' => self::TEMPLATE_MAPPING[$category],
                'word_count' => $article['test_metadata']['word_count']
            ]);

            return $article;
        } catch (\Exception $e) {
            Log::error('TestArticleService: Erro na geração do artigo de teste', [
                'category' => $category,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Gerar todos os artigos de teste disponíveis
     */
    public function generateAllTestArticles(): array
    {
        $testArticles = [];

        foreach (array_keys(self::TEST_VEHICLES) as $category) {
            try {
                $testArticles[$category] = $this->generateTestArticle($category);
            } catch (\Exception $e) {
                Log::error("TestArticleService: Erro ao gerar teste para categoria {$category}", [
                    'error' => $e->getMessage()
                ]);
                $testArticles[$category] = ['error' => $e->getMessage()];
            }
        }

        return $testArticles;
    }

    /**
     * Validar estrutura de artigo de teste
     */
    public function validateTestArticle(array $article): array
    {
        $validation = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
            'structure_score' => 0,
        ];

        // Validações obrigatórias
        $requiredFields = [
            'title',
            'slug',
            'template',
            'seo_data',
            'technical_content',
            'benefits_content',
            'maintenance_tips',
            'critical_alerts'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($article[$field]) || empty($article[$field])) {
                $validation['errors'][] = "Campo obrigatório '{$field}' ausente";
                $validation['is_valid'] = false;
            } else {
                $validation['structure_score'] += 10;
            }
        }

        // Validações específicas de SEO
        $seoData = $article['seo_data'] ?? [];

        if (isset($seoData['page_title']) && strlen($seoData['page_title']) > 65) {
            $validation['warnings'][] = "Title muito longo: " . strlen($seoData['page_title']) . " caracteres";
        }

        if (isset($seoData['meta_description']) && strlen($seoData['meta_description']) > 165) {
            $validation['warnings'][] = "Meta description muito longa: " . strlen($seoData['meta_description']) . " caracteres";
        }

        // Validação de conteúdo técnico
        $techContent = $article['technical_content'] ?? [];
        if (!isset($techContent['especificacoes_pressao'])) {
            $validation['errors'][] = "Especificações de pressão não encontradas";
            $validation['is_valid'] = false;
        }

        // Score final
        $validation['structure_score'] = min(100, $validation['structure_score']);

        return $validation;
    }

    // ===== MÉTODOS PRIVADOS DE GERAÇÃO =====

    private function generateTitle(array $vehicle): string
    {
        return "Calibragem do Pneu da {$vehicle['make']} {$vehicle['model']} {$vehicle['year']} – Guia Completo";
    }

    private function generateSlug(array $vehicle): string
    {
        $make = Str::slug($vehicle['make']);
        $model = Str::slug($vehicle['model']);
        return "calibragem-pneu-{$make}-{$model}-{$vehicle['year']}";
    }

    private function generateSeoData(array $vehicle): array
    {
        $fullName = "{$vehicle['make']} {$vehicle['model']} {$vehicle['year']}";
        $primaryKeyword = strtolower("calibragem pneu {$vehicle['make']} {$vehicle['model']} {$vehicle['year']}");

        return [
            'page_title' => "Calibragem do Pneu da {$fullName} – Guia Completo",
            'meta_description' => "Guia completo de calibragem dos pneus da {$fullName}. Dianteiros: {$vehicle['pressure_front']} PSI / Traseiros: {$vehicle['pressure_rear']} PSI. Procedimento detalhado.",
            'primary_keyword' => $primaryKeyword,
            'secondary_keywords' => [
                "pressão pneu {$vehicle['make']} {$vehicle['model']}",
                "calibrar pneu {$vehicle['make']}",
                "psi pneu {$vehicle['model']} {$vehicle['year']}",
                "manutenção pneu {$vehicle['category']}"
            ],
            'focus_keyphrase' => $primaryKeyword,
            'canonical_url' => null,
            'og_title' => "Calibragem do Pneu da {$fullName}",
            'og_description' => "Guia prático com as pressões corretas e procedimento completo de calibragem dos pneus da {$fullName}.",
            'og_image' => null,
        ];
    }

    private function generateTechnicalContent(array $vehicle): array
    {
        return [
            'especificacoes_pressao' => [
                'pressao_dianteira' => [
                    'valor_psi' => $vehicle['pressure_front'],
                    'valor_bar' => round($vehicle['pressure_front'] * 0.0689476, 2),
                    'valor_kpa' => round($vehicle['pressure_front'] * 6.89476, 0),
                    'condicao' => 'Pneu frio (veículo parado por mais de 3 horas)'
                ],
                'pressao_traseira' => [
                    'valor_psi' => $vehicle['pressure_rear'],
                    'valor_bar' => round($vehicle['pressure_rear'] * 0.0689476, 2),
                    'valor_kpa' => round($vehicle['pressure_rear'] * 6.89476, 0),
                    'condicao' => 'Pneu frio (veículo parado por mais de 3 horas)'
                ],
                'pressao_estepe' => [
                    'valor_psi' => $vehicle['pressure_rear'] + 10,
                    'valor_bar' => round(($vehicle['pressure_rear'] + 10) * 0.0689476, 2),
                    'observacao' => 'Pressão mais alta para compensar perda gradual de ar'
                ]
            ],
            'especificacoes_pneu' => [
                'tamanho_recomendado' => $vehicle['tire_size'],
                'categoria_veiculo' => ucfirst($vehicle['category']),
                'tipo_construcao' => 'Radial',
                'indice_velocidade_recomendado' => $this->getSpeedIndex($vehicle['category']),
                'indice_carga_recomendado' => $this->getLoadIndex($vehicle['category'])
            ],
            'instrumentos_necessarios' => [
                'calibrador_analogico' => 'Recomendado para precisão',
                'calibrador_digital' => 'Alternativa moderna',
                'compressor_ar' => 'Para adicionar pressão',
                'tampa_valvula_reserva' => 'Para substituição se necessário'
            ]
        ];
    }

    private function generateBenefitsContent(array $vehicle): array
    {
        return [
            'economico' => [
                'economia_combustivel' => [
                    'percentual_economia' => $this->getFuelSavingPercentage($vehicle['category']),
                    'explicacao' => 'Pressão correta reduz a resistência ao rolamento, diminuindo o consumo.',
                    'exemplo_pratico' => "Em um {$vehicle['make']} {$vehicle['model']}, a diferença pode ser significativa no orçamento mensal."
                ],
                'vida_util_pneu' => [
                    'aumento_durabilidade' => 'Até 30%',
                    'explicacao' => 'Evita desgaste irregular e prematuro dos pneus.',
                    'economia_estimada' => 'Economia de centenas de reais por ano'
                ]
            ],
            'seguranca' => [
                'aderencia_melhorada' => [
                    'condicao_seca' => 'Maior área de contato com o asfalto',
                    'condicao_molhada' => 'Melhor drenagem da água entre os sulcos',
                    'frenagem' => 'Distância de frenagem reduzida'
                ],
                'estabilidade_direcional' => [
                    'curvas' => 'Maior estabilidade em mudanças de direção',
                    'alta_velocidade' => 'Comportamento mais previsível',
                    'carga' => 'Distribuição adequada do peso do veículo'
                ]
            ],
            'desempenho' => [
                'conforto_rodagem' => [
                    'absorcao_impactos' => 'Reduz trepidações e vibrações',
                    'ruido' => 'Diminui ruídos de rodagem',
                    'dirigibilidade' => 'Resposta mais precisa da direção'
                ]
            ]
        ];
    }

    private function generateMaintenanceTips(array $vehicle): array
    {
        return [
            'verificacao_periodica' => [
                'frequencia_recomendada' => $this->getCheckFrequency($vehicle['category']),
                'melhor_horario' => 'Manhã cedo, com pneus frios',
                'pontos_atencao' => [
                    'Verificar todas as rodas, incluindo estepe',
                    'Observar desgaste irregular dos pneus',
                    'Conferir integridade das válvulas'
                ]
            ],
            'procedimento_calibragem' => [
                'passo_1' => 'Aguardar pelo menos 3 horas com veículo parado',
                'passo_2' => 'Remover tampa da válvula',
                'passo_3' => 'Conectar calibrador e verificar pressão atual',
                'passo_4' => 'Ajustar para pressão recomendada',
                'passo_5' => 'Recolocar tampa da válvula'
            ],
            'sinais_alerta' => [
                'pressao_baixa' => [
                    'sinais' => 'Desgaste nas bordas externas, consumo elevado',
                    'acao' => 'Calibrar imediatamente'
                ],
                'pressao_alta' => [
                    'sinais' => 'Desgaste no centro do pneu, menor aderência',
                    'acao' => 'Reduzir pressão gradualmente'
                ]
            ],
            'cuidados_especiais' => $this->getSpecialCare($vehicle['category'])
        ];
    }

    private function generateCriticalAlerts(array $vehicle): array
    {
        return [
            'seguranca_critica' => [
                'nunca_calibrar_quente' => [
                    'alerta' => 'NUNCA calibre pneus após rodar',
                    'explicacao' => 'Calor aumenta pressão temporariamente',
                    'tempo_espera' => 'Aguardar pelo menos 3 horas'
                ],
                'pressao_correta_vital' => [
                    'alerta' => 'Pressão incorreta compromete segurança',
                    'riscos' => [
                        'Aquaplanagem em piso molhado',
                        'Perda de controle em curvas',
                        'Aumento da distância de frenagem'
                    ]
                ]
            ],
            'manutencao_preventiva' => [
                'inspecao_visual' => [
                    'frequencia' => 'Semanalmente',
                    'verificar' => [
                        'Objetos perfurantes (pregos, parafusos)',
                        'Cortes ou bolhas na borracha',
                        'Desgaste irregular dos sulcos'
                    ]
                ],
                'quando_substituir' => [
                    'indicador_desgaste' => 'Sulcos com menos de 1,6mm',
                    'idade_pneu' => 'Mais de 5 anos (mesmo sem uso)',
                    'danos_estruturais' => 'Qualquer avaria na carcaça'
                ]
            ]
        ];
    }

    // ===== MÉTODOS AUXILIARES =====

    private function getSpeedIndex(string $category): string
    {
        $indexes = [
            'sedan' => 'H (210 km/h)',
            'suv' => 'V (240 km/h)',
            'hatch' => 'H (210 km/h)',
            'pickup' => 'S (180 km/h)',
            'motorcycle' => 'W (270 km/h)',
            'car_electric' => 'V (240 km/h)',
        ];

        return $indexes[$category] ?? 'T (190 km/h)';
    }

    private function getLoadIndex(string $category): string
    {
        $indexes = [
            'sedan' => '91-95',
            'suv' => '100-110',
            'hatch' => '85-91',
            'pickup' => '110-121',
            'motorcycle' => '73-75',
            'car_electric' => '95-100',
        ];

        return $indexes[$category] ?? '91-95';
    }

    private function getFuelSavingPercentage(string $category): string
    {
        $savings = [
            'sedan' => '3-5%',
            'suv' => '4-7%',
            'hatch' => '3-5%',
            'pickup' => '5-8%',
            'motorcycle' => '2-4%',
            'car_electric' => '5-10%', // Mais impacto na autonomia
        ];

        return $savings[$category] ?? '3-6%';
    }

    private function getCheckFrequency(string $category): string
    {
        $frequencies = [
            'sedan' => 'A cada 15 dias',
            'suv' => 'Semanalmente',
            'hatch' => 'A cada 15 dias',
            'pickup' => 'Semanalmente (uso intenso)',
            'motorcycle' => 'Semanalmente',
            'car_electric' => 'A cada 10 dias',
        ];

        return $frequencies[$category] ?? 'Quinzenalmente';
    }

    private function getSpecialCare(string $category): array
    {
        $specialCare = [
            'motorcycle' => [
                'equilibrio_pressoes' => 'Diferença front/rear mais crítica que carros',
                'pneu_morno' => 'Verificar após curtas distâncias é aceitável',
                'carga_passageiro' => 'Ajustar pressão conforme peso do passageiro'
            ],
            'car_electric' => [
                'torque_instantaneo' => 'Maior desgaste por torque imediato',
                'peso_baterias' => 'Pressões ligeiramente maiores podem ser necessárias',
                'economia_energia' => 'Impacto direto na autonomia da bateria'
            ],
            'pickup' => [
                'variacao_carga' => 'Ajustar pressão conforme carga transportada',
                'uso_off_road' => 'Verificar após trilhas ou terrenos irregulares',
                'tração_4x4' => 'Manter pressões uniformes para não forçar diferencial'
            ]
        ];

        return $specialCare[$category] ?? [
            'verificacao_regular' => 'Seguir frequência padrão de verificação',
            'atencao_desgaste' => 'Observar padrões de desgaste específicos do modelo'
        ];
    }

    private function countArticleWords(array $article): int
    {
        $text = '';

        // Concatenar todos os textos do artigo
        array_walk_recursive($article, function ($value) use (&$text) {
            if (is_string($value)) {
                $text .= ' ' . $value;
            }
        });

        return str_word_count(strip_tags($text));
    }

    /**
     * Obter estatísticas do serviço de teste
     */
    public function getTestStats(): array
    {
        return [
            'available_categories' => array_keys(self::TEST_VEHICLES),
            'total_test_vehicles' => count(self::TEST_VEHICLES),
            'template_types' => array_unique(array_values(self::TEMPLATE_MAPPING)),
            'service_version' => '1.0',
            'last_updated' => '2025-08-24'
        ];
    }
}

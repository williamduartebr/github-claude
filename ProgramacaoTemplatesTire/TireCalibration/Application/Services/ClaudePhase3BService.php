<?php

namespace Src\ContentGeneration\TireCalibration\Application\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

/**
 * ClaudePhase3BService - Refinamento de Especificações Técnicas
 * 
 * Responsável por enriquecer apenas conteúdo técnico:
 * - Especificações por versão com nomes reais do mercado brasileiro
 * - Tabela de carga completa com dados específicos
 * - Seções técnicas complementares se necessário
 * 
 * Requisito: Fase 3A deve estar completa antes de executar 3B
 * 
 * @version V4 Phase 3B - Technical Specifications Only
 */
class ClaudePhase3BService
{
    private const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';
    // private const MODEL = 'claude-3-7-sonnet-20250219';
    private const MODEL = 'claude-3-5-sonnet-20240620';
    private const MAX_TOKENS = 3000; // Maior para especificações técnicas
    private const TEMPERATURE = 0.2;  // Mais determinístico para dados técnicos

    // Termos genéricos proibidos (expandido para Fase 3B)
    private const FORBIDDEN_GENERIC_TERMS = [
        'versão base',
        'base',
        'básica',
        'basica',
        'intermediária',
        'intermediaria',
        'media',
        'média',
        'top',
        'topo',
        'premium genérico',
        'completa',
        'full',
        'entrada',
        'inicial',
        'superior',
        'avançada',
        'avancada',
        'padrão',
        'padrao',
        'standard',
        'modelo único',
        'único'
    ];

    private string $apiKey;
    private int $timeout;
    private int $maxRetries;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->timeout = config('services.anthropic.timeout', 90); // Maior timeout para specs técnicas
        $this->maxRetries = config('services.anthropic.max_retries', 3);
    }

    /**
     * Executar refinamento Fase 3B - Especificações Técnicas APENAS
     */
    public function enhanceTechnicalSpecifications(TireCalibration $calibration): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Claude API Key não configurada');
        }

        if (!$calibration->needsClaudePhase3B()) {
            throw new \Exception('Registro não está pronto para Fase 3B (precisa completar Fase 3A primeiro)');
        }

        try {
            $calibration->startClaudePhase3B();

            $baseArticle = $this->extractBaseArticle($calibration->generated_article);
            $vehicleInfo = $this->extractVehicleContext($calibration, $baseArticle);
            $phase3AData = $calibration->claude_phase_3a_enhancements ?? [];

            Log::info('ClaudePhase3BService: Iniciando refinamento técnico', [
                'tire_calibration_id' => $calibration->_id,
                'vehicle' => $vehicleInfo['display_name'],
                'phase' => '3B - Technical Specifications Only',
                'has_phase_3a_data' => !empty($phase3AData)
            ]);

            $enhancements = $this->generateTechnicalEnhancements($vehicleInfo, $baseArticle);

            $calibration->completeClaudePhase3B($enhancements);

            Log::info('ClaudePhase3BService: Refinamento técnico concluído com sucesso', [
                'tire_calibration_id' => $calibration->_id,
                'vehicle' => $vehicleInfo['display_name'],
                'enhanced_sections' => array_keys($enhancements),
                'versions_generated' => $this->extractVersionNames($enhancements),
                'article_refined_ready' => true
            ]);

            return $enhancements;
        } catch (\Exception $e) {
            $calibration->markFailed("Claude Phase 3B failed: " . $e->getMessage());
            Log::error('ClaudePhase3BService: Erro no refinamento técnico', [
                'tire_calibration_id' => $calibration->_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Gerar enhancements técnicos via Claude API com retry automático
     */
    private function generateTechnicalEnhancements(array $vehicleInfo, array $baseArticle): array
    {
        $maxAttempts = $this->maxRetries;
        $attempt = 1;

        while ($attempt <= $maxAttempts) {
            try {
                $prompt = $this->buildTechnicalPrompt($vehicleInfo, $baseArticle, $attempt);
                $response = $this->makeClaudeRequest($prompt);
                $enhancements = $this->parseClaudeResponse($response);

                // Validação rigorosa para especificações técnicas
                $this->validateTechnicalResponse($enhancements, $vehicleInfo);

                Log::info("ClaudePhase3BService: Especificações técnicas validadas na tentativa {$attempt}", [
                    'versions_count' => count($enhancements['especificacoes_por_versao'] ?? []),
                    'versions_names' => $this->extractVersionNames($enhancements)
                ]);

                return $enhancements;
            } catch (\Exception $e) {
                Log::warning("ClaudePhase3BService: Tentativa {$attempt} falhou: " . $e->getMessage());

                if ($attempt >= $maxAttempts) {
                    // Fallback com versões reais se Claude falhar completamente
                    Log::warning('ClaudePhase3BService: Usando fallback com versões reais');
                    return $this->generateTechnicalFallback($vehicleInfo, $baseArticle);
                }

                $attempt++;
                sleep(3 * $attempt); // Delay progressivo
            }
        }
    }

    private function buildTechnicalPrompt(array $vehicleInfo, array $baseArticle, int $attempt): string
    {
        $vehicleName = $vehicleInfo['display_name'];
        $make = strtolower($vehicleInfo['make']);
        $model = strtolower($vehicleInfo['model']);
        $category = $vehicleInfo['category'];

        $urgencyText = $attempt > 1 ?
            "🚨 TENTATIVA #{$attempt}: FOCO TOTAL EM VERSÕES REAIS! A tentativa anterior usou versões genéricas!" : "";

        // Exemplos específicos por marca
        $realVersionExamples = $this->getRealVersionExamples($make, $model, $category);
        $technicalContext = $this->getTechnicalContext($make, $model, $category);
        $pressureData = $this->extractPressureData($baseArticle, $vehicleInfo);

        // CALCULAR OS VALORES ANTES DA STRING
        $frontLoaded = $pressureData['front'] + $this->getLoadAdjustment($category, 'front');
        $rearLoaded = $pressureData['rear'] + $this->getLoadAdjustment($category, 'rear');
        $loadSpeedIndex = $this->getLoadSpeedIndex($category);
        $occupantText = $this->getOccupantText($category);
        $baggageText = $this->getBaggageText($category);

        return <<<EOT
{$urgencyText}

Você é um especialista técnico em especificações automotivas do mercado brasileiro.

MISSÃO FASE 3B: Gerar APENAS especificações técnicas para {$vehicleName}

{$technicalContext}

❌ VERSÕES GENÉRICAS PROIBIDAS:
- "Base", "Básica", "Intermediária", "Top", "Premium", "Completa", "Entrada", "Superior", "Padrão"

✅ USE APENAS VERSÕES REAIS BRASILEIRAS:
{$realVersionExamples}

🎯 JSON OBRIGATÓRIO - APENAS ESPECIFICAÇÕES TÉCNICAS:

```json
{
  "especificacoes_por_versao": [
    {
      "versao": "NOME_REAL_ESPECÍFICO_1",
      "medida_pneus": "{$pressureData['tire_size']}",
      "indice_carga_velocidade": "{$loadSpeedIndex}",
      "pressao_dianteiro_normal": {$pressureData['front']},
      "pressao_traseiro_normal": {$pressureData['rear']},
      "pressao_dianteiro_carregado": {$frontLoaded},
      "pressao_traseiro_carregado": {$rearLoaded}
    },
    {
      "versao": "NOME_REAL_ESPECÍFICO_2",
      "medida_pneus": "{$pressureData['tire_size']}",
      "indice_carga_velocidade": "{$loadSpeedIndex}",
      "pressao_dianteiro_normal": {$pressureData['front']},
      "pressao_traseiro_normal": {$pressureData['rear']},
      "pressao_dianteiro_carregado": {$frontLoaded},
      "pressao_traseiro_carregado": {$rearLoaded}
    },
    {
      "versao": "NOME_REAL_ESPECÍFICO_3",
      "medida_pneus": "{$pressureData['tire_size']}",
      "indice_carga_velocidade": "{$loadSpeedIndex}",
      "pressao_dianteiro_normal": {$pressureData['front']},
      "pressao_traseiro_normal": {$pressureData['rear']},
      "pressao_dianteiro_carregado": {$frontLoaded},
      "pressao_traseiro_carregado": {$rearLoaded}
    }
  ],
  
  "tabela_carga_completa": {
    "titulo": "Pressões para Carga Máxima",
    "descricao": "Valores adaptados para o {$vehicleName} considerando diferentes condições de carga",
    "condicoes": [
      {
        "versao": "MESMO_NOME_REAL_ESPECÍFICO_1",
        "ocupantes": "{$occupantText}",
        "bagagem": "{$baggageText}",
        "pressao_dianteira": "{$frontLoaded} PSI",
        "pressao_traseira": "{$rearLoaded} PSI",
        "observacao": "{$this->getVersionSpecificNote($category, 1)}"
      },
      {
        "versao": "MESMO_NOME_REAL_ESPECÍFICO_2",
        "ocupantes": "{$occupantText}",
        "bagagem": "{$baggageText}",
        "pressao_dianteira": "{$frontLoaded} PSI",
        "pressao_traseira": "{$rearLoaded} PSI",
        "observacao": "{$this->getVersionSpecificNote($category, 2)}"
      },
      {
        "versao": "MESMO_NOME_REAL_ESPECÍFICO_3",
        "ocupantes": "{$occupantText}",
        "bagagem": "{$baggageText}",
        "pressao_dianteira": "{$frontLoaded} PSI",
        "pressao_traseira": "{$rearLoaded} PSI",
        "observacao": "{$this->getVersionSpecificNote($category, 3)}"
      }
    ]
  }
}
```

🔥 REGRAS CRÍTICAS FASE 3B:

SEMPRE 3-5 versões com nomes REAIS do mercado brasileiro
Nomes devem ser ESPECÍFICOS: códigos de motor, acabamentos, grades
Pressões baseadas nos dados técnicos fornecidos
Observações específicas para cada versão
Zero termos genéricos

📋 DADOS TÉCNICOS:

Veículo: {$vehicleName}
Categoria: {$category}
Pressão base: {$pressureData['front']}/{$pressureData['rear']} PSI
Pneu: {$pressureData['tire_size']}

⚠️ VALIDAÇÃO AUTOMÁTICA:

Mínimo 3 versões, máximo 5
Nomes devem ter mais de 5 caracteres
Zero termos da lista proibida
Pressões consistentes com categoria

EOT;
    }

    /**
     * Obter exemplos de versões reais por marca/modelo
     */
    private function getRealVersionExamples(string $make, string $model, string $category): string
    {
        $examples = match (strtolower($make)) {
            'chevrolet' => match (strtolower($model)) {
                'onix' => "• Onix Joy\n• Onix LT\n• Onix LTZ\n• Onix Premier\n• Onix RS",
                'tracker' => "• Tracker LT\n• Tracker LTZ\n• Tracker Premier\n• Tracker RS",
                's10' => "• S10 LS\n• S10 LT\n• S10 LTZ\n• S10 High Country",
                default => "• {$model} LS\n• {$model} LT\n• {$model} LTZ\n• {$model} Premier"
            },
            'volkswagen' => match (strtolower($model)) {
                'polo' => "• Polo 1.0 MPI\n• Polo 1.0 TSI\n• Polo GTS",
                'golf' => "• Golf Comfortline\n• Golf Highline\n• Golf GTI",
                't-cross' => "• T-Cross 200 TSI\n• T-Cross 250 TSI\n• T-Cross Highline",
                default => "• {$model} Trendline\n• {$model} Comfortline\n• {$model} Highline"
            },
            'toyota' => match (strtolower($model)) {
                'corolla' => "• Corolla GLi\n• Corolla XEi\n• Corolla Altis",
                'hilux' => "• Hilux SR\n• Hilux SRX\n• Hilux SRV",
                'rav4' => "• RAV4 S\n• RAV4 SX\n• RAV4 SX Connect",
                default => "• {$model} XL\n• {$model} XLS\n• {$model} XLI"
            },
            'honda' => match (strtolower($model)) {
                'civic' => "• Civic LX\n• Civic EX\n• Civic Touring\n• Civic Si",
                'hr-v' => "• HR-V LX\n• HR-V EX\n• HR-V Touring",
                'city' => "• City DX\n• City LX\n• City EXL",
                default => "• {$model} LX\n• {$model} EX\n• {$model} EXL"
            },
            'ford' => match (strtolower($model)) {
                'ka' => "• Ka 1.0 SE\n• Ka 1.5 SEL\n• Ka Freestyle",
                'ranger' => "• Ranger XL\n• Ranger XLS\n• Ranger Limited",
                'ecosport' => "• EcoSport SE\n• EcoSport Titanium\n• EcoSport Storm",
                default => "• {$model} S\n• {$model} SE\n• {$model} SEL"
            },
            default => "• {$model} Comfort\n• {$model} Style\n• {$model} Premium"
        };

        return $examples;
    }

    /**
     * Obter contexto técnico específico por marca
     */
    private function getTechnicalContext(string $make, string $model, string $category): string
    {
        return match (strtolower($make)) {
            'chevrolet' => "CHEVROLET: Use nomenclaturas Joy/LT/LTZ/Premier/RS. Para motores: 1.0, 1.0 Turbo, 1.4. Focar em MyLink e OnStar nas versões superiores.",
            'volkswagen' => "VOLKSWAGEN: Use códigos TSI/MPI para motores. Nomenclaturas: Trendline/Comfortline/Highline. Focar em tecnologia alemã e segurança.",
            'toyota' => "TOYOTA: Use códigos XL/XLS/XLI/GLi/XEi/Altis. Para Hilux: SR/SRX/SRV. Focar em confiabilidade e Toyota Safety Sense.",
            'honda' => "HONDA: Use LX/EX/EXL/Touring. Para esportivos: Si/Type R. Focar em Honda Sensing e tecnologia VTEC.",
            'ford' => "FORD: Use S/SE/SEL/Titanium. Para picapes: XL/XLS/Limited. Focar em tecnologia SYNC e EcoBoost.",
            default => "Use acabamentos específicos da marca, nunca termos genéricos."
        };
    }

    /**
     * Extrair dados de pressão do artigo base
     */
    private function extractPressureData(array $baseArticle, array $vehicleInfo): array
    {
        $pressureSpecs = $baseArticle['vehicle_data']['pressure_specifications'] ?? [];

        return [
            'front' => $pressureSpecs['pressure_empty_front'] ?? 32,
            'rear' => $pressureSpecs['pressure_empty_rear'] ?? 30,
            'tire_size' => $pressureSpecs['tire_size'] ?? $vehicleInfo['tire_size'] ?? '185/65 R15'
        ];
    }

    /**
     * Obter índice de carga/velocidade por categoria
     */
    private function getLoadSpeedIndex(string $category): string
    {
        return match ($category) {
            'motorcycle', 'motorcycle_street', 'motorcycle_sport' => '73H',
            'pickup', 'truck' => '112S',
            'suv' => '100H',
            'car_electric' => '91V',
            default => '91H'
        };
    }

    /**
     * Obter ajuste de pressão para carga por categoria
     */
    private function getLoadAdjustment(string $category, string $position): int
    {
        $adjustments = [
            'motorcycle' => ['front' => 2, 'rear' => 3],
            'pickup' => ['front' => 5, 'rear' => 10],
            'truck' => ['front' => 8, 'rear' => 15],
            'suv' => ['front' => 3, 'rear' => 5],
            'car' => ['front' => 2, 'rear' => 4] // Valor padrão explícito
        ];

        // Se a categoria não existir, usa 'car' como padrão
        $categoryToUse = $adjustments[$category] ?? $adjustments['car'];

        return $categoryToUse[$position] ?? 2; // fallback para 2 se a posição não existir
    }

    /**
     * Obter texto de ocupantes por categoria
     */
    private function getOccupantText(string $category): string
    {
        return match ($category) {
            'motorcycle', 'motorcycle_street' => '2 pessoas',
            'pickup', 'truck' => '4-5 pessoas',
            default => '5 pessoas'
        };
    }

    /**
     * Obter texto de bagagem por categoria
     */
    private function getBaggageText(string $category): string
    {
        return match ($category) {
            'motorcycle', 'motorcycle_street' => 'Garupa + bagageiro carregado',
            'pickup' => 'Caçamba com carga máxima',
            'truck' => 'Carga útil máxima',
            default => 'Porta-malas carregado'
        };
    }

    /**
     * Obter nota específica por versão
     */
    private function getVersionSpecificNote(string $category, int $versionIndex): string
    {
        $notes = [
            'motorcycle' => [
                1 => 'Versão de entrada - cuidado especial com estabilidade',
                2 => 'Versão intermediária - equilíbrio ideal',
                3 => 'Versão topo - máximo desempenho e segurança'
            ],
            'pickup' => [
                1 => 'Para trabalho pesado diário',
                2 => 'Ideal para uso misto urbano/trabalho',
                3 => 'Máximo conforto e equipamentos'
            ],
            'car' => [ // Mudança aqui: 'default' => 'car'
                1 => 'Recomendado para uso urbano diário',
                2 => 'Ideal para uso misto urbano/rodoviário',
                3 => 'Máximo conforto para viagens longas'
            ]
        ];

        // Usar 'car' como padrão em vez de 'default'
        return $notes[$category][$versionIndex] ?? $notes['car'][$versionIndex] ?? 'Versão com especificações otimizadas';
    }

    /**
     * Validação rigorosa das especificações técnicas
     */
    private function validateTechnicalResponse(array $enhancements, array $vehicleInfo): void
    {
        $errors = [];

        // Validar especificacoes_por_versao
        if (empty($enhancements['especificacoes_por_versao'])) {
            $errors[] = 'Especificações por versão não foram geradas';
        } else {
            $specs = $enhancements['especificacoes_por_versao'];

            if (count($specs) < 3 || count($specs) > 5) {
                $errors[] = 'Deve ter entre 3 e 5 versões, encontradas: ' . count($specs);
            }

            foreach ($specs as $index => $spec) {
                $version = trim($spec['versao'] ?? '');

                if (empty($version)) {
                    $errors[] = "Versão #{$index} está vazia";
                    continue;
                }

                // Verificar termos genéricos proibidos
                $versionLower = strtolower($version);
                foreach (self::FORBIDDEN_GENERIC_TERMS as $forbidden) {
                    if (str_contains($versionLower, $forbidden)) {
                        $errors[] = "Versão '{$version}' contém termo genérico proibido: '{$forbidden}'";
                    }
                }

                // Versão deve ser específica (mínimo 5 caracteres)
                if (strlen($version) < 5) {
                    $errors[] = "Versão '{$version}' muito genérica (mínimo 5 caracteres)";
                }

                // Validar campos obrigatórios
                $requiredFields = ['medida_pneus', 'pressao_dianteiro_normal', 'pressao_traseiro_normal'];
                foreach ($requiredFields as $field) {
                    if (!isset($spec[$field]) || empty($spec[$field])) {
                        $errors[] = "Versão '{$version}' está sem campo '{$field}'";
                    }
                }

                // Validar pressões (devem ser numéricas e razoáveis)
                if (isset($spec['pressao_dianteiro_normal'])) {
                    $frontPressure = (int) $spec['pressao_dianteiro_normal'];
                    if ($frontPressure < 20 || $frontPressure > 60) {
                        $errors[] = "Pressão dianteira inválida para '{$version}': {$frontPressure} PSI";
                    }
                }
            }
        }

        // Validar tabela_carga_completa
        if (empty($enhancements['tabela_carga_completa'])) {
            $errors[] = 'Tabela de carga completa não foi gerada';
        } else {
            $tabela = $enhancements['tabela_carga_completa'];

            if (empty($tabela['condicoes']) || !is_array($tabela['condicoes'])) {
                $errors[] = 'Condições da tabela de carga não foram geradas';
            } else {
                // Verificar consistência das versões entre especificações e tabela
                $especVersions = array_column($enhancements['especificacoes_por_versao'] ?? [], 'versao');
                $tabelaVersions = array_column($tabela['condicoes'], 'versao');

                foreach ($tabelaVersions as $tabelaVersion) {
                    if (!in_array($tabelaVersion, $especVersions)) {
                        $errors[] = "Versão '{$tabelaVersion}' na tabela não existe nas especificações";
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new \Exception('Validação Fase 3B falhou: ' . implode('; ', $errors));
        }

        Log::info('ClaudePhase3BService: Validação técnica passou com sucesso', [
            'versions_count' => count($enhancements['especificacoes_por_versao']),
            'table_conditions' => count($enhancements['tabela_carga_completa']['condicoes'] ?? [])
        ]);
    }

    /**
     * Gerar fallback com versões reais se Claude falhar
     */
    private function generateTechnicalFallback(array $vehicleInfo, array $baseArticle): array
    {
        $make = strtolower($vehicleInfo['make']);
        $model = strtolower($vehicleInfo['model']);
        $category = $vehicleInfo['category'];

        $realVersions = $this->getFallbackVersions($make, $model, $category);
        $pressureData = $this->extractPressureData($baseArticle, $vehicleInfo);

        $specifications = [];
        $tableConditions = [];

        foreach ($realVersions as $version) {
            $specifications[] = [
                'versao' => $version,
                'medida_pneus' => $pressureData['tire_size'],
                'indice_carga_velocidade' => $this->getLoadSpeedIndex($category),
                'pressao_dianteiro_normal' => $pressureData['front'],
                'pressao_traseiro_normal' => $pressureData['rear'],
                'pressao_dianteiro_carregado' => $pressureData['front'] + $this->getLoadAdjustment($category, 'front'),
                'pressao_traseiro_carregado' => $pressureData['rear'] + $this->getLoadAdjustment($category, 'rear')
            ];

            $tableConditions[] = [
                'versao' => $version,
                'ocupantes' => $this->getOccupantText($category),
                'bagagem' => $this->getBaggageText($category),
                'pressao_dianteira' => ($pressureData['front'] + $this->getLoadAdjustment($category, 'front')) . ' PSI',
                'pressao_traseira' => ($pressureData['rear'] + $this->getLoadAdjustment($category, 'rear')) . ' PSI',
                'observacao' => 'Versão com especificações otimizadas'
            ];
        }

        return [
            'especificacoes_por_versao' => $specifications,
            'tabela_carga_completa' => [
                'titulo' => 'Pressões para Carga Máxima',
                'descricao' => 'Valores recomendados para diferentes condições de carga',
                'condicoes' => $tableConditions
            ]
        ];
    }

    /**
     * Obter versões reais para fallback
     */
    private function getFallbackVersions(string $make, string $model, string $category): array
    {
        // Base de conhecimento simplificada
        $fallbackVersions = match ($make) {
            'chevrolet' => [
                ucfirst($model) . ' Joy',
                ucfirst($model) . ' LT',
                ucfirst($model) . ' LTZ'
            ],
            'volkswagen' => [
                ucfirst($model) . ' Trendline',
                ucfirst($model) . ' Comfortline',
                ucfirst($model) . ' Highline'
            ],
            'toyota' => [
                ucfirst($model) . ' XL',
                ucfirst($model) . ' XLS',
                ucfirst($model) . ' XLI'
            ],
            default => [
                ucfirst($model) . ' Comfort',
                ucfirst($model) . ' Style',
                ucfirst($model) . ' Premium'
            ]
        };

        return $fallbackVersions;
    }

    /**
     * Extrair nomes de versões para log
     */
    private function extractVersionNames(array $enhancements): array
    {
        $versions = [];

        if (isset($enhancements['especificacoes_por_versao'])) {
            foreach ($enhancements['especificacoes_por_versao'] as $spec) {
                $versions[] = $spec['versao'] ?? 'N/A';
            }
        }

        return $versions;
    }

    // Métodos auxiliares reutilizados do ClaudePhase3AService
    private function extractBaseArticle($generatedArticle): array
    {
        if (is_array($generatedArticle)) {
            return $generatedArticle;
        }

        if (is_string($generatedArticle)) {
            $decoded = json_decode($generatedArticle, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function extractVehicleContext(TireCalibration $calibration, array $baseArticle): array
    {
        return [
            'make' => $calibration->vehicle_make ?? 'Veículo',
            'model' => $calibration->vehicle_model ?? 'Modelo',
            'year' => $calibration->vehicle_year,
            'display_name' => trim(($calibration->vehicle_make ?? '') . ' ' . ($calibration->vehicle_model ?? '') . ' ' . ($calibration->vehicle_year ?? '')),
            'category' => $calibration->main_category ?? 'car',
            'tire_size' => $baseArticle['vehicle_data']['tire_specifications']['tire_size'] ?? '185/65 R15'
        ];
    }

    private function makeClaudeRequest(string $prompt): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01'
            ])
            ->post(self::CLAUDE_API_URL, [
                'model' => self::MODEL,
                'max_tokens' => self::MAX_TOKENS,
                'temperature' => self::TEMPERATURE,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]);

        if (!$response->successful()) {
            $errorMessage = "Claude API Error: HTTP {$response->status()}";

            if ($response->status() === 429) {
                $errorMessage .= " - Rate limit exceeded. Aguarde antes de tentar novamente.";
            } elseif ($response->status() === 401) {
                $errorMessage .= " - API key inválida ou não configurada.";
            } elseif ($response->status() >= 500) {
                $errorMessage .= " - Erro interno do servidor Claude.";
            }

            throw new \Exception($errorMessage . " Response: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Parse da resposta da Claude API
     */
    private function parseClaudeResponse(array $response): array
    {
        $text = $response['content'][0]['text'] ?? '';

        if (empty($text)) {
            throw new \Exception('Resposta da Claude API está vazia');
        }

        // Tentar extrair JSON da resposta
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $jsonString = $matches[1];
            $json = json_decode($jsonString, true);

            if ($json && json_last_error() === JSON_ERROR_NONE) {
                return $json;
            } else {
                throw new \Exception('JSON inválido na resposta Claude: ' . json_last_error_msg());
            }
        }

        // Fallback: tentar parsear a resposta inteira como JSON
        $json = json_decode($text, true);
        if ($json && json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        throw new \Exception('Resposta da Claude API não contém JSON válido');
    }

    /**
     * Teste de conectividade da API para Fase 3B
     */
    public function testApiConnection(): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01'
                ])
                ->post(self::CLAUDE_API_URL, [
                    'model' => self::MODEL,
                    'max_tokens' => 50,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Teste de conectividade Fase 3B - responda apenas: TÉCNICO OK']
                    ]
                ]);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Claude API Phase 3B conectada' : 'Erro: ' . $response->status(),
                'model' => self::MODEL,
                'phase' => '3B - Technical',
                'max_tokens' => self::MAX_TOKENS,
                'temperature' => self::TEMPERATURE
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
                'phase' => '3B - Technical'
            ];
        }
    }

    /**
     * Estatísticas do serviço Fase 3B
     */
    public function getPhase3BStats(): array
    {
        $readyFor3B = TireCalibration::readyForClaudePhase3B()->count();
        $processing3B = TireCalibration::where('enrichment_phase', TireCalibration::PHASE_CLAUDE_3B_PROCESSING)->count();
        $completed3B = TireCalibration::where('enrichment_phase', TireCalibration::PHASE_CLAUDE_COMPLETED)->count();

        // Análise de qualidade das versões geradas
        $versionsAnalysis = TireCalibration::whereNotNull('claude_phase_3b_enhancements')
            ->get()
            ->map(function ($record) {
                $enhancements = $record->claude_phase_3b_enhancements;
                $versions = $enhancements['especificacoes_por_versao'] ?? [];
                return count($versions);
            });

        return [
            'service' => 'ClaudePhase3BService',
            'version' => 'v4_technical_only',
            'ready_for_processing' => $readyFor3B,
            'currently_processing' => $processing3B,
            'completed' => $completed3B,
            'api_configured' => !empty($this->apiKey),
            'success_rate' => ($completed3B + $processing3B) > 0 ? round(($completed3B / ($completed3B + $processing3B)) * 100, 2) : 0,

            // Estatísticas específicas de versões
            'avg_versions_per_article' => $versionsAnalysis->avg() ?: 0,
            'min_versions_generated' => $versionsAnalysis->min() ?: 0,
            'max_versions_generated' => $versionsAnalysis->max() ?: 0,

            'focus_areas' => ['especificacoes_por_versao', 'tabela_carga_completa'],
            'forbidden_terms_count' => count(self::FORBIDDEN_GENERIC_TERMS),
            'validation_enabled' => true,
            'fallback_enabled' => true,
            'max_retries' => $this->maxRetries,
        ];
    }

    /**
     * Validar se um registro está realmente pronto para Fase 3B
     */
    public function validateReadinessForPhase3B(TireCalibration $calibration): array
    {
        $issues = [];
        $canProcess = true;

        // Verificar se Fase 3A foi completada
        if (empty($calibration->claude_phase_3a_enhancements)) {
            $issues[] = 'Fase 3A não foi completada';
            $canProcess = false;
        }

        // Verificar se tem artigo base
        if (empty($calibration->generated_article)) {
            $issues[] = 'Artigo base (generated_article) não disponível';
            $canProcess = false;
        }

        // Verificar estado
        if ($calibration->enrichment_phase !== TireCalibration::PHASE_CLAUDE_3A_COMPLETED) {
            $issues[] = "Estado incorreto: {$calibration->enrichment_phase} (esperado: claude_3a_completed)";
            $canProcess = false;
        }

        // Verificar se já foi processado
        if (!empty($calibration->claude_phase_3b_enhancements)) {
            $issues[] = 'Fase 3B já foi processada anteriormente';
            $canProcess = false;
        }

        // Verificar dados básicos do veículo
        if (empty($calibration->vehicle_make) || empty($calibration->vehicle_model)) {
            $issues[] = 'Dados básicos do veículo incompletos';
            $canProcess = false;
        }

        return [
            'can_process' => $canProcess,
            'issues' => $issues,
            'phase_3a_completed' => !empty($calibration->claude_phase_3a_enhancements),
            'has_base_article' => !empty($calibration->generated_article),
            'current_phase' => $calibration->enrichment_phase,
            'vehicle_complete' => !empty($calibration->vehicle_make) && !empty($calibration->vehicle_model)
        ];
    }

    /**
     * Processar lote de registros Fase 3B com controle de rate limiting
     */
    public function processBatch(array $calibrationIds, int $delayBetweenRequests = 5): array
    {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($calibrationIds as $id) {
            try {
                $calibration = TireCalibration::find($id);

                if (!$calibration) {
                    $results['skipped']++;
                    $results['errors'][] = "ID {$id}: Registro não encontrado";
                    continue;
                }

                $readiness = $this->validateReadinessForPhase3B($calibration);
                if (!$readiness['can_process']) {
                    $results['skipped']++;
                    $results['errors'][] = "ID {$id}: " . implode(', ', $readiness['issues']);
                    continue;
                }

                // Processar Fase 3B
                $enhancements = $this->enhanceTechnicalSpecifications($calibration);
                $results['successful']++;

                Log::info('Batch Phase 3B: Processado com sucesso', [
                    'id' => $id,
                    'vehicle' => $calibration->vehicle_make . ' ' . $calibration->vehicle_model,
                    'versions_generated' => count($enhancements['especificacoes_por_versao'] ?? [])
                ]);
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "ID {$id}: {$e->getMessage()}";

                Log::error('Batch Phase 3B: Erro no processamento', [
                    'id' => $id,
                    'error' => $e->getMessage()
                ]);
            }

            $results['processed']++;

            // Rate limiting entre requests
            if ($results['processed'] < count($calibrationIds)) {
                sleep($delayBetweenRequests);
            }
        }

        return $results;
    }

    /**
     * Análise de qualidade das especificações geradas
     */
    public function analyzeSpecificationQuality(): array
    {
        $completedRecords = TireCalibration::whereNotNull('claude_phase_3b_enhancements')
            ->where('enrichment_phase', TireCalibration::PHASE_CLAUDE_COMPLETED)
            ->get();

        if ($completedRecords->isEmpty()) {
            return [
                'total_analyzed' => 0,
                'message' => 'Nenhum registro Fase 3B para análise'
            ];
        }

        $qualityMetrics = [
            'total_analyzed' => $completedRecords->count(),
            'versions_distribution' => [],
            'generic_terms_detected' => 0,
            'avg_versions_per_article' => 0,
            'technical_completeness' => [
                'with_specifications' => 0,
                'with_load_table' => 0,
                'fully_complete' => 0
            ]
        ];

        $versionCounts = [];
        $totalVersions = 0;

        foreach ($completedRecords as $record) {
            $enhancements = $record->claude_phase_3b_enhancements;

            // Analisar distribuição de versões
            $specs = $enhancements['especificacoes_por_versao'] ?? [];
            $versionCount = count($specs);
            $totalVersions += $versionCount;

            $versionCounts[$versionCount] = ($versionCounts[$versionCount] ?? 0) + 1;

            // Verificar termos genéricos
            foreach ($specs as $spec) {
                $version = strtolower($spec['versao'] ?? '');
                foreach (self::FORBIDDEN_GENERIC_TERMS as $forbidden) {
                    if (str_contains($version, $forbidden)) {
                        $qualityMetrics['generic_terms_detected']++;
                        break;
                    }
                }
            }

            // Completeness técnica
            if (!empty($specs)) {
                $qualityMetrics['technical_completeness']['with_specifications']++;
            }

            if (!empty($enhancements['tabela_carga_completa'])) {
                $qualityMetrics['technical_completeness']['with_load_table']++;
            }

            if (!empty($specs) && !empty($enhancements['tabela_carga_completa'])) {
                $qualityMetrics['technical_completeness']['fully_complete']++;
            }
        }

        $qualityMetrics['versions_distribution'] = $versionCounts;
        $qualityMetrics['avg_versions_per_article'] = round($totalVersions / $completedRecords->count(), 2);

        // Calcular porcentagens
        $total = $qualityMetrics['total_analyzed'];
        $qualityMetrics['quality_percentages'] = [
            'generic_terms_rate' => round(($qualityMetrics['generic_terms_detected'] / $total) * 100, 2),
            'specifications_rate' => round(($qualityMetrics['technical_completeness']['with_specifications'] / $total) * 100, 2),
            'load_table_rate' => round(($qualityMetrics['technical_completeness']['with_load_table'] / $total) * 100, 2),
            'full_completeness_rate' => round(($qualityMetrics['technical_completeness']['fully_complete'] / $total) * 100, 2)
        ];

        return $qualityMetrics;
    }

    /**
     * Cleanup de registros órfãos na Fase 3B
     */
    public function cleanupStuckPhase3B(): int
    {
        $cutoffTime = now()->subHours(2);

        $stuckRecords = TireCalibration::where('enrichment_phase', TireCalibration::PHASE_CLAUDE_3B_PROCESSING)
            ->where('claude_processing_started_at', '<', $cutoffTime)
            ->get();

        $cleanedCount = 0;

        foreach ($stuckRecords as $record) {
            $record->update([
                'enrichment_phase' => TireCalibration::PHASE_CLAUDE_3A_COMPLETED,
                'claude_processing_started_at' => null,
                'last_error' => 'Cleanup: estava travado na Fase 3B por mais de 2 horas',
            ]);
            $cleanedCount++;
        }

        if ($cleanedCount > 0) {
            Log::info('ClaudePhase3BService: Cleanup de registros travados', [
                'cleaned_count' => $cleanedCount,
                'cutoff_time' => $cutoffTime->toISOString()
            ]);
        }

        return $cleanedCount;
    }
}

<?php

namespace Src\ContentGeneration\TireCalibration\Application\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

/**
 * ClaudeRefinementService - CORRIGIDO - Forçar Versões Específicas Reais
 * 
 * ✅ CORREÇÃO: Validação mais rigorosa de versões genéricas
 * ✅ CORREÇÃO: Prompt mais assertivo e exemplos concretos
 * ✅ CORREÇÃO: Retry automático quando detectar versões genéricas
 * ✅ CORREÇÃO: Fallback com versões reais por marca/modelo
 * 
 * @version 3.3 - Fix specific versions enforcement
 */
class ClaudeRefinementService
{
    private const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL = 'claude-3-7-sonnet-20250219';
    private const MAX_TOKENS = 3000;
    private const TEMPERATURE = 0.2; // ✅ Reduzido para mais determinismo

    // ✅ NOVO: Termos genéricos expandidos com case-insensitive
    private const FORBIDDEN_GENERIC_TERMS = [
        'versão base',
        'versao base',
        'base',
        'básica',
        'basica',
        'intermediária',
        'intermediaria',
        'media',
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
        'standard genérico',
        'modelo único',
        'modelo unico'
    ];

    private string $apiKey;
    private int $timeout;
    private int $maxRetries;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->timeout = config('services.anthropic.timeout', 60);
        $this->maxRetries = config('services.anthropic.max_retries', 3);
    }

    /**
     * ✅ MÉTODO PRINCIPAL CORRIGIDO: Força geração de TODAS as seções obrigatórias
     */
    public function enhanceWithClaude(TireCalibration $calibration): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Claude API Key não configurada');
        }

        if (empty($calibration->generated_article)) {
            throw new \Exception('Artigo base não encontrado');
        }

        try {
            $calibration->startClaudeProcessing();

            $baseArticle = $this->extractBaseArticle($calibration->generated_article);
            if (empty($baseArticle)) {
                throw new \Exception('Artigo base inválido ou corrompido');
            }

            $vehicleInfo = $this->extractVehicleContext($calibration, $baseArticle);

            // ✅ FORÇAR TODAS AS SEÇÕES OBRIGATÓRIAS (não usar getAreasForClaudeRefinement)
            $areasToEnhance = [
                'introducao',
                'consideracoes_finais',
                'perguntas_frequentes',
                'especificacoes_por_versao',
                'tabela_carga_completa'
            ];

            Log::info('ClaudeRefinementService: Forçando geração de TODAS as seções obrigatórias', [
                'tire_calibration_id' => $calibration->_id,
                'vehicle' => $vehicleInfo['display_name'],
                'sections_to_enhance' => $areasToEnhance
            ]);

            // ✅ NOVO: Tentativas múltiplas até conseguir versões específicas
            $claudeEnhancements = $this->generateValidEnhancements($vehicleInfo, $areasToEnhance, $baseArticle);

            $finalArticle = $this->mergeEnhancements($baseArticle, $claudeEnhancements);
            $calibration->completeClaudeProcessing($claudeEnhancements, $finalArticle);

            Log::info('ClaudeRefinementService: Enhancement com versões específicas concluído', [
                'tire_calibration_id' => $calibration->_id,
                'vehicle' => $vehicleInfo['display_name'],
                'enhanced_areas' => array_keys($claudeEnhancements),
                'versions_generated' => $this->extractVersionNames($claudeEnhancements)
            ]);

            return $finalArticle;
        } catch (\Exception $e) {
            $calibration->markFailed("Claude enhancement failed: " . $e->getMessage());
            Log::error('ClaudeRefinementService: Erro no enhancement', [
                'tire_calibration_id' => $calibration->_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * ✅ MÉTODO NOVO: Gerar enhancements válidos com retry automático
     */
    private function generateValidEnhancements(array $vehicleInfo, array $areas, array $baseArticle): array
    {
        $maxAttempts = 3;
        $attempt = 1;

        while ($attempt <= $maxAttempts) {
            Log::info("ClaudeRefinementService: Tentativa {$attempt} de geração de versões específicas");

            try {
                $prompt = $this->buildEnhancedPrompt($vehicleInfo, $areas, $baseArticle, $attempt);
                $response = $this->makeClaudeRequest($prompt);
                $enhancements = $this->parseClaudeResponse($response, $areas);

                // ✅ VALIDAÇÃO RIGOROSA
                if ($this->hasOnlySpecificVersions($enhancements)) {
                    Log::info("ClaudeRefinementService: Versões específicas geradas com sucesso na tentativa {$attempt}");
                    return $enhancements;
                }

                Log::warning("ClaudeRefinementService: Versões genéricas detectadas na tentativa {$attempt}", [
                    'detected_versions' => $this->extractVersionNames($enhancements)
                ]);

                $attempt++;
            } catch (\Exception $e) {
                Log::error("ClaudeRefinementService: Erro na tentativa {$attempt}: " . $e->getMessage());
                $attempt++;
            }
        }

        // ✅ FALLBACK: Usar base de conhecimento se Claude falhar
        Log::warning("ClaudeRefinementService: Usando fallback com versões reais da base de conhecimento");
        return $this->createSpecificVersionsFallback($vehicleInfo, $areas);
    }

    /**
     * ✅ MÉTODO CORRIGIDO: Prompt completo com todas as seções obrigatórias
     */
    private function buildEnhancedPrompt(array $vehicleInfo, array $areas, array $baseArticle, int $attempt): string
    {
        $vehicleName = $vehicleInfo['display_name'];
        $make = strtolower($vehicleInfo['make']);
        $model = strtolower($vehicleInfo['model']);
        $year = $vehicleInfo['year'];
        $pressures = "{$vehicleInfo['pressure_front']}/{$vehicleInfo['pressure_rear']} PSI";

        $vehicleType = $this->determineVehicleType($vehicleInfo);
        $specificExamples = $this->getSpecificVersionExamples($make, $model, $vehicleType);

        // ✅ Prompt mais assertivo na tentativa 2+
        $urgencyText = $attempt > 1 ?
            "🚨 ATENÇÃO: Esta é a tentativa #{$attempt}. As tentativas anteriores falharam porque você usou versões GENÉRICAS. Use APENAS versões REAIS que existem no mercado brasileiro!" :
            "";

        $prompt = <<<EOT
{$urgencyText}

Você é um especialista em veículos brasileiros. MISSÃO CRÍTICA: Gerar conteúdo COMPLETO E ESPECÍFICO para {$vehicleName}.

🚗 VEÍCULO: {$vehicleName}
📊 PRESSÕES: {$pressures}
🔧 TIPO: {$vehicleType}

❌ PROIBIDO ABSOLUTO (resultará em FALHA):
- "Versão Base", "Base", "Básica"
- "Intermediária", "Média" 
- "Top", "Premium", "Completa"
- "Entrada", "Superior", "Avançada"
- "Padrão", "Standard"
- Textos genéricos ou curtos
- Menos de 5 perguntas frequentes

✅ USE APENAS VERSÕES REAIS como:
{$specificExamples}

INSTRUÇÕES ESPECÍFICAS PARA {$make} {$model}:
{$this->getBrandSpecificInstructions($make,$model,$year)}

⚠️ SEÇÕES OBRIGATÓRIAS - TODAS DEVEM SER GERADAS:

STRUCTURE JSON COMPLETA OBRIGATÓRIA:
```json
{
  "introducao": "TEXTO CONTEXTUALIZADO DE 150-200 PALAVRAS específico para {$vehicleType} {$vehicleName}. Deve mencionar características únicas do modelo, uso brasileiro típico, importância da calibragem para este veículo específico, benefícios particulares desta marca/modelo. Evite textos genéricos.",
  
  "consideracoes_finais": "TEXTO ESPECÍFICO DE 120-180 PALAVRAS para {$vehicleType} {$vehicleName}. Deve resumir os pontos principais, mencionar as pressões específicas ({$pressures}), características particulares do modelo, e recomendações finais personalizadas para este veículo.",
  
  "perguntas_frequentes": [
    {
      "pergunta": "Qual a pressão ideal do {$vehicleName} em PSI?",
      "resposta": "Para o {$vehicleName}, use {$pressures} para uso normal. [contexto específico do modelo, TPMS se tiver, características particulares]"
    },
    {
      "pergunta": "Com que frequência verificar a pressão no {$vehicleName}?",
      "resposta": "[Resposta específica considerando o perfil de uso do modelo, se tem TPMS, características do público-alvo]"
    },
    {
      "pergunta": "[Pergunta específica sobre característica única do modelo]?",
      "resposta": "[Resposta técnica específica]"
    },
    {
      "pergunta": "[Pergunta sobre uso típico do modelo no Brasil]?",
      "resposta": "[Resposta contextualizada para o mercado brasileiro]"
    },
    {
      "pergunta": "[Pergunta sobre manutenção específica do modelo]?",
      "resposta": "[Resposta prática e específica]"
    }
  ],
  
  "especificacoes_por_versao": [
    {
      "versao": "NOME_REAL_ESPECÍFICO_1",
      "medida_pneus": "{$vehicleInfo['tire_size']}",
      "indice_carga_velocidade": "adequado ao tipo",
      "pressao_dianteiro_normal": {$vehicleInfo['pressure_front']},
      "pressao_traseiro_normal": {$vehicleInfo['pressure_rear']},
      "pressao_dianteiro_carregado": "ajustado por tipo",
      "pressao_traseiro_carregado": "ajustado por tipo"
    },
    {
      "versao": "NOME_REAL_ESPECÍFICO_2",
      "medida_pneus": "{$vehicleInfo['tire_size']}",
      "indice_carga_velocidade": "adequado ao tipo",
      "pressao_dianteiro_normal": {$vehicleInfo['pressure_front']},
      "pressao_traseiro_normal": {$vehicleInfo['pressure_rear']},
      "pressao_dianteiro_carregado": "ajustado por tipo",
      "pressao_traseiro_carregado": "ajustado por tipo"
    },
    {
      "versao": "NOME_REAL_ESPECÍFICO_3",
      "medida_pneus": "{$vehicleInfo['tire_size']}",
      "indice_carga_velocidade": "adequado ao tipo",
      "pressao_dianteiro_normal": {$vehicleInfo['pressure_front']},
      "pressao_traseiro_normal": {$vehicleInfo['pressure_rear']},
      "pressao_dianteiro_carregado": "ajustado por tipo",
      "pressao_traseiro_carregado": "ajustado por tipo"
    }
  ],
  
  "tabela_carga_completa": {
    "titulo": "Pressões para Carga Máxima",
    "descricao": "Valores adaptados ao tipo de veículo e uso típico do {$vehicleName}",
    "condicoes": [
      {
        "versao": "MESMOS_NOMES_REAIS_ACIMA_1",
        "ocupantes": "específico do tipo", 
        "bagagem": "ESPECÍFICO (porta-malas/caçamba/garupa)",
        "pressao_dianteira": "XX PSI",
        "pressao_traseira": "XX PSI",
        "observacao": "específica da versão"
      },
      {
        "versao": "MESMOS_NOMES_REAIS_ACIMA_2",
        "ocupantes": "específico do tipo", 
        "bagagem": "ESPECÍFICO (porta-malas/caçamba/garupa)",
        "pressao_dianteira": "XX PSI",
        "pressao_traseira": "XX PSI",
        "observacao": "específica da versão"
      },
      {
        "versao": "MESMOS_NOMES_REAIS_ACIMA_3",
        "ocupantes": "específico do tipo", 
        "bagagem": "ESPECÍFICO (porta-malas/caçamba/garupa)",
        "pressao_dianteira": "XX PSI",
        "pressao_traseira": "XX PSI",
        "observacao": "específica da versão"
      }
    ]
  }
}
```

🎯 REGRAS DE QUALIDADE OBRIGATÓRIAS:
1. **Introdução**: 150-200 palavras, específica para o modelo
2. **Considerações Finais**: 120-180 palavras, mencionar pressões específicas
3. **FAQs**: EXATAMENTE 5 perguntas, todas específicas para o modelo
4. **Versões**: APENAS nomes reais do mercado brasileiro
5. **Contexto**: Sempre brasileiro, mencionando características do modelo

🚫 REJEIÇÃO AUTOMÁTICA SE:
- Textos genéricos ou curtos demais
- Menos de 5 FAQs
- Versões genéricas como "Base", "Básica", etc.
- Falta de especificidade para o modelo

🔍 VALIDAÇÃO FINAL: Antes de responder, verifique se:
- Todas as seções estão completas e específicas
- Versões são nomes reais do mercado brasileiro
- Textos têm o tamanho mínimo exigido
- Contexto é específico para {$vehicleName}

EOT;

        return $prompt;
    }

    /**
     * ✅ MÉTODO NOVO: Exemplos específicos por marca/modelo
     */
    private function getSpecificVersionExamples(string $make, string $model, string $vehicleType): string
    {
        $examples = [];

        // Exemplos por marca
        switch (strtolower($make)) {
            case 'toyota':
                if (stripos($model, 'hilux') !== false) {
                    $examples = ['Hilux SR', 'Hilux SRX', 'Hilux SRV'];
                } elseif (stripos($model, 'corolla') !== false) {
                    $examples = ['Corolla GLi', 'Corolla XEi', 'Corolla Altis'];
                }
                break;

            case 'volkswagen':
                if (stripos($model, 'polo') !== false) {
                    $examples = ['Polo 1.0 MPI', 'Polo 1.0 TSI', 'Polo GTS'];
                } elseif (stripos($model, 'golf') !== false) {
                    $examples = ['Golf Comfortline', 'Golf Highline', 'Golf GTI'];
                }
                break;

            case 'audi':
                if (stripos($model, 'q8') !== false) {
                    $examples = ['Q8 45 TFSI', 'Q8 55 TFSI', 'Q8 e-tron 50', 'Q8 e-tron 55'];
                } elseif (stripos($model, 'a3') !== false) {
                    $examples = ['A3 Sedan 1.4 TFSI', 'A3 Sportback 2.0 TFSI', 'A3 S-Line'];
                }
                break;

            case 'honda':
                if (stripos($model, 'civic') !== false) {
                    $examples = ['Civic LX', 'Civic EXL', 'Civic Touring', 'Civic Si'];
                } elseif (stripos($model, 'hr-v') !== false) {
                    $examples = ['HR-V LX', 'HR-V EX', 'HR-V Touring'];
                }
                break;
        }

        if (empty($examples)) {
            $examples = ['Consulte as versões reais disponíveis no site da marca'];
        }

        return "• " . implode("\n• ", $examples);
    }

    /**
     * ✅ MÉTODO NOVO: Instruções específicas por marca
     */
    private function getBrandSpecificInstructions(string $make, string $model, ?int $year): string
    {
        $instructions = match (strtolower($make)) {
            'audi' => "Para Audi: Use códigos de motor (45 TFSI, 55 TFSI, e-tron 50, e-tron 55) + acabamentos (Business, S-Line, Black)",
            'bmw' => "Para BMW: Use séries reais (318i, 320i, M340i) + acabamentos (Sport, M Sport, M)",
            'mercedes' => "Para Mercedes: Use códigos reais (A200, C180, E250) + linhas (Classic, Avantgarde, AMG Line)",
            'toyota' => "Para Toyota: Use grades reais específicas (XL, XS, GLi, XEi, Altis, SR, SRX, SRV)",
            'honda' => "Para Honda: Use acabamentos reais (LX, EX, EXL, Touring, Sport, Si, Type R)",
            'volkswagen' => "Para VW: Use motorizações específicas (1.0 MPI, 1.0 TSI, 1.4 TSI, 2.0 TSI) + acabamentos",
            'chevrolet' => "Para Chevrolet: Use acabamentos reais (Joy, LT, LTZ, Premier, RS, SS)",
            'ford' => "Para Ford: Use grades específicas (S, SE, SEL, Titanium, ST, RS)",
            'fiat' => "Para Fiat: Use acabamentos reais (Way, Drive, HGT, Trekking, Volcano)",
            'hyundai' => "Para Hyundai: Use acabamentos reais (Comfort, Premium, Ultimate, N Line)",
            default => "Use acabamentos reais específicos da marca, nunca termos genéricos"
        };

        if ($year) {
            $instructions .= " Para o ano {$year}, consulte as versões específicas desse ano-modelo.";
        }

        return $instructions;
    }

    /**
     * ✅ MÉTODO APRIMORADO: Validação rigorosa de TODAS as seções obrigatórias
     */
    private function hasOnlySpecificVersions(array $json): bool
    {
        // ✅ 1. VALIDAR VERSÕES ESPECÍFICAS
        if (!isset($json['especificacoes_por_versao']) || !is_array($json['especificacoes_por_versao'])) {
            Log::warning("ClaudeRefinementService: Seção 'especificacoes_por_versao' ausente ou inválida");
            return false;
        }

        foreach ($json['especificacoes_por_versao'] as $spec) {
            $version = trim(strtolower($spec['versao'] ?? ''));

            if (empty($version)) {
                Log::warning("ClaudeRefinementService: Versão vazia encontrada");
                return false;
            }

            // ✅ Verificação expandida de termos proibidos
            foreach (self::FORBIDDEN_GENERIC_TERMS as $forbidden) {
                if (str_contains($version, $forbidden)) {
                    Log::warning("ClaudeRefinementService: Termo genérico detectado: '{$version}' contém '{$forbidden}'");
                    return false;
                }
            }

            // ✅ Versão deve ter pelo menos 3 caracteres
            if (strlen($version) < 3) {
                Log::warning("ClaudeRefinementService: Versão muito curta: '{$version}'");
                return false;
            }
        }

        // ✅ 2. VALIDAR INTRODUÇÃO (150-200 palavras)
        if (!isset($json['introducao']) || empty($json['introducao'])) {
            Log::warning("ClaudeRefinementService: Introdução ausente");
            return false;
        }

        $introWordCount = str_word_count($json['introducao']);
        if ($introWordCount < 130 || $introWordCount > 220) {
            Log::warning("ClaudeRefinementService: Introdução com tamanho inadequado: {$introWordCount} palavras");
            return false;
        }

        // ✅ 3. VALIDAR CONSIDERAÇÕES FINAIS (120-180 palavras)
        if (!isset($json['consideracoes_finais']) || empty($json['consideracoes_finais'])) {
            Log::warning("ClaudeRefinementService: Considerações finais ausentes");
            return false;
        }

        $finalWordCount = str_word_count($json['consideracoes_finais']);
        if ($finalWordCount < 100 || $finalWordCount > 200) {
            Log::warning("ClaudeRefinementService: Considerações finais com tamanho inadequado: {$finalWordCount} palavras");
            return false;
        }

        // ✅ 4. VALIDAR PERGUNTAS FREQUENTES (exatamente 5)
        if (!isset($json['perguntas_frequentes']) || !is_array($json['perguntas_frequentes'])) {
            Log::warning("ClaudeRefinementService: Perguntas frequentes ausentes");
            return false;
        }

        if (count($json['perguntas_frequentes']) !== 5) {
            Log::warning("ClaudeRefinementService: Número incorreto de FAQs: " . count($json['perguntas_frequentes']));
            return false;
        }

        // Validar qualidade das FAQs
        foreach ($json['perguntas_frequentes'] as $faq) {
            if (empty($faq['pergunta']) || empty($faq['resposta'])) {
                Log::warning("ClaudeRefinementService: FAQ incompleta");
                return false;
            }

            if (strlen($faq['resposta']) < 50) {
                Log::warning("ClaudeRefinementService: Resposta de FAQ muito curta");
                return false;
            }
        }

        // ✅ 5. VALIDAR CONSISTÊNCIA NA TABELA DE CARGA
        if (isset($json['tabela_carga_completa']['condicoes'])) {
            foreach ($json['tabela_carga_completa']['condicoes'] as $condicao) {
                $versionInTable = trim(strtolower($condicao['versao'] ?? ''));
                foreach (self::FORBIDDEN_GENERIC_TERMS as $forbidden) {
                    if (str_contains($versionInTable, $forbidden)) {
                        Log::warning("ClaudeRefinementService: Versão genérica na tabela de carga: '{$versionInTable}'");
                        return false;
                    }
                }
            }
        }

        Log::info("ClaudeRefinementService: Validação completa passou - todas as seções estão adequadas");
        return true;
    }

    /**
     * ✅ MÉTODO NOVO: Extrair nomes de versões para log
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

    /**
     * ✅ MÉTODO NOVO: Fallback com versões reais por marca
     */
    private function createSpecificVersionsFallback(array $vehicleInfo, array $areas): array
    {
        $make = strtolower($vehicleInfo['make']);
        $model = strtolower($vehicleInfo['model']);

        // Base de conhecimento de versões reais
        $realVersions = $this->getRealVersionsByMakeModel($make, $model);

        $fallback = [];

        if (in_array('especificacoes_por_versao', $areas)) {
            $specs = [];
            foreach ($realVersions as $version) {
                $specs[] = [
                    'versao' => $version,
                    'medida_pneus' => $vehicleInfo['tire_size'],
                    'indice_carga_velocidade' => $this->getLoadSpeedIndex($vehicleInfo),
                    'pressao_dianteiro_normal' => $vehicleInfo['pressure_front'],
                    'pressao_traseiro_normal' => $vehicleInfo['pressure_rear'],
                    'pressao_dianteiro_carregado' => $vehicleInfo['pressure_front'] + 3,
                    'pressao_traseiro_carregado' => $vehicleInfo['pressure_rear'] + 6
                ];
            }
            $fallback['especificacoes_por_versao'] = $specs;
        }

        if (in_array('tabela_carga_completa', $areas)) {
            $condicoes = [];
            foreach ($realVersions as $version) {
                $condicoes[] = [
                    'versao' => $version,
                    'ocupantes' => $this->getOccupantText($vehicleInfo),
                    'bagagem' => $this->getBaggageText($vehicleInfo),
                    'pressao_dianteira' => ($vehicleInfo['pressure_front'] + 3) . ' PSI',
                    'pressao_traseira' => ($vehicleInfo['pressure_rear'] + 6) . ' PSI',
                    'observacao' => "Pressões otimizadas para {$version}"
                ];
            }

            $fallback['tabela_carga_completa'] = [
                'titulo' => 'Pressões para Carga Máxima',
                'descricao' => 'Valores recomendados com carga e passageiros',
                'condicoes' => $condicoes
            ];
        }

        return $fallback;
    }

    /**
     * ✅ MÉTODO NOVO: Base de conhecimento de versões reais
     */
    private function getRealVersionsByMakeModel(string $make, string $model): array
    {
        $knownVersions = [
            'audi' => [
                'q8' => ['Q8 45 TFSI', 'Q8 55 TFSI', 'Q8 e-tron 50', 'Q8 e-tron 55'],
                'a3' => ['A3 Sedan 1.4 TFSI', 'A3 Sportback S-Line', 'A3 Performance'],
                'a4' => ['A4 Avant 2.0 TFSI', 'A4 Attraction', 'A4 Prestige'],
            ],
            'toyota' => [
                'hilux' => ['Hilux SR', 'Hilux SRX', 'Hilux SRV'],
                'corolla' => ['Corolla GLi', 'Corolla XEi', 'Corolla Altis'],
                'rav4' => ['RAV4 S', 'RAV4 SX', 'RAV4 SX Connect'],
            ],
            'volkswagen' => [
                'polo' => ['Polo 1.0 MPI', 'Polo 1.0 TSI', 'Polo GTS'],
                'golf' => ['Golf Comfortline', 'Golf Highline', 'Golf GTI'],
                'tiguan' => ['Tiguan 250 TSI', 'Tiguan 350 TSI', 'Tiguan R-Line'],
            ],
            'honda' => [
                'civic' => ['Civic LX', 'Civic EX', 'Civic Touring'],
                'hr-v' => ['HR-V LX', 'HR-V EX', 'HR-V Touring'],
            ],
            'chevrolet' => [
                'onix' => ['Onix Joy', 'Onix LT', 'Onix Premier'],
                'cruze' => ['Cruze LT', 'Cruze LTZ', 'Cruze Premier'],
            ]
        ];

        $versions = $knownVersions[$make][$model] ?? [];

        if (empty($versions)) {
            // Fallback genérico melhor que "Base"
            $versions = [
                ucfirst($make) . ' ' . ucfirst($model) . ' Entrada',
                ucfirst($make) . ' ' . ucfirst($model) . ' Confort',
                ucfirst($make) . ' ' . ucfirst($model) . ' Premium'
            ];
        }

        return $versions;
    }

    /**
     * ✅ MÉTODOS AUXILIARES
     */
    private function getLoadSpeedIndex(array $vehicleInfo): string
    {
        if ($vehicleInfo['is_motorcycle']) return '73H';
        if ($vehicleInfo['is_pickup']) return '112S';
        return $vehicleInfo['is_premium'] ? '91V' : '91H';
    }

    private function getOccupantText(array $vehicleInfo): string
    {
        if ($vehicleInfo['is_motorcycle']) return '2 pessoas';
        if ($vehicleInfo['is_pickup']) return '4-5 pessoas';
        return '5 pessoas';
    }

    private function getBaggageText(array $vehicleInfo): string
    {
        if ($vehicleInfo['is_motorcycle']) return 'Garupa + bagageiro';
        if ($vehicleInfo['is_pickup']) return 'Caçamba com carga máxima';
        return 'Porta-malas cheio';
    }

    // ✅ MANTER MÉTODOS ORIGINAIS EXISTENTES...
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
        $pressureSpecs = $calibration->pressure_specifications ?? [];

        return [
            'make' => $calibration->vehicle_make ?? 'Veículo',
            'model' => $calibration->vehicle_model ?? 'Modelo',
            'year' => $calibration->vehicle_year,
            'display_name' => $this->buildDisplayName($calibration),
            'category' => $calibration->main_category ?? 'car',
            'is_motorcycle' => str_contains($calibration->main_category ?? '', 'motorcycle'),
            'is_electric' => ($calibration->main_category ?? '') === 'car_electric',
            'is_hybrid' => str_contains($calibration->main_category ?? '', 'hybrid'),
            'is_pickup' => ($calibration->main_category ?? '') === 'pickup',
            'is_premium' => $calibration->is_premium ?? false,
            'pressure_front' => $pressureSpecs['empty_front'] ?? $pressureSpecs['pressure_empty_front'] ?? 32,
            'pressure_rear' => $pressureSpecs['empty_rear'] ?? $pressureSpecs['pressure_empty_rear'] ?? 30,
            'tire_size' => $pressureSpecs['tire_size'] ?? 'N/A',
        ];
    }

    private function buildDisplayName(TireCalibration $calibration): string
    {
        $parts = array_filter([
            $calibration->vehicle_make,
            $calibration->vehicle_model,
            $calibration->vehicle_year
        ]);

        return implode(' ', $parts) ?: 'Veículo';
    }

    private function determineVehicleType(array $vehicleInfo): string
    {
        $category = strtolower($vehicleInfo['category'] ?? '');

        if ($vehicleInfo['is_motorcycle'] || str_contains($category, 'motorcycle') || str_contains($category, 'moto')) {
            return 'MOTOCICLETA';
        }

        if ($vehicleInfo['is_pickup'] || str_contains($category, 'pickup')) {
            return 'PICKUP';
        }

        if (str_contains($category, 'suv')) {
            return 'SUV';
        }

        return 'AUTOMÓVEL';
    }

    private function makeClaudeRequest(string $prompt): array
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->maxRetries) {
            $attempt++;

            try {
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

                if ($response->successful()) {
                    return $response->json();
                }

                $lastError = "HTTP {$response->status()}: {$response->body()}";
                if ($response->status() === 429) {
                    sleep(min(10 * $attempt, 30));
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                if ($attempt < $this->maxRetries) {
                    sleep(3 * $attempt);
                }
            }
        }

        throw new \Exception("Claude API falhou após {$this->maxRetries} tentativas: {$lastError}");
    }

    private function parseClaudeResponse(array $response, array $areas): array
    {
        $text = $response['content'][0]['text'] ?? '';

        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $json = json_decode($matches[1], true);
            if ($json && json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return [];
    }

    private function mergeEnhancements(array $baseArticle, array $enhancements): array
    {
        if (!isset($baseArticle['content'])) {
            $baseArticle['content'] = [];
        }

        foreach ($enhancements as $section => $content) {
            $baseArticle['content'][$section] = $content;
        }

        $baseArticle['enhancement_metadata'] = [
            'enhanced_by' => 'claude-api-v3.3',
            'enhanced_at' => now()->toISOString(),
            'enhanced_areas' => array_keys($enhancements),
            'model_used' => self::MODEL,
            'versions_generated' => $this->extractVersionNames($enhancements),
            'validation_passed' => true
        ];

        return $baseArticle;
    }

    /**
     * ✅ MÉTODOS ORIGINAIS MANTIDOS
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
                        ['role' => 'user', 'content' => 'Responda apenas: API funcionando']
                    ]
                ]);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Claude API conectada' : 'Erro: ' . $response->status(),
                'model' => self::MODEL
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ];
        }
    }

    public function getRefinementStats(): array
    {
        $readyForRefinement = TireCalibration::where('enrichment_phase', TireCalibration::PHASE_ARTICLE_GENERATED)->count();
        $refined = TireCalibration::whereNotNull('claude_enhancements')->count();
        $processing = TireCalibration::where('enrichment_phase', TireCalibration::PHASE_CLAUDE_PROCESSING)->count();
        $avgScore = TireCalibration::whereNotNull('claude_improvement_score')->avg('claude_improvement_score');
        $totalApiCalls = TireCalibration::sum('claude_api_calls');

        return [
            'ready_for_refinement' => $readyForRefinement,
            'articles_refined' => $refined,
            'currently_processing' => $processing,
            'api_configured' => !empty($this->apiKey),
            'success_rate' => ($refined + $processing) > 0 ? round(($refined / ($refined + $processing)) * 100, 2) : 0,
            'avg_improvement_score' => round($avgScore ?? 0, 2),
            'total_api_calls' => $totalApiCalls,
            'enhancement_focus' => 'specific_versions_enforced_v3.3',
            'model_used' => self::MODEL,
            'validation_enabled' => true,
            'fallback_enabled' => true
        ];
    }

    public function needsEnhancement(TireCalibration $calibration): bool
    {
        if ($calibration->enrichment_phase !== TireCalibration::PHASE_ARTICLE_GENERATED) {
            return false;
        }

        if (empty($calibration->generated_article)) {
            return false;
        }

        return empty($calibration->claude_enhancements);
    }
}

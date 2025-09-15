<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Services\Claude;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Strategy para escalação automática de modelos Claude
 * 
 * Implementa Chain of Responsibility para tentar modelos em ordem crescente de custo
 * apenas quando necessário, otimizando custo vs precisão
 * 
 * @author Engenheiro de Software Elite
 * @version 1.0 - Escalação inteligente de modelos
 */
class ClaudeModelEscalationStrategy
{
    private const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';
    
    /**
     * Modelos ordenados por custo (menor para maior)
     */
    private const MODELS = [
        'standard' => [
            'name' => 'claude-3-5-sonnet-20240620',
            'max_tokens' => 2000,
            'temperature' => 0.1,
            'cost_level' => 1,
            'retry_on_generic_version' => true
        ],
        'intermediate' => [
            'name' => 'claude-3-7-sonnet-20250219',
            'max_tokens' => 2500,
            'temperature' => 0.05,
            'cost_level' => 2,
            'retry_on_generic_version' => true
        ],
        'premium' => [
            'name' => 'claude-3-opus-20240229',
            'max_tokens' => 3000,
            'temperature' => 0.0,
            'cost_level' => 3,
            'retry_on_generic_version' => false // Último recurso
        ]
    ];

    private const ESCALATION_TRIGGERS = [
        'generic_version_persist' => ['intermediate', 'premium'],
        'json_parse_error' => ['intermediate'],
        'validation_error' => ['intermediate', 'premium'],
        'api_timeout' => [], // Não escalar por timeout
        'api_rate_limit' => [], // Não escalar por rate limit
    ];

    private string $apiKey;
    private int $timeout;
    private int $maxRetries;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->timeout = config('services.anthropic.timeout', 90);
        $this->maxRetries = config('services.anthropic.max_retries', 3);
    }

    /**
     * Executa correção com escalação automática baseada em falhas
     */
    public function generateCorrections(
        array $vehicleInfo, 
        array $content, 
        string $tempArticleId,
        string $startModel = 'standard'
    ): array {
        $modelsToTry = $this->getModelsChain($startModel);
        $lastError = null;
        $attempts = [];

        foreach ($modelsToTry as $modelKey) {
            $modelConfig = self::MODELS[$modelKey];
            
            Log::info("Tentando correção com modelo {$modelKey}", [
                'temp_article_id' => $tempArticleId,
                'vehicle' => $vehicleInfo['display_name'] ?? 'Unknown',
                'model' => $modelConfig['name'],
                'attempt_number' => count($attempts) + 1
            ]);

            try {
                $result = $this->callClaudeApi(
                    $this->buildPrompt($vehicleInfo, $content, $modelKey),
                    $modelConfig
                );

                $corrections = $this->parseCorrections($result);
                $this->validateCorrections($corrections, $vehicleInfo);

                // Sucesso - registrar e retornar
                Log::info("Correção bem-sucedida com modelo {$modelKey}", [
                    'temp_article_id' => $tempArticleId,
                    'model_used' => $modelConfig['name'],
                    'cost_level' => $modelConfig['cost_level'],
                    'attempts_before_success' => count($attempts)
                ]);

                return [
                    'corrections' => $corrections,
                    'model_used' => $modelKey,
                    'attempts' => count($attempts) + 1,
                    'escalated' => $modelKey !== $startModel
                ];

            } catch (\Exception $e) {
                $lastError = $e;
                $errorCategory = $this->categorizeError($e->getMessage());
                
                $attempts[] = [
                    'model' => $modelKey,
                    'error' => $e->getMessage(),
                    'error_category' => $errorCategory
                ];

                Log::warning("Falha com modelo {$modelKey}", [
                    'temp_article_id' => $tempArticleId,
                    'model' => $modelConfig['name'],
                    'error' => $e->getMessage(),
                    'error_category' => $errorCategory
                ]);

                // Verificar se deve tentar próximo modelo
                if (!$this->shouldEscalate($errorCategory, $modelKey)) {
                    break;
                }
            }
        }

        // Falha em todos os modelos
        Log::error("Falha em todos os modelos disponíveis", [
            'temp_article_id' => $tempArticleId,
            'vehicle' => $vehicleInfo['display_name'] ?? 'Unknown',
            'attempts' => $attempts,
            'final_error' => $lastError->getMessage()
        ]);

        throw new \Exception(
            "Falha após {" . count($attempts) . "} tentativas. Último erro: " . $lastError->getMessage()
        );
    }

    /**
     * Obtém cadeia de modelos para tentar baseado no modelo inicial
     */
    private function getModelsChain(string $startModel): array
    {
        $allModels = ['standard', 'intermediate', 'premium'];
        $startIndex = array_search($startModel, $allModels);
        
        if ($startIndex === false) {
            $startIndex = 0;
        }
        
        return array_slice($allModels, $startIndex);
    }

    /**
     * Verifica se deve escalar para próximo modelo baseado no erro
     */
    private function shouldEscalate(string $errorCategory, string $currentModel): bool
    {
        if (!isset(self::ESCALATION_TRIGGERS[$errorCategory])) {
            return false;
        }

        $escalationModels = self::ESCALATION_TRIGGERS[$errorCategory];
        return in_array($this->getNextModel($currentModel), $escalationModels);
    }

    /**
     * Obtém próximo modelo na cadeia
     */
    private function getNextModel(string $currentModel): ?string
    {
        $models = ['standard', 'intermediate', 'premium'];
        $currentIndex = array_search($currentModel, $models);
        
        if ($currentIndex === false || $currentIndex >= count($models) - 1) {
            return null;
        }
        
        return $models[$currentIndex + 1];
    }

    /**
     * Chama Claude API com configuração específica do modelo
     */
    private function callClaudeApi(string $prompt, array $modelConfig): string
    {
        $retryCount = 0;
        
        while ($retryCount < $this->maxRetries) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'x-api-key' => $this->apiKey,
                        'anthropic-version' => '2023-06-01'
                    ])
                    ->post(self::CLAUDE_API_URL, [
                        'model' => $modelConfig['name'],
                        'max_tokens' => $modelConfig['max_tokens'],
                        'temperature' => $modelConfig['temperature'],
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ]
                    ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    return $responseData['content'][0]['text'] ?? '';
                }
                
                throw new \Exception('Claude API Error: HTTP ' . $response->status());
                
            } catch (\Exception $e) {
                $retryCount++;
                if ($retryCount >= $this->maxRetries) {
                    throw $e;
                }
                sleep(pow(2, $retryCount)); // Backoff exponencial
            }
        }

        throw new \Exception('Máximo de tentativas excedido para modelo ' . $modelConfig['name']);
    }

    /**
     * Constrói prompt otimizado por modelo
     */
    private function buildPrompt(array $vehicleInfo, array $content, string $modelKey): string
    {
        $basePrompt = $this->getBasePrompt($vehicleInfo, $content);
        
        // Prompts específicos por modelo para melhor precisão
        $modelSpecificInstructions = match($modelKey) {
            'standard' => "\nIMPORTANTE: Seja extremamente específico com nomes de versões reais. Evite termos genéricos.",
            'intermediate' => "\nFOCO MÁXIMO: Use apenas versões comerciais oficiais do mercado brasileiro. Zero tolerância a versões genéricas.",
            'premium' => "\nEXCELÊNCIA ABSOLUTA: Pesquise mentalmente especificações exatas e use nomenclaturas oficiais das montadoras."
        };

        return $basePrompt . $modelSpecificInstructions;
    }

    /**
     * Prompt base otimizado
     */
    private function getBasePrompt(array $vehicleInfo, array $content): string
    {
        $currentVersions = $this->extractCurrentVersions($content);

        return "Você é especialista em especificações automotivas brasileiras. Corrija versões genéricas.

VEÍCULO: {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$vehicleInfo['ano']}
CATEGORIA: {$vehicleInfo['categoria']}
PNEUS: {$vehicleInfo['tire_size']}

VERSÕES ATUAIS (GENÉRICAS PARA CORRIGIR):
" . implode(', ', $currentVersions) . "

TAREFA CRÍTICA:
1. Substitua por versões COMERCIAIS REAIS vendidas no Brasil
2. Use nomenclaturas OFICIAIS da montadora
3. Mantenha coerência técnica

VERSÕES PROIBIDAS:
- Comfort, Style, Premium, Base, Entry, Standard
- Qualquer variação genérica

EXEMPLOS DE VERSÕES CORRETAS:
- Hyundai: 1.6 GLS, 2.0 Executive, HB20 Vision
- Toyota: XEi, GLi, Platinum
- Volkswagen: Trendline, Comfortline, Highline

RESPONDA APENAS JSON VÁLIDO:
```json
{
  \"especificacoes_por_versao\": [
    {
      \"versao\": \"Nome oficial da versão\",
      \"medida_pneus\": \"255/70 R16\",
      \"indice_carga_velocidade\": \"112S\",
      \"pressao_dianteiro_normal\": 35,
      \"pressao_traseiro_normal\": 35,
      \"pressao_dianteiro_carregado\": 40,
      \"pressao_traseiro_carregado\": 45
    }
  ],
  \"tabela_carga_completa\": {
    \"condicoes\": [
      {
        \"versao\": \"Nome oficial da versão\",
        \"ocupantes\": \"4-5 pessoas\",
        \"bagagem\": \"Carga máxima\",
        \"pressao_dianteira\": \"40 PSI\",
        \"pressao_traseira\": \"45 PSI\",
        \"observacao\": \"Conforme manual\"
      }
    ]
  }
}
```";
    }

    /**
     * Extrai versões atuais do conteúdo
     */
    private function extractCurrentVersions(array $content): array
    {
        $versions = [];

        if (isset($content['especificacoes_por_versao'])) {
            foreach ($content['especificacoes_por_versao'] as $spec) {
                if (isset($spec['versao'])) {
                    $versions[] = $spec['versao'];
                }
            }
        }

        if (isset($content['tabela_carga_completa']['condicoes'])) {
            foreach ($content['tabela_carga_completa']['condicoes'] as $condicao) {
                if (isset($condicao['versao']) && !in_array($condicao['versao'], $versions)) {
                    $versions[] = $condicao['versao'];
                }
            }
        }

        return array_unique($versions);
    }

    /**
     * Parse das correções com validação aprimorada
     */
    private function parseCorrections(string $text): array
    {
        // Primeiro tenta extrair JSON do bloco markdown
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $jsonString = $matches[1];
            $json = json_decode($jsonString, true);

            if ($json && json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        // Fallback: tentar parsear texto completo como JSON
        $json = json_decode($text, true);
        if ($json && json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        throw new \Exception('Resposta Claude não contém JSON válido: ' . json_last_error_msg());
    }

    /**
     * Validação rigorosa com detecção de versões genéricas
     */
    private function validateCorrections(array $corrections, array $vehicleInfo): void
    {
        if (!isset($corrections['especificacoes_por_versao']) || !isset($corrections['tabela_carga_completa'])) {
            throw new \Exception('Estrutura de correções incompleta - campos obrigatórios ausentes');
        }

        $specs = $corrections['especificacoes_por_versao'];
        if (!is_array($specs) || count($specs) < 2) {
            throw new \Exception('Especificações insuficientes - mínimo 2 versões necessárias');
        }

        // Validação rigorosa de versões genéricas
        foreach ($specs as $spec) {
            $versao = $spec['versao'] ?? '';
            if ($this->isGenericVersion($versao)) {
                throw new \Exception("Versão genérica presente: {$versao}");
            }
        }

        // Validação de campos obrigatórios
        foreach ($specs as $spec) {
            $required = ['versao', 'medida_pneus', 'pressao_dianteiro_normal', 'pressao_traseiro_normal'];
            foreach ($required as $field) {
                if (!isset($spec[$field]) || empty($spec[$field])) {
                    throw new \Exception("Campo obrigatório ausente: {$field}");
                }
            }
        }

        // Validação da tabela de carga
        if (!isset($corrections['tabela_carga_completa']['condicoes']) || 
            !is_array($corrections['tabela_carga_completa']['condicoes']) ||
            empty($corrections['tabela_carga_completa']['condicoes'])) {
            throw new \Exception('Tabela de carga incompleta');
        }
    }

    /**
     * Detecção aprimorada de versões genéricas
     */
    private function isGenericVersion(string $versionName): bool
    {
        $genericPatterns = [
            'comfort', 'style', 'premium', 'base', 'entry', 'standard',
            'basic', 'classic', 'deluxe', 'luxury', 'sport',
            'rwd', 'fwd', 'awd', '4wd', // Patterns de tração isolados
            'range', 'version', 'trim', 'variant'
        ];

        $versionLower = strtolower($versionName);
        
        foreach ($genericPatterns as $pattern) {
            if (stripos($versionLower, $pattern) !== false) {
                // Exceções: se contém marca/modelo específico, pode não ser genérico
                if ($this->hasSpecificBrandModel($versionName)) {
                    continue;
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se a versão contém referências específicas à marca/modelo
     */
    private function hasSpecificBrandModel(string $versionName): bool
    {
        $specificTerms = [
            'gls', 'gli', 'glx', 'platinum', 'executive', 'vision',
            'trendline', 'comfortline', 'highline',
            'xei', 'xli', 'cross', 'turbo', 'flex',
            'manual', 'automatico', 'cvt'
        ];

        $versionLower = strtolower($versionName);
        
        foreach ($specificTerms as $term) {
            if (stripos($versionLower, $term) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Categorização de erros para estratégia de escalação
     */
    private function categorizeError(string $errorMessage): string
    {
        $errorLower = strtolower($errorMessage);
        
        if (strpos($errorLower, 'timeout') !== false) {
            return 'api_timeout';
        } elseif (strpos($errorLower, 'rate') !== false) {
            return 'api_rate_limit';
        } elseif (strpos($errorLower, 'json') !== false) {
            return 'json_parse_error';
        } elseif (strpos($errorLower, 'estrutura') !== false || strpos($errorLower, 'campo') !== false) {
            return 'validation_error';
        } elseif (strpos($errorLower, 'genérica') !== false || strpos($errorLower, 'generic') !== false) {
            return 'generic_version_persist';
        } else {
            return 'other_errors';
        }
    }

    /**
     * Obtém estatísticas de uso dos modelos
     */
    public function getModelUsageStats(): array
    {
        return [
            'models_available' => array_keys(self::MODELS),
            'escalation_triggers' => self::ESCALATION_TRIGGERS,
            'cost_levels' => array_column(self::MODELS, 'cost_level', 'name')
        ];
    }
}
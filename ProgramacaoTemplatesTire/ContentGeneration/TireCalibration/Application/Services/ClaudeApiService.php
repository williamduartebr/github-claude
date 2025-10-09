<?php

namespace Src\ContentGeneration\TireCalibration\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ClaudeApiService - Service único para comunicação com API Claude
 * 
 * RESPONSABILIDADE ÚNICA:
 * - Comunicação direta com API Claude
 * - Recebe versão do modelo como parâmetro
 * - Parsing de respostas JSON
 * - Tratamento de erros de API
 * - Retry automático
 * 
 * USADO PELOS 3 COMANDOS:
 * - CorrectGenericVersionsStandardCommand (claude-3-5-sonnet-20240620)
 * - CorrectGenericVersionsIntermediateCommand (claude-3-7-sonnet-20250219) 
 * - CorrectGenericVersionsPremiumCommand (claude-3-opus-20240229)
 * 
 * @author Engenheiro de Software Elite  
 * @version 1.0 - Single Responsibility API Service
 */
class ClaudeApiService
{
    private const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';

    /**
     * Configurações dos modelos por versão
     */
    private const MODEL_CONFIGS = [
        'claude-3-5-sonnet-20240620' => [
            'max_tokens' => 2000,
            'temperature' => 0.1,
            'cost_level' => 'standard',
            'description' => 'Padrão - Econômico'
        ],
        'claude-3-7-sonnet-20250219' => [
            'max_tokens' => 2500,
            'temperature' => 0.05,
            'cost_level' => 'intermediate',
            'description' => 'Intermediário - Balanceado'
        ],
        'claude-3-opus-20240229' => [
            'max_tokens' => 3000,
            'temperature' => 0.0,
            'cost_level' => 'premium',
            'description' => 'Premium - Máxima Precisão'
        ]
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
     * Corrigir versões genéricas usando modelo específico
     * 
     * @param string $modelVersion Versão do modelo Claude (ex: claude-3-5-sonnet-20240620)
     * @param array $vehicleInfo Informações do veículo
     * @param array $currentContent Conteúdo atual com versões genéricas
     * @param string $tempArticleId ID do TempArticle para logs
     * @return array Resultado da correção
     */
    public function correctGenericVersions(
        string $modelVersion, 
        array $vehicleInfo, 
        array $currentContent, 
        string $tempArticleId
    ): array {
        
        if (empty($this->apiKey)) {
            throw new \Exception('Claude API Key não configurada');
        }

        if (!isset(self::MODEL_CONFIGS[$modelVersion])) {
            throw new \Exception("Modelo não suportado: {$modelVersion}");
        }

        $modelConfig = self::MODEL_CONFIGS[$modelVersion];
        $prompt = $this->buildCorrectionPrompt($vehicleInfo, $currentContent, $modelVersion);

        Log::info("Iniciando correção com Claude", [
            'model' => $modelVersion,
            'temp_article_id' => $tempArticleId,
            'vehicle' => $vehicleInfo['display_name'] ?? 'Unknown',
            'cost_level' => $modelConfig['cost_level']
        ]);

        try {
            $response = $this->callClaudeApiWithRetry($modelVersion, $modelConfig, $prompt);
            $corrections = $this->parseClaudeResponse($response);
            
            // Validar se correção foi bem-sucedida
            $this->validateCorrections($corrections, $tempArticleId);

            Log::info("Correção bem-sucedida", [
                'model' => $modelVersion,
                'temp_article_id' => $tempArticleId,
                'versions_corrected' => count($corrections['especificacoes_por_versao'] ?? [])
            ]);

            return [
                'success' => true,
                'corrections' => $corrections,
                'model_used' => $modelVersion,
                'cost_level' => $modelConfig['cost_level'],
                'tokens_used' => $modelConfig['max_tokens'],
                'metadata' => [
                    'corrected_at' => now()->toISOString(),
                    'model_description' => $modelConfig['description']
                ]
            ];

        } catch (\Exception $e) {
            Log::error("Falha na correção Claude", [
                'model' => $modelVersion,
                'temp_article_id' => $tempArticleId,
                'error' => $e->getMessage(),
                'vehicle' => $vehicleInfo['display_name'] ?? 'Unknown'
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'model_used' => $modelVersion,
                'cost_level' => $modelConfig['cost_level'],
                'error_category' => $this->categorizeError($e->getMessage())
            ];
        }
    }

    /**
     * Chamar Claude API com retry automático
     */
    private function callClaudeApiWithRetry(string $modelVersion, array $modelConfig, string $prompt): string
    {
        $retryCount = 0;
        $lastException = null;

        while ($retryCount < $this->maxRetries) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'x-api-key' => $this->apiKey,
                        'anthropic-version' => '2023-06-01'
                    ])
                    ->post(self::CLAUDE_API_URL, [
                        'model' => $modelVersion,
                        'max_tokens' => $modelConfig['max_tokens'],
                        'temperature' => $modelConfig['temperature'],
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ]
                    ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $text = $responseData['content'][0]['text'] ?? '';
                    
                    if (empty($text)) {
                        throw new \Exception('Resposta da Claude API está vazia');
                    }

                    return $text;
                }
                
                throw new \Exception('Claude API Error: HTTP ' . $response->status() . ' - ' . $response->body());
                
            } catch (\Exception $e) {
                $lastException = $e;
                $retryCount++;
                
                if ($retryCount < $this->maxRetries) {
                    // Delay exponencial entre retries
                    $delay = pow(2, $retryCount);
                    Log::warning("Retry {$retryCount} em {$delay}s", [
                        'model' => $modelVersion,
                        'error' => $e->getMessage()
                    ]);
                    sleep($delay);
                }
            }
        }
        
        throw new \Exception("Falha após {$this->maxRetries} tentativas: " . $lastException->getMessage());
    }

    /**
     * Construir prompt otimizado para correção
     */
    private function buildCorrectionPrompt(array $vehicleInfo, array $currentContent, string $modelVersion): string
    {
        $currentVersions = $this->extractCurrentVersions($currentContent);
        $brandVersions = $this->getBrandSpecificVersions($vehicleInfo['marca'] ?? '');
        $modelDescription = self::MODEL_CONFIGS[$modelVersion]['description'];

        return "
MISSÃO CRÍTICA: Eliminar TODAS as versões genéricas de especificações de pneus brasileiras.
MODELO CLAUDE: {$modelVersion} ({$modelDescription})

VEÍCULO ALVO:
🚗 {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$vehicleInfo['ano']}
⛽ Combustível: {$vehicleInfo['combustivel']}
🎯 Display: {$vehicleInfo['display_name']}

VERSÕES PROBLEMÁTICAS ATUAIS:
" . json_encode($currentVersions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "

PROBLEMA CRÍTICO:
❌ Versões contêm termos GENÉRICOS proibidos
❌ Necessária substituição por nomenclaturas REAIS do mercado brasileiro

VERSÕES REAIS PARA {$vehicleInfo['marca']}:
{$brandVersions}

TERMOS GENÉRICOS PROIBIDOS:
❌ Base, Básica, Premium, Standard, Comfort, Style, Entry, Top, Full
❌ Intermediária, Superior, Completa, Padrão, Único, Inicial

REGRAS DE OURO:
1. ✅ ZERO tolerância para termos genéricos
2. ✅ Usar APENAS nomenclaturas oficiais das montadoras
3. ✅ Manter especificações técnicas coerentes 
4. ✅ Pressões de pneu adequadas ao peso do veículo
5. ✅ Compatibilidade com ano e combustível

FORMATO OBRIGATÓRIO - JSON VÁLIDO:
```json
{
    \"especificacoes_por_versao\": [
        {
            \"versao\": \"Nome REAL da versão (ex: Trendline 1.0 TSI Manual)\",
            \"motor\": \"Especificação real (ex: 1.0 TSI Turbo)\",
            \"potencia\": \"Potência real (ex: 116 cv)\",
            \"transmissao\": \"Câmbio real (ex: Manual 6 marchas)\",
            \"tracao\": \"Tração específica (ex: Dianteira)\",
            \"pneu_dianteiro\": \"Medida exata (ex: 205/55 R16)\",
            \"pneu_traseiro\": \"Medida exata (ex: 205/55 R16)\",
            \"pressao_dianteira\": \"Pressão correta (ex: 32 psi)\",
            \"pressao_traseira\": \"Pressão correta (ex: 30 psi)\"
        }
    ],
    \"validation_passed\": true,
    \"corrections_summary\": {
        \"total_versions_corrected\": 0,
        \"generic_terms_removed\": [],
        \"real_versions_added\": []
    },
    \"model_metadata\": {
        \"claude_model\": \"{$modelVersion}\",
        \"cost_level\": \"{$modelDescription}\"
    }
}
```

VALIDAÇÃO FINAL OBRIGATÓRIA:
- ✅ Nenhuma versão pode conter palavras proibidas
- ✅ Todas as versões devem ter nomenclatura real
- ✅ Especificações técnicas factualmente corretas
- ✅ JSON válido e bem estruturado

ATENÇÃO: Esta correção é CRÍTICA. Falha resulta em erro reportado ao command.
";
    }

    /**
     * Extrair versões atuais do conteúdo
     */
    private function extractCurrentVersions(array $content): array
    {
        if (!isset($content['especificacoes_por_versao'])) {
            return [];
        }

        return collect($content['especificacoes_por_versao'])
            ->map(function ($spec) {
                return [
                    'versao' => $spec['versao'] ?? 'N/A',
                    'motor' => $spec['motor'] ?? '',
                    'transmissao' => $spec['transmissao'] ?? ''
                ];
            })
            ->toArray();
    }

    /**
     * Obter versões específicas por marca
     */
    private function getBrandSpecificVersions(string $marca): string
    {
        $brandVersions = [
            'volkswagen' => '📋 Trendline, Comfortline, Highline, Cross, Connect, Move, Run',
            'fiat' => '📋 Way, Essence, Sublime, Sporting, Adventure, Trekking, Blackjack',
            'chevrolet' => '📋 LS, LT, LTZ, Premier, Midnight, SS, RS, Advantage',
            'honda' => '📋 LX, EX, EXL, Touring, Sport, City, Fit',
            'toyota' => '📋 XEI, XLI, Altis, Cross, Dynamic, Platinum, SR',
            'hyundai' => '📋 Comfort, Vision, Premium, N Line, Limited, Ultimate',
            'ford' => '📋 S, SE, SEL, Titanium, ST, RS, EcoBoost',
            'nissan' => '📋 S, SV, SL, Advance, Exclusive, Tekna, Nismo',
            'renault' => '📋 Authentique, Expression, Dynamique, Privilege, Initiale',
            'peugeot' => '📋 Active, Allure, Feline, Griffe, Roland Garros, GT',
            'citroen' => '📋 Live, Feel, Shine, Origins, Lounge, Exclusive',
            'jeep' => '📋 Sport, Longitude, Limited, Trailhawk, Summit, SRT'
        ];

        $marcaLower = strtolower($marca);
        return $brandVersions[$marcaLower] ?? '📋 Use versões reais da montadora conforme nomenclatura oficial';
    }

    /**
     * Parse da resposta do Claude
     */
    private function parseClaudeResponse(string $text): array
    {
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

        // Fallback: tentar parsear resposta inteira como JSON
        $json = json_decode($text, true);
        if ($json && json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        throw new \Exception('Resposta da Claude API não contém JSON válido');
    }

    /**
     * Validar se correções removeram termos genéricos
     */
    private function validateCorrections(array $corrections, string $tempArticleId): void
    {
        if (!isset($corrections['especificacoes_por_versao'])) {
            throw new \Exception('Resposta não contém especificacoes_por_versao');
        }

        $versions = $corrections['especificacoes_por_versao'];
        
        if (empty($versions)) {
            throw new \Exception('Nenhuma versão encontrada na resposta');
        }

        // Verificar se ainda contém termos genéricos
        $genericTerms = [
            'base', 'basic', 'básica', 'basica',
            'premium', 'top', 'topo',
            'standard', 'padrão', 'padrao',
            'comfort', 'confort', 'style', 'estilo',
            'entry', 'entrada', 'inicial',
            'intermediate', 'intermediária', 'intermediaria',
            'superior', 'completa', 'full', 'único', 'unico'
        ];

        foreach ($versions as $index => $version) {
            $versionName = strtolower($version['versao'] ?? '');
            
            foreach ($genericTerms as $term) {
                if (strpos($versionName, $term) !== false) {
                    throw new \Exception("Versão {$index} ainda contém termo genérico: '{$term}' em '{$version['versao']}'");
                }
            }

            // Verificar campos obrigatórios
            $requiredFields = ['versao', 'motor', 'transmissao', 'pneu_dianteiro'];
            foreach ($requiredFields as $field) {
                if (!isset($version[$field]) || empty($version[$field])) {
                    throw new \Exception("Versão {$index}: campo obrigatório '{$field}' está vazio");
                }
            }
        }

        // Verificar flag de validação
        if (!isset($corrections['validation_passed']) || !$corrections['validation_passed']) {
            throw new \Exception('Flag validation_passed não está marcada como true');
        }
    }

    /**
     * Categorizar erro para relatórios
     */
    private function categorizeError(string $errorMessage): string
    {
        $errorLower = strtolower($errorMessage);
        
        if (strpos($errorLower, 'timeout') !== false) {
            return 'api_timeout';
        } elseif (strpos($errorLower, 'rate') !== false || strpos($errorLower, 'limit') !== false) {
            return 'api_rate_limit';
        } elseif (strpos($errorLower, 'json') !== false) {
            return 'json_parse_error';
        } elseif (strpos($errorLower, 'validação') !== false || strpos($errorLower, 'validation') !== false) {
            return 'validation_error';
        } elseif (strpos($errorLower, 'genéric') !== false || strpos($errorLower, 'generic') !== false) {
            return 'generic_terms_persist';
        } elseif (strpos($errorLower, 'network') !== false || strpos($errorLower, 'connection') !== false) {
            return 'network_error';
        } else {
            return 'other_error';
        }
    }

    /**
     * Testar conectividade com modelo específico
     */
    public function testConnectivity(string $modelVersion): array
    {
        if (!isset(self::MODEL_CONFIGS[$modelVersion])) {
            return [
                'success' => false,
                'message' => "Modelo não suportado: {$modelVersion}",
                'model' => $modelVersion
            ];
        }

        try {
            $modelConfig = self::MODEL_CONFIGS[$modelVersion];
            
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01'
                ])
                ->post(self::CLAUDE_API_URL, [
                    'model' => $modelVersion,
                    'max_tokens' => 50,
                    'messages' => [
                        ['role' => 'user', 'content' => "Teste de conectividade {$modelConfig['cost_level']} - responda apenas: API OK"]
                    ]
                ]);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 
                    "Claude {$modelConfig['cost_level']} conectado" : 
                    'Erro: HTTP ' . $response->status(),
                'model' => $modelVersion,
                'cost_level' => $modelConfig['cost_level'],
                'description' => $modelConfig['description']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
                'model' => $modelVersion
            ];
        }
    }

    /**
     * Obter configurações disponíveis
     */
    public function getAvailableModels(): array
    {
        return self::MODEL_CONFIGS;
    }

    /**
     * Verificar se API está configurada
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
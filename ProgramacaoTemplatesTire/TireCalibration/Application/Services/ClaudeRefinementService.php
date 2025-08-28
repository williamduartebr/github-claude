<?php

namespace Src\ContentGeneration\TireCalibration\Application\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

/**
 * ClaudeRefinementService - FOCADO EM ENHANCEMENTS ESPECÍFICOS
 * 
 * Responsável por:
 * - Enriquecer apenas áreas-chave com linguagem contextualizada
 * - Introdução rica e envolvente
 * - Considerações finais personalizadas
 * - FAQs contextuais e relevantes
 * - Alertas críticos específicos por categoria
 * 
 * @author Claude Sonnet 4
 * @version 3.0 - Especializado em enhancements
 */
class ClaudeRefinementService
{
    private const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL = 'claude-3-7-sonnet-20250219';
    private const MAX_TOKENS = 2500; // Reduzido - focado apenas em enhancements
    private const TEMPERATURE = 0.3;

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
     * Enriquecer artigo com Claude API - APENAS áreas específicas
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
            // Iniciar processamento
            $calibration->startClaudeProcessing();

            // Determinar áreas para enhancement
            $areasToEnhance = $calibration->getAreasForClaudeRefinement();

            // Extrair dados do artigo base (string)
            $baseArticle = json_decode($calibration->generated_article, true);
            $vehicleInfo = $this->extractVehicleContext($calibration, $baseArticle);

            // Gerar enhancements específicos via Claude
            $claudeEnhancements = $this->generateClaudeEnhancements(
                $vehicleInfo, 
                $areasToEnhance, 
                $baseArticle
            );

            // Criar artigo final com enhancements
            $finalArticle = $this->mergeEnhancements($baseArticle, $claudeEnhancements);

            // Finalizar processamento
            $calibration->completeClaudeProcessing($claudeEnhancements, $finalArticle);

            Log::info('ClaudeRefinementService: Enhancement concluído', [
                'tire_calibration_id' => $calibration->_id,
                'vehicle' => $vehicleInfo['display_name'],
                'enhanced_areas' => array_keys($claudeEnhancements),
                'improvement_score' => $calibration->claude_improvement_score
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
     * Extrair contexto do veículo para personalização Claude
     */
    private function extractVehicleContext(TireCalibration $calibration, array $baseArticle): array
    {
        return [
            'make' => $calibration->vehicle_make,
            'model' => $calibration->vehicle_model,
            'year' => $calibration->vehicle_year,
            'display_name' => "{$calibration->vehicle_make} {$calibration->vehicle_model}" . ($calibration->vehicle_year ? " {$calibration->vehicle_year}" : ""),
            'category' => $calibration->main_category,
            'is_motorcycle' => str_contains($calibration->main_category, 'motorcycle'),
            'is_electric' => $calibration->main_category === 'car_electric',
            'is_hybrid' => str_contains($calibration->main_category, 'hybrid'),
            'is_pickup' => $calibration->main_category === 'pickup',
            'pressure_front' => $calibration->pressure_specifications['empty_front'] ?? 32,
            'pressure_rear' => $calibration->pressure_specifications['empty_rear'] ?? 30,
            'tire_size' => $calibration->pressure_specifications['tire_size'] ?? '',
        ];
    }

    /**
     * Gerar enhancements específicos via Claude API
     */
    private function generateClaudeEnhancements(array $vehicleInfo, array $areas, array $baseArticle): array
    {
        $prompt = $this->buildEnhancementPrompt($vehicleInfo, $areas, $baseArticle);
        $response = $this->makeClaudeRequest($prompt);
        
        return $this->parseClaudeEnhancements($response, $areas);
    }

    /**
     * Construir prompt específico para enhancements
     */
    private function buildEnhancementPrompt(array $vehicleInfo, array $areas, array $baseArticle): string
    {
        $vehicleName = $vehicleInfo['display_name'];
        $category = $vehicleInfo['category'];
        $pressures = "{$vehicleInfo['pressure_front']}/{$vehicleInfo['pressure_rear']} PSI";

        $prompt = <<<EOT
Você é um especialista em redação automotiva brasileira. Sua tarefa é enriquecer APENAS as seções específicas de um artigo sobre calibragem de pneus com linguagem contextualizada e envolvente.

VEÍCULO: {$vehicleName}
CATEGORIA: {$category}
PRESSÕES: {$pressures}

SEÇÕES A ENRIQUECER: 
EOT;

        foreach ($areas as $area) {
            $prompt .= "- {$area}\n";
        }

        $prompt .= <<<EOT

DIRETRIZES DE ENHANCEMENT:

1. INTRODUÇÃO:
- Contextualizar o veículo no mercado brasileiro
- Mencionar características únicas da marca/modelo
- Explicar por que calibragem é crítica PARA ESTE veículo específico
- Tom: informativo mas envolvente
- Tamanho: 150-200 palavras

2. CONSIDERAÇÕES FINAIS:
- Resumir benefícios específicos para ESTE modelo
- Mencionar recompensas do cuidado adequado
- Contexto de mercado/popularidade do veículo
- Tom: conclusivo e motivacional
- Tamanho: 120-180 palavras

3. PERGUNTAS FREQUENTES:
- 4-5 perguntas específicas para ESTE veículo
- Respostas contextualizadas ao modelo
- Incluir particularidades da categoria (moto/carro/pickup)
- Mencionar pressões específicas em cada resposta

4. ALERTAS CRÍTICOS (se solicitado):
- Específicos para a categoria do veículo
- Consequências reais no contexto brasileiro
- Tom: sério mas não alarmista

CONTEXTO BRASILEIRO:
- Mencionar condições locais (clima, estradas)
- Usar linguagem natural brasileira
- Referenciar posto de gasolina, oficina, etc.
- Considerar perfil do usuário típico deste veículo

RETORNO:
Formate APENAS as seções solicitadas em JSON:
```json
{
  "introducao": "texto da introdução...",
  "consideracoes_finais": "texto das considerações...",
  "perguntas_frequentes": [
    {"pergunta": "...", "resposta": "..."},
    ...
  ]
}
```

ARTIGO BASE PARA CONTEXTO:
``` json
EOT;

        $prompt .= json_encode($baseArticle, JSON_UNESCAPED_UNICODE);
        $prompt .= "\n```";

        return $prompt;
    }

    /**
     * Fazer requisição para Claude API
     */
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

    /**
     * Processar resposta Claude e extrair enhancements
     */
    private function parseClaudeEnhancements(array $response, array $areas): array
    {
        $text = $response['content'][0]['text'] ?? '';
        
        // Extrair JSON da resposta
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $json = json_decode($matches[1], true);
            if ($json && json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        // Fallback: criar enhancements básicos
        return $this->createFallbackEnhancements($areas);
    }

    /**
     * Merge enhancements com artigo base
     */
    private function mergeEnhancements(array $baseArticle, array $enhancements): array
    {
        // Aplicar enhancements às seções correspondentes
        if (isset($enhancements['introducao'])) {
            $baseArticle['content']['introducao'] = $enhancements['introducao'];
        }

        if (isset($enhancements['consideracoes_finais'])) {
            $baseArticle['content']['consideracoes_finais'] = $enhancements['consideracoes_finais'];
        }

        if (isset($enhancements['perguntas_frequentes'])) {
            $baseArticle['content']['perguntas_frequentes'] = $enhancements['perguntas_frequentes'];
        }

        // Adicionar metadados Claude
        $baseArticle['enhancement_metadata'] = [
            'enhanced_by' => 'claude-api',
            'enhanced_at' => now()->toISOString(),
            'enhanced_areas' => array_keys($enhancements),
            'model_used' => self::MODEL,
        ];

        return $baseArticle;
    }

    /**
     * Criar enhancements fallback se Claude falhar
     */
    private function createFallbackEnhancements(array $areas): array
    {
        $fallback = [];

        if (in_array('introducao', $areas)) {
            $fallback['introducao'] = 'A calibragem correta dos pneus é fundamental para garantir segurança, economia e performance do seu veículo.';
        }

        if (in_array('consideracoes_finais', $areas)) {
            $fallback['consideracoes_finais'] = 'Mantenha sempre a pressão adequada para desfrutar de todos os benefícios de segurança e economia.';
        }

        if (in_array('perguntas_frequentes', $areas)) {
            $fallback['perguntas_frequentes'] = [
                [
                    'pergunta' => 'Com que frequência devo verificar a pressão dos pneus?',
                    'resposta' => 'Recomenda-se verificar mensalmente e sempre antes de viagens longas.'
                ]
            ];
        }

        return $fallback;
    }

    /**
     * Testar conectividade Claude API
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

    /**
     * Obter estatísticas de enhancement
     */
    public function getEnhancementStats(): array
    {
        $readyForEnhancement = TireCalibration::where('enrichment_phase', TireCalibration::PHASE_ARTICLE_GENERATED)->count();
        $enhanced = TireCalibration::whereNotNull('claude_enhancements')->count();
        $processing = TireCalibration::where('enrichment_phase', TireCalibration::PHASE_CLAUDE_PROCESSING)->count();
        $avgScore = TireCalibration::whereNotNull('claude_improvement_score')->avg('claude_improvement_score');
        $totalApiCalls = TireCalibration::sum('claude_api_calls');

        return [
            'ready_for_enhancement' => $readyForEnhancement,
            'articles_enhanced' => $enhanced,
            'currently_processing' => $processing,
            'api_configured' => !empty($this->apiKey),
            'success_rate' => ($enhanced + $processing) > 0 ? round(($enhanced / ($enhanced + $processing)) * 100, 2) : 0,
            'avg_improvement_score' => round($avgScore ?? 0, 2),
            'total_api_calls' => $totalApiCalls,
            'enhancement_focus' => 'context_specific_content',
            'model_used' => self::MODEL,
        ];
    }

    /**
     * Validar se artigo precisa de enhancement
     */
    public function needsEnhancement(TireCalibration $calibration): bool
    {
        // Deve ter artigo base gerado
        if ($calibration->enrichment_phase !== TireCalibration::PHASE_ARTICLE_GENERATED) {
            return false;
        }

        // Deve ter conteúdo base
        if (empty($calibration->generated_article)) {
            return false;
        }

        // Não deve ter enhancements ainda
        return empty($calibration->claude_enhancements);
    }
}
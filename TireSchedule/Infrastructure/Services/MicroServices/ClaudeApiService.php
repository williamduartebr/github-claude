<?php

namespace Src\ContentGeneration\TireSchedule\Infrastructure\Services\MicroServices;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 🤖 Micro-Service: Comunicação com Claude API
 * 
 * Responsabilidade única: Gerenciar calls para Claude API
 * Rate limiting inteligente sem bloquear aplicação
 */
class ClaudeApiService
{
    private $apiKey;
    private $apiUrl = 'https://api.anthropic.com/v1/messages';
    private $rateLimitKey = 'claude_api_rate_limit';
    
    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
    }

    /**
     * 🎯 Verificar se pode fazer request (non-blocking)
     */
    public function canMakeRequest(): bool
    {
        $lastRequest = Cache::get($this->rateLimitKey, 0);
        $timeSinceLastRequest = time() - $lastRequest;
        
        return $timeSinceLastRequest >= 60; // 1 minuto entre requests
    }

    /**
     * 🎯 Estimar quando próximo request será possível
     */
    public function getNextAvailableTime(): int
    {
        $lastRequest = Cache::get($this->rateLimitKey, 0);
        $timeSinceLastRequest = time() - $lastRequest;
        
        if ($timeSinceLastRequest >= 60) {
            return 0; // Disponível agora
        }
        
        return 60 - $timeSinceLastRequest; // Segundos para aguardar
    }

    /**
     * 🤖 Processar correção de pressões de pneus
     */
    public function processTirePressureCorrection(array $vehicleData, array $currentContent): ?array
    {
        if (!$this->canMakeRequest()) {
            Log::info("🕒 Claude API rate limited. Próximo request em: " . $this->getNextAvailableTime() . "s");
            return null;
        }

        $prompt = $this->createTirePressurePrompt($vehicleData, $currentContent);
        
        return $this->makeClaudeRequest($prompt, 'tire_pressure');
    }

    /**
     * 🤖 Processar correção de títulos e SEO
     */
    public function processTitleSeoCorrection(array $vehicleData, array $seoData, array $faqs): ?array
    {
        if (!$this->canMakeRequest()) {
            Log::info("🕒 Claude API rate limited. Próximo request em: " . $this->getNextAvailableTime() . "s");
            return null;
        }

        $prompt = $this->createTitleSeoPrompt($vehicleData, $seoData, $faqs);
        
        return $this->makeClaudeRequest($prompt, 'title_seo');
    }

    /**
     * 🔧 Request otimizado para Claude API
     */
    private function makeClaudeRequest(string $prompt, string $type): ?array
    {
        try {
            // ✅ Marcar rate limit ANTES do request
            Cache::put($this->rateLimitKey, time(), 300);
            
            $response = Http::retry(2, 3000) // ✅ Apenas 2 tentativas, 3s entre cada
                ->timeout(45) // ✅ Timeout mais agressivo: 45s
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => $type === 'tire_pressure' ? 2500 : 2000, // ✅ Menos tokens = mais rápido
                    'temperature' => 0.3,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'system' => $this->getSystemPrompt($type)
                ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');
                $correctedData = $this->extractJsonFromResponse($content);
                
                if ($correctedData) {
                    Log::info("✅ Claude API sucesso para tipo: {$type}");
                    return $correctedData;
                } else {
                    Log::warning("⚠️ Claude retornou resposta inválida para: {$type}");
                    return null;
                }
            } else {
                $statusCode = $response->status();
                $errorBody = $response->body();
                
                Log::error("❌ Claude API falhou", [
                    'type' => $type,
                    'status' => $statusCode,
                    'error' => $errorBody
                ]);
                
                return null;
            }
        } catch (\Exception $e) {
            Log::error("❌ Exceção na Claude API", [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    /**
     * 📝 Prompt otimizado para correção de pressões
     */
    private function createTirePressurePrompt(array $vehicleData, array $currentContent): string
    {
        $vehicleName = $vehicleData['vehicle_name'] ?? 'N/A';
        $vehicleYear = $vehicleData['vehicle_year'] ?? 'N/A';
        $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;
        
        $pressures = $vehicleData['pressures'] ?? [];
        $currentIntro = $currentContent['introducao'] ?? '';
        $currentConclusion = $currentContent['consideracoes_finais'] ?? '';

        $vehicleType = $isMotorcycle ? 'motocicleta' : 'veículo';

        return <<<EOT
Corrija pressões e melhore conteúdo para {$vehicleName} {$vehicleYear}.

**PRESSÕES ATUAIS:**
- Vazio: {$pressures['empty_front']}/{$pressures['empty_rear']} PSI
- Carregado: {$pressures['loaded_front']}/{$pressures['loaded_rear']} PSI
- Display: {$vehicleData['pressure_display']}

**CONTEÚDO ATUAL:**
Introdução: "{$currentIntro}"
Conclusão: "{$currentConclusion}"

**TAREFAS:**
1. Corrija pressões irreais (carregado 0 = erro)
2. Melhore introdução (mais envolvente, foque segurança)
3. Melhore conclusão (motivacional, responsabilidade)

**RETORNE JSON:**
```json
{
  "needs_update": true|false,
  "reason": "motivo breve",
  "corrected_pressures": {
    "empty_front": valor,
    "empty_rear": valor,
    "loaded_front": valor,
    "loaded_rear": valor,
    "pressure_display": "X/Y PSI",
    "pressure_loaded_display": "X/Y PSI"
  },
  "corrected_content": {
    "introducao": "nova introdução envolvente",
    "consideracoes_finais": "nova conclusão motivadora"
  }
}
```
EOT;
    }

    /**
     * 📝 Prompt otimizado para correção de títulos/SEO
     */
    private function createTitleSeoPrompt(array $vehicleData, array $seoData, array $faqs): string
    {
        $vehicleName = $vehicleData['vehicle_name'] ?? 'N/A';
        $vehicleYear = $vehicleData['vehicle_year'] ?? 'N/A';
        
        $currentTitle = $seoData['page_title'] ?? '';
        $currentMeta = $seoData['meta_description'] ?? '';

        return <<<EOT
Corrija SEO e FAQs para {$vehicleName} {$vehicleYear}.

**ATUAL:**
Título: "{$currentTitle}"
Meta: "{$currentMeta}"
FAQs: " . json_encode($faqs, JSON_UNESCAPED_UNICODE) . "

**TAREFAS:**
1. Substitua "N/A N/A N/A" por nome real do veículo
2. Inclua ano {$vehicleYear} naturalmente no título
3. Otimize meta description (150-160 chars)
4. Corrija todas as FAQs com placeholders

**RETORNE JSON:**
```json
{
  "needs_update": true|false,
  "title_updated": true|false,
  "meta_updated": true|false,
  "faq_updated": true|false,
  "corrected_seo": {
    "page_title": "título com ano",
    "meta_description": "meta com ano e pressões"
  },
  "corrected_content": {
    "perguntas_frequentes": [
      {
        "pergunta": "pergunta corrigida",
        "resposta": "resposta corrigida"
      }
    ]
  }
}
```
EOT;
    }

    /**
     * 🎯 System prompt específico por tipo
     */
    private function getSystemPrompt(string $type): string
    {
        if ($type === 'tire_pressure') {
            return "Você é um especialista automotivo brasileiro. Corrija pressões irreais e crie conteúdo envolvente sobre segurança de pneus. Sempre retorne JSON válido.";
        }
        
        return "Você é um especialista em SEO automotivo brasileiro. Inclua anos dos veículos naturalmente e otimize para conversão. Sempre retorne JSON válido.";
    }

    /**
     * 🔍 Extrair JSON da resposta Claude
     */
    private function extractJsonFromResponse(string $content): ?array
    {
        // Remove markdown
        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
            $content = $matches[1];
        }

        // Extrai JSON
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');

        if ($firstBrace !== false && $lastBrace !== false) {
            $jsonContent = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
            $result = json_decode($jsonContent, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $result;
            }
        }

        Log::warning("🔍 Falha ao extrair JSON", ['content_preview' => substr($content, 0, 200)]);
        return null;
    }

    /**
     * 📊 Obter estatísticas de performance da API
     */
    public function getApiStats(): array
    {
        $lastRequest = Cache::get($this->rateLimitKey, 0);
        $timeSinceLastRequest = time() - $lastRequest;
        
        return [
            'api_available' => $this->canMakeRequest(),
            'seconds_since_last_request' => $timeSinceLastRequest,
            'next_available_in_seconds' => $this->getNextAvailableTime(),
            'rate_limit_key' => $this->rateLimitKey,
            'api_configured' => !empty($this->apiKey)
        ];
    }

    /**
     * 🧪 Testar conectividade com Claude API
     */
    public function testConnection(): array
    {
        if (!$this->canMakeRequest()) {
            return [
                'success' => false,
                'message' => 'Rate limited - aguarde ' . $this->getNextAvailableTime() . ' segundos',
                'code' => 'RATE_LIMITED'
            ];
        }

        try {
            $testPrompt = "Responda apenas: 'Conexão OK'";
            
            Cache::put($this->rateLimitKey, time(), 300);
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => 50,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $testPrompt
                        ]
                    ]
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conexão com Claude API funcionando',
                    'response_time_ms' => $response->transferStats?->getTransferTime() * 1000 ?? 0
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Falha na conexão: ' . $response->status(),
                    'code' => 'HTTP_ERROR'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exceção: ' . $e->getMessage(),
                'code' => 'EXCEPTION'
            ];
        }
    }
}
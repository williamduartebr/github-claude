<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Service para integração com Claude 3.5 Sonnet
 * 
 * Especializado em geração de conteúdo de alta qualidade
 * com rate limiting e retry logic
 */
class Claude35SonnetService
{
    private string $apiKey;
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';
    private string $model = 'claude-3-5-sonnet-20240620';
    private int $maxRetries = 3;
    private int $retryDelay = 5; // segundos

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
        
        if (empty($this->apiKey)) {
            throw new \Exception('Claude API key não configurada');
        }
    }

    /**
     * Gerar conteúdo usando Claude 3.5 Sonnet
     */
    public function generateContent(string $prompt, array $options = []): ?array
    {
        $attempt = 0;
        $lastError = null;

        // Rate limiting check
        if (!$this->checkRateLimit()) {
            Log::warning("Rate limit ativo, aguardando...");
            sleep(60);
        }

        while ($attempt < $this->maxRetries) {
            $attempt++;

            try {
                Log::info("Chamando Claude 3.5 Sonnet", [
                    'attempt' => $attempt,
                    'prompt_length' => strlen($prompt)
                ]);

                $response = Http::timeout(60)
                    ->withHeaders([
                        'x-api-key' => $this->apiKey,
                        'anthropic-version' => '2023-06-01',
                        'content-type' => 'application/json'
                    ])
                    ->post($this->apiUrl, [
                        'model' => $options['model'] ?? $this->model,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt
                            ]
                        ],
                        'max_tokens' => $options['max_tokens'] ?? 4000,
                        'temperature' => $options['temperature'] ?? 0.3,
                        'top_p' => $options['top_p'] ?? 0.95,
                        'stop_sequences' => $options['stop_sequences'] ?? []
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Registrar uso para rate limiting
                    $this->recordUsage();
                    
                    // Extrair conteúdo
                    $content = $data['content'][0]['text'] ?? '';
                    
                    // Log de sucesso
                    Log::info("Claude 3.5 Sonnet respondeu com sucesso", [
                        'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                        'stop_reason' => $data['stop_reason'] ?? 'unknown'
                    ]);

                    return [
                        'content' => $content,
                        'usage' => $data['usage'] ?? [],
                        'model' => $data['model'] ?? $this->model,
                        'stop_reason' => $data['stop_reason'] ?? null
                    ];
                }

                // Tratar erros HTTP
                $statusCode = $response->status();
                $error = $response->json('error.message', 'Erro desconhecido');

                // Rate limit error
                if ($statusCode === 429) {
                    $retryAfter = $response->header('retry-after', 60);
                    Log::warning("Rate limit da API atingido", [
                        'retry_after' => $retryAfter
                    ]);
                    sleep((int)$retryAfter);
                    continue;
                }

                // Erro de servidor
                if ($statusCode >= 500) {
                    Log::error("Erro de servidor na Claude API", [
                        'status' => $statusCode,
                        'error' => $error
                    ]);
                    
                    if ($attempt < $this->maxRetries) {
                        sleep($this->retryDelay * $attempt);
                        continue;
                    }
                }

                // Outros erros
                throw new \Exception("Claude API error ({$statusCode}): {$error}");

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                
                Log::error("Erro ao chamar Claude 3.5 Sonnet", [
                    'attempt' => $attempt,
                    'error' => $lastError
                ]);

                if ($attempt < $this->maxRetries) {
                    sleep($this->retryDelay * $attempt);
                    continue;
                }
            }
        }

        // Todas as tentativas falharam
        Log::error("Todas as tentativas falharam para Claude 3.5 Sonnet", [
            'last_error' => $lastError
        ]);

        return null;
    }

    /**
     * Verificar rate limit interno
     */
    private function checkRateLimit(): bool
    {
        $key = 'claude_sonnet_rate_limit';
        $limit = 30; // 30 requests por hora
        $window = 3600; // 1 hora em segundos

        $current = Cache::get($key, 0);
        
        if ($current >= $limit) {
            Log::warning("Rate limit interno atingido", [
                'current' => $current,
                'limit' => $limit
            ]);
            return false;
        }

        return true;
    }

    /**
     * Registrar uso para rate limiting
     */
    private function recordUsage(): void
    {
        $key = 'claude_sonnet_rate_limit';
        $window = 3600; // 1 hora

        $current = Cache::get($key, 0);
        Cache::put($key, $current + 1, $window);

        // Registrar timestamp da última chamada
        Cache::put('claude_sonnet_last_call', now(), $window);
    }

    /**
     * Obter estatísticas de uso
     */
    public function getUsageStats(): array
    {
        $key = 'claude_sonnet_rate_limit';
        $current = Cache::get($key, 0);
        $lastCall = Cache::get('claude_sonnet_last_call');
        
        return [
            'calls_in_window' => $current,
            'calls_remaining' => max(0, 30 - $current),
            'last_call' => $lastCall ? $lastCall->diffForHumans() : 'Nunca',
            'rate_limit_active' => $current >= 30
        ];
    }

    /**
     * Limpar rate limit (para testes)
     */
    public function clearRateLimit(): void
    {
        Cache::forget('claude_sonnet_rate_limit');
        Cache::forget('claude_sonnet_last_call');
        
        Log::info("Rate limit do Claude 3.5 Sonnet foi limpo");
    }

    /**
     * Estimar custo de uma requisição
     */
    public function estimateCost(int $inputTokens, int $outputTokens): array
    {
        // Preços do Claude 3.5 Sonnet (em USD por 1M tokens)
        $inputCostPerMillion = 3.00;
        $outputCostPerMillion = 15.00;

        $inputCost = ($inputTokens / 1_000_000) * $inputCostPerMillion;
        $outputCost = ($outputTokens / 1_000_000) * $outputCostPerMillion;
        $totalCost = $inputCost + $outputCost;

        return [
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $inputTokens + $outputTokens,
            'input_cost' => round($inputCost, 6),
            'output_cost' => round($outputCost, 6),
            'total_cost' => round($totalCost, 6),
            'cost_formatted' => '$' . number_format($totalCost, 4)
        ];
    }

    /**
     * Validar resposta JSON
     */
    public function validateJsonResponse(string $content): array
    {
        // Tentar extrair JSON da resposta
        $jsonMatch = [];
        preg_match('/\{[\s\S]*\}/', $content, $jsonMatch);
        
        if (empty($jsonMatch)) {
            return [
                'valid' => false,
                'error' => 'Nenhum JSON encontrado na resposta',
                'content' => null
            ];
        }

        $json = $jsonMatch[0];
        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'valid' => false,
                'error' => 'JSON inválido: ' . json_last_error_msg(),
                'content' => null
            ];
        }

        return [
            'valid' => true,
            'error' => null,
            'content' => $decoded
        ];
    }
}
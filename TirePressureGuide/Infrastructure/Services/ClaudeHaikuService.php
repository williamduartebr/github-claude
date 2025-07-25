<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Service SIMPLES para Claude 3 Haiku
 * 
 * OTIMIZADO PARA: Dados estruturados, JSON, correções rápidas
 * CARACTERÍSTICAS: Rápido, barato, preciso para dados técnicos
 */
class ClaudeHaikuService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $rateLimitSeconds;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key');
        $this->baseUrl = 'https://api.anthropic.com/v1/messages';
        $this->rateLimitSeconds = 120; // 2 minutos entre chamadas
    }

    /**
     * Gerar conteúdo com Claude 3 Haiku
     */
    public function generateContent(string $prompt, array $options = []): string
    {
        // Rate limiting
        $this->enforceRateLimit();

        $payload = $this->buildPayload($prompt, $options);

        Log::info('ClaudeHaikuService: Enviando requisição', [
            'model' => $payload['model'],
            'max_tokens' => $payload['max_tokens'],
            'prompt_length' => strlen($prompt)
        ]);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
                ->timeout($options['timeout'] ?? 30)
                ->post($this->baseUrl, $payload);

            if (!$response->successful()) {
                throw new \Exception("Claude API error: " . $response->body());
            }

            $responseData = $response->json();
            $content = $responseData['content'][0]['text'] ?? '';

            if (empty($content)) {
                throw new \Exception('Claude retornou resposta vazia');
            }

            Log::info('ClaudeHaikuService: Resposta recebida', [
                'content_length' => strlen($content),
                'usage' => $responseData['usage'] ?? null
            ]);

            // Atualizar rate limit
            $this->updateRateLimit();

            return $content;
        } catch (\Exception $e) {
            Log::error('ClaudeHaikuService: Erro na requisição', [
                'error' => $e->getMessage(),
                'prompt_preview' => substr($prompt, 0, 200)
            ]);
            throw $e;
        }
    }

    /**
     * Construir payload para API
     */
    protected function buildPayload(string $prompt, array $options): array
    {
        return [
            'model' => 'claude-3-5-sonnet-20240620',
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.1,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];
    }

    /**
     * Controle de rate limit
     */
    protected function enforceRateLimit(): void
    {
        $cacheKey = 'claude_haiku_last_request';
        $lastRequest = Cache::get($cacheKey);

        if ($lastRequest) {
            $timeSinceLastRequest = time() - $lastRequest;

            if ($timeSinceLastRequest < $this->rateLimitSeconds) {
                $waitTime = $this->rateLimitSeconds - $timeSinceLastRequest;

                Log::info("ClaudeHaikuService: Rate limit ativo, aguardando {$waitTime} segundos");
                sleep($waitTime);
            }
        }
    }

    /**
     * Atualizar timestamp do rate limit
     */
    protected function updateRateLimit(): void
    {
        Cache::put('claude_haiku_last_request', time(), 3600); // 1 hora
    }

    /**
     * Verificar se pode fazer requisição
     */
    public function canMakeRequest(): bool
    {
        $cacheKey = 'claude_haiku_last_request';
        $lastRequest = Cache::get($cacheKey);

        if (!$lastRequest) {
            return true;
        }

        $timeSinceLastRequest = time() - $lastRequest;
        return $timeSinceLastRequest >= $this->rateLimitSeconds;
    }

    /**
     * Tempo até próxima requisição permitida
     */
    public function timeUntilNextRequest(): int
    {
        $cacheKey = 'claude_haiku_last_request';
        $lastRequest = Cache::get($cacheKey);

        if (!$lastRequest) {
            return 0;
        }

        $timeSinceLastRequest = time() - $lastRequest;
        $waitTime = $this->rateLimitSeconds - $timeSinceLastRequest;

        return max(0, $waitTime);
    }
}

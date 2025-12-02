<?php

namespace Src\TestimonyCorrection\Infrastructure\LLM;

use Illuminate\Support\Facades\Http;

class ClaudeTestimonyClient
{
    private string $model;

    public function __construct()
    {
        $this->model = env('CLAUDE_TESTIMONY_MODEL', 'claude-sonnet-4-5-20250929');
    }

    public function correct(string $prompt, array $payload): string
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) throw new \Exception('Claude API key ausente');

        $body = [
            'model' => $this->model,
            'max_tokens' => 4000,
            'temperature' => 0.1,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
                ['role' => 'user', 'content' => json_encode($payload, JSON_UNESCAPED_UNICODE)]
            ]
        ];

        $resp = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json'
        ])
            ->timeout(180)
            ->post('https://api.anthropic.com/v1/messages', $body);

        if (!$resp->successful()) {
            throw new \Exception('Erro Claude: ' . $resp->status());
        }

        return $resp->json('content.0.text') ?? '';
    }
}

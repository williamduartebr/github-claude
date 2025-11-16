<?php

namespace Src\GenericArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;
use Src\GenericArticleGenerator\Application\Services\GenerationClaudeApiService;

/**
 * GeneratePremiumOpusCommand - TESTE COM CLAUDE OPUS
 * 
 * ⚠️ COMANDO DE TESTE APENAS!
 * Este comando testa diferentes versões do Claude Opus para descobrir qual funciona.
 * 
 * MODELOS OPUS PARA TESTAR:
 * - claude-opus-4-20250514
 * - claude-3-opus-20240229
 * - claude-opus-20240229
 * - claude-opus-latest
 * 
 * ESTRATÉGIA DE TESTE:
 * 1. Tenta cada versão do Opus sequencialmente
 * 2. Se uma funcionar, registra no log
 * 3. Se todas falharem, reporta os erros
 * 
 * USO:
 * php artisan temp-article:test-opus --limit=1
 * php artisan temp-article:test-opus --model=claude-opus-4-20250514
 * 
 * @author Claude Sonnet 4.5
 * @version TEST - Descobrir versão Opus funcional
 */
class GeneratePremiumOpusCommand extends Command
{
    protected $signature = 'temp-article:test-opus
                            {--limit=1 : Quantidade de artigos para testar}
                            {--model= : Modelo específico para testar}
                            {--list-models : Apenas listar modelos disponíveis}';

    protected $description = '🧪 TESTE: Descobrir qual versão do Claude Opus funciona';

    private GenerationClaudeApiService $claudeService;

    // Versões do Opus para testar
    private const OPUS_MODELS = [
        'opus-4' => [
            'id' => 'claude-opus-4-20250514',
            'name' => 'Claude Opus 4.0 (2025)',
            'cost_multiplier' => 5.0,
        ],
        'opus-3' => [
            'id' => 'claude-3-opus-20240229',
            'name' => 'Claude 3 Opus (2024)',
            'cost_multiplier' => 4.8,
        ],
        'opus-simple' => [
            'id' => 'claude-opus-20240229',
            'name' => 'Claude Opus (sem versão)',
            'cost_multiplier' => 4.8,
        ],
        'opus-latest' => [
            'id' => 'claude-opus-latest',
            'name' => 'Claude Opus Latest',
            'cost_multiplier' => 5.0,
        ],
    ];

    private array $testResults = [];

    public function __construct(GenerationClaudeApiService $claudeService)
    {
        parent::__construct();
        $this->claudeService = $claudeService;
    }

    public function handle(): int
    {
        $this->displayHeader();

        // Opção para listar modelos apenas
        if ($this->option('list-models')) {
            $this->listModels();
            return self::SUCCESS;
        }

        if (!$this->claudeService->isConfigured()) {
            $this->error('❌ Claude API Key não configurada!');
            return self::FAILURE;
        }

        try {
            $limit = (int) $this->option('limit');
            $specificModel = $this->option('model');

            // Buscar artigos para teste
            $articles = $this->fetchTestArticles($limit);

            if ($articles->isEmpty()) {
                $this->warn('⚠️ Nenhum artigo pendente encontrado para teste');
                $this->line('💡 Execute: php artisan temp-article:seed --count=5');
                return self::SUCCESS;
            }

            $this->displayTestPlan($articles, $specificModel);

            if (!$this->confirm('Iniciar testes com Claude Opus?', true)) {
                $this->info('⏹️ Teste cancelado');
                return self::SUCCESS;
            }

            // Se modelo específico foi informado, testa apenas ele
            if ($specificModel) {
                $this->testSpecificModel($articles->first(), $specificModel);
            } else {
                // Testa todos os modelos sequencialmente
                $this->testAllModels($articles->first());
            }

            $this->displayResults();

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("💥 Erro crítico: " . $e->getMessage());
            Log::error('GeneratePremiumOpusCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Listar modelos disponíveis para teste
     */
    private function listModels(): void
    {
        $this->info('📋 MODELOS OPUS DISPONÍVEIS PARA TESTE:');
        $this->newLine();

        $this->table(
            ['Key', 'Model ID', 'Nome', 'Custo'],
            collect(self::OPUS_MODELS)->map(function($config, $key) {
                return [
                    $key,
                    $config['id'],
                    $config['name'],
                    $config['cost_multiplier'] . 'x'
                ];
            })
        );

        $this->newLine();
        $this->info('💡 USO:');
        $this->line('   Testar todos: php artisan temp-article:test-opus');
        $this->line('   Testar um específico: php artisan temp-article:test-opus --model=claude-opus-4-20250514');
    }

    /**
     * Buscar artigos para teste
     */
    private function fetchTestArticles(int $limit)
    {
        return GenerationTempArticle::where('generation_status', 'pending')
            ->whereNull('generated_at')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Testar modelo específico
     */
    private function testSpecificModel($article, string $modelId): void
    {
        $this->newLine();
        $this->info("🧪 TESTANDO MODELO ESPECÍFICO: {$modelId}");
        $this->newLine();

        $result = $this->testModel($article, $modelId, 'custom', 5.0);

        $this->testResults[$modelId] = $result;
    }

    /**
     * Testar todos os modelos sequencialmente
     */
    private function testAllModels($article): void
    {
        $this->newLine();
        $this->info('🧪 TESTANDO TODOS OS MODELOS OPUS SEQUENCIALMENTE');
        $this->newLine();

        foreach (self::OPUS_MODELS as $key => $config) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->warn("🔄 Testando: {$config['name']}");
            $this->line("   Model ID: {$config['id']}");
            $this->newLine();

            $result = $this->testModel(
                $article,
                $config['id'],
                $key,
                $config['cost_multiplier']
            );

            $this->testResults[$key] = $result;

            // Se encontrou um que funciona, perguntar se quer continuar
            if ($result['success']) {
                $this->newLine();
                $this->info('✅ MODELO FUNCIONAL ENCONTRADO!');
                
                if (!$this->confirm('Continuar testando outros modelos?', false)) {
                    $this->line('⏹️ Testes interrompidos');
                    break;
                }
            }

            // Delay entre testes
            if (count($this->testResults) < count(self::OPUS_MODELS)) {
                $this->line('⏳ Aguardando 5s antes do próximo teste...');
                sleep(5);
            }

            $this->newLine();
        }
    }

    /**
     * Testar um modelo específico
     */
    private function testModel($article, string $modelId, string $modelKey, float $costMultiplier): array
    {
        $startTime = microtime(true);

        try {
            $this->line("   📝 Artigo: {$article->title}");
            $this->line("   ⏱️ Iniciando teste...");

            // Preparar dados
            $params = [
                'title' => $article->title,
                'category_id' => $article->category_id,
                'category_name' => $article->category_name,
                'category_slug' => $article->category_slug,
                'subcategory_id' => $article->subcategory_id,
                'subcategory_name' => $article->subcategory_name,
                'subcategory_slug' => $article->subcategory_slug,
            ];

            // Chamar API diretamente (bypass do service para teste)
            $response = $this->callOpusApi($modelId, $params);

            $executionTime = round(microtime(true) - $startTime, 2);

            if ($response['success']) {
                $this->info("   ✅ SUCESSO!");
                $this->line("   ⏱️ Tempo: {$executionTime}s");
                $this->line("   💰 Custo estimado: {$costMultiplier}x");
                $this->line("   📊 Tokens: ~" . $this->estimateTokens($response['json']));

                // Salvar resultado no artigo
                $article->update([
                    'generated_json' => $response['json'],
                    'generation_status' => 'generated',
                    'generated_at' => now(),
                    'generation_model_used' => $modelKey,
                    'generation_cost' => $costMultiplier,
                ]);

                return [
                    'success' => true,
                    'model_id' => $modelId,
                    'execution_time' => $executionTime,
                    'cost' => $costMultiplier,
                    'error' => null
                ];

            } else {
                $this->error("   ❌ FALHOU");
                $this->line("   ⏱️ Tempo: {$executionTime}s");
                $this->warn("   ⚠️ Erro: " . substr($response['error'], 0, 100));

                return [
                    'success' => false,
                    'model_id' => $modelId,
                    'execution_time' => $executionTime,
                    'cost' => 0,
                    'error' => $response['error']
                ];
            }

        } catch (\Exception $e) {
            $executionTime = round(microtime(true) - $startTime, 2);

            $this->error("   💥 EXCEÇÃO");
            $this->line("   ⏱️ Tempo: {$executionTime}s");
            $this->warn("   ⚠️ " . substr($e->getMessage(), 0, 100));

            return [
                'success' => false,
                'model_id' => $modelId,
                'execution_time' => $executionTime,
                'cost' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Chamar API Anthropic diretamente
     */
    private function callOpusApi(string $modelId, array $params): array
    {
        try {
            $prompt = $this->buildPrompt($params);

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-api-key' => config('services.anthropic.api_key'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
                ->timeout(300)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $modelId,
                    'max_tokens' => 10000,
                    'temperature' => 0.1,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ]
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'API Error: ' . $response->body()
                ];
            }

            $content = $response->json()['content'][0]['text'] ?? '';
            
            if (empty($content)) {
                return [
                    'success' => false,
                    'error' => 'API retornou conteúdo vazio'
                ];
            }

            // Limpar markdown
            $content = preg_replace('/^```json\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
            $content = trim($content);

            $json = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'error' => 'JSON inválido: ' . json_last_error_msg()
                ];
            }

            // Adicionar campos do banco
            $json['category_id'] = $params['category_id'];
            $json['category_name'] = $params['category_name'];
            $json['category_slug'] = $params['category_slug'];
            $json['subcategory_id'] = $params['subcategory_id'];
            $json['subcategory_name'] = $params['subcategory_name'];
            $json['subcategory_slug'] = $params['subcategory_slug'];

            return [
                'success' => true,
                'json' => $json
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Construir prompt simplificado para teste
     */
    private function buildPrompt(array $params): string
    {
        $title = $params['title'];
        $categoryName = $params['category_name'];

        return <<<PROMPT
Você é um especialista em criar artigos técnicos automotivos para o mercado brasileiro.

Gere um artigo completo em formato JSON baseado no título: "{$title}"

Categoria: {$categoryName}

Retorne APENAS um JSON válido (sem ```json, sem explicações) com esta estrutura mínima:

{
  "title": "título do artigo",
  "slug": "slug-do-artigo",
  "template": "generic_article",
  "seo_data": {
    "page_title": "Título SEO [2025]",
    "meta_description": "Descrição 150-160 chars",
    "h1": "H1 otimizado"
  },
  "metadata": {
    "content_blocks": [
      {
        "block_id": "intro-001",
        "block_type": "intro",
        "display_order": 1,
        "content": {
          "text": "Introdução de 3-4 frases..."
        }
      },
      {
        "block_id": "tldr-001",
        "block_type": "tldr",
        "display_order": 2,
        "heading": "Resposta Rápida",
        "content": {
          "answer": "Resposta direta",
          "key_points": ["Ponto 1", "Ponto 2", "Ponto 3"]
        }
      },
      {
        "block_id": "faq-001",
        "block_type": "faq",
        "display_order": 3,
        "content": {
          "questions": [
            {"question": "Pergunta 1?", "answer": "Resposta 1"}
          ]
        }
      },
      {
        "block_id": "conclusion-001",
        "block_type": "conclusion",
        "display_order": 4,
        "content": {
          "text": "Conclusão..."
        }
      }
    ]
  }
}

Gere o artigo agora.
PROMPT;
    }

    /**
     * Estimar tokens
     */
    private function estimateTokens(array $json): int
    {
        return (int) ceil(strlen(json_encode($json)) / 4);
    }

    /**
     * Exibir header
     */
    private function displayHeader(): void
    {
        $this->newLine();
        $this->error('🧪 TESTE: DESCOBRIR VERSÃO CLAUDE OPUS FUNCIONAL');
        $this->newLine();
        $this->warn('⚠️ Este é um comando de TESTE apenas');
        $this->line('Objetivo: Descobrir qual versão do Claude Opus funciona no seu ambiente');
        $this->newLine();
    }

    /**
     * Exibir plano de teste
     */
    private function displayTestPlan($articles, ?string $specificModel): void
    {
        $this->info('📋 PLANO DE TESTE:');
        $this->newLine();

        if ($specificModel) {
            $this->line("   🎯 Testar modelo específico: {$specificModel}");
        } else {
            $this->line("   🎯 Testar todos os modelos Opus sequencialmente");
            $this->line("   📊 Total de modelos: " . count(self::OPUS_MODELS));
        }

        $this->line("   📝 Artigos para teste: {$articles->count()}");
        $this->line("   📄 Primeiro artigo: " . \Illuminate\Support\Str::limit($articles->first()->title, 60));
        $this->newLine();
    }

    /**
     * Exibir resultados finais
     */
    private function displayResults(): void
    {
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->error('🏆 RESULTADOS DOS TESTES');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $tableData = [];
        $successfulModels = [];

        foreach ($this->testResults as $key => $result) {
            $status = $result['success'] ? '✅ FUNCIONOU' : '❌ FALHOU';
            $time = $result['execution_time'] . 's';
            $cost = $result['cost'] ? $result['cost'] . 'x' : '-';
            $error = $result['error'] ? \Illuminate\Support\Str::limit($result['error'], 50) : '-';

            $tableData[] = [
                $result['model_id'],
                $status,
                $time,
                $cost,
                $error
            ];

            if ($result['success']) {
                $successfulModels[] = $result['model_id'];
            }
        }

        $this->table(
            ['Model ID', 'Status', 'Tempo', 'Custo', 'Erro'],
            $tableData
        );

        $this->newLine();

        // Resumo
        $total = count($this->testResults);
        $success = count($successfulModels);
        $failed = $total - $success;

        $this->info("📊 RESUMO:");
        $this->line("   Total testados: {$total}");
        $this->line("   ✅ Funcionaram: {$success}");
        $this->line("   ❌ Falharam: {$failed}");
        $this->newLine();

        // Recomendação
        if (!empty($successfulModels)) {
            $this->info('🎉 MODELOS FUNCIONAIS ENCONTRADOS:');
            foreach ($successfulModels as $model) {
                $this->line("   ✅ {$model}");
            }
            $this->newLine();
            $this->warn('💡 RECOMENDAÇÃO:');
            $this->line("   Use este modelo no GenerationClaudeApiService.php:");
            $this->line("   'premium' => [");
            $this->line("       'id' => '{$successfulModels[0]}',");
            $this->line("       'cost_multiplier' => 5.0,");
            $this->line("   ]");
        } else {
            $this->error('❌ NENHUM MODELO OPUS FUNCIONOU!');
            $this->newLine();
            $this->warn('💡 POSSÍVEIS CAUSAS:');
            $this->line('   • API Key sem acesso ao Opus');
            $this->line('   • Região não suportada');
            $this->line('   • Limite de rate excedido');
            $this->line('   • Modelo Opus não disponível para sua conta');
            $this->newLine();
            $this->info('✅ ALTERNATIVA:');
            $this->line('   Continue usando Sonnet 4.5 como premium:');
            $this->line("   'premium' => [");
            $this->line("       'id' => 'claude-sonnet-4-5-20250929',");
            $this->line("       'cost_multiplier' => 4.0,");
            $this->line("   ]");
        }

        $this->newLine();
    }
}
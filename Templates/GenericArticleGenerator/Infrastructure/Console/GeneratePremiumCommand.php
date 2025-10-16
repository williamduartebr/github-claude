<?php

namespace Src\GenericArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;
use Src\GenericArticleGenerator\Application\Services\GenerationClaudeApiService;

/**
 * GeneratePremiumCommand - Modelo Premium (Sonnet 4.5)
 * 
 * MODELO: claude-sonnet-4-5-20250929
 * CUSTO: 4.0x (CARO!)
 * QUALIDADE: Máxima disponível
 * VELOCIDADE: ~30-60s por artigo
 * 
 * ⚠️ ATENÇÃO: USE COM MODERAÇÃO!
 * 
 * QUANDO USAR:
 * - Artigos que falharam 2+ vezes
 * - Temas extremamente complexos
 * - Artigos flagship/pillar content
 * - Casos críticos onde qualidade máxima é essencial
 * 
 * NÃO USE PARA:
 * - Artigos comuns/simples
 * - Processamento em massa
 * - Artigos de prioridade baixa/média
 * 
 * ESTRATÉGIA:
 * 1. Apenas artigos críticos
 * 2. Máximo 1-3 artigos por execução
 * 3. Delay alto entre requisições (10s+)
 * 4. Confirmar custo antes de executar
 * 
 * USO:
 * php artisan temp-article:generate-premium --limit=1
 * php artisan temp-article:generate-premium --only-critical
 * php artisan temp-article:generate-premium --force-confirm
 * 
 * @author Claude Sonnet 4.5
 * @version 2.0 - Atualizado para Claude Sonnet 4.5
 */
class GeneratePremiumCommand extends Command
{
    protected $signature = 'temp-article:generate-premium
                            {--limit=1 : Quantidade máxima (MÁXIMO RECOMENDADO: 3)}
                            {--delay=10 : Delay entre requisições (mínimo: 10s)}
                            {--only-critical : Apenas artigos críticos (falharam 2+ vezes)}
                            {--priority=high : Prioridade mínima (high apenas)}
                            {--category= : Categoria específica}
                            {--dry-run : Simulação sem gerar}
                            {--force-confirm : Pular confirmação (cuidado!)}
                            {--max-cost=20 : Limite máximo de custo}';

    protected $description = 'Gerar artigos usando modelo PREMIUM (claude-sonnet-4-5) - ÚLTIMA INSTÂNCIA';

    private GenerationClaudeApiService $claudeService;
    private array $stats = [
        'processed' => 0,
        'successful' => 0,
        'failed' => 0,
        'total_cost' => 0.0,
        'total_time' => 0.0
    ];

    public function __construct(GenerationClaudeApiService $claudeService)
    {
        parent::__construct();
        $this->claudeService = $claudeService;
    }

    public function handle(): int
    {
        $startTime = microtime(true);

        $this->displayWarningHeader();

        if (!$this->claudeService->isConfigured()) {
            $this->error('❌ Claude API Key não configurada!');
            return self::FAILURE;
        }

        try {
            $limit = min((int) $this->option('limit'), 5);
            $delay = max((int) $this->option('delay'), 10);
            $maxCost = (float) $this->option('max-cost');
            $dryRun = $this->option('dry-run');

            if ($limit * 4.0 > $maxCost) {
                $this->error("❌ Custo estimado (" . ($limit * 4.0) . ") excede limite ({$maxCost})");
                $this->line("💡 Reduza --limit ou aumente --max-cost");
                return self::FAILURE;
            }

            $articles = $this->fetchCriticalArticles($limit);

            if ($articles->isEmpty()) {
                $this->info('✅ Nenhum artigo CRÍTICO encontrado para processar com modelo PREMIUM');
                $this->line('💡 Isso é BOM! Significa que os modelos mais baratos estão funcionando.');
                return self::SUCCESS;
            }

            $this->displayArticlesSummary($articles);

            if ($dryRun) {
                $this->warn('🧪 DRY RUN - Nenhuma geração real será executada');
                $this->displayDryRunSimulation($articles->count());
                return self::SUCCESS;
            }

            if (!$this->option('force-confirm')) {
                if (!$this->confirmCriticalExecution($articles->count())) {
                    $this->info('⏹️ Execução cancelada (decisão sábia para economizar custos)');
                    return self::SUCCESS;
                }
            }

            $this->processArticlesSequentially($articles, $delay);

            $this->stats['total_time'] = round(microtime(true) - $startTime, 2);
            $this->displayFinalResults();

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("💥 Erro crítico: " . $e->getMessage());
            Log::error('GeneratePremiumCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stats' => $this->stats
            ]);
            return self::FAILURE;
        }
    }

    private function fetchCriticalArticles(int $limit)
    {
        $query = GenerationTempArticle::query();

        if ($this->option('only-critical')) {
            $query->where('generation_status', 'failed')
                  ->where('generation_retry_count', '>=', 2);
        } else {
            $query->where('generation_priority', 'high')
                  ->where(function($q) {
                      $q->where('generation_status', 'failed')
                        ->orWhere(function($subQ) {
                            $subQ->where('generation_status', 'pending')
                                 ->whereNotNull('generation_last_attempt_at');
                        });
                  });
        }

        if ($category = $this->option('category')) {
            $query->where('category_slug', $category);
        }

        return $query->orderBy('generation_retry_count', 'desc')
                    ->orderBy('created_at', 'asc')
                    ->limit($limit)
                    ->get();
    }

    private function processArticlesSequentially($articles, int $delay): void
    {
        $total = $articles->count();

        foreach ($articles as $index => $article) {
            $current = $index + 1;
            
            $this->info("🔄 Processando artigo {$current}/{$total} (PREMIUM)");
            $this->newLine();

            $this->processArticle($article);

            if ($current < $total) {
                $this->warn("⏳ Aguardando {$delay}s antes do próximo (economia de custo)...");
                sleep($delay);
                $this->newLine();
            }

            $this->displayProgressStats($current, $total);
            $this->newLine();
        }
    }

    private function processArticle(GenerationTempArticle $article): void
    {
        $articleStartTime = microtime(true);

        $this->line("📝 Título: {$article->title}");
        $this->line("   📁 Categoria: {$article->category_name} > {$article->subcategory_name}");
        $this->line("   🎯 Prioridade: " . strtoupper($article->generation_priority));
        $this->line("   💥 Tentativas anteriores: " . ($article->generation_retry_count ?? 0));
        $this->line("   🤖 Modelos tentados: " . $this->getModelsAttempted($article));
        $this->newLine();

        try {
            $article->markAsGenerating('premium');

            $this->warn('   ⚙️ Chamando Claude Sonnet 4.5 (aguarde ~30-60s)...');

            $result = $this->claudeService->generateArticle([
                'title' => $article->title,
                'category_id' => $article->category_id,
                'category_name' => $article->category_name,
                'category_slug' => $article->category_slug,
                'subcategory_id' => $article->subcategory_id,
                'subcategory_name' => $article->subcategory_name,
                'subcategory_slug' => $article->subcategory_slug,
            ], 'premium');

            if ($result['success']) {
                $article->markAsGenerated(
                    $result['json'],
                    'premium',
                    $result['cost']
                );

                $executionTime = round(microtime(true) - $articleStartTime, 2);

                $this->info("   🎉 SUCESSO COM MODELO PREMIUM!");
                $this->line("   ⏱️ Tempo: {$executionTime}s");
                $this->line("   💰 Custo: {$result['cost']} unidades (4.0x standard)");
                $this->line("   📊 Tokens: ~{$result['tokens_estimated']}");
                $this->line("   📏 Blocos gerados: " . count($result['json']['metadata']['content_blocks'] ?? []));

                $this->stats['successful']++;
                $this->stats['total_cost'] += $result['cost'];

            } else {
                $article->markAsFailed($result['error'], 'premium');

                $this->error("   ❌ FALHA MESMO COM PREMIUM: {$result['error']}");
                $this->warn("   ⚠️ Artigo pode ter problemas fundamentais no título/tema");

                $this->stats['failed']++;
            }

        } catch (\Exception $e) {
            $article->markAsFailed($e->getMessage(), 'premium');
            $this->error("   💥 Exceção: " . $e->getMessage());
            $this->stats['failed']++;
        }

        $this->stats['processed']++;
        $this->newLine();
    }

    private function getModelsAttempted(GenerationTempArticle $article): string
    {
        $attempts = $article->generation_attempts ?? [];
        $models = array_unique(array_column($attempts, 'model'));
        return !empty($models) ? implode(' → ', $models) : 'nenhum';
    }

    private function displayWarningHeader(): void
    {
        $this->newLine();
        $this->error('⚠️⚠️⚠️ ATENÇÃO: MODELO PREMIUM (SONNET 4.5) ⚠️⚠️⚠️');
        $this->newLine();
        $this->warn('💰 CUSTO: 4.0x MAIS CARO que modelo standard');
        $this->warn('🎯 USO: Apenas casos CRÍTICOS e complexos');
        $this->warn('⏱️ VELOCIDADE: Mais lento (~30-60s por artigo)');
        $this->newLine();
        $this->info('✅ QUANDO USAR:');
        $this->line('   • Artigos que falharam 2+ vezes');
        $this->line('   • Conteúdo flagship/pillar');
        $this->line('   • Temas extremamente técnicos');
        $this->newLine();
        $this->error('❌ NÃO USE PARA:');
        $this->line('   • Artigos simples/comuns');
        $this->line('   • Processamento em massa');
        $this->line('   • Primeira tentativa');
        $this->newLine();
    }

    private function displayArticlesSummary($articles): void
    {
        $this->warn('🔴 ARTIGOS CRÍTICOS PARA PROCESSAMENTO PREMIUM:');
        $this->table(
            ['#', 'Título', 'Categoria', 'Tentativas', 'Último Modelo', 'Último Erro'],
            $articles->map(function($article, $index) {
                $lastError = $article->generation_error ?? 'N/A';
                return [
                    $index + 1,
                    \Illuminate\Support\Str::limit($article->title, 40),
                    $article->category_name,
                    $article->generation_retry_count ?? 0,
                    $article->generation_model_used ?? 'nenhum',
                    \Illuminate\Support\Str::limit($lastError, 30)
                ];
            })
        );
        $this->newLine();
    }

    private function displayDryRunSimulation(int $count): void
    {
        $estimatedCost = $count * 4.0;
        $estimatedTime = $count * ((int)$this->option('delay') + 45);

        $this->info('🧪 SIMULAÇÃO (DRY RUN):');
        $this->line("   📊 Artigos a processar: {$count}");
        $this->warn("   💰 Custo estimado: {$estimatedCost} unidades");
        $this->line("   ⏱️ Tempo estimado: " . gmdate("H:i:s", $estimatedTime));
        $this->line("   📈 Taxa de sucesso esperada: ~95%+");
        $this->newLine();
        $this->info('💡 Para executar de verdade, remova --dry-run');
    }

    private function confirmCriticalExecution(int $count): bool
    {
        $estimatedCost = $count * 4.0;
        $estimatedTime = $count * ((int)$this->option('delay') + 45);

        $this->error('🚨 CONFIRMAÇÃO CRÍTICA DE CUSTOS:');
        $this->newLine();
        $this->line("📊 Artigos: {$count}");
        $this->warn("💰 Custo TOTAL: {$estimatedCost} unidades (≈ " . ($count * 1.7) . "x intermediate)");
        $this->line("⏱️ Tempo estimado: " . gmdate("H:i:s", $estimatedTime));
        $this->line("🤖 Modelo: claude-sonnet-4-5-20250929");
        $this->newLine();
        
        $this->warn('💡 ALTERNATIVAS MAIS BARATAS:');
        $this->line("   • Tentar intermediate: php artisan temp-article:generate-intermediate --limit={$count}");
        $this->line("   • Revisar títulos: pode haver problema nos dados de entrada");
        $this->line("   • Aguardar: problemas temporários da API podem resolver sozinhos");
        $this->newLine();

        $this->error('⚠️ VOCÊ TEM CERTEZA ABSOLUTA?');
        $confirmed = $this->confirm('Prosseguir com modelo PREMIUM (custo 4.0x)?', false);

        if ($confirmed) {
            $this->warn('⚠️ ÚLTIMA CHANCE!');
            return $this->confirm('CONFIRMAR NOVAMENTE: Processar com modelo PREMIUM?', false);
        }

        return false;
    }

    private function displayProgressStats(int $current, int $total): void
    {
        $progress = round(($current / $total) * 100, 1);
        $successRate = $this->stats['processed'] > 0 
            ? round(($this->stats['successful'] / $this->stats['processed']) * 100, 1) 
            : 0;

        $this->info("📊 PROGRESSO PREMIUM:");
        $this->line("   📈 Progresso: {$current}/{$total} ({$progress}%)");
        $this->line("   ✅ Sucessos: {$this->stats['successful']}");
        $this->line("   ❌ Falhas: {$this->stats['failed']}");
        $this->line("   📊 Taxa: {$successRate}%");
        $this->warn("   💰 Custo acumulado: {$this->stats['total_cost']} unidades");
    }

    private function displayFinalResults(): void
    {
        $this->newLine();
        $this->error('🏆 RESULTADOS FINAIS - MODELO PREMIUM (SONNET 4.5)');
        $this->newLine();

        $successRate = $this->stats['processed'] > 0 
            ? round(($this->stats['successful'] / $this->stats['processed']) * 100, 1) 
            : 0;

        $avgCostPerArticle = $this->stats['successful'] > 0 
            ? round($this->stats['total_cost'] / $this->stats['successful'], 2) 
            : 0;

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Processados', $this->stats['processed']],
                ['✅ Sucessos', $this->stats['successful']],
                ['❌ Falhas', $this->stats['failed']],
                ['📈 Taxa de Sucesso', $successRate . '%'],
                ['💰 Custo Total', $this->stats['total_cost'] . ' unidades'],
                ['💵 Custo Médio/Artigo', $avgCostPerArticle . ' unidades'],
                ['⏱️ Tempo Total', $this->stats['total_time'] . 's'],
                ['🎯 Eficiência vs Standard', 'Custo 4.0x maior'],
            ]
        );

        $this->newLine();

        if ($successRate >= 90) {
            $this->info('🎉 EXCELENTE! Modelo premium resolveu os casos críticos.');
            $this->line('💡 Isso justifica o custo alto para estes artigos específicos.');
        } elseif ($successRate >= 70) {
            $this->warn('⚠️ Taxa de sucesso MODERADA com premium.');
            $this->line('💡 Possíveis problemas:');
            $this->line('   • Títulos mal formulados (problema na entrada)');
            $this->line('   • Temas impossíveis de cobrir adequadamente');
            $this->line('   • Necessidade de ajuste nos prompts');
        } else {
            $this->error('🚨 Taxa de sucesso BAIXA mesmo com premium!');
            $this->line('💡 AÇÃO URGENTE:');
            $this->line('   • Revisar TODOS os títulos destes artigos');
            $this->line('   • Verificar logs detalhados dos erros');
            $this->line('   • Considerar que alguns artigos podem ser inviáveis');
            $this->line('   • Não continuar gastando até resolver o problema raiz');
        }

        $this->newLine();
        $this->warn('💰 ANÁLISE DE CUSTO:');
        
        $equivalentStandard = round($this->stats['total_cost'] / 2.3, 0);
        $equivalentIntermediate = round($this->stats['total_cost'] / 3.5, 0);

        $this->line("   Com este custo, você poderia ter gerado:");
        $this->line("   • {$equivalentStandard} artigos com modelo STANDARD");
        $this->line("   • {$equivalentIntermediate} artigos com modelo INTERMEDIATE");
        $this->newLine();

        $this->info('📊 STATUS GERAL DO SISTEMA:');
        
        $pendingCount = GenerationTempArticle::pending()->count();
        $failedCount = GenerationTempArticle::where('generation_status', 'failed')->count();
        $generatedCount = GenerationTempArticle::where('generation_status', 'generated')->count();
        $validatedCount = GenerationTempArticle::where('generation_status', 'validated')->count();

        $this->table(
            ['Status', 'Quantidade'],
            [
                ['Pendentes', $pendingCount],
                ['Falhados', $failedCount],
                ['Gerados (aguardando validação)', $generatedCount],
                ['Validados (prontos para publicar)', $validatedCount],
            ]
        );

        $this->newLine();
        $this->info('📝 PRÓXIMAS AÇÕES RECOMENDADAS:');

        if ($failedCount > 0) {
            $this->warn("   ⚠️ {$failedCount} artigos ainda falhados:");
            $this->line('      • Revisar títulos manualmente');
            $this->line('      • Verificar se são temas viáveis');
            $this->line('      • Considerar descarte de artigos impossíveis');
        }

        if ($generatedCount > 0) {
            $this->info("   ✅ {$generatedCount} artigos gerados. Próximo passo:");
            $this->line('      📦 php artisan temp-article:validate');
        }

        if ($validatedCount > 0) {
            $this->info("   🚀 {$validatedCount} artigos validados. Próximo passo:");
            $this->line('      🌐 php artisan temp-article:publish');
        }

        $this->newLine();
        $this->warn('💡 RECOMENDAÇÃO FINAL:');
        $this->line('   Use modelo premium com MUITA MODERAÇÃO.');
        $this->line('   Priorize sempre: standard → intermediate → premium (última instância)');
    }
}
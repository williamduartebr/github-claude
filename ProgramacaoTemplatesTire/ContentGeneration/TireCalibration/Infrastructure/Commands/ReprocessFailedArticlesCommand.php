<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudePhase3AService;
use Carbon\Carbon;

/**
 * ReprocessFailedArticlesCommand - Reprocessamento Inteligente de Artigos Falhados
 * 
 * OBJETIVO:
 * - Reprocessar artigos v2 que falharam na Phase 3A
 * - Aplicar correções e melhorias nos prompts
 * - Resetar tentativas para dar nova chance
 * - Análise inteligente dos tipos de erro
 * 
 * FILTROS SUPORTADOS:
 * - version: "v2" 
 * - enrichment_phase: "failed"
 * - Análise específica de erros de validação
 * 
 * USO:
 * php artisan tire-calibration:reprocess-failed --limit=50
 * php artisan tire-calibration:reprocess-failed --reset-attempts
 * php artisan tire-calibration:reprocess-failed --analyze-errors-only
 * 
 * @author Claude Sonnet 4
 * @version 1.0 - Reprocessamento V2 Failed
 */
class ReprocessFailedArticlesCommand extends Command
{
    protected $signature = 'tire-calibration:reprocess-failed
                            {--limit=1 : Número máximo de artigos a reprocessar}
                            {--category= : Filtrar por categoria específica}
                            {--make= : Filtrar por marca específica}
                            {--reset-attempts : Resetar contador de tentativas}
                            {--analyze-errors-only : Apenas analisar tipos de erro sem reprocessar}
                            {--force-retry-all : Forçar retry mesmo com muitas tentativas}
                            {--dry-run : Simular execução sem salvar}
                            {--delay=2 : Delay entre processamentos (segundos)}
                            {--debug : Mostrar informações detalhadas}';

    protected $description = 'Reprocessar artigos V2 que falharam na Phase 3A com validações corrigidas';

    private ClaudePhase3AService $claudeService;
    private int $processedCount = 0;
    private int $successCount = 0;
    private int $errorCount = 0;
    private int $skippedCount = 0;
    private array $errorAnalysis = [];
    private array $processingStats = [];

    public function __construct(ClaudePhase3AService $claudeService)
    {
        parent::__construct();
        $this->claudeService = $claudeService;
    }

    public function handle(): ?int
    {
        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return null;
        }

        $startTime = microtime(true);

        $this->info('🔄 REPROCESSAMENTO DE ARTIGOS FALHADOS V2');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            // 1. Análise inicial dos erros
            $errorAnalysis = $this->analyzeFailedArticles();
            $this->displayErrorAnalysis($errorAnalysis);

            if ($this->option('analyze-errors-only')) {
                return self::SUCCESS;
            }

            // 2. Buscar candidatos para reprocessamento
            $candidates = $this->getFailedCandidates();

            if ($candidates->isEmpty()) {
                $this->warn('❌ Nenhum artigo falhado encontrado para reprocessamento');
                return self::SUCCESS;
            }

            $this->info("📊 Encontrados {$candidates->count()} artigo(s) falhado(s) para reprocessamento");
            $this->newLine();

            // 3. Confirmar reprocessamento
            // if (!$this->confirmReprocessing($candidates->count())) {
            //     $this->info('⏹️ Reprocessamento cancelado pelo usuário');
            //     return self::SUCCESS;
            // }

            // 4. Reset de tentativas se solicitado
            if ($this->option('reset-attempts')) {
                $this->resetProcessingAttempts($candidates);
            }

            // 5. Reprocessar artigos
            $this->processFailedArticles($candidates);

            // 6. Mostrar resultados finais
            $this->showFinalResults(microtime(true) - $startTime);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ ERRO CRÍTICO: ' . $e->getMessage());
            Log::error('ReprocessFailedArticlesCommand: Erro crítico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Analisar tipos de erro nos artigos falhados
     */
    private function analyzeFailedArticles(): array
    {
        $this->info('📊 ANÁLISE DOS ERROS EXISTENTES');
        $this->line('─────────────────────────────────────');

        $failedArticles = TireCalibration::where('version', 'v2')
            ->where('enrichment_phase', 'failed')
            ->whereNotNull('last_error')
            ->get();

        $errorCategories = [
            'validation_intro_words' => 0,
            'validation_final_words' => 0,
            'validation_faq_count' => 0,
            'validation_meta_length' => 0,
            'api_timeout' => 0,
            'api_rate_limit' => 0,
            'json_parse_error' => 0,
            'other_errors' => 0
        ];

        $detailedErrors = [];

        foreach ($failedArticles as $article) {
            $error = $article->last_error;
            $detailedErrors[] = [
                'id' => $article->_id,
                'vehicle' => "{$article->vehicle_make} {$article->vehicle_model}",
                'attempts' => $article->processing_attempts ?? 0,
                'error' => $error,
                'failed_at' => $article->failed_at
            ];

            // Categorizar erros
            if (str_contains($error, 'Introdução com') && str_contains($error, 'palavras')) {
                $errorCategories['validation_intro_words']++;
            } elseif (str_contains($error, 'Considerações finais com') && str_contains($error, 'palavras')) {
                $errorCategories['validation_final_words']++;
            } elseif (str_contains($error, 'perguntas frequentes')) {
                $errorCategories['validation_faq_count']++;
            } elseif (str_contains($error, 'timeout')) {
                $errorCategories['api_timeout']++;
            } elseif (str_contains($error, 'rate limit')) {
                $errorCategories['api_rate_limit']++;
            } elseif (str_contains($error, 'JSON')) {
                $errorCategories['json_parse_error']++;
            } else {
                $errorCategories['other_errors']++;
            }
        }

        return [
            'total_failed' => $failedArticles->count(),
            'categories' => $errorCategories,
            'detailed_errors' => collect($detailedErrors)->sortByDesc('attempts')->take(10)->toArray()
        ];
    }

    /**
     * Mostrar análise dos erros
     */
    private function displayErrorAnalysis(array $analysis): void
    {
        $this->info("📊 Total de artigos falhados: {$analysis['total_failed']}");
        $this->newLine();

        $this->line('🔍 CATEGORIAS DE ERRO:');
        foreach ($analysis['categories'] as $category => $count) {
            if ($count > 0) {
                $percentage = round(($count / $analysis['total_failed']) * 100, 1);
                $emoji = $this->getErrorEmoji($category);
                $this->line("   {$emoji} " . str_replace('_', ' ', ucfirst($category)) . ": {$count} ({$percentage}%)");
            }
        }

        $this->newLine();
        $this->line('🔍 TOP 10 ERROS DETALHADOS:');
        foreach ($analysis['detailed_errors'] as $index => $error) {
            $this->line(sprintf(
                '   %d. %s (tentativas: %d)',
                $index + 1,
                $error['vehicle'],
                $error['attempts']
            ));
            $this->line("      📋 " . $this->truncateError($error['error']));
        }

        $this->newLine();
    }

    /**
     * Buscar candidatos para reprocessamento
     */
    private function getFailedCandidates()
    {
        $query = TireCalibration::where('version', 'v2')
            ->where('enrichment_phase', 'failed')
            ->whereNotNull('generated_article');

        // Filtros opcionais
        if ($category = $this->option('category')) {
            $query->where('main_category', $category);
        }

        if ($make = $this->option('make')) {
            $query->where('vehicle_make', $make);
        }

        // Evitar artigos com muitas tentativas (a menos que forçado)
        if (!$this->option('force-retry-all')) {
            $query->where(function ($q) {
                $q->where('processing_attempts', '<=', 3)
                    ->orWhereNull('processing_attempts');
            });
        }

        return $query->limit($this->option('limit'))
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Resetar tentativas de processamento
     */
    private function resetProcessingAttempts($candidates): void
    {
        $this->info('🔄 Resetando contadores de tentativas...');

        foreach ($candidates as $candidate) {
            $candidate->update([
                'processing_attempts' => 0,
                'claude_api_calls' => 0,
                'last_error' => null,
                'claude_processing_history' => []
            ]);
        }

        $this->info("✅ Resetado para {$candidates->count()} artigo(s)");
        $this->newLine();
    }

    /**
     * Processar artigos falhados
     */
    private function processFailedArticles($candidates): void
    {
        $delay = (int) $this->option('delay');

        $this->info('🚀 INICIANDO REPROCESSAMENTO');
        $this->newLine();

        foreach ($candidates as $index => $calibration) {
            $this->processedCount++;

            try {
                $vehicleName = "{$calibration->vehicle_make} {$calibration->vehicle_model}";

                $this->line("📝 [{$this->processedCount}/{$candidates->count()}] Processando: {$vehicleName}");

                if ($this->option('dry-run')) {
                    $this->line("   🔍 [DRY-RUN] Simulando processamento...");
                    $this->successCount++;
                    sleep(1);
                    continue;
                }

                // Resetar estado para nova tentativa
                $calibration->update([
                    'enrichment_phase' => TireCalibration::PHASE_ARTICLE_GENERATED,
                    'last_error' => null,
                    'failed_at' => null
                ]);

                // Executar Phase 3A com serviço corrigido
                $startTime = microtime(true);
                $enhancements = $this->claudeService->enhanceEditorialContent($calibration);
                $processingTime = microtime(true) - $startTime;

                $this->processingStats[] = [
                    'vehicle' => $vehicleName,
                    'processing_time' => $processingTime,
                    'sections_enhanced' => array_keys($enhancements),
                    'success' => true
                ];

                $this->successCount++;
                $this->line("   ✅ Sucesso em " . round($processingTime, 2) . "s");

                if ($this->option('debug')) {
                    $this->displayProcessingDetails($enhancements);
                }
            } catch (\Exception $e) {
                $this->errorCount++;
                $errorMsg = $e->getMessage();

                $this->line("   ❌ Erro: " . $this->truncateError($errorMsg));

                Log::warning('ReprocessFailedArticlesCommand: Erro no reprocessamento', [
                    'tire_calibration_id' => $calibration->_id,
                    'vehicle' => $vehicleName,
                    'error' => $errorMsg,
                    'attempt' => $this->processedCount
                ]);

                $this->processingStats[] = [
                    'vehicle' => $vehicleName,
                    'error' => $errorMsg,
                    'success' => false
                ];
            }

            // Delay entre processamentos
            if ($delay > 0 && $index < $candidates->count() - 1) {
                sleep($delay);
            }
        }
    }

    /**
     * Confirmar reprocessamento com o usuário
     */
    private function confirmReprocessing(int $count): bool
    {
        if ($this->option('no-interaction')) {
            return true;
        }

        $this->warn("⚠️  ATENÇÃO: Você está prestes a reprocessar {$count} artigo(s) falhado(s)");
        $this->line('   - Isso consumirá créditos da API Claude');
        $this->line('   - Artigos serão resetados para status "article_generated"');
        $this->line('   - O processo pode levar vários minutos');
        $this->newLine();

        return $this->confirm('Deseja continuar com o reprocessamento?');
    }

    /**
     * Mostrar detalhes do processamento (debug)
     */
    private function displayProcessingDetails(array $enhancements): void
    {
        $this->line("   📊 Detalhes do processamento:");

        if (isset($enhancements['meta_description'])) {
            $metaLength = strlen($enhancements['meta_description']);
            $this->line("      📝 Meta description: {$metaLength} caracteres");
        }

        if (isset($enhancements['introducao'])) {
            $introWords = str_word_count($enhancements['introducao']);
            $this->line("      📖 Introdução: {$introWords} palavras");
        }

        if (isset($enhancements['consideracoes_finais'])) {
            $finalWords = str_word_count($enhancements['consideracoes_finais']);
            $this->line("      📋 Considerações finais: {$finalWords} palavras");
        }

        if (isset($enhancements['perguntas_frequentes'])) {
            $faqCount = count($enhancements['perguntas_frequentes']);
            $this->line("      ❓ Perguntas frequentes: {$faqCount} perguntas");
        }
    }

    /**
     * Mostrar resultados finais
     */
    private function showFinalResults(float $totalTime): void
    {
        $this->newLine();
        $this->info('📊 RESULTADOS DO REPROCESSAMENTO');
        $this->line('═════════════════════════════════════');

        // Estatísticas gerais
        $successRate = $this->processedCount > 0 ? round(($this->successCount / $this->processedCount) * 100, 1) : 0;
        $avgTime = $this->successCount > 0 ? round($totalTime / $this->successCount, 2) : 0;

        $this->line("📈 Processados: {$this->processedCount}");
        $this->line("✅ Sucessos: {$this->successCount}");
        $this->line("❌ Erros: {$this->errorCount}");
        $this->line("📊 Taxa de sucesso: {$successRate}%");
        $this->line("⏱️  Tempo total: " . round($totalTime, 2) . "s");
        $this->line("⚡ Tempo médio por artigo: {$avgTime}s");

        $this->newLine();

        // Análise dos novos erros
        if ($this->errorCount > 0) {
            $this->line('🔍 ANÁLISE DOS NOVOS ERROS:');

            $newErrors = collect($this->processingStats)
                ->filter(fn($stat) => !$stat['success'])
                ->groupBy('error')
                ->map(fn($group) => $group->count())
                ->sortDesc();

            foreach ($newErrors as $error => $count) {
                $this->line("   💥 " . $this->truncateError($error) . " ({$count}x)");
            }
        }

        $this->newLine();

        // Recomendações
        $this->displayRecommendations($successRate);
    }

    /**
     * Mostrar recomendações baseadas nos resultados
     */
    private function displayRecommendations(float $successRate): void
    {
        $this->line('💡 RECOMENDAÇÕES:');

        if ($successRate >= 80) {
            $this->line('   ✅ Excelente taxa de sucesso! As correções estão funcionando bem.');
            $this->line('   🚀 Considere executar o comando em lotes maiores.');
        } elseif ($successRate >= 60) {
            $this->line('   ⚠️  Taxa moderada de sucesso. Algumas melhorias podem ser necessárias.');
            $this->line('   🔧 Verifique os logs para padrões de erro.');
        } elseif ($successRate >= 40) {
            $this->line('   ⚠️  Taxa baixa de sucesso. Recomenda-se revisar o serviço.');
            $this->line('   🔍 Analise os tipos de erro mais comuns.');
            $this->line('   🛠️  Considere ajustar as validações ou prompts.');
        } else {
            $this->line('   🚨 Taxa crítica de sucesso. Ação imediata necessária!');
            $this->line('   🛑 Pare o reprocessamento e revise o código.');
            $this->line('   📞 Verifique conectividade com a API Claude.');
        }

        $this->newLine();
        $this->line('📝 PRÓXIMOS PASSOS:');
        $this->line('   1. Execute: php artisan tire-calibration:stats (verificar estatísticas atualizadas)');
        $this->line('   2. Monitore: php artisan tire-calibration:refine-3a --limit=5 (processar novos)');

        if ($this->errorCount > 0) {
            $this->line('   3. Analise: Revisar logs para padrões de erro persistentes');
            $this->line('   4. Considere: Ajustar validações baseadas nos novos erros');
        }
    }

    /**
     * Obter emoji para categoria de erro
     */
    private function getErrorEmoji(string $category): string
    {
        $emojis = [
            'validation_intro_words' => '📝',
            'validation_final_words' => '📋',
            'validation_faq_count' => '❓',
            'validation_meta_length' => '🏷️',
            'api_timeout' => '⏰',
            'api_rate_limit' => '🚦',
            'json_parse_error' => '🔧',
            'other_errors' => '❓'
        ];

        return $emojis[$category] ?? '❓';
    }

    /**
     * Truncar mensagem de erro para exibição
     */
    private function truncateError(string $error): string
    {
        return strlen($error) > 100 ? substr($error, 0, 97) . '...' : $error;
    }
}

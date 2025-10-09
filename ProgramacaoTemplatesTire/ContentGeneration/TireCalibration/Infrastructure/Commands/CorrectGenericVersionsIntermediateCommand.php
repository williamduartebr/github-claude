<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudeApiService;
use Carbon\Carbon;

/**
 * CorrectGenericVersionsIntermediateCommand - Modelo INTERMEDIÁRIO
 * 
 * FOCO: claude-3-7-sonnet-20250219 (Balanceado)
 * 
 * ESTRATÉGIA:
 * - Processa falhas do modelo PADRÃO
 * - Casos mais complexos que precisam maior precisão
 * - Custo 2.3x maior, mas maior taxa de sucesso
 * 
 * FILTROS:
 * ✅ has_generic_versions === true
 * ✅ has_specific_versions !== true
 * ✅ Opcional: corrected_by != 'claude_standard_v1' (falhas do padrão)
 * 
 * @author Engenheiro de Software Elite
 * @version 1.0 - Intermediate Model Command
 */
class CorrectGenericVersionsIntermediateCommand extends Command
{
    protected $signature = 'temp-article:correct-intermediate
                            {--limit=5 : Número máximo de registros}
                            {--batch-size=2 : Tamanho do batch (menor devido ao custo)}
                            {--dry-run : Simulação sem modificar dados}
                            {--priority=high : Prioridade (high|medium|low|all)}
                            {--force-reprocess : Reprocessar já corrigidos}
                            {--only-failed-standard : Apenas falhas do modelo padrão}
                            {--delay=5 : Delay entre requests (segundos)}
                            {--debug : Debug detalhado}
                            {--test-api : Testar conectividade}';

    protected $description = 'Corrigir versões genéricas usando Claude INTERMEDIÁRIO (claude-3-7-sonnet-20250219)';

    private const MODEL_VERSION = 'claude-3-7-sonnet-20250219';
    private const COST_LEVEL = 'intermediate';
    private const CORRECTED_BY = 'claude_intermediate_v1';
    private const COST_MULTIPLIER = 2.3;

    private ClaudeApiService $claudeService;

    // Estatísticas
    private int $totalProcessed = 0;
    private int $successfulCorrections = 0;
    private int $failedCorrections = 0;
    private int $escalatedFromStandard = 0;
    private array $errorSummary = [];
    private float $totalCostIncurred = 0.0;

    public function __construct(ClaudeApiService $claudeService)
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

        $this->displayHeader();

        if ($this->option('test-api')) {
            return $this->testApiConnectivity();
        }

        if (!$this->claudeService->isConfigured()) {
            $this->error('❌ Claude API Key não configurada!');
            return self::FAILURE;
        }

        try {
            $config = $this->getConfiguration();
            $this->displayConfiguration($config);

            $tempArticles = $this->getTempArticlesForIntermediateCorrection($config);

            if ($tempArticles->isEmpty()) {
                $this->handleNoArticlesFound($config);
                return self::SUCCESS;
            }

            $this->info("📊 Encontrados: {$tempArticles->count()} TempArticles para correção INTERMEDIÁRIA");
            $this->analyzeInputArticles($tempArticles);
            $this->newLine();

            $this->processArticlesInBatches($tempArticles, $config);
            $this->displayResults();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("💥 Erro durante execução: " . $e->getMessage());
            Log::error('CorrectIntermediateCommand failed', [
                'error' => $e->getMessage(),
                'model' => self::MODEL_VERSION
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Buscar TempArticles para correção intermediária
     */
    private function getTempArticlesForIntermediateCorrection(array $config)
    {
        $query = TempArticle::where('has_generic_versions', true);

        if ($config['only_failed_standard']) {
            // APENAS falhas do standard: corrected_by = standard + has_specific_versions = false
            $query->where('corrected_by', 'claude_standard_v1')
                ->where('has_specific_versions', false);
        } else {
            // Novos OU falhas do standard
            $query->where(function ($q) {
                $q->whereNull('corrected_by')
                    ->orWhere(function ($subQ) {
                        $subQ->where('corrected_by', 'claude_standard_v1')
                            ->where('has_specific_versions', false);
                    });
            });
        }

        return $query->orderBy('version_corrected_at', 'desc')
            ->limit($config['limit'])
            ->get();
    }

    /**
     * Analisar artigos de entrada
     */
    private function analyzeInputArticles($tempArticles): void
    {
        $standardFailed = 0;
        $neverProcessed = 0;
        $otherStates = 0;

        foreach ($tempArticles as $article) {
            if (empty($article->corrected_by)) {
                $neverProcessed++;
            } elseif ($article->corrected_by !== 'claude_standard_v1') {
                $standardFailed++;
            } else {
                $otherStates++;
            }
        }

        $this->line("📋 Análise dos artigos:");
        $this->line("   🔄 Falhas do padrão: {$standardFailed}");
        $this->line("   🆕 Nunca processados: {$neverProcessed}");
        $this->line("   📊 Outros estados: {$otherStates}");

        $this->escalatedFromStandard = $standardFailed;
    }

    /**
     * Processar artigos em lotes
     */
    private function processArticlesInBatches($tempArticles, array $config): void
    {
        $batches = $tempArticles->chunk($config['batch_size']);

        foreach ($batches as $batchIndex => $batch) {
            $this->line("🔄 Lote " . ($batchIndex + 1) . "/" . $batches->count() . " - Modelo INTERMEDIÁRIO");

            foreach ($batch as $tempArticle) {
                $this->processSingleArticle($tempArticle, $config);

                if ($config['delay'] > 0) {
                    sleep($config['delay']);
                }
            }
            $this->newLine();
        }
    }

    /**
     * Processar um artigo individual
     */
    private function processSingleArticle($tempArticle, array $config): void
    {
        $this->totalProcessed++;

        if ($config['debug']) {
            $this->line("🔍 Processando ID: {$tempArticle->id}");
            $this->line("   Veículo: {$tempArticle->vehicle_make} {$tempArticle->vehicle_model} {$tempArticle->vehicle_year}");
            $this->line("   Estado anterior: " . ($tempArticle->corrected_by ?? 'nunca processado'));
        }

        if ($config['dry_run']) {
            $this->line("🧪 [DRY RUN] Simulando correção INTERMEDIÁRIA...");
            return;
        }

        try {
            $vehicleInfo = $this->extractVehicleInfo($tempArticle);
            $currentContent = $tempArticle->content ?? [];

            // Usar Claude API Service com modelo intermediário
            $result = $this->claudeService->correctGenericVersions(
                self::MODEL_VERSION,
                $vehicleInfo,
                $currentContent,
                $tempArticle->id
            );

            if ($result['success']) {
                $this->handleSuccessfulCorrection($tempArticle, $result, $config);
            } else {
                $this->handleFailedCorrection($tempArticle, $result, $config);
            }
        } catch (\Exception $e) {
            $this->handleCorrectionException($tempArticle, $e, $config);
        }
    }

    /**
     * Lidar com correção bem-sucedida
     */
    private function handleSuccessfulCorrection($tempArticle, array $result, array $config): void
    {
        // Aplicar correções ao conteúdo
        $content = $tempArticle->content ?? [];
        $content['especificacoes_por_versao'] = $result['corrections']['especificacoes_por_versao'];

        // Marcar se foi escalação do padrão
        $wasEscalated = !empty($tempArticle->corrected_by) && $tempArticle->corrected_by !== self::CORRECTED_BY;

        // Atualizar flags de status
        $tempArticle->update([
            'content' => $content,
            'has_specific_versions' => true,      // ✅ Agora tem versões específicas
            'has_generic_versions' => false,     // ✅ Não tem mais versões genéricas
            'version_corrected_at' => now(),
            'corrected_by' => self::CORRECTED_BY,
            'correction_metadata' => [
                'model_used' => self::MODEL_VERSION,
                'cost_level' => self::COST_LEVEL,
                'escalated_from_standard' => $wasEscalated,
                'corrected_at' => now()->toISOString(),
                'versions_count' => count($result['corrections']['especificacoes_por_versao']),
                'cost_multiplier' => self::COST_MULTIPLIER
            ]
        ]);

        $this->successfulCorrections++;
        $this->totalCostIncurred += self::COST_MULTIPLIER;

        if ($config['debug']) {
            $escalationMsg = $wasEscalated ? ' (escalação bem-sucedida)' : '';
            $this->line("   ✅ Sucesso{$escalationMsg}! Versões: " . count($result['corrections']['especificacoes_por_versao']));
        }
    }

    /**
     * Lidar com correção falhada
     */
    private function handleFailedCorrection($tempArticle, array $result, array $config): void
    {
        $this->failedCorrections++;

        $errorCategory = $result['error_category'] ?? 'unknown';
        $this->errorSummary[$errorCategory] = ($this->errorSummary[$errorCategory] ?? 0) + 1;

        Log::warning('Falha na correção INTERMEDIÁRIA', [
            'temp_article_id' => $tempArticle->id,
            'vehicle' => "{$tempArticle->vehicle_make} {$tempArticle->vehicle_model}",
            'error' => $result['error'],
            'model' => self::MODEL_VERSION,
            'error_category' => $errorCategory,
            'previous_correction' => $tempArticle->corrected_by
        ]);

        if ($config['debug']) {
            $this->line("   ❌ Falha: " . substr($result['error'], 0, 100));
        }
    }

    /**
     * Lidar com exceção durante correção
     */
    private function handleCorrectionException($tempArticle, \Exception $e, array $config): void
    {
        $this->failedCorrections++;

        $errorCategory = 'exception';
        $this->errorSummary[$errorCategory] = ($this->errorSummary[$errorCategory] ?? 0) + 1;

        Log::error('Exceção na correção INTERMEDIÁRIA', [
            'temp_article_id' => $tempArticle->id,
            'vehicle' => "{$tempArticle->vehicle_make} {$tempArticle->vehicle_model}",
            'error' => $e->getMessage(),
            'model' => self::MODEL_VERSION
        ]);

        if ($config['debug']) {
            $this->line("   💥 Exceção: " . substr($e->getMessage(), 0, 100));
        }
    }

    /**
     * Extrair informações do veículo
     */
    private function extractVehicleInfo($tempArticle): array
    {
        return [
            'marca' => $tempArticle->vehicle_make ?? 'Unknown',
            'modelo' => $tempArticle->vehicle_model ?? 'Unknown',
            'ano' => $tempArticle->vehicle_year ?? date('Y'),
            'combustivel' => $tempArticle->vehicle_fuel ?? 'flex',
            'display_name' => "{$tempArticle->vehicle_make} {$tempArticle->vehicle_model} {$tempArticle->vehicle_year}"
        ];
    }

    /**
     * Teste de conectividade da API
     */
    private function testApiConnectivity(): int
    {
        $this->info('🌐 Testando conectividade Claude INTERMEDIÁRIO...');

        $result = $this->claudeService->testConnectivity(self::MODEL_VERSION);

        if ($result['success']) {
            $this->line("   ✅ {$result['message']}");
            $this->line("   📊 Modelo: {$result['model']}");
            $this->line("   💰 Custo: {$result['cost_level']} (" . self::COST_MULTIPLIER . "x)");
            $this->line("   📝 Descrição: {$result['description']}");
            return self::SUCCESS;
        } else {
            $this->error("   ❌ {$result['message']}");
            return self::FAILURE;
        }
    }

    /**
     * Obter configuração do comando
     */
    private function getConfiguration(): array
    {
        return [
            'limit' => (int) $this->option('limit'),
            'batch_size' => (int) $this->option('batch-size'),
            'dry_run' => $this->option('dry-run'),
            'priority' => $this->option('priority'),
            'force_reprocess' => $this->option('force-reprocess'),
            'only_failed_standard' => $this->option('only-failed-standard'),
            'delay' => (int) $this->option('delay'),
            'debug' => $this->option('debug')
        ];
    }

    /**
     * Exibir cabeçalho
     */
    private function displayHeader(): void
    {
        $this->info('🚀 CORREÇÃO VERSÕES GENÉRICAS - MODELO INTERMEDIÁRIO');
        $this->info('🤖 Claude: ' . self::MODEL_VERSION);
        $this->info('💰 Custo: ' . self::COST_LEVEL . ' (' . self::COST_MULTIPLIER . 'x padrão)');
        $this->info('🎯 Foco: Casos complexos e escalações');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();
    }

    /**
     * Exibir configuração
     */
    private function displayConfiguration(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO INTERMEDIÁRIA:');
        $this->line('   📊 Limite: ' . $config['limit']);
        $this->line('   📦 Batch: ' . $config['batch_size'] . ' (reduzido devido ao custo)');
        $this->line('   🎯 Prioridade: ' . $config['priority']);
        $this->line('   🔄 Modo: ' . ($config['dry_run'] ? '🧪 SIMULAÇÃO' : '💾 EXECUÇÃO'));
        $this->line('   ♻️ Reprocessar: ' . ($config['force_reprocess'] ? 'SIM' : 'NÃO'));
        $this->line('   🔄 Apenas falhas padrão: ' . ($config['only_failed_standard'] ? 'SIM' : 'NÃO'));
        $this->line('   ⏱️ Delay: ' . $config['delay'] . 's (maior devido complexidade)');
        $this->line('   🐛 Debug: ' . ($config['debug'] ? 'SIM' : 'NÃO'));
        $this->newLine();
    }

    /**
     * Lidar com nenhum artigo encontrado
     */
    private function handleNoArticlesFound(array $config): void
    {
        $this->warn('🔍 Nenhum TempArticle encontrado para correção INTERMEDIÁRIA');

        if ($config['only_failed_standard']) {
            $this->line('💡 Critério: apenas falhas do modelo padrão');
            $this->line('✅ Isso pode significar que o modelo PADRÃO está funcionando bem!');
        } else {
            $this->line('💡 Critérios gerais:');
            $this->line('   • has_generic_versions = true');
            $this->line('   • has_specific_versions != true');
        }

        $this->newLine();
        $this->line('📝 Comandos sugeridos:');
        $this->line('   • Verificar padrão: php artisan temp-article:correct-standard --test-api');
        $this->line('   • Investigar novos: php artisan temp-article:investigate-generic-versions');
    }

    /**
     * Exibir resultados finais
     */
    private function displayResults(): void
    {
        $this->newLine();
        $this->info('📈 RESULTADOS MODELO INTERMEDIÁRIO:');
        $this->newLine();

        $successRate = $this->totalProcessed > 0 ?
            round(($this->successfulCorrections / $this->totalProcessed) * 100, 1) : 0;

        $escalationSuccessRate = $this->escalatedFromStandard > 0 ?
            round(($this->successfulCorrections / $this->escalatedFromStandard) * 100, 1) : 0;

        $this->line("📊 Total processado: {$this->totalProcessed}");
        $this->line("✅ Correções bem-sucedidas: {$this->successfulCorrections}");
        $this->line("❌ Falhas: {$this->failedCorrections}");
        $this->line("📈 Taxa de sucesso: {$successRate}%");
        $this->line("🔄 Escalações do padrão: {$this->escalatedFromStandard}");
        if ($this->escalatedFromStandard > 0) {
            $this->line("⬆️ Taxa sucesso escalação: {$escalationSuccessRate}%");
        }
        $this->line("💰 Custo total: {$this->totalCostIncurred} unidades (" . self::COST_MULTIPLIER . "x cada)");
        $this->newLine();

        // Resumo de erros
        if (!empty($this->errorSummary)) {
            $this->warn('⚠️ RESUMO DE ERROS:');
            foreach ($this->errorSummary as $category => $count) {
                $emoji = $this->getErrorEmoji($category);
                $this->line("   {$emoji} {$category}: {$count}x");
            }
            $this->newLine();
        }

        // Análise de custo-benefício
        $this->displayCostAnalysis();

        // Recomendações
        $this->displayRecommendations($successRate, $escalationSuccessRate);
    }

    /**
     * Exibir análise de custo-benefício
     */
    private function displayCostAnalysis(): void
    {
        if ($this->totalProcessed === 0) return;

        $this->info('💰 ANÁLISE CUSTO-BENEFÍCIO:');

        $costPerSuccess = $this->successfulCorrections > 0 ?
            round($this->totalCostIncurred / $this->successfulCorrections, 2) : 0;

        $this->line("   📊 Custo por sucesso: {$costPerSuccess} unidades");
        $this->line("   🔄 Eficiência escalação: " . ($this->escalatedFromStandard > 0 ? 'Justificada' : 'N/A'));

        if ($this->escalatedFromStandard > 0 && $this->successfulCorrections > 0) {
            $escalationValue = round(($this->successfulCorrections / $this->escalatedFromStandard) * 100, 1);
            $this->line("   ⬆️ Valor da escalação: {$escalationValue}% dos casos resolvidos");
        }

        $this->newLine();
    }

    /**
     * Exibir recomendações
     */
    private function displayRecommendations(float $successRate, float $escalationSuccessRate): void
    {
        $this->info('💡 RECOMENDAÇÕES MODELO INTERMEDIÁRIO:');

        if ($successRate >= 90) {
            $this->line('   🎉 Excelente! Modelo INTERMEDIÁRIO muito eficaz.');
            $this->line('   💰 Custo justificado pela alta taxa de sucesso.');
            if ($this->escalatedFromStandard > 0) {
                $this->line('   ⬆️ Escalação do padrão funcionando perfeitamente.');
            }
        } elseif ($successRate >= 75) {
            $this->line('   👍 Boa performance do modelo INTERMEDIÁRIO.');
            $this->line('   💰 Custo-benefício adequado para casos complexos.');
            if ($escalationSuccessRate >= 70) {
                $this->line('   ⬆️ Escalação eficiente - continue usando.');
            }
        } elseif ($successRate >= 60) {
            $this->line('   ⚠️ Performance moderada do modelo INTERMEDIÁRIO.');
            $this->line('   🔧 Para falhas persistentes, considere modelo PREMIUM.');
            $this->line('   📊 Execute: php artisan temp-article:correct-premium');
        } else {
            $this->line('   🚨 Performance baixa mesmo no modelo INTERMEDIÁRIO!');
            $this->line('   ⬆️ Escalação crítica: php artisan temp-article:correct-premium');
            $this->line('   🔍 Revisar qualidade dos dados de entrada.');
        }

        $this->newLine();
        $this->info('📝 PRÓXIMOS PASSOS:');

        if ($this->failedCorrections > 0) {
            $this->line('   1. Para falhas críticas: php artisan temp-article:correct-premium --limit=' . $this->failedCorrections);
            $this->line('   2. Analisar patterns: revisar logs para padrões de erro');
        }

        if ($this->successfulCorrections > 0) {
            $this->line('   3. Verificar resultados: TempArticle::where("corrected_by", "' . self::CORRECTED_BY . '")->count()');
        }

        $this->line('   4. Monitorar custos: acompanhar ROI do modelo intermediário');
    }

    /**
     * Obter emoji para categoria de erro
     */
    private function getErrorEmoji(string $category): string
    {
        $emojis = [
            'generic_terms_persist' => '🔄',
            'json_parse_error' => '🔧',
            'validation_error' => '📋',
            'api_timeout' => '⏰',
            'api_rate_limit' => '🚦',
            'network_error' => '🌐',
            'exception' => '💥',
            'unknown' => '❓'
        ];

        return $emojis[$category] ?? '❓';
    }
}

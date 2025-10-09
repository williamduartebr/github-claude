<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudeApiService;
use Carbon\Carbon;

/**
 * CorrectGenericVersionsPremiumCommand - Modelo PREMIUM
 * 
 * FOCO: claude-3-opus-20240229 (Máxima Precisão)
 * 
 * ESTRATÉGIA PREMIUM:
 * - ÚLTIMO RECURSO para casos críticos
 * - Falhas dos modelos Standard E Intermediate
 * - Custo 4.8x maior, mas máxima taxa de sucesso
 * - Processamento cauteloso (batch menor, delay maior)
 * 
 * FILTROS CRÍTICOS:
 * ✅ has_generic_versions === true
 * ✅ has_specific_versions !== true  
 * ✅ Preferencialmente: falhas de standard/intermediate
 * 
 * @author Engenheiro de Software Elite
 * @version 1.0 - Premium Model Command - Last Resort
 */
class CorrectGenericVersionsPremiumCommand extends Command
{
    protected $signature = 'temp-article:correct-premium
                            {--limit=3 : Número máximo de registros (baixo devido ao custo)}
                            {--batch-size=1 : Processamento individual (máxima atenção)}
                            {--dry-run : Simulação sem modificar dados}
                            {--priority=high : Prioridade (high|medium|low|all)}
                            {--force-reprocess : Reprocessar já corrigidos}
                            {--only-critical : Apenas casos críticos (falhas de outros modelos)}
                            {--delay=8 : Delay maior entre requests (segundos)}
                            {--debug : Debug detalhado}
                            {--test-api : Testar conectividade}
                            {--cost-confirmation : Confirmar custos antes de executar}';

    protected $description = 'Corrigir versões genéricas usando Claude PREMIUM (claude-3-opus-20240229) - ÚLTIMO RECURSO';

    private const MODEL_VERSION = 'claude-3-opus-20240229';
    private const COST_LEVEL = 'premium';
    private const CORRECTED_BY = 'claude_premium_v1';
    private const COST_MULTIPLIER = 4.8;

    private ClaudeApiService $claudeService;

    // Estatísticas
    private int $totalProcessed = 0;
    private int $successfulCorrections = 0;
    private int $failedCorrections = 0;
    private int $criticalEscalations = 0;
    private array $errorSummary = [];
    private float $totalCostIncurred = 0.0;
    private array $processingDetails = [];

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

            // Confirmação de custo se solicitada
            if ($config['cost_confirmation'] && !$this->confirmCosts($config)) {
                $this->info('⏹️ Execução cancelada pelo usuário.');
                return self::SUCCESS;
            }

            $tempArticles = $this->getTempArticlesForPremiumCorrection($config);

            if ($tempArticles->isEmpty()) {
                $this->handleNoArticlesFound($config);
                return self::SUCCESS;
            }

            $this->displayCostWarning($tempArticles->count());
            $this->analyzeInputArticles($tempArticles);
            $this->newLine();

            $this->processArticlesWithMaximumCare($tempArticles, $config);
            $this->displayPremiumResults();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("💥 Erro durante execução PREMIUM: " . $e->getMessage());
            Log::error('CorrectPremiumCommand failed', [
                'error' => $e->getMessage(),
                'model' => self::MODEL_VERSION,
                'cost_incurred' => $this->totalCostIncurred
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Buscar TempArticles para correção premium (casos críticos)
     */
    private function getTempArticlesForPremiumCorrection(array $config)
    {
        $query = TempArticle::where('has_generic_versions', true);

        if ($config['only_critical']) {
            // APENAS falhas do intermediate: corrected_by = intermediate + has_specific_versions = false
            $query->where('corrected_by', 'claude_intermediate_v1')
                ->where('has_specific_versions', false);
        } else {
            // Falhas do standard OU intermediate
            $query->where(function ($q) {
                $q->where(function ($subQ) {
                    // Falhas do standard
                    $subQ->where('corrected_by', 'claude_standard_v1')
                        ->where('has_specific_versions', false);
                })
                    ->orWhere(function ($subQ) {
                        // Falhas do intermediate
                        $subQ->where('corrected_by', 'claude_intermediate_v1')
                            ->where('has_specific_versions', false);
                    });
            });
        }

        return $query->orderBy('version_corrected_at', 'desc')
            ->limit($config['limit'])
            ->get();
    }

    /**
     * Analisar artigos de entrada para relatório
     */
    private function analyzeInputArticles($tempArticles): void
    {
        $standardFailed = 0;
        $intermediateFailed = 0;
        $neverProcessed = 0;
        $otherStates = 0;

        foreach ($tempArticles as $article) {
            if (empty($article->corrected_by)) {
                $neverProcessed++;
            } elseif ($article->corrected_by === 'claude_standard_v1') {
                $standardFailed++;
            } elseif ($article->corrected_by === 'claude_intermediate_v1') {
                $intermediateFailed++;
            } else {
                $otherStates++;
            }
        }

        $this->criticalEscalations = $standardFailed + $intermediateFailed;

        $this->warn("🔍 ANÁLISE CRÍTICA DOS ARTIGOS:");
        $this->line("   🚨 Falhas do PADRÃO: {$standardFailed}");
        $this->line("   🚨 Falhas do INTERMEDIÁRIO: {$intermediateFailed}");
        $this->line("   🆕 Nunca processados: {$neverProcessed}");
        $this->line("   📊 Outros estados: {$otherStates}");
        $this->line("   ⚠️ Total escalações críticas: {$this->criticalEscalations}");
    }

    /**
     * Exibir aviso de custo
     */
    private function displayCostWarning(int $articleCount): void
    {
        $estimatedCost = $articleCount * self::COST_MULTIPLIER;

        $this->warn("💰 AVISO DE CUSTO PREMIUM:");
        $this->line("   📊 Artigos a processar: {$articleCount}");
        $this->line("   💸 Custo por artigo: " . self::COST_MULTIPLIER . "x unidades");
        $this->line("   💳 Custo estimado total: {$estimatedCost} unidades");
        $this->line("   🔥 Este é o modelo MAIS CARO disponível!");
        $this->newLine();
    }

    /**
     * Confirmar custos com usuário
     */
    private function confirmCosts(array $config): bool
    {
        $estimatedCost = $config['limit'] * self::COST_MULTIPLIER;

        $this->warn("💰 CONFIRMAÇÃO DE CUSTO PREMIUM:");
        $this->line("Processamento estimado: {$config['limit']} artigos x " . self::COST_MULTIPLIER . " = {$estimatedCost} unidades");
        $this->newLine();

        return $this->confirm('⚠️ Confirma o uso do modelo PREMIUM com este custo?');
    }

    /**
     * Processar artigos com máximo cuidado
     */
    private function processArticlesWithMaximumCare($tempArticles, array $config): void
    {
        $this->info("🎯 PROCESSAMENTO PREMIUM - MÁXIMO CUIDADO");
        $this->line("Processando individualmente cada artigo...");
        $this->newLine();

        foreach ($tempArticles as $index => $tempArticle) {
            $this->line("🔥 Artigo " . ($index + 1) . "/{$tempArticles->count()} - MODELO PREMIUM");
            $this->processSingleArticleWithCare($tempArticle, $config, $index + 1);

            if ($config['delay'] > 0 && $index < $tempArticles->count() - 1) {
                $this->line("   ⏱️ Aguardando {$config['delay']}s antes do próximo...");
                sleep($config['delay']);
            }

            $this->newLine();
        }
    }

    /**
     * Processar um artigo individual com máximo cuidado
     */
    private function processSingleArticleWithCare($tempArticle, array $config, int $position): void
    {
        $this->totalProcessed++;
        $startTime = microtime(true);

        if ($config['debug']) {
            $this->line("   🔍 ID: {$tempArticle->id}");
            $this->line("   🚗 Veículo: {$tempArticle->vehicle_make} {$tempArticle->vehicle_model} {$tempArticle->vehicle_year}");
            $this->line("   📝 Estado anterior: " . ($tempArticle->corrected_by ?? 'nunca processado'));
            $this->line("   💰 Custo desta operação: " . self::COST_MULTIPLIER . " unidades");
        }

        if ($config['dry_run']) {
            $this->line("   🧪 [DRY RUN] Simulando correção PREMIUM...");
            return;
        }

        try {
            $vehicleInfo = $this->extractVehicleInfo($tempArticle);
            $currentContent = $tempArticle->content ?? [];

            $this->line("   🤖 Iniciando processamento com Claude OPUS...");

            // Usar Claude API Service com modelo premium
            $result = $this->claudeService->correctGenericVersions(
                self::MODEL_VERSION,
                $vehicleInfo,
                $currentContent,
                $tempArticle->id
            );

            $processingTime = round(microtime(true) - $startTime, 2);

            if ($result['success']) {
                $this->handlePremiumSuccess($tempArticle, $result, $config, $processingTime, $position);
            } else {
                $this->handlePremiumFailure($tempArticle, $result, $config, $processingTime, $position);
            }
        } catch (\Exception $e) {
            $processingTime = round(microtime(true) - $startTime, 2);
            $this->handlePremiumException($tempArticle, $e, $config, $processingTime, $position);
        }
    }

    /**
     * Lidar com sucesso premium
     */
    private function handlePremiumSuccess($tempArticle, array $result, array $config, float $processingTime, int $position): void
    {
        // Aplicar correções ao conteúdo
        $content = $tempArticle->content ?? [];
        $content['especificacoes_por_versao'] = $result['corrections']['especificacoes_por_versao'];

        $previousModel = $tempArticle->corrected_by ?? 'none';
        $wasCriticalEscalation = in_array($previousModel, ['claude_standard_v1', 'claude_intermediate_v1']);

        // Atualizar flags de status
        $tempArticle->update([
            'content' => $content,
            'has_specific_versions' => true,      // ✅ Finalmente corrigido!
            'has_generic_versions' => false,     // ✅ Versões genéricas eliminadas
            'version_corrected_at' => now(),
            'corrected_by' => self::CORRECTED_BY,
            'correction_metadata' => [
                'model_used' => self::MODEL_VERSION,
                'cost_level' => self::COST_LEVEL,
                'previous_model' => $previousModel,
                'critical_escalation' => $wasCriticalEscalation,
                'processing_time' => $processingTime,
                'corrected_at' => now()->toISOString(),
                'versions_count' => count($result['corrections']['especificacoes_por_versao']),
                'cost_multiplier' => self::COST_MULTIPLIER,
                'position_in_batch' => $position
            ]
        ]);

        $this->successfulCorrections++;
        $this->totalCostIncurred += self::COST_MULTIPLIER;

        $this->processingDetails[] = [
            'id' => $tempArticle->id,
            'success' => true,
            'processing_time' => $processingTime,
            'versions_count' => count($result['corrections']['especificacoes_por_versao']),
            'previous_model' => $previousModel
        ];

        $escalationMsg = $wasCriticalEscalation ? " (ESCALAÇÃO CRÍTICA RESOLVIDA!)" : "";
        $this->line("   ✅ SUCESSO PREMIUM{$escalationMsg}");
        $this->line("   📊 Versões corrigidas: " . count($result['corrections']['especificacoes_por_versao']));
        $this->line("   ⏱️ Tempo processamento: {$processingTime}s");
        $this->line("   💰 Custo: " . self::COST_MULTIPLIER . " unidades");
    }

    /**
     * Lidar com falha premium
     */
    private function handlePremiumFailure($tempArticle, array $result, array $config, float $processingTime, int $position): void
    {
        $this->failedCorrections++;

        $errorCategory = $result['error_category'] ?? 'unknown';
        $this->errorSummary[$errorCategory] = ($this->errorSummary[$errorCategory] ?? 0) + 1;

        // MARCAR FALHA CRÍTICA DO PREMIUM - ÚLTIMO RECURSO FALHOU
        $tempArticle->update([
            'corrected_by' => self::CORRECTED_BY,
            'has_generic_versions' => true,
            'has_specific_versions' => false,
            'version_corrected_at' => now(),
            'correction_metadata' => [
                'model_used' => self::MODEL_VERSION,
                'failed' => true,
                'error' => $result['error'],
                'error_category' => $errorCategory,
                'failed_at' => now()->toISOString(),
                'escalated_from' => 'intermediate',
                'critical_failure' => true, // Último recurso falhou
                'processing_time' => $processingTime,
                'cost_incurred' => self::COST_MULTIPLIER
            ]
        ]);

        $this->processingDetails[] = [
            'id' => $tempArticle->id,
            'success' => false,
            'processing_time' => $processingTime,
            'error_category' => $errorCategory,
            'previous_model' => $tempArticle->corrected_by ?? 'none'
        ];

        Log::error('FALHA CRÍTICA - Modelo PREMIUM falhou', [
            'temp_article_id' => $tempArticle->id,
            'vehicle' => "{$tempArticle->vehicle_make} {$tempArticle->vehicle_model}",
            'error' => $result['error'],
            'model' => self::MODEL_VERSION,
            'error_category' => $errorCategory,
            'previous_corrections' => $tempArticle->corrected_by,
            'processing_time' => $processingTime,
            'cost_incurred' => self::COST_MULTIPLIER
        ]);

        $this->error("   ❌ FALHA CRÍTICA! Até mesmo o modelo PREMIUM falhou!");
        $this->line("   💬 Erro: " . substr($result['error'], 0, 150));
        $this->line("   ⏱️ Tempo: {$processingTime}s");
        $this->line("   💸 Custo perdido: " . self::COST_MULTIPLIER . " unidades");
        $this->line("   🚨 CASO CRÍTICO: Requer investigação manual");
    }

    /**
     * Lidar com exceção premium
     */
    private function handlePremiumException($tempArticle, \Exception $e, array $config, float $processingTime, int $position): void
    {
        $this->failedCorrections++;

        $errorCategory = 'critical_exception';
        $this->errorSummary[$errorCategory] = ($this->errorSummary[$errorCategory] ?? 0) + 1;

        $this->processingDetails[] = [
            'id' => $tempArticle->id,
            'success' => false,
            'processing_time' => $processingTime,
            'error_category' => $errorCategory,
            'previous_model' => $tempArticle->corrected_by ?? 'none'
        ];

        Log::critical('EXCEÇÃO CRÍTICA - Modelo PREMIUM teve exceção', [
            'temp_article_id' => $tempArticle->id,
            'vehicle' => "{$tempArticle->vehicle_make} {$tempArticle->vehicle_model}",
            'error' => $e->getMessage(),
            'model' => self::MODEL_VERSION,
            'processing_time' => $processingTime,
            'cost_incurred' => self::COST_MULTIPLIER,
            'trace' => $e->getTraceAsString()
        ]);

        $this->error("   💥 EXCEÇÃO CRÍTICA no modelo PREMIUM!");
        $this->line("   💬 Erro: " . substr($e->getMessage(), 0, 150));
        $this->line("   ⏱️ Tempo: {$processingTime}s");
        $this->line("   💸 Custo perdido: " . self::COST_MULTIPLIER . " unidades");
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
     * Exibir resultados premium
     */
    private function displayPremiumResults(): void
    {
        $this->newLine();
        $this->info('📈 RESULTADOS MODELO PREMIUM (CRÍTICOS):');
        $this->newLine();

        $successRate = $this->totalProcessed > 0 ?
            round(($this->successfulCorrections / $this->totalProcessed) * 100, 1) : 0;

        $criticalSuccessRate = $this->criticalEscalations > 0 ?
            round(($this->successfulCorrections / $this->criticalEscalations) * 100, 1) : 0;

        $avgProcessingTime = count($this->processingDetails) > 0 ?
            round(collect($this->processingDetails)->avg('processing_time'), 2) : 0;

        $this->line("📊 Total processado: {$this->totalProcessed}");
        $this->line("✅ Correções bem-sucedidas: {$this->successfulCorrections}");
        $this->line("❌ Falhas: {$this->failedCorrections}");
        $this->line("📈 Taxa de sucesso: {$successRate}%");
        $this->line("🚨 Escalações críticas: {$this->criticalEscalations}");
        if ($this->criticalEscalations > 0) {
            $this->line("⬆️ Taxa sucesso escalação crítica: {$criticalSuccessRate}%");
        }
        $this->line("💰 Custo total: {$this->totalCostIncurred} unidades (" . self::COST_MULTIPLIER . "x cada)");
        $this->line("⏱️ Tempo médio processamento: {$avgProcessingTime}s");
        $this->newLine();

        // Análise detalhada de custos
        $this->displayPremiumCostAnalysis();

        // Resumo de erros
        if (!empty($this->errorSummary)) {
            $this->error('🚨 RESUMO DE ERROS CRÍTICOS:');
            foreach ($this->errorSummary as $category => $count) {
                $emoji = $this->getErrorEmoji($category);
                $this->line("   {$emoji} {$category}: {$count}x (CRÍTICO!)");
            }
            $this->newLine();
        }

        // Detalhes de processamento
        if (!empty($this->processingDetails)) {
            $this->displayProcessingDetails();
        }

        // Recomendações críticas
        $this->displayCriticalRecommendations($successRate, $criticalSuccessRate);
    }

    /**
     * Exibir análise detalhada de custos premium
     */
    private function displayPremiumCostAnalysis(): void
    {
        $this->warn('💰 ANÁLISE CRÍTICA DE CUSTOS PREMIUM:');

        $costPerSuccess = $this->successfulCorrections > 0 ?
            round($this->totalCostIncurred / $this->successfulCorrections, 2) : 0;

        $costPerAttempt = $this->totalProcessed > 0 ?
            round($this->totalCostIncurred / $this->totalProcessed, 2) : 0;

        $this->line("   💸 Custo por sucesso: {$costPerSuccess} unidades");
        $this->line("   💳 Custo por tentativa: {$costPerAttempt} unidades");

        if ($this->criticalEscalations > 0) {
            $escalationValue = $this->successfulCorrections > 0 ?
                round(($this->successfulCorrections / $this->criticalEscalations) * 100, 1) : 0;
            $this->line("   ⬆️ ROI escalação crítica: {$escalationValue}%");

            $costSavedByPreviousModels = ($this->totalProcessed * self::COST_MULTIPLIER) - $this->totalCostIncurred;
            if ($costSavedByPreviousModels > 0) {
                $this->line("   💚 Economia por tentar modelos anteriores: {$costSavedByPreviousModels} unidades");
            }
        }

        // Comparação com modelos anteriores
        $standardCost = $this->totalProcessed * 1.0;
        $intermediateCost = $this->totalProcessed * 2.3;
        $this->line("   📊 Comparação de custos:");
        $this->line("      Standard: {$standardCost} unidades");
        $this->line("      Intermediate: {$intermediateCost} unidades");
        $this->line("      Premium: {$this->totalCostIncurred} unidades");

        $this->newLine();
    }

    /**
     * Exibir detalhes de processamento
     */
    private function displayProcessingDetails(): void
    {
        if (count($this->processingDetails) <= 5) {
            $this->info('🔍 DETALHES DE PROCESSAMENTO:');
            foreach ($this->processingDetails as $index => $detail) {
                $status = $detail['success'] ? '✅' : '❌';
                $time = $detail['processing_time'];
                $versions = $detail['versions_count'] ?? 0;
                $previous = $detail['previous_model'] ?? 'none';

                $this->line("   {$status} Artigo {$detail['id']}: {$time}s, {$versions} versões, anterior: {$previous}");
            }
            $this->newLine();
        }
    }

    /**
     * Exibir recomendações críticas
     */
    private function displayCriticalRecommendations(float $successRate, float $criticalSuccessRate): void
    {
        $this->error('🚨 RECOMENDAÇÕES CRÍTICAS - MODELO PREMIUM:');

        if ($successRate >= 95) {
            $this->line('   🎉 EXCEPCIONAL! Modelo PREMIUM resolveu casos impossíveis.');
            $this->line('   💰 Custo justificado pela resolução de casos críticos.');
            $this->line('   🏆 Continue usando PREMIUM apenas para casos extremos.');
        } elseif ($successRate >= 80) {
            $this->line('   👍 BOA performance do modelo PREMIUM.');
            $this->line('   💰 Custo alto, mas eficaz para casos críticos.');
            $this->line('   ⚖️ Balance: use PREMIUM apenas quando outros falharem.');
        } elseif ($successRate >= 60) {
            $this->line('   ⚠️ Performance MODERADA mesmo com modelo PREMIUM.');
            $this->line('   🔍 INVESTIGAÇÃO NECESSÁRIA: problemas nos dados de entrada.');
            $this->line('   💸 Custo muito alto para esta taxa de sucesso.');
        } else {
            $this->line('   🚨 ALERTA CRÍTICO: Até o modelo PREMIUM está falhando!');
            $this->line('   🛑 PARE o processamento imediatamente.');
            $this->line('   🔍 REVISE urgentemente:');
            $this->line('      • Qualidade dos dados TempArticle');
            $this->line('      • Prompts e validações');
            $this->line('      • Configuração da API Claude');
            $this->line('   💸 NÃO continue gastando com PREMIUM até resolver o problema base.');
        }

        $this->newLine();
        $this->error('📝 AÇÕES CRÍTICAS IMEDIATAS:');

        if ($this->failedCorrections > 0) {
            $this->line('   🚨 Para falhas do PREMIUM:');
            $this->line('      1. PAUSE processamento automático');
            $this->line('      2. ANALISE manualmente os casos que falharam');
            $this->line('      3. REVISE estrutura dos dados de entrada');
            $this->line('      4. TESTE com dados mais simples primeiro');
        }

        if ($this->successfulCorrections > 0) {
            $this->line('   ✅ Para sucessos do PREMIUM:');
            $this->line("      1. Verificar: TempArticle::where('corrected_by', '" . self::CORRECTED_BY . "')->count()");
            $this->line('      2. DOCUMENTE os padrões de sucesso');
            $this->line('      3. USE esses padrões para melhorar modelos anteriores');
        }

        $this->line('   💰 CONTROLE DE CUSTOS:');
        $this->line("      Total gasto hoje: {$this->totalCostIncurred} unidades");
        $this->line('      MONITORE gastos diários com PREMIUM');
        $this->line('      ESTABELEÇA limites orçamentários');

        if ($criticalSuccessRate < 70 && $this->criticalEscalations > 0) {
            $this->newLine();
            $this->error('⚠️ ALERTA: Escalações críticas com baixo ROI!');
            $this->line('   🔧 OPTIMIZE os modelos Standard e Intermediate primeiro');
            $this->line('   📊 ANALISE por que estão falhando tanto');
        }
    }

    /**
     * Teste de conectividade da API
     */
    private function testApiConnectivity(): int
    {
        $this->info('🌐 Testando conectividade Claude PREMIUM...');

        $result = $this->claudeService->testConnectivity(self::MODEL_VERSION);

        if ($result['success']) {
            $this->line("   ✅ {$result['message']}");
            $this->line("   📊 Modelo: {$result['model']}");
            $this->line("   💰 Custo: {$result['cost_level']} (" . self::COST_MULTIPLIER . "x)");
            $this->line("   📝 Descrição: {$result['description']}");
            $this->warn("   🚨 ATENÇÃO: Este é o modelo MAIS CARO!");
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
            'only_critical' => $this->option('only-critical'),
            'delay' => (int) $this->option('delay'),
            'debug' => $this->option('debug'),
            'cost_confirmation' => $this->option('cost-confirmation')
        ];
    }

    /**
     * Exibir cabeçalho
     */
    private function displayHeader(): void
    {
        $this->error('🚀 CORREÇÃO VERSÕES GENÉRICAS - MODELO PREMIUM');
        $this->error('🤖 Claude: ' . self::MODEL_VERSION);
        $this->error('💰 Custo: ' . self::COST_LEVEL . ' (' . self::COST_MULTIPLIER . 'x padrão)');
        $this->error('🎯 Foco: ÚLTIMO RECURSO - Casos críticos');
        $this->error('⚠️ AVISO: MODELO MAIS CARO!');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();
    }

    /**
     * Exibir configuração
     */
    private function displayConfiguration(array $config): void
    {
        $this->warn('⚙️ CONFIGURAÇÃO PREMIUM (CRÍTICA):');
        $this->line('   📊 Limite: ' . $config['limit'] . ' (baixo devido ao custo)');
        $this->line('   📦 Batch: ' . $config['batch_size'] . ' (processamento individual)');
        $this->line('   🎯 Prioridade: ' . $config['priority']);
        $this->line('   🔄 Modo: ' . ($config['dry_run'] ? '🧪 SIMULAÇÃO' : '💾 EXECUÇÃO REAL'));
        $this->line('   ♻️ Reprocessar: ' . ($config['force_reprocess'] ? 'SIM' : 'NÃO'));
        $this->line('   🚨 Apenas críticos: ' . ($config['only_critical'] ? 'SIM' : 'NÃO'));
        $this->line('   ⏱️ Delay: ' . $config['delay'] . 's (máximo cuidado)');
        $this->line('   🐛 Debug: ' . ($config['debug'] ? 'SIM' : 'NÃO'));
        $this->line('   💰 Confirmação custo: ' . ($config['cost_confirmation'] ? 'SIM' : 'NÃO'));
        $this->newLine();
    }

    /**
     * Lidar com nenhum artigo encontrado
     */
    private function handleNoArticlesFound(array $config): void
    {
        if ($config['only_critical']) {
            $this->info('✅ EXCELENTE! Nenhum caso crítico encontrado.');
            $this->line('💡 Isso significa que os modelos Standard e Intermediate estão funcionando bem!');
            $this->line('🎉 Não há necessidade de usar o modelo PREMIUM no momento.');
        } else {
            $this->warn('🔍 Nenhum TempArticle encontrado para correção PREMIUM');
            $this->line('💡 Critérios:');
            $this->line('   • has_generic_versions = true');
            $this->line('   • has_specific_versions != true');
        }

        $this->newLine();
        $this->line('📝 Comandos sugeridos:');
        $this->line('   • Verificar outros modelos: php artisan temp-article:correct-standard --test-api');
        $this->line('   • Processar com intermediário: php artisan temp-article:correct-intermediate');
        $this->line('   • Investigar novos casos: php artisan temp-article:investigate-generic-versions');
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
            'critical_exception' => '💥',
            'unknown' => '❓'
        ];

        return $emojis[$category] ?? '❓';
    }
}

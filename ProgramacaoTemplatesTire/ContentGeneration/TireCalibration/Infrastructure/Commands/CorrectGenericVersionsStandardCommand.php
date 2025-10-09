<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudeApiService;
use Carbon\Carbon;

/**
 * CorrectGenericVersionsStandardCommand - Modelo PADRÃO
 * 
 * FOCO: claude-3-5-sonnet-20240620 (Mais Econômico)
 * 
 * FILTROS RIGOROSOS:
 * ✅ has_generic_versions === true (obrigatório)
 * ✅ has_specific_versions !== true (não corrigido ainda)
 * 
 * SUCESSO:
 * ✅ has_specific_versions = true
 * ✅ has_generic_versions = false  
 * ✅ version_corrected_at = now()
 * ✅ corrected_by = 'claude_standard_v1'
 * 
 * USO:
 * php artisan temp-article:correct-standard --limit=10
 * php artisan temp-article:correct-standard --dry-run --debug
 * 
 * @author Engenheiro de Software Elite
 * @version 1.0 - Standard Model Command - COMPLETO
 */
class CorrectGenericVersionsStandardCommand extends Command
{
    protected $signature = 'temp-article:correct-standard
                            {--limit=5 : Número máximo de registros}
                            {--batch-size=3 : Tamanho do batch}
                            {--dry-run : Simulação sem modificar dados}
                            {--priority=high : Prioridade (high|medium|low|all)}
                            {--force-reprocess : Reprocessar já corrigidos}
                            {--delay=3 : Delay entre requests (segundos)}
                            {--debug : Debug detalhado}
                            {--test-api : Testar conectividade}';

    protected $description = 'Corrigir versões genéricas usando Claude PADRÃO (claude-3-5-sonnet-20240620)';

    private const MODEL_VERSION = 'claude-3-5-sonnet-20240620';
    private const COST_LEVEL = 'standard';
    private const CORRECTED_BY = 'claude_standard_v1';

    private ClaudeApiService $claudeService;

    // Estatísticas
    private int $totalProcessed = 0;
    private int $successfulCorrections = 0;
    private int $failedCorrections = 0;
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

            $tempArticles = $this->getTempArticlesForStandardCorrection($config);

            if ($tempArticles->isEmpty()) {
                $this->handleNoArticlesFound();
                return self::SUCCESS;
            }

            $this->info("📊 Encontrados: {$tempArticles->count()} TempArticles para correção PADRÃO");
            $this->newLine();

            $this->processArticlesInBatches($tempArticles, $config);
            $this->displayResults();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("💥 Erro durante execução: " . $e->getMessage());
            Log::error('CorrectStandardCommand failed', [
                'error' => $e->getMessage(),
                'model' => self::MODEL_VERSION
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Buscar TempArticles para correção com modelo padrão
     */
    private function getTempArticlesForStandardCorrection(array $config)
    {
        // SIMPLES: Se tem versões genéricas, corrige!
        $query = TempArticle::where('has_generic_versions', true);

        // EVITAR LOOP: Não processar se já tentou com qualquer modelo (a menos que force)
        if (!$config['force_reprocess']) {
            $query->whereNull('corrected_by');
        }

        return $query->orderBy('flagged_at', 'desc')
            ->limit($config['limit'])
            ->get();
    }

    /**
     * Processar artigos em lotes
     */
    private function processArticlesInBatches($tempArticles, array $config): void
    {
        $batches = $tempArticles->chunk($config['batch_size']);

        foreach ($batches as $batchIndex => $batch) {
            $this->line("🔄 Lote " . ($batchIndex + 1) . "/" . $batches->count() . " - Modelo PADRÃO");

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
        }

        if ($config['dry_run']) {
            $this->line("🧪 [DRY RUN] Simulando correção PADRÃO...");
            return;
        }

        try {
            $vehicleInfo = $this->extractVehicleInfo($tempArticle);
            $currentContent = $tempArticle->content ?? [];

            // Usar Claude API Service com modelo padrão
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

        // Atualizar flags de status (CRÍTICO!)
        $tempArticle->update([
            'content' => $content,
            'has_specific_versions' => true,      // ✅ Agora tem versões específicas
            'has_generic_versions' => false,     // ✅ Não tem mais versões genéricas
            'version_corrected_at' => now(),
            'corrected_by' => self::CORRECTED_BY,
            'correction_metadata' => [
                'model_used' => self::MODEL_VERSION,
                'cost_level' => self::COST_LEVEL,
                'corrected_at' => now()->toISOString(),
                'versions_count' => count($result['corrections']['especificacoes_por_versao'])
            ]
        ]);

        $this->successfulCorrections++;
        $this->totalCostIncurred += 1.0; // Custo base para modelo padrão

        if ($config['debug']) {
            $this->line("   ✅ Sucesso! Versões corrigidas: " . count($result['corrections']['especificacoes_por_versao']));
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

        // MARCAR FALHA DO STANDARD PARA ESCALAÇÃO
        $tempArticle->update([
            'corrected_by' => self::CORRECTED_BY,           // Marca que tentou
            'has_generic_versions' => true,                  // Ainda tem versões genéricas
            'has_specific_versions' => false,                // Falhou na correção
            'version_corrected_at' => now(),
            'correction_metadata' => [
                'model_used' => self::MODEL_VERSION,
                'failed' => true,
                'error' => $result['error'],
                'error_category' => $errorCategory,
                'failed_at' => now()->toISOString()
            ]
        ]);

        Log::warning('Falha na correção PADRÃO', [
            'temp_article_id' => $tempArticle->id,
            'vehicle' => "{$tempArticle->vehicle_make} {$tempArticle->vehicle_model}",
            'error' => $result['error'],
            'model' => self::MODEL_VERSION,
            'error_category' => $errorCategory
        ]);

        if ($config['debug']) {
            $this->line("   ❌ Falha: " . substr($result['error'], 0, 100));
            $this->line("   🏷️ Marcado para escalação INTERMEDIÁRIA");
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

        // MARCAR EXCEÇÃO DO STANDARD PARA ESCALAÇÃO
        $tempArticle->update([
            'corrected_by' => self::CORRECTED_BY,
            'has_generic_versions' => true,
            'has_specific_versions' => false,
            'version_corrected_at' => now(),
            'correction_metadata' => [
                'model_used' => self::MODEL_VERSION,
                'failed' => true,
                'error' => $e->getMessage(),
                'error_category' => $errorCategory,
                'failed_at' => now()->toISOString()
            ]
        ]);

        Log::error('Exceção na correção PADRÃO', [
            'temp_article_id' => $tempArticle->id,
            'vehicle' => "{$tempArticle->vehicle_make} {$tempArticle->vehicle_model}",
            'error' => $e->getMessage(),
            'model' => self::MODEL_VERSION
        ]);

        if ($config['debug']) {
            $this->line("   💥 Exceção: " . substr($e->getMessage(), 0, 100));
            $this->line("   🏷️ Marcado para escalação INTERMEDIÁRIA");
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
        $this->info('🌐 Testando conectividade Claude PADRÃO...');

        $result = $this->claudeService->testConnectivity(self::MODEL_VERSION);

        if ($result['success']) {
            $this->line("   ✅ {$result['message']}");
            $this->line("   📊 Modelo: {$result['model']}");
            $this->line("   💰 Custo: {$result['cost_level']}");
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
            'delay' => (int) $this->option('delay'),
            'debug' => $this->option('debug')
        ];
    }

    /**
     * Exibir cabeçalho
     */
    private function displayHeader(): void
    {
        $this->info('🚀 CORREÇÃO VERSÕES GENÉRICAS - MODELO PADRÃO');
        $this->info('🤖 Claude: ' . self::MODEL_VERSION);
        $this->info('💰 Custo: ' . self::COST_LEVEL . ' (mais econômico)');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();
    }

    /**
     * Exibir configuração
     */
    private function displayConfiguration(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO PADRÃO:');
        $this->line('   📊 Limite: ' . $config['limit']);
        $this->line('   📦 Batch: ' . $config['batch_size']);
        $this->line('   🎯 Prioridade: ' . $config['priority']);
        $this->line('   🔄 Modo: ' . ($config['dry_run'] ? '🧪 SIMULAÇÃO' : '💾 EXECUÇÃO'));
        $this->line('   ♻️ Reprocessar: ' . ($config['force_reprocess'] ? 'SIM' : 'NÃO'));
        $this->line('   ⏱️ Delay: ' . $config['delay'] . 's');
        $this->line('   🐛 Debug: ' . ($config['debug'] ? 'SIM' : 'NÃO'));
        $this->newLine();
    }

    /**
     * Lidar com nenhum artigo encontrado
     */
    private function handleNoArticlesFound(): void
    {
        $this->warn('🔍 Nenhum TempArticle encontrado para correção PADRÃO');
        $this->line('💡 Critérios aplicados:');
        $this->line('   • has_generic_versions = true');
        $this->line('   • corrected_by != "claude_standard_v1" (ou null)');
        $this->newLine();

        // Estatísticas úteis para debug
        $totalWithGeneric = TempArticle::where('has_generic_versions', true)->count();
        $alreadyCorrectedByStandard = TempArticle::where('corrected_by', self::CORRECTED_BY)->count();
        $pending = $totalWithGeneric - $alreadyCorrectedByStandard;

        $this->line("📊 Estatísticas:");
        $this->line("   • Total com versões genéricas: {$totalWithGeneric}");
        $this->line("   • Já corrigidos por padrão: {$alreadyCorrectedByStandard}");
        $this->line("   • Pendentes para padrão: {$pending}");
        $this->newLine();

        if ($totalWithGeneric === 0) {
            $this->line('📝 Execute primeiro: php artisan temp-article:investigate-generic-versions --flag-for-correction');
        } else {
            $this->line('🔄 Tente com --force-reprocess para reprocessar já corrigidos');
        }
    }

    /**
     * Exibir resultados finais
     */
    private function displayResults(): void
    {
        $this->newLine();
        $this->info('📈 RESULTADOS MODELO PADRÃO:');
        $this->newLine();

        $successRate = $this->totalProcessed > 0 ?
            round(($this->successfulCorrections / $this->totalProcessed) * 100, 1) : 0;

        $this->line("📊 Total processado: {$this->totalProcessed}");
        $this->line("✅ Correções bem-sucedidas: {$this->successfulCorrections}");
        $this->line("❌ Falhas: {$this->failedCorrections}");
        $this->line("📈 Taxa de sucesso: {$successRate}%");
        $this->line("💰 Custo estimado: {$this->totalCostIncurred} unidades padrão");
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

        // Recomendações
        $this->displayRecommendations($successRate);
    }

    /**
     * Exibir recomendações
     */
    private function displayRecommendations(float $successRate): void
    {
        $this->info('💡 RECOMENDAÇÕES MODELO PADRÃO:');

        if ($successRate >= 85) {
            $this->line('   🎉 Excelente! Modelo PADRÃO está funcionando muito bem.');
            $this->line('   🚀 Pode aumentar o limite para processar mais artigos.');
            $this->line('   💰 Continue usando modelo padrão para economia.');
        } elseif ($successRate >= 70) {
            $this->line('   👍 Boa performance geral do modelo PADRÃO.');
            $this->line('   🔍 Analise erros para melhorar ainda mais.');
            $this->line('   💰 Modelo padrão ainda é eficiente para maioria dos casos.');
        } elseif ($successRate >= 50) {
            $this->line('   ⚠️ Performance moderada do modelo PADRÃO.');
            $this->line('   🔧 Considere usar modelo INTERMEDIÁRIO para casos complexos.');
            $this->line('   📊 Execute: php artisan temp-article:correct-intermediate');
        } else {
            $this->line('   🚨 Performance baixa do modelo PADRÃO!');
            $this->line('   ⬆️ Escale para INTERMEDIÁRIO: php artisan temp-article:correct-intermediate');
            $this->line('   🔍 Analise logs para identificar problemas sistemáticos.');
        }

        $this->newLine();
        $this->info('📝 PRÓXIMOS PASSOS:');

        if ($this->failedCorrections > 0) {
            $this->line('   1. Para falhas: php artisan temp-article:correct-intermediate --limit=' . $this->failedCorrections);
            $this->line('   2. Verificar: TempArticle::where("has_specific_versions", true)->count()');
        } else {
            $this->line('   1. Processar mais: php artisan temp-article:correct-standard --limit=10');
            $this->line('   2. Verificar progresso: TempArticle::where("corrected_by", "' . self::CORRECTED_BY . '")->count()');
        }
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

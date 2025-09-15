<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireCalibration\Infrastructure\Traits\ClaudeEscalationTrait;
use Carbon\Carbon;

/**
 * CorrectGenericVersionsTempArticleCommand v2.0
 * 
 * NOVA VERSÃO COM ESCALAÇÃO AUTOMÁTICA DE MODELOS CLAUDE
 * 
 * Implementa estratégia inteligente que:
 * - Inicia sempre com modelo mais barato (standard)
 * - Escala automaticamente baseado no tipo de erro
 * - Mantém histórico de tentativas por registro
 * - Otimiza custo vs precisão
 * 
 * @author Engenheiro de Software Elite
 * @version 2.0 - Escalação automática de modelos Claude
 */
class CorrectGenericVersionsTempArticleCommand extends Command
{
    use ClaudeEscalationTrait;

    protected $signature = 'temp-article:correct-generic-versions
                            {--limit=1 : Número máximo de registros}
                            {--batch-size=5 : Tamanho do batch}
                            {--dry-run : Simulação sem modificar dados}
                            {--priority=high : Prioridade (high|medium|low|all)}
                            {--force-reprocess : Reprocessar já corrigidos}
                            {--delay=2 : Delay entre requests (segundos)}
                            {--debug : Debug detalhado}
                            {--force-model=standard : Forçar modelo específico (standard|intermediate|premium)}
                            {--disable-escalation : Desabilitar escalação automática}
                            {--escalation-stats : Exibir apenas estatísticas de escalação}
                            {--migrate-escalation : Migrar registros para suporte à escalação}';

    protected $description = 'Corrigir versões genéricas usando escalação automática de modelos Claude';

    private int $totalProcessed = 0;
    private int $successfulCorrections = 0;
    private int $failedCorrections = 0;
    private array $errorSummary = [];
    private array $correctionExamples = [];

    public function handle(): ?int
    {

        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return null;
        }

        // Comandos especiais
        if ($this->option('escalation-stats')) {
            return $this->handleEscalationStatsCommand();
        }

        if ($this->option('migrate-escalation')) {
            $this->migrateTempArticlesForEscalation();
            return self::SUCCESS;
        }

        $this->displayHeader();

        try {
            $config = $this->getConfiguration();
            $this->displayConfiguration($config);

            if (!$this->testClaudeApiConnectivity()) {
                return self::FAILURE;
            }

            if ($config['debug']) {
                $this->debugQuery($config);
            }

            $tempArticles = $this->getTempArticlesForCorrection($config);

            if ($tempArticles->isEmpty()) {
                $this->info('Nenhum TempArticle encontrado para correção.');
                return self::SUCCESS;
            }

            $this->info("Iniciando correção de {$tempArticles->count()} TempArticles...");
            $this->newLine();

            $batches = $tempArticles->chunk($config['batch_size']);

            foreach ($batches as $batchIndex => $batch) {
                $this->info("Batch " . ($batchIndex + 1) . "/" . $batches->count());

                foreach ($batch as $tempArticle) {
                    if ($config['disable_escalation']) {
                        $this->processTempArticleCorrection($tempArticle, $config);
                    } else {
                        $this->processTempArticleCorrectionWithEscalation($tempArticle, $config);
                    }

                    if ($config['delay'] > 0) {
                        sleep($config['delay']);
                    }
                }
                $this->newLine();
            }

            $this->displayResults($config);
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro durante correção: " . $e->getMessage());
            Log::error('CorrectGenericVersions failed', [
                'error' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function getConfiguration(): array
    {
        $baseConfig = [
            'limit' => (int) $this->option('limit'),
            'batch_size' => (int) $this->option('batch-size'),
            'dry_run' => $this->option('dry-run'),
            'priority' => $this->option('priority'),
            'force_reprocess' => $this->option('force-reprocess'),
            'delay' => (int) $this->option('delay'),
            'debug' => $this->option('debug')
        ];

        return array_merge($baseConfig, $this->getEscalationConfig());
    }

    private function testClaudeApiConnectivity(): bool
    {
        $this->info('Testando conectividade Claude API...');

        if (!isset($this->escalationStrategy)) {
            $this->initializeEscalationStrategy();
        }

        try {
            // Teste simples com o modelo padrão
            $testResult = $this->escalationStrategy->generateCorrections(
                [
                    'marca' => 'Test',
                    'modelo' => 'Test',
                    'ano' => '2024',
                    'categoria' => 'Test',
                    'display_name' => 'Test Test',
                    'tire_size' => '205/60 R16'
                ],
                [
                    'especificacoes_por_versao' => [
                        ['versao' => 'Standard', 'medida_pneus' => '205/60 R16']
                    ]
                ],
                'test_connection',
                'standard'
            );

            $this->line('   Claude API conectada e estratégia de escalação ativa');
            return true;
        } catch (\Exception $e) {
            $this->error('   Falha na conectividade: ' . $e->getMessage());
            return false;
        }
    }

    private function getTempArticlesForCorrection(array $config)
    {
        $query = TempArticle::where('needs_version_correction', true);

        if ($config['priority'] !== 'all') {
            $query->where('version_correction_priority', $config['priority']);
        }

        if (!$config['force_reprocess']) {
            $query->whereNull('version_corrected_at');
        }

        return $query->orderBy('correction_flagged_at', 'desc')
            ->limit($config['limit'])
            ->get();
    }

    private function debugQuery(array $config): void
    {
        $this->info('DEBUG DA QUERY:');

        $total = TempArticle::count();
        $this->line("   Total TempArticles: {$total}");

        $needsCorrection = TempArticle::where('needs_version_correction', true)->count();
        $this->line("   Needs correction: {$needsCorrection}");

        if ($config['priority'] !== 'all') {
            $withPriority = TempArticle::where('needs_version_correction', true)
                ->where('version_correction_priority', $config['priority'])
                ->count();
            $this->line("   Priority {$config['priority']}: {$withPriority}");
        }

        if (!$config['force_reprocess']) {
            $notCorrected = TempArticle::where('needs_version_correction', true)
                ->whereNull('version_corrected_at')
                ->count();
            $this->line("   Não corrigidos: {$notCorrected}");
        }

        // Estatísticas de escalação
        $withEscalationHistory = TempArticle::whereNotNull('escalation_history')->count();
        $this->line("   Com histórico de escalação: {$withEscalationHistory}");

        $this->newLine();
    }

    /**
     * Método original mantido para compatibilidade quando escalação está desabilitada
     */
    private function processTempArticleCorrection(TempArticle $tempArticle, array $config): void
    {
        $this->totalProcessed++;

        try {
            $vehicleInfo = $this->extractVehicleInfo($tempArticle);
            $this->line("Processando (modo legado): {$vehicleInfo['display_name']} (ID: {$tempArticle->id})");

            $content = $tempArticle->content ?? [];
            $corrections = $this->generateCorrectionsLegacy($vehicleInfo, $content);

            if (empty($corrections)) {
                throw new \Exception('Claude API retornou correções vazias');
            }

            $this->validateCorrections($corrections, $vehicleInfo);

            if (!$config['dry_run']) {
                $this->applyCorrections($tempArticle, $corrections);
                $this->markAsCorrected($tempArticle);
            }

            $this->successfulCorrections++;
            $this->line("   Corrigido com sucesso (modo legado)");
        } catch (\Exception $e) {
            $this->failedCorrections++;
            $this->line("   Falha: " . $e->getMessage());

            $errorCategory = $this->categorizeError($e->getMessage());
            if (!isset($this->errorSummary[$errorCategory])) {
                $this->errorSummary[$errorCategory] = 0;
            }
            $this->errorSummary[$errorCategory]++;

            Log::error('Falha na correção (modo legado)', [
                'temp_article_id' => $tempArticle->id,
                'vehicle' => $vehicleInfo['display_name'] ?? 'Unknown',
                'error' => $e->getMessage(),
                'error_category' => $errorCategory
            ]);
        }
    }

    /**
     * Método legado simplificado para quando escalação está desabilitada
     */
    private function generateCorrectionsLegacy(array $vehicleInfo, array $content): array
    {
        // Implementação básica usando apenas modelo padrão
        if (!isset($this->escalationStrategy)) {
            $this->initializeEscalationStrategy();
        }

        $result = $this->escalationStrategy->generateCorrections(
            $vehicleInfo,
            $content,
            'legacy_mode',
            'standard'
        );

        return $result['corrections'];
    }

    private function extractVehicleInfo(TempArticle $tempArticle): array
    {
        $entities = $tempArticle->extracted_entities ?? [];
        $vehicleInfo = $tempArticle->vehicle_info ?? [];

        return [
            'marca' => $entities['marca'] ?? $vehicleInfo['make'] ?? 'Unknown',
            'modelo' => $entities['modelo'] ?? $vehicleInfo['model'] ?? 'Unknown',
            'ano' => $entities['ano'] ?? date('Y'),
            'categoria' => $entities['categoria'] ?? $vehicleInfo['main_category'] ?? 'Unknown',
            'display_name' => ($entities['marca'] ?? 'Unknown') . ' ' . ($entities['modelo'] ?? 'Unknown'),
            'tire_size' => $vehicleInfo['tire_size'] ?? $entities['pneus'] ?? 'Unknown'
        ];
    }

    private function validateCorrections(array $corrections, array $vehicleInfo): void
    {
        if (!isset($corrections['especificacoes_por_versao']) || !isset($corrections['tabela_carga_completa'])) {
            throw new \Exception('Estrutura de correções incompleta');
        }

        $specs = $corrections['especificacoes_por_versao'];
        if (!is_array($specs) || count($specs) < 2) {
            throw new \Exception('Especificações insuficientes');
        }

        foreach ($specs as $spec) {
            $versao = $spec['versao'] ?? '';
            if ($this->isGenericVersion($versao)) {
                throw new \Exception("Versão genérica presente: {$versao}");
            }
        }

        foreach ($specs as $spec) {
            $required = ['versao', 'medida_pneus', 'pressao_dianteiro_normal', 'pressao_traseiro_normal'];
            foreach ($required as $field) {
                if (!isset($spec[$field]) || empty($spec[$field])) {
                    throw new \Exception("Campo obrigatório ausente: {$field}");
                }
            }
        }
    }

    private function isGenericVersion(string $versionName): bool
    {
        $patterns = ['comfort', 'style', 'premium', 'base', 'entry', 'standard'];

        foreach ($patterns as $pattern) {
            if (stripos($versionName, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function applyCorrections(TempArticle $tempArticle, array $corrections): void
    {
        $content = $tempArticle->content ?? [];

        if (isset($corrections['especificacoes_por_versao'])) {
            $content['especificacoes_por_versao'] = $corrections['especificacoes_por_versao'];
        }

        if (isset($corrections['tabela_carga_completa'])) {
            $content['tabela_carga_completa'] = array_merge(
                $content['tabela_carga_completa'] ?? [],
                $corrections['tabela_carga_completa']
            );
        }

        $tempArticle->update(['content' => $content]);
    }

    private function markAsCorrected(TempArticle $tempArticle): void
    {
        $tempArticle->update([
            'needs_version_correction' => false,
            'version_corrected_at' => now(),
            'corrected_by' => 'claude_api_v2.0_escalation'
        ]);
    }

    private function categorizeError(string $errorMessage): string
    {
        if (strpos($errorMessage, 'timeout') !== false) {
            return 'api_timeout';
        } elseif (strpos($errorMessage, 'rate') !== false) {
            return 'api_rate_limit';
        } elseif (strpos($errorMessage, 'JSON') !== false) {
            return 'json_parse_error';
        } elseif (strpos($errorMessage, 'validation') !== false) {
            return 'validation_error';
        } elseif (strpos($errorMessage, 'genérica') !== false) {
            return 'generic_version_persist';
        } else {
            return 'other_errors';
        }
    }

    private function displayHeader(): void
    {
        $this->info('CORREÇÃO DE VERSÕES GENÉRICAS v2.0');
        $this->info('Claude API + Escalação Automática de Modelos');
        $this->info(now()->format('d/m/Y H:i:s'));
        $this->newLine();
    }

    private function displayConfiguration(array $config): void
    {
        $this->line('CONFIGURAÇÃO:');
        $this->line('   Limite: ' . $config['limit']);
        $this->line('   Batch: ' . $config['batch_size']);
        $this->line('   Prioridade: ' . $config['priority']);
        $this->line('   Modo: ' . ($config['dry_run'] ? 'SIMULAÇÃO' : 'EXECUÇÃO'));
        $this->line('   Reprocessar: ' . ($config['force_reprocess'] ? 'SIM' : 'NÃO'));
        $this->line('   Delay: ' . $config['delay'] . 's');

        // Configurações de escalação
        $this->newLine();
        $this->info('CONFIGURAÇÃO DE ESCALAÇÃO:');
        $this->line('   Escalação ativa: ' . ($config['disable_escalation'] ? 'NÃO' : 'SIM'));
        if ($config['force_model']) {
            $this->line('   Modelo forçado: ' . $config['force_model']);
        } else {
            $this->line('   Estratégia: Iniciar com standard → escalar conforme necessário');
        }
        $this->newLine();
    }

    private function displayResults(array $config): void
    {
        $this->newLine();
        $this->info('RESULTADOS:');
        $this->line("   Total processado: {$this->totalProcessed}");
        $this->line("   Sucessos: {$this->successfulCorrections}");
        $this->line("   Falhas: {$this->failedCorrections}");

        $successRate = $this->totalProcessed > 0 ?
            round(($this->successfulCorrections / $this->totalProcessed) * 100, 1) : 0;

        $this->line("   Taxa de sucesso: {$successRate}%");
        $this->newLine();

        // Exibir estatísticas de escalação se ativa
        if (!$config['disable_escalation']) {
            $this->displayEscalationStats();
        }

        if (!empty($this->errorSummary)) {
            $this->displayErrorSummary();
        }

        if (!empty($this->correctionExamples)) {
            $this->displayCorrectionExamples();
        }

        $this->displayRecommendations($successRate, $config);
    }

    private function displayErrorSummary(): void
    {
        $this->info('RESUMO DE ERROS:');

        foreach ($this->errorSummary as $category => $count) {
            $this->line("   {$category}: {$count}");
        }
        $this->newLine();
    }

    private function displayCorrectionExamples(): void
    {
        $this->info('EXEMPLOS DE CORREÇÕES:');

        foreach ($this->correctionExamples as $example) {
            $this->line("   {$example['vehicle']} (ID: {$example['temp_article_id']})");
            $this->line("      Correções: " . implode(', ', $example['corrections_applied']));
        }
        $this->newLine();
    }

    private function displayRecommendations(float $successRate, array $config): void
    {
        $this->line('RECOMENDAÇÕES:');

        if ($successRate >= 95) {
            $this->line('   ✅ Excelente! Sistema funcionando perfeitamente.');
        } elseif ($successRate >= 80) {
            $this->line('   ⚠️  Boa taxa. Verificar erros para otimizações.');
        } elseif ($successRate >= 60) {
            $this->line('   📊 Taxa moderada. Considerar ajustes na escalação.');
        } else {
            $this->line('   ❌ Taxa baixa! Revisar prompts e estratégia de escalação.');
        }

        // Recomendações específicas baseadas na configuração
        if ($config['disable_escalation'] && $successRate < 80) {
            $this->line('   💡 Considere ativar escalação automática para melhorar taxa de sucesso');
        }

        if ($config['force_model'] && $successRate < 90) {
            $this->line('   🔧 Modelo forçado pode estar limitando eficiência - considere escalação automática');
        }

        if (!$config['disable_escalation'] && $this->escalationStats['total_escalations'] === 0) {
            $this->line('   📈 Nenhuma escalação necessária - prompts funcionando bem com modelo padrão');
        }

        $this->newLine();
        $this->line('PRÓXIMOS PASSOS:');
        $this->line('   1. Monitorar estatísticas de escalação semanalmente');
        $this->line('   2. Usar --escalation-stats para análise detalhada');

        if ($this->failedCorrections > 0) {
            $this->line('   3. Investigar registros com falha para padrões');
            $this->line('   4. Considerar ajustes nos prompts baseado nos erros');
        }

        if (!$config['disable_escalation']) {
            $this->line('   5. Otimizar custo monitorando uso de modelos premium');
        }
    }
}

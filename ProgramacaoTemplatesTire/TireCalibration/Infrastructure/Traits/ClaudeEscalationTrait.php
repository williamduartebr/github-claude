<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Traits;

use Src\ContentGeneration\TireCalibration\Infrastructure\Services\Claude\ClaudeModelEscalationStrategy;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Illuminate\Support\Facades\Log;

/**
 * Trait para integração da estratégia de escalação de modelos Claude
 * 
 * Substitui chamadas diretas à API Claude por sistema inteligente
 * que escala automaticamente modelos baseado em tipos de falha
 * 
 * @author Engenheiro de Software Elite
 * @version 1.0 - Integração seamless com commands existentes
 */
trait ClaudeEscalationTrait
{
    private ClaudeModelEscalationStrategy $escalationStrategy;
    private array $escalationStats = [
        'total_escalations' => 0,
        'successful_escalations' => 0,
        'cost_savings' => 0,
        'model_usage' => []
    ];

    /**
     * Inicializa estratégia de escalação
     */
    private function initializeEscalationStrategy(): void
    {
        $this->escalationStrategy = new ClaudeModelEscalationStrategy();
    }

    /**
     * Substituto inteligente para generateCorrectionsViaClaudeApi
     * 
     * Mantém mesma assinatura mas implementa escalação automática
     */
    private function generateCorrectionsViaClaudeApiWithEscalation(
        array $vehicleInfo, 
        array $content, 
        array $config,
        string $tempArticleId
    ): array {
        if (!isset($this->escalationStrategy)) {
            $this->initializeEscalationStrategy();
        }

        try {
            $startModel = $this->determineStartModel($config, $tempArticleId);
            
            $result = $this->escalationStrategy->generateCorrections(
                $vehicleInfo,
                $content,
                $tempArticleId,
                $startModel
            );

            $this->trackEscalationSuccess($result, $tempArticleId);
            
            return $result['corrections'];
            
        } catch (\Exception $e) {
            $this->trackEscalationFailure($e, $tempArticleId);
            throw $e;
        }
    }

    /**
     * Determina modelo inicial baseado na configuração e histórico
     */
    private function determineStartModel(array $config, string $tempArticleId): string
    {
        // Se forçar modelo específico via config
        if (isset($config['force_model'])) {
            return $config['force_model'];
        }

        // Verificar histórico de falhas para este TempArticle
        $previousAttempts = $this->getPreviousEscalationAttempts($tempArticleId);
        
        if ($previousAttempts > 0) {
            // Se já falhou antes, começar com modelo intermediário
            return 'intermediate';
        }

        // Lógica baseada na prioridade do registro
        $tempArticle = TempArticle::find($tempArticleId);
        if ($tempArticle && isset($tempArticle->version_correction_priority)) {
            return match($tempArticle->version_correction_priority) {
                'high' => 'standard',      // Alta prioridade: tentar econômico primeiro
                'medium' => 'standard',    // Média: econômico
                'low' => 'standard',       // Baixa: econômico
                default => 'standard'
            };
        }

        return 'standard'; // Padrão: sempre começar com modelo mais barato
    }

    /**
     * Obtém tentativas anteriores de escalação para o registro
     */
    private function getPreviousEscalationAttempts(string $tempArticleId): int
    {
        $tempArticle = TempArticle::find($tempArticleId);
        
        if (!$tempArticle || !isset($tempArticle->escalation_history)) {
            return 0;
        }

        return count($tempArticle->escalation_history ?? []);
    }

    /**
     * Registra sucesso da escalação com métricas
     */
    private function trackEscalationSuccess(array $result, string $tempArticleId): void
    {
        $modelUsed = $result['model_used'];
        $escalated = $result['escalated'];
        $attempts = $result['attempts'];

        // Atualizar estatísticas locais
        if ($escalated) {
            $this->escalationStats['total_escalations']++;
            $this->escalationStats['successful_escalations']++;
        }

        if (!isset($this->escalationStats['model_usage'][$modelUsed])) {
            $this->escalationStats['model_usage'][$modelUsed] = 0;
        }
        $this->escalationStats['model_usage'][$modelUsed]++;

        // Calcular economia de custo (assumindo que standard = 1x, intermediate = 3x, premium = 10x)
        $costMultipliers = ['standard' => 1, 'intermediate' => 3, 'premium' => 10];
        $actualCost = $costMultipliers[$modelUsed] ?? 1;
        $maxCost = $costMultipliers['premium'];
        $this->escalationStats['cost_savings'] += ($maxCost - $actualCost);

        // Persistir histórico no TempArticle
        $this->updateEscalationHistory($tempArticleId, [
            'timestamp' => now(),
            'model_used' => $modelUsed,
            'escalated' => $escalated,
            'attempts' => $attempts,
            'result' => 'success'
        ]);

        Log::info('Escalação bem-sucedida', [
            'temp_article_id' => $tempArticleId,
            'model_used' => $modelUsed,
            'escalated' => $escalated,
            'attempts' => $attempts,
            'cost_efficiency' => $actualCost <= 3 // Considera eficiente se não usou premium
        ]);
    }

    /**
     * Registra falha da escalação
     */
    private function trackEscalationFailure(\Exception $e, string $tempArticleId): void
    {
        $this->escalationStats['total_escalations']++;

        $this->updateEscalationHistory($tempArticleId, [
            'timestamp' => now(),
            'result' => 'failure',
            'error' => $e->getMessage(),
            'error_category' => $this->categorizeError($e->getMessage())
        ]);

        Log::error('Falha na escalação', [
            'temp_article_id' => $tempArticleId,
            'error' => $e->getMessage(),
            'escalation_stats' => $this->escalationStats
        ]);
    }

    /**
     * Atualiza histórico de escalação no banco
     */
    private function updateEscalationHistory(string $tempArticleId, array $escalationData): void
    {
        try {
            $tempArticle = TempArticle::find($tempArticleId);
            if (!$tempArticle) {
                return;
            }

            $history = $tempArticle->escalation_history ?? [];
            $history[] = $escalationData;

            // Manter apenas últimos 10 registros para evitar documento muito grande
            if (count($history) > 10) {
                $history = array_slice($history, -10);
            }

            $tempArticle->update([
                'escalation_history' => $history,
                'last_escalation_at' => now()
            ]);

        } catch (\Exception $e) {
            Log::warning('Falha ao atualizar histórico de escalação', [
                'temp_article_id' => $tempArticleId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Substitui método original no command para usar escalação
     */
    private function processTempArticleCorrectionWithEscalation(TempArticle $tempArticle, array $config): void
    {
        $this->totalProcessed++;

        try {
            $vehicleInfo = $this->extractVehicleInfo($tempArticle);
            $this->line("Processando: {$vehicleInfo['display_name']} (ID: {$tempArticle->id})");

            if ($config['debug']) {
                $issuesCount = count($tempArticle->version_issues_detected ?? []);
                $this->line("   Issues: {$issuesCount}");
                
                // Mostrar histórico de escalação se existir
                if (isset($tempArticle->escalation_history) && !empty($tempArticle->escalation_history)) {
                    $lastAttempt = end($tempArticle->escalation_history);
                    $this->line("   Último modelo usado: " . ($lastAttempt['model_used'] ?? 'N/A'));
                }
            }

            $content = $tempArticle->content ?? [];
            
            // Usar método com escalação em vez do original
            $corrections = $this->generateCorrectionsViaClaudeApiWithEscalation(
                $vehicleInfo, 
                $content, 
                $config,
                $tempArticle->id
            );

            if (empty($corrections)) {
                throw new \Exception('Claude API retornou correções vazias');
            }

            $this->validateCorrections($corrections, $vehicleInfo);

            if (!$config['dry_run']) {
                $this->applyCorrections($tempArticle, $corrections);
                $this->markAsCorrected($tempArticle);
            }

            $this->successfulCorrections++;
            $this->line("   Corrigido com sucesso");

            if (count($this->correctionExamples) < 3) {
                $this->correctionExamples[] = [
                    'vehicle' => $vehicleInfo['display_name'],
                    'temp_article_id' => $tempArticle->id,
                    'corrections_applied' => array_keys($corrections)
                ];
            }

        } catch (\Exception $e) {
            $this->failedCorrections++;
            $this->line("   Falha: " . $e->getMessage());

            $errorCategory = $this->categorizeError($e->getMessage());
            if (!isset($this->errorSummary[$errorCategory])) {
                $this->errorSummary[$errorCategory] = 0;
            }
            $this->errorSummary[$errorCategory]++;

            Log::error('Falha na correção de versão genérica', [
                'temp_article_id' => $tempArticle->id,
                'vehicle' => $vehicleInfo['display_name'] ?? 'Unknown',
                'error' => $e->getMessage(),
                'error_category' => $errorCategory
            ]);
        }
    }

    /**
     * Exibe estatísticas da escalação nos resultados
     */
    private function displayEscalationStats(): void
    {
        if ($this->escalationStats['total_escalations'] === 0) {
            return;
        }

        $this->info('ESTATÍSTICAS DE ESCALAÇÃO:');
        
        $successRate = $this->escalationStats['total_escalations'] > 0 ?
            round(($this->escalationStats['successful_escalations'] / $this->escalationStats['total_escalations']) * 100, 1) : 0;

        $this->line("   Total escalações: {$this->escalationStats['total_escalations']}");
        $this->line("   Escalações bem-sucedidas: {$this->escalationStats['successful_escalations']}");
        $this->line("   Taxa de sucesso escalação: {$successRate}%");
        $this->line("   Economia estimada: {$this->escalationStats['cost_savings']}x");

        if (!empty($this->escalationStats['model_usage'])) {
            $this->line("   Uso por modelo:");
            foreach ($this->escalationStats['model_usage'] as $model => $count) {
                $percentage = round(($count / $this->totalProcessed) * 100, 1);
                $this->line("      {$model}: {$count} ({$percentage}%)");
            }
        }

        $this->newLine();
    }

    /**
     * Adiciona opções específicas de escalação ao command
     */
    protected function addEscalationOptions(): array
    {
        return [
            '--force-model=standard : Forçar modelo específico (standard|intermediate|premium)',
            '--disable-escalation : Desabilitar escalação automática',
            '--escalation-stats : Exibir apenas estatísticas de escalação'
        ];
    }

    /**
     * Processa configuração de escalação das opções do command
     */
    private function getEscalationConfig(): array
    {
        return [
            'force_model' => $this->option('force-model'),
            'disable_escalation' => $this->option('disable-escalation'),
            'escalation_stats_only' => $this->option('escalation-stats')
        ];
    }

    /**
     * Comando específico para estatísticas de escalação
     */
    private function handleEscalationStatsCommand(): int
    {
        $this->info('RELATÓRIO DE ESCALAÇÃO CLAUDE - ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        // Estatísticas dos últimos 30 dias
        $recentStats = $this->getRecentEscalationStats();
        $this->displayRecentEscalationStats($recentStats);

        // Recomendações baseadas no histórico
        $this->displayEscalationRecommendations($recentStats);

        return self::SUCCESS;
    }

    /**
     * Obtém estatísticas recentes de escalação
     */
    private function getRecentEscalationStats(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        $tempArticles = TempArticle::where('last_escalation_at', '>=', $thirtyDaysAgo)
            ->whereNotNull('escalation_history')
            ->get();

        $stats = [
            'total_articles_processed' => $tempArticles->count(),
            'total_escalations' => 0,
            'successful_escalations' => 0,
            'model_usage' => ['standard' => 0, 'intermediate' => 0, 'premium' => 0],
            'error_categories' => [],
            'average_attempts' => 0,
            'cost_efficiency' => 0
        ];

        $totalAttempts = 0;
        $totalCostUnits = 0;

        foreach ($tempArticles as $article) {
            $history = $article->escalation_history ?? [];
            
            foreach ($history as $escalation) {
                if (!isset($escalation['timestamp']) || 
                    \Carbon\Carbon::parse($escalation['timestamp'])->lt($thirtyDaysAgo)) {
                    continue;
                }

                $stats['total_escalations']++;
                
                if (($escalation['result'] ?? '') === 'success') {
                    $stats['successful_escalations']++;
                    
                    $modelUsed = $escalation['model_used'] ?? 'standard';
                    $stats['model_usage'][$modelUsed]++;
                    
                    $attempts = $escalation['attempts'] ?? 1;
                    $totalAttempts += $attempts;
                    
                    // Calcular custo (standard = 1, intermediate = 3, premium = 10)
                    $costMultipliers = ['standard' => 1, 'intermediate' => 3, 'premium' => 10];
                    $totalCostUnits += $costMultipliers[$modelUsed] ?? 1;
                } else {
                    $errorCategory = $escalation['error_category'] ?? 'unknown';
                    if (!isset($stats['error_categories'][$errorCategory])) {
                        $stats['error_categories'][$errorCategory] = 0;
                    }
                    $stats['error_categories'][$errorCategory]++;
                }
            }
        }

        if ($stats['successful_escalations'] > 0) {
            $stats['average_attempts'] = round($totalAttempts / $stats['successful_escalations'], 2);
            
            // Custo médio vs custo máximo (se tudo fosse premium)
            $maxPossibleCost = $stats['successful_escalations'] * 10; // Todos premium
            $stats['cost_efficiency'] = round((1 - ($totalCostUnits / $maxPossibleCost)) * 100, 1);
        }

        return $stats;
    }

    /**
     * Exibe estatísticas recentes detalhadas
     */
    private function displayRecentEscalationStats(array $stats): void
    {
        $this->info('RESUMO DOS ÚLTIMOS 30 DIAS:');
        $this->line("   Artigos processados: {$stats['total_articles_processed']}");
        $this->line("   Total de escalações: {$stats['total_escalations']}");
        $this->line("   Escalações bem-sucedidas: {$stats['successful_escalations']}");
        
        $successRate = $stats['total_escalations'] > 0 ?
            round(($stats['successful_escalations'] / $stats['total_escalations']) * 100, 1) : 0;
        $this->line("   Taxa de sucesso: {$successRate}%");
        $this->line("   Tentativas médias: {$stats['average_attempts']}");
        $this->line("   Eficiência de custo: {$stats['cost_efficiency']}%");
        $this->newLine();

        // Distribuição de uso por modelo
        $this->info('USO POR MODELO:');
        $totalUsage = array_sum($stats['model_usage']);
        if ($totalUsage > 0) {
            foreach ($stats['model_usage'] as $model => $count) {
                $percentage = round(($count / $totalUsage) * 100, 1);
                $this->line("   {$model}: {$count} ({$percentage}%)");
            }
        } else {
            $this->line("   Nenhum uso registrado");
        }
        $this->newLine();

        // Categorias de erro
        if (!empty($stats['error_categories'])) {
            $this->info('CATEGORIAS DE ERRO:');
            arsort($stats['error_categories']);
            foreach ($stats['error_categories'] as $category => $count) {
                $this->line("   {$category}: {$count}");
            }
            $this->newLine();
        }
    }

    /**
     * Exibe recomendações baseadas no histórico
     */
    private function displayEscalationRecommendations(array $stats): void
    {
        $this->info('RECOMENDAÇÕES:');

        $successRate = $stats['total_escalations'] > 0 ?
            ($stats['successful_escalations'] / $stats['total_escalations']) * 100 : 0;

        if ($successRate >= 95) {
            $this->line('   ✅ Sistema de escalação funcionando perfeitamente');
        } elseif ($successRate >= 80) {
            $this->line('   ⚠️  Sistema bom, mas há margem para melhoria');
        } else {
            $this->line('   ❌ Sistema precisa de ajustes urgentes');
        }

        // Recomendações baseadas no uso de modelos
        $totalUsage = array_sum($stats['model_usage']);
        if ($totalUsage > 0) {
            $standardPercentage = ($stats['model_usage']['standard'] / $totalUsage) * 100;
            $premiumPercentage = ($stats['model_usage']['premium'] / $totalUsage) * 100;

            if ($standardPercentage < 60) {
                $this->line('   💡 Considere melhorar prompts para reduzir escalação');
            }

            if ($premiumPercentage > 20) {
                $this->line('   💰 Alto uso do modelo premium - revisar estratégia');
            }

            if ($stats['cost_efficiency'] < 70) {
                $this->line('   📊 Eficiência de custo baixa - otimizar escalação');
            }
        }

        // Recomendações baseadas em erros
        if (!empty($stats['error_categories'])) {
            $topError = array_key_first($stats['error_categories']);
            $topErrorCount = $stats['error_categories'][$topError];

            if ($topErrorCount > ($stats['total_escalations'] * 0.3)) {
                $this->line("   🔧 Foco em resolver: {$topError} ({$topErrorCount} ocorrências)");
            }
        }

        $this->newLine();
        $this->line('PRÓXIMOS PASSOS:');
        $this->line('   1. Monitorar tendências semanalmente');
        $this->line('   2. Ajustar prompts baseado nos erros mais comuns');
        $this->line('   3. Considerar cache para padrões de sucesso');
    }

    /**
     * Método para migração: adiciona campos de escalação aos TempArticles existentes
     */
    private function migrateTempArticlesForEscalation(): void
    {
        $this->info('Migrando TempArticles para suporte à escalação...');

        $count = 0;
        TempArticle::whereNull('escalation_history')
            ->chunk(100, function ($tempArticles) use (&$count) {
                foreach ($tempArticles as $tempArticle) {
                    $tempArticle->update([
                        'escalation_history' => [],
                        'last_escalation_at' => null
                    ]);
                    $count++;
                }
            });

        $this->line("   {$count} registros migrados com sucesso");
    }
}
<?php

namespace Src\VehicleData\Infrastructure\Console\Commands\Schedules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\VehicleData\Domain\Entities\VehicleEnrichmentGroup;

/**
 * Schedule automático para enriquecimento de dados de veículos via Claude API
 * 
 * EXECUÇÃO: A cada 5 minutos
 * PROCESSO: Enriquecimento otimizado via Claude Sonnet
 */
class VehicleDataEnrichmentSchedule extends Command
{
    protected $signature = 'schedule:vehicle-data-enrichment 
                           {--limit=1 : Limite de grupos por execução}
                           {--priority= : Prioridade específica (high/medium/low)}
                           {--dry-run : Modo simulação}
                           {--force : Forçar reprocessamento}';

    protected $description = 'Schedule automático para enriquecimento de dados via Claude API';

    public function handle(): ?int
    {
        $limit = (int) $this->option('limit');
        $priority = $this->option('priority');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        Log::info('VehicleDataEnrichmentSchedule: Iniciando execução', [
            'limit' => $limit,
            'priority' => $priority,
            'dry_run' => $dryRun,
            'force' => $force,
            'environment' => app()->environment()
        ]);

        $this->info('🚀 SCHEDULE DE ENRIQUECIMENTO DE DADOS');
        $this->info('   Horário: ' . now()->format('d/m/Y H:i:s'));
        $this->info('   Ambiente: ' . app()->environment());
        $this->newLine();

        try {
            // ✅ CORREÇÃO: Verificação mais flexível de ambiente
            if (app()->environment(['local', 'testing']) && !app()->runningInConsole()) {
                $this->warn('⚠️  Ambiente local sem console - executando em dry-run');
                $dryRun = true;
            }

            // Verificar pré-requisitos
            if (!$this->checkPrerequisites()) {
                return Command::SUCCESS; // ✅ Retornar SUCCESS para não marcar como falha
            }

            // Executar enriquecimento
            $results = $this->runEnrichment($limit, $priority, $dryRun, $force);

            // Exibir resumo
            $this->showSummary($results);

            // Cleanup opcional
            $this->performCleanup();

            return Command::SUCCESS; // ✅ Sempre retornar SUCCESS para schedules
        } catch (\Exception $e) {
            $this->error('❌ ERRO: ' . $e->getMessage());

            Log::error('VehicleDataEnrichmentSchedule: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // ✅ CORREÇÃO: Retornar SUCCESS mesmo com erro para não quebrar o schedule
            return Command::SUCCESS;
        }
    }

    /**
     * Verificar pré-requisitos do sistema
     */
    protected function checkPrerequisites(): bool
    {
        // Verificar se existem grupos para processar
        $pendingGroups = VehicleEnrichmentGroup::pendingEnrichment()->count();

        if ($pendingGroups === 0) {
            $this->info('✅ Nenhum grupo pendente para enriquecimento');
            Log::info('VehicleDataEnrichmentSchedule: Nenhum grupo pendente', [
                'total_groups' => VehicleEnrichmentGroup::count()
            ]);
            return false; // Não é erro, apenas não há trabalho
        }

        // Verificar configuração da API Claude
        $apiKey = config('services.anthropic.api_key');
        if (empty($apiKey)) {
            $this->warn('⚠️  API Key do Claude não configurada - executando em modo simulação');
            Log::warning('VehicleDataEnrichmentSchedule: API Key não configurada');
            return true; // Continuar mesmo assim (dry-run automático)
        }

        $this->info("✅ Pré-requisitos OK: {$pendingGroups} grupos pendentes");
        return true;
    }

    /**
     * Executar enriquecimento
     */
    protected function runEnrichment(int $limit, ?string $priority, bool $dryRun, bool $force): array
    {
        $this->info('🔄 EXECUTANDO ENRIQUECIMENTO VIA CLAUDE API');
        $this->newLine();

        // Verificar grupos pendentes específicos
        $query = VehicleEnrichmentGroup::pendingEnrichment();

        if ($priority) {
            $query->byPriority($priority);
        }

        $pendingCount = $query->count();

        if ($pendingCount === 0) {
            return [
                'processed' => 0,
                'success' => 0,
                'errors' => 0,
                'skipped' => 0,
                'reason' => 'no_groups_to_process'
            ];
        }

        $this->info("📊 Grupos para processar: {$pendingCount} (limite: {$limit})");

        // Preparar parâmetros para o comando
        $params = [
            '--limit' => $limit
        ];

        if ($priority) {
            $params['--priority'] = $priority;
        }

        if ($dryRun || empty(config('services.anthropic.api_key'))) {
            $params['--dry-run'] = true;
            $this->warn('🔍 Executando em modo DRY-RUN');
        }

        if ($force) {
            $params['--force'] = true;
        }

        // ✅ EXECUTAR O COMANDO ORIGINAL
        $exitCode = $this->call('vehicle-data:enrich-representatives', $params);

        // Obter estatísticas atualizadas
        $stats = VehicleEnrichmentGroup::getProcessingStats();

        return [
            'exit_code' => $exitCode,
            'processed' => min($limit, $pendingCount),
            'success' => $exitCode === Command::SUCCESS,
            'current_stats' => $stats,
            'dry_run' => $dryRun || empty(config('services.anthropic.api_key'))
        ];
    }

    /**
     * Exibir resumo da execução
     */
    protected function showSummary(array $results): void
    {
        $this->newLine();
        $this->info('=== RESUMO DA EXECUÇÃO ===');

        if (isset($results['reason'])) {
            $this->line("⏭️  {$results['reason']}");
            return;
        }

        // Estatísticas atuais
        $stats = $results['current_stats'] ?? VehicleEnrichmentGroup::getProcessingStats();

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Status da execução', $results['success'] ? '✅ Sucesso' : '❌ Falha'],
                ['Exit code', $results['exit_code'] ?? 'N/A'],
                ['Modo', $results['dry_run'] ? '🔍 DRY-RUN' : '🚀 PRODUÇÃO'],
                ['Total de grupos', $stats['total_groups']],
                ['Pendentes enrichment', $stats['pending_enrichment']],
                ['Já enriquecidos', $stats['enriched']],
                ['Completados', $stats['completed']],
                ['Taxa de conclusão', $stats['completion_rate'] . '%'],
            ]
        );

        // Log estruturado para monitoramento
        Log::info('VehicleDataEnrichmentSchedule: Execução concluída', [
            'results' => $results,
            'stats' => $stats,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Limpeza de grupos antigos
     */
    protected function performCleanup(): void
    {
        try {
            // Limpar grupos completados há mais de 15 dias
            $deleted = VehicleEnrichmentGroup::cleanupOldGroups(15);

            if ($deleted > 0) {
                $this->info("🧹 Limpeza: {$deleted} grupos antigos removidos");
                Log::info('VehicleDataEnrichmentSchedule: Limpeza realizada', [
                    'deleted' => $deleted
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('VehicleDataEnrichmentSchedule: Erro na limpeza', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ REGISTRAR NO SCHEDULE SEGUINDO O PADRÃO QUE FUNCIONA
     */
    public static function register($schedule): void
    {
        $schedule->command('schedule:vehicle-data-enrichment --limit=1')
            ->everyFiveMinutes()
            // ->withoutOverlapping(10) // Timeout de 10 minutos
            // ->runInBackground()
            ->appendOutputTo(storage_path('logs/vehicle-data-enrichment-schedule.log'))
            ->onFailure(function () {
                Log::error('VehicleDataEnrichmentSchedule: Falha na execução agendada');
            })
            ->onSuccess(function () {
                Log::info('VehicleDataEnrichmentSchedule: Execução agendada concluída');
            });
    }
}

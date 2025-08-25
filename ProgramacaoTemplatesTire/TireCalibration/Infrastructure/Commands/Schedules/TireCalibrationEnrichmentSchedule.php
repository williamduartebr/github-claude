<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands\Schedules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

/**
 * Schedule automático para enriquecimento de TireCalibration
 * 
 * FASE 1: Mapear VehicleData (diário às 03:00)
 * FASE 2: Gerar artigos completos (diário às 04:00) 
 * FASE 3: Refinar com Claude (a cada 30 min, 1 por vez)
 */
class TireCalibrationEnrichmentSchedule extends Command
{
    protected $signature = 'schedule:tire-calibration-enrichment 
                           {--phase=all : Fase específica (phase1|phase2|claude|all)}
                           {--limit=1 : Limite de processamento}
                           {--dry-run : Modo simulação}
                           {--force : Forçar reprocessamento}';

    protected $description = 'Schedule automático para enriquecimento de TireCalibration';

    public function handle(): ?int
    {
        $phase = $this->option('phase');
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        Log::info('TireCalibrationEnrichmentSchedule: Iniciando execução', [
            'phase' => $phase,
            'limit' => $limit,
            'dry_run' => $dryRun,
            'environment' => app()->environment()
        ]);

        $this->info('🚀 SCHEDULE DE TIRE CALIBRATION');
        $this->info('   Horário: ' . now()->format('d/m/Y H:i:s'));
        $this->info('   Fase: ' . $phase);
        $this->newLine();

        try {
            $results = [];

            switch ($phase) {
                case 'phase1':
                    $results = $this->runPhase1($limit, $dryRun, $force);
                    break;
                    
                case 'phase2':
                    $results = $this->runPhase2($limit, $dryRun, $force);
                    break;
                    
                case 'claude':
                    $results = $this->runClaudeRefinement($limit, $dryRun, $force);
                    break;
                    
                case 'all':
                    $results['phase1'] = $this->runPhase1($limit, $dryRun, $force);
                    $results['phase2'] = $this->runPhase2($limit, $dryRun, $force);
                    $results['claude'] = $this->runClaudeRefinement(1, $dryRun, $force); // Claude sempre 1
                    break;
            }

            $this->showSummary($results, $phase);
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ ERRO: ' . $e->getMessage());
            Log::error('TireCalibrationEnrichmentSchedule: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::SUCCESS; // Não quebrar o schedule
        }
    }

    /**
     * FASE 1: Mapear dados do VehicleData
     */
    protected function runPhase1(int $limit, bool $dryRun, bool $force): array
    {
        $this->info('🔄 FASE 1: Mapeando VehicleData...');
        
        $params = ['--limit' => $limit];
        if ($dryRun) $params['--dry-run'] = true;
        if ($force) $params['--force'] = true;
        
        $exitCode = $this->call('tire-calibration:map-vehicle-data', $params);
        
        return [
            'exit_code' => $exitCode,
            'success' => $exitCode === Command::SUCCESS,
            'phase' => 'phase1'
        ];
    }

    /**
     * FASE 2: Gerar artigos completos
     */
    protected function runPhase2(int $limit, bool $dryRun, bool $force): array
    {
        $this->info('🔄 FASE 2: Gerando artigos completos...');
        
        $params = ['--limit' => $limit];
        if ($dryRun) $params['--dry-run'] = true;
        if ($force) $params['--force'] = true;
        
        $exitCode = $this->call('tire-calibration:generate-articles-phase1', $params);
        
        return [
            'exit_code' => $exitCode,
            'success' => $exitCode === Command::SUCCESS,
            'phase' => 'phase2'
        ];
    }

    /**
     * FASE 3: Refinar com Claude (1 por vez)
     */
    protected function runClaudeRefinement(int $limit, bool $dryRun, bool $force): array
    {
        $this->info('🔄 FASE 3: Refinando com Claude API (1 por vez)...');
        
        // Claude sempre 1 por vez
        $params = ['--limit' => 1];
        if ($dryRun || empty(config('services.anthropic.api_key'))) {
            $params['--dry-run'] = true;
            $this->warn('🔍 API Claude não configurada - executando em DRY-RUN');
        }
        if ($force) $params['--force'] = true;
        
        $exitCode = $this->call('tire-calibration:refine-with-claude', $params);
        
        return [
            'exit_code' => $exitCode,
            'success' => $exitCode === Command::SUCCESS,
            'phase' => 'claude',
            'dry_run' => isset($params['--dry-run'])
        ];
    }

    /**
     * Exibir resumo da execução
     */
    protected function showSummary(array $results, string $phase): void
    {
        $this->newLine();
        $this->info('=== RESUMO DA EXECUÇÃO ===');

        // Estatísticas atuais
        $stats = TireCalibration::getProcessingStats();

        if ($phase === 'all') {
            $this->table(
                ['Fase', 'Status', 'Exit Code'],
                [
                    ['Fase 1 - Mapping', $results['phase1']['success'] ? '✅ Sucesso' : '❌ Falha', $results['phase1']['exit_code']],
                    ['Fase 2 - Articles', $results['phase2']['success'] ? '✅ Sucesso' : '❌ Falha', $results['phase2']['exit_code']],
                    ['Fase 3 - Claude', $results['claude']['success'] ? '✅ Sucesso' : '❌ Falha', $results['claude']['exit_code']],
                ]
            );
        } else {
            $this->line("✅ Fase {$phase}: " . ($results['success'] ? 'Sucesso' : 'Falha'));
        }

        $this->newLine();
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total registros', $stats['total']],
                ['Pendentes mapeamento', $stats['pending_mapping']],
                ['Pendentes artigos', $stats['pending_articles']],  
                ['Pendentes Claude', $stats['pending_claude']],
                ['Completados', $stats['completed']],
                ['Taxa conclusão', $stats['completion_rate'] . '%'],
            ]
        );

        Log::info('TireCalibrationEnrichmentSchedule: Execução concluída', [
            'results' => $results,
            'stats' => $stats,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * ✅ REGISTRAR NO SCHEDULE SEGUINDO O PADRÃO DO PROJETO
     */
    public static function register($schedule): void
    {
        // FASE 1: Mapear VehicleData (diário às 03:00)
        $schedule->command('schedule:tire-calibration-enrichment --phase=phase1 --limit=100')
            ->dailyAt('03:00')
            ->withoutOverlapping(30)
            ->appendOutputTo(storage_path('logs/tire-calibration-phase1.log'))
            ->onFailure(function () {
                Log::error('TireCalibrationSchedule: Falha na Fase 1');
            });

        // FASE 2: Gerar artigos completos (diário às 04:00)  
        $schedule->command('schedule:tire-calibration-enrichment --phase=phase2 --limit=100')
            ->dailyAt('04:00')
            ->withoutOverlapping(60)
            ->appendOutputTo(storage_path('logs/tire-calibration-phase2.log'))
            ->onFailure(function () {
                Log::error('TireCalibrationSchedule: Falha na Fase 2');
            });

        // FASE 3: Refinar com Claude (a cada 30 minutos, 1 por vez)
        $schedule->command('schedule:tire-calibration-enrichment --phase=claude --limit=1')
            ->everyThirtyMinutes()
            ->withoutOverlapping(25)
            ->appendOutputTo(storage_path('logs/tire-calibration-claude.log'))
            ->onFailure(function () {
                Log::error('TireCalibrationSchedule: Falha na Fase Claude');
            });

        // Estatísticas diárias (às 06:00)
        $schedule->command('tire-calibration:stats --detailed')
            ->dailyAt('06:00')
            ->appendOutputTo(storage_path('logs/tire-calibration-stats.log'));
    }
}
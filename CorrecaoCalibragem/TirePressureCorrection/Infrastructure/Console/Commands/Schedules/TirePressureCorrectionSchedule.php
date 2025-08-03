<?php

namespace Src\TirePressureCorrection\Infrastructure\Console\Commands\Schedules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\TirePressureCorrection\Domain\Entities\TirePressureCorrection;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * Schedule automático para correção de pressões de pneus via VehicleData
 * 
 * EXECUÇÃO: A cada 3 horas
 * PROCESSO: Atualização direta via VehicleData (sem API)
 */
class TirePressureCorrectionSchedule extends Command
{
    protected $signature = 'schedule:tire-pressure-correction 
                           {--limit=50 : Limite de artigos por execução}
                           {--min-quality-score=6.0 : Score mínimo de qualidade dos dados}
                           {--dry-run : Modo simulação}
                           {--force : Forçar reprocessamento}';
    
    protected $description = 'Schedule automático para correção de pressões via VehicleData';
    
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $minQualityScore = (float) $this->option('min-quality-score');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        Log::info('TirePressureCorrectionSchedule: Iniciando execução via VehicleData', [
            'limit' => $limit,
            'min_quality_score' => $minQualityScore,
            'dry_run' => $dryRun,
            'force' => $force
        ]);
        
        $this->info('🚀 SCHEDULE DE CORREÇÃO DE PRESSÕES (VIA VEHICLE DATA)');
        $this->info('   Horário: ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();
        
        try {
            // Verificar ambiente
            if (app()->environment('local') && !$dryRun) {
                $this->warn('⚠️  Ambiente local detectado - executando em modo dry-run');
                $dryRun = true;
            }
            
            // Verificar pré-requisitos
            if (!$this->checkPrerequisites()) {
                return Command::FAILURE;
            }
            
            // Executar correção via VehicleData
            $results = $this->runVehicleDataCorrection($limit, $minQualityScore, $dryRun, $force);
            
            // Exibir resumo
            $this->showSummary($results);
            
            // Limpeza opcional
            $this->performCleanup();
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ ERRO: ' . $e->getMessage());
            
            Log::error('TirePressureCorrectionSchedule: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Verificar pré-requisitos do sistema
     */
    protected function checkPrerequisites(): bool
    {
        // Verificar se VehicleData existe e tem dados
        $vehicleCount = VehicleData::count();
        
        if ($vehicleCount === 0) {
            $this->error('❌ VehicleData está vazio!');
            $this->line('   Execute primeiro: php artisan vehicle-data:extract');
            return false;
        }
        
        // Verificar qualidade dos dados
        $qualityVehicles = VehicleData::where('data_quality_score', '>=', 6.0)->count();
        
        if ($qualityVehicles === 0) {
            $this->error('❌ Nenhum veículo com qualidade suficiente no VehicleData!');
            return false;
        }
        
        $this->info("✅ Pré-requisitos OK: {$vehicleCount} veículos ({$qualityVehicles} com qualidade ≥6.0)");
        return true;
    }
    
    /**
     * Executar correção via VehicleData
     */
    protected function runVehicleDataCorrection(int $limit, float $minQualityScore, bool $dryRun, bool $force): array
    {
        $this->info('🔄 EXECUTANDO CORREÇÃO VIA VEHICLE DATA');
        $this->newLine();
        
        // Verificar artigos pendentes
        $pendingCount = $this->countPendingArticles($force);
        
        if ($pendingCount === 0) {
            $this->info('✅ Nenhum artigo pendente para processar');
            return [
                'processed' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => 0,
                'skipped_reason' => 'no_articles'
            ];
        }
        
        $this->info("📊 Artigos pendentes: {$pendingCount}");
        
        // Executar o novo comando
        $params = [
            '--limit' => $limit,
            '--min-quality-score' => $minQualityScore
        ];
        
        if ($dryRun) {
            $params['--dry-run'] = true;
        }
        
        if ($force) {
            $params['--force'] = true;
        }
        
        $exitCode = $this->call('articles:update-tire-pressures-from-vehicle-data', $params);
        
        // Simular retorno de resultados (seria melhor se o command retornasse dados)
        $stats = TirePressureCorrection::getStats();
        
        return [
            'exit_code' => $exitCode,
            'processed' => min($limit, $pendingCount),
            'success' => $exitCode === 0,
            'current_stats' => $stats
        ];
    }
    
    /**
     * Contar artigos pendentes
     */
    protected function countPendingArticles(bool $force): int
    {
        if ($force) {
            // Se forçar, contar todos os artigos válidos
            return $this->countValidArticles();
        }
        
        // Artigos já processados recentemente (últimos 7 dias)
        $processedArticles = TirePressureCorrection::where('created_at', '>=', now()->subDays(7))
            ->where('status', '!=', TirePressureCorrection::STATUS_FAILED)
            ->pluck('article_id');
        
        // Contar artigos não processados
        $count = 0;
        
        \Src\AutoInfoCenter\Domain\Eloquent\Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities')
            ->when($processedArticles->isNotEmpty(), function($query) use ($processedArticles) {
                $query->whereNotIn('_id', $processedArticles);
            })
            ->chunk(100, function ($articles) use (&$count) {
                foreach ($articles as $article) {
                    $marca = data_get($article, 'extracted_entities.marca');
                    $modelo = data_get($article, 'extracted_entities.modelo');
                    $ano = data_get($article, 'extracted_entities.ano');
                    
                    if (!empty($marca) && !empty($modelo) && !empty($ano)) {
                        $count++;
                    }
                }
            });
        
        return $count;
    }
    
    /**
     * Contar todos os artigos válidos
     */
    protected function countValidArticles(): int
    {
        $count = 0;
        
        \Src\AutoInfoCenter\Domain\Eloquent\Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities')
            ->chunk(100, function ($articles) use (&$count) {
                foreach ($articles as $article) {
                    $marca = data_get($article, 'extracted_entities.marca');
                    $modelo = data_get($article, 'extracted_entities.modelo');
                    $ano = data_get($article, 'extracted_entities.ano');
                    
                    if (!empty($marca) && !empty($modelo) && !empty($ano)) {
                        $count++;
                    }
                }
            });
        
        return $count;
    }
    
    /**
     * Exibir resumo da execução
     */
    protected function showSummary(array $results): void
    {
        $this->newLine();
        $this->info('=== RESUMO DA EXECUÇÃO ===');
        
        if (isset($results['skipped_reason'])) {
            $this->line("⏭️  Execução pulada: {$results['skipped_reason']}");
            return;
        }
        
        // Estatísticas atuais
        $stats = $results['current_stats'] ?? TirePressureCorrection::getDetailedStats();
        
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Status da execução', $results['success'] ? '✅ Sucesso' : '❌ Falha'],
                ['Exit code', $results['exit_code'] ?? 'N/A'],
                ['Total de correções', $stats['total']],
                ['Pendentes', $stats['pending']],
                ['Concluídas', $stats['completed']],
                ['Sem alterações', $stats['no_changes']],
                ['Falhas', $stats['failed']],
                ['Taxa de sucesso', round($stats['success_rate'] ?? 0, 1) . '%'],
            ]
        );
        
        // Log para monitoramento
        Log::info('TirePressureCorrectionSchedule: Execução concluída via VehicleData', [
            'results' => $results,
            'stats' => $stats
        ]);
    }
    
    /**
     * Realizar limpeza de dados antigos
     */
    protected function performCleanup(): void
    {
        // Limpar correções antigas (mais de 30 dias)
        $deleted = TirePressureCorrection::cleanOldCorrections(30);
        
        if ($deleted > 0) {
            $this->info("🧹 Limpeza: {$deleted} correções antigas removidas");
            
            Log::info('TirePressureCorrectionSchedule: Limpeza realizada', [
                'deleted' => $deleted
            ]);
        }
    }
    
    /**
     * Registrar schedule no Laravel
     */
    public static function register($schedule): void
    {
        // Executar a cada 3 horas usando VehicleData
        $schedule->command('schedule:tire-pressure-correction')
            ->everyThreeHours()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tire-pressure-correction-schedule.log'))
            ->onFailure(function () {
                Log::error('TirePressureCorrectionSchedule: Falha na execução agendada via VehicleData');
            })
            ->onSuccess(function () {
                Log::info('TirePressureCorrectionSchedule: Execução agendada concluída via VehicleData');
            });
    }
}
<?php

namespace Src\TirePressureCorrection\Infrastructure\Console\Commands\Schedules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\TirePressureCorrection\Domain\Entities\TirePressureCorrection;

/**
 * Schedule automático para correção de pressões de pneus
 * 
 * EXECUÇÃO: A cada 3 horas
 * PROCESSO: Coleta → Aplicação
 */
class TirePressureCorrectionSchedule extends Command
{
    protected $signature = 'schedule:tire-pressure-correction 
                           {--stage=both : Estágio a executar (collect|apply|both)}
                           {--limit=30 : Limite de artigos por execução}
                           {--groups=5 : Limite de grupos para coleta}
                           {--dry-run : Modo simulação}';
    
    protected $description = 'Schedule automático para correção de pressões de pneus';
    
    public function handle(): int
    {
        $stage = $this->option('stage');
        $limit = (int) $this->option('limit');
        $groups = (int) $this->option('groups');
        $dryRun = $this->option('dry-run');
        
        Log::info('TirePressureCorrectionSchedule: Iniciando', [
            'stage' => $stage,
            'limit' => $limit,
            'groups' => $groups,
            'dry_run' => $dryRun
        ]);
        
        $this->info('🚀 SCHEDULE DE CORREÇÃO DE PRESSÕES');
        $this->info('   Horário: ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();
        
        try {
            // Verificar ambiente
            if (app()->environment('local') && !$dryRun) {
                $this->warn('⚠️  Ambiente local detectado - executando em modo dry-run');
                $dryRun = true;
            }
            
            // Executar estágios
            $results = match($stage) {
                'collect' => $this->runCollectStage($limit, $groups, $dryRun),
                'apply' => $this->runApplyStage($limit, $dryRun),
                'both' => $this->runBothStages($limit, $groups, $dryRun),
                default => throw new \Exception("Estágio inválido: {$stage}")
            };
            
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
     * Executar estágio de coleta
     */
    protected function runCollectStage(int $limit, int $groups, bool $dryRun): array
    {
        $this->info('📥 ESTÁGIO 1: COLETA DE DADOS');
        $this->newLine();
        
        // Verificar se há artigos para processar
        $pendingCount = $this->countPendingArticles();
        
        if ($pendingCount === 0) {
            $this->info('✅ Nenhum artigo novo para processar');
            return ['collect' => ['skipped' => true]];
        }
        
        $this->info("📊 Artigos pendentes: {$pendingCount}");
        
        // Executar comando de coleta
        $params = [
            '--limit' => $limit,
            '--groups' => $groups
        ];
        
        if ($dryRun) {
            $params['--dry-run'] = true;
        }
        
        $exitCode = $this->call('articles:collect-tire-pressures', $params);
        
        return [
            'collect' => [
                'exit_code' => $exitCode,
                'skipped' => false
            ]
        ];
    }
    
    /**
     * Executar estágio de aplicação
     */
    protected function runApplyStage(int $limit, bool $dryRun): array
    {
        $this->newLine();
        $this->info('📤 ESTÁGIO 2: APLICAÇÃO DE CORREÇÕES');
        $this->newLine();
        
        // Verificar correções pendentes
        $pendingCorrections = TirePressureCorrection::pending()->count();
        
        if ($pendingCorrections === 0) {
            $this->info('✅ Nenhuma correção pendente para aplicar');
            return ['apply' => ['skipped' => true]];
        }
        
        $this->info("📊 Correções pendentes: {$pendingCorrections}");
        
        // Executar comando de aplicação
        $params = ['--limit' => $limit];
        
        if ($dryRun) {
            $params['--dry-run'] = true;
        }
        
        $exitCode = $this->call('articles:apply-tire-pressures', $params);
        
        return [
            'apply' => [
                'exit_code' => $exitCode,
                'skipped' => false
            ]
        ];
    }
    
    /**
     * Executar ambos os estágios
     */
    protected function runBothStages(int $limit, int $groups, bool $dryRun): array
    {
        $results = [];
        
        // Estágio 1: Coleta
        $results['collect'] = $this->runCollectStage($limit, $groups, $dryRun);
        
        // Aguardar um pouco entre estágios
        if (!$dryRun && !($results['collect']['collect']['skipped'] ?? false)) {
            $this->info('⏳ Aguardando 30 segundos entre estágios...');
            sleep(30);
        }
        
        // Estágio 2: Aplicação
        $results['apply'] = $this->runApplyStage($limit, $dryRun);
        
        return $results;
    }
    
    /**
     * Contar artigos pendentes
     */
    protected function countPendingArticles(): int
    {
        // Artigos já processados recentemente
        $processedArticles = TirePressureCorrection::where('created_at', '>=', now()->subDays(7))
            ->where('status', '!=', TirePressureCorrection::STATUS_FAILED)
            ->pluck('article_id');
        
        // Para MongoDB, fazer a contagem manualmente
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
                    
                    if (!empty($marca) && !empty($modelo)) {
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
        
        // Estatísticas gerais
        $stats = TirePressureCorrection::getDetailedStats();
        
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total de correções', $stats['total']],
                ['Pendentes', $stats['pending']],
                ['Concluídas', $stats['completed']],
                ['Falhas', $stats['failed']],
                ['Taxa de sucesso', $stats['success_rate'] . '%'],
                ['Média por dia', round($stats['average_per_day'], 1)]
            ]
        );
        
        // Log para monitoramento
        Log::info('TirePressureCorrectionSchedule: Execução concluída', [
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
        // Executar a cada 3 horas
        $schedule->command('schedule:tire-pressure-correction')
            ->everyThreeHours()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tire-pressure-correction-schedule.log'))
            ->onFailure(function () {
                Log::error('TirePressureCorrectionSchedule: Falha na execução agendada');
            })
            ->onSuccess(function () {
                Log::info('TirePressureCorrectionSchedule: Execução agendada concluída');
            });
    }
}
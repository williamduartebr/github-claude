<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;

class CorrectionHealthCheck extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:health-check 
                           {--fix : Corrigir problemas encontrados automaticamente}
                           {--clean-orphans : Remover correções órfãs (artigos que não existem mais)}
                           {--reset-stuck : Resetar correções presas em processamento}
                           {--consolidate-duplicates : Consolidar análises duplicadas}
                           {--dry-run : Apenas mostrar problemas sem corrigir}
                           {--detailed : Mostrar relatório detalhado}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Verifica e corrige problemas no sistema de correção de pontuação';

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $this->info('🔍 Verificação de Saúde do Sistema de Correção');
        $this->line('=============================================');
        $this->line('');

        $issues = [];
        $fixes = 0;

        // 1. Verificar correções órfãs
        $orphanIssues = $this->checkOrphanCorrections();
        if (!empty($orphanIssues)) {
            $issues['orphans'] = $orphanIssues;
            if ($this->option('clean-orphans') || $this->option('fix')) {
                $fixes += $this->fixOrphanCorrections($orphanIssues);
            }
        }

        // 2. Verificar correções presas
        $stuckIssues = $this->checkStuckCorrections();
        if (!empty($stuckIssues)) {
            $issues['stuck'] = $stuckIssues;
            if ($this->option('reset-stuck') || $this->option('fix')) {
                $fixes += $this->fixStuckCorrections($stuckIssues);
            }
        }

        // 3. Verificar análises duplicadas
        $duplicateIssues = $this->checkDuplicateAnalyses();
        if (!empty($duplicateIssues)) {
            $issues['duplicates'] = $duplicateIssues;
            if ($this->option('consolidate-duplicates') || $this->option('fix')) {
                $fixes += $this->fixDuplicateAnalyses($duplicateIssues);
            }
        }

        // 4. Verificar inconsistências de status
        $statusIssues = $this->checkStatusInconsistencies();
        if (!empty($statusIssues)) {
            $issues['status'] = $statusIssues;
            if ($this->option('fix')) {
                $fixes += $this->fixStatusInconsistencies($statusIssues);
            }
        }

        // 5. Verificar correções sem dados
        $dataIssues = $this->checkMissingData();
        if (!empty($dataIssues)) {
            $issues['missing_data'] = $dataIssues;
        }

        // Mostrar resumo
        $this->showHealthSummary($issues, $fixes);

        return empty($issues) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Verifica correções órfãs (artigos que não existem mais)
     */
    protected function checkOrphanCorrections()
    {
        $this->info('🔍 Verificando correções órfãs...');

        $corrections = ArticleCorrection::all(['_id', 'article_slug', 'correction_type', 'status']);
        $orphans = [];

        foreach ($corrections as $correction) {
            $articleExists = Article::where('slug', $correction->article_slug)->exists();
            
            if (!$articleExists) {
                $orphans[] = $correction;
            }
        }

        if (!empty($orphans)) {
            $this->warn("⚠️ Encontradas {$orphans} correções órfãs");
            
            if ($this->option('detailed')) {
                $tableData = [];
                foreach (array_slice($orphans, 0, 10) as $orphan) {
                    $tableData[] = [
                        $orphan->_id,
                        $orphan->article_slug,
                        $orphan->correction_type,
                        $orphan->status
                    ];
                }
                $this->table(['ID', 'Slug', 'Tipo', 'Status'], $tableData);
                
                if (count($orphans) > 10) {
                    $this->line('... (mostrando apenas os primeiros 10)');
                }
            }
        } else {
            $this->info('✅ Nenhuma correção órfã encontrada');
        }

        return $orphans;
    }

    /**
     * Verifica correções presas em processamento
     */
    protected function checkStuckCorrections()
    {
        $this->info('🔍 Verificando correções presas...');

        // Correções em processamento há mais de 2 horas
        $stuckCorrections = ArticleCorrection::where('status', ArticleCorrection::STATUS_PROCESSING)
            ->where('processed_at', '<', now()->subHours(2))
            ->get();

        if ($stuckCorrections->isNotEmpty()) {
            $this->warn("⚠️ Encontradas {$stuckCorrections->count()} correções presas em processamento");
            
            if ($this->option('detailed')) {
                $tableData = [];
                foreach ($stuckCorrections->take(10) as $stuck) {
                    $tableData[] = [
                        $stuck->_id,
                        $stuck->article_slug,
                        $stuck->correction_type,
                        $stuck->processed_at ? $stuck->processed_at->diffForHumans() : 'N/A'
                    ];
                }
                $this->table(['ID', 'Slug', 'Tipo', 'Processando há'], $tableData);
            }
        } else {
            $this->info('✅ Nenhuma correção presa encontrada');
        }

        return $stuckCorrections->toArray();
    }

    /**
     * Verifica análises duplicadas para o mesmo artigo
     */
    protected function checkDuplicateAnalyses()
    {
        $this->info('🔍 Verificando análises duplicadas...');

        $duplicates = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
            ->get()
            ->groupBy('article_slug')
            ->filter(function($group) {
                return $group->count() > 1;
            });

        $duplicateIssues = [];
        
        foreach ($duplicates as $slug => $analyses) {
            // Ordenar por data de criação
            $sortedAnalyses = $analyses->sortBy('created_at');
            
            // Manter apenas a mais recente, marcar outras como duplicadas
            $toRemove = $sortedAnalyses->slice(0, -1);
            
            if ($toRemove->isNotEmpty()) {
                $duplicateIssues[$slug] = $toRemove->toArray();
            }
        }

        if (!empty($duplicateIssues)) {
            $totalDuplicates = collect($duplicateIssues)->flatten()->count();
            $this->warn("⚠️ Encontradas análises duplicadas para " . count($duplicateIssues) . " artigos ({$totalDuplicates} duplicatas)");
            
            if ($this->option('detailed')) {
                foreach (array_slice($duplicateIssues, 0, 5, true) as $slug => $duplicates) {
                    $this->line("• {$slug}: " . count($duplicates) . " duplicatas");
                }
            }
        } else {
            $this->info('✅ Nenhuma análise duplicada encontrada');
        }

        return $duplicateIssues;
    }

    /**
     * Verifica inconsistências de status
     */
    protected function checkStatusInconsistencies()
    {
        $this->info('🔍 Verificando inconsistências de status...');

        $issues = [];

        // Correções completadas sem data de processamento
        $noProcessedAt = ArticleCorrection::where('status', ArticleCorrection::STATUS_COMPLETED)
            ->whereNull('processed_at')
            ->get();

        if ($noProcessedAt->isNotEmpty()) {
            $issues['no_processed_at'] = $noProcessedAt->toArray();
            $this->warn("⚠️ {$noProcessedAt->count()} correções completadas sem data de processamento");
        }

        // Correções pendentes com data de processamento
        $pendingWithDate = ArticleCorrection::where('status', ArticleCorrection::STATUS_PENDING)
            ->whereNotNull('processed_at')
            ->get();

        if ($pendingWithDate->isNotEmpty()) {
            $issues['pending_with_date'] = $pendingWithDate->toArray();
            $this->warn("⚠️ {$pendingWithDate->count()} correções pendentes com data de processamento");
        }

        if (empty($issues)) {
            $this->info('✅ Nenhuma inconsistência de status encontrada');
        }

        return $issues;
    }

    /**
     * Verifica correções com dados ausentes
     */
    protected function checkMissingData()
    {
        $this->info('🔍 Verificando dados ausentes...');

        $issues = [];

        // Análises sem dados originais
        $noOriginalData = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
            ->where(function($query) {
                $query->whereNull('original_data')
                      ->orWhere('original_data', [])
                      ->orWhereRaw(['original_data.introducao' => null]);
            })
            ->get();

        if ($noOriginalData->isNotEmpty()) {
            $issues['no_original_data'] = $noOriginalData->toArray();
            $this->warn("⚠️ {$noOriginalData->count()} análises sem dados originais adequados");
        }

        // Correções completadas sem dados de correção
        $noCorrectionData = ArticleCorrection::where('status', ArticleCorrection::STATUS_COMPLETED)
            ->where('correction_type', ArticleCorrection::TYPE_INTRODUCTION_FIX)
            ->where(function($query) {
                $query->whereNull('correction_data')
                      ->orWhere('correction_data', []);
            })
            ->get();

        if ($noCorrectionData->isNotEmpty()) {
            $issues['no_correction_data'] = $noCorrectionData->toArray();
            $this->warn("⚠️ {$noCorrectionData->count()} correções completadas sem dados de correção");
        }

        if (empty($issues)) {
            $this->info('✅ Nenhum problema de dados ausentes encontrado');
        }

        return $issues;
    }

    /**
     * Corrige correções órfãs
     */
    protected function fixOrphanCorrections($orphans)
    {
        if ($this->option('dry-run')) {
            $this->info("🔍 [DRY RUN] {$orphans} correções órfãs seriam removidas");
            return 0;
        }

        $removed = 0;
        foreach ($orphans as $orphan) {
            try {
                $orphan->delete();
                $removed++;
            } catch (\Exception $e) {
                $this->error("Erro ao remover correção órfã {$orphan->_id}: " . $e->getMessage());
            }
        }

        $this->info("✅ {$removed} correções órfãs removidas");
        return $removed;
    }

    /**
     * Corrige correções presas
     */
    protected function fixStuckCorrections($stuckCorrections)
    {
        if ($this->option('dry-run')) {
            $this->info("🔍 [DRY RUN] " . count($stuckCorrections) . " correções presas seriam resetadas");
            return 0;
        }

        $reset = 0;
        foreach ($stuckCorrections as $stuckData) {
            try {
                $stuck = ArticleCorrection::find($stuckData['_id']);
                if ($stuck) {
                    $stuck->resetForReprocessing();
                    $reset++;
                }
            } catch (\Exception $e) {
                $this->error("Erro ao resetar correção {$stuckData['_id']}: " . $e->getMessage());
            }
        }

        $this->info("✅ {$reset} correções presas resetadas");
        return $reset;
    }

    /**
     * Corrige análises duplicadas
     */
    protected function fixDuplicateAnalyses($duplicateIssues)
    {
        if ($this->option('dry-run')) {
            $totalDuplicates = collect($duplicateIssues)->flatten()->count();
            $this->info("🔍 [DRY RUN] {$totalDuplicates} análises duplicadas seriam removidas");
            return 0;
        }

        $removed = 0;
        foreach ($duplicateIssues as $slug => $duplicates) {
            foreach ($duplicates as $duplicateData) {
                try {
                    $duplicate = ArticleCorrection::find($duplicateData['_id']);
                    if ($duplicate) {
                        $duplicate->delete();
                        $removed++;
                    }
                } catch (\Exception $e) {
                    $this->error("Erro ao remover análise duplicada {$duplicateData['_id']}: " . $e->getMessage());
                }
            }
        }

        $this->info("✅ {$removed} análises duplicadas removidas");
        return $removed;
    }

    /**
     * Corrige inconsistências de status
     */
    protected function fixStatusInconsistencies($statusIssues)
    {
        if ($this->option('dry-run')) {
            $total = collect($statusIssues)->flatten()->count();
            $this->info("🔍 [DRY RUN] {$total} inconsistências de status seriam corrigidas");
            return 0;
        }

        $fixed = 0;

        // Corrigir correções completadas sem data de processamento
        if (isset($statusIssues['no_processed_at'])) {
            foreach ($statusIssues['no_processed_at'] as $issueData) {
                try {
                    $correction = ArticleCorrection::find($issueData['_id']);
                    if ($correction) {
                        $correction->update(['processed_at' => $correction->updated_at ?? now()]);
                        $fixed++;
                    }
                } catch (\Exception $e) {
                    $this->error("Erro ao corrigir data de processamento {$issueData['_id']}: " . $e->getMessage());
                }
            }
        }

        // Corrigir correções pendentes com data de processamento
        if (isset($statusIssues['pending_with_date'])) {
            foreach ($statusIssues['pending_with_date'] as $issueData) {
                try {
                    $correction = ArticleCorrection::find($issueData['_id']);
                    if ($correction) {
                        $correction->update(['processed_at' => null]);
                        $fixed++;
                    }
                } catch (\Exception $e) {
                    $this->error("Erro ao limpar data de processamento {$issueData['_id']}: " . $e->getMessage());
                }
            }
        }

        $this->info("✅ {$fixed} inconsistências de status corrigidas");
        return $fixed;
    }

    /**
     * Mostra resumo da verificação de saúde
     */
    protected function showHealthSummary($issues, $fixes)
    {
        $this->line('');
        $this->info('📋 RESUMO DA VERIFICAÇÃO DE SAÚDE');
        $this->line('================================');

        if (empty($issues)) {
            $this->info('🎉 Sistema está saudável! Nenhum problema encontrado.');
            return;
        }

        $totalIssues = 0;
        foreach ($issues as $category => $categoryIssues) {
            $count = is_array($categoryIssues) ? 
                (isset($categoryIssues[0]) && is_array($categoryIssues[0]) ? count($categoryIssues) : count(collect($categoryIssues)->flatten())) :
                count($categoryIssues);
            $totalIssues += $count;
        }

        $this->warn("⚠️ {$totalIssues} problemas encontrados");
        
        if ($fixes > 0) {
            $this->info("✅ {$fixes} problemas foram corrigidos automaticamente");
        }

        $this->line('');
        $this->info('💡 RECOMENDAÇÕES:');
        
        if (isset($issues['orphans'])) {
            $this->line('• Execute: php artisan articles:health-check --clean-orphans');
        }
        
        if (isset($issues['stuck'])) {
            $this->line('• Execute: php artisan articles:health-check --reset-stuck');
        }
        
        if (isset($issues['duplicates'])) {
            $this->line('• Execute: php artisan articles:health-check --consolidate-duplicates');
        }
        
        if ($totalIssues > 0 && $fixes === 0) {
            $this->line('• Execute: php artisan articles:health-check --fix (para corrigir todos)');
        }
        
        $this->line('• Execute verificações regulares para manter o sistema saudável');
    }
}
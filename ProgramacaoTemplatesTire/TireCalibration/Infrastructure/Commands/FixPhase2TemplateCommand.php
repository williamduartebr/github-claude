<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

/**
 * FixPhase2TemplateCommand - CORREÇÃO OTIMIZADA APENAS FASE 2
 * 
 * ESTRATÉGIA INTELIGENTE:
 * 1. Corrigir APENAS generated_article.template (Fase 2)
 * 2. Resetar enrichment_phase para PHASE_ARTICLE_GENERATED
 * 3. Limpar fases Claude (3A e 3B)
 * 4. Schedule automático reprocessará com template correto
 * 
 * VANTAGENS:
 * ✅ Correção rápida e focada
 * ✅ Schedule automático cuida do resto
 * ✅ Zero desperdício de processamento
 * ✅ Reaproveitamento da infraestrutura existente
 * 
 * @author Claude Sonnet 4
 * @version 2.0 - Otimizada para reprocessamento automático
 */
class FixPhase2TemplateCommand extends Command
{
    protected $signature = 'tire-calibration:fix-phase2-templates
                            {--dry-run : Simular correções sem salvar}
                            {--limit=100 : Limitar número de correções}
                            {--show-affected : Mostrar registros afetados}
                            {--stats : Mostrar estatísticas detalhadas}';

    protected $description = '🔧 Correção otimizada: Corrigir Fase 2 e deixar schedule reprocessar Claude';

    private int $affectedCount = 0;
    private int $correctedCount = 0;
    private int $errorCount = 0;
    private array $affectedIds = [];

    public function handle(): int
    {
        $this->alert('🔧 CORREÇÃO OTIMIZADA - APENAS FASE 2');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->info('🎯 Estratégia: Corrigir Fase 2 → Schedule reprocessa Claude automaticamente');
        $this->newLine();

        try {
            // 1. Identificar registros afetados
            $affected = $this->identifyAffectedRecords();
            
            if ($affected->isEmpty()) {
                $this->info('✅ Nenhuma inconsistência encontrada!');
                return self::SUCCESS;
            }

            $this->affectedCount = $affected->count();
            $this->warn("🔍 Encontradas {$this->affectedCount} inconsistências de template");
            
            // 2. Mostrar estatísticas se solicitado
            if ($this->option('stats')) {
                $this->showDetailedStats($affected);
            }

            // 3. Mostrar registros afetados se solicitado
            if ($this->option('show-affected')) {
                $this->showAffectedRecords($affected);
            }

            // 4. Aplicar correções ou simulação
            if (!$this->option('dry-run')) {
                $this->info('🚀 Aplicando correções otimizadas...');
                $results = $this->applyOptimizedCorrections($affected);
                $this->showResults($results);
                
                // 5. Orientações pós-correção
                $this->showPostCorrectionGuidance();
            } else {
                $this->warn('🧪 DRY-RUN: Nenhuma alteração foi salva');
                $this->showDryRunSummary($affected);
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ ERRO: ' . $e->getMessage());
            Log::error('FixPhase2TemplateCommand: Erro', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Identificar registros com inconsistência de template
     */
    private function identifyAffectedRecords()
    {
        $this->info('🔍 Identificando registros com templates incorretos...');

        return TireCalibration::where(function($query) {
            // Condição 1: É motocicleta mas usa template de carro
            $query->where([
                ['vehicle_features.vehicle_type', '=', 'motorcycle'],
                ['generated_article.template', '=', 'tire_calibration_car']
            ])
            // OU Condição 2: Categoria começa com motorcycle_ mas template é car
            ->orWhere(function($subQuery) {
                $subQuery->where('main_category', 'regex', '/^motorcycle_/')
                         ->where('generated_article.template', '=', 'tire_calibration_car');
            });
        })
        ->limit($this->option('limit'))
        ->get();
    }

    /**
     * Mostrar estatísticas detalhadas
     */
    private function showDetailedStats($affected): void
    {
        $this->newLine();
        $this->info('📊 ESTATÍSTICAS DETALHADAS:');
        
        // Agrupar por categoria
        $byCategory = $affected->groupBy('main_category');
        $categoryStats = [];
        
        foreach ($byCategory as $category => $records) {
            $categoryStats[] = [
                $category ?: 'N/A',
                $records->count(),
                $records->where('enrichment_phase', TireCalibration::PHASE_CLAUDE_COMPLETED)->count() . ' Completados',
                $records->where('enrichment_phase', TireCalibration::PHASE_ARTICLE_GENERATED)->count() . ' Aguardando'
            ];
        }

        $this->table([
            'Categoria',
            'Registros',
            'Claude Processado', 
            'Aguardando Claude'
        ], $categoryStats);

        // Agrupar por fase
        $byPhase = $affected->groupBy('enrichment_phase');
        $phaseStats = [];
        
        foreach ($byPhase as $phase => $records) {
            $phaseDescription = match($phase) {
                TireCalibration::PHASE_ARTICLE_GENERATED => 'Pronto para Claude (não processou)',
                TireCalibration::PHASE_CLAUDE_3A_COMPLETED => 'Claude 3A concluído',
                TireCalibration::PHASE_CLAUDE_COMPLETED => 'Claude completo (reprocessar)',
                default => $phase
            };
            
            $phaseStats[] = [$phaseDescription, $records->count()];
        }

        $this->newLine();
        $this->table(['Fase Atual', 'Registros'], $phaseStats);
    }

    /**
     * Mostrar registros afetados
     */
    private function showAffectedRecords($affected): void
    {
        $this->newLine();
        $this->info('📋 REGISTROS AFETADOS (primeiros 20):');
        
        $tableData = [];
        foreach ($affected->take(20) as $record) {
            $phaseDisplay = match($record->enrichment_phase) {
                TireCalibration::PHASE_ARTICLE_GENERATED => '⏳ Aguardando',
                TireCalibration::PHASE_CLAUDE_COMPLETED => '✅ Completo',
                default => $record->enrichment_phase
            };
            
            $tableData[] = [
                substr($record->_id, -8),
                $record->vehicle_make . ' ' . $record->vehicle_model,
                $record->main_category ?? 'N/A',
                $phaseDisplay,
                $record->claude_api_calls ?? 0
            ];
        }

        $this->table([
            'ID (últimos 8)',
            'Veículo',
            'Categoria',
            'Fase Atual',
            'API Calls'
        ], $tableData);
        
        if ($affected->count() > 20) {
            $this->line("... e mais " . ($affected->count() - 20) . " registros");
        }
    }

    /**
     * Aplicar correções otimizadas
     */
    private function applyOptimizedCorrections($affected): array
    {
        $results = [
            'corrected' => 0,
            'errors' => 0,
            'details' => [],
            'claude_resets' => 0,
            'phase2_only' => 0
        ];

        $progressBar = $this->output->createProgressBar($affected->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %message%');
        $progressBar->setMessage('Iniciando correções...');
        $progressBar->start();

        foreach ($affected as $record) {
            $progressBar->setMessage("Corrigindo: {$record->vehicle_make} {$record->vehicle_model}");
            
            try {
                $correctionResult = $this->correctSingleRecord($record);
                
                if ($correctionResult['success']) {
                    $results['corrected']++;
                    $this->correctedCount++;
                    
                    if ($correctionResult['claude_reset']) {
                        $results['claude_resets']++;
                    } else {
                        $results['phase2_only']++;
                    }
                } else {
                    $results['errors']++;
                    $this->errorCount++;
                    $results['details'][] = $correctionResult['error'];
                }

            } catch (\Exception $e) {
                $results['errors']++;
                $this->errorCount++;
                $results['details'][] = "ID {$record->_id}: {$e->getMessage()}";
                
                Log::error('FixPhase2TemplateCommand: Erro individual', [
                    'record_id' => $record->_id,
                    'vehicle' => $record->vehicle_make . ' ' . $record->vehicle_model,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->setMessage('Correções concluídas!');
        $progressBar->finish();
        $this->newLine(2);

        return $results;
    }

    /**
     * Corrigir um registro específico - ESTRATÉGIA OTIMIZADA
     */
    private function correctSingleRecord(TireCalibration $record): array
    {
        try {
            $updates = [];
            $claudeReset = false;

            // 1. SEMPRE corrigir o template na Fase 2 (generated_article)
            if (isset($record->generated_article['template'])) {
                $generatedArticle = $record->generated_article;
                $generatedArticle['template'] = 'tire_calibration_motorcycle';
                $updates['generated_article'] = $generatedArticle;
            }

            // 2. ESTRATÉGIA BASEADA NA FASE ATUAL
            switch ($record->enrichment_phase) {
                
                case TireCalibration::PHASE_ARTICLE_GENERATED:
                    // Caso ideal: ainda não processou Claude
                    // Apenas corrigir template, schedule pegará automaticamente
                    break;
                    
                case TireCalibration::PHASE_CLAUDE_3A_COMPLETED:
                case TireCalibration::PHASE_CLAUDE_COMPLETED:
                    // Já processou Claude com template errado
                    // Resetar para reprocessamento
                    $updates['enrichment_phase'] = TireCalibration::PHASE_ARTICLE_GENERATED;
                    $updates['claude_phase_3a_enhancements'] = null;
                    $updates['claude_phase_3b_enhancements'] = null;
                    $updates['claude_enhancements'] = null;
                    $updates['article_refined'] = null;
                    $updates['claude_refinement_version'] = null;
                    $updates['last_claude_processing'] = null;
                    $updates['claude_processing_started_at'] = null;
                    $claudeReset = true;
                    break;
                    
                default:
                    // Outros casos: apenas corrigir template
                    break;
            }

            // 3. Adicionar metadata de correção
            $updates['phase2_correction'] = [
                'corrected_at' => now(),
                'corrected_from' => 'tire_calibration_car',
                'corrected_to' => 'tire_calibration_motorcycle',
                'reason' => 'motorcycle_using_wrong_template_phase2',
                'corrected_by' => 'FixPhase2TemplateCommand',
                'claude_reset_applied' => $claudeReset,
                'original_phase' => $record->enrichment_phase
            ];

            // 4. Aplicar updates
            $record->update($updates);

            $this->affectedIds[] = (string) $record->_id;

            return [
                'success' => true,
                'id' => $record->_id,
                'vehicle' => $record->vehicle_make . ' ' . $record->vehicle_model,
                'claude_reset' => $claudeReset
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'id' => $record->_id,
                'error' => $e->getMessage(),
                'claude_reset' => false
            ];
        }
    }

    /**
     * Mostrar resultados finais
     */
    private function showResults(array $results): void
    {
        $this->newLine();
        
        if ($results['corrected'] > 0) {
            $this->info("✅ {$results['corrected']} registros corrigidos com sucesso!");
            $this->line("   • {$results['claude_resets']} com reset Claude (reprocessamento)");
            $this->line("   • {$results['phase2_only']} apenas template corrigido");
        }

        if ($results['errors'] > 0) {
            $this->error("❌ {$results['errors']} erro(s) encontrados:");
            foreach (array_slice($results['details'], 0, 5) as $error) {
                $this->line("  • $error");
            }
            
            if (count($results['details']) > 5) {
                $this->line("  • ... e mais " . (count($results['details']) - 5) . " erros");
            }
        }

        // Estatísticas finais
        $this->newLine();
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Registros Identificados', $this->affectedCount],
                ['Registros Corrigidos', $this->correctedCount], 
                ['Erros', $this->errorCount],
                ['Taxa de Sucesso', $this->affectedCount > 0 ? round(($this->correctedCount / $this->affectedCount) * 100, 1) . '%' : '0%'],
                ['Resetados para Reprocessamento', $results['claude_resets']],
                ['Apenas Template Corrigido', $results['phase2_only']],
            ]
        );

        // Log final
        Log::info('FixPhase2TemplateCommand: Correção concluída', [
            'affected_count' => $this->affectedCount,
            'corrected_count' => $this->correctedCount,
            'error_count' => $this->errorCount,
            'claude_resets' => $results['claude_resets'],
            'phase2_only' => $results['phase2_only'],
            'affected_ids' => $this->affectedIds
        ]);
    }

    /**
     * Mostrar orientações pós-correção
     */
    private function showPostCorrectionGuidance(): void
    {
        $this->newLine();
        $this->info('🎯 PRÓXIMOS PASSOS AUTOMÁTICOS:');
        $this->line('');
        $this->line('📅 Schedule automático processará:');
        $this->line('   • FASE 3A: A cada 30 min (conteúdo editorial)');
        $this->line('   • FASE 3B: Após 3A (especificações técnicas)');
        $this->line('');
        $this->info('⏰ Cronograma esperado:');
        $this->line('   • Próxima execução Claude: ' . now()->addMinutes(30)->format('H:i'));
        $this->line('   • Processamento: 1 registro por vez');
        $this->line('   • Conclusão estimada: ' . now()->addMinutes(30 * $this->correctedCount)->format('d/m H:i'));
        $this->line('');
        $this->info('📊 Para monitorar progresso:');
        $this->line('   php artisan tire-calibration:stats');
        $this->line('   tail -f storage/logs/tire-calibration-claude.log');
    }

    /**
     * Mostrar resumo do dry-run
     */
    private function showDryRunSummary($affected): void
    {
        $this->newLine();
        $this->info('🧪 SIMULAÇÃO - CORREÇÕES QUE SERIAM APLICADAS:');
        
        $claudeResetCount = 0;
        $phase2OnlyCount = 0;
        
        foreach ($affected as $record) {
            $action = 'Template corrigido';
            
            if (in_array($record->enrichment_phase, [
                TireCalibration::PHASE_CLAUDE_3A_COMPLETED,
                TireCalibration::PHASE_CLAUDE_COMPLETED
            ])) {
                $action = 'Template corrigido + Reset Claude';
                $claudeResetCount++;
            } else {
                $phase2OnlyCount++;
            }
            
            $this->line("• ID {$record->_id}: {$record->vehicle_make} {$record->vehicle_model}");
            $this->line("  Ação: $action");
            $this->line("  Fase atual: {$record->enrichment_phase}");
        }

        $this->newLine();
        $this->info('📊 RESUMO DA SIMULAÇÃO:');
        $this->line("• Total: {$affected->count()} registros");
        $this->line("• Apenas template: $phase2OnlyCount");
        $this->line("• Template + Reset Claude: $claudeResetCount");
        $this->newLine();
        $this->warn('Para aplicar as correções, execute novamente sem --dry-run');
    }
}
<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Illuminate\Support\Facades\Log;

/**
 * ResetPickupRecordsCommand
 * 
 * Comando específico para resetar registros pickup com estrutura de refinamento quebrada
 * e recolocá-los na fila de reprocessamento.
 * 
 * PROBLEMA IDENTIFICADO:
 * - Registros pickup processados têm duplicação massiva de dados
 * - 4 cópias do mesmo conteúdo em campos diferentes
 * - Estrutura de refinamento fragmentada e redundante
 * - generated_article deveria ser a base, article_refined o resultado final
 * 
 * SOLUÇÃO:
 * - Limpar campos de refinamento Claude
 * - Manter generated_article como base
 * - Resetar enrichment_phase para article_generated
 * - Permitir reprocessamento com estrutura corrigida
 * 
 * USO:
 * php artisan calibration:reset-pickup --dry-run
 * php artisan calibration:reset-pickup --limit=10 --force
 * php artisan calibration:reset-pickup --all-pickup
 */
class ResetPickupRecordsCommand extends Command
{
    protected $signature = 'calibration:reset-pickup
                            {--limit=20 : Limite de registros para resetar}
                            {--all-pickup : Resetar todos os pickups (ignora limite)}
                            {--dry-run : Simular sem fazer alterações}
                            {--force : Executar sem confirmação}
                            {--debug : Exibir detalhes dos registros}';

    protected $description = 'Resetar registros pickup com estrutura de refinamento quebrada para reprocessamento';

    private int $analyzedCount = 0;
    private int $resetCount = 0;
    private int $skippedCount = 0;
    private array $resetDetails = [];

    public function handle(): int
    {
        $this->info('🔧 RESET DE REGISTROS PICKUP COM ESTRUTURA QUEBRADA');
        $this->newLine();

        try {
            $config = $this->getConfig();
            $this->displayConfig($config);

            // Buscar registros pickup candidatos
            $candidates = $this->getCandidates($config);
            
            if ($candidates->isEmpty()) {
                $this->warn('Nenhum registro pickup encontrado para reset');
                return self::SUCCESS;
            }

            $this->info("📊 Encontrados {$candidates->count()} registros pickup para análise");
            $this->newLine();

            // Confirmar operação se não for dry-run
            if (!$config['dry_run'] && !$config['force']) {
                if (!$this->confirmReset($candidates->count())) {
                    $this->info('Operação cancelada pelo usuário');
                    return self::SUCCESS;
                }
            }

            // Analisar e resetar registros
            $this->analyzeAndResetRecords($candidates, $config);

            // Exibir resultados
            $this->displayResults();

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Erro no comando: ' . $e->getMessage());
            Log::error('ResetPickupRecordsCommand: Erro', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Obter configuração do comando
     */
    private function getConfig(): array
    {
        return [
            'limit' => $this->option('all-pickup') ? null : (int) $this->option('limit'),
            'all_pickup' => $this->option('all-pickup'),
            'dry_run' => $this->option('dry-run'),
            'force' => $this->option('force'),
            'debug' => $this->option('debug'),
        ];
    }

    /**
     * Exibir configuração
     */
    private function displayConfig(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO:');
        $this->line('   • Template alvo: tire_calibration_pickup');
        $this->line('   • Limite: ' . ($config['all_pickup'] ? 'TODOS' : $config['limit']));
        $this->line('   • Modo: ' . ($config['dry_run'] ? '🔍 SIMULAÇÃO' : '💾 EXECUÇÃO REAL'));
        $this->line('   • Confirmação: ' . ($config['force'] ? 'PULAR' : 'SOLICITAR'));
        $this->line('   • Debug: ' . ($config['debug'] ? 'SIM' : 'NÃO'));
        $this->newLine();

        $this->info('🎯 AÇÃO:');
        $this->line('   1. Limpar claude_phase_3a_enhancements');
        $this->line('   2. Limpar claude_phase_3b_enhancements');
        $this->line('   3. Limpar article_refined');
        $this->line('   4. Limpar claude_enhancements');
        $this->line('   5. Resetar enrichment_phase para "article_generated"');
        $this->line('   6. Manter generated_article intacto');
        $this->newLine();
    }

    /**
     * Buscar registros pickup candidatos
     */
    private function getCandidates(array $config)
    {
        $query = TireCalibration::where('main_category', 'pickup')
            ->where('version', 'v2')
            ->whereIn('enrichment_phase', [
                'claude_3a_completed',
                'claude_3b_completed', 
                'claude_completed'
            ])
            ->whereNotNull('generated_article');

        // Focar em registros que já foram refinados e têm duplicação
        $query->where(function($q) {
            $q->whereNotNull('claude_phase_3a_enhancements')
              ->orWhereNotNull('claude_phase_3b_enhancements')
              ->orWhereNotNull('article_refined')
              ->orWhereNotNull('claude_enhancements');
        });

        if (!$config['all_pickup'] && $config['limit']) {
            $query->limit($config['limit']);
        }

        return $query->orderBy('updated_at', 'desc')->get();
    }

    /**
     * Confirmar operação com usuário
     */
    private function confirmReset(int $count): bool
    {
        $this->warn("⚠️ ATENÇÃO: Esta operação irá resetar {$count} registros pickup.");
        $this->line('   • Dados de refinamento Claude serão removidos');
        $this->line('   • Registros voltarão para fila de reprocessamento');
        $this->line('   • generated_article será mantido intacto');
        $this->newLine();

        return $this->confirm('Confirma a execução?', false);
    }

    /**
     * Analisar e resetar registros
     */
    private function analyzeAndResetRecords($candidates, array $config): void
    {
        $progressBar = $this->output->createProgressBar($candidates->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->start();

        foreach ($candidates as $record) {
            $this->analyzedCount++;
            
            $vehicleInfo = "{$record->vehicle_make} {$record->vehicle_model}";
            $progressBar->setMessage($vehicleInfo);

            try {
                $analysis = $this->analyzeRecord($record);
                
                if ($analysis['needs_reset']) {
                    if (!$config['dry_run']) {
                        $this->resetRecord($record);
                    }
                    
                    $this->resetCount++;
                    $this->resetDetails[] = [
                        'vehicle' => $vehicleInfo,
                        'phase' => $record->enrichment_phase,
                        'issues' => $analysis['issues'],
                        'action' => $config['dry_run'] ? 'SIMULADO' : 'RESETADO'
                    ];
                } else {
                    $this->skippedCount++;
                }

                if ($config['debug'] && $analysis['needs_reset']) {
                    $this->newLine();
                    $this->debugRecord($record, $analysis);
                }

            } catch (\Exception $e) {
                $this->error("Erro processando {$vehicleInfo}: " . $e->getMessage());
                Log::error('ResetPickupRecordsCommand: Erro no registro', [
                    'id' => $record->_id,
                    'vehicle' => $vehicleInfo,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * Analisar registro individual
     */
    private function analyzeRecord(TireCalibration $record): array
    {
        $issues = [];
        $needsReset = false;

        // Verificar se tem generated_article válido
        if (empty($record->generated_article)) {
            $issues[] = 'Sem generated_article válido';
            return ['needs_reset' => false, 'issues' => $issues];
        }

        // Verificar duplicações problemáticas
        $hasDuplications = false;

        // Verificar se tem conteúdo em múltiplos lugares
        if (!empty($record->claude_phase_3a_enhancements) && !empty($record->article_refined)) {
            $issues[] = 'Duplicação: claude_phase_3a_enhancements + article_refined';
            $hasDuplications = true;
        }

        if (!empty($record->claude_enhancements) && !empty($record->article_refined)) {
            $issues[] = 'Duplicação: claude_enhancements + article_refined';
            $hasDuplications = true;
        }

        // Verificar se article_refined tem size excessivo (indicativo de duplicação)
        if (!empty($record->article_refined)) {
            $refinedSize = strlen(json_encode($record->article_refined));
            if ($refinedSize > 10000) { // > 10KB indica duplicação excessiva
                $issues[] = "article_refined muito grande: {$refinedSize} bytes";
                $hasDuplications = true;
            }
        }

        // Verificar se tem template pickup
        $generatedArticle = is_array($record->generated_article) 
            ? $record->generated_article 
            : json_decode($record->generated_article, true);
            
        if (($generatedArticle['template'] ?? '') === 'tire_calibration_pickup') {
            $issues[] = 'Template pickup confirmado';
            
            // Se tem template pickup E duplicações, precisa reset
            if ($hasDuplications) {
                $needsReset = true;
            }
        }

        return [
            'needs_reset' => $needsReset,
            'issues' => $issues
        ];
    }

    /**
     * Resetar registro individual
     */
    private function resetRecord(TireCalibration $record): void
    {
        $record->update([
            // Limpar todos os campos de refinamento Claude
            'claude_phase_3a_enhancements' => null,
            'claude_phase_3b_enhancements' => null,
            'article_refined' => null,
            'claude_enhancements' => null,
            
            // Resetar fase para reprocessamento
            'enrichment_phase' => TireCalibration::PHASE_ARTICLE_GENERATED,
            
            // Limpar metadados de processamento Claude
            'claude_refinement_version' => null,
            'claude_completed_at' => null,
            'phase_3a_completed_at' => null,
            'phase_3b_completed_at' => null,
            'last_claude_processing' => null,
            'claude_processing_history' => null,
            
            // Resetar contadores
            'claude_api_calls' => 0,
            'claude_improvement_score' => null,
            
            // Limpar erros
            'last_error' => null,
            
            // Resetar tentativas para permitir reprocessamento
            'processing_attempts' => 0,
        ]);

        Log::info('ResetPickupRecordsCommand: Registro resetado', [
            'id' => $record->_id,
            'vehicle' => "{$record->vehicle_make} {$record->vehicle_model}",
            'old_phase' => $record->enrichment_phase,
            'new_phase' => TireCalibration::PHASE_ARTICLE_GENERATED
        ]);
    }

    /**
     * Debug detalhado do registro
     */
    private function debugRecord(TireCalibration $record, array $analysis): void
    {
        $vehicleInfo = "{$record->vehicle_make} {$record->vehicle_model}";
        
        $this->line("🔍 DEBUG: {$vehicleInfo}");
        $this->line("   • Fase atual: {$record->enrichment_phase}");
        $this->line("   • Issues encontradas: " . count($analysis['issues']));
        
        foreach ($analysis['issues'] as $issue) {
            $this->line("     - {$issue}");
        }
        
        // Mostrar tamanhos dos campos
        $sizes = [
            'generated_article' => strlen(json_encode($record->generated_article ?? [])),
            'claude_phase_3a_enhancements' => strlen(json_encode($record->claude_phase_3a_enhancements ?? [])),
            'article_refined' => strlen(json_encode($record->article_refined ?? [])),
            'claude_enhancements' => strlen(json_encode($record->claude_enhancements ?? [])),
        ];
        
        $this->line("   • Tamanhos dos campos:");
        foreach ($sizes as $field => $size) {
            if ($size > 0) {
                $this->line("     - {$field}: {$size} bytes");
            }
        }
        
        $this->newLine();
    }

    /**
     * Exibir resultados finais
     */
    private function displayResults(): void
    {
        $this->info('📊 RESULTADOS DA OPERAÇÃO:');
        $this->newLine();

        $this->table(['Métrica', 'Quantidade'], [
            ['Registros analisados', $this->analyzedCount],
            ['Registros resetados', $this->resetCount],
            ['Registros ignorados', $this->skippedCount],
            ['Taxa de reset', $this->analyzedCount > 0 ? round(($this->resetCount / $this->analyzedCount) * 100, 1) . '%' : '0%']
        ]);

        if (!empty($this->resetDetails)) {
            $this->newLine();
            $this->info('🔧 REGISTROS PROCESSADOS:');
            
            foreach ($this->resetDetails as $detail) {
                $this->line("• {$detail['vehicle']} ({$detail['phase']}) - {$detail['action']}");
                
                if (count($detail['issues']) > 0) {
                    foreach ($detail['issues'] as $issue) {
                        $this->line("  - {$issue}");
                    }
                }
            }
        }

        $this->newLine();
        
        if ($this->resetCount > 0) {
            $this->info('✅ PRÓXIMOS PASSOS:');
            $this->line('   1. Execute: php artisan tire-calibration:refine-3a --limit=5');
            $this->line('   2. Monitore se a estrutura fica correta sem duplicações');
            $this->line('   3. Execute: php artisan tire-calibration:refine-3b --limit=5');
            $this->line('   4. Verifique se article_refined fica limpo');
        } else {
            $this->info('ℹ️ Nenhum registro precisou de reset');
        }
    }
}
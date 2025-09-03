<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Application\Services\PickupArticleFixService;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Carbon\Carbon;

/**
 * FixIncompletePickupArticlesCommand - Corrigir pickups com article_refined incompleto
 * 
 * Este command identifica e corrige pickups que passaram pelo processo V4
 * mas ficaram com conteúdo incompleto na seção 'content' do article_refined.
 * 
 * PROBLEMA IDENTIFICADO:
 * - Pickups com claude_refinement_version = "v4_completed"
 * - Mas article_refined.content possui apenas 'introducao' cortada
 * - Faltam: perguntas_frequentes, consideracoes_finais, especificacoes_por_versao
 * 
 * ESTRATÉGIA:
 * 1. Alterar flag: v4_completed -> v4_pickup_fixing
 * 2. Reprocessar conteúdo usando template Toyota Hilux como base
 * 3. Restaurar flag: v4_pickup_fixing -> v4_completed
 * 
 * USO:
 * php artisan tire-calibration:fix-incomplete-pickups
 * php artisan tire-calibration:fix-incomplete-pickups --dry-run
 * php artisan tire-calibration:fix-incomplete-pickups --limit=5
 * php artisan tire-calibration:fix-incomplete-pickups --force-all
 * 
 * @author Claude Sonnet 4
 * @version 1.0
 */
class FixIncompletePickupArticlesCommand extends Command
{
    /**
     * Signature do command
     */
    protected $signature = 'tire-calibration:fix-incomplete-pickups
                            {--dry-run : Simular apenas, não executar alterações}
                            {--limit=1 : Número máximo de registros a processar}
                            {--force-all : Forçar correção mesmo em pickups que parecem completos}
                            {--category=pickup : Categoria específica (pickup, truck)}
                            {--debug : Mostrar informações detalhadas de debug}';

    /**
     * Descrição do command
     */
    protected $description = 'Corrigir pickups com article_refined incompleto usando Claude 3.5 Sonnet';

    private PickupArticleFixService $fixService;
    private int $processedCount = 0;
    private int $fixedCount = 0;
    private int $skippedCount = 0;
    private int $errorCount = 0;
    private array $errors = [];

    public function __construct(PickupArticleFixService $fixService)
    {
        parent::__construct();
        $this->fixService = $fixService;
    }

    /**
     * Executar o command
     */
    public function handle(): int
    {
        $config = $this->getConfiguration();
        
        $this->displayHeader($config);
        
        try {
            // 1. Encontrar pickups problemáticos
            $candidates = $this->findIncompletePickups($config);
            
            if ($candidates->isEmpty()) {
                $this->info('✅ Nenhum pickup incompleto encontrado!');
                return self::SUCCESS;
            }

            $this->displayCandidatesSummary($candidates, $config);
            
            if (!$config['dry_run'] && !$this->confirm('Continuar com a correção?')) {
                $this->info('❌ Operação cancelada pelo usuário.');
                return self::SUCCESS;
            }

            // 2. Processar candidatos
            $this->processCandidates($candidates, $config);
            
            // 3. Exibir resultados finais
            $this->displayFinalResults();
            
            return $this->fixedCount > 0 ? self::SUCCESS : self::FAILURE;
            
        } catch (\Exception $e) {
            $this->error('❌ Erro crítico: ' . $e->getMessage());
            
            if ($config['debug']) {
                $this->error('Stack trace: ' . $e->getTraceAsString());
            }
            
            return self::FAILURE;
        }
    }

    /**
     * Configuração baseada nas opções
     */
    private function getConfiguration(): array
    {
        return [
            'dry_run' => $this->option('dry-run'),
            'limit' => (int) $this->option('limit'),
            'force_all' => $this->option('force-all'),
            'category' => $this->option('category'),
            'debug' => $this->option('debug'),
        ];
    }

    /**
     * Exibir header do command
     */
    private function displayHeader(array $config): void
    {
        $this->info('🔧 CORREÇÃO DE PICKUPS INCOMPLETOS - V4');
        $this->info('🤖 Powered by Claude 3.5 Sonnet');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        $this->info('⚙️  CONFIGURAÇÃO:');
        $this->line("   • Limite: {$config['limit']} registros");
        $this->line("   • Categoria: {$config['category']}");
        $this->line("   • Modo: " . ($config['dry_run'] ? '🔍 DRY-RUN' : '💾 PRODUÇÃO'));
        $this->line("   • Força tudo: " . ($config['force_all'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Debug: " . ($config['debug'] ? '✅ SIM' : '❌ NÃO'));
        $this->newLine();

        $this->info('🎯 PROBLEMA ALVO:');
        $this->line('   • claude_refinement_version = "v4_completed"');
        $this->line('   • MAS article_refined.content incompleto');
        $this->line('   • Faltam: FAQs, considerações finais, especificações');
        $this->newLine();
    }

    /**
     * Encontrar pickups com conteúdo incompleto
     */
    private function findIncompletePickups(array $config)
    {
        $this->info('🔍 Procurando pickups incompletos...');
        
        $query = TireCalibration::where('main_category', $config['category'])
            ->where('claude_refinement_version', 'v4_completed')
            ->whereNotNull('article_refined');

        if (!$config['force_all']) {
            // Adicionar lógica para detectar conteúdo incompleto
            $query->where(function($q) {
                // MongoDB query para verificar se article_refined.content está incompleto
                $q->whereRaw([
                    '$or' => [
                        ['article_refined.content.perguntas_frequentes' => ['$exists' => false]],
                        ['article_refined.content.consideracoes_finais' => ['$exists' => false]],
                        ['article_refined.content.especificacoes_por_versao' => ['$exists' => false]],
                    ]
                ]);
            });
        }

        $candidates = $query->orderBy('updated_at', 'desc')
            ->limit($config['limit'])
            ->get();
        
        $this->line("   ✅ Encontrados: {$candidates->count()} candidatos");
        
        return $candidates;
    }

    /**
     * Exibir resumo dos candidatos
     */
    private function displayCandidatesSummary($candidates, array $config): void
    {
        if ($config['debug']) {
            $this->info('📋 CANDIDATOS ENCONTRADOS:');
            
            foreach ($candidates->take(3) as $candidate) {
                $contentStatus = $this->analyzeContentCompleteness($candidate);
                
                $this->line("   • {$candidate->vehicle_make} {$candidate->vehicle_model}");
                $this->line("     - ID: {$candidate->_id}");
                $this->line("     - Status: {$contentStatus['summary']}");
                
                if ($config['debug']) {
                    $this->line("     - Introdução: {$contentStatus['introducao']}");
                    $this->line("     - FAQs: {$contentStatus['faqs']}");
                    $this->line("     - Considerações: {$contentStatus['consideracoes']}");
                }
            }
            
            if ($candidates->count() > 3) {
                $remaining = $candidates->count() - 3;
                $this->line("   ... e mais {$remaining} registros");
            }
            
            $this->newLine();
        }
    }

    /**
     * Analisar completude do conteúdo
     */
    private function analyzeContentCompleteness(TireCalibration $calibration): array
    {
        $content = $calibration->article_refined['content'] ?? [];
        
        $introducao = !empty($content['introducao']) ? 
            (strlen($content['introducao']) > 100 ? 'Completa' : 'Truncada') : 
            'Ausente';
            
        $faqs = !empty($content['perguntas_frequentes']) ? 'Presente' : 'Ausente';
        $consideracoes = !empty($content['consideracoes_finais']) ? 'Presente' : 'Ausente';
        $especificacoes = !empty($content['especificacoes_por_versao']) ? 'Presente' : 'Ausente';
        
        $problems = [];
        if ($introducao !== 'Completa') $problems[] = 'Intro';
        if ($faqs === 'Ausente') $problems[] = 'FAQs';
        if ($consideracoes === 'Ausente') $problems[] = 'Conclusão';
        if ($especificacoes === 'Ausente') $problems[] = 'Specs';
        
        $summary = empty($problems) ? 
            'Aparentemente Completo' : 
            'Incompleto (' . implode(', ', $problems) . ')';
            
        return [
            'summary' => $summary,
            'introducao' => $introducao,
            'faqs' => $faqs,
            'consideracoes' => $consideracoes,
            'especificacoes' => $especificacoes,
            'problems' => $problems
        ];
    }

    /**
     * Processar candidatos
     */
    private function processCandidates($candidates, array $config): void
    {
        $this->info('🚀 INICIANDO CORREÇÕES...');
        $this->newLine();

        $progressBar = $this->output->createProgressBar($candidates->count());
        $progressBar->start();

        foreach ($candidates as $candidate) {
            $this->processedCount++;
            
            try {
                $result = $this->processCandidate($candidate, $config);
                
                if ($result['success']) {
                    $this->fixedCount++;
                } else {
                    $this->skippedCount++;
                }
                
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'vehicle' => "{$candidate->vehicle_make} {$candidate->vehicle_model}",
                    'error' => $e->getMessage(),
                    'id' => $candidate->_id,
                ];
                
                Log::error('Erro ao corrigir pickup', [
                    'id' => $candidate->_id,
                    'vehicle' => "{$candidate->vehicle_make} {$candidate->vehicle_model}",
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            $progressBar->advance();
            
            // Delay entre processamentos para não sobrecarregar API
            if (!$config['dry_run']) {
                sleep(2);
            }
        }
        
        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * Processar um candidato específico
     */
    private function processCandidate(TireCalibration $candidate, array $config): array
    {
        if ($config['dry_run']) {
            return [
                'success' => true,
                'action' => 'simulated',
                'message' => 'Simulação - seria corrigido'
            ];
        }

        // 1. Alterar flag para permitir reprocessamento
        $originalFlag = $candidate->claude_refinement_version;
        $candidate->update([
            'claude_refinement_version' => 'v4_pickup_fixing'
        ]);

        try {
            // 2. Executar correção usando o service
            $result = $this->fixService->fixIncompletePickupContent($candidate);
            
            // 3. Restaurar flag para completed
            $candidate->update([
                'claude_refinement_version' => 'v4_completed',
                'last_pickup_fix_at' => now(),
                'pickup_fix_notes' => 'Conteúdo corrigido via FixIncompletePickupArticlesCommand'
            ]);
            
            return [
                'success' => true,
                'action' => 'fixed',
                'message' => $result['message'] ?? 'Corrigido com sucesso'
            ];
            
        } catch (\Exception $e) {
            // Restaurar flag original em caso de erro
            $candidate->update([
                'claude_refinement_version' => $originalFlag
            ]);
            
            throw $e;
        }
    }

    /**
     * Exibir resultados finais
     */
    private function displayFinalResults(): void
    {
        $this->info('📊 RESULTADOS FINAIS:');
        $this->line("   • Total processados: {$this->processedCount}");
        $this->line("   • ✅ Corrigidos: {$this->fixedCount}");
        $this->line("   • ⏭️  Pulados: {$this->skippedCount}");
        $this->line("   • ❌ Erros: {$this->errorCount}");
        $this->newLine();

        if ($this->errorCount > 0) {
            $this->error('❌ ERROS ENCONTRADOS:');
            foreach (array_slice($this->errors, 0, 5) as $error) {
                $this->line("   • {$error['vehicle']}: {$error['error']}");
            }
            
            if (count($this->errors) > 5) {
                $remaining = count($this->errors) - 5;
                $this->line("   ... e mais {$remaining} erros (verifique logs)");
            }
            $this->newLine();
        }

        if ($this->fixedCount > 0) {
            $this->info('✅ PRÓXIMOS PASSOS:');
            $this->line('   1. Execute: php artisan tire-calibration:stats (verificar estatísticas)');
            $this->line('   2. Monitore: Verificar se os pickups corrigidos estão completos');
            $this->line('   3. Teste: Validar alguns artigos corrigidos manualmente');
        }

        $this->newLine();
        $this->info('🏁 Correção de pickups concluída!');
    }
}
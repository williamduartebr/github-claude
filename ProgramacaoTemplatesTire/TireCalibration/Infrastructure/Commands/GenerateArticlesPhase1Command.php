<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Src\ContentGeneration\TireCalibration\Application\Services\ArticleGenerationService;
use Carbon\Carbon;

/**
 * GenerateArticlesPhase1Command - FASES 1+2: Mapeamento VehicleData → JSON estruturado
 * 
 * Command principal do módulo TireCalibration que executa:
 * - FASE 1: Validação de dados VehicleData (dados já processados)
 * - FASE 2: Mapeamento para JSON estruturado de artigo
 * 
 * ⚠️ FOCO: Mapear dados existentes do VehicleData, NÃO gerar conteúdo novo
 * 
 * USO:
 * php artisan tire-calibration:generate-articles
 * php artisan tire-calibration:generate-articles --limit=10 --dry-run
 * php artisan tire-calibration:generate-articles --category=sedan --force
 * 
 * @author Claude Sonnet 4
 * @version 1.0
 */
class GenerateArticlesPhase1Command extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tire-calibration:generate-articles
                            {--limit=50 : Número máximo de artigos a processar}
                            {--category= : Filtrar por categoria específica (sedan, suv, motorcycle, etc)}
                            {--dry-run : Simular execução sem salvar}
                            {--force : Forçar re-processamento de artigos já gerados}
                            {--min-quality=70 : Score mínimo de qualidade dos dados (0-100)}';

    /**
     * The console command description.
     */
    protected $description = 'FASE 1+2: Mapear dados VehicleData para JSON estruturado de artigos de calibragem';

    private ArticleGenerationService $articleService;
    
    public function __construct(ArticleGenerationService $articleService)
    {
        parent::__construct();
        $this->articleService = $articleService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);
        
        $this->info('🚀 INICIANDO GERAÇÃO DE ARTIGOS - FASE 1+2 (VehicleData → JSON estruturado)');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            // 1. Validar configurações
            $config = $this->validateAndGetConfig();
            $this->displayConfig($config);

            // 2. Buscar TireCalibration candidates
            $candidates = $this->getCandidateCalibrations($config);
            
            if ($candidates->isEmpty()) {
                $this->warn('❌ Nenhuma TireCalibration encontrada com os critérios especificados.');
                return self::SUCCESS;
            }

            $this->info("📊 Encontradas {$candidates->count()} TireCalibration(s) para processamento");
            $this->newLine();

            // 3. Processar TireCalibrations
            $results = $this->processCandidates($candidates, $config);

            // 4. Exibir estatísticas finais
            $this->displayFinalStats($results, microtime(true) - $startTime);

            Log::info('GenerateArticlesPhase1Command: Execução concluída', [
                'total_processed' => $results['processed'],
                'success_count' => $results['success'],
                'error_count' => $results['errors'],
                'duration_seconds' => round(microtime(true) - $startTime, 2),
                'config' => $config
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro durante execução: ' . $e->getMessage());
            Log::error('GenerateArticlesPhase1Command: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Validar parâmetros e configurações
     */
    private function validateAndGetConfig(): array
    {
        $limit = (int) $this->option('limit');
        $minQuality = (int) $this->option('min-quality');
        $category = $this->option('category');

        // Validações
        if ($limit <= 0 || $limit > 1000) {
            throw new \InvalidArgumentException('Limite deve estar entre 1 e 1000');
        }

        if ($minQuality < 0 || $minQuality > 100) {
            throw new \InvalidArgumentException('Score de qualidade deve estar entre 0 e 100');
        }

        $validCategories = ['sedan', 'suv', 'hatch', 'pickup', 'motorcycle', 'motorcycle_street', 'motorcycle_scooter', 'car_electric', 'truck'];
        if ($category && !in_array($category, $validCategories)) {
            throw new \InvalidArgumentException("Categoria inválida. Disponíveis: " . implode(', ', $validCategories));
        }

        return [
            'limit' => $limit,
            'category' => $category,
            'dry_run' => $this->option('dry-run'),
            'force' => $this->option('force'),
            'min_quality' => $minQuality,
        ];
    }

    /**
     * Exibir configuração da execução
     */
    private function displayConfig(array $config): void
    {
        $this->info('⚙️  CONFIGURAÇÃO:');
        $this->line("   • Limite: {$config['limit']} registros");
        $this->line("   • Categoria: " . ($config['category'] ?? 'Todas'));
        $this->line("   • Qualidade mínima: {$config['min_quality']}%");
        $this->line("   • Modo: " . ($config['dry_run'] ? '🔍 DRY-RUN (simulação)' : '💾 PRODUÇÃO'));
        $this->line("   • Reprocessar: " . ($config['force'] ? '✅ SIM' : '❌ NÃO'));
        $this->newLine();
    }

    /**
     * Buscar TireCalibrations candidatas para processamento
     */
    private function getCandidateCalibrations(array $config)
    {
        $query = TireCalibration::query()
            ->where('enrichment_phase', TireCalibration::PHASE_VEHICLE_ENRICHED)
            ->whereNotNull('vehicle_make')
            ->whereNotNull('vehicle_model')
            ->whereNotNull('vehicle_year')
            ->whereNotNull('main_category');

        // Filtrar por qualidade dos dados
        if ($config['min_quality'] > 0) {
            $query->where('data_completeness_score', '>=', $config['min_quality']);
        }

        // Filtrar por categoria específica
        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        // Se não forçar, excluir já processados
        if (!$config['force']) {
            $query->whereNull('generated_article');
        }

        return $query->limit($config['limit'])->get();
    }

    /**
     * Processar candidates
     */
    private function processCandidates($candidates, array $config): array
    {
        $results = [
            'processed' => 0,
            'success' => 0,
            'errors' => 0,
            'skipped' => 0,
            'error_details' => []
        ];

        $progressBar = $this->output->createProgressBar($candidates->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Iniciando...');

        foreach ($candidates as $calibration) {
            $results['processed']++;
            
            try {
                $vehicleInfo = "{$calibration->vehicle_make} {$calibration->vehicle_model} {$calibration->vehicle_year}";
                $progressBar->setMessage("Processando: {$vehicleInfo}");

                // Validar pré-requisitos específicos
                if (!$this->validateCalibrationPrerequisites($calibration)) {
                    $results['skipped']++;
                    $progressBar->advance();
                    continue;
                }

                // Gerar artigo estruturado via ArticleGenerationService
                $articleData = $this->articleService->generateCalibrationArticle($calibration);

                if (!$config['dry_run']) {
                    // Salvar artigo gerado
                    $calibration->generated_article = $articleData;
                    $calibration->enrichment_phase = TireCalibration::PHASE_ARTICLE_GENERATED;
                    $calibration->last_processing_at = now();
                    $calibration->processing_history = array_merge(
                        $calibration->processing_history ?? [],
                        [[
                            'phase' => TireCalibration::PHASE_ARTICLE_GENERATED,
                            'processed_at' => now()->toISOString(),
                            'method' => 'vehicle_data_mapping',
                            'word_count' => $articleData['generation_metadata']['word_count'] ?? 0,
                            'template' => $articleData['template'] ?? null
                        ]]
                    );
                    $calibration->save();
                }

                $results['success']++;
                
                if ($config['dry_run']) {
                    $this->line("✅ [DRY-RUN] {$vehicleInfo} - Template: {$articleData['template']} - Palavras: {$articleData['generation_metadata']['word_count']}");
                }

            } catch (\Exception $e) {
                $results['errors']++;
                $results['error_details'][] = [
                    'vehicle' => $vehicleInfo ?? 'N/A',
                    'error' => $e->getMessage()
                ];

                if (!$config['dry_run']) {
                    $calibration->enrichment_phase = TireCalibration::PHASE_FAILED;
                    $calibration->last_error = $e->getMessage();
                    $calibration->error_count = ($calibration->error_count ?? 0) + 1;
                    $calibration->save();
                }

                $this->newLine();
                $this->error("❌ Erro em " . ($vehicleInfo ?? 'veículo desconhecido') . ": {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        return $results;
    }

    /**
     * Validar pré-requisitos específicos da TireCalibration
     */
    private function validateCalibrationPrerequisites(TireCalibration $calibration): bool
    {
        // Verificar se tem dados básicos do veículo
        if (empty($calibration->vehicle_basic_data) && empty($calibration->pressure_specifications)) {
            Log::warning('GenerateArticlesPhase1Command: TireCalibration sem dados VehicleData mapeados', [
                'tire_calibration_id' => $calibration->_id,
                'vehicle' => "{$calibration->vehicle_make} {$calibration->vehicle_model} {$calibration->vehicle_year}"
            ]);
            return false;
        }

        // Verificar se está na fase correta
        if ($calibration->enrichment_phase !== TireCalibration::PHASE_VEHICLE_ENRICHED) {
            Log::warning('GenerateArticlesPhase1Command: TireCalibration em fase incorreta', [
                'tire_calibration_id' => $calibration->_id,
                'current_phase' => $calibration->enrichment_phase,
                'expected_phase' => TireCalibration::PHASE_VEHICLE_ENRICHED
            ]);
            return false;
        }

        return true;
    }

    /**
     * Exibir estatísticas finais
     */
    private function displayFinalStats(array $results, float $duration): void
    {
        $this->info('📈 ESTATÍSTICAS FINAIS:');
        $this->newLine();

        // Estatísticas principais
        $this->line("✅ <fg=green>Processados com sucesso:</fg=green> {$results['success']}");
        $this->line("❌ <fg=red>Erros:</fg=red> {$results['errors']}");
        $this->line("⏭️  <fg=yellow>Ignorados:</fg=yellow> {$results['skipped']}");
        $this->line("📊 <fg=blue>Total processado:</fg=blue> {$results['processed']}");
        $this->newLine();

        // Performance
        $avgTime = $results['processed'] > 0 ? round($duration / $results['processed'], 2) : 0;
        $this->line("⏱️  <fg=cyan>Tempo total:</fg=cyan> " . round($duration, 2) . "s");
        $this->line("🔄 <fg=cyan>Tempo médio por artigo:</fg=cyan> {$avgTime}s");
        
        // Taxa de sucesso
        $successRate = $results['processed'] > 0 ? round(($results['success'] / $results['processed']) * 100, 1) : 0;
        $this->line("🎯 <fg=magenta>Taxa de sucesso:</fg=magenta> {$successRate}%");
        $this->newLine();

        // Mostrar erros se houver
        if (!empty($results['error_details'])) {
            $this->error('🚨 DETALHES DOS ERROS:');
            foreach (array_slice($results['error_details'], 0, 5) as $error) {
                $this->line("   • {$error['vehicle']}: {$error['error']}");
            }
            
            if (count($results['error_details']) > 5) {
                $remaining = count($results['error_details']) - 5;
                $this->line("   ... e mais {$remaining} erro(s). Verifique os logs para detalhes.");
            }
            $this->newLine();
        }

        // Próximos passos
        if ($results['success'] > 0) {
            $this->info('🎉 PRÓXIMOS PASSOS:');
            $this->line('   1. Execute: php artisan tire-calibration:refine-with-claude');
            $this->line('   2. Para estatísticas: php artisan tire-calibration:stats');
            $this->newLine();
        }

        // Recommendations
        if ($results['errors'] > $results['success']) {
            $this->warn('⚠️  ATENÇÃO: Muitos erros detectados. Verifique:');
            $this->line('   • Qualidade dos dados VehicleData');
            $this->line('   • Logs do sistema para mais detalhes');
            $this->line('   • Considere aumentar --min-quality');
        }

        if ($results['skipped'] > 0) {
            $this->info('ℹ️  REGISTROS IGNORADOS: Possíveis causas:');
            $this->line('   • Dados VehicleData não mapeados corretamente');
            $this->line('   • TireCalibration em fase incorreta');
            $this->line('   • Use --force para reprocessar');
        }
    }

    /**
     * Obter estatísticas do sistema
     */
    public function getSystemStats(): array
    {
        return [
            'ready_for_processing' => TireCalibration::where('enrichment_phase', TireCalibration::PHASE_VEHICLE_ENRICHED)->count(),
            'articles_generated' => TireCalibration::where('enrichment_phase', TireCalibration::PHASE_ARTICLE_GENERATED)->count(),
            'failed_processing' => TireCalibration::where('enrichment_phase', TireCalibration::PHASE_FAILED)->count(),
            'categories_available' => TireCalibration::distinct('main_category')->pluck('main_category')->filter()->values(),
            'avg_quality_score' => round(TireCalibration::whereNotNull('data_completeness_score')->avg('data_completeness_score'), 1),
            'command_focus' => 'vehicle_data_mapping',
            'phase_coverage' => 'FASE_1_2_COMBINED'
        ];
    }
}
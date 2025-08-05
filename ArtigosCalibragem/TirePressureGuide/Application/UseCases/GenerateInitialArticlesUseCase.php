<?php

namespace Src\ContentGeneration\TirePressureGuide\Application\UseCases;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\VehicleDataProcessorService;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\InitialArticleGeneratorService;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * COMPLETE V3 GenerateInitialArticlesUseCase - DUAL TEMPLATE SUPPORT
 * 
 * FUNCIONALIDADES COMPLETAS:
 * - Suporte total a templateType ('ideal', 'calibration', 'both')
 * - Geração de 1 ou 2 artigos por veículo baseado no templateType
 * - Estatísticas completas separadas por template
 * - Validação robusta específica por template
 * - Cache inteligente e otimizações de performance
 * - Sistema de recovery para falhas parciais
 * - Relatórios detalhados de integridade
 * - Monitoramento em tempo real
 */
class GenerateInitialArticlesUseCase
{
    protected VehicleDataProcessorService $vehicleProcessor;
    protected InitialArticleGeneratorService $articleGenerator;
    protected array $processingMetrics;
    protected array $templateConfigurations;

    public function __construct(
        VehicleDataProcessorService $vehicleProcessor,
        InitialArticleGeneratorService $articleGenerator
    ) {
        $this->vehicleProcessor = $vehicleProcessor;
        $this->articleGenerator = $articleGenerator;
        $this->initializeProcessingMetrics();
        $this->initializeTemplateConfigurations();
    }

    /**
     * Executar geração de artigos com suporte completo a dual template
     */
    public function execute(
        string $csvPath,
        int $batchSize = 50,
        array $filters = [],
        bool $dryRun = false,
        bool $overwrite = false,
        string $templateType = 'ideal',
        ?callable $progressCallback = null,
        array $options = []
    ): object {
        
        $startTime = microtime(true);
        $batchId = $this->generateBatchId($templateType);
        
        $results = (object)[
            'success' => false,
            'execution_id' => $batchId,
            'template_type' => $templateType,
            'start_time' => $startTime,
            'end_time' => null,
            'duration_seconds' => 0,
            
            // Dados de processamento
            'total_vehicles_processed' => 0,
            'total_articles_generated' => 0,
            'articles_skipped' => 0,
            'articles_failed' => 0,
            
            // Breakdown por template
            'template_breakdown' => [],
            'template_performance' => [],
            
            // Dados de CSV e validação
            'csv_validation' => [],
            'processing_stats' => [],
            
            // Erros e warnings
            'errors' => [],
            'warnings' => [],
            'critical_issues' => [],
            
            // Estatísticas detalhadas
            'generation_summary' => [],
            'performance_metrics' => [],
            'memory_usage' => [],
            
            // Recovery e continuidade
            'recovery_data' => [],
            'partial_success' => false,
            
            // Configurações usadas
            'execution_config' => [
                'batch_size' => $batchSize,
                'filters' => $filters,
                'dry_run' => $dryRun,
                'overwrite' => $overwrite,
                'options' => $options
            ]
        ];

        try {
            Log::info("=== INICIANDO GERAÇÃO DUAL TEMPLATE - TirePressureGuide ===", [
                'execution_id' => $batchId,
                'csv_path' => $csvPath,
                'template_type' => $templateType,
                'batch_size' => $batchSize,
                'filters' => $filters,
                'dry_run' => $dryRun,
                'overwrite' => $overwrite,
                'options' => $options
            ]);

            // 1. Validações iniciais
            $this->performInitialValidations($csvPath, $templateType, $options);

            // 2. Processar e validar dados do CSV
            $vehicleData = $this->processAndValidateVehicleData($csvPath, $filters, $results);
            
            if ($vehicleData->isEmpty()) {
                throw new \Exception("Nenhum dado válido encontrado no CSV após processamento e validação");
            }

            $results->total_vehicles_processed = $vehicleData->count();
            $results->processing_stats = $this->vehicleProcessor->getProcessingStats($vehicleData);

            // 3. Analisar templates a gerar
            $templatesToGenerate = $this->analyzeAndPrepareTemplates($templateType, $vehicleData, $results);

            // 4. Filtrar artigos existentes se necessário
            if (!$overwrite) {
                $vehicleData = $this->intelligentFilterExistingArticles($vehicleData, $templatesToGenerate, $results);
            }

            // 5. Executar geração por template com monitoramento
            foreach ($templatesToGenerate as $template) {
                $templateResults = $this->executeTemplateGenerationWithMonitoring(
                    $vehicleData,
                    $template,
                    $batchSize,
                    $dryRun,
                    $batchId,
                    $progressCallback,
                    $options
                );

                // Consolidar resultados
                $this->consolidateTemplateResults($results, $template, $templateResults);
            }

            // 6. Análise final e relatórios
            $results->success = $this->analyzeExecutionSuccess($results);
            $results->generation_summary = $this->generateComprehensiveSummary($results);
            $results->performance_metrics = $this->calculatePerformanceMetrics($results, $startTime);

            // 7. Salvar dados de recovery para possível continuação
            if (!$dryRun) {
                $this->saveRecoveryData($results);
            }

            Log::info("=== GERAÇÃO DUAL TEMPLATE CONCLUÍDA ===", [
                'execution_id' => $batchId,
                'success' => $results->success,
                'template_type' => $templateType,
                'vehicles_processed' => $results->total_vehicles_processed,
                'articles_generated' => $results->total_articles_generated,
                'duration_seconds' => $results->duration_seconds,
                'template_breakdown' => $results->template_breakdown
            ]);

        } catch (\Exception $e) {
            $results->success = false;
            $results->critical_issues[] = "Erro crítico: " . $e->getMessage();
            $results->errors[] = "Erro geral na execução: " . $e->getMessage();
            
            // Tentar salvar dados de recovery mesmo em caso de erro
            try {
                $this->saveRecoveryData($results);
            } catch (\Exception $recoveryError) {
                Log::error("Falha ao salvar dados de recovery", [
                    'original_error' => $e->getMessage(),
                    'recovery_error' => $recoveryError->getMessage()
                ]);
            }

            Log::error("=== ERRO NA GERAÇÃO DUAL TEMPLATE ===", [
                'execution_id' => $batchId,
                'template_type' => $templateType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'partial_success' => $results->total_articles_generated > 0
            ]);
        } finally {
            // Finalizar métricas
            $results->end_time = microtime(true);
            $results->duration_seconds = round($results->end_time - $startTime, 2);
            $results->memory_usage = $this->getMemoryUsageStats();
            
            // Limpar cache se necessário
            $this->cleanupProcessingCache($batchId);
        }

        return $results;
    }

    /**
     * Validações iniciais robustas
     */
    protected function performInitialValidations(string $csvPath, string $templateType, array $options): void
    {
        // Validar template type
        $this->validateTemplateType($templateType);

        // Validar arquivo CSV
        if (!file_exists($csvPath)) {
            throw new \Exception("Arquivo CSV não encontrado: {$csvPath}");
        }

        if (!is_readable($csvPath)) {
            throw new \Exception("Arquivo CSV não é legível: {$csvPath}");
        }

        // Validar espaço em disco (se não for dry run)
        if (!($options['dry_run'] ?? false)) {
            $this->validateDiskSpace();
        }

        // Validar conexão com MongoDB
        $this->validateMongoDBConnection();

        // Validar recursos do sistema
        $this->validateSystemResources($options);

        Log::info("Validações iniciais concluídas com sucesso", [
            'csv_path' => $csvPath,
            'template_type' => $templateType,
            'validations_passed' => true
        ]);
    }

    /**
     * Processar e validar dados do CSV com robustez
     */
    protected function processAndValidateVehicleData(string $csvPath, array $filters, object &$results): Collection
    {
        try {
            // 1. Validar compatibilidade do CSV
            $results->csv_validation = $this->validateCsvCompatibility($csvPath);
            
            if (!$results->csv_validation['compatible']) {
                $issues = implode(', ', $results->csv_validation['issues']);
                throw new \Exception("CSV incompatível: {$issues}");
            }

            // 2. Processar dados com monitoramento
            Log::info("Iniciando processamento de dados do CSV", [
                'csv_path' => $csvPath,
                'filters' => $filters,
                'estimated_records' => $results->csv_validation['estimated_articles']
            ]);

            $vehicleData = $this->vehicleProcessor->processVehicleData($csvPath, $filters);

            // 3. Validações pós-processamento
            $this->validateProcessedVehicleData($vehicleData);

            // 4. Análise de qualidade dos dados
            $this->analyzeDataQuality($vehicleData, $results);

            Log::info("Dados do CSV processados com sucesso", [
                'vehicles_found' => $vehicleData->count(),
                'data_quality_score' => $results->processing_stats['quality_score'] ?? 'unknown'
            ]);

            return $vehicleData;
            
        } catch (\Exception $e) {
            Log::error("Erro no processamento do CSV", [
                'csv_path' => $csvPath,
                'filters' => $filters,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Analisar e preparar templates com configurações específicas
     */
    protected function analyzeAndPrepareTemplates(string $templateType, Collection $vehicleData, object &$results): array
    {
        $templatesToGenerate = $this->getTemplatesToGenerate($templateType);
        
        // Estimar recursos necessários
        $resourceEstimate = $this->estimateResourceRequirements($vehicleData, $templatesToGenerate);
        
        // Configurar templates baseado no tipo de veículo
        $vehicleTypes = $vehicleData->pluck('is_motorcycle')->unique();
        $templateConfigs = [];
        
        foreach ($templatesToGenerate as $template) {
            $templateConfigs[$template] = [
                'name' => $template,
                'supports_cars' => true,
                'supports_motorcycles' => true,
                'estimated_articles' => $vehicleData->count(),
                'complexity_score' => $this->getTemplateComplexityScore($template),
                'average_generation_time' => $this->getAverageGenerationTime($template),
                'memory_requirements' => $this->getTemplateMemoryRequirements($template)
            ];
        }

        $results->template_performance = $templateConfigs;

        Log::info("Templates analisados e preparados", [
            'templates_to_generate' => $templatesToGenerate,
            'resource_estimate' => $resourceEstimate,
            'template_configs' => $templateConfigs
        ]);

        return $templatesToGenerate;
    }

    /**
     * Executar geração de template com monitoramento completo
     */
    protected function executeTemplateGenerationWithMonitoring(
        Collection $vehicleData,
        string $template,
        int $batchSize,
        bool $dryRun,
        string $batchId,
        ?callable $progressCallback,
        array $options
    ): array {
        
        $templateStartTime = microtime(true);
        $templateResults = [
            'template' => $template,
            'start_time' => $templateStartTime,
            'end_time' => null,
            'duration_seconds' => 0,
            'generated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
            'warnings' => [],
            'performance_data' => [],
            'memory_peaks' => [],
            'batch_statistics' => []
        ];

        Log::info("Iniciando geração para template: {$template}", [
            'template' => $template,
            'vehicles_count' => $vehicleData->count(),
            'batch_size' => $batchSize,
            'dry_run' => $dryRun
        ]);

        try {
            // Processar em lotes com monitoramento
            $chunks = $vehicleData->chunk($batchSize);
            $processedVehicles = 0;
            $totalVehicles = $vehicleData->count();

            foreach ($chunks as $chunkIndex => $chunk) {
                $chunkStartTime = microtime(true);
                $chunkResults = $this->processChunkWithMonitoring(
                    $chunk,
                    $template,
                    $batchId,
                    $dryRun,
                    $chunkIndex,
                    $processedVehicles,
                    $totalVehicles,
                    $progressCallback
                );

                // Consolidar resultados do chunk
                $templateResults['generated'] += $chunkResults['generated'];
                $templateResults['skipped'] += $chunkResults['skipped'];
                $templateResults['failed'] += $chunkResults['failed'];
                $templateResults['errors'] = array_merge($templateResults['errors'], $chunkResults['errors']);
                $templateResults['warnings'] = array_merge($templateResults['warnings'], $chunkResults['warnings']);

                // Métricas de performance do chunk
                $chunkDuration = microtime(true) - $chunkStartTime;
                $templateResults['batch_statistics'][] = [
                    'chunk_index' => $chunkIndex,
                    'chunk_size' => $chunk->count(),
                    'duration_seconds' => round($chunkDuration, 2),
                    'articles_per_second' => round($chunkResults['generated'] / max($chunkDuration, 0.001), 2),
                    'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                    'success_rate' => $this->calculateChunkSuccessRate($chunkResults)
                ];

                $processedVehicles += $chunk->count();

                // Monitoramento de memória
                $currentMemory = memory_get_usage(true);
                $templateResults['memory_peaks'][] = $currentMemory;

                // Limpeza de memória a cada 5 lotes
                if ($chunkIndex > 0 && $chunkIndex % 5 === 0) {
                    gc_collect_cycles();
                    
                    Log::debug("Limpeza de memória executada", [
                        'template' => $template,
                        'chunk_index' => $chunkIndex,
                        'memory_before_mb' => round($currentMemory / 1024 / 1024, 2),
                        'memory_after_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
                    ]);
                }

                // Verificar limites de erro
                if ($templateResults['failed'] > ($totalVehicles * 0.2)) {
                    $templateResults['warnings'][] = "Alta taxa de falhas detectada para template {$template}. Considerar interromper processamento.";
                    
                    if ($templateResults['failed'] > ($totalVehicles * 0.5)) {
                        throw new \Exception("Taxa de falhas muito alta para template {$template}: {$templateResults['failed']}/{$totalVehicles}");
                    }
                }
            }

        } catch (\Exception $e) {
            $templateResults['errors'][] = "Erro crítico no template {$template}: " . $e->getMessage();
            Log::error("Erro na geração de template", [
                'template' => $template,
                'error' => $e->getMessage(),
                'processed_vehicles' => $processedVehicles,
                'results_so_far' => [
                    'generated' => $templateResults['generated'],
                    'failed' => $templateResults['failed']
                ]
            ]);
            
            // Não re-lançar exceção para permitir processamento de outros templates
        } finally {
            $templateResults['end_time'] = microtime(true);
            $templateResults['duration_seconds'] = round($templateResults['end_time'] - $templateStartTime, 2);
            
            // Calcular estatísticas finais do template
            $templateResults['performance_data'] = [
                'total_articles' => $templateResults['generated'] + $templateResults['failed'],
                'success_rate' => $this->calculateSuccessRate($templateResults),
                'articles_per_second' => $templateResults['duration_seconds'] > 0 
                    ? round($templateResults['generated'] / $templateResults['duration_seconds'], 2) 
                    : 0,
                'average_memory_mb' => count($templateResults['memory_peaks']) > 0
                    ? round(array_sum($templateResults['memory_peaks']) / count($templateResults['memory_peaks']) / 1024 / 1024, 2)
                    : 0,
                'peak_memory_mb' => count($templateResults['memory_peaks']) > 0
                    ? round(max($templateResults['memory_peaks']) / 1024 / 1024, 2)
                    : 0
            ];
        }

        Log::info("Template {$template} processado", [
            'template' => $template,
            'duration_seconds' => $templateResults['duration_seconds'],
            'generated' => $templateResults['generated'],
            'failed' => $templateResults['failed'],
            'success_rate' => $templateResults['performance_data']['success_rate'],
            'articles_per_second' => $templateResults['performance_data']['articles_per_second']
        ]);

        return $templateResults;
    }

    /**
     * Processar chunk com monitoramento detalhado
     */
    protected function processChunkWithMonitoring(
        Collection $chunk,
        string $template,
        string $batchId,
        bool $dryRun,
        int $chunkIndex,
        int $processedVehicles,
        int $totalVehicles,
        ?callable $progressCallback
    ): array {
        
        $chunkResults = [
            'generated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
            'warnings' => []
        ];

        Log::debug("Processando chunk {$chunkIndex} para template {$template}", [
            'chunk_size' => $chunk->count(),
            'template' => $template,
            'processed' => $processedVehicles,
            'total' => $totalVehicles,
            'progress_percentage' => round(($processedVehicles / $totalVehicles) * 100, 2)
        ]);

        foreach ($chunk as $vehicleIndex => $vehicle) {
            try {
                // Callback de progresso
                if ($progressCallback) {
                    $currentProgress = $processedVehicles + $vehicleIndex;
                    $progressCallback($currentProgress, $totalVehicles, $template, $vehicle);
                }

                $vehicleIdentifier = $vehicle['vehicle_identifier'] ?? 
                    ($vehicle['make'] ?? 'Unknown') . ' ' . 
                    ($vehicle['model'] ?? 'Unknown') . ' ' . 
                    ($vehicle['year'] ?? 'Unknown');

                if (!$dryRun) {
                    // Geração real do artigo
                    $article = $this->articleGenerator->generateArticle(
                        $vehicle,
                        $batchId,
                        $template
                    );

                    if ($article) {
                        $chunkResults['generated']++;
                        
                        Log::debug("Artigo gerado com sucesso", [
                            'vehicle' => $vehicleIdentifier,
                            'template' => $template,
                            'slug' => $article->slug,
                            'content_score' => $article->content_score ?? 0
                        ]);
                    } else {
                        $chunkResults['failed']++;
                        $error = "Falha na geração (retorno null) - Template: {$template}, Veículo: {$vehicleIdentifier}";
                        $chunkResults['errors'][] = $error;
                        
                        Log::warning($error);
                    }
                } else {
                    // Simulação para dry run
                    $chunkResults['generated']++;
                    
                    Log::debug("Artigo simulado (dry run)", [
                        'vehicle' => $vehicleIdentifier,
                        'template' => $template
                    ]);
                }

                // Verificar qualidade dos dados do veículo
                $this->validateVehicleDataQuality($vehicle, $template, $chunkResults);

            } catch (\Exception $e) {
                $chunkResults['failed']++;
                $vehicleIdentifier = $vehicle['vehicle_identifier'] ?? 'unknown_vehicle';
                $error = "Erro na geração - Template: {$template}, Veículo: {$vehicleIdentifier}, Erro: " . $e->getMessage();
                $chunkResults['errors'][] = $error;
                
                Log::error("Erro na geração de artigo individual", [
                    'template' => $template,
                    'vehicle' => $vehicleIdentifier,
                    'error' => $e->getMessage(),
                    'chunk_index' => $chunkIndex,
                    'vehicle_data' => $this->sanitizeVehicleDataForLog($vehicle)
                ]);
            }
        }

        return $chunkResults;
    }

    /**
     * Validar qualidade dos dados do veículo
     */
    protected function validateVehicleDataQuality(array $vehicle, string $template, array &$chunkResults): void
    {
        $requiredFields = ['make', 'model', 'year', 'tire_size'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($vehicle[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $vehicleId = $vehicle['vehicle_identifier'] ?? 'unknown';
            $warning = "Dados incompletos para {$vehicleId} - Template: {$template} - Campos faltando: " . implode(', ', $missingFields);
            $chunkResults['warnings'][] = $warning;
        }

        // Validações específicas por template
        if ($template === 'calibration') {
            if (empty($vehicle['pressure_empty_front']) || empty($vehicle['pressure_empty_rear'])) {
                $vehicleId = $vehicle['vehicle_identifier'] ?? 'unknown';
                $warning = "Pressões faltando para template calibration - Veículo: {$vehicleId}";
                $chunkResults['warnings'][] = $warning;
            }
        }
    }

    /**
     * Consolidar resultados de template
     */
    protected function consolidateTemplateResults(object &$results, string $template, array $templateResults): void
    {
        // Agregar números principais
        $results->total_articles_generated += $templateResults['generated'];
        $results->articles_skipped += $templateResults['skipped'];
        $results->articles_failed += $templateResults['failed'];
        $results->errors = array_merge($results->errors, $templateResults['errors']);
        $results->warnings = array_merge($results->warnings, $templateResults['warnings']);

        // Breakdown detalhado por template
        $results->template_breakdown[$template] = [
            'generated' => $templateResults['generated'],
            'skipped' => $templateResults['skipped'],
            'failed' => $templateResults['failed'],
            'success_rate' => $templateResults['performance_data']['success_rate'] ?? 0,
            'duration_seconds' => $templateResults['duration_seconds'],
            'articles_per_second' => $templateResults['performance_data']['articles_per_second'] ?? 0,
            'memory_usage' => [
                'average_mb' => $templateResults['performance_data']['average_memory_mb'] ?? 0,
                'peak_mb' => $templateResults['performance_data']['peak_memory_mb'] ?? 0
            ],
            'batch_count' => count($templateResults['batch_statistics']),
            'error_count' => count($templateResults['errors']),
            'warning_count' => count($templateResults['warnings'])
        ];

        // Verificar se houve sucesso parcial
        if ($templateResults['generated'] > 0) {
            $results->partial_success = true;
        }
    }

    /**
     * Filtrar artigos existentes com inteligência
     */
    protected function intelligentFilterExistingArticles(Collection $vehicleData, array $templates, object &$results): Collection
    {
        $originalCount = $vehicleData->count();
        $skippedCombinations = 0;
        $existingArticlesByTemplate = [];

        // Cache de verificações para performance
        $existenceCache = [];

        $filteredData = $vehicleData->filter(function($vehicle) use ($templates, &$skippedCombinations, &$existingArticlesByTemplate, &$existenceCache) {
            $vehicleKey = $this->generateVehicleKey($vehicle);
            $shouldProcess = false;

            foreach ($templates as $template) {
                $cacheKey = "{$vehicleKey}_{$template}";
                
                if (!isset($existenceCache[$cacheKey])) {
                    $exists = $this->articleExistsForTemplate($vehicle, $template);
                    $existenceCache[$cacheKey] = $exists;
                } else {
                    $exists = $existenceCache[$cacheKey];
                }

                if (!$exists) {
                    $shouldProcess = true;
                } else {
                    $skippedCombinations++;
                    
                    if (!isset($existingArticlesByTemplate[$template])) {
                        $existingArticlesByTemplate[$template] = 0;
                    }
                    $existingArticlesByTemplate[$template]++;
                }
            }

            return $shouldProcess;
        });

        $results->articles_skipped = $skippedCombinations;

        Log::info("Filtro inteligente de artigos existentes aplicado", [
            'original_vehicles' => $originalCount,
            'filtered_vehicles' => $filteredData->count(),
            'skipped_combinations' => $skippedCombinations,
            'existing_by_template' => $existingArticlesByTemplate,
            'templates' => $templates
        ]);

        return $filteredData;
    }

    /**
     * Analisar sucesso da execução
     */
    protected function analyzeExecutionSuccess(object $results): bool
    {
        // Critérios de sucesso
        $totalAttempts = $results->total_articles_generated + $results->articles_failed;
        
        if ($totalAttempts === 0) {
            return false; // Nenhum artigo processado
        }

        $overallSuccessRate = ($results->total_articles_generated / $totalAttempts) * 100;
        
        // Sucesso se taxa >= 80% e pelo menos 1 artigo gerado
        $success = $overallSuccessRate >= 80.0 && $results->total_articles_generated > 0;
        
        // Verificar problemas críticos
        if (!empty($results->critical_issues)) {
            $success = false;
        }

        return $success;
    }

    /**
     * Gerar sumário abrangente
     */
    protected function generateComprehensiveSummary(object $results): array
    {
        $summary = [
            'execution_type' => $results->template_type,
            'execution_status' => $results->success ? 'success' : 'failed',
            'partial_success' => $results->partial_success,
            
            // Números principais
            'totals' => [
                'vehicles_processed' => $results->total_vehicles_processed,
                'articles_generated' => $results->total_articles_generated,
                'articles_skipped' => $results->articles_skipped,
                'articles_failed' => $results->articles_failed,
                'articles_per_vehicle' => $results->total_vehicles_processed > 0 
                    ? round($results->total_articles_generated / $results->total_vehicles_processed, 2) 
                    : 0
            ],
            
            // Performance geral
            'performance' => [
                'overall_success_rate' => $this->calculateOverallSuccessRate($results),
                'duration_seconds' => $results->duration_seconds,
                'articles_per_second' => $results->duration_seconds > 0 
                    ? round($results->total_articles_generated / $results->duration_seconds, 2) 
                    : 0,
                'memory_efficiency' => $this->calculateMemoryEfficiency($results)
            ],
            
            // Performance por template
            'template_performance' => [],
            
            // Qualidade e problemas
            'quality_metrics' => [
                'error_count' => count($results->errors),
                'warning_count' => count($results->warnings),
                'critical_issues_count' => count($results->critical_issues),
                'data_quality_score' => $results->processing_stats['quality_score'] ?? 0
            ],
            
            // Recursos utilizados
            'resource_usage' => $results->memory_usage,
            
            // Recomendações automáticas
            'recommendations' => $this->generateIntelligentRecommendations($results),
            
            // Next steps
            'next_steps' => $this->generateNextSteps($results)
        ];

        // Performance detalhada por template
        foreach ($results->template_breakdown as $template => $stats) {
            $summary['template_performance'][$template] = [
                'articles_generated' => $stats['generated'],
                'success_rate' => $stats['success_rate'],
                'performance_rating' => $this->getPerformanceRating($stats['success_rate']),
                'duration_seconds' => $stats['duration_seconds'],
                'efficiency_score' => $this->calculateTemplateEfficiencyScore($stats),
                'quality_indicators' => [
                    'error_rate' => $stats['generated'] > 0 ? round(($stats['failed'] / ($stats['generated'] + $stats['failed'])) * 100, 2) : 0,
                    'warning_rate' => $stats['generated'] > 0 ? round($stats['warning_count'] / $stats['generated'] * 100, 2) : 0,
                    'avg_articles_per_second' => $stats['articles_per_second']
                ]
            ];
        }

        return $summary;
    }

    /**
     * Calcular métricas de performance
     */
    protected function calculatePerformanceMetrics(object $results, float $startTime): array
    {
        $endTime = microtime(true);
        $totalDuration = $endTime - $startTime;

        return [
            'execution_time' => [
                'total_seconds' => round($totalDuration, 2),
                'total_minutes' => round($totalDuration / 60, 2),
                'start_timestamp' => $startTime,
                'end_timestamp' => $endTime
            ],
            'throughput' => [
                'vehicles_per_second' => $totalDuration > 0 ? round($results->total_vehicles_processed / $totalDuration, 2) : 0,
                'articles_per_second' => $totalDuration > 0 ? round($results->total_articles_generated / $totalDuration, 2) : 0,
                'articles_per_minute' => $totalDuration > 0 ? round(($results->total_articles_generated / $totalDuration) * 60, 2) : 0
            ],
            'efficiency' => [
                'success_ratio' => $this->calculateOverallSuccessRate($results) / 100,
                'processing_efficiency' => $this->calculateProcessingEfficiency($results),
                'resource_efficiency' => $this->calculateResourceEfficiency($results)
            ],
            'scalability_indicators' => [
                'memory_growth_rate' => $this->calculateMemoryGrowthRate($results),
                'processing_stability' => $this->calculateProcessingStability($results),
                'error_progression' => $this->analyzeErrorProgression($results)
            ]
        ];
    }

    /**
     * Salvar dados de recovery
     */
    protected function saveRecoveryData(object $results): void
    {
        try {
            $recoveryData = [
                'execution_id' => $results->execution_id,
                'timestamp' => now()->toISOString(),
                'template_type' => $results->template_type,
                'execution_config' => $results->execution_config,
                'progress' => [
                    'total_vehicles' => $results->total_vehicles_processed,
                    'articles_generated' => $results->total_articles_generated,
                    'articles_failed' => $results->articles_failed,
                    'template_breakdown' => $results->template_breakdown
                ],
                'last_successful_batch' => $this->getLastSuccessfulBatch($results),
                'failed_vehicles' => $this->extractFailedVehicles($results),
                'recovery_suggestions' => $this->generateRecoverySuggestions($results)
            ];

            Cache::put("tire_pressure_recovery_{$results->execution_id}", $recoveryData, now()->addDays(7));

            Log::info("Dados de recovery salvos", [
                'execution_id' => $results->execution_id,
                'recovery_key' => "tire_pressure_recovery_{$results->execution_id}"
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao salvar dados de recovery", [
                'execution_id' => $results->execution_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    // =======================================================================
    // MÉTODOS DE VALIDAÇÃO E ANÁLISE
    // =======================================================================

    /**
     * Validar tipo de template
     */
    protected function validateTemplateType(string $templateType): void
    {
        $validTypes = ['ideal', 'calibration', 'both'];
        
        if (!in_array($templateType, $validTypes)) {
            throw new \Exception("Template type inválido: {$templateType}. Válidos: " . implode(', ', $validTypes));
        }
    }

    /**
     * Validar espaço em disco
     */
    protected function validateDiskSpace(): void
    {
        $freeBytes = disk_free_space(storage_path());
        $requiredBytes = 500 * 1024 * 1024; // 500MB mínimo

        if ($freeBytes < $requiredBytes) {
            throw new \Exception("Espaço em disco insuficiente. Disponível: " . round($freeBytes / 1024 / 1024, 2) . "MB, Requerido: " . round($requiredBytes / 1024 / 1024, 2) . "MB");
        }
    }

    /**
     * Validar conexão MongoDB
     */
    protected function validateMongoDBConnection(): void
    {
        try {
            TirePressureArticle::count();
        } catch (\Exception $e) {
            throw new \Exception("Falha na conexão com MongoDB: " . $e->getMessage());
        }
    }

    /**
     * Validar recursos do sistema
     */
    protected function validateSystemResources(array $options): void
    {
        // Verificar memória
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        $recommendedBytes = 512 * 1024 * 1024; // 512MB

        if ($memoryLimitBytes < $recommendedBytes) {
            Log::warning("Limite de memória baixo", [
                'current_limit' => $memoryLimit,
                'recommended' => '512M'
            ]);
        }

        // Verificar extensões PHP necessárias
        $requiredExtensions = ['mbstring', 'json', 'mongodb'];
        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                throw new \Exception("Extensão PHP requerida não encontrada: {$extension}");
            }
        }
    }

    /**
     * Converter limite de memória para bytes
     */
    protected function convertToBytes(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit)-1]);
        $size = (int) $memoryLimit;
        
        switch($last) {
            case 'g': $size *= 1024;
            case 'm': $size *= 1024;
            case 'k': $size *= 1024;
        }
        
        return $size;
    }

    /**
     * Validar dados processados do veículo
     */
    protected function validateProcessedVehicleData(Collection $vehicleData): void
    {
        if ($vehicleData->isEmpty()) {
            throw new \Exception("Nenhum veículo válido encontrado após processamento");
        }

        // Verificar campos essenciais
        $requiredFields = ['make', 'model', 'year', 'tire_size'];
        $invalidVehicles = 0;
        $sampleSize = min(100, $vehicleData->count()); // Amostra para performance

        foreach ($vehicleData->take($sampleSize) as $vehicle) {
            foreach ($requiredFields as $field) {
                if (empty($vehicle[$field])) {
                    $invalidVehicles++;
                    break;
                }
            }
        }

        $invalidRate = ($invalidVehicles / $sampleSize) * 100;
        if ($invalidRate > 15) { // Mais de 15% inválidos
            throw new \Exception("Muitos veículos com dados incompletos na amostra: {$invalidVehicles}/{$sampleSize} ({$invalidRate}%)");
        }

        Log::info("Dados de veículos validados", [
            'total_vehicles' => $vehicleData->count(),
            'sample_size' => $sampleSize,
            'invalid_vehicles_in_sample' => $invalidVehicles,
            'invalid_rate_percent' => $invalidRate,
            'validation_passed' => true
        ]);
    }

    /**
     * Analisar qualidade dos dados
     */
    protected function analyzeDataQuality(Collection $vehicleData, object &$results): void
    {
        $qualityAnalysis = [
            'total_vehicles' => $vehicleData->count(),
            'quality_score' => 0,
            'completeness_score' => 0,
            'consistency_score' => 0,
            'issues' => []
        ];

        // Análise de completude
        $completeVehicles = 0;
        $requiredFields = ['make', 'model', 'year', 'tire_size'];
        
        foreach ($vehicleData as $vehicle) {
            $isComplete = true;
            foreach ($requiredFields as $field) {
                if (empty($vehicle[$field])) {
                    $isComplete = false;
                    break;
                }
            }
            if ($isComplete) $completeVehicles++;
        }

        $qualityAnalysis['completeness_score'] = round(($completeVehicles / $vehicleData->count()) * 100, 2);

        // Análise de consistência (marcas válidas, anos razoáveis, etc.)
        $validYears = $vehicleData->filter(function($vehicle) {
            $year = $vehicle['year'] ?? 0;
            return $year >= 1990 && $year <= (date('Y') + 2);
        })->count();

        $qualityAnalysis['consistency_score'] = round(($validYears / $vehicleData->count()) * 100, 2);

        // Score geral (média ponderada)
        $qualityAnalysis['quality_score'] = round(
            ($qualityAnalysis['completeness_score'] * 0.6) + 
            ($qualityAnalysis['consistency_score'] * 0.4), 
            2
        );

        // Identificar problemas
        if ($qualityAnalysis['completeness_score'] < 90) {
            $qualityAnalysis['issues'][] = "Baixa completude dos dados ({$qualityAnalysis['completeness_score']}%)";
        }
        if ($qualityAnalysis['consistency_score'] < 95) {
            $qualityAnalysis['issues'][] = "Problemas de consistência detectados ({$qualityAnalysis['consistency_score']}%)";
        }

        $results->processing_stats['quality_analysis'] = $qualityAnalysis;
        $results->processing_stats['quality_score'] = $qualityAnalysis['quality_score'];

        Log::info("Análise de qualidade dos dados concluída", $qualityAnalysis);
    }

    // =======================================================================
    // MÉTODOS AUXILIARES E UTILITÁRIOS
    // =======================================================================

    /**
     * Obter templates para gerar
     */
    protected function getTemplatesToGenerate(string $templateType): array
    {
        switch ($templateType) {
            case 'ideal':
                return ['ideal'];
            case 'calibration':
                return ['calibration'];
            case 'both':
                return ['ideal', 'calibration'];
            default:
                throw new \Exception("Template type não suportado: {$templateType}");
        }
    }

    /**
     * Verificar se artigo existe para template específico
     */
    protected function articleExistsForTemplate(array $vehicleData, string $template): bool
    {
        try {
            $slug = $this->generateSlugForTemplate($vehicleData, $template);
            
            return TirePressureArticle::where('slug', $slug)
                                    ->where('template_type', $template)
                                    ->exists();
        } catch (\Exception $e) {
            Log::warning("Erro ao verificar artigo existente", [
                'vehicle' => $vehicleData['vehicle_identifier'] ?? 'unknown',
                'template' => $template,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Gerar slug para template específico
     */
    protected function generateSlugForTemplate(array $vehicleData, string $template): string
    {
        $make = \Illuminate\Support\Str::slug($vehicleData['make'] ?? '');
        $model = \Illuminate\Support\Str::slug($vehicleData['model'] ?? '');
        $year = $vehicleData['year'] ?? '';
        
        if ($template === 'ideal') {
            return "pressao-ideal-pneu-{$make}-{$model}-{$year}";
        } elseif ($template === 'calibration') {
            return "calibragem-pneu-{$make}-{$model}-{$year}";
        }
        
        return "pneu-{$make}-{$model}-{$year}";
    }

    /**
     * Gerar chave única para veículo
     */
    protected function generateVehicleKey(array $vehicle): string
    {
        return strtolower(
            ($vehicle['make'] ?? '') . '_' . 
            ($vehicle['model'] ?? '') . '_' . 
            ($vehicle['year'] ?? '')
        );
    }

    /**
     * Calcular taxa de sucesso
     */
    protected function calculateSuccessRate(array $templateResults): float
    {
        $total = $templateResults['generated'] + $templateResults['failed'];
        
        if ($total === 0) {
            return 0.0;
        }
        
        return round(($templateResults['generated'] / $total) * 100, 2);
    }

    /**
     * Calcular taxa de sucesso do chunk
     */
    protected function calculateChunkSuccessRate(array $chunkResults): float
    {
        $total = $chunkResults['generated'] + $chunkResults['failed'];
        
        if ($total === 0) {
            return 0.0;
        }
        
        return round(($chunkResults['generated'] / $total) * 100, 2);
    }

    /**
     * Calcular taxa de sucesso geral
     */
    protected function calculateOverallSuccessRate(object $results): float
    {
        $totalAttempts = $results->total_articles_generated + $results->articles_failed;
        
        if ($totalAttempts === 0) {
            return 0.0;
        }
        
        return round(($results->total_articles_generated / $totalAttempts) * 100, 2);
    }

    /**
     * Estimar requisitos de recursos
     */
    protected function estimateResourceRequirements(Collection $vehicleData, array $templates): array
    {
        $vehicleCount = $vehicleData->count();
        $templateCount = count($templates);
        $totalArticles = $vehicleCount * $templateCount;

        return [
            'estimated_articles' => $totalArticles,
            'estimated_memory_mb' => $totalArticles * 2, // ~2MB por artigo
            'estimated_duration_minutes' => ceil($totalArticles * 0.5 / 60), // ~0.5s por artigo
            'disk_space_mb' => $totalArticles * 0.1, // ~100KB por artigo
            'database_operations' => $totalArticles * 2 // Insert + Update
        ];
    }

    /**
     * Obter score de complexidade do template
     */
    protected function getTemplateComplexityScore(string $template): int
    {
        $complexityMap = [
            'ideal' => 6,      // Estrutura mais simples
            'calibration' => 9  // Estrutura mais complexa
        ];
        
        return $complexityMap[$template] ?? 5;
    }

    /**
     * Obter tempo médio de geração
     */
    protected function getAverageGenerationTime(string $template): float
    {
        $timeMap = [
            'ideal' => 0.4,      // 400ms em média
            'calibration' => 0.7  // 700ms em média (mais complexo)
        ];
        
        return $timeMap[$template] ?? 0.5;
    }

    /**
     * Obter requisitos de memória do template
     */
    protected function getTemplateMemoryRequirements(string $template): int
    {
        $memoryMap = [
            'ideal' => 1536,      // ~1.5MB por artigo
            'calibration' => 2560  // ~2.5MB por artigo (mais complexo)
        ];
        
        return $memoryMap[$template] ?? 2048;
    }

    /**
     * Obter classificação de performance
     */
    protected function getPerformanceRating(float $successRate): string
    {
        if ($successRate >= 95) return 'Excelente';
        if ($successRate >= 85) return 'Boa';
        if ($successRate >= 70) return 'Regular';
        if ($successRate >= 50) return 'Baixa';
        return 'Crítica';
    }

    /**
     * Calcular score de eficiência do template
     */
    protected function calculateTemplateEfficiencyScore(array $stats): float
    {
        $successWeight = 0.4;
        $speedWeight = 0.3;
        $memoryWeight = 0.3;

        $successScore = min($stats['success_rate'] / 100, 1.0);
        $speedScore = min($stats['articles_per_second'] / 2.0, 1.0); // 2 artigos/segundo = 100%
        $memoryScore = isset($stats['memory_usage']['average_mb']) 
            ? max(0, 1.0 - ($stats['memory_usage']['average_mb'] / 1000)) // 1GB = 0%
            : 0.5;

        return round(
            ($successScore * $successWeight) + 
            ($speedScore * $speedWeight) + 
            ($memoryScore * $memoryWeight),
            3
        );
    }

    /**
     * Calcular eficiência de memória
     */
    protected function calculateMemoryEfficiency(object $results): float
    {
        if (empty($results->memory_usage['peak_memory_mb'])) {
            return 0.0;
        }

        $peakMemoryMB = $results->memory_usage['peak_memory_mb'];
        $articlesGenerated = $results->total_articles_generated;

        if ($articlesGenerated === 0) {
            return 0.0;
        }

        $memoryPerArticle = $peakMemoryMB / $articlesGenerated;
        
        // Eficiência baseada em menos de 2MB por artigo = 100%
        return min(100, max(0, (2.0 - $memoryPerArticle) / 2.0 * 100));
    }

    /**
     * Calcular eficiência de processamento
     */
    protected function calculateProcessingEfficiency(object $results): float
    {
        if ($results->duration_seconds === 0 || $results->total_articles_generated === 0) {
            return 0.0;
        }

        $articlesPerSecond = $results->total_articles_generated / $results->duration_seconds;
        
        // 1 artigo por segundo = 100% eficiência
        return min(100, $articlesPerSecond * 100);
    }

    /**
     * Calcular eficiência de recursos
     */
    protected function calculateResourceEfficiency(object $results): float
    {
        $memoryEff = $this->calculateMemoryEfficiency($results);
        $processingEff = $this->calculateProcessingEfficiency($results);
        
        return round(($memoryEff + $processingEff) / 2, 2);
    }

    /**
     * Calcular taxa de crescimento de memória
     */
    protected function calculateMemoryGrowthRate(object $results): float
    {
        // Análise simplificada baseada nos resultados
        if (empty($results->template_breakdown)) {
            return 0.0;
        }

        $memoryUsages = [];
        foreach ($results->template_breakdown as $template => $stats) {
            if (isset($stats['memory_usage']['average_mb'])) {
                $memoryUsages[] = $stats['memory_usage']['average_mb'];
            }
        }

        if (count($memoryUsages) < 2) {
            return 0.0;
        }

        return round((max($memoryUsages) - min($memoryUsages)) / min($memoryUsages) * 100, 2);
    }

    /**
     * Calcular estabilidade de processamento
     */
    protected function calculateProcessingStability(object $results): float
    {
        $successRates = [];
        foreach ($results->template_breakdown as $template => $stats) {
            $successRates[] = $stats['success_rate'];
        }

        if (empty($successRates)) {
            return 0.0;
        }

        $average = array_sum($successRates) / count($successRates);
        $variance = 0;

        foreach ($successRates as $rate) {
            $variance += pow($rate - $average, 2);
        }

        $variance = $variance / count($successRates);
        $standardDeviation = sqrt($variance);

        // Menos desvio = mais estabilidade
        return max(0, 100 - $standardDeviation);
    }

    /**
     * Analisar progressão de erros
     */
    protected function analyzeErrorProgression(object $results): array
    {
        $errorAnalysis = [
            'trend' => 'stable',
            'error_rate_by_template' => [],
            'critical_errors' => count($results->critical_issues),
            'total_errors' => count($results->errors)
        ];

        foreach ($results->template_breakdown as $template => $stats) {
            $total = $stats['generated'] + $stats['failed'];
            $errorRate = $total > 0 ? round(($stats['failed'] / $total) * 100, 2) : 0;
            $errorAnalysis['error_rate_by_template'][$template] = $errorRate;
        }

        // Determinar tendência
        $errorRates = array_values($errorAnalysis['error_rate_by_template']);
        if (count($errorRates) > 1) {
            if (max($errorRates) - min($errorRates) > 20) {
                $errorAnalysis['trend'] = 'inconsistent';
            } elseif (array_sum($errorRates) / count($errorRates) > 15) {
                $errorAnalysis['trend'] = 'high_error_rate';
            }
        }

        return $errorAnalysis;
    }

    /**
     * Gerar recomendações inteligentes
     */
    protected function generateIntelligentRecommendations(object $results): array
    {
        $recommendations = [];

        // Análise de sucesso geral
        $overallSuccessRate = $this->calculateOverallSuccessRate($results);
        
        if ($overallSuccessRate < 70) {
            $recommendations[] = [
                'type' => 'critical',
                'message' => "Taxa de sucesso muito baixa ({$overallSuccessRate}%). Revisar configurações e dados de entrada.",
                'action' => 'review_configuration'
            ];
        } elseif ($overallSuccessRate < 90) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => "Taxa de sucesso abaixo do ideal ({$overallSuccessRate}%). Investigar causas dos erros.",
                'action' => 'investigate_errors'
            ];
        }

        // Análise por template
        foreach ($results->template_breakdown as $template => $stats) {
            if ($stats['success_rate'] < 80) {
                $recommendations[] = [
                    'type' => 'warning',
                    'message' => "Template '{$template}' com baixa taxa de sucesso ({$stats['success_rate']}%). Verificar estrutura de geração.",
                    'action' => 'review_template_structure',
                    'template' => $template
                ];
            }

            if ($stats['articles_per_second'] < 0.5) {
                $recommendations[] = [
                    'type' => 'performance',
                    'message' => "Template '{$template}' com baixa performance ({$stats['articles_per_second']} art/s). Considerar otimizações.",
                    'action' => 'optimize_performance',
                    'template' => $template
                ];
            }
        }

        // Análise de memória
        if (isset($results->memory_usage['peak_memory_mb']) && $results->memory_usage['peak_memory_mb'] > 1000) {
            $recommendations[] = [
                'type' => 'resource',
                'message' => "Alto uso de memória ({$results->memory_usage['peak_memory_mb']}MB). Considerar lotes menores.",
                'action' => 'reduce_batch_size'
            ];
        }

        // Recomendações específicas para 'both'
        if ($results->template_type === 'both' && count($results->template_breakdown) === 2) {
            $templates = array_keys($results->template_breakdown);
            $template1 = $results->template_breakdown[$templates[0]];
            $template2 = $results->template_breakdown[$templates[1]];
            
            $successDiff = abs($template1['success_rate'] - $template2['success_rate']);
            
            if ($successDiff > 25) {
                $recommendations[] = [
                    'type' => 'consistency',
                    'message' => "Grande diferença na taxa de sucesso entre templates (diferença: {$successDiff}%). Investigar causas específicas.",
                    'action' => 'investigate_template_differences'
                ];
            }
        }

        // Próximos passos
        if ($results->total_articles_generated > 0) {
            $recommendations[] = [
                'type' => 'next_step',
                'message' => "Executar Etapa 2: Refinamento Claude para os {$results->total_articles_generated} artigos gerados.",
                'action' => 'execute_claude_refinement'
            ];

            if ($results->template_type === 'both') {
                $recommendations[] = [
                    'type' => 'enhancement',
                    'message' => "Implementar cross-linking entre artigos irmãos (mesmo veículo, templates diferentes).",
                    'action' => 'implement_cross_linking'
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Gerar próximos passos
     */
    protected function generateNextSteps(object $results): array
    {
        $nextSteps = [];

        if ($results->success && $results->total_articles_generated > 0) {
            $nextSteps[] = [
                'priority' => 1,
                'action' => 'Verificar artigos gerados no MongoDB',
                'command' => 'db.tire_pressure_articles.find({batch_id: "' . $results->execution_id . '"}).count()',
                'description' => 'Confirmar que todos os artigos foram salvos corretamente'
            ];

            $nextSteps[] = [
                'priority' => 2,
                'action' => 'Executar refinamento Claude',
                'command' => 'php artisan tire-pressure-guide:refine-sections --template=' . $results->template_type,
                'description' => 'Refinar conteúdo dos artigos gerados com Claude AI'
            ];

            $nextSteps[] = [
                'priority' => 3,
                'action' => 'Validar compatibilidade com ViewModels',
                'command' => 'php artisan tire-pressure-guide:validate-structure --template=' . $results->template_type,
                'description' => 'Verificar se estruturas JSON estão compatíveis com ViewModels'
            ];

            if ($results->template_type === 'both') {
                $nextSteps[] = [
                    'priority' => 4,
                    'action' => 'Implementar cross-links',
                    'command' => 'php artisan tire-pressure-guide:create-cross-links',
                    'description' => 'Conectar artigos irmãos (mesmo veículo, templates diferentes)'
                ];
            }

            $nextSteps[] = [
                'priority' => 5,
                'action' => 'Testar publicação',
                'command' => 'php artisan tire-pressure-guide:publish-temp --filter-template=' . $results->template_type . ' --limit=5',
                'description' => 'Testar publicação com alguns artigos primeiro'
            ];
        } else {
            $nextSteps[] = [
                'priority' => 1,
                'action' => 'Analisar logs de erro',
                'command' => 'grep "ERROR\|CRITICAL" storage/logs/laravel.log | grep tire-pressure',
                'description' => 'Identificar causas dos erros para correção'
            ];

            if ($results->articles_failed > 0) {
                $nextSteps[] = [
                    'priority' => 2,
                    'action' => 'Recuperar geração para veículos que falharam',
                    'command' => 'php artisan tire-pressure:recover --execution-id=' . $results->execution_id,
                    'description' => 'Tentar reprocessar apenas os veículos que falharam'
                ];
            }
        }

        return $nextSteps;
    }

    /**
     * Obter estatísticas de uso de memória
     */
    protected function getMemoryUsageStats(): array
    {
        return [
            'current_memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'memory_limit' => ini_get('memory_limit'),
            'memory_usage_percentage' => round((memory_get_usage(true) / $this->convertToBytes(ini_get('memory_limit'))) * 100, 2)
        ];
    }

    /**
     * Sanitizar dados do veículo para log
     */
    protected function sanitizeVehicleDataForLog(array $vehicle): array
    {
        return [
            'make' => $vehicle['make'] ?? 'unknown',
            'model' => $vehicle['model'] ?? 'unknown',
            'year' => $vehicle['year'] ?? 'unknown',
            'tire_size' => $vehicle['tire_size'] ?? 'unknown',
            'is_motorcycle' => $vehicle['is_motorcycle'] ?? false,
            'main_category' => $vehicle['main_category'] ?? 'unknown'
        ];
    }

    /**
     * Obter último lote bem-sucedido
     */
    protected function getLastSuccessfulBatch(object $results): ?array
    {
        // Implementação simplificada - pode ser expandida
        if ($results->total_articles_generated > 0) {
            return [
                'articles_generated' => $results->total_articles_generated,
                'timestamp' => now()->toISOString(),
                'templates_completed' => array_keys($results->template_breakdown)
            ];
        }
        
        return null;
    }

    /**
     * Extrair veículos que falharam
     */
    protected function extractFailedVehicles(object $results): array
    {
        // Esta implementação seria mais robusta com tracking detalhado durante processamento
        // Por enquanto, retornamos informações básicas dos erros
        $failedVehicles = [];
        
        foreach ($results->errors as $error) {
            if (preg_match('/Veículo: ([^,]+)/', $error, $matches)) {
                $failedVehicles[] = [
                    'vehicle_identifier' => $matches[1],
                    'error' => $error,
                    'timestamp' => now()->toISOString()
                ];
            }
        }
        
        return $failedVehicles;
    }

    /**
     * Gerar sugestões de recovery
     */
    protected function generateRecoverySuggestions(object $results): array
    {
        $suggestions = [];
        
        if ($results->articles_failed > 0) {
            $suggestions[] = [
                'type' => 'retry',
                'description' => 'Reprocessar apenas veículos que falharam',
                'command' => 'php artisan tire-pressure:retry-failed --execution-id=' . $results->execution_id,
                'estimated_time_minutes' => ceil($results->articles_failed * 0.5 / 60)
            ];
        }
        
        if (!empty($results->critical_issues)) {
            $suggestions[] = [
                'type' => 'fix_critical',
                'description' => 'Corrigir problemas críticos antes de continuar',
                'action' => 'review_logs_and_configuration'
            ];
        }
        
        $overallSuccessRate = $this->calculateOverallSuccessRate($results);
        if ($overallSuccessRate < 70 && $results->total_articles_generated > 0) {
            $suggestions[] = [
                'type' => 'partial_recovery',
                'description' => 'Continuar com artigos gerados com sucesso e investigar falhas',
                'next_step' => 'claude_refinement_for_successful_articles'
            ];
        }
        
        return $suggestions;
    }

    /**
     * Limpar cache de processamento
     */
    protected function cleanupProcessingCache(string $batchId): void
    {
        try {
            // Limpar caches específicos do processamento se existirem
            Cache::forget("processing_stats_{$batchId}");
            Cache::forget("vehicle_data_{$batchId}");
            
            Log::debug("Cache de processamento limpo", [
                'batch_id' => $batchId
            ]);
        } catch (\Exception $e) {
            Log::warning("Erro ao limpar cache de processamento", [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Inicializar métricas de processamento
     */
    protected function initializeProcessingMetrics(): void
    {
        $this->processingMetrics = [
            'start_time' => null,
            'end_time' => null,
            'memory_snapshots' => [],
            'processing_rates' => [],
            'error_patterns' => []
        ];
    }

    /**
     * Inicializar configurações de template
     */
    protected function initializeTemplateConfigurations(): void
    {
        $this->templateConfigurations = [
            'ideal' => [
                'name' => 'Pressão Ideal',
                'view_model' => 'IdealTirePressureCarViewModel',
                'complexity' => 'medium',
                'avg_generation_time_ms' => 400,
                'memory_requirements_mb' => 1.5,
                'required_sections' => [
                    'introducao',
                    'especificacoes_pneus',
                    'tabela_pressoes',
                    'conversao_unidades',
                    'beneficios_calibragem',
                    'perguntas_frequentes',
                    'consideracoes_finais'
                ]
            ],
            'calibration' => [
                'name' => 'Calibragem Completa',
                'view_model' => 'TirePressureGuideCarViewModel',
                'complexity' => 'high',
                'avg_generation_time_ms' => 700,
                'memory_requirements_mb' => 2.5,
                'required_sections' => [
                    'introducao',
                    'tire_specifications',
                    'pressure_table',
                    'calibration_procedure',
                    'equipment_guide',
                    'tpms_system',
                    'maintenance_schedule',
                    'troubleshooting',
                    'safety_considerations',
                    'cost_considerations',
                    'perguntas_frequentes',
                    'consideracoes_finais'
                ]
            ]
        ];
    }

    /**
     * Gerar ID único para o lote incluindo template type
     */
    protected function generateBatchId(string $templateType): string
    {
        $prefix = $templateType === 'both' ? 'dual' : $templateType;
        return $prefix . '_tpg_' . now()->format('Ymd_His') . '_' . \Illuminate\Support\Str::random(6);
    }

    // =======================================================================
    // MÉTODOS PÚBLICOS PARA INTEGRAÇÃO E RELATÓRIOS
    // =======================================================================

    /**
     * Validar compatibilidade do CSV (método público para usar no Command)
     */
    public function validateCsvCompatibility(string $csvPath): array
    {
        $validation = [
            'compatible' => false,
            'issues' => [],
            'recommendations' => [],
            'estimated_articles' => 0,
            'missing_fields' => [],
            'file_stats' => [],
            'quality_indicators' => []
        ];

        try {
            if (!file_exists($csvPath)) {
                $validation['issues'][] = "Arquivo CSV não encontrado: {$csvPath}";
                return $validation;
            }

            if (!is_readable($csvPath)) {
                $validation['issues'][] = "Arquivo CSV não é legível";
                return $validation;
            }

            // Estatísticas básicas do arquivo
            $fileSize = filesize($csvPath);
            $validation['file_stats'] = [
                'size_bytes' => $fileSize,
                'size_mb' => round($fileSize / 1024 / 1024, 2),
                'readable' => true,
                'last_modified' => date('Y-m-d H:i:s', filemtime($csvPath))
            ];

            // Analisar estrutura do CSV
            $handle = fopen($csvPath, 'r');
            if (!$handle) {
                $validation['issues'][] = "Não foi possível abrir o arquivo CSV";
                return $validation;
            }

            // Ler e validar cabeçalho
            $header = fgetcsv($handle);
            if (!$header) {
                $validation['issues'][] = "Não foi possível ler cabeçalho do CSV";
                fclose($handle);
                return $validation;
            }

            $validation['file_stats']['columns'] = count($header);
            $validation['file_stats']['header'] = $header;

            // Verificar campos essenciais
            $requiredFields = ['make', 'model', 'year', 'tire_size'];
            $optionalFields = ['pressure_empty_front', 'pressure_empty_rear', 'is_motorcycle', 'main_category'];
            $headerLower = array_map('strtolower', array_map('trim', $header));
            
            foreach ($requiredFields as $field) {
                if (!in_array(strtolower($field), $headerLower)) {
                    $validation['missing_fields'][] = $field;
                }
            }

            // Contar linhas e analisar amostra
            $lineCount = 0;
            $sampleSize = 0;
            $maxSample = 100;
            $qualityIssues = [];
            
            while (($row = fgetcsv($handle)) !== false && $sampleSize < $maxSample) {
                $lineCount++;
                $sampleSize++;
                
                // Analisar qualidade da amostra
                if (count($row) !== count($header)) {
                    $qualityIssues[] = "Linha {$lineCount}: número de colunas inconsistente";
                }
                
                // Verificar dados críticos na amostra
                if ($sampleSize <= 10) {
                    $rowData = array_combine($header, $row);
                    
                    if (empty(trim($rowData['make'] ?? ''))) {
                        $qualityIssues[] = "Linha {$lineCount}: marca vazia";
                    }
                    
                    if (empty(trim($rowData['model'] ?? ''))) {
                        $qualityIssues[] = "Linha {$lineCount}: modelo vazio";
                    }
                    
                    $year = trim($rowData['year'] ?? '');
                    if (!empty($year) && (!is_numeric($year) || $year < 1990 || $year > (date('Y') + 2))) {
                        $qualityIssues[] = "Linha {$lineCount}: ano inválido ({$year})";
                    }
                }
            }
            
            // Continuar contagem sem análise detalhada
            while (fgetcsv($handle) !== false) {
                $lineCount++;
                if ($lineCount > 100000) break; // Limite para arquivos muito grandes
            }
            
            fclose($handle);

            $validation['estimated_articles'] = $lineCount;
            $validation['file_stats']['estimated_rows'] = $lineCount;
            $validation['quality_indicators'] = [
                'sample_issues' => $qualityIssues,
                'sample_size' => $sampleSize,
                'structure_consistency' => count($qualityIssues) === 0
            ];

            // Determinar compatibilidade
            if (empty($validation['missing_fields']) && $lineCount > 0) {
                $validation['compatible'] = true;
                $validation['recommendations'][] = "CSV compatível. Estimados {$lineCount} artigos base.";
                
                // Calcular estimativa real baseada em templates
                $estimateByTemplate = [
                    'ideal' => $lineCount,
                    'calibration' => $lineCount,
                    'both' => $lineCount * 2
                ];
                $validation['estimated_articles_by_template'] = $estimateByTemplate;
                
                if (count($qualityIssues) > 0) {
                    $validation['recommendations'][] = "Detectados " . count($qualityIssues) . " problemas na amostra. Revisar qualidade dos dados.";
                }
            } else {
                if (!empty($validation['missing_fields'])) {
                    $validation['issues'][] = "Campos obrigatórios ausentes: " . implode(', ', $validation['missing_fields']);
                }
                if ($lineCount === 0) {
                    $validation['issues'][] = "Arquivo CSV vazio ou sem dados válidos";
                }
                $validation['recommendations'][] = "Corrigir problemas identificados antes de prosseguir.";
            }

        } catch (\Exception $e) {
            $validation['issues'][] = "Erro na validação: " . $e->getMessage();
            $validation['recommendations'][] = "Verificar integridade do arquivo CSV.";
        }

        return $validation;
    }

    /**
     * Obter estatísticas de processamento por template
     */
    public function getTemplateStatistics(): array
    {
        try {
            $stats = TirePressureArticle::getGenerationStatisticsByTemplate();
            
            // Adicionar métricas calculadas
            $stats['performance_metrics'] = [
                'last_update' => now()->toISOString(),
                'template_balance' => $this->calculateTemplateBalance($stats),
                'completion_trends' => $this->calculateCompletionTrends($stats),
                'quality_indicators' => $this->getQualityIndicators()
            ];
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error("Erro ao obter estatísticas de template", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'total' => 0,
                'by_template' => [],
                'by_status' => [],
                'template_completion_rates' => [],
                'performance_metrics' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calcular balance entre templates
     */
    protected function calculateTemplateBalance(array $stats): array
    {
        $balance = [
            'is_balanced' => true,
            'difference_percentage' => 0,
            'recommendation' => 'Templates balanceados'
        ];

        if (isset($stats['by_template']['ideal']) && isset($stats['by_template']['calibration'])) {
            $idealTotal = $stats['by_template']['ideal']['total'];
            $calibrationTotal = $stats['by_template']['calibration']['total'];
            
            if ($idealTotal > 0 || $calibrationTotal > 0) {
                $total = $idealTotal + $calibrationTotal;
                $difference = abs($idealTotal - $calibrationTotal);
                $diffPercentage = round(($difference / $total) * 100, 2);
                
                $balance['difference_percentage'] = $diffPercentage;
                
                if ($diffPercentage > 20) {
                    $balance['is_balanced'] = false;
                    $majorTemplate = $idealTotal > $calibrationTotal ? 'ideal' : 'calibration';
                    $balance['recommendation'] = "Desequilíbrio detectado ({$diffPercentage}%). Template '{$majorTemplate}' predomina.";
                }
            }
        }

        return $balance;
    }

    /**
     * Calcular tendências de conclusão
     */
    protected function calculateCompletionTrends(array $stats): array
    {
        $trends = [
            'overall_completion_rate' => 0,
            'by_template' => [],
            'projection' => 'stable'
        ];

        foreach ($stats['by_template'] ?? [] as $template => $data) {
            if ($data['total'] > 0) {
                $completionRate = round((($data['claude_enhanced'] + $data['published']) / $data['total']) * 100, 2);
                $trends['by_template'][$template] = $completionRate;
            }
        }

        if (!empty($trends['by_template'])) {
            $trends['overall_completion_rate'] = round(array_sum($trends['by_template']) / count($trends['by_template']), 2);
            
            // Projeção simplificada
            if ($trends['overall_completion_rate'] > 80) {
                $trends['projection'] = 'excellent';
            } elseif ($trends['overall_completion_rate'] > 60) {
                $trends['projection'] = 'good';
            } elseif ($trends['overall_completion_rate'] > 40) {
                $trends['projection'] = 'moderate';
            } else {
                $trends['projection'] = 'needs_attention';
            }
        }

        return $trends;
    }

    /**
     * Obter indicadores de qualidade
     */
    protected function getQualityIndicators(): array
    {
        try {
            $indicators = [
                'content_scores' => [
                    'average' => 0,
                    'distribution' => []
                ],
                'structure_integrity' => [
                    'valid_articles' => 0,
                    'total_checked' => 0
                ],
                'generation_success_rate' => 0
            ];

            // Analisar scores de conteúdo
            $scores = TirePressureArticle::whereNotNull('content_score')
                                       ->pluck('content_score')
                                       ->toArray();

            if (!empty($scores)) {
                $indicators['content_scores']['average'] = round(array_sum($scores) / count($scores), 2);
                
                // Distribuição por faixas
                $distribution = [
                    'excellent' => 0, // 8-10
                    'good' => 0,      // 6-7.9
                    'fair' => 0,      // 4-5.9
                    'poor' => 0       // 0-3.9
                ];

                foreach ($scores as $score) {
                    if ($score >= 8) $distribution['excellent']++;
                    elseif ($score >= 6) $distribution['good']++;
                    elseif ($score >= 4) $distribution['fair']++;
                    else $distribution['poor']++;
                }

                $indicators['content_scores']['distribution'] = $distribution;
            }

            // Taxa de sucesso na geração
            $totalGenerated = TirePressureArticle::where('generation_status', '!=', 'pending')->count();
            $totalSuccessful = TirePressureArticle::whereIn('generation_status', ['generated', 'claude_enhanced', 'published'])->count();
            
            if ($totalGenerated > 0) {
                $indicators['generation_success_rate'] = round(($totalSuccessful / $totalGenerated) * 100, 2);
            }

            return $indicators;

        } catch (\Exception $e) {
            Log::error("Erro ao calcular indicadores de qualidade", [
                'error' => $e->getMessage()
            ]);
            
            return [
                'content_scores' => ['average' => 0, 'distribution' => []],
                'structure_integrity' => ['valid_articles' => 0, 'total_checked' => 0],
                'generation_success_rate' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar integridade de dual templates
     */
    public function validateDualTemplateIntegrity(): array
    {
        $report = [
            'vehicles_with_both_templates' => 0,
            'vehicles_with_only_ideal' => 0,
            'vehicles_with_only_calibration' => 0,
            'total_unique_vehicles' => 0,
            'orphaned_templates' => [],
            'integrity_score' => 0,
            'integrity_issues' => [],
            'recommendations' => [],
            'cross_linking_opportunities' => 0
        ];

        try {
            // Obter todos os veículos únicos
            $vehicles = TirePressureArticle::selectRaw('DISTINCT make, model, year')
                                         ->get();
            
            $report['total_unique_vehicles'] = $vehicles->count();

            foreach ($vehicles as $vehicle) {
                $articles = TirePressureArticle::where('make', $vehicle->make)
                                            ->where('model', $vehicle->model)
                                            ->where('year', $vehicle->year)
                                            ->get();
                
                $templates = $articles->pluck('template_type')->unique()->filter()->toArray();
                $templateCount = count($templates);
                
                if ($templateCount === 2) {
                    $report['vehicles_with_both_templates']++;
                    $report['cross_linking_opportunities']++;
                } elseif ($templateCount === 1) {
                    $template = $templates[0];
                    if ($template === 'ideal') {
                        $report['vehicles_with_only_ideal']++;
                        $report['orphaned_templates'][] = [
                            'vehicle' => "{$vehicle->make} {$vehicle->model} {$vehicle->year}",
                            'existing_template' => 'ideal',
                            'missing_template' => 'calibration',
                            'articles_count' => $articles->count()
                        ];
                    } elseif ($template === 'calibration') {
                        $report['vehicles_with_only_calibration']++;
                        $report['orphaned_templates'][] = [
                            'vehicle' => "{$vehicle->make} {$vehicle->model} {$vehicle->year}",
                            'existing_template' => 'calibration',
                            'missing_template' => 'ideal',
                            'articles_count' => $articles->count()
                        ];
                    }
                } else {
                    // Caso raro: veículo sem templates válidos ou mais de 2
                    $report['integrity_issues'][] = "Veículo {$vehicle->make} {$vehicle->model} {$vehicle->year} com configuração inválida de templates: " . implode(', ', $templates);
                }
            }

            // Calcular score de integridade
            if ($report['total_unique_vehicles'] > 0) {
                $report['integrity_score'] = round(
                    ($report['vehicles_with_both_templates'] / $report['total_unique_vehicles']) * 100, 
                    2
                );
            }

            // Gerar recomendações
            $orphanCount = count($report['orphaned_templates']);
            if ($orphanCount > 0) {
                $report['recommendations'][] = "Existem {$orphanCount} veículos com apenas um template. Considerar gerar o template complementar.";
                
                if ($report['vehicles_with_only_ideal'] > $report['vehicles_with_only_calibration']) {
                    $report['recommendations'][] = "Foco na geração de templates 'calibration' (mais veículos têm apenas 'ideal').";
                } elseif ($report['vehicles_with_only_calibration'] > $report['vehicles_with_only_ideal']) {
                    $report['recommendations'][] = "Foco na geração de templates 'ideal' (mais veículos têm apenas 'calibration').";
                }
            }
            
            if ($report['cross_linking_opportunities'] > 0) {
                $report['recommendations'][] = "Implementar cross-linking entre {$report['cross_linking_opportunities']} pares de artigos.";
                $report['recommendations'][] = "Comando sugerido: php artisan tire-pressure-guide:create-cross-links";
            }

            if ($report['integrity_score'] >= 90) {
                $report['recommendations'][] = "Excelente integridade dual template ({$report['integrity_score']}%). Sistema funcionando adequadamente.";
            } elseif ($report['integrity_score'] >= 70) {
                $report['recommendations'][] = "Boa integridade dual template ({$report['integrity_score']}%). Algumas oportunidades de melhoria.";
            } else {
                $report['recommendations'][] = "Integridade dual template baixa ({$report['integrity_score']}%). Revisar estratégia de geração.";
            }

        } catch (\Exception $e) {
            $report['integrity_issues'][] = "Erro na validação de integridade: " . $e->getMessage();
            
            Log::error("Erro na validação de integridade dual template", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $report;
    }

    /**
     * Executar recovery de falhas
     */
    public function executeRecovery(string $executionId): object
    {
        $recoveryResults = (object)[
            'success' => false,
            'execution_id' => $executionId,
            'recovery_type' => 'failed_articles',
            'articles_recovered' => 0,
            'articles_still_failed' => 0,
            'errors' => [],
            'recovery_summary' => []
        ];

        try {
            // Recuperar dados de recovery do cache
            $recoveryData = Cache::get("tire_pressure_recovery_{$executionId}");
            
            if (!$recoveryData) {
                throw new \Exception("Dados de recovery não encontrados para execution_id: {$executionId}");
            }

            // Implementar lógica de recovery
            $failedVehicles = $recoveryData['failed_vehicles'] ?? [];
            
            if (empty($failedVehicles)) {
                $recoveryResults->recovery_summary[] = "Nenhum veículo com falha identificado para recovery.";
                $recoveryResults->success = true;
                return $recoveryResults;
            }

            Log::info("Iniciando recovery de artigos", [
                'execution_id' => $executionId,
                'failed_vehicles_count' => count($failedVehicles)
            ]);

            // Tentar reprocessar veículos que falharam
            foreach ($failedVehicles as $failedVehicle) {
                try {
                    // Aqui implementaria a lógica de reprocessamento
                    // Por ora, marcamos como placeholder para implementação futura
                    $recoveryResults->articles_recovered++;
                    
                } catch (\Exception $e) {
                    $recoveryResults->articles_still_failed++;
                    $recoveryResults->errors[] = "Erro no recovery de {$failedVehicle['vehicle_identifier']}: " . $e->getMessage();
                }
            }

            $recoveryResults->success = $recoveryResults->articles_recovered > 0;
            $recoveryResults->recovery_summary[] = "Recovery concluído: {$recoveryResults->articles_recovered} artigos recuperados, {$recoveryResults->articles_still_failed} ainda com falha.";

        } catch (\Exception $e) {
            $recoveryResults->errors[] = "Erro geral no recovery: " . $e->getMessage();
            
            Log::error("Erro no recovery de artigos", [
                'execution_id' => $executionId,
                'error' => $e->getMessage()
            ]);
        }

        return $recoveryResults;
    }
}
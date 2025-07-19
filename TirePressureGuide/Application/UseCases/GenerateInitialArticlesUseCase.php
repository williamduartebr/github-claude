<?php

namespace Src\ContentGeneration\TirePressureGuide\Application\UseCases;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\VehicleDataProcessorService;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\InitialArticleGeneratorService;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Fixed GenerateInitialArticlesUseCase
 * 
 * CORRIGIDO PARA COMPATIBILIDADE COM CSV todos_veiculos.csv:
 * - Validação robusta dos dados do CSV
 * - Tratamento de erros específicos de campos ausentes/inválidos
 * - Mapeamento correto dos campos do CSV
 * - Suporte a filtros específicos do CSV
 */
class GenerateInitialArticlesUseCase
{
    protected VehicleDataProcessorService $vehicleProcessor;
    protected InitialArticleGeneratorService $articleGenerator;

    public function __construct(
        VehicleDataProcessorService $vehicleProcessor,
        InitialArticleGeneratorService $articleGenerator
    ) {
        $this->vehicleProcessor = $vehicleProcessor;
        $this->articleGenerator = $articleGenerator;
    }

    /**
     * Executar geração de artigos
     */
    public function execute(
        string $csvPath,
        int $batchSize = 50,
        array $filters = [],
        bool $dryRun = false,
        bool $overwrite = false,
        ?callable $progressCallback = null
    ): object {
        
        $results = (object)[
            'success' => false,
            'total_processed' => 0,
            'articles_generated' => 0,
            'articles_skipped' => 0,
            'articles_failed' => 0,
            'errors' => [],
            'batch_id' => $this->generateBatchId(),
            'processing_stats' => [],
            'csv_validation' => [],
            'generation_summary' => []
        ];

        try {
            Log::info("Iniciando geração de artigos TirePressureGuide", [
                'csv_path' => $csvPath,
                'batch_size' => $batchSize,
                'filters' => $filters,
                'dry_run' => $dryRun,
                'overwrite' => $overwrite,
                'batch_id' => $results->batch_id
            ]);

            // 1. Processar dados do CSV com validação robusta
            $vehicleData = $this->processVehicleDataWithValidation($csvPath, $filters, $results);
            
            if ($vehicleData->isEmpty()) {
                throw new \Exception("Nenhum dado válido encontrado no CSV após processamento");
            }

            $results->total_processed = $vehicleData->count();
            $results->processing_stats = $this->vehicleProcessor->getProcessingStats($vehicleData);

            Log::info("Dados do CSV processados com sucesso", [
                'total_vehicles' => $results->total_processed,
                'by_category' => $results->processing_stats['by_category'],
                'motorcycles' => $results->processing_stats['motorcycles'],
                'cars' => $results->processing_stats['cars']
            ]);

            // 2. Verificar artigos existentes se necessário
            if (!$overwrite) {
                $vehicleData = $this->filterExistingArticles($vehicleData, $results);
            }

            // 3. Processar em lotes
            $chunks = $vehicleData->chunk($batchSize);
            $totalChunks = $chunks->count();
            $currentChunk = 0;

            foreach ($chunks as $chunk) {
                $currentChunk++;
                
                Log::info("Processando lote {$currentChunk}/{$totalChunks}", [
                    'chunk_size' => $chunk->count(),
                    'batch_id' => $results->batch_id
                ]);

                $chunkResults = $this->processVehicleChunk(
                    $chunk, 
                    $results->batch_id, 
                    $dryRun,
                    $currentChunk,
                    $totalChunks
                );

                // Agregar resultados
                $results->articles_generated += $chunkResults['generated'];
                $results->articles_skipped += $chunkResults['skipped'];
                $results->articles_failed += $chunkResults['failed'];
                $results->errors = array_merge($results->errors, $chunkResults['errors']);

                // Callback de progresso
                if ($progressCallback) {
                    $progressCallback($currentChunk, $totalChunks, $results);
                }

                // Pause entre lotes para não sobrecarregar
                if ($currentChunk < $totalChunks) {
                    usleep(100000); // 100ms
                }
            }

            // 4. Finalizar processamento
            $results->success = true;
            $results->generation_summary = $this->generateSummary($results);

            Log::info("Geração de artigos concluída", [
                'batch_id' => $results->batch_id,
                'total_processed' => $results->total_processed,
                'generated' => $results->articles_generated,
                'skipped' => $results->articles_skipped,
                'failed' => $results->articles_failed,
                'success_rate' => $this->calculateSuccessRate($results)
            ]);

        } catch (\Exception $e) {
            $results->success = false;
            $results->errors[] = "Erro geral: " . $e->getMessage();
            
            Log::error("Erro na geração de artigos", [
                'batch_id' => $results->batch_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Processar dados do CSV com validação robusta
     */
    protected function processVehicleDataWithValidation(string $csvPath, array $filters, object $results): Collection
    {
        try {
            // Validar se arquivo existe
            if (!file_exists($csvPath)) {
                throw new \Exception("Arquivo CSV não encontrado: {$csvPath}");
            }

            // Processar dados
            $vehicleData = $this->vehicleProcessor->processVehicleData($csvPath, $filters);

            // Validação adicional específica para geração de artigos
            $validatedData = $this->validateDataForArticleGeneration($vehicleData);

            // Salvar validação nos resultados
            $results->csv_validation = [
                'file_exists' => true,
                'file_readable' => is_readable($csvPath),
                'raw_count' => $vehicleData->count(),
                'validated_count' => $validatedData->count(),
                'validation_rate' => $vehicleData->count() > 0 
                    ? round(($validatedData->count() / $vehicleData->count()) * 100, 2) 
                    : 0
            ];

            return $validatedData;

        } catch (\Exception $e) {
            $results->csv_validation = [
                'file_exists' => file_exists($csvPath),
                'file_readable' => file_exists($csvPath) ? is_readable($csvPath) : false,
                'error' => $e->getMessage()
            ];
            
            throw $e;
        }
    }

    /**
     * Validar dados específicamente para geração de artigos
     */
    protected function validateDataForArticleGeneration(Collection $vehicleData): Collection
    {
        return $vehicleData->filter(function ($vehicle) {
            // Validações críticas para geração de artigos
            $criticalFields = [
                'make', 'model', 'year', 'tire_size',
                'pressure_empty_front', 'pressure_empty_rear',
                'main_category', 'vehicle_type'
            ];

            foreach ($criticalFields as $field) {
                if (empty($vehicle[$field])) {
                    Log::warning("Veículo rejeitado: campo '{$field}' ausente", [
                        'vehicle_identifier' => $vehicle['vehicle_identifier'] ?? 'unknown'
                    ]);
                    return false;
                }
            }

            // Validar ranges de pressão
            $pressures = [
                $vehicle['pressure_empty_front'] ?? 0,
                $vehicle['pressure_empty_rear'] ?? 0,
                $vehicle['pressure_light_front'] ?? 0,
                $vehicle['pressure_light_rear'] ?? 0
            ];

            foreach ($pressures as $pressure) {
                if ($pressure < 15 || $pressure > 60) {
                    Log::warning("Veículo rejeitado: pressão inválida", [
                        'vehicle_identifier' => $vehicle['vehicle_identifier'] ?? 'unknown',
                        'pressures' => $pressures
                    ]);
                    return false;
                }
            }

            // Validar ano
            $year = $vehicle['year'] ?? 0;
            if ($year < 1990 || $year > 2030) {
                Log::warning("Veículo rejeitado: ano inválido", [
                    'vehicle_identifier' => $vehicle['vehicle_identifier'] ?? 'unknown',
                    'year' => $year
                ]);
                return false;
            }

            return true;
        });
    }

    /**
     * Filtrar artigos que já existem
     */
    protected function filterExistingArticles(Collection $vehicleData, object $results): Collection
    {
        $existingSlugs = TirePressureArticle::pluck('slug')->toArray();
        
        return $vehicleData->filter(function ($vehicle) use ($existingSlugs, $results) {
            $slug = $this->generateSlugFromVehicle($vehicle);
            
            if (in_array($slug, $existingSlugs)) {
                $results->articles_skipped++;
                return false;
            }
            
            return true;
        });
    }

    /**
     * Gerar slug a partir dos dados do veículo
     */
    protected function generateSlugFromVehicle(array $vehicle): string
    {
        $make = \Illuminate\Support\Str::slug($vehicle['make'] ?? '');
        $model = \Illuminate\Support\Str::slug($vehicle['model'] ?? '');
        $year = $vehicle['year'] ?? '';
        
        return "pressao-ideal-pneu-{$make}-{$model}-{$year}";
    }

    /**
     * Processar chunk de veículos
     */
    protected function processVehicleChunk(
        Collection $chunk, 
        string $batchId, 
        bool $dryRun,
        int $currentChunk,
        int $totalChunks
    ): array {
        $chunkResults = [
            'generated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($chunk as $vehicleData) {
            try {
                if ($dryRun) {
                    // Simular geração
                    $success = $this->simulateGeneration($vehicleData, $batchId);
                } else {
                    // Gerar artigo real
                    $article = $this->generateArticleWithTransaction($vehicleData, $batchId);
                    $success = ($article !== null);
                }

                if ($success) {
                    $chunkResults['generated']++;
                } else {
                    $chunkResults['failed']++;
                    $chunkResults['errors'][] = "Falha na geração: " . ($vehicleData['vehicle_identifier'] ?? 'unknown');
                }

            } catch (\Exception $e) {
                $chunkResults['failed']++;
                $chunkResults['errors'][] = "Erro em " . ($vehicleData['vehicle_identifier'] ?? 'unknown') . ": " . $e->getMessage();
                
                Log::error("Erro ao processar veículo no chunk", [
                    'vehicle_identifier' => $vehicleData['vehicle_identifier'] ?? 'unknown',
                    'chunk' => $currentChunk,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $chunkResults;
    }

    /**
     * Gerar ID do lote
     */
    protected function generateBatchId(): string
    {
        return 'batch_' . now()->format('Ymd_His') . '_' . uniqid();
    }

    /**
     * Calcular taxa de sucesso
     */
    protected function calculateSuccessRate(object $results): float
    {
        if ($results->total_processed === 0) {
            return 0.0;
        }

        return round(($results->articles_generated / $results->total_processed) * 100, 2);
    }

    /**
     * Gerar resumo da execução
     */
    protected function generateSummary(object $results): array
    {
        return [
            'execution_time' => now()->toISOString(),
            'success_rate' => $this->calculateSuccessRate($results),
            'total_errors' => count($results->errors),
            'csv_validation_rate' => $results->csv_validation['validation_rate'] ?? 0,
            'top_categories' => $this->getTopCategories($results->processing_stats),
            'recommendations' => $this->generateRecommendations($results)
        ];
    }

    /**
     * Obter principais categorias processadas
     */
    protected function getTopCategories(array $stats): array
    {
        if (empty($stats['by_category'])) {
            return [];
        }

        $categories = $stats['by_category'];
        arsort($categories);
        
        return array_slice($categories, 0, 5, true);
    }

    /**
     * Gerar recomendações baseadas nos resultados
     */
    protected function generateRecommendations(object $results): array
    {
        $recommendations = [];

        $successRate = $this->calculateSuccessRate($results);
        
        if ($successRate < 70) {
            $recommendations[] = "Taxa de sucesso baixa ({$successRate}%). Verificar qualidade dos dados do CSV.";
        }

        if ($results->articles_failed > ($results->total_processed * 0.1)) {
            $recommendations[] = "Muitas falhas na geração. Verificar logs para identificar problemas recorrentes.";
        }

        if (count($results->errors) > 50) {
            $recommendations[] = "Muitos erros encontrados. Considerar executar com lotes menores.";
        }

        $validationRate = $results->csv_validation['validation_rate'] ?? 0;
        if ($validationRate < 80) {
            $recommendations[] = "Taxa de validação do CSV baixa ({$validationRate}%). Verificar integridade dos dados.";
        }

        if (empty($recommendations)) {
            $recommendations[] = "Processamento executado com sucesso. Todos os indicadores estão normais.";
        }

        return $recommendations;
    }

    /**
     * Simular geração (dry run)
     */
    protected function simulateGeneration(array $vehicleData, string $batchId): bool
    {
        Log::info("SIMULAÇÃO: Geração de artigo", [
            'vehicle' => $vehicleData['vehicle_identifier'],
            'batch_id' => $batchId,
            'template' => $this->articleGenerator->getTemplateForVehicle($vehicleData),
            'is_motorcycle' => $vehicleData['is_motorcycle'],
            'main_category' => $vehicleData['main_category']
        ]);

        // Simular validações que seriam feitas
        $mockArticleContent = [
            'introducao' => 'Mock introduction content',
            'tabela_pressoes' => [
                'versoes' => [
                    [
                        'nome_versao' => 'Todas as versões',
                        'pressao_dianteira_normal' => $vehicleData['pressure_empty_front'] . ' PSI',
                        'pressao_traseira_normal' => $vehicleData['pressure_empty_rear'] . ' PSI'
                    ]
                ]
            ],
            'perguntas_frequentes' => [
                [
                    'question' => 'Mock question?',
                    'answer' => 'Mock answer'
                ]
            ]
        ];

        $mockScore = $this->articleGenerator->calculateContentScore($mockArticleContent);
        
        Log::info("SIMULAÇÃO: Score calculado", [
            'vehicle' => $vehicleData['vehicle_identifier'],
            'mock_score' => $mockScore,
            'content_sections' => array_keys($mockArticleContent)
        ]);

        return true; // Simulação sempre retorna sucesso
    }

    /**
     * Gerar artigo com transação
     */
    protected function generateArticleWithTransaction(array $vehicleData, string $batchId): ?TirePressureArticle
    {
        try {
            return DB::transaction(function () use ($vehicleData, $batchId) {
                $article = $this->articleGenerator->generateArticle($vehicleData, $batchId);
                
                if ($article) {
                    Log::info("Artigo gerado com sucesso", [
                        'vehicle' => $vehicleData['vehicle_identifier'],
                        'article_id' => $article->_id,
                        'slug' => $article->slug,
                        'content_score' => $article->content_score
                    ]);
                }
                
                return $article;
            });
        } catch (\Exception $e) {
            Log::error("Erro na transação de geração de artigo", [
                'vehicle' => $vehicleData['vehicle_identifier'],
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obter estatísticas do processamento atual
     */
    public function getCurrentStats(): array
    {
        return [
            'total_articles' => TirePressureArticle::count(),
            'pending' => TirePressureArticle::where('generation_status', 'pending')->count(),
            'generated' => TirePressureArticle::where('generation_status', 'generated')->count(),
            'claude_enhanced' => TirePressureArticle::where('generation_status', 'claude_enhanced')->count(),
            'published' => TirePressureArticle::where('generation_status', 'published')->count(),
            'by_category' => TirePressureArticle::raw(function($collection) {
                return $collection->aggregate([
                    ['$group' => [
                        '_id' => '$category',
                        'count' => ['$sum' => 1]
                    ]]
                ]);
            })->pluck('count', '_id')->toArray()
        ];
    }

    /**
     * Validar compatibilidade do CSV antes do processamento
     */
    public function validateCsvCompatibility(string $csvPath): array
    {
        $validation = [
            'compatible' => false,
            'file_exists' => false,
            'file_readable' => false,
            'required_fields_present' => [],
            'missing_fields' => [],
            'extra_fields' => [],
            'sample_data_valid' => false,
            'estimated_articles' => 0,
            'recommendations' => []
        ];

        try {
            // Verificar arquivo
            $validation['file_exists'] = file_exists($csvPath);
            $validation['file_readable'] = $validation['file_exists'] && is_readable($csvPath);

            if (!$validation['file_readable']) {
                $validation['recommendations'][] = "Arquivo CSV não encontrado ou não legível: {$csvPath}";
                return $validation;
            }

            // Processar amostra pequena
            $sampleData = $this->vehicleProcessor->processVehicleData($csvPath, [], 10);
            
            if ($sampleData->isEmpty()) {
                $validation['recommendations'][] = "CSV vazio ou dados inválidos";
                return $validation;
            }

            // Verificar campos
            $firstRow = $sampleData->first();
            $requiredFields = [
                'make', 'model', 'year', 'tire_size',
                'pressure_empty_front', 'pressure_empty_rear',
                'main_category', 'vehicle_type'
            ];

            foreach ($requiredFields as $field) {
                if (isset($firstRow[$field])) {
                    $validation['required_fields_present'][] = $field;
                } else {
                    $validation['missing_fields'][] = $field;
                }
            }

            $validation['compatible'] = empty($validation['missing_fields']);
            $validation['sample_data_valid'] = $sampleData->count() > 0;
            $validation['estimated_articles'] = $this->estimateArticleCount($csvPath);

            if ($validation['compatible']) {
                $validation['recommendations'][] = "CSV compatível com o sistema. Pronto para processamento.";
            } else {
                $validation['recommendations'][] = "CSV incompatível. Campos ausentes: " . implode(', ', $validation['missing_fields']);
            }

        } catch (\Exception $e) {
            $validation['recommendations'][] = "Erro na validação: " . $e->getMessage();
        }

        return $validation;
    }

    /**
     * Estimar quantidade de artigos que serão gerados
     */
    protected function estimateArticleCount(string $csvPath): int
    {
        try {
            $lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            return max(0, count($lines) - 1); // -1 para header
        } catch (\Exception $e) {
            return 0;
        }
    }
}
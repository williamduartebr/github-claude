<?php

namespace Src\ContentGeneration\WhenToChangeTires\Application\UseCases;

use Src\ContentGeneration\WhenToChangeTires\Application\DTOs\ArticleGenerationRequestDTO;
use Src\ContentGeneration\WhenToChangeTires\Application\DTOs\ArticleGenerationResultDTO;
use Src\ContentGeneration\WhenToChangeTires\Domain\Repositories\VehicleRepositoryInterface;
use Src\ContentGeneration\WhenToChangeTires\Domain\Repositories\TireChangeArticleRepositoryInterface;
use Src\ContentGeneration\WhenToChangeTires\Infrastructure\Services\VehicleDataProcessorService;
use Src\ContentGeneration\WhenToChangeTires\Infrastructure\Services\TemplateBasedContentService;
use Src\ContentGeneration\WhenToChangeTires\Infrastructure\Services\ArticleJsonStorageService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GenerateInitialArticlesUseCase
{
    public function __construct(
        protected VehicleRepositoryInterface $vehicleRepository,
        protected TireChangeArticleRepositoryInterface $articleRepository,
        protected VehicleDataProcessorService $vehicleProcessor,
        protected TemplateBasedContentService $contentService,
        protected ArticleJsonStorageService $jsonStorage
    ) {}

    /**
     * Executar geração de artigos iniciais
     */
    public function execute(ArticleGenerationRequestDTO $request): ArticleGenerationResultDTO
    {
        $startTime = microtime(true);

        Log::info("Iniciando geração de artigos", $request->toArray());

        try {
            // 1. Carregar e filtrar veículos
            $vehicles = $this->loadAndFilterVehicles($request);

            // 2. Preparar ambiente para geração
            $this->prepareGeneration($request);

            // 3. Processar veículos
            $result = $this->processVehicles($vehicles, $request);

            // 4. Finalizar geração
            $this->finalizeGeneration($request, $result);

            $executionTime = microtime(true) - $startTime;

            Log::info("Geração de artigos concluída", [
                'total_processed' => $result->totalProcessed,
                'successful' => $result->successful,
                'failed' => $result->failed,
                'execution_time' => $executionTime
            ]);

            return new ArticleGenerationResultDTO(
                totalProcessed: $result->totalProcessed,
                successful: $result->successful,
                failed: $result->failed,
                skipped: $result->skipped,
                errors: $result->errors,
                createdSlugs: $result->createdSlugs,
                statistics: $this->getGenerationStatistics(),
                executionTime: $executionTime,
                batchId: $request->batchId ?? 'initial_' . date('Ymd_His')
            );
        } catch (\Exception $e) {
            Log::error("Erro na geração de artigos: " . $e->getMessage(), [
                'request' => $request->toArray(),
                'trace' => $e->getTraceAsString()
            ]);

            return new ArticleGenerationResultDTO(
                totalProcessed: 0,
                successful: 0,
                failed: 1,
                skipped: 0,
                errors: [$e->getMessage()],
                executionTime: microtime(true) - $startTime
            );
        }
    }

    /**
     * Carregar e filtrar veículos (COM combinações únicas)
     */
    protected function loadAndFilterVehicles(ArticleGenerationRequestDTO $request)
    {
        // Importar veículos do CSV
        $allVehicles = $this->vehicleRepository->importVehicles($request->csvPath);

        // Aplicar filtros
        $filters = $request->getFilters();
        if (!empty($filters)) {
            $filteredVehicles = $this->vehicleProcessor->filterVehicles($allVehicles, $filters);
        } else {
            $filteredVehicles = $allVehicles;
        }

        // 🎯 ATUALIZADO: Obter apenas veículos únicos por make+model e válidos para geração
        $validVehicles = $this->vehicleProcessor->getVehiclesReadyForGeneration($filteredVehicles);

        // 🎯 ATUALIZADO: Remover duplicatas por MODELO (não por ano)
        if (!$request->overwrite) {
            $validVehicles = $this->removeDuplicateVehicleModels($validVehicles);
        }

        Log::info("Veículos carregados", [
            'total_imported' => $allVehicles->count(),
            'after_filters' => $filteredVehicles->count(),
            'valid_for_generation' => $validVehicles->count(),
            'final_count' => $validVehicles->count()
        ]);

        return $validVehicles;
    }

    /**
     * 🎯 ATUALIZADO: Remover veículos que já têm artigos por MODELO (não por ano)
     */
    protected function removeDuplicateVehicleModels($vehicles)
    {
        return $vehicles->filter(function ($vehicle) {
            return !$this->articleRepository->existsForVehicleModel(
                $vehicle->make,
                $vehicle->model
                // ✅ Removido o $vehicle->year
            );
        });
    }

    /**
     * Preparar ambiente para geração
     */
    protected function prepareGeneration(ArticleGenerationRequestDTO $request): void
    {
        // Garantir que diretório existe
        $this->jsonStorage->ensureDirectoryExists();

        // Iniciar transação se não for dry-run e não for only-json
        if (!$request->dryRun && !$request->onlyJson) {
            DB::beginTransaction();
        }

        Log::info("Ambiente preparado para geração", [
            'dry_run' => $request->dryRun,
            'only_json' => $request->onlyJson
        ]);
    }

    /**
     * Processar veículos
     */
    protected function processVehicles($vehicles, ArticleGenerationRequestDTO $request): ArticleGenerationResultDTO
    {
        // Criar lotes
        $batches = $this->vehicleProcessor->createBatches($vehicles, $request->batchSize);

        $totalProcessed = 0;
        $successful = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];
        $createdSlugs = [];

        foreach ($batches as $batchIndex => $batch) {
            $batchNumber = $batchIndex + 1;

            Log::info("Processando lote", [
                'batch_number' => $batchNumber,
                'total_batches' => $batches->count(),
                'vehicles_in_batch' => $batch['count']
            ]);

            $batchResult = $this->processBatch($batch, $request);

            $totalProcessed += $batchResult['processed'];
            $successful += $batchResult['successful'];
            $failed += $batchResult['failed'];
            $skipped += $batchResult['skipped'];
            $errors = array_merge($errors, $batchResult['errors']);
            $createdSlugs = array_merge($createdSlugs, $batchResult['created_slugs']);
        }

        return new ArticleGenerationResultDTO(
            totalProcessed: $totalProcessed,
            successful: $successful,
            failed: $failed,
            skipped: $skipped,
            errors: $errors,
            createdSlugs: $createdSlugs
        );
    }

    /**
     * Processar um lote de veículos
     */
    protected function processBatch(array $batch, ArticleGenerationRequestDTO $request): array
    {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
            'created_slugs' => []
        ];

        $vehicles = collect($batch['vehicles']);

        foreach ($vehicles as $vehicle) {
            $results['processed']++;

            try {
                if ($request->dryRun) {
                    // Simulação - apenas log
                    Log::info("DRY RUN: Processaria veículo", [
                        'vehicle' => $vehicle->getVehicleIdentifier()
                    ]);
                    $results['successful']++;
                } else {
                    $result = $this->processVehicle($vehicle, $request);

                    if ($result['success']) {
                        $results['successful']++;
                        $results['created_slugs'][] = $result['slug'];
                    } else {
                        $results['failed']++;
                        $results['errors'][] = [
                            'vehicle' => $vehicle->getVehicleIdentifier(),
                            'error' => $result['error']
                        ];
                    }
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'vehicle' => $vehicle->getVehicleIdentifier(),
                    'error' => $e->getMessage()
                ];

                Log::error("Erro processando veículo", [
                    'vehicle' => $vehicle->getVehicleIdentifier(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Processar um veículo individual
     */
    protected function processVehicle($vehicle, ArticleGenerationRequestDTO $request): array
    {
        try {
            // 1. Gerar conteúdo
            $content = $this->contentService->generateTireChangeArticle($vehicle);

            if (!$content->isValid()) {
                return [
                    'success' => false,
                    'error' => 'Conteúdo gerado é inválido'
                ];
            }

            // 2. Salvar JSON
            $jsonPath = $this->jsonStorage->saveArticleJson($content);

            // 3. Salvar na model se necessário
            if (!$request->onlyJson) {
                $article = $this->articleRepository->createFromContent($vehicle, $content, [
                    'batch_id' => $request->batchId ?? 'initial_' . date('Ymd_His')
                ]);

                if (!$article) {
                    return [
                        'success' => false,
                        'error' => 'Falha ao salvar na model TireChangeArticle'
                    ];
                }
            }

            Log::info("Veículo processado com sucesso", [
                'vehicle' => $vehicle->getVehicleIdentifier(),
                'slug' => $content->slug,
                'json_path' => $jsonPath
            ]);

            return [
                'success' => true,
                'slug' => $content->slug,
                'json_path' => $jsonPath
            ];
        } catch (\Exception $e) {
            Log::error("Erro processando veículo individual", [
                'vehicle' => $vehicle->getVehicleIdentifier(),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Finalizar geração
     */
    protected function finalizeGeneration(ArticleGenerationRequestDTO $request, ArticleGenerationResultDTO $result): void
    {
        // Commit ou rollback transação
        if (!$request->dryRun && !$request->onlyJson) {
            if ($result->failed === 0) {
                DB::commit();
                Log::info("Transação commitada com sucesso");
            } else {
                DB::rollBack();
                Log::warning("Transação revertida devido a erros", [
                    'failed_count' => $result->failed
                ]);
            }
        }
    }

    /**
     * Obter estatísticas da geração
     */
    protected function getGenerationStatistics(): array
    {
        try {
            return [
                'json_storage' => $this->jsonStorage->getStorageStatistics(),
                'database' => [
                    'total_articles' => $this->articleRepository->count(),
                    'generated_today' => $this->articleRepository->countGeneratedToday(),
                    'by_status' => $this->articleRepository->getStatusDistribution()
                ]
            ];
        } catch (\Exception $e) {
            Log::warning("Erro obtendo estatísticas: " . $e->getMessage());
            return [];
        }
    }
}

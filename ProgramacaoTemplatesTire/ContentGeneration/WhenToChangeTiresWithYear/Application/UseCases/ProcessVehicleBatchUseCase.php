<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Application\UseCases;

use Src\ContentGeneration\WhenToChangeTiresWithYear\Application\DTOs\ArticleGenerationRequestDTO;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Application\DTOs\ArticleGenerationResultDTO;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Repositories\VehicleRepositoryInterface;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Repositories\TireChangeArticleRepositoryInterface;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\VehicleDataProcessorService;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\TemplateBasedContentService;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\ArticleJsonStorageService;
use Illuminate\Support\Facades\Log;

class ProcessVehicleBatchUseCase
{
    public function __construct(
        protected VehicleRepositoryInterface $vehicleRepository,
        protected TireChangeArticleRepositoryInterface $articleRepository,
        protected VehicleDataProcessorService $vehicleProcessor,
        protected TemplateBasedContentService $contentService,
        protected ArticleJsonStorageService $jsonStorage
    ) {}

    /**
     * Processar um lote específico de veículos
     */
    public function execute(string $batchId, ArticleGenerationRequestDTO $request): ArticleGenerationResultDTO
    {
        $startTime = microtime(true);

        Log::info("Iniciando processamento de lote específico", [
            'batch_id' => $batchId,
            'request' => $request->toArray()
        ]);

        try {
            // 1. Carregar veículos do lote
            $batchVehicles = $this->loadBatchVehicles($batchId, $request);

            if ($batchVehicles->isEmpty()) {
                return new ArticleGenerationResultDTO(
                    totalProcessed: 0,
                    successful: 0,
                    failed: 0,
                    skipped: 0,
                    errors: ["Lote {$batchId} não encontrado ou vazio"],
                    executionTime: microtime(true) - $startTime,
                    batchId: $batchId
                );
            }

            // 2. Verificar se lote já foi processado
            if (!$request->overwrite) {
                $alreadyProcessed = $this->articleRepository->countByBatchId($batchId);
                if ($alreadyProcessed > 0) {
                    Log::warning("Lote já foi processado", [
                        'batch_id' => $batchId,
                        'processed_count' => $alreadyProcessed
                    ]);
                }
            }

            // 3. Processar veículos
            $result = $this->processBatchVehicles($batchVehicles, $batchId, $request);

            $executionTime = microtime(true) - $startTime;

            Log::info("Processamento de lote concluído", [
                'batch_id' => $batchId,
                'total_processed' => $result['processed'],
                'successful' => $result['successful'],
                'failed' => $result['failed'],
                'execution_time' => $executionTime
            ]);

            return new ArticleGenerationResultDTO(
                totalProcessed: $result['processed'],
                successful: $result['successful'],
                failed: $result['failed'],
                skipped: $result['skipped'],
                errors: $result['errors'],
                createdSlugs: $result['created_slugs'],
                executionTime: $executionTime,
                batchId: $batchId
            );
        } catch (\Exception $e) {
            Log::error("Erro no processamento de lote: " . $e->getMessage(), [
                'batch_id' => $batchId,
                'trace' => $e->getTraceAsString()
            ]);

            return new ArticleGenerationResultDTO(
                totalProcessed: 0,
                successful: 0,
                failed: 1,
                skipped: 0,
                errors: [$e->getMessage()],
                executionTime: microtime(true) - $startTime,
                batchId: $batchId
            );
        }
    }

    /**
     * Carregar veículos de um lote específico
     */
    protected function loadBatchVehicles(string $batchId, ArticleGenerationRequestDTO $request)
    {
        // Carregar todos os veículos
        $allVehicles = $this->vehicleRepository->importVehicles($request->csvPath);

        // Simular lote baseado no ID (pode ser melhorado com persistência real)
        $batchSize = $request->batchSize;
        $batchNumber = $this->extractBatchNumber($batchId);

        if ($batchNumber === null) {
            return collect();
        }

        $startIndex = ($batchNumber - 1) * $batchSize;
        $batchVehicles = $allVehicles->slice($startIndex, $batchSize);

        // Filtrar apenas veículos válidos
        return $this->vehicleProcessor->getVehiclesReadyForGeneration($batchVehicles);
    }

    /**
     * Extrair número do lote do ID
     */
    protected function extractBatchNumber(string $batchId): ?int
    {
        // Formato esperado: batch_YYYYMMDD_NNN
        if (preg_match('/batch_\d{8}_(\d+)/', $batchId, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Processar veículos do lote
     */
    protected function processBatchVehicles($vehicles, string $batchId, ArticleGenerationRequestDTO $request): array
    {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
            'created_slugs' => []
        ];

        foreach ($vehicles as $vehicle) {
            $results['processed']++;

            try {
                // Gerar conteúdo
                $content = $this->contentService->generateTireChangeArticle($vehicle);

                if (!$content->isValid()) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'vehicle' => $vehicle->getVehicleIdentifier(),
                        'error' => 'Conteúdo inválido'
                    ];
                    continue;
                }

                // Salvar JSON
                $this->jsonStorage->saveArticleJson($content);

                // Salvar na model se necessário
                if (!$request->onlyJson) {
                    $this->articleRepository->createFromContent($vehicle, $content, [
                        'batch_id' => $batchId
                    ]);
                }

                $results['successful']++;
                $results['created_slugs'][] = $content->slug;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'vehicle' => $vehicle->getVehicleIdentifier(),
                    'error' => $e->getMessage()
                ];

                Log::error("Erro processando veículo no lote", [
                    'vehicle' => $vehicle->getVehicleIdentifier(),
                    'batch_id' => $batchId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }
}

<?php

namespace Src\ContentGeneration\WhenToChangeTires\Application\UseCases;

use Src\ContentGeneration\WhenToChangeTires\Application\DTOs\ArticleGenerationRequestDTO;
use Src\ContentGeneration\WhenToChangeTires\Domain\Repositories\VehicleRepositoryInterface;
use Src\ContentGeneration\WhenToChangeTires\Infrastructure\Services\VehicleDataProcessorService;
use Illuminate\Support\Facades\Log;

class ValidateVehiclesForGenerationUseCase
{
    public function __construct(
        protected VehicleRepositoryInterface $vehicleRepository,
        protected VehicleDataProcessorService $vehicleProcessor
    ) {}

    /**
     * Validar veículos antes da geração
     */
    public function execute(ArticleGenerationRequestDTO $request): array
    {
        Log::info("Iniciando validação de veículos", $request->toArray());

        try {
            // 1. Carregar veículos
            $allVehicles = $this->vehicleRepository->importVehicles($request->csvPath);

            // 2. Aplicar filtros
            $filters = $request->getFilters();
            if (!empty($filters)) {
                $filteredVehicles = $this->vehicleProcessor->filterVehicles($allVehicles, $filters);
            } else {
                $filteredVehicles = $allVehicles;
            }

            // 3. Validar cada veículo
            $validationResults = [
                'total_vehicles' => $filteredVehicles->count(),
                'valid_vehicles' => 0,
                'invalid_vehicles' => 0,
                'validation_issues' => [],
                'ready_for_generation' => 0,
                'statistics' => $this->vehicleProcessor->getStatistics($filteredVehicles)
            ];

            foreach ($filteredVehicles as $vehicle) {
                $issues = $this->vehicleProcessor->validateVehicleData($vehicle);

                if (empty($issues)) {
                    $validationResults['valid_vehicles']++;
                    $validationResults['ready_for_generation']++;
                } else {
                    $validationResults['invalid_vehicles']++;
                    $validationResults['validation_issues'][$vehicle->getVehicleIdentifier()] = $issues;
                }
            }

            // 4. Verificar duplicatas se não for overwrite
            if (!$request->overwrite) {
                $validationResults['existing_articles'] = $this->checkExistingArticles($filteredVehicles);
            }

            Log::info("Validação concluída", [
                'total' => $validationResults['total_vehicles'],
                'valid' => $validationResults['valid_vehicles'],
                'invalid' => $validationResults['invalid_vehicles']
            ]);

            return $validationResults;
        } catch (\Exception $e) {
            Log::error("Erro na validação: " . $e->getMessage());

            return [
                'total_vehicles' => 0,
                'valid_vehicles' => 0,
                'invalid_vehicles' => 0,
                'validation_issues' => [],
                'ready_for_generation' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar artigos existentes
     */
    protected function checkExistingArticles($vehicles): array
    {
        $existing = [];

        foreach ($vehicles as $vehicle) {
            if ($this->vehicleRepository->exists($vehicle->make, $vehicle->model, $vehicle->year)) {
                $existing[] = $vehicle->getVehicleIdentifier();
            }
        }

        return $existing;
    }
}

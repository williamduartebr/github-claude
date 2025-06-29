<?php

namespace App\ContentGeneration\WhenToChangeTires\Infrastructure\Repositories;

use App\ContentGeneration\WhenToChangeTires\Domain\Repositories\VehicleRepositoryInterface;
use App\ContentGeneration\WhenToChangeTires\Domain\ValueObjects\VehicleData;
use App\ContentGeneration\WhenToChangeTires\Infrastructure\Services\VehicleDataProcessorService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class VehicleRepository implements VehicleRepositoryInterface
{
    public function __construct(
        protected VehicleDataProcessorService $processor
    ) {}

    public function importVehicles(string $source): Collection
    {
        $cacheKey = "vehicles_import_" . md5($source);
        
        return Cache::remember($cacheKey, 3600, function () use ($source) {
            return $this->processor->importFromCsv($source);
        });
    }

    public function findByCriteria(array $criteria): Collection
    {
        $vehicles = $this->importVehicles('todos_veiculos.csv');
        return $this->processor->filterVehicles($vehicles, $criteria);
    }

    public function getByBatch(string $batchId): Collection
    {
        // Implementar busca por batch_id se necessário
        // Por enquanto, retorna veículos filtrados
        return collect();
    }

    public function exists(string $make, string $model, int $year): bool
    {
        $vehicles = $this->findByCriteria([
            'make' => $make,
            'year_from' => $year,
            'year_to' => $year
        ]);

        return $vehicles->contains(function (VehicleData $vehicle) use ($model) {
            return strtolower($vehicle->model) === strtolower($model);
        });
    }

    public function save(VehicleData $vehicle): bool
    {
        // Para o sistema atual, os dados vêm do CSV
        // Em uma evolução futura, pode salvar em banco
        return true;
    }

    public function getStatistics(): array
    {
        $vehicles = $this->importVehicles('todos_veiculos.csv');
        return $this->processor->getStatistics($vehicles);
    }
}

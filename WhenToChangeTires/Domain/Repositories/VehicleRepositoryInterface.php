<?php

namespace Src\ContentGeneration\WhenToChangeTires\Domain\Repositories;

use Src\ContentGeneration\WhenToChangeTires\Domain\ValueObjects\VehicleData;
use Illuminate\Support\Collection;

interface VehicleRepositoryInterface
{
    /**
     * Importar veículos de uma fonte externa (CSV, API, etc.)
     */
    public function importVehicles(string $source): Collection;

    /**
     * Buscar veículos por critérios
     */
    public function findByCriteria(array $criteria): Collection;

    /**
     * Obter veículos por lote
     */
    public function getByBatch(string $batchId): Collection;

    /**
     * Verificar se veículo já existe
     */
    public function exists(string $make, string $model, int $year): bool;

    /**
     * Salvar dados de um veículo
     */
    public function save(VehicleData $vehicle): bool;

    /**
     * Obter estatísticas dos veículos
     */
    public function getStatistics(): array;
}

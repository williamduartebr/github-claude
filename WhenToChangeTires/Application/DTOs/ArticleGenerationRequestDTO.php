<?php

namespace App\ContentGeneration\WhenToChangeTires\Application\DTOs;

class ArticleGenerationRequestDTO
{
    public function __construct(
        public readonly string $csvPath,
        public readonly int $batchSize = 50,
        public readonly ?string $filterMake = null,
        public readonly ?string $filterCategory = null,
        public readonly ?string $filterVehicleType = null,
        public readonly ?int $yearFrom = null,
        public readonly ?int $yearTo = null,
        public readonly bool $onlyJson = false,
        public readonly bool $overwrite = false,
        public readonly bool $dryRun = false,
        public readonly ?string $batchId = null
    ) {}

    public function getFilters(): array
    {
        $filters = [];
        
        if ($this->filterMake) $filters['make'] = $this->filterMake;
        if ($this->filterCategory) $filters['category'] = $this->filterCategory;
        if ($this->filterVehicleType) $filters['vehicle_type'] = $this->filterVehicleType;
        if ($this->yearFrom) $filters['year_from'] = $this->yearFrom;
        if ($this->yearTo) $filters['year_to'] = $this->yearTo;
        
        $filters['require_tire_pressure'] = true;
        
        return $filters;
    }

    public function toArray(): array
    {
        return [
            'csv_path' => $this->csvPath,
            'batch_size' => $this->batchSize,
            'filters' => $this->getFilters(),
            'only_json' => $this->onlyJson,
            'overwrite' => $this->overwrite,
            'dry_run' => $this->dryRun,
            'batch_id' => $this->batchId
        ];
    }
}

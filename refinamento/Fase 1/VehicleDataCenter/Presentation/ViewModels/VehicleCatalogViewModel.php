<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

class VehicleCatalogViewModel
{
    private $results;

    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->results['total'],
            'vehicles' => collect($this->results['results'])->map(function ($vehicle) {
                return [
                    'id' => $vehicle['id'],
                    'name' => $vehicle['full_name'],
                    'make' => $vehicle['make'],
                    'model' => $vehicle['model'],
                    'year' => $vehicle['year'],
                    'category' => $vehicle['enriched_data']['category'] ?? null,
                    'fuel_type' => $vehicle['enriched_data']['fuel_type'] ?? null,
                    'power_hp' => $vehicle['enriched_data']['power_hp'] ?? null,
                    'url' => url("/veiculos/{$vehicle['make']}/{$vehicle['model']}/{$vehicle['year']}/{$vehicle['version']}")
                ];
            })->toArray()
        ];
    }
}

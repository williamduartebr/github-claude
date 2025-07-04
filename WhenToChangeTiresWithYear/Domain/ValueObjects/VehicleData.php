<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\ValueObjects;


class VehicleData
{
    public function __construct(
        public readonly string $make,
        public readonly string $model,
        public readonly int $year,
        public readonly string $tireSize,
        public readonly int $pressureEmptyFront,
        public readonly int $pressureEmptyRear,
        public readonly float $pressureLightFront,
        public readonly float $pressureLightRear,
        public readonly int $pressureMaxFront,
        public readonly int $pressureMaxRear,
        public readonly ?float $pressureSpare,
        public readonly string $category,
        public readonly ?string $recommendedOil
    ) {}

    public function getVehicleIdentifier(): string
    {
        return "{$this->make} {$this->model} {$this->year}";
    }

    public function getSlug(): string
    {
        return \Illuminate\Support\Str::slug($this->getVehicleIdentifier());
    }

    public function isMotorcycle(): bool
    {
        return str_contains($this->category, 'motorcycle');
    }

    public function isCar(): bool
    {
        return in_array($this->category, [
            'hatch',
            'sedan',
            'suv',
            'pickup',
            'van',
            'minivan',
            'car_sedan',
            'car_hatchback',
            'car_suv',
            'car_pickup',
            'car_sports',
            'car_hybrid',
            'car_electric'
        ]);
    }

    public function isElectric(): bool
    {
        return str_contains($this->category, 'electric');
    }

    public function isHybrid(): bool
    {
        return str_contains($this->category, 'hybrid');
    }

    public function getVehicleType(): string
    {
        if ($this->isMotorcycle()) {
            return 'motorcycle';
        }

        if ($this->isElectric()) {
            return 'electric';
        }

        if ($this->isHybrid()) {
            return 'hybrid';
        }

        return 'car';
    }

    public function getMainCategory(): string
    {
        // Mapear categorias específicas para principais
        $categoryMapping = [
            'hatch' => 'hatchback',
            'sedan' => 'sedan',
            'suv' => 'suv',
            'pickup' => 'pickup',
            'van' => 'van',
            'minivan' => 'minivan',
            'car_sedan' => 'sedan',
            'car_hatchback' => 'hatchback',
            'car_suv' => 'suv',
            'car_pickup' => 'pickup',
            'car_sports' => 'sports',
            'car_hybrid' => 'hybrid',
            'car_electric' => 'electric',
            'motorcycle_street' => 'street',
            'motorcycle_sport' => 'sport',
            'motorcycle_trail' => 'trail',
            'motorcycle_adventure' => 'adventure',
            'motorcycle_scooter' => 'scooter',
            'motorcycle_cruiser' => 'cruiser',
            'motorcycle_touring' => 'touring',
            'motorcycle_custom' => 'custom',
            'motorcycle_electric' => 'electric'
        ];

        return $categoryMapping[$this->category] ?? $this->category;
    }

    public function toArray(): array
    {
        return [
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'tire_size' => $this->tireSize,
            'pressure_empty_front' => $this->pressureEmptyFront,
            'pressure_empty_rear' => $this->pressureEmptyRear,
            'pressure_light_front' => $this->pressureLightFront,
            'pressure_light_rear' => $this->pressureLightRear,
            'pressure_max_front' => $this->pressureMaxFront,
            'pressure_max_rear' => $this->pressureMaxRear,
            'pressure_spare' => $this->pressureSpare,
            'category' => $this->category,
            'recommended_oil' => $this->recommendedOil,
            'vehicle_identifier' => $this->getVehicleIdentifier(),
            'slug' => $this->getSlug(),
            'vehicle_type' => $this->getVehicleType(),
            'main_category' => $this->getMainCategory()
        ];
    }
}

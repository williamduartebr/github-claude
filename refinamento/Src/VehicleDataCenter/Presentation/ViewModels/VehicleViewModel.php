<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

class VehicleViewModel
{
    private $version;

    public function __construct($version)
    {
        $this->version = $version;
    }

    public function toArray(): array
    {
        $make = $this->version->model->make;
        $model = $this->version->model;
        $specs = $this->version->specs;

        return [
            'id' => $this->version->id,
            'make' => [
                'id' => $make->id,
                'name' => $make->name,
                'slug' => $make->slug,
                'logo' => $make->logo_url
            ],
            'model' => [
                'id' => $model->id,
                'name' => $model->name,
                'slug' => $model->slug,
                'category' => $model->category
            ],
            'version' => [
                'id' => $this->version->id,
                'name' => $this->version->name,
                'slug' => $this->version->slug,
                'year' => $this->version->year,
                'fuel_type' => $this->version->fuel_type,
                'transmission' => $this->version->transmission,
                'price_msrp' => $this->version->price_msrp
            ],
            'specs' => [
                'power_hp' => $specs->power_hp ?? null,
                'power_kw' => $specs->power_kw ?? null,
                'torque_nm' => $specs->torque_nm ?? null,
                'acceleration_0_100' => $specs->acceleration_0_100 ?? null,
                'top_speed_kmh' => $specs->top_speed_kmh ?? null,
                'fuel_consumption_city' => $specs->fuel_consumption_city ?? null,
                'fuel_consumption_highway' => $specs->fuel_consumption_highway ?? null,
                'fuel_consumption_mixed' => $specs->fuel_consumption_mixed ?? null,
                'weight_kg' => $specs->weight_kg ?? null,
                'trunk_capacity_liters' => $specs->trunk_capacity_liters ?? null,
                'seating_capacity' => $specs->seating_capacity ?? 5,
                'doors' => $specs->doors ?? null
            ],
            'full_name' => "{$make->name} {$model->name} {$this->version->name} {$this->version->year}",
            'display_name' => "{$make->name} {$model->name} {$this->version->year}",
            'url' => url("/veiculos/{$make->slug}/{$model->slug}/{$this->version->year}/{$this->version->slug}")
        ];
    }
}

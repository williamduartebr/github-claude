<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

class VehicleSpecsViewModel
{
    private $version;
    private $specs;

    public function __construct($version, array $specs)
    {
        $this->version = $version;
        $this->specs = $specs;
    }

    public function toArray(): array
    {
        return [
            'vehicle' => [
                'make' => $this->version->model->make->name,
                'model' => $this->version->model->name,
                'version' => $this->version->name,
                'year' => $this->version->year
            ],
            'general' => $this->specs['general'],
            'engine' => $this->specs['engine'],
            'tires' => $this->specs['tires'],
            'fluids' => $this->specs['fluids'],
            'battery' => $this->specs['battery'],
            'dimensions' => $this->specs['dimensions'],
            'formatted' => [
                'performance' => $this->formatPerformance(),
                'consumption' => $this->formatConsumption(),
                'maintenance' => $this->formatMaintenance()
            ]
        ];
    }

    private function formatPerformance(): array
    {
        $general = $this->specs['general'];
        $engine = $this->specs['engine'];

        return [
            'power' => isset($general['power_hp']) ? "{$general['power_hp']} cv" : 'N/A',
            'torque' => isset($general['torque_nm']) ? "{$general['torque_nm']} Nm" : 'N/A',
            'acceleration' => isset($general['acceleration_0_100']) ? "{$general['acceleration_0_100']}s (0-100 km/h)" : 'N/A',
            'top_speed' => isset($general['top_speed_kmh']) ? "{$general['top_speed_kmh']} km/h" : 'N/A',
            'engine_size' => isset($engine['displacement_cc']) ? "{$engine['displacement_cc']} cc" : 'N/A'
        ];
    }

    private function formatConsumption(): array
    {
        $general = $this->specs['general'];

        return [
            'city' => isset($general['fuel_consumption_city']) ? "{$general['fuel_consumption_city']} km/l" : 'N/A',
            'highway' => isset($general['fuel_consumption_highway']) ? "{$general['fuel_consumption_highway']} km/l" : 'N/A',
            'mixed' => isset($general['fuel_consumption_mixed']) ? "{$general['fuel_consumption_mixed']} km/l" : 'N/A',
            'tank' => isset($general['fuel_tank_capacity']) ? "{$general['fuel_tank_capacity']} litros" : 'N/A'
        ];
    }

    private function formatMaintenance(): array
    {
        $fluids = $this->specs['fluids'];
        $tires = $this->specs['tires'];
        $battery = $this->specs['battery'];

        return [
            'engine_oil' => $fluids['engine_oil_type'] ?? 'N/A',
            'tires' => $tires['front_tire_size'] ?? 'N/A',
            'battery' => $battery['group_size'] ?? 'N/A'
        ];
    }
}

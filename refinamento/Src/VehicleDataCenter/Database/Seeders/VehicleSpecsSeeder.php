<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleSpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleEngineSpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleTireSpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleFluidSpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleBatterySpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleDimensionsSpec;

class VehicleSpecsSeeder extends Seeder
{
    public function run(): void
    {
        $versions = VehicleVersion::all();

        foreach ($versions as $version) {
            // General Specs
            VehicleSpec::create([
                'version_id' => $version->id,
                'power_hp' => rand(70, 150),
                'power_kw' => rand(52, 110),
                'torque_nm' => rand(90, 200),
                'top_speed_kmh' => rand(160, 220),
                'acceleration_0_100' => rand(9, 15) + (rand(0, 9) / 10),
                'fuel_consumption_city' => rand(8, 12) + (rand(0, 9) / 10),
                'fuel_consumption_highway' => rand(12, 16) + (rand(0, 9) / 10),
                'fuel_consumption_mixed' => rand(10, 14) + (rand(0, 9) / 10),
                'fuel_tank_capacity' => rand(45, 65),
                'weight_kg' => rand(1000, 1500),
                'trunk_capacity_liters' => rand(250, 500),
                'seating_capacity' => 5,
                'body_type' => $version->model->category,
                'doors' => in_array($version->model->category, ['sedan', 'suv']) ? 4 : 2,
                'drive_type' => 'fwd'
            ]);

            // Engine Specs
            VehicleEngineSpec::create([
                'version_id' => $version->id,
                'engine_type' => 'Inline',
                'engine_code' => 'E' . rand(100, 999),
                'displacement_cc' => rand(1000, 2000),
                'cylinders' => [3, 4][rand(0, 1)],
                'cylinder_arrangement' => 'inline',
                'valves_per_cylinder' => [2, 4][rand(0, 1)],
                'aspiration' => ['naturally_aspirated', 'turbo'][rand(0, 1)],
                'compression_ratio' => 10 + (rand(0, 5) / 10),
                'max_rpm' => rand(5500, 6500)
            ]);

            // Tire Specs
            VehicleTireSpec::create([
                'version_id' => $version->id,
                'front_tire_size' => '185/65 R15',
                'rear_tire_size' => '185/65 R15',
                'front_rim_size' => '15',
                'rear_rim_size' => '15',
                'front_pressure_psi' => 32.0,
                'rear_pressure_psi' => 32.0,
                'spare_tire_type' => 'full_size'
            ]);

            // Fluid Specs
            VehicleFluidSpec::create([
                'version_id' => $version->id,
                'engine_oil_type' => ['5W-30', '10W-40', '5W-40'][rand(0, 2)],
                'engine_oil_capacity' => 3.5 + (rand(0, 10) / 10),
                'engine_oil_standard' => 'API SN',
                'coolant_type' => 'Etileno Glicol',
                'coolant_capacity' => 5.0 + (rand(0, 10) / 10),
                'transmission_fluid_type' => 'ATF Dexron VI',
                'transmission_fluid_capacity' => 2.5,
                'brake_fluid_type' => 'DOT 4',
                'power_steering_fluid_type' => 'ATF'
            ]);

            // Battery Specs
            VehicleBatterySpec::create([
                'version_id' => $version->id,
                'battery_type' => 'Lead-acid',
                'voltage' => 12,
                'capacity_ah' => rand(45, 70),
                'cca' => rand(400, 600),
                'group_size' => ['60', '65', '75'][rand(0, 2)]
            ]);

            // Dimensions Specs
            VehicleDimensionsSpec::create([
                'version_id' => $version->id,
                'length_mm' => rand(4000, 4800),
                'width_mm' => rand(1700, 1850),
                'height_mm' => rand(1400, 1700),
                'wheelbase_mm' => rand(2500, 2750),
                'front_track_mm' => rand(1450, 1600),
                'rear_track_mm' => rand(1450, 1600),
                'ground_clearance_mm' => rand(150, 220)
            ]);
        }
    }
}

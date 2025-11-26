<?php

declare(strict_types=1);

namespace Src\VehicleDataCenter\Database\Seeders;

use Illuminate\Database\Seeder;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;

/**
 * Seeder de Versões de Veículos - Mercado Brasileiro
 *
 * Continuação a partir de Jeep Compass Limited 1.3 Turbo
 *
 * @author Claude AI Assistant
 * @version 1.0.0
 */
class VehicleVersionSeeder extends Seeder
{
    public function run(): void
    {
        $versionsData = $this->getVersionsData();
        $insertedCount = 0;

        foreach ($versionsData as $makeSlug => $models) {
            $make = VehicleMake::where('slug', $makeSlug)->first();
            if (!$make) {
                $this->command->warn("⚠️  Marca '{$makeSlug}' não encontrada.");
                continue;
            }

            foreach ($models as $modelSlug => $versions) {
                $model = VehicleModel::where('make_id', $make->id)->where('slug', $modelSlug)->first();
                if (!$model) {
                    $this->command->warn("⚠️  Modelo '{$modelSlug}' não encontrado.");
                    continue;
                }

                foreach ($versions as $v) {
                    VehicleVersion::updateOrCreate(
                        ['model_id' => $model->id, 'slug' => $v['slug'], 'year_from' => $v['year_from']],
                        $v
                    );
                    $insertedCount++;
                }
            }
        }

        $this->command->info("✅ {$insertedCount} versões inseridas!");
    }

    private function getVersionsData(): array
    {
        return [

            // ═══════════════════════════════════════════════════════════════
            // FIAT
            // ═══════════════════════════════════════════════════════════════
            'fiat' => [
                'strada' => [
                    // STRADA 2ª GERAÇÃO (2020+)
                    [
                        'name' => 'Strada Endurance 1.4 Cabine Plus',
                        'slug' => 'endurance-1-4-cabine-plus',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'Fire Evo',
                        'engine_displacement' => 1368,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 3.3,
                                'capacity_without_filter' => 3.0,
                                'change_interval_km' => 10000,
                                'change_interval_months' => 12,
                                'type' => 'semi-sintetico',
                            ],
                            'tires' => [
                                'front' => '185/65 R15',
                                'rear' => '185/65 R15',
                                'spare' => '185/65 R15',
                                'pressure_front_psi' => 30,
                                'pressure_rear_psi' => 35,
                                'pressure_loaded_psi' => 38,
                            ],
                            'engine' => [
                                'power_hp_gas' => 85,
                                'power_hp_eth' => 88,
                                'torque_nm_gas' => 121,
                                'torque_nm_eth' => 124,
                                'cylinders' => 4,
                                'valves' => 8,
                            ],
                            'dimensions' => [
                                'length_mm' => 4478,
                                'width_mm' => 1746,
                                'height_mm' => 1527,
                                'wheelbase_mm' => 2740,
                                'trunk_liters' => 844,
                                'fuel_tank_liters' => 55,
                            ],
                            'fluids' => [
                                'coolant_type' => 'Organico',
                                'coolant_capacity' => 5.5,
                                'brake_fluid' => 'DOT 4',
                                'power_steering' => 'Eletrica',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Strada Freedom 1.3 Cabine Simples',
                        'slug' => 'freedom-1-3-cabine-simples',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'Firefly',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'acea' => 'C3',
                                'capacity_with_filter' => 4.0,
                                'capacity_without_filter' => 3.5,
                                'change_interval_km' => 12000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '185/65 R15',
                                'rear' => '185/65 R15',
                                'pressure_front_psi' => 30,
                                'pressure_rear_psi' => 35,
                            ],
                            'engine' => [
                                'power_hp_gas' => 101,
                                'power_hp_eth' => 109,
                                'torque_nm_gas' => 132,
                                'torque_nm_eth' => 139,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Strada Volcano 1.3 Cabine Dupla',
                        'slug' => 'volcano-1-3-cabine-dupla',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'Firefly',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/55 R16',
                                'rear' => '195/55 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 35,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Strada Ranch 1.3 Turbo Cabine Dupla',
                        'slug' => 'ranch-1-3-turbo-cabine-dupla',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'T270 Turbo',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.2,
                                'change_interval_km' => 15000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/55 R16',
                                'rear' => '195/55 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 35,
                            ],
                            'engine' => [
                                'power_hp_gas' => 180,
                                'power_hp_eth' => 185,
                                'torque_nm' => 270,
                                'turbo' => true,
                            ],
                        ],
                    ],
                ],

                'argo' => [
                    [
                        'name' => 'Argo Drive 1.0',
                        'slug' => 'drive-1-0',
                        'year_from' => 2017,
                        'year_to' => null,
                        'engine_code' => 'Firefly',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.5,
                                'change_interval_km' => 12000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '175/65 R15',
                                'rear' => '175/65 R15',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 72,
                                'power_hp_eth' => 77,
                                'torque_nm_gas' => 101,
                                'torque_nm_eth' => 104,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Argo Drive 1.3',
                        'slug' => 'drive-1-3',
                        'year_from' => 2017,
                        'year_to' => null,
                        'engine_code' => 'Firefly',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '185/65 R15',
                                'rear' => '185/65 R15',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Argo Trekking 1.3',
                        'slug' => 'trekking-1-3',
                        'year_from' => 2019,
                        'year_to' => null,
                        'engine_code' => 'Firefly',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '185/60 R15',
                                'rear' => '185/60 R15',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Argo HGT 1.8',
                        'slug' => 'hgt-1-8',
                        'year_from' => 2017,
                        'year_to' => 2023,
                        'engine_code' => 'E.torQ',
                        'engine_displacement' => 1747,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 4.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/55 R16',
                                'rear' => '195/55 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 135,
                                'power_hp_eth' => 139,
                                'torque_nm_gas' => 177,
                                'torque_nm_eth' => 180,
                            ],
                        ],
                    ],
                ],

                'pulse' => [
                    [
                        'name' => 'Pulse Drive 1.0 Turbo',
                        'slug' => 'drive-1-0-turbo',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'T200 Turbo',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.0,
                                'change_interval_km' => 15000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/65 R15',
                                'rear' => '195/65 R15',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 125,
                                'power_hp_eth' => 130,
                                'torque_nm' => 200,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Pulse Impetus 1.0 Turbo',
                        'slug' => 'impetus-1-0-turbo',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'T200 Turbo',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/55 R17',
                                'rear' => '205/55 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Pulse Abarth 1.3 Turbo',
                        'slug' => 'abarth-1-3-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'T270 Turbo',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/45 R18',
                                'rear' => '215/45 R18',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 180,
                                'power_hp_eth' => 185,
                                'torque_nm' => 270,
                                'turbo' => true,
                            ],
                        ],
                    ],
                ],

                'toro' => [
                    [
                        'name' => 'Toro Endurance 1.3 Turbo',
                        'slug' => 'endurance-1-3-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'T270 Turbo',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/60 R17',
                                'rear' => '215/60 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 35,
                            ],
                            'engine' => [
                                'power_hp_eth' => 185,
                                'torque_nm' => 270,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Toro Ranch 2.0 Turbo Diesel',
                        'slug' => 'ranch-2-0-diesel',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'Multijet II',
                        'engine_displacement' => 1956,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-9',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'acea' => 'C3',
                                'capacity_with_filter' => 5.5,
                                'change_interval_km' => 30000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '225/55 R18',
                                'rear' => '225/55 R18',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 35,
                            ],
                            'engine' => [
                                'power_hp' => 170,
                                'torque_nm' => 350,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Toro Ultra 2.0 Turbo Diesel 4x4',
                        'slug' => 'ultra-2-0-diesel-4x4',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'Multijet II',
                        'engine_displacement' => 1956,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-9',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'capacity_with_filter' => 5.5,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '255/55 R19',
                                'rear' => '255/55 R19',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 35,
                            ],
                            'engine' => [
                                'power_hp' => 170,
                                'torque_nm' => 350,
                            ],
                            'drivetrain' => '4x4',
                        ],
                    ],
                ],

                'mobi' => [
                    [
                        'name' => 'Mobi Like 1.0',
                        'slug' => 'like-1-0',
                        'year_from' => 2016,
                        'year_to' => null,
                        'engine_code' => 'Firefly',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.5,
                                'change_interval_km' => 12000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '175/70 R14',
                                'rear' => '175/70 R14',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 72,
                                'power_hp_eth' => 77,
                                'torque_nm_gas' => 101,
                                'torque_nm_eth' => 104,
                            ],
                            'dimensions' => [
                                'length_mm' => 3566,
                                'width_mm' => 1630,
                                'height_mm' => 1520,
                                'wheelbase_mm' => 2370,
                                'trunk_liters' => 215,
                                'fuel_tank_liters' => 45,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Mobi Trekking 1.0',
                        'slug' => 'trekking-1-0',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'Firefly',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.5,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '175/65 R14',
                                'rear' => '175/65 R14',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // VOLKSWAGEN
            // ═══════════════════════════════════════════════════════════════
            'volkswagen' => [
                'polo' => [
                    [
                        'name' => 'Polo 1.0 MPI',
                        'slug' => '1-0-mpi',
                        'year_from' => 2017,
                        'year_to' => null,
                        'engine_code' => 'CHYB',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'SN',
                                'vw_norm' => 'VW 502.00',
                                'capacity_with_filter' => 3.6,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '185/65 R15',
                                'rear' => '185/65 R15',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 75,
                                'power_hp_eth' => 84,
                                'torque_nm_gas' => 97,
                                'torque_nm_eth' => 101,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Polo 1.0 TSI',
                        'slug' => '1-0-tsi',
                        'year_from' => 2017,
                        'year_to' => null,
                        'engine_code' => 'DHSB',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'SN',
                                'vw_norm' => 'VW 502.00',
                                'capacity_with_filter' => 4.0,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/55 R16',
                                'rear' => '195/55 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 116,
                                'power_hp_eth' => 128,
                                'torque_nm' => 200,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Polo GTS 1.4 TSI',
                        'slug' => 'gts-1-4-tsi',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'CZDA',
                        'engine_displacement' => 1395,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'SN',
                                'vw_norm' => 'VW 502.00',
                                'capacity_with_filter' => 4.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/50 R17',
                                'rear' => '205/50 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 150,
                                'power_hp_eth' => 150,
                                'torque_nm' => 250,
                                'turbo' => true,
                            ],
                        ],
                    ],
                ],

                't-cross' => [
                    [
                        'name' => 'T-Cross Sense 1.0 TSI',
                        'slug' => 'sense-1-0-tsi',
                        'year_from' => 2019,
                        'year_to' => null,
                        'engine_code' => 'DHSB',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'SN',
                                'vw_norm' => 'VW 502.00',
                                'capacity_with_filter' => 4.0,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/60 R16',
                                'rear' => '205/60 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 116,
                                'power_hp_eth' => 128,
                                'torque_nm' => 200,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'T-Cross Highline 1.4 TSI',
                        'slug' => 'highline-1-4-tsi',
                        'year_from' => 2019,
                        'year_to' => null,
                        'engine_code' => 'CZDA',
                        'engine_displacement' => 1395,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'SN',
                                'vw_norm' => 'VW 502.00',
                                'capacity_with_filter' => 4.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/55 R17',
                                'rear' => '205/55 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 150,
                                'power_hp_eth' => 150,
                                'torque_nm' => 250,
                                'turbo' => true,
                            ],
                        ],
                    ],
                ],

                'virtus' => [
                    [
                        'name' => 'Virtus Comfortline 1.0 TSI',
                        'slug' => 'comfortline-1-0-tsi',
                        'year_from' => 2018,
                        'year_to' => null,
                        'engine_code' => 'DHSB',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'SN',
                                'vw_norm' => 'VW 502.00',
                                'capacity_with_filter' => 4.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/55 R16',
                                'rear' => '195/55 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Virtus GTS 1.4 TSI',
                        'slug' => 'gts-1-4-tsi',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'CZDA',
                        'engine_displacement' => 1395,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'SN',
                                'vw_norm' => 'VW 502.00',
                                'capacity_with_filter' => 4.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/50 R17',
                                'rear' => '205/50 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp' => 150,
                                'torque_nm' => 250,
                                'turbo' => true,
                            ],
                        ],
                    ],
                ],

                'saveiro' => [
                    [
                        'name' => 'Saveiro Robust 1.6 Cabine Simples',
                        'slug' => 'robust-1-6-cs',
                        'year_from' => 2017,
                        'year_to' => null,
                        'engine_code' => 'CWLA',
                        'engine_displacement' => 1598,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'SN',
                                'vw_norm' => 'VW 502.00',
                                'capacity_with_filter' => 4.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '185/60 R15',
                                'rear' => '185/60 R15',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 35,
                            ],
                            'engine' => [
                                'power_hp_gas' => 101,
                                'power_hp_eth' => 104,
                                'torque_nm_gas' => 152,
                                'torque_nm_eth' => 155,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Saveiro Cross 1.6 Cabine Dupla',
                        'slug' => 'cross-1-6-cd',
                        'year_from' => 2017,
                        'year_to' => null,
                        'engine_code' => 'CWLA',
                        'engine_displacement' => 1598,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'SN',
                                'capacity_with_filter' => 4.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/60 R15',
                                'rear' => '205/60 R15',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 35,
                            ],
                        ],
                    ],
                ],

                'amarok' => [
                    [
                        'name' => 'Amarok Highline 3.0 V6 TDI',
                        'slug' => 'highline-3-0-v6-tdi',
                        'year_from' => 2018,
                        'year_to' => 2022,
                        'engine_code' => 'DDXC',
                        'engine_displacement' => 2967,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-8',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CJ-4',
                                'vw_norm' => 'VW 507.00',
                                'capacity_with_filter' => 7.0,
                                'change_interval_km' => 15000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '255/60 R18',
                                'rear' => '255/60 R18',
                                'pressure_front_psi' => 35,
                                'pressure_rear_psi' => 38,
                            ],
                            'engine' => [
                                'power_hp' => 258,
                                'torque_nm' => 580,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Nova Amarok Extreme V6 3.0 TDI',
                        'slug' => 'extreme-3-0-v6-tdi-2023',
                        'year_from' => 2023,
                        'year_to' => null,
                        'engine_code' => 'V6 TDI',
                        'engine_displacement' => 2993,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-10',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'capacity_with_filter' => 8.0,
                                'change_interval_km' => 20000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '265/60 R18',
                                'rear' => '265/60 R18',
                                'pressure_front_psi' => 36,
                                'pressure_rear_psi' => 38,
                            ],
                            'engine' => [
                                'power_hp' => 250,
                                'torque_nm' => 600,
                                'turbo' => true,
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // CHEVROLET
            // ═══════════════════════════════════════════════════════════════
            'chevrolet' => [
                'onix' => [
                    [
                        'name' => 'Onix LT 1.0',
                        'slug' => 'lt-1-0',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'SPE/4',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'dexos' => 'Dexos1 Gen 2',
                                'capacity_with_filter' => 4.2,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '185/65 R15',
                                'rear' => '185/65 R15',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 75,
                                'power_hp_eth' => 82,
                                'torque_nm_gas' => 95,
                                'torque_nm_eth' => 99,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Onix Premier 1.0 Turbo',
                        'slug' => 'premier-1-0-turbo',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'SGE',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'dexos' => 'Dexos1 Gen 3',
                                'capacity_with_filter' => 4.5,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/55 R16',
                                'rear' => '195/55 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 116,
                                'power_hp_eth' => 116,
                                'torque_nm' => 162,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Onix RS 1.0 Turbo',
                        'slug' => 'rs-1-0-turbo',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'SGE',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'dexos' => 'Dexos1 Gen 3',
                                'capacity_with_filter' => 4.5,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/45 R17',
                                'rear' => '205/45 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                ],

                'tracker' => [
                    [
                        'name' => 'Tracker LT 1.0 Turbo',
                        'slug' => 'lt-1-0-turbo',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'SGE',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'dexos' => 'Dexos1 Gen 3',
                                'capacity_with_filter' => 4.5,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/60 R17',
                                'rear' => '215/60 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 116,
                                'power_hp_eth' => 116,
                                'torque_nm' => 162,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Tracker Premier 1.2 Turbo',
                        'slug' => 'premier-1-2-turbo',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'HRA/HRL',
                        'engine_displacement' => 1199,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'dexos' => 'Dexos1 Gen 3',
                                'capacity_with_filter' => 4.8,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/55 R18',
                                'rear' => '215/55 R18',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 133,
                                'power_hp_eth' => 133,
                                'torque_nm' => 210,
                                'turbo' => true,
                            ],
                        ],
                    ],
                ],

                's10' => [
                    [
                        'name' => 'S10 LT 2.8 Turbo Diesel',
                        'slug' => 'lt-2-8-diesel',
                        'year_from' => 2024,
                        'year_to' => null,
                        'engine_code' => 'Duramax',
                        'engine_displacement' => 2776,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'CK-4',
                                'dexos' => 'Dexos2',
                                'capacity_with_filter' => 6.5,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '255/70 R16',
                                'rear' => '255/70 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 35,
                            ],
                            'engine' => [
                                'power_hp' => 200,
                                'torque_nm' => 500,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'S10 High Country 2.8 Turbo Diesel 4x4',
                        'slug' => 'high-country-2-8-diesel-4x4',
                        'year_from' => 2024,
                        'year_to' => null,
                        'engine_code' => 'Duramax',
                        'engine_displacement' => 2776,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'CK-4',
                                'dexos' => 'Dexos2',
                                'capacity_with_filter' => 6.5,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '265/60 R18',
                                'rear' => '265/60 R18',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 35,
                            ],
                            'engine' => [
                                'power_hp' => 200,
                                'torque_nm' => 500,
                            ],
                            'drivetrain' => '4x4',
                        ],
                    ],
                ],

                'montana' => [
                    [
                        'name' => 'Montana LT 1.2 Turbo',
                        'slug' => 'lt-1-2-turbo',
                        'year_from' => 2023,
                        'year_to' => null,
                        'engine_code' => 'HRA/HRL',
                        'engine_displacement' => 1199,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'dexos' => 'Dexos1 Gen 3',
                                'capacity_with_filter' => 4.8,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/55 R17',
                                'rear' => '215/55 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 35,
                            ],
                            'engine' => [
                                'power_hp' => 133,
                                'torque_nm' => 210,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Montana Premier 1.2 Turbo',
                        'slug' => 'premier-1-2-turbo',
                        'year_from' => 2023,
                        'year_to' => null,
                        'engine_code' => 'HRA/HRL',
                        'engine_displacement' => 1199,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'dexos' => 'Dexos1 Gen 3',
                                'capacity_with_filter' => 4.8,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/55 R18',
                                'rear' => '215/55 R18',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 35,
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // TOYOTA
            // ═══════════════════════════════════════════════════════════════
            'toyota' => [
                'corolla' => [
                    [
                        'name' => 'Corolla GLi 2.0',
                        'slug' => 'gli-2-0',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'M20A-FKS',
                        'engine_displacement' => 1987,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.4,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/55 R16',
                                'rear' => '205/55 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 170,
                                'power_hp_eth' => 177,
                                'torque_nm_gas' => 203,
                                'torque_nm_eth' => 209,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Corolla XEi 2.0',
                        'slug' => 'xei-2-0',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'M20A-FKS',
                        'engine_displacement' => 1987,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.4,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/55 R17',
                                'rear' => '215/55 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Corolla Altis Híbrido',
                        'slug' => 'altis-hibrido',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => '2ZR-FXE',
                        'engine_displacement' => 1798,
                        'fuel_type' => 'hibrido-flex',
                        'transmission' => 'automatico-ecvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-16',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.0,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/55 R17',
                                'rear' => '215/55 R17',
                                'pressure_front_psi' => 36,
                                'pressure_rear_psi' => 36,
                            ],
                            'engine' => [
                                'power_hp_combined' => 122,
                                'electric_motor_hp' => 72,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Corolla GR-S 2.0',
                        'slug' => 'gr-s-2-0',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'M20A-FKS',
                        'engine_displacement' => 1987,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.4,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '225/40 R18',
                                'rear' => '225/40 R18',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    // Gerações anteriores (muito buscadas)
                    [
                        'name' => 'Corolla XEi 1.8',
                        'slug' => 'xei-1-8-2008',
                        'year_from' => 2008,
                        'year_to' => 2014,
                        'engine_code' => '2ZR-FE',
                        'engine_displacement' => 1798,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-4',
                        'is_active' => false,
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SL',
                                'capacity_with_filter' => 4.2,
                                'change_interval_km' => 10000,
                                'type' => 'semi-sintetico',
                            ],
                            'tires' => [
                                'front' => '195/65 R15',
                                'rear' => '195/65 R15',
                                'pressure_front_psi' => 30,
                                'pressure_rear_psi' => 30,
                            ],
                            'engine' => [
                                'power_hp_gas' => 132,
                                'power_hp_eth' => 136,
                                'torque_nm_gas' => 171,
                                'torque_nm_eth' => 173,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Corolla XEi 1.8 16V',
                        'slug' => 'xei-1-8-2003',
                        'year_from' => 2003,
                        'year_to' => 2007,
                        'engine_code' => '1ZZ-FE',
                        'engine_displacement' => 1794,
                        'fuel_type' => 'gasolina',
                        'transmission' => 'automatico-4',
                        'is_active' => false,
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SL',
                                'capacity_with_filter' => 4.0,
                                'change_interval_km' => 10000,
                                'type' => 'semi-sintetico',
                            ],
                            'tires' => [
                                'front' => '195/60 R15',
                                'rear' => '195/60 R15',
                                'pressure_front_psi' => 30,
                                'pressure_rear_psi' => 30,
                            ],
                            'engine' => [
                                'power_hp' => 136,
                                'torque_nm' => 171,
                            ],
                        ],
                    ],
                ],

                'corolla-cross' => [
                    [
                        'name' => 'Corolla Cross XRE 2.0',
                        'slug' => 'xre-2-0',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'M20A-FKS',
                        'engine_displacement' => 1987,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.4,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/60 R17',
                                'rear' => '215/60 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Corolla Cross XRX Híbrido',
                        'slug' => 'xrx-hibrido',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => '2ZR-FXE',
                        'engine_displacement' => 1798,
                        'fuel_type' => 'hibrido-flex',
                        'transmission' => 'automatico-ecvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-16',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '225/50 R18',
                                'rear' => '225/50 R18',
                                'pressure_front_psi' => 36,
                                'pressure_rear_psi' => 36,
                            ],
                        ],
                    ],
                ],

                'hilux' => [
                    [
                        'name' => 'Hilux SRV 2.8 Turbo Diesel',
                        'slug' => 'srv-2-8-diesel',
                        'year_from' => 2016,
                        'year_to' => null,
                        'engine_code' => '1GD-FTV',
                        'engine_displacement' => 2755,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'capacity_with_filter' => 8.0,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '265/65 R17',
                                'rear' => '265/65 R17',
                                'pressure_front_psi' => 29,
                                'pressure_rear_psi' => 35,
                            ],
                            'engine' => [
                                'power_hp' => 204,
                                'torque_nm' => 500,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Hilux SRX 2.8 Turbo Diesel 4x4',
                        'slug' => 'srx-2-8-diesel-4x4',
                        'year_from' => 2016,
                        'year_to' => null,
                        'engine_code' => '1GD-FTV',
                        'engine_displacement' => 2755,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'capacity_with_filter' => 8.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '265/60 R18',
                                'rear' => '265/60 R18',
                                'pressure_front_psi' => 29,
                                'pressure_rear_psi' => 35,
                            ],
                            'drivetrain' => '4x4',
                        ],
                    ],
                    [
                        'name' => 'Hilux GR-S 2.8 Turbo Diesel 4x4',
                        'slug' => 'gr-s-2-8-diesel-4x4',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => '1GD-FTV',
                        'engine_displacement' => 2755,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'capacity_with_filter' => 8.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '265/60 R18',
                                'rear' => '265/60 R18',
                                'pressure_front_psi' => 29,
                                'pressure_rear_psi' => 35,
                            ],
                            'engine' => [
                                'power_hp' => 224,
                                'torque_nm' => 500,
                            ],
                            'drivetrain' => '4x4',
                        ],
                    ],
                ],

                'sw4' => [
                    [
                        'name' => 'SW4 SRX 2.8 Turbo Diesel',
                        'slug' => 'srx-2-8-diesel',
                        'year_from' => 2016,
                        'year_to' => null,
                        'engine_code' => '1GD-FTV',
                        'engine_displacement' => 2755,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'capacity_with_filter' => 8.0,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '265/60 R18',
                                'rear' => '265/60 R18',
                                'pressure_front_psi' => 29,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp' => 204,
                                'torque_nm' => 500,
                                'turbo' => true,
                            ],
                            'dimensions' => [
                                'seats' => 7,
                            ],
                        ],
                    ],
                    [
                        'name' => 'SW4 Diamond 2.8 Turbo Diesel',
                        'slug' => 'diamond-2-8-diesel',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => '1GD-FTV',
                        'engine_displacement' => 2755,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'capacity_with_filter' => 8.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '265/55 R19',
                                'rear' => '265/55 R19',
                                'pressure_front_psi' => 29,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // HYUNDAI
            // ═══════════════════════════════════════════════════════════════
            'hyundai' => [
                'hb20' => [
                    [
                        'name' => 'HB20 Sense 1.0',
                        'slug' => 'sense-1-0',
                        'year_from' => 2019,
                        'year_to' => null,
                        'engine_code' => 'Kappa',
                        'engine_displacement' => 998,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.3,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '175/70 R14',
                                'rear' => '175/70 R14',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 75,
                                'power_hp_eth' => 80,
                                'torque_nm_gas' => 95,
                                'torque_nm_eth' => 99,
                            ],
                        ],
                    ],
                    [
                        'name' => 'HB20 Evolution 1.0 Turbo',
                        'slug' => 'evolution-1-0-turbo',
                        'year_from' => 2019,
                        'year_to' => null,
                        'engine_code' => 'Kappa TGDI',
                        'engine_displacement' => 998,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.6,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/55 R16',
                                'rear' => '195/55 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 118,
                                'power_hp_eth' => 120,
                                'torque_nm' => 172,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'HB20 Sport 1.0 Turbo',
                        'slug' => 'sport-1-0-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'Kappa TGDI',
                        'engine_displacement' => 998,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.6,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/50 R17',
                                'rear' => '195/50 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                ],

                'creta' => [
                    [
                        'name' => 'Creta Action 1.6',
                        'slug' => 'action-1-6',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'Gamma',
                        'engine_displacement' => 1591,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.6,
                                'change_interval_km' => 10000,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/65 R16',
                                'rear' => '205/65 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 121,
                                'power_hp_eth' => 130,
                                'torque_nm_gas' => 155,
                                'torque_nm_eth' => 162,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Creta Ultimate 1.0 Turbo',
                        'slug' => 'ultimate-1-0-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'Kappa TGDI',
                        'engine_displacement' => 998,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-7-dct',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.6,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/55 R17',
                                'rear' => '215/55 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                            'engine' => [
                                'power_hp_gas' => 118,
                                'power_hp_eth' => 120,
                                'torque_nm' => 172,
                                'turbo' => true,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Creta N Line 1.0 Turbo',
                        'slug' => 'n-line-1-0-turbo',
                        'year_from' => 2023,
                        'year_to' => null,
                        'engine_code' => 'Kappa TGDI',
                        'engine_displacement' => 998,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-7-dct',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.6,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/55 R18',
                                'rear' => '215/55 R18',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                ],
            ],
            // ═══════════════════════════════════════════════════════════════
            // JEEP (continuação)
            // ═══════════════════════════════════════════════════════════════
            'jeep' => [
                'compass' => [
                    [
                        'name' => 'Compass Limited 1.3 Turbo',
                        'slug' => 'limited-1-3-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'T270 Turbo',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '225/55 R18',
                                'rear' => '225/55 R18',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Compass Trailhawk 2.0 Diesel 4x4',
                        'slug' => 'trailhawk-2-0-diesel',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'Multijet II',
                        'engine_displacement' => 1956,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-9',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'acea' => 'C3',
                                'capacity_with_filter' => 5.5,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '225/55 R18',
                                'rear' => '225/55 R18',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Compass S 1.3 Turbo',
                        'slug' => 's-1-3-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'T270 Turbo',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '235/45 R19',
                                'rear' => '235/45 R19',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                ],
                'renegade' => [
                    [
                        'name' => 'Renegade Sport 1.3 Turbo',
                        'slug' => 'sport-1-3-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'T270 Turbo',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/60 R17',
                                'rear' => '215/60 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Renegade Longitude 1.3 Turbo',
                        'slug' => 'longitude-1-3-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'T270 Turbo',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/60 R17',
                                'rear' => '215/60 R17',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Renegade Trailhawk 2.0 Diesel 4x4',
                        'slug' => 'trailhawk-2-0-diesel',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'Multijet II',
                        'engine_displacement' => 1956,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-9',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'acea' => 'C3',
                                'capacity_with_filter' => 5.5,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/60 R17',
                                'rear' => '215/60 R17',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                ],
                'commander' => [
                    [
                        'name' => 'Commander Limited 1.3 Turbo',
                        'slug' => 'limited-1-3-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'T270 Turbo',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '235/55 R19',
                                'rear' => '235/55 R19',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Commander Overland 2.0 Diesel 4x4',
                        'slug' => 'overland-2-0-diesel',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'Multijet II',
                        'engine_displacement' => 1956,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-9',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'acea' => 'C3',
                                'capacity_with_filter' => 5.5,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '235/55 R19',
                                'rear' => '235/55 R19',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // HONDA
            // ═══════════════════════════════════════════════════════════════
            'honda' => [
                'hr-v' => [
                    [
                        'name' => 'HR-V EX 1.5 Turbo',
                        'slug' => 'ex-1-5-turbo',
                        'year_from' => 2023,
                        'year_to' => null,
                        'engine_code' => 'L15B Turbo',
                        'engine_displacement' => 1498,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.7,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '225/50 R18',
                                'rear' => '225/50 R18',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'HR-V EXL 1.5 Turbo',
                        'slug' => 'exl-1-5-turbo',
                        'year_from' => 2023,
                        'year_to' => null,
                        'engine_code' => 'L15B Turbo',
                        'engine_displacement' => 1498,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.7,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '225/50 R18',
                                'rear' => '225/50 R18',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'HR-V Touring 1.5 Turbo',
                        'slug' => 'touring-1-5-turbo',
                        'year_from' => 2023,
                        'year_to' => null,
                        'engine_code' => 'L15B Turbo',
                        'engine_displacement' => 1498,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.7,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '225/50 R18',
                                'rear' => '225/50 R18',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                ],
                'civic' => [
                    [
                        'name' => 'Civic LX 2.0',
                        'slug' => 'lx-2-0',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'K20C3',
                        'engine_displacement' => 1996,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/55 R17',
                                'rear' => '215/55 R17',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Civic EXL 2.0',
                        'slug' => 'exl-2-0',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'K20C3',
                        'engine_displacement' => 1996,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 4.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '235/45 R18',
                                'rear' => '235/45 R18',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Civic Touring 1.5 Turbo',
                        'slug' => 'touring-1-5-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'L15B7 Turbo',
                        'engine_displacement' => 1498,
                        'fuel_type' => 'gasolina',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.7,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '235/40 R18',
                                'rear' => '235/40 R18',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                ],
                'city' => [
                    [
                        'name' => 'City EX 1.5',
                        'slug' => 'ex-1-5',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'L15B',
                        'engine_displacement' => 1498,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.4,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '185/55 R16',
                                'rear' => '185/55 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'City EXL 1.5',
                        'slug' => 'exl-1-5',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'L15B',
                        'engine_displacement' => 1498,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.4,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '185/55 R16',
                                'rear' => '185/55 R16',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // HYUNDAI
            // ═══════════════════════════════════════════════════════════════
            'hyundai' => [
                'hb20' => [
                    [
                        'name' => 'HB20 Sense 1.0',
                        'slug' => 'sense-1-0',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'Kappa 1.0',
                        'engine_displacement' => 998,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 3.3,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '175/70 R14',
                                'rear' => '175/70 R14',
                                'pressure_front_psi' => 32,
                                'pressure_rear_psi' => 32,
                            ],
                        ],
                    ],
                    [
                        'name' => 'HB20 Evolution 1.0 Turbo',
                        'slug' => 'evolution-1-0-turbo',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'Kappa 1.0 TGDI',
                        'engine_displacement' => 998,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 3.6,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/55 R16',
                                'rear' => '195/55 R16',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'HB20 Platinum 1.0 Turbo',
                        'slug' => 'platinum-1-0-turbo',
                        'year_from' => 2020,
                        'year_to' => null,
                        'engine_code' => 'Kappa 1.0 TGDI',
                        'engine_displacement' => 998,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 3.6,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/55 R16',
                                'rear' => '195/55 R16',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                ],
                'creta' => [
                    [
                        'name' => 'Creta Action 1.6',
                        'slug' => 'action-1-6',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'Gamma 1.6',
                        'engine_displacement' => 1591,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 3.6,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/65 R16',
                                'rear' => '205/65 R16',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Creta Limited 1.0 Turbo',
                        'slug' => 'limited-1-0-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'Kappa 1.0 TGDI',
                        'engine_displacement' => 998,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-7-dct',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 3.6,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/55 R17',
                                'rear' => '215/55 R17',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Creta Ultimate 1.0 Turbo',
                        'slug' => 'ultimate-1-0-turbo',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'Kappa 1.0 TGDI',
                        'engine_displacement' => 998,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-7-dct',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 3.6,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/55 R18',
                                'rear' => '215/55 R18',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // RENAULT
            // ═══════════════════════════════════════════════════════════════
            'renault' => [
                'kwid' => [
                    [
                        'name' => 'Kwid Zen 1.0',
                        'slug' => 'zen-1-0',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'SCe 1.0',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SL',
                                'capacity_with_filter' => 3.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '165/70 R14',
                                'rear' => '165/70 R14',
                                'pressure_front_psi' => 30,
                                'pressure_rear_psi' => 30,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Kwid Intense 1.0',
                        'slug' => 'intense-1-0',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'SCe 1.0',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SL',
                                'capacity_with_filter' => 3.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '165/70 R14',
                                'rear' => '165/70 R14',
                                'pressure_front_psi' => 30,
                                'pressure_rear_psi' => 30,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Kwid Outsider 1.0',
                        'slug' => 'outsider-1-0',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'SCe 1.0',
                        'engine_displacement' => 999,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SL',
                                'capacity_with_filter' => 3.2,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '185/60 R15',
                                'rear' => '185/60 R15',
                                'pressure_front_psi' => 30,
                                'pressure_rear_psi' => 30,
                            ],
                        ],
                    ],
                ],
                'duster' => [
                    [
                        'name' => 'Duster Zen 1.6',
                        'slug' => 'zen-1-6',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'SCe 1.6',
                        'engine_displacement' => 1598,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SL',
                                'capacity_with_filter' => 4.8,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/60 R17',
                                'rear' => '215/60 R17',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Duster Intense 1.3 Turbo',
                        'slug' => 'intense-1-3-turbo',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'TCe 1.3',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'SN',
                                'capacity_with_filter' => 4.5,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/60 R17',
                                'rear' => '215/60 R17',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Duster Iconic 1.3 Turbo',
                        'slug' => 'iconic-1-3-turbo',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'TCe 1.3',
                        'engine_displacement' => 1332,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-40',
                                'api' => 'SN',
                                'capacity_with_filter' => 4.5,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '215/55 R18',
                                'rear' => '215/55 R18',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // NISSAN
            // ═══════════════════════════════════════════════════════════════
            'nissan' => [
                'kicks' => [
                    [
                        'name' => 'Kicks Sense 1.6',
                        'slug' => 'sense-1-6',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'HR16DE',
                        'engine_displacement' => 1598,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 4.1,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/60 R16',
                                'rear' => '205/60 R16',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Kicks Advance 1.6',
                        'slug' => 'advance-1-6',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => 'HR16DE',
                        'engine_displacement' => 1598,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 4.1,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/55 R17',
                                'rear' => '205/55 R17',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Kicks Exclusive e-Power',
                        'slug' => 'exclusive-e-power',
                        'year_from' => 2023,
                        'year_to' => null,
                        'engine_code' => 'HR12DE + EM47',
                        'engine_displacement' => 1198,
                        'fuel_type' => 'flex-hibrido',
                        'transmission' => 'e-power',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 3.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '205/55 R17',
                                'rear' => '205/55 R17',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                ],
                'versa' => [
                    [
                        'name' => 'Versa Sense 1.6',
                        'slug' => 'sense-1-6',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'HR16DE',
                        'engine_displacement' => 1598,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 4.1,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '185/65 R15',
                                'rear' => '185/65 R15',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Versa Advance 1.6',
                        'slug' => 'advance-1-6',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'HR16DE',
                        'engine_displacement' => 1598,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 4.1,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/55 R16',
                                'rear' => '195/55 R16',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Versa Exclusive 1.6',
                        'slug' => 'exclusive-1-6',
                        'year_from' => 2021,
                        'year_to' => null,
                        'engine_code' => 'HR16DE',
                        'engine_displacement' => 1598,
                        'fuel_type' => 'flex',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'SN',
                                'capacity_with_filter' => 4.1,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '195/55 R16',
                                'rear' => '195/55 R16',
                                'pressure_front_psi' => 33,
                                'pressure_rear_psi' => 33,
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // RAM
            // ═══════════════════════════════════════════════════════════════
            'ram' => [
                'rampage' => [
                    [
                        'name' => 'Rampage Laramie 2.0 Turbo',
                        'slug' => 'laramie-2-0-turbo',
                        'year_from' => 2023,
                        'year_to' => null,
                        'engine_code' => 'Hurricane 2.0T',
                        'engine_displacement' => 1995,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-9',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 5.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '255/60 R18',
                                'rear' => '255/60 R18',
                                'pressure_front_psi' => 35,
                                'pressure_rear_psi' => 35,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Rampage Rebel 2.0 Turbo',
                        'slug' => 'rebel-2-0-turbo',
                        'year_from' => 2023,
                        'year_to' => null,
                        'engine_code' => 'Hurricane 2.0T',
                        'engine_displacement' => 1995,
                        'fuel_type' => 'flex',
                        'transmission' => 'automatico-9',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '0W-20',
                                'api' => 'SP',
                                'capacity_with_filter' => 5.0,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '265/60 R18',
                                'rear' => '265/60 R18',
                                'pressure_front_psi' => 35,
                                'pressure_rear_psi' => 35,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Rampage R/T 2.0 Turbo Diesel',
                        'slug' => 'rt-2-0-diesel',
                        'year_from' => 2023,
                        'year_to' => null,
                        'engine_code' => 'Multijet II',
                        'engine_displacement' => 1956,
                        'fuel_type' => 'diesel',
                        'transmission' => 'automatico-9',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '5W-30',
                                'api' => 'CK-4',
                                'acea' => 'C3',
                                'capacity_with_filter' => 5.5,
                                'type' => 'sintetico',
                            ],
                            'tires' => [
                                'front' => '265/60 R18',
                                'rear' => '265/60 R18',
                                'pressure_front_psi' => 35,
                                'pressure_rear_psi' => 35,
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MOTOS - HONDA
            // ═══════════════════════════════════════════════════════════════
            'honda-motos' => [
                'cg-160' => [
                    [
                        'name' => 'CG 160 Start',
                        'slug' => 'start',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => '160cc OHC',
                        'engine_displacement' => 162,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '10W-30',
                                'api' => 'SL',
                                'jaso' => 'MA',
                                'capacity' => 1.0,
                                'type' => 'mineral-4t',
                            ],
                            'tires' => [
                                'front' => '80/100-18',
                                'rear' => '90/90-18',
                                'pressure_front_psi' => 23,
                                'pressure_rear_psi' => 28,
                            ],
                        ],
                    ],
                    [
                        'name' => 'CG 160 Fan',
                        'slug' => 'fan',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => '160cc OHC',
                        'engine_displacement' => 162,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '10W-30',
                                'api' => 'SL',
                                'jaso' => 'MA',
                                'capacity' => 1.0,
                                'type' => 'mineral-4t',
                            ],
                            'tires' => [
                                'front' => '80/100-18',
                                'rear' => '90/90-18',
                                'pressure_front_psi' => 23,
                                'pressure_rear_psi' => 28,
                            ],
                        ],
                    ],
                    [
                        'name' => 'CG 160 Titan',
                        'slug' => 'titan',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => '160cc OHC',
                        'engine_displacement' => 162,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-5',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '10W-30',
                                'api' => 'SL',
                                'jaso' => 'MA',
                                'capacity' => 1.0,
                                'type' => 'mineral-4t',
                            ],
                            'tires' => [
                                'front' => '110/70-17',
                                'rear' => '130/70-17',
                                'pressure_front_psi' => 25,
                                'pressure_rear_psi' => 29,
                            ],
                        ],
                    ],
                ],
                'pcx-160' => [
                    [
                        'name' => 'PCX 160 DLX',
                        'slug' => 'dlx',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => '160cc eSP+',
                        'engine_displacement' => 156,
                        'fuel_type' => 'gasolina',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '10W-30',
                                'api' => 'SL',
                                'jaso' => 'MB',
                                'capacity' => 0.8,
                                'type' => 'sintetico-4t',
                            ],
                            'tires' => [
                                'front' => '110/70-14',
                                'rear' => '130/70-13',
                                'pressure_front_psi' => 25,
                                'pressure_rear_psi' => 29,
                            ],
                        ],
                    ],
                    [
                        'name' => 'PCX 160 Sport',
                        'slug' => 'sport',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => '160cc eSP+',
                        'engine_displacement' => 156,
                        'fuel_type' => 'gasolina',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '10W-30',
                                'api' => 'SL',
                                'jaso' => 'MB',
                                'capacity' => 0.8,
                                'type' => 'sintetico-4t',
                            ],
                            'tires' => [
                                'front' => '110/70-14',
                                'rear' => '130/70-13',
                                'pressure_front_psi' => 25,
                                'pressure_rear_psi' => 29,
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MOTOS - YAMAHA
            // ═══════════════════════════════════════════════════════════════
            'yamaha' => [
                'fazer-250' => [
                    [
                        'name' => 'Fazer 250 ABS',
                        'slug' => 'abs',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => '249cc Blue Core',
                        'engine_displacement' => 249,
                        'fuel_type' => 'flex',
                        'transmission' => 'manual-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '10W-40',
                                'api' => 'SL',
                                'jaso' => 'MA',
                                'capacity' => 1.2,
                                'type' => 'sintetico-4t',
                            ],
                            'tires' => [
                                'front' => '110/70-17',
                                'rear' => '140/70-17',
                                'pressure_front_psi' => 25,
                                'pressure_rear_psi' => 29,
                            ],
                        ],
                    ],
                ],
                'mt-03' => [
                    [
                        'name' => 'MT-03 ABS',
                        'slug' => 'abs',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => '321cc bicilíndrico',
                        'engine_displacement' => 321,
                        'fuel_type' => 'gasolina',
                        'transmission' => 'manual-6',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '10W-40',
                                'api' => 'SL',
                                'jaso' => 'MA',
                                'capacity' => 2.4,
                                'type' => 'sintetico-4t',
                            ],
                            'tires' => [
                                'front' => '110/70-17',
                                'rear' => '140/70-17',
                                'pressure_front_psi' => 29,
                                'pressure_rear_psi' => 29,
                            ],
                        ],
                    ],
                ],
                'nmax-160' => [
                    [
                        'name' => 'NMAX 160 ABS',
                        'slug' => 'abs',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => '155cc Blue Core',
                        'engine_displacement' => 155,
                        'fuel_type' => 'gasolina',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '10W-40',
                                'api' => 'SL',
                                'jaso' => 'MB',
                                'capacity' => 0.85,
                                'type' => 'sintetico-4t',
                            ],
                            'tires' => [
                                'front' => '110/70-13',
                                'rear' => '130/70-13',
                                'pressure_front_psi' => 26,
                                'pressure_rear_psi' => 29,
                            ],
                        ],
                    ],
                    [
                        'name' => 'NMAX 160 Connected',
                        'slug' => 'connected',
                        'year_from' => 2022,
                        'year_to' => null,
                        'engine_code' => '155cc Blue Core',
                        'engine_displacement' => 155,
                        'fuel_type' => 'gasolina',
                        'transmission' => 'cvt',
                        'specs' => [
                            'oil' => [
                                'viscosity' => '10W-40',
                                'api' => 'SL',
                                'jaso' => 'MB',
                                'capacity' => 0.85,
                                'type' => 'sintetico-4t',
                            ],
                            'tires' => [
                                'front' => '110/70-13',
                                'rear' => '130/70-13',
                                'pressure_front_psi' => 26,
                                'pressure_rear_psi' => 29,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}

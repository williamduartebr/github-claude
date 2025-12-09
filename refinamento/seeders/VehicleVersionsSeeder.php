<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;

/**
 * VehicleVersionsSeeder - EXPANDIDO Sprint 5
 * 
 * ✅ VERSÃO EXPANDIDA: 100 versões
 * - 6 modelos: Corolla, Hilux, Onix, S10, Civic, HR-V
 * - 6 anos: 2020-2025
 * - 3-4 versões por modelo/ano
 * 
 * EXECUÇÃO:
 * php artisan db:seed --class=VehicleVersionsSeeder
 */
class VehicleVersionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚗 Criando versões de veículos...');
        
        $count = 0;
        foreach ($this->versions() as $version) {
            VehicleVersion::updateOrCreate(
                ['id' => $version['id']],  // IDs fixos
                [
                    'model_id'     => $version['model_id'],
                    'name'         => $version['name'],
                    'slug'         => Str::slug($version['name']),
                    'year'         => $version['year'],
                    'engine_code'  => $version['engine_code'] ?? null,
                    'fuel_type'    => $version['fuel_type'],
                    'transmission' => $version['transmission'],
                    'price_msrp'   => $version['price_msrp'] ?? null,
                    'is_active'    => true,
                    'metadata'     => $version['metadata'] ?? null,
                ]
            );
            $count++;
        }

        $this->command->info("✅ {$count} versões criadas/atualizadas");
    }

    /**
     * Retorna array com todas as versões
     * Total: 100 versões (6 modelos × 6 anos × ~3 versões)
     */
    private function versions(): array
    {
        return array_merge(
            $this->toyotaCorollaVersions(),
            $this->toyotaHiluxVersions(),
            $this->chevroletOnixVersions(),
            $this->chevroletS10Versions(),
            $this->hondaCivicVersions(),
            $this->hondaHrvVersions()
        );
    }

    // ================================================================
    // TOYOTA COROLLA - 18 versões (6 anos × 3 versões)
    // ================================================================
    private function toyotaCorollaVersions(): array
    {
        // Model IDs: 1 = Corolla
        $modelId = 1;
        $baseId = 1000;

        return [
            // 2020 - 11ª Geração (E210) - Motor 1.8 e 2.0
            ['id' => $baseId + 1, 'model_id' => $modelId, 'year' => 2020, 'name' => 'GLi 1.8 CVT', 'engine_code' => '2ZR-FE', 'fuel_type' => 'gasoline', 'transmission' => 'automatic', 'price_msrp' => 95000],
            ['id' => $baseId + 2, 'model_id' => $modelId, 'year' => 2020, 'name' => 'XEi 2.0 CVT', 'engine_code' => '3ZR-FAE', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 110000],
            ['id' => $baseId + 3, 'model_id' => $modelId, 'year' => 2020, 'name' => 'Altis 2.0 CVT', 'engine_code' => '3ZR-FAE', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 125000],

            // 2021 - Motor 1.8 e 2.0
            ['id' => $baseId + 4, 'model_id' => $modelId, 'year' => 2021, 'name' => 'GLi 1.8 CVT', 'engine_code' => '2ZR-FE', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 98000],
            ['id' => $baseId + 5, 'model_id' => $modelId, 'year' => 2021, 'name' => 'XEi 2.0 CVT', 'engine_code' => '3ZR-FAE', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 113000],
            ['id' => $baseId + 6, 'model_id' => $modelId, 'year' => 2021, 'name' => 'Altis 2.0 CVT', 'engine_code' => '3ZR-FAE', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 128000],

            // 2022 - Motor 2.0 Dynamic Force
            ['id' => $baseId + 7, 'model_id' => $modelId, 'year' => 2022, 'name' => 'GLi 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 105000],
            ['id' => $baseId + 8, 'model_id' => $modelId, 'year' => 2022, 'name' => 'XEi 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 118000],
            ['id' => $baseId + 9, 'model_id' => $modelId, 'year' => 2022, 'name' => 'Altis Premium 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 135000],

            // 2023 - Motor 2.0 Dynamic Force
            ['id' => $baseId + 10, 'model_id' => $modelId, 'year' => 2023, 'name' => 'GLi 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 110000],
            ['id' => $baseId + 11, 'model_id' => $modelId, 'year' => 2023, 'name' => 'XEi 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 123000],
            ['id' => $baseId + 12, 'model_id' => $modelId, 'year' => 2023, 'name' => 'Altis Premium 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 142000],

            // 2024 - Motor 2.0 Dynamic Force + Cross
            ['id' => $baseId + 13, 'model_id' => $modelId, 'year' => 2024, 'name' => 'GLi 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 115000],
            ['id' => $baseId + 14, 'model_id' => $modelId, 'year' => 2024, 'name' => 'XEi 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 128000],
            ['id' => $baseId + 15, 'model_id' => $modelId, 'year' => 2024, 'name' => 'Altis Premium 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 148000],
            ['id' => $baseId + 16, 'model_id' => $modelId, 'year' => 2024, 'name' => 'Cross 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 155000],

            // 2025 - Motor 2.0 Dynamic Force (previsão)
            ['id' => $baseId + 17, 'model_id' => $modelId, 'year' => 2025, 'name' => 'GLi 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 122000],
            ['id' => $baseId + 18, 'model_id' => $modelId, 'year' => 2025, 'name' => 'Altis Premium 2.0 CVT', 'engine_code' => 'M20A-FKS', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 155000],
        ];
    }

    // ================================================================
    // TOYOTA HILUX - 18 versões (6 anos × 3 versões)
    // ================================================================
    private function toyotaHiluxVersions(): array
    {
        // Model IDs: 2 = Hilux
        $modelId = 2;
        $baseId = 2000;

        return [
            // 2020 - 8ª Geração (AN120/AN130) - Motor 2.8 Diesel
            ['id' => $baseId + 1, 'model_id' => $modelId, 'year' => 2020, 'name' => 'SR 2.8 Diesel 4x4 MT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 195000],
            ['id' => $baseId + 2, 'model_id' => $modelId, 'year' => 2020, 'name' => 'SRV 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 220000],
            ['id' => $baseId + 3, 'model_id' => $modelId, 'year' => 2020, 'name' => 'SRX 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 245000],

            // 2021 - Motor 2.8 Diesel
            ['id' => $baseId + 4, 'model_id' => $modelId, 'year' => 2021, 'name' => 'SR 2.8 Diesel 4x4 MT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 205000],
            ['id' => $baseId + 5, 'model_id' => $modelId, 'year' => 2021, 'name' => 'SRV 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 230000],
            ['id' => $baseId + 6, 'model_id' => $modelId, 'year' => 2021, 'name' => 'SRX 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 255000],

            // 2022 - Motor 2.8 Diesel Bi-Turbo
            ['id' => $baseId + 7, 'model_id' => $modelId, 'year' => 2022, 'name' => 'SR 2.8 Diesel 4x4 MT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 215000],
            ['id' => $baseId + 8, 'model_id' => $modelId, 'year' => 2022, 'name' => 'SRV 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 240000],
            ['id' => $baseId + 9, 'model_id' => $modelId, 'year' => 2022, 'name' => 'SRX Diamond 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 270000],

            // 2023 - Motor 2.8 Diesel Bi-Turbo
            ['id' => $baseId + 10, 'model_id' => $modelId, 'year' => 2023, 'name' => 'SR 2.8 Diesel 4x4 MT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 225000],
            ['id' => $baseId + 11, 'model_id' => $modelId, 'year' => 2023, 'name' => 'SRV 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 250000],
            ['id' => $baseId + 12, 'model_id' => $modelId, 'year' => 2023, 'name' => 'SRX Diamond 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 285000],

            // 2024 - Motor 2.8 Diesel Bi-Turbo
            ['id' => $baseId + 13, 'model_id' => $modelId, 'year' => 2024, 'name' => 'SR 2.8 Diesel 4x4 MT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 238000],
            ['id' => $baseId + 14, 'model_id' => $modelId, 'year' => 2024, 'name' => 'SRV 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 265000],
            ['id' => $baseId + 15, 'model_id' => $modelId, 'year' => 2024, 'name' => 'SRX Diamond 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 298000],

            // 2025 - Motor 2.8 Diesel (previsão)
            ['id' => $baseId + 16, 'model_id' => $modelId, 'year' => 2025, 'name' => 'SR 2.8 Diesel 4x4 MT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 250000],
            ['id' => $baseId + 17, 'model_id' => $modelId, 'year' => 2025, 'name' => 'SRV 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 280000],
            ['id' => $baseId + 18, 'model_id' => $modelId, 'year' => 2025, 'name' => 'GR Sport 2.8 Diesel 4x4 AT', 'engine_code' => '1GD-FTV', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 320000],
        ];
    }

    // ================================================================
    // CHEVROLET ONIX - 18 versões (6 anos × 3 versões)
    // ================================================================
    private function chevroletOnixVersions(): array
    {
        // Model IDs: 3 = Onix
        $modelId = 3;
        $baseId = 3000;

        return [
            // 2020 - 2ª Geração - Motor 1.0 e 1.0 Turbo
            ['id' => $baseId + 1, 'model_id' => $modelId, 'year' => 2020, 'name' => 'LT 1.0 MT', 'engine_code' => 'B10D', 'fuel_type' => 'flex', 'transmission' => 'manual', 'price_msrp' => 52000],
            ['id' => $baseId + 2, 'model_id' => $modelId, 'year' => 2020, 'name' => 'LT 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 62000],
            ['id' => $baseId + 3, 'model_id' => $modelId, 'year' => 2020, 'name' => 'Premier 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 68000],

            // 2021 - Motor 1.0 e 1.0 Turbo
            ['id' => $baseId + 4, 'model_id' => $modelId, 'year' => 2021, 'name' => 'LT 1.0 MT', 'engine_code' => 'B10D', 'fuel_type' => 'flex', 'transmission' => 'manual', 'price_msrp' => 55000],
            ['id' => $baseId + 5, 'model_id' => $modelId, 'year' => 2021, 'name' => 'LT 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 65000],
            ['id' => $baseId + 6, 'model_id' => $modelId, 'year' => 2021, 'name' => 'Premier 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 71000],

            // 2022 - Motor 1.0 e 1.0 Turbo
            ['id' => $baseId + 7, 'model_id' => $modelId, 'year' => 2022, 'name' => 'LT 1.0 MT', 'engine_code' => 'B10D', 'fuel_type' => 'flex', 'transmission' => 'manual', 'price_msrp' => 58000],
            ['id' => $baseId + 8, 'model_id' => $modelId, 'year' => 2022, 'name' => 'LTZ 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 68000],
            ['id' => $baseId + 9, 'model_id' => $modelId, 'year' => 2022, 'name' => 'Premier 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 75000],

            // 2023 - Motor 1.0 e 1.0 Turbo
            ['id' => $baseId + 10, 'model_id' => $modelId, 'year' => 2023, 'name' => 'LT 1.0 MT', 'engine_code' => 'B10D', 'fuel_type' => 'flex', 'transmission' => 'manual', 'price_msrp' => 62000],
            ['id' => $baseId + 11, 'model_id' => $modelId, 'year' => 2023, 'name' => 'LTZ 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 72000],
            ['id' => $baseId + 12, 'model_id' => $modelId, 'year' => 2023, 'name' => 'Premier 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 80000],

            // 2024 - Motor 1.0 e 1.0 Turbo
            ['id' => $baseId + 13, 'model_id' => $modelId, 'year' => 2024, 'name' => 'LT 1.0 MT', 'engine_code' => 'B10D', 'fuel_type' => 'flex', 'transmission' => 'manual', 'price_msrp' => 66000],
            ['id' => $baseId + 14, 'model_id' => $modelId, 'year' => 2024, 'name' => 'LTZ 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 76000],
            ['id' => $baseId + 15, 'model_id' => $modelId, 'year' => 2024, 'name' => 'Premier 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 85000],

            // 2025 - Motor 1.0 Turbo (previsão)
            ['id' => $baseId + 16, 'model_id' => $modelId, 'year' => 2025, 'name' => 'LT 1.0 MT', 'engine_code' => 'B10D', 'fuel_type' => 'flex', 'transmission' => 'manual', 'price_msrp' => 70000],
            ['id' => $baseId + 17, 'model_id' => $modelId, 'year' => 2025, 'name' => 'LTZ 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 82000],
            ['id' => $baseId + 18, 'model_id' => $modelId, 'year' => 2025, 'name' => 'RS 1.0 Turbo AT', 'engine_code' => 'B10DT', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 92000],
        ];
    }

    // ================================================================
    // CHEVROLET S10 - 18 versões (6 anos × 3 versões)
    // ================================================================
    private function chevroletS10Versions(): array
    {
        // Model IDs: 4 = S10
        $modelId = 4;
        $baseId = 4000;

        return [
            // 2020 - 3ª Geração - Motor 2.8 Diesel
            ['id' => $baseId + 1, 'model_id' => $modelId, 'year' => 2020, 'name' => 'LT 2.8 Diesel 4x4 MT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 170000],
            ['id' => $baseId + 2, 'model_id' => $modelId, 'year' => 2020, 'name' => 'LTZ 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 190000],
            ['id' => $baseId + 3, 'model_id' => $modelId, 'year' => 2020, 'name' => 'High Country 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 210000],

            // 2021 - Motor 2.8 Diesel
            ['id' => $baseId + 4, 'model_id' => $modelId, 'year' => 2021, 'name' => 'LT 2.8 Diesel 4x4 MT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 178000],
            ['id' => $baseId + 5, 'model_id' => $modelId, 'year' => 2021, 'name' => 'LTZ 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 198000],
            ['id' => $baseId + 6, 'model_id' => $modelId, 'year' => 2021, 'name' => 'High Country 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 218000],

            // 2022 - Motor 2.8 Diesel
            ['id' => $baseId + 7, 'model_id' => $modelId, 'year' => 2022, 'name' => 'LT 2.8 Diesel 4x4 MT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 188000],
            ['id' => $baseId + 8, 'model_id' => $modelId, 'year' => 2022, 'name' => 'LTZ 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 208000],
            ['id' => $baseId + 9, 'model_id' => $modelId, 'year' => 2022, 'name' => 'High Country 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 228000],

            // 2023 - Motor 2.8 Diesel
            ['id' => $baseId + 10, 'model_id' => $modelId, 'year' => 2023, 'name' => 'LT 2.8 Diesel 4x4 MT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 198000],
            ['id' => $baseId + 11, 'model_id' => $modelId, 'year' => 2023, 'name' => 'LTZ 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 218000],
            ['id' => $baseId + 12, 'model_id' => $modelId, 'year' => 2023, 'name' => 'High Country 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 240000],

            // 2024 - Motor 2.8 Diesel
            ['id' => $baseId + 13, 'model_id' => $modelId, 'year' => 2024, 'name' => 'LT 2.8 Diesel 4x4 MT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 210000],
            ['id' => $baseId + 14, 'model_id' => $modelId, 'year' => 2024, 'name' => 'LTZ 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 232000],
            ['id' => $baseId + 15, 'model_id' => $modelId, 'year' => 2024, 'name' => 'High Country 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 255000],

            // 2025 - Motor 2.8 Diesel (previsão)
            ['id' => $baseId + 16, 'model_id' => $modelId, 'year' => 2025, 'name' => 'LT 2.8 Diesel 4x4 MT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'manual', 'price_msrp' => 225000],
            ['id' => $baseId + 17, 'model_id' => $modelId, 'year' => 2025, 'name' => 'LTZ 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 245000],
            ['id' => $baseId + 18, 'model_id' => $modelId, 'year' => 2025, 'name' => 'Z71 2.8 Diesel 4x4 AT', 'engine_code' => 'LWH', 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'price_msrp' => 270000],
        ];
    }

    // ================================================================
    // HONDA CIVIC - 16 versões (6 anos × 2-3 versões)
    // ================================================================
    private function hondaCivicVersions(): array
    {
        // Model IDs: 5 = Civic
        $modelId = 5;
        $baseId = 5000;

        return [
            // 2020 - 10ª Geração (FC) - Motor 2.0
            ['id' => $baseId + 1, 'model_id' => $modelId, 'year' => 2020, 'name' => 'LX 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 92000],
            ['id' => $baseId + 2, 'model_id' => $modelId, 'year' => 2020, 'name' => 'EX 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 105000],
            ['id' => $baseId + 3, 'model_id' => $modelId, 'year' => 2020, 'name' => 'Touring 1.5 Turbo CVT', 'engine_code' => 'L15B', 'fuel_type' => 'gasoline', 'transmission' => 'automatic', 'price_msrp' => 125000],

            // 2021 - Motor 2.0
            ['id' => $baseId + 4, 'model_id' => $modelId, 'year' => 2021, 'name' => 'LX 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 96000],
            ['id' => $baseId + 5, 'model_id' => $modelId, 'year' => 2021, 'name' => 'EX 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 110000],
            ['id' => $baseId + 6, 'model_id' => $modelId, 'year' => 2021, 'name' => 'Touring 1.5 Turbo CVT', 'engine_code' => 'L15B', 'fuel_type' => 'gasoline', 'transmission' => 'automatic', 'price_msrp' => 132000],

            // 2022 - 11ª Geração (FE) - Motor 2.0 e 1.5 Turbo
            ['id' => $baseId + 7, 'model_id' => $modelId, 'year' => 2022, 'name' => 'LX 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 135000],
            ['id' => $baseId + 8, 'model_id' => $modelId, 'year' => 2022, 'name' => 'EX 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 150000],
            ['id' => $baseId + 9, 'model_id' => $modelId, 'year' => 2022, 'name' => 'Touring 1.5 Turbo CVT', 'engine_code' => 'L15B', 'fuel_type' => 'gasoline', 'transmission' => 'automatic', 'price_msrp' => 175000],

            // 2023 - Motor 2.0 e 1.5 Turbo
            ['id' => $baseId + 10, 'model_id' => $modelId, 'year' => 2023, 'name' => 'Sport 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 145000],
            ['id' => $baseId + 11, 'model_id' => $modelId, 'year' => 2023, 'name' => 'EX 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 158000],
            ['id' => $baseId + 12, 'model_id' => $modelId, 'year' => 2023, 'name' => 'Touring 1.5 Turbo CVT', 'engine_code' => 'L15B', 'fuel_type' => 'gasoline', 'transmission' => 'automatic', 'price_msrp' => 185000],

            // 2024 - Motor 2.0 e 1.5 Turbo
            ['id' => $baseId + 13, 'model_id' => $modelId, 'year' => 2024, 'name' => 'Sport 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 155000],
            ['id' => $baseId + 14, 'model_id' => $modelId, 'year' => 2024, 'name' => 'Touring 1.5 Turbo CVT', 'engine_code' => 'L15B', 'fuel_type' => 'gasoline', 'transmission' => 'automatic', 'price_msrp' => 195000],

            // 2025 - Motor 2.0 (previsão)
            ['id' => $baseId + 15, 'model_id' => $modelId, 'year' => 2025, 'name' => 'Sport 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 165000],
            ['id' => $baseId + 16, 'model_id' => $modelId, 'year' => 2025, 'name' => 'Touring 1.5 Turbo CVT', 'engine_code' => 'L15B', 'fuel_type' => 'gasoline', 'transmission' => 'automatic', 'price_msrp' => 205000],
        ];
    }

    // ================================================================
    // HONDA HR-V - 16 versões (6 anos × 2-3 versões)
    // ================================================================
    private function hondaHrvVersions(): array
    {
        // Model IDs: 6 = HR-V
        $modelId = 6;
        $baseId = 6000;

        return [
            // 2020 - 2ª Geração - Motor 1.8
            ['id' => $baseId + 1, 'model_id' => $modelId, 'year' => 2020, 'name' => 'LX 1.8 CVT', 'engine_code' => 'R18Z', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 95000],
            ['id' => $baseId + 2, 'model_id' => $modelId, 'year' => 2020, 'name' => 'EX 1.8 CVT', 'engine_code' => 'R18Z', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 105000],
            ['id' => $baseId + 3, 'model_id' => $modelId, 'year' => 2020, 'name' => 'Touring 1.5 Turbo CVT', 'engine_code' => 'L15B', 'fuel_type' => 'gasoline', 'transmission' => 'automatic', 'price_msrp' => 120000],

            // 2021 - Motor 1.8
            ['id' => $baseId + 4, 'model_id' => $modelId, 'year' => 2021, 'name' => 'LX 1.8 CVT', 'engine_code' => 'R18Z', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 98000],
            ['id' => $baseId + 5, 'model_id' => $modelId, 'year' => 2021, 'name' => 'EX 1.8 CVT', 'engine_code' => 'R18Z', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 108000],
            ['id' => $baseId + 6, 'model_id' => $modelId, 'year' => 2021, 'name' => 'Touring 1.5 Turbo CVT', 'engine_code' => 'L15B', 'fuel_type' => 'gasoline', 'transmission' => 'automatic', 'price_msrp' => 125000],

            // 2022 - 3ª Geração - Motor 2.0
            ['id' => $baseId + 7, 'model_id' => $modelId, 'year' => 2022, 'name' => 'EX 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 135000],
            ['id' => $baseId + 8, 'model_id' => $modelId, 'year' => 2022, 'name' => 'EXL 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 145000],
            ['id' => $baseId + 9, 'model_id' => $modelId, 'year' => 2022, 'name' => 'Touring 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 160000],

            // 2023 - Motor 2.0
            ['id' => $baseId + 10, 'model_id' => $modelId, 'year' => 2023, 'name' => 'EX 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 142000],
            ['id' => $baseId + 11, 'model_id' => $modelId, 'year' => 2023, 'name' => 'EXL 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 152000],
            ['id' => $baseId + 12, 'model_id' => $modelId, 'year' => 2023, 'name' => 'Touring 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 168000],

            // 2024 - Motor 2.0
            ['id' => $baseId + 13, 'model_id' => $modelId, 'year' => 2024, 'name' => 'EX 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 150000],
            ['id' => $baseId + 14, 'model_id' => $modelId, 'year' => 2024, 'name' => 'Touring 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 175000],

            // 2025 - Motor 2.0 (previsão)
            ['id' => $baseId + 15, 'model_id' => $modelId, 'year' => 2025, 'name' => 'EX 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 158000],
            ['id' => $baseId + 16, 'model_id' => $modelId, 'year' => 2025, 'name' => 'Advance 2.0 CVT', 'engine_code' => 'R20A', 'fuel_type' => 'flex', 'transmission' => 'automatic', 'price_msrp' => 185000],
        ];
    }
}
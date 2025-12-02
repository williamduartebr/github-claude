<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;

class VehicleMainMakesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->makes() as $make) {
            VehicleMake::updateOrCreate(
                ['id' => $make['id']], // IDs fixos para API futura
                [
                    'name' => $make['name'],
                    'slug' => Str::slug($make['name']),
                    'type' => $make['type'],
                    'country_origin' => $make['country'],
                    'is_active' => $make['is_active'],
                    'logo_url' => Str::slug($make['name']).'.svg'
                ]
            );
        }

        $this->command->info('✓ Marcas principais e secundárias cadastradas com sucesso.');
    }

    /**
     * Marcas mais conhecidas no Brasil (ativas) + secundárias (inativas)
     */
    private function makes(): array
    {
        return [

            // ============
            // PRINCIPAIS (is_active = true)
            // ============

            ['id' => 1, 'name' => 'Chevrolet', 'type' => 'car', 'country' => 'US', 'is_active' => true],
            ['id' => 2, 'name' => 'Volkswagen', 'type' => 'car', 'country' => 'DE', 'is_active' => true],
            ['id' => 3, 'name' => 'Fiat', 'type' => 'car', 'country' => 'IT', 'is_active' => true],
            ['id' => 4, 'name' => 'Toyota', 'type' => 'car', 'country' => 'JP', 'is_active' => true],
            ['id' => 5, 'name' => 'Hyundai', 'type' => 'car', 'country' => 'KR', 'is_active' => true],
            ['id' => 6, 'name' => 'Honda', 'type' => 'car', 'country' => 'JP', 'is_active' => true],
            ['id' => 7, 'name' => 'Renault', 'type' => 'car', 'country' => 'FR', 'is_active' => true],
            ['id' => 8, 'name' => 'Nissan', 'type' => 'car', 'country' => 'JP', 'is_active' => true],
            ['id' => 9, 'name' => 'Ford', 'type' => 'car', 'country' => 'US', 'is_active' => true],
            ['id' => 10, 'name' => 'Jeep', 'type' => 'car', 'country' => 'US', 'is_active' => true],

            // Motos (principais Brasil)
            ['id' => 20, 'name' => 'Honda Motos', 'type' => 'motorcycle', 'country' => 'JP', 'is_active' => true],
            ['id' => 21, 'name' => 'Yamaha', 'type' => 'motorcycle', 'country' => 'JP', 'is_active' => true],

            // Vans e utilitários principais
            ['id' => 30, 'name' => 'Mercedes-Benz Vans', 'type' => 'van', 'country' => 'DE', 'is_active' => true],
            ['id' => 31, 'name' => 'Fiat Utilitários', 'type' => 'van', 'country' => 'IT', 'is_active' => true],

            // Caminhões
            ['id' => 40, 'name' => 'Volvo Trucks', 'type' => 'truck', 'country' => 'SE', 'is_active' => true],
            ['id' => 41, 'name' => 'Scania', 'type' => 'truck', 'country' => 'SE', 'is_active' => true],
            ['id' => 42, 'name' => 'Mercedes-Benz Trucks', 'type' => 'truck', 'country' => 'DE', 'is_active' => true],
            ['id' => 43, 'name' => 'Volkswagen Caminhões', 'type' => 'truck', 'country' => 'BR', 'is_active' => true],
            ['id' => 44, 'name' => 'MAN', 'type' => 'truck', 'country' => 'DE', 'is_active' => true],
            ['id' => 45, 'name' => 'IVECO', 'type' => 'truck', 'country' => 'IT', 'is_active' => true],
            ['id' => 46, 'name' => 'DAF', 'type' => 'truck', 'country' => 'NL', 'is_active' => true],
            

            // Elétricos (mais vendidos no Brasil)
            ['id' => 50, 'name' => 'BYD', 'type' => 'electric', 'country' => 'CN', 'is_active' => true],
            ['id' => 51, 'name' => 'Tesla', 'type' => 'electric', 'country' => 'US', 'is_active' => false],
            ['id' => 52, 'name' => 'GWM Ora', 'type' => 'electric', 'country' => 'CN', 'is_active' => true],
            ['id' => 53, 'name' => 'Volvo Electric', 'type' => 'electric', 'country' => 'SE', 'is_active' => true],


            // ==========================
            // SECUNDÁRIAS (inativas)
            // ==========================

            ['id' => 100, 'name' => 'Peugeot', 'type' => 'car', 'country' => 'FR', 'is_active' => false],
            ['id' => 101, 'name' => 'Citroën', 'type' => 'car', 'country' => 'FR', 'is_active' => false],
            ['id' => 102, 'name' => 'Chery', 'type' => 'car', 'country' => 'CN', 'is_active' => false],
            ['id' => 103, 'name' => 'JAC Motors', 'type' => 'car', 'country' => 'CN', 'is_active' => false],
            ['id' => 104, 'name' => 'Suzuki', 'type' => 'car', 'country' => 'JP', 'is_active' => false],
            ['id' => 105, 'name' => 'Kia', 'type' => 'car', 'country' => 'KR', 'is_active' => false],
            ['id' => 106, 'name' => 'Mitsubishi', 'type' => 'car', 'country' => 'JP', 'is_active' => false],
            ['id' => 107, 'name' => 'Land Rover', 'type' => 'car', 'country' => 'UK', 'is_active' => false],
            ['id' => 108, 'name' => 'Audi', 'type' => 'car', 'country' => 'DE', 'is_active' => false],
            ['id' => 109, 'name' => 'BMW', 'type' => 'car', 'country' => 'DE', 'is_active' => false],
            ['id' => 110, 'name' => 'Porsche', 'type' => 'car', 'country' => 'DE', 'is_active' => false],
            ['id' => 111, 'name' => 'Jaguar', 'type' => 'car', 'country' => 'UK', 'is_active' => false],

        ];
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;

class VehicleVersionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->versions() as $version) {
            VehicleVersion::updateOrCreate(
                ['id' => $version['id']],  // IDs fixos
                [
                    'model_id'     => $version['model_id'],
                    'name'         => $version['name'],
                    'slug'         => Str::slug($version['name']),
                    'year'         => $version['year'],
                    'engine_code'  => $version['engine_code'] ?? null,
                    'fuel_type'    => $version['fuel_type'],      // precisa ser ENUM válido (:contentReference[oaicite:3]{index=3})
                    'transmission' => $version['transmission'],   // precisa ser ENUM válido (:contentReference[oaicite:4]{index=4})
                    'price_msrp'   => $version['price_msrp'] ?? null,
                    'is_active'    => true,
                    'metadata'     => $version['metadata'] ?? null,
                ]
            );
        }

        $this->command->info('✓ Versões cadastradas com sucesso!');
    }


    private function versions(): array
    {
        return [

            // ==================================================
            // model_id = 1 → Onix
            // ==================================================
            [
                'id' => 1,
                'model_id' => 1,
                'name' => 'Onix LT 1.0',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'manual',
                'engine_code' => 'CSS Prime',
                'price_msrp' => 82990,
            ],
            [
                'id' => 2,
                'model_id' => 1,
                'name' => 'Onix Premier 1.0 Turbo',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'automatic',
                'engine_code' => 'CSS Turbo',
                'price_msrp' => 109990,
            ],


            // ==================================================
            // model_id = 2 → Gol
            // ==================================================
            [
                'id' => 3,
                'model_id' => 2,
                'name' => 'Gol 1.0 MPI',
                'year' => 2022,
                'fuel_type' => 'flex',
                'transmission' => 'manual',
                'engine_code' => 'EA211',
                'price_msrp' => 75990,
            ],
            [
                'id' => 4,
                'model_id' => 2,
                'name' => 'Gol 1.6 MSI',
                'year' => 2021,
                'fuel_type' => 'flex',
                'transmission' => 'manual',
                'engine_code' => 'EA111',
                'price_msrp' => 82990,
            ],


            // ==================================================
            // model_id = 3 → Argo
            // ==================================================
            [
                'id' => 5,
                'model_id' => 3,
                'name' => 'Argo Drive 1.0',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'manual',
                'engine_code' => 'Firefly 1.0',
                'price_msrp' => 78990,
            ],
            [
                'id' => 6,
                'model_id' => 3,
                'name' => 'Argo Trekking 1.3',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'manual',
                'engine_code' => 'Firefly 1.3',
                'price_msrp' => 89990,
            ],


            // ==================================================
            // model_id = 4 → Corolla
            // ==================================================
            [
                'id' => 7,
                'model_id' => 4,
                'name' => 'Corolla GLi 2.0',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'cvt',
                'engine_code' => 'M20A-FKS',
                'price_msrp' => 152990,
            ],
            [
                'id' => 8,
                'model_id' => 4,
                'name' => 'Corolla XEi 2.0',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'cvt',
                'engine_code' => 'M20A-FKS',
                'price_msrp' => 167990,
            ],


            // ==================================================
            // model_id = 5 → HB20
            // ==================================================
            [
                'id' => 9,
                'model_id' => 5,
                'name' => 'HB20 Comfort 1.0',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'manual',
                'engine_code' => 'Kappa 1.0',
                'price_msrp' => 78590,
            ],
            [
                'id' => 10,
                'model_id' => 5,
                'name' => 'HB20 Platinum 1.0 Turbo',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'automatic',
                'engine_code' => 'Kappa 1.0 Turbo',
                'price_msrp' => 111990,
            ],


            // ==================================================
            // model_id = 6 → Civic
            // ==================================================
            [
                'id' => 11,
                'model_id' => 6,
                'name' => 'Civic Sport 2.0',
                'year' => 2021,
                'fuel_type' => 'flex',
                'transmission' => 'cvt',
                'engine_code' => 'R20Z1',
                'price_msrp' => 146000,
            ],
            [
                'id' => 12,
                'model_id' => 6,
                'name' => 'Civic Touring 1.5 Turbo',
                'year' => 2021,
                'fuel_type' => 'gasoline',
                'transmission' => 'cvt',
                'engine_code' => 'L15B7',
                'price_msrp' => 170000,
            ],


            // ==================================================
            // SUVs
            // ==================================================

            // model_id = 7 → Creta
            [
                'id' => 20,
                'model_id' => 7,
                'name' => 'Creta Comfort 1.6',
                'year' => 2022,
                'fuel_type' => 'flex',
                'transmission' => 'automatic',
                'engine_code' => 'Gamma 1.6',
                'price_msrp' => 114000,
            ],
            [
                'id' => 21,
                'model_id' => 7,
                'name' => 'Creta Ultimate 2.0',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'automatic',
                'engine_code' => 'Nu 2.0',
                'price_msrp' => 160000,
            ],

            // model_id = 8 → Compass
            [
                'id' => 22,
                'model_id' => 8,
                'name' => 'Compass Sport T270',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'automatic',
                'engine_code' => 'T270',
                'price_msrp' => 175000,
            ],
            [
                'id' => 23,
                'model_id' => 8,
                'name' => 'Compass Longitude Diesel 2.0',
                'year' => 2022,
                'fuel_type' => 'diesel',
                'transmission' => 'automatic',
                'engine_code' => 'Multijet 2.0',
                'price_msrp' => 200000,
            ],


            // ==================================================
            // ELÉTRICOS
            // ==================================================

            // model_id = 30 → BYD Dolphin
            [
                'id' => 30,
                'model_id' => 30,
                'name' => 'BYD Dolphin GS',
                'year' => 2023,
                'fuel_type' => 'electric',
                'transmission' => 'automatic',
                'engine_code' => 'e-Motor',
                'price_msrp' => 149800,
            ],

            // model_id = 31 → GWM Ora 03
            [
                'id' => 31,
                'model_id' => 31,
                'name' => 'GWM Ora 03 Skin',
                'year' => 2023,
                'fuel_type' => 'electric',
                'transmission' => 'automatic',
                'engine_code' => 'e-Motor GWM',
                'price_msrp' => 150000,
            ],

            // model_id = 32 → Volvo EX30
            [
                'id' => 32,
                'model_id' => 32,
                'name' => 'Volvo EX30 Ultra',
                'year' => 2023,
                'fuel_type' => 'electric',
                'transmission' => 'automatic',
                'engine_code' => 'e-Motor Volvo',
                'price_msrp' => 219000,
            ],


            // ==================================================
            // MOTOS
            // ==================================================

            // model_id = 40 → CG 160
            [
                'id' => 40,
                'model_id' => 40,
                'name' => 'CG 160 Start',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'manual',
                'engine_code' => 'OHC 160',
                'price_msrp' => 14200,
            ],
            [
                'id' => 41,
                'model_id' => 40,
                'name' => 'CG 160 Titan',
                'year' => 2023,
                'fuel_type' => 'flex',
                'transmission' => 'manual',
                'engine_code' => 'OHC 160',
                'price_msrp' => 17600,
            ],

            // model_id = 41 → Fazer 250
            [
                'id' => 42,
                'model_id' => 41,
                'name' => 'Fazer 250 ABS',
                'year' => 2023,
                'fuel_type' => 'gasoline',
                'transmission' => 'manual',
                'engine_code' => 'BlueFlex 250',
                'price_msrp' => 23500,
            ],

            // ==================================================
            // CAMINHÕES - MAN (model_id = 22 TGX, 23 TGS)
            // ==================================================

            // MAN TGX
            [
                'id' => 100,
                'model_id' => 22,
                'name' => 'MAN TGX 29.480',
                'year' => 2023,
                'fuel_type' => 'diesel',
                'transmission' => 'amt',
                'engine_code' => 'D26 480hp',
                'price_msrp' => 690000,
            ],

            [
                'id' => 101,
                'model_id' => 22,
                'name' => 'MAN TGX 28.440',
                'year' => 2022,
                'fuel_type' => 'diesel',
                'transmission' => 'amt',
                'engine_code' => 'D26 440hp',
                'price_msrp' => 640000,
            ],

            // MAN TGS
            [
                'id' => 102,
                'model_id' => 23,
                'name' => 'MAN TGS 26.440',
                'year' => 2023,
                'fuel_type' => 'diesel',
                'transmission' => 'amt',
                'engine_code' => 'D26 440hp',
                'price_msrp' => 610000,
            ],


            // ==================================================
            // CAMINHÕES - IVECO (model_id = 24 Hi-Way, 25 Eurocargo)
            // ==================================================

            // IVECO Hi-Way
            [
                'id' => 110,
                'model_id' => 24,
                'name' => 'Iveco Hi-Way 600S',
                'year' => 2022,
                'fuel_type' => 'diesel',
                'transmission' => 'amt',
                'engine_code' => 'Cursor 13 600hp',
                'price_msrp' => 680000,
            ],

            [
                'id' => 111,
                'model_id' => 24,
                'name' => 'Iveco Hi-Way 480',
                'year' => 2021,
                'fuel_type' => 'diesel',
                'transmission' => 'manual',
                'engine_code' => 'Cursor 13 480hp',
                'price_msrp' => 630000,
            ],

            // IVECO Eurocargo
            [
                'id' => 112,
                'model_id' => 25,
                'name' => 'Iveco Eurocargo 170E',
                'year' => 2023,
                'fuel_type' => 'diesel',
                'transmission' => 'manual',
                'engine_code' => 'Tector 6.7',
                'price_msrp' => 410000,
            ],


            // ==================================================
            // CAMINHÕES - DAF (model_id = 26 XF, 27 CF)
            // ==================================================

            // DAF XF
            [
                'id' => 120,
                'model_id' => 26,
                'name' => 'DAF XF 530',
                'year' => 2023,
                'fuel_type' => 'diesel',
                'transmission' => 'amt',
                'engine_code' => 'PACCAR MX-13 530hp',
                'price_msrp' => 720000,
            ],

            [
                'id' => 121,
                'model_id' => 26,
                'name' => 'DAF XF 480',
                'year' => 2022,
                'fuel_type' => 'diesel',
                'transmission' => 'amt',
                'engine_code' => 'PACCAR MX-13 480hp',
                'price_msrp' => 680000,
            ],

            // DAF CF
            [
                'id' => 122,
                'model_id' => 27,
                'name' => 'DAF CF 410',
                'year' => 2022,
                'fuel_type' => 'diesel',
                'transmission' => 'manual',
                'engine_code' => 'PACCAR MX-11 410hp',
                'price_msrp' => 520000,
            ],

        ];
    }
}

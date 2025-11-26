<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * Seeder para categorias de guias
 */
class GuideCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Óleo do Motor',
                'slug' => 'oleo',
                'description' => 'Especificações de óleo do motor, capacidades e recomendações',
                'icon' => 'fa-oil-can',
                'order' => 1,
                'active' => true,
            ],
            [
                'name' => 'Calibragem de Pneus',
                'slug' => 'calibragem',
                'description' => 'Pressão recomendada dos pneus para diferentes condições',
                'icon' => 'fa-gauge',
                'order' => 2,
                'active' => true,
            ],
            [
                'name' => 'Pneus e Rodas',
                'slug' => 'pneus',
                'description' => 'Medidas de pneus e rodas recomendadas',
                'icon' => 'fa-circle-notch',
                'order' => 3,
                'active' => true,
            ],
            [
                'name' => 'Revisão Programada',
                'slug' => 'revisao',
                'description' => 'Plano de manutenção preventiva e revisões',
                'icon' => 'fa-wrench',
                'order' => 4,
                'active' => true,
            ],
            [
                'name' => 'Consumo de Combustível',
                'slug' => 'consumo',
                'description' => 'Médias de consumo em cidade, estrada e misto',
                'icon' => 'fa-gas-pump',
                'order' => 5,
                'active' => true,
            ],
            [
                'name' => 'Problemas Comuns',
                'slug' => 'problemas',
                'description' => 'Problemas conhecidos e soluções',
                'icon' => 'fa-triangle-exclamation',
                'order' => 6,
                'active' => true,
            ],
            [
                'name' => 'Fluidos e Lubrificantes',
                'slug' => 'fluidos',
                'description' => 'Especificações de todos os fluidos do veículo',
                'icon' => 'fa-droplet',
                'order' => 7,
                'active' => true,
            ],
            [
                'name' => 'Bateria',
                'slug' => 'bateria',
                'description' => 'Especificações da bateria e sistema elétrico',
                'icon' => 'fa-battery-full',
                'order' => 8,
                'active' => true,
            ],
            [
                'name' => 'Câmbio e Transmissão',
                'slug' => 'cambio',
                'description' => 'Informações sobre câmbio e transmissão',
                'icon' => 'fa-gears',
                'order' => 9,
                'active' => true,
            ],
            [
                'name' => 'Motor e Especificações',
                'slug' => 'motor',
                'description' => 'Especificações técnicas do motor',
                'icon' => 'fa-engine',
                'order' => 10,
                'active' => true,
            ],
        ];

        foreach ($categories as $category) {
            GuideCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Guide categories seeded successfully!');
    }
}

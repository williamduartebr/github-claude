<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * Seeder de categorias de guias
 * 
 * Baseado nos mockups HTML - 14 categorias identificadas
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
                'name' => 'Óleo',
                'slug' => 'oleo',
                'description' => 'Especificações de óleo do motor, capacidades e recomendações',
                'icon' => '🛢️',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Calibragem',
                'slug' => 'calibragem',
                'description' => 'Pressão recomendada dos pneus para diferentes condições',
                'icon' => '🔧',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Pneus',
                'slug' => 'pneus',
                'description' => 'Medidas de pneus e rodas recomendadas',
                'icon' => '🚗',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Consumo',
                'slug' => 'consumo',
                'description' => 'Médias de consumo em cidade, estrada e misto',
                'icon' => '⛽',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Problemas',
                'slug' => 'problemas',
                'description' => 'Problemas conhecidos e soluções',
                'icon' => '⚠️',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Revisão',
                'slug' => 'revisao',
                'description' => 'Plano de manutenção preventiva e revisões',
                'icon' => '📋',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Arrefecimento',
                'slug' => 'arrefecimento',
                'description' => 'Sistema de arrefecimento e fluido de radiador',
                'icon' => '🌡️',
                'order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Câmbio',
                'slug' => 'cambio',
                'description' => 'Informações sobre câmbio e transmissão',
                'icon' => '⚙️',
                'order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Torque',
                'slug' => 'torque',
                'description' => 'Torque de aperto de parafusos e componentes',
                'icon' => '🔩',
                'order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Fluidos',
                'slug' => 'fluidos',
                'description' => 'Especificações de todos os fluidos do veículo',
                'icon' => '💧',
                'order' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Bateria',
                'slug' => 'bateria',
                'description' => 'Especificações da bateria e sistema elétrico',
                'icon' => '🔋',
                'order' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'Elétrica',
                'slug' => 'eletrica',
                'description' => 'Sistema elétrico e componentes',
                'icon' => '⚡',
                'order' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Motores',
                'slug' => 'motores',
                'description' => 'Especificações técnicas do motor',
                'icon' => '🏎️',
                'order' => 13,
                'is_active' => true,
            ],
            [
                'name' => 'Manutenção',
                'slug' => 'manutencao',
                'description' => 'Guias gerais de manutenção preventiva',
                'icon' => '🔧',
                'order' => 14,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            GuideCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('✓ 14 categorias de guias criadas com sucesso!');
    }
}

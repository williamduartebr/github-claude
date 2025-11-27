<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCluster;

/**
 * Seeder de Clusters de Guias - Links Internos SEO
 *
 * Tipos de clusters:
 * - super: Todos os guias de um modelo
 * - category: Guias da mesma categoria por modelo
 * - related: Guias relacionados (mesmo modelo, categorias diferentes)
 * - year: Guias do mesmo ano
 * - generation: Guias da mesma geração do veículo
 *
 * @author Claude AI Assistant
 * @version 1.0.0
 */
class GuideClusterSeeder extends Seeder
{
    public function run(): void
    {
        $clusters = $this->getClustersData();
        $insertedCount = 0;

        foreach ($clusters as $clusterData) {
            GuideCluster::updateOrCreate(
                [
                    'make_slug' => $clusterData['make_slug'],
                    'model_slug' => $clusterData['model_slug'],
                    'cluster_type' => $clusterData['cluster_type'],
                ],
                $clusterData
            );
            $insertedCount++;
        }

        $this->command->info("✅ {$insertedCount} clusters inseridos!");
    }

    private function getClustersData(): array
    {
        return [
            // ═══════════════════════════════════════════════════════════════
            // TOYOTA COROLLA - SUPER CLUSTER
            // ═══════════════════════════════════════════════════════════════
            [
                'make_slug' => 'toyota',
                'model_slug' => 'corolla',
                'cluster_type' => 'super',
                'year_range' => '2020-2025',
                'links' => [
                    ['title' => 'Óleo Corolla XEi 2024', 'url' => '/guias/oleo/toyota/corolla/xei-2-0/2024', 'category' => 'oleo'],
                    ['title' => 'Óleo Corolla GLi 2024', 'url' => '/guias/oleo/toyota/corolla/gli-2-0/2024', 'category' => 'oleo'],
                    ['title' => 'Calibragem Corolla XEi 2024', 'url' => '/guias/calibragem/toyota/corolla/xei-2-0/2024', 'category' => 'calibragem'],
                    ['title' => 'Revisão Corolla XEi 2024', 'url' => '/guias/revisao/toyota/corolla/xei-2-0/2024', 'category' => 'revisao'],
                ],
                'metadata' => [
                    'total_guides' => 4,
                    'categories' => ['oleo', 'calibragem', 'revisao'],
                ],
            ],

            // Toyota Corolla - Cluster por Categoria (Óleo)
            [
                'make_slug' => 'toyota',
                'model_slug' => 'corolla',
                'cluster_type' => 'category',
                'category_slug' => 'oleo',
                'year_range' => '2020-2025',
                'links' => [
                    ['title' => 'Óleo Corolla XEi 2.0 2024', 'url' => '/guias/oleo/toyota/corolla/xei-2-0/2024', 'version' => 'XEi 2.0'],
                    ['title' => 'Óleo Corolla GLi 2.0 2024', 'url' => '/guias/oleo/toyota/corolla/gli-2-0/2024', 'version' => 'GLi 2.0'],
                ],
                'metadata' => ['category' => 'oleo'],
            ],

            // ═══════════════════════════════════════════════════════════════
            // FIAT STRADA - SUPER CLUSTER
            // ═══════════════════════════════════════════════════════════════
            [
                'make_slug' => 'fiat',
                'model_slug' => 'strada',
                'cluster_type' => 'super',
                'year_range' => '2021-2025',
                'links' => [
                    ['title' => 'Óleo Strada Freedom 2024', 'url' => '/guias/oleo/fiat/strada/freedom-1-3/2024', 'category' => 'oleo'],
                    ['title' => 'Óleo Strada Volcano 2024', 'url' => '/guias/oleo/fiat/strada/volcano-1-3/2024', 'category' => 'oleo'],
                    ['title' => 'Calibragem Strada Freedom 2024', 'url' => '/guias/calibragem/fiat/strada/freedom-1-3/2024', 'category' => 'calibragem'],
                    ['title' => 'Revisão Strada Freedom 2024', 'url' => '/guias/revisao/fiat/strada/freedom-1-3/2024', 'category' => 'revisao'],
                ],
                'metadata' => [
                    'total_guides' => 4,
                    'categories' => ['oleo', 'calibragem', 'revisao'],
                ],
            ],

            // Fiat Strada - Cluster por Categoria (Óleo)
            [
                'make_slug' => 'fiat',
                'model_slug' => 'strada',
                'cluster_type' => 'category',
                'category_slug' => 'oleo',
                'year_range' => '2021-2025',
                'links' => [
                    ['title' => 'Óleo Strada Freedom 1.3 2024', 'url' => '/guias/oleo/fiat/strada/freedom-1-3/2024', 'version' => 'Freedom 1.3'],
                    ['title' => 'Óleo Strada Volcano 1.3 2024', 'url' => '/guias/oleo/fiat/strada/volcano-1-3/2024', 'version' => 'Volcano 1.3'],
                ],
                'metadata' => ['category' => 'oleo'],
            ],

            // ═══════════════════════════════════════════════════════════════
            // VOLKSWAGEN POLO - SUPER CLUSTER
            // ═══════════════════════════════════════════════════════════════
            [
                'make_slug' => 'volkswagen',
                'model_slug' => 'polo',
                'cluster_type' => 'super',
                'year_range' => '2018-2025',
                'links' => [
                    ['title' => 'Óleo Polo Highline TSI 2024', 'url' => '/guias/oleo/volkswagen/polo/highline-1-0-tsi/2024', 'category' => 'oleo'],
                    ['title' => 'Calibragem Polo Highline 2024', 'url' => '/guias/calibragem/volkswagen/polo/highline-1-0-tsi/2024', 'category' => 'calibragem'],
                ],
                'metadata' => [
                    'total_guides' => 2,
                    'categories' => ['oleo', 'calibragem'],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // CHEVROLET ONIX - SUPER CLUSTER
            // ═══════════════════════════════════════════════════════════════
            [
                'make_slug' => 'chevrolet',
                'model_slug' => 'onix',
                'cluster_type' => 'super',
                'year_range' => '2020-2025',
                'links' => [
                    ['title' => 'Óleo Onix LTZ Turbo 2024', 'url' => '/guias/oleo/chevrolet/onix/ltz-1-0-turbo/2024', 'category' => 'oleo'],
                    ['title' => 'Calibragem Onix LTZ 2024', 'url' => '/guias/calibragem/chevrolet/onix/ltz-1-0-turbo/2024', 'category' => 'calibragem'],
                ],
                'metadata' => [
                    'total_guides' => 2,
                    'categories' => ['oleo', 'calibragem'],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // HYUNDAI HB20 - SUPER CLUSTER
            // ═══════════════════════════════════════════════════════════════
            [
                'make_slug' => 'hyundai',
                'model_slug' => 'hb20',
                'cluster_type' => 'super',
                'year_range' => '2020-2025',
                'links' => [
                    ['title' => 'Óleo HB20 Platinum Turbo 2024', 'url' => '/guias/oleo/hyundai/hb20/platinum-1-0-turbo/2024', 'category' => 'oleo'],
                ],
                'metadata' => [
                    'total_guides' => 1,
                    'categories' => ['oleo'],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MOTOS - HONDA CG 160
            // ═══════════════════════════════════════════════════════════════
            [
                'make_slug' => 'honda',
                'model_slug' => 'cg-160',
                'cluster_type' => 'super',
                'year_range' => '2016-2025',
                'links' => [
                    ['title' => 'Óleo CG 160 Titan 2024', 'url' => '/guias/oleo/honda/cg-160/titan/2024', 'category' => 'oleo'],
                    ['title' => 'Calibragem CG 160 Titan 2024', 'url' => '/guias/calibragem/honda/cg-160/titan/2024', 'category' => 'calibragem'],
                ],
                'metadata' => [
                    'total_guides' => 2,
                    'categories' => ['oleo', 'calibragem'],
                    'vehicle_type' => 'motorcycle',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MOTOS - YAMAHA FAZER 250
            // ═══════════════════════════════════════════════════════════════
            [
                'make_slug' => 'yamaha',
                'model_slug' => 'fazer-250',
                'cluster_type' => 'super',
                'year_range' => '2016-2025',
                'links' => [
                    ['title' => 'Óleo Fazer 250 ABS 2024', 'url' => '/guias/oleo/yamaha/fazer-250/abs/2024', 'category' => 'oleo'],
                ],
                'metadata' => [
                    'total_guides' => 1,
                    'categories' => ['oleo'],
                    'vehicle_type' => 'motorcycle',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // CLUSTERS RELACIONADOS - CROSS-MODEL (Sedans Médios)
            // ═══════════════════════════════════════════════════════════════
            [
                'make_slug' => '_cross',
                'model_slug' => 'sedans-medios',
                'cluster_type' => 'related',
                'year_range' => '2024',
                'links' => [
                    ['title' => 'Óleo Toyota Corolla 2024', 'url' => '/guias/oleo/toyota/corolla/xei-2-0/2024', 'make' => 'Toyota'],
                    ['title' => 'Óleo Honda Civic 2024', 'url' => '/guias/oleo/honda/civic/exl-2-0/2024', 'make' => 'Honda'],
                    ['title' => 'Óleo VW Virtus 2024', 'url' => '/guias/oleo/volkswagen/virtus/highline-1-0-tsi/2024', 'make' => 'VW'],
                ],
                'metadata' => [
                    'segment' => 'sedan-medio',
                    'comparison_type' => 'competitors',
                ],
            ],

            // Clusters Relacionados - Hatches Compactos
            [
                'make_slug' => '_cross',
                'model_slug' => 'hatches-compactos',
                'cluster_type' => 'related',
                'year_range' => '2024',
                'links' => [
                    ['title' => 'Óleo VW Polo 2024', 'url' => '/guias/oleo/volkswagen/polo/highline-1-0-tsi/2024', 'make' => 'VW'],
                    ['title' => 'Óleo Chevrolet Onix 2024', 'url' => '/guias/oleo/chevrolet/onix/ltz-1-0-turbo/2024', 'make' => 'Chevrolet'],
                    ['title' => 'Óleo Hyundai HB20 2024', 'url' => '/guias/oleo/hyundai/hb20/platinum-1-0-turbo/2024', 'make' => 'Hyundai'],
                ],
                'metadata' => [
                    'segment' => 'hatch-compacto',
                    'comparison_type' => 'competitors',
                ],
            ],

            // Clusters Relacionados - Pickups Compactas
            [
                'make_slug' => '_cross',
                'model_slug' => 'pickups-compactas',
                'cluster_type' => 'related',
                'year_range' => '2024',
                'links' => [
                    ['title' => 'Óleo Fiat Strada 2024', 'url' => '/guias/oleo/fiat/strada/freedom-1-3/2024', 'make' => 'Fiat'],
                    ['title' => 'Óleo VW Saveiro 2024', 'url' => '/guias/oleo/volkswagen/saveiro/robust-1-6/2024', 'make' => 'VW'],
                ],
                'metadata' => [
                    'segment' => 'pickup-compacta',
                    'comparison_type' => 'competitors',
                ],
            ],

            // Clusters Relacionados - Motos 160cc
            [
                'make_slug' => '_cross',
                'model_slug' => 'motos-160cc',
                'cluster_type' => 'related',
                'year_range' => '2024',
                'links' => [
                    ['title' => 'Óleo Honda CG 160 2024', 'url' => '/guias/oleo/honda/cg-160/titan/2024', 'make' => 'Honda'],
                    ['title' => 'Óleo Yamaha Factor 150 2024', 'url' => '/guias/oleo/yamaha/factor-150/ed/2024', 'make' => 'Yamaha'],
                ],
                'metadata' => [
                    'segment' => 'street-entrada',
                    'vehicle_type' => 'motorcycle',
                    'comparison_type' => 'competitors',
                ],
            ],
        ];
    }
}
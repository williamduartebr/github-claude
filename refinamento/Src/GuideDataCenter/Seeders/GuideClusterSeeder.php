<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCluster;

/**
 * Seeder para clusters de exemplo
 */
class GuideClusterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca alguns guias para criar clusters
        $fiatUno = Guide::where('slug', 'fiat-uno-oleo-2010-2020')->first();
        $vwGol = Guide::where('slug', 'volkswagen-gol-pneus-2013-2022')->first();
        $hondaCivic = Guide::where('slug', 'honda-civic-calibragem-2017-2021')->first();

        if ($fiatUno) {
            GuideCluster::create([
                'guide_id' => $fiatUno->_id,
                'make_slug' => 'fiat',
                'model_slug' => 'uno',
                'year_range' => '2010-2020',
                'cluster_type' => GuideCluster::TYPE_SUPER,
                'links' => [
                    'oleo' => [
                        'url' => $fiatUno->url,
                        'title' => 'Óleo do Motor Fiat Uno',
                        'guide_id' => $fiatUno->_id,
                    ],
                ],
            ]);
        }

        if ($vwGol) {
            GuideCluster::create([
                'guide_id' => $vwGol->_id,
                'make_slug' => 'volkswagen',
                'model_slug' => 'gol',
                'year_range' => '2013-2022',
                'cluster_type' => GuideCluster::TYPE_SUPER,
                'links' => [
                    'pneus' => [
                        'url' => $vwGol->url,
                        'title' => 'Pneus VW Gol',
                        'guide_id' => $vwGol->_id,
                    ],
                ],
            ]);
        }

        if ($hondaCivic) {
            GuideCluster::create([
                'guide_id' => $hondaCivic->_id,
                'make_slug' => 'honda',
                'model_slug' => 'civic',
                'year_range' => '2017-2021',
                'cluster_type' => GuideCluster::TYPE_CATEGORY,
                'links' => [
                    'calibragem' => [
                        'url' => $hondaCivic->url,
                        'title' => 'Calibragem Honda Civic',
                        'guide_id' => $hondaCivic->_id,
                    ],
                ],
            ]);
        }

        $this->command->info('Sample clusters seeded successfully!');
    }
}

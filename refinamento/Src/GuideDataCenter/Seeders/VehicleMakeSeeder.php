<?php

declare(strict_types=1);

namespace Src\VehicleDataCenter\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;

/**
 * Seeder de Marcas de Veículos - Mercado Brasileiro
 *
 * Este seeder contém as principais marcas comercializadas no Brasil,
 * estruturado para ser escalável via API Claude (Sonnet 4.5 / Haiku 4.5).
 *
 * Estrutura preparada para geração automática de conteúdo:
 * - /guias (técnicos): 1.300 artigos/semana
 * - /veiculos (fichas): 800 artigos/semana
 *
 * @author Claude AI Assistant
 * @version 1.0.0
 */
class VehicleMakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $makes = $this->getMakesData();

        foreach ($makes as $makeData) {
            VehicleMake::updateOrCreate(
                ['slug' => $makeData['slug']],
                [
                    'name' => $makeData['name'],
                    'slug' => $makeData['slug'],
                    'country_origin' => $makeData['country_origin'],
                    'type' => $makeData['type'],
                    'logo_url' => $makeData['logo_url'] ?? null,
                    'website' => $makeData['website'] ?? null,
                    'is_active' => $makeData['is_active'] ?? true,
                    'market_share_br' => $makeData['market_share_br'] ?? null,
                    'ranking_br' => $makeData['ranking_br'] ?? null,
                    'metadata' => $makeData['metadata'] ?? [],
                ]
            );
        }

        $this->command->info('✅ ' . count($makes) . ' marcas de veículos inseridas com sucesso!');
    }

    /**
     * Retorna dados das marcas do mercado brasileiro
     *
     * Ranking baseado em vendas acumuladas (FENABRAVE 2024)
     * Top 20 marcas + marcas premium + motos
     *
     * @return array<int, array<string, mixed>>
     */
    private function getMakesData(): array
    {
        return [
            // ═══════════════════════════════════════════════════════════════
            // TOP 10 MARCAS - MAIOR VOLUME DE VENDAS NO BRASIL
            // ═══════════════════════════════════════════════════════════════

            [
                'name' => 'Fiat',
                'slug' => 'fiat',
                'country_origin' => 'IT',
                'type' => 'car',
                'logo_url' => '/images/brands/fiat/logo.png',
                'website' => 'https://www.fiat.com.br',
                'is_active' => true,
                'market_share_br' => 21.5,
                'ranking_br' => 1,
                'metadata' => [
                    'founded' => 1899,
                    'headquarters' => 'Turim, Itália',
                    'brazil_since' => 1976,
                    'factories_br' => ['Betim-MG', 'Goiana-PE'],
                    'group' => 'Stellantis',
                    'segments' => ['hatch', 'sedan', 'suv', 'pickup', 'van'],
                ],
            ],

            [
                'name' => 'Volkswagen',
                'slug' => 'volkswagen',
                'country_origin' => 'DE',
                'type' => 'car',
                'logo_url' => '/images/brands/volkswagen/logo.png',
                'website' => 'https://www.vw.com.br',
                'is_active' => true,
                'market_share_br' => 15.8,
                'ranking_br' => 2,
                'metadata' => [
                    'founded' => 1937,
                    'headquarters' => 'Wolfsburg, Alemanha',
                    'brazil_since' => 1953,
                    'factories_br' => ['São Bernardo do Campo-SP', 'Taubaté-SP', 'São Carlos-SP', 'São José dos Pinhais-PR'],
                    'group' => 'Volkswagen AG',
                    'segments' => ['hatch', 'sedan', 'suv', 'pickup'],
                ],
            ],

            [
                'name' => 'General Motors',
                'slug' => 'chevrolet',
                'country_origin' => 'US',
                'type' => 'car',
                'logo_url' => '/images/brands/chevrolet/logo.png',
                'website' => 'https://www.chevrolet.com.br',
                'is_active' => true,
                'market_share_br' => 14.2,
                'ranking_br' => 3,
                'metadata' => [
                    'founded' => 1911,
                    'headquarters' => 'Detroit, EUA',
                    'brazil_since' => 1925,
                    'factories_br' => ['São Caetano do Sul-SP', 'São José dos Campos-SP', 'Gravataí-RS', 'Joinville-SC'],
                    'group' => 'General Motors',
                    'segments' => ['hatch', 'sedan', 'suv', 'pickup'],
                ],
            ],

            [
                'name' => 'Toyota',
                'slug' => 'toyota',
                'country_origin' => 'JP',
                'type' => 'car',
                'logo_url' => '/images/brands/toyota/logo.png',
                'website' => 'https://www.toyota.com.br',
                'is_active' => true,
                'market_share_br' => 10.5,
                'ranking_br' => 4,
                'metadata' => [
                    'founded' => 1937,
                    'headquarters' => 'Toyota City, Japão',
                    'brazil_since' => 1958,
                    'factories_br' => ['Indaiatuba-SP', 'Sorocaba-SP', 'Porto Feliz-SP'],
                    'group' => 'Toyota Motor Corporation',
                    'segments' => ['sedan', 'suv', 'pickup', 'hatch'],
                ],
            ],

            [
                'name' => 'Hyundai',
                'slug' => 'hyundai',
                'country_origin' => 'KR',
                'type' => 'car',
                'logo_url' => '/images/brands/hyundai/logo.png',
                'website' => 'https://www.hyundai.com.br',
                'is_active' => true,
                'market_share_br' => 8.7,
                'ranking_br' => 5,
                'metadata' => [
                    'founded' => 1967,
                    'headquarters' => 'Seul, Coreia do Sul',
                    'brazil_since' => 2012,
                    'factories_br' => ['Piracicaba-SP'],
                    'group' => 'Hyundai Motor Group',
                    'segments' => ['hatch', 'sedan', 'suv'],
                ],
            ],

            [
                'name' => 'Jeep',
                'slug' => 'jeep',
                'country_origin' => 'US',
                'type' => 'car',
                'logo_url' => '/images/brands/jeep/logo.png',
                'website' => 'https://www.jeep.com.br',
                'is_active' => true,
                'market_share_br' => 6.8,
                'ranking_br' => 6,
                'metadata' => [
                    'founded' => 1941,
                    'headquarters' => 'Toledo, EUA',
                    'brazil_since' => 2015,
                    'factories_br' => ['Goiana-PE'],
                    'group' => 'Stellantis',
                    'segments' => ['suv'],
                ],
            ],

            [
                'name' => 'Renault',
                'slug' => 'renault',
                'country_origin' => 'FR',
                'type' => 'car',
                'logo_url' => '/images/brands/renault/logo.png',
                'website' => 'https://www.renault.com.br',
                'is_active' => true,
                'market_share_br' => 5.2,
                'ranking_br' => 7,
                'metadata' => [
                    'founded' => 1899,
                    'headquarters' => 'Boulogne-Billancourt, França',
                    'brazil_since' => 1998,
                    'factories_br' => ['São José dos Pinhais-PR'],
                    'group' => 'Renault Group',
                    'segments' => ['hatch', 'sedan', 'suv', 'pickup'],
                ],
            ],

            [
                'name' => 'Honda',
                'slug' => 'honda',
                'country_origin' => 'JP',
                'type' => 'car',
                'logo_url' => '/images/brands/honda/logo.png',
                'website' => 'https://www.honda.com.br',
                'is_active' => true,
                'market_share_br' => 4.8,
                'ranking_br' => 8,
                'metadata' => [
                    'founded' => 1948,
                    'headquarters' => 'Tóquio, Japão',
                    'brazil_since' => 1971,
                    'factories_br' => ['Sumaré-SP', 'Itirapina-SP'],
                    'group' => 'Honda Motor Co.',
                    'segments' => ['hatch', 'sedan', 'suv'],
                ],
            ],

            [
                'name' => 'Nissan',
                'slug' => 'nissan',
                'country_origin' => 'JP',
                'type' => 'car',
                'logo_url' => '/images/brands/nissan/logo.png',
                'website' => 'https://www.nissan.com.br',
                'is_active' => true,
                'market_share_br' => 3.5,
                'ranking_br' => 9,
                'metadata' => [
                    'founded' => 1933,
                    'headquarters' => 'Yokohama, Japão',
                    'brazil_since' => 2002,
                    'factories_br' => ['Resende-RJ'],
                    'group' => 'Renault-Nissan-Mitsubishi Alliance',
                    'segments' => ['hatch', 'sedan', 'suv', 'pickup'],
                ],
            ],

            [
                'name' => 'Peugeot',
                'slug' => 'peugeot',
                'country_origin' => 'FR',
                'type' => 'car',
                'logo_url' => '/images/brands/peugeot/logo.png',
                'website' => 'https://www.peugeot.com.br',
                'is_active' => true,
                'market_share_br' => 2.1,
                'ranking_br' => 10,
                'metadata' => [
                    'founded' => 1810,
                    'headquarters' => 'Paris, França',
                    'brazil_since' => 1992,
                    'factories_br' => ['Porto Real-RJ'],
                    'group' => 'Stellantis',
                    'segments' => ['hatch', 'sedan', 'suv'],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MARCAS SECUNDÁRIAS - VOLUME MÉDIO
            // ═══════════════════════════════════════════════════════════════

            [
                'name' => 'Citroën',
                'slug' => 'citroen',
                'country_origin' => 'FR',
                'type' => 'car',
                'logo_url' => '/images/brands/citroen/logo.png',
                'website' => 'https://www.citroen.com.br',
                'is_active' => true,
                'market_share_br' => 1.8,
                'ranking_br' => 11,
                'metadata' => [
                    'founded' => 1919,
                    'headquarters' => 'Paris, França',
                    'brazil_since' => 1992,
                    'factories_br' => ['Porto Real-RJ'],
                    'group' => 'Stellantis',
                    'segments' => ['hatch', 'suv', 'van'],
                ],
            ],

            [
                'name' => 'Mitsubishi',
                'slug' => 'mitsubishi',
                'country_origin' => 'JP',
                'type' => 'car',
                'logo_url' => '/images/brands/mitsubishi/logo.png',
                'website' => 'https://www.mitsubishimotors.com.br',
                'is_active' => true,
                'market_share_br' => 1.5,
                'ranking_br' => 12,
                'metadata' => [
                    'founded' => 1970,
                    'headquarters' => 'Tóquio, Japão',
                    'brazil_since' => 1998,
                    'factories_br' => ['Catalão-GO'],
                    'group' => 'Renault-Nissan-Mitsubishi Alliance',
                    'segments' => ['suv', 'pickup'],
                ],
            ],

            [
                'name' => 'Ford',
                'slug' => 'ford',
                'country_origin' => 'US',
                'type' => 'car',
                'logo_url' => '/images/brands/ford/logo.png',
                'website' => 'https://www.ford.com.br',
                'is_active' => true,
                'market_share_br' => 1.2,
                'ranking_br' => 13,
                'metadata' => [
                    'founded' => 1903,
                    'headquarters' => 'Dearborn, EUA',
                    'brazil_since' => 1919,
                    'factories_br' => [], // Encerrou produção em 2021
                    'group' => 'Ford Motor Company',
                    'segments' => ['suv', 'pickup'],
                    'note' => 'Apenas importados após 2021',
                ],
            ],

            [
                'name' => 'Kia',
                'slug' => 'kia',
                'country_origin' => 'KR',
                'type' => 'car',
                'logo_url' => '/images/brands/kia/logo.png',
                'website' => 'https://www.kia.com.br',
                'is_active' => true,
                'market_share_br' => 1.0,
                'ranking_br' => 14,
                'metadata' => [
                    'founded' => 1944,
                    'headquarters' => 'Seul, Coreia do Sul',
                    'brazil_since' => 1992,
                    'factories_br' => [],
                    'group' => 'Hyundai Motor Group',
                    'segments' => ['sedan', 'suv', 'hatch'],
                ],
            ],

            [
                'name' => 'Caoa Chery',
                'slug' => 'caoa-chery',
                'country_origin' => 'CN',
                'type' => 'car',
                'logo_url' => '/images/brands/caoa-chery/logo.png',
                'website' => 'https://www.caoa.com.br',
                'is_active' => true,
                'market_share_br' => 0.9,
                'ranking_br' => 15,
                'metadata' => [
                    'founded' => 1997,
                    'headquarters' => 'Wuhu, China',
                    'brazil_since' => 2014,
                    'factories_br' => ['Anápolis-GO', 'Jacareí-SP'],
                    'group' => 'CAOA Group / Chery',
                    'segments' => ['hatch', 'sedan', 'suv'],
                ],
            ],

            [
                'name' => 'RAM',
                'slug' => 'ram',
                'country_origin' => 'US',
                'type' => 'car',
                'logo_url' => '/images/brands/ram/logo.png',
                'website' => 'https://www.ramtruck.com.br',
                'is_active' => true,
                'market_share_br' => 0.8,
                'ranking_br' => 16,
                'metadata' => [
                    'founded' => 2010,
                    'headquarters' => 'Auburn Hills, EUA',
                    'brazil_since' => 2015,
                    'factories_br' => ['Goiana-PE'],
                    'group' => 'Stellantis',
                    'segments' => ['pickup'],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MARCAS PREMIUM / LUXO
            // ═══════════════════════════════════════════════════════════════

            [
                'name' => 'BMW',
                'slug' => 'bmw',
                'country_origin' => 'DE',
                'type' => 'car',
                'logo_url' => '/images/brands/bmw/logo.png',
                'website' => 'https://www.bmw.com.br',
                'is_active' => true,
                'market_share_br' => 0.6,
                'ranking_br' => 17,
                'metadata' => [
                    'founded' => 1916,
                    'headquarters' => 'Munique, Alemanha',
                    'brazil_since' => 2014,
                    'factories_br' => ['Araquari-SC'],
                    'group' => 'BMW Group',
                    'segments' => ['sedan', 'suv', 'coupe', 'conversivel'],
                    'category' => 'premium',
                ],
            ],

            [
                'name' => 'Mercedes-Benz',
                'slug' => 'mercedes-benz',
                'country_origin' => 'DE',
                'type' => 'car',
                'logo_url' => '/images/brands/mercedes-benz/logo.png',
                'website' => 'https://www.mercedes-benz.com.br',
                'is_active' => true,
                'market_share_br' => 0.5,
                'ranking_br' => 18,
                'metadata' => [
                    'founded' => 1926,
                    'headquarters' => 'Stuttgart, Alemanha',
                    'brazil_since' => 1956,
                    'factories_br' => ['Iracemápolis-SP', 'São Bernardo do Campo-SP'],
                    'group' => 'Mercedes-Benz Group',
                    'segments' => ['sedan', 'suv', 'coupe', 'van', 'caminhao'],
                    'category' => 'premium',
                ],
            ],

            [
                'name' => 'Audi',
                'slug' => 'audi',
                'country_origin' => 'DE',
                'type' => 'car',
                'logo_url' => '/images/brands/audi/logo.png',
                'website' => 'https://www.audi.com.br',
                'is_active' => true,
                'market_share_br' => 0.4,
                'ranking_br' => 19,
                'metadata' => [
                    'founded' => 1909,
                    'headquarters' => 'Ingolstadt, Alemanha',
                    'brazil_since' => 1994,
                    'factories_br' => ['São José dos Pinhais-PR'],
                    'group' => 'Volkswagen AG',
                    'segments' => ['sedan', 'suv', 'hatch', 'coupe'],
                    'category' => 'premium',
                ],
            ],

            [
                'name' => 'Volvo',
                'slug' => 'volvo',
                'country_origin' => 'SE',
                'type' => 'car',
                'logo_url' => '/images/brands/volvo/logo.png',
                'website' => 'https://www.volvocars.com.br',
                'is_active' => true,
                'market_share_br' => 0.3,
                'ranking_br' => 20,
                'metadata' => [
                    'founded' => 1927,
                    'headquarters' => 'Gotemburgo, Suécia',
                    'brazil_since' => 1979,
                    'factories_br' => [],
                    'group' => 'Geely Holding',
                    'segments' => ['sedan', 'suv', 'wagon'],
                    'category' => 'premium',
                ],
            ],

            [
                'name' => 'Land Rover',
                'slug' => 'land-rover',
                'country_origin' => 'GB',
                'type' => 'car',
                'logo_url' => '/images/brands/land-rover/logo.png',
                'website' => 'https://www.landrover.com.br',
                'is_active' => true,
                'market_share_br' => 0.25,
                'ranking_br' => 21,
                'metadata' => [
                    'founded' => 1948,
                    'headquarters' => 'Whitley, Inglaterra',
                    'brazil_since' => 2016,
                    'factories_br' => ['Itatiaia-RJ'],
                    'group' => 'Jaguar Land Rover / Tata Motors',
                    'segments' => ['suv'],
                    'category' => 'premium',
                ],
            ],

            [
                'name' => 'Jaguar',
                'slug' => 'jaguar',
                'country_origin' => 'GB',
                'type' => 'car',
                'logo_url' => '/images/brands/jaguar/logo.png',
                'website' => 'https://www.jaguar.com.br',
                'is_active' => true,
                'market_share_br' => 0.1,
                'ranking_br' => 22,
                'metadata' => [
                    'founded' => 1922,
                    'headquarters' => 'Whitley, Inglaterra',
                    'brazil_since' => 2016,
                    'factories_br' => ['Itatiaia-RJ'],
                    'group' => 'Jaguar Land Rover / Tata Motors',
                    'segments' => ['sedan', 'suv', 'coupe'],
                    'category' => 'premium',
                ],
            ],

            [
                'name' => 'Porsche',
                'slug' => 'porsche',
                'country_origin' => 'DE',
                'type' => 'car',
                'logo_url' => '/images/brands/porsche/logo.png',
                'website' => 'https://www.porsche.com.br',
                'is_active' => true,
                'market_share_br' => 0.08,
                'ranking_br' => 23,
                'metadata' => [
                    'founded' => 1931,
                    'headquarters' => 'Stuttgart, Alemanha',
                    'brazil_since' => 1999,
                    'factories_br' => [],
                    'group' => 'Volkswagen AG',
                    'segments' => ['coupe', 'suv', 'sedan', 'conversivel'],
                    'category' => 'luxury',
                ],
            ],

            [
                'name' => 'Lexus',
                'slug' => 'lexus',
                'country_origin' => 'JP',
                'type' => 'car',
                'logo_url' => '/images/brands/lexus/logo.png',
                'website' => 'https://www.lexus.com.br',
                'is_active' => true,
                'market_share_br' => 0.05,
                'ranking_br' => 24,
                'metadata' => [
                    'founded' => 1989,
                    'headquarters' => 'Nagoya, Japão',
                    'brazil_since' => 2013,
                    'factories_br' => [],
                    'group' => 'Toyota Motor Corporation',
                    'segments' => ['sedan', 'suv', 'coupe'],
                    'category' => 'premium',
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MARCAS CHINESAS EM CRESCIMENTO
            // ═══════════════════════════════════════════════════════════════

            [
                'name' => 'BYD',
                'slug' => 'byd',
                'country_origin' => 'CN',
                'type' => 'car',
                'logo_url' => '/images/brands/byd/logo.png',
                'website' => 'https://www.byd.com.br',
                'is_active' => true,
                'market_share_br' => 0.7,
                'ranking_br' => 25,
                'metadata' => [
                    'founded' => 1995,
                    'headquarters' => 'Shenzhen, China',
                    'brazil_since' => 2015,
                    'factories_br' => ['Camaçari-BA'],
                    'group' => 'BYD Company',
                    'segments' => ['hatch', 'sedan', 'suv'],
                    'specialization' => 'electric_hybrid',
                ],
            ],

            [
                'name' => 'GWM',
                'slug' => 'gwm',
                'country_origin' => 'CN',
                'type' => 'car',
                'logo_url' => '/images/brands/gwm/logo.png',
                'website' => 'https://www.gwm.com.br',
                'is_active' => true,
                'market_share_br' => 0.4,
                'ranking_br' => 26,
                'metadata' => [
                    'founded' => 1984,
                    'headquarters' => 'Baoding, China',
                    'brazil_since' => 2022,
                    'factories_br' => ['Iracemápolis-SP'],
                    'group' => 'Great Wall Motor',
                    'segments' => ['suv', 'pickup'],
                    'specialization' => 'electric_hybrid',
                ],
            ],

            [
                'name' => 'JAC',
                'slug' => 'jac',
                'country_origin' => 'CN',
                'type' => 'car',
                'logo_url' => '/images/brands/jac/logo.png',
                'website' => 'https://www.jacmotors.com.br',
                'is_active' => true,
                'market_share_br' => 0.2,
                'ranking_br' => 27,
                'metadata' => [
                    'founded' => 1964,
                    'headquarters' => 'Hefei, China',
                    'brazil_since' => 2011,
                    'factories_br' => ['Camaçari-BA'],
                    'group' => 'JAC Motors',
                    'segments' => ['hatch', 'sedan', 'suv', 'pickup'],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MOTOCICLETAS - PRINCIPAIS MARCAS
            // ═══════════════════════════════════════════════════════════════

            [
                'name' => 'Honda Motos',
                'slug' => 'honda-motos',
                'country_origin' => 'JP',
                'type' => 'motorcycle',
                'logo_url' => '/images/brands/honda-motos/logo.png',
                'website' => 'https://www.honda.com.br/motos',
                'is_active' => true,
                'market_share_br' => 78.5,
                'ranking_br' => 1,
                'metadata' => [
                    'founded' => 1948,
                    'headquarters' => 'Tóquio, Japão',
                    'brazil_since' => 1971,
                    'factories_br' => ['Manaus-AM'],
                    'group' => 'Honda Motor Co.',
                    'segments' => ['street', 'sport', 'trail', 'scooter', 'custom'],
                ],
            ],

            [
                'name' => 'Yamaha',
                'slug' => 'yamaha',
                'country_origin' => 'JP',
                'type' => 'motorcycle',
                'logo_url' => '/images/brands/yamaha/logo.png',
                'website' => 'https://www.yamaha-motor.com.br',
                'is_active' => true,
                'market_share_br' => 12.3,
                'ranking_br' => 2,
                'metadata' => [
                    'founded' => 1955,
                    'headquarters' => 'Iwata, Japão',
                    'brazil_since' => 1974,
                    'factories_br' => ['Manaus-AM'],
                    'group' => 'Yamaha Motor Co.',
                    'segments' => ['street', 'sport', 'trail', 'scooter', 'custom'],
                ],
            ],

            [
                'name' => 'Suzuki',
                'slug' => 'suzuki',
                'country_origin' => 'JP',
                'type' => 'motorcycle',
                'logo_url' => '/images/brands/suzuki/logo.png',
                'website' => 'https://www.suzukimotos.com.br',
                'is_active' => true,
                'market_share_br' => 3.2,
                'ranking_br' => 3,
                'metadata' => [
                    'founded' => 1909,
                    'headquarters' => 'Hamamatsu, Japão',
                    'brazil_since' => 2002,
                    'factories_br' => [],
                    'group' => 'Suzuki Motor Corporation',
                    'segments' => ['street', 'sport', 'trail', 'scooter'],
                ],
            ],

            [
                'name' => 'BMW Motorrad',
                'slug' => 'bmw-motorrad',
                'country_origin' => 'DE',
                'type' => 'motorcycle',
                'logo_url' => '/images/brands/bmw-motorrad/logo.png',
                'website' => 'https://www.bmw-motorrad.com.br',
                'is_active' => true,
                'market_share_br' => 1.5,
                'ranking_br' => 4,
                'metadata' => [
                    'founded' => 1923,
                    'headquarters' => 'Munique, Alemanha',
                    'brazil_since' => 2016,
                    'factories_br' => ['Manaus-AM'],
                    'group' => 'BMW Group',
                    'segments' => ['touring', 'sport', 'adventure', 'roadster'],
                    'category' => 'premium',
                ],
            ],

            [
                'name' => 'Harley-Davidson',
                'slug' => 'harley-davidson',
                'country_origin' => 'US',
                'type' => 'motorcycle',
                'logo_url' => '/images/brands/harley-davidson/logo.png',
                'website' => 'https://www.harley-davidson.com.br',
                'is_active' => true,
                'market_share_br' => 0.8,
                'ranking_br' => 5,
                'metadata' => [
                    'founded' => 1903,
                    'headquarters' => 'Milwaukee, EUA',
                    'brazil_since' => 1999,
                    'factories_br' => ['Manaus-AM'],
                    'group' => 'Harley-Davidson Inc.',
                    'segments' => ['cruiser', 'touring', 'sportster'],
                    'category' => 'premium',
                ],
            ],

            [
                'name' => 'Kawasaki',
                'slug' => 'kawasaki',
                'country_origin' => 'JP',
                'type' => 'motorcycle',
                'logo_url' => '/images/brands/kawasaki/logo.png',
                'website' => 'https://www.kawasaki.com.br',
                'is_active' => true,
                'market_share_br' => 0.6,
                'ranking_br' => 6,
                'metadata' => [
                    'founded' => 1896,
                    'headquarters' => 'Kobe, Japão',
                    'brazil_since' => 2012,
                    'factories_br' => [],
                    'group' => 'Kawasaki Heavy Industries',
                    'segments' => ['sport', 'street', 'adventure', 'off-road'],
                ],
            ],

            [
                'name' => 'Triumph',
                'slug' => 'triumph',
                'country_origin' => 'GB',
                'type' => 'motorcycle',
                'logo_url' => '/images/brands/triumph/logo.png',
                'website' => 'https://www.triumph.com.br',
                'is_active' => true,
                'market_share_br' => 0.4,
                'ranking_br' => 7,
                'metadata' => [
                    'founded' => 1902,
                    'headquarters' => 'Hinckley, Inglaterra',
                    'brazil_since' => 2006,
                    'factories_br' => ['Manaus-AM'],
                    'group' => 'Triumph Motorcycles Ltd.',
                    'segments' => ['classic', 'roadster', 'adventure', 'sport'],
                    'category' => 'premium',
                ],
            ],

            [
                'name' => 'Ducati',
                'slug' => 'ducati',
                'country_origin' => 'IT',
                'type' => 'motorcycle',
                'logo_url' => '/images/brands/ducati/logo.png',
                'website' => 'https://www.ducati.com.br',
                'is_active' => true,
                'market_share_br' => 0.3,
                'ranking_br' => 8,
                'metadata' => [
                    'founded' => 1926,
                    'headquarters' => 'Bolonha, Itália',
                    'brazil_since' => 2012,
                    'factories_br' => [],
                    'group' => 'Volkswagen AG (Audi)',
                    'segments' => ['sport', 'naked', 'touring', 'scrambler'],
                    'category' => 'premium',
                ],
            ],
        ];
    }
}
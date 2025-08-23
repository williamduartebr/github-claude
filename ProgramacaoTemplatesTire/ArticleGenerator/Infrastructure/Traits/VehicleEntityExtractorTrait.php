<?php

namespace Src\ArticleGenerator\Infrastructure\Traits;

trait VehicleEntityExtractorTrait
{
    /**
     * Extrai entidades do título quando a API não fornece - versão melhorada
     */
    protected function extractEntitiesFromTitle($title)
    {
        // Implementação básica para extrair informações do título
        $entities = [
            'marca' => '',
            'modelo' => '',
            'ano' => '',
            'motorizacao' => '',
            'versao' => '',
            'tipo_veiculo' => 'carro', // Valor padrão mais genérico
            'categoria' => '',
            'combustivel' => ''
        ];

        // Extrair marca
        $marcas = [
            'volkswagen',
            'vw',
            'ford',
            'chevrolet',
            'gm',
            'fiat',
            'honda',
            'toyota',
            'hyundai',
            'renault',
            'nissan',
            'kia',
            'bmw',
            'mercedes',
            'audi',
            'citroen',
            'peugeot',
            'mitsubishi',
            'suzuki',
            'subaru',
            'jeep',
            'land rover',
            'yamaha',
            'kawasaki',
            'ducati',
            'harley',
            'triumph',
            'piaggio',
            'aprilia',
            'mv agusta',
            'benelli',
            'shineray',
            'traxx',
            'dafra',
            'scania',
            'volvo',
            'mercedes-benz',
            'iveco',
            'man',
            'ford cargo',
            'volkswagen constellation'
        ];

        foreach ($marcas as $marca) {
            if (stripos($title, $marca) !== false) {
                $entities['marca'] = $marca;
                break;
            }
        }

        // Extrair modelo
        $modelos = [
            // Carros populares
            'gol',
            'palio',
            'uno',
            'ka',
            'celta',
            'corsa',
            'fiesta',
            'civic',
            'corolla',
            'fit',
            'hb20',
            'clio',
            'sandero',
            'kwid',
            'stepway',
            'fox',
            'polo',
            'voyage',
            'onix',
            'cruze',
            'renegade',
            'compass',
            'tiguan',
            'golf',
            'jetta',
            'passat',
            'amarok',
            'hilux',
            'ranger',
            'frontier',
            's10',
            'ecosport',
            'focus',
            'fusion',
            'edge',
            'Territory',
            // Motos
            'cb',
            'cbr',
            'pop',
            'biz',
            'cg',
            'fan',
            'titan',
            'twister',
            'hornet',
            'africa twin',
            'goldwing',
            'fazer',
            'factor',
            'crosser',
            'lander',
            'tenere',
            'mt',
            'yzf',
            'r1',
            'r6',
            'ninja',
            'z',
            'versys',
            'zx',
            'er',
            'klx',
            'panigale',
            'monster',
            'multistrada',
            'diavel',
            'scrambler',
            'street',
            'sportster',
            'softail',
            'touring',
            'trike',
            // Caminhões
            'atego',
            'accelo',
            'axor',
            'actros',
            'arocs',
            'antos',
            'econic',
            'unimog',
            'fh',
            'fm',
            'fe',
            'constellation',
            'delivery',
            'cargo',
            'worker',
            'tector',
            'cursor',
            'stralis',
            'daily'
        ];

        foreach ($modelos as $modelo) {
            if (stripos($title, $modelo) !== false) {
                $entities['modelo'] = $modelo;
                break;
            }
        }

        // Extrair ano
        if (preg_match('/\b(19|20)\d{2}\b/', $title, $matches)) {
            $entities['ano'] = $matches[0];
        }

        // Extrair motorização
        if (preg_match('/\b(\d\.\d)\b/', $title, $matches)) {
            $entities['motorizacao'] = $matches[0];
        } elseif (preg_match('/\b(\d+\.\d+)\s*(16v|8v|v6|v8)\b/i', $title, $matches)) {
            $entities['motorizacao'] = $matches[0];
        } elseif (preg_match('/\b(\d+\.\d+)\b/', $title, $matches)) {
            $entities['motorizacao'] = $matches[0];
        } elseif (preg_match('/\b(\d+)\s*(16v|8v|v6|v8)\b/i', $title, $matches)) {
            $entities['motorizacao'] = $matches[0];
        } elseif (preg_match('/\b(\d+cc)\b/i', $title, $matches)) {
            // Para motos
            $entities['motorizacao'] = $matches[0];
        }

        // Extrair combustível
        $combustiveis = ['flex', 'gasolina', 'diesel', 'álcool', 'alcool', 'etanol', 'gnv', 'híbrido', 'hibrido', 'elétrico', 'eletrico'];

        foreach ($combustiveis as $combustivel) {
            if (stripos($title, $combustivel) !== false) {
                $entities['combustivel'] = $combustivel;
                break;
            }
        }

        // Detectar tipo de veículo básico por palavras-chave
        $tiposVeiculo = [
            'moto' => [
                'moto', 'motocicleta', 'scooter', 'cb', 'cbr', 'pop', 'biz', 'cg', 'fan', 'titan', 
                'twister', 'hornet', 'africa twin', 'goldwing', 'fazer', 'factor', 'crosser', 
                'lander', 'tenere', 'mt', 'yzf', 'r1', 'r6', 'ninja', 'z', 'versys', 'zx', 
                'er', 'klx', 'panigale', 'monster', 'multistrada', 'diavel', 'scrambler', 
                'street', 'sportster', 'softail', 'touring', 'trike', 'yamaha', 'kawasaki', 
                'ducati', 'harley', 'triumph', 'aprilia', 'mv agusta', 'benelli', 'shineray', 
                'traxx', 'dafra', 'piaggio', 'vespa'
            ],
            'caminhão' => [
                'caminhão', 'caminhao', 'truck', 'mb', 'volvo', 'scania', 'iveco', 'man', 
                'atego', 'accelo', 'axor', 'actros', 'arocs', 'antos', 'econic', 'unimog', 
                'fh', 'fm', 'fe', 'constellation', 'delivery', 'cargo', 'worker', 'tector', 
                'cursor', 'stralis', 'daily'
            ],
            'SUV' => [
                'suv', 'crossover', 'compass', 'renegade', 'tucson', 'hr-v', 'ecosport', 
                'tiguan', 'touareg', 'q3', 'q5', 'q7', 'x1', 'x3', 'x5', 'glc', 'gle', 
                'territory', 'edge', 'kuga', 'evoque', 'discovery', 'defender'
            ],
            'picape' => [
                'picape', 'pickup', 'hilux', 'ranger', 'amarok', 'frontier', 's10', 
                'toro', 'oroch', 'alaskan', 'navara', 'l200', 'strada'
            ],
            'utilitário' => [
                'utilitario', 'utilitário', 'van', 'furgão', 'furgao', 'ducato', 'sprinter', 
                'master', 'boxer', 'jumper', 'daily', 'transit', 'crafter', 'partner', 
                'berlingo', 'doblo', 'combo'
            ],
            'trator' => [
                'trator', 'john deere', 'case', 'new holland', 'massey ferguson', 
                'valtra', 'agrale', 'landini', 'same', 'deutz', 'fendt'
            ],
            'ônibus' => [
                'ônibus', 'onibus', 'micro-ônibus', 'micro onibus', 'rodoviário', 
                'rodoviario', 'urbano', 'escolar', 'turismo', 'o500', 'of', 'oh'
            ]
        ];

        foreach ($tiposVeiculo as $tipo => $palavrasChave) {
            foreach ($palavrasChave as $palavra) {
                if (stripos($title, $palavra) !== false) {
                    $entities['tipo_veiculo'] = $tipo;
                    break 2;
                }
            }
        }

        // Detectar versão específica
        $versoes = [
            'lx', 'ex', 'sport', 'comfort', 'luxury', 'premium', 'executive', 
            'highline', 'comfortline', 'trendline', 'cross', 'adventure', 
            'limited', 'laredo', 'overland', 'summit', 'trailhawk', 'longitude', 
            'diesel', 'turbo', 'gti', 'gts', 'rs', 'st', 'type r', 'si', 
            'touring', 'elite', 'prestige', 'quattro', 'xdrive', '4matic'
        ];

        foreach ($versoes as $versao) {
            if (stripos($title, $versao) !== false) {
                $entities['versao'] = $versao;
                break;
            }
        }

        return $entities;
    }

    /**
     * Detecta tipo de veículo básico do título - método auxiliar
     */
    protected function detectVehicleTypeFromTitle($title)
    {
        $tiposVeiculo = [
            'moto' => ['moto', 'motocicleta', 'scooter', 'cb', 'pop', 'fan', 'titan'],
            'caminhão' => ['caminhão', 'caminhao', 'truck'],
            'SUV' => ['suv', 'crossover'],
            'picape' => ['picape', 'pickup'],
            'utilitário' => ['utilitario', 'utilitário', 'van', 'furgão'],
            'trator' => ['trator'],
            'ônibus' => ['ônibus', 'onibus']
        ];

        foreach ($tiposVeiculo as $tipo => $palavrasChave) {
            foreach ($palavrasChave as $palavra) {
                if (stripos($title, $palavra) !== false) {
                    return $tipo;
                }
            }
        }

        return 'carro'; // Default
    }
}
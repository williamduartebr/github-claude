<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\ClaudeHaikuService;

/**
 * Service APRIMORADO para correção do vehicle_data
 * 
 * MELHORIAS:
 * - Validação inteligente por categoria de veículo
 * - Faixas de pressão específicas para cada tipo
 * - Validação de segmento mais robusta
 * - Tratamento especial para veículos comerciais
 */
class VehicleDataCorrectionService
{
    protected ClaudeHaikuService $claudeService;

    /**
     * Faixas de pressão por categoria de veículo
     */
    protected array $pressureRanges = [
        'hatch' => ['min' => 20, 'max' => 45, 'spare_min' => 28, 'spare_max' => 65],
        'sedan' => ['min' => 22, 'max' => 45, 'spare_min' => 30, 'spare_max' => 65],
        'suv' => ['min' => 25, 'max' => 50, 'spare_min' => 32, 'spare_max' => 70],
        'pickup' => ['min' => 30, 'max' => 80, 'spare_min' => 35, 'spare_max' => 90],
        'van' => ['min' => 35, 'max' => 70, 'spare_min' => 40, 'spare_max' => 80],
        'truck' => ['min' => 40, 'max' => 100, 'spare_min' => 45, 'spare_max' => 110],
        'commercial' => ['min' => 35, 'max' => 85, 'spare_min' => 40, 'spare_max' => 95],
        'motorcycle' => ['min' => 25, 'max' => 45, 'spare_min' => 30, 'spare_max' => 55],
        'electric' => ['min' => 20, 'max' => 55, 'spare_min' => 25, 'spare_max' => 70],
        'default' => ['min' => 20, 'max' => 60, 'spare_min' => 25, 'spare_max' => 80]
    ];

    public function __construct(ClaudeHaikuService $claudeService)
    {
        $this->claudeService = $claudeService;
    }

    /**
     * Corrigir vehicle_data usando Claude
     */
    public function correctVehicleData(array $currentVehicleData): array
    {
        Log::info('VehicleDataCorrectionService: Iniciando correção', [
            'vehicle' => "{$currentVehicleData['make']} {$currentVehicleData['model']} {$currentVehicleData['year']}"
        ]);

        try {
            // 1. Construir prompt para Claude
            $prompt = $this->buildCorrectionPrompt($currentVehicleData);

            // 2. Chamar Claude 3 Haiku
            $response = $this->claudeService->generateContent($prompt, [
                'max_tokens' => 2000,
                'temperature' => 0.1,
                'timeout' => 30
            ]);

            // 3. Parsear resposta JSON
            $correctedData = $this->parseClaudeResponse($response, $currentVehicleData);

            // 4. Validar dados corrigidos com validação inteligente
            $this->validateWithLogging($correctedData, $correctedData['vehicle_full_name'] ?? 'unknown');

            Log::info('VehicleDataCorrectionService: Correção concluída', [
                'vehicle' => $correctedData['vehicle_full_name'],
                'segment' => $correctedData['vehicle_segment'],
                'category' => $correctedData['main_category'] ?? 'unknown',
                'front_pressure' => $correctedData['pressure_light_front'],
                'rear_pressure' => $correctedData['pressure_light_rear']
            ]);

            return $correctedData;
        } catch (\Exception $e) {
            Log::error('VehicleDataCorrectionService: Erro na correção', [
                'vehicle' => "{$currentVehicleData['make']} {$currentVehicleData['model']} {$currentVehicleData['year']}",
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Construir prompt melhorado para Claude
     */
    protected function buildCorrectionPrompt(array $vehicleData): string
    {
        return "Você é um especialista em pressão de pneus automotivos. Preciso que corrija os dados técnicos do veículo abaixo.

VEÍCULO: {$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}
PNEU: {$vehicleData['tire_size']}

DADOS ATUAIS (GERADOS POR SCRIPT - INCORRETOS):
```json
" . json_encode($vehicleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "
```

INSTRUÇÕES ESPECÍFICAS:

1. **IDENTIFIQUE O TIPO DE VEÍCULO PRIMEIRO:**
   - Motocicleta/Moto: 25-42 PSI (Triumph, Honda, Yamaha, Kawasaki, etc.)
   - Hatchback/Compacto: 20-40 PSI
   - Sedan: 22-42 PSI  
   - SUV: 28-48 PSI
   - Pickup/Caminhonete: 35-80 PSI (IMPORTANTE: Pickups usam pressões altas!)
   - Van/Comercial: 35-70 PSI

   ATENÇÃO: 
   - Triumph Tiger 900 = MOTOCICLETA (não SUV!)
   - Super Soco TC Max = MOTOCICLETA ELÉTRICA (não hatch!)
   - Mercedes-Benz EQA/EQB/EQC = CARROS ELÉTRICOS sem estepe
   - GWM ORA 03 = CARRO ELÉTRICO CHINÊS sem estepe
   - Voltz EV1 = ELÉTRICO sem estepe
   - BYD/Tesla/Audi e-tron = Geralmente SEM estepe
   - car_electric = categoria para veículos 100% elétricos

2. **PRESSÕES REAIS** (SEMPRE números inteiros, nunca texto):
   - pressure_empty_front: Pressão dianteira vazio (número PSI, ex: 32)
   - pressure_empty_rear: Pressão traseira vazio (número PSI, ex: 30)
   - pressure_light_front: Pressão dianteira carga leve (número PSI, ex: 32)
   - pressure_light_rear: Pressão traseira carga leve (número PSI, ex: 30)
   - pressure_max_front: Pressão dianteira carga máxima (número PSI, ex: 36)
   - pressure_max_rear: Pressão traseira carga máxima (número PSI, ex: 34)
   - pressure_spare: Pressão pneu estepe (número PSI, ex: 60) - MAIOR que pneus normais

   IMPORTANTE: Sempre usar NÚMEROS, nunca texto como \"32 PSI\" ou \"thirty-two\"

3. **SEGMENTO CORRETO:**
   - A: Micro (Fiat 500, Smart)
   - B: Compacto (Onix, HB20, Gol)
   - C: Médio (Civic, Corolla, Cruze)
   - D: Grande/SUV (Hilux, SW4, Amarok)
   - F: Pickup/Comercial (RAM, F-150, Silverado)

4. **CATEGORIAS:**
   - hatch, sedan, suv, pickup, van, motorcycle

RESPONDA APENAS COM JSON VÁLIDO (todos os campos originais + correções):

```json
{";
    }

    /**
     * Parsear resposta do Claude (mantido igual)
     */
    protected function parseClaudeResponse(string $response, array $originalData): array
    {
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}') + 1;

        if ($jsonStart === false || $jsonEnd === false) {
            throw new \Exception('Resposta do Claude não contém JSON válido');
        }

        $jsonContent = substr($response, $jsonStart, $jsonEnd - $jsonStart);
        $correctedData = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Erro ao decodificar JSON do Claude: ' . json_last_error_msg());
        }

        return array_merge($originalData, $correctedData);
    }

    /**
     * ✅ NOVA: Validação inteligente baseada no tipo de veículo
     */
    protected function validateCorrectedDataIntelligent(array $data): void
    {
        // ✅ LIMPAR E CONVERTER dados antes da validação
        $data = $this->sanitizeAndConvertPressureData($data);
        
        // Campos sempre obrigatórios
        $requiredFields = [
            'pressure_empty_front',
            'pressure_empty_rear',
            'pressure_light_front',
            'pressure_light_rear',
            'pressure_max_front',
            'pressure_max_rear',
            'vehicle_segment'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                throw new \Exception("Campo obrigatório ausente ou inválido: {$field}");
            }
        }

        // ✅ VALIDAÇÃO CONDICIONAL: pressure_spare
        $this->validatePressureSpareConditional($data);

        // ✅ VALIDAÇÃO INTELIGENTE: Determinar categoria do veículo
        $vehicleCategory = $this->determineVehicleCategory($data);
        $pressureRange = $this->pressureRanges[$vehicleCategory] ?? $this->pressureRanges['default'];

        Log::info('VehicleDataCorrectionService: Validando pressões', [
            'vehicle' => "{$data['make']} {$data['model']} {$data['year']}",
            'category' => $vehicleCategory,
            'pressure_range' => [
                'normal' => ['min' => $pressureRange['min'], 'max' => $pressureRange['max']],
                'spare' => ['min' => $pressureRange['spare_min'], 'max' => $pressureRange['spare_max']]
            ],
            'front_pressure' => $data['pressure_light_front'],
            'rear_pressure' => $data['pressure_light_rear'],
            'spare_pressure' => $data['pressure_spare'] ?? 'not_set'
        ]);

        // Validar pressão dianteira
        if ($data['pressure_light_front'] < $pressureRange['min'] || $data['pressure_light_front'] > $pressureRange['max']) {
            throw new \Exception("Pressão dianteira fora da faixa válida para {$vehicleCategory}: {$data['pressure_light_front']} PSI (válido: {$pressureRange['min']}-{$pressureRange['max']} PSI)");
        }

        // Validar pressão traseira
        if ($data['pressure_light_rear'] < $pressureRange['min'] || $data['pressure_light_rear'] > $pressureRange['max']) {
            throw new \Exception("Pressão traseira fora da faixa válida para {$vehicleCategory}: {$data['pressure_light_rear']} PSI (válido: {$pressureRange['min']}-{$pressureRange['max']} PSI)");
        }

        // Validar segmento
        $validSegments = ['A', 'B', 'C', 'D', 'E', 'F', 'SUV', 'PICKUP', 'VAN', 'MOTO'];
        if (!in_array($data['vehicle_segment'], $validSegments)) {
            throw new \Exception("Segmento inválido: {$data['vehicle_segment']}");
        }

        Log::info('VehicleDataCorrectionService: Validação passou', [
            'vehicle' => "{$data['make']} {$data['model']} {$data['year']}",
            'category' => $vehicleCategory,
            'pressures_valid' => 'yes',
            'has_spare' => isset($data['pressure_spare']) ? 'yes' : 'no'
        ]);
    }

    /**
     * ✅ NOVO: Validar pressure_spare condicionalmente
     */
    protected function validatePressureSpareConditional(array $data): void
    {
        $vehicleCategory = $this->determineVehicleCategory($data);
        $make = strtolower($data['make'] ?? '');
        $model = strtolower($data['model'] ?? '');

        // Verificar se deveria ter pneu sobressalente
        $shouldHaveSpare = $this->shouldHaveSpareTime($vehicleCategory, $make, $model);

        if ($shouldHaveSpare) {
            // Exigir pressure_spare
            if (!isset($data['pressure_spare']) || empty($data['pressure_spare'])) {
                throw new \Exception("Campo pressure_spare obrigatório para {$vehicleCategory}: {$make} {$model}");
            }

            // Validar faixa se presente
            $pressureRange = $this->pressureRanges[$vehicleCategory] ?? $this->pressureRanges['default'];
            
            // ✅ USAR FAIXA ESPECÍFICA PARA ESTEPE
            $spareMin = $pressureRange['spare_min'];
            $spareMax = $pressureRange['spare_max'];
            
            if ($data['pressure_spare'] < $spareMin || $data['pressure_spare'] > $spareMax) {
                throw new \Exception("Pressão do estepe fora da faixa válida para {$vehicleCategory}: {$data['pressure_spare']} PSI (válido: {$spareMin}-{$spareMax} PSI)");
            }
        } else {
            // Opcional para motocicletas e alguns carros modernos
            if (!isset($data['pressure_spare']) || empty($data['pressure_spare'])) {
                // Usar valor padrão baseado na categoria
                $data['pressure_spare'] = $this->getDefaultSparePresure($vehicleCategory, $data);
                
                Log::info('VehicleDataCorrectionService: pressure_spare definido como padrão', [
                    'vehicle' => "{$data['make']} {$data['model']} {$data['year']}",
                    'category' => $vehicleCategory,
                    'default_spare_pressure' => $data['pressure_spare'],
                    'reason' => $this->getReasonForNoSpare($vehicleCategory, strtolower($data['make'] ?? ''), strtolower($data['model'] ?? ''))
                ]);
            }
        }
    }

    /**
     * ✅ NOVO: Verificar se veículo deveria ter pneu sobressalente
     */
    protected function shouldHaveSpareTime(string $category, string $make, string $model): bool
    {
        // Motocicletas raramente têm estepe
        if ($category === 'motorcycle') {
            return false;
        }

        // ✅ ELÉTRICOS raramente têm estepe
        if ($category === 'electric') {
            return false;
        }

        // ✅ MELHORADO: Detecção de veículos elétricos/híbridos
        $electricModels = [
            // Carros elétricos brasileiros
            'voltz', 'ev1', 'leaf', 'bolt', 'volt', 'i3', 'i8', 'model',
            'tesla', 'prius', 'corolla cross hybrid', 'rav4 hybrid',
            
            // ✅ ADICIONADO: Mercedes EQ linha completa
            'eqa', 'eqb', 'eqc', 'eqs', 'eqe', 'eqg', 'eqv',
            
            // ✅ ADICIONADO: Carros elétricos chineses
            'ora', 'ora 03', 'byd', 'dolphin', 'atto 3', 'han', 'tang',
            'seal', 'yuan', 'song', 'qin', 'e6', 'e5', 'f3dm',
            'gwm', 'great wall', 'haval', 'wey', 'tank',
            
            // ✅ ADICIONADO: Audi elétricos
            'e-tron', 'e-tron gt', 'q8 e-tron', 'q4 e-tron',
            
            // Elétricos internacionais  
            'ioniq', 'kona electric', 'niro ev', 'soul ev', 'e-golf',
            'id.3', 'id.4', 'taycan', 'mach-e', 'lightning',
            
            // Híbridos que frequentemente não têm estepe
            'prius', 'camry hybrid', 'accord hybrid', 'fusion hybrid',
            'escape hybrid', 'highlander hybrid', 'sienna hybrid'
        ];

        $modelLower = strtolower($model);
        foreach ($electricModels as $electricModel) {
            if (str_contains($modelLower, $electricModel)) {
                return false;
            }
        }

        // ✅ MELHORADO: Marcas elétricas
        $electricBrands = ['tesla', 'rivian', 'lucid', 'byd', 'nio', 'gwm', 'ora', 'great wall', 'voltz', 'energica'];
        if (in_array(strtolower($make), $electricBrands)) {
            return false;
        }

        // ✅ MELHORADO: Padrões elétricos no nome
        if (preg_match('/\b(electric|ev|hybrid|plug-in|phev|bev|eq[a-z])\b/i', $model)) {
            return false;
        }

        // Alguns carros modernos usam kit de reparo
        $modelsWithoutSpare = [
            'mini cooper', 'smart', 'fiat 500e', 'up!', 'fox'
        ];

        foreach ($modelsWithoutSpare as $modelPattern) {
            if (str_contains($modelLower, $modelPattern)) {
                return false;
            }
        }

        // Marcas que frequentemente não incluem estepe
        $brandsOftenWithoutSpare = ['smart', 'mini'];
        if (in_array(strtolower($make), $brandsOftenWithoutSpare)) {
            return false;
        }

        // Carros, SUVs, pickups normalmente têm estepe
        return in_array($category, ['hatch', 'sedan', 'suv', 'pickup', 'van']);
    }

    /**
     * ✅ NOVO: Obter pressão padrão para estepe
     */
    protected function getDefaultSparePresure(string $category, array $data): int
    {
        // Para motocicletas, usar pressão traseira + 5 PSI
        if ($category === 'motorcycle') {
            return ($data['pressure_light_rear'] ?? 30) + 5;
        }

        // Para carros, usar pressão maior + 10 PSI (mais realista)
        $frontPressure = $data['pressure_light_front'] ?? 32;
        $rearPressure = $data['pressure_light_rear'] ?? 30;
        $higherPressure = max($frontPressure, $rearPressure);
        
        return $higherPressure + 10;
    }

    /**
     * ✅ NOVO: Obter razão para não ter estepe
     */
    protected function getReasonForNoSpare(string $category, string $make, string $model): string
    {
        if ($category === 'motorcycle') {
            return 'motorcycle_rarely_has_spare';
        }

        // Verificar se é elétrico
        $electricPatterns = ['voltz', 'ev1', 'leaf', 'bolt', 'volt', 'tesla', 'model', 'electric', 'ev', 'hybrid', 'ora', 'byd', 'gwm'];
        foreach ($electricPatterns as $pattern) {
            if (str_contains($model, $pattern)) {
                return 'electric_vehicle_kit_repair';
            }
        }

        if (in_array($make, ['tesla', 'rivian', 'byd', 'nio', 'gwm', 'ora', 'great wall'])) {
            return 'electric_brand_no_spare';
        }

        // Verificar se é compacto
        if (str_contains($model, 'mini') || str_contains($model, 'smart')) {
            return 'compact_car_space_constraint';
        }

        return 'modern_car_kit_repair';
    }

    /**
     * ✅ NOVO: Sanitizar e converter dados de pressão
     */
    protected function sanitizeAndConvertPressureData(array &$data): array
    {
        $pressureFields = [
            'pressure_empty_front',
            'pressure_empty_rear', 
            'pressure_light_front',
            'pressure_light_rear',
            'pressure_max_front',
            'pressure_max_rear',
            'pressure_spare'
        ];

        foreach ($pressureFields as $field) {
            if (isset($data[$field])) {
                // Converter para número
                $value = $this->convertToNumeric($data[$field]);
                
                if ($value !== null) {
                    // Garantir que seja um número válido entre 1 e 150 PSI
                    $value = max(1, min(150, $value));
                    $data[$field] = $value;
                } else {
                    // Se não conseguir converter, remover o campo
                    unset($data[$field]);
                    
                    Log::warning('VehicleDataCorrectionService: Campo de pressão inválido removido', [
                        'field' => $field,
                        'original_value' => $data[$field] ?? 'null',
                        'vehicle' => "{$data['make']} {$data['model']} {$data['year']}"
                    ]);
                }
            }
        }

        return $data;
    }

    /**
     * ✅ NOVO: Converter valor para numérico
     */
    protected function convertToNumeric($value): ?float
    {
        // Se já é numérico, retornar
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Se é string, tentar extrair número
        if (is_string($value)) {
            // Remover caracteres não-numéricos exceto ponto e vírgula
            $cleaned = preg_replace('/[^0-9.,]/', '', $value);
            
            // Converter vírgula para ponto
            $cleaned = str_replace(',', '.', $cleaned);
            
            // Se tem múltiplos pontos, manter apenas o último
            if (substr_count($cleaned, '.') > 1) {
                $parts = explode('.', $cleaned);
                $last = array_pop($parts);
                $cleaned = implode('', $parts) . '.' . $last;
            }
            
            if (is_numeric($cleaned) && $cleaned > 0) {
                return (float) $cleaned;
            }
        }

        return null;
    }

    /**
     * ✅ NOVO: Validar dados com log detalhado
     */
    protected function validateWithLogging(array $data, string $vehicleIdentifier): void
    {
        try {
            $this->validateCorrectedDataIntelligent($data);
            
            Log::info('VehicleDataCorrectionService: Validação passou', [
                'vehicle' => $vehicleIdentifier,
                'pressures' => [
                    'empty_front' => $data['pressure_empty_front'] ?? null,
                    'empty_rear' => $data['pressure_empty_rear'] ?? null,
                    'light_front' => $data['pressure_light_front'] ?? null,
                    'light_rear' => $data['pressure_light_rear'] ?? null,
                    'max_front' => $data['pressure_max_front'] ?? null,
                    'max_rear' => $data['pressure_max_rear'] ?? null,
                    'spare' => $data['pressure_spare'] ?? null
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('VehicleDataCorrectionService: Falha na validação', [
                'vehicle' => $vehicleIdentifier,
                'error' => $e->getMessage(),
                'data_received' => [
                    'pressure_empty_front' => [
                        'value' => $data['pressure_empty_front'] ?? null,
                        'type' => gettype($data['pressure_empty_front'] ?? null)
                    ],
                    'pressure_light_front' => [
                        'value' => $data['pressure_light_front'] ?? null,
                        'type' => gettype($data['pressure_light_front'] ?? null)
                    ],
                    'pressure_spare' => [
                        'value' => $data['pressure_spare'] ?? null,
                        'type' => gettype($data['pressure_spare'] ?? null)
                    ]
                ]
            ]);
            
            throw $e;
        }
    }

    /**
     * ✅ NOVO: Determinar categoria do veículo para validação
     */
    protected function determineVehicleCategory(array $data): string
    {
        $make = strtolower($data['make'] ?? '');
        $model = strtolower($data['model'] ?? '');
        $category = strtolower($data['main_category'] ?? '');
        $segment = strtoupper($data['vehicle_segment'] ?? '');

        // 1. PRIORIDADE: Verificar por marca de motocicleta PRIMEIRO
        $motorcycleBrands = [
            'honda', 'yamaha', 'suzuki', 'kawasaki', 'triumph', 'ducati', 
            'harley-davidson', 'harley', 'bmw', 'ktm', 'aprilia', 'mv agusta',
            'moto guzzi', 'indian', 'benelli', 'royal enfield', 'husqvarna',
            'gasgas', 'beta', 'sherco', 'tm racing', 'husaberg',
            
            // ✅ ADICIONADO: Marcas elétricas e chinesas
            'super soco', 'supersoco', 'zero', 'energica', 'lightning', 'cake',
            'niu', 'gogoro', 'vmoto', 'silence', 'johammer', 'arc',
            
            // ✅ ADICIONADO: Marcas chinesas de motocicleta
            'cfmoto', 'benelli', 'qj motor', 'lifan', 'zontes', 'voge'
        ];

        if (in_array($make, $motorcycleBrands)) {
            return 'motorcycle';
        }

        // 2. Verificar por modelos de motocicleta específicos
        $motorcycleModels = [
            // Honda
            'bros', 'cb', 'cbr', 'crf', 'xre', 'hornet', 'transalp', 'africa twin',
            
            // Yamaha
            'r1', 'r6', 'mt', 'yzf', 'xtz', 'xt', 'fazer', 'tenere', 'tracer',
            
            // Suzuki
            'gsxr', 'gsx', 'vstrom', 'sv', 'katana', 'hayabusa', 'bandit',
            
            // Kawasaki
            'ninja', 'z', 'versys', 'klx', 'kx', 'vulcan', 'w800',
            
            // Triumph
            'tiger', 'street triple', 'speed triple', 'rocket', 'bonneville',
            
            // Ducati
            'panigale', 'monster', 'multistrada', 'scrambler', 'diavel',
            
            // Harley-Davidson
            'sportster', 'iron', 'forty-eight', 'street', 'road king',
            'fat boy', 'heritage', 'electra glide', 'ultra limited',
            
            // ✅ ADICIONADO: Modelos Super Soco e elétricas
            'tc max', 'tc', 'cux', 'cpx', 'tsx', 'vmoto', 'ts',
            
            // ✅ ADICIONADO: Padrões gerais
            'adventure', 'enduro', 'motocross', 'dirt bike', 'quad', 'scooter',
            'electric scooter', 'e-bike', 'e-scooter'
        ];

        foreach ($motorcycleModels as $motoModel) {
            if (str_contains($model, $motoModel)) {
                return 'motorcycle';
            }
        }

        // 3. Verificar por categoria explícita
        if (!empty($category)) {
            $categoryMap = [
                'hatch' => 'hatch',
                'hatchback' => 'hatch',
                'hatchbacks' => 'hatch',
                'sedan' => 'sedan',
                'sedans' => 'sedan',
                'suv' => 'suv',
                'suvs' => 'suv',
                'pickup' => 'pickup',
                'pickups' => 'pickup',
                'van' => 'van',
                'vans' => 'van',
                'motorcycle' => 'motorcycle',
                'motocicleta' => 'motorcycle',
                'motocicletas' => 'motorcycle',
                'moto' => 'motorcycle',
                'motos' => 'motorcycle',
                
                // ✅ ADICIONADO: Categorias elétricas
                'car_electric' => 'electric',
                'electric' => 'electric',
                'eletrico' => 'electric',
                'hybrid' => 'electric',
                'hibrido' => 'electric'
            ];

            if (isset($categoryMap[$category])) {
                return $categoryMap[$category];
            }
        }

        // 4. Verificar por segmento (depois de motocicletas)
        if ($segment === 'F' || $segment === 'PICKUP') {
            return 'pickup';
        }
        if ($segment === 'MOTO') {
            return 'motorcycle';
        }

        // 5. Verificar por modelo/marca (casos específicos para carros)
        $pickupModels = [
            'ram', 'silverado', 'f-150', 'f150', 'hilux', 'ranger', 'frontier', 
            'amarok', 'l200', 's10', 'colorado', 'canyon', 'tacoma', 'ridgeline',
            '1500', '2500', '3500'
        ];

        foreach ($pickupModels as $pickupModel) {
            if (str_contains($model, $pickupModel)) {
                return 'pickup';
            }
        }

        // 6. Verificar SUVs grandes
        $largeSuvModels = [
            'suburban', 'tahoe', 'escalade', 'navigator', 'expedition', 'sequoia',
            'land cruiser', 'gx', 'lx', 'qx80', 'armada'
        ];

        foreach ($largeSuvModels as $suvModel) {
            if (str_contains($model, $suvModel)) {
                return 'suv';
            }
        }

        // 7. Verificar por marca (pickups/comerciais) - APENAS para carros
        $commercialBrands = ['ram', 'ford', 'chevrolet', 'gmc', 'dodge'];
        if (in_array($make, $commercialBrands)) {
            // Se é marca comercial e modelo contém números grandes, pode ser pickup
            if (preg_match('/\b(1500|2500|3500|4500|5500)\b/', $model)) {
                return 'pickup';
            }
        }

        // 8. Fallback por segmento
        $segmentMap = [
            'A' => 'hatch',
            'B' => 'hatch', 
            'C' => 'sedan',
            'D' => 'suv',
            'E' => 'sedan',
            'F' => 'pickup'
        ];

        if (isset($segmentMap[$segment])) {
            return $segmentMap[$segment];
        }

        // 9. Default
        return 'default';
    }

    /**
     * ✅ NOVO: Obter informações da categoria determinada
     */
    public function getVehicleCategoryInfo(array $vehicleData): array
    {
        $category = $this->determineVehicleCategory($vehicleData);
        $pressureRange = $this->pressureRanges[$category] ?? $this->pressureRanges['default'];

        return [
            'category' => $category,
            'pressure_range' => $pressureRange,
            'validation_rules' => $this->getValidationRulesForCategory($category)
        ];
    }

    /**
     * ✅ NOVO: Obter regras de validação para categoria
     */
    protected function getValidationRulesForCategory(string $category): array
    {
        $rules = [
            'pickup' => [
                'description' => 'Pickups e veículos comerciais pesados',
                'pressure_note' => 'Pressões altas são normais para suportar carga',
                'typical_range' => '35-80 PSI'
            ],
            'suv' => [
                'description' => 'SUVs e utilitários esportivos',
                'pressure_note' => 'Pressões moderadas para conforto e performance',
                'typical_range' => '25-50 PSI'
            ],
            'sedan' => [
                'description' => 'Sedans e carros de passeio médios',
                'pressure_note' => 'Pressões padrão para uso urbano',
                'typical_range' => '22-45 PSI'
            ],
            'hatch' => [
                'description' => 'Hatchbacks e carros compactos',
                'pressure_note' => 'Pressões menores para economia e conforto',
                'typical_range' => '20-45 PSI'
            ],
            'default' => [
                'description' => 'Categoria não identificada',
                'pressure_note' => 'Faixa ampla para segurança',
                'typical_range' => '20-60 PSI'
            ]
        ];

        return $rules[$category] ?? $rules['default'];
    }
}
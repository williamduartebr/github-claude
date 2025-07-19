<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Fixed VehicleDataProcessorService
 * 
 * CORRIGIDO PARA COMPATIBILIDADE COM CSV todos_veiculos.csv:
 * - Mapeamento correto dos campos (category -> main_category)
 * - Derivação de is_motorcycle e vehicle_type baseado em category
 * - Validação robusta de dados do CSV
 * - Tratamento de campos ausentes
 */
class VehicleDataProcessorService
{
    /**
     * Mapeamento de campos CSV para sistema
     */
    protected array $fieldMapping = [
        'make' => 'make',
        'model' => 'model', 
        'year' => 'year',
        'tire_size' => 'tire_size',
        'pressure_empty_front' => 'pressure_empty_front',
        'pressure_empty_rear' => 'pressure_empty_rear',
        'pressure_light_front' => 'pressure_light_front',
        'pressure_light_rear' => 'pressure_light_rear',
        'pressure_max_front' => 'pressure_max_front',
        'pressure_max_rear' => 'pressure_max_rear',
        'pressure_spare' => 'pressure_spare',
        'category' => 'main_category', // MAPEAMENTO PRINCIPAL
        'recommended_oil' => 'recommended_oil' // CAMPO EXTRA
    ];

    /**
     * Mapeamento de categorias para tipos de veículo
     */
    protected array $categoryToVehicleType = [
        // Carros
        'sedans' => 'car',
        'hatchbacks' => 'car', 
        'suvs' => 'car',
        'pickups' => 'car',
        'conversíveis' => 'car',
        'wagons' => 'car',
        'compactos' => 'car',
        'luxo' => 'car',
        'esportivos' => 'car',
        'utilitários' => 'car',
        
        // Motocicletas
        'motocicletas' => 'motorcycle',
        'motos' => 'motorcycle',
        'scooters' => 'motorcycle',
        'trail' => 'motorcycle',
        'touring' => 'motorcycle',
        'custom' => 'motorcycle',
        'naked' => 'motorcycle',
        'sport' => 'motorcycle'
    ];

    /**
     * Processar CSV de veículos
     */
    public function processVehicleData(string $csvPath, array $filters = []): Collection
    {
        try {
            Log::info("Iniciando processamento do CSV", [
                'csv_path' => $csvPath,
                'filters' => $filters
            ]);

            // 1. Ler e validar CSV
            $rawData = $this->readCsvFile($csvPath);
            
            if ($rawData->isEmpty()) {
                throw new \Exception("CSV vazio ou não encontrado: {$csvPath}");
            }

            Log::info("CSV lido com sucesso", [
                'total_rows' => $rawData->count(),
                'first_row_keys' => array_keys($rawData->first() ?? [])
            ]);

            // 2. Validar estrutura do CSV
            $this->validateCsvStructure($rawData->first());

            // 3. Processar cada linha
            $processedData = $rawData->map(function ($row, $index) {
                try {
                    return $this->processVehicleRow($row, $index);
                } catch (\Exception $e) {
                    Log::warning("Erro ao processar linha {$index}", [
                        'row_data' => $row,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter(); // Remove nulls

            // 4. Aplicar filtros
            $filteredData = $this->applyFilters($processedData, $filters);

            // 5. Validar dados processados
            $validatedData = $this->validateProcessedData($filteredData);

            Log::info("Processamento concluído", [
                'raw_count' => $rawData->count(),
                'processed_count' => $processedData->count(),
                'filtered_count' => $filteredData->count(),
                'validated_count' => $validatedData->count()
            ]);

            return $validatedData;

        } catch (\Exception $e) {
            Log::error("Erro no processamento do CSV", [
                'csv_path' => $csvPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Ler arquivo CSV
     */
    protected function readCsvFile(string $csvPath): Collection
    {
        if (!file_exists($csvPath)) {
            throw new \Exception("Arquivo CSV não encontrado: {$csvPath}");
        }

        $csvContent = file_get_contents($csvPath);
        if ($csvContent === false) {
            throw new \Exception("Não foi possível ler o arquivo CSV: {$csvPath}");
        }

        // Parse CSV
        $lines = array_map('str_getcsv', explode("\n", trim($csvContent)));
        
        if (empty($lines)) {
            throw new \Exception("CSV vazio");
        }

        // Primeira linha são os headers
        $headers = array_shift($lines);
        
        // Limpar headers (remover espaços, caracteres especiais)
        $headers = array_map(function($header) {
            return trim(strtolower($header));
        }, $headers);

        // Converter para Collection de arrays associativos
        $data = collect($lines)->map(function ($line) use ($headers) {
            if (count($line) !== count($headers)) {
                return null; // Linha inválida
            }
            
            return array_combine($headers, $line);
        })->filter(); // Remove nulls

        return $data;
    }

    /**
     * Validar estrutura do CSV
     */
    protected function validateCsvStructure(array $firstRow): void
    {
        $requiredFields = [
            'make', 'model', 'year', 'tire_size', 
            'pressure_empty_front', 'pressure_empty_rear',
            'pressure_light_front', 'pressure_light_rear',
            'pressure_max_front', 'pressure_max_rear',
            'pressure_spare', 'category'
        ];

        $availableFields = array_keys($firstRow);
        
        $missingFields = array_diff($requiredFields, $availableFields);
        
        if (!empty($missingFields)) {
            throw new \Exception("Campos obrigatórios ausentes no CSV: " . implode(', ', $missingFields));
        }

        Log::info("Estrutura do CSV validada com sucesso", [
            'required_fields' => $requiredFields,
            'available_fields' => $availableFields
        ]);
    }

    /**
     * Processar linha individual do CSV
     */
    protected function processVehicleRow(array $row, int $index): array
    {
        // 1. Mapear campos básicos
        $vehicleData = [];
        
        foreach ($this->fieldMapping as $csvField => $systemField) {
            $value = trim($row[$csvField] ?? '');
            
            // Converter tipos de dados
            $vehicleData[$systemField] = $this->convertFieldValue($csvField, $value);
        }

        // 2. Derivar campos ausentes
        $vehicleData = $this->deriveAdditionalFields($vehicleData, $row, $index);

        // 3. Validar dados da linha
        $this->validateVehicleRowData($vehicleData, $index);

        // 4. Enriquecer dados
        $vehicleData = $this->enrichVehicleData($vehicleData);

        return $vehicleData;
    }

    /**
     * Converter valor do campo para tipo correto
     */
    protected function convertFieldValue(string $field, string $value): mixed
    {
        if (empty($value)) {
            return $this->getDefaultValue($field);
        }

        switch ($field) {
            case 'year':
            case 'pressure_empty_front':
            case 'pressure_empty_rear':
            case 'pressure_max_front':
            case 'pressure_max_rear':
                return (int) $value;
                
            case 'pressure_light_front':
            case 'pressure_light_rear': 
            case 'pressure_spare':
                return (float) $value;
                
            case 'make':
            case 'model':
                return ucwords(trim($value)); // Capitalizar
                
            case 'tire_size':
                return strtoupper(trim($value)); // Maiúsculo
                
            case 'category':
                return strtolower(trim($value)); // Minúsculo para mapeamento
                
            default:
                return trim($value);
        }
    }

    /**
     * Obter valor padrão para campo
     */
    protected function getDefaultValue(string $field): mixed
    {
        $defaults = [
            'year' => 2020,
            'pressure_empty_front' => 30,
            'pressure_empty_rear' => 28,
            'pressure_light_front' => 32.0,
            'pressure_light_rear' => 30.0,
            'pressure_max_front' => 36,
            'pressure_max_rear' => 34,
            'pressure_spare' => 32.0,
            'main_category' => 'hatchbacks',
            'recommended_oil' => '5W30'
        ];

        return $defaults[$field] ?? '';
    }

    /**
     * Derivar campos adicionais
     */
    protected function deriveAdditionalFields(array $vehicleData, array $originalRow, int $index): array
    {
        // 1. Determinar se é motocicleta
        $category = strtolower($vehicleData['main_category'] ?? '');
        $vehicleData['is_motorcycle'] = $this->isMotorcycle($category);
        
        // 2. Determinar tipo de veículo
        $vehicleData['vehicle_type'] = $vehicleData['is_motorcycle'] ? 'motorcycle' : 'car';
        
        // 3. Gerar identificador único
        $vehicleData['vehicle_identifier'] = $this->generateVehicleIdentifier(
            $vehicleData['make'] ?? '',
            $vehicleData['model'] ?? '',
            $vehicleData['year'] ?? 0
        );
        
        // 4. Gerar slug make-model
        $vehicleData['make_model_slug'] = $this->generateMakeModelSlug(
            $vehicleData['make'] ?? '',
            $vehicleData['model'] ?? ''
        );
        
        // 5. Adicionar índice de linha para debug
        $vehicleData['csv_row_index'] = $index;
        
        // 6. Timestamp de processamento
        $vehicleData['processed_at'] = now()->toISOString();

        // 7. Normalizar categoria para padrão do sistema
        $vehicleData['main_category'] = $this->normalizeCategoryName($vehicleData['main_category'] ?? '');

        // 8. Gerar display de pressões
        $vehicleData['pressure_display'] = $this->generatePressureDisplay($vehicleData);
        $vehicleData['pressure_empty_display'] = $this->generateEmptyPressureDisplay($vehicleData);
        $vehicleData['pressure_loaded_display'] = $this->generateLoadedPressureDisplay($vehicleData);

        return $vehicleData;
    }

    /**
     * Verificar se é motocicleta baseado na categoria
     */
    protected function isMotorcycle(string $category): bool
    {
        $motorcycleKeywords = [
            'moto', 'motocicleta', 'scooter', 'trail', 'touring', 
            'custom', 'naked', 'sport', 'cb', 'ninja', 'fazer'
        ];

        foreach ($motorcycleKeywords as $keyword) {
            if (str_contains($category, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gerar identificador único do veículo
     */
    protected function generateVehicleIdentifier(string $make, string $model, int $year): string
    {
        return Str::slug($make) . '-' . Str::slug($model) . '-' . $year;
    }

    /**
     * Gerar slug make-model
     */
    protected function generateMakeModelSlug(string $make, string $model): string
    {
        return Str::slug($make . ' ' . $model);
    }

    /**
     * Normalizar nome da categoria
     */
    protected function normalizeCategoryName(string $category): string
    {
        $categoryMap = [
            'sedan' => 'Sedans',
            'sedans' => 'Sedans',
            'hatch' => 'Hatchbacks',
            'hatchback' => 'Hatchbacks', 
            'hatchbacks' => 'Hatchbacks',
            'suv' => 'SUVs',
            'suvs' => 'SUVs',
            'pickup' => 'Pickups',
            'pickups' => 'Pickups',
            'conversível' => 'Conversíveis',
            'conversíveis' => 'Conversíveis',
            'wagon' => 'Wagons',
            'wagons' => 'Wagons',
            'compacto' => 'Compactos',
            'compactos' => 'Compactos',
            'motocicleta' => 'Motocicletas',
            'motocicletas' => 'Motocicletas',
            'moto' => 'Motocicletas',
            'motos' => 'Motocicletas'
        ];

        $normalized = $categoryMap[strtolower($category)] ?? ucfirst($category);
        
        return $normalized ?: 'Outros';
    }

    /**
     * Gerar display de pressões
     */
    protected function generatePressureDisplay(array $vehicleData): string
    {
        $front = $vehicleData['pressure_light_front'] ?? 30;
        $rear = $vehicleData['pressure_light_rear'] ?? 28;
        
        return "Dianteiros: {$front} PSI / Traseiros: {$rear} PSI";
    }

    /**
     * Gerar display de pressão vazio
     */
    protected function generateEmptyPressureDisplay(array $vehicleData): string
    {
        $front = $vehicleData['pressure_empty_front'] ?? 30;
        $rear = $vehicleData['pressure_empty_rear'] ?? 28;
        
        return "{$front}/{$rear} PSI";
    }

    /**
     * Gerar display de pressão carregado
     */
    protected function generateLoadedPressureDisplay(array $vehicleData): string
    {
        $front = $vehicleData['pressure_max_front'] ?? 36;
        $rear = $vehicleData['pressure_max_rear'] ?? 34;
        
        return "{$front}/{$rear} PSI";
    }

    /**
     * Validar dados da linha processada
     */
    protected function validateVehicleRowData(array $vehicleData, int $index): void
    {
        $errors = [];

        // Validações críticas
        if (empty($vehicleData['make'])) {
            $errors[] = 'Marca ausente';
        }

        if (empty($vehicleData['model'])) {
            $errors[] = 'Modelo ausente';
        }

        if (($vehicleData['year'] ?? 0) < 1990 || ($vehicleData['year'] ?? 0) > 2030) {
            $errors[] = 'Ano inválido: ' . ($vehicleData['year'] ?? 'N/A');
        }

        if (empty($vehicleData['tire_size'])) {
            $errors[] = 'Tamanho do pneu ausente';
        }

        // Validar pressões
        $pressureFields = [
            'pressure_empty_front', 'pressure_empty_rear',
            'pressure_light_front', 'pressure_light_rear',
            'pressure_max_front', 'pressure_max_rear',
            'pressure_spare'
        ];

        foreach ($pressureFields as $field) {
            $value = $vehicleData[$field] ?? 0;
            if ($value <= 0 || $value > 60) {
                $errors[] = "Pressão inválida {$field}: {$value}";
            }
        }

        if (!empty($errors)) {
            throw new \Exception("Erros na linha {$index}: " . implode(', ', $errors));
        }
    }

    /**
     * Enriquecer dados do veículo
     */
    protected function enrichVehicleData(array $vehicleData): array
    {
        // Adicionar timestamp de enriquecimento
        $vehicleData['enriched_at'] = now()->toISOString();

        // Detectar características especiais do veículo
        $vehicleData['is_premium'] = $this->isPremiumVehicle($vehicleData);
        $vehicleData['has_tpms'] = $this->hasTpmsSystem($vehicleData);
        $vehicleData['segment'] = $this->determineVehicleSegment($vehicleData);

        return $vehicleData;
    }

    /**
     * Verificar se é veículo premium
     */
    protected function isPremiumVehicle(array $vehicleData): bool
    {
        $premiumMakes = ['BMW', 'Mercedes-Benz', 'Audi', 'Lexus', 'Acura', 'Infiniti'];
        $make = $vehicleData['make'] ?? '';
        
        return in_array($make, $premiumMakes) || 
               ($vehicleData['year'] ?? 0) >= 2020 && 
               str_contains(strtolower($vehicleData['model'] ?? ''), 'premium');
    }

    /**
     * Verificar se tem sistema TPMS
     */
    protected function hasTpmsSystem(array $vehicleData): bool
    {
        // TPMS obrigatório no Brasil a partir de 2014 para carros novos
        return ($vehicleData['year'] ?? 0) >= 2014 && !$vehicleData['is_motorcycle'];
    }

    /**
     * Determinar segmento do veículo
     */
    protected function determineVehicleSegment(array $vehicleData): string
    {
        $category = strtolower($vehicleData['main_category'] ?? '');
        
        $segmentMap = [
            'hatchbacks' => 'B',
            'sedans' => 'C', 
            'suvs' => 'D',
            'pickups' => 'F',
            'motocicletas' => 'MOTO'
        ];

        return $segmentMap[$category] ?? 'OUTROS';
    }

    /**
     * Aplicar filtros aos dados processados
     */
    protected function applyFilters(Collection $data, array $filters): Collection
    {
        if (empty($filters)) {
            return $data;
        }

        return $data->filter(function ($vehicle) use ($filters) {
            foreach ($filters as $field => $value) {
                if (isset($vehicle[$field]) && $vehicle[$field] !== $value) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Validar dados processados finais
     */
    protected function validateProcessedData(Collection $data): Collection
    {
        return $data->filter(function ($vehicle) {
            // Filtros de qualidade final
            return !empty($vehicle['make']) &&
                   !empty($vehicle['model']) && 
                   !empty($vehicle['tire_size']) &&
                   ($vehicle['pressure_empty_front'] ?? 0) > 0 &&
                   ($vehicle['pressure_empty_rear'] ?? 0) > 0;
        });
    }

    /**
     * Obter estatísticas do processamento
     */
    public function getProcessingStats(Collection $data): array
    {
        $stats = [
            'total_vehicles' => $data->count(),
            'by_make' => $data->groupBy('make')->map->count()->toArray(),
            'by_category' => $data->groupBy('main_category')->map->count()->toArray(),
            'by_year' => $data->groupBy('year')->map->count()->toArray(),
            'motorcycles' => $data->where('is_motorcycle', true)->count(),
            'cars' => $data->where('is_motorcycle', false)->count(),
            'premium_vehicles' => $data->where('is_premium', true)->count(),
            'with_tpms' => $data->where('has_tpms', true)->count()
        ];

        // Ordenar estatísticas
        arsort($stats['by_make']);
        arsort($stats['by_category']);
        krsort($stats['by_year']);

        return $stats;
    }
}
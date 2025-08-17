<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Diagnostic Enhanced VehicleDataProcessorService
 * 
 * ADICIONADO:
 * - Rastreamento detalhado de perda de dados
 * - Logs específicos para cada etapa de filtragem
 * - Relatório de diagnóstico completo
 * - Validação menos restritiva para preservar dados
 */
class DiagnosticVehicleDataProcessorService extends VehicleDataProcessorService
{
    protected array $processingStats = [];
    protected array $rejectedRows = [];
    protected array $validationFailures = [];

    /**
     * Processar CSV com diagnóstico completo
     */
    public function processVehicleData(string $csvPath, array $filters = []): Collection
    {
        $this->initializeDiagnostics();
        
        try {
            Log::info("🔍 DIAGNÓSTICO: Iniciando processamento com rastreamento completo", [
                'csv_path' => $csvPath,
                'filters' => $filters
            ]);

            // 1. Ler CSV com diagnóstico
            $rawData = $this->readCsvFileWithDiagnostic($csvPath);
            $this->processingStats['01_raw_data'] = $rawData->count();
            
            if ($rawData->isEmpty()) {
                throw new \Exception("CSV vazio ou não encontrado: {$csvPath}");
            }

            Log::info("📊 STEP 1: CSV lido", [
                'total_rows' => $rawData->count(),
                'first_row_keys' => array_keys($rawData->first() ?? [])
            ]);

            // 2. Validar estrutura (sem rejeitar dados)
            $this->validateCsvStructureFlexible($rawData->first());

            // 3. Processar cada linha com diagnóstico
            $processedData = $this->processRowsWithDiagnostic($rawData);
            $this->processingStats['02_processed_data'] = $processedData->count();
            
            Log::info("📊 STEP 2: Dados processados", [
                'before' => $rawData->count(),
                'after' => $processedData->count(),
                'lost' => $rawData->count() - $processedData->count(),
                'loss_percentage' => round((($rawData->count() - $processedData->count()) / $rawData->count()) * 100, 2)
            ]);

            // 4. Aplicar filtros com diagnóstico
            $filteredData = $this->applyFiltersWithDiagnostic($processedData, $filters);
            $this->processingStats['03_filtered_data'] = $filteredData->count();
            
            Log::info("📊 STEP 3: Filtros aplicados", [
                'before' => $processedData->count(),
                'after' => $filteredData->count(),
                'lost' => $processedData->count() - $filteredData->count(),
                'filters_applied' => !empty($filters)
            ]);

            // 5. Validação final FLEXÍVEL
            $validatedData = $this->validateProcessedDataFlexible($filteredData);
            $this->processingStats['04_validated_data'] = $validatedData->count();
            
            Log::info("📊 STEP 4: Validação final", [
                'before' => $filteredData->count(),
                'after' => $validatedData->count(),
                'lost' => $filteredData->count() - $validatedData->count(),
                'loss_percentage' => round((($filteredData->count() - $validatedData->count()) / $filteredData->count()) * 100, 2)
            ]);

            // 6. Gerar relatório de diagnóstico
            $this->generateDiagnosticReport();

            return $validatedData;

        } catch (\Exception $e) {
            Log::error("🚨 DIAGNÓSTICO: Erro no processamento", [
                'csv_path' => $csvPath,
                'error' => $e->getMessage(),
                'processing_stats' => $this->processingStats,
                'rejected_rows_count' => count($this->rejectedRows)
            ]);
            throw $e;
        }
    }

    /**
     * Inicializar diagnósticos
     */
    protected function initializeDiagnostics(): void
    {
        $this->processingStats = [
            'started_at' => now()->toISOString(),
            '01_raw_data' => 0,
            '02_processed_data' => 0,
            '03_filtered_data' => 0,
            '04_validated_data' => 0
        ];
        
        $this->rejectedRows = [];
        $this->validationFailures = [];
    }

    /**
     * Ler CSV com diagnóstico
     */
    protected function readCsvFileWithDiagnostic(string $csvPath): Collection
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
        
        Log::info("🔍 CSV RAW ANALYSIS", [
            'total_lines' => count($lines),
            'first_line' => $lines[0] ?? 'EMPTY',
            'last_line' => end($lines) ?: 'EMPTY',
            'file_size_mb' => round(strlen($csvContent) / 1024 / 1024, 2)
        ]);
        
        if (empty($lines)) {
            throw new \Exception("CSV vazio");
        }

        // Primeira linha são os headers
        $headers = array_shift($lines);
        
        // Limpar headers
        $cleanHeaders = array_map(function($header) {
            return trim(strtolower($header));
        }, $headers);

        Log::info("🔍 CSV HEADERS ANALYSIS", [
            'original_headers' => $headers,
            'clean_headers' => $cleanHeaders,
            'header_count' => count($cleanHeaders)
        ]);

        // Converter para Collection - SEM FILTRAR LINHAS INVÁLIDAS AINDA
        $data = collect($lines)->map(function ($line, $index) use ($cleanHeaders) {
            if (count($line) !== count($cleanHeaders)) {
                // Não rejeitar ainda, apenas marcar
                Log::warning("⚠️ Linha com colunas inconsistentes", [
                    'line_index' => $index,
                    'expected_columns' => count($cleanHeaders),
                    'actual_columns' => count($line),
                    'line_content' => $line
                ]);
                
                // Tentar ajustar linha
                if (count($line) < count($cleanHeaders)) {
                    // Preencher colunas faltantes
                    $line = array_pad($line, count($cleanHeaders), '');
                } else {
                    // Cortar colunas extras
                    $line = array_slice($line, 0, count($cleanHeaders));
                }
            }
            
            return array_combine($cleanHeaders, $line);
        });

        Log::info("🔍 CSV DATA CONVERSION", [
            'lines_processed' => $data->count(),
            'sample_record' => $data->first()
        ]);

        return $data;
    }

    /**
     * Validar estrutura CSV de forma flexível
     */
    protected function validateCsvStructureFlexible(array $firstRow): void
    {
        $requiredFields = [
            'make', 'model', 'year', 'tire_size'
        ];

        $availableFields = array_keys($firstRow);
        $missingFields = array_diff($requiredFields, $availableFields);
        
        if (!empty($missingFields)) {
            Log::warning("⚠️ Campos obrigatórios ausentes (continuando mesmo assim)", [
                'missing_fields' => $missingFields,
                'available_fields' => $availableFields
            ]);
            // NÃO lançar exceção - continuar processamento
        } else {
            Log::info("✅ Estrutura do CSV validada com sucesso", [
                'required_fields' => $requiredFields,
                'available_fields' => $availableFields
            ]);
        }
    }

    /**
     * Processar linhas com diagnóstico detalhado
     */
    protected function processRowsWithDiagnostic(Collection $rawData): Collection
    {
        $processed = collect();
        $rejectedCount = 0;

        foreach ($rawData as $index => $row) {
            try {
                $processedRow = $this->processVehicleRowWithDiagnostic($row, $index);
                if ($processedRow) {
                    $processed->push($processedRow);
                } else {
                    $rejectedCount++;
                }
            } catch (\Exception $e) {
                $rejectedCount++;
                $this->rejectedRows[] = [
                    'index' => $index,
                    'row' => $row,
                    'error' => $e->getMessage(),
                    'stage' => 'processing'
                ];
                
                Log::warning("⚠️ Linha rejeitada durante processamento", [
                    'line_index' => $index,
                    'error' => $e->getMessage(),
                    'row_data' => $row
                ]);
            }
        }

        Log::info("📊 PROCESSAMENTO DE LINHAS COMPLETO", [
            'total_input' => $rawData->count(),
            'successfully_processed' => $processed->count(),
            'rejected' => $rejectedCount,
            'success_rate' => round(($processed->count() / $rawData->count()) * 100, 2)
        ]);

        return $processed;
    }

    /**
     * Processar linha individual com diagnóstico
     */
    protected function processVehicleRowWithDiagnostic(array $row, int $index): ?array
    {
        // Validações críticas MAIS FLEXÍVEIS
        $make = trim($row['make'] ?? '');
        $model = trim($row['model'] ?? '');
        
        if (empty($make) || empty($model)) {
            $this->validationFailures[] = [
                'index' => $index,
                'issue' => 'make_or_model_empty',
                'make' => $make,
                'model' => $model
            ];
            return null; // Esta linha realmente deve ser rejeitada
        }

        // Processar dados básicos
        $vehicleData = [];
        
        foreach ($this->fieldMapping as $csvField => $systemField) {
            $value = trim($row[$csvField] ?? '');
            $vehicleData[$systemField] = $this->convertFieldValueFlexible($csvField, $value);
        }

        // Derivar campos adicionais
        $vehicleData = $this->deriveAdditionalFieldsFlexible($vehicleData, $row, $index);

        // Validação mínima
        if (!$this->isVehicleDataMinimallyValid($vehicleData, $index)) {
            return null;
        }

        // Enriquecer dados
        $vehicleData = $this->enrichVehicleDataFlexible($vehicleData);

        return $vehicleData;
    }

    /**
     * Converter valor de campo de forma mais flexível
     */
    protected function convertFieldValueFlexible(string $field, string $value): mixed
    {
        if (empty($value)) {
            return $this->getDefaultValueFlexible($field);
        }

        switch ($field) {
            case 'year':
                $year = (int) $value;
                // Aceitar anos mais amplos
                return ($year >= 1980 && $year <= 2030) ? $year : 2020;
                
            case 'pressure_empty_front':
            case 'pressure_empty_rear':
            case 'pressure_max_front':
            case 'pressure_max_rear':
                $pressure = (int) $value;
                // Aceitar pressões mais amplas (15-60 PSI)
                return ($pressure >= 15 && $pressure <= 60) ? $pressure : $this->getDefaultValueFlexible($field);
                
            case 'pressure_light_front':
            case 'pressure_light_rear': 
            case 'pressure_spare':
                $pressure = (float) $value;
                return ($pressure >= 15.0 && $pressure <= 60.0) ? $pressure : $this->getDefaultValueFlexible($field);
                
            default:
                return parent::convertFieldValue($field, $value);
        }
    }

    /**
     * Obter valores padrão mais flexíveis
     */
    protected function getDefaultValueFlexible(string $field): mixed
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
            'recommended_oil' => '5W30',
            'tire_size' => '185/65 R15' // Padrão para casos sem tire_size
        ];

        return $defaults[$field] ?? '';
    }

    /**
     * Derivar campos adicionais de forma flexível
     */
    protected function deriveAdditionalFieldsFlexible(array $vehicleData, array $originalRow, int $index): array
    {
        // Derivar is_motorcycle de forma mais flexível
        $category = strtolower($vehicleData['main_category'] ?? 'hatchbacks');
        $vehicleData['is_motorcycle'] = in_array($category, ['motocicletas', 'motos', 'scooters', 'trail', 'touring', 'custom', 'naked', 'sport']);
        
        // Derivar vehicle_type
        $vehicleData['vehicle_type'] = $vehicleData['is_motorcycle'] ? 'motorcycle' : 'car';
        
        // Outros campos derivados (mais flexíveis)
        $vehicleData['is_premium'] = $this->isPremiumVehicleFlexible($vehicleData);
        $vehicleData['has_tpms'] = $this->hasTpmsFlexible($vehicleData);
        $vehicleData['vehicle_segment'] = $this->determineVehicleSegmentFlexible($vehicleData);
        
        return $vehicleData;
    }

    /**
     * Verificar se dados do veículo são minimamente válidos
     */
    protected function isVehicleDataMinimallyValid(array $vehicleData, int $index): bool
    {
        // Validação MUITO mais flexível
        $hasBasicInfo = !empty($vehicleData['make']) && !empty($vehicleData['model']);
        
        if (!$hasBasicInfo) {
            $this->validationFailures[] = [
                'index' => $index,
                'issue' => 'missing_basic_info',
                'data' => $vehicleData
            ];
            return false;
        }

        return true;
    }

    /**
     * Validação final flexível
     */
    protected function validateProcessedDataFlexible(Collection $data): Collection
    {
        return $data->filter(function ($vehicle, $index) {
            // Apenas validações críticas
            $isValid = !empty($vehicle['make']) && 
                      !empty($vehicle['model']);
            
            if (!$isValid) {
                $this->validationFailures[] = [
                    'index' => $index,
                    'issue' => 'final_validation_failed',
                    'data' => $vehicle
                ];
            }
            
            return $isValid;
        });
    }

    /**
     * Aplicar filtros com diagnóstico
     */
    protected function applyFiltersWithDiagnostic(Collection $data, array $filters): Collection
    {
        if (empty($filters)) {
            Log::info("📊 Nenhum filtro aplicado - mantendo todos os dados");
            return $data;
        }

        $beforeCount = $data->count();
        
        $filtered = $data->filter(function ($vehicle) use ($filters) {
            foreach ($filters as $field => $value) {
                if (isset($vehicle[$field]) && $vehicle[$field] !== $value) {
                    return false;
                }
            }
            return true;
        });

        Log::info("📊 FILTROS APLICADOS", [
            'filters' => $filters,
            'before' => $beforeCount,
            'after' => $filtered->count(),
            'removed' => $beforeCount - $filtered->count()
        ]);

        return $filtered;
    }

    /**
     * Gerar relatório de diagnóstico completo
     */
    protected function generateDiagnosticReport(): void
    {
        $report = [
            'processing_stats' => $this->processingStats,
            'data_loss_analysis' => [
                'stage_1_processing' => [
                    'input' => $this->processingStats['01_raw_data'],
                    'output' => $this->processingStats['02_processed_data'],
                    'lost' => $this->processingStats['01_raw_data'] - $this->processingStats['02_processed_data'],
                    'loss_percentage' => $this->calculateLossPercentage('01_raw_data', '02_processed_data')
                ],
                'stage_2_filtering' => [
                    'input' => $this->processingStats['02_processed_data'],
                    'output' => $this->processingStats['03_filtered_data'],
                    'lost' => $this->processingStats['02_processed_data'] - $this->processingStats['03_filtered_data'],
                    'loss_percentage' => $this->calculateLossPercentage('02_processed_data', '03_filtered_data')
                ],
                'stage_3_validation' => [
                    'input' => $this->processingStats['03_filtered_data'],
                    'output' => $this->processingStats['04_validated_data'],
                    'lost' => $this->processingStats['03_filtered_data'] - $this->processingStats['04_validated_data'],
                    'loss_percentage' => $this->calculateLossPercentage('03_filtered_data', '04_validated_data')
                ]
            ],
            'rejection_summary' => [
                'total_rejected' => count($this->rejectedRows),
                'validation_failures' => count($this->validationFailures),
                'top_rejection_reasons' => $this->getTopRejectionReasons()
            ],
            'recommendations' => $this->generateRecommendations()
        ];

        Log::info("📋 RELATÓRIO DE DIAGNÓSTICO COMPLETO", $report);
    }

    /**
     * Calcular porcentagem de perda
     */
    protected function calculateLossPercentage(string $inputStage, string $outputStage): float
    {
        $input = $this->processingStats[$inputStage] ?? 0;
        $output = $this->processingStats[$outputStage] ?? 0;
        
        if ($input === 0) return 0;
        
        return round((($input - $output) / $input) * 100, 2);
    }

    /**
     * Obter principais razões de rejeição
     */
    protected function getTopRejectionReasons(): array
    {
        $reasons = [];
        
        foreach ($this->validationFailures as $failure) {
            $issue = $failure['issue'];
            $reasons[$issue] = ($reasons[$issue] ?? 0) + 1;
        }
        
        arsort($reasons);
        return array_slice($reasons, 0, 5, true);
    }

    /**
     * Gerar recomendações
     */
    protected function generateRecommendations(): array
    {
        $recommendations = [];
        
        $totalLoss = $this->processingStats['01_raw_data'] - $this->processingStats['04_validated_data'];
        $lossPercentage = $this->calculateLossPercentage('01_raw_data', '04_validated_data');
        
        if ($lossPercentage > 40) {
            $recommendations[] = "⚠️ Alta perda de dados ({$lossPercentage}%) - revisar critérios de validação";
        }
        
        if ($this->calculateLossPercentage('01_raw_data', '02_processed_data') > 20) {
            $recommendations[] = "🔧 Muitas linhas rejeitadas no processamento - verificar formatação do CSV";
        }
        
        if ($this->calculateLossPercentage('03_filtered_data', '04_validated_data') > 30) {
            $recommendations[] = "📝 Validação final muito restritiva - considerar critérios menos rigorosos";
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "✅ Processamento dentro dos parâmetros normais";
        }
        
        return $recommendations;
    }

    // Métodos auxiliares flexíveis
    protected function isPremiumVehicleFlexible(array $vehicleData): bool
    {
        return ($vehicleData['year'] ?? 0) >= 2018; // Mais flexível
    }

    protected function hasTpmsFlexible(array $vehicleData): bool
    {
        return ($vehicleData['year'] ?? 0) >= 2012; // Mais flexível
    }

    protected function determineVehicleSegmentFlexible(array $vehicleData): string
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

    protected function enrichVehicleDataFlexible(array $vehicleData): array
    {
        // Enriquecimento mais básico e flexível
        $vehicleData['vehicle_full_name'] = trim($vehicleData['make'] . ' ' . $vehicleData['model'] . ' ' . $vehicleData['year']);
        $vehicleData['category_normalized'] = $this->normalizeCategoryName($vehicleData['main_category'] ?? '');
        
        return $vehicleData;
    }
}
<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced VehicleDataProcessorService - SOLUÇÃO PARA PERDA DE DADOS
 * 
 * PRINCIPAIS CORREÇÕES:
 * - Validação mais flexível que preserva mais dados
 * - Logs detalhados para identificar perdas
 * - Correção automática de dados quando possível
 * - Processamento robusto de CSV com problemas
 * - Mapeamento inteligente de campos ausentes
 */
class EnhancedVehicleDataProcessorService
{
    /**
     * Mapeamento de campos CSV para sistema (expandido)
     */
    protected array $fieldMapping = [
        'make' => 'make',
        'marca' => 'make', // Alias em português
        'model' => 'model',
        'modelo' => 'model', // Alias em português
        'year' => 'year',
        'ano' => 'year', // Alias em português
        'tire_size' => 'tire_size',
        'tamanho_pneu' => 'tire_size', // Alias
        'pressure_empty_front' => 'pressure_empty_front',
        'pressure_empty_rear' => 'pressure_empty_rear',
        'pressure_light_front' => 'pressure_light_front',
        'pressure_light_rear' => 'pressure_light_rear',
        'pressure_max_front' => 'pressure_max_front',
        'pressure_max_rear' => 'pressure_max_rear',
        'pressure_spare' => 'pressure_spare',
        'category' => 'main_category',
        'categoria' => 'main_category', // Alias
        'recommended_oil' => 'recommended_oil',
        'oleo_recomendado' => 'recommended_oil' // Alias
    ];

    /**
     * Campos obrigatórios mínimos
     */
    protected array $requiredFields = ['make', 'model'];

    /**
     * Campos opcionais com valores padrão
     */
    protected array $optionalFieldsWithDefaults = [
        'year' => 2020,
        'tire_size' => '185/65 R15',
        'pressure_empty_front' => 30,
        'pressure_empty_rear' => 28,
        'pressure_light_front' => 32.0,
        'pressure_light_rear' => 30.0,
        'pressure_max_front' => 36,
        'pressure_max_rear' => 34,
        'pressure_spare' => 32.0,
        'main_category' => 'hatchbacks'
    ];

    /**
     * Estatísticas de processamento
     */
    protected array $processingStats = [];

    /**
     * Processar CSV com estratégia enhanced
     */
    public function processVehicleData(string $csvPath, array $filters = []): Collection
    {
        $this->initializeStats();
        
        try {
            Log::info("🚀 Enhanced: Iniciando processamento otimizado", [
                'csv_path' => $csvPath,
                'filters' => $filters
            ]);

            // 1. Ler CSV com estratégia robusta
            $rawData = $this->readCsvRobust($csvPath);
            $this->recordStat('01_raw_data', $rawData->count());
            
            Log::info("📊 Enhanced: CSV carregado", [
                'total_rows' => $rawData->count()
            ]);

            // 2. Normalizar headers e estrutura
            $normalizedData = $this->normalizeDataStructure($rawData);
            $this->recordStat('02_normalized_data', $normalizedData->count());

            // 3. Processar cada registro com máxima preservação
            $processedData = $this->processRecordsMaxPreservation($normalizedData);
            $this->recordStat('03_processed_data', $processedData->count());
            
            Log::info("📊 Enhanced: Dados processados", [
                'input' => $normalizedData->count(),
                'output' => $processedData->count(),
                'preservation_rate' => round(($processedData->count() / $normalizedData->count()) * 100, 2) . '%'
            ]);

            // 4. Aplicar filtros (opcional)
            $filteredData = empty($filters) ? $processedData : $this->applyFiltersGently($processedData, $filters);
            $this->recordStat('04_filtered_data', $filteredData->count());

            // 5. Validação final mínima
            $validatedData = $this->validateMinimalRequirements($filteredData);
            $this->recordStat('05_validated_data', $validatedData->count());

            // 6. Enriquecer dados finais
            $enrichedData = $this->enrichDataIntelligently($validatedData);
            $this->recordStat('06_final_data', $enrichedData->count());

            // 7. Relatório final
            $this->generateProcessingReport();

            Log::info("✅ Enhanced: Processamento concluído", [
                'final_count' => $enrichedData->count(),
                'overall_preservation' => round(($enrichedData->count() / $rawData->count()) * 100, 2) . '%'
            ]);

            return $enrichedData;

        } catch (\Exception $e) {
            Log::error("❌ Enhanced: Erro no processamento", [
                'error' => $e->getMessage(),
                'stats' => $this->processingStats
            ]);
            throw $e;
        }
    }

    /**
     * Ler CSV com estratégia robusta
     */
    protected function readCsvRobust(string $csvPath): Collection
    {
        if (!file_exists($csvPath)) {
            throw new \Exception("Arquivo CSV não encontrado: {$csvPath}");
        }

        // Detectar encoding
        $content = file_get_contents($csvPath);
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        
        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            Log::info("🔧 Enhanced: Convertido encoding de {$encoding} para UTF-8");
        }

        // Detectar delimitador
        $delimiter = $this->detectCsvDelimiter($content);
        Log::info("🔧 Enhanced: Delimitador detectado: '{$delimiter}'");

        // Parse robusto
        $lines = str_getcsv($content, "\n");
        $data = [];

        foreach ($lines as $index => $line) {
            if (empty(trim($line))) continue; // Pular linhas vazias
            
            $row = str_getcsv($line, $delimiter);
            
            // Limpar valores
            $row = array_map(function($value) {
                return trim($value, " \t\n\r\0\x0B\"'");
            }, $row);
            
            $data[] = $row;
        }

        if (empty($data)) {
            throw new \Exception("CSV vazio após processamento");
        }

        // Separar headers e dados
        $headers = array_shift($data);
        $headers = array_map('strtolower', array_map('trim', $headers));

        Log::info("🔧 Enhanced: Headers detectados", [
            'count' => count($headers),
            'headers' => $headers
        ]);

        // Converter para Collection
        $collection = collect($data)->map(function($row) use ($headers) {
            // Ajustar tamanho da linha se necessário
            $row = array_pad($row, count($headers), '');
            $row = array_slice($row, 0, count($headers));
            
            return array_combine($headers, $row);
        })->filter(function($row) {
            // Remover apenas linhas completamente vazias
            return !empty(array_filter($row, function($value) {
                return !empty(trim($value));
            }));
        });

        return $collection;
    }

    /**
     * Detectar delimitador CSV
     */
    protected function detectCsvDelimiter(string $content): string
    {
        $delimiters = [',', ';', '\t', '|'];
        $maxCount = 0;
        $bestDelimiter = ',';

        foreach ($delimiters as $delimiter) {
            $count = substr_count(substr($content, 0, 1000), $delimiter);
            if ($count > $maxCount) {
                $maxCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter === '\t' ? "\t" : $bestDelimiter;
    }

    /**
     * Normalizar estrutura de dados
     */
    protected function normalizeDataStructure(Collection $rawData): Collection
    {
        return $rawData->map(function($row) {
            $normalized = [];
            
            // Mapear campos conhecidos
            foreach ($this->fieldMapping as $csvField => $systemField) {
                if (isset($row[$csvField])) {
                    $normalized[$systemField] = $row[$csvField];
                }
            }

            // Preservar campos não mapeados
            foreach ($row as $key => $value) {
                if (!in_array($key, array_keys($this->fieldMapping))) {
                    $normalized[$key] = $value;
                }
            }

            return $normalized;
        });
    }

    /**
     * Processar registros com máxima preservação
     */
    protected function processRecordsMaxPreservation(Collection $data): Collection
    {
        return $data->map(function($row, $index) {
            try {
                return $this->processSingleRecordRobust($row, $index);
            } catch (\Exception $e) {
                Log::warning("⚠️ Enhanced: Erro ao processar linha {$index}", [
                    'error' => $e->getMessage(),
                    'row_data' => $row
                ]);
                
                // Tentar recuperação
                return $this->attemptRecordRecovery($row, $index);
            }
        })->filter(); // Remove nulls
    }

    /**
     * Processar registro individual de forma robusta
     */
    protected function processSingleRecordRobust(array $row, int $index): ?array
    {
        $processed = [];

        // 1. Garantir campos obrigatórios
        foreach ($this->requiredFields as $field) {
            $value = trim($row[$field] ?? '');
            if (empty($value)) {
                throw new \Exception("Campo obrigatório ausente: {$field}");
            }
            $processed[$field] = $this->cleanFieldValue($field, $value);
        }

        // 2. Processar campos opcionais com defaults inteligentes
        foreach ($this->optionalFieldsWithDefaults as $field => $default) {
            $value = trim($row[$field] ?? '');
            
            if (empty($value)) {
                $processed[$field] = $this->getIntelligentDefault($field, $processed, $default);
            } else {
                $processed[$field] = $this->convertAndValidateValue($field, $value, $default);
            }
        }

        // 3. Derivar campos calculados
        $processed = $this->deriveCalculatedFields($processed, $row);

        // 4. Validação final do registro
        if (!$this->isRecordMinimallyValid($processed)) {
            throw new \Exception("Registro não atende critérios mínimos");
        }

        return $processed;
    }

    /**
     * Tentar recuperar registro com problemas
     */
    protected function attemptRecordRecovery(array $row, int $index): ?array
    {
        // Estratégias de recuperação
        $recovered = [];

        // 1. Tentar extrair make/model de campos concatenados
        if (empty($row['make']) && empty($row['model'])) {
            $fullName = $row['vehicle_name'] ?? $row['nome_veiculo'] ?? $row['full_name'] ?? '';
            if (!empty($fullName)) {
                $parts = explode(' ', trim($fullName), 2);
                $row['make'] = $parts[0] ?? '';
                $row['model'] = $parts[1] ?? '';
            }
        }

        // 2. Inferir ano se ausente
        if (empty($row['year'])) {
            $row['year'] = 2020; // Ano padrão razoável
        }

        // 3. Tentar processar novamente
        try {
            return $this->processSingleRecordRobust($row, $index);
        } catch (\Exception $e) {
            Log::debug("💀 Enhanced: Recuperação falhou para linha {$index}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Limpar valor do campo
     */
    protected function cleanFieldValue(string $field, string $value): string
    {
        // Remover caracteres problemáticos
        $cleaned = preg_replace('/[^\w\s\-\.\/]/', '', $value);
        $cleaned = trim($cleaned);
        
        // Capitalização apropriada para make/model
        if (in_array($field, ['make', 'model'])) {
            $cleaned = ucwords(strtolower($cleaned));
        }

        return $cleaned;
    }

    /**
     * Obter valor padrão inteligente
     */
    protected function getIntelligentDefault(string $field, array $processed, $fallback)
    {
        switch ($field) {
            case 'tire_size':
                // Inferir tire_size baseado na categoria
                $category = strtolower($processed['main_category'] ?? '');
                if (str_contains($category, 'moto')) {
                    return '120/70 R17';
                }
                return '185/65 R15'; // Padrão para carros
                
            case 'main_category':
                // Inferir categoria baseada no modelo
                $model = strtolower($processed['model'] ?? '');
                if (str_contains($model, 'suv') || str_contains($model, 'sw4')) {
                    return 'suvs';
                } elseif (str_contains($model, 'sedan') || str_contains($model, 'corolla')) {
                    return 'sedans';
                }
                return 'hatchbacks';
                
            default:
                return $fallback;
        }
    }

    /**
     * Converter e validar valor
     */
    protected function convertAndValidateValue(string $field, string $value, $default)
    {
        switch ($field) {
            case 'year':
                $year = (int) $value;
                return ($year >= 1980 && $year <= 2030) ? $year : $default;
                
            case 'pressure_empty_front':
            case 'pressure_empty_rear':
            case 'pressure_max_front':
            case 'pressure_max_rear':
                $pressure = (int) $value;
                return ($pressure >= 10 && $pressure <= 70) ? $pressure : $default;
                
            case 'pressure_light_front':
            case 'pressure_light_rear':
            case 'pressure_spare':
                $pressure = (float) $value;
                return ($pressure >= 10.0 && $pressure <= 70.0) ? $pressure : $default;
                
            default:
                return trim($value) ?: $default;
        }
    }

    /**
     * Derivar campos calculados
     */
    protected function deriveCalculatedFields(array $processed, array $originalRow): array
    {
        // is_motorcycle
        $category = strtolower($processed['main_category'] ?? '');
        $processed['is_motorcycle'] = in_array($category, [
            'motocicletas', 'motos', 'scooters', 'trail', 'touring', 
            'custom', 'naked', 'sport', 'motorcycle'
        ]);

        // vehicle_type
        $processed['vehicle_type'] = $processed['is_motorcycle'] ? 'motorcycle' : 'car';

        // is_premium (baseado no ano)
        $processed['is_premium'] = ($processed['year'] ?? 0) >= 2015;

        // has_tpms (baseado no ano)
        $processed['has_tpms'] = ($processed['year'] ?? 0) >= 2010;

        // vehicle_segment
        $processed['vehicle_segment'] = $this->determineSegment($processed);

        // Campos para SEO e URLs
        $processed['vehicle_full_name'] = trim($processed['make'] . ' ' . $processed['model'] . ' ' . $processed['year']);
        $processed['url_slug'] = Str::slug($processed['vehicle_full_name']);

        return $processed;
    }

    /**
     * Verificar se registro é minimamente válido
     */
    protected function isRecordMinimallyValid(array $record): bool
    {
        // Apenas validações absolutamente críticas
        return !empty($record['make']) && 
               !empty($record['model']) &&
               !empty($record['year']);
    }

    /**
     * Aplicar filtros gentilmente
     */
    protected function applyFiltersGently(Collection $data, array $filters): Collection
    {
        if (empty($filters)) {
            return $data;
        }

        Log::info("🔧 Enhanced: Aplicando filtros", ['filters' => $filters]);

        return $data->filter(function($vehicle) use ($filters) {
            foreach ($filters as $field => $value) {
                $vehicleValue = $vehicle[$field] ?? null;
                if ($vehicleValue !== $value) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Validação de requisitos mínimos
     */
    protected function validateMinimalRequirements(Collection $data): Collection
    {
        return $data->filter(function($vehicle) {
            return $this->isRecordMinimallyValid($vehicle);
        });
    }

    /**
     * Enriquecer dados inteligentemente
     */
    protected function enrichDataIntelligently(Collection $data): Collection
    {
        return $data->map(function($vehicle) {
            // Normalizar categoria
            $vehicle['category_normalized'] = $this->normalizeCategoryName($vehicle['main_category'] ?? '');
            
            // Gerar displays de pressão
            $vehicle['pressure_display'] = $this->generatePressureDisplay($vehicle);
            $vehicle['empty_pressure_display'] = $this->generateEmptyPressureDisplay($vehicle);
            $vehicle['loaded_pressure_display'] = $this->generateLoadedPressureDisplay($vehicle);
            
            return $vehicle;
        });
    }

    /**
     * Métodos auxiliares
     */
    protected function initializeStats(): void
    {
        $this->processingStats = [
            'started_at' => now()->toISOString()
        ];
    }

    protected function recordStat(string $stage, int $count): void
    {
        $this->processingStats[$stage] = $count;
    }

    protected function generateProcessingReport(): void
    {
        $report = [
            'processing_summary' => $this->processingStats,
            'data_flow' => $this->calculateDataFlow(),
            'preservation_rate' => $this->calculatePreservationRate()
        ];

        Log::info("📊 Enhanced: Relatório de processamento", $report);
    }

    protected function calculateDataFlow(): array
    {
        $flow = [];
        $stages = array_keys($this->processingStats);
        
        for ($i = 1; $i < count($stages); $i++) {
            $prevStage = $stages[$i-1];
            $currentStage = $stages[$i];
            
            if (is_numeric($this->processingStats[$prevStage]) && is_numeric($this->processingStats[$currentStage])) {
                $prev = $this->processingStats[$prevStage];
                $current = $this->processingStats[$currentStage];
                $lost = $prev - $current;
                $lossRate = $prev > 0 ? round(($lost / $prev) * 100, 2) : 0;
                
                $flow["{$prevStage}_to_{$currentStage}"] = [
                    'input' => $prev,
                    'output' => $current,
                    'lost' => $lost,
                    'loss_rate' => $lossRate . '%'
                ];
            }
        }
        
        return $flow;
    }

    protected function calculatePreservationRate(): string
    {
        $initial = $this->processingStats['01_raw_data'] ?? 0;
        $final = $this->processingStats['06_final_data'] ?? 0;
        
        if ($initial === 0) return '0%';
        
        return round(($final / $initial) * 100, 2) . '%';
    }

    // Métodos herdados necessários
    protected function determineSegment(array $vehicleData): string
    {
        $category = strtolower($vehicleData['main_category'] ?? '');
        
        $segmentMap = [
            'hatchbacks' => 'B',
            'sedans' => 'C', 
            'suvs' => 'D',
            'pickups' => 'F',
            'motocicletas' => 'MOTO',
            'motos' => 'MOTO'
        ];

        return $segmentMap[$category] ?? 'OUTROS';
    }

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

        return $categoryMap[strtolower($category)] ?? ucfirst($category) ?: 'Outros';
    }

    protected function generatePressureDisplay(array $vehicleData): string
    {
        $front = $vehicleData['pressure_light_front'] ?? 30;
        $rear = $vehicleData['pressure_light_rear'] ?? 28;
        
        return "Dianteiros: {$front} PSI / Traseiros: {$rear} PSI";
    }

    protected function generateEmptyPressureDisplay(array $vehicleData): string
    {
        $front = $vehicleData['pressure_empty_front'] ?? 30;
        $rear = $vehicleData['pressure_empty_rear'] ?? 28;
        
        return "{$front}/{$rear} PSI";
    }

    protected function generateLoadedPressureDisplay(array $vehicleData): string
    {
        $front = $vehicleData['pressure_max_front'] ?? 36;
        $rear = $vehicleData['pressure_max_rear'] ?? 34;
        
        return "{$front}/{$rear} PSI";
    }

    /**
     * Obter estatísticas detalhadas do processamento
     */
    public function getDetailedProcessingStats(): array
    {
        return [
            'processing_stats' => $this->processingStats,
            'data_flow' => $this->calculateDataFlow(),
            'preservation_rate' => $this->calculatePreservationRate(),
            'quality_metrics' => $this->calculateQualityMetrics()
        ];
    }

    protected function calculateQualityMetrics(): array
    {
        $final = $this->processingStats['06_final_data'] ?? 0;
        $initial = $this->processingStats['01_raw_data'] ?? 0;
        
        return [
            'data_preservation' => $this->calculatePreservationRate(),
            'processing_efficiency' => $initial > 0 ? round(($final / $initial) * 100, 2) : 0,
            'estimated_quality_score' => $this->estimateQualityScore($final, $initial),
            'recommendation' => $this->getProcessingRecommendation($final, $initial)
        ];
    }

    protected function estimateQualityScore(int $final, int $initial): float
    {
        if ($initial === 0) return 0;
        
        $preservationRate = ($final / $initial) * 100;
        
        if ($preservationRate >= 90) return 9.5;
        if ($preservationRate >= 80) return 8.5;
        if ($preservationRate >= 70) return 7.5;
        if ($preservationRate >= 60) return 6.5;
        if ($preservationRate >= 50) return 5.5;
        
        return max(1.0, $preservationRate / 10);
    }

    protected function getProcessingRecommendation(int $final, int $initial): string
    {
        if ($initial === 0) return "Dados insuficientes para análise";
        
        $preservationRate = ($final / $initial) * 100;
        
        if ($preservationRate >= 95) {
            return "✅ Excelente processamento! Mínima perda de dados.";
        } elseif ($preservationRate >= 85) {
            return "✅ Bom processamento. Perda de dados aceitável.";
        } elseif ($preservationRate >= 70) {
            return "⚠️ Processamento moderado. Considere revisar validações.";
        } elseif ($preservationRate >= 50) {
            return "⚠️ Alta perda de dados. Recomenda-se investigação detalhada.";
        } else {
            return "❌ Perda crítica de dados. Ação corretiva necessária urgentemente.";
        }
    }
}
<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services;

use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\ValueObjects\VehicleData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class VehicleDataProcessorService
{
    /**
     * Importar veículos do CSV todos_veiculos.csv
     */
    public function importFromCsv(string $csvPath = 'todos_veiculos.csv'): Collection
    {
        Log::info("Iniciando importação de veículos do CSV: {$csvPath}");

        // Usar método mais robusto para encontrar o arquivo
        $fullPath = $this->findCsvFile($csvPath);

        if (!$fullPath) {
            throw new \Exception("Arquivo CSV não encontrado: {$csvPath}. Verifique se o arquivo existe em storage/app/");
        }

        Log::info("CSV encontrado em: {$fullPath}");

        // Ler o arquivo diretamente usando File facade
        $csvContent = File::get($fullPath);
        $vehicles = collect();

        $lines = explode("\n", $csvContent);
        $headers = null;
        $processedCount = 0;
        $errorCount = 0;

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            // Primeira linha são os cabeçalhos
            if ($headers === null) {
                $headers = str_getcsv($line);
                Log::info("Cabeçalhos encontrados: " . implode(', ', $headers));
                continue;
            }

            try {
                $data = str_getcsv($line);

                // Verificar se tem o número correto de colunas
                if (count($data) !== count($headers)) {
                    Log::warning("Linha {$lineNumber}: número incorreto de colunas - " . count($data) . " encontradas, " . count($headers) . " esperadas");
                    $errorCount++;
                    continue;
                }

                $vehicleData = array_combine($headers, $data);
                $vehicle = $this->createVehicleFromCsvData($vehicleData);

                if ($vehicle) {
                    $vehicles->push($vehicle);
                    $processedCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Exception $e) {
                Log::error("Erro processando linha {$lineNumber}: " . $e->getMessage());
                $errorCount++;
            }
        }

        Log::info("Importação concluída: {$processedCount} veículos processados, {$errorCount} erros");

        return $vehicles;
    }

    /**
     * Agrupar por make+model+year (cada ano como grupo separado)
     */
    public function getUniqueVehicleCombinations(Collection $vehicles): Collection
    {
        Log::info("Criando combinações únicas make+model+year", [
            'total_vehicles' => $vehicles->count()
        ]);

        $uniqueCombinations = $vehicles->groupBy(function (VehicleData $vehicle) {
            // ✅ CHAVE COM ANO: Inclui o ano para diferenciar artigos
            return strtolower(trim($vehicle->make)) . '_' .
                strtolower(trim($vehicle->model)) . '_' .
                $vehicle->year;
        })->map(function (Collection $group) {
            // Como cada grupo agora é make+model+year, 
            // geralmente terá apenas 1 item, mas pode ter duplicatas exatas
            return $group->sortByDesc(function (VehicleData $vehicle) {
                $score = 0;

                // Dados mais completos têm prioridade
                if ($vehicle->recommendedOil && $vehicle->recommendedOil !== 'NA') {
                    $score += 10;
                }

                if ($vehicle->pressureSpare && $vehicle->pressureSpare > 0) {
                    $score += 5;
                }

                if (!empty($vehicle->tireSize)) {
                    $score += 3;
                }

                return $score;
            })->first();
        })->values();

        Log::info("Combinações únicas criadas", [
            'original_count' => $vehicles->count(),
            'unique_combinations' => $uniqueCombinations->count()
        ]);

        return $uniqueCombinations;
    }

    /**
     * 🔥 CORRIGIDO: Obter veículos prontos para geração (COM combinações únicas)
     */
    public function getVehiclesReadyForGeneration(Collection $vehicles): Collection
    {
        // 1. Primeiro filtrar apenas os veículos válidos
        $validVehicles = $vehicles->filter(function (VehicleData $vehicle) {
            $issues = $this->validateVehicleData($vehicle);
            return empty($issues);
        });

        // 2. Aplicar combinações únicas (agora deve dar ~965 únicos)
        $uniqueVehicles = $this->getUniqueVehicleCombinations($validVehicles);

        Log::info("Veículos prontos para geração", [
            'total_imported' => $vehicles->count(),
            'valid_vehicles' => $validVehicles->count(),
            'unique_combinations' => $uniqueVehicles->count()
        ]);

        return $uniqueVehicles;
    }

    /**
     * 🔧 CORRIGIDO: Filtrar veículos por critérios (filtros mais flexíveis)
     */
    public function filterVehicles(Collection $vehicles, array $criteria): Collection
    {
        return $vehicles->filter(function (VehicleData $vehicle) use ($criteria) {
            // Filtro por marca
            if (!empty($criteria['make'])) {
                if (strtolower($vehicle->make) !== strtolower($criteria['make'])) {
                    return false;
                }
            }

            // Filtro por modelo
            if (!empty($criteria['model'])) {
                if (stripos($vehicle->model, $criteria['model']) === false) {
                    return false;
                }
            }

            // Filtro por ano (range)
            if (!empty($criteria['year_from'])) {
                if ($vehicle->year < $criteria['year_from']) {
                    return false;
                }
            }

            if (!empty($criteria['year_to'])) {
                if ($vehicle->year > $criteria['year_to']) {
                    return false;
                }
            }

            // 🔧 CORRIGIDO: Filtro por categoria (mais flexível)
            if (!empty($criteria['category'])) {
                $filterCategory = strtolower(trim($criteria['category']));
                $vehicleCategory = strtolower(trim($vehicle->category));
                $vehicleMainCategory = strtolower(trim($vehicle->getMainCategory()));
                
                // Verificar se bate com categoria original, principal ou contém
                if ($vehicleCategory !== $filterCategory && 
                    $vehicleMainCategory !== $filterCategory &&
                    !str_contains($vehicleCategory, $filterCategory) &&
                    !str_contains($vehicleMainCategory, $filterCategory)) {
                    return false;
                }
            }

            // 🔧 CORRIGIDO: Filtro por tipo de veículo (mais robusto)
            if (!empty($criteria['vehicle_type'])) {
                $filterType = strtolower(trim($criteria['vehicle_type']));
                $vehicleType = strtolower(trim($vehicle->getVehicleType()));
                
                // Mapeamento flexível para tipos
                $typeMapping = [
                    'motorcycle' => ['motorcycle', 'moto', 'motocicleta'],
                    'car' => ['car', 'carro', 'hatch', 'sedan', 'suv', 'pickup', 'hatchback'],
                    'electric' => ['electric', 'elétrico', 'eletrico'],
                    'hybrid' => ['hybrid', 'híbrido', 'hibrido']
                ];
                
                $matches = false;
                
                // Verificação direta
                if ($vehicleType === $filterType) {
                    $matches = true;
                } else {
                    // Verificação por mapeamento
                    foreach ($typeMapping as $mappedType => $variants) {
                        if ($filterType === $mappedType && in_array($vehicleType, $variants)) {
                            $matches = true;
                            break;
                        }
                        // Verificação inversa
                        if (in_array($filterType, $variants) && $vehicleType === $mappedType) {
                            $matches = true;
                            break;
                        }
                    }
                }
                
                if (!$matches) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Validar dados do veículo
     */
    public function validateVehicleData(VehicleData $vehicle): array
    {
        $issues = [];

        // Validações obrigatórias
        if (empty(trim($vehicle->make))) {
            $issues[] = 'Marca não informada';
        }

        if (empty(trim($vehicle->model))) {
            $issues[] = 'Modelo não informado';
        }

        if ($vehicle->year < 1990 || $vehicle->year > 2030) {
            $issues[] = "Ano inválido: {$vehicle->year}";
        }

        // Validações de pressão
        if ($vehicle->pressureEmptyFront <= 0 || $vehicle->pressureEmptyFront > 50) {
            $issues[] = "Pressão dianteira inválida: {$vehicle->pressureEmptyFront}";
        }

        if ($vehicle->pressureEmptyRear <= 0 || $vehicle->pressureEmptyRear > 50) {
            $issues[] = "Pressão traseira inválida: {$vehicle->pressureEmptyRear}";
        }

        return $issues;
    }

    /**
     * 🆕 NOVO: Debug de filtros aplicados
     */
    public function debugFilterResults(Collection $vehicles, array $criteria): array
    {
        Log::info("🔍 DEBUG: Aplicando filtros", [
            'total_vehicles' => $vehicles->count(),
            'criteria' => $criteria
        ]);
        
        $beforeFilter = $vehicles->count();
        $afterFilter = $this->filterVehicles($vehicles, $criteria)->count();
        
        // Analisar por que veículos foram filtrados
        $sampleFiltered = $vehicles->filter(function (VehicleData $vehicle) use ($criteria) {
            return !$this->vehicleMatchesCriteria($vehicle, $criteria);
        })->take(5);
        
        Log::info("🔍 DEBUG: Resultados do filtro", [
            'before_filter' => $beforeFilter,
            'after_filter' => $afterFilter,
            'filtered_out' => $beforeFilter - $afterFilter,
            'sample_filtered_vehicles' => $sampleFiltered->map(function($v) {
                return [
                    'vehicle' => "{$v->make} {$v->model} {$v->year}",
                    'category' => $v->category,
                    'main_category' => $v->getMainCategory(),
                    'vehicle_type' => $v->getVehicleType()
                ];
            })->toArray()
        ]);
        
        return [
            'before' => $beforeFilter,
            'after' => $afterFilter,
            'removed' => $beforeFilter - $afterFilter
        ];
    }

    /**
     * 🆕 NOVO: Verificar se veículo atende critérios (para debug)
     */
    private function vehicleMatchesCriteria(VehicleData $vehicle, array $criteria): bool
    {
        // Reimplementar a lógica de filtro para debug
        if (!empty($criteria['make'])) {
            if (strtolower($vehicle->make) !== strtolower($criteria['make'])) {
                return false;
            }
        }

        if (!empty($criteria['model'])) {
            if (stripos($vehicle->model, $criteria['model']) === false) {
                return false;
            }
        }

        if (!empty($criteria['year_from'])) {
            if ($vehicle->year < $criteria['year_from']) {
                return false;
            }
        }

        if (!empty($criteria['year_to'])) {
            if ($vehicle->year > $criteria['year_to']) {
                return false;
            }
        }

        // Filtro de categoria corrigido
        if (!empty($criteria['category'])) {
            $filterCategory = strtolower(trim($criteria['category']));
            $vehicleCategory = strtolower(trim($vehicle->category));
            $vehicleMainCategory = strtolower(trim($vehicle->getMainCategory()));
            
            if ($vehicleCategory !== $filterCategory && 
                $vehicleMainCategory !== $filterCategory &&
                !str_contains($vehicleCategory, $filterCategory) &&
                !str_contains($vehicleMainCategory, $filterCategory)) {
                return false;
            }
        }

        // Filtro de tipo corrigido
        if (!empty($criteria['vehicle_type'])) {
            $filterType = strtolower(trim($criteria['vehicle_type']));
            $vehicleType = strtolower(trim($vehicle->getVehicleType()));
            
            $typeMapping = [
                'motorcycle' => ['motorcycle', 'moto', 'motocicleta'],
                'car' => ['car', 'carro', 'hatch', 'sedan', 'suv', 'pickup', 'hatchback'],
                'electric' => ['electric', 'elétrico', 'eletrico'],
                'hybrid' => ['hybrid', 'híbrido', 'hibrido']
            ];
            
            $matches = false;
            
            if ($vehicleType === $filterType) {
                $matches = true;
            } else {
                foreach ($typeMapping as $mappedType => $variants) {
                    if ($filterType === $mappedType && in_array($vehicleType, $variants)) {
                        $matches = true;
                        break;
                    }
                    if (in_array($filterType, $variants) && $vehicleType === $mappedType) {
                        $matches = true;
                        break;
                    }
                }
            }
            
            if (!$matches) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obter estatísticas dos veículos
     */
    public function getStatistics(Collection $vehicles): array
    {
        $stats = [
            'total_vehicles' => $vehicles->count(),
            'unique_combinations' => $this->getUniqueVehicleCombinations($vehicles)->count(),
            'by_make' => $vehicles->groupBy('make')->map->count()->sortDesc()->toArray(),
            'by_category' => $vehicles->groupBy('category')->map->count()->toArray(),
            'by_year' => $vehicles->groupBy('year')->map->count()->sortDesc()->toArray(),
            'by_vehicle_type' => $vehicles->groupBy(function ($v) {
                return $v->getVehicleType();
            })->map->count()->toArray(),
            'by_main_category' => $vehicles->groupBy(function ($v) {
                return $v->getMainCategory();
            })->map->count()->toArray()
        ];

        return $stats;
    }

    /**
     * Criar lotes de veículos para processamento
     */
    public function createBatches(Collection $vehicles, int $batchSize = 50): Collection
    {
        return $vehicles->chunk($batchSize)->map(function (Collection $chunk, int $index) {
            return [
                'index' => $index,
                'count' => $chunk->count(),
                'vehicles' => $chunk->values()->toArray()
            ];
        })->values();
    }

    /**
     * Encontrar arquivo CSV usando múltiplas estratégias
     */
    protected function findCsvFile(string $csvPath): ?string
    {
        // Lista de caminhos possíveis para tentar
        $possiblePaths = [
            // Caminho direto baseado em storage_path
            storage_path("app/{$csvPath}"),
            storage_path($csvPath),

            // Caminho baseado em base_path
            base_path("storage/app/{$csvPath}"),
            base_path($csvPath),

            // Caminho absoluto se fornecido
            $csvPath,

            // Caminho relativo ao diretório atual
            getcwd() . "/{$csvPath}",
            getcwd() . "/storage/app/{$csvPath}",

            // Apenas o nome do arquivo no storage/app
            storage_path("app/" . basename($csvPath)),
        ];

        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                Log::info("CSV encontrado em: {$path}");
                return $path;
            }
        }

        return null;
    }

    /**
     * Criar VehicleData a partir dos dados do CSV
     */
    protected function createVehicleFromCsvData(array $csvData): ?VehicleData
    {
        try {
            // Validar dados obrigatórios
            if (empty($csvData['make']) || empty($csvData['model']) || empty($csvData['year'])) {
                Log::warning("Dados obrigatórios ausentes: " . json_encode($csvData));
                return null;
            }

            // Limpar e validar dados
            $make = trim($csvData['make']);
            $model = trim($csvData['model']);
            $year = (int) $csvData['year'];

            // Validar ano
            if ($year < 1990 || $year > 2030) {
                Log::warning("Ano inválido: {$year} para {$make} {$model}");
                return null;
            }

            return new VehicleData(
                make: $make,
                model: $model,
                year: $year,
                tireSize: trim($csvData['tire_size'] ?? ''),
                pressureEmptyFront: (int) ($csvData['pressure_empty_front'] ?? 32),
                pressureEmptyRear: (int) ($csvData['pressure_empty_rear'] ?? 32),
                pressureLightFront: (float) ($csvData['pressure_light_front'] ?? 34.0),
                pressureLightRear: (float) ($csvData['pressure_light_rear'] ?? 34.0),
                pressureMaxFront: (int) ($csvData['pressure_max_front'] ?? 36),
                pressureMaxRear: (int) ($csvData['pressure_max_rear'] ?? 36),
                pressureSpare: !empty($csvData['pressure_spare']) ? (float) $csvData['pressure_spare'] : null,
                category: trim($csvData['category'] ?? 'car'),
                recommendedOil: !empty($csvData['recommended_oil']) && $csvData['recommended_oil'] !== 'NA'
                    ? trim($csvData['recommended_oil'])
                    : null
            );
        } catch (\Exception $e) {
            Log::error("Erro criando VehicleData: " . $e->getMessage() . " - Dados: " . json_encode($csvData));
            return null;
        }
    }

    /**
     * 🆕 NOVO: Analisar distribuição de combinações
     */
    public function analyzeCombinationDistribution(Collection $vehicles): array
    {
        $analysis = [
            'total_vehicles' => $vehicles->count(),
            'unique_combinations' => 0,
            'by_year_distribution' => [],
            'duplicates_analysis' => [],
            'sample_combinations' => []
        ];

        // Agrupar por make+model+year
        $combinations = $vehicles->groupBy(function (VehicleData $vehicle) {
            return strtolower(trim($vehicle->make)) . '_' . 
                   strtolower(trim($vehicle->model)) . '_' . 
                   $vehicle->year;
        });

        $analysis['unique_combinations'] = $combinations->count();

        // Analisar distribuição por ano
        $analysis['by_year_distribution'] = $vehicles->groupBy('year')
            ->map->count()
            ->sortDesc()
            ->toArray();

        // Analisar duplicatas
        $duplicates = $combinations->filter(function ($group) {
            return $group->count() > 1;
        });

        $analysis['duplicates_analysis'] = [
            'total_duplicate_groups' => $duplicates->count(),
            'total_duplicate_vehicles' => $duplicates->sum(function ($group) {
                return $group->count() - 1; // -1 porque manteremos 1 de cada
            })
        ];

        // Amostras de combinações
        $analysis['sample_combinations'] = $combinations->take(10)
            ->map(function ($group, $key) {
                $first = $group->first();
                return [
                    'combination_key' => $key,
                    'vehicle' => "{$first->make} {$first->model} {$first->year}",
                    'count_in_group' => $group->count(),
                    'category' => $first->category,
                    'main_category' => $first->getMainCategory(),
                    'vehicle_type' => $first->getVehicleType()
                ];
            })->toArray();

        return $analysis;
    }
}
<?php

namespace App\ContentGeneration\WhenToChangeTires\Infrastructure\Services;

use App\ContentGeneration\WhenToChangeTires\Domain\ValueObjects\VehicleData;
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
     * 🎯 NOVA FUNÇÃO: Obter combinações únicas de veículos (make + model)
     * Esta função agrupa os veículos por make+model e retorna apenas 1 representante de cada grupo
     */
    public function getUniqueVehicleCombinations(Collection $vehicles): Collection
    {
        Log::info("Criando combinações únicas make+model", [
            'total_vehicles' => $vehicles->count()
        ]);

        $uniqueCombinations = $vehicles->groupBy(function (VehicleData $vehicle) {
            // Criar chave única baseada em make+model (normalizada)
            return strtolower(trim($vehicle->make)) . '_' . strtolower(trim($vehicle->model));
        })->map(function (Collection $group) {
            // Para cada grupo, pegar o "melhor" representante
            return $group->sortByDesc(function (VehicleData $vehicle) {
                // Critério de prioridade:
                // 1. Ano mais recente (peso 100)
                // 2. Tem óleo recomendado (peso 10)
                // 3. Tem pressão spare (peso 5)
                // 4. Dados mais completos
                $score = $vehicle->year * 100;
                
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

        // Log de algumas amostras para debug
        $uniqueCombinations->take(5)->each(function (VehicleData $vehicle) {
            Log::debug("Combinação única selecionada: {$vehicle->make} {$vehicle->model} ({$vehicle->year})");
        });

        return $uniqueCombinations;
    }

    /**
     * 🔥 ATUALIZADA: Obter veículos prontos para geração (COM combinações únicas)
     */
    public function getVehiclesReadyForGeneration(Collection $vehicles): Collection
    {
        // 1. Primeiro filtrar apenas os veículos válidos
        $validVehicles = $vehicles->filter(function (VehicleData $vehicle) {
            $issues = $this->validateVehicleData($vehicle);
            return empty($issues);
        });

        // 2. Aplicar combinações únicas (289 únicos)
        $uniqueVehicles = $this->getUniqueVehicleCombinations($validVehicles);

        Log::info("Veículos prontos para geração", [
            'total_imported' => $vehicles->count(),
            'valid_vehicles' => $validVehicles->count(),
            'unique_combinations' => $uniqueVehicles->count()
        ]);

        return $uniqueVehicles;
    }

    /**
     * Filtrar veículos por critérios
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

            // Filtro por categoria
            if (!empty($criteria['category'])) {
                if ($vehicle->category !== $criteria['category']) {
                    return false;
                }
            }

            // Filtro por tipo de veículo
            if (!empty($criteria['vehicle_type'])) {
                if ($vehicle->getVehicleType() !== $criteria['vehicle_type']) {
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
            'by_vehicle_type' => $vehicles->groupBy(function($v) { 
                return $v->getVehicleType(); 
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
}
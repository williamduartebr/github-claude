<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Serviço para processamento de dados de veículos do CSV
 * 
 * Responsável por carregar, validar, filtrar e processar dados de veículos
 * Similar ao sistema atual (vehicle_importer.php), mas adaptado para DDD
 */
class VehicleDataProcessorService
{
    protected array $requiredHeaders = [
        'make',
        'model', 
        'year',
        'tire_size'
    ];

    protected array $optionalHeaders = [
        'pressure_empty_front',
        'pressure_empty_rear',
        'pressure_light_front',
        'pressure_light_rear',
        'pressure_max_front',
        'pressure_max_rear',
        'pressure_spare',
        'category'
    ];

    /**
     * Carregar veículos do arquivo CSV
     */
    public function loadFromCsv(string $csvPath): Collection
    {
        if (!file_exists($csvPath)) {
            throw new \InvalidArgumentException("Arquivo CSV não encontrado: {$csvPath}");
        }

        $vehicles = collect();
        $handle = fopen($csvPath, 'r');
        
        if ($handle === false) {
            throw new \RuntimeException("Não foi possível abrir o arquivo: {$csvPath}");
        }

        try {
            // Ler cabeçalho
            $headers = fgetcsv($handle);
            if ($headers === false) {
                throw new \RuntimeException("Não foi possível ler o cabeçalho do CSV");
            }

            // Normalizar cabeçalhos (lowercase)
            $headers = array_map('strtolower', $headers);
            $headerMap = $this->mapHeaders($headers);

            // Validar cabeçalhos obrigatórios
            $this->validateRequiredHeaders($headerMap);

            $lineNumber = 1;
            while (($data = fgetcsv($handle)) !== false) {
                $lineNumber++;
                
                try {
                    $vehicle = $this->processVehicleLine($data, $headerMap, $lineNumber);
                    if ($vehicle) {
                        $vehicles->push($vehicle);
                    }
                } catch (\Exception $e) {
                    Log::warning("Erro ao processar linha {$lineNumber} do CSV: " . $e->getMessage(), [
                        'line_data' => $data,
                        'csv_path' => $csvPath
                    ]);
                }
            }

        } finally {
            fclose($handle);
        }

        return $vehicles;
    }

    /**
     * Mapear cabeçalhos para índices
     */
    protected function mapHeaders(array $headers): array
    {
        $map = [];
        
        foreach ($headers as $index => $header) {
            $map[$header] = $index;
        }
        
        return $map;
    }

    /**
     * Validar cabeçalhos obrigatórios
     */
    protected function validateRequiredHeaders(array $headerMap): void
    {
        $missing = [];
        
        foreach ($this->requiredHeaders as $required) {
            if (!isset($headerMap[$required])) {
                $missing[] = $required;
            }
        }
        
        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                "Cabeçalhos obrigatórios não encontrados no CSV: " . implode(', ', $missing)
            );
        }
    }

    /**
     * Processar linha individual do CSV
     */
    protected function processVehicleLine(array $data, array $headerMap, int $lineNumber): ?array
    {
        // Verificar se linha tem dados suficientes
        if (count($data) < count($this->requiredHeaders)) {
            return null;
        }

        $vehicle = [];
        
        // Processar campos obrigatórios
        foreach ($this->requiredHeaders as $field) {
            $index = $headerMap[$field];
            $value = isset($data[$index]) ? trim($data[$index]) : '';
            
            if (empty($value)) {
                // Pular veículos com campos obrigatórios vazios
                return null;
            }
            
            $vehicle[$field] = $this->cleanFieldValue($field, $value);
        }

        // Processar campos opcionais
        foreach ($this->optionalHeaders as $field) {
            if (isset($headerMap[$field])) {
                $index = $headerMap[$field];
                $value = isset($data[$index]) ? trim($data[$index]) : '';
                $vehicle[$field] = $this->cleanFieldValue($field, $value);
            } else {
                // Definir valores padrão
                $vehicle[$field] = $this->getDefaultValue($field);
            }
        }

        // Adicionar dados processados
        $vehicle = $this->enrichVehicleData($vehicle);

        return $vehicle;
    }

    /**
     * Limpar e normalizar valores dos campos
     */
    protected function cleanFieldValue(string $field, string $value): mixed
    {
        if (empty($value) || $value === 'NA' || $value === 'N/A') {
            return $this->getDefaultValue($field);
        }

        switch ($field) {
            case 'make':
            case 'model':
                return $this->cleanStringField($value);
                
            case 'year':
                return $this->cleanYearField($value);
                
            case 'tire_size':
                return $this->cleanTireSizeField($value);
                
            case 'category':
                return $this->cleanCategoryField($value);
                
            // Campos de pressão
            case 'pressure_empty_front':
            case 'pressure_empty_rear':
            case 'pressure_light_front':
            case 'pressure_light_rear':
            case 'pressure_max_front':
            case 'pressure_max_rear':
            case 'pressure_spare':
                return $this->cleanPressureField($value);
                
            default:
                return trim($value);
        }
    }

    /**
     * Limpar campos de texto
     */
    protected function cleanStringField(string $value): string
    {
        // Remover espaços extras e normalizar
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value);
        
        // Primeira letra maiúscula
        return ucfirst(strtolower($value));
    }

    /**
     * Limpar campo de ano
     */
    protected function cleanYearField(string $value): int
    {
        $year = (int) preg_replace('/[^0-9]/', '', $value);
        
        // Validar range razoável
        if ($year < 1990 || $year > date('Y') + 2) {
            throw new \InvalidArgumentException("Ano inválido: {$value}");
        }
        
        return $year;
    }

    /**
     * Limpar campo de tamanho do pneu
     */
    protected function cleanTireSizeField(string $value): string
    {
        $value = trim(strtoupper($value));
        
        // Remover espaços desnecessários
        $value = preg_replace('/\s+/', ' ', $value);
        
        return $value;
    }

    /**
     * Limpar campo de categoria
     */
    protected function cleanCategoryField(string $value): string
    {
        $value = trim(strtolower($value));
        
        // Mapear categorias conhecidas
        $categoryMap = [
            'sedan' => 'sedan',
            'hatch' => 'hatchback',
            'hatchback' => 'hatchback',
            'suv' => 'suv',
            'pickup' => 'pickup',
            'moto' => 'motorcycle',
            'motocicleta' => 'motorcycle',
            'motorcycle' => 'motorcycle',
            'van' => 'van',
            'comercial' => 'commercial'
        ];
        
        return $categoryMap[$value] ?? $value;
    }

    /**
     * Limpar campos de pressão
     */
    protected function cleanPressureField(string $value): ?float
    {
        if (empty($value) || $value === 'NA' || $value === 'N/A') {
            return null;
        }
        
        // Extrair apenas números e ponto decimal
        $pressure = preg_replace('/[^0-9.]/', '', $value);
        $pressure = (float) $pressure;
        
        // Validar range razoável (10-60 PSI)
        if ($pressure < 10 || $pressure > 60) {
            return null;
        }
        
        return round($pressure, 1);
    }

    /**
     * Obter valor padrão para campo
     */
    protected function getDefaultValue(string $field): mixed
    {
        $defaults = [
            'tire_size' => '185/65 R15',
            'pressure_empty_front' => 30,
            'pressure_empty_rear' => 28,
            'pressure_light_front' => 32,
            'pressure_light_rear' => 30,
            'pressure_max_front' => 36,
            'pressure_max_rear' => 34,
            'pressure_spare' => 35,
            'category' => 'sedan'
        ];
        
        return $defaults[$field] ?? null;
    }

    /**
     * Enriquecer dados do veículo com informações processadas
     */
    protected function enrichVehicleData(array $vehicle): array
    {
        // Detectar se é motocicleta
        $vehicle['is_motorcycle'] = $this->isMotorcycle($vehicle);
        
        // Detectar tipo de veículo
        $vehicle['vehicle_type'] = $this->determineVehicleType($vehicle);
        
        // Categoria principal
        $vehicle['main_category'] = $this->determineMainCategory($vehicle);
        
        // Identificador único
        $vehicle['vehicle_identifier'] = "{$vehicle['make']} {$vehicle['model']} {$vehicle['year']}";
        
        // Dados estruturados do veículo
        $vehicle['vehicle_data'] = [
            'make' => $vehicle['make'],
            'model' => $vehicle['model'],
            'year' => $vehicle['year'],
            'tire_size' => $vehicle['tire_size'],
            'is_motorcycle' => $vehicle['is_motorcycle'],
            'vehicle_type' => $vehicle['vehicle_type'],
            'main_category' => $vehicle['main_category'],
            'pressure_display' => $this->formatPressureDisplay($vehicle),
            'pressure_empty_display' => $this->formatEmptyPressureDisplay($vehicle),
            'pressure_loaded_display' => $this->formatLoadedPressureDisplay($vehicle)
        ];
        
        return $vehicle;
    }

    /**
     * Verificar se é motocicleta
     */
    protected function isMotorcycle(array $vehicle): bool
    {
        // Verificar categoria
        if (in_array($vehicle['category'] ?? '', ['motorcycle', 'moto', 'motocicleta'])) {
            return true;
        }
        
        // Verificar tamanho do pneu (padrões típicos de moto)
        $tireSize = $vehicle['tire_size'] ?? '';
        if (preg_match('/\d{2,3}\/\d{2}-\d{2}/', $tireSize) && 
            (strpos($tireSize, 'dianteiro') !== false || strpos($tireSize, 'traseiro') !== false)) {
            return true;
        }
        
        return false;
    }

    /**
     * Determinar tipo de veículo
     */
    protected function determineVehicleType(array $vehicle): string
    {
        if ($vehicle['is_motorcycle']) {
            return 'motorcycle';
        }
        
        $category = $vehicle['category'] ?? '';
        
        $typeMap = [
            'sedan' => 'car',
            'hatchback' => 'car',
            'suv' => 'suv',
            'pickup' => 'pickup',
            'van' => 'van',
            'commercial' => 'commercial'
        ];
        
        return $typeMap[$category] ?? 'car';
    }

    /**
     * Determinar categoria principal
     */
    protected function determineMainCategory(array $vehicle): string
    {
        if ($vehicle['is_motorcycle']) {
            return 'Motocicletas';
        }
        
        $categoryMap = [
            'sedan' => 'Sedans',
            'hatchback' => 'Hatchbacks',
            'suv' => 'SUVs',
            'pickup' => 'Picapes',
            'van' => 'Vans',
            'commercial' => 'Comerciais'
        ];
        
        $category = $vehicle['category'] ?? 'sedan';
        return $categoryMap[$category] ?? 'Carros';
    }

    /**
     * Formatar exibição de pressão geral
     */
    protected function formatPressureDisplay(array $vehicle): string
    {
        $front = $vehicle['pressure_empty_front'] ?? 30;
        $rear = $vehicle['pressure_empty_rear'] ?? 28;
        
        if ($vehicle['is_motorcycle']) {
            return "Dianteiro: {$front} PSI / Traseiro: {$rear} PSI";
        }
        
        return "Dianteiros: {$front} PSI / Traseiros: {$rear} PSI";
    }

    /**
     * Formatar pressão para veículo vazio
     */
    protected function formatEmptyPressureDisplay(array $vehicle): string
    {
        $front = $vehicle['pressure_empty_front'] ?? 30;
        $rear = $vehicle['pressure_empty_rear'] ?? 28;
        return "{$front}/{$rear} PSI";
    }

    /**
     * Formatar pressão para veículo carregado
     */
    protected function formatLoadedPressureDisplay(array $vehicle): string
    {
        $front = $vehicle['pressure_max_front'] ?? 36;
        $rear = $vehicle['pressure_max_rear'] ?? 34;
        return "{$front}/{$rear} PSI";
    }

    /**
     * Aplicar filtros aos veículos
     */
    public function applyFilters(Collection $vehicles, array $filters): Collection
    {
        return $vehicles->filter(function ($vehicle) use ($filters) {
            // Filtro por marca
            if (!empty($filters['make'])) {
                if (strcasecmp($vehicle['make'], $filters['make']) !== 0) {
                    return false;
                }
            }
            
            // Filtro por categoria
            if (!empty($filters['category'])) {
                if (strcasecmp($vehicle['category'] ?? '', $filters['category']) !== 0) {
                    return false;
                }
            }
            
            // Filtro por tipo de veículo
            if (!empty($filters['vehicle_type'])) {
                if ($vehicle['vehicle_type'] !== $filters['vehicle_type']) {
                    return false;
                }
            }
            
            // Filtro por faixa de anos
            if (!empty($filters['year_from'])) {
                if ($vehicle['year'] < $filters['year_from']) {
                    return false;
                }
            }
            
            if (!empty($filters['year_to'])) {
                if ($vehicle['year'] > $filters['year_to']) {
                    return false;
                }
            }
            
            // Filtro de dados de pressão obrigatórios
            if (!empty($filters['require_tire_pressure'])) {
                if (empty($vehicle['pressure_empty_front']) || empty($vehicle['pressure_empty_rear'])) {
                    return false;
                }
            }
            
            return true;
        });
    }

    /**
     * Remover veículos duplicados
     */
    public function removeDuplicates(Collection $vehicles): Collection
    {
        return $vehicles->unique(function ($vehicle) {
            return $vehicle['vehicle_identifier'];
        })->values();
    }

    /**
     * Validar dados essenciais dos veículos
     */
    public function validateVehicleData(Collection $vehicles): Collection
    {
        return $vehicles->filter(function ($vehicle) {
            // Campos obrigatórios não podem estar vazios
            if (empty($vehicle['make']) || empty($vehicle['model']) || empty($vehicle['year'])) {
                return false;
            }
            
            // Ano deve ser válido
            if ($vehicle['year'] < 1990 || $vehicle['year'] > date('Y') + 2) {
                return false;
            }
            
            // Deve ter pelo menos pressões básicas
            if (empty($vehicle['pressure_empty_front']) || empty($vehicle['pressure_empty_rear'])) {
                return false;
            }
            
            return true;
        })->values();
    }

    /**
     * Obter estatísticas dos veículos
     */
    public function getStatistics(Collection $vehicles): array
    {
        $total = $vehicles->count();
        
        $byMake = $vehicles->groupBy('make')->map->count()->sortDesc();
        $byCategory = $vehicles->groupBy('main_category')->map->count()->sortDesc();
        $byVehicleType = $vehicles->groupBy('vehicle_type')->map->count()->sortDesc();
        $byYear = $vehicles->groupBy('year')->map->count()->sortDesc();
        
        // Estatísticas de pressão
        $withCompleteData = $vehicles->filter(function ($vehicle) {
            return !empty($vehicle['pressure_empty_front']) && 
                   !empty($vehicle['pressure_empty_rear']) &&
                   !empty($vehicle['pressure_max_front']) && 
                   !empty($vehicle['pressure_max_rear']);
        })->count();
        
        $motorcycles = $vehicles->where('is_motorcycle', true)->count();
        $cars = $total - $motorcycles;
        
        return [
            'total' => $total,
            'motorcycles' => $motorcycles,
            'cars' => $cars,
            'with_complete_pressure_data' => $withCompleteData,
            'by_make' => $byMake->toArray(),
            'by_category' => $byCategory->toArray(),
            'by_vehicle_type' => $byVehicleType->toArray(),
            'by_year' => $byYear->toArray(),
            'year_range' => [
                'min' => $vehicles->min('year'),
                'max' => $vehicles->max('year')
            ]
        ];
    }

    /**
     * Criar lotes de veículos para processamento
     */
    public function createBatches(Collection $vehicles, int $batchSize): Collection
    {
        $batches = collect();
        $chunks = $vehicles->chunk($batchSize);
        
        foreach ($chunks as $index => $chunk) {
            $batchId = 'batch_' . date('Ymd_His') . '_' . ($index + 1);
            
            $batches->push([
                'batch_id' => $batchId,
                'vehicles' => $chunk,
                'count' => $chunk->count(),
                'created_at' => now()
            ]);
        }
        
        return $batches;
    }

    /**
     * Validar estrutura do CSV
     */
    public function validateCsvStructure(string $csvPath): array
    {
        $errors = [];
        
        if (!file_exists($csvPath)) {
            $errors[] = "Arquivo não encontrado: {$csvPath}";
            return $errors;
        }
        
        if (!is_readable($csvPath)) {
            $errors[] = "Arquivo não é legível: {$csvPath}";
            return $errors;
        }
        
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $errors[] = "Não foi possível abrir o arquivo: {$csvPath}";
            return $errors;
        }
        
        try {
            // Verificar cabeçalho
            $headers = fgetcsv($handle);
            if ($headers === false) {
                $errors[] = "Não foi possível ler o cabeçalho do CSV";
                return $errors;
            }
            
            // Normalizar cabeçalhos
            $headers = array_map('strtolower', $headers);
            
            // Verificar cabeçalhos obrigatórios
            foreach ($this->requiredHeaders as $required) {
                if (!in_array($required, $headers)) {
                    $errors[] = "Cabeçalho obrigatório não encontrado: {$required}";
                }
            }
            
            // Verificar se há dados
            $firstLine = fgetcsv($handle);
            if ($firstLine === false) {
                $errors[] = "CSV não contém dados além do cabeçalho";
            }
            
        } finally {
            fclose($handle);
        }
        
        return $errors;
    }

    /**
     * Obter informações sobre o CSV
     */
    public function getCsvInfo(string $csvPath): array
    {
        $info = [
            'file_path' => $csvPath,
            'file_size' => 0,
            'file_size_human' => '0 B',
            'total_lines' => 0,
            'headers' => [],
            'estimated_vehicles' => 0,
            'last_modified' => null
        ];
        
        if (!file_exists($csvPath)) {
            return $info;
        }
        
        $info['file_size'] = filesize($csvPath);
        $info['file_size_human'] = $this->formatBytes($info['file_size']);
        $info['last_modified'] = date('Y-m-d H:i:s', filemtime($csvPath));
        
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            return $info;
        }
        
        try {
            // Ler cabeçalho
            $headers = fgetcsv($handle);
            if ($headers !== false) {
                $info['headers'] = $headers;
            }
            
            // Contar linhas
            $lineCount = 0;
            while (fgetcsv($handle) !== false) {
                $lineCount++;
            }
            
            $info['total_lines'] = $lineCount + 1; // +1 para o cabeçalho
            $info['estimated_vehicles'] = $lineCount;
            
        } finally {
            fclose($handle);
        }
        
        return $info;
    }

    /**
     * Formatar bytes em formato legível
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Exportar veículos processados para CSV
     */
    public function exportToCsv(Collection $vehicles, string $outputPath): int
    {
        $handle = fopen($outputPath, 'w');
        if ($handle === false) {
            throw new \RuntimeException("Não foi possível criar arquivo: {$outputPath}");
        }
        
        try {
            // Escrever cabeçalho
            if ($vehicles->isNotEmpty()) {
                $firstVehicle = $vehicles->first();
                fputcsv($handle, array_keys($firstVehicle));
                
                // Escrever dados
                foreach ($vehicles as $vehicle) {
                    fputcsv($handle, $vehicle);
                }
            }
            
        } finally {
            fclose($handle);
        }
        
        return $vehicles->count();
    }

    /**
     * Obter veículos por critérios específicos
     */
    public function getVehiclesByCriteria(Collection $vehicles, array $criteria): Collection
    {
        return $vehicles->filter(function ($vehicle) use ($criteria) {
            foreach ($criteria as $field => $value) {
                if (isset($vehicle[$field])) {
                    if (is_array($value)) {
                        if (!in_array($vehicle[$field], $value)) {
                            return false;
                        }
                    } else {
                        if ($vehicle[$field] != $value) {
                            return false;
                        }
                    }
                }
            }
            return true;
        });
    }

    /**
     * Validar consistência de dados de pressão
     */
    public function validatePressureConsistency(array $vehicle): array
    {
        $errors = [];
        
        $emptyFront = $vehicle['pressure_empty_front'] ?? 0;
        $emptyRear = $vehicle['pressure_empty_rear'] ?? 0;
        $maxFront = $vehicle['pressure_max_front'] ?? 0;
        $maxRear = $vehicle['pressure_max_rear'] ?? 0;
        
        // Pressão máxima deve ser maior que vazio
        if ($maxFront > 0 && $emptyFront > 0 && $maxFront <= $emptyFront) {
            $errors[] = "Pressão máxima dianteira deve ser maior que vazio";
        }
        
        if ($maxRear > 0 && $emptyRear > 0 && $maxRear <= $emptyRear) {
            $errors[] = "Pressão máxima traseira deve ser maior que vazio";
        }
        
        // Pressões devem estar dentro de ranges razoáveis
        if ($emptyFront > 0 && ($emptyFront < 15 || $emptyFront > 50)) {
            $errors[] = "Pressão dianteira fora do range razoável (15-50 PSI)";
        }
        
        if ($emptyRear > 0 && ($emptyRear < 15 || $emptyRear > 50)) {
            $errors[] = "Pressão traseira fora do range razoável (15-50 PSI)";
        }
        
        return $errors;
    }

    /**
     * Limpar e otimizar collection de veículos
     */
    public function optimizeVehicleCollection(Collection $vehicles): Collection
    {
        return $vehicles
            ->filter(function ($vehicle) {
                // Remover veículos com dados inválidos
                return !empty($vehicle['make']) && 
                       !empty($vehicle['model']) && 
                       !empty($vehicle['year']) &&
                       $vehicle['year'] >= 1990 &&
                       $vehicle['year'] <= date('Y') + 2;
            })
            ->map(function ($vehicle) {
                // Limpar campos desnecessários
                unset($vehicle['_raw_data']);
                return $vehicle;
            })
            ->values(); // Reindexar
    }
}
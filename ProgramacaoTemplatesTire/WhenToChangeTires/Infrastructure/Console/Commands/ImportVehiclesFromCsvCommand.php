<?php

namespace Src\ContentGeneration\WhenToChangeTires\Infrastructure\Console\Commands;

use Src\ContentGeneration\WhenToChangeTires\Infrastructure\Services\VehicleDataProcessorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportVehiclesFromCsvCommand extends Command
{
    protected $signature = 'when-to-change-tires:import-vehicles 
                          {csv_path=todos_veiculos.csv : Caminho para o arquivo CSV}
                          {--filter-category= : Filtrar por categoria específica}
                          {--filter-make= : Filtrar por marca específica}
                          {--filter-vehicle-type= : Filtrar por tipo (car, motorcycle)}
                          {--year-from= : Filtrar a partir do ano}
                          {--year-to= : Filtrar até o ano}
                          {--batch-size=50 : Tamanho dos lotes}
                          {--validate-only : Apenas validar dados sem processar}
                          {--show-stats : Mostrar estatísticas detalhadas}';

    protected $description = 'Importa dados de veículos do CSV todos_veiculos.csv e processa para geração de artigos';

    public function __construct(
        protected VehicleDataProcessorService $vehicleProcessor
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $csvPath = $this->argument('csv_path');

        $this->info("🚀 Iniciando importação de veículos do CSV: {$csvPath}");

        try {
            // Importar veículos do CSV
            $this->info("📥 Importando dados do CSV...");
            $vehicles = $this->vehicleProcessor->importFromCsv($csvPath);

            if ($vehicles->isEmpty()) {
                $this->error("❌ Nenhum veículo encontrado no CSV");
                return 1;
            }

            $this->info("✅ {$vehicles->count()} veículos importados com sucesso");

            // Aplicar filtros se especificados
            $filters = $this->getFilters();
            if (!empty($filters)) {
                $this->info("🔍 Aplicando filtros...");
                $filteredVehicles = $this->vehicleProcessor->filterVehicles($vehicles, $filters);
                $this->info("📊 {$filteredVehicles->count()} veículos após filtros");
                $vehicles = $filteredVehicles;
            }

            // Mostrar estatísticas se solicitado
            if ($this->option('show-stats')) {
                $this->showStatistics($vehicles);
            }

            // Validar dados se solicitado
            if ($this->option('validate-only')) {
                $this->validateVehicles($vehicles);
                return 0;
            }

            // Obter veículos prontos para geração
            $readyVehicles = $this->vehicleProcessor->getVehiclesReadyForGeneration($vehicles);
            $this->info("✅ {$readyVehicles->count()} veículos prontos para geração de artigos");

            if ($readyVehicles->isEmpty()) {
                $this->warn("⚠️ Nenhum veículo passou na validação para geração de artigos");
                return 1;
            }

            // Criar lotes para processamento
            $batchSize = (int) $this->option('batch-size');
            $batches = $this->vehicleProcessor->createBatches($readyVehicles, $batchSize);

            $this->info("📦 Criados {$batches->count()} lotes para processamento");

            // Mostrar resumo dos lotes
            $this->table(
                ['Lote', 'Veículos', 'Primeiro Veículo', 'Último Veículo'],
                $batches->map(function ($batch, $index) {
                    $vehicles = collect($batch['vehicles']);
                    $first = $vehicles->first();
                    $last = $vehicles->last();

                    return [
                        $batch['batch_id'],
                        $batch['count'],
                        $first->getVehicleIdentifier(),
                        $last->getVehicleIdentifier()
                    ];
                })->toArray()
            );

            $this->info("🎯 Use o próximo comando para gerar artigos:");
            $this->line("php artisan when-to-change-tires:generate-initial-articles");

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erro durante importação: " . $e->getMessage());
            Log::error("Erro ImportVehiclesFromCsvCommand: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    protected function getFilters(): array
    {
        $filters = [];

        if ($category = $this->option('filter-category')) {
            $filters['category'] = $category;
        }

        if ($make = $this->option('filter-make')) {
            $filters['make'] = $make;
        }

        if ($vehicleType = $this->option('filter-vehicle-type')) {
            $filters['vehicle_type'] = $vehicleType;
        }

        if ($yearFrom = $this->option('year-from')) {
            $filters['year_from'] = (int) $yearFrom;
        }

        if ($yearTo = $this->option('year-to')) {
            $filters['year_to'] = (int) $yearTo;
        }

        $filters['require_tire_pressure'] = true; // Sempre exigir dados de pressão

        return $filters;
    }

    protected function showStatistics($vehicles): void
    {
        $this->info("📊 ESTATÍSTICAS DOS VEÍCULOS IMPORTADOS");

        $stats = $this->vehicleProcessor->getStatistics($vehicles);

        // Total
        $this->line("Total de veículos: " . $stats['total_vehicles']);
        $this->line("");

        // Por marca (top 10)
        $this->line("🏭 Top 10 Marcas:");
        $topMakes = array_slice($stats['by_make'], 0, 10, true);
        foreach ($topMakes as $make => $count) {
            $this->line("  {$make}: {$count} veículos");
        }
        $this->line("");

        // Por categoria
        $this->line("📂 Por Categoria:");
        foreach ($stats['by_category'] as $category => $count) {
            $this->line("  {$category}: {$count} veículos");
        }
        $this->line("");

        // Por tipo de veículo
        $this->line("🚗 Por Tipo:");
        foreach ($stats['by_vehicle_type'] as $type => $count) {
            $this->line("  {$type}: {$count} veículos");
        }
        $this->line("");

        // Faixas de pressão
        $this->line("🔧 Faixas de Pressão (PSI):");
        foreach ($stats['pressure_ranges'] as $type => $range) {
            if ($range['min'] !== null && $range['max'] !== null) {
                $this->line("  {$type}: {$range['min']} - {$range['max']} PSI");
            }
        }
        $this->line("");

        // Top óleos
        $this->line("🛢️ Top 10 Tipos de Óleo:");
        $topOils = array_slice($stats['oil_types'], 0, 10, true);
        foreach ($topOils as $oil => $count) {
            $this->line("  {$oil}: {$count} veículos");
        }
    }

    protected function validateVehicles($vehicles): void
    {
        $this->info("🔍 VALIDANDO DADOS DOS VEÍCULOS");

        $validCount = 0;
        $issueCount = 0;
        $allIssues = [];

        foreach ($vehicles as $vehicle) {
            $issues = $this->vehicleProcessor->validateVehicleData($vehicle);

            if (empty($issues)) {
                $validCount++;
            } else {
                $issueCount++;
                $allIssues[$vehicle->getVehicleIdentifier()] = $issues;
            }
        }

        $this->info("✅ {$validCount} veículos válidos");
        $this->info("⚠️ {$issueCount} veículos com problemas");

        if (!empty($allIssues) && $this->option('verbose')) {
            $this->line("");
            $this->warn("PROBLEMAS ENCONTRADOS:");

            foreach ($allIssues as $vehicleId => $issues) {
                $this->line("• {$vehicleId}:");
                foreach ($issues as $issue) {
                    $this->line("  - {$issue}");
                }
            }
        }
    }
}

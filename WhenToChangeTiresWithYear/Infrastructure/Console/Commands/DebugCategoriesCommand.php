<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\VehicleDataProcessorService;

class DebugCategoriesCommand extends Command
{
    protected $signature = 'when-to-change-tires:debug-categories';
    protected $description = 'Debug das categorias de veículos';

    public function __construct(
        private VehicleDataProcessorService $processorService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info("🔍 Debugando categorias de veículos...");

        // Importar veículos
        $vehicles = $this->processorService->importFromCsv();
        
        $this->info("📊 Total de veículos importados: " . $vehicles->count());

        // Analisar categorias únicas
        $categoriesOriginal = $vehicles->pluck('category')->unique()->sort()->values();
        $categoriesMain = $vehicles->map(fn($v) => $v->getMainCategory())->unique()->sort()->values();
        $vehicleTypes = $vehicles->map(fn($v) => $v->getVehicleType())->unique()->sort()->values();

        $this->line("");
        $this->info("📋 CATEGORIAS ORIGINAIS DO CSV:");
        foreach ($categoriesOriginal as $cat) {
            $this->line("  • {$cat}");
        }

        $this->line("");
        $this->info("📋 CATEGORIAS PRINCIPAIS (getMainCategory):");
        foreach ($categoriesMain as $cat) {
            $this->line("  • {$cat}");
        }

        $this->line("");
        $this->info("📋 TIPOS DE VEÍCULO (getVehicleType):");
        foreach ($vehicleTypes as $type) {
            $this->line("  • {$type}");
        }

        // Mostrar alguns exemplos detalhados
        $this->line("");
        $this->info("🔍 EXEMPLOS DETALHADOS (primeiros 10):");
        
        $vehicles->take(10)->each(function($vehicle) {
            $debug = $vehicle->debugCategory();
            $this->line("  {$vehicle->make} {$vehicle->model} {$vehicle->year}:");
            $this->line("    Original: {$debug['original_category']}");
            $this->line("    Main: {$debug['main_category']}");
            $this->line("    Type: {$debug['vehicle_type']}");
            $this->line("    Flags: " . 
                ($debug['is_motorcycle'] ? 'MOTO ' : '') .
                ($debug['is_car'] ? 'CAR ' : '') .
                ($debug['is_electric'] ? 'ELECTRIC ' : '') .
                ($debug['is_hybrid'] ? 'HYBRID' : '')
            );
            $this->line("");
        });

        // Estatísticas por categoria
        $this->line("");
        $this->info("📊 CONTAGEM POR CATEGORIA ORIGINAL:");
        $categoryStats = $vehicles->groupBy('category')->map->count()->sortDesc();
        foreach ($categoryStats as $category => $count) {
            $this->line("  {$category}: {$count}");
        }

        return 0;
    }
}
<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Repositories\TireChangeArticleRepository;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\VehicleDataProcessorService;

class DebugExistingArticlesCommand extends Command
{
    protected $signature = 'when-to-change-tires:debug-existing';
    protected $description = 'Debug dos artigos existentes no banco';

    public function __construct(
        private TireChangeArticleRepository $repository,
        private VehicleDataProcessorService $processorService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info("🔍 Debugando artigos existentes...");

        // 1. Analisar artigos no banco
        $analysis = $this->repository->debugExistingArticles();
        
        $this->info("📊 ARTIGOS NO BANCO:");
        $this->line("  Total: {$analysis['total_existing']}");
        
        $this->line("\n📊 DISTRIBUIÇÃO POR ANO:");
        foreach ($analysis['by_year'] as $year => $count) {
            $this->line("  {$year}: {$count} artigos");
        }

        // 2. Verificar problemas de slug
        if (!empty($analysis['slug_patterns'])) {
            $this->line("\n⚠️ PROBLEMAS DE SLUG ENCONTRADOS:");
            foreach (array_slice($analysis['slug_patterns'], 0, 10) as $problem) {
                $this->line("  {$problem['vehicle']}:");
                $this->line("    Atual: {$problem['current_slug']}");
                $this->line("    Esperado: {$problem['expected_slug']}");
                $this->line("");
            }
        }

        // 3. Verificar duplicatas potenciais
        if (!empty($analysis['potential_duplicates'])) {
            $this->line("\n🔄 VEÍCULOS COM MÚLTIPLOS ANOS:");
            foreach (array_slice($analysis['potential_duplicates'], 0, 5, true) as $vehicle => $years) {
                $this->line("  {$vehicle}:");
                foreach ($years as $yearData) {
                    $this->line("    {$yearData['year']}: {$yearData['slug']}");
                }
                $this->line("");
            }
        }

        // 4. Comparar com CSV
        $this->line("\n🔍 COMPARANDO COM CSV...");
        $vehicles = $this->processorService->importFromCsv();
        $uniqueVehicles = $this->processorService->getUniqueVehicleCombinations($vehicles);
        
        $this->line("  Veículos únicos no CSV: {$uniqueVehicles->count()}");
        $this->line("  Artigos no banco: {$analysis['total_existing']}");
        $this->line("  Diferença: " . ($uniqueVehicles->count() - $analysis['total_existing']));

        // 5. Testar alguns veículos específicos
        $this->line("\n🧪 TESTANDO VERIFICAÇÃO DE EXISTÊNCIA:");
        $sampleVehicles = $uniqueVehicles->take(5);
        
        foreach ($sampleVehicles as $vehicle) {
            $existsBySlug = $this->repository->existsForVehicle($vehicle->make, $vehicle->model, $vehicle->year);
            $existsByModel = $this->repository->existsForVehicleModel($vehicle->make, $vehicle->model, $vehicle->year);
            $expectedSlug = \Illuminate\Support\Str::slug("quando-trocar-pneus-{$vehicle->make}-{$vehicle->model}-{$vehicle->year}");
            
            $this->line("  {$vehicle->make} {$vehicle->model} {$vehicle->year}:");
            $this->line("    Slug esperado: {$expectedSlug}");
            $this->line("    Existe (por slug): " . ($existsBySlug ? 'SIM' : 'NÃO'));
            $this->line("    Existe (por model): " . ($existsByModel ? 'SIM' : 'NÃO'));
            $this->line("");
        }

        return 0;
    }
}
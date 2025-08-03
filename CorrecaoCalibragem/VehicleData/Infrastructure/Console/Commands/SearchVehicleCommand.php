<?php

namespace Src\VehicleData\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * Command para buscar veículos na base de dados
 * 
 * Permite buscar por marca, modelo, ano e outros critérios
 */
class SearchVehicleCommand extends Command
{
    protected $signature = 'vehicle-data:search
                           {--make= : Marca do veículo}
                           {--model= : Modelo do veículo}
                           {--year= : Ano do veículo}
                           {--category= : Categoria do veículo}
                           {--term= : Termo de busca livre}
                           {--suggest= : Sugestões baseadas em termo}
                           {--limit=10 : Limite de resultados}';

    protected $description = 'Buscar veículos na base de dados';

    /**
     * Executar busca
     */
    public function handle(): ?int
    {
        $make = $this->option('make');
        $model = $this->option('model');
        $year = $this->option('year');
        $category = $this->option('category');
        $term = $this->option('term');
        $suggest = $this->option('suggest');
        $limit = (int) $this->option('limit');

        try {
            if ($suggest) {
                $this->handleSuggestions($suggest);
            } elseif ($term) {
                $this->handleFuzzySearch($term, $limit);
            } elseif ($make && $model) {
                $this->handleSpecificSearch($make, $model, $year ? (int) $year : null);
            } elseif ($make) {
                $this->handleMakeSearch($make, compact('category'), $limit);
            } else {
                $this->showUsageExamples();
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ ERRO: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Busca específica por marca e modelo
     */
    protected function handleSpecificSearch(string $make, string $model, ?int $year): void
    {
        $this->info("🔍 Buscando: {$make} {$model}" . ($year ? " {$year}" : ""));

        if ($year) {
            // Busca exata
            $vehicle = VehicleData::findVehicle($make, $model, $year);
            
            if ($vehicle) {
                $this->displayVehicleDetails($vehicle);
            } else {
                $this->warn("❌ Veículo não encontrado: {$make} {$model} {$year}");
                $this->suggestAlternatives($make, $model);
            }
        } else {
            // Buscar todos os anos disponíveis
            $vehicles = VehicleData::findAllYears($make, $model);
            
            if ($vehicles->isNotEmpty()) {
                $this->info("✅ Encontrados {$vehicles->count()} veículo(s):");
                $this->displayVehicleList($vehicles);
            } else {
                $this->warn("❌ Nenhum veículo encontrado para: {$make} {$model}");
                $this->suggestAlternatives($make, $model);
            }
        }
    }

    /**
     * Busca por marca com filtros
     */
    protected function handleMakeSearch(string $make, array $filters, int $limit): void
    {
        $this->info("🔍 Buscando veículos da marca: {$make}");

        $vehicles = VehicleData::findByMakeWithFilters($make, $filters)->take($limit);

        if ($vehicles->isNotEmpty()) {
            $this->info("✅ Encontrados {$vehicles->count()} veículo(s):");
            $this->displayVehicleList($vehicles);
        } else {
            $this->warn("❌ Nenhum veículo encontrado para a marca: {$make}");
        }
    }

    /**
     * Busca fuzzy por termo livre
     */
    protected function handleFuzzySearch(string $term, int $limit): void
    {
        $this->info("🔍 Busca livre: {$term}");

        $vehicles = VehicleData::fuzzySearch($term, $limit);

        if ($vehicles->isNotEmpty()) {
            $this->info("✅ Encontrados {$vehicles->count()} veículo(s):");
            $this->displayVehicleList($vehicles);
        } else {
            $this->warn("❌ Nenhum veículo encontrado para: {$term}");
        }
    }

    /**
     * Mostrar sugestões
     */
    protected function handleSuggestions(string $term): void
    {
        $this->info("💡 Sugestões para: {$term}");

        $suggestions = VehicleData::suggest($term);

        if (!empty($suggestions['makes'])) {
            $this->line("\n🏭 Marcas:");
            foreach ($suggestions['makes'] as $make) {
                $this->line("   • {$make}");
            }
        }

        if (!empty($suggestions['models'])) {
            $this->line("\n🚗 Modelos:");
            foreach ($suggestions['models'] as $model) {
                $this->line("   • {$model}");
            }
        }

        if (!empty($suggestions['vehicles'])) {
            $this->line("\n🎯 Veículos:");
            foreach ($suggestions['vehicles'] as $vehicle) {
                $this->line("   • {$vehicle}");
            }
        }

        if (empty($suggestions['makes']) && empty($suggestions['models']) && empty($suggestions['vehicles'])) {
            $this->warn("❌ Nenhuma sugestão encontrada para: {$term}");
        }
    }

    /**
     * Sugerir alternativas quando não encontrar
     */
    protected function suggestAlternatives(string $make, string $model): void
    {
        $this->line("\n💡 Sugestões:");

        // Modelos similares da mesma marca
        $similar = VehicleData::findSimilarModels($make, $model);
        
        if ($similar->isNotEmpty()) {
            $this->line("\n🔄 Modelos similares:");
            foreach ($similar as $vehicle) {
                $this->line("   • {$vehicle->make} {$vehicle->model} {$vehicle->year}");
            }
        }

        // Sugestões por termo
        $suggestions = VehicleData::suggest($model);
        if (!empty($suggestions['models'])) {
            $this->line("\n📝 Modelos que contêm '{$model}':");
            foreach (array_slice($suggestions['models'], 0, 5) as $suggestedModel) {
                $this->line("   • {$suggestedModel}");
            }
        }
    }

    /**
     * Exibir detalhes de um veículo
     */
    protected function displayVehicleDetails(VehicleData $vehicle): void
    {
        $this->info("✅ Veículo encontrado:");
        $this->newLine();
        
        $this->line("🚗 <fg=cyan>{$vehicle->vehicle_full_name}</fg=cyan>");
        $this->line("📂 Categoria: {$vehicle->main_category}");
        $this->line("🎯 Segmento: {$vehicle->vehicle_segment}");
        $this->line("📊 Qualidade: {$vehicle->data_quality_score}/10");
        
        // Pressões
        if (!empty($vehicle->pressure_specifications)) {
            $specs = $vehicle->pressure_specifications;
            $this->line("\n🔧 Pressões:");
            $this->line("   • Dianteira: " . ($specs['pressure_light_front'] ?? 'N/A') . " PSI");
            $this->line("   • Traseira: " . ($specs['pressure_light_rear'] ?? 'N/A') . " PSI");
            if (!empty($specs['pressure_spare'])) {
                $this->line("   • Estepe: " . $specs['pressure_spare'] . " PSI");
            }
        }

        // Características
        $features = [];
        if ($vehicle->is_premium) $features[] = 'Premium';
        if ($vehicle->is_electric) $features[] = 'Elétrico';
        if ($vehicle->is_hybrid) $features[] = 'Híbrido';
        if ($vehicle->has_tpms) $features[] = 'TPMS';
        if ($vehicle->is_motorcycle) $features[] = 'Motocicleta';

        if (!empty($features)) {
            $this->line("\n✨ Características: " . implode(', ', $features));
        }

    }

    /**
     * Exibir lista de veículos
     */
    protected function displayVehicleList($vehicles): void
    {
        $this->newLine();

        foreach ($vehicles as $vehicle) {
            $quality = number_format($vehicle->data_quality_score ?? 0, 1);
            $features = [];
            
            if ($vehicle->is_premium) $features[] = 'Premium';
            if ($vehicle->is_electric) $features[] = 'EV';
            if ($vehicle->is_hybrid) $features[] = 'Hybrid';
            if ($vehicle->has_tpms) $features[] = 'TPMS';

            $featuresStr = !empty($features) ? ' [' . implode(', ', $features) . ']' : '';
            
            $this->line("• <fg=cyan>{$vehicle->make} {$vehicle->model} {$vehicle->year}</fg=cyan> " .
                       "({$vehicle->main_category}) " .
                       "<fg=yellow>Q:{$quality}</fg=yellow>" .
                       "<fg=green>{$featuresStr}</fg=green>");
        }
    }

    /**
     * Mostrar exemplos de uso
     */
    protected function showUsageExamples(): void
    {
        $this->info("🔍 EXEMPLOS DE USO:");
        $this->newLine();
        
        $this->line("📋 Busca específica:");
        $this->line("   php artisan vehicle-data:search --make=\"BMW\" --model=\"R 1250 RT\"");
        $this->line("   php artisan vehicle-data:search --make=\"BMW\" --model=\"R 1250 RT\" --year=2021");
        $this->newLine();
        
        $this->line("🏭 Por marca:");
        $this->line("   php artisan vehicle-data:search --make=\"Toyota\"");
        $this->line("   php artisan vehicle-data:search --make=\"BMW\" --category=\"motorcycle\"");
        $this->newLine();
        
        $this->line("🔍 Busca livre:");
        $this->line("   php artisan vehicle-data:search --term=\"BMW R1250\"");
        $this->line("   php artisan vehicle-data:search --term=\"Tesla Model\"");
        $this->newLine();
        
        $this->line("💡 Sugestões:");
        $this->line("   php artisan vehicle-data:search --suggest=\"BMW\"");
        $this->line("   php artisan vehicle-data:search --suggest=\"R1250\"");
    }
}
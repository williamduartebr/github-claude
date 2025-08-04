<?php

namespace Src\VehicleData\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * Command para limpeza e manutenção de dados de veículos
 * 
 * Remove duplicatas, dados órfãos e executa otimizações básicas
 * na collection vehicle_data
 */
class CleanVehicleDataCommand extends Command
{
    protected $signature = 'vehicle-data:clean
                           {--remove-duplicates : Remover registros duplicados}
                           {--remove-orphans : Remover registros órfãos sem artigos fonte}
                           {--normalize-data : Normalizar nomes de marcas e modelos}
                           {--dry-run : Executar sem fazer alterações}
                           {--all : Executar todas as operações de limpeza}';

    protected $description = 'Limpar e otimizar dados de veículos';

    protected int $duplicatesRemoved = 0;
    protected int $orphansRemoved = 0;
    protected int $recordsNormalized = 0;

    /**
     * Executar limpeza
     */
    public function handle(): ?int
    {
        $this->info('🧹 Iniciando limpeza de dados de veículos...');

        $removeDuplicates = $this->option('remove-duplicates');
        $removeOrphans = $this->option('remove-orphans');
        $normalizeData = $this->option('normalize-data');
        $isDryRun = $this->option('dry-run');
        $all = $this->option('all');

        if ($isDryRun) {
            $this->warn('⚠️  MODO DRY-RUN ATIVO - Nenhuma alteração será feita');
        }

        try {
            // Estatísticas iniciais
            $this->displayInitialStats();

            // Executar operações solicitadas
            if ($all || $removeDuplicates) {
                $this->removeDuplicateRecords($isDryRun);
            }

            if ($all || $removeOrphans) {
                $this->removeOrphanRecords($isDryRun);
            }

            if ($all || $normalizeData) {
                $this->normalizeVehicleData($isDryRun);
            }

            // Resultados finais
            $this->displayFinalResults();

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ ERRO: " . $e->getMessage());
            Log::error('CleanVehicleDataCommand failed', [
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    /**
     * Exibir estatísticas iniciais
     */
    protected function displayInitialStats(): void
    {
        $totalRecords = VehicleData::count();
        $this->info("\n📊 ESTATÍSTICAS INICIAIS:");
        $this->line("   📄 Total de registros: {$totalRecords}");

        // Contar órfãos
        $orphans = VehicleData::where(function ($query) {
            $query->whereNull('source_articles')
                  ->orWhere('source_articles', []);
        })->count();
        $this->line("   👻 Registros órfãos: {$orphans}");

        // Dados de qualidade
        $lowQuality = VehicleData::where('data_quality_score', '<', 6.0)->count();
        $this->line("   📉 Baixa qualidade (<6.0): {$lowQuality}");
    }

    /**
     * Remover registros duplicados (versão simplificada)
     */
    protected function removeDuplicateRecords(bool $isDryRun): void
    {
        $this->info("\n🔄 Removendo registros duplicados...");

        // Buscar duplicatas usando Eloquent
        $duplicateGroups = VehicleData::select('make', 'model', 'year')
            ->groupBy('make', 'model', 'year')
            ->havingRaw('count(*) > 1')
            ->get();

        if ($duplicateGroups->isEmpty()) {
            $this->info("   ✅ Nenhuma duplicata encontrada");
            return;
        }

        $this->line("   🔍 Encontrados {$duplicateGroups->count()} grupos de duplicatas");

        if ($isDryRun) {
            // Estimar quantos seriam removidos
            foreach ($duplicateGroups as $group) {
                $duplicates = VehicleData::where('make', $group->make)
                    ->where('model', $group->model)
                    ->where('year', $group->year)
                    ->count();
                $this->duplicatesRemoved += ($duplicates - 1);
            }
            return;
        }

        $bar = $this->output->createProgressBar($duplicateGroups->count());
        
        foreach ($duplicateGroups as $group) {
            // Buscar todos os duplicados deste grupo
            $duplicates = VehicleData::where('make', $group->make)
                ->where('model', $group->model)
                ->where('year', $group->year)
                ->orderByDesc('data_quality_score')
                ->orderByDesc('created_at')
                ->get();
            
            // Manter o primeiro (melhor qualidade), remover os outros
            $duplicates->skip(1)->each(function ($duplicate) {
                $duplicate->delete();
                $this->duplicatesRemoved++;
            });
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✅ {$this->duplicatesRemoved} duplicatas removidas");
    }

    /**
     * Remover registros órfãos
     */
    protected function removeOrphanRecords(bool $isDryRun): void
    {
        $this->info("\n👻 Removendo registros órfãos...");

        $orphans = VehicleData::where(function ($query) {
            $query->whereNull('source_articles')
                  ->orWhere('source_articles', [])
                  ->orWhere('source_articles', '[]');
        })->get();
        
        if ($orphans->isEmpty()) {
            $this->info("   ✅ Nenhum registro órfão encontrado");
            return;
        }

        $this->line("   🔍 Encontrados {$orphans->count()} registros órfãos");

        if ($isDryRun) {
            $this->orphansRemoved = $orphans->count();
            return;
        }

        $bar = $this->output->createProgressBar($orphans->count());
        
        foreach ($orphans as $orphan) {
            $orphan->delete();
            $this->orphansRemoved++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✅ {$this->orphansRemoved} registros órfãos removidos");
    }

    /**
     * Normalizar dados de veículos
     */
    protected function normalizeVehicleData(bool $isDryRun): void
    {
        $this->info("\n📝 Normalizando dados de veículos...");

        $vehicles = VehicleData::all();
        
        if ($vehicles->isEmpty()) {
            $this->info("   ✅ Nenhum registro para normalizar");
            return;
        }

        $bar = $this->output->createProgressBar($vehicles->count());
        
        foreach ($vehicles as $vehicle) {
            $updated = false;

            // Normalizar marca (primeira letra maiúscula)
            $normalizedMake = $this->normalizeMake($vehicle->make);
            if ($normalizedMake !== $vehicle->make) {
                $vehicle->make = $normalizedMake;
                $updated = true;
            }

            // Normalizar modelo
            $normalizedModel = $this->normalizeModel($vehicle->model);
            if ($normalizedModel !== $vehicle->model) {
                $vehicle->model = $normalizedModel;
                $updated = true;
            }

            // Verificar se categoria está correta
            $detectedCategory = $this->detectCategory($vehicle);
            if ($detectedCategory !== $vehicle->main_category) {
                $vehicle->main_category = $detectedCategory;
                $updated = true;
            }

            if ($updated && !$isDryRun) {
                $vehicle->save();
                $this->recordsNormalized++;
            } elseif ($updated) {
                $this->recordsNormalized++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✅ {$this->recordsNormalized} registros normalizados");
    }

    /**
     * Normalizar nome da marca
     */
    protected function normalizeMake(string $make): string
    {
        // Casos especiais conhecidos
        $knownMakes = [
            'bmw' => 'BMW',
            'byd' => 'BYD',
            'vw' => 'Volkswagen',
            'gm' => 'GM',
            'jac' => 'JAC',
            'gwm' => 'GWM',
            'mercedes-benz' => 'Mercedes-Benz',
            'land rover' => 'Land Rover',
            'harley-davidson' => 'Harley-Davidson',
        ];

        $makeLower = strtolower($make);
        
        if (isset($knownMakes[$makeLower])) {
            return $knownMakes[$makeLower];
        }

        // Normalização padrão: primeira letra maiúscula
        return ucfirst(strtolower($make));
    }

    /**
     * Normalizar nome do modelo
     */
    protected function normalizeModel(string $model): string
    {
        // Remover espaços extras
        $model = preg_replace('/\s+/', ' ', trim($model));
        
        // Casos especiais
        $specialCases = [
            '/\bev\b/i' => 'EV',
            '/\bhev\b/i' => 'HEV',
            '/\bphev\b/i' => 'PHEV',
            '/\bgt\b/i' => 'GT',
            '/\bgti\b/i' => 'GTI',
            '/\btdi\b/i' => 'TDI',
            '/\btsi\b/i' => 'TSI',
        ];

        foreach ($specialCases as $pattern => $replacement) {
            $model = preg_replace($pattern, $replacement, $model);
        }

        return $model;
    }

    /**
     * Detectar categoria baseada em marca/modelo (simplificado)
     */
    protected function detectCategory(VehicleData $vehicle): string
    {
        $make = strtolower($vehicle->make);
        $model = strtolower($vehicle->model);

        // Motocicletas
        $motorcycleBrands = [
            'honda', 'yamaha', 'suzuki', 'kawasaki', 'triumph', 
            'ducati', 'harley-davidson', 'bmw', 'ktm'
        ];
        
        if (in_array($make, $motorcycleBrands)) {
            // Verificar se é realmente moto pelo modelo
            $carModels = ['civic', 'accord', 'fit', 'city', 'hrv', 'crv'];
            foreach ($carModels as $carModel) {
                if (str_contains($model, $carModel)) {
                    return $vehicle->main_category; // Manter categoria atual se for carro Honda
                }
            }
            return VehicleData::CATEGORY_MOTORCYCLE;
        }

        // Elétricos
        if ($make === 'tesla' || 
            str_contains($model, 'electric') || 
            str_contains($model, 'ev') ||
            str_contains($model, 'leaf') ||
            str_contains($model, 'bolt')) {
            return VehicleData::CATEGORY_ELECTRIC;
        }

        // Pickups
        $pickupModels = ['hilux', 'ranger', 'amarok', 'l200', 's10', 'frontier'];
        foreach ($pickupModels as $pickupModel) {
            if (str_contains($model, $pickupModel)) {
                return VehicleData::CATEGORY_PICKUP;
            }
        }

        // Manter categoria atual se não detectar mudança
        return $vehicle->main_category;
    }

    /**
     * Exibir resultados finais
     */
    protected function displayFinalResults(): void
    {
        $this->info("\n📊 RESULTADOS DA LIMPEZA:");
        $this->line("   🗑️  Duplicatas removidas: {$this->duplicatesRemoved}");
        $this->line("   👻 Órfãos removidos: {$this->orphansRemoved}");
        $this->line("   📝 Registros normalizados: {$this->recordsNormalized}");

        // Estatísticas finais
        $finalCount = VehicleData::count();
        $this->line("   📄 Registros finais: {$finalCount}");

        $this->info("\n✅ Limpeza concluída com sucesso!");

        // Log dos resultados
        Log::info('Vehicle data cleanup completed', [
            'duplicates_removed' => $this->duplicatesRemoved,
            'orphans_removed' => $this->orphansRemoved,
            'records_normalized' => $this->recordsNormalized,
            'final_count' => $finalCount
        ]);
    }
}
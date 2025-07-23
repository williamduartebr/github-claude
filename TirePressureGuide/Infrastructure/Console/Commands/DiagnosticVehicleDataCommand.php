<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Command para diagnosticar problemas com vehicle_data
 */
class DiagnosticVehicleDataCommand extends Command
{
    protected $signature = 'tire-pressure:diagnostic-vehicle-data 
                           {--limit=10 : Número de registros para analisar}';

    protected $description = 'Diagnosticar problemas com dados de veículos';

    public function handle(): int
    {
        $this->info("🔍 DIAGNÓSTICO DOS DADOS DE VEÍCULOS");
        $this->info("=====================================");

        // 1. Estatísticas gerais
        $this->showGeneralStats();

        // 2. Artigos que precisam correção
        $this->showArticlesNeedingCorrection();

        // 3. Verificar dados duplicados/órfãos
        $this->checkForOrphanedData();

        // 4. Verificar templates
        $this->checkTemplateDistribution();

        return 0;
    }

    protected function showGeneralStats(): void
    {
        $this->info("\n📊 ESTATÍSTICAS GERAIS:");
        
        $total = TirePressureArticle::count();
        $withVehicleData = TirePressureArticle::whereNotNull('vehicle_data')->count();
        $needsCorrection = TirePressureArticle::query()->needsVehicleDataCorrection()->count();
        $alreadyCorrected = TirePressureArticle::where('vehicle_data_version', 'v2.1')->count();

        $this->line("• Total de artigos: {$total}");
        $this->line("• Com vehicle_data: {$withVehicleData}");
        $this->line("• Precisam correção: {$needsCorrection}");
        $this->line("• Já corrigidos: {$alreadyCorrected}");
    }

    protected function showArticlesNeedingCorrection(): void
    {
        $this->info("\n🔧 ARTIGOS QUE PRECISAM CORREÇÃO:");
        
        $limit = (int) $this->option('limit');
        
        $articles = TirePressureArticle::query()
            ->needsVehicleDataCorrection()
            ->whereNotNull('vehicle_data')
            ->limit($limit)
            ->get();

        if ($articles->isEmpty()) {
            $this->info("✅ Nenhum artigo precisa de correção!");
            return;
        }

        foreach ($articles as $article) {
            $vehicleData = $article->vehicle_data ?? [];
            $make = $vehicleData['make'] ?? 'N/A';
            $model = $vehicleData['model'] ?? 'N/A';
            $year = $vehicleData['year'] ?? 'N/A';
            
            $this->line("• ID: {$article->id}");
            $this->line("  Veículo: {$make} {$model} {$year}");
            $this->line("  Template: {$article->template_type}");
            $this->line("  Slug: {$article->slug}");
            $this->line("  Versão: " . ($article->vehicle_data_version ?? 'null'));
            
            // ✅ BUSCA CORRIGIDA: Usar valores exatos dos campos
            $siblings = TirePressureArticle::where('vehicle_data.make', $make)
                ->where('vehicle_data.model', $model)
                ->where('vehicle_data.year', $year)
                ->where('_id', '!=', $article->_id)  // ✅ Usar _id do MongoDB
                ->get();
                
            $this->line("  Irmãos encontrados: {$siblings->count()}");
            
            if ($siblings->count() > 0) {
                foreach ($siblings as $sibling) {
                    $this->line("    - {$sibling->template_type}: {$sibling->slug}");
                }
            } else {
                // ✅ DEBUG: Verificar se o problema é na busca
                $this->warn("    🔍 Debug: Buscando com outros critérios...");
                
                // Tentar busca mais ampla
                $allSameVehicle = TirePressureArticle::where('vehicle_data.make', $make)
                    ->where('vehicle_data.model', $model)
                    ->where('vehicle_data.year', $year)
                    ->get();
                    
                $this->line("    🔍 Total do mesmo veículo: {$allSameVehicle->count()}");
                
                foreach ($allSameVehicle as $same) {
                    $this->line("      - {$same->template_type}: {$same->slug} (ID: {$same->_id})");
                }
            }
            
            $this->line("");
        }
    }

    protected function checkForOrphanedData(): void
    {
        $this->info("\n🔍 VERIFICANDO DADOS ÓRFÃOS:");
        
        // Buscar artigos com dados de veículo mas sem irmãos
        $orphans = TirePressureArticle::query()
            ->needsVehicleDataCorrection()
            ->whereNotNull('vehicle_data')
            ->get()
            ->filter(function ($article) {
                $vehicleData = $article->vehicle_data ?? [];
                $make = $vehicleData['make'] ?? null;
                $model = $vehicleData['model'] ?? null;
                $year = $vehicleData['year'] ?? null;
                
                if (!$make || !$model || !$year) {
                    return true; // Dados incompletos = órfão
                }
                
                $siblings = TirePressureArticle::where('vehicle_data.make', $make)
                    ->where('vehicle_data.model', $model)
                    ->where('vehicle_data.year', $year)
                    ->count();
                    
                return $siblings === 1; // Só encontrou ele mesmo = órfão
            });

        if ($orphans->isEmpty()) {
            $this->info("✅ Nenhum dado órfão encontrado!");
            return;
        }

        $this->warn("⚠️  Encontrados {$orphans->count()} registros órfãos:");
        
        foreach ($orphans as $orphan) {
            $vehicleData = $orphan->vehicle_data ?? [];
            $make = $vehicleData['make'] ?? 'N/A';
            $model = $vehicleData['model'] ?? 'N/A';
            $year = $vehicleData['year'] ?? 'N/A';
            
            $this->line("• {$make} {$model} {$year} - Template: {$orphan->template_type}");
            $this->line("  ID: {$orphan->id}");
            $this->line("  Slug: {$orphan->slug}");
        }
    }

    protected function checkTemplateDistribution(): void
    {
        $this->info("\n📋 DISTRIBUIÇÃO POR TEMPLATE:");
        
        // Para MongoDB, usar método simples sem selectRaw
        $idealCount = TirePressureArticle::where('template_type', 'ideal')->count();
        $calibrationCount = TirePressureArticle::where('template_type', 'calibration')->count();
        $otherCount = TirePressureArticle::whereNotIn('template_type', ['ideal', 'calibration'])->count();
        
        $this->line("• ideal: {$idealCount} artigos");
        $this->line("• calibration: {$calibrationCount} artigos");
        
        if ($otherCount > 0) {
            $this->line("• outros: {$otherCount} artigos");
        }
        
        // Verificar correções necessárias por template
        $idealNeedsCorrection = TirePressureArticle::query()
            ->needsVehicleDataCorrection()
            ->where('template_type', 'ideal')
            ->count();
            
        $calibrationNeedsCorrection = TirePressureArticle::query()
            ->needsVehicleDataCorrection()
            ->where('template_type', 'calibration')
            ->count();
            
        $this->info("\n🔧 PRECISAM CORREÇÃO:");
        $this->line("• ideal: {$idealNeedsCorrection} artigos");
        $this->line("• calibration: {$calibrationNeedsCorrection} artigos");
        
        // ⚠️ DIAGNÓSTICO CRÍTICO
        if ($calibrationCount == 0) {
            $this->error("\n❌ PROBLEMA CRÍTICO DETECTADO:");
            $this->error("• Não há artigos com template 'calibration'!");
            $this->error("• Esperado: ~963 artigos de cada template");
            $this->error("• Atual: {$idealCount} ideal, {$calibrationCount} calibration");
            $this->warn("\n💡 POSSÍVEIS CAUSAS:");
            $this->warn("• Geração incompleta na primeira etapa");
            $this->warn("• Problema no campo template_type");
            $this->warn("• Dados importados incorretamente");
        }
    }
}
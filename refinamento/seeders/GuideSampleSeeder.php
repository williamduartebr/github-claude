<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * GuideSampleSeeder - CORRIGIDO
 * 
 * ✅ Busca veículos do MySQL PRIMEIRO
 * ✅ Cria guias COM FKs (vehicle_make_id, vehicle_model_id, vehicle_version_id)
 * ✅ Enriquece payload com dados reais de vehicle_fluid_specs
 */
class GuideSampleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Criando guias com relacionamento MySQL...');
        
        // Buscar categorias
        $categories = [
            'oleo' => GuideCategory::where('slug', 'oleo')->first(),
            'fluidos' => GuideCategory::where('slug', 'fluidos')->first(),
            'calibragem' => GuideCategory::where('slug', 'calibragem')->first(),
        ];
        
        // Veículos a processar
        $vehicles = [
            'toyota' => ['corolla', 'hilux'],
            'chevrolet' => ['onix', 's10'],
            'honda' => ['civic', 'hrv'],
        ];
        
        $years = [2022, 2023, 2024];
        $insertedCount = 0;
        
        foreach ($vehicles as $makeSlug => $models) {
            $make = VehicleMake::where('slug', $makeSlug)->first();
            
            if (!$make) {
                $this->command->warn("⚠️  Marca '{$makeSlug}' não encontrada");
                continue;
            }
            
            foreach ($models as $modelSlug) {
                $model = VehicleModel::where('slug', $modelSlug)
                    ->where('make_id', $make->id)
                    ->first();
                
                if (!$model) {
                    $this->command->warn("⚠️  Modelo '{$modelSlug}' não encontrado");
                    continue;
                }
                
                foreach ($years as $year) {
                    // Buscar versão no MySQL
                    $version = VehicleVersion::where('model_id', $model->id)
                        ->where('year', $year)
                        ->first();
                    
                    if (!$version) {
                        $this->command->warn("⚠️  Versão {$makeSlug} {$modelSlug} {$year} não encontrada");
                        continue;
                    }
                    
                    // Criar guia para cada categoria
                    foreach ($categories as $catSlug => $category) {
                        if (!$category) continue;
                        
                        $guide = $this->createGuide($make, $model, $version, $category, $catSlug, $year);
                        
                        if ($guide) {
                            $insertedCount++;
                            $this->command->info("  ✅ {$make->name} {$model->name} {$year} - {$category->name}");
                        }
                    }
                }
            }
        }
        
        $this->command->info("✅ {$insertedCount} guias inseridos com FKs!");
    }
    
    private function createGuide($make, $model, $version, $category, $categorySlug, $year)
    {
        $slug = "{$categorySlug}-{$make->slug}-{$model->slug}-{$year}";
        
        // Verificar se já existe
        if (Guide::where('slug', $slug)->exists()) {
            return null;
        }
        
        return Guide::create([
            // ✅ FKs para MySQL
            'guide_category_id' => $category->_id,
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'vehicle_version_id' => $version->id,
            
            // Display (denormalizado)
            'make' => $make->name,
            'make_slug' => $make->slug,
            'model' => $model->name,
            'model_slug' => $model->slug,
            'version' => $version->name,
            'version_slug' => $version->slug,
            'year_start' => $year,
            'year_end' => $year,
            
            // Metadata
            'template' => $categorySlug,
            'slug' => $slug,
            'url' => "/guias/{$categorySlug}/{$make->slug}/{$model->slug}/{$year}",
            
            // Payload enriquecido do MySQL
            'payload' => $this->buildPayload($version, $categorySlug),
            
            // SEO
            'seo' => [
                'title' => "{$category->name} {$make->name} {$model->name} {$year}",
                'meta_description' => "Guia completo de {$category->name} do {$make->name} {$model->name} {$year}.",
            ],
        ]);
    }
    
    private function buildPayload($version, $categorySlug): array
    {
        switch ($categorySlug) {
            case 'oleo':
                return $this->buildOilPayload($version);
            case 'fluidos':
                return $this->buildFluidPayload($version);
            case 'calibragem':
                return $this->buildTirePayload($version);
            default:
                return [];
        }
    }
    
    private function buildOilPayload($version): array
    {
        $fluidSpec = $version->fluidSpecs;
        
        return [
            'oil_specs' => [
                ['label' => 'Viscosidade', 'value' => $fluidSpec->engine_oil_type ?? '5W-30'],
                ['label' => 'Capacidade', 'value' => ($fluidSpec->engine_oil_capacity ?? 4.2) . ' L'],
                ['label' => 'Especificação', 'value' => $fluidSpec->engine_oil_standard ?? 'API SN'],
            ],
            'compatible_oils' => [
                ['name' => 'Mobil 1 5W-30', 'spec' => 'API SN Plus'],
                ['name' => 'Castrol Edge 5W-30', 'spec' => 'API SN Plus'],
                ['name' => 'Shell Helix Ultra 5W-30', 'spec' => 'API SN'],
            ],
            'change_intervals' => [
                ['label' => 'Uso normal', 'value' => '10.000 km ou 12 meses'],
                ['label' => 'Uso severo', 'value' => '5.000 km ou 6 meses'],
            ],
            'severe_use_note' => 'Uso severo: trajetos curtos frequentes, trânsito intenso, reboque, áreas empoeiradas.',
        ];
    }
    
    private function buildFluidPayload($version): array
    {
        $fluidSpec = $version->fluidSpecs;
        
        return [
            'coolant_specs' => [
                ['label' => 'Tipo', 'value' => $fluidSpec->coolant_type ?? 'Etilenoglicol orgânico'],
                ['label' => 'Capacidade', 'value' => ($fluidSpec->coolant_capacity ?? 6.5) . ' L'],
            ],
            'brake_fluid' => [
                ['label' => 'Tipo', 'value' => $fluidSpec->brake_fluid_type ?? 'DOT 4'],
            ],
            'transmission_fluid' => [
                ['label' => 'Tipo', 'value' => $fluidSpec->transmission_fluid_type ?? 'ATF Dexron VI'],
                ['label' => 'Capacidade', 'value' => ($fluidSpec->transmission_fluid_capacity ?? 2.5) . ' L'],
            ],
        ];
    }
    
    private function buildTirePayload($version): array
    {
        $tireSpec = $version->tireSpecs;
        
        return [
            'tire_pressures' => [
                ['label' => 'Dianteira (vazio)', 'value' => ($tireSpec->front_pressure_psi ?? 32) . ' PSI'],
                ['label' => 'Traseira (vazio)', 'value' => ($tireSpec->rear_pressure_psi ?? 32) . ' PSI'],
                ['label' => 'Dianteira (carga)', 'value' => (($tireSpec->front_pressure_psi ?? 32) + 3) . ' PSI'],
                ['label' => 'Traseira (carga)', 'value' => (($tireSpec->rear_pressure_psi ?? 32) + 6) . ' PSI'],
            ],
            'tire_sizes' => [
                $tireSpec->front_tire_size ?? '185/65 R15',
                $tireSpec->rear_tire_size ?? '185/65 R15',
            ],
        ];
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Database Seeder Master
 * 
 * Executa todos os seeders na ordem correta
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeders...');
        $this->command->newLine();

        // ========================================
        // 1. VehicleDataCenter Seeders (MySQL)
        // ========================================
        $this->command->info('📊 VehicleDataCenter (MySQL)');
        $this->command->info('--------------------------------');
        
        $this->call(VehicleMakesAndModelsSeeder::class);
        $this->call(VehicleSpecsSeeder::class);
        
        $this->command->newLine();

        // ========================================
        // 2. GuideDataCenter Seeders (MongoDB)
        // ========================================
        $this->command->info('📚 GuideDataCenter (MongoDB)');
        $this->command->info('--------------------------------');
        
        $this->call(GuideCategorySeeder::class);
        $this->call(GuideSampleSeeder::class);
        $this->call(GuideClusterSeeder::class);
        
        $this->command->newLine();

        // ========================================
        // Resumo Final
        // ========================================
        $this->command->info('✅ SEEDERS CONCLUÍDOS COM SUCESSO!');
        $this->command->newLine();
        $this->command->info('Dados criados:');
        $this->command->info('  → VehicleDataCenter (MySQL):');
        $this->command->info('    • 12 marcas de veículos');
        $this->command->info('    • 20+ modelos');
        $this->command->info('    • 30+ versões com specs completas');
        $this->command->newLine();
        $this->command->info('  → GuideDataCenter (MongoDB):');
        $this->command->info('    • 14 categorias de guias');
        $this->command->info('    • 5 guias de exemplo (incluindo Toyota Corolla 2003)');
        $this->command->info('    • Clusters de links relacionados');
        $this->command->newLine();
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Database Seeder Master
 * 
 * Executa todos os seeders na ordem exata e correta
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando processo de seeding...');
        $this->command->newLine();

        // ============================================================
        // 1. VehicleDataCenter (MySQL)
        // ============================================================
        $this->command->info('📊 VehicleDataCenter (MySQL)');
        $this->command->info('--------------------------------');

        $this->call(VehicleMainMakesSeeder::class);
        $this->call(VehicleBaseModelsSeeder::class);
        $this->call(VehicleVersionsSeeder::class);
        $this->call(VehicleSpecsSeeder::class);

        $this->command->newLine();

        // ============================================================
        // 2. GuideDataCenter (MongoDB)
        // ============================================================
        $this->command->info('📚 GuideDataCenter (MongoDB)');
        $this->command->info('--------------------------------');

        $this->call(GuideCategorySeeder::class);
        $this->call(GuidesMasterSeeder::class);

        $this->command->newLine();

        // ============================================================
        // FINALIZAÇÃO
        // ============================================================
        $this->command->info('✅ SEEDERS CONCLUÍDOS COM SUCESSO!');
        $this->command->newLine();

        $this->command->info('📊 VehicleDataCenter (MySQL):');
        $this->command->info('  • Marcas principais + secundárias OK');
        $this->command->info('  • Modelos base instalados');
        $this->command->info('  • Versões base instaladas');
        $this->command->info('  • Specs gerados em ambiente DEV');
        $this->command->newLine();

        $this->command->info('📚 GuideDataCenter (MongoDB):');
        $this->command->info('  • Categorias instaladas');
        $this->command->info('  • Guias de exemplo populados');
        $this->command->info('  • Clusters registrados');
        $this->command->newLine();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder Master - CORRIGIDO
 * 
 * Executa apenas os seeders que realmente funcionam
 * (buscam IDs do MySQL corretamente)
 * 
 * Execute com:
 * php artisan db:seed --class=GuidesMasterSeeder
 */
class GuidesMasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando população de guias (versão corrigida)...');
        $this->command->newLine();

        // Verificar categorias
        $categoryCount = \Src\GuideDataCenter\Domain\Mongo\GuideCategory::count();
        
        if ($categoryCount === 0) {
            $this->command->warn('⚠️  Categorias não encontradas!');
            $this->command->info('Executando GuideCategorySeeder...');
            $this->call(GuideCategorySeeder::class);
            $this->command->newLine();
        }

        // Executar seeders corrigidos (TODOS)
        $seeders = [
            GuideOleoSeeder::class,              // 1. Óleo
            GuideCalibragemSeeder::class,        // 2. Calibragem
            GuideFluidsSeeder::class,            // 3. Fluidos
            GuidePneusSeeder::class,             // 4. Pneus
            GuideBateriaSeeder::class,           // 5. Bateria
            GuideRevisaoSeeder::class,           // 6. Revisão
            GuideConsumoSeeder::class,           // 7. Consumo
            GuideTecnicosSeeder::class,          // 8-10. Câmbio, Arrefecimento, Suspensão
            GuideInformativosSeeder::class,      // 11-13. Problemas, Recalls, Comparações
        ];

        foreach ($seeders as $seeder) {
            $this->call($seeder);
        }

        $this->command->newLine();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('🎉 CONCLUÍDO!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();

        // Estatísticas
        $totalGuides = \Src\GuideDataCenter\Domain\Mongo\Guide::count();
        $totalCategories = \Src\GuideDataCenter\Domain\Mongo\GuideCategory::count();

        $this->command->table(
            ['Métrica', 'Valor'],
            [
                ['Categorias', $totalCategories],
                ['Guias criados', $totalGuides],
            ]
        );

        $this->command->newLine();
        $this->command->info('📝 Testar no navegador:');
        $this->command->line('   http://localhost/guias/oleo/toyota/corolla/2025/gli');
        $this->command->newLine();
    }
}

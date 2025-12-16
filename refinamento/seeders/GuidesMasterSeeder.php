<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

/**
 * 🎯 SEEDER MASTER SIMPLIFICADO
 * 
 * Executa apenas os 3 seeders essenciais:
 * 1. GuideCategorySeeder → Cria as 13 categorias
 * 2. GuideComprehensiveSeeder → Cria TODOS os guias (13 categorias × N veículos)
 * 3. GuideRelationshipsSeeder → Popula links_internal (Guias Relacionados + Conteúdos Essenciais)
 * 
 * ❌ REMOVIDOS: GuideOleoSeeder, GuideCalibragemSeeder, etc (redundantes)
 * 
 * Execute com:
 * php artisan db:seed --class=Database\\Seeders\\GuidesMasterSeeder
 */
class GuidesMasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('🚀 SEEDER MASTER - GuideDataCenter');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();

        // ═══════════════════════════════════════════════════════════
        // ETAPA 1: CATEGORIAS (13 categorias)
        // ═══════════════════════════════════════════════════════════
        $this->command->info('📂 ETAPA 1/3: Verificando Categorias');
        $this->command->line('─────────────────────────────────');
        
        $categoryCount = GuideCategory::count();
        
        if ($categoryCount === 0) {
            $this->command->warn('⚠️  Categorias não encontradas!');
            $this->command->info('🔄 Executando GuideCategorySeeder...');
            $this->call(GuideCategorySeeder::class);
            $categoryCount = GuideCategory::count();
        }
        
        $this->command->info("✅ Categorias disponíveis: {$categoryCount}");
        $this->command->newLine();

        // ═══════════════════════════════════════════════════════════
        // ETAPA 2: GUIAS COMPREHENSIVOS (TODOS os guias de uma vez)
        // ═══════════════════════════════════════════════════════════
        $this->command->info('📊 ETAPA 2/3: Criando Guias Completos');
        $this->command->line('─────────────────────────────────');
        
        $guidesBefore = Guide::count();
        $this->command->info("📈 Guias atuais: {$guidesBefore}");
        $this->command->newLine();

        $this->command->info('🔄 Executando GuideComprehensiveSeeder...');
        $this->command->warn('⏳ Isso pode levar alguns minutos dependendo da quantidade de VehicleVersions...');
        $this->command->newLine();
        
        $this->call(\Database\Seeders\GuideComprehensiveSeeder::class);
        
        $guidesAfter = Guide::count();
        $guidesCreated = $guidesAfter - $guidesBefore;
        
        $this->command->newLine();
        $this->command->info("✅ Guias criados: {$guidesCreated}");
        $this->command->info("📊 Total de guias: {$guidesAfter}");
        $this->command->newLine();

        // ═══════════════════════════════════════════════════════════
        // ETAPA 3: RELACIONAMENTOS (Links internos)
        // ═══════════════════════════════════════════════════════════
        $this->command->info('🔗 ETAPA 3/3: Populando Relacionamentos');
        $this->command->line('─────────────────────────────────');
        
        $this->command->info('🔄 Executando GuideRelationshipsSeeder...');
        $this->command->info('   • Guias Relacionados (outras categorias)');
        $this->command->info('   • Conteúdos Essenciais (cluster estratégico)');
        $this->command->newLine();
        
        $this->call(\Database\Seeders\GuideRelationshipsSeeder::class);

        // ═══════════════════════════════════════════════════════════
        // RESUMO FINAL
        // ═══════════════════════════════════════════════════════════
        $this->command->newLine();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('🎉 CONCLUÍDO COM SUCESSO!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();

        // Estatísticas finais
        $totalGuides = Guide::count();
        $totalCategories = GuideCategory::count();
        
        // Contar guias por categoria (top 5)
        $guidesByCategory = Guide::raw()->aggregate([
            [
                '$lookup' => [
                    'from' => 'guide_categories',
                    'localField' => 'guide_category_id',
                    'foreignField' => '_id',
                    'as' => 'category'
                ]
            ],
            ['$unwind' => '$category'],
            [
                '$group' => [
                    '_id' => '$category.name',
                    'count' => ['$sum' => 1]
                ]
            ],
            ['$sort' => ['count' => -1]],
            ['$limit' => 5]
        ])->toArray();

        $this->command->table(
            ['Métrica', 'Valor'],
            [
                ['Categorias', $totalCategories],
                ['Guias criados', $totalGuides],
                ['Média por categoria', round($totalGuides / max($totalCategories, 1), 1)],
            ]
        );

        if (!empty($guidesByCategory)) {
            $this->command->newLine();
            $this->command->info('📊 Top 5 Categorias:');
            foreach ($guidesByCategory as $stat) {
                $this->command->line("   • {$stat['_id']}: {$stat['count']} guias");
            }
        }

        $this->command->newLine();
        $this->command->info('🌐 TESTAR NO NAVEGADOR:');
        $this->command->line('   • http://localhost:8000/guias/oleo');
        $this->command->line('   • http://localhost:8000/guias/oleo/toyota/corolla/2025');
        $this->command->line('   • http://localhost:8000/guias/calibragem/hyundai/hb20/2025');
        $this->command->newLine();

        $this->command->info('💡 DICAS:');
        $this->command->line('   • Execute "php artisan cache:clear" se a página não atualizar');
        $this->command->line('   • Verifique as seções "Guias Relacionados" e "Conteúdos Essenciais"');
        $this->command->line('   • Use "php artisan tinker" para verificar os dados no MongoDB');
        $this->command->newLine();
    }
}
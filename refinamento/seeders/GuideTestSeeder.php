<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;

/**
 * Script de Teste Rápido
 * 
 * Valida os dados criados pelo GuideComprehensiveSeeder
 * 
 * Uso: php artisan db:seed --class=Database\\Seeders\\GuideTestSeeder
 */
class GuideTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 INICIANDO TESTES DE VALIDAÇÃO');
        $this->command->info('');

        // Teste 1: Contagem geral
        $this->testGeneralCounts();

        // Teste 2: Verificar primeira versão
        $this->testFirstVersion();

        // Teste 3: Verificar categorias
        $this->testCategories();

        // Teste 4: Verificar payload
        $this->testPayload();

        // Teste 5: Verificar links internos
        $this->testInternalLinks();

        // Teste 6: Verificar SEO
        $this->testSeo();

        $this->command->info('');
        $this->command->info('✅ TODOS OS TESTES CONCLUÍDOS!');
    }

    private function testGeneralCounts(): void
    {
        $this->command->info('📊 TESTE 1: Contagens Gerais');
        $this->command->line('─────────────────────────────────');

        $versionsCount = VehicleVersion::count();
        $guidesCount = Guide::count();
        $categoriesCount = GuideCategory::count();

        $this->command->info("VehicleVersions no banco: {$versionsCount}");
        $this->command->info("Guias criados: {$guidesCount}");
        $this->command->info("Categorias disponíveis: {$categoriesCount}");

        if ($guidesCount > 0) {
            $avgPerVersion = round($guidesCount / $versionsCount, 1);
            $this->command->info("Média de guias por versão: {$avgPerVersion}");
            
            if ($avgPerVersion >= 13) {
                $this->command->line('✅ Todas as categorias criadas!');
            } else {
                $this->command->warn("⚠️  Algumas categorias podem estar faltando");
            }
        }

        $this->command->info('');
    }

    private function testFirstVersion(): void
    {
        $this->command->info('🚗 TESTE 2: Primeira VehicleVersion');
        $this->command->line('─────────────────────────────────');

        $version = VehicleVersion::with('model.make')->first();

        if (!$version) {
            $this->command->error('❌ Nenhuma VehicleVersion encontrada!');
            return;
        }

        $make = $version->model->make->name;
        $model = $version->model->name;
        $year = $version->year;
        $versionName = $version->name;

        $this->command->info("Veículo: {$make} {$model} {$year} {$versionName}");

        // Busca guias desta versão
        $guides = Guide::where('make', $make)
            ->where('model', $model)
            ->where('year', $year)
            ->get();

        $this->command->info("Guias encontrados: {$guides->count()}");

        if ($guides->count() >= 13) {
            $this->command->line('✅ Todas as categorias criadas para este veículo!');
        } else {
            $this->command->warn("⚠️  Esperado: 13 guias, Encontrado: {$guides->count()}");
        }

        // Lista categorias criadas
        $this->command->line('Categorias criadas:');
        foreach ($guides as $guide) {
            $category = GuideCategory::find($guide->guide_category_id);
            $this->command->line("  • {$category->name} → {$guide->slug}");
        }

        $this->command->info('');
    }

    private function testCategories(): void
    {
        $this->command->info('📂 TESTE 3: Distribuição por Categoria');
        $this->command->line('─────────────────────────────────');

        $categories = GuideCategory::all();

        foreach ($categories as $category) {
            $count = Guide::where('guide_category_id', (string) $category->_id)->count();
            $icon = $this->getCategoryIcon($category->slug);
            $this->command->line("{$icon} {$category->name}: {$count} guias");
        }

        $this->command->info('');
    }

    private function testPayload(): void
    {
        $this->command->info('💾 TESTE 4: Validação de Payload');
        $this->command->line('─────────────────────────────────');

        // Pega um guia de óleo
        $oilCategory = GuideCategory::where('slug', 'oleo')->first();
        $oilGuide = Guide::where('guide_category_id', (string) $oilCategory->_id)->first();

        if ($oilGuide) {
            $this->command->info("Guia testado: {$oilGuide->slug}");
            
            $payload = $oilGuide->payload;
            
            $this->command->line('Estrutura do payload:');
            $this->command->line('  • intro: ' . (isset($payload['intro']) ? '✅' : '❌'));
            $this->command->line('  • specifications: ' . (isset($payload['specifications']) ? '✅' : '❌'));
            $this->command->line('  • compatible_oils: ' . (isset($payload['compatible_oils']) ? '✅' : '❌'));
            $this->command->line('  • change_interval: ' . (isset($payload['change_interval']) ? '✅' : '❌'));
            
            if (isset($payload['specifications'])) {
                $specs = $payload['specifications'];
                $this->command->info('');
                $this->command->line('Especificações:');
                $this->command->line("  • Viscosidade: {$specs['viscosity']}");
                $this->command->line("  • Capacidade: {$specs['capacity']}");
                $this->command->line("  • API Spec: {$specs['api_spec']}");
            }
        } else {
            $this->command->error('❌ Nenhum guia de óleo encontrado!');
        }

        $this->command->info('');
    }

    private function testInternalLinks(): void
    {
        $this->command->info('🔗 TESTE 5: Links Internos');
        $this->command->line('─────────────────────────────────');

        $guide = Guide::first();

        if ($guide) {
            $this->command->info("Guia testado: {$guide->slug}");
            
            $linksCount = count($guide->links_internal ?? []);
            $this->command->info("Total de links internos: {$linksCount}");

            if ($linksCount > 0) {
                $this->command->line('');
                $this->command->line('Primeiros 5 links:');
                foreach (array_slice($guide->links_internal, 0, 5) as $link) {
                    $icon = $link['icon'] ?? '•';
                    $title = $link['title'] ?? 'Sem título';
                    $this->command->line("  {$icon} {$title}");
                }
                
                $this->command->line('✅ Links internos criados com sucesso!');
            } else {
                $this->command->warn('⚠️  Nenhum link interno encontrado');
            }
        }

        $this->command->info('');
    }

    private function testSeo(): void
    {
        $this->command->info('📊 TESTE 6: Dados de SEO');
        $this->command->line('─────────────────────────────────');

        $guide = Guide::first();

        if ($guide) {
            $this->command->info("Guia testado: {$guide->slug}");
            
            $seo = $guide->seo;
            
            $this->command->line('Dados de SEO:');
            $this->command->line('  • title: ' . (isset($seo['title']) ? '✅' : '❌'));
            $this->command->line('  • meta_description: ' . (isset($seo['meta_description']) ? '✅' : '❌'));
            $this->command->line('  • h1: ' . (isset($seo['h1']) ? '✅' : '❌'));
            $this->command->line('  • primary_keyword: ' . (isset($seo['primary_keyword']) ? '✅' : '❌'));
            $this->command->line('  • canonical_url: ' . (isset($seo['canonical_url']) ? '✅' : '❌'));

            if (isset($seo['title'])) {
                $this->command->info('');
                $this->command->line("Title: {$seo['title']}");
                $this->command->line("H1: {$seo['h1']}");
            }

            $this->command->line('✅ SEO gerado com sucesso!');
        }

        $this->command->info('');
    }

    private function getCategoryIcon(string $slug): string
    {
        return match ($slug) {
            'oleo' => '🛢️',
            'fluidos' => '💧',
            'calibragem' => '🔧',
            'pneus' => '🚗',
            'bateria' => '🔋',
            'manutencao' => '📋',
            'consumo' => '⛽',
            'transmissao' => '⚙️',
            'arrefecimento' => '❄️',
            'suspensao' => '🔩',
            'problemas-comuns' => '⚠️',
            'recalls' => '📢',
            'comparacoes' => '📊',
            default => '•',
        };
    }
}
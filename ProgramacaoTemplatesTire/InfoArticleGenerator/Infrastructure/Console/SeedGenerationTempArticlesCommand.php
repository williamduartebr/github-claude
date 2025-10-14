<?php

namespace Src\InfoArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Src\InfoArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;
use Illuminate\Support\Str;

/**
 * SeedGenerationTempArticlesCommand - Gerar Títulos de Teste
 * 
 * Insere títulos no GenerationTempArticle para testar geração via Claude API
 * 
 * USO:
 * php artisan temp-article:seed --count=10
 * php artisan temp-article:seed --category=oleo --count=5
 * php artisan temp-article:seed --priority=high
 * 
 * @author Claude Sonnet 4
 * @version 1.0
 */
class SeedGenerationTempArticlesCommand extends Command
{
    protected $signature = 'temp-article:seed
                            {--count=10 : Quantidade de artigos}
                            {--category= : Categoria específica (oleo|velas|embreagem|diferencial|all)}
                            {--priority=medium : Prioridade (high|medium|low)}
                            {--dry-run : Apenas visualizar sem inserir}';

    protected $description = 'Gerar títulos de teste no GenerationTempArticle para geração via Claude API';

    // Templates de títulos por categoria
    private const TITLE_TEMPLATES = [
        'oleo' => [
            'Óleo {viscosity}: Quando Usar e Por Quê?',
            'Diferença entre Óleo {type1} e {type2}: Qual Escolher?',
            '{brand} {viscosity}: Vale a Pena? Teste Real',
            'Trocar Óleo a Cada Quanto Tempo? Guia Completo {year}',
            'Óleo Sintético vs Mineral: Economia Real em {km}km',
            'Misturei Óleo {viscosity1} com {viscosity2}: O que Fazer?',
            '{brand} {viscosity}: Economiza Combustível? Teste de {months} Meses',
        ],
        'velas' => [
            'Vela {type} vs Comum: {km}km de Teste Real',
            'Quando Trocar Velas de Ignição? Sintomas e Intervalos',
            '{brand} {type}: Vale o Investimento? Análise Completa',
            'Vela Iridium Dura {km}km? Teste Prático',
            'Sintomas de Vela Ruim: Como Identificar',
            'Vela Platina vs Iridium: Qual Melhor para Seu Carro?',
        ],
        'embreagem' => [
            'Kit Completo de Embreagem: R${price} Valeram a Pena?',
            'Trocar Só Disco ou Kit Completo? Economia Real',
            'Embreagem Patinando: {causes} Causas Principais',
            'Quanto Custa Trocar Embreagem? Preços {year}',
            '{brand} vs Original: Kit de Embreagem Comparado',
            'Embreagem Dura Quanto Tempo? Fatores que Influenciam',
        ],
        'diferencial' => [
            'Diferencial: Óleo Separado ou Junto do Câmbio?',
            'Quando Trocar Óleo do Diferencial? Guia Completo',
            'Diferencial 4x4: Manutenção e Custos Reais',
            'Barulho no Diferencial: {symptoms} Sintomas',
            'Óleo {viscosity} para Diferencial: Especificação Correta',
        ],
    ];

    // Variáveis para templates
    private const TEMPLATE_VARS = [
        'viscosity' => ['5W30', '5W40', '10W40', '15W40', '20W50'],
        'viscosity1' => ['5W30', '10W40', '15W40'],
        'viscosity2' => ['5W40', '10W40', '20W50'],
        'type' => ['Iridium', 'Platina', 'Comum', 'Dupla Iridium'],
        'type1' => ['Sintético', 'Semissintético', 'Mineral'],
        'type2' => ['Mineral', 'Semissintético', 'Sintético'],
        'brand' => ['Mobil', 'Castrol', 'Shell', 'Lubrax', 'NGK', 'Bosch', 'Denso', 'LUK', 'Sachs', 'Valeo'],
        'km' => ['50.000', '100.000', '120.000', '150.000'],
        'months' => ['6', '12', '18', '24'],
        'price' => ['800', '1.200', '1.500', '2.000'],
        'year' => ['2024', '2025'],
        'causes' => ['7', '5', '10'],
        'symptoms' => ['5', '7', '10'],
    ];

    // Categorias e subcategorias
    private const CATEGORIES = [
        'oleo' => [
            'id' => 1,
            'name' => 'Óleo de Motor',
            'slug' => 'oleo-motor',
            'subcategories' => [
                ['id' => 101, 'name' => 'Viscosidade', 'slug' => 'viscosidade'],
                ['id' => 102, 'name' => 'Tipos de Óleo', 'slug' => 'tipos-oleo'],
                ['id' => 103, 'name' => 'Manutenção', 'slug' => 'manutencao'],
            ]
        ],
        'velas' => [
            'id' => 2,
            'name' => 'Velas de Ignição',
            'slug' => 'velas-ignicao',
            'subcategories' => [
                ['id' => 201, 'name' => 'Tipos de Vela', 'slug' => 'tipos-vela'],
                ['id' => 202, 'name' => 'Diagnóstico', 'slug' => 'diagnostico'],
                ['id' => 203, 'name' => 'Comparação', 'slug' => 'comparacao'],
            ]
        ],
        'embreagem' => [
            'id' => 3,
            'name' => 'Embreagem',
            'slug' => 'embreagem',
            'subcategories' => [
                ['id' => 301, 'name' => 'Manutenção', 'slug' => 'manutencao'],
                ['id' => 302, 'name' => 'Problemas', 'slug' => 'problemas'],
                ['id' => 303, 'name' => 'Custos', 'slug' => 'custos'],
            ]
        ],
        'diferencial' => [
            'id' => 4,
            'name' => 'Diferencial e Transmissão',
            'slug' => 'diferencial-transmissao',
            'subcategories' => [
                ['id' => 401, 'name' => 'Manutenção', 'slug' => 'manutencao'],
                ['id' => 402, 'name' => 'Diagnóstico', 'slug' => 'diagnostico'],
                ['id' => 403, 'name' => 'Tipos', 'slug' => 'tipos'],
            ]
        ],
    ];

    public function handle(): int
    {
        $count = (int) $this->option('count');
        $category = $this->option('category');
        $priority = $this->option('priority');
        $dryRun = $this->option('dry-run');

        $this->info('🎯 SEED DE TÍTULOS PARA TEMP_ARTICLES');
        $this->newLine();

        // Determinar categorias a processar
        $categoriesToProcess = $category && $category !== 'all'
            ? [$category]
            : array_keys(self::TITLE_TEMPLATES);

        $generatedTitles = [];

        foreach ($categoriesToProcess as $cat) {
            $countPerCategory = (int) ceil($count / count($categoriesToProcess));
            $titles = $this->generateTitlesForCategory($cat, $countPerCategory);
            $generatedTitles = array_merge($generatedTitles, $titles);
        }

        // Limitar ao count solicitado
        $generatedTitles = array_slice($generatedTitles, 0, $count);

        // Exibir preview
        $this->displayPreview($generatedTitles, $priority);

        if ($dryRun) {
            $this->warn('🧪 DRY RUN - Nenhum dado foi inserido');
            return self::SUCCESS;
        }

        if (!$this->confirm('Inserir estes ' . count($generatedTitles) . ' artigos no GenerationTempArticle?')) {
            $this->info('⏹️ Operação cancelada');
            return self::SUCCESS;
        }

        // Inserir no banco
        $inserted = $this->insertTitles($generatedTitles, $priority);

        $this->newLine();
        $this->info("✅ {$inserted} artigos inseridos com sucesso!");
        $this->line('🔄 Para gerar os JSONs: php artisan temp-article:generate-standard --limit=5');

        return self::SUCCESS;
    }

    /**
     * Gerar títulos para uma categoria
     */
    private function generateTitlesForCategory(string $category, int $count): array
    {
        $templates = self::TITLE_TEMPLATES[$category] ?? [];
        $categoryData = self::CATEGORIES[$category] ?? [];
        $titles = [];

        for ($i = 0; $i < $count; $i++) {
            // Escolher template aleatório
            $template = $templates[array_rand($templates)];

            // Substituir variáveis
            $title = $this->fillTemplate($template);

            // Escolher subcategoria aleatória
            $subcategory = $categoryData['subcategories'][array_rand($categoryData['subcategories'])];

            $titles[] = [
                'title' => $title,
                'slug' => Str::slug($title),
                'category_id' => $categoryData['id'],
                'category_name' => $categoryData['name'],
                'category_slug' => $categoryData['slug'],
                'subcategory_id' => $subcategory['id'],
                'subcategory_name' => $subcategory['name'],
                'subcategory_slug' => $subcategory['slug'],
            ];
        }

        return $titles;
    }

    /**
     * Preencher template com variáveis
     */
    private function fillTemplate(string $template): string
    {
        preg_match_all('/\{(\w+)\}/', $template, $matches);

        foreach ($matches[1] as $var) {
            if (isset(self::TEMPLATE_VARS[$var])) {
                $value = self::TEMPLATE_VARS[$var][array_rand(self::TEMPLATE_VARS[$var])];
                $template = str_replace('{' . $var . '}', $value, $template);
            }
        }

        return $template;
    }

    /**
     * Inserir títulos no banco
     */
    private function insertTitles(array $titles, string $priority): int
    {
        $inserted = 0;

        foreach ($titles as $titleData) {
            // Verificar se slug já existe
            if (GenerationTempArticle::where('slug', $titleData['slug'])->exists()) {
                $this->warn("⚠️ Slug duplicado, pulando: {$titleData['slug']}");
                continue;
            }

            GenerationTempArticle::create([
                'title' => $titleData['title'],
                'slug' => $titleData['slug'],
                'category_id' => $titleData['category_id'],
                'category_name' => $titleData['category_name'],
                'category_slug' => $titleData['category_slug'],
                'subcategory_id' => $titleData['subcategory_id'],
                'subcategory_name' => $titleData['subcategory_name'],
                'subcategory_slug' => $titleData['subcategory_slug'],
                'generation_status' => 'pending',
                'generation_priority' => $priority,
                'generation_retry_count' => 0,
                'scheduled_for' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $inserted++;
        }

        return $inserted;
    }

    /**
     * Exibir preview dos títulos
     */
    private function displayPreview(array $titles, string $priority): void
    {
        $this->info('📋 PREVIEW DOS ARTIGOS:');
        $this->newLine();

        foreach ($titles as $index => $title) {
            $this->line(($index + 1) . ". {$title['title']}");
            $this->line("   📁 {$title['category_name']} > {$title['subcategory_name']}");
            $this->line("   🔗 {$title['slug']}");
            $this->newLine();
        }

        $this->info("🎯 Prioridade: {$priority}");
        $this->info("📊 Total: " . count($titles) . " artigos");
        $this->newLine();
    }
}

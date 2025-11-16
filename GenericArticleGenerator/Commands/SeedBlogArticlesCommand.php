<?php

namespace Src\GenericArticleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;

/**
 * Command para Migração de Artigos do Blog Antigo
 * 
 * Importa artigos existentes do blog para a collection generation_temp_articles
 * permitindo sua regeneração com Claude API
 * 
 * FEATURES:
 * - Importação de artigos do blog antigo (migracao-blog.json)
 * - Flag 'origin' na raiz do documento para identificação e filtros
 * - Preservação de slugs originais do WordPress
 * - Mapping correto de categorias e subcategorias
 * - Suporte a dry-run e preview
 * - Estatísticas detalhadas por categoria
 * - Preservação de métricas (visualizações)
 * 
 * @author Claude Sonnet 4.5
 * @version 1.0.0
 * @see GenerationTempArticle
 */
class SeedBlogArticlesCommand extends Command
{
    /**
     * Assinatura do comando
     */
    protected $signature = 'generation:seed-blog-articles 
                          {--category= : Categoria específica (ex: oil_motor, oil_cambio)}
                          {--dry-run : Simular sem inserir no banco}
                          {--preview : Mostrar preview dos artigos sem processar}
                          {--force : Forçar inserção mesmo que já existam}
                          {--min-views= : Importar apenas artigos com X ou mais visualizações}';

    /**
     * Descrição do comando
     */
    protected $description = 'Popular collection generation_temp_articles com artigos existentes do blog (migração)';

    /**
     * Caminho do arquivo JSON de migração
     */
    private const MIGRATION_FILE = 'src/GenericArticleGenerator/Data/ArticleTitles/migracao-blog.json';

    /**
     * Contadores de estatísticas
     */
    private int $totalInserted = 0;
    private int $totalSkipped = 0;
    private int $totalErrors = 0;
    private array $categoryStats = [];

    /**
     * Execute o comando
     */
    public function handle(): int
    {
        $this->displayHeader();

        // Verificar se arquivo existe
        if (!$this->validateMigrationFile()) {
            return self::FAILURE;
        }

        // Carregar dados do JSON
        $data = $this->loadMigrationData();
        
        if (!$data) {
            return self::FAILURE;
        }

        // Modo preview
        if ($this->option('preview')) {
            return $this->previewArticles($data);
        }

        // Processar importação
        return $this->processImport($data);
    }

    /**
     * Exibir cabeçalho do comando
     */
    private function displayHeader(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║       📚  IMPORTAÇÃO DE ARTIGOS DO BLOG ANTIGO              ║');
        $this->info('║              Migração para Nova Estrutura                    ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    /**
     * Validar se arquivo de migração existe
     */
    private function validateMigrationFile(): bool
    {
        $path = base_path(self::MIGRATION_FILE);

        if (!File::exists($path)) {
            $this->error('❌ Arquivo de migração não encontrado!');
            $this->line("   Caminho esperado: {$path}");
            $this->newLine();
            $this->line('   Crie o arquivo migracao-blog.json primeiro.');
            return false;
        }

        $this->info("✅ Arquivo encontrado: " . self::MIGRATION_FILE);
        $this->newLine();

        return true;
    }

    /**
     * Carregar dados do JSON de migração
     */
    private function loadMigrationData(): ?array
    {
        $path = base_path(self::MIGRATION_FILE);

        try {
            $content = File::get($path);
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('❌ Erro ao decodificar JSON: ' . json_last_error_msg());
                return null;
            }

            $this->info('✅ JSON carregado com sucesso');
            $this->newLine();

            return $data;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao ler arquivo: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Preview dos artigos sem processar
     */
    private function previewArticles(array $data): int
    {
        $existingArticles = $data['existing_articles_categorized'] ?? [];
        $summary = $data['summary'] ?? [];

        $this->info('📊 RESUMO GERAL:');
        $this->line("   Total de artigos: <fg=cyan>{$summary['total_articles_mapped']}</>");
        $this->newLine();

        foreach ($existingArticles as $categoryKey => $categoryData) {
            $this->displayCategoryPreview($categoryKey, $categoryData);
        }

        $this->displaySummaryStats($summary);

        return self::SUCCESS;
    }

    /**
     * Exibir preview de uma categoria
     */
    private function displayCategoryPreview(string $categoryKey, array $categoryData): void
    {
        $categoryName = $categoryData['category_name'] ?? $categoryKey;
        $categoryId = $categoryData['category_id'] ?? 'N/A';
        $totalArticles = $categoryData['total_articles'] ?? 0;

        $this->info("📁 {$categoryName} (ID: {$categoryId})");
        $this->line("   └─ Total: <fg=yellow>{$totalArticles}</> artigos");
        $this->newLine();

        foreach ($categoryData['subcategories'] ?? [] as $subKey => $subData) {
            $subName = $subData['subcategory_name'] ?? $subKey;
            $subId = $subData['subcategory_id'] ?? 'N/A';
            $articles = $subData['articles'] ?? [];
            $count = count($articles);

            $this->line("   📂 {$subName} (ID: {$subId})");
            $this->line("      ├─ Artigos: <fg=white>{$count}</>");

            if ($count > 0) {
                $totalViews = array_sum(array_column($articles, 'visualizacoes'));
                $avgViews = $count > 0 ? round($totalViews / $count) : 0;
                $this->line("      ├─ Visualizações totais: <fg=cyan>{$totalViews}</>");
                $this->line("      └─ Média de visualizações: <fg=green>{$avgViews}</>");
            }

            $this->newLine();
        }
    }

    /**
     * Exibir estatísticas do resumo
     */
    private function displaySummaryStats(array $summary): void
    {
        $this->info('📈 ESTATÍSTICAS POR CATEGORIA:');
        
        foreach ($summary['articles_by_category'] ?? [] as $category => $count) {
            $this->line("   ├─ {$category}: <fg=cyan>{$count}</> artigos");
        }

        $this->newLine();
    }

    /**
     * Processar importação completa
     */
    private function processImport(array $data): int
    {
        $existingArticles = $data['existing_articles_categorized'] ?? [];
        $categoryFilter = $this->option('category');
        $minViews = (int) $this->option('min-views');

        if ($categoryFilter && !isset($existingArticles[$categoryFilter])) {
            $this->error("❌ Categoria '{$categoryFilter}' não encontrada!");
            $this->line('   Categorias disponíveis: ' . implode(', ', array_keys($existingArticles)));
            return self::FAILURE;
        }

        $this->info('🚀 Iniciando importação...');
        
        if ($minViews > 0) {
            $this->warn("   ⚠️  Filtrando artigos com mínimo de {$minViews} visualizações");
        }

        if ($this->option('dry-run')) {
            $this->warn('   🔍 Modo DRY-RUN ativo (simulação)');
        }

        $this->newLine();

        // Processar cada categoria
        foreach ($existingArticles as $categoryKey => $categoryData) {
            // Aplicar filtro de categoria se especificado
            if ($categoryFilter && $categoryKey !== $categoryFilter) {
                continue;
            }

            $this->processCategoryImport($categoryKey, $categoryData, $minViews);
        }

        $this->displayFinalSummary();

        return self::SUCCESS;
    }

    /**
     * Processar importação de uma categoria
     */
    private function processCategoryImport(string $categoryKey, array $categoryData, int $minViews): void
    {
        $categoryName = $categoryData['category_name'] ?? $categoryKey;
        $categoryId = $categoryData['category_id'] ?? null;

        $this->info("📁 Processando: <fg=cyan>{$categoryName}</> (ID: {$categoryId})");
        $this->newLine();

        $categoryInserted = 0;
        $categorySkipped = 0;
        $categoryErrors = 0;

        // Processar cada subcategoria
        foreach ($categoryData['subcategories'] ?? [] as $subKey => $subData) {
            $result = $this->processSubcategoryImport(
                $categoryKey,
                $categoryId,
                $subKey,
                $subData,
                $minViews
            );

            $categoryInserted += $result['inserted'];
            $categorySkipped += $result['skipped'];
            $categoryErrors += $result['errors'];
        }

        $this->totalInserted += $categoryInserted;
        $this->totalSkipped += $categorySkipped;
        $this->totalErrors += $categoryErrors;

        $this->categoryStats[$categoryKey] = [
            'name' => $categoryName,
            'inserted' => $categoryInserted,
            'skipped' => $categorySkipped,
            'errors' => $categoryErrors
        ];

        $this->displayCategoryResults($categoryName, $categoryInserted, $categorySkipped, $categoryErrors);
    }

    /**
     * Processar importação de uma subcategoria
     */
    private function processSubcategoryImport(
        string $categoryKey,
        ?int $categoryId,
        string $subKey,
        array $subData,
        int $minViews
    ): array {
        $subName = $subData['subcategory_name'] ?? $subKey;
        $subId = $subData['subcategory_id'] ?? null;
        $articles = $subData['articles'] ?? [];

        if (empty($articles)) {
            return ['inserted' => 0, 'skipped' => 0, 'errors' => 0];
        }

        // Filtrar por visualizações mínimas
        if ($minViews > 0) {
            $articles = array_filter($articles, fn($article) => 
                ($article['visualizacoes'] ?? 0) >= $minViews
            );
        }

        $totalArticles = count($articles);

        if ($totalArticles === 0) {
            return ['inserted' => 0, 'skipped' => 0, 'errors' => 0];
        }

        $this->line("   📂 {$subName} (ID: {$subId}) - {$totalArticles} artigos");

        $bar = $this->output->createProgressBar($totalArticles);
        $bar->setFormat('      %current%/%max% [%bar%] %percent:3s%% | %message%');

        $inserted = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($articles as $article) {
            $bar->setMessage(Str::limit($article['title'] ?? 'Sem título', 40));

            $result = $this->importArticle(
                $article,
                $categoryKey,
                $categoryId,
                $subKey,
                $subId
            );

            match($result) {
                'inserted' => $inserted++,
                'skipped' => $skipped++,
                'error' => $errors++,
            };

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return [
            'inserted' => $inserted,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * Importar um artigo individual
     */
    private function importArticle(
        array $article,
        string $categoryKey,
        ?int $categoryId,
        string $subKey,
        ?int $subId
    ): string {
        try {
            // Extrair e limpar título
            $title = trim($article['title'] ?? '');
            
            if (empty($title)) {
                return 'error';
            }

            // Gerar slug limpo (removendo barras do WordPress)
            $originalSlug = $article['slug'] ?? '';
            $cleanSlug = $this->cleanWordPressSlug($originalSlug);
            
            // Se não tiver slug limpo, gerar do título
            if (empty($cleanSlug)) {
                $cleanSlug = Str::slug($title);
            }

            // Verificar se já existe
            $exists = GenerationTempArticle::where('slug', $cleanSlug)->exists();

            if ($exists && !$this->option('force')) {
                return 'skipped';
            }

            // Não inserir no modo dry-run
            if ($this->option('dry-run')) {
                return 'inserted';
            }

            // Criar registro com flag 'origin' na raiz
            GenerationTempArticle::updateOrCreate(
                ['slug' => $cleanSlug],
                [
                    'title' => $title,
                    'slug' => $cleanSlug,
                    'origin' => 'blog', // FLAG NA RAIZ para filtros eficientes
                    'maintenance_category_id' => $categoryId,
                    'maintenance_subcategory_id' => $subId,
                    'generation_status' => 'pending',
                    'generation_priority' => $this->calculatePriority($article),
                    'generation_model' => null,
                    'generation_retry_count' => 0,
                    'generation_cost' => 0,
                    'metadata' => [
                        'source' => 'blog_migration',
                        'file' => 'migracao-blog.json',
                        'category_key' => $categoryKey,
                        'subcategory_key' => $subKey,
                        'original_slug' => $originalSlug,
                        'visualizacoes' => $article['visualizacoes'] ?? 0,
                        'estimated_tokens' => $this->estimateTokens($title),
                        'migrated_at' => now()->toISOString(),
                    ]
                ]
            );

            return 'inserted';

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("   ❌ Erro ao importar: {$article['title']}");
            $this->line("      Motivo: " . $e->getMessage());
            
            return 'error';
        }
    }

    /**
     * Limpar slug do WordPress (remover barras)
     */
    private function cleanWordPressSlug(string $slug): string
    {
        // Remove barras iniciais e finais
        $slug = trim($slug, '/');
        
        // Remove qualquer barra restante
        $slug = str_replace('/', '-', $slug);
        
        return $slug;
    }

    /**
     * Calcular prioridade baseado nas visualizações
     */
    private function calculatePriority(array $article): string
    {
        $views = $article['visualizacoes'] ?? 0;

        // Alta prioridade: artigos populares do blog
        if ($views >= 50) {
            return 'high';
        }

        // Média prioridade: artigos com tráfego moderado
        if ($views >= 25) {
            return 'medium';
        }

        // Baixa prioridade: artigos com pouco tráfego
        return 'low';
    }

    /**
     * Estimar tokens baseado no título
     */
    private function estimateTokens(string $title): int
    {
        // Palavras-chave que indicam conteúdo técnico mais extenso
        $technicalKeywords = [
            'especificação', 'guia completo', 'análise', 'entenda',
            'diferença', 'recomendado', 'indicado'
        ];

        // Palavras-chave que indicam conteúdo prático
        $practicalKeywords = [
            'posso', 'qual', 'como', 'quantos', 'melhor', 'serve'
        ];

        $titleLower = mb_strtolower($title);

        // Artigos técnicos/guias tendem a ser mais longos
        foreach ($technicalKeywords as $keyword) {
            if (stripos($titleLower, $keyword) !== false) {
                return 4200;
            }
        }

        // Artigos práticos são mais diretos
        foreach ($practicalKeywords as $keyword) {
            if (stripos($titleLower, $keyword) !== false) {
                return 3200;
            }
        }

        // Padrão para artigos informativos gerais
        return 3500;
    }

    /**
     * Exibir resultados de uma categoria
     */
    private function displayCategoryResults(
        string $categoryName,
        int $inserted,
        int $skipped,
        int $errors
    ): void {
        $mode = $this->option('dry-run') ? 'Simulados' : 'Inseridos';
        
        $this->info("   ✅ {$categoryName} processada:");
        $this->line("      ├─ {$mode}: <fg=green>{$inserted}</>");
        $this->line("      ├─ Ignorados: <fg=yellow>{$skipped}</>");
        
        if ($errors > 0) {
            $this->line("      └─ Erros: <fg=red>{$errors}</>");
        } else {
            $this->line("      └─ Erros: <fg=green>0</>");
        }
        
        $this->newLine();
    }

    /**
     * Exibir resumo final da importação
     */
    private function displayFinalSummary(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                  📊 RESUMO FINAL DA IMPORTAÇÃO              ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $mode = $this->option('dry-run') ? 'Simulados' : 'Inseridos';

        $this->line("   {$mode}: <fg=green>{$this->totalInserted}</> artigos");
        $this->line("   Ignorados: <fg=yellow>{$this->totalSkipped}</> artigos (já existiam)");

        if ($this->totalErrors > 0) {
            $this->line("   Erros: <fg=red>{$this->totalErrors}</> artigos");
        }

        $grandTotal = $this->totalInserted + $this->totalSkipped;
        $this->line("   Total processado: <fg=cyan>{$grandTotal}</> artigos");

        $this->newLine();

        // Estatísticas por categoria
        if (!empty($this->categoryStats)) {
            $this->info('   📈 Detalhamento por Categoria:');
            $this->newLine();

            foreach ($this->categoryStats as $key => $stats) {
                $this->line("   📁 {$stats['name']}");
                $this->line("      ├─ {$mode}: <fg=green>{$stats['inserted']}</>");
                $this->line("      ├─ Ignorados: <fg=yellow>{$stats['skipped']}</>");
                $this->line("      └─ Erros: " . 
                    ($stats['errors'] > 0 ? "<fg=red>{$stats['errors']}</>" : "<fg=green>0</>")
                );
                $this->newLine();
            }
        }

        // Estatísticas de prioridade
        // if (!$this->option('dry-run') && $this->totalInserted > 0) {
        //     $this->displayPriorityStats();
        // }

        // Próximos passos
        if (!$this->option('dry-run') && $this->totalInserted > 0) {
            $this->info('✅ Artigos do blog importados com sucesso!');
            $this->newLine();
            $this->line('   🚀 Próximos passos:');
            $this->line('   ├─ Gerar artigos: <fg=cyan>php artisan generation:generate-standard</>');
            $this->line('   ├─ Ver estatísticas: <fg=cyan>php artisan generation:stats</>');
            $this->line('   └─ Filtrar por origem: <fg=cyan>where("origin", "blog")</>');
            $this->newLine();
            
            $this->comment('   💡 Dica: Use a flag "origin" na raiz para filtrar artigos do blog:');
            $this->line('      GenerationTempArticle::where("origin", "blog")->get()');
        }

        $this->newLine();
    }

    /**
     * Exibir estatísticas por prioridade
     */
    private function displayPriorityStats(): void
    {
        $stats = GenerationTempArticle::where('generation_status', 'pending')
            ->where('origin', 'blog')
            ->select('generation_priority', DB::raw('count(*) as total'))
            ->groupBy('generation_priority')
            ->get();

        if ($stats->isNotEmpty()) {
            $this->line('   📊 Distribuição por prioridade (artigos do blog):');

            foreach ($stats as $stat) {
                $priority = $stat->generation_priority;
                $total = $stat->total;
                $color = $this->getPriorityColor($priority);

                $this->line("      ├─ <fg={$color}>{$priority}</> : {$total} artigos");
            }

            $this->newLine();
        }
    }

    /**
     * Obter cor para prioridade
     */
    private function getPriorityColor(string $priority): string
    {
        return match(strtolower($priority)) {
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'white',
        };
    }
}

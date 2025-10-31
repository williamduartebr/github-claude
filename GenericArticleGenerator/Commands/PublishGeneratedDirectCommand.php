<?php

namespace Src\GenericArticleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;
use Src\GenericArticleGenerator\Traits\UpdatesMaintenanceEntities;

/**
 * PublishGeneratedDirectCommand
 * 
 * Publica artigos gerados pelo Claude API diretamente para produção (Article)
 * sem humanização de datas - usa timestamps reais do momento da publicação.
 * 
 * ORIGEM: GenerationTempArticle (collection MongoDB)
 * DESTINO: Article (collection MongoDB)
 * 
 * FILTROS:
 * - generation_status = 'generated'
 * - published_article_id = null (não publicado ainda)
 * 
 * FLUXO:
 * 1. Buscar artigos gerados e não publicados
 * 2. Validar estrutura do generated_json
 * 3. Criar Article com dados do generated_json
 * 4. Marcar como publicado no GenerationTempArticle
 * 
 * USO:
 * php artisan generated-article:publish-direct --limit=1
 * php artisan generated-article:publish-direct --limit=10 --force
 * php artisan generated-article:publish-direct --dry-run
 * 
 * @author Claude Sonnet 4.5
 * @version 1.0
 */
class PublishGeneratedDirectCommand extends Command
{
    use UpdatesMaintenanceEntities;

    protected $signature = 'generated-article:publish-direct 
                            {--limit=1 : Quantidade de artigos para publicar}
                            {--force : Forçar publicação mesmo com slug duplicado (adiciona sufixo)}
                            {--dry-run : Simulação sem publicar}
                            {--category= : Filtrar por category_slug}
                            {--priority= : Filtrar por prioridade (high|medium|low)}';

    protected $description = 'Publicar artigos gerados (GenerationTempArticle) para produção (Article) - Datas Reais';

    private array $stats = [
        'processed' => 0,
        'published' => 0,
        'skipped' => 0,
        'errors' => 0
    ];

    public function handle(): int
    {
        $this->displayHeader();

        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        // Buscar artigos prontos para publicação
        $articlesToPublish = $this->getArticlesToPublish($limit);

        if ($articlesToPublish->isEmpty()) {
            $this->warn('⚠️ Nenhum artigo gerado encontrado para publicação!');
            $this->displaySuggestions();
            return self::SUCCESS;
        }

        $this->displayArticlesSummary($articlesToPublish);

        if ($dryRun) {
            $this->info('🧪 DRY-RUN: Simulação concluída sem publicar');
            return self::SUCCESS;
        }

        if (!$this->confirm("Publicar {$articlesToPublish->count()} artigo(s)?", true)) {
            $this->info('❌ Operação cancelada');
            return self::SUCCESS;
        }

        $this->newLine();

        foreach ($articlesToPublish as $index => $tempArticle) {
            $processed = $this->stats['processed'] + 1;
            $this->info("📄 [{$processed}] {$tempArticle->title}");
            $this->publishArticle($tempArticle);
            $this->newLine();
        }

        $this->displayFinalStats();

        return $this->stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Buscar artigos prontos para publicação
     */
    private function getArticlesToPublish(int $limit)
    {
        $query = GenerationTempArticle::where('generation_status', 'generated')
            ->whereNull('published_article_id');

        // Filtros opcionais
        if ($category = $this->option('category')) {
            $query->where('category_slug', $category);
        }

        if ($priority = $this->option('priority')) {
            $query->where('generation_priority', $priority);
        }

        return $query->orderBy('generated_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Publicar artigo individual
     */
    private function publishArticle(GenerationTempArticle $tempArticle): void
    {
        try {
            // Validar estrutura do JSON
            if (empty($tempArticle->generated_json)) {
                $this->error("   ❌ generated_json vazio!");
                $this->stats['errors']++;
                $this->stats['processed']++;
                return;
            }

            $json = $tempArticle->generated_json;

            // Validar campos obrigatórios
            $requiredFields = ['title', 'slug', 'seo_data', 'metadata'];
            foreach ($requiredFields as $field) {
                if (!isset($json[$field])) {
                    $this->error("   ❌ Campo obrigatório ausente: {$field}");
                    $this->stats['errors']++;
                    $this->stats['processed']++;
                    return;
                }
            }

            // Verificar slug duplicado
            $slug = $json['slug'];
            if (Article::where('slug', $slug)->exists()) {
                if ($this->option('force')) {
                    $slug = $this->generateUniqueSlug($slug);
                    $this->warn("   ⚠️ Slug duplicado! Usando: {$slug}");
                } else {
                    $this->warn("   ⏭️ Artigo já existe (slug: {$slug}). Use --force para adicionar sufixo");
                    $this->stats['skipped']++;
                    $this->stats['processed']++;
                    return;
                }
            }

            $this->line("   📁 Categoria: {$json['category_name']} > {$json['subcategory_name']}");
            $this->line("   🔗 Slug: {$slug}");

            // Extrair content (pode estar na raiz ou em metadata.content_blocks)
            $content = $this->extractContent($json);

            // Preparar metadata (sem content_blocks se estava lá)
            $metadata = $json['metadata'] ?? [];
            if (isset($metadata['content_blocks'])) {
                unset($metadata['content_blocks']);
            }

            // Criar Article na produção
            $article = Article::create([
                'title' => $json['title'],
                'slug' => $slug,
                'template' => $json['template'] ?? 'generic_article',
                'category_id' => $json['category_id'],
                'category_name' => $json['category_name'],
                'category_slug' => $json['category_slug'],
                'subcategory_id' => $json['subcategory_id'] ?? null,
                'subcategory_name' => $json['subcategory_name'] ?? null,
                'subcategory_slug' => $json['subcategory_slug'] ?? null,
                'content' => $content, // ✅ Content na RAIZ
                'seo_data' => $json['seo_data'],
                'metadata' => $metadata, // ✅ Metadata sem content_blocks
                'extracted_entities' => $json['extracted_entities'] ?? [],
                'tags' => $this->extractTags($json),
                'related_topics' => $this->extractRelatedTopics($json),
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Marcar como publicado no GenerationTempArticle
            $tempArticle->markAsPublished($article->_id);

            // Ativar MaintenanceCategory e MaintenanceSubcategory se necessário
            $this->activateMaintenanceEntities($article);

            $this->info("   ✅ Publicado com sucesso! ID: {$article->_id}");
            $this->line("   📅 Data: " . now()->format('d/m/Y H:i:s'));

            $this->stats['published']++;

            Log::info('PublishGeneratedDirect: Artigo publicado', [
                'temp_article_id' => $tempArticle->_id,
                'article_id' => $article->_id,
                'title' => $article->title,
                'slug' => $article->slug,
                'category' => $json['category_name'],
            ]);
        } catch (\Exception $e) {
            $this->error("   💥 Erro: " . $e->getMessage());
            $this->stats['errors']++;

            Log::error('PublishGeneratedDirect: Erro ao publicar', [
                'temp_article_id' => $tempArticle->_id ?? 'N/A',
                'title' => $tempArticle->title ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $this->stats['processed']++;
    }

    /**
     * Extrair content do JSON (raiz ou metadata.content_blocks)
     * 
     * PRIORIDADE:
     * 1. content na raiz (estrutura nova ✅)
     * 2. metadata.content_blocks (estrutura antiga do Claude)
     */
    private function extractContent(array $json): array
    {
        // 1. Verificar se content está na raiz (CORRETO)
        if (isset($json['content']) && is_array($json['content'])) {
            $this->line("   ✅ Content encontrado na raiz (estrutura correta)");
            return $json['content'];
        }

        // 2. Verificar se está em metadata.content_blocks (ANTIGO)
        if (isset($json['metadata']['content_blocks']) && is_array($json['metadata']['content_blocks'])) {
            $this->warn("   ⚠️ Content encontrado em metadata.content_blocks (estrutura antiga)");
            $this->line("   🔄 Convertendo para estrutura correta...");

            // Converter content_blocks em estrutura para ViewModel
            return [
                'blocks' => $json['metadata']['content_blocks']
            ];
        }

        // 3. Fallback: retornar vazio e logar erro
        $this->error("   ❌ Content não encontrado em lugar nenhum!");
        Log::error('PublishGeneratedDirect: Content não encontrado', [
            'title' => $json['title'] ?? 'N/A',
            'has_content_root' => isset($json['content']),
            'has_metadata_content_blocks' => isset($json['metadata']['content_blocks']),
        ]);

        return [];
    }

    /**
     * Gerar slug único adicionando sufixo numérico
     */
    private function generateUniqueSlug(string $baseSlug): string
    {
        $counter = 1;
        $newSlug = $baseSlug;

        while (Article::where('slug', $newSlug)->exists()) {
            $newSlug = $baseSlug . '-' . $counter;
            $counter++;

            // Limite de segurança
            if ($counter > 100) {
                $newSlug = $baseSlug . '-' . uniqid();
                break;
            }
        }

        return $newSlug;
    }

    /**
     * Extrair tags do JSON
     */
    private function extractTags(array $json): array
    {
        $tags = [];

        // De seo_data
        if (!empty($json['seo_data']['primary_keyword'])) {
            $tags[] = $json['seo_data']['primary_keyword'];
        }

        if (!empty($json['seo_data']['secondary_keywords'])) {
            if (is_array($json['seo_data']['secondary_keywords'])) {
                $tags = array_merge($tags, $json['seo_data']['secondary_keywords']);
            }
        }

        // De metadata
        if (!empty($json['metadata']['keywords'])) {
            if (is_array($json['metadata']['keywords'])) {
                $tags = array_merge($tags, $json['metadata']['keywords']);
            }
        }

        return array_unique(array_filter($tags));
    }

    /**
     * Extrair tópicos relacionados do JSON
     */
    private function extractRelatedTopics(array $json): array
    {
        $topics = [];

        // De metadata.related_content
        if (!empty($json['metadata']['related_content'])) {
            foreach ($json['metadata']['related_content'] as $related) {
                if (!empty($related['title'])) {
                    $topics[] = [
                        'title' => $related['title'],
                        'slug' => $related['slug'] ?? \Illuminate\Support\Str::slug($related['title']),
                        'icon' => $related['icon'] ?? null
                    ];
                }
            }
        }

        // De seo_data.related_topics
        if (empty($topics) && !empty($json['seo_data']['related_topics'])) {
            foreach ($json['seo_data']['related_topics'] as $topic) {
                if (is_string($topic)) {
                    $topics[] = [
                        'title' => $topic,
                        'slug' => \Illuminate\Support\Str::slug($topic),
                        'icon' => null
                    ];
                }
            }
        }

        return $topics;
    }

    /**
     * Exibir header
     */
    private function displayHeader(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║   📤 PUBLICAR ARTIGOS GERADOS - DATAS REAIS              ║');
        $this->info('║   GenerationTempArticle → Article                        ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    /**
     * Exibir sumário de artigos
     */
    private function displayArticlesSummary($articles): void
    {
        $this->info('📋 ARTIGOS PRONTOS PARA PUBLICAÇÃO:');
        $this->table(
            ['#', 'Título', 'Categoria', 'Modelo', 'Custo', 'Gerado em'],
            $articles->map(function ($article, $index) {
                return [
                    $index + 1,
                    \Illuminate\Support\Str::limit($article->title, 40),
                    $article->generated_json['category_name'] ?? 'N/A',
                    strtoupper($article->generation_model_used ?? 'N/A'),
                    number_format($article->generation_cost ?? 0, 2),
                    $article->generated_at ? $article->generated_at->format('d/m/Y H:i') : 'N/A',
                ];
            })
        );
        $this->newLine();
    }

    /**
     * Exibir estatísticas finais
     */
    private function displayFinalStats(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║                    📊 RESULTADO                          ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("✅ Publicados: {$this->stats['published']}");
        $this->line("⏭️ Pulados: {$this->stats['skipped']}");
        $this->line("❌ Erros: {$this->stats['errors']}");
        $this->line("📊 Total processado: {$this->stats['processed']}");
        $this->newLine();

        if ($this->stats['published'] > 0) {
            $this->info('✅ Artigos publicados com sucesso!');
            $this->line('   Acesse o site para visualizar');
        }
    }

    /**
     * Exibir sugestões
     */
    private function displaySuggestions(): void
    {
        $this->newLine();
        $this->info('💡 SUGESTÕES:');
        $this->line('   • Gere artigos primeiro: php artisan temp-article:generate-standard');
        $this->line('   • Valide artigos: php artisan temp-article:validate');
        $this->line('   • Verifique status: php artisan temp-article:stats');
        $this->newLine();

        // Mostrar estatísticas rápidas
        $pending = GenerationTempArticle::where('generation_status', 'pending')->count();
        $generated = GenerationTempArticle::where('generation_status', 'generated')
            ->whereNull('published_article_id')
            ->count();

        $this->line("📊 Status atual:");
        $this->line("   Pendentes de geração: {$pending}");
        $this->line("   Gerados (prontos): {$generated}");
        $this->newLine();
    }
}
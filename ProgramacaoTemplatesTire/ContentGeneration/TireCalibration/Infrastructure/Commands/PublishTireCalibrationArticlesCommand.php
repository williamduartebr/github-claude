<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Support\Str;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;
use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

class PublishTireCalibrationArticlesCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles-temp:publish-drafts {--limit=1 : Número de artigos a processar}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Transfere os artigos temporários com status "draft" para a coleção permanente';

    /**
     * Cache para MaintenanceCategories já processadas
     */
    private array $processedCategories = [];

    /**
     * Execute o comando.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando transferência de artigos temporários...');

        $limit = (int) $this->option('limit');

        $draftArticles = TempArticle::where('has_specific_versions', true)
            ->where('status', 'draft')
            ->take($limit)
            ->get();

        $this->info("Encontrados {$draftArticles->count()} artigos para publicação.");

        $bar = $this->output->createProgressBar($draftArticles->count());
        $bar->start();

        $processed = 0;
        $errors = 0;

        foreach ($draftArticles as $draftArticle) {
            try {
                // Verificar se o slug já existe
                $slugExists = Article::where('slug', $draftArticle->slug)->exists();

                if ($slugExists) {
                    $this->error("Slug já existe: {$draftArticle->slug}");
                    $errors++;
                    $bar->advance();
                    continue;
                }

                // Extrair tags dos dados existentes
                $tags = $this->extractTags($draftArticle);

                // Extrair tópicos relacionados
                $relatedTopics = $this->extractRelatedTopics($draftArticle);

                // Determinar datas de publicação e atualização
                $createdAt = now();
                $updatedAt = now();

                // Criar o novo artigo
                Article::create([
                    'title' => $draftArticle->title,
                    'slug' => $draftArticle->slug,
                    'template' => $draftArticle->template,
                    'category_id' => $draftArticle->category_id,
                    'category_name' => $draftArticle->category_name,
                    'category_slug' => $draftArticle->category_slug,
                    'content' => $draftArticle->content,
                    'extracted_entities' => $draftArticle->extracted_entities,
                    'seo_data' => $draftArticle->seo_data,
                    'metadata' => $draftArticle->metadata,
                    'tags' => $tags,
                    'related_topics' => $relatedTopics,
                    'status' => 'published',
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                    'vehicle_info' => $draftArticle->vehicle_info,
                    'filter_data' => $draftArticle->filter_data,
                ]);

                $this->updateMaintenanceCategoryIfNeeded($draftArticle->category_slug,);

                // Atualizar o status do artigo temporário para evitar duplicação
                $draftArticle->status = 'published';
                $draftArticle->save();

                $processed++;
            } catch (\Exception $e) {
                $this->error("Erro ao processar artigo ID: {$draftArticle->_id} - {$e->getMessage()}");
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Processo concluído.");
        $this->info("Artigos publicados: {$processed}");

        if ($errors > 0) {
            $this->error("Erros encontrados: {$errors}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Extrai tags do artigo
     *
     * @param mixed $article
     * @return array
     */
    protected function extractTags($article)
    {
        $tags = [];

        // Extrair de palavras-chave primárias e secundárias
        if (!empty($article->seo_data['primary_keyword'])) {
            $tags[] = $article->seo_data['primary_keyword'];
        }

        if (!empty($article->seo_data['secondary_keywords']) && is_array($article->seo_data['secondary_keywords'])) {
            $tags = array_merge($tags, $article->seo_data['secondary_keywords']);
        }

        // Extrair de entidades extraídas
        if (!empty($article->extracted_entities)) {
            $relevantEntities = ['marca', 'modelo', 'categoria', 'tipo_veiculo', 'motorizacao'];

            foreach ($relevantEntities as $entity) {
                if (!empty($article->extracted_entities[$entity])) {
                    $tags[] = $article->extracted_entities[$entity];
                }
            }
        }

        // Remover duplicatas e valores vazios
        $tags = array_unique(array_filter($tags));

        return array_values($tags); // Resetar as chaves do array
    }

    /**
     * Extrai tópicos relacionados do artigo
     *
     * @param mixed $article
     * @return array
     */
    protected function extractRelatedTopics($article)
    {
        $topics = [];

        // Verificar se há conteúdo relacionado na metadata
        if (!empty($article->metadata['related_content']) && is_array($article->metadata['related_content'])) {
            foreach ($article->metadata['related_content'] as $related) {
                if (!empty($related['title']) && !empty($related['slug'])) {
                    $topics[] = [
                        'title' => $related['title'],
                        'slug' => $related['slug'],
                        'icon' => $related['icon'] ?? null
                    ];
                }
            }
        }

        // Se não encontrar na metadata, tentar em related_topics da seo_data
        if (empty($topics) && !empty($article->seo_data['related_topics']) && is_array($article->seo_data['related_topics'])) {
            foreach ($article->seo_data['related_topics'] as $topic) {
                $topics[] = [
                    'title' => $topic,
                    'slug' => Str::slug($topic),
                    'icon' => null
                ];
            }
        }

        return $topics;
    }

    /**
     * Atualiza MaintenanceCategory para to_follow = true se necessário
     */
    private function updateMaintenanceCategoryIfNeeded(string $slug): void
    {
        if (empty($slug)) {
            return;
        }

        // Evitar processamento duplicado da mesma categoria
        if (in_array($slug, $this->processedCategories)) {
            return;
        }

        try {
            $category = MaintenanceCategory::where('slug', $slug)
                ->where('to_follow', false)
                ->first();

            if ($category) {
                $category->update(['to_follow' => true]);
                $this->info("MaintenanceCategory '{$slug}' marcada como to_follow = true");
            }

            // Adicionar ao cache para evitar reprocessamento
            $this->processedCategories[] = $slug;
        } catch (\Exception $e) {
            $this->warn("Erro ao atualizar MaintenanceCategory '{$slug}': {$e->getMessage()}");
        }
    }
}

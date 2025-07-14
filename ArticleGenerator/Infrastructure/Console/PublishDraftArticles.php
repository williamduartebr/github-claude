<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

class PublishDraftArticles extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:publish-drafts 
                           {--update-tags : Atualizar tags de artigos existentes}
                           {--humanize-dates : Humanizar as datas dos artigos após publicação}
                           {--days=30 : Número de dias para distribuir os artigos ao humanizar}
                           {--imported-only : Processar apenas artigos com original_post_id}
                           {--new-only : Processar apenas artigos sem original_post_id}
                           {--keep-original-dates : Manter datas originais de publicação para artigos importados}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Transfere os artigos temporários com status "draft" para a coleção permanente';

    /**
     * Execute o comando.
     *
     * @return int
     */
    public function handle()
    {
        // Verificar se é apenas para atualizar tags
        if ($this->option('update-tags')) {
            return $this->updateAllArticlesTags();
        }
        
        $this->info('Iniciando transferência de artigos temporários...');

        // Filtrar pelo tipo de artigo (importado ou novo)
        $query = TempArticle::where('status', 'draft');
        
        if ($this->option('imported-only')) {
            $query->whereNotNull('original_post_id');
            $this->info('Processando apenas artigos importados (com original_post_id).');
        } elseif ($this->option('new-only')) {
            $query->whereNull('original_post_id');
            $this->info('Processando apenas artigos novos (sem original_post_id).');
        }
        
        $draftArticles = $query->get();
        
        $this->info("Encontrados {$draftArticles->count()} artigos para publicação.");
        
        $bar = $this->output->createProgressBar($draftArticles->count());
        $bar->start();
        
        $processed = 0;
        $errors = 0;
        
        foreach ($draftArticles as $draftArticle) {
            try {
                // Verificar se o slug já existe
                $slugExists = Article::where('slug', $draftArticle->new_slug)->exists();
                
                if ($slugExists) {
                    $this->error("Slug já existe: {$draftArticle->new_slug}");
                    $errors++;
                    $bar->advance();
                    continue;
                }
                
                // Extrair tags dos dados existentes
                $tags = $this->extractTags($draftArticle);
                
                // Extrair tópicos relacionados
                $relatedTopics = $this->extractRelatedTopics($draftArticle);
                
                // Determinar datas de publicação e atualização
                $createdAt = null;
                $updatedAt = now();
                
                // Se for um artigo importado (com original_post_id), usar a data original
                if (!empty($draftArticle->original_post_id) && !empty($draftArticle->published_at)) {
                    $createdAt = $draftArticle->published_at;
                } else {
                    // Para artigos novos, a data de publicação será atualizada pela humanização
                    $createdAt = now();
                }
                
                // Extrair informações para SEO e filtros
                $seoFilterData = $this->extractSeoFilterData($draftArticle);
                
                // Criar o novo artigo
                Article::create([
                    'title' => $draftArticle->title,
                    'slug' => $draftArticle->new_slug,
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
                    'original_post_id' => $draftArticle->original_post_id ?? null,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                    'vehicle_info' => $seoFilterData['vehicle_info'] ?? null,
                    'filter_data' => $seoFilterData['filter_data'] ?? null,
                ]);
                
                // Atualizar o status do artigo temporário para evitar duplicação
                $draftArticle->status = 'processed';
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
        
        // Humanizar as datas, se solicitado
        if ($this->option('humanize-dates')) {
            $this->info('Iniciando humanização de datas...');
            
            $options = [
                '--days' => $this->option('days'),
                '--keep-original-dates' => true // Sempre manter datas originais para artigos importados
            ];
            
            if ($this->option('imported-only')) {
                $options['--imported-only'] = true;
            } elseif ($this->option('new-only')) {
                $options['--new-only'] = true;
            }
            
            if ($this->option('start-date')) {
                $options['--start-date'] = $this->option('start-date');
            }
            
            $this->call('articles:humanize-dates', $options);
            
            // Também atribuir autores automaticamente
            $this->info('Atribuindo autores...');
            
            $authorOptions = [];
            
            if ($this->option('imported-only')) {
                $authorOptions['--imported-only'] = true;
            } elseif ($this->option('new-only')) {
                $authorOptions['--new-only'] = true;
            }
            
            $this->call('articles:assign-authors', $authorOptions);
        }
        
        if ($errors > 0) {
            $this->error("Erros encontrados: {$errors}");
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Atualiza as tags de todos os artigos existentes
     *
     * @return int
     */
    protected function updateAllArticlesTags()
    {
        $this->info('Iniciando atualização de tags de artigos existentes...');
        
        // Determinar quais artigos processar
        $query = Article::query();
        
        if ($this->option('imported-only')) {
            $query->whereNotNull('original_post_id');
            $this->info('Processando apenas artigos importados.');
        } elseif ($this->option('new-only')) {
            $query->whereNull('original_post_id');
            $this->info('Processando apenas artigos novos.');
        }
        
        // Contar todos os artigos para a barra de progresso
        $articlesCount = $query->count();
        
        if ($articlesCount === 0) {
            $this->warn('Nenhum artigo encontrado para processar.');
            return Command::SUCCESS;
        }
        
        $this->info("Encontrados {$articlesCount} artigos para atualização.");
        
        $bar = $this->output->createProgressBar($articlesCount);
        $bar->start();
        
        $updated = 0;
        
        // Processar artigos em lotes para evitar problemas de memória
        $perPage = 100;
        $page = 1;
        
        do {
            $articles = $query->forPage($page, $perPage)->get();
            
            if ($articles->isEmpty()) {
                break;
            }
            
            foreach ($articles as $article) {
                $tags = $this->extractTags($article);
                $relatedTopics = $this->extractRelatedTopics($article);
                
                Article::find($article->_id)
                    ->update([
                        'tags' => $tags,
                        'related_topics' => $relatedTopics,
                    ]);
                
                $updated++;
                $bar->advance();
            }
            
            // Limpar a memória
            $articles = null;
            gc_collect_cycles();
            
            // Avançar para a próxima página
            $page++;
            
        } while (true);
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("Processo concluído. Artigos atualizados: {$updated}");
        
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
     * Extrai dados de SEO e filtros do artigo
     *
     * @param mixed $article
     * @return array
     */
    protected function extractSeoFilterData($article)
    {
        $result = [
            'vehicle_info' => [],
            'filter_data' => []
        ];
        
        // Veículos: marca, modelo, ano, versão, motorização
        if (!empty($article->extracted_entities)) {
            $vehicleInfo = [];
            $filterData = [];
            
            // Mapeamento de campos para vehicle_info
            $vehicleFields = [
                'marca' => 'make',
                'modelo' => 'model',
                'ano' => 'year',
                'versao' => 'version',
                'motorizacao' => 'engine',
                'combustivel' => 'fuel',
                'categoria' => 'category',
                'tipo_veiculo' => 'vehicle_type'
            ];
            
            foreach ($vehicleFields as $sourceField => $targetField) {
                if (!empty($article->extracted_entities[$sourceField])) {
                    $vehicleInfo[$targetField] = $article->extracted_entities[$sourceField];
                    
                    // Adicionar aos dados de filtro também
                    $filterData[$sourceField] = $article->extracted_entities[$sourceField];
                }
            }
            
            // Tratar ano como array (pode ser um intervalo)
            if (!empty($vehicleInfo['year']) && strpos($vehicleInfo['year'], '-') !== false) {
                $yearRange = explode('-', $vehicleInfo['year']);
                if (count($yearRange) == 2) {
                    $vehicleInfo['year_start'] = trim($yearRange[0]);
                    $vehicleInfo['year_end'] = trim($yearRange[1]);
                    $vehicleInfo['year_range'] = true;
                }
            }
            
            // Adicionar slug combinados para SEO e filtros
            if (!empty($vehicleInfo['make'])) {
                $makeSlug = \Illuminate\Support\Str::slug($vehicleInfo['make']);
                $vehicleInfo['make_slug'] = $makeSlug;
                $filterData['marca_slug'] = $makeSlug;
                
                if (!empty($vehicleInfo['model'])) {
                    $modelSlug = \Illuminate\Support\Str::slug($vehicleInfo['model']);
                    $vehicleInfo['model_slug'] = $modelSlug;
                    $filterData['modelo_slug'] = $modelSlug;
                    
                    // Slug combinado marca-modelo
                    $vehicleInfo['make_model_slug'] = $makeSlug . '-' . $modelSlug;
                    $filterData['marca_modelo_slug'] = $makeSlug . '-' . $modelSlug;
                }
            }
            
            $result['vehicle_info'] = $vehicleInfo;
            $result['filter_data'] = $filterData;
        }
        
        return $result;
    }
}

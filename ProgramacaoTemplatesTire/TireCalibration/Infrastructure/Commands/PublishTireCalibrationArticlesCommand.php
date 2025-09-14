<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

/**
 * PublishTireCalibrationArticlesCommand
 * 
 * Transfere artigos de calibragem de pneus de TempArticle para Article (publicação final).
 * Segue o mesmo padrão do PublishDraftArticles com adaptações específicas para tire calibration.
 * 
 * CRITÉRIOS DE FILTRO:
 * - source_collection = 'tire_calibrations'
 * - status = 'draft'
 * - template = 'tire_calibration*'
 * 
 * FLUXO:
 * TempArticle (tire calibration) → Article (publicado)
 * 
 * @author Claude Sonnet 4
 * @version 1.0
 */
class PublishTireCalibrationArticlesCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     */
    protected $signature = 'tire-calibration:publish-articles
                           {--update-tags : Atualizar tags de artigos existentes}
                           {--humanize-dates : Humanizar as datas dos artigos após publicação}
                           {--days=30 : Número de dias para distribuir os artigos ao humanizar}
                           {--limit=100 : Número máximo de artigos a processar}
                           {--dry-run : Simular execução sem persistir dados}
                           {--force : Republica artigos mesmo se slug já existir}
                           {--start-date= : Data inicial para humanização (Y-m-d)}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Transfere artigos de calibragem de pneus de TempArticle para Article (publicação final)';

    /**
     * Execute o comando.
     */
    public function handle(): int
    {
        // Verificar se é apenas para atualizar tags
        if ($this->option('update-tags')) {
            return $this->updateExistingTireArticlesTags();
        }

        $this->displayHeader();

        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        try {
            // Buscar artigos de calibragem elegíveis
            $draftArticles = $this->findEligibleTireArticles($limit);

            if ($draftArticles->isEmpty()) {
                $this->warn('Nenhum artigo de calibragem de pneus encontrado para publicação.');
                return Command::SUCCESS;
            }

            $this->info("Encontrados {$draftArticles->count()} artigos de calibragem para publicação.");

            if ($dryRun) {
                $this->warn('MODO SIMULAÇÃO - Nenhuma alteração será persistida');
            }

            // Processar artigos
            $results = $this->processTireArticles($draftArticles, $dryRun, $force);

            // Exibir resultados
            $this->displayResults($results);

            // Executar pós-processamento se solicitado
            if (!$dryRun && $results['published'] > 0) {
                $this->runPostProcessing();
            }

            return $results['errors'] === 0 ? Command::SUCCESS : Command::FAILURE;
        } catch (\Exception $e) {
            $this->error("Erro fatal: {$e->getMessage()}");
            Log::error('PublishTireCalibrationArticlesCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Exibe cabeçalho do comando
     */
    private function displayHeader(): void
    {
        $this->info('PUBLICAÇÃO FINAL - ARTIGOS DE CALIBRAGEM DE PNEUS');
        $this->info(now()->format('d/m/Y H:i:s'));
        $this->info('TempArticle → Article');
        $this->newLine();
    }

    /**
     * Busca artigos de calibragem elegíveis para publicação
     */
    private function findEligibleTireArticles(int $limit)
    {
        $this->info('Buscando artigos de calibragem elegíveis...');

        return TempArticle::where('status', 'draft')
            ->where('source_collection', 'tire_calibrations')
            // ->where('template', 'like', 'tire_calibration%')
            ->whereNotNull('source_document_id')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Processa os artigos de calibragem
     */
    private function processTireArticles($draftArticles, bool $dryRun, bool $force): array
    {
        $bar = $this->output->createProgressBar($draftArticles->count());
        $bar->start();

        $results = [
            'published' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => []
        ];

        foreach ($draftArticles as $draftArticle) {
            try {
                $result = $this->publishTireArticle($draftArticle, $dryRun, $force);

                if ($result['published']) {
                    $results['published']++;
                } else {
                    $results['skipped']++;
                }

                $results['details'][] = $result;
            } catch (\Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'published' => false,
                    'slug' => $draftArticle->new_slug ?? $draftArticle->slug,
                    'error' => $e->getMessage()
                ];

                Log::error('Failed to publish tire calibration article', [
                    'temp_article_id' => $draftArticle->_id,
                    'error' => $e->getMessage()
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        return $results;
    }

    /**
     * Gera slug final para Article
     */
    private function generateFinalSlug($draftArticle): string
    {
        $make = Str::slug($draftArticle->vehicle_make);
        $model = Str::slug($draftArticle->vehicle_model);

        return "calibragem-pneu-{$make}-{$model}";
    }


    /**
     * Publica um artigo individual de calibragem
     */
    private function publishTireArticle($draftArticle, bool $dryRun, bool $force): array
    {
        $finalSlug = $this->generateFinalSlug($draftArticle);

        // Verificar se o slug já existe
        if (!$force) {
            $slugExists = Article::where('slug', $finalSlug)->exists();

            if ($slugExists) {
                return [
                    'published' => false,
                    'slug' => $finalSlug,
                    'reason' => 'Slug já existe'
                ];
            }
        }

        if ($dryRun) {
            return [
                'published' => true,
                'slug' => $finalSlug,
                'reason' => 'Simulação bem-sucedida'
            ];
        }

        // Extrair e enriquecer dados específicos para calibragem
        $articleData = $this->buildTireArticleData($draftArticle, $finalSlug);

        // Criar o artigo final
        if ($force && Article::where('slug', $finalSlug)->exists()) {
            Article::where('slug', $finalSlug)->delete();
        }

        Article::create($articleData);

        // Marcar TempArticle como processado
        $draftArticle->update([
            'status' => 'published',
            'final_published_at' => now(),
            'final_article_slug' => $finalSlug
        ]);

        Log::info('Tire calibration article published successfully', [
            'temp_article_id' => $draftArticle->_id,
            'final_slug' => $finalSlug,
            'vehicle' => $this->getVehicleInfo($draftArticle)
        ]);

        return [
            'published' => true,
            'slug' => $finalSlug,
            'title' => $articleData['title']
        ];
    }

    /**
     * Constrói dados do artigo final para calibragem
     */
    private function buildTireArticleData($draftArticle, string $finalSlug): array
    {
        // Tags específicas para calibragem de pneus
        $tags = $this->extractTireCalibrationTags($draftArticle);

        // Tópicos relacionados específicos
        $relatedTopics = $this->extractTireRelatedTopics($draftArticle);

        // Dados de SEO e filtros
        $seoFilterData = $this->extractTireSeoFilterData($draftArticle);

        return [
            'title' => $draftArticle->title,
            'slug' => $finalSlug,
            'template' => $draftArticle->template,
            'category_id' => $draftArticle->category_id,
            'category_name' => $draftArticle->category_name,
            'category_slug' => $draftArticle->category_slug,
            'content' => $draftArticle->content,
            'extracted_entities' => $draftArticle->extracted_entities,
            'seo_data' => $draftArticle->seo_data,
            'metadata' => $this->enrichTireMetadata($draftArticle),
            'tags' => $tags,
            'related_topics' => $relatedTopics,
            'status' => 'published',
            'original_post_id' => null, // Artigos novos de calibragem
            'created_at' => now(), // Será humanizada se solicitado
            'updated_at' => now(),
            'published_at' => now(),
            'vehicle_info' => $seoFilterData['vehicle_info'],
            'filter_data' => $seoFilterData['filter_data'],
            'author' => $this->assignTireAuthor($draftArticle),
            'source_collection' => 'tire_calibrations',
            'source_document_id' => $draftArticle->source_document_id,
            'humanized_at' => null // Será preenchida na humanização
        ];
    }

    /**
     * Extrai tags específicas para artigos de calibragem de pneus
     */
    private function extractTireCalibrationTags($draftArticle): array
    {
        $tags = [];

        // Tags base de SEO
        if (!empty($draftArticle->seo_data['primary_keyword'])) {
            $tags[] = $draftArticle->seo_data['primary_keyword'];
        }

        if (!empty($draftArticle->seo_data['secondary_keywords']) && is_array($draftArticle->seo_data['secondary_keywords'])) {
            $tags = array_merge($tags, $draftArticle->seo_data['secondary_keywords']);
        }

        // Tags específicas de veículos
        $entities = $draftArticle->extracted_entities ?? [];
        $vehicleTags = ['marca', 'modelo', 'categoria', 'tipo_veiculo'];

        foreach ($vehicleTags as $entity) {
            if (!empty($entities[$entity])) {
                $tags[] = $entities[$entity];
            }
        }

        // Tags específicas de calibragem
        $calibrationTags = [
            'calibragem de pneus',
            'pressão dos pneus',
            'manutenção automotiva',
            'segurança veicular',
            'TPMS'
        ];

        $tags = array_merge($tags, $calibrationTags);

        // Adicionar marca + modelo como tag combinada
        if (!empty($entities['marca']) && !empty($entities['modelo'])) {
            $tags[] = $entities['marca'] . ' ' . $entities['modelo'];
        }

        // Remover duplicatas e valores vazios
        $tags = array_unique(array_filter($tags));

        return array_values($tags);
    }

    /**
     * Extrai tópicos relacionados específicos para calibragem de pneus
     */
    private function extractTireRelatedTopics($draftArticle): array
    {
        $topics = [];
        $entities = $draftArticle->extracted_entities ?? [];

        // Se há informações de veículo, criar tópicos relacionados
        if (!empty($entities['marca']) && !empty($entities['modelo'])) {
            $vehicle = $entities['marca'] . ' ' . $entities['modelo'];

            // Tópicos relacionados padrão para calibragem
            $relatedTopics = [
                [
                    'title' => "Óleo Recomendado para {$vehicle}",
                    'slug' => Str::slug("oleo-recomendado-{$vehicle}"),
                    'icon' => 'oil-can'
                ],
                [
                    'title' => "Filtro de Ar {$vehicle}",
                    'slug' => Str::slug("filtro-ar-{$vehicle}"),
                    'icon' => 'air-filter'
                ],
                [
                    'title' => "Consumo de Combustível {$vehicle}",
                    'slug' => Str::slug("consumo-combustivel-{$vehicle}"),
                    'icon' => 'fuel-pump'
                ],
                [
                    'title' => "Manutenção Preventiva {$vehicle}",
                    'slug' => Str::slug("manutencao-preventiva-{$vehicle}"),
                    'icon' => 'wrench'
                ]
            ];

            $topics = $relatedTopics;
        }

        // Verificar se há conteúdo relacionado já definido
        if (empty($topics) && !empty($draftArticle->metadata['related_content'])) {
            foreach ($draftArticle->metadata['related_content'] as $related) {
                if (!empty($related['title']) && !empty($related['slug'])) {
                    $topics[] = [
                        'title' => $related['title'],
                        'slug' => $related['slug'],
                        'icon' => $related['icon'] ?? 'tire-pressure'
                    ];
                }
            }
        }

        return $topics;
    }

    /**
     * Extrai dados de SEO e filtros específicos para calibragem de pneus
     */
    private function extractTireSeoFilterData($draftArticle): array
    {
        $result = [
            'vehicle_info' => [],
            'filter_data' => []
        ];

        $entities = $draftArticle->extracted_entities ?? [];
        $vehicleInfo = $draftArticle->vehicle_info ?? [];

        if (!empty($entities)) {
            $vehicleInfoData = [];
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
                $value = $entities[$sourceField] ?? $vehicleInfo[$sourceField] ?? null;

                if (!empty($value)) {
                    $vehicleInfoData[$targetField] = $value;
                    $filterData[$sourceField] = $value;
                }
            }

            // Adicionar dados específicos de pneus se disponível
            if (!empty($draftArticle->metadata['vehicle_specifications']['tire_size'])) {
                $vehicleInfoData['tire_size'] = $draftArticle->metadata['vehicle_specifications']['tire_size'];
                $filterData['medida_pneus'] = $draftArticle->metadata['vehicle_specifications']['tire_size'];
            }

            // Adicionar slugs
            if (!empty($vehicleInfoData['make'])) {
                $makeSlug = Str::slug($vehicleInfoData['make']);
                $vehicleInfoData['make_slug'] = $makeSlug;
                $filterData['marca_slug'] = $makeSlug;

                if (!empty($vehicleInfoData['model'])) {
                    $modelSlug = Str::slug($vehicleInfoData['model']);
                    $vehicleInfoData['model_slug'] = $modelSlug;
                    $filterData['modelo_slug'] = $modelSlug;

                    // Slug combinado
                    $vehicleInfoData['make_model_slug'] = $makeSlug . '-' . $modelSlug;
                    $filterData['marca_modelo_slug'] = $makeSlug . '-' . $modelSlug;
                }
            }

            $result['vehicle_info'] = $vehicleInfoData;
            $result['filter_data'] = $filterData;
        }

        return $result;
    }

    /**
     * Enriquece metadados específicos para calibragem de pneus
     */
    private function enrichTireMetadata($draftArticle): array
    {
        $metadata = $draftArticle->metadata ?? [];

        // Adicionar informações específicas de calibragem
        $metadata['article_type'] = 'tire_calibration';
        $metadata['content_focus'] = 'procedimento técnico';
        $metadata['target_audience'] = 'proprietários de veículos';

        // Informações de processamento
        $metadata['processing_pipeline'] = 'TireCalibration->TempArticle->Article';
        $metadata['published_via'] = 'PublishTireCalibrationArticlesCommand';
        $metadata['final_published_at'] = now()->toISOString();

        // Manter dados técnicos importantes
        if (!empty($draftArticle->metadata['vehicle_specifications'])) {
            $metadata['technical_data'] = $draftArticle->metadata['vehicle_specifications'];
        }

        return $metadata;
    }

    /**
     * Atribui autor apropriado para artigos de calibragem
     */
    private function assignTireAuthor($draftArticle): array
    {
        // Autores especializados em manutenção automotiva
        $authors = [
            [
                'name' => 'Equipe Editorial',
                'bio' => 'Equipe especializada em conteúdo automotivo'
            ],
            [
                'name' => 'Departamento Técnico',
                'bio' => 'Engenheiros e mecânicos especializados'
            ],
            [
                'name' => 'Redação',
                'bio' => 'Editores especialistas em veículos'
            ],
            [
                'name' => 'Equipe de Conteúdo',
                'bio' => 'Especialistas em informação automotiva'
            ]
        ];

        // Selecionar autor baseado no hash do slug para consistência
        $index = crc32($draftArticle->slug) % count($authors);
        return $authors[$index];
    }

    /**
     * Obtém informações do veículo para logs
     */
    private function getVehicleInfo($draftArticle): string
    {
        $entities = $draftArticle->extracted_entities ?? [];
        $make = $entities['marca'] ?? 'Unknown';
        $model = $entities['modelo'] ?? 'Unknown';

        return "{$make} {$model}";
    }

    /**
     * Executa pós-processamento após publicação
     */
    private function runPostProcessing(): void
    {
        // Humanizar as datas, se solicitado
        if ($this->option('humanize-dates')) {
            $this->info('Iniciando humanização de datas...');

            $humanizeOptions = [
                '--days' => $this->option('days'),
                // '--tire-calibration-only' => true // Flag específica para artigos de calibragem
            ];

            if ($this->option('start-date')) {
                $humanizeOptions['--start-date'] = $this->option('start-date');
            }

            $this->call('articles:humanize-dates', $humanizeOptions);

            $this->info('Humanização concluída.');
        }
    }

    /**
     * Atualiza tags de artigos de calibragem já publicados
     */
    protected function updateExistingTireArticlesTags(): int
    {
        $this->info('Atualizando tags de artigos de calibragem existentes...');

        // Buscar artigos de calibragem já publicados
        $query = Article::where('template', 'like', 'tire_calibration%')
            ->where('source_collection', 'tire_calibrations');

        $articlesCount = $query->count();

        if ($articlesCount === 0) {
            $this->warn('Nenhum artigo de calibragem encontrado para atualização.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$articlesCount} artigos de calibragem para atualização.");

        $bar = $this->output->createProgressBar($articlesCount);
        $bar->start();

        $updated = 0;
        $perPage = 50;
        $page = 1;

        do {
            $articles = $query->forPage($page, $perPage)->get();

            if ($articles->isEmpty()) {
                break;
            }

            foreach ($articles as $article) {
                $tags = $this->extractTireCalibrationTags($article);
                $relatedTopics = $this->extractTireRelatedTopics($article);

                Article::find($article->_id)->update([
                    'tags' => $tags,
                    'related_topics' => $relatedTopics,
                    'updated_at' => now()
                ]);

                $updated++;
                $bar->advance();
            }

            $articles = null;
            gc_collect_cycles();
            $page++;
        } while (true);

        $bar->finish();
        $this->newLine(2);

        $this->info("Processo concluído. Artigos de calibragem atualizados: {$updated}");

        return Command::SUCCESS;
    }

    /**
     * Exibe resultados da publicação
     */
    private function displayResults(array $results): void
    {
        $this->info('RESULTADOS DA PUBLICAÇÃO:');
        $this->line("   Publicados: {$results['published']}");
        $this->line("   Ignorados: {$results['skipped']}");
        $this->line("   Erros: {$results['errors']}");

        if ($results['errors'] > 0) {
            $this->newLine();
            $this->warn('ERROS ENCONTRADOS:');
            foreach ($results['details'] as $detail) {
                if (!$detail['published'] && isset($detail['error'])) {
                    $this->line("   • {$detail['slug']}: {$detail['error']}");
                }
            }
        }

        if ($results['published'] > 0) {
            $this->newLine();
            $this->info('Artigos de calibragem publicados com sucesso!');

            if (!$this->option('humanize-dates')) {
                $this->line('Dica: Use --humanize-dates para distribuir as datas de publicação.');
            }
        }
    }
}

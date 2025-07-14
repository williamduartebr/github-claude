<?php

namespace Src\ArticleGenerator\Domain\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class AutoSyncService
{
    private array $syncResults = [];

    /**
     * Sincronização completa automática após agendamento de artigos
     */
    public function performCompleteSync(Collection $scheduledArticles): array
    {
        $this->syncResults = [
            'started_at' => now(),
            'articles_processed' => 0,
            'vehicle_data_synced' => 0,
            'authors_assigned' => 0,
            'mysql_records_created' => 0,
            'errors' => [],
            'warnings' => [],
        ];

        try {
            // 1. Verificar se tabelas existem (sem criar)
            if (!$this->checkMySQLTablesExist()) {
                $this->syncResults['warnings'][] = "Tabelas MySQL não encontradas. Sync de veículos pulado.";
                // Ainda processa autores
                foreach ($scheduledArticles as $articleData) {
                    $this->assignAuthorToArticle($articleData['article']);
                    $this->syncResults['articles_processed']++;
                    $this->syncResults['authors_assigned']++;
                }
                return $this->syncResults;
            }

            // 2. Processar cada artigo agendado
            foreach ($scheduledArticles as $articleData) {
                $this->processSingleArticle($articleData);
            }

            // 3. Atualizar contadores finais
            $this->updateArticleCounters();

            // 4. Gerar estatísticas finais
            $this->syncResults['completed_at'] = now();
            $this->syncResults['duration_seconds'] = $this->syncResults['completed_at']
                ->diffInSeconds($this->syncResults['started_at']);

        } catch (\Exception $e) {
            $this->syncResults['errors'][] = "Erro geral na sincronização: {$e->getMessage()}";
        }

        return $this->syncResults;
    }

    /**
     * Processa um único artigo para sincronização
     */
    private function processSingleArticle(array $articleData): void
    {
        try {
            $article = $articleData['article'];
            $articleId = (string) $article->_id;

            // 1. Extrair metadados de veículo
            $vehicleData = $this->extractVehicleMetadata($article);
            
            // 2. Atribuir autor automaticamente
            $authorData = $this->assignAuthorToArticle($article);

            // 3. Sincronizar com MySQL se houver dados de veículo
            if (!empty($vehicleData['vehicle_info']['make'])) {
                $this->syncVehicleDataToMySQL($articleId, $article, $vehicleData);
                $this->syncResults['vehicle_data_synced']++;
            }

            // 4. Registrar sucesso
            $this->syncResults['articles_processed']++;
            if (!empty($authorData)) {
                $this->syncResults['authors_assigned']++;
            }

        } catch (\Exception $e) {
            $this->syncResults['errors'][] = "Erro ao processar artigo {$article->_id}: {$e->getMessage()}";
        }
    }

    /**
     * Extrai metadados de veículo do artigo
     */
    private function extractVehicleMetadata($article): array
    {
        $result = [
            'vehicle_info' => [],
            'filter_data' => []
        ];

        if (empty($article->extracted_entities)) {
            return $result;
        }

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
                $filterData[$sourceField] = $article->extracted_entities[$sourceField];
            }
        }

        // Tratar ano como intervalo se necessário
        if (!empty($vehicleInfo['year']) && strpos($vehicleInfo['year'], '-') !== false) {
            $yearRange = explode('-', $vehicleInfo['year']);
            if (count($yearRange) == 2) {
                $vehicleInfo['year_start'] = trim($yearRange[0]);
                $vehicleInfo['year_end'] = trim($yearRange[1]);
                $vehicleInfo['year_range'] = true;
            }
        }

        // Gerar slugs para SEO
        if (!empty($vehicleInfo['make'])) {
            $makeSlug = Str::slug($vehicleInfo['make']);
            $vehicleInfo['make_slug'] = $makeSlug;
            $filterData['marca_slug'] = $makeSlug;

            if (!empty($vehicleInfo['model'])) {
                $modelSlug = Str::slug($vehicleInfo['model']);
                $vehicleInfo['model_slug'] = $modelSlug;
                $filterData['modelo_slug'] = $modelSlug;
                $vehicleInfo['make_model_slug'] = $makeSlug . '-' . $modelSlug;
                $filterData['marca_modelo_slug'] = $makeSlug . '-' . $modelSlug;
            }
        }

        return [
            'vehicle_info' => $vehicleInfo,
            'filter_data' => $filterData
        ];
    }

    /**
     * Atribui autor automaticamente baseado no tipo do artigo
     */
    private function assignAuthorToArticle($article): array
    {
        // Autores disponíveis
        $authors = [
            'imported' => [
                'William Duarte' => 'Entusiasta automotivo e mecânica automotiva',
                'Marley Rondon' => 'Especialista em veículos e mecânica automotiva',
            ],
            'new' => [
                'Equipe Editorial' => 'Equipe especializada em conteúdo automotivo',
                'Departamento Técnico' => 'Engenheiros e mecânicos especializados',
                'Redação' => 'Editores especialistas em veículos',
                'Equipe de Conteúdo' => 'Especialistas em informação automotiva'
            ]
        ];

        // Determinar tipo do artigo
        $isImported = !empty($article->original_post_id);
        $authorPool = $isImported ? $authors['imported'] : $authors['new'];

        // Selecionar autor aleatório
        $authorNames = array_keys($authorPool);
        $selectedAuthorName = $authorNames[array_rand($authorNames)];
        $selectedAuthorBio = $authorPool[$selectedAuthorName];

        return [
            'name' => $selectedAuthorName,
            'bio' => $selectedAuthorBio,
            'type' => $isImported ? 'imported' : 'new'
        ];
    }

    /**
     * Sincroniza dados de veículo com MySQL (INSERT apenas se não existir)
     */
    private function syncVehicleDataToMySQL(string $articleId, $article, array $vehicleData): void
    {
        try {
            $vehicleInfo = $vehicleData['vehicle_info'];

            // Verificar se já existe registro para este artigo
            $existingRecord = DB::connection('mysql')
                ->table('vehicle_models')
                ->where('article_id', $articleId)
                ->first();

            if ($existingRecord) {
                // Já existe, pular
                return;
            }

            // Preparar dados para vehicle_models
            $vehicleModelData = [
                'article_id' => $articleId,
                'make' => $vehicleInfo['make'] ?? null,
                'make_slug' => $vehicleInfo['make_slug'] ?? null,
                'model' => $vehicleInfo['model'] ?? null,
                'model_slug' => $vehicleInfo['model_slug'] ?? null,
                'year_start' => $vehicleInfo['year_start'] ?? null,
                'year_end' => $vehicleInfo['year_end'] ?? null,
                'year_range' => $vehicleInfo['year_range'] ?? false,
                'engine' => $vehicleInfo['engine'] ?? null,
                'version' => $vehicleInfo['version'] ?? null,
                'fuel' => $vehicleInfo['fuel'] ?? null,
                'category' => $vehicleInfo['category'] ?? null,
                'vehicle_type' => $vehicleInfo['vehicle_type'] ?? null,
                'article_title' => $article->title,
                'article_slug' => $article->new_slug ?? $article->slug,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Inserir apenas se não existe
            DB::connection('mysql')->table('vehicle_models')->insert($vehicleModelData);

            // Sincronizar marcas (apenas se não existir)
            if (!empty($vehicleInfo['make']) && !empty($vehicleInfo['make_slug'])) {
                $this->syncMakeToMySQLIfNotExists($vehicleInfo);
            }

            // Sincronizar modelos (apenas se não existir)
            if (!empty($vehicleInfo['make_slug']) && !empty($vehicleInfo['model']) && !empty($vehicleInfo['model_slug'])) {
                $this->syncModelToMySQLIfNotExists($vehicleInfo);
            }

            $this->syncResults['mysql_records_created']++;

        } catch (\Exception $e) {
            $this->syncResults['errors'][] = "Erro ao sincronizar MySQL para artigo {$articleId}: {$e->getMessage()}";
        }
    }

    /**
     * Sincroniza marca para tabela makes (apenas se não existir)
     */
    private function syncMakeToMySQLIfNotExists(array $vehicleInfo): void
    {
        // Verificar se já existe
        $exists = DB::connection('mysql')
            ->table('makes')
            ->where('slug', $vehicleInfo['make_slug'])
            ->exists();

        if ($exists) {
            return; // Já existe, pular
        }

        $makeData = [
            'name' => $vehicleInfo['make'],
            'slug' => $vehicleInfo['make_slug'],
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            DB::connection('mysql')->table('makes')->insert($makeData);
        } catch (\Exception $e) {
            // Se deu erro de duplicata, ignorar (race condition)
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                throw $e;
            }
        }
    }

    /**
     * Sincroniza modelo para tabela models (apenas se não existir)
     */
    private function syncModelToMySQLIfNotExists(array $vehicleInfo): void
    {
        // Verificar se já existe
        $exists = DB::connection('mysql')
            ->table('models')
            ->where('make_slug', $vehicleInfo['make_slug'])
            ->where('slug', $vehicleInfo['model_slug'])
            ->exists();

        if ($exists) {
            return; // Já existe, pular
        }

        $modelData = [
            'make_slug' => $vehicleInfo['make_slug'],
            'name' => $vehicleInfo['model'],
            'slug' => $vehicleInfo['model_slug'],
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            DB::connection('mysql')->table('models')->insert($modelData);
        } catch (\Exception $e) {
            // Se deu erro de duplicata, ignorar (race condition)
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                throw $e;
            }
        }
    }

    /**
     * Verifica se as tabelas MySQL existem
     */
    private function checkMySQLTablesExist(): bool
    {
        try {
            $schema = DB::connection('mysql')->getSchemaBuilder();
            
            return $schema->hasTable('makes') && 
                   $schema->hasTable('models') && 
                   $schema->hasTable('vehicle_models');
                   
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Atualiza contadores de artigos nas tabelas MySQL
     */
    private function updateArticleCounters(): void
    {
        try {
            // Atualizar contador de artigos por marca
            DB::connection('mysql')->statement("
                UPDATE makes m SET article_count = (
                    SELECT COUNT(*) FROM vehicle_models vm 
                    WHERE vm.make_slug = m.slug
                )
            ");

            // Atualizar contador de artigos por modelo
            DB::connection('mysql')->statement("
                UPDATE models mo SET article_count = (
                    SELECT COUNT(*) FROM vehicle_models vm 
                    WHERE vm.make_slug = mo.make_slug AND vm.model_slug = mo.slug
                )
            ");

        } catch (\Exception $e) {
            $this->syncResults['warnings'][] = "Erro ao atualizar contadores: {$e->getMessage()}";
        }
    }

    /**
     * Garante que as tabelas MySQL existem
     */
    private function ensureMySQLTablesExist(): void
    {
        try {
            $schema = DB::connection('mysql')->getSchemaBuilder();

            // Criar tabela makes se não existir
            if (!$schema->hasTable('makes')) {
                $schema->create('makes', function ($table) {
                    $table->id();
                    $table->string('name');
                    $table->string('slug')->unique();
                    $table->string('logo_url')->nullable();
                    $table->text('description')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->integer('article_count')->default(0);
                    $table->timestamps();
                });
            }

            // Criar tabela models se não existir
            if (!$schema->hasTable('models')) {
                $schema->create('models', function ($table) {
                    $table->id();
                    $table->string('make_slug');
                    $table->string('name');
                    $table->string('slug');
                    $table->string('image_url')->nullable();
                    $table->text('description')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->integer('article_count')->default(0);
                    $table->timestamps();

                    $table->unique(['make_slug', 'slug']);
                    $table->index('make_slug');
                });
            }

            // Criar tabela vehicle_models se não existir
            if (!$schema->hasTable('vehicle_models')) {
                $schema->create('vehicle_models', function ($table) {
                    $table->id();
                    $table->string('article_id');
                    $table->string('make')->nullable();
                    $table->string('make_slug')->nullable();
                    $table->string('model')->nullable();
                    $table->string('model_slug')->nullable();
                    $table->string('year_start')->nullable();
                    $table->string('year_end')->nullable();
                    $table->boolean('year_range')->default(false);
                    $table->string('engine')->nullable();
                    $table->string('version')->nullable();
                    $table->string('fuel')->nullable();
                    $table->string('category')->nullable();
                    $table->string('vehicle_type')->nullable();
                    $table->string('article_title');
                    $table->string('article_slug');
                    $table->timestamps();

                    $table->unique('article_id');
                    $table->index('make_slug');
                    $table->index('model_slug');
                    $table->index(['make_slug', 'model_slug']);
                });
            }

        } catch (\Exception $e) {
            $this->syncResults['errors'][] = "Erro ao criar tabelas MySQL: {$e->getMessage()}";
        }
    }

    /**
     * Sincronização específica apenas para dados de veículo
     */
    public function syncVehicleDataOnly(Collection $articles): array
    {
        $results = [
            'processed' => 0,
            'synced' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        // Verificar se tabelas existem
        if (!$this->checkMySQLTablesExist()) {
            $results['errors'][] = "Tabelas MySQL não encontradas";
            return $results;
        }

        foreach ($articles as $article) {
            try {
                // Verificar se já existe no MySQL
                $articleId = (string) $article->_id;
                $existingRecord = DB::connection('mysql')
                    ->table('vehicle_models')
                    ->where('article_id', $articleId)
                    ->first();

                if ($existingRecord) {
                    $results['skipped']++;
                    $results['processed']++;
                    continue;
                }

                $vehicleData = $this->extractVehicleMetadata($article);
                
                if (!empty($vehicleData['vehicle_info']['make'])) {
                    $this->syncVehicleDataToMySQL($articleId, $article, $vehicleData);
                    $results['synced']++;
                } else {
                    $results['skipped']++;
                }
                
                $results['processed']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Erro no artigo {$article->_id}: {$e->getMessage()}";
            }
        }

        return $results;
    }

    /**
     * Sincronização específica apenas para autores
     */
    public function syncAuthorsOnly(Collection $articles): array
    {
        $results = [
            'processed' => 0,
            'assigned' => 0,
            'errors' => []
        ];

        foreach ($articles as $article) {
            try {
                $authorData = $this->assignAuthorToArticle($article);
                
                if (!empty($authorData)) {
                    // Em uma implementação real, aqui você atualizaria o banco
                    // Article::find($article->_id)->update(['author' => $authorData]);
                    $results['assigned']++;
                }
                
                $results['processed']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Erro no artigo {$article->_id}: {$e->getMessage()}";
            }
        }

        return $results;
    }

    /**
     * Gera relatório detalhado da sincronização
     */
    public function generateSyncReport(array $syncResults): array
    {
        $duration = $syncResults['duration_seconds'] ?? 0;
        
        return [
            'summary' => [
                'total_duration' => $duration . ' segundos',
                'articles_processed' => $syncResults['articles_processed'],
                'success_rate' => $this->calculateSuccessRate($syncResults),
                'errors_count' => count($syncResults['errors']),
                'warnings_count' => count($syncResults['warnings']),
            ],
            'sync_details' => [
                'vehicle_data_synced' => $syncResults['vehicle_data_synced'],
                'authors_assigned' => $syncResults['authors_assigned'],
                'mysql_records_created' => $syncResults['mysql_records_created'],
            ],
            'performance' => [
                'articles_per_second' => $duration > 0 ? round($syncResults['articles_processed'] / $duration, 2) : 0,
                'average_time_per_article' => $syncResults['articles_processed'] > 0 
                    ? round($duration / $syncResults['articles_processed'], 3) 
                    : 0,
            ],
            'issues' => [
                'errors' => $syncResults['errors'],
                'warnings' => $syncResults['warnings'],
            ],
        ];
    }

    /**
     * Calcula taxa de sucesso
     */
    private function calculateSuccessRate(array $syncResults): float
    {
        $total = $syncResults['articles_processed'];
        if ($total === 0) {
            return 0.0;
        }

        $errors = count($syncResults['errors']);
        $successCount = $total - $errors;
        
        return round(($successCount / $total) * 100, 2);
    }

    /**
     * Limpa dados de sincronização antigos
     */
    public function cleanupOldSyncData(int $daysOld = 30): array
    {
        $results = [
            'vehicle_models_cleaned' => 0,
            'orphaned_makes_cleaned' => 0,
            'orphaned_models_cleaned' => 0,
        ];

        try {
            $cutoffDate = now()->subDays($daysOld);

            // Remover registros antigos sem artigos correspondentes
            // (implementação dependeria da lógica específica de negócio)
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }

        return $results;
    }
}
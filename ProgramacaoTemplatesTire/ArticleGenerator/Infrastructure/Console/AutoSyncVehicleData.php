<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Domain\Services\AutoSyncService;

class AutoSyncVehicleData extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:auto-sync-vehicle-data 
                           {--batch-size=100 : Número de artigos a processar por lote}
                           {--unprocessed-only : Sincronizar apenas artigos sem vehicle_info}
                           {--recent-only : Sincronizar apenas artigos dos últimos dias}
                           {--recent-days=7 : Número de dias para considerar como "recente"}
                           {--status=published : Status dos artigos a sincronizar (published|scheduled|all)}
                           {--force-recreate-tables : Forçar recriação das tabelas MySQL}
                           {--authors-sync : Incluir sincronização de autores}
                           {--cleanup-old : Limpar dados antigos após sincronização}
                           {--max-execution-time=3600 : Tempo máximo de execução em segundos}
                           {--dry-run : Simular sincronização sem fazer alterações}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Sincronização automática completa de dados de veículos e autores para MySQL';

    private AutoSyncService $autoSyncService;
    private int $startTime;
    private int $maxExecutionTime;

    /**
     * Execute o comando.
     *
     * @return int
     */
    public function handle()
    {
        $this->startTime = time();
        $this->maxExecutionTime = (int) $this->option('max-execution-time');
        
        $this->info("Iniciando sincronização automática - " . date('Y-m-d H:i:s'));

        // Inicializar serviço
        $this->autoSyncService = new AutoSyncService($this->option('force-recreate-tables'));

        // Validar e processar opções
        $options = $this->validateAndParseOptions();
        if ($options === null) {
            return Command::FAILURE;
        }

        // Verificar e criar tabelas MySQL se necessário
        if ($options['force_recreate_tables'] || !$this->checkMySQLTablesExist()) {
            $this->info('Verificando/criando tabelas MySQL...');
            $this->ensureMySQLTablesExist();
        }

        // Obter artigos para sincronização
        $articlesToSync = $this->getArticlesToSync($options);
        if ($articlesToSync->isEmpty()) {
            $this->info('Nenhum artigo encontrado para sincronização.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$articlesToSync->count()} artigos para sincronização.");

        // Executar sincronização em lotes
        $results = $this->executeSyncInBatches($articlesToSync, $options);

        // Atualizar contadores finais
        if (!$options['dry_run'] && $results['success']) {
            $this->updateFinalCounters();
        }

        // Cleanup se solicitado
        if ($options['cleanup_old'] && !$options['dry_run']) {
            $this->performCleanup();
        }

        // Log dos resultados
        $this->logResults($results, $options);

        return $results['success'] ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Valida e processa as opções do comando
     */
    private function validateAndParseOptions(): ?array
    {
        try {
            $batchSize = (int) $this->option('batch-size');
            $recentDays = (int) $this->option('recent-days');
            $status = $this->option('status');

            // Validações automáticas com ajustes
            if ($batchSize <= 0 || $batchSize > 500) {
                $this->warn('Batch size inválido, usando padrão: 100');
                $batchSize = 100;
            }

            if ($recentDays <= 0 || $recentDays > 365) {
                $this->warn('Dias recentes inválido, usando padrão: 7');
                $recentDays = 7;
            }

            if (!in_array($status, ['published', 'scheduled', 'all'])) {
                $this->warn('Status inválido, usando padrão: published');
                $status = 'published';
            }

            return [
                'batch_size' => $batchSize,
                'unprocessed_only' => $this->option('unprocessed-only'),
                'recent_only' => $this->option('recent-only'),
                'recent_days' => $recentDays,
                'status' => $status,
                'force_recreate_tables' => $this->option('force-recreate-tables'),
                'authors_sync' => $this->option('authors-sync'),
                'cleanup_old' => $this->option('cleanup-old'),
                'dry_run' => $this->option('dry-run'),
            ];

        } catch (\Exception $e) {
            $this->error("Erro ao validar opções: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Obtém artigos para sincronização
     */
    private function getArticlesToSync(array $options): Collection
    {
        $query = Article::query();

        // Filtro por status
        if ($options['status'] !== 'all') {
            $query->where('status', $options['status']);
        }

        // Filtro para não processados
        if ($options['unprocessed_only']) {
            $query->where(function($q) {
                $q->whereNull('vehicle_info')
                  ->orWhere('vehicle_info', '=', [])
                  ->orWhere('vehicle_info', '=', '{}');
            });
            $this->info('Sincronizando apenas artigos sem dados de veículo.');
        }

        // Filtro para artigos recentes
        if ($options['recent_only']) {
            $cutoffDate = now()->subDays($options['recent_days']);
            $query->where(function($q) use ($cutoffDate) {
                $q->where('created_at', '>=', $cutoffDate)
                  ->orWhere('updated_at', '>=', $cutoffDate);
            });
            $this->info("Sincronizando apenas artigos dos últimos {$options['recent_days']} dias.");
        }

        // Filtrar apenas artigos com extracted_entities (que podem ter dados de veículo)
        $query->whereNotNull('extracted_entities')
             ->where('extracted_entities', '!=', [])
             ->where('extracted_entities', '!=', '{}');

        // Ordenar por prioridade: primeiro artigos sem vehicle_info, depois por data
        $query->orderByRaw('CASE WHEN vehicle_info IS NULL OR vehicle_info = "{}" THEN 0 ELSE 1 END')
              ->orderBy('updated_at', 'desc');

        return $query->get();
    }

    /**
     * Executa sincronização em lotes
     */
    private function executeSyncInBatches(Collection $articles, array $options): array
    {
        $results = [
            'success' => true,
            'total_articles' => $articles->count(),
            'processed' => 0,
            'vehicle_data_synced' => 0,
            'authors_assigned' => 0,
            'mysql_records_created' => 0,
            'batches_processed' => 0,
            'skipped' => 0,
            'errors' => [],
            'execution_stopped_early' => false,
        ];

        // Processar em lotes
        $batches = $articles->chunk($options['batch_size']);
        $totalBatches = $batches->count();

        $this->info("Processando {$results['total_articles']} artigos em {$totalBatches} lotes...");

        foreach ($batches as $batchIndex => $batch) {
            // Verificar tempo de execução
            if ($this->shouldStopExecution()) {
                $this->warn('Tempo máximo de execução atingido. Parando processamento.');
                $results['execution_stopped_early'] = true;
                break;
            }

            $this->info("Processando lote " . ($batchIndex + 1) . "/{$totalBatches} ({$batch->count()} artigos)");

            try {
                $batchResult = $this->processSyncBatch($batch, $options);
                
                $results['processed'] += $batchResult['processed'];
                $results['vehicle_data_synced'] += $batchResult['vehicle_data_synced'];
                $results['authors_assigned'] += $batchResult['authors_assigned'];
                $results['mysql_records_created'] += $batchResult['mysql_records_created'];
                $results['skipped'] += $batchResult['skipped'];
                $results['batches_processed']++;
                
                if (!empty($batchResult['errors'])) {
                    $results['errors'] = array_merge($results['errors'], $batchResult['errors']);
                }

            } catch (\Exception $e) {
                $results['errors'][] = "Erro no lote " . ($batchIndex + 1) . ": {$e->getMessage()}";
                $results['success'] = false;
            }

            // Pequena pausa entre lotes
            if ($batchIndex < $totalBatches - 1) {
                usleep(50000); // 50ms
            }
        }

        return $results;
    }

    /**
     * Processa um lote de sincronização
     */
    private function processSyncBatch(Collection $batch, array $options): array
    {
        $batchResult = [
            'processed' => 0,
            'vehicle_data_synced' => 0,
            'authors_assigned' => 0,
            'mysql_records_created' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($batch as $article) {
            try {
                // Verificar se o artigo precisa de processamento
                if ($this->shouldSkipArticle($article, $options)) {
                    $batchResult['skipped']++;
                    $batchResult['processed']++;
                    continue;
                }

                // Processar dados de veículo
                $vehicleProcessed = $this->processVehicleData($article, $options);
                if ($vehicleProcessed) {
                    $batchResult['vehicle_data_synced']++;
                    $batchResult['mysql_records_created']++;
                }

                // Processar autor se solicitado
                if ($options['authors_sync']) {
                    $authorProcessed = $this->processAuthorData($article, $options);
                    if ($authorProcessed) {
                        $batchResult['authors_assigned']++;
                    }
                }

                $batchResult['processed']++;

            } catch (\Exception $e) {
                $batchResult['errors'][] = "Erro ao processar artigo {$article->_id}: {$e->getMessage()}";
            }
        }

        return $batchResult;
    }

    /**
     * Verifica se deve pular um artigo
     */
    private function shouldSkipArticle(Article $article, array $options): bool
    {
        // Se for apenas não processados, pular se já tem vehicle_info
        if ($options['unprocessed_only']) {
            if (!empty($article->vehicle_info) && !empty($article->vehicle_info['make'])) {
                return true;
            }
        }

        // Pular se não tem extracted_entities com dados relevantes
        if (empty($article->extracted_entities)) {
            return true;
        }

        $relevantFields = ['marca', 'modelo', 'ano', 'motorizacao', 'combustivel', 'categoria', 'tipo_veiculo'];
        $hasRelevantData = false;
        
        foreach ($relevantFields as $field) {
            if (!empty($article->extracted_entities[$field])) {
                $hasRelevantData = true;
                break;
            }
        }

        return !$hasRelevantData;
    }

    /**
     * Processa dados de veículo para um artigo
     */
    private function processVehicleData(Article $article, array $options): bool
    {
        try {
            if ($options['dry_run']) {
                return true; // Simular sucesso
            }

            // Usar AutoSyncService para processar
            $articleData = ['article' => $article];
            $syncResults = $this->autoSyncService->syncVehicleDataOnly(collect([$article]));

            // Verificar se foi processado com sucesso
            return $syncResults['synced'] > 0;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Processa dados de autor para um artigo
     */
    private function processAuthorData(Article $article, array $options): bool
    {
        try {
            if ($options['dry_run']) {
                return true; // Simular sucesso
            }

            // Verificar se já tem autor
            if (!empty($article->author) && !empty($article->author['name'])) {
                return false; // Já tem autor
            }

            // Usar AutoSyncService para processar
            $syncResults = $this->autoSyncService->syncAuthorsOnly(collect([$article]));

            return $syncResults['assigned'] > 0;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verifica se deve parar a execução por tempo
     */
    private function shouldStopExecution(): bool
    {
        $currentTime = time();
        $elapsed = $currentTime - $this->startTime;
        
        return $elapsed >= $this->maxExecutionTime;
    }

    /**
     * Verifica se as tabelas MySQL existem
     */
    private function checkMySQLTablesExist(): bool
    {
        try {
            $schema = \Illuminate\Support\Facades\DB::connection('mysql')->getSchemaBuilder();
            
            return $schema->hasTable('makes') && 
                   $schema->hasTable('models') && 
                   $schema->hasTable('vehicle_models');
                   
        } catch (\Exception $e) {
            $this->warn("Erro ao verificar tabelas MySQL: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Garante que as tabelas MySQL existem
     */
    private function ensureMySQLTablesExist(): void
    {
        try {
            $schema = \Illuminate\Support\Facades\DB::connection('mysql')->getSchemaBuilder();

            // Criar tabela makes
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
                $this->info('Tabela "makes" criada.');
            }

            // Criar tabela models
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
                $this->info('Tabela "models" criada.');
            }

            // Criar tabela vehicle_models
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
                $this->info('Tabela "vehicle_models" criada.');
            }

        } catch (\Exception $e) {
            $this->error("Erro ao criar tabelas MySQL: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Atualiza contadores finais das tabelas
     */
    private function updateFinalCounters(): void
    {
        try {
            $this->info('Atualizando contadores finais...');

            // Atualizar contador de artigos por marca
            \Illuminate\Support\Facades\DB::connection('mysql')->statement("
                UPDATE makes m SET article_count = (
                    SELECT COUNT(*) FROM vehicle_models vm 
                    WHERE vm.make_slug = m.slug
                )
            ");

            // Atualizar contador de artigos por modelo
            \Illuminate\Support\Facades\DB::connection('mysql')->statement("
                UPDATE models mo SET article_count = (
                    SELECT COUNT(*) FROM vehicle_models vm 
                    WHERE vm.make_slug = mo.make_slug AND vm.model_slug = mo.slug
                )
            ");

            $this->info('Contadores atualizados com sucesso.');

        } catch (\Exception $e) {
            $this->warn("Erro ao atualizar contadores: {$e->getMessage()}");
        }
    }

    /**
     * Executa limpeza de dados antigos
     */
    private function performCleanup(): void
    {
        try {
            $this->info('Executando limpeza de dados antigos...');

            // Remover registros órfãos de vehicle_models (sem artigo correspondente)
            $orphanedCount = \Illuminate\Support\Facades\DB::connection('mysql')
                ->table('vehicle_models')
                ->whereNotIn('article_id', function($query) {
                    $query->select('_id')->from('articles');
                })
                ->count();

            if ($orphanedCount > 0) {
                \Illuminate\Support\Facades\DB::connection('mysql')
                    ->table('vehicle_models')
                    ->whereNotIn('article_id', function($query) {
                        $query->select('_id')->from('articles');
                    })
                    ->delete();
                    
                $this->info("Removidos {$orphanedCount} registros órfãos de vehicle_models.");
            }

            // Remover marcas sem artigos
            $emptyMakesCount = \Illuminate\Support\Facades\DB::connection('mysql')
                ->table('makes')
                ->where('article_count', 0)
                ->count();

            if ($emptyMakesCount > 0) {
                \Illuminate\Support\Facades\DB::connection('mysql')
                    ->table('makes')
                    ->where('article_count', 0)
                    ->delete();
                    
                $this->info("Removidas {$emptyMakesCount} marcas sem artigos.");
            }

            // Remover modelos sem artigos
            $emptyModelsCount = \Illuminate\Support\Facades\DB::connection('mysql')
                ->table('models')
                ->where('article_count', 0)
                ->count();

            if ($emptyModelsCount > 0) {
                \Illuminate\Support\Facades\DB::connection('mysql')
                    ->table('models')
                    ->where('article_count', 0)
                    ->delete();
                    
                $this->info("Removidos {$emptyModelsCount} modelos sem artigos.");
            }

        } catch (\Exception $e) {
            $this->warn("Erro durante limpeza: {$e->getMessage()}");
        }
    }

    /**
     * Log dos resultados da execução
     */
    private function logResults(array $results, array $options): void
    {
        $endTime = time();
        $duration = $endTime - $this->startTime;

        $this->info('=== RESULTADOS DA SINCRONIZAÇÃO ===');
        $this->info("Duração: {$duration} segundos");
        $this->info("Total de artigos: {$results['total_articles']}");
        $this->info("Artigos processados: {$results['processed']}");
        $this->info("Dados de veículo sincronizados: {$results['vehicle_data_synced']}");
        $this->info("Autores atribuídos: {$results['authors_assigned']}");
        $this->info("Registros MySQL criados: {$results['mysql_records_created']}");
        $this->info("Artigos ignorados: {$results['skipped']}");
        $this->info("Lotes processados: {$results['batches_processed']}");

        if ($options['dry_run']) {
            $this->warn('MODO SIMULAÇÃO - Nenhuma alteração foi feita');
        }

        if ($results['execution_stopped_early']) {
            $this->warn('EXECUÇÃO INTERROMPIDA - Tempo máximo atingido');
        }

        if (!empty($results['errors'])) {
            $this->warn("Erros encontrados: " . count($results['errors']));
            foreach (array_slice($results['errors'], 0, 5) as $error) {
                $this->error("  • {$error}");
            }
            
            if (count($results['errors']) > 5) {
                $remaining = count($results['errors']) - 5;
                $this->warn("  ... e mais {$remaining} erros");
            }
        }

        // Estatísticas de performance
        if ($results['processed'] > 0) {
            $avgTimePerArticle = round($duration / $results['processed'], 3);
            $this->info("Tempo médio por artigo: {$avgTimePerArticle} segundos");
            
            $syncRate = round(($results['vehicle_data_synced'] / $results['processed']) * 100, 1);
            $this->info("Taxa de sincronização: {$syncRate}%");
        }

        // Log estruturado
        $executionLog = [
            'command' => 'articles:auto-sync-vehicle-data',
            'started_at' => date('Y-m-d H:i:s', $this->startTime),
            'completed_at' => date('Y-m-d H:i:s', $endTime),
            'duration_seconds' => $duration,
            'options' => $options,
            'results' => $results,
            'memory_usage' => memory_get_peak_usage(true),
            'success' => $results['success'],
        ];

        $logLine = json_encode($executionLog) . "\n";
        $logFile = storage_path('logs/auto-sync-execution-' . date('Y-m-d') . '.log');
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

        // Verificar alertas
        $this->checkForAlerts($results, $options);
    }

    /**
     * Verifica condições que requerem alertas
     */
    private function checkForAlerts(array $results, array $options): void
    {
        $alerts = [];

        // Baixa taxa de sincronização
        if ($results['processed'] > 0) {
            $syncRate = ($results['vehicle_data_synced'] / $results['processed']) * 100;
            if ($syncRate < 50 && !$options['unprocessed_only']) {
                $alerts[] = "Baixa taxa de sincronização: {$syncRate}%";
            }
        }

        // Muitos erros
        if (count($results['errors']) > 10) {
            $alerts[] = "Muitos erros encontrados: " . count($results['errors']);
        }

        // Execução interrompida
        if ($results['execution_stopped_early']) {
            $alerts[] = "Execução interrompida por tempo limite";
        }

        // Nenhum artigo processado quando deveria haver
        if ($results['total_articles'] > 0 && $results['processed'] === 0) {
            $alerts[] = "Nenhum artigo foi processado apesar de haver artigos disponíveis";
        }

        if (!empty($alerts)) {
            $this->warn('ALERTAS DETECTADOS:');
            foreach ($alerts as $alert) {
                $this->error("  ⚠️  {$alert}");
            }

            $alertLog = [
                'timestamp' => date('Y-m-d H:i:s'),
                'command' => 'articles:auto-sync-vehicle-data',
                'alerts' => $alerts,
                'results' => $results,
                'options' => $options,
            ];

            $logLine = json_encode($alertLog) . "\n";
            $alertFile = storage_path('logs/alerts-' . date('Y-m-d') . '.log');
            file_put_contents($alertFile, $logLine, FILE_APPEND | LOCK_EX);
        }
    }
}
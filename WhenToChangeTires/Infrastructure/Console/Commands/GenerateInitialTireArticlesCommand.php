<?php

namespace Src\ContentGeneration\WhenToChangeTires\Infrastructure\Console\Commands;

use Src\ContentGeneration\WhenToChangeTires\Infrastructure\Services\VehicleDataProcessorService;
use Src\ContentGeneration\WhenToChangeTires\Infrastructure\Services\TemplateBasedContentService;
use Src\ContentGeneration\WhenToChangeTires\Infrastructure\Services\ArticleJsonStorageService;
use Src\ContentGeneration\WhenToChangeTires\Domain\Entities\TireChangeArticle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GenerateInitialTireArticlesCommand extends Command
{
    protected $signature = 'when-to-change-tires:generate-initial-articles 
                          {--csv-path=todos_veiculos.csv : Caminho para o arquivo CSV}
                          {--batch-size=50 : Número de artigos por lote}
                          {--filter-make= : Filtrar por marca específica}
                          {--filter-category= : Filtrar por categoria específica}
                          {--filter-vehicle-type= : Filtrar por tipo (car, motorcycle)}
                          {--year-from= : Filtrar a partir do ano}
                          {--year-to= : Filtrar até o ano}
                          {--only-json : Gerar apenas JSONs, não salvar na model}
                          {--overwrite : Sobrescrever artigos existentes}
                          {--dry-run : Simular execução sem gerar artigos}
                          {--show-progress : Mostrar barra de progresso}';

    protected $description = 'Gera artigos iniciais "Quando Trocar Pneus" a partir dos dados do CSV';

    public function __construct(
        protected VehicleDataProcessorService $vehicleProcessor,
        protected TemplateBasedContentService $contentService,
        protected ArticleJsonStorageService $jsonStorage
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info("🚀 Iniciando geração de artigos iniciais 'Quando Trocar Pneus'");
        $this->line("");

        try {
            // 1. Configurar parâmetros
            $config = $this->getConfiguration();
            $this->displayConfiguration($config);

            // 2. Importar e filtrar veículos
            $vehicles = $this->loadAndFilterVehicles($config);

            if ($vehicles->isEmpty()) {
                $this->error("❌ Nenhum veículo encontrado com os filtros aplicados");
                return 1;
            }

            // 3. Preparar ambiente
            $this->prepareEnvironment();

            // 4. Processar em lotes
            $results = $this->processVehiclesInBatches($vehicles, $config);

            // 5. Mostrar relatório final
            $this->displayFinalReport($results);

            $this->info("✅ Geração de artigos concluída com sucesso!");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erro durante geração: " . $e->getMessage());
            Log::error("GenerateInitialTireArticlesCommand falhou: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Obter configuração do comando
     */
    protected function getConfiguration(): array
    {
        return [
            'csv_path' => $this->option('csv-path'),
            'batch_size' => (int) $this->option('batch-size'),
            'filters' => [
                'make' => $this->option('filter-make'),
                'category' => $this->option('filter-category'),
                'vehicle_type' => $this->option('filter-vehicle-type'),
                'year_from' => $this->option('year-from') ? (int) $this->option('year-from') : null,
                'year_to' => $this->option('year-to') ? (int) $this->option('year-to') : null,
                'require_tire_pressure' => true
            ],
            'only_json' => $this->option('only-json'),
            'overwrite' => $this->option('overwrite'),
            'dry_run' => $this->option('dry-run'),
            'show_progress' => $this->option('show-progress')
        ];
    }

    /**
     * Exibir configuração
     */
    protected function displayConfiguration(array $config): void
    {
        $this->info("📋 CONFIGURAÇÃO:");
        $this->line("   📂 CSV: {$config['csv_path']}");
        $this->line("   📦 Lote: {$config['batch_size']} artigos");

        if (!empty(array_filter($config['filters']))) {
            $this->line("   🔍 Filtros ativos:");
            foreach ($config['filters'] as $key => $value) {
                if ($value !== null && $key !== 'require_tire_pressure') {
                    $this->line("      {$key}: {$value}");
                }
            }
        }

        $options = [];
        if ($config['only_json']) $options[] = 'Apenas JSON';
        if ($config['overwrite']) $options[] = 'Sobrescrever';
        if ($config['dry_run']) $options[] = 'Simulação';

        if (!empty($options)) {
            $this->line("   ⚙️ Opções: " . implode(', ', $options));
        }

        $this->line("");
    }

    /**
     * Carregar e filtrar veículos
     */
    protected function loadAndFilterVehicles(array $config)
    {
        $this->info("📥 Carregando veículos do CSV...");

        $allVehicles = $this->vehicleProcessor->importFromCsv($config['csv_path']);
        $this->line("   Total importados: {$allVehicles->count()}");

        // Aplicar filtros
        $filters = array_filter($config['filters'], function ($value) {
            return $value !== null;
        });

        if (!empty($filters)) {
            $this->info("🔍 Aplicando filtros...");
            $filteredVehicles = $this->vehicleProcessor->filterVehicles($allVehicles, $filters);
            $this->line("   Após filtros: {$filteredVehicles->count()}");
        } else {
            $filteredVehicles = $allVehicles;
        }

        // Obter apenas veículos válidos
        $validVehicles = $this->vehicleProcessor->getVehiclesReadyForGeneration($filteredVehicles);
        $this->line("   Válidos para geração: {$validVehicles->count()}");

        // Remover duplicatas por veículo (se não overwrite)
        if (!$config['overwrite']) {
            $validVehicles = $this->removeDuplicateVehicles($validVehicles);
            $this->line("   Únicos (sem duplicatas): {$validVehicles->count()}");
        }

        return $validVehicles;
    }

    /**
     * Remover veículos que já têm artigos
     */
    protected function removeDuplicateVehicles($vehicles)
    {
        return $vehicles->filter(function ($vehicle) {
            // Verificar se já existe na model TireChangeArticle
            $exists = TireChangeArticle::where('make', $vehicle->make)
                ->where('model', $vehicle->model)
                // ->where('year', $vehicle->year)
                ->exists();

            if ($exists && !$this->option('overwrite')) {
                $this->line("   ⏭️ Pulando {$vehicle->getVehicleIdentifier()} (já existe)");
                return false;
            }

            return true;
        });
    }

    /**
     * Preparar ambiente para geração
     */
    protected function prepareEnvironment(): void
    {
        $this->info("🔧 Preparando ambiente...");

        // Garantir que diretório de JSONs existe
        $this->jsonStorage->ensureDirectoryExists();

        // Iniciar transação para rollback em caso de erro (se não for dry-run)
        if (!$this->option('dry-run') && !$this->option('only-json')) {
            DB::beginTransaction();
        }

        $this->line("   ✅ Ambiente preparado");
        $this->line("");
    }

    /**
     * Processar veículos em lotes
     */
    protected function processVehiclesInBatches($vehicles, array $config): array
    {
        $batches = $this->vehicleProcessor->createBatches($vehicles, $config['batch_size']);
        $totalBatches = $batches->count();

        $this->info("📦 Processando {$totalBatches} lotes:");
        $this->line("");

        $results = [
            'total_processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($batches as $batchIndex => $batch) {
            $batchNumber = $batchIndex + 1;
            $this->info("📦 Lote {$batchNumber}/{$totalBatches} - {$batch['count']} veículos");

            $batchResults = $this->processBatch($batch, $config);

            // Agregar resultados
            $results['total_processed'] += $batchResults['processed'];
            $results['successful'] += $batchResults['successful'];
            $results['failed'] += $batchResults['failed'];
            $results['skipped'] += $batchResults['skipped'];
            $results['errors'] = array_merge($results['errors'], $batchResults['errors']);

            // Progresso do lote
            $this->line("   ✅ {$batchResults['successful']} criados, ❌ {$batchResults['failed']} falhas, ⏭️ {$batchResults['skipped']} pulados");
            $this->line("");
        }

        return $results;
    }

    /**
     * Processar um lote de veículos
     */
    protected function processBatch(array $batch, array $config): array
    {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        $vehicles = collect($batch['vehicles']);

        // Barra de progresso se solicitada
        if ($config['show_progress']) {
            $progressBar = $this->output->createProgressBar($vehicles->count());
            $progressBar->start();
        }

        foreach ($vehicles as $vehicle) {
            $results['processed']++;

            try {
                if ($config['dry_run']) {
                    $this->line("   🔍 [DRY RUN] Processaria: {$vehicle->getVehicleIdentifier()}");
                    $results['successful']++;
                } else {
                    $result = $this->processVehicle($vehicle, $config);

                    if ($result['success']) {
                        $results['successful']++;
                        if ($this->option('verbose')) {
                            $this->line("   ✅ {$vehicle->getVehicleIdentifier()}");
                        }
                    } else {
                        $results['failed']++;
                        $results['errors'][] = [
                            'vehicle' => $vehicle->getVehicleIdentifier(),
                            'error' => $result['error']
                        ];

                        if ($this->option('verbose')) {
                            $this->line("   ❌ {$vehicle->getVehicleIdentifier()}: {$result['error']}");
                        }
                    }
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'vehicle' => $vehicle->getVehicleIdentifier(),
                    'error' => $e->getMessage()
                ];

                Log::error("Erro processando veículo {$vehicle->getVehicleIdentifier()}: " . $e->getMessage());
            }

            if ($config['show_progress']) {
                $progressBar->advance();
            }
        }

        if ($config['show_progress']) {
            $progressBar->finish();
            $this->line("");
        }

        return $results;
    }

    /**
     * Processar um veículo individual
     */
    protected function processVehicle($vehicle, array $config): array
    {
        try {
            // 1. Gerar conteúdo do artigo
            $content = $this->contentService->generateTireChangeArticle($vehicle);

            if (!$content->isValid()) {
                return [
                    'success' => false,
                    'error' => 'Conteúdo gerado é inválido'
                ];
            }

            // 2. Salvar JSON se solicitado ou sempre
            $jsonPath = $this->jsonStorage->saveArticleJson($content);

            // 3. Salvar na model se não for only-json
            if (!$config['only_json']) {
                $article = $this->saveToTireChangeArticleModel($vehicle, $content, $jsonPath);

                if (!$article) {
                    return [
                        'success' => false,
                        'error' => 'Falha ao salvar na model TireChangeArticle'
                    ];
                }
            }

            return [
                'success' => true,
                'content' => $content,
                'json_path' => $jsonPath
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Salvar na model TireChangeArticle
     */
    protected function saveToTireChangeArticleModel($vehicle, $content, string $jsonPath): ?TireChangeArticle
    {
        try {
            $jsonData = $content->toJsonStructure();
            $wordCount = $content->getWordCount();

            $article = TireChangeArticle::create([
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'tire_size' => $vehicle->tireSize,
                'vehicle_data' => $vehicle->toArray(),
                'title' => $jsonData['title'],
                'slug' => $jsonData['slug'],
                'article_content' => json_encode($jsonData['content'], JSON_UNESCAPED_UNICODE),
                'template_used' => $jsonData['template'],
                'meta_description' => $jsonData['seo_data']['meta_description'],
                'seo_keywords' => $jsonData['seo_data']['secondary_keywords'],
                'wordpress_url' => $jsonData['seo_data']['url_slug'],
                'canonical_url' => $jsonData['seo_data']['canonical_url'] ?? null,
                'generation_status' => 'generated',
                'pressure_empty_front' => $vehicle->pressureEmptyFront,
                'pressure_empty_rear' => $vehicle->pressureEmptyRear,
                'pressure_light_front' => $vehicle->pressureLightFront,
                'pressure_light_rear' => $vehicle->pressureLightRear,
                'pressure_max_front' => $vehicle->pressureMaxFront,
                'pressure_max_rear' => $vehicle->pressureMaxRear,
                'pressure_spare' => $vehicle->pressureSpare,
                'category' => $vehicle->category,
                'recommended_oil' => $vehicle->recommendedOil,
                'quality_checked' => true,
                'content_score' => $wordCount >= 1500 ? 9.0 : ($wordCount >= 1000 ? 8.0 : 7.0),
                'batch_id' => 'initial_' . date('Ymd_His'),
                'processed_at' => now()
            ]);

            $article->markAsGenerated();

            return $article;
        } catch (\Exception $e) {
            Log::error("Erro salvando TireChangeArticle: " . $e->getMessage(), [
                'vehicle' => $vehicle->getVehicleIdentifier()
            ]);
            return null;
        }
    }

    /**
     * Exibir relatório final
     */
    protected function displayFinalReport(array $results): void
    {
        $this->line("");
        $this->info("📊 RELATÓRIO FINAL:");
        $this->line("   📄 Total processados: {$results['total_processed']}");
        $this->line("   ✅ Sucessos: {$results['successful']}");
        $this->line("   ❌ Falhas: {$results['failed']}");
        $this->line("   ⏭️ Pulados: {$results['skipped']}");

        if ($results['successful'] > 0) {
            $successRate = round(($results['successful'] / $results['total_processed']) * 100, 1);
            $this->line("   📈 Taxa de sucesso: {$successRate}%");
        }

        // Mostrar erros se houver
        if (!empty($results['errors']) && $this->option('verbose')) {
            $this->line("");
            $this->warn("⚠️ ERROS ENCONTRADOS:");
            foreach (array_slice($results['errors'], 0, 10) as $error) {
                $this->line("   • {$error['vehicle']}: {$error['error']}");
            }

            if (count($results['errors']) > 10) {
                $remaining = count($results['errors']) - 10;
                $this->line("   ... e mais {$remaining} erros");
            }
        }

        // Estatísticas de armazenamento
        if (!$this->option('dry-run')) {
            $this->line("");
            $this->displayStorageStats();
        }

        // Commit transação se tudo ok
        if (!$this->option('dry-run') && !$this->option('only-json') && $results['failed'] === 0) {
            DB::commit();
            $this->line("✅ Todas as alterações foram salvas no banco");
        } elseif (!$this->option('dry-run') && !$this->option('only-json')) {
            DB::rollBack();
            $this->warn("⚠️ Transação revertida devido a erros");
        }
    }

    /**
     * Exibir estatísticas de armazenamento
     */
    protected function displayStorageStats(): void
    {
        try {
            $jsonStats = $this->jsonStorage->getStorageStatistics();
            $this->info("💾 ESTATÍSTICAS DE ARMAZENAMENTO:");
            $this->line("   📄 Total de JSONs: {$jsonStats['total_articles']}");
            $this->line("   📦 Tamanho total: {$jsonStats['storage_size_formatted']}");
            $this->line("   📝 Total de palavras: " . number_format($jsonStats['total_words']));

            if (!$this->option('only-json')) {
                $dbCount = TireChangeArticle::count();
                $this->line("   🗄️ Artigos no banco: {$dbCount}");
            }
        } catch (\Exception $e) {
            $this->warn("Não foi possível obter estatísticas de armazenamento");
        }
    }
}

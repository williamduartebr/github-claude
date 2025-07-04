<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands;

use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\VehicleDataProcessorService;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\TemplateBasedContentService;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\ArticleJsonStorageService;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Entities\TireChangeArticle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessVehicleBatchCommand extends Command
{
    protected $signature = 'when-to-change-tires:process-batch 
                          {batch-id : ID do lote a processar}
                          {--csv-path=todos_veiculos.csv : Caminho para o arquivo CSV}
                          {--force : Processar mesmo se lote já foi processado}
                          {--only-json : Gerar apenas JSONs}';

    protected $description = 'Processa um lote específico de veículos para geração de artigos';

    public function __construct(
        protected VehicleDataProcessorService $vehicleProcessor,
        protected TemplateBasedContentService $contentService,
        protected ArticleJsonStorageService $jsonStorage
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $batchId = $this->argument('batch-id');
        $csvPath = $this->option('csv-path');

        $this->info("🔄 Processando lote: {$batchId}");

        try {
            // 1. Carregar todos os veículos
            $allVehicles = $this->vehicleProcessor->importFromCsv($csvPath);

            // 2. Filtrar veículos do lote específico (simulado por índice)
            $batchSize = 50; // Tamanho padrão do lote
            $batchNumber = (int) str_replace(['batch_', '_'], '', $batchId);
            $startIndex = ($batchNumber - 1) * $batchSize;

            $batchVehicles = $allVehicles->slice($startIndex, $batchSize);

            if ($batchVehicles->isEmpty()) {
                $this->error("❌ Lote {$batchId} não encontrado ou vazio");
                return 1;
            }

            $this->info("📦 Encontrados {$batchVehicles->count()} veículos no lote");

            // 3. Verificar se lote já foi processado
            if (!$this->option('force')) {
                $processed = TireChangeArticle::where('batch_id', $batchId)->count();
                if ($processed > 0) {
                    $this->warn("⚠️ Lote {$batchId} já foi processado ({$processed} artigos)");
                    if (!$this->confirm('Deseja continuar mesmo assim?')) {
                        return 0;
                    }
                }
            }

            // 4. Processar veículos
            $successful = 0;
            $failed = 0;

            $progressBar = $this->output->createProgressBar($batchVehicles->count());
            $progressBar->start();

            foreach ($batchVehicles as $vehicle) {
                try {
                    // Gerar conteúdo
                    $content = $this->contentService->generateTireChangeArticle($vehicle);

                    // Salvar JSON
                    $this->jsonStorage->saveArticleJson($content);

                    // Salvar na model se não for only-json
                    if (!$this->option('only-json')) {
                        $this->saveTireChangeArticle($vehicle, $content, $batchId);
                    }

                    $successful++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::error("Erro processando {$vehicle->getVehicleIdentifier()}: " . $e->getMessage());
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->line("");

            // 5. Relatório
            $this->info("✅ Lote {$batchId} processado:");
            $this->line("   Sucessos: {$successful}");
            $this->line("   Falhas: {$failed}");

            return $failed > 0 ? 1 : 0;
        } catch (\Exception $e) {
            $this->error("❌ Erro processando lote: " . $e->getMessage());
            return 1;
        }
    }

    protected function saveTireChangeArticle($vehicle, $content, string $batchId): void
    {
        $jsonData = $content->toJsonStructure();

        TireChangeArticle::create([
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
            'batch_id' => $batchId,
            'processed_at' => now()
        ]);
    }
}

<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Src\ContentGeneration\TirePressureGuide\Application\UseCases\GenerateInitialArticlesUseCase;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\VehicleDataProcessorService;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\InitialArticleGeneratorService;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Fixed Command para geração inicial de artigos de calibragem (Etapa 1)
 * 
 * CORRIGIDO:
 * - Método processVehicleData() em vez de loadFromCsv()
 * - Compatibilidade com CSV todos_veiculos.csv
 * - Validação robusta de memória e dados
 * - Relatórios detalhados de processamento
 */
class GenerateTirePressureArticlesCommand extends Command
{
    protected $signature = 'tire-pressure:generate-initial 
                           {--csv-path=data/todos_veiculos.csv : Caminho para o CSV de veículos}
                           {--batch-size=50 : Número de artigos por lote}
                           {--filter-make= : Filtrar por marca específica}
                           {--filter-category= : Filtrar por categoria}
                           {--filter-vehicle-type= : Filtrar por tipo de veículo}
                           {--year-from= : Ano inicial para filtro}
                           {--year-to= : Ano final para filtro}
                           {--overwrite : Sobrescrever artigos existentes}
                           {--dry-run : Simular execução sem persistir}
                           {--show-progress : Mostrar barra de progresso}
                           {--validate-csv : Validar CSV antes do processamento}
                           {--batch-id= : ID personalizado para o lote}
                           {--memory-limit=512M : Limite de memória para o processo}';

    protected $description = 'Gera artigos iniciais de calibragem de pneus a partir de CSV (Etapa 1) - Formato ideal_tire_pressure_car.json';

    protected VehicleDataProcessorService $vehicleProcessor;
    protected InitialArticleGeneratorService $articleGenerator;
    protected GenerateInitialArticlesUseCase $generateUseCase;

    public function __construct(
        VehicleDataProcessorService $vehicleProcessor,
        InitialArticleGeneratorService $articleGenerator,
        GenerateInitialArticlesUseCase $generateUseCase
    ) {
        parent::__construct();
        $this->vehicleProcessor = $vehicleProcessor;
        $this->articleGenerator = $articleGenerator;
        $this->generateUseCase = $generateUseCase;
    }

    public function handle(): int
    {
        try {
            $this->info("🚀 INICIANDO GERAÇÃO DE ARTIGOS - TIRE PRESSURE GUIDE (ETAPA 1)");
            $this->line("=================================================================");

            // 1. Verificar e ajustar configuração do sistema
            $this->checkSystemRequirements();

            // 2. Obter e validar configuração
            $config = $this->getConfiguration();

            // 3. Validar CSV se solicitado
            if ($this->option('validate-csv')) {
                $this->validateCsvFile($config['csv_path']);
            }

            // 4. Validar ambiente
            $this->validateEnvironment($config);

            // 5. Mostrar estatísticas atuais
            $this->showCurrentStatistics();

            // 6. Confirmar execução
            if (!$this->confirmExecution($config)) {
                $this->info("Execução cancelada pelo usuário.");
                return self::SUCCESS;
            }

            // 7. Executar geração usando UseCase
            $results = $this->executeGeneration($config);

            // 8. Mostrar resultados
            $this->showResults($results);

            return $results->success ? self::SUCCESS : self::FAILURE;

        } catch (\Exception $e) {
            $this->error("💥 Erro na execução: " . $e->getMessage());
            Log::error("Erro no command GenerateInitialTirePressureArticles", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Verificar requisitos do sistema
     */
    protected function checkSystemRequirements(): void
    {
        // Verificar memória
        $memoryLimit = ini_get('memory_limit');
        $this->line("💾 Limite de memória: {$memoryLimit}");
        
        if ($this->isMemoryLimitLow($memoryLimit)) {
            $this->warn("⚠️ Limite de memória baixo. Recomendado: 512MB ou mais");
            
            // Tentar ajustar se foi especificado
            $requestedLimit = $this->option('memory-limit');
            if ($requestedLimit) {
                ini_set('memory_limit', $requestedLimit);
                $this->info("✅ Limite de memória ajustado para: {$requestedLimit}");
            }
        }

        // Verificar extensões necessárias
        $requiredExtensions = ['mbstring', 'json'];
        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                throw new \Exception("Extensão PHP requerida não encontrada: {$extension}");
            }
        }
    }

    /**
     * Verificar se limite de memória é baixo
     */
    protected function isMemoryLimitLow(string $memoryLimit): bool
    {
        $memoryInBytes = $this->convertToBytes($memoryLimit);
        $recommendedBytes = 512 * 1024 * 1024; // 512MB
        
        return $memoryInBytes < $recommendedBytes;
    }

    /**
     * Converter limite de memória para bytes
     */
    protected function convertToBytes(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit)-1]);
        $size = (int) $memoryLimit;
        
        switch($last) {
            case 'g': $size *= 1024;
            case 'm': $size *= 1024;
            case 'k': $size *= 1024;
        }
        
        return $size;
    }

    /**
     * Obter configuração da execução
     */
    protected function getConfiguration(): array
    {
        $config = [
            'csv_path' => $this->option('csv-path'),
            'batch_size' => (int) $this->option('batch-size'),
            'dry_run' => $this->option('dry-run'),
            'overwrite' => $this->option('overwrite'),
            'show_progress' => $this->option('show-progress'),
            'batch_id' => $this->option('batch-id') ?: $this->generateBatchId(),
            'filters' => $this->buildFilters()
        ];

        $this->info("📋 CONFIGURAÇÃO:");
        $this->line("   📂 CSV: {$config['csv_path']}");
        $this->line("   📦 Lote: {$config['batch_size']} artigos");
        $this->line("   🆔 Batch ID: {$config['batch_id']}");
        
        if ($config['dry_run']) {
            $this->line("   🔍 Modo: DRY RUN (simulação)");
        }
        
        if ($config['overwrite']) {
            $this->line("   ♻️ Sobrescrever: Ativado");
        }

        $this->line("   🔍 Filtros ativos:");
        if (empty(array_filter($config['filters']))) {
            $this->line("      (nenhum filtro aplicado)");
        } else {
            foreach ($config['filters'] as $key => $value) {
                if (!empty($value)) {
                    $this->line("      {$key}: {$value}");
                }
            }
        }

        return $config;
    }

    /**
     * Construir filtros a partir das opções
     */
    protected function buildFilters(): array
    {
        $filters = [];

        if ($make = $this->option('filter-make')) {
            $filters['make'] = $make;
        }

        if ($category = $this->option('filter-category')) {
            $filters['main_category'] = $category;
        }

        if ($vehicleType = $this->option('filter-vehicle-type')) {
            $filters['vehicle_type'] = $vehicleType;
        }

        if ($yearFrom = $this->option('year-from')) {
            $filters['year_from'] = (int) $yearFrom;
        }

        if ($yearTo = $this->option('year-to')) {
            $filters['year_to'] = (int) $yearTo;
        }

        return $filters;
    }

    /**
     * Validar arquivo CSV
     */
    protected function validateCsvFile(string $csvPath): void
    {
        $this->info("🔍 Validando arquivo CSV...");

        $validation = $this->generateUseCase->validateCsvCompatibility($csvPath);

        if (!$validation['compatible']) {
            $this->error("❌ CSV incompatível:");
            foreach ($validation['recommendations'] as $recommendation) {
                $this->line("   - {$recommendation}");
            }
            throw new \Exception("CSV não é compatível com o sistema");
        }

        $this->info("✅ CSV validado com sucesso");
        $this->line("   📊 Artigos estimados: {$validation['estimated_articles']}");
        
        if (!empty($validation['missing_fields'])) {
            $this->warn("⚠️ Campos ausentes (serão derivados): " . implode(', ', $validation['missing_fields']));
        }
    }

    /**
     * Validar ambiente de execução
     */
    protected function validateEnvironment(array $config): void
    {
        // Verificar se arquivo CSV existe
        if (!file_exists($config['csv_path'])) {
            throw new \Exception("Arquivo CSV não encontrado: {$config['csv_path']}");
        }

        // Verificar se é legível
        if (!is_readable($config['csv_path'])) {
            throw new \Exception("Arquivo CSV não é legível: {$config['csv_path']}");
        }

        // Verificar conexão com MongoDB
        try {
            TirePressureArticle::count();
        } catch (\Exception $e) {
            throw new \Exception("Erro de conexão com MongoDB: " . $e->getMessage());
        }

        $this->info("✅ Ambiente validado com sucesso");
    }

    /**
     * Mostrar estatísticas atuais
     */
    protected function showCurrentStatistics(): void
    {
        $stats = $this->generateUseCase->getCurrentStats();

        $this->info("📊 ESTATÍSTICAS ATUAIS:");
        $this->line("   Total de artigos: {$stats['total_articles']}");
        $this->line("   Pendentes: {$stats['pending']}");
        $this->line("   Gerados: {$stats['generated']}");
        $this->line("   Refinados (Claude): {$stats['claude_enhanced']}");
        $this->line("   Publicados: {$stats['published']}");

        if (!empty($stats['by_category'])) {
            $this->line("   Por categoria:");
            foreach (array_slice($stats['by_category'], 0, 5, true) as $category => $count) {
                $this->line("      {$category}: {$count}");
            }
        }

        $this->newLine();
    }

    /**
     * Confirmar execução
     */
    protected function confirmExecution(array $config): bool
    {
        if ($config['dry_run']) {
            return true; // Não precisa confirmar para dry run
        }

        return $this->confirm('Deseja continuar com a geração?', false);
    }

    /**
     * Executar geração usando UseCase
     */
    protected function executeGeneration(array $config): object
    {
        $progressCallback = null;

        if ($config['show_progress']) {
            $progressBar = $this->output->createProgressBar();
            $progressBar->setFormat('verbose');
            
            $progressCallback = function ($current, $total, $results) use ($progressBar) {
                $progressBar->setMaxSteps($total);
                $progressBar->setProgress($current);
                $progressBar->setMessage("Lote {$current}/{$total} - Gerados: {$results->articles_generated}");
            };
        }

        // Executar geração através do UseCase
        $results = $this->generateUseCase->execute(
            $config['csv_path'],
            $config['batch_size'],
            $config['filters'],
            $config['dry_run'],
            $config['overwrite'],
            $progressCallback
        );

        if ($progressCallback) {
            $this->newLine(2);
        }

        return $results;
    }

    /**
     * Mostrar resultados da execução
     */
    protected function showResults(object $results): void
    {
        if ($results->success) {
            $this->info("🎉 GERAÇÃO CONCLUÍDA COM SUCESSO!");
        } else {
            $this->error("❌ GERAÇÃO CONCLUÍDA COM ERROS");
        }

        $this->line("=================================================================");
        
        // Estatísticas principais
        $this->info("📊 ESTATÍSTICAS:");
        $this->line("   📥 Total processado: {$results->total_processed}");
        $this->line("   ✅ Artigos gerados: {$results->articles_generated}");
        $this->line("   ⏭️ Artigos ignorados: {$results->articles_skipped}");
        $this->line("   ❌ Artigos com falha: {$results->articles_failed}");
        
        if (isset($results->generation_summary['success_rate'])) {
            $successRate = $results->generation_summary['success_rate'];
            $this->line("   📈 Taxa de sucesso: {$successRate}%");
        }

        // Validação do CSV
        if (!empty($results->csv_validation)) {
            $this->newLine();
            $this->info("📋 VALIDAÇÃO DO CSV:");
            $csvVal = $results->csv_validation;
            
            if (isset($csvVal['validation_rate'])) {
                $this->line("   Taxa de validação: {$csvVal['validation_rate']}%");
            }
            
            if (isset($csvVal['raw_count']) && isset($csvVal['validated_count'])) {
                $this->line("   Dados brutos: {$csvVal['raw_count']}");
                $this->line("   Dados válidos: {$csvVal['validated_count']}");
            }
        }

        // Estatísticas de processamento
        if (!empty($results->processing_stats)) {
            $this->newLine();
            $this->info("🚗 ESTATÍSTICAS POR TIPO:");
            $stats = $results->processing_stats;
            
            if (isset($stats['cars']) && isset($stats['motorcycles'])) {
                $this->line("   Carros: {$stats['cars']}");
                $this->line("   Motocicletas: {$stats['motorcycles']}");
            }

            if (!empty($stats['by_category'])) {
                $this->line("   Top categorias:");
                foreach (array_slice($stats['by_category'], 0, 5, true) as $category => $count) {
                    $this->line("      {$category}: {$count}");
                }
            }
        }

        // Erros (se houver)
        if (!empty($results->errors)) {
            $this->newLine();
            $this->warn("⚠️ ERROS ENCONTRADOS:");
            
            $errorCount = count($results->errors);
            $maxDisplay = 10;
            
            foreach (array_slice($results->errors, 0, $maxDisplay) as $error) {
                $this->line("   - {$error}");
            }
            
            if ($errorCount > $maxDisplay) {
                $remaining = $errorCount - $maxDisplay;
                $this->line("   ... e mais {$remaining} erros (verificar logs)");
            }
        }

        // Recomendações
        if (!empty($results->generation_summary['recommendations'])) {
            $this->newLine();
            $this->info("💡 RECOMENDAÇÕES:");
            foreach ($results->generation_summary['recommendations'] as $recommendation) {
                $this->line("   - {$recommendation}");
            }
        }

        // Próximos passos
        if ($results->success && $results->articles_generated > 0) {
            $this->newLine();
            $this->info("🚀 PRÓXIMOS PASSOS:");
            
            if (!$results instanceof \stdClass || !property_exists($results, 'dry_run') || !$results->dry_run) {
                $this->line("   1. Verificar artigos gerados no MongoDB");
                $this->line("   2. Executar Segunda Etapa (refinamento Claude):");
                $this->line("      php artisan tire-pressure-guide:refine-sections");
                $this->line("   3. Testar publicação na TempArticle:");
                $this->line("      php artisan tire-pressure-guide:publish-temp --dry-run");
            } else {
                $this->line("   1. Executar sem --dry-run para gerar artigos reais");
                $this->line("   2. Ajustar filtros se necessário");
            }
        }

        $this->newLine();
    }

    /**
     * Gerar ID único para o lote
     */
    protected function generateBatchId(): string
    {
        return 'batch_' . now()->format('Ymd_His');
    }

    /**
     * Validar lote de tamanho
     */
    protected function validateBatchSize(int $batchSize): void
    {
        if ($batchSize < 1 || $batchSize > 1000) {
            throw new \Exception("Tamanho do lote deve estar entre 1 e 1000. Fornecido: {$batchSize}");
        }

        if ($batchSize > 100) {
            $this->warn("⚠️ Lote grande ({$batchSize}). Considere usar lotes menores para melhor performance.");
        }
    }
}
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
 * FIXED Command for DUAL TEMPLATE generation - MongoDB Compatible
 * 
 * CORREÇÕES:
 * - Removido selectRaw incompatível com MongoDB
 * - Corrigido estimateVehiclesFromCsv para usar Collection
 * - Melhorado showCurrentStatistics para MongoDB
 */
class GenerateTirePressureArticlesCommand extends Command
{
    protected $signature = 'tire-pressure:generate-initial 
                           {--csv-path=data/todos_veiculos.csv : Caminho para o CSV de veículos}
                           {--template=ideal : Tipo de template (ideal|calibration|both)}
                           {--batch-size=50 : Número de veículos por lote}
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

    protected $description = 'Gera artigos de pneus em DUAL TEMPLATE: ideal_tire_pressure_car + tire_pressure_guide_car (Etapa 1)';

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
            $this->info("🚀 TIRE PRESSURE GUIDE - DUAL TEMPLATE GENERATION (ETAPA 1)");
            $this->line("================================================================");

            // 1. Verificar sistema
            $this->checkSystemRequirements();

            // 2. Validar e obter configuração
            $config = $this->getConfiguration();

            // 3. Validar template option
            $this->validateTemplateOption($config['template']);

            // 4. Mostrar informações do template
            $this->showTemplateInfo($config['template']);

            // 5. Validar CSV se solicitado
            if ($this->option('validate-csv')) {
                $this->validateCsvFile($config['csv_path']);
            }

            // 6. Mostrar estatísticas atuais
            $this->showCurrentStatistics();

            // 7. Estimar artigos que serão gerados
            $this->showGenerationEstimate($config);

            // 8. Confirmar execução
            if (!$this->confirmExecution($config)) {
                $this->info("Execução cancelada pelo usuário.");
                return self::SUCCESS;
            }

            // 9. Executar geração baseada no template
            $results = $this->executeTemplateBasedGeneration($config);

            // 10. Mostrar resultados
            $this->showResults($results);

            return $results->success ? self::SUCCESS : self::FAILURE;

        } catch (\Exception $e) {
            $this->error("💥 Erro na execução: " . $e->getMessage());
            Log::error("Erro no GenerateTirePressureArticlesCommand", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Obter configuração incluindo template type
     */
    protected function getConfiguration(): array
    {
        $config = [
            'csv_path' => $this->option('csv-path'),
            'template' => $this->option('template'),
            'batch_size' => (int) $this->option('batch-size'),
            'dry_run' => $this->option('dry-run'),
            'overwrite' => $this->option('overwrite'),
            'show_progress' => $this->option('show-progress'),
            'batch_id' => $this->option('batch-id') ?: $this->generateBatchId(),
            'filters' => $this->buildFilters()
        ];

        $this->info("📋 CONFIGURAÇÃO:");
        $this->line("   📂 CSV: {$config['csv_path']}");
        $this->line("   🎨 Template: {$config['template']}");
        $this->line("   📦 Lote: {$config['batch_size']} veículos");
        $this->line("   🆔 Batch ID: {$config['batch_id']}");
        
        if ($config['dry_run']) {
            $this->line("   🔍 Modo: DRY RUN (simulação)");
        }
        
        if ($config['overwrite']) {
            $this->line("   ♻️ Sobrescrever: Ativado");
        }

        return $config;
    }

    /**
     * Validar opção de template
     */
    protected function validateTemplateOption(string $template): void
    {
        $validTemplates = ['ideal', 'calibration', 'both'];
        
        if (!in_array($template, $validTemplates)) {
            throw new \Exception("Template inválido: {$template}. Use: " . implode(', ', $validTemplates));
        }
    }

    /**
     * Mostrar informações sobre os templates
     */
    protected function showTemplateInfo(string $template): void
    {
        $this->info("🎨 INFORMAÇÕES DO TEMPLATE:");
        
        switch ($template) {
            case 'ideal':
                $this->line("   📊 Tipo: Pressão Ideal (IdealTirePressureCarViewModel)");
                $this->line("   🔗 URL: /pressao-ideal-pneu-honda-civic-2022/");
                $this->line("   📝 Foco: Especificações, pressões ideais, economia");
                $this->line("   📊 Artigos por veículo: 1");
                break;
                
            case 'calibration':
                $this->line("   🔧 Tipo: Calibragem (TirePressureGuideCarViewModel)");
                $this->line("   🔗 URL: /calibragem-pneu-honda-civic-2022/");
                $this->line("   📝 Foco: Procedimentos, TPMS, troubleshooting");
                $this->line("   📊 Artigos por veículo: 1");
                break;
                
            case 'both':
                $this->line("   🔄 Tipo: AMBOS os templates");
                $this->line("   🔗 URLs: /pressao-ideal-* + /calibragem-*");
                $this->line("   📝 Foco: Cobertura completa do tópico");
                $this->line("   📊 Artigos por veículo: 2 (DOBRO)");
                $this->warn("   ⚠️ ATENÇÃO: Gerará 2x mais artigos!");
                break;
        }
        
        $this->newLine();
    }

    /**
     * FIXED: Mostrar estimativa de geração (MongoDB compatible)
     */
    protected function showGenerationEstimate(array $config): void
    {
        try {
            // CORRIGIDO: Estimar veículos do CSV usando o VehicleDataProcessor
            $estimatedVehicles = $this->estimateVehiclesFromCsv($config['csv_path'], $config['filters']);
            
            // Calcular artigos baseado no template
            $articlesPerVehicle = $config['template'] === 'both' ? 2 : 1;
            $estimatedArticles = $estimatedVehicles * $articlesPerVehicle;
            
            $this->info("📈 ESTIMATIVA DE GERAÇÃO:");
            $this->line("   🚗 Veículos no CSV: {$estimatedVehicles}");
            $this->line("   📄 Artigos por veículo: {$articlesPerVehicle}");
            $this->line("   📚 Total de artigos: {$estimatedArticles}");
            
            if ($config['template'] === 'both') {
                $this->line("   ├── Pressão Ideal: {$estimatedVehicles}");
                $this->line("   └── Calibragem: {$estimatedVehicles}");
            }
            
            // Estimar tempo (baseado em experiência)
            $timePerArticle = 0.5; // segundos
            $estimatedTime = ($estimatedArticles * $timePerArticle) / 60; // minutos
            $this->line("   ⏱️ Tempo estimado: " . round($estimatedTime, 1) . " minutos");
            
            $this->newLine();
            
        } catch (\Exception $e) {
            $this->warn("⚠️ Não foi possível calcular estimativa: " . $e->getMessage());
        }
    }

    /**
     * FIXED: Estimar veículos do CSV (usando VehicleDataProcessor)
     */
    protected function estimateVehiclesFromCsv(string $csvPath, array $filters): int
    {
        try {
            // CORRIGIDO: Usar o VehicleDataProcessor em vez de query MongoDB
            $sampleData = $this->vehicleProcessor->processVehicleData($csvPath, $filters);
            return $sampleData->count();
        } catch (\Exception $e) {
            Log::warning("Erro ao estimar veículos do CSV", [
                'csv_path' => $csvPath,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Executar geração baseada no template
     */
    protected function executeTemplateBasedGeneration(array $config): object
    {
        $results = (object)[
            'success' => false,
            'total_vehicles_processed' => 0,
            'articles_generated' => 0,
            'articles_skipped' => 0,
            'articles_failed' => 0,
            'template_breakdown' => [],
            'errors' => [],
            'batch_id' => $config['batch_id']
        ];

        try {
            // Usar o UseCase para a execução principal
            $useCaseResults = $this->generateUseCase->execute(
                $config['csv_path'],
                $config['batch_size'],
                $config['filters'],
                $config['dry_run'],
                $config['overwrite'],
                $config['template'],
                $config['show_progress'] ? $this->createProgressCallback() : null
            );

            // Converter resultados do UseCase para o formato esperado
            $results->success = $useCaseResults->success;
            $results->total_vehicles_processed = $useCaseResults->total_vehicles_processed;
            $results->articles_generated = $useCaseResults->total_articles_generated;
            $results->articles_skipped = $useCaseResults->articles_skipped;
            $results->articles_failed = $useCaseResults->articles_failed;
            $results->template_breakdown = $useCaseResults->template_breakdown;
            $results->errors = $useCaseResults->errors;

        } catch (\Exception $e) {
            $results->success = false;
            $results->errors[] = "Erro geral: " . $e->getMessage();
            
            Log::error("Erro na execução template-based generation", [
                'config' => $config,
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Criar callback de progresso
     */
    protected function createProgressCallback(): ?callable
    {
        if (!$this->option('show-progress')) {
            return null;
        }

        $progressBar = null;
        
        return function ($current, $total, $template = null, $vehicle = null) use (&$progressBar) {
            if (!$progressBar) {
                $progressBar = $this->output->createProgressBar($total);
                $progressBar->setFormat('verbose');
            }
            
            $progressBar->setProgress($current);
            
            if ($template) {
                $progressBar->setMessage("Template: {$template}");
            }
            
            if ($current >= $total) {
                $progressBar->finish();
                $this->newLine();
            }
        };
    }

    /**
     * Verificar se artigo já existe para o template específico
     */
    protected function articleExists(array $vehicleData, string $templateType): bool
    {
        try {
            $slug = $this->generateSlugForTemplate($vehicleData, $templateType);
            
            return TirePressureArticle::where('slug', $slug)
                                    ->where('template_type', $templateType)
                                    ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Gerar slug para template específico
     */
    protected function generateSlugForTemplate(array $vehicleData, string $templateType): string
    {
        $make = \Illuminate\Support\Str::slug($vehicleData['make'] ?? '');
        $model = \Illuminate\Support\Str::slug($vehicleData['model'] ?? '');
        $year = $vehicleData['year'] ?? '';
        
        if ($templateType === 'ideal') {
            return "pressao-ideal-pneu-{$make}-{$model}-{$year}";
        } elseif ($templateType === 'calibration') {
            return "calibragem-pneu-{$make}-{$model}-{$year}";
        }
        
        return "pneu-{$make}-{$model}-{$year}";
    }

    /**
     * FIXED: Mostrar estatísticas atuais (MongoDB compatible)
     */
    protected function showCurrentStatistics(): void
    {
        try {
            // CORRIGIDO: Usar métodos compatíveis com MongoDB
            $totalArticles = TirePressureArticle::count();
            $pendingArticles = TirePressureArticle::where('generation_status', 'pending')->count();
            $generatedArticles = TirePressureArticle::where('generation_status', 'generated')->count();
            $enhancedArticles = TirePressureArticle::where('generation_status', 'claude_enhanced')->count();
            $publishedArticles = TirePressureArticle::where('generation_status', 'published')->count();

            $this->info("📊 ESTATÍSTICAS ATUAIS:");
            $this->line("   Total de artigos: {$totalArticles}");
            $this->line("   Pendentes: {$pendingArticles}");
            $this->line("   Gerados: {$generatedArticles}");
            $this->line("   Refinados (Claude): {$enhancedArticles}");
            $this->line("   Publicados: {$publishedArticles}");

            // Estatísticas por template type - CORRIGIDO
            $templateStats = TirePressureArticle::get(['template_type'])
                                              ->groupBy('template_type')
                                              ->map(function($group) {
                                                  return $group->count();
                                              });

            if ($templateStats->isNotEmpty()) {
                $this->line("   Por template:");
                foreach ($templateStats as $templateType => $count) {
                    $templateName = $templateType ?: 'indefinido';
                    $this->line("      {$templateName}: {$count}");
                }
            }

        } catch (\Exception $e) {
            $this->warn("⚠️ Erro ao obter estatísticas: " . $e->getMessage());
            Log::error("Erro ao obter estatísticas atuais", [
                'error' => $e->getMessage()
            ]);
        }

        $this->newLine();
    }

    /**
     * Confirmar execução com informações de template
     */
    protected function confirmExecution(array $config): bool
    {
        if ($config['dry_run']) {
            return true; // Não precisa confirmar para dry run
        }

        $templateDesc = $config['template'] === 'both' ? 'AMBOS os templates (2x artigos)' : "template '{$config['template']}'";
        
        return $this->confirm("Deseja continuar com a geração usando {$templateDesc}?", false);
    }

    /**
     * Mostrar resultados com breakdown por template
     */
    protected function showResults(object $results): void
    {
        if ($results->success) {
            $this->info("🎉 GERAÇÃO DUAL TEMPLATE CONCLUÍDA COM SUCESSO!");
        } else {
            $this->error("❌ GERAÇÃO CONCLUÍDA COM ERROS");
        }

        $this->line("=================================================================");
        
        // Estatísticas principais
        $this->info("📊 ESTATÍSTICAS GERAIS:");
        $this->line("   🚗 Veículos processados: {$results->total_vehicles_processed}");
        $this->line("   ✅ Artigos gerados: {$results->articles_generated}");
        $this->line("   ⏭️ Artigos ignorados: {$results->articles_skipped}");
        $this->line("   ❌ Artigos com falha: {$results->articles_failed}");

        // Breakdown por template
        if (!empty($results->template_breakdown)) {
            $this->newLine();
            $this->info("🎨 BREAKDOWN POR TEMPLATE:");
            
            foreach ($results->template_breakdown as $templateType => $stats) {
                $this->line("   📄 Template '{$templateType}':");
                $this->line("      ✅ Gerados: {$stats['generated']}");
                $this->line("      ⏭️ Ignorados: {$stats['skipped']}");
                $this->line("      ❌ Falhas: {$stats['failed']}");
                
                $total = $stats['generated'] + $stats['skipped'] + $stats['failed'];
                if ($total > 0) {
                    $successRate = round(($stats['generated'] / $total) * 100, 1);
                    $this->line("      📈 Taxa de sucesso: {$successRate}%");
                }
            }
        }

        // URLs de exemplo geradas
        if ($results->articles_generated > 0) {
            $this->newLine();
            $this->info("🔗 EXEMPLOS DE URLs GERADAS:");
            
            if (isset($results->template_breakdown['ideal'])) {
                $this->line("   📊 Pressão Ideal: /pressao-ideal-pneu-honda-civic-2022/");
            }
            
            if (isset($results->template_breakdown['calibration'])) {
                $this->line("   🔧 Calibragem: /calibragem-pneu-honda-civic-2022/");
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

        // Próximos passos
        if ($results->success && $results->articles_generated > 0) {
            $this->newLine();
            $this->info("🚀 PRÓXIMOS PASSOS:");
            
            $this->line("   1. Verificar artigos gerados no MongoDB:");
            $this->line("      db.tire_pressure_articles.find({template_type: 'ideal'})");
            $this->line("      db.tire_pressure_articles.find({template_type: 'calibration'})");
            
            $this->line("   2. Executar Segunda Etapa (refinamento Claude):");
            $this->line("      php artisan tire-pressure-guide:refine-sections --template=ideal");
            $this->line("      php artisan tire-pressure-guide:refine-sections --template=calibration");
        }

        $this->newLine();
    }

    // ===== MÉTODOS AUXILIARES =====

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
            
            $requestedLimit = $this->option('memory-limit');
            if ($requestedLimit) {
                ini_set('memory_limit', $requestedLimit);
                $this->info("✅ Limite de memória ajustado para: {$requestedLimit}");
            }
        }

        // Verificar extensões necessárias
        $requiredExtensions = ['mbstring', 'json', 'mongodb'];
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
     * Gerar ID único para o lote
     */
    protected function generateBatchId(): string
    {
        return 'dual_batch_' . now()->format('Ymd_His');
    }
}
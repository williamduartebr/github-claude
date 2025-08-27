<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Carbon\Carbon;

/**
 * CopyCalibrationArticlesCommand - CORRIGIDO - Versões V1/V2
 * 
 * V1: Inclui vehicle_year (963 artigos esperados)
 * V2: Remove vehicle_year (300+ artigos esperados)
 * 
 * USO:
 * php artisan tire-calibration:copy-calibration --version=v1 --limit=100 --dry-run
 * php artisan tire-calibration:copy-calibration --version=v2 --validate
 * php artisan tire-calibration:copy-calibration --version=both --force
 * 
 * @version 3.0 - V1/V2 com controle vehicle_year
 */
class CopyCalibrationArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tire-calibration:copy-calibration
                            {--versao=v1 : Versão a processar (v1, v2, both)}
                            {--limit=1000 : Número máximo de artigos a processar}
                            {--category= : Filtrar por categoria específica}
                            {--make= : Filtrar por marca específica}
                            {--dry-run : Simular execução sem salvar dados}
                            {--validate : Validar dados antes de processar}
                            {--skip-existing : Pular artigos já processados}
                            {--force : Reprocessar artigos já existentes}';

    /**
     * The console command description.
     */
    protected $description = 'Copiar dados TirePressureArticle com versões V1 (com vehicle_year) e V2 (sem vehicle_year)';

    protected int $processedCount = 0;
    protected int $skippedCount = 0;
    protected int $errorCount = 0;
    protected array $stats = [];
    protected array $validationErrors = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);

        $this->info('🔄 COPIANDO ARTIGOS TIRE PRESSURE - VERSÕES V1/V2');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            // 1. Validar configurações
            $config = $this->getConfiguration();
            $this->displayConfiguration($config);

            // 2. Buscar artigos para processamento
            $articles = $this->getTirePressureArticles($config);

            if ($articles->isEmpty()) {
                $this->warn('⚠️ Nenhum artigo encontrado com template_type = calibration');
                return self::SUCCESS;
            }

            $this->info("📊 {$articles->count()} artigos calibration encontrados para processamento");
            $this->newLine();

            // 3. Processar baseado na versão
            if ($config['version'] === 'both') {
                $this->processVersion($articles, 'v1', $config);
                $this->processVersion($articles, 'v2', $config);
            } else {
                $this->processVersion($articles, $config['version'], $config);
            }

            // 4. Exibir estatísticas finais
            $this->displayFinalStats($startTime);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ ERRO FATAL: ' . $e->getMessage());
            Log::error('CopyCalibrationArticlesCommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Obter configuração do comando
     */
    protected function getConfiguration(): array
    {
        $version = $this->option('versao');
        
        // Validar versão
        if (!in_array($version, ['v1', 'v2', 'both'])) {
            throw new \InvalidArgumentException("Versão inválida: {$version}. Use: v1, v2 ou both");
        }

        return [
            'version' => $version,
            'limit' => (int) $this->option('limit'),
            'category' => $this->option('category'),
            'make' => $this->option('make'),
            'dry_run' => $this->option('dry-run'),
            'validate' => $this->option('validate'),
            'skip_existing' => $this->option('skip-existing'),
            'force' => $this->option('force'),
        ];
    }

    /**
     * Exibir configuração
     */
    protected function displayConfiguration(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO:');
        $this->line("   • 🎯 Versão: {$config['version']}");
        $this->line("   • 📊 Limite: {$config['limit']} artigos");
        $this->line("   • 🏷️ Categoria: " . ($config['category'] ?? 'Todas'));
        $this->line("   • 🚗 Marca: " . ($config['make'] ?? 'Todas'));
        $this->line("   • 🧪 Modo simulação: " . ($config['dry_run'] ? '✅ SIM' : '❌ NÃO'));
        $this->newLine();

        $this->info('📋 DIFERENÇAS DAS VERSÕES:');
        $this->line("   • V1: COM vehicle_year (~963 artigos)");
        $this->line("   • V2: SEM vehicle_year (~289 artigos)");
        $this->newLine();
    }

    /**
     * Buscar artigos TirePressureArticle
     */
    protected function getTirePressureArticles(array $config)
    {
        $query = \Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle::where('template_type', 'calibration');

        // Filtros
        if ($config['category']) {
            $query->where('vehicle_data', 'like', '%"main_category":"' . $config['category'] . '"%');
        }

        if ($config['make']) {
            $query->where('vehicle_data', 'like', '%"make":"' . $config['make'] . '"%');
        }

        // Campos obrigatórios
        $query->whereNotNull('wordpress_url')
              ->whereNotNull('vehicle_data')
              ->where('vehicle_data', '!=', '');

        return $query->limit($config['limit'])->get();
    }

    /**
     * Processar versão específica
     */
    protected function processVersion($articles, string $version, array $config): void
    {
        $this->info("🔄 PROCESSANDO VERSÃO {$version}...");
        
        // V2: Agrupar por make+model (sem ano) para evitar duplicatas
        if ($version === 'v2') {
            $articles = $this->deduplicateForV2($articles);
            $this->line("   📊 Após agrupamento V2: {$articles->count()} artigos únicos");
        }
        
        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->start();

        foreach ($articles as $article) {
            try {
                // Parse do vehicle_data (string ou array)
                $vehicleData = $this->parseVehicleData($article->vehicle_data ?? null);
                
                if (!$vehicleData) {
                    $this->errorCount++;
                    $progressBar->advance();
                    continue;
                }

                // Construir dados baseado na versão
                $calibrationData = $this->buildCalibrationData($article, $vehicleData, $version);

                // Salvar no banco
                if (!$config['dry_run']) {
                    $this->saveTireCalibration($calibrationData, $config);
                }

                $this->processedCount++;
                $progressBar->advance();

            } catch (\Exception $e) {
                $this->errorCount++;
                Log::error('CopyCalibrationArticlesCommand: Erro no processamento', [
                    'version' => $version,
                    'article_url' => $article->wordpress_url ?? 'N/A',
                    'error' => $e->getMessage()
                ]);
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * Deduplificar artigos para V2 (agrupar por make+model, ignorar ano)
     */
    protected function deduplicateForV2($articles)
    {
        $unique = collect();
        $seenCombinations = [];

        foreach ($articles as $article) {
            $vehicleData = $this->parseVehicleData($article->vehicle_data ?? null);
            
            if (!$vehicleData) continue;
            
            $make = $vehicleData['make'] ?? '';
            $model = $vehicleData['model'] ?? '';
            $key = strtolower($make . '|' . $model);
            
            // Se ainda não vimos esta combinação make+model, adicionar
            if (!isset($seenCombinations[$key])) {
                $seenCombinations[$key] = true;
                $unique->push($article);
            }
            // Se já vimos, pular (ignorar anos diferentes do mesmo modelo)
        }

        return $unique;
    }

    /**
     * Parse do vehicle_data (aceita array ou string)
     */
    protected function parseVehicleData($vehicleData): ?array
    {
        // Se já é array, retorna diretamente
        if (is_array($vehicleData)) {
            return $vehicleData;
        }
        
        // Se não é string, retorna null
        if (!is_string($vehicleData) || empty($vehicleData)) {
            return null;
        }

        // Tentar parse JSON
        $parsed = json_decode($vehicleData, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $parsed;
        }

        return null;
    }

    /**
     * Construir dados baseado na versão
     */
    protected function buildCalibrationData($article, array $vehicleData, string $version): array
    {
        $data = [
            // Campos básicos  
            'version' => $version,
            'blog_modified_time' => $this->parseTimestamp($article->blog_modified_time ?? null),
            'blog_published_time' => $this->parseTimestamp($article->blog_published_time ?? null),
            
            // Dados do veículo
            'vehicle_make' => $vehicleData['make'] ?? null,
            'vehicle_model' => $vehicleData['model'] ?? null,
            'main_category' => $vehicleData['main_category'] ?? null,
            
            // Dados estruturados
            'vehicle_basic_data' => [
                'make' => $vehicleData['make'] ?? null,
                'model' => $vehicleData['model'] ?? null,
                'full_name' => $vehicleData['vehicle_full_name'] ?? null,
                'category_normalized' => $vehicleData['category_normalized'] ?? null,
            ],
            
            'pressure_specifications' => [
                'tire_size' => $vehicleData['tire_size'] ?? null,
                'empty_front' => $this->parseFloat($vehicleData['pressure_empty_front'] ?? null),
                'empty_rear' => $this->parseFloat($vehicleData['pressure_empty_rear'] ?? null),
                'light_front' => $this->parseFloat($vehicleData['pressure_light_front'] ?? null),
                'light_rear' => $this->parseFloat($vehicleData['pressure_light_rear'] ?? null),
                'spare' => $this->parseFloat($vehicleData['pressure_spare'] ?? null),
            ],
            
            'vehicle_features' => [
                'has_tpms' => $this->parseBoolean($vehicleData['has_tpms'] ?? null),
                'is_premium' => $this->parseBoolean($vehicleData['is_premium'] ?? null),
                'vehicle_type' => $vehicleData['vehicle_type'] ?? 'car',
            ],
            
            // Estado inicial
            'enrichment_phase' => 'pending',
            'processing_attempts' => 0,
            'data_completeness_score' => $this->calculateCompletenessScore($vehicleData),
        ];

        // DIFERENÇA PRINCIPAL: V1 inclui vehicle_year e URL com ano, V2 não
        if ($version === 'v1') {
            $data['vehicle_year'] = (int) ($vehicleData['year'] ?? 0);
            $data['vehicle_basic_data']['year'] = (int) ($vehicleData['year'] ?? 0);
            $data['wordpress_url'] = $article->wordpress_url ?? null; // URL original com ano
        } else {
            // V2: URL genérica sem ano
            $data['wordpress_url'] = $this->generateGenericUrlForV2($vehicleData);
            // vehicle_year propositalmente NÃO incluído
        }

        return $data;
    }

    /**
     * Gerar URL genérica para V2 (sem ano)
     */
    protected function generateGenericUrlForV2(array $vehicleData): string
    {
        $make = strtolower($vehicleData['make'] ?? '');
        $model = strtolower($vehicleData['model'] ?? '');
        
        $make = preg_replace('/[^a-z0-9]/', '-', $make);
        $model = preg_replace('/[^a-z0-9]/', '-', $model);
        
        return "calibragem-pneu-{$make}-{$model}";
    }

    /**
     * Salvar TireCalibration no banco
     */
    protected function saveTireCalibration(array $data, array $config): void
    {
        TireCalibration::updateOrCreate(
            [
                'wordpress_url' => $data['wordpress_url'],
                'version' => $data['version']
            ],
            $data
        );
    }

    /**
     * Exibir estatísticas finais
     */
    protected function displayFinalStats(float $startTime): void
    {
        $executionTime = round(microtime(true) - $startTime, 2);
        
        $this->newLine();
        $this->info('=== ESTATÍSTICAS FINAIS ===');
        $this->line("✅ Processados: {$this->processedCount}");
        $this->line("⏭️ Ignorados: {$this->skippedCount}");
        $this->line("❌ Erros: {$this->errorCount}");
        $this->line("⏱️ Tempo: {$executionTime}s");

        Log::info('CopyCalibrationArticlesCommand: Execução concluída', [
            'processed' => $this->processedCount,
            'errors' => $this->errorCount,
            'execution_time' => $executionTime
        ]);
    }

    /**
     * Helper methods
     */
    protected function parseTimestamp($value): ?Carbon
    {
        if (!$value) return null;
        
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseFloat($value): ?float
    {
        if ($value === null || $value === '') return null;
        return (float) $value;
    }

    protected function parseBoolean($value): ?bool
    {
        if ($value === null) return null;
        return (bool) $value;
    }

    protected function calculateCompletenessScore(array $vehicleData): float
    {
        $essentialFields = ['make', 'model', 'main_category', 'tire_size', 'pressure_empty_front'];
        
        $filled = 0;
        foreach ($essentialFields as $field) {
            if (!empty($vehicleData[$field])) {
                $filled++;
            }
        }
        
        return round(($filled / count($essentialFields)) * 10, 1);
    }
}
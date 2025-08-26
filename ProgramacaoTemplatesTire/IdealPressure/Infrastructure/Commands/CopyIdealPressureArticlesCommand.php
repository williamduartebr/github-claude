<?php

namespace Src\ContentGeneration\IdealPressure\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\IdealPressure\Domain\Entities\IdealPressure;
use Carbon\Carbon;

/**
 * CopyIdealPressureArticlesCommand - CORRIGIDO - Cópia inteligente de TirePressureArticle
 * 
 * Extrai e estrutura dados do campo vehicle_data (JSON string) para criar
 * registros IdealPressure otimizados para busca e processamento futuro.
 * 
 * ESTRATÉGIA:
 * - Parse do JSON vehicle_data string
 * - Extração de campos-chave para indexação
 * - Estruturação em arrays organizados para VehicleData lookup
 * - Campos de filtro para consultas eficientes
 * 
 * USO:
 * php artisan ideal-pressure:copy-calibration --limit=100 --dry-run
 * php artisan ideal-pressure:copy-calibration --category=hatch --validate
 * 
 * @author Claude Sonnet 4
 * @version 2.0 - Implementação corrigida com parsing JSON
 */
class CopyIdealPressureArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ideal-pressure:copy-calibration
                            {--limit=1000 : Número máximo de artigos a processar}
                            {--category= : Filtrar por categoria específica (hatch, suv, sedan, etc)}
                            {--make= : Filtrar por marca específica}
                            {--dry-run : Simular execução sem salvar dados}
                            {--validate : Validar dados antes de processar}
                            {--skip-existing : Pular artigos já processados}
                            {--force : Reprocessar artigos já existentes}';

    /**
     * The console command description.
     */
    protected $description = 'CORRIGIDO: Copiar dados de TirePressureArticle com parsing inteligente do vehicle_data JSON';

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

        $this->info('🔄 COPIANDO ARTIGOS TIRE PRESSURE - VERSÃO CORRIGIDA');
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

            if ($config['validate']) {
                $this->displayArticlesPreview($articles);
            }

            $this->newLine();

            // 3. Processar artigos
            $this->processArticles($articles, $config);

            // 4. Exibir estatísticas finais
            $this->displayFinalStats($startTime);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ ERRO FATAL: ' . $e->getMessage());
            Log::error('CopyIdealPressureArticlesCommand: Erro fatal', [
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
        return [
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
        $this->line("   • Limite: {$config['limit']} artigos");
        $this->line("   • Categoria: " . ($config['category'] ?? 'Todas'));
        $this->line("   • Marca: " . ($config['make'] ?? 'Todas'));
        $this->line("   • Modo simulação: " . ($config['dry_run'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Validar dados: " . ($config['validate'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Pular existentes: " . ($config['skip_existing'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Forçar reprocessamento: " . ($config['force'] ? '✅ SIM' : '❌ NÃO'));
        $this->newLine();
    }

    /**
     * Buscar artigos TirePressureArticle para processamento
     */
    protected function getTirePressureArticles(array $config)
    {
        // Usar diretamente o model TirePressureArticle com filtro específico
        $query = \Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle::where('template_type', 'ideal');

        // Filtros adicionais baseados no vehicle_data JSON
        if ($config['category']) {
            $query->where('vehicle_data', 'like', '%"main_category":"' . $config['category'] . '"%');
        }

        if ($config['make']) {
            $query->where('vehicle_data', 'like', '%"make":"' . $config['make'] . '"%');
        }

        // Pular existentes se solicitado
        if ($config['skip_existing'] && !$config['force']) {
            $existingUrls = IdealPressure::pluck('wordpress_url')->toArray();
            if (!empty($existingUrls)) {
                $query->whereNotIn('wordpress_url', $existingUrls);
            }
        }

        // Campos obrigatórios para o processamento
        $query->whereNotNull('wordpress_url')
            ->whereNotNull('vehicle_data')
            ->where('vehicle_data', '!=', '');

        return $query->limit($config['limit'])->get();
    }

    /**
     * Processar artigos encontrados
     */
    protected function processArticles($articles, array $config): void
    {
        $this->info('🔄 PROCESSANDO ARTIGOS...');
        $this->newLine();

        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->start();

        foreach ($articles as $article) {
            try {
                $this->processArticle($article, $config);
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->line('');
                $this->error("❌ Erro no artigo {$article['wordpress_url']}: " . $e->getMessage());

                Log::error('CopyIdealPressureArticlesCommand: Erro no processamento', [
                    'article_url' => $article['wordpress_url'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $progressBar->advance();
                continue;
            }
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * Processar artigo individual - CORE LOGIC
     */
    protected function processArticle($article, array $config): void
    {
        // 1. Parse do JSON vehicle_data
        $vehicleDataParsed = $this->parseVehicleData($article['vehicle_data'] ?? '');

        if (!$vehicleDataParsed) {
            $this->skippedCount++;
            return;
        }

        // 2. Validar dados se solicitado
        if ($config['validate']) {
            $validation = $this->validateVehicleData($vehicleDataParsed, $article['wordpress_url'] ?? '');
            if (!$validation['valid']) {
                $this->validationErrors[] = $validation;
                $this->skippedCount++;
                return;
            }
        }

        // 3. Estruturar dados para IdealPressure
        $calibrationData = $this->buildCalibrationData($article, $vehicleDataParsed);

        // 4. Salvar ou simular
        if (!$config['dry_run']) {
            $this->saveIdealPressure($calibrationData, $config);
        }

        $this->processedCount++;
        $this->updateStats($vehicleDataParsed);
    }

    /**
     * Parse do JSON vehicle_data com error handling robusto
     */
    protected function parseVehicleData($vehicleData): ?array
    {
        // Se já é array, retornar diretamente
        if (is_array($vehicleData)) {
            return $vehicleData;
        }

        // Se é string, fazer parse do JSON
        if (is_string($vehicleData)) {
            if (empty($vehicleData)) {
                return null;
            }

            try {
                // Limpar possíveis caracteres problemáticos
                $cleanJson = trim($vehicleData);
                $cleanJson = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cleanJson);

                $parsed = json_decode($cleanJson, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('CopyIdealPressureArticlesCommand: JSON inválido', [
                        'json_error' => json_last_error_msg(),
                        'vehicle_data_sample' => substr($vehicleData, 0, 200)
                    ]);
                    return null;
                }

                return $parsed;
            } catch (\Exception $e) {
                Log::error('CopyIdealPressureArticlesCommand: Erro no parse JSON', [
                    'error' => $e->getMessage(),
                    'vehicle_data_sample' => substr($vehicleData, 0, 200)
                ]);
                return null;
            }
        }

        // Tipo não suportado
        return null;
    }

    /**
     * Validar dados extraídos do vehicle_data
     */
    protected function validateVehicleData(array $vehicleData, string $articleUrl): array
    {
        $errors = [];
        $requiredFields = ['make', 'model', 'year', 'main_category'];

        foreach ($requiredFields as $field) {
            if (empty($vehicleData[$field])) {
                $errors[] = "Campo obrigatório '{$field}' ausente ou vazio";
            }
        }

        // Validações específicas
        if (!empty($vehicleData['year']) && ($vehicleData['year'] < 1990 || $vehicleData['year'] > date('Y') + 2)) {
            $errors[] = "Ano inválido: {$vehicleData['year']}";
        }

        if (!empty($vehicleData['main_category']) && !in_array($vehicleData['main_category'], [
            'hatch',
            'sedan',
            'suv',
            'pickup',
            'van',
            'motorcycle',
            'car_electric',
            'truck'
        ])) {
            $errors[] = "Categoria inválida: {$vehicleData['main_category']}";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'article_url' => $articleUrl
        ];
    }

    /**
     * Construir dados estruturados para IdealPressure
     */
    protected function buildCalibrationData($article, array $vehicleData): array
    {
        return [
            // Campos originais do artigo
            'wordpress_url' => $article->wordpress_url ?? null,
            'blog_modified_time' => $this->parseTimestamp($article->blog_modified_time ?? null),
            'blog_published_time' => $this->parseTimestamp($article->blog_published_time ?? null),

            // Campos extraídos para busca eficiente
            'vehicle_make' => $vehicleData['make'] ?? null,
            'vehicle_model' => $vehicleData['model'] ?? null,
            'vehicle_year' => (int) ($vehicleData['year'] ?? 0),
            'main_category' => $vehicleData['main_category'] ?? null,

            // Dados estruturados para VehicleData lookup
            'vehicle_basic_data' => [
                'make' => $vehicleData['make'] ?? null,
                'model' => $vehicleData['model'] ?? null,
                'year' => (int) ($vehicleData['year'] ?? 0),
                'segment' => $vehicleData['vehicle_segment'] ?? null,
                'full_name' => $vehicleData['vehicle_full_name'] ?? null,
                'category_normalized' => $vehicleData['category_normalized'] ?? null,
                'url_slug' => $vehicleData['url_slug'] ?? null,
            ],

            'pressure_specifications' => [
                'tire_size' => $vehicleData['tire_size'] ?? null,
                'empty_front' => $this->parseFloat($vehicleData['pressure_empty_front'] ?? null),
                'empty_rear' => $this->parseFloat($vehicleData['pressure_empty_rear'] ?? null),
                'light_front' => $this->parseFloat($vehicleData['pressure_light_front'] ?? null),
                'light_rear' => $this->parseFloat($vehicleData['pressure_light_rear'] ?? null),
                'max_front' => $this->parseFloat($vehicleData['pressure_max_front'] ?? null),
                'max_rear' => $this->parseFloat($vehicleData['pressure_max_rear'] ?? null),
                'spare' => $this->parseFloat($vehicleData['pressure_spare'] ?? null),
                'pressure_display' => $vehicleData['pressure_display'] ?? null,
                'empty_pressure_display' => $vehicleData['empty_pressure_display'] ?? null,
                'loaded_pressure_display' => $vehicleData['loaded_pressure_display'] ?? null,
            ],

            'vehicle_features' => [
                'has_tpms' => $this->parseBoolean($vehicleData['has_tpms'] ?? null),
                'is_premium' => $this->parseBoolean($vehicleData['is_premium'] ?? null),
                'is_motorcycle' => $this->parseBoolean($vehicleData['is_motorcycle'] ?? false),
                'vehicle_type' => $vehicleData['vehicle_type'] ?? 'car',
                'recommended_oil' => $vehicleData['recommended_oil'] ?? null,
            ],

            // Estado inicial do processamento
            'enrichment_phase' => IdealPressure::PHASE_PENDING,
            'processing_attempts' => 0,
            'data_completeness_score' => $this->calculateCompletenessScore($vehicleData),
        ];
    }

    /**
     * Salvar IdealPressure no banco
     */
    protected function saveIdealPressure(array $data, array $config): void
    {
        if ($config['force'] || !$config['skip_existing']) {
            // Upsert baseado no wordpress_url
            IdealPressure::updateOrCreate(
                ['wordpress_url' => $data['wordpress_url']],
                $data
            );
        } else {
            // Apenas criar se não existir
            $existing = IdealPressure::where('wordpress_url', $data['wordpress_url'])->first();
            if (!$existing) {
                IdealPressure::create($data);
            }
        }
    }

    /**
     * Utilitários de parsing
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

    /**
     * Calcular score de completude dos dados
     */
    protected function calculateCompletenessScore(array $vehicleData): float
    {
        $essentialFields = [
            'make',
            'model',
            'year',
            'main_category',
            'tire_size',
            'pressure_empty_front',
            'pressure_empty_rear',
            'pressure_spare'
        ];

        $filled = 0;
        foreach ($essentialFields as $field) {
            if (!empty($vehicleData[$field])) {
                $filled++;
            }
        }

        return round(($filled / count($essentialFields)) * 10, 1);
    }

    /**
     * Atualizar estatísticas
     */
    protected function updateStats(array $vehicleData): void
    {
        $make = $vehicleData['make'] ?? 'unknown';
        $category = $vehicleData['main_category'] ?? 'unknown';

        $this->stats['by_make'][$make] = ($this->stats['by_make'][$make] ?? 0) + 1;
        $this->stats['by_category'][$category] = ($this->stats['by_category'][$category] ?? 0) + 1;
    }

    /**
     * Exibir estatísticas finais
     */
    protected function displayFinalStats(float $startTime): void
    {
        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info('📊 ESTATÍSTICAS FINAIS:');
        $this->line("   • Processados: {$this->processedCount}");
        $this->line("   • Ignorados: {$this->skippedCount}");
        $this->line("   • Erros: {$this->errorCount}");
        $this->line("   • Duração: {$duration}s");

        if (!empty($this->stats['by_make'])) {
            $this->newLine();
            $this->info('📈 POR MARCA:');
            arsort($this->stats['by_make']);
            foreach (array_slice($this->stats['by_make'], 0, 10) as $make => $count) {
                $this->line("   • {$make}: {$count}");
            }
        }

        if (!empty($this->stats['by_category'])) {
            $this->newLine();
            $this->info('📈 POR CATEGORIA:');
            arsort($this->stats['by_category']);
            foreach ($this->stats['by_category'] as $category => $count) {
                $this->line("   • {$category}: {$count}");
            }
        }

        if (!empty($this->validationErrors)) {
            $this->newLine();
            $this->warn('⚠️ ERROS DE VALIDAÇÃO ENCONTRADOS:');
            foreach (array_slice($this->validationErrors, 0, 5) as $error) {
                $this->line("   • {$error['article_url']}: " . implode(', ', $error['errors']));
            }
            if (count($this->validationErrors) > 5) {
                $remaining = count($this->validationErrors) - 5;
                $this->line("   ... e mais {$remaining} erros");
            }
        }

        $this->newLine();
        $this->info('✅ PROCESSAMENTO CONCLUÍDO!');

        // Log para auditoria
        Log::info('CopyIdealPressureArticlesCommand: Processamento concluído', [
            'processed' => $this->processedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errorCount,
            'duration' => $duration,
            'stats' => $this->stats
        ]);
    }

    /**
     * Mostrar prévia dos artigos encontrados
     */
    protected function displayArticlesPreview($articles): void
    {
        $this->info('📋 PRÉVIA DOS ARTIGOS ENCONTRADOS:');

        // Estatísticas por marca/categoria
        $byMake = [];
        $byCategory = [];
        $validArticles = 0;

        foreach ($articles->take(5) as $index => $article) {
            $vehicleDataParsed = $this->parseVehicleData($article->vehicle_data ?? '');

            if ($vehicleDataParsed) {
                $validArticles++;
                $make = $vehicleDataParsed['make'] ?? 'Unknown';
                $category = $vehicleDataParsed['main_category'] ?? 'Unknown';

                $byMake[$make] = ($byMake[$make] ?? 0) + 1;
                $byCategory[$category] = ($byCategory[$category] ?? 0) + 1;

                $this->line("   {$index}. {$make} {$vehicleDataParsed['model']} {$vehicleDataParsed['year']} ({$category})");
            } else {
                $this->line("   {$index}. [DADOS INVÁLIDOS] - URL: {$article->wordpress_url}");
            }
        }

        if ($articles->count() > 5) {
            $remaining = $articles->count() - 5;
            $this->line("   ... e mais {$remaining} artigos");
        }

        $this->newLine();
        $this->info("✅ Artigos válidos: {$validArticles} de {$articles->count()}");

        if (!empty($byMake)) {
            $this->info('📈 DISTRIBUIÇÃO POR MARCA:');
            arsort($byMake);
            foreach (array_slice($byMake, 0, 5, true) as $make => $count) {
                $this->line("   • {$make}: {$count}");
            }
        }

        if (!empty($byCategory)) {
            $this->info('📈 DISTRIBUIÇÃO POR CATEGORIA:');
            arsort($byCategory);
            foreach ($byCategory as $category => $count) {
                $this->line("   • {$category}: {$count}");
            }
        }

        $this->newLine();
    }
}

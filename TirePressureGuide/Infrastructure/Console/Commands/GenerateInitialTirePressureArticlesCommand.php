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
 * Command para geração inicial de artigos de calibragem (Etapa 1)
 * 
 * Processa CSV de veículos e gera artigos completos na TirePressureArticle
 * Similar ao sistema atual, mas persistindo direto na model MongoDB
 */
class GenerateInitialTirePressureArticlesCommand extends Command
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
                           {--batch-id= : ID personalizado para o lote}';

    protected $description = 'Gera artigos iniciais de calibragem de pneus a partir de CSV (Etapa 1)';

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

            // 1. Obter configuração
            $config = $this->getConfiguration();

            // 2. Validar ambiente
            $this->validateEnvironment($config);

            // 3. Exibir configuração
            $this->displayConfiguration($config);

            if (!$this->confirm('Deseja continuar com a geração?')) {
                $this->info("❌ Operação cancelada pelo usuário.");
                return 0;
            }

            // 4. Carregar e processar veículos
            $vehicles = $this->loadAndFilterVehicles($config);

            if ($vehicles->isEmpty()) {
                $this->warn("⚠️ Nenhum veículo encontrado com os filtros aplicados.");
                return 1;
            }

            // 5. Preparar ambiente
            // $this->prepareEnvironment();

            // 6. Processar em lotes
            $results = $this->processVehiclesInBatches($vehicles, $config);

            // 7. Mostrar relatório final
            $this->displayFinalReport($results);

            $this->info("✅ Geração de artigos concluída com sucesso!");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro durante geração: " . $e->getMessage());
            Log::error("GenerateInitialTirePressureArticlesCommand falhou: " . $e->getMessage(), [
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
            'overwrite' => $this->option('overwrite'),
            'dry_run' => $this->option('dry-run'),
            'show_progress' => $this->option('show-progress'),
            'batch_id' => $this->option('batch-id') ?? 'batch_' . date('Ymd_His')
        ];
    }

    /**
     * Validar ambiente antes da execução
     */
    protected function validateEnvironment(array $config): void
    {
        // Verificar se CSV existe
        if (!file_exists($config['csv_path'])) {
            throw new \Exception("Arquivo CSV não encontrado: {$config['csv_path']}");
        }

        // Verificar conexão MongoDB
        try {
            TirePressureArticle::raw(function($collection) {
                return $collection->findOne();
            });
        } catch (\Exception $e) {
            throw new \Exception("Erro de conexão MongoDB: " . $e->getMessage());
        }

        // Verificar memória disponível
        $memoryLimit = ini_get('memory_limit');
        $this->line("💾 Limite de memória: {$memoryLimit}");

        if ($this->convertToBytes($memoryLimit) < 512 * 1024 * 1024) { // 512MB
            $this->warn("⚠️ Limite de memória baixo. Recomendado: 512MB ou mais");
        }
    }

    /**
     * Exibir configuração do processamento
     */
    protected function displayConfiguration(array $config): void
    {
        $this->info("📋 CONFIGURAÇÃO:");
        $this->line("   📂 CSV: {$config['csv_path']}");
        $this->line("   📦 Lote: {$config['batch_size']} artigos");
        $this->line("   🆔 Batch ID: {$config['batch_id']}");

        if (!empty(array_filter($config['filters']))) {
            $this->line("   🔍 Filtros ativos:");
            foreach ($config['filters'] as $key => $value) {
                if ($value !== null && $key !== 'require_tire_pressure') {
                    $this->line("      {$key}: {$value}");
                }
            }
        }

        $options = [];
        if ($config['overwrite']) $options[] = 'Sobrescrever';
        if ($config['dry_run']) $options[] = 'Simulação';

        if (!empty($options)) {
            $this->line("   ⚙️ Opções: " . implode(', ', $options));
        }
    }

    /**
     * Carregar e filtrar veículos do CSV
     */
    protected function loadAndFilterVehicles(array $config): Collection
    {
        $this->info("📥 Carregando veículos do CSV...");

        $vehicles = $this->vehicleProcessor->loadFromCsv($config['csv_path']);
        $this->line("   Total carregados: " . $vehicles->count());

        // Aplicar filtros
        if (!empty(array_filter($config['filters']))) {
            $this->info("🔍 Aplicando filtros...");
            $vehicles = $this->vehicleProcessor->applyFilters($vehicles, $config['filters']);
            $this->line("   Após filtros: " . $vehicles->count());
        }

        // Remover duplicatas
        $vehicles = $this->vehicleProcessor->removeDuplicates($vehicles);
        $this->line("   Únicos: " . $vehicles->count());

        // Validar dados essenciais
        $validVehicles = $this->vehicleProcessor->validateVehicleData($vehicles);
        $this->line("   Válidos: " . $validVehicles->count());

        if ($vehicles->count() !== $validVehicles->count()) {
            $this->warn("⚠️ " . ($vehicles->count() - $validVehicles->count()) . " veículos removidos por dados inválidos");
        }

        // Verificar artigos existentes (se não for sobrescrever)
        if (!$config['overwrite']) {
            $newVehicles = $this->filterExistingArticles($validVehicles);
            $this->line("   Novos (não existentes): " . $newVehicles->count());
            return $newVehicles;
        }

        return $validVehicles;
    }

    /**
     * Filtrar veículos que já possuem artigos
     */
    protected function filterExistingArticles(Collection $vehicles): Collection
    {
        $this->info("🔍 Verificando artigos existentes...");

        $existingSlugs = TirePressureArticle::pluck('wordpress_slug')->toArray();
        $existingSlugsSet = array_flip($existingSlugs);

        return $vehicles->filter(function ($vehicle) use ($existingSlugsSet) {
            $slug = $this->generateSlugForVehicle($vehicle);
            return !isset($existingSlugsSet[$slug]);
        });
    }

    /**
     * Gerar slug para veículo (sem persistir)
     */
    protected function generateSlugForVehicle(array $vehicle): string
    {
        $make = $this->slugify($vehicle['make']);
        $model = $this->slugify($vehicle['model']);
        $year = $vehicle['year'];
        
        return "calibragem-pneu-{$make}-{$model}-{$year}";
    }

    /**
     * Converter string para slug
     */
    protected function slugify(string $text): string
    {
        // Remover acentos
        $text = $this->removeAccents($text);
        
        // Converter para minúsculas
        $text = strtolower($text);
        
        // Remover caracteres especiais e substituir por hífen
        $text = preg_replace('/[^a-z0-9\-_]/', '-', $text);
        
        // Remover hífens múltiplos
        $text = preg_replace('/-+/', '-', $text);
        
        // Remover hífens do início e fim
        return trim($text, '-');
    }

    /**
     * Remover acentos
     */
    protected function removeAccents(string $text): string
    {
        $unwanted = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y', 'ñ' => 'n', 'ç' => 'c',
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Ñ' => 'N', 'Ç' => 'C',
        ];

        return strtr($text, $unwanted);
    }

    /**
     * Preparar ambiente para processamento
     */
    protected function prepareEnvironment(): void
    {
        $this->info("🛠️ Preparando ambiente...");

        // Aumentar limite de memória se necessário
        ini_set('memory_limit', '1G');
        
        // Aumentar tempo limite de execução
        set_time_limit(0);
        
        // Criar índices MongoDB se não existirem
        try {
            TirePressureArticle::createIndexes();
            $this->line("   ✅ Índices MongoDB verificados");
        } catch (\Exception $e) {
            $this->warn("   ⚠️ Erro ao criar índices: " . $e->getMessage());
        }
    }

    /**
     * Processar veículos em lotes
     */
    protected function processVehiclesInBatches(Collection $vehicles, array $config): array
    {
        $totalVehicles = $vehicles->count();
        $batchSize = $config['batch_size'];
        $totalBatches = ceil($totalVehicles / $batchSize);

        $this->info("🔄 Processando {$totalVehicles} veículos em {$totalBatches} lote(s)...");

        $results = [
            'total_vehicles' => $totalVehicles,
            'total_batches' => $totalBatches,
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $batches = $vehicles->chunk($batchSize);

        foreach ($batches as $batchIndex => $batch) {
            $batchNumber = $batchIndex + 1;
            $this->info("📦 Processando lote {$batchNumber}/{$totalBatches} ({$batch->count()} veículos)");

            $batchResults = $this->processBatch($batch, $config, $batchNumber);

            // Consolidar resultados
            $results['processed'] += $batchResults['processed'];
            $results['successful'] += $batchResults['successful'];
            $results['failed'] += $batchResults['failed'];
            $results['errors'] = array_merge($results['errors'], $batchResults['errors']);

            // Mostrar progresso do lote
            $this->line("   ✅ Sucesso: {$batchResults['successful']}");
            if ($batchResults['failed'] > 0) {
                $this->line("   ❌ Falhas: {$batchResults['failed']}");
            }

            // Pausa entre lotes para não sobrecarregar
            if ($batchNumber < $totalBatches) {
                sleep(1);
            }
        }

        return $results;
    }

    /**
     * Processar um lote específico
     */
    protected function processBatch(Collection $batch, array $config, int $batchNumber): array
    {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $progressBar = null;
        if ($config['show_progress']) {
            $progressBar = $this->output->createProgressBar($batch->count());
            $progressBar->start();
        }

        foreach ($batch as $vehicle) {
            $results['processed']++;

            try {
                // Usar o Use Case para gerar o artigo
                $success = $this->generateUseCase->execute(
                    $vehicle,
                    $config['batch_id'] . '_' . $batchNumber,
                    $config['dry_run']
                );

                if ($success) {
                    $results['successful']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Falha ao gerar artigo para {$vehicle['make']} {$vehicle['model']} {$vehicle['year']}";
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $vehicleId = "{$vehicle['make']} {$vehicle['model']} {$vehicle['year']}";
                $error = "Erro ao processar {$vehicleId}: " . $e->getMessage();
                $results['errors'][] = $error;

                Log::error("Erro no processamento de veículo", [
                    'vehicle' => $vehicleId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            if ($progressBar) {
                $progressBar->advance();
            }
        }

        if ($progressBar) {
            $progressBar->finish();
            $this->newLine();
        }

        return $results;
    }

    /**
     * Exibir relatório final
     */
    protected function displayFinalReport(array $results): void
    {
        $this->newLine();
        $this->info("📊 RELATÓRIO FINAL:");
        $this->line("===================");
        
        $this->table([
            'Métrica', 'Valor'
        ], [
            ['Veículos processados', $results['processed']],
            ['Artigos gerados com sucesso', $results['successful']],
            ['Falhas', $results['failed']],
            ['Taxa de sucesso', $this->calculateSuccessRate($results) . '%'],
            ['Total de lotes', $results['total_batches']]
        ]);

        // Mostrar erros se houver
        if (!empty($results['errors'])) {
            $this->error("❌ ERROS ENCONTRADOS:");
            foreach (array_slice($results['errors'], 0, 10) as $error) {
                $this->line("   • {$error}");
            }
            
            if (count($results['errors']) > 10) {
                $remaining = count($results['errors']) - 10;
                $this->line("   ... e mais {$remaining} erro(s). Verifique os logs para detalhes.");
            }
        }

        // Estatísticas da base
        $this->newLine();
        $this->info("📈 ESTATÍSTICAS DA BASE:");
        $stats = TirePressureArticle::getGenerationStatistics();
        
        $this->table([
            'Status', 'Quantidade'
        ], [
            ['Total de artigos', $stats['total']],
            ['Pendentes', $stats['pending']],
            ['Gerados (Etapa 1)', $stats['generated']],
            ['Refinados Claude (Etapa 2)', $stats['claude_enhanced']],
            ['Publicados', $stats['published']],
            ['Prontos para Claude', $stats['ready_for_claude']]
        ]);
    }

    /**
     * Calcular taxa de sucesso
     */
    protected function calculateSuccessRate(array $results): float
    {
        if ($results['processed'] === 0) {
            return 0;
        }
        
        return round(($results['successful'] / $results['processed']) * 100, 2);
    }

    /**
     * Converter string de memória para bytes
     */
    protected function convertToBytes(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $number = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $number *= 1024;
            case 'm':
                $number *= 1024;
            case 'k':
                $number *= 1024;
        }

        return $number;
    }
}
<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Src\ContentGeneration\TireCalibration\Application\Services\ArticleMappingService;
use Carbon\Carbon;

/**
 * GenerateArticlesPhase2Command - NOVA FASE 2: Mapear arquivos JSON físicos para artigos estruturados
 * 
 * Command refatorado para trabalhar com nova arquitetura:
 * - ENTRADA: TireCalibration com version="v2" + arquivos JSON em database/vehicle-data/
 * - PROCESSAMENTO: Mapear dados físicos para estrutura igual aos mocks/articles/
 * - SAÍDA: JSON estruturado salvo no campo generated_article
 * 
 * DIFERENÇA DA VERSÃO ANTERIOR:
 * - ANTES: Trabalhava com dados do MongoDB (enrichment_phase)
 * - AGORA: Trabalha com arquivos JSON físicos como fonte de verdade
 * 
 * USO:
 * php artisan tire-calibration:generate-articles-phase2
 * php artisan tire-calibration:generate-articles-phase2 --limit=10 --dry-run
 * php artisan tire-calibration:generate-articles-phase2 --make=Honda --force
 * 
 * @author Claude Sonnet 4
 * @version 2.0 - Refatorado para arquivos JSON físicos
 */
class GenerateArticlesPhase2Command extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tire-calibration:generate-articles-phase2
                            {--limit=50 : Número máximo de artigos a processar}
                            {--make= : Filtrar por marca específica (Honda, Toyota, etc)}
                            {--category= : Filtrar por categoria (sedan, suv, motorcycle, etc)}
                            {--dry-run : Simular execução sem salvar no MongoDB}
                            {--force : Reprocessar artigos que já têm generated_article}
                            {--validate-files : Validar existência dos arquivos JSON antes de processar}';

    /**
     * The console command description.
     */
    protected $description = 'FASE 2: Mapear arquivos JSON físicos (database/vehicle-data/) para artigos estruturados';

    private ArticleMappingService $mappingService;
    
    // Estatísticas de processamento
    private int $processedCount = 0;
    private int $successCount = 0;
    private int $errorCount = 0;
    private int $skippedCount = 0;
    private array $errorDetails = [];
    
    public function __construct(ArticleMappingService $mappingService)
    {
        parent::__construct();
        $this->mappingService = $mappingService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);
        
        $this->info('🚀 GERANDO ARTIGOS - FASE 2 (Arquivos JSON → Artigos Estruturados)');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            // 1. Validar configurações
            $config = $this->validateAndGetConfig();
            $this->displayConfig($config);

            // 2. Validar arquivos se solicitado
            if ($config['validate_files']) {
                $this->validateVehicleDataFiles();
            }

            // 3. Buscar TireCalibration candidates (version="v2")
            $candidates = $this->getCandidateCalibrations($config);
            
            if ($candidates->isEmpty()) {
                $this->warn('❌ Nenhuma TireCalibration version="v2" encontrada com os critérios especificados.');
                return self::SUCCESS;
            }

            $this->info("📊 Encontradas {$candidates->count()} TireCalibration(s) version=\"v2\" para processamento");
            $this->newLine();

            // 4. Processar candidates
            $results = $this->processCandidates($candidates, $config);

            // 5. Exibir estatísticas finais
            $this->displayFinalStats($startTime, $results);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ ERRO FATAL: ' . $e->getMessage());
            Log::error('GenerateArticlesPhase2Command: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Validar e obter configuração do comando
     */
    private function validateAndGetConfig(): array
    {
        return [
            'limit' => (int) $this->option('limit'),
            'make' => $this->option('make'),
            'category' => $this->option('category'),
            'dry_run' => $this->option('dry-run'),
            'force' => $this->option('force'),
            'validate_files' => $this->option('validate-files'),
        ];
    }

    /**
     * Exibir configuração do processamento
     */
    private function displayConfig(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO:');
        $this->line("   • 📊 Limite: {$config['limit']} registros");
        $this->line("   • 🚗 Marca: " . ($config['make'] ?? 'Todas'));
        $this->line("   • 🏷️ Categoria: " . ($config['category'] ?? 'Todas'));
        $this->line("   • 🧪 Modo: " . ($config['dry_run'] ? '🔍 DRY-RUN (simulação)' : '💾 PRODUÇÃO'));
        $this->line("   • 🔄 Reprocessar: " . ($config['force'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • 📁 Validar arquivos: " . ($config['validate_files'] ? '✅ SIM' : '❌ NÃO'));
        $this->newLine();
    }

    /**
     * Validar existência dos arquivos JSON em database/vehicle-data/
     */
    private function validateVehicleDataFiles(): void
    {
        $this->info('📁 VALIDANDO ARQUIVOS JSON...');
        
        $basePath = database_path('vehicle-data');
        if (!is_dir($basePath)) {
            throw new \Exception("Diretório database/vehicle-data/ não encontrado");
        }

        $files = glob($basePath . '/*.json');
        $this->line("   ✅ Encontrados " . count($files) . " arquivos JSON");
        
        // Validar alguns arquivos aleatórios
        $sampleFiles = array_slice($files, 0, 3);
        foreach ($sampleFiles as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Arquivo JSON inválido: " . basename($file));
            }
        }
        
        $this->line("   ✅ Validação dos arquivos: OK");
        $this->newLine();
    }

    /**
     * Buscar TireCalibrations candidatas (version="v2" em PHASE_PENDING)
     */
    private function getCandidateCalibrations(array $config)
    {
        $this->info('🔍 BUSCANDO CANDIDATOS (version="v2" em PHASE_PENDING)...');
        
        $query = TireCalibration::where('version', 'v2')
            ->where('enrichment_phase', TireCalibration::PHASE_PENDING)
            ->whereNotNull('vehicle_make')
            ->whereNotNull('vehicle_model');

        // Filtrar por marca específica
        if ($config['make']) {
            $query->where('vehicle_make', 'LIKE', '%' . $config['make'] . '%');
        }

        // Filtrar por categoria específica
        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        // Se não forçar, excluir já processados (que já tem generated_article)
        if (!$config['force']) {
            $query->whereNull('generated_article');
        }

        $candidates = $query->limit($config['limit'])->get();
        
        $this->line("   📊 Query executada: {$candidates->count()} registros encontrados");
        
        return $candidates;
    }

    /**
     * Processar candidates mapeando arquivos JSON para artigos
     */
    private function processCandidates($candidates, array $config): array
    {
        $this->info('⚡ PROCESSANDO MAPEAMENTO JSON → ARTIGO...');
        
        $progressBar = $this->output->createProgressBar($candidates->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Iniciando...');
        $progressBar->start();

        foreach ($candidates as $calibration) {
            $this->processedCount++;
            
            try {
                $vehicleInfo = "{$calibration->vehicle_make} {$calibration->vehicle_model}";
                $progressBar->setMessage("Processando: {$vehicleInfo}");

                // 1. Construir nome do arquivo baseado nos dados do TireCalibration
                $filename = $this->buildVehicleDataFilename($calibration);
                
                // 2. Carregar dados do arquivo JSON físico
                $vehicleJsonData = $this->loadVehicleDataFromFile($filename);
                
                if (!$vehicleJsonData) {
                    $this->skippedCount++;
                    $this->errorDetails[] = "Arquivo não encontrado: {$filename}";
                    $progressBar->advance();
                    continue;
                }

                // 3. Mapear dados para estrutura de artigo (igual aos mocks)
                $articleStructure = $this->mappingService->mapVehicleDataToArticle(
                    $vehicleJsonData, 
                    $calibration
                );

                // 4. Salvar no MongoDB se não for dry-run
                if (!$config['dry_run']) {
                    // Atualizar pelo ID do TireCalibration
                    $calibration->update([
                        'generated_article' => $articleStructure,
                        'enrichment_phase' => TireCalibration::PHASE_ARTICLE_GENERATED,
                        'article_generated_at' => now(),
                        'processing_attempts' => ($calibration->processing_attempts ?? 0) + 1,
                    ]);
                    
                    Log::info('GenerateArticlesPhase2Command: TireCalibration atualizada', [
                        'id' => $calibration->_id,
                        'vehicle' => $vehicleInfo,
                        'article_size' => strlen(json_encode($articleStructure))
                    ]);
                }

                $this->successCount++;
                $progressBar->advance();

            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errorDetails[] = "Erro em {$vehicleInfo}: {$e->getMessage()}";
                
                Log::error('GenerateArticlesPhase2Command: Erro no processamento', [
                    'calibration_id' => $calibration->_id,
                    'vehicle' => $vehicleInfo,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        return [
            'processed' => $this->processedCount,
            'success' => $this->successCount,
            'errors' => $this->errorCount,
            'skipped' => $this->skippedCount,
            'error_details' => $this->errorDetails
        ];
    }

    /**
     * Construir nome do arquivo JSON baseado nos dados do TireCalibration
     */
    private function buildVehicleDataFilename(TireCalibration $calibration): string
    {
        $make = strtolower($calibration->vehicle_make);
        $model = strtolower($calibration->vehicle_model);
        
        // Limpar caracteres especiais
        $make = preg_replace('/[^a-z0-9]/', '-', $make);
        $model = preg_replace('/[^a-z0-9]/', '-', $model);
        
        // Para version="v2" não usamos ano (conforme especificação)
        return "{$make}-{$model}.json";
    }

    /**
     * Carregar dados do arquivo JSON físico
     */
    private function loadVehicleDataFromFile(string $filename): ?array
    {
        $filepath = database_path("vehicle-data/{$filename}");
        
        if (!file_exists($filepath)) {
            // Tentar variações do nome do arquivo
            $variations = $this->generateFilenameVariations($filename);
            
            foreach ($variations as $variation) {
                $variationPath = database_path("vehicle-data/{$variation}");
                if (file_exists($variationPath)) {
                    $filepath = $variationPath;
                    break;
                }
            }
            
            if (!file_exists($filepath)) {
                return null;
            }
        }

        try {
            $content = file_get_contents($filepath);
            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("JSON inválido: " . json_last_error_msg());
            }
            
            return $data;
            
        } catch (\Exception $e) {
            Log::error('GenerateArticlesPhase2Command: Erro ao carregar arquivo JSON', [
                'filename' => $filename,
                'filepath' => $filepath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Gerar variações do nome do arquivo para tentar encontrar correspondências
     */
    private function generateFilenameVariations(string $filename): array
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        
        return [
            // Variações com hífen, underscore, espaço
            str_replace('-', '_', $base) . '.json',
            str_replace('-', ' ', $base) . '.json',
            str_replace('_', '-', $base) . '.json',
            
            // Variações sem caracteres especiais
            preg_replace('/[^a-z0-9]/', '', $base) . '.json',
            
            // Variações com prefixos/sufixos comuns
            'vehicle-' . $base . '.json',
            $base . '-data.json',
        ];
    }

    /**
     * Exibir estatísticas finais
     */
    private function displayFinalStats(float $startTime, array $results): void
    {
        $executionTime = round(microtime(true) - $startTime, 2);
        
        $this->newLine();
        $this->info('=== ESTATÍSTICAS FINAIS - FASE 2 ===');
        $this->line("✅ Processados: {$results['processed']}");
        $this->line("🎯 Sucessos: {$results['success']}");  
        $this->line("⏭️ Ignorados: {$results['skipped']}");
        $this->line("❌ Erros: {$results['errors']}");
        $this->line("⏱️ Tempo: {$executionTime}s");
        
        if ($results['success'] > 0) {
            $avgTime = round($executionTime / $results['success'], 2);
            $this->line("📊 Média: {$avgTime}s por artigo");
        }
        
        // Mostrar alguns erros se houver
        if (!empty($results['error_details']) && count($results['error_details']) <= 5) {
            $this->newLine();
            $this->warn('❌ DETALHES DOS ERROS:');
            foreach (array_slice($results['error_details'], 0, 5) as $error) {
                $this->line("   • {$error}");
            }
        }

        $this->newLine();
        $this->info('🚀 PRÓXIMOS PASSOS:');
        $this->line('   1. Verifique artigos gerados no campo generated_article');
        $this->line('   2. Execute: php artisan tire-calibration:stats para ver progresso');
        $this->line('   3. Execute Fase 3 (Claude refinement) quando pronto');
        
        Log::info('GenerateArticlesPhase2Command: Execução concluída', [
            'results' => $results,
            'execution_time' => $executionTime
        ]);
    }
}
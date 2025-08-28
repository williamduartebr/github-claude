<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Src\ContentGeneration\TireCalibration\Application\Services\ArticleMappingService;

/**
 * GenerateArticlesPhase2Command - CORREÇÃO FOCADA
 * 
 * PROBLEMA IDENTIFICADO:
 * - Dados já existem no TireCalibration (vehicle_basic_data, pressure_specifications, etc.)
 * - Não precisa buscar arquivos externos
 * - Só precisa mapear os dados existentes para estrutura de artigo
 * 
 * @author Claude Sonnet 4
 * @version 3.0 - Correção focada nos dados existentes
 */
class GenerateArticlesPhase2Command extends Command
{
    protected $signature = 'tire-calibration:generate-articles-phase2
                            {--limit=50 : Número máximo de artigos a processar}
                            {--make= : Filtrar por marca específica}
                            {--category= : Filtrar por categoria}
                            {--dry-run : Simular execução sem salvar}
                            {--force : Reprocessar artigos existentes}
                            {--debug : Mostrar dados de debug}';

    protected $description = 'FASE 2: Mapear dados existentes do TireCalibration para artigos estruturados';

    private ArticleMappingService $mappingService;
    
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

    public function handle(): int
    {
        $startTime = microtime(true);
        
        $this->info('🚀 GERANDO ARTIGOS - FASE 2 (Dados Existentes)');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            $config = $this->getConfig();
            $this->displayConfig($config);

            // Buscar registros que têm dados suficientes
            $candidates = $this->getCandidates($config);
            
            if ($candidates->isEmpty()) {
                $this->warn('❌ Nenhum registro encontrado para processamento');
                return self::SUCCESS;
            }

            $this->info("📊 Encontrados {$candidates->count()} registro(s) para processamento");
            
            if ($config['debug']) {
                $this->showSampleData($candidates->first());
            }

            $this->newLine();

            // Processar registros
            $results = $this->processRecords($candidates, $config);

            // Mostrar resultados
            $this->showResults($results, microtime(true) - $startTime);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ ERRO: ' . $e->getMessage());
            Log::error('GenerateArticlesPhase2Command: Erro', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }
    }

    private function getConfig(): array
    {
        return [
            'limit' => (int) $this->option('limit'),
            'make' => $this->option('make'),
            'category' => $this->option('category'),
            'dry_run' => $this->option('dry-run'),
            'force' => $this->option('force'),
            'debug' => $this->option('debug'),
        ];
    }

    private function displayConfig(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO:');
        $this->line("   • Limite: {$config['limit']}");
        $this->line("   • Marca: " . ($config['make'] ?? 'Todas'));
        $this->line("   • Categoria: " . ($config['category'] ?? 'Todas'));
        $this->line("   • Modo: " . ($config['dry_run'] ? 'DRY-RUN' : 'PRODUÇÃO'));
        $this->line("   • Reprocessar: " . ($config['force'] ? 'SIM' : 'NÃO'));
        $this->newLine();
    }

    private function getCandidates(array $config)
    {
        $query = TireCalibration::whereNotNull('vehicle_make')
            ->whereNotNull('vehicle_model')
            ->where('version', 'v2')
            ->where('enrichment_phase', TireCalibration::PHASE_PENDING);

        // Filtros opcionais
        if ($config['make']) {
            $query->where('vehicle_make', 'LIKE', '%' . $config['make'] . '%');
        }

        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        // Se não forçar, pular os que já têm artigo
        if (!$config['force']) {
            $query->whereNull('generated_article');
        }

        return $query->limit($config['limit'])->get();
    }

    private function showSampleData($record): void
    {
        $this->info('🔍 DADOS DE EXEMPLO:');
        $this->line("   ID: {$record->_id}");
        $this->line("   Veículo: {$record->vehicle_make} {$record->vehicle_model} " . ($record->vehicle_year ?? ''));
        $this->line("   Categoria: {$record->main_category}");
        $this->line("   Fase: {$record->enrichment_phase}");
        
        if ($record->pressure_specifications) {
            $pressures = $record->pressure_specifications;
            $this->line("   Estrutura pressure_specifications:");
            $this->line("      • " . json_encode($pressures, JSON_UNESCAPED_UNICODE));
        }
        
        if ($record->vehicle_basic_data) {
            $basic = $record->vehicle_basic_data;
            $this->line("   vehicle_basic_data: " . count($basic) . ' campos');
        }
        
        $this->newLine();
    }

    private function processRecords($candidates, array $config): array
    {
        $progressBar = $this->output->createProgressBar($candidates->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->start();

        foreach ($candidates as $record) {
            $this->processedCount++;
            
            $vehicleInfo = "{$record->vehicle_make} {$record->vehicle_model}";
            $progressBar->setMessage($vehicleInfo);

            try {
                // 1. Extrair dados do próprio registro TireCalibration
                $vehicleData = $this->extractVehicleData($record);
                
                if (!$vehicleData) {
                    $this->skippedCount++;
                    $this->errorDetails[] = "Dados insuficientes: {$vehicleInfo}";
                    $progressBar->advance();
                    continue;
                }

                // 2. Usar ArticleMappingService para gerar estrutura
                $articleData = $this->mappingService->mapVehicleDataToArticle($vehicleData, $record);

                // 3. Salvar resultado se não for dry-run
                if (!$config['dry_run']) {
                    $record->update([
                        'generated_article' => $articleData,
                        'enrichment_phase' => TireCalibration::PHASE_ARTICLE_GENERATED,
                        'article_generated_at' => now(),
                        'processing_attempts' => ($record->processing_attempts ?? 0) + 1,
                        'content_quality_score' => $this->calculateQuality($articleData),
                    ]);
                }

                $this->successCount++;
                
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errorDetails[] = "{$vehicleInfo}: {$e->getMessage()}";
                
                Log::error('ProcessRecord Error', [
                    'id' => $record->_id,
                    'vehicle' => $vehicleInfo,
                    'error' => $e->getMessage()
                ]);
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        return [
            'processed' => $this->processedCount,
            'success' => $this->successCount,
            'errors' => $this->errorCount,
            'skipped' => $this->skippedCount,
            'details' => $this->errorDetails
        ];
    }

    /**
     * Extrair dados do veículo do próprio registro TireCalibration
     */
    private function extractVehicleData(TireCalibration $record): ?array
    {
        // Dados básicos obrigatórios
        if (empty($record->vehicle_make) || empty($record->vehicle_model)) {
            return null;
        }

        // Montar estrutura baseada nos dados existentes no registro
        $vehicleData = [
            'make' => $record->vehicle_make,
            'model' => $record->vehicle_model,
            'main_category' => $record->main_category ?? 'sedan',
            'data_quality_score' => $record->data_completeness_score ?? 8,
        ];

        // Adicionar ano se disponível (version V1)
        if (!empty($record->vehicle_year)) {
            $vehicleData['year'] = $record->vehicle_year;
        }

        // Extrair dados do vehicle_basic_data se existir
        if ($record->vehicle_basic_data) {
            $basic = $record->vehicle_basic_data;
            
            // Mapear tire_size de várias fontes possíveis
            $tireSize = $basic['tire_size'] ?? null;
            if (empty($tireSize) && $record->pressure_specifications) {
                $tireSize = $record->pressure_specifications['tire_size'] ?? null;
            }
            
            $vehicleData = array_merge($vehicleData, [
                'tire_size' => $tireSize ?? '215/55 R16',
                'full_name' => $basic['full_name'] ?? "{$record->vehicle_make} {$record->vehicle_model}",
                'category_normalized' => $basic['category_normalized'] ?? 'Sedan',
                'segment' => $basic['segment'] ?? 'C',
            ]);
        } else {
            // Se não tem vehicle_basic_data, tentar pegar tire_size de pressure_specifications
            $tireSize = ($record->pressure_specifications['tire_size'] ?? null) ?? '215/55 R16';
            $vehicleData['tire_size'] = $tireSize;
        }

        // Extrair especificações de pressão se existir
        if ($record->pressure_specifications) {
            $pressure = $record->pressure_specifications;
            
            // Tratar as duas estruturas diferentes (TireCalibration vs VehicleData)
            $frontPressure = $pressure['empty_front'] ?? $pressure['pressure_empty_front'] ?? $pressure['light_front'] ?? $pressure['pressure_light_front'] ?? 32;
            $rearPressure = $pressure['empty_rear'] ?? $pressure['pressure_empty_rear'] ?? $pressure['light_rear'] ?? $pressure['pressure_light_rear'] ?? 30;
            
            $vehicleData = array_merge($vehicleData, [
                'pressure_empty_front' => $frontPressure,
                'pressure_empty_rear' => $rearPressure,
                'pressure_max_front' => $pressure['max_front'] ?? $pressure['pressure_max_front'] ?? ($frontPressure + 3),
                'pressure_max_rear' => $pressure['max_rear'] ?? $pressure['pressure_max_rear'] ?? ($rearPressure + 3),
                'pressure_spare' => $pressure['spare'] ?? $pressure['pressure_spare'] ?? 60,
                'tire_size' => $pressure['tire_size'] ?? $vehicleData['tire_size'] ?? '215/55 R16',
            ]);
        } else {
            // Valores padrão se não tiver dados de pressão
            $vehicleData = array_merge($vehicleData, [
                'pressure_empty_front' => 32,
                'pressure_empty_rear' => 30,
                'pressure_max_front' => 35,
                'pressure_max_rear' => 33,
                'pressure_spare' => 60,
                'tire_size' => '215/55 R16',
            ]);
        }

        // Extrair características do veículo se existir
        if ($record->vehicle_features) {
            $features = $record->vehicle_features;
            $vehicleData = array_merge($vehicleData, [
                'has_tpms' => $features['has_tpms'] ?? false,
                'is_premium' => $features['is_premium'] ?? false,
                'is_motorcycle' => $features['is_motorcycle'] ?? false,
                'vehicle_type' => $features['vehicle_type'] ?? 'car',
                'recommended_oil' => $features['recommended_oil'] ?? '5W30 Sintético',
            ]);
        } else {
            // Valores padrão
            $vehicleData = array_merge($vehicleData, [
                'has_tpms' => false,
                'is_premium' => false,
                'is_motorcycle' => str_contains($record->main_category ?? '', 'motorcycle'),
                'vehicle_type' => str_contains($record->main_category ?? '', 'motorcycle') ? 'motorcycle' : 'car',
                'recommended_oil' => str_contains($record->main_category ?? '', 'motorcycle') ? '10W40 Sintético' : '5W30 Sintético',
            ]);
        }

        return $vehicleData;
    }

    private function calculateQuality(array $article): float
    {
        $score = 0;

        if (!empty($article['title'])) $score += 2;
        if (!empty($article['seo_data']['meta_description'])) $score += 2;
        if (!empty($article['content'])) $score += 3;
        if (!empty($article['content']['especificacoes_por_versao'])) $score += 2;
        if (!empty($article['seo_data']['primary_keyword'])) $score += 1;

        return round($score, 1);
    }

    private function showResults(array $results, float $executionTime): void
    {
        $this->info('=== RESULTADOS ===');
        $this->line("✅ Processados: {$results['processed']}");
        $this->line("🎯 Sucessos: {$results['success']}");  
        $this->line("⏭️ Ignorados: {$results['skipped']}");
        $this->line("❌ Erros: {$results['errors']}");
        $this->line("⏱️ Tempo: " . round($executionTime, 2) . "s");

        if ($results['success'] > 0) {
            $this->newLine();
            $this->info('✅ ARTIGOS GERADOS!');
            $this->line('   • Salvos no campo: generated_article');
            $this->line('   • Fase atualizada para: article_generated');
            $this->line('   • Prontos para Fase 3 (Claude)');
        }

        // Mostrar alguns erros se houver
        if (!empty($results['details']) && $results['errors'] > 0) {
            $this->newLine();
            $this->warn('⚠️ ALGUNS ERROS:');
            foreach (array_slice($results['details'], 0, 3) as $detail) {
                $this->line("   • {$detail}");
            }
        }

        $this->newLine();
        $this->info('🚀 PRÓXIMOS PASSOS:');
        $this->line('   php artisan tire-calibration:stats');
        $this->line('   php artisan tire-calibration:refine-with-claude --limit=5');
    }
}
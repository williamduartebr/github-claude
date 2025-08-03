<?php

namespace Src\VehicleData\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * Command para extrair dados de veículos dos artigos de pressão de pneus
 * 
 * Processa todos os artigos TirePressureArticle e extrai informações
 * técnicas dos veículos para armazenar na collection vehicle_data
 */
class ExtractVehicleDataCommand extends Command
{
    protected $signature = 'vehicle-data:extract
                           {--batch-size=100 : Número de artigos por lote}
                           {--make= : Filtrar por marca específica}
                           {--category= : Filtrar por categoria específica}
                           {--dry-run : Executar sem salvar dados}
                           {--force : Força atualização de dados existentes}
                           {--validate : Validar dados após extração}';

    protected $description = 'Extrair dados de veículos dos artigos de pressão de pneus';

    protected int $processedCount = 0;
    protected int $createdCount = 0;
    protected int $updatedCount = 0;
    protected int $errorCount = 0;
    protected array $stats = [];

    /**
     * Executar o command
     */
    public function handle(): ?int
    {
        $this->info('🚀 Iniciando extração de dados de veículos...');
        
        $batchSize = (int) $this->option('batch-size');
        $make = $this->option('make');
        $category = $this->option('category');
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');
        $validate = $this->option('validate');

        if ($isDryRun) {
            $this->warn('⚠️  MODO DRY-RUN ATIVO - Nenhum dado será salvo');
        }

        try {
            // Obter contadores iniciais
            $this->displayInitialStats();

            // Processar artigos em lotes
            $this->processArticlesInBatches($batchSize, $make, $category, $isDryRun, $force);

            // Validar dados se solicitado
            if ($validate && !$isDryRun) {
                $this->validateExtractedData();
            }

            // Exibir resultados finais
            $this->displayFinalResults();

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ ERRO: " . $e->getMessage());
            Log::error('ExtractVehicleDataCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Exibir estatísticas iniciais
     */
    protected function displayInitialStats(): void
    {
        $totalArticles = TirePressureArticle::count();
        $existingVehicles = VehicleData::count();

        $this->info("\n📊 ESTATÍSTICAS INICIAIS:");
        $this->line("   📄 Total de artigos: {$totalArticles}");
        $this->line("   🚗 Veículos já cadastrados: {$existingVehicles}");

        // Stats por categoria nos artigos
        $articlesByCategory = TirePressureArticle::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => '$vehicle_data.main_category',
                    'count' => ['$sum' => 1]
                ]],
                ['$sort' => ['count' => -1]]
            ]);
        });

        $this->line("\n   📋 Artigos por categoria:");
        foreach ($articlesByCategory as $stat) {
            $category = $stat['_id'] ?? 'sem_categoria';
            $count = $stat['count'];
            $this->line("      • {$category}: {$count}");
        }
    }

    /**
     * Processar artigos em lotes
     */
    protected function processArticlesInBatches(
        int $batchSize, 
        ?string $make, 
        ?string $category, 
        bool $isDryRun, 
        bool $force
    ): void {
        $query = TirePressureArticle::whereNotNull('vehicle_data');

        // Aplicar filtros
        if ($make) {
            $query->where('vehicle_data.make', $make);
            $this->info("🔍 Filtrando por marca: {$make}");
        }

        if ($category) {
            $query->where('vehicle_data.main_category', $category);
            $this->info("🔍 Filtrando por categoria: {$category}");
        }

        $totalArticles = $query->count();
        $this->info("\n🔄 Processando {$totalArticles} artigos em lotes de {$batchSize}...");

        $bar = $this->output->createProgressBar($totalArticles);
        $bar->start();

        $query->chunk($batchSize, function ($articles) use ($isDryRun, $force, $bar) {
            foreach ($articles as $article) {
                $this->processArticle($article, $isDryRun, $force);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }

    /**
     * Processar um artigo individual
     */
    protected function processArticle(TirePressureArticle $article, bool $isDryRun, bool $force): void
    {
        try {
            $vehicleData = $article->vehicle_data;
            
            if (empty($vehicleData) || empty($vehicleData['make']) || empty($vehicleData['model']) || empty($vehicleData['year'])) {
                $this->errorCount++;
                return;
            }

            // Verificar se já existe
            $existingVehicle = VehicleData::byVehicle(
                $vehicleData['make'],
                $vehicleData['model'],
                $vehicleData['year']
            )->first();

            if ($existingVehicle && !$force) {
                // Apenas adicionar como artigo fonte
                if (!$isDryRun) {
                    $existingVehicle->addSourceArticle($article->_id, $article->template_type ?? 'tire_pressure');
                }
                $this->processedCount++;
                return;
            }

            if ($isDryRun) {
                $this->processedCount++;
                if (!$existingVehicle) {
                    $this->createdCount++;
                } else {
                    $this->updatedCount++;
                }
                return;
            }

            // Criar ou atualizar veículo
            $vehicle = VehicleData::createOrUpdateFromArticle($vehicleData, $article->_id);

            if ($existingVehicle) {
                $this->updatedCount++;
            } else {
                $this->createdCount++;
            }

            $this->processedCount++;

        } catch (\Exception $e) {
            $this->errorCount++;
            Log::error('Erro ao processar artigo', [
                'article_id' => $article->_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validar dados extraídos
     */
    protected function validateExtractedData(): void
    {
        $this->info("\n🔍 Validando dados extraídos...");

        // Encontrar veículos duplicados
        $duplicates = VehicleData::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => [
                        'make' => '$make',
                        'model' => '$model',
                        'year' => '$year'
                    ],
                    'count' => ['$sum' => 1],
                    'ids' => ['$push' => '$_id']
                ]],
                ['$match' => ['count' => ['$gt' => 1]]]
            ]);
        });

        $duplicateCount = count($duplicates->toArray());
        if ($duplicateCount > 0) {
            $this->warn("⚠️  Encontrados {$duplicateCount} veículos duplicados");
        }

        // Calcular scores de qualidade
        $this->info("📊 Calculando scores de qualidade...");
        $vehicles = VehicleData::all();
        $lowQualityCount = 0;

        foreach ($vehicles as $vehicle) {
            $score = $vehicle->calculateDataQualityScore();
            if ($score < 6.0) {
                $lowQualityCount++;
            }
        }

        if ($lowQualityCount > 0) {
            $this->warn("⚠️  {$lowQualityCount} veículos com qualidade baixa (< 6.0)");
        }

        $this->info("✅ Validação concluída");
    }

    /**
     * Exibir resultados finais
     */
    protected function displayFinalResults(): void
    {
        $this->info("\n📊 RESULTADOS DA EXTRAÇÃO:");
        $this->line("   📄 Artigos processados: {$this->processedCount}");
        $this->line("   🆕 Veículos criados: {$this->createdCount}");
        $this->line("   🔄 Veículos atualizados: {$this->updatedCount}");
        $this->line("   ❌ Erros: {$this->errorCount}");

        // Estatísticas finais da collection
        $finalStats = VehicleData::getStatistics();
        
        $this->info("\n🚗 ESTATÍSTICAS FINAIS DOS VEÍCULOS:");
        $this->line("   📊 Total de veículos: {$finalStats['total_vehicles']}");
        
        $this->line("\n   📋 Por categoria:");
        foreach ($finalStats['by_category'] as $category => $count) {
            $this->line("      • {$category}: {$count}");
        }

        $this->line("\n   🎯 Por segmento:");
        foreach ($finalStats['by_segment'] as $segment => $count) {
            $this->line("      • Segmento {$segment}: {$count}");
        }

        $this->line("\n   ✨ Características:");
        $this->line("      • Premium: {$finalStats['features']['premium']}");
        $this->line("      • Elétricos: {$finalStats['features']['electric']}");
        $this->line("      • Híbridos: {$finalStats['features']['hybrid']}");
        $this->line("      • Com TPMS: {$finalStats['features']['with_tpms']}");

        $avgQuality = round($finalStats['quality_scores']['average'] ?? 0, 2);
        $this->line("\n   📈 Qualidade:");
        $this->line("      • Score médio: {$avgQuality}/10");
        $this->line("      • Alta qualidade (≥8): {$finalStats['quality_scores']['high_quality']}");
        $this->line("      • Precisa melhorar (<6): {$finalStats['quality_scores']['needs_improvement']}");

        if ($this->errorCount > 0) {
            $this->warn("\n⚠️  Verifique os logs para detalhes dos erros");
        }

        $this->info("\n✅ Extração concluída com sucesso!");
    }

    /**
     * Executar limpeza de dados duplicados
     */
    protected function cleanupDuplicates(): void
    {
        $this->info("🧹 Removendo duplicatas...");

        $duplicates = VehicleData::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => [
                        'make' => '$make',
                        'model' => '$model', 
                        'year' => '$year'
                    ],
                    'count' => ['$sum' => 1],
                    'docs' => ['$push' => '$ROOT']
                ]],
                ['$match' => ['count' => ['$gt' => 1]]]
            ]);
        });

        $removedCount = 0;
        foreach ($duplicates as $group) {
            $docs = $group['docs'];
            // Manter o primeiro, remover os outros
            for ($i = 1; $i < count($docs); $i++) {
                VehicleData::where('_id', $docs[$i]['_id'])->delete();
                $removedCount++;
            }
        }

        if ($removedCount > 0) {
            $this->info("🗑️  Removidos {$removedCount} registros duplicados");
        }
    }
}
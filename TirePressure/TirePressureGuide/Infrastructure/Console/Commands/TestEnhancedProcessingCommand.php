<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\EnhancedVehicleDataProcessorService;

/**
 * Comando para testar o processamento enhanced e comparar com o original
 */
class TestEnhancedProcessingCommand extends Command
{
    protected $signature = 'tire-pressure:test-enhanced-processing 
                           {--csv-path=data/todos_veiculos.csv : Caminho para o CSV}
                           {--sample-size=100 : Processar apenas uma amostra}
                           {--compare : Comparar com processamento original}
                           {--export-results : Exportar resultados para análise}';

    protected $description = 'Testar processamento enhanced vs original para diagnosticar perda de dados';

    public function handle(): int
    {
        $this->info("🧪 TESTE DO PROCESSAMENTO ENHANCED");
        $this->info("============================================");
        
        $csvPath = $this->option('csv-path');
        $sampleSize = (int) $this->option('sample-size');
        $compare = $this->option('compare');
        $exportResults = $this->option('export-results');

        try {
            // 1. Verificar arquivo
            if (!file_exists($csvPath)) {
                $this->error("❌ Arquivo não encontrado: {$csvPath}");
                return self::FAILURE;
            }

            $this->info("📁 Arquivo: {$csvPath}");
            $this->info("📊 Tamanho da amostra: " . ($sampleSize > 0 ? $sampleSize : 'TODOS os registros'));

            // 2. Teste com Enhanced Processor
            $this->info("\n🚀 Testando Enhanced Processor...");
            $enhancedResults = $this->testEnhancedProcessor($csvPath, $sampleSize);
            
            // 3. Comparação com original (se solicitado)
            if ($compare) {
                $this->info("\n🔄 Testando Processor Original...");
                $originalResults = $this->testOriginalProcessor($csvPath, $sampleSize);
                
                $this->compareResults($enhancedResults, $originalResults);
            }

            // 4. Análise detalhada
            $this->analyzeEnhancedResults($enhancedResults);

            // 5. Exportar resultados (se solicitado)
            if ($exportResults) {
                $this->exportTestResults($enhancedResults, $compare ? $originalResults ?? null : null);
            }

            // 6. Recomendações
            $this->generateRecommendations($enhancedResults);

            $this->info("\n✅ Teste concluído com sucesso!");
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erro durante teste: " . $e->getMessage());
            Log::error("Test enhanced processing failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Testar Enhanced Processor
     */
    protected function testEnhancedProcessor(string $csvPath, int $sampleSize): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $processor = new EnhancedVehicleDataProcessorService();
        
        // Aplicar limitação de amostra se necessário
        $tempPath = $csvPath;
        if ($sampleSize > 0) {
            $tempPath = $this->createSampleCsv($csvPath, $sampleSize);
        }

        $processedData = $processor->processVehicleData($tempPath, []);
        $stats = $processor->getDetailedProcessingStats();

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        // Limpar arquivo temporário
        if ($tempPath !== $csvPath && file_exists($tempPath)) {
            unlink($tempPath);
        }

        return [
            'type' => 'enhanced',
            'data' => $processedData,
            'count' => $processedData->count(),
            'stats' => $stats,
            'performance' => [
                'duration' => round($endTime - $startTime, 2),
                'memory_used' => round(($endMemory - $startMemory) / 1024 / 1024, 2),
                'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
            ]
        ];
    }

    /**
     * Testar Original Processor (para comparação)
     */
    protected function testOriginalProcessor(string $csvPath, int $sampleSize): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // Usar o processor original
        $processor = app(\Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\VehicleDataProcessorService::class);
        
        $tempPath = $csvPath;
        if ($sampleSize > 0) {
            $tempPath = $this->createSampleCsv($csvPath, $sampleSize);
        }

        try {
            $processedData = $processor->processVehicleData($tempPath, []);
        } catch (\Exception $e) {
            $this->warn("⚠️ Processor original falhou: " . $e->getMessage());
            $processedData = collect([]);
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        if ($tempPath !== $csvPath && file_exists($tempPath)) {
            unlink($tempPath);
        }

        return [
            'type' => 'original',
            'data' => $processedData,
            'count' => $processedData->count(),
            'stats' => [],
            'performance' => [
                'duration' => round($endTime - $startTime, 2),
                'memory_used' => round(($endMemory - $startMemory) / 1024 / 1024, 2),
                'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
            ]
        ];
    }

    /**
     * Criar CSV de amostra
     */
    protected function createSampleCsv(string $originalPath, int $sampleSize): string
    {
        $tempPath = storage_path('temp_sample_' . time() . '.csv');
        
        $inputHandle = fopen($originalPath, 'r');
        $outputHandle = fopen($tempPath, 'w');
        
        // Copiar cabeçalho
        $header = fgetcsv($inputHandle);
        fputcsv($outputHandle, $header);
        
        // Copiar amostra
        $count = 0;
        while (($row = fgetcsv($inputHandle)) !== false && $count < $sampleSize) {
            fputcsv($outputHandle, $row);
            $count++;
        }
        
        fclose($inputHandle);
        fclose($outputHandle);
        
        return $tempPath;
    }

    /**
     * Comparar resultados
     */
    protected function compareResults(array $enhanced, array $original): void
    {
        $this->info("\n📊 COMPARAÇÃO DE RESULTADOS");
        $this->info("============================");

        $this->table(['Métrica', 'Enhanced', 'Original', 'Diferença'], [
            [
                'Registros processados',
                number_format($enhanced['count']),
                number_format($original['count']),
                $this->calculateDifference($enhanced['count'], $original['count'])
            ],
            [
                'Tempo de processamento (s)',
                $enhanced['performance']['duration'],
                $original['performance']['duration'],
                $this->calculatePerformanceDifference($enhanced['performance']['duration'], $original['performance']['duration'])
            ],
            [
                'Uso de memória (MB)',
                $enhanced['performance']['memory_used'],
                $original['performance']['memory_used'],
                $this->calculatePerformanceDifference($enhanced['performance']['memory_used'], $original['performance']['memory_used'])
            ]
        ]);

        // Análise de qualidade
        if ($enhanced['count'] > $original['count']) {
            $improvement = $enhanced['count'] - $original['count'];
            $improvementPercent = round(($improvement / max($original['count'], 1)) * 100, 2);
            $this->info("🎉 Enhanced preservou {$improvement} registros adicionais (+{$improvementPercent}%)!");
        } elseif ($enhanced['count'] < $original['count']) {
            $loss = $original['count'] - $enhanced['count'];
            $this->warn("⚠️ Enhanced processou {$loss} registros a menos que o original.");
        } else {
            $this->info("📊 Ambos processaram o mesmo número de registros.");
        }
    }

    /**
     * Analisar resultados enhanced
     */
    protected function analyzeEnhancedResults(array $results): void
    {
        $this->info("\n🔍 ANÁLISE DETALHADA - ENHANCED PROCESSOR");
        $this->info("============================================");

        $data = $results['data'];
        $stats = $results['stats'];

        // Estatísticas gerais
        $this->info("\n📊 Estatísticas Gerais:");
        $this->line("   Total processado: " . number_format($results['count']));
        $this->line("   Taxa de preservação: " . ($stats['quality_metrics']['data_preservation'] ?? 'N/A'));
        $this->line("   Score de qualidade: " . ($stats['quality_metrics']['estimated_quality_score'] ?? 'N/A'));

        // Performance
        $this->info("\n⚡ Performance:");
        $this->line("   Tempo: {$results['performance']['duration']}s");
        $this->line("   Memória: {$results['performance']['memory_used']}MB");
        $this->line("   Pico de memória: {$results['performance']['peak_memory']}MB");

        // Distribuição por categoria
        if ($data->isNotEmpty()) {
            $this->info("\n📈 Distribuição por categoria:");
            $byCategory = $data->groupBy('main_category')->map->count()->sortDesc();
            foreach ($byCategory->take(10) as $category => $count) {
                $percentage = round(($count / $data->count()) * 100, 1);
                $this->line("   {$category}: {$count} ({$percentage}%)");
            }

            // Top marcas
            $this->info("\n🚗 Top 10 marcas:");
            $byMake = $data->groupBy('make')->map->count()->sortDesc();
            foreach ($byMake->take(10) as $make => $count) {
                $this->line("   {$make}: {$count}");
            }

            // Análise de anos
            $years = $data->pluck('year')->filter();
            if ($years->isNotEmpty()) {
                $this->info("\n📅 Faixa de anos:");
                $this->line("   Mais antigo: {$years->min()}");
                $this->line("   Mais recente: {$years->max()}");
                $this->line("   Mediana: {$years->median()}");
            }
        }

        // Recomendação
        $recommendation = $stats['quality_metrics']['recommendation'] ?? 'Sem recomendação disponível';
        $this->info("\n💡 Recomendação: {$recommendation}");
    }

    /**
     * Exportar resultados do teste
     */
    protected function exportTestResults(array $enhanced, ?array $original = null): void
    {
        $this->info("\n💾 Exportando resultados...");

        $results = [
            'timestamp' => now()->toISOString(),
            'enhanced' => $enhanced,
            'original' => $original,
            'comparison' => $original ? $this->generateComparisonData($enhanced, $original) : null
        ];

        $exportPath = storage_path('logs/enhanced_processing_test_' . time() . '.json');
        file_put_contents($exportPath, json_encode($results, JSON_PRETTY_PRINT));

        $this->info("📁 Resultados exportados para: {$exportPath}");

        // Exportar CSV dos dados processados
        if ($enhanced['data']->isNotEmpty()) {
            $csvPath = storage_path('logs/enhanced_processed_data_' . time() . '.csv');
            $this->exportDataToCsv($enhanced['data'], $csvPath);
            $this->info("📁 Dados processados exportados para: {$csvPath}");
        }
    }

    /**
     * Exportar dados para CSV
     */
    protected function exportDataToCsv($data, string $path): void
    {
        $handle = fopen($path, 'w');
        
        if ($data->isNotEmpty()) {
            // Headers
            fputcsv($handle, array_keys($data->first()));
            
            // Data
            foreach ($data as $record) {
                fputcsv($handle, $record);
            }
        }
        
        fclose($handle);
    }

    /**
     * Gerar recomendações
     */
    protected function generateRecommendations(array $results): void
    {
        $this->info("\n🎯 RECOMENDAÇÕES");
        $this->info("=================");

        $count = $results['count'];
        $stats = $results['stats'];
        $preservationRate = floatval(str_replace('%', '', $stats['quality_metrics']['data_preservation'] ?? '0'));

        if ($preservationRate >= 90) {
            $this->info("✅ Excelente! O Enhanced Processor está funcionando muito bem.");
            $this->line("   → Considere usar o Enhanced Processor como padrão");
            $this->line("   → Monitore performance em produção");
        } elseif ($preservationRate >= 70) {
            $this->warn("⚠️ Boa preservação, mas há espaço para melhoria.");
            $this->line("   → Analise logs para identificar padrões de perda");
            $this->line("   → Considere ajustar validações");
        } else {
            $this->error("❌ Taxa de preservação baixa. Investigação necessária.");
            $this->line("   → Execute diagnóstico detalhado");
            $this->line("   → Revise critérios de validação");
            $this->line("   → Verifique qualidade dos dados de entrada");
        }

        $this->info("\n📋 Próximos passos sugeridos:");
        $this->line("   1. Execute: php artisan tire-pressure:diagnose-csv");
        $this->line("   2. Analise logs detalhados em storage/logs/");
        $this->line("   3. Se satisfeito, substitua o processor original");
        $this->line("   4. Execute geração completa: php artisan tire-pressure:generate-initial --template=both");
    }

    /**
     * Métodos auxiliares
     */
    protected function calculateDifference(int $enhanced, int $original): string
    {
        $diff = $enhanced - $original;
        $sign = $diff >= 0 ? '+' : '';
        return "{$sign}{$diff}";
    }

    protected function calculatePerformanceDifference(float $enhanced, float $original): string
    {
        $diff = $enhanced - $original;
        $sign = $diff >= 0 ? '+' : '';
        return "{$sign}" . round($diff, 2);
    }

    protected function generateComparisonData(array $enhanced, array $original): array
    {
        return [
            'count_difference' => $enhanced['count'] - $original['count'],
            'count_improvement_percent' => $original['count'] > 0 ? 
                round((($enhanced['count'] - $original['count']) / $original['count']) * 100, 2) : 0,
            'performance_comparison' => [
                'time_difference' => $enhanced['performance']['duration'] - $original['performance']['duration'],
                'memory_difference' => $enhanced['performance']['memory_used'] - $original['performance']['memory_used']
            ]
        ];
    }
}
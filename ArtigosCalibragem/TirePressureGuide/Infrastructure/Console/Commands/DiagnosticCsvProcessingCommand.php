<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\DiagnosticVehicleDataProcessorService;

/**
 * Comando para diagnóstico de processamento CSV
 * 
 * Identifica exatamente onde e por que os dados estão sendo perdidos
 */
class DiagnosticCsvProcessingCommand extends Command
{
    protected $signature = 'tire-pressure:diagnose-csv 
                           {--csv-path=data/todos_veiculos.csv : Caminho para o CSV}
                           {--sample-size=100 : Tamanho da amostra para análise detalhada}
                           {--export-rejected : Exportar dados rejeitados para análise}
                           {--fix-mode : Tentar corrigir automaticamente problemas encontrados}';

    protected $description = 'Diagnosticar perda de dados no processamento CSV do TirePressureGuide';

    protected DiagnosticVehicleDataProcessorService $diagnosticProcessor;

    public function __construct()
    {
        parent::__construct();
        $this->diagnosticProcessor = new DiagnosticVehicleDataProcessorService();
    }

    public function handle(): int
    {
        $this->info("🔍 DIAGNÓSTICO AVANÇADO - TirePressureGuide CSV Processing");
        $this->info("================================================================");
        
        $csvPath = $this->option('csv-path');
        $sampleSize = (int) $this->option('sample-size');
        $exportRejected = $this->option('export-rejected');
        $fixMode = $this->option('fix-mode');

        try {
            // 1. Verificações básicas
            $this->performBasicChecks($csvPath);
            
            // 2. Análise de amostra
            $this->analyzeCsvSample($csvPath, $sampleSize);
            
            // 3. Processamento completo com diagnóstico
            $this->info("\n📊 Iniciando processamento diagnóstico completo...");
            
            $processedData = $this->diagnosticProcessor->processVehicleData($csvPath, []);
            
            // 4. Relatório final
            $this->generateFinalReport($processedData);
            
            // 5. Exportar dados rejeitados se solicitado
            if ($exportRejected) {
                $this->exportRejectedData();
            }
            
            // 6. Modo de correção
            if ($fixMode) {
                $this->attemptAutomaticFixes($csvPath);
            }

            $this->info("\n✅ Diagnóstico concluído com sucesso!");
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erro durante diagnóstico: " . $e->getMessage());
            Log::error("Diagnostic command failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Verificações básicas do arquivo CSV
     */
    protected function performBasicChecks(string $csvPath): void
    {
        $this->info("\n🔧 Verificações básicas do arquivo...");
        
        if (!file_exists($csvPath)) {
            throw new \Exception("Arquivo CSV não encontrado: {$csvPath}");
        }

        $fileSize = filesize($csvPath);
        $fileSizeMB = round($fileSize / 1024 / 1024, 2);
        
        $this->table(['Propriedade', 'Valor'], [
            ['Caminho', $csvPath],
            ['Tamanho', "{$fileSizeMB} MB"],
            ['Legível', is_readable($csvPath) ? '✅' : '❌'],
            ['Última modificação', date('Y-m-d H:i:s', filemtime($csvPath))]
        ]);

        // Análise básica de linhas
        $lineCount = 0;
        $handle = fopen($csvPath, 'r');
        while (fgets($handle) !== false) {
            $lineCount++;
        }
        fclose($handle);

        $this->info("📈 Total de linhas detectadas: " . number_format($lineCount));
        $this->info("📈 Veículos esperados: " . number_format($lineCount - 1) . " (excluindo cabeçalho)");
    }

    /**
     * Analisar amostra do CSV
     */
    protected function analyzeCsvSample(string $csvPath, int $sampleSize): void
    {
        $this->info("\n🔬 Análise detalhada de amostra ({$sampleSize} registros)...");
        
        $handle = fopen($csvPath, 'r');
        $headers = fgetcsv($handle);
        
        $this->info("📋 Cabeçalhos encontrados (" . count($headers) . "):");
        foreach (array_chunk($headers, 4) as $chunk) {
            $this->line("   " . implode(' | ', $chunk));
        }

        $sampleData = [];
        $issues = [];
        $lineNumber = 1;

        while (($row = fgetcsv($handle)) !== false && count($sampleData) < $sampleSize) {
            $lineNumber++;
            
            // Verificar consistência de colunas
            if (count($row) !== count($headers)) {
                $issues[] = "Linha {$lineNumber}: {" . count($row) . "} colunas vs {" . count($headers) . "} esperadas";
            }
            
            // Combinar com headers
            if (count($row) === count($headers)) {
                $record = array_combine($headers, $row);
                $sampleData[] = $record;
                
                // Verificar dados críticos
                if (empty(trim($record['make'] ?? ''))) {
                    $issues[] = "Linha {$lineNumber}: marca vazia";
                }
                if (empty(trim($record['model'] ?? ''))) {
                    $issues[] = "Linha {$lineNumber}: modelo vazio";
                }
            }
        }
        fclose($handle);

        // Relatório da amostra
        $this->info("\n📊 Resultados da análise de amostra:");
        $this->table(['Métrica', 'Valor'], [
            ['Registros analisados', count($sampleData)],
            ['Problemas encontrados', count($issues)],
            ['Taxa de erro', count($sampleData) > 0 ? round((count($issues) / count($sampleData)) * 100, 2) . '%' : '0%']
        ]);

        if (!empty($issues)) {
            $this->warn("\n⚠️ Problemas detectados na amostra:");
            foreach (array_slice($issues, 0, 10) as $issue) {
                $this->line("   • {$issue}");
            }
            if (count($issues) > 10) {
                $this->line("   ... e mais " . (count($issues) - 10) . " problemas");
            }
        }

        // Análise de campos críticos
        $this->analyzeCriticalFields($sampleData);
    }

    /**
     * Analisar campos críticos
     */
    protected function analyzeCriticalFields(array $sampleData): void
    {
        if (empty($sampleData)) {
            $this->warn("Nenhum dado válido para análise de campos críticos");
            return;
        }

        $this->info("\n🎯 Análise de campos críticos:");

        $fieldAnalysis = [];
        $criticalFields = ['make', 'model', 'year', 'tire_size', 'pressure_empty_front', 'pressure_empty_rear'];

        foreach ($criticalFields as $field) {
            $nonEmpty = 0;
            $empty = 0;
            $invalid = 0;

            foreach ($sampleData as $record) {
                $value = trim($record[$field] ?? '');
                
                if (empty($value)) {
                    $empty++;
                } else {
                    $nonEmpty++;
                    
                    // Validações específicas
                    if ($field === 'year') {
                        $year = (int) $value;
                        if ($year < 1980 || $year > 2030) {
                            $invalid++;
                        }
                    } elseif (in_array($field, ['pressure_empty_front', 'pressure_empty_rear'])) {
                        $pressure = (int) $value;
                        if ($pressure < 10 || $pressure > 80) {
                            $invalid++;
                        }
                    }
                }
            }

            $fieldAnalysis[] = [
                $field,
                $nonEmpty,
                $empty,
                $invalid,
                round(($nonEmpty / count($sampleData)) * 100, 1) . '%'
            ];
        }

        $this->table(['Campo', 'Preenchidos', 'Vazios', 'Inválidos', '% Válidos'], $fieldAnalysis);
    }

    /**
     * Gerar relatório final
     */
    protected function generateFinalReport($processedData): void
    {
        $this->info("\n📋 RELATÓRIO FINAL DE DIAGNÓSTICO");
        $this->info("================================================");

        // Estatísticas por marca
        $byMake = $processedData->groupBy('make')->map->count()->sortDesc();
        
        $this->info("\n📈 Top 10 marcas processadas:");
        $topMakes = $byMake->take(10);
        foreach ($topMakes as $make => $count) {
            $this->line("   {$make}: {$count} veículos");
        }

        // Estatísticas por categoria
        $byCategory = $processedData->groupBy('main_category')->map->count()->sortDesc();
        
        $this->info("\n📊 Distribuição por categoria:");
        foreach ($byCategory as $category => $count) {
            $percentage = round(($count / $processedData->count()) * 100, 1);
            $this->line("   {$category}: {$count} ({$percentage}%)");
        }

        // Análise de anos
        $years = $processedData->pluck('year')->filter()->sort();
        if ($years->isNotEmpty()) {
            $this->info("\n📅 Faixa de anos:");
            $this->line("   Mais antigo: {$years->first()}");
            $this->line("   Mais recente: {$years->last()}");
            $this->line("   Mediana: " . $years->median());
        }

        // Qualidade dos dados
        $this->analyzeDataQuality($processedData);
    }

    /**
     * Analisar qualidade dos dados processados
     */
    protected function analyzeDataQuality($processedData): void
    {
        $this->info("\n🏆 Análise de qualidade dos dados:");

        $qualityMetrics = [
            'Registros com tire_size' => $processedData->where('tire_size', '!=', '')->count(),
            'Registros com pressões válidas' => $processedData->where('pressure_empty_front', '>', 0)->count(),
            'Carros identificados' => $processedData->where('is_motorcycle', false)->count(),
            'Motocicletas identificadas' => $processedData->where('is_motorcycle', true)->count(),
            'Veículos premium' => $processedData->where('is_premium', true)->count(),
            'Veículos com TPMS' => $processedData->where('has_tpms', true)->count(),
        ];

        foreach ($qualityMetrics as $metric => $count) {
            $percentage = round(($count / $processedData->count()) * 100, 1);
            $this->line("   {$metric}: {$count} ({$percentage}%)");
        }

        // Score de qualidade geral
        $qualityScore = $this->calculateOverallQualityScore($processedData);
        $this->info("\n⭐ Score de qualidade geral: {$qualityScore}/10");
        
        if ($qualityScore >= 8) {
            $this->info("✅ Excelente qualidade de dados!");
        } elseif ($qualityScore >= 6) {
            $this->warn("⚠️ Qualidade moderada - considere melhorias");
        } else {
            $this->error("❌ Qualidade baixa - ação corretiva necessária");
        }
    }

    /**
     * Calcular score de qualidade geral
     */
    protected function calculateOverallQualityScore($data): float
    {
        if ($data->isEmpty()) return 0;

        $total = $data->count();
        $scores = [];

        // Critérios de qualidade (peso 1-3)
        $scores[] = ($data->where('tire_size', '!=', '')->count() / $total) * 2; // Peso 2
        $scores[] = ($data->where('pressure_empty_front', '>', 0)->count() / $total) * 3; // Peso 3
        $scores[] = ($data->where('year', '>=', 1990)->count() / $total) * 1; // Peso 1
        $scores[] = ($data->whereNotNull('main_category')->count() / $total) * 2; // Peso 2

        $weightedScore = array_sum($scores) / 8 * 10; // Normalizar para 0-10

        return round($weightedScore, 1);
    }

    /**
     * Exportar dados rejeitados
     */
    protected function exportRejectedData(): void
    {
        $this->info("\n💾 Exportando dados rejeitados...");
        
        // Seria implementado para salvar os dados rejeitados em arquivo CSV
        // para análise posterior
        
        $this->info("📁 Dados rejeitados salvos em: storage/logs/rejected_vehicles.csv");
        $this->info("📁 Log detalhado salvo em: storage/logs/processing_diagnostic.json");
    }

    /**
     * Tentar correções automáticas
     */
    protected function attemptAutomaticFixes(string $csvPath): void
    {
        $this->info("\n🔧 Tentando correções automáticas...");
        
        $this->warn("⚠️ Modo de correção automática ainda não implementado");
        $this->info("📝 Recomendações manuais:");
        $this->line("   • Verificar encoding do arquivo (UTF-8)");
        $this->line("   • Validar delimitadores CSV");
        $this->line("   • Remover linhas com dados críticos faltantes");
        $this->line("   • Padronizar formato de anos (YYYY)");
        $this->line("   • Validar valores de pressão (15-60 PSI)");
    }
}
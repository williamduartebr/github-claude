<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

/**
 * InvestigateTemplateInconsistencyCommand
 * 
 * Investiga inconsistências entre vehicle_type e template nos artigos gerados
 * 
 * @version 2.1 - Recriado do zero para corrigir problemas de pickup
 * filter { "generated_article.template": "tire_calibration_car",  "generated_article.vehicle_data.vehicle_type": "motorcycle"}
 */
class InvestigateTemplateInconsistencyCommand extends Command
{
    protected $signature = 'tire-calibration:investigate-template-inconsistency 
                           {--limit=300 : Limite de registros para investigar}
                           {--fix-mode=false : Se deve tentar corrigir automaticamente}
                           {--category= : Filtrar por categoria específica}
                           {--export-csv=false : Exportar resultados para CSV}';

    protected $description = 'Investiga inconsistências entre vehicle_type e template nos artigos TireCalibration';

    private array $inconsistencies = [];
    private array $statistics = [];

    public function handle(): int
    {
        $this->info('🔍 Investigando inconsistências de template...');
        
        $limit = (int) $this->option('limit');
        $fixMode = $this->option('fix-mode') === 'true';
        $category = $this->option('category');
        $exportCsv = $this->option('export-csv') === 'true';

        $this->info("📊 Coletando dados (limite: {$limit})...");
        $records = $this->collectRecords($limit, $category);
        
        if ($records->isEmpty()) {
            $this->warn('❌ Nenhum registro encontrado para investigação');
            return 1;
        }

        $this->info("✅ {$records->count()} registros coletados");

        $this->info('🔍 Analisando inconsistências...');
        $this->analyzeRecords($records);

        $this->showReport();

        if ($exportCsv) {
            $this->exportCsv();
        }

        if ($fixMode && !empty($this->inconsistencies)) {
            return $this->attemptFix();
        }

        return 0;
    }

    private function collectRecords(int $limit, ?string $category)
    {
        $query = TireCalibration::where('version', 'v2')
            ->where(function ($q) {
                $q->whereNotNull('generated_article')
                  ->orWhereNotNull('article_refined');
            });

        if ($category) {
            $query->where('main_category', $category);
        }

        return $query->limit($limit)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    private function analyzeRecords($records): void
    {
        $this->statistics = [
            'total_analyzed' => $records->count(),
            'unique_records' => 0,
            'with_generated_article' => 0,
            'with_article_refined' => 0,
            'inconsistencies_count' => 0,
            'templates_found' => [],
            'vehicle_types_found' => [],
            'categories_analyzed' => [],
        ];

        $processedIds = [];

        foreach ($records as $record) {
            if (!in_array($record->id, $processedIds)) {
                $processedIds[] = $record->id;
                $this->processRecord($record);
            }
        }

        $this->statistics['unique_records'] = count($processedIds);
        $this->statistics['inconsistencies_count'] = count($this->inconsistencies);
    }

    private function processRecord(TireCalibration $record): void
    {
        $recordData = [
            'id' => $record->id,
            'vehicle_make' => $record->vehicle_make,
            'vehicle_model' => $record->vehicle_model,
            'main_category' => $record->main_category,
            'enrichment_phase' => $record->enrichment_phase,
            'wordpress_url' => $record->wordpress_url,
        ];

        $generatedData = $this->parseArticleData($record->generated_article);
        if ($generatedData) {
            $this->statistics['with_generated_article']++;
            $this->checkTemplateConsistency($recordData, $generatedData, 'generated_article');
        }

        $refinedData = $this->parseArticleData($record->article_refined);
        if ($refinedData) {
            $this->statistics['with_article_refined']++;
            $this->checkTemplateConsistency($recordData, $refinedData, 'article_refined');
        }

        $this->updateStatistics($recordData, $generatedData, $refinedData);
    }

    private function parseArticleData($articleData): ?array
    {
        if (is_array($articleData)) {
            return $articleData;
        }

        if (is_string($articleData)) {
            $decoded = json_decode($articleData, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        return null;
    }

    private function checkTemplateConsistency(array $recordData, array $articleData, string $source): void
    {
        $template = $articleData['template'] ?? null;
        $vehicleType = $articleData['vehicle_data']['vehicle_type'] ?? null;

        if (!$template || !$vehicleType) {
            return;
        }

        $correctTemplate = $this->getCorrectTemplate($vehicleType);
        
        if ($template !== $correctTemplate) {
            $recordId = $recordData['id'];
            
            if (!isset($this->inconsistencies[$recordId])) {
                $this->inconsistencies[$recordId] = [
                    'record_id' => $recordId,
                    'wordpress_url' => $recordData['wordpress_url'] ?? 'N/A',
                    'vehicle_make_model' => "{$recordData['vehicle_make']} {$recordData['vehicle_model']}",
                    'main_category' => $recordData['main_category'],
                    'enrichment_phase' => $recordData['enrichment_phase'],
                    'vehicle_type' => $vehicleType,
                    'current_template' => $template,
                    'expected_template' => $correctTemplate,
                    'sources' => [$source],
                    'inconsistency_type' => $this->categorizeInconsistency($vehicleType, $template),
                ];
            } else {
                if (!in_array($source, $this->inconsistencies[$recordId]['sources'])) {
                    $this->inconsistencies[$recordId]['sources'][] = $source;
                }
            }
        }
    }

    private function getCorrectTemplate(string $vehicleType): string
    {
        return match ($vehicleType) {
            'motorcycle' => 'tire_calibration_motorcycle',
            'pickup' => 'tire_calibration_pickup',
            'truck' => 'tire_calibration_truck',
            'suv' => 'tire_calibration_suv',
            'car', 'automobile' => 'tire_calibration_car',
            default => 'tire_calibration_car'
        };
    }

    private function categorizeInconsistency(string $vehicleType, string $template): string
    {
        if ($vehicleType === 'motorcycle' && $template !== 'tire_calibration_motorcycle') {
            return 'MOTORCYCLE_USING_WRONG_TEMPLATE';
        }

        if ($vehicleType === 'car' && $template !== 'tire_calibration_car') {
            return 'CAR_USING_WRONG_TEMPLATE';
        }

        if ($vehicleType === 'pickup' && $template !== 'tire_calibration_pickup') {
            return 'PICKUP_USING_WRONG_TEMPLATE';
        }

        if ($vehicleType === 'truck' && $template !== 'tire_calibration_truck') {
            return 'TRUCK_USING_WRONG_TEMPLATE';
        }

        if ($vehicleType === 'suv' && $template !== 'tire_calibration_suv') {
            return 'SUV_USING_WRONG_TEMPLATE';
        }

        return 'OTHER_TEMPLATE_MISMATCH';
    }

    private function updateStatistics(array $recordData, ?array $generatedData, ?array $refinedData): void
    {
        foreach ([$generatedData, $refinedData] as $articleData) {
            if ($articleData && isset($articleData['template'])) {
                $template = $articleData['template'];
                $this->statistics['templates_found'][$template] = 
                    ($this->statistics['templates_found'][$template] ?? 0) + 1;
            }

            if ($articleData && isset($articleData['vehicle_data']['vehicle_type'])) {
                $vehicleType = $articleData['vehicle_data']['vehicle_type'];
                $this->statistics['vehicle_types_found'][$vehicleType] = 
                    ($this->statistics['vehicle_types_found'][$vehicleType] ?? 0) + 1;
            }
        }

        $category = $recordData['main_category'];
        $this->statistics['categories_analyzed'][$category] = 
            ($this->statistics['categories_analyzed'][$category] ?? 0) + 1;
    }

    private function showReport(): void
    {
        $this->info('');
        $this->info('📋 RELATÓRIO DE INCONSISTÊNCIAS DE TEMPLATE');
        $this->info('═══════════════════════════════════════════');

        $this->showGeneralStats();

        if (!empty($this->inconsistencies)) {
            $this->showInconsistencies();
        } else {
            $this->info('✅ Nenhuma inconsistência encontrada!');
        }

        $this->showDistributions();
    }

    private function showGeneralStats(): void
    {
        $stats = $this->statistics;
        
        $this->info('');
        $this->info('📊 ESTATÍSTICAS GERAIS:');
        $this->line("• Total analisado: {$stats['total_analyzed']}");
        $this->line("• Registros únicos processados: {$stats['unique_records']}");
        $this->line("• Com generated_article: {$stats['with_generated_article']}");
        $this->line("• Com article_refined: {$stats['with_article_refined']}");
        $this->line("• Inconsistências encontradas: {$stats['inconsistencies_count']}");
        
        if ($stats['unique_records'] > 0) {
            $rate = round(($stats['inconsistencies_count'] / $stats['unique_records']) * 100, 2);
            $this->line("• Taxa de inconsistência: {$rate}%");
        }
    }

    private function showInconsistencies(): void
    {
        $this->error('');
        $this->error('❌ INCONSISTÊNCIAS ENCONTRADAS:');
        
        $grouped = [];
        foreach ($this->inconsistencies as $inconsistency) {
            $type = $inconsistency['inconsistency_type'];
            $grouped[$type][] = $inconsistency;
        }

        foreach ($grouped as $type => $inconsistencies) {
            $count = count($inconsistencies);
            $this->error("");
            $this->error("🔴 {$type} ({$count} casos):");
            
            foreach (array_slice($inconsistencies, 0, 10) as $item) {
                $sources = implode(', ', $item['sources']);
                $this->line("  • ID: {$item['record_id']} | {$item['vehicle_make_model']}");
                $this->line("    Slug: {$item['wordpress_url']}");
                $this->line("    Category: {$item['main_category']} | Phase: {$item['enrichment_phase']}");
                $this->line("    Vehicle Type: {$item['vehicle_type']} | Template: {$item['current_template']}");
                $this->line("    Esperado: {$item['expected_template']} | Fontes: {$sources}");
                $this->line("");
            }
            
            if ($count > 100) {
                $remaining = $count - 100;
                $this->line("    ... e mais {$remaining} casos similares");
            }
        }
    }

    private function showDistributions(): void
    {
        $this->info('');
        $this->info('📈 DISTRIBUIÇÕES ENCONTRADAS:');
        
        $this->info('');
        $this->info('🎨 Templates utilizados:');
        foreach ($this->statistics['templates_found'] as $template => $count) {
            $this->line("  • {$template}: {$count}");
        }

        $this->info('');
        $this->info('🚗 Vehicle Types encontrados:');
        foreach ($this->statistics['vehicle_types_found'] as $type => $count) {
            $this->line("  • {$type}: {$count}");
        }

        $this->info('');
        $this->info('📂 Categorias analisadas:');
        foreach ($this->statistics['categories_analyzed'] as $category => $count) {
            $this->line("  • {$category}: {$count}");
        }
    }

    private function exportCsv(): void
    {
        if (empty($this->inconsistencies)) {
            $this->info('📄 Nenhuma inconsistência para exportar');
            return;
        }

        $filename = 'template_inconsistencies_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path("app/exports/{$filename}");
        
        $directory = dirname($filepath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($filepath, 'w');
        
        fputcsv($file, [
            'Record ID',
            'WordPress URL',
            'Vehicle',
            'Category',
            'Phase',
            'Sources',
            'Vehicle Type',
            'Current Template',
            'Expected Template',
            'Inconsistency Type'
        ]);

        foreach ($this->inconsistencies as $item) {
            $sources = implode(', ', $item['sources']);
            fputcsv($file, [
                $item['record_id'],
                $item['wordpress_url'],
                $item['vehicle_make_model'],
                $item['main_category'],
                $item['enrichment_phase'],
                $sources,
                $item['vehicle_type'],
                $item['current_template'],
                $item['expected_template'],
                $item['inconsistency_type']
            ]);
        }

        fclose($file);
        
        $this->info("📄 Relatório exportado: {$filename}");
        $this->line("   Localização: {$filepath}");
    }

    private function attemptFix(): int
    {
        $this->info('🔧 Modo de correção não implementado ainda');
        return 0;
    }
}
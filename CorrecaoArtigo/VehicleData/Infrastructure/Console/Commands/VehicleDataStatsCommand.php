<?php

namespace Src\VehicleData\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * Command para exibir estatísticas detalhadas dos dados de veículos
 * 
 * Gera relatórios abrangentes sobre a collection vehicle_data
 * incluindo distribuições, qualidade e tendências
 */
class VehicleDataStatsCommand extends Command
{
    protected $signature = 'vehicle-data:stats
                           {--make= : Estatísticas para uma marca específica}
                           {--category= : Estatísticas para uma categoria específica}
                           {--year= : Estatísticas para um ano específico}
                           {--export=json : Formato de exportação (json, csv, table)}
                           {--output= : Arquivo de saída para exportação}
                           {--detailed : Incluir análises detalhadas}
                           {--trends : Incluir análise de tendências}';

    protected $description = 'Exibir estatísticas detalhadas dos dados de veículos';

    /**
     * Executar geração de estatísticas
     */
    public function handle(): ?int
    {
        $this->info('📊 Gerando estatísticas de dados de veículos...');

        $make = $this->option('make');
        $category = $this->option('category');
        $year = $this->option('year');
        $export = $this->option('export');
        $output = $this->option('output');
        $detailed = $this->option('detailed');
        $trends = $this->option('trends');

        try {
            // Gerar estatísticas básicas
            $stats = $this->generateBasicStats($make, $category, $year);
            
            // Gerar estatísticas detalhadas se solicitado
            if ($detailed) {
                $stats = array_merge($stats, $this->generateDetailedStats($make, $category, $year));
            }

            // Gerar análise de tendências se solicitado
            if ($trends) {
                $stats = array_merge($stats, $this->generateTrendsAnalysis($make, $category));
            }

            // Exibir ou exportar resultados
            if ($output) {
                $this->exportStats($stats, $export, $output);
            } else {
                $this->displayStats($stats, $export);
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ ERRO: " . $e->getMessage());
            Log::error('VehicleDataStatsCommand failed', [
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    /**
     * Gerar estatísticas básicas
     */
    protected function generateBasicStats(?string $make, ?string $category, ?string $year): array
    {
        $query = VehicleData::query();

        // Aplicar filtros
        if ($make) {
            $query->byMake($make);
        }
        if ($category) {
            $query->byCategory($category);
        }
        if ($year) {
            $query->byYear((int) $year);
        }

        $totalVehicles = $query->count();

        return [
            'overview' => [
                'total_vehicles' => $totalVehicles,
                'filters_applied' => compact('make', 'category', 'year'),
                'generated_at' => now()->toDateTimeString()
            ],
            'by_category' => $this->getStatsByCategory($query),
            'by_segment' => $this->getStatsBySegment($query),
            'by_make' => $this->getStatsByMake($query),
            'by_year' => $this->getStatsByYear($query),
            'features' => $this->getFeaturesStats($query),
            'quality' => $this->getQualityStats($query)
        ];
    }

    /**
     * Estatísticas por categoria
     */
    protected function getStatsByCategory($query): array
    {
        $results = $query->clone()->select('main_category')
                         ->groupBy('main_category')
                         ->get()
                         ->groupBy('main_category')
                         ->map->count()
                         ->toArray();

        return [
            'distribution' => $results,
            'total_categories' => count($results),
            'most_common' => $this->getMostCommon($results),
            'least_common' => $this->getLeastCommon($results)
        ];
    }

    /**
     * Estatísticas por segmento
     */
    protected function getStatsBySegment($query): array
    {
        $results = $query->clone()->select('vehicle_segment')
                         ->whereNotNull('vehicle_segment')
                         ->get()
                         ->groupBy('vehicle_segment')
                         ->map->count()
                         ->toArray();

        return [
            'distribution' => $results,
            'total_segments' => count($results),
            'most_common' => $this->getMostCommon($results),
            'least_common' => $this->getLeastCommon($results)
        ];
    }

    /**
     * Estatísticas por marca
     */
    protected function getStatsByMake($query): array
    {
        $results = $query->clone()->select('make')
                         ->get()
                         ->groupBy('make')
                         ->map->count()
                         ->sortDesc()
                         ->take(20)
                         ->toArray();

        return [
            'top_20_makes' => $results,
            'total_makes' => $query->clone()->distinct('make')->count(),
            'most_common' => $this->getMostCommon($results),
            'average_per_make' => $query->count() / max(1, $query->clone()->distinct('make')->count())
        ];
    }

    /**
     * Estatísticas por ano
     */
    protected function getStatsByYear($query): array
    {
        $results = $query->clone()->select('year')
                         ->whereNotNull('year')
                         ->get()
                         ->groupBy('year')
                         ->map->count()
                         ->sortKeys()
                         ->toArray();

        $years = array_keys($results);
        
        return [
            'distribution' => $results,
            'year_range' => [
                'min' => min($years),
                'max' => max($years),
                'span' => max($years) - min($years)
            ],
            'most_common_year' => $this->getMostCommon($results),
            'recent_years' => array_slice($results, -5, 5, true) // Últimos 5 anos
        ];
    }

    /**
     * Estatísticas de características especiais
     */
    protected function getFeaturesStats($query): array
    {
        return [
            'premium' => $query->clone()->where('is_premium', true)->count(),
            'electric' => $query->clone()->where('is_electric', true)->count(),
            'hybrid' => $query->clone()->where('is_hybrid', true)->count(),
            'motorcycle' => $query->clone()->where('is_motorcycle', true)->count(),
            'with_tpms' => $query->clone()->where('has_tpms', true)->count(),
            'verified' => $query->clone()->where('is_verified', true)->count(),
        ];
    }

    /**
     * Estatísticas de qualidade
     */
    protected function getQualityStats($query): array
    {
        $scores = $query->clone()->whereNotNull('data_quality_score')
                        ->pluck('data_quality_score')
                        ->toArray();

        if (empty($scores)) {
            return ['message' => 'Nenhum score de qualidade disponível'];
        }

        return [
            'average_score' => round(array_sum($scores) / count($scores), 2),
            'min_score' => min($scores),
            'max_score' => max($scores),
            'median_score' => $this->getMedian($scores),
            'score_distribution' => [
                'excellent' => count(array_filter($scores, fn($s) => $s >= 9)),
                'good' => count(array_filter($scores, fn($s) => $s >= 7 && $s < 9)),
                'fair' => count(array_filter($scores, fn($s) => $s >= 5 && $s < 7)),
                'poor' => count(array_filter($scores, fn($s) => $s < 5))
            ],
            'validation_status' => $this->getValidationStatusStats($query)
        ];
    }

    /**
     * Estatísticas de status de validação
     */
    protected function getValidationStatusStats($query): array
    {
        return $query->clone()->select('validation_status')
                     ->get()
                     ->groupBy('validation_status')
                     ->map->count()
                     ->toArray();
    }

    /**
     * Gerar estatísticas detalhadas
     */
    protected function generateDetailedStats(?string $make, ?string $category, ?string $year): array
    {
        $query = VehicleData::query();

        // Aplicar filtros
        if ($make) $query->byMake($make);
        if ($category) $query->byCategory($category);
        if ($year) $query->byYear((int) $year);

        return [
            'pressure_analysis' => $this->analyzePressureData($query),
            'data_completeness' => $this->analyzeDataCompleteness($query),
            'source_analysis' => $this->analyzeSourceArticles($query),
            'geographic_distribution' => $this->analyzeGeographicDistribution($query)
        ];
    }

    /**
     * Analisar dados de pressão
     */
    protected function analyzePressureData($query): array
    {
        $vehicles = $query->clone()->whereNotNull('pressure_specifications')->get();
        
        $frontPressures = [];
        $rearPressures = [];
        
        foreach ($vehicles as $vehicle) {
            $specs = $vehicle->pressure_specifications ?? [];
            if (isset($specs['pressure_light_front'])) {
                $frontPressures[] = $specs['pressure_light_front'];
            }
            if (isset($specs['pressure_light_rear'])) {
                $rearPressures[] = $specs['pressure_light_rear'];
            }
        }

        return [
            'front_pressure' => [
                'average' => empty($frontPressures) ? 0 : round(array_sum($frontPressures) / count($frontPressures), 1),
                'min' => empty($frontPressures) ? 0 : min($frontPressures),
                'max' => empty($frontPressures) ? 0 : max($frontPressures),
                'count' => count($frontPressures)
            ],
            'rear_pressure' => [
                'average' => empty($rearPressures) ? 0 : round(array_sum($rearPressures) / count($rearPressures), 1),
                'min' => empty($rearPressures) ? 0 : min($rearPressures),
                'max' => empty($rearPressures) ? 0 : max($rearPressures),
                'count' => count($rearPressures)
            ]
        ];
    }

    /**
     * Analisar completude dos dados
     */
    protected function analyzeDataCompleteness($query): array
    {
        $total = $query->count();
        
        return [
            'pressure_specifications' => [
                'complete' => $query->clone()->whereNotNull('pressure_specifications')->count(),
                'percentage' => $total > 0 ? round(($query->clone()->whereNotNull('pressure_specifications')->count() / $total) * 100, 1) : 0
            ],
            'vehicle_features' => [
                'complete' => $query->clone()->whereNotNull('vehicle_features')->count(),
                'percentage' => $total > 0 ? round(($query->clone()->whereNotNull('vehicle_features')->count() / $total) * 100, 1) : 0
            ],
            'tire_specifications' => [
                'complete' => $query->clone()->whereNotNull('tire_specifications')->count(),
                'percentage' => $total > 0 ? round(($query->clone()->whereNotNull('tire_specifications')->count() / $total) * 100, 1) : 0
            ]
        ];
    }

    /**
     * Analisar artigos fonte
     */
    protected function analyzeSourceArticles($query): array
    {
        $vehicles = $query->clone()->whereNotNull('source_articles')->get();
        
        $sourceCount = [];
        foreach ($vehicles as $vehicle) {
            $sources = $vehicle->source_articles ?? [];
            $count = count($sources);
            $sourceCount[$count] = ($sourceCount[$count] ?? 0) + 1;
        }

        return [
            'with_sources' => $vehicles->count(),
            'without_sources' => $query->count() - $vehicles->count(),
            'sources_per_vehicle' => $sourceCount,
            'average_sources' => $vehicles->isEmpty() ? 0 : round($vehicles->sum(fn($v) => count($v->source_articles ?? [])) / $vehicles->count(), 1)
        ];
    }

    /**
     * Analisar distribuição geográfica (placeholder)
     */
    protected function analyzeGeographicDistribution($query): array
    {
        // Por enquanto, análise simples baseada em marcas
        $makeOrigins = [
            'Toyota' => 'Japão',
            'Honda' => 'Japão',
            'Ford' => 'EUA',
            'Chevrolet' => 'EUA',
            'Volkswagen' => 'Alemanha',
            'BMW' => 'Alemanha',
            'Mercedes-Benz' => 'Alemanha',
            'Hyundai' => 'Coreia do Sul',
            'Kia' => 'Coreia do Sul',
            'BYD' => 'China',
            'GWM' => 'China'
        ];

        $makes = $query->clone()->pluck('make')->toArray();
        $origins = [];

        foreach ($makes as $make) {
            $origin = $makeOrigins[$make] ?? 'Outros';
            $origins[$origin] = ($origins[$origin] ?? 0) + 1;
        }

        return $origins;
    }

    /**
     * Gerar análise de tendências
     */
    protected function generateTrendsAnalysis(?string $make, ?string $category): array
    {
        $currentYear = now()->year;
        $years = range($currentYear - 10, $currentYear);

        $yearlyData = [];
        foreach ($years as $year) {
            $query = VehicleData::byYear($year);
            if ($make) $query->byMake($make);
            if ($category) $query->byCategory($category);
            
            $yearlyData[$year] = [
                'total' => $query->count(),
                'electric' => $query->clone()->where('is_electric', true)->count(),
                'premium' => $query->clone()->where('is_premium', true)->count(),
                'with_tpms' => $query->clone()->where('has_tpms', true)->count()
            ];
        }

        return [
            'yearly_trends' => $yearlyData,
            'growth_analysis' => $this->calculateGrowthRates($yearlyData),
            'technology_adoption' => $this->analyzeTechnologyAdoption($yearlyData)
        ];
    }

    /**
     * Calcular taxas de crescimento
     */
    protected function calculateGrowthRates(array $yearlyData): array
    {
        $years = array_keys($yearlyData);
        $growthRates = [];

        for ($i = 1; $i < count($years); $i++) {
            $currentYear = $years[$i];
            $previousYear = $years[$i - 1];
            
            $current = $yearlyData[$currentYear]['total'];
            $previous = $yearlyData[$previousYear]['total'];
            
            if ($previous > 0) {
                $growthRate = (($current - $previous) / $previous) * 100;
                $growthRates[$currentYear] = round($growthRate, 1);
            }
        }

        return $growthRates;
    }

    /**
     * Analisar adoção de tecnologias
     */
    protected function analyzeTechnologyAdoption(array $yearlyData): array
    {
        $adoption = [];
        
        foreach ($yearlyData as $year => $data) {
            if ($data['total'] > 0) {
                $adoption[$year] = [
                    'electric_percentage' => round(($data['electric'] / $data['total']) * 100, 1),
                    'premium_percentage' => round(($data['premium'] / $data['total']) * 100, 1),
                    'tpms_percentage' => round(($data['with_tpms'] / $data['total']) * 100, 1)
                ];
            }
        }

        return $adoption;
    }

    /**
     * Exibir estatísticas
     */
    protected function displayStats(array $stats, string $format): void
    {
        if ($format === 'json') {
            $this->line(json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return;
        }

        // Exibir em formato tabular amigável
        $this->info("\n📊 ESTATÍSTICAS DE VEÍCULOS\n");
        
        // Overview
        $overview = $stats['overview'];
        $this->info("📋 VISÃO GERAL:");
        $this->line("   Total de veículos: {$overview['total_vehicles']}");
        $this->line("   Gerado em: {$overview['generated_at']}");
        
        if (!empty($overview['filters_applied'])) {
            $filters = array_filter($overview['filters_applied']);
            if (!empty($filters)) {
                $this->line("   Filtros: " . implode(', ', array_map(fn($k, $v) => "$k=$v", array_keys($filters), $filters)));
            }
        }

        // Por categoria
        $this->info("\n📂 POR CATEGORIA:");
        foreach ($stats['by_category']['distribution'] as $category => $count) {
            $this->line("   • {$category}: {$count}");
        }

        // Por segmento
        $this->info("\n🎯 POR SEGMENTO:");
        foreach ($stats['by_segment']['distribution'] as $segment => $count) {
            $this->line("   • Segmento {$segment}: {$count}");
        }

        // Top marcas
        $this->info("\n🏭 TOP 10 MARCAS:");
        $topMakes = array_slice($stats['by_make']['top_20_makes'], 0, 10, true);
        foreach ($topMakes as $make => $count) {
            $this->line("   • {$make}: {$count}");
        }

        // Características
        $this->info("\n✨ CARACTERÍSTICAS:");
        foreach ($stats['features'] as $feature => $count) {
            $featureName = ucfirst(str_replace('_', ' ', $feature));
            $this->line("   • {$featureName}: {$count}");
        }

        // Qualidade
        if (isset($stats['quality']['average_score'])) {
            $this->info("\n📈 QUALIDADE:");
            $this->line("   • Score médio: {$stats['quality']['average_score']}/10");
            $this->line("   • Score mínimo: {$stats['quality']['min_score']}");
            $this->line("   • Score máximo: {$stats['quality']['max_score']}");
            
            $dist = $stats['quality']['score_distribution'];
            $this->line("   • Excelente (≥9): {$dist['excellent']}");
            $this->line("   • Bom (7-8.9): {$dist['good']}");
            $this->line("   • Regular (5-6.9): {$dist['fair']}");
            $this->line("   • Ruim (<5): {$dist['poor']}");
        }
    }

    /**
     * Exportar estatísticas
     */
    protected function exportStats(array $stats, string $format, string $filename): void
    {
        $this->info("💾 Exportando para: {$filename}");
        
        $content = '';
        switch ($format) {
            case 'json':
                $content = json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                break;
            case 'csv':
                $content = $this->convertToCSV($stats);
                break;
            default:
                $content = $this->convertToTable($stats);
        }
        
        file_put_contents($filename, $content);
        $this->info("✅ Arquivo exportado com sucesso!");
    }

    /**
     * Converter para CSV
     */
    protected function convertToCSV(array $stats): string
    {
        $csv = "Categoria,Valor\n";
        
        // Flatten basic stats
        foreach ($stats['by_category']['distribution'] as $category => $count) {
            $csv .= "Categoria {$category},{$count}\n";
        }
        
        return $csv;
    }

    /**
     * Converter para tabela
     */
    protected function convertToTable(array $stats): string
    {
        return print_r($stats, true);
    }

    // Métodos utilitários
    protected function getMostCommon(array $data): ?string
    {
        if (empty($data)) return null;
        return array_keys($data, max($data))[0];
    }

    protected function getLeastCommon(array $data): ?string
    {
        if (empty($data)) return null;
        return array_keys($data, min($data))[0];
    }

    protected function getMedian(array $numbers): float
    {
        sort($numbers);
        $count = count($numbers);
        $middle = floor($count / 2);
        
        if ($count % 2 === 0) {
            return ($numbers[$middle - 1] + $numbers[$middle]) / 2;
        }
        
        return $numbers[$middle];
    }
}
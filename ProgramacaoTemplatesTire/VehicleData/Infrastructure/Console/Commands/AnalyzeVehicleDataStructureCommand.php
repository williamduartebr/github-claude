<?php

namespace Src\VehicleData\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * Command para analisar estrutura e dados do VehicleData no MongoDB
 * 
 * Gera relatório detalhado da estrutura atual dos dados,
 * campos vazios, preenchidos e estatísticas por marca/categoria
 */
class AnalyzeVehicleDataStructureCommand extends Command
{
    protected $signature = 'vehicle-data:analyze-structure
                           {--limit=1000 : Limite de documentos para análise}
                           {--sample-size=10 : Tamanho da amostra para inspeção detalhada}
                           {--output-file= : Arquivo para salvar relatório (opcional)}';

    protected $description = 'Analisar estrutura atual dos dados no VehicleData (MongoDB)';

    protected array $analysis = [];
    protected int $totalDocuments = 0;
    protected array $fieldAnalysis = [];
    protected array $sampleDocuments = [];

    public function handle(): int
    {
        $this->info('🔍 ANALISANDO ESTRUTURA DO VEHICLE DATA (MongoDB)');
        $this->newLine();

        $limit = (int) $this->option('limit');
        $sampleSize = (int) $this->option('sample-size');
        $outputFile = $this->option('output-file');

        try {
            // 1. Análise básica de contadores
            $this->analyzeBasicStats();

            // 2. Análise de estrutura de campos
            $this->analyzeFieldStructure($limit);

            // 3. Análise por marca e categoria
            $this->analyzeByMakeAndCategory();

            // 4. Amostra detalhada de documentos
            $this->analyzeSampleDocuments($sampleSize);

            // 5. Análise de campos vazios vs preenchidos
            $this->analyzeFieldCompleteness();

            // 6. Análise de qualidade dos dados
            $this->analyzeDataQuality();

            // 7. Gerar relatório
            $this->generateReport($outputFile);

            $this->info('✅ Análise concluída! Verifique os logs para o relatório completo.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ ERRO: ' . $e->getMessage());
            Log::error('AnalyzeVehicleDataStructureCommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Análise básica de estatísticas
     */
    protected function analyzeBasicStats(): void
    {
        $this->info('📊 Coletando estatísticas básicas...');

        $this->totalDocuments = VehicleData::count();
        
        $this->analysis['basic_stats'] = [
            'total_documents' => $this->totalDocuments,
            'collection_name' => 'vehicle_data',
            'database_type' => 'MongoDB',
            'analysis_timestamp' => now()->toISOString(),
        ];

        // Estatísticas por marca
        $byMake = VehicleData::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => '$make',
                    'count' => ['$sum' => 1]
                ]],
                ['$sort' => ['count' => -1]],
                ['$limit' => 20]
            ]);
        });

        $this->analysis['basic_stats']['top_makes'] = $byMake->toArray();

        // Estatísticas por categoria
        $byCategory = VehicleData::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => '$main_category',
                    'count' => ['$sum' => 1]
                ]],
                ['$sort' => ['count' => -1]]
            ]);
        });

        $this->analysis['basic_stats']['by_category'] = $byCategory->toArray();

        $this->line("  Total de documentos: {$this->totalDocuments}");
    }

    /**
     * Análise detalhada da estrutura de campos
     */
    protected function analyzeFieldStructure(int $limit): void
    {
        $this->info('🔍 Analisando estrutura de campos...');

        $documents = VehicleData::limit($limit)->get();
        $fieldStats = [];

        foreach ($documents as $doc) {
            $this->analyzeDocumentFields($doc->toArray(), '', $fieldStats);
        }

        // Calcular percentuais de preenchimento
        foreach ($fieldStats as $field => &$stats) {
            $stats['fill_percentage'] = round(($stats['filled'] / $this->totalDocuments) * 100, 2);
            $stats['empty_percentage'] = round(($stats['empty'] / $this->totalDocuments) * 100, 2);
        }

        $this->fieldAnalysis = $fieldStats;
        $this->analysis['field_structure'] = $fieldStats;
    }

    /**
     * Analisar campos de um documento recursivamente
     */
    protected function analyzeDocumentFields(array $data, string $prefix, array &$fieldStats): void
    {
        foreach ($data as $key => $value) {
            $fieldPath = $prefix ? "{$prefix}.{$key}" : $key;

            if (!isset($fieldStats[$fieldPath])) {
                $fieldStats[$fieldPath] = [
                    'type' => gettype($value),
                    'filled' => 0,
                    'empty' => 0,
                    'sample_values' => [],
                ];
            }

            // Determinar se o campo está preenchido
            $isEmpty = $this->isFieldEmpty($value);
            
            if ($isEmpty) {
                $fieldStats[$fieldPath]['empty']++;
            } else {
                $fieldStats[$fieldPath]['filled']++;
                
                // Coletar valores de amostra
                if (count($fieldStats[$fieldPath]['sample_values']) < 5) {
                    $sampleValue = is_array($value) ? '[array]' : (string) $value;
                    if (strlen($sampleValue) > 100) {
                        $sampleValue = substr($sampleValue, 0, 100) . '...';
                    }
                    $fieldStats[$fieldPath]['sample_values'][] = $sampleValue;
                }
            }

            // Se for array e não estiver vazio, analisar recursivamente
            if (is_array($value) && !empty($value) && !$this->isSimpleArray($value)) {
                $this->analyzeDocumentFields($value, $fieldPath, $fieldStats);
            }
        }
    }

    /**
     * Verificar se um campo está vazio
     */
    protected function isFieldEmpty($value): bool
    {
        if (is_null($value)) return true;
        if ($value === '') return true;
        if (is_array($value) && empty($value)) return true;
        if (is_string($value) && trim($value) === '') return true;
        
        return false;
    }

    /**
     * Verificar se é um array simples (não associativo)
     */
    protected function isSimpleArray($value): bool
    {
        if (!is_array($value)) return false;
        return array_keys($value) === range(0, count($value) - 1);
    }

    /**
     * Análise por marca e categoria
     */
    protected function analyzeByMakeAndCategory(): void
    {
        $this->info('📋 Analisando por marca e categoria...');

        // Análise detalhada por marca
        $makeAnalysis = VehicleData::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => '$make',
                    'count' => ['$sum' => 1],
                    'avg_quality_score' => ['$avg' => '$data_quality_score'],
                    'categories' => ['$addToSet' => '$main_category'],
                    'year_range' => [
                        '$push' => '$year'
                    ]
                ]],
                ['$sort' => ['count' => -1]]
            ]);
        });

        foreach ($makeAnalysis as &$make) {
            if (isset($make['year_range'])) {
                $years = array_filter($make['year_range']);
                $make['min_year'] = !empty($years) ? min($years) : null;
                $make['max_year'] = !empty($years) ? max($years) : null;
                unset($make['year_range']);
            }
        }

        $this->analysis['by_make_detailed'] = $makeAnalysis->toArray();

        // Análise de preenchimento de campos por categoria
        $categoryFieldAnalysis = VehicleData::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => '$main_category',
                    'count' => ['$sum' => 1],
                    'engine_data_filled' => [
                        '$sum' => [
                            '$cond' => [
                                ['$and' => [
                                    ['$ne' => ['$engine_data', null]],
                                    ['$ne' => ['$engine_data', []]]
                                ]],
                                1, 0
                            ]
                        ]
                    ],
                    'transmission_data_filled' => [
                        '$sum' => [
                            '$cond' => [
                                ['$and' => [
                                    ['$ne' => ['$transmission_data', null]],
                                    ['$ne' => ['$transmission_data', []]]
                                ]],
                                1, 0
                            ]
                        ]
                    ],
                    'fuel_data_filled' => [
                        '$sum' => [
                            '$cond' => [
                                ['$and' => [
                                    ['$ne' => ['$fuel_data', null]],
                                    ['$ne' => ['$fuel_data', []]]
                                ]],
                                1, 0
                            ]
                        ]
                    ],
                    'dimensions_filled' => [
                        '$sum' => [
                            '$cond' => [
                                ['$and' => [
                                    ['$ne' => ['$dimensions', null]],
                                    ['$ne' => ['$dimensions', []]]
                                ]],
                                1, 0
                            ]
                        ]
                    ]
                ]]
            ]);
        });

        $this->analysis['field_completeness_by_category'] = $categoryFieldAnalysis->toArray();
    }

    /**
     * Analisar amostra detalhada de documentos
     */
    protected function analyzeSampleDocuments(int $sampleSize): void
    {
        $this->info('🎯 Coletando amostra detalhada...');

        // Amostra de diferentes categorias
        $samples = [];

        $categories = VehicleData::distinct('main_category');
        foreach ($categories as $category) {
            $sample = VehicleData::where('main_category', $category)
                ->orderByDesc('data_quality_score')
                ->limit(2)
                ->get()
                ->toArray();
            
            if (!empty($sample)) {
                $samples[$category] = $sample;
            }
        }

        $this->sampleDocuments = $samples;
        $this->analysis['sample_documents'] = $samples;
    }

    /**
     * Análise de completude de campos
     */
    protected function analyzeFieldCompleteness(): void
    {
        $this->info('📈 Analisando completude de campos...');

        $completeness = [
            'critical_fields' => [],
            'empty_fields' => [],
            'partially_filled' => [],
            'well_filled' => []
        ];

        // Campos críticos para análise
        $criticalFields = [
            'make', 'model', 'year', 'main_category',
            'engine_data', 'transmission_data', 'fuel_data', 'dimensions',
            'technical_specs', 'market_data', 'pressure_specifications'
        ];

        foreach ($this->fieldAnalysis as $field => $stats) {
            $fillPercentage = $stats['fill_percentage'];
            
            if (in_array($field, $criticalFields)) {
                $completeness['critical_fields'][$field] = $fillPercentage;
            }

            if ($fillPercentage == 0) {
                $completeness['empty_fields'][] = $field;
            } elseif ($fillPercentage < 50) {
                $completeness['partially_filled'][$field] = $fillPercentage;
            } elseif ($fillPercentage >= 90) {
                $completeness['well_filled'][$field] = $fillPercentage;
            }
        }

        $this->analysis['field_completeness'] = $completeness;
    }

    /**
     * Análise de qualidade dos dados
     */
    protected function analyzeDataQuality(): void
    {
        $this->info('⭐ Analisando qualidade dos dados...');

        $qualityStats = VehicleData::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => null,
                    'avg_quality_score' => ['$avg' => '$data_quality_score'],
                    'min_quality_score' => ['$min' => '$data_quality_score'],
                    'max_quality_score' => ['$max' => '$data_quality_score'],
                    'total_with_score' => [
                        '$sum' => [
                            '$cond' => [
                                ['$ne' => ['$data_quality_score', null]],
                                1, 0
                            ]
                        ]
                    ]
                ]]
            ]);
        });

        // Distribuição por faixas de qualidade
        $qualityDistribution = VehicleData::raw(function ($collection) {
            return $collection->aggregate([
                ['$bucket' => [
                    'groupBy' => '$data_quality_score',
                    'boundaries' => [0, 3, 5, 7, 8.5, 10],
                    'default' => 'no_score',
                    'output' => [
                        'count' => ['$sum' => 1],
                        'avg_score' => ['$avg' => '$data_quality_score']
                    ]
                ]]
            ]);
        });

        $this->analysis['data_quality'] = [
            'overall_stats' => $qualityStats->first(),
            'score_distribution' => $qualityDistribution->toArray()
        ];
    }

    /**
     * Gerar relatório completo
     */
    protected function generateReport(?string $outputFile): void
    {
        $report = [
            'analysis_metadata' => [
                'command' => 'vehicle-data:analyze-structure',
                'executed_at' => now()->toISOString(),
                'laravel_version' => app()->version(),
                'total_documents_analyzed' => $this->totalDocuments
            ],
            'analysis_results' => $this->analysis
        ];

        $reportJson = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Log completo
        Log::channel('single')->info('=== VEHICLE DATA STRUCTURE ANALYSIS REPORT ===');
        Log::channel('single')->info($reportJson);
        Log::channel('single')->info('=== END OF VEHICLE DATA ANALYSIS REPORT ===');

        // Salvar em arquivo se especificado
        if ($outputFile) {
            file_put_contents($outputFile, $reportJson);
            $this->info("📄 Relatório salvo em: {$outputFile}");
        }

        // Exibir resumo no console
        $this->displaySummary();
    }

    /**
     * Exibir resumo no console
     */
    protected function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== RESUMO DA ANÁLISE ===');
        $this->newLine();

        $this->line("📊 <fg=cyan>Total de documentos:</> {$this->totalDocuments}");
        
        if (isset($this->analysis['basic_stats']['top_makes'])) {
            $topMake = $this->analysis['basic_stats']['top_makes'][0] ?? null;
            if ($topMake) {
                $this->line("🏭 <fg=cyan>Marca com mais modelos:</> {$topMake['id']} ({$topMake['count']} modelos)");
            }
        }

        if (isset($this->analysis['field_completeness']['empty_fields'])) {
            $emptyCount = count($this->analysis['field_completeness']['empty_fields']);
            $this->line("❌ <fg=red>Campos completamente vazios:</> {$emptyCount}");
        }

        if (isset($this->analysis['field_completeness']['well_filled'])) {
            $wellFilledCount = count($this->analysis['field_completeness']['well_filled']);
            $this->line("✅ <fg=green>Campos bem preenchidos (>90%):</> {$wellFilledCount}");
        }

        if (isset($this->analysis['data_quality']['overall_stats']['avg_quality_score'])) {
            $avgQuality = round($this->analysis['data_quality']['overall_stats']['avg_quality_score'], 2);
            $this->line("⭐ <fg=yellow>Qualidade média dos dados:</> {$avgQuality}/10");
        }

        $this->newLine();
        $this->info('📋 Relatório completo disponível nos logs (canal: single)');
        $this->info('🔍 Use "tail -f storage/logs/laravel.log | grep VEHICLE" para acompanhar');
    }
}
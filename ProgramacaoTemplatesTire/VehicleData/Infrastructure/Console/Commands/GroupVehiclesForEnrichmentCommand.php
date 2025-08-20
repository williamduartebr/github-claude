<?php

namespace Src\VehicleData\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Src\VehicleData\Domain\Entities\VehicleData;
use Src\VehicleData\Domain\Entities\VehicleEnrichmentGroup;

/**
 * Command para agrupar veículos e identificar representantes para enrichment
 * 
 * Analisa os 963 veículos e identifica grupos únicos (make+model+generation)
 * para otimizar o uso da API Claude, reduzindo chamadas de 963 para ~200
 */
class GroupVehiclesForEnrichmentCommand extends Command
{
    protected $signature = 'vehicle-data:group-for-enrichment
                           {--make= : Agrupar apenas uma marca específica}
                           {--max-year-diff=5 : Diferença máxima de anos para mesmo grupo}
                           {--dry-run : Simular agrupamento sem salvar dados}
                           {--force : Recriar grupos mesmo se já existirem}
                           {--output-file= : Salvar resultado em arquivo}';

    protected $description = 'Agrupar veículos e identificar representantes para enrichment via Claude API';

    protected int $totalVehicles = 0;
    protected int $totalGroups = 0;
    protected int $potentialApiCalls = 0;
    protected array $groupingResults = [];

    public function handle(): int
    {
        $this->info('🔍 AGRUPANDO VEÍCULOS PARA ENRICHMENT OTIMIZADO');
        $this->newLine();

        $make = $this->option('make');
        $maxYearDiff = (int) $this->option('max-year-diff');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $outputFile = $this->option('output-file');

        try {
            // Verificar se já existem grupos e --force não foi usado
            if (!$force && !$dryRun) {
                $existingGroups = VehicleEnrichmentGroup::count();
                if ($existingGroups > 0) {
                    $this->warn("⚠️  Já existem {$existingGroups} grupos cadastrados!");
                    if (!$this->confirm('Deseja limpar e recriar todos os grupos?')) {
                        $this->info('Operação cancelada. Use --force para sobrescrever automaticamente.');
                        return Command::SUCCESS;
                    }
                    $force = true;
                }
            }

            // Limpar grupos existentes se --force
            if ($force && !$dryRun) {
                $this->clearExistingGroups($make);
            }
            // Análise inicial
            $this->displayInitialAnalysis($make);

            // Executar agrupamento
            $groups = $this->groupVehiclesByModel($make, $maxYearDiff);

            // Identificar representantes
            $representatives = $this->identifyRepresentatives($groups);

            // Analisar economia
            $this->analyzeOptimization($representatives);

            // Salvar resultados
            if (!$dryRun) {
                $this->saveGroupsToDatabase($representatives);
            }

            // Exportar se solicitado
            if ($outputFile) {
                $this->exportResults($representatives, $outputFile);
            }

            // Exibir resultados
            $this->displayResults($dryRun);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ ERRO: ' . $e->getMessage());
            Log::error('GroupVehiclesForEnrichmentCommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Limpar grupos existentes
     */
    protected function clearExistingGroups(?string $make): void
    {
        $this->info('🗑️ Limpando grupos existentes...');

        $query = VehicleEnrichmentGroup::query();
        
        if ($make) {
            $query->where('make', $make);
        }

        $deletedCount = $query->delete();
        $this->line("   Removidos: {$deletedCount} grupos");
    }

    /**
     * Análise inicial dos dados
     */
    protected function displayInitialAnalysis(?string $make): void
    {
        $query = VehicleData::query();
        
        if ($make) {
            $query->where('make', $make);
        }

        $this->totalVehicles = $query->count();
        
        $this->info("📊 ANÁLISE INICIAL:");
        $this->line("   Total de veículos: {$this->totalVehicles}");
        
        if ($make) {
            $this->line("   Filtro: {$make}");
        }

        // Estatísticas por categoria
        $byCategory = $query->get()->groupBy('main_category')->map->count();
        $this->line("   Por categoria:");
        foreach ($byCategory->take(5) as $category => $count) {
            $this->line("      • {$category}: {$count}");
        }

        $this->newLine();
    }

    /**
     * Agrupar veículos por modelo e geração
     */
    protected function groupVehiclesByModel(?string $make, int $maxYearDiff): \Illuminate\Support\Collection
    {
        $this->info('🔄 Executando agrupamento inteligente...');

        // Usar Eloquent ao invés de agregação raw para evitar problemas de campo
        $query = VehicleData::query();
        
        if ($make) {
            $query->where('make', $make);
        }

        $vehicles = $query->get();

        // Agrupar manualmente por make, model, main_category
        $groups = $vehicles->groupBy(function ($vehicle) {
            return $vehicle->make . '|' . $vehicle->model . '|' . $vehicle->main_category;
        });

        $processedGroups = collect();

        foreach ($groups as $key => $vehicleGroup) {
            $keyParts = explode('|', $key);
            $groupId = [
                'make' => $keyParts[0],
                'model' => $keyParts[1],
                'main_category' => $keyParts[2]
            ];

            $vehicleArrays = $vehicleGroup->map(function ($vehicle) {
                return $vehicle->toArray();
            })->toArray();

            $rawGroup = [
                '_id' => $groupId,
                'vehicles' => $vehicleArrays,
                'count' => $vehicleGroup->count(),
                'years' => $vehicleGroup->pluck('year')->toArray(),
                'min_year' => $vehicleGroup->min('year'),
                'max_year' => $vehicleGroup->max('year')
            ];

            $subGroups = $this->splitByGeneration($rawGroup, $maxYearDiff);
            $processedGroups = $processedGroups->merge($subGroups);
        }

        $this->totalGroups = $processedGroups->count();
        $this->line("   Grupos identificados: {$this->totalGroups}");

        return $processedGroups;
    }

    /**
     * Dividir grupo por gerações (baseado na diferença de anos)
     */
    protected function splitByGeneration(array $rawGroup, int $maxYearDiff): array
    {
        // Garantir que vehicles seja um array de arrays
        $vehiclesData = $rawGroup['vehicles'] ?? [];
        $vehicles = collect($vehiclesData)->map(function ($vehicle) {
            return is_array($vehicle) ? $vehicle : $vehicle->toArray();
        })->sortBy('year');
        
        $subGroups = [];
        $currentGroup = [];
        $lastYear = null;

        foreach ($vehicles as $vehicle) {
            $currentYear = $vehicle['year'];

            // Se é o primeiro veículo ou a diferença é aceitável
            if ($lastYear === null || ($currentYear - $lastYear) <= $maxYearDiff) {
                $currentGroup[] = $vehicle;
            } else {
                // Criar novo subgrupo (nova geração detectada)
                if (!empty($currentGroup)) {
                    $subGroups[] = $this->createSubGroup($rawGroup['_id'], $currentGroup);
                }
                $currentGroup = [$vehicle];
            }

            $lastYear = $currentYear;
        }

        // Adicionar último grupo
        if (!empty($currentGroup)) {
            $subGroups[] = $this->createSubGroup($rawGroup['_id'], $currentGroup);
        }

        return $subGroups;
    }

    /**
     * Criar subgrupo estruturado
     */
    protected function createSubGroup(array $groupId, array $vehicles): array
    {
        $years = array_column($vehicles, 'year');
        
        return [
            '_id' => $groupId, // Manter como _id para compatibilidade
            'vehicles' => $vehicles,
            'count' => count($vehicles),
            'years' => $years,
            'min_year' => min($years),
            'max_year' => max($years),
            'year_span' => max($years) - min($years),
            'generation_key' => $this->generateGenerationKey($groupId, $years)
        ];
    }

    /**
     * Gerar chave única para geração
     */
    protected function generateGenerationKey(array $groupId, array $years): string
    {
        $make = $groupId['make'];
        $model = $groupId['model'];
        $category = $groupId['main_category'];
        $minYear = min($years);
        $maxYear = max($years);

        return sprintf('%s_%s_%s_%d-%d', 
            $make, $model, $category, $minYear, $maxYear
        );
    }

    /**
     * Identificar representantes de cada grupo
     */
    protected function identifyRepresentatives(\Illuminate\Support\Collection $groups): array
    {
        $this->info('🎯 Identificando representantes para API...');

        $representatives = [];

        foreach ($groups as $group) {
            $representative = $this->chooseGroupRepresentative($group);
            
            if ($representative) {
                $representatives[] = [
                    'group_info' => [
                        'generation_key' => $group['generation_key'],
                        'make' => $group['_id']['make'],
                        'model' => $group['_id']['model'],
                        'category' => $group['_id']['main_category'],
                        'year_span' => "{$group['min_year']}-{$group['max_year']}",
                        'vehicle_count' => $group['count']
                    ],
                    'representative' => $representative,
                    'siblings' => array_filter($group['vehicles'], function($v) use ($representative) {
                        // Usar 'id' se '_id' não existir
                        $vId = $v['_id'] ?? $v['id'] ?? null;
                        $repId = $representative['_id'] ?? $representative['id'] ?? null;
                        return $vId !== $repId;
                    })
                ];
            }
        }

        $this->potentialApiCalls = count($representatives);
        $this->line("   Representantes selecionados: {$this->potentialApiCalls}");

        return $representatives;
    }

    /**
     * Escolher melhor representante do grupo
     */
    protected function chooseGroupRepresentative(array $group): ?array
    {
        $vehicles = collect($group['vehicles'])->map(function ($vehicle) {
            return is_array($vehicle) ? $vehicle : $vehicle->toArray();
        });

        if ($vehicles->isEmpty()) {
            return null;
        }

        // Critérios de priorização (em ordem de importância)
        $scored = $vehicles->map(function ($vehicle) {
            $score = 0;

            // 1. Preferir anos mais recentes (dados mais atualizados)
            $score += $vehicle['year'] * 10;

            // 2. Preferir anos "redondos" (2020, 2025) - mais documentados
            if ($vehicle['year'] % 5 === 0) {
                $score += 50;
            }

            // 3. Preferir qualidade de dados mais alta
            $score += ($vehicle['data_quality_score'] ?? 0) * 20;

            // 4. Preferir veículos com mais dados preenchidos
            $score += $this->calculateFieldCompleteness($vehicle) * 5;

            // 5. Bonus para categorias específicas bem documentadas
            if (in_array($vehicle['main_category'], ['hatch', 'sedan', 'suv'])) {
                $score += 25;
            }

            return [
                'vehicle' => $vehicle,
                'score' => $score
            ];
        });

        return $scored->sortByDesc('score')->first()['vehicle'];
    }

    /**
     * Calcular completude dos campos existentes
     */
    protected function calculateFieldCompleteness(array $vehicle): int
    {
        $score = 0;

        // Campos básicos já preenchidos
        $basicFields = ['make', 'model', 'year', 'tire_size', 'main_category'];
        foreach ($basicFields as $field) {
            if (!empty($vehicle[$field])) {
                $score += 2;
            }
        }

        // Dados de pressão completos
        if (!empty($vehicle['pressure_specifications'])) {
            $pressureFields = ['pressure_light_front', 'pressure_light_rear', 'pressure_max_front', 'pressure_max_rear'];
            foreach ($pressureFields as $field) {
                if (!empty($vehicle['pressure_specifications'][$field])) {
                    $score += 3;
                }
            }
        }

        // Features do veículo
        if (!empty($vehicle['vehicle_features']['recommended_oil'])) {
            $score += 5;
        }

        return $score;
    }

    /**
     * Analisar otimização obtida
     */
    protected function analyzeOptimization(array $representatives): void
    {
        $this->info('📈 ANÁLISE DE OTIMIZAÇÃO:');
        
        $originalCalls = $this->totalVehicles;
        $optimizedCalls = $this->potentialApiCalls;
        $reduction = $originalCalls - $optimizedCalls;
        $reductionPercent = round(($reduction / $originalCalls) * 100, 1);

        $this->line("   Cenário original: {$originalCalls} chamadas API");
        $this->line("   Cenário otimizado: {$optimizedCalls} chamadas API");
        $this->line("   Redução: {$reduction} chamadas ({$reductionPercent}%)");

        // Cálculo de custo estimado
        $originalCost = $originalCalls * 0.017; // ~$0.017 por chamada
        $optimizedCost = $optimizedCalls * 0.017;
        $savings = $originalCost - $optimizedCost;

        $this->line("   Custo estimado original: $" . number_format($originalCost, 2));
        $this->line("   Custo estimado otimizado: $" . number_format($optimizedCost, 2));
        $this->line("   Economia estimada: $" . number_format($savings, 2));

        // Tempo estimado (com rate limit de 2 minutos)
        $originalTime = ($originalCalls * 2) / 60; // horas
        $optimizedTime = ($optimizedCalls * 2) / 60; // horas

        $this->line("   Tempo estimado original: " . number_format($originalTime, 1) . "h");
        $this->line("   Tempo estimado otimizado: " . number_format($optimizedTime, 1) . "h");
        
        $this->newLine();
    }

    /**
     * Salvar grupos no banco de dados
     */
    protected function saveGroupsToDatabase(array $representatives): void
    {
        $this->info('💾 Salvando grupos no banco de dados...');

        $savedCount = 0;
        $errorCount = 0;

        foreach ($representatives as $groupData) {
            try {
                // Log dados do grupo para debug
                Log::debug('Tentando salvar grupo', [
                    'generation_key' => $groupData['group_info']['generation_key'] ?? 'missing',
                    'make' => $groupData['group_info']['make'] ?? 'missing',
                    'model' => $groupData['group_info']['model'] ?? 'missing',
                    'representative_id' => $groupData['representative']['id'] ?? $groupData['representative']['_id'] ?? 'missing'
                ]);

                VehicleEnrichmentGroup::createGroup($groupData);
                $savedCount++;
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('Erro ao salvar grupo', [
                    'group' => $groupData['group_info']['generation_key'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Mostrar primeiro erro no console para debug
                if ($errorCount === 1) {
                    $this->warn("   Primeiro erro: " . $e->getMessage());
                }
            }
        }

        $this->line("   Grupos salvos: {$savedCount}");
        if ($errorCount > 0) {
            $this->warn("   Erros: {$errorCount}");
            $this->warn("   Verifique logs para detalhes: tail -f storage/logs/laravel.log");
        }
    }

    /**
     * Salvar resultados no cache (DEPRECATED - mantido para compatibilidade)
     */
    protected function saveGroupingResults(array $representatives): void
    {
        // Método mantido para compatibilidade, mas agora salva no banco
        $this->saveGroupsToDatabase($representatives);
        
        // Também salvar no cache como backup
        $cacheKey = 'vehicle_enrichment_groups_backup';
        $cacheData = [
            'generated_at' => now()->toISOString(),
            'total_vehicles' => $this->totalVehicles,
            'total_groups' => $this->totalGroups,
            'representatives_count' => $this->potentialApiCalls,
            'representatives' => $representatives
        ];

        Cache::put($cacheKey, $cacheData, now()->addDays(7));
    }

    /**
     * Exportar resultados para arquivo
     */
    protected function exportResults(array $representatives, string $outputFile): void
    {
        $exportData = [
            'metadata' => [
                'generated_at' => now()->toISOString(),
                'command' => 'vehicle-data:group-for-enrichment',
                'total_vehicles' => $this->totalVehicles,
                'total_groups' => $this->totalGroups,
                'api_calls_needed' => $this->potentialApiCalls,
                'optimization_rate' => round((1 - $this->potentialApiCalls / $this->totalVehicles) * 100, 1) . '%'
            ],
            'representatives' => $representatives
        ];

        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($outputFile, $json);

        $this->info("📄 Resultados exportados para: {$outputFile}");
    }

    /**
     * Exibir resultados finais
     */
    protected function displayResults(bool $dryRun): void
    {
        $this->info('=== RESULTADO DO AGRUPAMENTO ===');
        $this->newLine();

        $this->line("📊 <fg=cyan>Total de veículos analisados:</> {$this->totalVehicles}");
        $this->line("🔄 <fg=cyan>Grupos identificados:</> {$this->totalGroups}");
        $this->line("🎯 <fg=green>Representantes selecionados:</> {$this->potentialApiCalls}");

        $reduction = $this->totalVehicles - $this->potentialApiCalls;
        $reductionPercent = round(($reduction / $this->totalVehicles) * 100, 1);
        $this->line("📉 <fg=yellow>Redução de API calls:</> {$reduction} ({$reductionPercent}%)");

        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 MODO DRY-RUN: Nenhum dado foi salvo');
        } else {
            $this->info('✅ Grupos salvos no banco de dados (vehicle_enrichment_groups)');
        }

        // Exibir estatísticas dos grupos salvos
        if (!$dryRun) {
            $this->displayDatabaseStats();
        }

        $this->newLine();
        $this->info('📋 PRÓXIMOS PASSOS:');
        $this->line('   1. php artisan vehicle-data:enrich-representatives');
        $this->line('   2. php artisan vehicle-data:propagate-from-representatives');

        Log::info('GroupVehiclesForEnrichmentCommand: Execução concluída', [
            'total_vehicles' => $this->totalVehicles,
            'total_groups' => $this->totalGroups,
            'representatives' => $this->potentialApiCalls,
            'reduction_percent' => $reductionPercent
        ]);
    }

    /**
     * Exibir estatísticas do banco de dados
     */
    protected function displayDatabaseStats(): void
    {
        $stats = VehicleEnrichmentGroup::getProcessingStats();
        
        $this->newLine();
        $this->info('📊 ESTATÍSTICAS DO BANCO:');
        $this->line("   Total de grupos: {$stats['total_groups']}");
        $this->line("   Pendentes: {$stats['pending_enrichment']}");
        $this->line("   Por prioridade:");
        
        // Usar método mais simples para evitar erro do MongoDB
        $high = VehicleEnrichmentGroup::where('priority', 'high')->count();
        $medium = VehicleEnrichmentGroup::where('priority', 'medium')->count();
        $low = VehicleEnrichmentGroup::where('priority', 'low')->count();
            
        $this->line("      • high: {$high}");
        $this->line("      • medium: {$medium}");
        $this->line("      • low: {$low}");
    }
}
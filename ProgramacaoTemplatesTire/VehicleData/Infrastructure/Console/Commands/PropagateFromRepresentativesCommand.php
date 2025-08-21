<?php

namespace Src\VehicleData\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\VehicleData\Domain\Entities\VehicleEnrichmentGroup;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * VERSÃO CORRIGIDA - Propagação funcionando corretamente
 * 
 * PROBLEMA ENCONTRADO: Os dados enriquecidos não estavam sendo aplicados 
 * nos veículos irmãos, apenas marcado como "propagado" sem fazer nada.
 */
class PropagateFromRepresentativesCommand extends Command
{
    protected $signature = 'vehicle-data:propagate-from-representatives
                           {--batch-size=20 : Número de grupos por lote}
                           {--priority= : Processar apenas uma prioridade (high/medium/low)}
                           {--make= : Processar apenas uma marca específica}
                           {--dry-run : Simular propagação sem salvar dados}
                           {--force : Reprocessar grupos já propagados}
                           {--limit= : Limite total de grupos para processar}
                           {--debug : Modo debug com logs detalhados}';

    protected $description = 'Propagar dados enriquecidos dos representantes para veículos irmãos';

    protected int $processedGroups = 0;
    protected int $successGroups = 0;
    protected int $errorGroups = 0;
    protected int $skippedGroups = 0;
    protected int $totalVehiclesUpdated = 0;
    protected array $errors = [];
    protected array $propagationRules = [];
    protected bool $debugMode = false;

    public function handle(): int
    {
        $this->info('🔄 INICIANDO PROPAGAÇÃO DE DADOS ENRIQUECIDOS - VERSÃO CORRIGIDA');
        $this->newLine();

        $batchSize = (int) $this->option('batch-size');
        $priority = $this->option('priority');
        $make = $this->option('make');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $this->debugMode = $this->option('debug');

        if ($dryRun) {
            $this->warn('🔍 MODO DRY-RUN: Nenhum dado será salvo');
        }

        if ($this->debugMode) {
            $this->info('🐛 MODO DEBUG: Logs detalhados ativados');
        }

        $this->newLine();

        try {
            // Definir regras de propagação
            $this->definePropagationRules();

            // Exibir estatísticas iniciais
            $this->displayInitialStats($priority, $make, $force);

            // Buscar grupos para propagar
            $groups = $this->getGroupsToPropagate($batchSize, $priority, $make, $force, $limit);

            if ($groups->isEmpty()) {
                $this->info('✅ Nenhum grupo encontrado para propagação');
                return Command::SUCCESS;
            }

            // Processar grupos
            $this->processGroups($groups, $dryRun);

            // Exibir resultados
            $this->displayResults();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ ERRO: ' . $e->getMessage());
            Log::error('PropagateFromRepresentativesCommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Definir regras de propagação por tipo de campo
     */
    protected function definePropagationRules(): void
    {
        $this->propagationRules = [
            // Campos que SEMPRE propagam (raramente mudam entre anos)
            'always_propagate' => [
                'dimensions.length',
                'dimensions.width',
                'dimensions.height',
                'dimensions.wheelbase',
                'technical_specs.suspension_front',
                'technical_specs.suspension_rear',
                'technical_specs.brakes_front',
                'technical_specs.brakes_rear',
                'transmission_data.type',
                'transmission_data.gears',
                'engine_data.engine_type',
                'engine_data.displacement',
                'engine_data.fuel_type',
                'fuel_data.fuel_tank_capacity',
            ],

            // Campos que propagam com AJUSTES graduais
            'propagate_with_adjustment' => [
                'engine_data.horsepower', // +2-3% por ano
                'engine_data.torque', // +2-3% por ano
                'fuel_data.consumption_city', // Melhora 1-2% por ano
                'fuel_data.consumption_highway', // Melhora 1-2% por ano
            ],

            // Campos que NUNCA propagam (sempre únicos por ano)
            'never_propagate' => [
                'market_data.launch_year',
                'market_data.price_range', // Muda muito ano a ano
            ],

            // Campos condicionais (só propagam se anos muito próximos ≤2)
            'conditional_propagate' => [
                'dimensions.weight', // Pode variar levemente
                'technical_specs.max_load',
                'market_data.main_competitors', // Pode mudar com o tempo
            ]
        ];
    }

    /**
     * Exibir estatísticas iniciais
     */
    protected function displayInitialStats(?string $priority, ?string $make, bool $force): void
    {
        $stats = VehicleEnrichmentGroup::getProcessingStats();

        $this->info('📊 ESTATÍSTICAS ATUAIS:');
        $this->line("   Total de grupos: {$stats['total_groups']}");
        $this->line("   Enriquecidos: {$stats['enriched']}");
        $this->line("   Pendentes propagação: {$stats['pending_propagation']}");
        $this->line("   Já propagados: " . ($stats['completed']));

        if ($priority) {
            $priorityCount = VehicleEnrichmentGroup::byPriority($priority)
                ->where('is_enriched', true)->count();
            $this->line("   Filtro prioridade '{$priority}': {$priorityCount}");
        }

        if ($make) {
            $makeCount = VehicleEnrichmentGroup::byMake($make)
                ->where('is_enriched', true)->count();
            $this->line("   Filtro marca '{$make}': {$makeCount}");
        }

        if ($force) {
            $this->warn("   MODO FORCE: Reprocessará grupos já propagados");
        }

        $this->newLine();
    }

    /**
     * Buscar grupos prontos para propagação
     */
    protected function getGroupsToPropagate(
        int $batchSize,
        ?string $priority,
        ?string $make,
        bool $force,
        ?int $limit
    ): \Illuminate\Support\Collection {

        if ($force) {
            // Se force, pegar todos os grupos enriquecidos
            $query = VehicleEnrichmentGroup::where('is_enriched', true);
        } else {
            // Apenas grupos pendentes para propagação
            $query = VehicleEnrichmentGroup::pendingPropagation();
        }

        // Aplicar filtros
        if ($priority) {
            $query->byPriority($priority);
        }

        if ($make) {
            $query->byMake($make);
        }

        // Buscar todos e ordenar na memória
        $groups = $query->get();

        // Ordenar na memória
        $groups = $groups->sortBy([
            function ($group) {
                return match ($group->priority) {
                    'high' => 1,
                    'medium' => 2,
                    'low' => 3,
                    default => 4
                };
            },
            ['enriched_at', 'asc']
        ]);

        // Aplicar limite
        if ($limit) {
            $groups = $groups->take($limit);
        } else {
            $groups = $groups->take($batchSize * 5);
        }

        return $groups;
    }

    /**
     * Processar grupos
     */
    protected function processGroups(\Illuminate\Support\Collection $groups, bool $dryRun): void
    {
        $this->info("🔄 Propagando dados para {$groups->count()} grupos...");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($groups->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');

        foreach ($groups as $group) {
            $repInfo = $group->getRepresentativeInfo();
            $vehicleName = $repInfo['full_name'];
            $siblingCount = $group->sibling_count;

            $progressBar->setMessage("Propagando: {$vehicleName} → {$siblingCount} veículos");

            $this->processGroup($group, $dryRun);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * Processar um grupo individual
     */
    protected function processGroup(VehicleEnrichmentGroup $group, bool $dryRun): void
    {
        try {
            $this->processedGroups++;

            // Verificar se pode tentar novamente
            if (!$group->canRetryPropagation()) {
                $this->skippedGroups++;
                if ($this->debugMode) {
                    $this->warn("Grupo {$group->generation_key} ignorado - máximo de tentativas atingido");
                }
                return;
            }

            // Verificar se tem dados enriquecidos
            if (empty($group->enriched_data)) {
                $this->skippedGroups++;
                if ($this->debugMode) {
                    $this->warn("Grupo {$group->generation_key} ignorado - sem dados enriquecidos");
                }
                return;
            }

            if ($dryRun) {
                $this->successGroups++;
                $this->totalVehiclesUpdated += $group->sibling_count;

                if ($this->debugMode) {
                    $this->info("DRY-RUN: Grupo {$group->generation_key} seria propagado");
                    $this->displayEnrichedDataPreview($group->enriched_data);
                }
                return;
            }

            // Marcar como propagando
            $group->markAsPropagating();

            // ✅ CORREÇÃO CRÍTICA: Propagar para REPRESENTANTE também
            $results = $this->propagateToAllVehicles($group);

            // Marcar como concluído
            $group->markAsPropagated($results);
            $this->successGroups++;
            $this->totalVehiclesUpdated += $results['updated_count'];

            if ($this->debugMode) {
                $this->info("Grupo {$group->generation_key} propagado: {$results['updated_count']} veículos atualizados");
            }
        } catch (\Exception $e) {
            $this->errorGroups++;
            $group->markPropagationAsFailed($e->getMessage());

            $repInfo = $group->getRepresentativeInfo();
            $this->errors[] = [
                'group_id' => $group->id,
                'generation_key' => $group->generation_key,
                'vehicle' => $repInfo['full_name'] ?? 'Unknown',
                'error' => $e->getMessage()
            ];

            if ($this->debugMode) {
                $this->error("ERRO no grupo {$group->generation_key}: " . $e->getMessage());
            }
        }
    }

    /**
     * ✅ CORREÇÃO CRÍTICA: Propagar para TODOS os veículos (representante + irmãos)
     */
    protected function propagateToAllVehicles(VehicleEnrichmentGroup $group): array
    {
        $enrichedData = $group->enriched_data;
        $representativeData = $group->representative_data;

        $updatedCount = 0;
        $errorCount = 0;
        $errors = [];

        // 1. ✅ APLICAR NO REPRESENTANTE PRIMEIRO
        try {
            $representativeId = $group->representative_vehicle_id;
            $representative = VehicleData::find($representativeId);

            if ($representative) {
                if ($this->debugMode) {
                    $this->line("Atualizando representante: {$representative->make} {$representative->model} {$representative->year}");
                }

                // Aplicar dados enriquecidos diretamente (sem ajustes)
                $this->applyEnrichedDataToVehicle($representative, $enrichedData);
                $updatedCount++;

                if ($this->debugMode) {
                    $this->info("✅ Representante atualizado com sucesso");
                }
            }
        } catch (\Exception $e) {
            $errorCount++;
            $errors[] = [
                'vehicle_id' => $representativeId ?? 'unknown',
                'type' => 'representative',
                'error' => $e->getMessage()
            ];

            if ($this->debugMode) {
                $this->error("Erro ao atualizar representante: " . $e->getMessage());
            }
        }

        // 2. ✅ APLICAR NOS IRMÃOS COM AJUSTES
        $siblings = $group->sibling_vehicles ?? [];
        foreach ($siblings as $siblingData) {
            try {
                $siblingId = $siblingData['id'] ?? $siblingData['_id'] ?? null;
                if (!$siblingId) {
                    throw new \Exception('ID do veículo irmão não encontrado');
                }

                $siblingVehicle = VehicleData::find($siblingId);
                if (!$siblingVehicle) {
                    throw new \Exception("Veículo irmão {$siblingId} não encontrado");
                }

                if ($this->debugMode) {
                    $this->line("Atualizando irmão: {$siblingVehicle->make} {$siblingVehicle->model} {$siblingVehicle->year}");
                }

                // Calcular diferença de anos
                $repYear = $representativeData['year'];
                $siblingYear = $siblingVehicle->year;
                $yearDiff = $siblingYear - $repYear;

                // Aplicar dados com ajustes por diferença de anos
                $this->applyEnrichedDataWithAdjustments($siblingVehicle, $enrichedData, $yearDiff);
                $updatedCount++;

                if ($this->debugMode) {
                    $this->info("✅ Irmão atualizado (diff: {$yearDiff} anos)");
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'vehicle_id' => $siblingId ?? 'unknown',
                    'type' => 'sibling',
                    'error' => $e->getMessage()
                ];

                if ($this->debugMode) {
                    $this->error("Erro ao atualizar irmão: " . $e->getMessage());
                }
            }
        }

        return [
            'updated_count' => $updatedCount,
            'error_count' => $errorCount,
            'errors' => $errors,
            'processed_at' => now()->toISOString()
        ];
    }

    /**
     * ✅ NOVO: Aplicar dados enriquecidos diretamente (para representante)
     */
    protected function applyEnrichedDataToVehicle(VehicleData $vehicle, array $enrichedData): void
    {
        foreach ($enrichedData as $section => $sectionData) {
            if (!is_array($sectionData)) continue;

            $currentSection = $vehicle->{$section} ?? [];

            // Merge dos dados (enriquecidos têm prioridade)
            $mergedSection = array_merge($currentSection, $sectionData);
            $vehicle->{$section} = $mergedSection;

            if ($this->debugMode) {
                $newFields = array_diff_key($sectionData, $currentSection);
                if (!empty($newFields)) {
                    $fieldsList = implode(', ', array_keys($newFields));
                    $this->line("   📝 {$section}: +{$fieldsList}");
                }
            }
        }

        // Recalcular score de qualidade
        $vehicle->calculateDataQualityScore();
        $vehicle->save();
    }

    /**
     * ✅ ATUALIZADO: Aplicar dados com ajustes por diferença de anos
     */
    protected function applyEnrichedDataWithAdjustments(VehicleData $vehicle, array $enrichedData, int $yearDiff): void
    {
        foreach ($enrichedData as $section => $sectionData) {
            if (!is_array($sectionData)) continue;

            $currentSection = $vehicle->{$section} ?? [];
            $adjustedSection = $currentSection;

            foreach ($sectionData as $field => $value) {
                $fieldPath = "{$section}.{$field}";

                // Verificar se deve propagar este campo
                if ($this->shouldPropagateField($fieldPath, $yearDiff)) {
                    $adjustedValue = $this->adjustValueForYear($fieldPath, $value, $yearDiff);
                    $adjustedSection[$field] = $adjustedValue;

                    if ($this->debugMode && $adjustedValue != $value) {
                        $this->line("   🔧 {$fieldPath}: {$value} → {$adjustedValue} (diff: {$yearDiff} anos)");
                    }
                }
            }

            $vehicle->{$section} = $adjustedSection;
        }

        // Recalcular score de qualidade
        $vehicle->calculateDataQualityScore();
        $vehicle->save();
    }

    /**
     * ✅ NOVO: Verificar se deve propagar um campo
     */
    protected function shouldPropagateField(string $fieldPath, int $yearDiff): bool
    {
        // Sempre propagar
        if (in_array($fieldPath, $this->propagationRules['always_propagate'])) {
            return true;
        }

        // Propagar com ajuste
        if (in_array($fieldPath, $this->propagationRules['propagate_with_adjustment'])) {
            return true;
        }

        // Nunca propagar
        if (in_array($fieldPath, $this->propagationRules['never_propagate'])) {
            return false;
        }

        // Condicionais (só se anos próximos)
        if (in_array($fieldPath, $this->propagationRules['conditional_propagate'])) {
            return abs($yearDiff) <= 2;
        }

        // Por padrão, propagar se diferença <= 3 anos
        return abs($yearDiff) <= 3;
    }

    /**
     * Ajustar valor baseado na diferença de anos
     */
    protected function adjustValueForYear(string $fieldPath, $value, int $yearDiff): mixed
    {
        if (!is_numeric($value) || $yearDiff === 0) {
            return $value;
        }

        // Extrair apenas números do valor
        if (is_string($value)) {
            preg_match('/[\d.]+/', $value, $matches);
            if (empty($matches)) {
                return $value; // Se não tem números, retornar original
            }
            $numericValue = (float) $matches[0];
            $suffix = str_replace($matches[0], '', $value);
        } else {
            $numericValue = (float) $value;
            $suffix = '';
        }

        // Regras de ajuste por tipo de campo
        $adjustmentRules = [
            'engine_data.horsepower' => 0.025, // +2.5% por ano
            'engine_data.torque' => 0.025,
            'fuel_data.consumption_city' => -0.015, // Melhora 1.5% por ano  
            'fuel_data.consumption_highway' => -0.015,
        ];

        $adjustmentRate = $adjustmentRules[$fieldPath] ?? 0;

        if ($adjustmentRate === 0) {
            return $value;
        }

        // Aplicar ajuste baseado na diferença de anos
        $adjustedValue = $numericValue * (1 + ($adjustmentRate * $yearDiff));

        // Arredondar adequadamente
        if (str_contains($fieldPath, 'consumption')) {
            $adjustedValue = round($adjustedValue, 1);
        } else {
            $adjustedValue = round($adjustedValue);
        }

        return $adjustedValue . $suffix;
    }

    /**
     * ✅ NOVO: Exibir preview dos dados enriquecidos
     */
    protected function displayEnrichedDataPreview(array $enrichedData): void
    {
        $this->line("   📋 Dados que seriam aplicados:");
        foreach ($enrichedData as $section => $data) {
            if (is_array($data)) {
                $fieldCount = count($data);
                $this->line("      • {$section}: {$fieldCount} campos");
            }
        }
    }

    /**
     * Exibir resultados
     */
    protected function displayResults(): void
    {
        $this->info('=== RESULTADO DA PROPAGAÇÃO ===');
        $this->newLine();

        $this->line("📄 <fg=cyan>Grupos processados:</> {$this->processedGroups}");
        $this->line("✅ <fg=green>Propagados com sucesso:</> {$this->successGroups}");
        $this->line("🚗 <fg=blue>Veículos atualizados:</> {$this->totalVehiclesUpdated}");
        $this->line("⏭️  <fg=yellow>Ignorados:</> {$this->skippedGroups}");
        $this->line("❌ <fg=red>Erros:</> {$this->errorGroups}");

        if (!empty($this->errors)) {
            $this->newLine();
            $this->warn('Primeiros erros encontrados:');
            foreach (array_slice($this->errors, 0, 3) as $error) {
                $this->line("  • {$error['vehicle']}: {$error['error']}");
            }
        }

        // Estatísticas finais
        $this->newLine();
        $stats = VehicleEnrichmentGroup::getProcessingStats();
        $this->info('📊 ESTATÍSTICAS FINAIS:');
        $this->line("   Taxa de conclusão: {$stats['completion_rate']}%");
        $this->line("   Grupos completos: {$stats['completed']}");
        $this->line("   Ainda pendentes: {$stats['pending_enrichment']}");

        $this->newLine();

        if ($stats['completion_rate'] >= 95) {
            $this->info('🎉 PIPELINE QUASE COMPLETO!');
        } else {
            $this->info('📋 PARA COMPLETAR O PIPELINE:');
            if ($stats['pending_enrichment'] > 0) {
                $this->line("   1. php artisan vehicle-data:enrich-representatives");
            }
            if ($stats['pending_propagation'] > 0) {
                $this->line("   2. php artisan vehicle-data:propagate-from-representatives --force");
            }
        }
    }
}

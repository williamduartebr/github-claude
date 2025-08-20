<?php

namespace Src\VehicleData\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\VehicleData\Domain\Entities\VehicleEnrichmentGroup;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * Command para propagar dados enriquecidos dos representantes para veículos irmãos
 * 
 * Distribui dados técnicos enriquecidos via Claude API para todos os veículos
 * do mesmo grupo, aplicando ajustes inteligentes baseados na diferença de anos
 */
class PropagateFromRepresentativesCommand extends Command
{
    protected $signature = 'vehicle-data:propagate-from-representatives
                           {--batch-size=20 : Número de grupos por lote}
                           {--priority= : Processar apenas uma prioridade (high/medium/low)}
                           {--make= : Processar apenas uma marca específica}
                           {--dry-run : Simular propagação sem salvar dados}
                           {--force : Reprocessar grupos já propagados}
                           {--limit= : Limite total de grupos para processar}';

    protected $description = 'Propagar dados enriquecidos dos representantes para veículos irmãos';

    protected int $processedGroups = 0;
    protected int $successGroups = 0;
    protected int $errorGroups = 0;
    protected int $skippedGroups = 0;
    protected int $totalVehiclesUpdated = 0;
    protected array $errors = [];
    protected array $propagationRules = [];

    public function handle(): int
    {
        $this->info('🔄 INICIANDO PROPAGAÇÃO DE DADOS ENRIQUECIDOS');
        $this->newLine();

        $batchSize = (int) $this->option('batch-size');
        $priority = $this->option('priority');
        $make = $this->option('make');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        if ($dryRun) {
            $this->warn('🔍 MODO DRY-RUN: Nenhum dado será salvo');
            $this->newLine();
        }

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
                'transmission_data.type', // Manual/Auto raramente muda
                'engine_data.engine_type', // "1.6 16V" geralmente igual
                'engine_data.displacement',
                'engine_data.fuel_type',
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
                'fuel_data.fuel_tank_capacity',
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

        // Ordenação: prioridade > enriquecidos mais antigos
        $query->orderByRaw("
            CASE priority 
                WHEN 'high' THEN 1 
                WHEN 'medium' THEN 2 
                WHEN 'low' THEN 3 
                ELSE 4 
            END
        ")
        ->orderBy('enriched_at', 'asc');

        // Aplicar limite
        if ($limit) {
            $query->limit($limit);
        } else {
            $query->limit($batchSize * 5); // Máximo 5 lotes por execução
        }

        return $query->get();
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
                Log::info('Grupo ignorado - máximo de tentativas de propagação atingido', [
                    'group_id' => $group->id,
                    'generation_key' => $group->generation_key,
                    'attempts' => $group->propagation_attempts
                ]);
                return;
            }

            // Verificar se tem dados enriquecidos
            if (empty($group->enriched_data)) {
                $this->skippedGroups++;
                Log::warning('Grupo ignorado - sem dados enriquecidos', [
                    'group_id' => $group->id,
                    'generation_key' => $group->generation_key
                ]);
                return;
            }

            if ($dryRun) {
                $this->successGroups++;
                $this->totalVehiclesUpdated += $group->sibling_count;
                
                Log::info('DRY-RUN: Grupo seria propagado', [
                    'group_id' => $group->id,
                    'generation_key' => $group->generation_key,
                    'siblings_count' => $group->sibling_count
                ]);
                return;
            }

            // Marcar como propagando
            $group->markAsPropagating();

            // Propagar dados para siblings
            $results = $this->propagateToSiblings($group);

            // Marcar como concluído
            $group->markAsPropagated($results);
            $this->successGroups++;
            $this->totalVehiclesUpdated += $results['updated_count'];

            Log::info('Grupo propagado com sucesso', [
                'group_id' => $group->id,
                'generation_key' => $group->generation_key,
                'vehicles_updated' => $results['updated_count'],
                'errors' => $results['error_count']
            ]);

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

            Log::error('Erro ao propagar grupo', [
                'group_id' => $group->id,
                'generation_key' => $group->generation_key,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Propagar dados para veículos irmãos
     */
    protected function propagateToSiblings(VehicleEnrichmentGroup $group): array
    {
        $enrichedData = $group->enriched_data;
        $representativeData = $group->representative_data;
        $siblings = $group->sibling_vehicles;
        
        $updatedCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($siblings as $siblingData) {
            try {
                // Buscar veículo irmão no banco
                $siblingId = $siblingData['id'] ?? $siblingData['_id'] ?? null;
                if (!$siblingId) {
                    throw new \Exception('ID do veículo irmão não encontrado');
                }

                $siblingVehicle = VehicleData::find($siblingId);
                if (!$siblingVehicle) {
                    throw new \Exception("Veículo irmão {$siblingId} não encontrado");
                }

                // Calcular diferença de anos
                $repYear = $representativeData['year'];
                $siblingYear = $siblingVehicle->year;
                $yearDiff = $siblingYear - $repYear;

                // Aplicar propagação com ajustes
                $updates = $this->calculateUpdatesForSibling($enrichedData, $yearDiff);

                if (empty($updates)) {
                    continue; // Nada para atualizar
                }

                // Aplicar atualizações
                $this->applyUpdatesToVehicle($siblingVehicle, $updates);
                $updatedCount++;

            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'sibling_id' => $siblingId ?? 'unknown',
                    'error' => $e->getMessage()
                ];

                Log::error('Erro ao propagar para veículo irmão', [
                    'group_id' => $group->id,
                    'sibling_id' => $siblingId ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
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
     * Calcular atualizações para veículo irmão
     */
    protected function calculateUpdatesForSibling(array $enrichedData, int $yearDiff): array
    {
        $updates = [];

        // Aplicar regras de propagação
        foreach ($this->propagationRules['always_propagate'] as $fieldPath) {
            $value = data_get($enrichedData, $fieldPath);
            if ($value !== null) {
                $updates[$fieldPath] = $value;
            }
        }

        // Campos com ajuste por ano
        foreach ($this->propagationRules['propagate_with_adjustment'] as $fieldPath) {
            $value = data_get($enrichedData, $fieldPath);
            if ($value !== null && is_numeric($value)) {
                $adjustedValue = $this->adjustValueForYear($fieldPath, $value, $yearDiff);
                $updates[$fieldPath] = $adjustedValue;
            }
        }

        // Campos condicionais (só se anos próximos)
        if (abs($yearDiff) <= 2) {
            foreach ($this->propagationRules['conditional_propagate'] as $fieldPath) {
                $value = data_get($enrichedData, $fieldPath);
                if ($value !== null) {
                    $updates[$fieldPath] = $value;
                }
            }
        }

        return $updates;
    }

    /**
     * Ajustar valor baseado na diferença de anos
     */
    protected function adjustValueForYear(string $fieldPath, $value, int $yearDiff): mixed
    {
        if (!is_numeric($value) || $yearDiff === 0) {
            return $value;
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
        $adjustedValue = $value * (1 + ($adjustmentRate * $yearDiff));
        
        // Arredondar adequadamente
        if (str_contains($fieldPath, 'consumption')) {
            return round($adjustedValue, 1); // 1 casa decimal para consumo
        }
        
        return round($adjustedValue); // Inteiro para potência/torque
    }

    /**
     * Aplicar atualizações no veículo
     */
    protected function applyUpdatesToVehicle(VehicleData $vehicle, array $updates): void
    {
        foreach ($updates as $fieldPath => $value) {
            $this->setNestedValue($vehicle, $fieldPath, $value);
        }

        // Recalcular score de qualidade
        $vehicle->calculateDataQualityScore();
        $vehicle->save();
    }

    /**
     * Definir valor aninhado no modelo
     */
    protected function setNestedValue(VehicleData $vehicle, string $fieldPath, $value): void
    {
        $parts = explode('.', $fieldPath);
        
        if (count($parts) === 1) {
            $vehicle->{$parts[0]} = $value;
            return;
        }

        // Para campos aninhados
        $topLevel = $parts[0];
        $subField = $parts[1];
        
        $data = $vehicle->{$topLevel} ?? [];
        $data[$subField] = $value;
        $vehicle->{$topLevel} = $data;
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

            if (count($this->errors) > 3) {
                $this->line("  ... e mais " . (count($this->errors) - 3) . " erros");
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
            $this->line('   Todos os veículos foram enriquecidos com dados técnicos.');
        } else {
            $this->info('📋 PARA COMPLETAR:');
            if ($stats['pending_enrichment'] > 0) {
                $this->line("   1. php artisan vehicle-data:enrich-representatives (ainda há {$stats['pending_enrichment']} pendentes)");
            }
            if ($stats['pending_propagation'] > 0) {
                $this->line("   2. php artisan vehicle-data:propagate-from-representatives (ainda há {$stats['pending_propagation']} para propagar)");
            }
        }

        Log::info('PropagateFromRepresentativesCommand: Execução concluída', [
            'processed_groups' => $this->processedGroups,
            'success_groups' => $this->successGroups,
            'vehicles_updated' => $this->totalVehiclesUpdated,
            'errors' => $this->errorGroups,
            'skipped' => $this->skippedGroups
        ]);
    }
}
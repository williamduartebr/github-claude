<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Command para gerenciar índices da collection tire_pressure_articles
 * 
 * Permite criar, verificar e remover índices de forma controlada
 * Ideal para usar após importar dados ou recriar collections
 */
class ManageIndexesCommand extends Command
{
    protected $signature = 'tire-pressure:manage-indexes 
                           {action=create : Ação: create, check, remove, optimize}
                           {--essential : Criar apenas índices essenciais}
                           {--phase2 : Incluir índices da Fase 2}
                           {--force : Forçar recriação de índices existentes}
                           {--dry-run : Preview sem executar}';

    protected $description = 'Gerenciar índices da collection tire_pressure_articles';

    // Definição de todos os índices do sistema
    private array $indexes = [
        // CRÍTICOS
        'critical' => [
            'unique_wordpress_slug' => [
                'fields' => ['wordpress_slug'],
                'unique' => true,
                'description' => 'Evita slugs duplicados (CRÍTICO)'
            ],
        ],
        
        // ESSENCIAIS
        'essential' => [
            'vehicle_lookup_index' => [
                'fields' => ['make', 'model', 'year'],
                'description' => 'Busca por veículo'
            ],
            'template_type_index' => [
                'fields' => ['template_type'],
                'description' => 'Filtro por template'
            ],
            'slug_index' => [
                'fields' => ['slug'],
                'description' => 'Busca por slug'
            ],
            'status_date_index' => [
                'fields' => ['generation_status', 'created_at'],
                'description' => 'Queries por status e data'
            ],
            'created_at_index' => [
                'fields' => ['created_at'],
                'description' => 'Ordenação temporal'
            ],
            'blog_sync_index' => [
                'fields' => ['blog_synced', 'blog_status'],
                'description' => 'Controle de sincronização'
            ],
            'blog_id_index' => [
                'fields' => ['blog_id'],
                'description' => 'Referência WordPress'
            ],
            'content_score_index' => [
                'fields' => ['content_score'],
                'description' => 'Ordenar por qualidade'
            ],
        ],
        
        // FASE 2
        'phase2' => [
            'refinement_batch_id_index' => [
                'fields' => ['refinement_batch_id'],
                'description' => 'Índice do batch'
            ],
            'batch_position_index' => [
                'fields' => ['refinement_batch_id', 'refinement_batch_position'],
                'description' => 'Ordem de processamento'
            ],
            'ready_for_refinement_index' => [
                'fields' => ['vehicle_data_version', 'sections_refinement_version'],
                'description' => 'Artigos prontos para refinamento'
            ],
            'batch_monitoring_index' => [
                'fields' => ['refinement_batch_id', 'refinement_status'],
                'description' => 'Monitoramento de batches'
            ],
            'retry_management_index' => [
                'fields' => ['refinement_status', 'refinement_attempts'],
                'description' => 'Gestão de retry'
            ],
            'sections_last_refined_index' => [
                'fields' => ['sections_last_refined_at'],
                'description' => 'Ordenação de refinamento'
            ],
        ],
        
        // ADICIONAIS
        'additional' => [
            'publication_status_index' => [
                'fields' => ['generation_status', 'blog_status', 'blog_synced'],
                'description' => 'Status completo de publicação'
            ],
            'original_batch_index' => [
                'fields' => ['batch_id'],
                'description' => 'Batch da fase 1'
            ],
            'vehicle_data_version_index' => [
                'fields' => ['vehicle_data_version'],
                'description' => 'Versão dos dados do veículo'
            ],
            'refinement_version_index' => [
                'fields' => ['sections_refinement_version'],
                'description' => 'Versão do refinamento'
            ],
        ]
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn("🔍 MODO DRY RUN - Nenhuma alteração será feita");
        }

        try {
            switch ($action) {
                case 'create':
                    return $this->createIndexes($isDryRun);
                    
                case 'check':
                    return $this->checkIndexes();
                    
                case 'remove':
                    return $this->removeIndexes($isDryRun);
                    
                case 'optimize':
                    return $this->optimizeIndexes($isDryRun);
                    
                default:
                    $this->error("Ação inválida: {$action}");
                    return 1;
            }
        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Criar índices
     */
    private function createIndexes(bool $isDryRun): int
    {
        $essentialOnly = $this->option('essential');
        $includePhase2 = $this->option('phase2');
        $force = $this->option('force');

        $this->info("🚀 Criando índices...\n");

        // Determinar quais grupos criar
        $groups = ['critical'];
        
        if (!$essentialOnly || $includePhase2) {
            $groups[] = 'essential';
        }
        
        if ($includePhase2) {
            $groups[] = 'phase2';
        }
        
        if (!$essentialOnly && !$includePhase2) {
            $groups[] = 'additional';
        }

        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($groups as $group) {
            if (!isset($this->indexes[$group])) continue;
            
            $this->info("📦 Grupo: " . strtoupper($group));
            
            foreach ($this->indexes[$group] as $indexName => $config) {
                if ($isDryRun) {
                    $this->line("  • {$indexName}: " . $config['description']);
                    continue;
                }

                // Verificar se já existe
                if (!$force && $this->indexExists($indexName)) {
                    $this->comment("  ✓ {$indexName} já existe");
                    $skipped++;
                    continue;
                }

                // Criar índice
                try {
                    $this->createIndex($indexName, $config);
                    $this->info("  ✅ {$indexName}: " . $config['description']);
                    $created++;
                } catch (\Exception $e) {
                    $this->error("  ❌ {$indexName}: " . $e->getMessage());
                    $errors++;
                }
            }
            $this->newLine();
        }

        // Resumo
        $this->info("📊 Resumo:");
        $this->line("  • Criados: {$created}");
        $this->line("  • Ignorados: {$skipped}");
        $this->line("  • Erros: {$errors}");

        return $errors > 0 ? 1 : 0;
    }

    /**
     * Verificar índices existentes
     */
    private function checkIndexes(): int
    {
        $collection = $this->getCollection();
        $existingIndexes = collect($collection->listIndexes());
        
        $this->info("📊 Verificação de Índices\n");
        $this->line("Total existente: {$existingIndexes->count()}/64");
        $this->newLine();

        // Verificar cada grupo
        foreach ($this->indexes as $group => $indexes) {
            $this->info("📦 Grupo: " . strtoupper($group));
            
            foreach ($indexes as $indexName => $config) {
                $exists = $this->indexExists($indexName);
                $status = $exists ? '✅' : '❌';
                $this->line("  {$status} {$indexName}");
            }
            $this->newLine();
        }

        // Listar índices não reconhecidos
        $recognizedNames = collect($this->indexes)->flatten(1)->keys();
        $unrecognized = $existingIndexes->filter(function ($index) use ($recognizedNames) {
            $name = $index['name'] ?? '';
            return !$recognizedNames->contains($name) && $name !== '_id_';
        });

        if ($unrecognized->isNotEmpty()) {
            $this->warn("⚠️  Índices não reconhecidos:");
            foreach ($unrecognized as $index) {
                $this->line("  • " . ($index['name'] ?? 'sem nome'));
            }
        }

        return 0;
    }

    /**
     * Remover índices
     */
    private function removeIndexes(bool $isDryRun): int
    {
        $this->info("🗑️  Removendo índices...\n");

        if (!$this->confirm('Tem certeza que deseja remover índices?')) {
            $this->comment("Operação cancelada");
            return 0;
        }

        $removed = 0;
        $errors = 0;

        foreach ($this->indexes as $group => $indexes) {
            foreach ($indexes as $indexName => $config) {
                if ($indexName === 'unique_wordpress_slug') {
                    $this->warn("  ⚠️  Pulando {$indexName} (CRÍTICO)");
                    continue;
                }

                if ($isDryRun) {
                    $this->line("  • Removeria: {$indexName}");
                    continue;
                }

                try {
                    if ($this->removeIndex($indexName)) {
                        $this->info("  ❌ Removido: {$indexName}");
                        $removed++;
                    } else {
                        $this->comment("  - {$indexName} não existe");
                    }
                } catch (\Exception $e) {
                    $this->error("  ❌ Erro ao remover {$indexName}: " . $e->getMessage());
                    $errors++;
                }
            }
        }

        $this->newLine();
        $this->info("📊 Resumo:");
        $this->line("  • Removidos: {$removed}");
        $this->line("  • Erros: {$errors}");

        return $errors > 0 ? 1 : 0;
    }

    /**
     * Otimizar índices (remove não essenciais, cria essenciais)
     */
    private function optimizeIndexes(bool $isDryRun): int
    {
        $this->info("🔧 Otimizando índices...\n");

        // Primeiro, remover índices não reconhecidos
        $collection = $this->getCollection();
        $existingIndexes = collect($collection->listIndexes());
        $recognizedNames = collect($this->indexes)->flatten(1)->keys()->push('_id_');
        
        $toRemove = $existingIndexes->filter(function ($index) use ($recognizedNames) {
            $name = $index['name'] ?? '';
            return !$recognizedNames->contains($name);
        });

        if ($toRemove->isNotEmpty()) {
            $this->warn("Removendo índices não essenciais:");
            foreach ($toRemove as $index) {
                $indexName = $index['name'] ?? '';
                if (!$isDryRun) {
                    try {
                        $collection->dropIndex($indexName);
                        $this->info("  ❌ Removido: {$indexName}");
                    } catch (\Exception $e) {
                        $this->error("  Erro: " . $e->getMessage());
                    }
                } else {
                    $this->line("  • Removeria: {$indexName}");
                }
            }
            $this->newLine();
        }

        // Depois, criar essenciais
        $this->option('essential', true);
        $this->option('phase2', true);
        return $this->createIndexes($isDryRun);
    }

    /**
     * Verificar se índice existe
     */
    private function indexExists(string $indexName): bool
    {
        try {
            $collection = $this->getCollection();
            $indexes = $collection->listIndexes();
            
            foreach ($indexes as $index) {
                if (($index['name'] ?? '') === $indexName) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Criar um índice
     */
    private function createIndex(string $name, array $config): void
    {
        Schema::connection(env('MONGO_CONNECTION', 'mongodb'))
            ->table('tire_pressure_articles', function ($collection) use ($name, $config) {
                if ($config['unique'] ?? false) {
                    $collection->unique($config['fields'][0], $name);
                } else {
                    $collection->index($config['fields'], $name);
                }
            });
    }

    /**
     * Remover um índice
     */
    private function removeIndex(string $name): bool
    {
        try {
            $collection = $this->getCollection();
            $collection->dropIndex($name);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obter collection MongoDB
     */
    private function getCollection()
    {
        return DB::connection(env('MONGO_CONNECTION', 'mongodb'))
                ->getCollection('tire_pressure_articles');
    }
}
<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Command para verificar e gerenciar índices da collection
 * 
 * Ajuda a identificar índices duplicados ou desnecessários
 */
class CheckIndexesCommand extends Command
{
    protected $signature = 'tire-pressure:check-indexes 
                           {--drop-duplicates : Remove índices duplicados}
                           {--analyze : Analisa uso dos índices}';

    protected $description = 'Verificar índices da collection tire_pressure_articles';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dropDuplicates = $this->option('drop-duplicates');
        $analyze = $this->option('analyze');

        try {
            $collection = DB::connection(env('MONGO_CONNECTION', 'mongodb'))
                           ->getCollection('tire_pressure_articles');
            
            $indexes = collect($collection->listIndexes());
            
            $this->info("📊 Análise de Índices - tire_pressure_articles");
            $this->line("Total de índices: {$indexes->count()}/64");
            $this->newLine();

            // Agrupar índices por tipo
            $simpleIndexes = [];
            $compoundIndexes = [];
            $uniqueIndexes = [];
            
            foreach ($indexes as $index) {
                $name = $index['name'] ?? 'unnamed';
                $keys = $index['key'] ?? [];
                $unique = $index['unique'] ?? false;
                
                if ($unique) {
                    $uniqueIndexes[] = ['name' => $name, 'keys' => $keys];
                } elseif (count($keys) === 1) {
                    $simpleIndexes[] = ['name' => $name, 'keys' => $keys];
                } else {
                    $compoundIndexes[] = ['name' => $name, 'keys' => $keys];
                }
            }

            // Mostrar índices únicos
            if (!empty($uniqueIndexes)) {
                $this->info("🔑 Índices Únicos (" . count($uniqueIndexes) . "):");
                foreach ($uniqueIndexes as $idx) {
                    $keyList = [];
                    foreach ($idx['keys'] as $field => $order) {
                        $keyList[] = $field;
                    }
                    $this->line("  • {$idx['name']}: " . implode(', ', $keyList));
                }
                $this->newLine();
            }

            // Mostrar índices simples
            if (!empty($simpleIndexes)) {
                $total = count($simpleIndexes);
                $this->info("📌 Índices Simples ({$total}):");
                
                $tableData = [];
                foreach ($simpleIndexes as $idx) {
                    $field = array_keys($idx['keys'])[0] ?? 'unknown';
                    $tableData[] = [$idx['name'], $field];
                }
                
                $this->table(['Nome', 'Campo'], $tableData);
            }

            // Mostrar índices compostos
            if (!empty($compoundIndexes)) {
                $this->info("🔗 Índices Compostos (" . count($compoundIndexes) . "):");
                foreach ($compoundIndexes as $idx) {
                    $fields = implode(', ', array_keys($idx['keys']));
                    $this->line("  • {$idx['name']}: [{$fields}]");
                }
                $this->newLine();
            }

            // Identificar possíveis duplicados
            $this->checkForDuplicates($simpleIndexes, $compoundIndexes);

            // Analisar uso se solicitado
            if ($analyze) {
                $this->analyzeIndexUsage($collection);
            }

            // Sugestões para Fase 2
            $this->showPhase2Recommendations($indexes);

            return 0;

        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Verificar índices duplicados
     */
    private function checkForDuplicates(array $simpleIndexes, array $compoundIndexes): void
    {
        $this->info("🔍 Verificando possíveis duplicações...");
        
        $duplicates = [];
        
        // Verificar se campos simples estão cobertos por compostos
        foreach ($simpleIndexes as $simple) {
            $field = array_keys($simple['keys'])[0] ?? null;
            
            foreach ($compoundIndexes as $compound) {
                $firstField = array_keys($compound['keys'])[0] ?? null;
                
                if ($field === $firstField) {
                    $duplicates[] = [
                        'simple' => $simple['name'],
                        'compound' => $compound['name'],
                        'field' => $field
                    ];
                }
            }
        }

        if (!empty($duplicates)) {
            $this->warn("⚠️  Possíveis índices redundantes encontrados:");
            foreach ($duplicates as $dup) {
                $this->line("  • '{$dup['simple']}' pode ser redundante com '{$dup['compound']}'");
                $this->line("    Campo: {$dup['field']}");
            }
            $this->newLine();
        } else {
            $this->line("✅ Nenhuma duplicação óbvia encontrada");
            $this->newLine();
        }
    }

    /**
     * Analisar uso dos índices
     */
    private function analyzeIndexUsage($collection): void
    {
        $this->info("📈 Análise de Uso dos Índices:");
        
        try {
            // Executar $indexStats
            $stats = $collection->aggregate([
                ['$indexStats' => new \stdClass()]
            ]);

            $unusedIndexes = [];
            $lowUsageIndexes = [];

            foreach ($stats as $stat) {
                $name = $stat['name'] ?? 'unknown';
                $ops = $stat['accesses']['ops'] ?? 0;
                
                if ($ops === 0) {
                    $unusedIndexes[] = $name;
                } elseif ($ops < 100) {
                    $lowUsageIndexes[] = ['name' => $name, 'ops' => $ops];
                }
            }

            if (!empty($unusedIndexes)) {
                $this->warn("🚫 Índices nunca usados:");
                foreach ($unusedIndexes as $idx) {
                    $this->line("  • {$idx}");
                }
                $this->newLine();
            }

            if (!empty($lowUsageIndexes)) {
                $this->comment("⚡ Índices com baixo uso:");
                foreach ($lowUsageIndexes as $idx) {
                    $this->line("  • {$idx['name']}: {$idx['ops']} operações");
                }
                $this->newLine();
            }

        } catch (\Exception $e) {
            $this->comment("Não foi possível analisar uso dos índices: " . $e->getMessage());
        }
    }

    /**
     * Mostrar recomendações para Fase 2
     */
    private function showPhase2Recommendations($indexes): void
    {
        $this->info("💡 Recomendações para Fase 2:");
        
        $indexCount = $indexes->count();
        $available = 64 - $indexCount;
        
        $this->line("Espaço disponível: {$available} índices");
        
        if ($available < 10) {
            $this->warn("⚠️  Espaço limitado! Considere:");
            $this->line("  1. Remover índices não utilizados");
            $this->line("  2. Consolidar índices similares");
            $this->line("  3. Usar apenas índices compostos essenciais");
        } else {
            $this->line("✅ Espaço suficiente para os 5 índices essenciais da Fase 2");
        }

        // Verificar se índices essenciais já existem
        $this->newLine();
        $this->info("Índices essenciais para Fase 2:");
        
        $essentialIndexes = [
            'refinement_batch_id' => 'Para agrupar artigos em batches',
            'refinement_batch_id + refinement_batch_position' => 'Para ordem de processamento',
            'vehicle_data_version + sections_refinement_version' => 'Para encontrar artigos prontos',
            'refinement_batch_id + refinement_status' => 'Para monitoramento',
            'refinement_status + refinement_attempts' => 'Para retry de falhas'
        ];

        foreach ($essentialIndexes as $index => $purpose) {
            $exists = $this->checkIfIndexExists($indexes, $index);
            $status = $exists ? '✅' : '❌';
            $this->line("  {$status} {$index}");
            $this->line("     {$purpose}");
        }
    }

    /**
     * Verificar se um índice existe
     */
    private function checkIfIndexExists($indexes, string $indexSpec): bool
    {
        // Implementação simplificada - você pode melhorar isso
        foreach ($indexes as $index) {
            $keys = $index['key'] ?? [];
            $keyString = implode(' + ', array_keys($keys));
            
            if ($keyString === $indexSpec || array_key_first($keys) === $indexSpec) {
                return true;
            }
        }
        return false;
    }
}
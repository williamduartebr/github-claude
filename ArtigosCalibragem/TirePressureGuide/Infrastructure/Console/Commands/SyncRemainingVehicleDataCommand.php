<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Command para sincronizar artigos restantes que já foram corrigidos
 * 
 * OBJETIVO: Resolver inconsistências existentes onde um artigo foi corrigido
 * mas seu irmão ainda não foi sincronizado
 */
class SyncRemainingVehicleDataCommand extends Command
{
    protected $signature = 'tire-pressure:sync-remaining-vehicle-data 
                           {--limit=10 : Número de veículos para processar}
                           {--dry-run : Preview sem executar}
                           {--force : Forçar sincronização mesmo se já correto}';

    protected $description = 'Sincronizar vehicle_data entre artigos irmãos que já foram corrigidos';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("🔄 Sincronizando vehicle_data entre artigos irmãos...");

        if ($isDryRun) {
            $this->warn("⚠️  MODO DRY-RUN - Nenhuma alteração será salva");
        }

        try {
            // 1. Encontrar artigos que precisam sincronização
            $articlesNeedingSync = $this->findArticlesNeedingSync($limit, $force);

            if ($articlesNeedingSync->isEmpty()) {
                $this->info("✅ Todos os artigos irmãos já estão sincronizados!");
                return 0;
            }

            $this->info("📊 Encontrados {$articlesNeedingSync->count()} artigos que precisam sincronização");

            // 2. Processar cada artigo
            $results = $this->processArticles($articlesNeedingSync, $isDryRun);

            // 3. Exibir resultados
            $this->displayResults($results);

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ ERRO: " . $e->getMessage());
            Log::error('SyncRemainingVehicleDataCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Encontrar artigos que precisam sincronização
     */
    protected function findArticlesNeedingSync(int $limit, bool $force): \Illuminate\Support\Collection
    {
        $this->info("🔍 Buscando artigos que precisam sincronização...");

        // Buscar artigos já corrigidos (v3.1)
        $correctedArticles = TirePressureArticle::where('vehicle_data_version', 'v3.1')
            ->whereNotNull('vehicle_data')
            ->limit($limit * 2) // Buscar mais para ter margem
            ->get();

        $needingSync = collect();

        foreach ($correctedArticles as $article) {
            $vehicleData = $article->vehicle_data;

            if (!is_array($vehicleData) || empty($vehicleData['make']) || empty($vehicleData['model']) || empty($vehicleData['year'])) {
                continue;
            }

            // Buscar artigo irmão
            $sibling = $this->findSiblingArticle($article, $vehicleData);

            if (!$sibling) {
                $this->warn("⚠️  Artigo órfão: {$this->generateVehicleIdentifier($vehicleData)} - {$article->template_type}");
                continue;
            }

            // Verificar se irmão precisa sincronização
            if ($sibling->vehicle_data_version !== 'v3.1' || $force) {
                $needingSync->push([
                    'source_article' => $article,
                    'target_article' => $sibling,
                    'vehicle_identifier' => $this->generateVehicleIdentifier($vehicleData)
                ]);

                if ($needingSync->count() >= $limit) {
                    break;
                }
            }
        }

        return $needingSync;
    }

    /**
     * Encontrar artigo irmão
     */
    protected function findSiblingArticle(TirePressureArticle $article, array $vehicleData): ?TirePressureArticle
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];

        // Estratégia 1: Usar sibling_article_id
        if (!empty($article->sibling_article_id)) {
            $sibling = TirePressureArticle::where('_id', $article->sibling_article_id)->first();
            if ($sibling) {
                return $sibling;
            }
        }

        // Estratégia 2: Busca pelos dados do veículo
        return TirePressureArticle::where('vehicle_data.make', $make)
            ->where('vehicle_data.model', $model)
            ->where('vehicle_data.year', $year)
            ->where('template_type', '!=', $article->template_type)
            ->where('_id', '!=', $article->_id)
            ->first();
    }

    /**
     * Processar artigos
     */
    protected function processArticles(\Illuminate\Support\Collection $articlesNeedingSync, bool $isDryRun): array
    {
        $results = [
            'synced' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => []
        ];

        foreach ($articlesNeedingSync as $syncPair) {
            $sourceArticle = $syncPair['source_article'];
            $targetArticle = $syncPair['target_article'];
            $vehicleIdentifier = $syncPair['vehicle_identifier'];

            try {
                $this->info("🔄 Processando: {$vehicleIdentifier}");
                $this->line("   Origem: {$sourceArticle->template_type} (v{$sourceArticle->vehicle_data_version})");
                $this->line("   Destino: {$targetArticle->template_type} (v{$targetArticle->vehicle_data_version})");

                if (!$isDryRun) {
                    // Aplicar sincronização
                    $this->syncArticleData($targetArticle, $sourceArticle->vehicle_data);
                    $results['synced']++;
                    $this->info("   ✅ Sincronizado");
                } else {
                    $results['synced']++;
                    $this->info("   🔍 [DRY-RUN] Seria sincronizado");
                }

                $results['details'][] = [
                    'vehicle' => $vehicleIdentifier,
                    'source_template' => $sourceArticle->template_type,
                    'target_template' => $targetArticle->template_type,
                    'action' => $isDryRun ? 'simulated' : 'synced'
                ];
            } catch (\Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'vehicle' => $vehicleIdentifier,
                    'action' => 'error',
                    'error' => $e->getMessage()
                ];

                $this->error("   ❌ Erro: " . $e->getMessage());
                Log::error('Erro na sincronização individual', [
                    'vehicle' => $vehicleIdentifier,
                    'source_id' => $sourceArticle->_id,
                    'target_id' => $targetArticle->_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Sincronizar dados do artigo
     */
    protected function syncArticleData(TirePressureArticle $targetArticle, array $correctedVehicleData): void
    {
        // Aplicar dados corrigidos
        $targetArticle->vehicle_data = $correctedVehicleData;
        $targetArticle->vehicle_data_version = 'v3.1';
        $targetArticle->vehicle_data_corrected_at = now();

        // Atualizar campos derivados
        if (isset($correctedVehicleData['pressure_light_front'])) {
            $targetArticle->pressure_light_front = (string) $correctedVehicleData['pressure_light_front'];
        }

        if (isset($correctedVehicleData['pressure_light_rear'])) {
            $targetArticle->pressure_light_rear = (string) $correctedVehicleData['pressure_light_rear'];
        }

        if (isset($correctedVehicleData['pressure_spare'])) {
            $targetArticle->pressure_spare = (string) $correctedVehicleData['pressure_spare'];
        }

        // Salvar sem disparar observers para evitar loops
        $targetArticle->saveQuietly();
    }

    /**
     * Exibir resultados
     */
    protected function displayResults(array $results): void
    {
        $this->info("\n📊 RESULTADOS DA SINCRONIZAÇÃO:");
        $this->line("✅ Sincronizados: {$results['synced']}");
        $this->line("⏭️ Ignorados: {$results['skipped']}");
        $this->line("❌ Erros: {$results['errors']}");

        if (!empty($results['details'])) {
            $this->info("\n📋 DETALHES:");

            $successCount = 0;
            foreach ($results['details'] as $detail) {
                if ($detail['action'] === 'synced' || $detail['action'] === 'simulated') {
                    $successCount++;
                    if ($successCount <= 5) { // Mostrar apenas os primeiros 5
                        $action = $detail['action'] === 'simulated' ? '[SIMULADO]' : '[REAL]';
                        $this->line("   ✅ {$action} {$detail['vehicle']} ({$detail['source_template']} → {$detail['target_template']})");
                    }
                } else {
                    $this->line("   ❌ {$detail['vehicle']}: {$detail['error']}");
                }
            }

            if ($successCount > 5) {
                $remaining = $successCount - 5;
                $this->line("   ... e mais {$remaining} sincronizações");
            }
        }

        if ($results['synced'] > 0) {
            $this->info("\n🎉 Sincronização concluída!");
            $this->info("💡 Execute novamente para processar mais artigos");
        }
    }

    /**
     * Gerar identificador do veículo
     */
    protected function generateVehicleIdentifier(array $vehicleData): string
    {
        return "{$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}";
    }
}

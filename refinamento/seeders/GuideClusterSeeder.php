<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Services\GuideClusterService;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;

/**
 * GuideClusterSeeder - CORRIGIDO Sprint 5
 * 
 * ✅ USA GuideClusterService::generateSuperCluster()
 * ✅ Gera clusters automáticos para todos os modelos
 * ✅ Atualiza clusters existentes
 * 
 * ANTES (PROBLEMA):
 * - Criava clusters manualmente sem usar service
 * - Dados hardcoded
 * - Não sincronizado com guias reais
 * 
 * DEPOIS (SOLUÇÃO):
 * - Usa GuideClusterService
 * - Clusters gerados automaticamente
 * - Sempre sincronizado com guias
 * 
 * EXECUÇÃO:
 * php artisan db:seed --class=GuideClusterSeeder
 */
class GuideClusterSeeder extends Seeder
{
    private GuideClusterService $clusterService;

    public function __construct(GuideClusterService $clusterService)
    {
        $this->clusterService = $clusterService;
    }

    public function run(): void
    {
        $this->command->info('🔗 Gerando clusters automáticos...');
        $this->command->newLine();

        $totalClusters = 0;
        $errors = [];

        // Buscar todos os veículos que têm guias
        $vehicles = $this->getVehiclesWithGuides();

        $this->command->info("📊 Encontrados {$vehicles->count()} veículos com guias");
        $this->command->newLine();

        // Gerar super cluster para cada veículo
        foreach ($vehicles as $vehicle) {
            $makeSlug = $vehicle['make_slug'];
            $modelSlug = $vehicle['model_slug'];
            $makeName = $vehicle['make_name'];
            $modelName = $vehicle['model_name'];

            try {
                $this->command->info("   Gerando cluster: {$makeName} {$modelName}...");

                // ✅ USA O SERVICE (método correto)
                $cluster = $this->clusterService->generateSuperCluster($makeSlug, $modelSlug);

                if ($cluster) {
                    $linksCount = count($cluster->links ?? []);
                    $this->command->info("      ✅ Cluster criado ({$linksCount} categorias)");
                    $totalClusters++;
                } else {
                    $this->command->warn("      ⚠️  Nenhum cluster criado (sem guias?)");
                }
            } catch (\Exception $e) {
                $this->command->error("      ❌ Erro: " . $e->getMessage());
                $errors[] = [
                    'vehicle' => "{$makeName} {$modelName}",
                    'error' => $e->getMessage(),
                ];
            }
        }

        $this->command->newLine();
        $this->command->info("✅ {$totalClusters} clusters criados com sucesso!");

        if (!empty($errors)) {
            $this->command->newLine();
            $this->command->warn("⚠️  {count($errors)} erros encontrados:");
            foreach ($errors as $error) {
                $this->command->error("   • {$error['vehicle']}: {$error['error']}");
            }
        }

        $this->command->newLine();
        $this->showClusterStats($totalClusters);
    }

    /**
     * Busca veículos (make + model) que têm guias associados
     * 
     * @return \Illuminate\Support\Collection
     */
    private function getVehiclesWithGuides(): \Illuminate\Support\Collection
    {
        // Buscar combinações únicas de make + model que têm guias
        $guidesGrouped = \Src\GuideDataCenter\Domain\Mongo\Guide::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => [
                            'make_slug' => '$make_slug',
                            'model_slug' => '$model_slug',
                        ],
                        'make_name' => ['$first' => '$make'],
                        'model_name' => ['$first' => '$model'],
                        'guides_count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id.make_slug' => 1, '_id.model_slug' => 1]
                ]
            ]);
        });

        // Transformar em collection
        return collect($guidesGrouped)->map(function ($item) {
            return [
                'make_slug' => $item['_id']['make_slug'],
                'model_slug' => $item['_id']['model_slug'],
                'make_name' => $item['make_name'],
                'model_name' => $item['model_name'],
                'guides_count' => $item['guides_count'],
            ];
        });
    }

    /**
     * Exibe estatísticas dos clusters criados
     * 
     * @param int $totalClusters
     * @return void
     */
    private function showClusterStats(int $totalClusters): void
    {
        $this->command->info('📊 ESTATÍSTICAS:');
        $this->command->info('--------------------------------');

        // Total de clusters
        $clustersDb = \Src\GuideDataCenter\Domain\Mongo\GuideCluster::count();
        $this->command->info("   • Clusters no banco: {$clustersDb}");

        // Clusters por tipo
        $clustersByType = \Src\GuideDataCenter\Domain\Mongo\GuideCluster::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => '$cluster_type',
                        'count' => ['$sum' => 1]
                    ]
                ]
            ]);
        });

        foreach ($clustersByType as $type) {
            $typeName = $type['_id'] ?? 'indefinido';
            $count = $type['count'];
            $this->command->info("   • Tipo '{$typeName}': {$count} clusters");
        }

        // Clusters por marca
        $clustersByMake = \Src\GuideDataCenter\Domain\Mongo\GuideCluster::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => '$make_slug',
                        'count' => ['$sum' => 1]
                    ]
                ],
                [
                    '$sort' => ['count' => -1]
                ]
            ]);
        });

        foreach ($clustersByMake as $make) {
            $makeSlug = $make['_id'] ?? 'indefinido';
            $count = $make['count'];
            $this->command->info("   • Marca '{$makeSlug}': {$count} clusters");
        }

        $this->command->info('--------------------------------');
    }

    /**
     * MÉTODO ALTERNATIVO: Gerar clusters para veículos específicos
     * 
     * Use este método se quiser gerar clusters apenas para alguns veículos:
     * php artisan tinker
     * $seeder = new Database\Seeders\GuideClusterSeeder(app(Src\GuideDataCenter\Domain\Services\GuideClusterService::class));
     * $seeder->generateForSpecificVehicles(['toyota/corolla', 'honda/civic']);
     * 
     * @param array $vehicles Ex: ['toyota/corolla', 'honda/civic']
     * @return void
     */
    public function generateForSpecificVehicles(array $vehicles): void
    {
        $this->command->info('🔗 Gerando clusters para veículos específicos...');
        $this->command->newLine();

        foreach ($vehicles as $vehicle) {
            [$makeSlug, $modelSlug] = explode('/', $vehicle);

            try {
                $this->command->info("   Gerando cluster: {$vehicle}...");

                $cluster = $this->clusterService->generateSuperCluster($makeSlug, $modelSlug);

                if ($cluster) {
                    $linksCount = count($cluster->links ?? []);
                    $this->command->info("      ✅ Cluster criado ({$linksCount} categorias)");
                } else {
                    $this->command->warn("      ⚠️  Nenhum cluster criado");
                }
            } catch (\Exception $e) {
                $this->command->error("      ❌ Erro: " . $e->getMessage());
            }
        }

        $this->command->newLine();
        $this->command->info('✅ Concluído!');
    }

    /**
     * MÉTODO ALTERNATIVO: Regenerar todos os clusters (limpa antes)
     * 
     * Use este método se quiser limpar e regenerar todos os clusters:
     * php artisan tinker
     * $seeder = new Database\Seeders\GuideClusterSeeder(app(Src\GuideDataCenter\Domain\Services\GuideClusterService::class));
     * $seeder->regenerateAll();
     * 
     * @return void
     */
    public function regenerateAll(): void
    {
        $this->command->warn('⚠️  ATENÇÃO: Todos os clusters serão deletados e regenerados!');

        if (!$this->command->confirm('Deseja continuar?', false)) {
            $this->command->info('Operação cancelada.');
            return;
        }

        $this->command->newLine();
        $this->command->info('🗑️  Deletando clusters existentes...');

        $deleted = \Src\GuideDataCenter\Domain\Mongo\GuideCluster::count();
        \Src\GuideDataCenter\Domain\Mongo\GuideCluster::truncate();

        $this->command->info("   ✅ {$deleted} clusters deletados");
        $this->command->newLine();

        // Executar seeding normal
        $this->run();
    }

    /**
     * MÉTODO ALTERNATIVO: Atualizar apenas clusters desatualizados
     * 
     * Use este método para atualizar apenas clusters que foram modificados há mais de X dias:
     * php artisan tinker
     * $seeder = new Database\Seeders\GuideClusterSeeder(app(Src\GuideDataCenter\Domain\Services\GuideClusterService::class));
     * $seeder->updateOutdated(7); // Atualiza clusters com mais de 7 dias
     * 
     * @param int $daysOld
     * @return void
     */
    public function updateOutdated(int $daysOld = 7): void
    {
        $this->command->info("🔄 Atualizando clusters com mais de {$daysOld} dias...");
        $this->command->newLine();

        $cutoffDate = now()->subDays($daysOld);

        $outdatedClusters = \Src\GuideDataCenter\Domain\Mongo\GuideCluster::where('updated_at', '<', $cutoffDate)
            ->get();

        $this->command->info("   Encontrados {$outdatedClusters->count()} clusters desatualizados");
        $this->command->newLine();

        $updated = 0;
        foreach ($outdatedClusters as $cluster) {
            try {
                $makeSlug = $cluster->make_slug;
                $modelSlug = $cluster->model_slug;

                $this->command->info("   Atualizando: {$makeSlug}/{$modelSlug}...");

                $this->clusterService->generateSuperCluster($makeSlug, $modelSlug);

                $updated++;
                $this->command->info("      ✅ Atualizado");
            } catch (\Exception $e) {
                $this->command->error("      ❌ Erro: " . $e->getMessage());
            }
        }

        $this->command->newLine();
        $this->command->info("✅ {$updated} clusters atualizados!");
    }
}

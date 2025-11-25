<?php

namespace Src\VehicleDataCenter\Domain\Services;

use Illuminate\Support\Facades\Log;
use Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleSpecsRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMongoRepositoryInterface;

class VehicleSyncService
{
    public function __construct(
        private VehicleVersionRepositoryInterface $versionRepository,
        private VehicleSpecsRepositoryInterface $specsRepository,
        private VehicleMongoRepositoryInterface $mongoRepository
    ) {}

    public function syncVersionToMongo(int $versionId): array
    {
        try {
            // Buscar dados do MySQL
            $version = $this->versionRepository->findById($versionId);

            if (!$version) {
                return [
                    'success' => false,
                    'error' => 'Version not found'
                ];
            }

            $specs = $this->specsRepository->getCompleteSpecs($versionId);

            // Preparar payload para MongoDB
            $mongoData = [
                'version_id' => $version->id,
                'make_slug' => $version->model->make->slug,
                'model_slug' => $version->model->slug,
                'version_slug' => $version->slug,
                'year' => $version->year,
                'full_name' => $this->buildFullName($version),
                'payload' => [
                    'version' => $version->toArray(),
                    'specs' => $specs
                ],
                'enriched_data' => $this->buildEnrichedData($version, $specs),
                'metadata' => [
                    'synced_at' => now()->toIso8601String(),
                    'source' => 'mysql_sync'
                ]
            ];

            // Verificar se documento já existe
            $existingDoc = $this->mongoRepository->findDocumentByVersionId($versionId);

            if ($existingDoc) {
                $this->mongoRepository->updateDocument($existingDoc->_id, $mongoData);
            } else {
                $this->mongoRepository->createDocument($mongoData);
            }

            return [
                'success' => true,
                'version_id' => $versionId,
                'message' => 'Synced successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Sync error', [
                'version_id' => $versionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function syncAllVersions(): array
    {
        $versions = $this->versionRepository->all();
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($versions as $version) {
            $result = $this->syncVersionToMongo($version->id);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'version_id' => $version->id,
                    'error' => $result['error']
                ];
            }
        }

        return $results;
    }

    public function detectInconsistencies(): array
    {
        $inconsistencies = [];

        // Buscar todas as versões no MySQL
        $mysqlVersions = $this->versionRepository->all();

        foreach ($mysqlVersions as $version) {
            $mongoDoc = $this->mongoRepository->findDocumentByVersionId($version->id);

            if (!$mongoDoc) {
                $inconsistencies[] = [
                    'type' => 'missing_in_mongo',
                    'version_id' => $version->id,
                    'version_name' => $this->buildFullName($version)
                ];
                continue;
            }

            // Verificar se dados estão sincronizados
            if ($version->updated_at > $mongoDoc->updated_at) {
                $inconsistencies[] = [
                    'type' => 'outdated_mongo',
                    'version_id' => $version->id,
                    'version_name' => $this->buildFullName($version),
                    'mysql_updated' => $version->updated_at,
                    'mongo_updated' => $mongoDoc->updated_at
                ];
            }
        }

        return $inconsistencies;
    }

    private function buildFullName($version): string
    {
        $make = $version->model->make;
        $model = $version->model;
        return "{$make->name} {$model->name} {$version->name} {$version->year}";
    }

    private function buildEnrichedData($version, array $specs): array
    {
        return [
            'display_name' => $this->buildFullName($version),
            'short_name' => "{$version->model->make->name} {$version->model->name}",
            'year' => $version->year,
            'category' => $version->model->category,
            'fuel_type' => $version->fuel_type,
            'transmission' => $version->transmission,
            'power_hp' => $specs['general']['power_hp'] ?? null,
            'engine_cc' => $specs['engine']['displacement_cc'] ?? null,
            'searchable_text' => $this->buildSearchableText($version, $specs)
        ];
    }

    private function buildSearchableText($version, array $specs): string
    {
        $parts = [
            $version->model->make->name,
            $version->model->name,
            $version->name,
            $version->year,
            $version->fuel_type,
            $version->transmission,
            $specs['engine']['engine_type'] ?? '',
            $version->model->category
        ];

        return implode(' ', array_filter($parts));
    }
}

<?php

namespace Src\VehicleDataCenter\Infrastructure\Repositories;

use Src\VehicleDataCenter\Domain\Eloquent\VehicleDocument;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleCluster;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMongoRepositoryInterface;

class VehicleMongoRepository implements VehicleMongoRepositoryInterface
{
    public function createDocument(array $data)
    {
        return VehicleDocument::create($data);
    }

    public function updateDocument(string $id, array $data): bool
    {
        $document = VehicleDocument::find($id);
        if (!$document) {
            return false;
        }
        return $document->update($data);
    }

    public function findDocument(string $id)
    {
        return VehicleDocument::find($id);
    }

    public function findDocumentByVersionId(int $versionId)
    {
        return VehicleDocument::where('version_id', $versionId)->first();
    }

    public function search(array $criteria)
    {
        $query = VehicleDocument::query();

        if (isset($criteria['make_slug'])) {
            $query->byMake($criteria['make_slug']);
        }

        if (isset($criteria['model_slug'])) {
            $query->byModel($criteria['model_slug']);
        }

        if (isset($criteria['year'])) {
            $query->byYear($criteria['year']);
        }

        if (isset($criteria['keyword'])) {
            $query->where('full_name', 'like', "%{$criteria['keyword']}%");
        }

        return $query->get();
    }

    public function createCluster(array $data)
    {
        return VehicleCluster::create($data);
    }

    public function getClustersByType(string $type)
    {
        return VehicleCluster::byType($type)->get();
    }
}

<?php

namespace Src\VehicleDataCenter\Domain\Repositories;

interface VehicleMongoRepositoryInterface
{
    public function createDocument(array $data);
    public function updateDocument(string $id, array $data): bool;
    public function findDocument(string $id);
    public function findDocumentByVersionId(int $versionId);
    public function search(array $criteria);
    public function createCluster(array $data);
    public function getClustersByType(string $type);
}

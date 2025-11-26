<?php

namespace Src\GuideDataCenter\Domain\Repositories\Contracts;

use Src\GuideDataCenter\Domain\Mongo\GuideCluster;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface GuideClusterRepositoryInterface
 * 
 * Contrato para operações de repositório de clusters de guias
 */
interface GuideClusterRepositoryInterface
{
    /**
     * Busca clusters de um guia específico
     *
     * @param string $guideId
     * @return Collection
     */
    public function getClustersForGuide(string $guideId): Collection;

    /**
     * Busca super cluster de um veículo
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @return GuideCluster|null
     */
    public function getSuperCluster(string $makeSlug, string $modelSlug): ?GuideCluster;

    /**
     * Sincroniza clusters de um guia
     *
     * @param string $guideId
     * @param array $links
     * @return bool
     */
    public function syncClustersForGuide(string $guideId, array $links): bool;

    /**
     * Cria um novo cluster
     *
     * @param array $data
     * @return GuideCluster
     */
    public function createCluster(array $data): GuideCluster;

    /**
     * Atualiza um cluster
     *
     * @param string $id
     * @param array $data
     * @return GuideCluster
     */
    public function updateCluster(string $id, array $data): GuideCluster;

    /**
     * Deleta um cluster
     *
     * @param string $id
     * @return bool
     */
    public function deleteCluster(string $id): bool;

    /**
     * Busca cluster por tipo
     *
     * @param string $type
     * @param string $makeSlug
     * @param string $modelSlug
     * @return Collection
     */
    public function findByType(string $type, string $makeSlug, string $modelSlug): Collection;

    /**
     * Busca clusters por marca
     *
     * @param string $makeSlug
     * @return Collection
     */
    public function findByMake(string $makeSlug): Collection;

    /**
     * Busca clusters por modelo
     *
     * @param string $modelSlug
     * @return Collection
     */
    public function findByModel(string $modelSlug): Collection;

    /**
     * Busca ou cria super cluster
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @return GuideCluster
     */
    public function findOrCreateSuperCluster(string $makeSlug, string $modelSlug): GuideCluster;

    /**
     * Adiciona link ao cluster
     *
     * @param string $clusterId
     * @param string $category
     * @param string $url
     * @param string $title
     * @return bool
     */
    public function addLinkToCluster(string $clusterId, string $category, string $url, string $title): bool;

    /**
     * Remove link do cluster
     *
     * @param string $clusterId
     * @param string $category
     * @return bool
     */
    public function removeLinkFromCluster(string $clusterId, string $category): bool;
}

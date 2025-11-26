<?php

namespace Src\GuideDataCenter\Domain\Repositories\Mongo;

use Src\GuideDataCenter\Domain\Mongo\GuideCluster;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideClusterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class GuideClusterRepository
 * 
 * Implementação MongoDB do repositório de clusters
 */
class GuideClusterRepository implements GuideClusterRepositoryInterface
{
    /**
     * @var GuideCluster
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(GuideCluster $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getClustersForGuide(string $guideId): Collection
    {
        return $this->model
            ->byGuide($guideId)
            ->orderBy('cluster_type', 'asc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getSuperCluster(string $makeSlug, string $modelSlug): ?GuideCluster
    {
        return $this->model
            ->byMake($makeSlug)
            ->byModel($modelSlug)
            ->byType(GuideCluster::TYPE_SUPER)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function syncClustersForGuide(string $guideId, array $links): bool
    {
        try {
            // Remove clusters antigos do guia
            $this->model->where('guide_id', $guideId)->delete();

            // Cria novos clusters
            foreach ($links as $type => $linkData) {
                $this->createCluster([
                    'guide_id' => $guideId,
                    'cluster_type' => $type,
                    'links' => $linkData,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function createCluster(array $data): GuideCluster
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateCluster(string $id, array $data): GuideCluster
    {
        $cluster = $this->model->find($id);
        
        if (!$cluster) {
            throw new \Exception("Cluster with ID {$id} not found");
        }

        $cluster->update($data);
        $cluster->refresh();

        return $cluster;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteCluster(string $id): bool
    {
        $cluster = $this->model->find($id);
        
        if (!$cluster) {
            return false;
        }

        return $cluster->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function findByType(string $type, string $makeSlug, string $modelSlug): Collection
    {
        return $this->model
            ->byType($type)
            ->byMake($makeSlug)
            ->byModel($modelSlug)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findByMake(string $makeSlug): Collection
    {
        return $this->model
            ->byMake($makeSlug)
            ->orderBy('model_slug', 'asc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findByModel(string $modelSlug): Collection
    {
        return $this->model
            ->byModel($modelSlug)
            ->orderBy('make_slug', 'asc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findOrCreateSuperCluster(string $makeSlug, string $modelSlug): GuideCluster
    {
        $cluster = $this->getSuperCluster($makeSlug, $modelSlug);

        if (!$cluster) {
            $cluster = $this->createCluster([
                'make_slug' => $makeSlug,
                'model_slug' => $modelSlug,
                'cluster_type' => GuideCluster::TYPE_SUPER,
                'links' => [],
            ]);
        }

        return $cluster;
    }

    /**
     * {@inheritDoc}
     */
    public function addLinkToCluster(string $clusterId, string $category, string $url, string $title): bool
    {
        try {
            $cluster = $this->model->find($clusterId);
            
            if (!$cluster) {
                return false;
            }

            $cluster->addLink($category, $url, $title);
            $cluster->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeLinkFromCluster(string $clusterId, string $category): bool
    {
        try {
            $cluster = $this->model->find($clusterId);
            
            if (!$cluster) {
                return false;
            }

            $cluster->removeLink($category);
            $cluster->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

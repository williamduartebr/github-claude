<?php

namespace Src\GuideDataCenter\Domain\Repositories\Mongo;

use Src\GuideDataCenter\Domain\Mongo\GuideSeo;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideSeoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class GuideSeoRepository
 * 
 * Implementação MongoDB do repositório de SEO
 */
class GuideSeoRepository implements GuideSeoRepositoryInterface
{
    /**
     * @var GuideSeo
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(GuideSeo $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getSeoForGuide(string $guideId): ?GuideSeo
    {
        return $this->model
            ->byGuide($guideId)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function saveSeo(string $guideId, array $payload): GuideSeo
    {
        $seo = $this->getSeoForGuide($guideId);

        if ($seo) {
            $seo->update($payload);
            $seo->refresh();
            return $seo;
        }

        $payload['guide_id'] = $guideId;
        return $this->model->create($payload);
    }

    /**
     * {@inheritDoc}
     */
    public function findBySlug(string $slug): ?GuideSeo
    {
        return $this->model
            ->bySlug($slug)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findByPrimaryKeyword(string $keyword): Collection
    {
        return $this->model
            ->byPrimaryKeyword($keyword)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findBySecondaryKeyword(string $keyword): Collection
    {
        return $this->model
            ->bySecondaryKeyword($keyword)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteSeoForGuide(string $guideId): bool
    {
        $seo = $this->getSeoForGuide($guideId);
        
        if (!$seo) {
            return false;
        }

        return $seo->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function updateSchema(string $guideId, array $schema): bool
    {
        try {
            $seo = $this->getSeoForGuide($guideId);
            
            if (!$seo) {
                return false;
            }

            $seo->schema_org = $schema;
            $seo->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findByMinScore(float $minScore, int $limit = 50): Collection
    {
        // Como calculateSeoScore() é um método do model,
        // precisamos filtrar após carregar
        return $this->model
            ->orderBy('created_at', 'desc')
            ->limit($limit * 2) // Buscamos mais para filtrar
            ->get()
            ->filter(function ($seo) use ($minScore) {
                return $seo->calculateSeoScore() >= $minScore;
            })
            ->take($limit);
    }

    /**
     * {@inheritDoc}
     */
    public function findIncomplete(int $limit = 50): Collection
    {
        return $this->model
            ->where(function ($query) {
                $query->whereNull('title')
                      ->orWhereNull('h1')
                      ->orWhereNull('meta_description')
                      ->orWhereNull('primary_keyword');
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->model->count();
    }
}

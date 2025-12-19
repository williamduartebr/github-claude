<?php

namespace Src\GuideDataCenter\Domain\Repositories\Extensions;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Mongo\Guide;

/**
 * GuideRepositoryRelationshipExtension
 * 
 * Extension do GuideRepository com métodos específicos
 * para buscar relacionamentos entre guias
 * 
 * Seguindo Single Responsibility Principle
 * 
 * @package Src\GuideDataCenter\Domain\Repositories\Extensions
 */
trait GuideRepositoryRelationshipExtension
{
    /**
     * Busca guias do mesmo veículo mas categorias diferentes
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int $year
     * @param string $excludeCategoryId
     * @param int $limit
     * @return Collection
     */
    public function findSameVehicleDifferentCategories(
        string $makeSlug,
        string $modelSlug,
        int $year,
        string $excludeCategoryId,
        int $limit = 8
    ): Collection {
        return Guide::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('year_start', '<=', $year)
            ->where('year_end', '>=', $year)
            ->where('guide_category_id', '!=', $excludeCategoryId)
            ->with('category')
            ->orderBy('guide_category_id')
            ->limit($limit)
            ->get();
    }

    /**
     * Busca guias da mesma categoria mas anos diferentes
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param string $categoryId
     * @param int $excludeYear
     * @param int $limit
     * @return Collection
     */
    public function findSameCategoryDifferentYears(
        string $makeSlug,
        string $modelSlug,
        string $categoryId,
        int $excludeYear,
        int $limit = 8
    ): Collection {
        return Guide::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('guide_category_id', $categoryId)
            ->where('year_start', '!=', $excludeYear)
            ->orderByRaw('ABS(year_start - ?) ASC', [$excludeYear])
            ->limit($limit)
            ->get();
    }

    /**
     * Busca todas as versões disponíveis para um veículo/categoria
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int $year
     * @param string $categoryId
     * @return Collection
     */
    public function findAllVersions(
        string $makeSlug,
        string $modelSlug,
        int $year,
        string $categoryId
    ): Collection {
        return Guide::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('year_start', '<=', $year)
            ->where('year_end', '>=', $year)
            ->where('guide_category_id', $categoryId)
            ->whereNotNull('version')
            ->where('version', '!=', '')
            ->select('version', 'motor', 'fuel', 'slug', '_id', 'year_start', 'year_end')
            ->orderBy('version')
            ->get();
    }

    /**
     * Busca guias de modelos similares (mesma marca, categoria)
     *
     * @param string $makeSlug
     * @param string $excludeModelSlug
     * @param string $categoryId
     * @param int $year
     * @param int $limit
     * @return Collection
     */
    public function findSimilarModels(
        string $makeSlug,
        string $excludeModelSlug,
        string $categoryId,
        int $year,
        int $limit = 6
    ): Collection {
        return Guide::where('make_slug', $makeSlug)
            ->where('model_slug', '!=', $excludeModelSlug)
            ->where('guide_category_id', $categoryId)
            ->where('year_start', '<=', $year + 2)
            ->where('year_end', '>=', $year - 2)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Busca guias por geração (range de anos)
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int $yearStart
     * @param int $yearEnd
     * @param string $categoryId
     * @return Collection
     */
    public function findByGeneration(
        string $makeSlug,
        string $modelSlug,
        int $yearStart,
        int $yearEnd,
        string $categoryId
    ): Collection {
        return Guide::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('guide_category_id', $categoryId)
            ->where(function ($query) use ($yearStart, $yearEnd) {
                $query->whereBetween('year_start', [$yearStart, $yearEnd])
                      ->orWhereBetween('year_end', [$yearStart, $yearEnd]);
            })
            ->orderBy('year_start')
            ->get();
    }

    /**
     * Busca guias populares (mais acessados) de uma categoria
     *
     * @param string $categoryId
     * @param int $limit
     * @return Collection
     */
    public function findPopularByCategory(string $categoryId, int $limit = 10): Collection
    {
        return Guide::where('guide_category_id', $categoryId)
            ->where('views', '>', 0) // Assumindo que você tem um campo views
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Busca timeline completa de um modelo (todos os anos disponíveis)
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param string $categoryId
     * @return Collection
     */
    public function getModelTimeline(
        string $makeSlug,
        string $modelSlug,
        string $categoryId
    ): Collection {
        return Guide::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('guide_category_id', $categoryId)
            ->select('year_start', 'year_end', 'version', 'motor', 'slug', '_id')
            ->orderBy('year_start', 'desc')
            ->get();
    }

    /**
     * Busca guias que precisam de relacionamentos
     * (têm poucos links internos)
     *
     * @param int $maxLinks
     * @return Collection
     */
    public function findGuidesNeedingRelationships(int $maxLinks = 3): Collection
    {
        return Guide::whereRaw('JSON_LENGTH(links_internal) < ?', [$maxLinks])
            ->orWhereNull('links_internal')
            ->limit(100)
            ->get();
    }

    /**
     * Conta quantos guias existem para um veículo específico
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int $year
     * @return array
     */
    public function countGuidesByVehicle(
        string $makeSlug,
        string $modelSlug,
        int $year
    ): array {
        $guides = Guide::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('year_start', '<=', $year)
            ->where('year_end', '>=', $year)
            ->with('category')
            ->get();

        return [
            'total' => $guides->count(),
            'by_category' => $guides->groupBy('guide_category_id')->map->count(),
            'categories' => $guides->pluck('category.name')->unique()->values(),
        ];
    }

    /**
     * Busca gaps (anos faltantes) em uma timeline
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param string $categoryId
     * @return array
     */
    public function findTimelineGaps(
        string $makeSlug,
        string $modelSlug,
        string $categoryId
    ): array {
        $guides = Guide::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('guide_category_id', $categoryId)
            ->orderBy('year_start')
            ->get(['year_start', 'year_end']);

        if ($guides->isEmpty()) {
            return [];
        }

        $gaps = [];
        $years = $guides->pluck('year_start')->unique()->sort()->values();
        
        for ($i = 0; $i < $years->count() - 1; $i++) {
            $currentYear = $years[$i];
            $nextYear = $years[$i + 1];
            
            if ($nextYear - $currentYear > 1) {
                $gaps[] = [
                    'after' => $currentYear,
                    'before' => $nextYear,
                    'missing_years' => range($currentYear + 1, $nextYear - 1),
                ];
            }
        }

        return $gaps;
    }
}
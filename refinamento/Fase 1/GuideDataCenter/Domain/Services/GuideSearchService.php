<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Domain\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Illuminate\Support\Collection;


class GuideSearchService
{
    public function __construct(
        private readonly GuideRepositoryInterface $guideRepository
    ) {}

    /**
     * Busca guias por termo (full-text search)
     */
    public function search(string $query, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->guideRepository->searchFullText($query, $perPage, $page);
    }

    /**
     * Busca avançada com múltiplos filtros
     */
    public function advancedSearch(array $filters, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $criteria = [];

        // Busca textual
        if (!empty($filters['q'])) {
            $criteria['$text'] = ['$search' => $filters['q']];
        }

        // Filtro por categoria
        if (!empty($filters['category'])) {
            $criteria['guide_category_id'] = $filters['category'];
        }

        // Filtro por marca
        if (!empty($filters['make'])) {
            $criteria['make_slug'] = $this->normalizeSlug($filters['make']);
        }

        // Filtro por modelo
        if (!empty($filters['model'])) {
            $criteria['model_slug'] = $this->normalizeSlug($filters['model']);
        }

        // Filtro por ano inicial
        if (!empty($filters['year_start'])) {
            $criteria['year_start'] = ['$gte' => (int) $filters['year_start']];
        }

        // Filtro por ano final
        if (!empty($filters['year_end'])) {
            $criteria['year_end'] = ['$lte' => (int) $filters['year_end']];
        }

        return $this->guideRepository->findByAdvancedFilters($criteria, $perPage, $page);
    }

    /**
     * Retorna sugestões de autocomplete
     */
    public function autocomplete(string $query, int $limit = 10): Collection
    {
        if (mb_strlen($query) < 2) {
            return collect([]);
        }

        $results = $this->guideRepository->searchAutocomplete($query, $limit);

        return $results->map(function ($guide) {
            return [
                'id' => (string) $guide->_id,
                'title' => $guide->payload['title'] ?? "{$guide->make} {$guide->model}",
                'slug' => $guide->slug,
                'vehicle' => "{$guide->make} {$guide->model}",
                'url' => route('guide.show', ['slug' => $guide->slug]),
            ];
        });
    }

    /**
     * Busca guias similares
     */
    public function findSimilar(string $guideId, int $limit = 5): Collection
    {
        $guide = $this->guideRepository->findById($guideId);

        if (!$guide) {
            return collect([]);
        }

        // Busca guias do mesmo modelo ou categoria
        return $this->guideRepository->findSimilar(
            $guide->make_slug,
            $guide->model_slug,
            $guide->guide_category_id,
            $guideId,
            $limit
        );
    }

    /**
     * Retorna guias populares/recentes
     */
    public function getPopular(int $limit = 10): Collection
    {
        return $this->guideRepository->findRecent($limit);
    }

    /**
     * Busca guias por range de anos
     */
    public function searchByYearRange(
        int $yearStart,
        int $yearEnd,
        ?string $make = null,
        ?string $model = null,
        int $perPage = 15,
        int $page = 1
    ): LengthAwarePaginator {
        $filters = [
            'year_range' => [$yearStart, $yearEnd],
        ];

        if ($make) {
            $filters['make_slug'] = $this->normalizeSlug($make);
        }

        if ($model) {
            $filters['model_slug'] = $this->normalizeSlug($model);
        }

        return $this->guideRepository->findByFilters($filters, $perPage, $page);
    }

    /**
     * Normaliza string para slug
     */
    private function normalizeSlug(string $value): string
    {
        return mb_strtolower(
            preg_replace('/[^a-z0-9\-]/', '-', $value)
        );
    }
}

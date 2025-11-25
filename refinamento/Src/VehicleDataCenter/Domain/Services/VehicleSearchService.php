<?php

namespace Src\VehicleDataCenter\Domain\Services;

use Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMongoRepositoryInterface;

class VehicleSearchService
{
    public function __construct(
        private VehicleMakeRepositoryInterface $makeRepository,
        private VehicleModelRepositoryInterface $modelRepository,
        private VehicleVersionRepositoryInterface $versionRepository,
        private VehicleMongoRepositoryInterface $mongoRepository
    ) {}

    public function search(array $filters): array
    {
        $criteria = [];

        // Build search criteria
        if (isset($filters['make'])) {
            $criteria['make_slug'] = $filters['make'];
        }

        if (isset($filters['model'])) {
            $criteria['model_slug'] = $filters['model'];
        }

        if (isset($filters['year'])) {
            $criteria['year'] = (int) $filters['year'];
        }

        if (isset($filters['keyword'])) {
            $criteria['keyword'] = $filters['keyword'];
        }

        // Search in MongoDB first (faster)
        $results = $this->mongoRepository->search($criteria);

        return [
            'total' => $results->count(),
            'results' => $results->map(function ($doc) {
                return [
                    'id' => $doc->version_id,
                    'make' => $doc->make_slug,
                    'model' => $doc->model_slug,
                    'version' => $doc->version_slug,
                    'year' => $doc->year,
                    'full_name' => $doc->full_name,
                    'enriched_data' => $doc->enriched_data ?? []
                ];
            })->toArray()
        ];
    }

    public function searchBySpecs(array $specs): array
    {
        $query = $this->versionRepository->all();

        // Filter by fuel type
        if (isset($specs['fuel_type'])) {
            $query = $this->versionRepository->getByFuelType($specs['fuel_type']);
        }

        // Filter by year
        if (isset($specs['year'])) {
            $query = $this->versionRepository->getByYear($specs['year']);
        }

        // Additional filtering in collection
        $filtered = $query->filter(function ($version) use ($specs) {
            $versionSpecs = $version->specs;

            if (!$versionSpecs) {
                return false;
            }

            // Check power
            if (isset($specs['min_power_hp']) && $versionSpecs->power_hp < $specs['min_power_hp']) {
                return false;
            }

            if (isset($specs['max_power_hp']) && $versionSpecs->power_hp > $specs['max_power_hp']) {
                return false;
            }

            // Check category
            if (isset($specs['category']) && $version->model->category !== $specs['category']) {
                return false;
            }

            return true;
        });

        return [
            'total' => $filtered->count(),
            'results' => $filtered->map(function ($version) {
                return [
                    'id' => $version->id,
                    'make' => $version->model->make->name,
                    'model' => $version->model->name,
                    'version' => $version->name,
                    'year' => $version->year,
                    'specs' => [
                        'power_hp' => $version->specs->power_hp ?? null,
                        'fuel_type' => $version->fuel_type,
                        'transmission' => $version->transmission
                    ]
                ];
            })->values()->toArray()
        ];
    }

    public function quickSearch(string $query): array
    {
        // Search across makes, models, and versions
        $results = [];

        // Search makes
        $makes = $this->makeRepository->getActive();
        $matchedMakes = $makes->filter(function ($make) use ($query) {
            return stripos($make->name, $query) !== false;
        });

        foreach ($matchedMakes as $make) {
            $results[] = [
                'type' => 'make',
                'id' => $make->id,
                'name' => $make->name,
                'slug' => $make->slug
            ];
        }

        // Search in MongoDB for full vehicle names
        $mongoResults = $this->mongoRepository->search(['keyword' => $query]);

        foreach ($mongoResults->take(10) as $doc) {
            $results[] = [
                'type' => 'vehicle',
                'id' => $doc->version_id,
                'name' => $doc->full_name,
                'make_slug' => $doc->make_slug,
                'model_slug' => $doc->model_slug,
                'year' => $doc->year
            ];
        }

        return [
            'query' => $query,
            'total' => count($results),
            'results' => array_slice($results, 0, 20)
        ];
    }

    public function getByCategory(string $category): array
    {
        $models = $this->modelRepository->getByCategory($category);

        return [
            'category' => $category,
            'total' => $models->count(),
            'models' => $models->map(function ($model) {
                return [
                    'id' => $model->id,
                    'make' => $model->make->name,
                    'model' => $model->name,
                    'slug' => $model->slug,
                    'year_range' => [
                        'start' => $model->year_start,
                        'end' => $model->year_end
                    ]
                ];
            })->toArray()
        ];
    }

    public function getPopular(int $limit = 10): array
    {
        // This would typically come from analytics or a popularity score
        // For now, returning recent active versions
        $versions = $this->versionRepository->all()
            ->sortByDesc('created_at')
            ->take($limit);

        return [
            'total' => $versions->count(),
            'vehicles' => $versions->map(function ($version) {
                return [
                    'id' => $version->id,
                    'make' => $version->model->make->name,
                    'model' => $version->model->name,
                    'version' => $version->name,
                    'year' => $version->year,
                    'category' => $version->model->category
                ];
            })->toArray()
        ];
    }
}

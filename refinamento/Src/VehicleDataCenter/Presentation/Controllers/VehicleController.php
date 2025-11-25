<?php

namespace Src\VehicleDataCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\VehicleDataCenter\Domain\Services\VehicleSearchService;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface;
use Src\VehicleDataCenter\Presentation\ViewModels\VehicleViewModel;
use Src\VehicleDataCenter\Presentation\ViewModels\VehicleListViewModel;

class VehicleController
{
    public function __construct(
        private VehicleMakeRepositoryInterface $makeRepository,
        private VehicleModelRepositoryInterface $modelRepository,
        private VehicleVersionRepositoryInterface $versionRepository,
        private VehicleSearchService $searchService
    ) {}

    public function index()
    {
        $makes = $this->makeRepository->getActive();

        $viewModel = new VehicleListViewModel($makes);

        dd($viewModel );
        
        return view('vehicle-data-center::vehicles.index', [
            'makes' => $viewModel->getMakes()
        ]);
    }

    public function showMake(string $makeSlug)
    {
        $make = $this->makeRepository->findBySlug($makeSlug);

        if (!$make) {
            abort(404, 'Marca não encontrada');
        }

        $models = $this->modelRepository->getByMake($make->id);


        return view('vehicle-data-center::vehicles.make', [
            'make' => $make,
            'models' => $models
        ]);
    }

    public function showModel(string $makeSlug, string $modelSlug)
    {
        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);

        if (!$model) {
            abort(404, 'Modelo não encontrado');
        }

        $versions = $this->versionRepository->getByModel($model->id);

        return view('vehicle-data-center::vehicles.model', [
            'make' => $model->make,
            'model' => $model,
            'versions' => $versions
        ]);
    }

    public function showYear(string $makeSlug, string $modelSlug, int $year)
    {
        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);

        if (!$model) {
            abort(404, 'Modelo não encontrado');
        }

        $versions = $this->versionRepository->getByModel($model->id)
            ->where('year', $year);

        return view('vehicle-data-center::vehicles.year', [
            'make' => $model->make,
            'model' => $model,
            'year' => $year,
            'versions' => $versions
        ]);
    }

    public function showVersion(string $makeSlug, string $modelSlug, int $year, string $versionSlug)
    {
        $version = $this->versionRepository->findBySlug($makeSlug, $modelSlug, $year, $versionSlug);

        if (!$version) {
            abort(404, 'Versão não encontrada');
        }

        $viewModel = new VehicleViewModel($version);

        return view('vehicle-data-center::vehicles.version', [
            'vehicle' => $viewModel->toArray()
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $filters = $request->only(['make', 'model', 'year', 'keyword']);

        $results = $this->searchService->search($filters);

        return response()->json($results);
    }

    public function quickSearch(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'error' => 'Query too short'
            ], 400);
        }

        $results = $this->searchService->quickSearch($query);

        return response()->json($results);
    }

    public function byCategory(string $category): JsonResponse
    {
        $results = $this->searchService->getByCategory($category);

        return response()->json($results);
    }

    public function popular(): JsonResponse
    {
        $results = $this->searchService->getPopular();

        return response()->json($results);
    }
}

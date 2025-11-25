<?php

namespace Src\VehicleDataCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use Src\VehicleDataCenter\Domain\Services\VehicleSearchService;
use Src\VehicleDataCenter\Presentation\ViewModels\VehicleCatalogViewModel;

class VehicleCatalogController
{
    public function __construct(
        private VehicleSearchService $searchService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['category', 'fuel_type', 'year', 'price_min', 'price_max']);

        $results = $this->searchService->search($filters);

        $viewModel = new VehicleCatalogViewModel($results);

        return view('vehicle-data-center::catalog.index', [
            'catalog' => $viewModel->toArray(),
            'filters' => $filters
        ]);
    }

    public function category(string $category)
    {
        $results = $this->searchService->getByCategory($category);

        return view('vehicle-data-center::catalog.category', [
            'category' => $category,
            'vehicles' => $results['models']
        ]);
    }
}

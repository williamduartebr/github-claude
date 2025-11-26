<?php

namespace Src\VehicleDataCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\VehicleDataCenter\Domain\Services\VehicleSearchService;

class VehicleSearchController
{
    public function __construct(
        private VehicleSearchService $searchService
    ) {}

    public function index(Request $request)
    {
        return view('vehicle-data-center::search.index');
    }

    public function results(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $filters = $request->only(['make', 'model', 'year', 'category', 'fuel_type']);

        if ($query) {
            $results = $this->searchService->quickSearch($query);
        } else {
            $results = $this->searchService->search($filters);
        }

        return response()->json($results);
    }

    public function advanced(Request $request): JsonResponse
    {
        $specs = $request->only([
            'fuel_type',
            'year',
            'min_power_hp',
            'max_power_hp',
            'category'
        ]);

        $results = $this->searchService->searchBySpecs($specs);

        return response()->json($results);
    }
}

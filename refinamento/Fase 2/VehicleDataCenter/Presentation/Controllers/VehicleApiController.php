<?php

namespace Src\VehicleDataCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\VehicleDataCenter\Domain\Services\VehicleSearchService;
use Src\VehicleDataCenter\Domain\Services\VehicleSeoBuilderService;
use Src\VehicleDataCenter\Domain\Repositories\VehicleSpecsRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface;

class VehicleApiController
{
    public function __construct(
        private VehicleVersionRepositoryInterface $versionRepository,
        private VehicleSpecsRepositoryInterface $specsRepository,
        private VehicleSearchService $searchService,
        private VehicleSeoBuilderService $seoBuilder
    ) {}

    public function getVersion(int $versionId): JsonResponse
    {
        $version = $this->versionRepository->findById($versionId);

        if (!$version) {
            return response()->json(['error' => 'Version not found'], 404);
        }

        $specs = $this->specsRepository->getCompleteSpecs($versionId);

        return response()->json([
            'version' => $version->toArray(),
            'specs' => $specs
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $filters = $request->all();
        $results = $this->searchService->search($filters);

        return response()->json($results);
    }

    public function getSeo(int $versionId): JsonResponse
    {
        try {
            $seoData = $this->seoBuilder->buildSeoForVersion($versionId);
            return response()->json($seoData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function healthCheck(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'VehicleDataCenter',
            'timestamp' => now()->toIso8601String()
        ]);
    }
}

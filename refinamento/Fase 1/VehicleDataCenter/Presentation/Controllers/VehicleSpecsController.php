<?php

namespace Src\VehicleDataCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\VehicleDataCenter\Domain\Repositories\VehicleSpecsRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface;
use Src\VehicleDataCenter\Presentation\ViewModels\VehicleSpecsViewModel;

class VehicleSpecsController
{
    public function __construct(
        private VehicleVersionRepositoryInterface $versionRepository,
        private VehicleSpecsRepositoryInterface $specsRepository
    ) {}

    public function show(int $versionId)
    {
        $version = $this->versionRepository->findById($versionId);

        if (!$version) {
            abort(404, 'Versão não encontrada');
        }

        $specs = $this->specsRepository->getCompleteSpecs($versionId);

        $viewModel = new VehicleSpecsViewModel($version, $specs);

        return view('vehicle-data-center::specs.show', [
            'specs' => $viewModel->toArray()
        ]);
    }

    public function json(int $versionId): JsonResponse
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

    public function compare(Request $request)
    {
        $versionIds = $request->input('versions', []);

        if (count($versionIds) < 2 || count($versionIds) > 5) {
            return response()->json([
                'error' => 'You must compare between 2 and 5 vehicles'
            ], 400);
        }

        $comparison = [];

        foreach ($versionIds as $versionId) {
            $version = $this->versionRepository->findById($versionId);
            if ($version) {
                $specs = $this->specsRepository->getCompleteSpecs($versionId);
                $comparison[] = [
                    'version' => $version->toArray(),
                    'specs' => $specs
                ];
            }
        }

        return view('vehicle-data-center::specs.compare', [
            'comparison' => $comparison
        ]);
    }
}

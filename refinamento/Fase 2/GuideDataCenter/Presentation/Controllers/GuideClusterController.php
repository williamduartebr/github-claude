<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\Http\JsonResponse;
use Src\GuideDataCenter\Presentation\ViewModels\GuideListViewModel;
use Src\GuideDataCenter\Presentation\ViewModels\GuideClusterViewModel;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Domain\Services\GuideClusterService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;


class GuideClusterController extends Controller
{
    public function __construct(
        private readonly GuideClusterService $clusterService,
        private readonly GuideRepositoryInterface $guideRepository
    ) {}

    /**
     * Exibe cluster de guias para um veículo específico
     */
    public function show(
        Request $request,
        string $make,
        string $model,
        ?string $year = null
    ): View|JsonResponse {
        $yearRange = $year ? $this->parseYearRange($year) : null;

        $clusters = $this->clusterService->getClustersByVehicle($make, $model, $yearRange);

        if ($clusters->isEmpty()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum cluster encontrado para este veículo'
                ], 404);
            }

            abort(404, 'Nenhum cluster encontrado para este veículo');
        }

        // Busca guias relacionados ao cluster
        $guideIds = $clusters->pluck('guide_id')->unique()->toArray();
        $guides = $this->guideRepository->findByIds($guideIds);

        $clusterViewModels = $clusters->map(fn($cluster) => new GuideClusterViewModel($cluster));
        $guideViewModels = $guides->map(fn($guide) => new GuideListViewModel($guide));

        // Agrupa por tipo de cluster
        $groupedClusters = $clusterViewModels->groupBy('cluster_type');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'vehicle' => [
                        'make' => $make,
                        'model' => $model,
                        'year' => $year,
                    ],
                    'clusters' => $groupedClusters->toArray(),
                    'guides' => $guideViewModels->toArray()
                ],
                'meta' => [
                    'total_clusters' => $clusters->count(),
                    'total_guides' => $guides->count(),
                ]
            ]);
        }

        return view('guide::cluster.show', [
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'clusters' => $groupedClusters,
            'guides' => $guideViewModels,
            'title' => $this->buildTitle($make, $model, $year)
        ]);
    }

    /**
     * Lista todos os clusters de um tipo específico
     */
    public function byType(Request $request, string $type): View|JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);

        $clusters = $this->clusterService->getClustersByType($type, $perPage, $page);

        $viewModels = $clusters->map(fn($cluster) => new GuideClusterViewModel($cluster));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $viewModels->toArray(),
                'meta' => [
                    'type' => $type,
                    'total' => $clusters->total(),
                    'per_page' => $clusters->perPage(),
                    'current_page' => $clusters->currentPage(),
                    'last_page' => $clusters->lastPage(),
                ]
            ]);
        }

        return view('guide::cluster.show', [
            'clusters' => $viewModels->groupBy('cluster_type'),
            'guides' => collect([]),
            'title' => "Clusters: {$type}"
        ]);
    }

    /**
     * Converte string de ano para array de range
     */
    private function parseYearRange(string $year): ?array
    {
        if (str_contains($year, '-')) {
            $parts = explode('-', $year);
            return [
                'start' => (int) $parts[0],
                'end' => (int) ($parts[1] ?? $parts[0])
            ];
        }

        $yearInt = (int) $year;
        return ['start' => $yearInt, 'end' => $yearInt];
    }

    /**
     * Constrói título dinâmico
     */
    private function buildTitle(string $make, string $model, ?string $year): string
    {
        $title = ucfirst(str_replace('-', ' ', $make)) . ' ' . ucfirst(str_replace('-', ' ', $model));

        if ($year) {
            $title .= " ({$year})";
        }

        return "Cluster de Guias - {$title}";
    }
}

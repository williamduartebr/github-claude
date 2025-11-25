<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\Http\JsonResponse;
use Src\GuideDataCenter\Presentation\ViewModels\GuideListViewModel;
use Src\GuideDataCenter\Presentation\ViewModels\GuideViewModel;
use Src\GuideDataCenter\Domain\Services\GuideClusterService;
use Src\GuideDataCenter\Domain\Services\GuideSeoService;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;


class GuideController extends Controller
{
    public function __construct(
        private readonly GuideRepositoryInterface $guideRepository,
        private readonly GuideSeoService $seoService,
        private readonly GuideClusterService $clusterService
    ) {}

    /**
     * Exibe um guia específico pelo slug
     */
    public function show(Request $request, string $slug): View|JsonResponse
    {
        $guide = $this->guideRepository->findBySlug($slug);

        if (!$guide) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guia não encontrado'
                ], 404);
            }

            abort(404, 'Guia não encontrado');
        }

        // Busca SEO e clusters relacionados
        $seo = $this->seoService->getSeoByGuideId($guide->_id);
        $clusters = $this->clusterService->getClustersByGuideId($guide->_id);

        $viewModel = new GuideViewModel($guide, $seo, $clusters);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $viewModel->toArray()
            ]);
        }

        return view('guide::guide.show', [
            'guide' => $viewModel
        ]);
    }

    /**
     * Lista guias por marca e modelo do veículo
     */
    public function byModel(
        Request $request,
        string $make,
        string $model,
        ?int $year = null
    ): View|JsonResponse {
        $filters = [
            'make_slug' => $make,
            'model_slug' => $model,
        ];

        if ($year) {
            $filters['year'] = $year;
        }

        $perPage = (int) $request->get('per_page', 15);
        $page = (int) $request->get('page', 1);

        $guides = $this->guideRepository->findByFilters($filters, $perPage, $page);

        $viewModels = $guides->map(fn($guide) => new GuideListViewModel($guide));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $viewModels->toArray(),
                'meta' => [
                    'make' => $make,
                    'model' => $model,
                    'year' => $year,
                    'total' => $guides->total(),
                    'per_page' => $guides->perPage(),
                    'current_page' => $guides->currentPage(),
                    'last_page' => $guides->lastPage(),
                ]
            ]);
        }

        return view('guide::guide.index', [
            'guides' => $viewModels,
            'pagination' => $guides,
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'title' => $this->buildTitle($make, $model, $year)
        ]);
    }

    /**
     * Lista todos os guias com paginação
     */
    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $page = (int) $request->get('page', 1);

        $guides = $this->guideRepository->paginate($perPage, $page);

        $viewModels = $guides->map(fn($guide) => new GuideListViewModel($guide));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $viewModels->toArray(),
                'meta' => [
                    'total' => $guides->total(),
                    'per_page' => $guides->perPage(),
                    'current_page' => $guides->currentPage(),
                    'last_page' => $guides->lastPage(),
                ]
            ]);
        }

        return view('guide::guide.index', [
            'guides' => $viewModels,
            'pagination' => $guides,
            'title' => 'Guias Automotivos'
        ]);
    }

    /**
     * Constrói título dinâmico para listagem
     */
    private function buildTitle(string $make, string $model, ?int $year): string
    {
        $title = ucfirst(str_replace('-', ' ', $make)) . ' ' . ucfirst(str_replace('-', ' ', $model));

        if ($year) {
            $title .= " {$year}";
        }

        return "Guias para {$title}";
    }
}

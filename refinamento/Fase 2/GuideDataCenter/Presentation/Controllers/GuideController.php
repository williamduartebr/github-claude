<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Src\GuideDataCenter\Domain\Services\GuideSeoService;
use Src\GuideDataCenter\Domain\Services\GuideClusterService;
use Src\GuideDataCenter\Presentation\ViewModels\GuideViewModel;
use Src\GuideDataCenter\Presentation\ViewModels\GuideListViewModel;
use Src\GuideDataCenter\Presentation\ViewModels\GuideIndexViewModel;
use Src\GuideDataCenter\Presentation\ViewModels\GuideSpecificViewModel;
use Src\GuideDataCenter\Presentation\ViewModels\GuideCategoryMakeViewModel;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Presentation\ViewModels\GuideCategoryMakeModelViewModel;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;

class GuideController extends Controller
{
    public function __construct(
        private readonly GuideRepositoryInterface $guideRepository,
        private readonly GuideCategoryRepositoryInterface $categoryRepository,
        private readonly VehicleMakeRepositoryInterface $makeRepository,
        private readonly VehicleModelRepositoryInterface $modelRepository,
        private readonly GuideSeoService $seoService,
        private readonly GuideClusterService $clusterService
    ) {}

    /**
     * Página inicial de guias
     * 
     * Rota: GET /guias
     */
    public function index(): View
    {
        $categories = collect([]);
        $makes = collect([]);
        
        $viewModel = new GuideIndexViewModel($categories, $makes);
        
        return view('guide-data-center::guide.index', [
            'categories' => $viewModel->getCategories(),
            'makes' => $viewModel->getMakes(),
            'popularModels' => $viewModel->getPopularModels(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'stats' => $viewModel->getStats(),
        ]);
    }

    /**
     * Lista guias por categoria e marca
     * 
     * Rota: GET /guias/{category}/{make}
     */
    public function categoryMake(
        Request $request,
        string $categorySlug,
        string $makeSlug
    ): View|JsonResponse {
        $category = $this->categoryRepository->findBySlug($categorySlug);
        if (!$category) {
            abort(404, 'Categoria não encontrada');
        }

        $make = $this->makeRepository->findBySlug($makeSlug);
        if (!$make) {
            abort(404, 'Marca não encontrada');
        }

        $models = collect([]);
        $viewModel = new GuideCategoryMakeViewModel($category, $make, $models);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $viewModel->getCategory(),
                    'make' => $viewModel->getMake(),
                    'popular_models' => $viewModel->getPopularModels(),
                    'all_models' => $viewModel->getAllModels(),
                ]
            ]);
        }

        return view('guide-data-center::guide.category-make', [
            'category' => $viewModel->getCategory(),
            'make' => $viewModel->getMake(),
            'popularModels' => $viewModel->getPopularModels(),
            'allModels' => $viewModel->getAllModels(),
            'complementaryCategories' => $viewModel->getComplementaryCategories(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'stats' => $viewModel->getStats(),
        ]);
    }

    /**
     * Exibe guia específico por categoria, marca, modelo e ano
     * 
     * Rota: GET /guias/{category}/{make}/{model-year}
     * View: guide-data-center::guide.specific
     * Exemplos:
     * - /guias/oleo/toyota/corolla-2003
     * - /guias/calibragem/honda/civic-2010
     */
    public function specific(
        Request $request,
        string $categorySlug,
        string $makeSlug,
        string $modelYear
    ): View|JsonResponse {
        // Parse model-year (ex: corolla-2003)
        $parts = explode('-', $modelYear);
        $year = (int) array_pop($parts);
        $modelSlug = implode('-', $parts);

        // Busca categoria
        $category = $this->categoryRepository->findBySlug($categorySlug);
        if (!$category) {
            abort(404, 'Categoria não encontrada');
        }

        // Busca marca
        $make = $this->makeRepository->findBySlug($makeSlug);
        if (!$make) {
            abort(404, 'Marca não encontrada');
        }

        // Busca modelo
        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);
        if (!$model) {
            abort(404, 'Modelo não encontrado');
        }

        // Busca guia específico
        // TODO: Implementar busca real quando houver dados
        $guide = null; // Placeholder

        // Instancia o ViewModel
        $viewModel = new GuideSpecificViewModel($guide, $category, $make, $model, $year);

        // Se requisição JSON
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'guide' => $viewModel->getGuide(),
                    'category' => $viewModel->getCategory(),
                    'make' => $viewModel->getMake(),
                    'model' => $viewModel->getModel(),
                    'year' => $viewModel->getYear(),
                    'specs' => $viewModel->getOfficialSpecs(),
                ]
            ]);
        }

        // Retorna view com dados preparados
        return view('guide-data-center::guide.specific', [
            'guide' => $viewModel->getGuide(),
            'category' => $viewModel->getCategory(),
            'make' => $viewModel->getMake(),
            'model' => $viewModel->getModel(),
            'year' => $viewModel->getYear(),
            'badges' => $viewModel->getBadges(),
            'officialSpecs' => $viewModel->getOfficialSpecs(),
            'compatibleOils' => $viewModel->getCompatibleOils(),
            'changeIntervals' => $viewModel->getChangeIntervals(),
            'severeUseNote' => $viewModel->getSevereUseNote(),
            'relatedGuides' => $viewModel->getRelatedGuides(),
            'essentialCluster' => $viewModel->getEssentialCluster(),
            'disclaimer' => $viewModel->getDisclaimer(),
            'editorialInfo' => $viewModel->getEditorialInfo(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
        ]);
    }

    /**
     * Exibe um guia específico pelo slug
     * 
     * Rota: GET /guias/{slug}
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

        $seo = $this->seoService->getSeoByGuideId($guide->_id);
        $clusters = $this->clusterService->getClustersByGuideId($guide->_id);

        $viewModel = new GuideViewModel($guide, $seo, $clusters);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $viewModel->toArray()
            ]);
        }

        return view('guide-data-center::guide.show', [
            'guide' => $viewModel
        ]);
    }

    /**
     * Lista guias por marca e modelo do veículo
     * 
     * Rota: GET /guias/{make}/{model}/{year?}
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

        return view('guide-data-center::guide.by-model', [
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'guides' => $viewModels
        ]);
    }

    /**
     * Exibe guias por categoria, marca e modelo (lista de anos)
     * 
     * Rota: GET /guias/{category}/{make}/{model}
     * Exemplos:
     * - /guias/oleo/toyota/corolla
     * - /guias/calibragem/honda/civic
     */
    public function categoryMakeModel(
        Request $request,
        string $categorySlug,
        string $makeSlug,
        string $modelSlug
    ): View|JsonResponse {
        // Busca categoria, marca, modelo
        $category = $this->categoryRepository->findBySlug($categorySlug);
        $make = $this->makeRepository->findBySlug($makeSlug);
        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);
        
        $years = collect([]); // Placeholder
        
        $viewModel = new GuideCategoryMakeModelViewModel($category, $make, $model, $years);
        
        return view('guide-data-center::guide.category-make-model', [
            'category' => $viewModel->getCategory(),
            'make' => $viewModel->getMake(),
            'model' => $viewModel->getModel(),
            'availableYears' => $viewModel->getAvailableYears(),
            'stats' => $viewModel->getStats(),
            'complementaryCategories' => $viewModel->getComplementaryCategories(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
        ]);
    }
}
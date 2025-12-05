<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\View\View;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Presentation\ViewModels\GuideYearViewModel;
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

        // ✅ BUSCA GUIAS REAIS DO MONGODB
        $guideModel = app(Guide::class);
        $guides = $guideModel::where('category_slug', $categorySlug)
            ->where('make_slug', $makeSlug)
            ->get();

        $viewModel = new GuideCategoryMakeViewModel($category, $make, $guides);

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

        return view('guide-data-center::guide.category.category-make', [
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
     * Lista versões disponíveis do ano
     * 
     * Rota: GET /guias/{category}/{make}/{model}/{year}
     * Exemplo: /guias/oleo/toyota/corolla/2025
     */
    public function showYear(
        Request $request,
        string $categorySlug,
        string $makeSlug,
        string $modelSlug,
        string $year
    ): View|JsonResponse {
        $category = $this->categoryRepository->findBySlug($categorySlug);
        $make = $this->makeRepository->findBySlug($makeSlug);
        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);

        $viewModel = new GuideYearViewModel($category, $make, $model, $year);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $viewModel->getCategory(),
                    'make' => $viewModel->getMake(),
                    'model' => $viewModel->getModel(),
                    'year' => $viewModel->getYear(),
                    'versions' => $viewModel->getVersions(),
                ]
            ]);
        }

        return view('guide-data-center::guide.year', [
            'category' => $viewModel->getCategory(),
            'make' => $viewModel->getMake(),
            'model' => $viewModel->getModel(),
            'year' => $viewModel->getYear(),
            'availableVersions' => $viewModel->getVersions(),
            'stats' => $viewModel->getStats(),
            'complementaryCategories' => $viewModel->getComplementaryCategories(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
        ]);
    }

    /**
     * Exibe guia completo de uma versão específica
     * 
     * Rota: GET /guias/{category}/{make}/{model}/{year}/{version}
     * View: guide-data-center::guide.specific
     * Exemplo: /guias/oleo/toyota/corolla/2025/gli
     */
    public function showVersion(
        Request $request,
        string $categorySlug,
        string $makeSlug,
        string $modelSlug,
        string $year,
        string $versionSlug
    ): View|JsonResponse {
        $category = $this->categoryRepository->findBySlug($categorySlug);
        if (!$category) {
            abort(404, 'Categoria não encontrada');
        }

        $make = $this->makeRepository->findBySlug($makeSlug);
        if (!$make) {
            abort(404, 'Marca não encontrada');
        }

        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);
        if (!$model) {
            abort(404, 'Modelo não encontrado');
        }

        // TODO: Buscar guia real do banco
        $guide = null;

        // ⭐ IMPORTANTE: Passar $versionSlug para o ViewModel
        $viewModel = new GuideSpecificViewModel(
            $guide,
            $category,
            $make,
            $model,
            (int)$year,
            $versionSlug  // ← Passa a versão
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $viewModel->getGuide(),
            ]);
        }

        return view('guide-data-center::guide.specific', [
            'guide' => $viewModel->getGuide(),
            'category' => $viewModel->getCategory(),
            'make' => $viewModel->getMake(),
            'model' => $viewModel->getModel(),
            'year' => $viewModel->getYear(),
            'version' => $viewModel->getVersion(),  // ← Adicionar versão na view
            'badges' => $viewModel->getBadges(),
            'disclaimer' => $viewModel->getDisclaimer(),
            'officialSpecs' => $viewModel->getOfficialSpecs(),
            'compatibleOils' => $viewModel->getCompatibleOils(),
            'changeIntervals' => $viewModel->getChangeIntervals(),
            'severeUseNote' => $viewModel->getSevereUseNote(),
            'relatedGuides' => $viewModel->getRelatedGuides(),
            'essentialCluster' => $viewModel->getEssentialCluster(),
            'editorialInfo' => $viewModel->getEditorialInfo(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
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
        $category = $this->categoryRepository->findBySlug($categorySlug);
        if (!$category) {
            abort(404, 'Categoria não encontrada');
        }

        $make = $this->makeRepository->findBySlug($makeSlug);
        if (!$make) {
            abort(404, 'Marca não encontrada');
        }

        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);
        if (!$model) {
            abort(404, 'Modelo não encontrado');
        }

        // ✅ BUSCA GUIAS REAIS DO MONGODB
        $guideModel = app(Guide::class);
        $guides = $guideModel::where('category_slug', $categorySlug)
            ->where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->get();

        $viewModel = new GuideCategoryMakeModelViewModel($category, $make, $model, $guides);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $viewModel->getCategory(),
                    'make' => $viewModel->getMake(),
                    'model' => $viewModel->getModel(),
                    'available_years' => $viewModel->getAvailableYears(),
                    'stats' => $viewModel->getStats(),
                    'complementary_categories' => $viewModel->getComplementaryCategories(),
                ]
            ]);
        }

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

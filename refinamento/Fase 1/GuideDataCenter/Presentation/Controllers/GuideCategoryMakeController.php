<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Presentation\Traits\HasGuideMetaTags;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface;
use Src\GuideDataCenter\Presentation\ViewModels\GuideCategoryMakeViewModel;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;

/**
 * GuideCategoryMakeController
 * 
 * Responsabilidade: Listar modelos disponíveis para categoria + marca
 * Rota: GET /guias/{category}/{make}
 * Exemplo: /guias/oleo/toyota
 * 
 * Princípios SOLID:
 * - SRP: Apenas gerencia listagem de modelos
 * - DIP: Depende de abstrações (interfaces)
 * - OCP: Aberto para extensão via trait
 * 
 * @package Src\GuideDataCenter\Presentation\Controllers
 */
class GuideCategoryMakeController extends Controller
{
    use HasGuideMetaTags;

    public function __construct(
        private readonly GuideCategoryRepositoryInterface $categoryRepository,
        private readonly VehicleMakeRepositoryInterface $makeRepository
    ) {}

    /**
     * Exibe modelos disponíveis para categoria + marca
     * 
     * Rota: GET /guias/{category}/{make}
     * View: guide-data-center::guide.category.category-make
     * Exemplo: /guias/oleo/toyota
     * 
     * @param Request $request
     * @param string $categorySlug
     * @param string $makeSlug
     * @return View|JsonResponse
     */
    public function __invoke(
        Request $request,
        string $categorySlug,
        string $makeSlug
    ): View|JsonResponse {
        // Validar entidades
        $category = $this->categoryRepository->findBySlug($categorySlug);
        if (!$category) {
            abort(404, 'Categoria não encontrada');
        }

        $make = $this->makeRepository->findBySlug($makeSlug);
        if (!$make) {
            abort(404, 'Marca não encontrada');
        }

        // Buscar guias no MongoDB
        $guides = $this->findGuides($categorySlug, $makeSlug);

        // Instanciar ViewModel
        $viewModel = new GuideCategoryMakeViewModel($category, $make, $guides);

        // Resposta JSON (API)
        if ($request->wantsJson()) {
            return $this->jsonResponse($viewModel);
        }

        // Configurar SEO
        $this->setGuideMetaTags($viewModel->getSeoData());

        // Dados para view
        $viewData = [
            'category' => $viewModel->getCategory(),
            'make' => $viewModel->getMake(),
            'popularModels' => $viewModel->getPopularModels(),
            'allModels' => $viewModel->getAllModels(),
            'complementaryCategories' => $viewModel->getComplementaryCategories(),
            'stats' => $viewModel->getStats(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'structured_data' => $viewModel->getStructuredData(),
        ];

        return view('guide-data-center::guide.category.category-make', $viewData);
    }

    /**
     * Busca guias no MongoDB
     * 
     * @param string $categorySlug
     * @param string $makeSlug
     * @return \Illuminate\Support\Collection
     */
    private function findGuides(string $categorySlug, string $makeSlug)
    {
        $guideModel = app(Guide::class);

        return $guideModel::where('category_slug', $categorySlug)
            ->where('make_slug', $makeSlug)
            ->get();
    }

    /**
     * Retorna resposta JSON
     * 
     * @param GuideCategoryMakeViewModel $viewModel
     * @return JsonResponse
     */
    private function jsonResponse(GuideCategoryMakeViewModel $viewModel): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'category' => $viewModel->getCategory(),
                'make' => $viewModel->getMake(),
                'popular_models' => $viewModel->getPopularModels(),
                'all_models' => $viewModel->getAllModels(),
                'stats' => $viewModel->getStats(),
                'structured_data' => $viewModel->getStructuredData(),
            ],
        ]);
    }
}
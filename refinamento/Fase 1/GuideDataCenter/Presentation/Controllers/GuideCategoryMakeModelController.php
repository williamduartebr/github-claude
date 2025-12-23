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
use Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface;
use Src\GuideDataCenter\Presentation\ViewModels\GuideCategoryMakeModelViewModel;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;

/**
 * GuideCategoryMakeModelController
 * 
 * Responsabilidade: Listar anos disponíveis para categoria + marca + modelo
 * Rota: GET /guias/{category}/{make}/{model}
 * Exemplo: /guias/oleo/toyota/corolla
 * 
 * Princípios SOLID:
 * - SRP: Apenas gerencia listagem de anos
 * - DIP: Depende de abstrações (interfaces)
 * - OCP: Aberto para extensão via trait
 * 
 * @package Src\GuideDataCenter\Presentation\Controllers
 */
class GuideCategoryMakeModelController extends Controller
{
    use HasGuideMetaTags;

    public function __construct(
        private readonly GuideCategoryRepositoryInterface $categoryRepository,
        private readonly VehicleMakeRepositoryInterface $makeRepository,
        private readonly VehicleModelRepositoryInterface $modelRepository
    ) {}

    /**
     * Exibe anos disponíveis para categoria + marca + modelo
     * 
     * Rota: GET /guias/{category}/{make}/{model}
     * View: guide-data-center::guide.category.category-make-model
     * Exemplo: /guias/oleo/toyota/corolla
     * 
     * @param Request $request
     * @param string $categorySlug
     * @param string $makeSlug
     * @param string $modelSlug
     * @return View|JsonResponse
     */
    public function __invoke(
        Request $request,
        string $categorySlug,
        string $makeSlug,
        string $modelSlug
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

        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);
        if (!$model) {
            abort(404, 'Modelo não encontrado');
        }

        // Buscar guias no MongoDB
        $guides = $this->findGuides($categorySlug, $makeSlug, $modelSlug);

        // Instanciar ViewModel
        $viewModel = new GuideCategoryMakeModelViewModel($category, $make, $model, $guides);

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
            'model' => $viewModel->getModel(),
            'availableYears' => $viewModel->getAvailableYears(),
            'stats' => $viewModel->getStats(),
            'complementaryCategories' => $viewModel->getComplementaryCategories(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'structured_data' => $viewModel->getStructuredData(),
        ];

        return view('guide-data-center::guide.category.category-make-model', $viewData);
    }

    /**
     * Busca guias no MongoDB
     * 
     * @param string $categorySlug
     * @param string $makeSlug
     * @param string $modelSlug
     * @return \Illuminate\Support\Collection
     */
    private function findGuides(string $categorySlug, string $makeSlug, string $modelSlug)
    {
        $guideModel = app(Guide::class);

        return $guideModel::where('category_slug', $categorySlug)
            ->where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->get();
    }

    /**
     * Retorna resposta JSON
     * 
     * @param GuideCategoryMakeModelViewModel $viewModel
     * @return JsonResponse
     */
    private function jsonResponse(GuideCategoryMakeModelViewModel $viewModel): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'category' => $viewModel->getCategory(),
                'make' => $viewModel->getMake(),
                'model' => $viewModel->getModel(),
                'available_years' => $viewModel->getAvailableYears(),
                'stats' => $viewModel->getStats(),
                'complementary_categories' => $viewModel->getComplementaryCategories(),
                'structured_data' => $viewModel->getStructuredData(),
            ],
        ]);
    }
}
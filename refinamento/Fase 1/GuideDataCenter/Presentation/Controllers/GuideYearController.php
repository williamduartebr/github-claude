<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Src\GuideDataCenter\Presentation\Traits\HasGuideMetaTags;
use Src\GuideDataCenter\Presentation\ViewModels\GuideYearViewModel;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;

/**
 * GuideYearController
 * 
 * Responsabilidade: Listar versões disponíveis para um ano específico
 * Rota: GET /guias/{category}/{make}/{model}/{year}
 * Exemplo: /guias/oleo/toyota/corolla/2025
 * 
 * Princípios SOLID:
 * - SRP: Apenas gerencia listagem de versões por ano
 * - DIP: Depende de abstrações (interfaces)
 * - OCP: Aberto para extensão via trait
 * 
 * @package Src\GuideDataCenter\Presentation\Controllers
 */
class GuideYearController extends Controller
{
    use HasGuideMetaTags;

    public function __construct(
        private readonly GuideCategoryRepositoryInterface $categoryRepository,
        private readonly VehicleMakeRepositoryInterface $makeRepository,
        private readonly VehicleModelRepositoryInterface $modelRepository
    ) {}

    /**
     * Lista versões disponíveis do ano
     * 
     * Rota: GET /guias/{category}/{make}/{model}/{year}
     * View: guide-data-center::guide.year
     * Exemplo: /guias/oleo/toyota/corolla/2025
     * 
     * @param Request $request
     * @param string $categorySlug
     * @param string $makeSlug
     * @param string $modelSlug
     * @param string $year
     * @return View|JsonResponse
     */
    public function __invoke(
        Request $request,
        string $categorySlug,
        string $makeSlug,
        string $modelSlug,
        string $year
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

        // Instanciar ViewModel
        $viewModel = new GuideYearViewModel($category, $make, $model, $year);

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
            'year' => $viewModel->getYear(),
            'availableVersions' => $viewModel->getVersions(),
            'stats' => $viewModel->getStats(),
            'complementaryCategories' => $viewModel->getComplementaryCategories(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'structured_data' => $viewModel->getStructuredData(), // ✅ ADICIONADO
        ];

        return view('guide-data-center::guide.year.index', $viewData);
    }

    /**
     * Retorna resposta JSON
     * 
     * @param GuideYearViewModel $viewModel
     * @return JsonResponse
     */
    private function jsonResponse(GuideYearViewModel $viewModel): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'category' => $viewModel->getCategory(),
                'make' => $viewModel->getMake(),
                'model' => $viewModel->getModel(),
                'year' => $viewModel->getYear(),
                'versions' => $viewModel->getVersions(),
                'structured_data' => $viewModel->getStructuredData(),
            ],
        ]);
    }
}

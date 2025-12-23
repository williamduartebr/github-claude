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
use Src\GuideDataCenter\Presentation\ViewModels\GuideMakeModelViewModel;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;

/**
 * GuideMakeModelController
 * 
 * Responsabilidade: Exibir visão geral de um modelo (todas as categorias)
 * Rota: GET /guias/marca/{make}/{model}
 * Exemplo: /guias/marca/toyota/corolla
 * 
 * Princípios SOLID:
 * - SRP: Apenas gerencia visão geral do modelo
 * - DIP: Depende de abstrações (interfaces)
 * - OCP: Aberto para extensão via trait
 * 
 * @package Src\GuideDataCenter\Presentation\Controllers
 */
class GuideMakeModelController extends Controller
{
    use HasGuideMetaTags;

    public function __construct(
        private readonly VehicleMakeRepositoryInterface $makeRepository,
        private readonly VehicleModelRepositoryInterface $modelRepository,
        private readonly GuideCategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Exibe visão geral de guias para um modelo
     * 
     * Rota: GET /guias/marca/{make}/{model}
     * View: guide-data-center::guide.make-model
     * Exemplo: /guias/marca/toyota/corolla
     * 
     * @param Request $request
     * @param string $makeSlug
     * @param string $modelSlug
     * @return View|JsonResponse
     */
    public function __invoke(
        Request $request,
        string $makeSlug,
        string $modelSlug
    ): View|JsonResponse {
        // Validar entidades
        $make = $this->makeRepository->findBySlug($makeSlug);
        if (!$make) {
            abort(404, 'Marca não encontrada');
        }

        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);
        if (!$model) {
            abort(404, 'Modelo não encontrado');
        }

        // Buscar todas as categorias
        $categories = $this->categoryRepository->getActive();

        // Buscar guias no MongoDB (todas as categorias)
        $guides = $this->findGuides($makeSlug, $modelSlug);

        // Instanciar ViewModel
        $viewModel = new GuideMakeModelViewModel($make, $model, $guides, $categories);

        // Resposta JSON (API)
        if ($request->wantsJson()) {
            return $this->jsonResponse($viewModel);
        }

        // Configurar SEO
        $this->setGuideMetaTags($viewModel->getSeoData());

        // Dados para view
        $viewData = [
            'make' => $viewModel->getMake(),
            'model' => $viewModel->getModel(),
            'categoriesWithGuides' => $viewModel->getCategoriesWithGuides(),
            'yearsList' => $viewModel->getYearsList(),
            'relatedModels' => $viewModel->getRelatedModels(),
            'stats' => $viewModel->getStats(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'structured_data' => $viewModel->getStructuredData(),
        ];

        return view('guide-data-center::guide.make.make-model', $viewData);
    }

    /**
     * Busca guias no MongoDB
     * 
     * @param string $makeSlug
     * @param string $modelSlug
     * @return \Illuminate\Support\Collection
     */
    private function findGuides(string $makeSlug, string $modelSlug)
    {
        $guideModel = app(Guide::class);

        return $guideModel::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->get();
    }

    /**
     * Retorna resposta JSON
     * 
     * @param GuideMakeModelViewModel $viewModel
     * @return JsonResponse
     */
    private function jsonResponse(GuideMakeModelViewModel $viewModel): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'make' => $viewModel->getMake(),
                'model' => $viewModel->getModel(),
                'categories_with_guides' => $viewModel->getCategoriesWithGuides(),
                'years_list' => $viewModel->getYearsList(),
                'related_models' => $viewModel->getRelatedModels(),
                'stats' => $viewModel->getStats(),
                'structured_data' => $viewModel->getStructuredData(),
            ],
        ]);
    }
}
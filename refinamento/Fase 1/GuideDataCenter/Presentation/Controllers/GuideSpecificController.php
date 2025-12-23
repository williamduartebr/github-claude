<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Presentation\Traits\HasGuideMetaTags;
use Src\GuideDataCenter\Domain\Services\GuideRelationshipService;
use Src\GuideDataCenter\Presentation\ViewModels\GuideSpecificViewModel;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;

/**
 * GuideSpecificController
 * 
 * Responsabilidade: Exibir guia técnico específico completo
 * Rota: GET /guias/{category}/{make}/{model}/{year}/{version}
 * Exemplo: /guias/oleo/toyota/corolla/2025/gli
 * 
 * Princípios SOLID:
 * - SRP: Apenas gerencia visualização de guia específico
 * - DIP: Depende de abstrações (interfaces e services)
 * - OCP: Aberto para extensão via trait e services opcionais
 * 
 * @package Src\GuideDataCenter\Presentation\Controllers
 */
class GuideSpecificController extends Controller
{
    use HasGuideMetaTags;

    public function __construct(
        private readonly GuideCategoryRepositoryInterface $categoryRepository,
        private readonly VehicleMakeRepositoryInterface $makeRepository,
        private readonly VehicleModelRepositoryInterface $modelRepository,
        private readonly ?GuideRelationshipService $relationshipService = null
    ) {}

    /**
     * Exibe guia específico completo
     * 
     * Rota: GET /guias/{category}/{make}/{model}/{year}/{version}
     * View: guide-data-center::guide.specific.index
     * Exemplo: /guias/oleo/toyota/corolla/2025/gli
     * 
     * @param Request $request
     * @param string $categorySlug
     * @param string $makeSlug
     * @param string $modelSlug
     * @param string $year
     * @param string $versionSlug
     * @return View|JsonResponse
     */
    public function __invoke(
        Request $request,
        string $categorySlug,
        string $makeSlug,
        string $modelSlug,
        string $year,
        string $versionSlug
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

        // Buscar guia no MongoDB
        $guide = $this->findGuide(
            $categorySlug,
            $makeSlug,
            $modelSlug,
            $versionSlug,
            (int) $year
        );

        // Instanciar ViewModel
        $viewModel = new GuideSpecificViewModel(
            $guide,
            $category,
            $make,
            $model,
            (int) $year,
            $versionSlug
        );

        // Resposta JSON (API)
        if ($request->wantsJson()) {
            return $this->jsonResponse($viewModel);
        }

        // Preparar dados para view
        $viewData = $this->prepareViewData($viewModel);

        // Adicionar relacionamentos extras (se disponível)
        if ($this->relationshipService && $guide) {
            $viewData = $this->addRelationships($viewData, $guide, $makeSlug, $modelSlug, (int) $year, $category);
        }

        // Configurar SEO
        $this->setGuideMetaTags($viewModel->getSeoData());

        return view('guide-data-center::guide.specific.index', $viewData);
    }

    /**
     * Busca guia no MongoDB
     * 
     * @param string $categorySlug
     * @param string $makeSlug
     * @param string $modelSlug
     * @param string $versionSlug
     * @param int $year
     * @return Guide|null
     */
    private function findGuide(
        string $categorySlug,
        string $makeSlug,
        string $modelSlug,
        string $versionSlug,
        int $year
    ): ?Guide {
        $guideModel = app(Guide::class);

        return $guideModel::where('category_slug', $categorySlug)
            ->where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('version_slug', $versionSlug)
            ->where('year_start', '<=', $year)
            ->where('year_end', '>=', $year)
            ->first();
    }

    /**
     * Prepara dados básicos para view
     * 
     * @param GuideSpecificViewModel $viewModel
     * @return array
     */
    private function prepareViewData(GuideSpecificViewModel $viewModel): array
    {
        return [
            'guide' => $viewModel->getGuide(),
            'category' => $viewModel->getCategory(),
            'make' => $viewModel->getMake(),
            'model' => $viewModel->getModel(),
            'year' => $viewModel->getYear(),
            'version' => $viewModel->getVersion(),
            'badges' => $viewModel->getBadges(),
            'disclaimer' => $viewModel->getDisclaimer(),
            'relatedGuides' => $viewModel->getRelatedGuides(),
            'essentialCluster' => $viewModel->getEssentialCluster(),
            'editorialInfo' => $viewModel->getEditorialInfo(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'structured_data' => $viewModel->getStructuredData(), // ✅ ADICIONADO
        ];
    }

    /**
     * Adiciona relacionamentos extras via GuideRelationshipService
     * 
     * @param array $viewData
     * @param Guide $guide
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int $year
     * @param mixed $category
     * @return array
     */
    private function addRelationships(
        array $viewData,
        Guide $guide,
        string $makeSlug,
        string $modelSlug,
        int $year,
        $category
    ): array {
        try {
            $viewData['relatedGuidesExtra'] = $this->relationshipService->getRelatedGuides($guide, 8);
            $viewData['essentialContents'] = $this->relationshipService->getEssentialContents($guide, 8);
            $viewData['sameCategoryGuides'] = $this->relationshipService->getSameCategoryGuides($guide, 6);
            
            $viewData['availableYearsExtra'] = $this->relationshipService->getAvailableYears(
                $makeSlug,
                $modelSlug,
                (string) $category->_id
            );
            
            $viewData['availableVersionsExtra'] = $this->relationshipService->getAvailableVersions(
                $makeSlug,
                $modelSlug,
                $year,
                (string) $category->_id
            );
        } catch (\Exception $e) {
            Log::warning('GuideRelationships error: ' . $e->getMessage());
        }

        return $viewData;
    }

    /**
     * Retorna resposta JSON
     * 
     * @param GuideSpecificViewModel $viewModel
     * @return JsonResponse
     */
    private function jsonResponse(GuideSpecificViewModel $viewModel): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'guide' => $viewModel->getGuide(),
                'seo' => $viewModel->getSeoData(),
                'structured_data' => $viewModel->getStructuredData(),
            ],
        ]);
    }
}

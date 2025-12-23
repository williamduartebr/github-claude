<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Src\GuideDataCenter\Presentation\ViewModels\GuideCategoryPageViewModel;
use Src\GuideDataCenter\Presentation\Traits\HasGuideMetaTags;

/**
 * GuideCategoryController
 * 
 * Responsabilidade: Exibir página de categoria com guias paginados
 * Rota: GET /guias/{category}
 * Exemplo: /guias/oleo
 * 
 * Princípios SOLID:
 * - SRP: Métodos privados para responsabilidades específicas
 * - DIP: Depende de abstrações (interfaces)
 * - OCP: Aberto para extensão via trait
 * 
 * @package Src\GuideDataCenter\Presentation\Controllers
 */
class GuideCategoryController extends Controller
{
    use HasGuideMetaTags;

    private const GUIDES_PER_PAGE = 6;

    public function __construct(
        private readonly GuideCategoryRepositoryInterface $categoryRepository,
    ) {}

    /**
     * Exibe página de categoria com guias paginados
     * 
     * @param Request $request
     * @param string $categorySlug
     * @return View|JsonResponse
     */
    public function __invoke(Request $request, string $categorySlug): View|JsonResponse
    {
        // Validar categoria
        $category = $this->validateCategory($categorySlug);

        // Calcular paginação
        $currentPage = $this->getCurrentPage($request);

        // Buscar guias da categoria
        $allGuides = $this->fetchCategoryGuides($categorySlug);
        $totalGuides = $allGuides->count();

        // Calcular total de páginas
        $totalPages = $this->calculateTotalPages($totalGuides);

        // Validar página atual
        $this->validateCurrentPage($currentPage, $totalPages, $totalGuides);

        // Paginar guias
        $paginatedGuides = $this->paginateGuides($allGuides, $currentPage);

        // Buscar marcas
        $makes = $this->fetchMakes($allGuides);

        // Criar ViewModel
        $viewModel = new GuideCategoryPageViewModel(
            $category,
            $paginatedGuides,
            $makes,
            $currentPage,
            $totalPages,
            $totalGuides
        );

        // Resposta baseada no tipo de request
        return $request->wantsJson()
            ? $this->jsonResponse($viewModel)
            : $this->viewResponse($viewModel);
    }

    /**
     * Valida e retorna categoria
     * 
     * @param string $categorySlug
     * @return GuideCategory
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function validateCategory(string $categorySlug): GuideCategory
    {
        $category = $this->categoryRepository->findBySlug($categorySlug);

        if (!$category) {
            abort(404, 'Categoria não encontrada');
        }

        return $category;
    }

    /**
     * Obtém página atual da request
     * 
     * @param Request $request
     * @return int
     */
    private function getCurrentPage(Request $request): int
    {
        return max(1, (int) $request->get('page', 1));
    }

    /**
     * Busca todos os guias da categoria
     * 
     * @param string $categorySlug
     * @return Collection
     */
    private function fetchCategoryGuides(string $categorySlug): Collection
    {
        return Guide::where('category_slug', $categorySlug)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Calcula total de páginas
     * 
     * @param int $totalGuides
     * @return int
     */
    private function calculateTotalPages(int $totalGuides): int
    {
        return max(1, (int) ceil($totalGuides / self::GUIDES_PER_PAGE));
    }

    /**
     * Valida se página atual existe
     * 
     * @param int $currentPage
     * @param int $totalPages
     * @param int $totalGuides
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function validateCurrentPage(int $currentPage, int $totalPages, int $totalGuides): void
    {
        if ($currentPage > $totalPages && $totalGuides > 0) {
            abort(404, 'Página não encontrada');
        }
    }

    /**
     * Pagina guias
     * 
     * @param Collection $allGuides
     * @param int $currentPage
     * @return Collection
     */
    private function paginateGuides(Collection $allGuides, int $currentPage): Collection
    {
        $offset = ($currentPage - 1) * self::GUIDES_PER_PAGE;
        
        return $allGuides->slice($offset, self::GUIDES_PER_PAGE)->values();
    }

    /**
     * Busca marcas dos guias
     * 
     * @param Collection $guides
     * @return Collection
     */
    private function fetchMakes(Collection $guides): Collection
    {
        // Extrair IDs únicos de marcas
        $makeIds = $guides
            ->pluck('vehicle_make_id')
            ->unique()
            ->filter()
            ->values();

        // Buscar marcas específicas se houver IDs
        if ($makeIds->isNotEmpty()) {
            return VehicleMake::whereIn('id', $makeIds->toArray())
                ->orderBy('name', 'asc')
                ->get();
        }

        // Fallback: retornar marcas ativas padrão
        return $this->getDefaultMakes();
    }

    /**
     * Retorna marcas padrão quando não há guias
     * 
     * @return Collection
     */
    private function getDefaultMakes(): Collection
    {
        return VehicleMake::where('is_active', true)
            ->orderBy('name', 'asc')
            ->limit(20)
            ->get();
    }

    /**
     * Retorna resposta JSON
     * 
     * @param GuideCategoryPageViewModel $viewModel
     * @return JsonResponse
     */
    private function jsonResponse(GuideCategoryPageViewModel $viewModel): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'category' => $viewModel->getCategory(),
                'popular_guides' => $viewModel->getPopularGuides(),
                'makes' => $viewModel->getMakes(),
                'pagination' => $viewModel->getPagination(),
                'structured_data' => $viewModel->getStructuredData(),
            ]
        ]);
    }

    /**
     * Retorna resposta View
     * 
     * @param GuideCategoryPageViewModel $viewModel
     * @return View
     */
    private function viewResponse(GuideCategoryPageViewModel $viewModel): View
    {
        // ✅ Configurar SEO usando trait
        $this->setGuideMetaTags($viewModel->getSeoData());

        return view('guide-data-center::guide.category.index', [
            'category' => $viewModel->getCategory(),
            'relatedCategories' => $viewModel->getRelatedCategories(),
            'heroImage' => $viewModel->getHeroImage(),
            'popularGuides' => $viewModel->getPopularGuides(),
            'makes' => $viewModel->getMakes(),
            'evergreenContent' => $viewModel->getEvergreenContent(),
            'faqs' => $viewModel->getFaqs(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'pagination' => $viewModel->getPagination(),
            'structured_data' => $viewModel->getStructuredData(), // ✅ NOVO
        ]);
    }
}
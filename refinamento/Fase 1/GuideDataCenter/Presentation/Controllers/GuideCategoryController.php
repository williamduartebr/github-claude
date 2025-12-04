<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Presentation\ViewModels\GuideCategoryViewModel;
use Src\GuideDataCenter\Presentation\ViewModels\GuideCategoryPageViewModel;

class GuideCategoryController extends Controller
{
    public function __construct(
        private readonly GuideCategoryRepositoryInterface $categoryRepository,
        private readonly GuideRepositoryInterface $guideRepository
    ) {}

    /**
     * Lista todas as categorias disponíveis
     * 
     * Rota: GET /guias/categorias
     */
    public function all(Request $request): View|JsonResponse
    {
        $categories = $this->categoryRepository->getActive();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $categories->map(fn($cat) => [
                    'id' => $cat->_id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'icon' => $cat->icon,
                    'description' => $cat->description,
                ])
            ]);
        }

        return view('guide-data-center::category.all', [
            'categories' => $categories
        ]);
    }

    /**
     * Exibe página de uma categoria específica
     * 
     * Rota: GET /guias/{category}
     * View: guide-data-center::category.index
     * Exemplos: /guias/oleo, /guias/calibragem
     */
    public function index(Request $request, string $categorySlug): View|JsonResponse
    {
        // Busca a categoria pelo slug
        $category = $this->categoryRepository->findBySlug($categorySlug);

        if (!$category) {
            abort(404, 'Categoria não encontrada');
        }

        // Busca guias desta categoria
        // TODO: Implementar busca real quando houver dados
        $guides = collect([]); // Placeholder
        $makes = collect([]); // Placeholder

        // Instancia o ViewModel
        $viewModel = new GuideCategoryPageViewModel($category, $guides, $makes);

        // Se requisição JSON
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $viewModel->getCategory(),
                    'popular_guides' => $viewModel->getPopularGuides(),
                    'makes' => $viewModel->getMakes(),
                ]
            ]);
        }

        // Retorna view com dados preparados
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
        ]);
    }
}
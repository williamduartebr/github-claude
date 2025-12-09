<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface;
use Src\GuideDataCenter\Presentation\ViewModels\GuideMakeViewModel;

class GuideMakeController extends Controller
{
    public function __construct(
        private readonly GuideRepositoryInterface $guideRepository,
        private readonly GuideCategoryRepositoryInterface $categoryRepository,
        private readonly VehicleMakeRepositoryInterface $makeRepository
    ) {}

    /**
     * Exibe página de marca (todos os guias de uma marca)
     * 
     * Rota: GET /guias/marca/{make}
     * View: guide-data-center::make.index
     * Exemplo: /guias/marca/toyota
     */
    public function index(Request $request, string $makeSlug): View|JsonResponse
    {
        // Buscar marca no VehicleDataCenter (MySQL)
        $make = $this->makeRepository->findBySlug($makeSlug);
        
        if (!$make) {
            abort(404, 'Marca não encontrada');
        }

        // Buscar todos os guias desta marca (MongoDB)
        $guides = $this->guideRepository->findByFilters([
            'make_slug' => $makeSlug,
            'limit' => 500,
        ]);

        // Buscar categorias que têm guias desta marca
        $availableCategories = $this->getAvailableCategories($guides);

        // Instanciar ViewModel
        $viewModel = new GuideMakeViewModel(
            $make,
            $guides,
            $availableCategories,
            $this->guideRepository
        );

        // Se requisição JSON
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'make' => $viewModel->getMake(),
                    'categories' => $viewModel->getCategories(),
                    'popular_models' => $viewModel->getPopularModels(),
                    'stats' => $viewModel->getStats(),
                ]
            ]);
        }

        // Retornar view
        return view('guide-data-center::guide.make', [
            'make' => $viewModel->getMake(),
            'categories' => $viewModel->getCategories(),
            'popularModels' => $viewModel->getPopularModels(),
            'allModels' => $viewModel->getAllModels(),
            'stats' => $viewModel->getStats(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
        ]);
    }

    /**
     * Extrai categorias únicas dos guias
     */
    private function getAvailableCategories($guides)
    {
        $categorySlugs = $guides->pluck('category_slug')->unique()->filter();
        
        $categories = collect();
        foreach ($categorySlugs as $slug) {
            $category = $this->categoryRepository->findBySlug($slug);
            if ($category) {
                $categories->push($category);
            }
        }
        
        return $categories;
    }
}
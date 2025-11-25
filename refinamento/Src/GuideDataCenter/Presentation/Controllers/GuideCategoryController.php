<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\Http\JsonResponse;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

use Src\GuideDataCenter\Presentation\ViewModels\GuideCategoryViewModel;
use Src\GuideDataCenter\Presentation\ViewModels\GuideListViewModel;

class GuideCategoryController extends Controller
{
    public function __construct(
        private readonly GuideCategoryRepositoryInterface $categoryRepository,
        private readonly GuideRepositoryInterface $guideRepository
    ) {}

    /**
     * Lista guias por categoria
     */
    public function index(Request $request, string $category): View|JsonResponse
    {
        $categoryModel = $this->categoryRepository->findBySlug($category);

        if (!$categoryModel) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria não encontrada'
                ], 404);
            }

            abort(404, 'Categoria não encontrada');
        }

        $perPage = (int) $request->get('per_page', 15);
        $page = (int) $request->get('page', 1);

        $guides = $this->guideRepository->findByCategory($categoryModel->_id, $perPage, $page);

        $categoryViewModel = new GuideCategoryViewModel($categoryModel);
        $guideViewModels = $guides->map(fn($guide) => new GuideListViewModel($guide));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $categoryViewModel->toArray(),
                    'guides' => $guideViewModels->toArray()
                ],
                'meta' => [
                    'total' => $guides->total(),
                    'per_page' => $guides->perPage(),
                    'current_page' => $guides->currentPage(),
                    'last_page' => $guides->lastPage(),
                ]
            ]);
        }

        return view('guide::category.index', [
            'category' => $categoryViewModel,
            'guides' => $guideViewModels,
            'pagination' => $guides
        ]);
    }

    /**
     * Lista todas as categorias ativas
     */
    public function all(Request $request): View|JsonResponse
    {
        $categories = $this->categoryRepository->findAllActive();

        $viewModels = $categories->map(fn($cat) => new GuideCategoryViewModel($cat));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $viewModels->toArray()
            ]);
        }

        return view('guide::category.index', [
            'categories' => $viewModels,
            'guides' => collect([]),
            'pagination' => null
        ]);
    }
}

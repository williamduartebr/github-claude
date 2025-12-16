<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Presentation\ViewModels\GuideCategoryPageViewModel;

class GuideCategoryController extends Controller
{
    public function __construct(
        private readonly GuideCategoryRepositoryInterface $categoryRepository,
        private readonly GuideRepositoryInterface $guideRepository
    ) {}

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

    public function index(Request $request, string $categorySlug): View|JsonResponse
    {
        $category = $this->categoryRepository->findBySlug($categorySlug);

        if (!$category) {
            abort(404, 'Categoria não encontrada');
        }

        $currentPage = max(1, (int) $request->get('page', 1));
        $perPage = 6;
        $offset = ($currentPage - 1) * $perPage;

        $allGuides = Guide::where('category_slug', $categorySlug)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalGuides = $allGuides->count();
        $totalPages = max(1, (int) ceil($totalGuides / $perPage));

        if ($currentPage > $totalPages && $totalGuides > 0) {
            abort(404, 'Página não encontrada');
        }

        $guides = $allGuides->slice($offset, $perPage)->values();

        $makeIds = $allGuides->pluck('vehicle_make_id')
            ->unique()
            ->filter()
            ->values();

        $makes = collect([]);
        if ($makeIds->isNotEmpty()) {
            $makes = VehicleMake::whereIn('id', $makeIds->toArray())
                ->orderBy('name', 'asc')
                ->get();
        }

        if ($makes->isEmpty()) {
            $makes = VehicleMake::where('is_active', true)
                ->orderBy('name', 'asc')
                ->limit(20)
                ->get();
        }

        $viewModel = new GuideCategoryPageViewModel(
            $category, 
            $guides, 
            $makes,
            $currentPage,
            $totalPages,
            $totalGuides
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $viewModel->getCategory(),
                    'popular_guides' => $viewModel->getPopularGuides(),
                    'makes' => $viewModel->getMakes(),
                    'pagination' => $viewModel->getPagination(),
                ]
            ]);
        }

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
        ]);
    }
}
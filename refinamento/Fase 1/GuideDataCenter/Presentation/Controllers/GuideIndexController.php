<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\View\View;
use Illuminate\Routing\Controller;
use Src\GuideDataCenter\Presentation\Traits\HasGuideMetaTags;
use Src\GuideDataCenter\Presentation\ViewModels\GuideIndexViewModel;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;

/**
 * GuideIndexController
 * 
 * Responsabilidade: Exibir página inicial de guias
 * Rota: GET /guias
 * 
 * Princípios SOLID:
 * - SRP: Apenas gerencia a página inicial
 * - DIP: Depende de abstrações (interfaces)
 * - OCP: Aberto para extensão via trait
 * 
 * @package Src\GuideDataCenter\Presentation\Controllers
 */
class GuideIndexController extends Controller
{
    use HasGuideMetaTags;

    public function __construct(
        private readonly GuideRepositoryInterface $guideRepository,
        private readonly GuideCategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Exibe página inicial de guias
     * 
     * Rota: GET /guias
     * View: guide-data-center::guide.index
     * 
     * @return View
     */
    public function __invoke(): View
    {
        // Instanciar ViewModel
        $viewModel = new GuideIndexViewModel();

        // Configurar SEO
        $seoData = $viewModel->getSeoData();
        $this->setGuideMetaTags($seoData);

        // Dados para view
        $viewData = [
            'categories' => $viewModel->getCategories(),
            'makes' => $viewModel->getMakes(),
            'recentGuides' => $viewModel->getRecentGuides(),
            'popularModels' => $viewModel->getPopularModels(),
            'stats' => $viewModel->getStats(),
            'seo' => $seoData,
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'structured_data' => $viewModel->getWebPageSchema(),
        ];

        return view('guide-data-center::guide.index', $viewData);
    }
}

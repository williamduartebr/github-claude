<?php

namespace Src\VehicleDataCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\VehicleDataCenter\Domain\Services\VehicleSearchService;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface;
use Src\VehicleDataCenter\Presentation\ViewModels\VehicleViewModel;
use Src\VehicleDataCenter\Presentation\ViewModels\VehicleListViewModel;
use Src\VehicleDataCenter\Presentation\ViewModels\VehicleMakeViewModel;
use Src\VehicleDataCenter\Presentation\ViewModels\VehicleModelViewModel;
use Src\VehicleDataCenter\Presentation\ViewModels\VehicleVersionViewModel;
use Src\VehicleDataCenter\Presentation\ViewModels\VehicleYearViewModel;

class VehicleController
{
    public function __construct(
        private VehicleMakeRepositoryInterface $makeRepository,
        private VehicleModelRepositoryInterface $modelRepository,
        private VehicleVersionRepositoryInterface $versionRepository,
        private VehicleSearchService $searchService
    ) {}

    /**
     * Página inicial: listagem de todas as marcas
     * 
     * Rota: GET /veiculos
     * View: vehicle-data-center::vehicles.index
     */
    public function index()
    {
        $makes = $this->makeRepository->getActive();
        $viewModel = new VehicleListViewModel($makes);

        return view('vehicle-data-center::vehicles.index', [
            'makes' => $viewModel->getMakes(),
            'featuredMakes' => $viewModel->getFeaturedMakes(),
            'popularModels' => $viewModel->getPopularModels(),
            'allMakes' => $viewModel->getAllMakesForTable(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'stats' => $viewModel->getStats(),
        ]);
    }

    /**
     * Página de uma marca: lista modelos
     * 
     * Rota: GET /veiculos/{make}
     * View: vehicle-data-center::vehicles.make
     * Exemplo: /veiculos/toyota
     */
    public function showMake(string $makeSlug)
    {
        $make = $this->makeRepository->findBySlug($makeSlug);

        if (!$make) {
            abort(404, 'Marca não encontrada');
        }

        $models = $this->modelRepository->getByMake($make->id);
        $viewModel = new VehicleMakeViewModel($make, $models);

        return view('vehicle-data-center::vehicles.make', [
            'make' => $viewModel->getMake(),
            'models' => $viewModel->getModels(),
            'popularModels' => $viewModel->getPopularModels(),
            'allModels' => $viewModel->getAllModelsForTable(),
            'guideCategories' => $viewModel->getGuideCategories(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'stats' => $viewModel->getStats(),
        ]);
    }

    /**
     * Página de um modelo: lista versões por ano
     * 
     * Rota: GET /veiculos/{make}/{model}
     * View: vehicle-data-center::vehicles.model
     * Exemplo: /veiculos/toyota/corolla
     */
    public function showModel(string $makeSlug, string $modelSlug)
    {
        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);

        if (!$model) {
            abort(404, 'Modelo não encontrado');
        }

        $versions = $this->versionRepository->getByModel($model->id);
        $viewModel = new VehicleModelViewModel($model->make, $model, $versions);

        return view('vehicle-data-center::vehicles.model', [
            'make' => $viewModel->getMake(),
            'model' => $viewModel->getModel(),
            'quickGuides' => $viewModel->getQuickGuides(),
            'allGuideCategories' => $viewModel->getAllGuideCategories(),
            'versionsByYear' => $viewModel->getVersionsByYear(),
            'yearsList' => $viewModel->getYearsList(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'stats' => $viewModel->getStats(),
        ]);
    }

    /**
     * Página de um ano específico do modelo (NOVO - SOLUÇÃO PARA MÚLTIPLAS VERSÕES)
     * 
     * Rota: GET /veiculos/{make}/{model}/{year}
     * View: vehicle-data-center::vehicles.year
     * Exemplo: /veiculos/toyota/corolla/2023
     * 
     * ESTRATÉGIA IMPLEMENTADA:
     * - Se houver 1 versão apenas → redireciona para a versão (301)
     * - Se houver múltiplas versões → mostra view dedicada do ano
     * - Se não houver versões → 404
     * 
     * BENEFÍCIOS:
     * ✅ URL semântica preservada: /veiculos/toyota/corolla/2023
     * ✅ Conteúdo indexável no ano específico (SEO)
     * ✅ Meta tags personalizadas para o ano
     * ✅ Breadcrumbs completos
     * ✅ Schema.org específico do ano
     * ✅ UX: Usuário não perde contexto
     * ✅ Performance: Cache independente por ano
     */
    public function showYear(string $makeSlug, string $modelSlug, int $year)
    {
        // Busca o modelo
        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);

        if (!$model) {
            abort(404, 'Modelo não encontrado');
        }

        // Busca versões do ano específico (apenas ativas)
        $versions = $this->versionRepository->getByModel($model->id)
            ->where('year', $year)
            ->where('is_active', true);

        // Se não há versões, retorna 404
        if ($versions->isEmpty()) {
            abort(404, 'Nenhuma versão encontrada para este ano');
        }

        // Se há apenas 1 versão, redireciona direto para ela
        // Usar 301 (Permanent Redirect) para SEO
        if ($versions->count() === 1) {
            $version = $versions->first();
            return redirect()->route('vehicles.version', [
                'make' => $makeSlug,
                'model' => $modelSlug,
                'year' => $year,
                'version' => $version->slug,
            ], 301);
        }

        // Se há múltiplas versões, mostra página dedicada do ano
        $viewModel = new VehicleYearViewModel($model->make, $model, $year, $versions);

        return view('vehicle-data-center::vehicles.year', [
            'make' => $viewModel->getMake(),
            'model' => $viewModel->getModel(),
            'year' => $viewModel->getYear(),
            'fullTitle' => $viewModel->getFullTitle(),
            'description' => $viewModel->getDescription(),
            'versions' => $viewModel->getVersions(),
            'versionsByFuel' => $viewModel->getVersionsByFuel(),
            'stats' => $viewModel->getStats(),
            'nearbyYears' => $viewModel->getNearbyYears(),
            'quickGuides' => $viewModel->getQuickGuides(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
            'schemaOrg' => $viewModel->getSchemaOrg(),
        ]);
    }

    /**
     * Página de uma versão específica (ficha técnica completa)
     * 
     * Rota: GET /veiculos/{make}/{model}/{year}/{version}
     * View: vehicle-data-center::vehicles.version
     * Exemplo: /veiculos/toyota/corolla/2003/gli-1-8
     */
    public function showVersion(string $makeSlug, string $modelSlug, int $year, string $versionSlug)
    {
        // Busca versão pelo slug
        $version = $this->versionRepository->findBySlug($makeSlug, $modelSlug, $year, $versionSlug);

        if (!$version) {
            abort(404, 'Versão não encontrada');
        }

        // Instancia o ViewModel que prepara os dados
        $viewModel = new VehicleVersionViewModel($version);

        // Retorna a view com dados preparados pelo ViewModel
        return view('vehicle-data-center::vehicles.version', [
            'version' => $viewModel->getVersion(),
            'make' => $viewModel->getMake(),
            'model' => $viewModel->getModel(),
            'badges' => $viewModel->getBadges(),
            'quickFacts' => $viewModel->getQuickFacts(),
            'mainSpecs' => $viewModel->getMainSpecs(),
            'sideCards' => $viewModel->getSideCards(),
            'fluids' => $viewModel->getFluids(),
            'maintenanceSummary' => $viewModel->getMaintenanceSummary(),
            'guides' => $viewModel->getGuides(),
            'seo' => $viewModel->getSeoData(),
            'breadcrumbs' => $viewModel->getBreadcrumbs(),
        ]);
    }
}

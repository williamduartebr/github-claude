<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Controllers;

use Illuminate\Http\JsonResponse;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

use Src\GuideDataCenter\Domain\Services\GuideSearchService;
use Src\GuideDataCenter\Presentation\ViewModels\GuideListViewModel;

class GuideSearchController extends Controller
{
    public function __construct(
        private readonly GuideRepositoryInterface $guideRepository,
        private readonly GuideSearchService $searchService
    ) {}

    /**
     * Busca guias por termo
     */
    public function search(Request $request): View|JsonResponse
    {
        $query = trim($request->get('q', ''));
        $perPage = (int) $request->get('per_page', 15);
        $page = (int) $request->get('page', 1);

        if (empty($query)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Termo de busca é obrigatório',
                    'data' => []
                ], 400);
            }

            return view('guide::guide.index', [
                'guides' => collect([]),
                'pagination' => null,
                'title' => 'Busca de Guias',
                'query' => '',
                'message' => 'Digite um termo para buscar'
            ]);
        }

        // Verifica tamanho mínimo
        if (mb_strlen($query) < 2) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Termo de busca deve ter pelo menos 2 caracteres',
                    'data' => []
                ], 400);
            }

            return view('guide::guide.index', [
                'guides' => collect([]),
                'pagination' => null,
                'title' => 'Busca de Guias',
                'query' => $query,
                'message' => 'Termo de busca deve ter pelo menos 2 caracteres'
            ]);
        }

        // Executa busca
        $results = $this->searchService->search($query, $perPage, $page);

        $viewModels = $results->map(fn($guide) => new GuideListViewModel($guide));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $viewModels->toArray(),
                'meta' => [
                    'query' => $query,
                    'total' => $results->total(),
                    'per_page' => $results->perPage(),
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                ]
            ]);
        }

        return view('guide::guide.index', [
            'guides' => $viewModels,
            'pagination' => $results,
            'title' => "Resultados para: {$query}",
            'query' => $query,
            'message' => $results->isEmpty() ? 'Nenhum resultado encontrado' : null
        ]);
    }

    /**
     * Sugestões de autocomplete
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));
        $limit = min((int) $request->get('limit', 10), 20);

        if (mb_strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $suggestions = $this->searchService->autocomplete($query, $limit);

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    /**
     * Busca avançada com múltiplos filtros
     */
    public function advanced(Request $request): View|JsonResponse
    {
        $filters = [
            'q' => trim($request->get('q', '')),
            'category' => $request->get('category'),
            'make' => $request->get('make'),
            'model' => $request->get('model'),
            'year_start' => $request->get('year_start'),
            'year_end' => $request->get('year_end'),
        ];

        // Remove filtros vazios
        $filters = array_filter($filters, fn($value) => !empty($value));

        $perPage = (int) $request->get('per_page', 15);
        $page = (int) $request->get('page', 1);

        $results = $this->searchService->advancedSearch($filters, $perPage, $page);

        $viewModels = $results->map(fn($guide) => new GuideListViewModel($guide));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $viewModels->toArray(),
                'meta' => [
                    'filters' => $filters,
                    'total' => $results->total(),
                    'per_page' => $results->perPage(),
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                ]
            ]);
        }

        return view('guide::guide.index', [
            'guides' => $viewModels,
            'pagination' => $results,
            'title' => 'Busca Avançada',
            'filters' => $filters,
            'message' => $results->isEmpty() ? 'Nenhum resultado encontrado' : null
        ]);
    }
}

<?php

namespace Src\GuideDataCenter\Domain\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;

/**
 * GuideRelationshipService
 * 
 * Service responsável por gerenciar relacionamentos entre guias:
 * - Guias relacionados (mesma marca/modelo/ano, categorias diferentes)
 * - Conteúdos essenciais (mesma categoria, anos/versões diferentes)
 * - Super clusters (malha completa de links)
 * 
 * Seguindo princípios SOLID e Clean Code
 * 
 * @package Src\GuideDataCenter\Domain\Services
 */
class GuideRelationshipService
{
    /**
     * @var GuideRepositoryInterface
     */
    protected GuideRepositoryInterface $guideRepository;

    /**
     * @var GuideCategoryRepositoryInterface
     */
    protected GuideCategoryRepositoryInterface $categoryRepository;

    /**
     * Cache TTL em minutos
     */
    protected int $cacheTtl = 1440; // 24 horas

    /**
     * Constructor
     */
    public function __construct(
        GuideRepositoryInterface $guideRepository,
        GuideCategoryRepositoryInterface $categoryRepository
    ) {
        $this->guideRepository = $guideRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Busca guias relacionados para um guia específico
     * (mesma marca/modelo/ano, categorias diferentes)
     * 
     * Exemplo: Para "Óleo Toyota Corolla 2003"
     * Retorna: Calibragem, Pneus, Revisão, etc do Toyota Corolla 2003
     *
     * @param Guide $guide
     * @param int $limit
     * @return Collection
     */
    public function getRelatedGuides(Guide $guide, int $limit = 8): Collection
    {
        $cacheKey = "guide.related.{$guide->_id}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($guide, $limit) {
            return Guide::where('_id', '!=', $guide->_id)
                ->where('make_slug', $guide->make_slug)
                ->where('model_slug', $guide->model_slug)
                ->where(function ($query) use ($guide) {
                    // Busca pelo ano específico OU range que inclui o ano
                    $query->where('year_start', '<=', $guide->year_start)
                          ->where('year_end', '>=', $guide->year_start);
                })
                ->whereNotNull('guide_category_id')
                ->where('guide_category_id', '!=', $guide->guide_category_id)
                ->with('category')
                ->orderBy('guide_category_id')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Busca conteúdos essenciais para um guia específico
     * (mesma categoria, anos/versões diferentes do mesmo modelo)
     * 
     * Exemplo: Para "Óleo Toyota Corolla 2003"
     * Retorna: Óleo Corolla 2002, Óleo Corolla 2004, Óleo Corolla 1.6, etc
     *
     * @param Guide $guide
     * @param int $limit
     * @return Collection
     */
    public function getEssentialContents(Guide $guide, int $limit = 8): Collection
    {
        $cacheKey = "guide.essential.{$guide->_id}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($guide, $limit) {
            return Guide::where('_id', '!=', $guide->_id)
                ->where('make_slug', $guide->make_slug)
                ->where('model_slug', $guide->model_slug)
                ->where('guide_category_id', $guide->guide_category_id)
                ->where(function ($query) use ($guide) {
                    // Busca anos diferentes OU versões diferentes
                    $query->where('year_start', '!=', $guide->year_start)
                          ->orWhere('version', '!=', $guide->version)
                          ->orWhere('motor', '!=', $guide->motor);
                })
                ->orderByRaw('ABS(year_start - ?) ASC', [$guide->year_start])
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Busca guias da mesma categoria (para "Veja também")
     * 
     * Exemplo: Para "Óleo Toyota Corolla 2003"
     * Retorna: Óleo Civic 2003, Óleo Gol 2003, etc
     *
     * @param Guide $guide
     * @param int $limit
     * @return Collection
     */
    public function getSameCategoryGuides(Guide $guide, int $limit = 6): Collection
    {
        $cacheKey = "guide.same_category.{$guide->_id}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($guide, $limit) {
            return Guide::where('_id', '!=', $guide->_id)
                ->where('guide_category_id', $guide->guide_category_id)
                ->where(function ($query) use ($guide) {
                    // Prioriza mesma marca OU mesma faixa de ano
                    $query->where('make_slug', $guide->make_slug)
                          ->orWhereBetween('year_start', [
                              $guide->year_start - 2,
                              $guide->year_start + 2
                          ]);
                })
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Busca todos os anos disponíveis para um modelo/categoria
     * (usado para navegação entre anos)
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param string $categoryId
     * @return Collection
     */
    public function getAvailableYears(
        string $makeSlug, 
        string $modelSlug, 
        string $categoryId
    ): Collection {
        $cacheKey = "guide.years.{$makeSlug}.{$modelSlug}.{$categoryId}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () 
            use ($makeSlug, $modelSlug, $categoryId) {
            
            return Guide::where('make_slug', $makeSlug)
                ->where('model_slug', $modelSlug)
                ->where('guide_category_id', $categoryId)
                ->select('year_start', 'year_end', 'slug', '_id')
                ->orderBy('year_start', 'desc')
                ->get()
                ->unique('year_start');
        });
    }

    /**
     * Busca todas as versões disponíveis para um modelo/ano/categoria
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int $year
     * @param string $categoryId
     * @return Collection
     */
    public function getAvailableVersions(
        string $makeSlug,
        string $modelSlug,
        int $year,
        string $categoryId
    ): Collection {
        $cacheKey = "guide.versions.{$makeSlug}.{$modelSlug}.{$year}.{$categoryId}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () 
            use ($makeSlug, $modelSlug, $year, $categoryId) {
            
            return Guide::where('make_slug', $makeSlug)
                ->where('model_slug', $modelSlug)
                ->where('year_start', '<=', $year)
                ->where('year_end', '>=', $year)
                ->where('guide_category_id', $categoryId)
                ->whereNotNull('version')
                ->where('version', '!=', '')
                ->select('version', 'motor', 'slug', '_id')
                ->orderBy('version')
                ->get();
        });
    }

    /**
     * Busca o "super cluster" completo para um guia
     * Combina: relacionados + essenciais + mesmo categoria
     *
     * @param Guide $guide
     * @return array
     */
    public function getSuperCluster(Guide $guide): array
    {
        return [
            'related_guides' => $this->getRelatedGuides($guide),
            'essential_contents' => $this->getEssentialContents($guide),
            'same_category' => $this->getSameCategoryGuides($guide),
            'available_years' => $this->getAvailableYears(
                $guide->make_slug,
                $guide->model_slug,
                $guide->guide_category_id
            ),
            'available_versions' => $this->getAvailableVersions(
                $guide->make_slug,
                $guide->model_slug,
                $guide->year_start,
                $guide->guide_category_id
            ),
        ];
    }

    /**
     * Gera dados estruturados para navegação (breadcrumb style)
     *
     * @param Guide $guide
     * @return array
     */
    public function getNavigationData(Guide $guide): array
    {
        return [
            'category' => $guide->category,
            'make' => [
                'name' => $guide->make,
                'slug' => $guide->make_slug,
                'url' => "/guias/{$guide->category->slug}/{$guide->make_slug}"
            ],
            'model' => [
                'name' => $guide->model,
                'slug' => $guide->model_slug,
                'url' => "/guias/{$guide->category->slug}/{$guide->make_slug}/{$guide->model_slug}"
            ],
            'year' => [
                'start' => $guide->year_start,
                'end' => $guide->year_end,
                'display' => $guide->year_start === $guide->year_end 
                    ? $guide->year_start 
                    : "{$guide->year_start}-{$guide->year_end}",
            ],
            'version' => $guide->version,
        ];
    }

    /**
     * Limpa cache de relacionamentos de um guia
     *
     * @param string $guideId
     * @return void
     */
    public function clearCache(string $guideId): void
    {
        Cache::forget("guide.related.{$guideId}");
        Cache::forget("guide.essential.{$guideId}");
        Cache::forget("guide.same_category.{$guideId}");
    }

    /**
     * Limpa todo o cache de relacionamentos
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        Cache::tags(['guide-relationships'])->flush();
    }

    /**
     * Valida se um guia tem relacionamentos suficientes
     * (útil para SEO e qualidade)
     *
     * @param Guide $guide
     * @return array
     */
    public function validateRelationships(Guide $guide): array
    {
        $related = $this->getRelatedGuides($guide);
        $essential = $this->getEssentialContents($guide);
        $sameCategory = $this->getSameCategoryGuides($guide);

        return [
            'has_related' => $related->count() > 0,
            'has_essential' => $essential->count() > 0,
            'has_same_category' => $sameCategory->count() > 0,
            'total_links' => $related->count() + $essential->count() + $sameCategory->count(),
            'quality_score' => $this->calculateRelationshipQuality(
                $related->count(),
                $essential->count(),
                $sameCategory->count()
            ),
            'recommendations' => $this->generateRecommendations($guide, $related, $essential),
        ];
    }

    /**
     * Calcula score de qualidade dos relacionamentos (0-100)
     *
     * @param int $relatedCount
     * @param int $essentialCount
     * @param int $sameCategoryCount
     * @return int
     */
    protected function calculateRelationshipQuality(
        int $relatedCount,
        int $essentialCount,
        int $sameCategoryCount
    ): int {
        $score = 0;

        // Peso para guias relacionados (40%)
        $score += min(($relatedCount / 8) * 40, 40);

        // Peso para conteúdos essenciais (40%)
        $score += min(($essentialCount / 8) * 40, 40);

        // Peso para mesma categoria (20%)
        $score += min(($sameCategoryCount / 6) * 20, 20);

        return (int) round($score);
    }

    /**
     * Gera recomendações para melhorar relacionamentos
     *
     * @param Guide $guide
     * @param Collection $related
     * @param Collection $essential
     * @return array
     */
    protected function generateRecommendations(
        Guide $guide,
        Collection $related,
        Collection $essential
    ): array {
        $recommendations = [];

        if ($related->count() < 5) {
            $recommendations[] = "Criar mais guias de categorias diferentes para {$guide->make} {$guide->model} {$guide->year_start}";
        }

        if ($essential->count() < 5) {
            $recommendations[] = "Criar guias para outros anos do {$guide->make} {$guide->model}";
        }

        if (empty($guide->version)) {
            $recommendations[] = "Adicionar informação de versão ao guia";
        }

        return $recommendations;
    }
}
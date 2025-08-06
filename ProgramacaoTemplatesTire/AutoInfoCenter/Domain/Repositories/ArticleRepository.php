<?php

namespace Src\AutoInfoCenter\Domain\Repositories;

use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Illuminate\Database\Eloquent\Collection;
use MongoDB\Laravel\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Tempo de cache em minutos
     */
    private const CACHE_MINUTES = 60;

    /**
     * Encontra um artigo pela sua slug
     *
     * @param string $slug
     * @return Article|null
     */
    public function findBySlug(string $slug): ?Article
    {
        return Article::where('slug', $slug)
            ->where('status', 'published')
            ->first();
    }

    /**
     * Encontra artigos relacionados a um artigo específico
     *
     * @param Article $article
     * @param int $limit
     * @return Collection
     */
    public function findRelated(Article $article, int $limit = 4): Collection
    {
        $cacheKey = "related_articles:{$article->id}:{$limit}";

        return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($article, $limit) {
            $query = Article::where('_id', '!=', $article->_id)
                ->where('status', 'published');

            // Relacionamento por veículo
            if (!empty($article->vehicle_info['make'])) {
                $query->where(function ($q) use ($article) {
                    $q->where('vehicle_info.make', $article->vehicle_info['make']);

                    if (!empty($article->vehicle_info['model'])) {
                        $q->orWhere('vehicle_info.model', $article->vehicle_info['model']);
                    }
                });
            }

            // Relacionamento por categoria
            $query->orWhere('category_slug', $article->category_slug);

            // Relacionamento por tags (se existirem)
            if (!empty($article->tags)) {
                $query->orWhere(function ($q) use ($article) {
                    $q->whereIn('tags', $article->tags);
                });
            }

            return $query->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Incrementa a contagem de visualizações de um artigo
     *
     * @param Article $article
     * @return bool
     */
    public function incrementViewCount(Article $article): bool
    {
        // Verifica se metadata existe e possui campo click_count
        if (isset($article->metadata['original_clicks'])) {
            $article->metadata['original_clicks'] += 1;
        } else {
            // Inicializa se não existir
            if (!isset($article->metadata)) {
                $article->metadata = [];
            }
            $article->metadata['original_clicks'] = 1;
        }

        return $article->save();
    }

    /**
     * Busca artigos por categoria
     *
     * @param string $categorySlug
     * @param int $limit
     * @param int $offset
     * @return Collection
     */
    public function findByCategory(string $categorySlug, int $limit = 10, int $offset = 0): Collection
    {
        $cacheKey = "category_articles:{$categorySlug}:{$limit}:{$offset}";

        return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($categorySlug, $limit, $offset) {
            return Article::where('category_slug', $categorySlug)
                ->where('status', 'published')
                ->orderBy('updated_at', 'desc')
                ->skip($offset)
                ->limit($limit)
                ->select(['_id', 'title', 'slug', 'vehicle_info', 'created_at', 'updated_at', 'metadata', 'content.introducao'])
                ->get();
        });
    }

    /**
     * Conta total de artigos por categoria
     *
     * @param string $categorySlug
     * @return int
     */
    public function countByCategory(string $categorySlug): int
    {
        $cacheKey = "count_category_articles:{$categorySlug}";

        return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($categorySlug) {
            return Article::where('category_slug', $categorySlug)
                ->where('status', 'published')
                ->count();
        });
    }

    /**
     * Busca artigos por categoria e veículo
     *
     * @param string $categorySlug
     * @param string $make
     * @param string|null $model
     * @param string|null $year
     * @param int $limit
     * @param int $offset
     * @return Collection
     */
    public function findByCategoryAndVehicle(string $categorySlug, string $make, ?string $model = null, ?string $year = null, int $limit = 10, int $offset = 0): Collection
    {
        $cacheKey = "category_vehicle_articles:{$categorySlug}:{$make}:" . ($model ?? 'all') . ":" . ($year ?? 'all') . ":{$limit}:{$offset}";

        return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($categorySlug, $make, $model, $year, $limit, $offset) {
            $query = Article::where('category_slug', $categorySlug)
                ->where('status', 'published')
                ->where('vehicle_info.make', $make);

            if ($model) {
                $query->where('vehicle_info.model', $model);
            }

            if ($year) {
                $query->where(function ($q) use ($year) {
                    $q->where('vehicle_info.year', $year)
                        ->orWhere(function ($subQ) use ($year) {
                            $subQ->where('vehicle_info.year_start', '<=', $year)
                                ->where('vehicle_info.year_end', '>=', $year)
                                ->where('vehicle_info.year_range', true);
                        });
                });
            }

            return $query->orderBy('updated_at', 'desc')
                ->skip($offset)
                ->limit($limit)
                ->select(['_id', 'title', 'slug', 'vehicle_info', 'created_at', 'updated_at', 'metadata', 'content.introducao'])
                ->get();
        });
    }

    /**
     * Conta artigos por categoria e veículo
     *
     * @param string $categorySlug
     * @param string $make
     * @param string|null $model
     * @param string|null $year
     * @return int
     */
    public function countByCategoryAndVehicle(string $categorySlug, string $make, ?string $model = null, ?string $year = null): int
    {
        $cacheKey = "count_category_vehicle_articles:{$categorySlug}:{$make}:" . ($model ?? 'all') . ":" . ($year ?? 'all');

        return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($categorySlug, $make, $model, $year) {
            $query = Article::where('category_slug', $categorySlug)
                ->where('status', 'published')
                ->where('vehicle_info.make', $make);

            if ($model) {
                $query->where('vehicle_info.model', $model);
            }

            if ($year) {
                $query->where(function ($q) use ($year) {
                    $q->where('vehicle_info.year', $year)
                        ->orWhere(function ($subQ) use ($year) {
                            $subQ->where('vehicle_info.year_start', '<=', $year)
                                ->where('vehicle_info.year_end', '>=', $year)
                                ->where('vehicle_info.year_range', true);
                        });
                });
            }

            return $query->count();
        });
    }

    /**
     * Busca artigos para um veículo específico
     *
     * @param string $make Marca do veículo
     * @param string|null $model Modelo do veículo (opcional)
     * @param string|null $year Ano do veículo (opcional)
     * @param int $limit
     * @return Collection
     */
    public function findByVehicle(string $make, ?string $model = null, ?string $year = null, int $limit = 10): Collection
    {
        $cacheKey = "vehicle_articles:{$make}:" . ($model ?? 'all') . ":" . ($year ?? 'all') . ":{$limit}";

        return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($make, $model, $year, $limit) {
            $query = Article::where('vehicle_info.make', $make)
                ->where('status', 'published');

            if ($model) {
                $query->where('vehicle_info.model', $model);
            }

            if ($year) {
                $query->where(function ($q) use ($year) {
                    $q->where('vehicle_info.year', $year)
                        ->orWhere(function ($subQ) use ($year) {
                            $subQ->where('vehicle_info.year_start', '<=', $year)
                                ->where('vehicle_info.year_end', '>=', $year)
                                ->where('vehicle_info.year_range', true);
                        });
                });
            }

            return $query->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Busca artigos por termos de pesquisa
     *
     * @param string $searchTerm
     * @param int $limit
     * @return Collection
     */
    public function search(string $searchTerm, int $limit = 10): Collection
    {
        // Não cacheamos resultados de pesquisa para manter relevância
        return Article::search($searchTerm)
            ->where('status', 'published')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtém artigos populares baseados em visualizações
     *
     * @param int $limit
     * @return Collection
     */
    public function getPopular(int $limit = 5): Collection
    {
        $cacheKey = "popular_articles:{$limit}";

        return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($limit) {
            return Article::where('status', 'published')
                ->orderBy('metadata.original_clicks', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Obtém artigos mais recentes
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecent(int $limit = 5): Collection
    {
        $cacheKey = "recent_articles:{$limit}";

        return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($limit) {
            return Article::where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }
}

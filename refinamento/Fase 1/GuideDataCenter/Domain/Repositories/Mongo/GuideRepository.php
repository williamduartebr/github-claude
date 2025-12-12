<?php

namespace Src\GuideDataCenter\Domain\Repositories\Mongo;

use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class GuideRepository
 * 
 * Implementação MongoDB do repositório de guias
 */
class GuideRepository implements GuideRepositoryInterface
{
    /**
     * @var Guide
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(Guide $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function findBySlug(string $slug): ?Guide
    {
        return $this->model
            ->bySlug($slug)
            ->with(['category', 'guideSeo', 'clusters'])
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findByVehicle(string $makeSlug, string $modelSlug, ?int $year = null): ?Guide
    {
        $query = $this->model
            ->byMake($makeSlug)
            ->byModel($modelSlug);

        if ($year) {
            $query->byYear($year);
        }

        return $query->with(['category', 'guideSeo'])->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findByCategory(string $categorySlug, int $limit = 50): Collection
    {
        return $this->model
            ->whereHas('category', function ($query) use ($categorySlug) {
                $query->where('slug', $categorySlug);
            })
            ->with(['category', 'guideSeo'])
            ->limit($limit)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function listByMake(string $makeSlug, int $limit = 100): Collection
    {
        return $this->model
            ->byMake($makeSlug)
            ->with(['category', 'guideSeo'])
            ->limit($limit)
            ->orderBy('model_slug', 'asc')
            ->orderBy('year_start', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function listByModel(string $modelSlug, int $limit = 100): Collection
    {
        return $this->model
            ->byModel($modelSlug)
            ->with(['category', 'guideSeo'])
            ->limit($limit)
            ->orderBy('make_slug', 'asc')
            ->orderBy('year_start', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function listByTemplate(string $template, int $limit = 100): Collection
    {
        return $this->model
            ->byTemplate($template)
            ->with(['category', 'guideSeo'])
            ->limit($limit)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function search(string $term, int $limit = 50): Collection
    {
        return $this->model
            ->search($term)
            ->with(['category', 'guideSeo'])
            ->limit($limit)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function createGuide(array $data): Guide
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateGuide(string $id, array $data): Guide
    {
        $guide = $this->findById($id);

        if (!$guide) {
            throw new \Exception("Guide with ID {$id} not found");
        }

        $guide->update($data);
        $guide->refresh();

        return $guide;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteGuide(string $id): bool
    {
        $guide = $this->findById($id);

        if (!$guide) {
            return false;
        }

        return $guide->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function findById(string $id): ?Guide
    {
        return $this->model
            ->with(['category', 'guideSeo', 'clusters'])
            ->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(int $perPage = 15)
    {
        return $this->model
            ->with(['category', 'guideSeo'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function findByFilters(array $filters): Collection
    {
        $query = $this->model->query();

        // Filtro por marca
        if (isset($filters['make_slug'])) {
            $query->byMake($filters['make_slug']);
        }

        // Filtro por modelo
        if (isset($filters['model_slug'])) {
            $query->byModel($filters['model_slug']);
        }

        // Filtro por categoria
        if (isset($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        // Filtro por ano
        if (isset($filters['year'])) {
            $query->byYear($filters['year']);
        }

        // Filtro por template
        if (isset($filters['template'])) {
            $query->byTemplate($filters['template']);
        }

        // Filtro por termo de busca
        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        // Ordenação
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        // Limite
        $limit = $filters['limit'] ?? 50;

        return $query
            ->with(['category', 'guideSeo'])
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * {@inheritDoc}
     */
    public function findByYear(int $year, int $limit = 100): Collection
    {
        return $this->model
            ->byYear($year)
            ->with(['category', 'guideSeo'])
            ->limit($limit)
            ->orderBy('make_slug', 'asc')
            ->orderBy('model_slug', 'asc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findRelated(Guide $guide, int $limit = 10): Collection
    {
        // Busca guias relacionados baseado em:
        // 1. Mesma marca e modelo
        // 2. Mesma categoria
        // 3. Anos próximos

        return $this->model
            ->where('_id', '!=', $guide->_id)
            ->where(function ($query) use ($guide) {
                // Prioriza mesma marca/modelo
                $query->where(function ($q) use ($guide) {
                    $q->where('make_slug', $guide->make_slug)
                        ->where('model_slug', $guide->model_slug);
                })
                    // Ou mesma categoria
                    ->orWhere('guide_category_id', $guide->guide_category_id);
            })
            ->with(['category', 'guideSeo'])
            ->limit($limit)
            ->orderByRaw("
                CASE 
                    WHEN make_slug = ? AND model_slug = ? THEN 1
                    WHEN guide_category_id = ? THEN 2
                    ELSE 3
                END
            ", [
                $guide->make_slug,
                $guide->model_slug,
                $guide->guide_category_id
            ])
            ->get();
    }

    /**
     * Busca guias por marca e modelo
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @return Collection
     */
    public function findByMakeAndModel(string $makeSlug, string $modelSlug): Collection
    {
        return Guide::where('make_slug', $makeSlug)
            ->where('model_slug', $modelSlug)
            ->where('is_active', true)
            ->orderBy('year_start', 'desc')
            ->orderBy('guide_category_id')
            ->get();
    }
}

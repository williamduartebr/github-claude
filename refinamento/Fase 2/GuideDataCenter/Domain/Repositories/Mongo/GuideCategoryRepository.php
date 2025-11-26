<?php

namespace Src\GuideDataCenter\Domain\Repositories\Mongo;

use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class GuideCategoryRepository
 * 
 * Implementação MongoDB do repositório de categorias
 */
class GuideCategoryRepository implements GuideCategoryRepositoryInterface
{
    /**
     * @var GuideCategory
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(GuideCategory $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function findBySlug(string $slug): ?GuideCategory
    {
        return $this->model
            ->bySlug($slug)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findById(string $id): ?GuideCategory
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllActive(): Collection
    {
        return $this->model
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getAllOrdered(): Collection
    {
        return $this->model
            ->ordered()
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function createCategory(array $data): GuideCategory
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateCategory(string $id, array $data): GuideCategory
    {
        $category = $this->findById($id);
        
        if (!$category) {
            throw new \Exception("Category with ID {$id} not found");
        }

        $category->update($data);
        $category->refresh();

        return $category;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteCategory(string $id): bool
    {
        $category = $this->findById($id);
        
        if (!$category) {
            return false;
        }

        return $category->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function search(string $term): Collection
    {
        return $this->model
            ->search($term)
            ->ordered()
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
    public function updateOrder(array $orderMap): bool
    {
        try {
            foreach ($orderMap as $categoryId => $order) {
                $category = $this->findById($categoryId);
                
                if ($category) {
                    $category->order = $order;
                    $category->save();
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

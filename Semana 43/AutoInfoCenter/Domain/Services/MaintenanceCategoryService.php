<?php

namespace Src\AutoInfoCenter\Domain\Services;

use Src\AutoInfoCenter\Infrastructure\Repositories\MaintenanceCategoryRepository;

class MaintenanceCategoryService
{
    protected $repository;

    public function __construct(MaintenanceCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPopularCategories($limit = 6)
    {
        return $this->repository->getPopularCategories($limit);
    }

    public function getAllCategories($limit = 60)
    {
        return $this->repository->getAllCategories($limit);
    }

    public function findBySlug($slug)
    {
        return $this->repository->findBySlug($slug);
    }
}
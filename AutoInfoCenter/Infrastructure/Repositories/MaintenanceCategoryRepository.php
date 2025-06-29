<?php

namespace Src\AutoInfoCenter\Infrastructure\Repositories;

use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;

class MaintenanceCategoryRepository
{
    public function __construct(
        private MaintenanceCategory $maintenanceCategory
    ) {
        //
    }

    public function getPopularCategories($limit = 6)
    {
        return $this->maintenanceCategory->where('is_active', true)
            ->orderBy('display_order')
            ->limit($limit)
            ->get();
    }

    public function getAllCategories($limit = 40)
    {
        return $this->maintenanceCategory->where('is_active', true)
            ->orderBy('display_order')
            ->limit($limit)
            ->get();
    }
    
    public function findBySlug(string $slug): ?MaintenanceCategory
    {
        return $this->maintenanceCategory->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }
}
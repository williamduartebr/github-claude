<?php

namespace Src\AutoInfoCenter\ViewModels;

use Src\AutoInfoCenter\Domain\Services\MaintenanceCategoryService;

class InfoCategoriesViewModel
{
    public function __construct(
        private MaintenanceCategoryService $categoryService
    ) {
        //
    }

    public function getAllCategories()
    {
        $categories = $this->categoryService->getAllCategories();
        return $categories;
    }
}

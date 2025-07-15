<?php

namespace Src\AutoInfoCenter\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Src\AutoInfoCenter\ViewModels\InfoCategoriesViewModel;
use Src\AutoInfoCenter\ViewModels\InfoCategoryViewModel;

class InfoArticleModeloController extends Controller
{
    public function __construct(
        private InfoCategoriesViewModel $categoriesViewModel,
        private InfoCategoryViewModel $categoryViewModel
    ) {}

    public function view()
    {

        return view('auto-info-center::article.templates.MODELOS.Template_Quando_Trocar_os_Pneus');
    }
}

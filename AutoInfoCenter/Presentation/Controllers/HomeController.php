<?php

namespace Src\AutoInfoCenter\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Torann\LaravelMetaTags\Facades\MetaTag;
use Src\AutoInfoCenter\ViewModels\PopularCategoriesViewModel;
use Src\AutoInfoCenter\ViewModels\ArticleViewModel;

class HomeController extends Controller
{
    protected $categoriesViewModel;
    protected $articleViewModel;

    public function __construct(
        PopularCategoriesViewModel $categoriesViewModel,
        ArticleViewModel $articleViewModel
    ) {
        $this->categoriesViewModel = $categoriesViewModel;
        $this->articleViewModel = $articleViewModel;
    }

    public function index()
    {
        // Busca categorias normais (formato original)
        $popularCategories = $this->categoriesViewModel->getCategories(6);

        // Busca categorias com artigos para o info-center
        $infoCenterCategories = $this->categoriesViewModel->getCategoriesWithArticles(6);

        // Busca artigos recentes
        $recentArticles = $this->articleViewModel->getRecentArticles(6);

        MetaTag::set('title', "Mercado Veículos - Portal Automotivo");
        MetaTag::set('description', 'Seu portal completo de informações automotivas, com foco em manutenção e cuidados para todos os modelos.');

        // ✅ OPEN GRAPH TAGS PARA AMP
        MetaTag::set('og:title', "Mercado Veículos - Portal Automotivo");
        MetaTag::set('og:description', 'Seu portal completo de informações automotivas, com foco em manutenção e cuidados para todos os modelos.');
        MetaTag::set('og:url', URL::full());
        MetaTag::set('og:type', 'article');
        MetaTag::set('og:site_name', '"Mercado Veículos - Portal Automotivo');
        MetaTag::set('og:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');
        MetaTag::set('og:image:width', '1200');
        MetaTag::set('og:image:height', '630');
        MetaTag::set('robots', 'index, follow');


        $html = Cache::remember('auto_info_center_home', 86400 * 3, function () use ($popularCategories, $infoCenterCategories, $recentArticles) {
            return view('auto-info-center::home.index', compact('popularCategories', 'infoCenterCategories', 'recentArticles'))->render();
        });

        return response($html);
    }
}

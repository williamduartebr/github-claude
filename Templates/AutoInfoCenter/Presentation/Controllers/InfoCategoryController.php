<?php

namespace Src\AutoInfoCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Torann\LaravelMetaTags\Facades\MetaTag;
use Src\AutoInfoCenter\ViewModels\InfoCategoryViewModel;
use Src\AutoInfoCenter\ViewModels\InfoCategoriesViewModel;

class InfoCategoryController extends Controller
{
    public function __construct(
        private InfoCategoriesViewModel $categoriesViewModel,
        private InfoCategoryViewModel $categoryViewModel
    ) {}

    public function index()
    {
        $cacheKey = "info_category_index";

        $html = Cache::remember($cacheKey, 86400 * 3, function () { // 3 dias
            $categories = $this->categoriesViewModel->getAllCategories();

            // ✅ META TAGS PARA INDEX
            MetaTag::set('title', 'Informações Automotivas - Guia Completo | Mercado Veículos');
            MetaTag::set('description', 'Encontre informações detalhadas sobre manutenção automotiva, calibragem de pneus, óleos recomendados e muito mais para todos os modelos de veículos.');
            MetaTag::set('canonical', route('info.category.index'));

            // ✅ OPEN GRAPH TAGS
            MetaTag::set('og:type', 'website');
            MetaTag::set('og:url', route('info.category.index'));
            MetaTag::set('og:title', 'Informações Automotivas - Guia Completo | Mercado Veículos');
            MetaTag::set('og:description', 'Encontre informações detalhadas sobre manutenção automotiva, calibragem de pneus, óleos recomendados e muito mais para todos os modelos de veículos.');
            MetaTag::set('og:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');
            MetaTag::set('og:site_name', 'Mercado Veículos - Portal Automotivo');

            // ✅ TWITTER TAGS
            MetaTag::set('twitter:card', 'summary_large_image');
            MetaTag::set('twitter:url', route('info.category.index'));
            MetaTag::set('twitter:title', 'Informações Automotivas - Guia Completo | Mercado Veículos');
            MetaTag::set('twitter:description', 'Encontre informações detalhadas sobre manutenção automotiva, calibragem de pneus, óleos recomendados e muito mais para todos os modelos de veículos.');
            MetaTag::set('twitter:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');

            MetaTag::set('robots', 'index, follow');

            return view('auto-info-center::category.index', compact('categories'))->render();
        });

        return response($html);
    }

    public function show($slug, Request $request)
    {
        // Cache key incluindo filtros e página
        $page = (int) $request->get('page', 1);
        $filters = array_filter([
            'marca' => $request->get('marca'),
            'modelo' => $request->get('modelo'),
            'ano' => $request->get('ano'),
        ]);

        $cacheKey = "info_category_show_{$slug}_" . md5(serialize($filters) . $page);

        $html = Cache::remember($cacheKey, 3600, function () use ($slug, $request, $page, $filters) { // 1 hora
            $category = $this->categoryViewModel->findBySlug($slug);
            if (!$category) {
                abort(404);
            }

            // Busca artigos filtrados com paginação
            $articlesData = $this->categoryViewModel->getFilteredArticles($category->slug, $filters, $page, 12);

            // ✅ META TAGS PARA SHOW
            $title = $category->seo_info['title'] ?? $category->name . ' - Guia Completo | Mercado Veículos';
            $description = $category->seo_info['description'] ?? $category->description;

            MetaTag::set('title', $title);
            MetaTag::set('description', $description);
            MetaTag::set('canonical', route('info.category.show', $category->slug));

            // ✅ OPEN GRAPH TAGS
            MetaTag::set('og:type', 'website');
            MetaTag::set('og:url', route('info.category.show', $category->slug));
            MetaTag::set('og:title', $title);
            MetaTag::set('og:description', $description);
            MetaTag::set('og:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');
            MetaTag::set('og:site_name', 'Mercado Veículos - Portal Automotivo');

            // ✅ TWITTER TAGS
            MetaTag::set('twitter:card', 'summary_large_image');
            MetaTag::set('twitter:url', route('info.category.show', $category->slug));
            MetaTag::set('twitter:title', $title);
            MetaTag::set('twitter:description', $description);
            MetaTag::set('twitter:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');

            MetaTag::set('robots', 'index, follow');

            return view('auto-info-center::category.show', [
                'category' => $category,
                'articles' => $articlesData['articles'],
                'category_slug' => $category->slug,
                'pagination' => $articlesData['pagination'],
                'filters' => $articlesData['filters'],
            ])->render();
        });

        return response($html);
    }

    public function allModels($slug, Request $request)
    {
        $page = (int) $request->get('page', 1);
        $cacheKey = "info_category_all_models_{$slug}_page_{$page}";

        $html = Cache::remember($cacheKey, 3600, function () use ($slug, $request, $page) { // 1 hora
            $category = $this->categoryViewModel->findBySlug($slug);

            if (!$category) {
                abort(404);
            }

            // Busca todos os artigos da categoria (mais artigos por página)
            $articlesData = $this->categoryViewModel->getArticlesByCategory($category->slug, $page, 24);

            // ✅ META TAGS PARA ALL MODELS
            $title = $category->seo_info['title'] ?? $category->name . ' - Todos os Modelos | Mercado Veículos';
            $description = $category->seo_info['description'] ?? 'Veja todos os modelos disponíveis para ' . strtolower($category->name) . '. Guias completos e especializados para cada veículo.';

            MetaTag::set('title', $title);
            MetaTag::set('description', $description);
            MetaTag::set('canonical', route('info.category.all-models', $category->slug));

            // ✅ OPEN GRAPH TAGS
            MetaTag::set('og:type', 'website');
            MetaTag::set('og:url', route('info.category.all-models', $category->slug));
            MetaTag::set('og:title', $title);
            MetaTag::set('og:description', $description);
            MetaTag::set('og:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');
            MetaTag::set('og:site_name', 'Mercado Veículos - Portal Automotivo');

            // ✅ TWITTER TAGS
            MetaTag::set('twitter:card', 'summary_large_image');
            MetaTag::set('twitter:url', route('info.category.all-models', $category->slug));
            MetaTag::set('twitter:title', $title);
            MetaTag::set('twitter:description', $description);
            MetaTag::set('twitter:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');

            MetaTag::set('robots', 'index, follow');

            return view('auto-info-center::category.all-models', [
                'category' => $category,
                'articles' => $articlesData['articles'],
                'category_slug' => $category->slug,
                'pagination' => $articlesData['pagination'],
            ])->render();
        });

        return response($html);
    }
}

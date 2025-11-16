<?php

namespace Src\AutoInfoCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Torann\LaravelMetaTags\Facades\MetaTag;
use Src\AutoInfoCenter\ViewModels\RecentArticlesViewModel;

class RecentArticlesController extends Controller
{
    public function __construct(
        private RecentArticlesViewModel $recentArticlesViewModel
    ) {}

    public function index(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $cacheKey = "recent_articles_index_page_{$page}";

        $html = Cache::remember($cacheKey, 3600, function () use ($page) { // 1 hora
            // Busca artigos recentes com paginação
            $articlesData = $this->recentArticlesViewModel->getRecentArticles($page, 12);

            // ✅ META TAGS PARA ÚLTIMOS ARTIGOS
            $title = 'Últimos Artigos Publicados - Informações Automotivas | Mercado Veículos';
            $description = 'Confira os últimos artigos publicados sobre manutenção automotiva, dicas, especificações técnicas e guias completos para todos os modelos de veículos.';

            MetaTag::set('title', $title);
            MetaTag::set('description', $description);
            MetaTag::set('canonical', route('info.recent-articles'));

            // ✅ OPEN GRAPH TAGS
            MetaTag::set('og:type', 'website');
            MetaTag::set('og:url', route('info.recent-articles'));
            MetaTag::set('og:title', $title);
            MetaTag::set('og:description', $description);
            MetaTag::set('og:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');
            MetaTag::set('og:site_name', 'Mercado Veículos - Portal Automotivo');

            // ✅ TWITTER TAGS
            MetaTag::set('twitter:card', 'summary_large_image');
            MetaTag::set('twitter:url', route('info.recent-articles'));
            MetaTag::set('twitter:title', $title);
            MetaTag::set('twitter:description', $description);
            MetaTag::set('twitter:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');

            MetaTag::set('robots', 'index, follow');

            return view('auto-info-center::recent-articles.index', [
                'articles' => $articlesData['articles'],
                'pagination' => $articlesData['pagination'],
            ])->render();
        });

        return response($html);
    }
}

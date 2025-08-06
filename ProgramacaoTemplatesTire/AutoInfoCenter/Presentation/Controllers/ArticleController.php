<?php

namespace Src\AutoInfoCenter\Presentation\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Torann\LaravelMetaTags\Facades\MetaTag;
use Src\AutoInfoCenter\ViewModels\ArticleViewModel;

class ArticleController extends Controller
{
    public function __construct(
        private ArticleViewModel $articleViewModel
    ) {}

    /**
     * Exibe um artigo com base na slug
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $cacheKey = "article_view_{$slug}";

        $html = Cache::remember($cacheKey, 86400 * 90, function () use ($slug) {

            $articleData = $this->articleViewModel->getArticleBySlug($slug);

            if (!$articleData) {
                abort(404, 'Artigo não encontrado');
            }

            $templateName = $articleData->getTemplateName();
            $viewPath = "auto-info-center::article.templates.{$templateName}";

            // ✅ META TAGS BASEADAS EM PROCESSED DATA
            $processedData = $articleData->getData();

            MetaTag::set('title', $processedData['seo_data']['page_title'] ?? $processedData['title']);
            MetaTag::set('description', $processedData['seo_data']['meta_description'] ?? '');

            // ✅ META TAGS ESTRUTURADAS
            MetaTag::set('article:author', $processedData['author']['name'] ?? 'Mercado Veículos');
            MetaTag::set('article:section', $processedData['category']['name'] ?? 'Artigos');
            MetaTag::set('article:published_time', $processedData['created_at']->utc()->toAtomString());

            // Só adiciona modified_time se for diferente da published_time
            $publishedTime = $processedData['created_at']->utc()->toAtomString();
            $modifiedTime = $processedData['updated_at']->utc()->toAtomString();
            if ($publishedTime !== $modifiedTime) {
                MetaTag::set('article:modified_time', $modifiedTime);
            }

            // ✅ OPEN GRAPH TAGS
            MetaTag::set('og:title', $processedData['seo_data']['page_title'] ?? $processedData['title']);
            MetaTag::set('og:description', $processedData['seo_data']['meta_description'] ?? '');
            MetaTag::set('og:url', $processedData['canonical_url'] ?? '');
            MetaTag::set('og:type', 'article');
            MetaTag::set('og:site_name', 'Mercado Veículos - Portal Automotivo');
            MetaTag::set('og:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');
            MetaTag::set('og:image:width', '1200');
            MetaTag::set('og:image:height', '630');
            MetaTag::set('robots', 'index, follow');
            
            return view($viewPath, ['article' => $articleData])->render();
        });

        return response($html);
    }

    /**
     * Exibe a versão AMP de um artigo
     *
     * @param string $category
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function amp($slug)
    {
        $cacheKey = "article_amp_view_{$slug}";

        $html = Cache::remember($cacheKey, 86400 * 90, function () use ($slug) {
            $articleData = $this->articleViewModel->getArticleBySlug($slug);

            if (!$articleData) {
                abort(404, 'Artigo não encontrado');
            }

            // ✅ META TAGS PARA AMP
            $processedData = $articleData->getData();

            MetaTag::set('title', $processedData['seo_data']['page_title'] ?? $processedData['title']);
            MetaTag::set('description', $processedData['seo_data']['meta_description'] ?? '');

            // ✅ OPEN GRAPH TAGS PARA AMP
            MetaTag::set('og:title', $processedData['seo_data']['page_title'] ?? $processedData['title']);
            MetaTag::set('og:description', $processedData['seo_data']['meta_description'] ?? '');
            MetaTag::set('og:url', str_replace('/amp', '', $processedData['canonical_url'] ?? ''));
            MetaTag::set('og:type', 'article');
            MetaTag::set('og:site_name', '"Mercado Veículos - Portal Automotivo');
            MetaTag::set('og:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');
            MetaTag::set('og:image:width', '1200');
            MetaTag::set('og:image:height', '630');
            MetaTag::set('robots', 'index, follow');

            // ✅ META TAGS ESTRUTURADAS PARA AMP
            MetaTag::set('article:author', $processedData['author']['name'] ?? 'Mercado Veículos');
            MetaTag::set('article:section', $processedData['category']['name'] ?? 'Artigos');
            MetaTag::set('article:published_time', $processedData['created_at']->utc()->toAtomString());

            // Só adiciona modified_time se for diferente da published_time
            $publishedTime = $processedData['created_at']->utc()->toAtomString();
            $modifiedTime = $processedData['updated_at']->utc()->toAtomString();
            if ($publishedTime !== $modifiedTime) {
                MetaTag::set('article:modified_time', $modifiedTime);
            }

            $templateName = $articleData->getTemplateName();
            $viewPath = "auto-info-center::article.templates.amp.{$templateName}";

            return view($viewPath, [
                'article' => $articleData,
                'canonical' => $processedData['canonical_url']
            ])->render();
        });

        return response($html);
    }

    /**
     * Limpa o cache de um artigo específico
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache($slug)
    {
        $cacheKeyNormal = "article_view_{$slug}";
        $cacheKeyAmp = "article_amp_view_{$slug}";

        Cache::forget($cacheKeyNormal);
        Cache::forget($cacheKeyAmp);

        return response()->json([
            'success' => true,
            'message' => "Cache limpo para o artigo: {$slug}",
            'cleared_keys' => [$cacheKeyNormal, $cacheKeyAmp]
        ]);
    }
}

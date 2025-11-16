<?php

namespace Src\AutoInfoCenter\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Torann\LaravelMetaTags\Facades\MetaTag;
use Src\AutoInfoCenter\ViewModels\ArticleViewModel;

class ArticleController extends Controller
{
    private array $ampRedirectTemplates = [
        'oil_recommendation',
        'oil_table',
        'when_to_change_tires',
        'tire_recommendation',
        'tire_calibration_car',
        'tire_calibration_motorcycle',
        'tire_calibration_pickup',
        'review_schedule_car',
        'review_schedule_electric',
        'review_schedule_hybrid',
        'review_schedule_motorcycle',
    ];

    public function __construct(
        private ArticleViewModel $articleViewModel
    ) {}

    public function show(string $slug)
    {
        $articleData = $this->articleViewModel->getArticleBySlug($slug);

        if (!$articleData) {
            abort(404, 'Artigo não encontrado');
        }

        $templateName = $articleData->getTemplateName();
        $viewPath = "auto-info-center::article.templates.{$templateName}";

        $processedData = $articleData->getData();

        MetaTag::set('title', $processedData['seo_data']['page_title'] ?? $processedData['title']);
        MetaTag::set('description', $processedData['seo_data']['meta_description'] ?? '');
        MetaTag::set('article:author', $processedData['author']['name'] ?? 'Mercado Veículos');
        MetaTag::set('article:section', $processedData['category']['name'] ?? 'Artigos');
        MetaTag::set('article:published_time', $processedData['created_at']->utc()->toAtomString());

        $publishedTime = $processedData['created_at']->utc()->toAtomString();
        $modifiedTime = $processedData['updated_at']->utc()->toAtomString();

        if ($publishedTime !== $modifiedTime) {
            MetaTag::set('article:modified_time', $modifiedTime);
        }

        MetaTag::set('og:title', $processedData['seo_data']['page_title'] ?? $processedData['title']);
        MetaTag::set('og:description', $processedData['seo_data']['meta_description'] ?? '');
        MetaTag::set('og:url', $processedData['canonical_url'] ?? '');
        MetaTag::set('og:type', 'article');
        MetaTag::set('og:site_name', 'Mercado Veículos - Portal Automotivo');
        MetaTag::set('og:image', 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg');
        MetaTag::set('og:image:width', '1200');
        MetaTag::set('og:image:height', '630');
        MetaTag::set('robots', 'index, follow');

        return view($viewPath, ['article' => $articleData]);
    }

    public function amp(string $slug)
    {
        $articleData = $this->articleViewModel->getArticleBySlug($slug);

        if (!$articleData) {
            abort(404, 'Artigo não encontrado');
        }

        $templateName = $articleData->getTemplateName();

        if (in_array($templateName, $this->ampRedirectTemplates)) {
            return redirect()->route('info.article.show', $slug, 301);
        }

        abort(404);
    }

    public function clear(string $slug)
    {
        $this->articleViewModel->invalidateArticleCache($slug);
        return "Cache limpo slug: {$slug}";     
    }
}
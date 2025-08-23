<?php

namespace Src\ArticleGenerator\Infrastructure\Services;

use Src\WordPressSync\Infrastructure\Eloquent\PostDetail;
use Src\ArticleGenerator\Infrastructure\Services\ClaudeServiceOilRecommendation;
use Src\ArticleGenerator\Infrastructure\Services\ClaudeServiceTireRecommendation;
use Src\ArticleGenerator\Infrastructure\Services\ClaudeServiceOilTable; // NOVA LINHA

class ArticleGenerationService
{
    protected $claudeServiceOil;
    protected $claudeServiceTire;
    protected $claudeServiceOilTable; // NOVA LINHA

    public function __construct(
        ClaudeServiceOilRecommendation $claudeServiceOil,
        ClaudeServiceTireRecommendation $claudeServiceTire,
        ClaudeServiceOilTable $claudeServiceOilTable // NOVA LINHA
    ) {
        $this->claudeServiceOil = $claudeServiceOil;
        $this->claudeServiceTire = $claudeServiceTire;
        $this->claudeServiceOilTable = $claudeServiceOilTable; // NOVA LINHA
    }

    public function generateArticle($postId, $templateType = 'oil_recommendation')
    {
        // Buscar detalhes do post no MongoDB
        $postDetail = PostDetail::find($postId);

        if (!$postDetail) {
            throw new \Exception("Post não encontrado");
        }

        // Preparar dados para o Claude
        $articleData = $this->prepareArticleData($postDetail);

        // Gerar conteúdo através do Claude baseado no tipo de template
        switch ($templateType) {
            case 'tire_recommendation':
                $apiResult = $this->claudeServiceTire->generateTireContent($articleData);
                break;
            case 'oil_table': // NOVA LINHA
                $apiResult = $this->claudeServiceOilTable->generateOilTableContent($articleData); // NOVA LINHA
                break; // NOVA LINHA
            case 'oil_recommendation':
            default:
                $apiResult = $this->claudeServiceOil->renovateContent($articleData);
                break;
        }

        \Log::info('Articles', [$apiResult]);

        return $apiResult;
    }

    protected function prepareArticleData($postDetail)
    {
        // Extrair informações relevantes do post original
        return [
            'title' => $postDetail->title,
            'category_slug' => $postDetail->new_category_slug,
            'keywords' => $this->extractKeywords($postDetail->title, $postDetail->slug),
        ];
    }

    protected function extractKeywords($title, $slug)
    {
        // Extrair palavras-chave relevantes do título e slug
        $keywords = array_merge(
            explode('-', $slug),
            explode(' ', strtolower($title))
        );

        // Filtra palavras comuns e palavras muito curtas
        $keywords = array_filter($keywords, function ($word) {
            return strlen($word) > 3 && !in_array($word, [
                'para',
                'como',
                'qual',
                'guia',
                'completo',
                'recomendado',
                'dicas',
                'sobre',
                'escolha',
                'melhor',
                'entre'
            ]);
        });

        return implode(', ', array_unique($keywords));
    }


}

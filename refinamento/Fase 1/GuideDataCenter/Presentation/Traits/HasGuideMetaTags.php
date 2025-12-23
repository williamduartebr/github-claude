<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\Traits;

use Torann\LaravelMetaTags\Facades\MetaTag;

/**
 * Trait HasGuideMetaTags
 * 
 * Abstração para configuração de meta tags em controllers de guias
 * Usando a facade MetaTag do pacote torann/laravel-meta-tags
 * 
 * Princípios aplicados:
 * - DRY (Don't Repeat Yourself)
 * - SRP (Single Responsibility Principle)
 * - Clean Code
 * 
 * @package Src\GuideDataCenter\Presentation\Traits
 */
trait HasGuideMetaTags
{
    /**
     * Configura meta tags para guias
     * 
     * @param array $seoData Dados de SEO vindos do ViewModel
     * @return void
     */
    protected function setGuideMetaTags(array $seoData): void
    {
        // Title
        if (!empty($seoData['title'])) {
            MetaTag::set('title', $seoData['title']);
        }

        // Meta Description
        if (!empty($seoData['meta_description'])) {
            MetaTag::set('description', $seoData['meta_description']);
        }

        // Keywords
        if (!empty($seoData['keywords'])) {
            MetaTag::set('keywords', $seoData['keywords']);
        }

        // Canonical URL
        if (!empty($seoData['canonical_url'])) {
            MetaTag::set('canonical', $seoData['canonical_url']);
        }

        // Open Graph
        $this->setOpenGraphTags($seoData);

        // Twitter Cards
        $this->setTwitterCardTags($seoData);

        // Robots
        if (!empty($seoData['robots'])) {
            MetaTag::set('robots', $seoData['robots']);
        }
    }

    /**
     * Configura Open Graph meta tags
     * 
     * @param array $seoData
     * @return void
     */
    protected function setOpenGraphTags(array $seoData): void
    {
        $og = $seoData['og'] ?? [];

        if (!empty($og['title'])) {
            MetaTag::set('og:title', $og['title']);
        }

        if (!empty($og['description'])) {
            MetaTag::set('og:description', $og['description']);
        }

        if (!empty($og['image'])) {
            MetaTag::set('og:image', $og['image']);
        }

        if (!empty($og['url'])) {
            MetaTag::set('og:url', $og['url']);
        }

        if (!empty($og['type'])) {
            MetaTag::set('og:type', $og['type']);
        } else {
            MetaTag::set('og:type', 'article');
        }

        if (!empty($og['site_name'])) {
            MetaTag::set('og:site_name', $og['site_name']);
        }

        if (!empty($og['locale'])) {
            MetaTag::set('og:locale', $og['locale']);
        }
    }

    /**
     * Configura Twitter Card meta tags
     * 
     * @param array $seoData
     * @return void
     */
    protected function setTwitterCardTags(array $seoData): void
    {
        $twitter = $seoData['twitter'] ?? [];

        if (!empty($twitter['card'])) {
            MetaTag::set('twitter:card', $twitter['card']);
        } else {
            MetaTag::set('twitter:card', 'summary_large_image');
        }

        if (!empty($twitter['title'])) {
            MetaTag::set('twitter:title', $twitter['title']);
        }

        if (!empty($twitter['description'])) {
            MetaTag::set('twitter:description', $twitter['description']);
        }

        if (!empty($twitter['image'])) {
            MetaTag::set('twitter:image', $twitter['image']);
        }

        if (!empty($twitter['site'])) {
            MetaTag::set('twitter:site', $twitter['site']);
        }

        if (!empty($twitter['creator'])) {
            MetaTag::set('twitter:creator', $twitter['creator']);
        }
    }

    /**
     * Retorna Structured Data (Schema.org) formatado para JSON-LD
     * 
     * Este método será usado nas views com @push('head')
     * 
     * @param array $structuredData
     * @return string JSON-LD formatado
     */
    protected function getStructuredDataJson(array $structuredData): string
    {
        if (empty($structuredData)) {
            return '';
        }

        return json_encode(
            $structuredData,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }

    /**
     * Valida e sanitiza dados de SEO
     * 
     * @param array $seoData
     * @return array
     */
    protected function sanitizeSeoData(array $seoData): array
    {
        return [
            'title' => $this->sanitizeString($seoData['title'] ?? ''),
            'meta_description' => $this->sanitizeString($seoData['meta_description'] ?? ''),
            'keywords' => $this->sanitizeString($seoData['keywords'] ?? ''),
            'canonical_url' => $this->sanitizeUrl($seoData['canonical_url'] ?? ''),
            'og' => $seoData['og'] ?? [],
            'twitter' => $seoData['twitter'] ?? [],
            'robots' => $seoData['robots'] ?? 'index,follow',
        ];
    }

    /**
     * Sanitiza string removendo caracteres especiais
     * 
     * @param string $value
     * @return string
     */
    private function sanitizeString(string $value): string
    {
        return strip_tags(trim($value));
    }

    /**
     * Sanitiza URL
     * 
     * @param string $url
     * @return string
     */
    private function sanitizeUrl(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

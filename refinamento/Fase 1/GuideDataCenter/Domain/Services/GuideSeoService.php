<?php

namespace Src\GuideDataCenter\Domain\Services;

use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideSeo;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideSeoRepositoryInterface;

/**
 * Class GuideSeoService
 * 
 * Serviço para gerenciamento de SEO dos guias
 */
class GuideSeoService
{
    protected $seoRepository;

    public function __construct(GuideSeoRepositoryInterface $seoRepository)
    {
        $this->seoRepository = $seoRepository;
    }

    /**
     * Cria SEO inicial para um guia
     */
    public function createInitialSeo(Guide $guide): GuideSeo
    {
        $payload = $this->generateSeoPayload($guide);
        return $this->seoRepository->saveSeo($guide->_id, $payload);
    }

    /**
     * Gera payload de SEO automaticamente
     */
    protected function generateSeoPayload(Guide $guide): array
    {
        $category = $guide->category->name ?? 'Guia';
        $vehicle = $guide->make . ' ' . $guide->model;
        $yearText = $guide->year_range_text ?? '';

        $title = $this->generateTitle($category, $vehicle, $yearText);
        $h1 = $this->generateH1($category, $vehicle, $yearText);
        $metaDescription = $this->generateMetaDescription($category, $vehicle, $yearText);
        $primaryKeyword = $this->generatePrimaryKeyword($category, $vehicle);

        return [
            'slug' => $guide->slug,
            'title' => $title,
            'h1' => $h1,
            'meta_description' => $metaDescription,
            'meta_keywords' => implode(', ', [$primaryKeyword, $vehicle, $category]),
            'primary_keyword' => $primaryKeyword,
            'secondary_keywords' => $this->generateSecondaryKeywords($guide),
            'canonical_url' => $guide->url,
            'schema_org' => [],
            'open_graph' => [],
            'twitter_card' => [],
        ];
    }

    protected function generateTitle(string $category, string $vehicle, string $year): string
    {
        if ($year) {
            return "{$category} {$vehicle} {$year} - Guia Completo";
        }
        return "{$category} {$vehicle} - Guia Completo";
    }

    protected function generateH1(string $category, string $vehicle, string $year): string
    {
        if ($year) {
            return "{$category} do {$vehicle} ({$year})";
        }
        return "{$category} do {$vehicle}";
    }

    protected function generateMetaDescription(string $category, string $vehicle, string $year): string
    {
        $base = "Guia completo sobre {$category} do {$vehicle}";
        if ($year) {
            $base .= " {$year}";
        }
        $base .= ". Informações técnicas, especificações, recomendações e dicas de manutenção.";
        return substr($base, 0, 160);
    }

    protected function generatePrimaryKeyword(string $category, string $vehicle): string
    {
        return strtolower($category . ' ' . $vehicle);
    }

    protected function generateSecondaryKeywords(Guide $guide): array
    {
        return [
            $guide->make . ' ' . $guide->model,
            $guide->category->name ?? '',
            'manutenção ' . $guide->make,
            'especificações ' . $guide->model,
        ];
    }

    /**
     * Atualiza schema.org do SEO
     */
    public function updateSchemaOrg(Guide $guide): bool
    {
        $seo = $this->seoRepository->getSeoForGuide($guide->_id);
        if (!$seo) {
            return false;
        }

        $schema = $seo->generateTechnicalArticleSchema($guide);
        return $this->seoRepository->updateSchema($guide->_id, $schema);
    }

    /**
     * Calcula e retorna score de SEO
     */
    public function calculateSeoScore(string $guideId): float
    {
        $seo = $this->seoRepository->getSeoForGuide($guideId);
        return $seo ? $seo->calculateSeoScore() : 0.0;
    }
}

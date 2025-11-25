<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Contracts\Support\Arrayable;
use Src\GuideDataCenter\Domain\Mongo\GuideSeo;


class GuideSeoViewModel implements Arrayable
{
    public readonly string $id;
    public readonly string $guideId;
    public readonly string $slug;
    public readonly string $title;
    public readonly string $h1;
    public readonly string $metaDescription;
    public readonly string $primaryKeyword;
    public readonly array $secondaryKeywords;
    public readonly ?string $canonicalUrl;
    public readonly ?int $wordCount;
    public readonly ?float $readabilityScore;
    public readonly array $ogData;
    public readonly array $twitterData;
    public readonly ?array $schemaMarkup;

    public function __construct(GuideSeo $seo)
    {
        $this->id = (string) $seo->_id;
        $this->guideId = (string) $seo->guide_id;
        $this->slug = $seo->slug;
        $this->title = $seo->title;
        $this->h1 = $seo->h1;
        $this->metaDescription = $seo->meta_description;
        $this->primaryKeyword = $seo->primary_keyword;
        $this->secondaryKeywords = $seo->secondary_keywords ?? [];
        $this->canonicalUrl = $seo->canonical_url;
        $this->wordCount = $seo->word_count;
        $this->readabilityScore = $seo->readability_score;
        $this->ogData = $this->buildOgData($seo);
        $this->twitterData = $this->buildTwitterData($seo);
        $this->schemaMarkup = $seo->schema_markup ?? null;
    }

    /**
     * Constrói dados Open Graph
     */
    private function buildOgData(GuideSeo $seo): array
    {
        return [
            'og:title' => $seo->og_title ?? $seo->title,
            'og:description' => $seo->og_description ?? $seo->meta_description,
            'og:type' => $seo->og_type ?? 'article',
            'og:url' => $seo->canonical_url ?? '',
            'og:image' => $seo->og_image ?? '',
            'og:locale' => $seo->og_locale ?? 'pt_BR',
            'og:site_name' => $seo->og_site_name ?? config('app.name'),
        ];
    }

    /**
     * Constrói dados Twitter Card
     */
    private function buildTwitterData(GuideSeo $seo): array
    {
        return [
            'twitter:card' => $seo->twitter_card ?? 'summary_large_image',
            'twitter:title' => $seo->twitter_title ?? $seo->title,
            'twitter:description' => $seo->twitter_description ?? $seo->meta_description,
            'twitter:image' => $seo->twitter_image ?? $seo->og_image ?? '',
        ];
    }

    /**
     * Retorna todas as keywords combinadas
     */
    public function getAllKeywords(): array
    {
        return array_merge([$this->primaryKeyword], $this->secondaryKeywords);
    }

    /**
     * Retorna keywords como string para meta tag
     */
    public function getKeywordsString(): string
    {
        return implode(', ', $this->getAllKeywords());
    }

    /**
     * Retorna score de legibilidade formatado
     */
    public function getReadabilityLabel(): string
    {
        if ($this->readabilityScore === null) {
            return 'Não avaliado';
        }

        return match (true) {
            $this->readabilityScore >= 80 => 'Excelente',
            $this->readabilityScore >= 60 => 'Bom',
            $this->readabilityScore >= 40 => 'Regular',
            $this->readabilityScore >= 20 => 'Difícil',
            default => 'Muito difícil'
        };
    }

    /**
     * Gera meta tags HTML
     */
    public function getMetaTags(): string
    {
        $tags = [];

        // Meta básicas
        $tags[] = sprintf('<title>%s</title>', e($this->title));
        $tags[] = sprintf('<meta name="description" content="%s">', e($this->metaDescription));
        $tags[] = sprintf('<meta name="keywords" content="%s">', e($this->getKeywordsString()));

        // Canonical
        if ($this->canonicalUrl) {
            $tags[] = sprintf('<link rel="canonical" href="%s">', e($this->canonicalUrl));
        }

        // Open Graph
        foreach ($this->ogData as $property => $content) {
            if ($content) {
                $tags[] = sprintf('<meta property="%s" content="%s">', e($property), e($content));
            }
        }

        // Twitter Cards
        foreach ($this->twitterData as $name => $content) {
            if ($content) {
                $tags[] = sprintf('<meta name="%s" content="%s">', e($name), e($content));
            }
        }

        return implode("\n    ", $tags);
    }

    /**
     * Retorna Schema.org JSON-LD
     */
    public function getSchemaJsonLd(): string
    {
        if (!$this->schemaMarkup) {
            return '';
        }

        return sprintf(
            '<script type="application/ld+json">%s</script>',
            json_encode($this->schemaMarkup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'guide_id' => $this->guideId,
            'slug' => $this->slug,
            'title' => $this->title,
            'h1' => $this->h1,
            'meta_description' => $this->metaDescription,
            'primary_keyword' => $this->primaryKeyword,
            'secondary_keywords' => $this->secondaryKeywords,
            'all_keywords' => $this->getAllKeywords(),
            'keywords_string' => $this->getKeywordsString(),
            'canonical_url' => $this->canonicalUrl,
            'word_count' => $this->wordCount,
            'readability_score' => $this->readabilityScore,
            'readability_label' => $this->getReadabilityLabel(),
            'og_data' => $this->ogData,
            'twitter_data' => $this->twitterData,
            'schema_markup' => $this->schemaMarkup,
        ];
    }
}

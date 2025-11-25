<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Contracts\Support\Arrayable;
use Src\GuideDataCenter\Presentation\ViewModels\GuideClusterViewModel;
use Src\GuideDataCenter\Presentation\ViewModels\GuideSeoViewModel;
use Src\GuideDataCenter\Domain\Mongo\GuideSeo;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Illuminate\Support\Collection;

class GuideViewModel implements Arrayable
{
    public readonly string $id;
    public readonly string $slug;
    public readonly string $title;
    public readonly string $make;
    public readonly string $model;
    public readonly ?string $version;
    public readonly string $makeSlug;
    public readonly string $modelSlug;
    public readonly ?int $yearStart;
    public readonly ?int $yearEnd;
    public readonly string $yearRange;
    public readonly string $template;
    public readonly array $payload;
    public readonly ?GuideSeoViewModel $seo;
    public readonly Collection $clusters;
    public readonly string $url;
    public readonly string $createdAt;
    public readonly string $updatedAt;

    public function __construct(
        Guide $guide,
        ?GuideSeo $seo = null,
        ?Collection $clusters = null
    ) {
        $this->id = (string) $guide->_id;
        $this->slug = $guide->slug;
        $this->title = $guide->payload['title'] ?? $this->buildTitle($guide);
        $this->make = $guide->make;
        $this->model = $guide->model;
        $this->version = $guide->version;
        $this->makeSlug = $guide->make_slug;
        $this->modelSlug = $guide->model_slug;
        $this->yearStart = $guide->year_start;
        $this->yearEnd = $guide->year_end;
        $this->yearRange = $this->formatYearRange($guide->year_start, $guide->year_end);
        $this->template = $guide->template;
        $this->payload = $guide->payload ?? [];
        $this->url = route('guide.show', ['slug' => $guide->slug]);
        $this->createdAt = $guide->created_at?->format('Y-m-d H:i:s') ?? '';
        $this->updatedAt = $guide->updated_at?->format('Y-m-d H:i:s') ?? '';

        $this->seo = $seo ? new GuideSeoViewModel($seo) : null;
        $this->clusters = $clusters?->map(fn($c) => new GuideClusterViewModel($c)) ?? collect([]);
    }

    /**
     * Constrói título padrão se não existir no payload
     */
    private function buildTitle(Guide $guide): string
    {
        $title = "{$guide->make} {$guide->model}";

        if ($guide->version) {
            $title .= " {$guide->version}";
        }

        if ($guide->year_start) {
            $title .= " ({$this->formatYearRange($guide->year_start, $guide->year_end)})";
        }

        return $title;
    }

    /**
     * Formata range de anos para exibição
     */
    private function formatYearRange(?int $start, ?int $end): string
    {
        if (!$start) {
            return '';
        }

        if (!$end || $start === $end) {
            return (string) $start;
        }

        return "{$start}-{$end}";
    }

    /**
     * Retorna conteúdo do payload processado
     */
    public function getContent(): ?string
    {
        return $this->payload['content'] ?? null;
    }

    /**
     * Retorna seções do guia se existirem
     */
    public function getSections(): array
    {
        return $this->payload['sections'] ?? [];
    }

    /**
     * Retorna FAQs do guia
     */
    public function getFaqs(): array
    {
        return $this->payload['faqs'] ?? [];
    }

    /**
     * Retorna imagem principal do guia
     */
    public function getFeaturedImage(): ?string
    {
        return $this->payload['featured_image'] ?? null;
    }

    /**
     * Retorna dados para Schema.org (JSON-LD)
     */
    public function getStructuredData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->title,
            'url' => $this->url,
            'datePublished' => $this->createdAt,
            'dateModified' => $this->updatedAt,
        ];

        if ($this->getFeaturedImage()) {
            $data['image'] = $this->getFeaturedImage();
        }

        if ($this->seo) {
            $data['description'] = $this->seo->metaDescription;
            $data['keywords'] = implode(', ', array_merge(
                [$this->seo->primaryKeyword],
                $this->seo->secondaryKeywords
            ));
        }

        // Adiciona FAQ Schema se existirem
        $faqs = $this->getFaqs();
        if (!empty($faqs)) {
            $data['mainEntity'] = [
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn($faq) => [
                    '@type' => 'Question',
                    'name' => $faq['question'] ?? '',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['answer'] ?? ''
                    ]
                ], $faqs)
            ];
        }

        return $data;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'make' => $this->make,
            'model' => $this->model,
            'version' => $this->version,
            'make_slug' => $this->makeSlug,
            'model_slug' => $this->modelSlug,
            'year_start' => $this->yearStart,
            'year_end' => $this->yearEnd,
            'year_range' => $this->yearRange,
            'template' => $this->template,
            'payload' => $this->payload,
            'content' => $this->getContent(),
            'sections' => $this->getSections(),
            'faqs' => $this->getFaqs(),
            'featured_image' => $this->getFeaturedImage(),
            'url' => $this->url,
            'seo' => $this->seo?->toArray(),
            'clusters' => $this->clusters->toArray(),
            'structured_data' => $this->getStructuredData(),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}

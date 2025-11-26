<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Contracts\Support\Arrayable;
use Src\GuideDataCenter\Domain\Mongo\Guide;

class GuideListViewModel implements Arrayable
{
    public readonly string $id;
    public readonly string $slug;
    public readonly string $title;
    public readonly string $make;
    public readonly string $model;
    public readonly ?string $version;
    public readonly string $makeSlug;
    public readonly string $modelSlug;
    public readonly string $yearRange;
    public readonly string $template;
    public readonly ?string $excerpt;
    public readonly ?string $featuredImage;
    public readonly string $url;
    public readonly string $categoryUrl;
    public readonly string $vehicleUrl;
    public readonly string $createdAt;

    public function __construct(Guide $guide)
    {
        $this->id = (string) $guide->_id;
        $this->slug = $guide->slug;
        $this->title = $guide->payload['title'] ?? $this->buildTitle($guide);
        $this->make = $guide->make;
        $this->model = $guide->model;
        $this->version = $guide->version;
        $this->makeSlug = $guide->make_slug;
        $this->modelSlug = $guide->model_slug;
        $this->yearRange = $this->formatYearRange($guide->year_start, $guide->year_end);
        $this->template = $guide->template;
        $this->excerpt = $this->buildExcerpt($guide);
        $this->featuredImage = $guide->payload['featured_image'] ?? null;
        $this->url = route('guide.show', ['slug' => $guide->slug]);
        $this->categoryUrl = $guide->guide_category_id 
            ? route('guide.category', ['category' => $guide->guide_category_id])
            : '';
        $this->vehicleUrl = route('guide.byModel', [
            'make' => $guide->make_slug,
            'model' => $guide->model_slug
        ]);
        $this->createdAt = $guide->created_at?->format('d/m/Y') ?? '';
    }

    /**
     * Constrói título padrão
     */
    private function buildTitle(Guide $guide): string
    {
        $title = "{$guide->make} {$guide->model}";

        if ($guide->version) {
            $title .= " {$guide->version}";
        }

        return $title;
    }

    /**
     * Formata range de anos
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
     * Gera excerpt do conteúdo
     */
    private function buildExcerpt(Guide $guide, int $length = 160): ?string
    {
        $content = $guide->payload['excerpt'] 
            ?? $guide->payload['content'] 
            ?? $guide->seo['meta_description'] 
            ?? null;

        if (!$content) {
            return null;
        }

        // Remove HTML e trunca
        $text = strip_tags($content);
        
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . '...';
    }

    /**
     * Retorna nome formatado do veículo
     */
    public function getVehicleName(): string
    {
        $name = "{$this->make} {$this->model}";

        if ($this->version) {
            $name .= " {$this->version}";
        }

        if ($this->yearRange) {
            $name .= " ({$this->yearRange})";
        }

        return $name;
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
            'year_range' => $this->yearRange,
            'template' => $this->template,
            'excerpt' => $this->excerpt,
            'featured_image' => $this->featuredImage,
            'vehicle_name' => $this->getVehicleName(),
            'url' => $this->url,
            'category_url' => $this->categoryUrl,
            'vehicle_url' => $this->vehicleUrl,
            'created_at' => $this->createdAt,
        ];
    }
}

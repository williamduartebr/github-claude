<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Contracts\Support\Arrayable;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;

class GuideCategoryViewModel implements Arrayable
{
    public readonly string $id;
    public readonly string $name;
    public readonly string $slug;
    public readonly ?string $description;
    public readonly ?string $icon;
    public readonly ?string $image;
    public readonly int $order;
    public readonly bool $active;
    public readonly string $url;
    public readonly int $guidesCount;
    public readonly ?string $metaTitle;
    public readonly ?string $metaDescription;

    public function __construct(GuideCategory $category)
    {
        $this->id = (string) $category->_id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description;
        $this->icon = $category->icon ?? null;
        $this->image = $category->image ?? null;
        $this->order = $category->order ?? 0;
        $this->active = $category->active ?? true;
        $this->url = route('guide.category', ['category' => $category->slug]);
        $this->guidesCount = $category->guides_count ?? 0;
        $this->metaTitle = $category->meta_title ?? $category->name;
        $this->metaDescription = $category->meta_description ?? $category->description;
    }

    /**
     * Retorna dados para Schema.org (JSON-LD)
     */
    public function getStructuredData(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->url,
        ];
    }

    /**
     * Retorna breadcrumb da categoria
     */
    public function getBreadcrumb(): array
    {
        return [
            [
                'name' => 'Home',
                'url' => url('/'),
            ],
            [
                'name' => 'Guias',
                'url' => route('guide.index'),
            ],
            [
                'name' => $this->name,
                'url' => $this->url,
            ],
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'image' => $this->image,
            'order' => $this->order,
            'active' => $this->active,
            'url' => $this->url,
            'guides_count' => $this->guidesCount,
            'meta_title' => $this->metaTitle,
            'meta_description' => $this->metaDescription,
            'structured_data' => $this->getStructuredData(),
            'breadcrumb' => $this->getBreadcrumb(),
        ];
    }
}

<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

class VehicleSeoViewModel
{
    private $seoData;

    public function __construct(array $seoData)
    {
        $this->seoData = $seoData;
    }

    public function getTitle(): string
    {
        return $this->seoData['title'];
    }

    public function getMetaDescription(): string
    {
        return $this->seoData['meta_description'];
    }

    public function getKeywords(): string
    {
        return implode(', ', $this->seoData['meta_keywords']);
    }

    public function getCanonicalUrl(): string
    {
        return $this->seoData['canonical_url'];
    }

    public function getOpenGraphTags(): array
    {
        return $this->seoData['og_data'];
    }

    public function getJsonLd(): string
    {
        return json_encode($this->seoData['json_ld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function getInternalLinks(): array
    {
        return $this->seoData['internal_links'];
    }
}

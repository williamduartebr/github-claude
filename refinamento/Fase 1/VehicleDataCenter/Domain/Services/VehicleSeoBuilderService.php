<?php

namespace Src\VehicleDataCenter\Domain\Services;

use Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleSeoData;

class VehicleSeoBuilderService
{
    public function __construct(
        private VehicleVersionRepositoryInterface $versionRepository
    ) {}

    public function buildSeoForVersion(int $versionId): array
    {
        $version = $this->versionRepository->findById($versionId);

        if (!$version) {
            throw new \InvalidArgumentException('Version not found');
        }

        $make = $version->model->make;
        $model = $version->model;

        $seoData = [
            'version_id' => $version->id,
            'make_slug' => $make->slug,
            'model_slug' => $model->slug,
            'version_slug' => $version->slug,
            'year' => $version->year,
            'title' => $this->buildTitle($version),
            'meta_description' => $this->buildMetaDescription($version),
            'meta_keywords' => $this->buildKeywords($version),
            'canonical_url' => $this->buildCanonicalUrl($version),
            'og_data' => $this->buildOpenGraph($version),
            'schema_markup' => $this->buildSchemaMarkup($version),
            'json_ld' => $this->buildJsonLd($version),
            'internal_links' => $this->buildInternalLinks($version)
        ];

        // Save to MongoDB
        $existing = VehicleSeoData::where('version_id', $versionId)->first();

        if ($existing) {
            $existing->update($seoData);
        } else {
            VehicleSeoData::create($seoData);
        }

        return $seoData;
    }

    private function buildTitle($version): string
    {
        $make = $version->model->make->name;
        $model = $version->model->name;
        $versionName = $version->name;
        $year = $version->year;

        return "{$make} {$model} {$versionName} {$year} - Ficha Técnica Completa";
    }

    private function buildMetaDescription($version): string
    {
        $make = $version->model->make->name;
        $model = $version->model->name;
        $year = $version->year;
        $specs = $version->specs;

        $description = "Confira a ficha técnica completa do {$make} {$model} {$year}. ";

        if ($specs) {
            if ($specs->power_hp) {
                $description .= "{$specs->power_hp} cv, ";
            }
            if ($specs->fuel_consumption_mixed) {
                $description .= "{$specs->fuel_consumption_mixed} km/l, ";
            }
        }

        $description .= "especificações, motor, pneus, óleo e muito mais.";

        return substr($description, 0, 160);
    }

    private function buildKeywords($version): array
    {
        $make = $version->model->make->name;
        $model = $version->model->name;
        $year = $version->year;

        return [
            "{$make} {$model}",
            "{$make} {$model} {$year}",
            "ficha técnica {$make} {$model}",
            "{$make} {$model} especificações",
            "{$make} {$model} consumo",
            "{$make} {$model} motor",
            "{$make} {$model} pneus",
            "óleo {$make} {$model}",
            $version->fuel_type,
            $version->transmission,
            $version->model->category
        ];
    }

    private function buildCanonicalUrl($version): string
    {
        $make = $version->model->make->slug;
        $model = $version->model->slug;
        $year = $version->year;
        $versionSlug = $version->slug;

        return url("/veiculos/{$make}/{$model}/{$year}/{$versionSlug}");
    }

    private function buildOpenGraph($version): array
    {
        return [
            'og:type' => 'product',
            'og:title' => $this->buildTitle($version),
            'og:description' => $this->buildMetaDescription($version),
            'og:url' => $this->buildCanonicalUrl($version),
            'og:image' => $version->model->make->logo_url ?? '',
            'og:site_name' => config('app.name'),
            'product:brand' => $version->model->make->name,
            'product:category' => $version->model->category,
            'product:price:amount' => $version->price_msrp ?? '',
            'product:price:currency' => 'BRL'
        ];
    }

    private function buildSchemaMarkup($version): array
    {
        $specs = $version->specs;

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Car',
            'name' => "{$version->model->make->name} {$version->model->name} {$version->name}",
            'brand' => [
                '@type' => 'Brand',
                'name' => $version->model->make->name
            ],
            'model' => $version->model->name,
            'modelDate' => $version->year,
            'vehicleModelDate' => $version->year,
            'vehicleEngine' => [
                '@type' => 'EngineSpecification',
                'engineType' => $version->fuel_type,
                'enginePower' => $specs->power_hp ?? null
            ],
            'fuelType' => $version->fuel_type,
            'vehicleTransmission' => $version->transmission,
            'bodyType' => $version->model->category,
            'numberOfDoors' => $specs->doors ?? null,
            'seatingCapacity' => $specs->seating_capacity ?? 5,
            'url' => $this->buildCanonicalUrl($version)
        ];
    }

    private function buildJsonLd($version): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => "{$version->model->make->name} {$version->model->name} {$version->name} {$version->year}",
            'description' => $this->buildMetaDescription($version),
            'brand' => [
                '@type' => 'Brand',
                'name' => $version->model->make->name
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $version->price_msrp ?? '0',
                'priceCurrency' => 'BRL',
                'availability' => 'https://schema.org/InStock'
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.5',
                'reviewCount' => '0'
            ]
        ];
    }

    private function buildInternalLinks($version): array
    {
        $make = $version->model->make;
        $model = $version->model;

        return [
            [
                'anchor' => $make->name,
                'url' => url("/veiculos/{$make->slug}"),
                'title' => "Ver todos os modelos {$make->name}"
            ],
            [
                'anchor' => "{$make->name} {$model->name}",
                'url' => url("/veiculos/{$make->slug}/{$model->slug}"),
                'title' => "Ver todas as versões {$make->name} {$model->name}"
            ],
            [
                'anchor' => "{$make->name} {$model->name} {$version->year}",
                'url' => url("/veiculos/{$make->slug}/{$model->slug}/{$version->year}"),
                'title' => "Ver versões {$make->name} {$model->name} {$version->year}"
            ]
        ];
    }

    public function buildSeoForAllVersions(): array
    {
        $versions = $this->versionRepository->all();
        $results = [
            'success' => 0,
            'failed' => 0
        ];

        foreach ($versions as $version) {
            try {
                $this->buildSeoForVersion($version->id);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return $results;
    }
}

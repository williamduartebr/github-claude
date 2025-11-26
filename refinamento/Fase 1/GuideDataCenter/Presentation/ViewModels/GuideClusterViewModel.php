<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Contracts\Support\Arrayable;
use Src\GuideDataCenter\Domain\Mongo\GuideCluster;

class GuideClusterViewModel implements Arrayable
{
    public readonly string $id;
    public readonly string $guideId;
    public readonly string $makeSlug;
    public readonly string $modelSlug;
    public readonly ?string $yearRange;
    public readonly string $clusterType;
    public readonly array $relatedGuides;
    public readonly array $internalLinks;
    public readonly string $url;
    public readonly string $updatedAt;

    public function __construct(GuideCluster $cluster)
    {
        $this->id = (string) $cluster->_id;
        $this->guideId = (string) $cluster->guide_id;
        $this->makeSlug = $cluster->make_slug;
        $this->modelSlug = $cluster->model_slug;
        $this->yearRange = $cluster->year_range;
        $this->clusterType = $cluster->cluster_type;
        $this->relatedGuides = $cluster->related_guides ?? [];
        $this->internalLinks = $cluster->internal_links ?? [];
        $this->url = $this->buildUrl($cluster);
        $this->updatedAt = $cluster->updated_at?->format('Y-m-d H:i:s') ?? '';
    }

    /**
     * Constrói URL do cluster
     */
    private function buildUrl(GuideCluster $cluster): string
    {
        $params = [
            'make' => $cluster->make_slug,
            'model' => $cluster->model_slug,
        ];

        if ($cluster->year_range) {
            $params['year'] = $cluster->year_range;
        }

        return route('guide.cluster', $params);
    }

    /**
     * Retorna nome formatado do veículo
     */
    public function getVehicleName(): string
    {
        $make = ucfirst(str_replace('-', ' ', $this->makeSlug));
        $model = ucfirst(str_replace('-', ' ', $this->modelSlug));

        $name = "{$make} {$model}";

        if ($this->yearRange) {
            $name .= " ({$this->yearRange})";
        }

        return $name;
    }

    /**
     * Retorna tipo de cluster formatado
     */
    public function getClusterTypeLabel(): string
    {
        return match ($this->clusterType) {
            'model' => 'Por Modelo',
            'year' => 'Por Ano',
            'category' => 'Por Categoria',
            'super' => 'Super Cluster',
            default => ucfirst($this->clusterType)
        };
    }

    /**
     * Retorna quantidade de guias relacionados
     */
    public function getRelatedGuidesCount(): int
    {
        return count($this->relatedGuides);
    }

    /**
     * Retorna quantidade de links internos
     */
    public function getInternalLinksCount(): int
    {
        return count($this->internalLinks);
    }

    /**
     * Retorna links formatados para exibição
     */
    public function getFormattedLinks(): array
    {
        return array_map(fn($link) => [
            'title' => $link['title'] ?? 'Link',
            'url' => $link['url'] ?? '#',
            'anchor' => $link['anchor'] ?? $link['title'] ?? 'Saiba mais',
        ], $this->internalLinks);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'guide_id' => $this->guideId,
            'make_slug' => $this->makeSlug,
            'model_slug' => $this->modelSlug,
            'year_range' => $this->yearRange,
            'cluster_type' => $this->clusterType,
            'cluster_type_label' => $this->getClusterTypeLabel(),
            'vehicle_name' => $this->getVehicleName(),
            'related_guides' => $this->relatedGuides,
            'related_guides_count' => $this->getRelatedGuidesCount(),
            'internal_links' => $this->internalLinks,
            'internal_links_count' => $this->getInternalLinksCount(),
            'formatted_links' => $this->getFormattedLinks(),
            'url' => $this->url,
            'updated_at' => $this->updatedAt,
        ];
    }
}

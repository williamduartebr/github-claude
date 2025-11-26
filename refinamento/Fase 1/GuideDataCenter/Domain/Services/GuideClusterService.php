<?php

namespace Src\GuideDataCenter\Domain\Services;

use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCluster;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideClusterRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;

/**
 * Class GuideClusterService
 * 
 * Serviço responsável pela geração e gerenciamento de clusters de links internos
 * 
 * Funcionalidades:
 * - Gera links internos automáticos por categoria (óleo, pneus, etc)
 * - Cria super clusters por ano, geração, motor, versões
 * - Gerencia malha de links entre guias relacionados
 */
class GuideClusterService
{
    /**
     * @var GuideClusterRepositoryInterface
     */
    protected $clusterRepository;

    /**
     * @var GuideRepositoryInterface
     */
    protected $guideRepository;

    /**
     * Categorias de links internos
     */
    protected const INTERNAL_LINK_CATEGORIES = [
        'oleo' => 'Óleo Motor',
        'pneus' => 'Pneus e Rodas',
        'calibragem' => 'Calibragem de Pneus',
        'revisoes' => 'Revisões Programadas',
        'consumo' => 'Consumo de Combustível',
        'problemas' => 'Problemas Comuns',
        'fluidos' => 'Fluidos e Lubrificantes',
        'bateria' => 'Bateria',
        'cambio' => 'Transmissão e Câmbio',
        'motor' => 'Especificações do Motor',
    ];

    /**
     * Constructor
     */
    public function __construct(
        GuideClusterRepositoryInterface $clusterRepository,
        GuideRepositoryInterface $guideRepository
    ) {
        $this->clusterRepository = $clusterRepository;
        $this->guideRepository = $guideRepository;
    }

    /**
     * Cria cluster básico para um guia novo
     *
     * @param Guide $guide
     * @return GuideCluster
     */
    public function createBasicCluster(Guide $guide): GuideCluster
    {
        return $this->clusterRepository->createCluster([
            'guide_id' => $guide->_id,
            'make_slug' => $guide->make_slug,
            'model_slug' => $guide->model_slug,
            'year_range' => $guide->year_range_text,
            'cluster_type' => GuideCluster::TYPE_CATEGORY,
            'links' => [],
        ]);
    }

    /**
     * Gera links internos automáticos para todas as categorias
     *
     * @param Guide $guide
     * @return array
     */
    public function generateInternalLinks(Guide $guide): array
    {
        $links = [];

        foreach (self::INTERNAL_LINK_CATEGORIES as $slug => $title) {
            $targetGuide = $this->findGuideForCategory($guide, $slug);

            if ($targetGuide) {
                $links[$slug] = [
                    'url' => $targetGuide->url,
                    'title' => $title . ' - ' . $guide->make . ' ' . $guide->model,
                    'guide_id' => $targetGuide->_id,
                ];
            }
        }

        return $links;
    }

    /**
     * Busca guia de uma categoria específica para o veículo
     *
     * @param Guide $guide
     * @param string $categorySlug
     * @return Guide|null
     */
    protected function findGuideForCategory(Guide $guide, string $categorySlug): ?Guide
    {
        return $this->guideRepository->findByFilters([
            'make_slug' => $guide->make_slug,
            'model_slug' => $guide->model_slug,
            'category_slug' => $categorySlug,
            'year' => $guide->year_start,
            'limit' => 1,
        ])->first();
    }

    /**
     * Gera super cluster para marca/modelo
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @return GuideCluster
     */
    public function generateSuperCluster(string $makeSlug, string $modelSlug): GuideCluster
    {
        $cluster = $this->clusterRepository->findOrCreateSuperCluster($makeSlug, $modelSlug);

        // Busca todos os guias deste veículo
        $guides = $this->guideRepository->listByMake($makeSlug)->filter(function ($guide) use ($modelSlug) {
            return $guide->model_slug === $modelSlug;
        });

        // Agrupa por categoria
        $linksByCategory = [];
        foreach ($guides as $guide) {
            $categorySlug = $guide->category->slug ?? 'outros';
            
            if (!isset($linksByCategory[$categorySlug])) {
                $linksByCategory[$categorySlug] = [];
            }

            $linksByCategory[$categorySlug][] = [
                'url' => $guide->url,
                'title' => $guide->payload['title'] ?? $guide->full_title,
                'year_range' => $guide->year_range_text,
                'guide_id' => $guide->_id,
            ];
        }

        $cluster->links = $linksByCategory;
        $cluster->save();

        return $cluster;
    }

    /**
     * Cria cluster por ano
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int $yearStart
     * @param int|null $yearEnd
     * @return GuideCluster
     */
    public function createYearCluster(
        string $makeSlug,
        string $modelSlug,
        int $yearStart,
        ?int $yearEnd = null
    ): GuideCluster {
        $yearRange = $yearEnd && $yearEnd != $yearStart
            ? "{$yearStart}-{$yearEnd}"
            : (string) $yearStart;

        $guides = $this->guideRepository->findByFilters([
            'make_slug' => $makeSlug,
            'model_slug' => $modelSlug,
            'year' => $yearStart,
        ]);

        $links = [];
        foreach ($guides as $guide) {
            $categorySlug = $guide->category->slug ?? 'outros';
            $links[$categorySlug] = [
                'url' => $guide->url,
                'title' => $guide->payload['title'] ?? $guide->full_title,
                'guide_id' => $guide->_id,
            ];
        }

        return $this->clusterRepository->createCluster([
            'make_slug' => $makeSlug,
            'model_slug' => $modelSlug,
            'year_range' => $yearRange,
            'cluster_type' => GuideCluster::TYPE_YEAR,
            'links' => $links,
        ]);
    }

    /**
     * Cria cluster por geração
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param string $generation
     * @return GuideCluster
     */
    public function createGenerationCluster(
        string $makeSlug,
        string $modelSlug,
        string $generation
    ): GuideCluster {
        $guides = $this->guideRepository->findByFilters([
            'make_slug' => $makeSlug,
            'model_slug' => $modelSlug,
        ])->filter(function ($guide) use ($generation) {
            return ($guide->payload['generation'] ?? null) === $generation;
        });

        $links = $this->groupGuidesByCategory($guides);

        return $this->clusterRepository->createCluster([
            'make_slug' => $makeSlug,
            'model_slug' => $modelSlug,
            'cluster_type' => GuideCluster::TYPE_GENERATION,
            'links' => $links,
            'metadata' => ['generation' => $generation],
        ]);
    }

    /**
     * Cria cluster por motor
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param string $motor
     * @return GuideCluster
     */
    public function createMotorCluster(
        string $makeSlug,
        string $modelSlug,
        string $motor
    ): GuideCluster {
        $guides = $this->guideRepository->findByFilters([
            'make_slug' => $makeSlug,
            'model_slug' => $modelSlug,
        ])->filter(function ($guide) use ($motor) {
            return $guide->motor === $motor;
        });

        $links = $this->groupGuidesByCategory($guides);

        return $this->clusterRepository->createCluster([
            'make_slug' => $makeSlug,
            'model_slug' => $modelSlug,
            'cluster_type' => GuideCluster::TYPE_MOTOR,
            'links' => $links,
            'metadata' => ['motor' => $motor],
        ]);
    }

    /**
     * Agrupa guias por categoria
     *
     * @param \Illuminate\Support\Collection $guides
     * @return array
     */
    protected function groupGuidesByCategory($guides): array
    {
        $linksByCategory = [];

        foreach ($guides as $guide) {
            $categorySlug = $guide->category->slug ?? 'outros';
            
            if (!isset($linksByCategory[$categorySlug])) {
                $linksByCategory[$categorySlug] = [];
            }

            $linksByCategory[$categorySlug][] = [
                'url' => $guide->url,
                'title' => $guide->payload['title'] ?? $guide->full_title,
                'year_range' => $guide->year_range_text,
                'guide_id' => $guide->_id,
            ];
        }

        return $linksByCategory;
    }

    /**
     * Atualiza todos os clusters de um guia
     *
     * @param Guide $guide
     * @return bool
     */
    public function updateGuideClusters(Guide $guide): bool
    {
        // Gera links internos
        $internalLinks = $this->generateInternalLinks($guide);

        // Atualiza no guia
        $guide->links_internal = $internalLinks;
        $guide->save();

        // Atualiza super cluster
        $this->generateSuperCluster($guide->make_slug, $guide->model_slug);

        return true;
    }

    /**
     * Sincroniza clusters de todos os guias de um veículo
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @return int Número de guias atualizados
     */
    public function syncVehicleClusters(string $makeSlug, string $modelSlug): int
    {
        $guides = $this->guideRepository->findByFilters([
            'make_slug' => $makeSlug,
            'model_slug' => $modelSlug,
        ]);

        $count = 0;
        foreach ($guides as $guide) {
            $this->updateGuideClusters($guide);
            $count++;
        }

        return $count;
    }

    /**
     * Remove clusters órfãos (sem guia associado)
     *
     * @return int
     */
    public function cleanOrphanClusters(): int
    {
        // Implementação simplificada - pode ser expandida
        return 0;
    }

    /**
     * Retorna estatísticas dos clusters
     *
     * @return array
     */
    public function getClusterStats(): array
    {
        // Implementação de estatísticas
        return [
            'total_clusters' => 0,
            'by_type' => [],
            'by_make' => [],
        ];
    }
}

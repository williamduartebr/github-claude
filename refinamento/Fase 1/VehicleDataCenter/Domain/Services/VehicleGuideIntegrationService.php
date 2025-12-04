<?php

namespace Src\VehicleDataCenter\Domain\Services;

use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Serviço de integração: VehicleDataCenter → GuideDataCenter
 * Permite que páginas de veículos busquem guias do MongoDB
 */
class VehicleGuideIntegrationService
{
    public function __construct(
        private GuideRepositoryInterface $guideRepository,
        private GuideCategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Busca categorias de guias disponíveis para uma marca
     * 
     * @param string $makeSlug
     * @return Collection
     */
    public function getGuideCategoriesByMake(string $makeSlug): Collection
    {
        // Buscar todas as categorias ativas
        $categories = $this->categoryRepository->getAllActive();
        
        // Para cada categoria, verificar se existem guias dessa marca
        return $categories->filter(function($category) use ($makeSlug) {
            $guides = $this->guideRepository->findByFilters([
                'make_slug' => $makeSlug,
                'category_id' => (string) $category->_id,
                'limit' => 1
            ]);
            
            return $guides->isNotEmpty();
        });
    }

    /**
     * Busca guias de uma marca
     * 
     * @param string $makeSlug
     * @param int $limit
     * @return Collection
     */
    public function getGuidesByMake(string $makeSlug, int $limit = 20): Collection
    {
        return $this->guideRepository->listByMake($makeSlug, $limit);
    }

    /**
     * Busca guias de um modelo específico
     * 
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int $limit
     * @return Collection
     */
    public function getGuidesByModel(
        string $makeSlug, 
        string $modelSlug,
        int $limit = 20
    ): Collection {
        return $this->guideRepository->findByFilters([
            'make_slug' => $makeSlug,
            'model_slug' => $modelSlug,
            'limit' => $limit
        ]);
    }

    /**
     * Busca guias rápidos (mais acessados) de um modelo
     * Prioriza categorias importantes: óleo, calibragem, pneus, revisão
     * 
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int $limit
     * @return Collection
     */
    public function getQuickGuidesByModel(
        string $makeSlug,
        string $modelSlug,
        int $limit = 6
    ): Collection {
        // Buscar guias das categorias mais importantes
        $priorityCategories = ['oleo', 'calibragem', 'pneus', 'revisao', 'consumo', 'problemas'];
        
        $guides = collect();
        
        foreach ($priorityCategories as $categorySlug) {
            // Buscar categoria
            $category = $this->categoryRepository->findBySlug($categorySlug);
            
            if (!$category) {
                continue;
            }
            
            // Buscar guia desta categoria para este modelo
            $guide = $this->guideRepository->findByFilters([
                'make_slug' => $makeSlug,
                'model_slug' => $modelSlug,
                'category_id' => (string) $category->_id,
                'limit' => 1
            ])->first();
            
            if ($guide) {
                $guides->push($guide);
            }
            
            if ($guides->count() >= $limit) {
                break;
            }
        }
        
        return $guides;
    }

    /**
     * Busca todas as categorias (para listar)
     * 
     * @return Collection
     */
    public function getAllGuideCategories(): Collection
    {
        return $this->categoryRepository->getAllActive();
    }

    /**
     * Verifica se existem guias para um veículo
     * 
     * @param string $makeSlug
     * @param string|null $modelSlug
     * @return bool
     */
    public function hasGuides(string $makeSlug, ?string $modelSlug = null): bool
    {
        $filters = ['make_slug' => $makeSlug, 'limit' => 1];
        
        if ($modelSlug) {
            $filters['model_slug'] = $modelSlug;
        }
        
        $guides = $this->guideRepository->findByFilters($filters);
        
        return $guides->isNotEmpty();
    }

    /**
     * Busca guias por categoria e veículo
     * 
     * @param string $categorySlug
     * @param string $makeSlug
     * @param string|null $modelSlug
     * @param int $limit
     * @return Collection
     */
    public function getGuidesByCategoryAndVehicle(
        string $categorySlug,
        string $makeSlug,
        ?string $modelSlug = null,
        int $limit = 20
    ): Collection {
        // Buscar categoria
        $category = $this->categoryRepository->findBySlug($categorySlug);
        
        if (!$category) {
            return collect();
        }
        
        $filters = [
            'make_slug' => $makeSlug,
            'category_id' => (string) $category->_id,
            'limit' => $limit
        ];
        
        if ($modelSlug) {
            $filters['model_slug'] = $modelSlug;
        }
        
        return $this->guideRepository->findByFilters($filters);
    }
}
<?php

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;

/**
 * ViewModel para página de categoria + marca
 * 
 * Rota: /guias/{category}/{make}
 * View: guide.category-make
 * Exemplo: /guias/oleo/toyota
 */
class GuideCategoryMakeViewModel
{
    private $category;
    private $make;
    private Collection $models;

    public function __construct($category, $make, Collection $models)
    {
        $this->category = $category;
        $this->make = $make;
        $this->models = $models;
    }

    /**
     * Retorna dados da categoria
     */
    public function getCategory(): array
    {
        return [
            'id' => $this->category->_id ?? null,
            'name' => $this->category->name ?? $this->getCategoryName(),
            'slug' => $this->category->slug ?? 'oleo',
        ];
    }

    /**
     * Retorna dados da marca
     */
    public function getMake(): array
    {
        return [
            'id' => $this->make->id ?? null,
            'name' => $this->make->name ?? $this->getMakeName(),
            'slug' => $this->make->slug ?? 'toyota',
            'logo' => $this->getMakeLogo(),
        ];
    }

    /**
     * Retorna modelos populares (top 3)
     * 
     * TODO: Implementar ordenação por popularidade
     */
    public function getPopularModels(): array
    {
        // TODO: Usar $this->models quando houver dados
        $makeSlug = $this->make->slug ?? 'toyota';
        $categorySlug = $this->category->slug ?? 'oleo';
        
        $mocks = [
            'toyota' => [
                [
                    'name' => 'Corolla',
                    'image' => '/images/placeholder/corolla-hero.jpg',
                    'description' => 'Guia completo de óleo (todos os anos)',
                    'url' => "/guias/{$categorySlug}/toyota/corolla-2003",
                ],
                [
                    'name' => 'Hilux',
                    'image' => '/images/placeholder/hilux-hero.jpg',
                    'description' => 'Diesel • Sintético / Mineral',
                    'url' => "/guias/{$categorySlug}/toyota/hilux-2015",
                ],
                [
                    'name' => 'Yaris',
                    'image' => '/images/placeholder/yaris-hero.jpg',
                    'description' => 'Especificações para 1.3 e 1.5',
                    'url' => "/guias/{$categorySlug}/toyota/yaris-2019",
                ],
            ],
            'honda' => [
                [
                    'name' => 'Civic',
                    'image' => '/images/placeholder/civic-hero.jpg',
                    'description' => 'Guia completo de óleo',
                    'url' => "/guias/{$categorySlug}/honda/civic-2010",
                ],
                [
                    'name' => 'Fit',
                    'image' => '/images/placeholder/fit-hero.jpg',
                    'description' => 'Especificações para 1.4 e 1.5',
                    'url' => "/guias/{$categorySlug}/honda/fit-2015",
                ],
                [
                    'name' => 'HR-V',
                    'image' => '/images/placeholder/hrv-hero.jpg',
                    'description' => 'SUV compacto',
                    'url' => "/guias/{$categorySlug}/honda/hr-v-2018",
                ],
            ],
        ];
        
        return $mocks[$makeSlug] ?? [];
    }

    /**
     * Retorna lista completa de modelos para tabela
     * 
     * TODO: Buscar do banco quando houver dados
     */
    public function getAllModels(): array
    {
        // TODO: Usar $this->models quando houver dados
        $makeSlug = $this->make->slug ?? 'toyota';
        $categorySlug = $this->category->slug ?? 'oleo';
        
        $mocks = [
            'toyota' => [
                ['name' => 'Corolla', 'segment' => 'Sedã médio', 'years' => '2000–2025', 'url' => "/guias/{$categorySlug}/toyota/corolla"],
                ['name' => 'Hilux', 'segment' => 'Picape', 'years' => '1997–2025', 'url' => "/guias/{$categorySlug}/toyota/hilux"],
                ['name' => 'SW4', 'segment' => 'SUV grande', 'years' => '2005–2025', 'url' => "/guias/{$categorySlug}/toyota/sw4"],
                ['name' => 'Yaris', 'segment' => 'Hatch / Sedã', 'years' => '2018–2025', 'url' => "/guias/{$categorySlug}/toyota/yaris"],
                ['name' => 'Etios', 'segment' => 'Hatch / Sedã', 'years' => '2012–2021', 'url' => "/guias/{$categorySlug}/toyota/etios"],
            ],
            'honda' => [
                ['name' => 'Civic', 'segment' => 'Sedã médio', 'years' => '2000–2025', 'url' => "/guias/{$categorySlug}/honda/civic"],
                ['name' => 'Fit', 'segment' => 'Hatch compacto', 'years' => '2003–2020', 'url' => "/guias/{$categorySlug}/honda/fit"],
                ['name' => 'HR-V', 'segment' => 'SUV compacto', 'years' => '2015–2025', 'url' => "/guias/{$categorySlug}/honda/hr-v"],
                ['name' => 'City', 'segment' => 'Sedã compacto', 'years' => '2009–2025', 'url' => "/guias/{$categorySlug}/honda/city"],
                ['name' => 'CR-V', 'segment' => 'SUV médio', 'years' => '2007–2025', 'url' => "/guias/{$categorySlug}/honda/cr-v"],
            ],
        ];
        
        return $mocks[$makeSlug] ?? [];
    }

    /**
     * Retorna categorias complementares da marca
     */
    public function getComplementaryCategories(): array
    {
        // Todas as 12 categorias principais exceto a atual
        $all = [
            ['name' => 'Calibragem', 'slug' => 'calibragem'],
            ['name' => 'Pneus', 'slug' => 'pneus'],
            ['name' => 'Problemas', 'slug' => 'problemas'],
            ['name' => 'Revisão', 'slug' => 'revisao'],
            ['name' => 'Consumo', 'slug' => 'consumo'],
            ['name' => 'Bateria', 'slug' => 'bateria'],
            ['name' => 'Câmbio', 'slug' => 'cambio'],
            ['name' => 'Arrefecimento', 'slug' => 'arrefecimento'],
            ['name' => 'Torque', 'slug' => 'torque'],
            ['name' => 'Fluidos', 'slug' => 'fluidos'],
            ['name' => 'Motores', 'slug' => 'motores'],
            ['name' => 'Manutenção', 'slug' => 'manutencao'],
        ];
        
        // Remove a categoria atual
        $currentSlug = $this->category->slug ?? 'oleo';
        return array_filter($all, fn($cat) => $cat['slug'] !== $currentSlug);
    }

    /**
     * Retorna dados para SEO
     */
    public function getSeoData(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        
        return [
            'title' => "{$category['name']} {$make['name']} – Guias por modelo e ano | Mercado Veículos",
            'description' => "Guias completos de {$category['name']} para veículos {$make['name']}: especificações, recomendações por modelo e ano. Encontre o guia correto para seu veículo.",
            'canonical' => route('guides.make', [
                'category' => $category['slug'],
                'make' => $make['slug'],
            ]),
            'og_image' => $this->getMakeLogo(),
        ];
    }

    /**
     * Retorna breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $category['name'], 'url' => route('guide.category', ['category' => $category['slug']])],
            ['name' => $make['name'], 'url' => null],
        ];
    }

    /**
     * Retorna estatísticas
     */
    public function getStats(): array
    {
        return [
            'total_models' => count($this->getAllModels()),
            'popular_models' => count($this->getPopularModels()),
        ];
    }

    /**
     * Retorna nome da categoria
     */
    private function getCategoryName(): string
    {
        $names = [
            'oleo' => 'Óleo',
            'calibragem' => 'Calibragem',
            'pneus' => 'Pneus',
            'consumo' => 'Consumo',
            'problemas' => 'Problemas',
            'revisao' => 'Revisão',
        ];
        
        $slug = $this->category->slug ?? 'oleo';
        return $names[$slug] ?? ucfirst($slug);
    }

    /**
     * Retorna nome da marca
     */
    private function getMakeName(): string
    {
        $names = [
            'toyota' => 'Toyota',
            'honda' => 'Honda',
            'volkswagen' => 'Volkswagen',
            'chevrolet' => 'Chevrolet',
            'fiat' => 'Fiat',
            'hyundai' => 'Hyundai',
        ];
        
        $slug = $this->make->slug ?? 'toyota';
        return $names[$slug] ?? ucfirst($slug);
    }

    /**
     * Retorna logo da marca
     */
    private function getMakeLogo(): string
    {
        $makeSlug = $this->make->slug ?? 'toyota';
        return "/images/brands/{$makeSlug}/logo-{$makeSlug}-hero.png";
    }
}
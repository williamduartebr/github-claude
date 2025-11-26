<?php

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;

/**
 * ViewModel para página de categoria + marca + modelo
 * 
 * Rota: /guias/{category}/{make}/{model}
 * View: guide.category-make-model
 * Exemplo: /guias/oleo/toyota/corolla
 * 
 * Lista todos os anos disponíveis para este modelo específico
 */
class GuideCategoryMakeModelViewModel
{
    private $category;
    private $make;
    private $model;
    private Collection $years;

    public function __construct($category, $make, $model, Collection $years)
    {
        $this->category = $category;
        $this->make = $make;
        $this->model = $model;
        $this->years = $years;
    }

    /**
     * Retorna dados da categoria
     */
    public function getCategory(): array
    {
        return [
            'name' => $this->category->name ?? 'Óleo',
            'slug' => $this->category->slug ?? 'oleo',
        ];
    }

    /**
     * Retorna dados da marca
     */
    public function getMake(): array
    {
        return [
            'name' => $this->make->name ?? 'Toyota',
            'slug' => $this->make->slug ?? 'toyota',
        ];
    }

    /**
     * Retorna dados do modelo
     */
    public function getModel(): array
    {
        return [
            'name' => $this->model->name ?? 'Corolla',
            'slug' => $this->model->slug ?? 'corolla',
        ];
    }

    /**
     * Retorna lista de anos disponíveis
     * 
     * TODO: Buscar do banco quando houver dados
     */
    public function getAvailableYears(): array
    {
        // TODO: Usar $this->years quando houver dados
        
        // Mock de anos disponíveis
        $modelSlug = $this->model->slug ?? 'corolla';
        
        $mocks = [
            'corolla' => [
                ['year' => 2025, 'engine' => '2.0 Dynamic Force', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2025'],
                ['year' => 2024, 'engine' => '2.0 Dynamic Force', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2024'],
                ['year' => 2023, 'engine' => '2.0 Dynamic Force', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2023'],
                ['year' => 2022, 'engine' => '2.0 Dynamic Force', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2022'],
                ['year' => 2021, 'engine' => '2.0 Dynamic Force', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2021'],
                ['year' => 2020, 'engine' => '2.0 Dynamic Force', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2020'],
                ['year' => 2019, 'engine' => '1.8 / 2.0', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2019'],
                ['year' => 2018, 'engine' => '1.8 / 2.0', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2018'],
                ['year' => 2017, 'engine' => '1.8 / 2.0', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2017'],
                ['year' => 2016, 'engine' => '1.8 / 2.0', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2016'],
                ['year' => 2015, 'engine' => '1.8 / 2.0', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2015'],
                ['year' => 2014, 'engine' => '1.8 / 2.0', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2014'],
                ['year' => 2013, 'engine' => '1.8 / 2.0', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2013'],
                ['year' => 2012, 'engine' => '1.8 / 2.0', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2012'],
                ['year' => 2011, 'engine' => '1.8 / 2.0', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2011'],
                ['year' => 2010, 'engine' => '1.8 / 2.0', 'versions' => 'GLi, XEi, Altis', 'url' => '/guias/oleo/toyota/corolla-2010'],
                ['year' => 2009, 'engine' => '1.8', 'versions' => 'GLi, XEi, SEG', 'url' => '/guias/oleo/toyota/corolla-2009'],
                ['year' => 2008, 'engine' => '1.8', 'versions' => 'GLi, XEi, SEG', 'url' => '/guias/oleo/toyota/corolla-2008'],
                ['year' => 2007, 'engine' => '1.8', 'versions' => 'GLi, XEi, SEG', 'url' => '/guias/oleo/toyota/corolla-2007'],
                ['year' => 2006, 'engine' => '1.8', 'versions' => 'GLi, XEi, SEG', 'url' => '/guias/oleo/toyota/corolla-2006'],
                ['year' => 2005, 'engine' => '1.8', 'versions' => 'GLi, XEi, SEG', 'url' => '/guias/oleo/toyota/corolla-2005'],
                ['year' => 2004, 'engine' => '1.8', 'versions' => 'GLi, XEi, SEG', 'url' => '/guias/oleo/toyota/corolla-2004'],
                ['year' => 2003, 'engine' => '1.8', 'versions' => 'GLi, XEi, SEG', 'url' => '/guias/oleo/toyota/corolla-2003'],
                ['year' => 2002, 'engine' => '1.8', 'versions' => 'GLi, XEi, SEG', 'url' => '/guias/oleo/toyota/corolla-2002'],
                ['year' => 2001, 'engine' => '1.8', 'versions' => 'GLi, XEi, SEG', 'url' => '/guias/oleo/toyota/corolla-2001'],
                ['year' => 2000, 'engine' => '1.8', 'versions' => 'GLi, XEi, SEG', 'url' => '/guias/oleo/toyota/corolla-2000'],
            ],
            'hilux' => [
                ['year' => 2025, 'engine' => '2.8 Turbo Diesel', 'versions' => 'SR, SRV, SRX', 'url' => '/guias/oleo/toyota/hilux-2025'],
                ['year' => 2024, 'engine' => '2.8 Turbo Diesel', 'versions' => 'SR, SRV, SRX', 'url' => '/guias/oleo/toyota/hilux-2024'],
                ['year' => 2023, 'engine' => '2.8 Turbo Diesel', 'versions' => 'SR, SRV, SRX', 'url' => '/guias/oleo/toyota/hilux-2023'],
            ],
        ];
        
        return $mocks[$modelSlug] ?? [];
    }

    /**
     * Retorna estatísticas
     */
    public function getStats(): array
    {
        $years = $this->getAvailableYears();
        
        return [
            'total_years' => count($years),
            'oldest_year' => !empty($years) ? end($years)['year'] : null,
            'newest_year' => !empty($years) ? $years[0]['year'] : null,
        ];
    }

    /**
     * Retorna categorias complementares
     */
    public function getComplementaryCategories(): array
    {
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
        $model = $this->getModel();
        
        return [
            'title' => "{$category['name']} {$make['name']} {$model['name']} – Todos os anos | Mercado Veículos",
            'description' => "Guias completos de {$category['name']} para {$make['name']} {$model['name']}: todos os anos disponíveis. Escolha o ano do seu veículo e veja as especificações completas.",
            'canonical' => "/guias/{$category['slug']}/{$make['slug']}/{$model['slug']}",
            'og_image' => "/images/placeholder/{$model['slug']}-hero.jpg",
        ];
    }

    /**
     * Retorna breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();
        
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $category['name'], 'url' => route('guide.category', ['category' => $category['slug']])],
            ['name' => $make['name'], 'url' => route('guides.make', ['category' => $category['slug'], 'make' => $make['slug']])],
            ['name' => $model['name'], 'url' => null],
        ];
    }
}
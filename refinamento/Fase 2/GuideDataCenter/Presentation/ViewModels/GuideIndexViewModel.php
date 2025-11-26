<?php

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;

/**
 * ViewModel para página index de guias
 * 
 * Rota: /guias
 * View: guide.index
 */
class GuideIndexViewModel
{
    private Collection $categories;
    private Collection $makes;

    public function __construct(Collection $categories, Collection $makes)
    {
        $this->categories = $categories;
        $this->makes = $makes;
    }

    /**
     * Retorna categorias de guias
     * 
     * TODO: Buscar do banco quando tiver dados
     */
    public function getCategories(): array
    {
        // TODO: Usar $this->categories quando houver dados no banco
        // Por enquanto, retorna dados estáticos
        return [
            ['name' => 'Óleo', 'slug' => 'oleo', 'url' => route('guide.category', ['category' => 'oleo'])],
            ['name' => 'Calibragem', 'slug' => 'calibragem', 'url' => route('guide.category', ['category' => 'calibragem'])],
            ['name' => 'Pneus', 'slug' => 'pneus', 'url' => route('guide.category', ['category' => 'pneus'])],
            ['name' => 'Consumo', 'slug' => 'consumo', 'url' => route('guide.category', ['category' => 'consumo'])],
            ['name' => 'Problemas', 'slug' => 'problemas', 'url' => route('guide.category', ['category' => 'problemas'])],
            ['name' => 'Revisão', 'slug' => 'revisao', 'url' => route('guide.category', ['category' => 'revisao'])],
            ['name' => 'Arrefecimento', 'slug' => 'arrefecimento', 'url' => route('guide.category', ['category' => 'arrefecimento'])],
            ['name' => 'Câmbio', 'slug' => 'cambio', 'url' => route('guide.category', ['category' => 'cambio'])],
            ['name' => 'Torque', 'slug' => 'torque', 'url' => route('guide.category', ['category' => 'torque'])],
            ['name' => 'Fluidos', 'slug' => 'fluidos', 'url' => route('guide.category', ['category' => 'fluidos'])],
            ['name' => 'Bateria', 'slug' => 'bateria', 'url' => route('guide.category', ['category' => 'bateria'])],
            ['name' => 'Elétrica', 'slug' => 'eletrica', 'url' => route('guide.category', ['category' => 'eletrica'])],
            ['name' => 'Motores', 'slug' => 'motores', 'url' => route('guide.category', ['category' => 'motores'])],
            ['name' => 'Manutenção', 'slug' => 'manutencao', 'url' => route('guide.category', ['category' => 'manutencao'])],
            ['name' => 'Versões', 'slug' => 'versoes', 'url' => route('guide.category', ['category' => 'versoes'])],
        ];
    }

    /**
     * Retorna marcas suportadas
     * 
     * TODO: Buscar do banco quando tiver dados
     */
    public function getMakes(): array
    {
        // TODO: Usar $this->makes quando houver dados no banco
        // Por enquanto, retorna dados estáticos
        return [
            ['name' => 'Toyota', 'slug' => 'toyota', 'url' => '/guias/toyota'],
            ['name' => 'Honda', 'slug' => 'honda', 'url' => '/guias/honda'],
            ['name' => 'Volkswagen', 'slug' => 'volkswagen', 'url' => '/guias/volkswagen'],
            ['name' => 'Chevrolet', 'slug' => 'chevrolet', 'url' => '/guias/chevrolet'],
            ['name' => 'Hyundai', 'slug' => 'hyundai', 'url' => '/guias/hyundai'],
            ['name' => 'Fiat', 'slug' => 'fiat', 'url' => '/guias/fiat'],
            ['name' => 'Renault', 'slug' => 'renault', 'url' => '/guias/renault'],
            ['name' => 'Nissan', 'slug' => 'nissan', 'url' => '/guias/nissan'],
        ];
    }

    /**
     * Retorna modelos populares (entrada rápida)
     * 
     * TODO: Buscar do banco quando tiver dados
     */
    public function getPopularModels(): array
    {
        // TODO: Implementar busca real com base em popularidade
        return [
            [
                'name' => 'Toyota Corolla',
                'image' => '/images/placeholder/corolla-hero.jpg',
                'description' => 'Guias: óleo, pneus, consumo, revisões',
                'url' => '/guias/oleo/toyota/corolla-2023',
            ],
            [
                'name' => 'Volkswagen Gol',
                'image' => '/images/placeholder/gol-hero.jpg',
                'description' => 'Guias: pneus, calibragem, problemas',
                'url' => '/guias/calibragem/volkswagen/gol-2016',
            ],
            [
                'name' => 'Chevrolet Onix',
                'image' => '/images/placeholder/onix-hero.jpg',
                'description' => 'Guias: óleo, revisões, consumo',
                'url' => '/guias/oleo/chevrolet/onix-2020',
            ],
        ];
    }

    /**
     * Retorna dados para SEO
     */
    public function getSeoData(): array
    {
        return [
            'title' => 'Guias Automotivos – Óleo, Pneus, Calibragem, Manutenção | Mercado Veículos',
            'description' => 'Guias técnicos automotivos completos: óleo, calibragem, pneus, consumo, manutenção, bateria, câmbio, torque, fluidos e muito mais. Escolha uma categoria e filtre por marca e modelo.',
            'canonical' => route('guide.index'),
            'og_image' => 'https://mercadoveiculos.com/images/guias-automotivos.jpg',
        ];
    }

    /**
     * Retorna breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => null],
        ];
    }

    /**
     * Retorna estatísticas
     * 
     * TODO: Calcular estatísticas reais do banco
     */
    public function getStats(): array
    {
        return [
            'total_categories' => 15,
            'total_makes' => 8,
            'total_guides' => 0, // TODO: Contar guias no banco
        ];
    }
}
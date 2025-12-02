<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;

/**
 * ViewModel para listagem de marcas na página inicial de veículos
 * 
 * Rota: /veiculos
 * View: vehicles.index
 */
class VehicleListViewModel
{
    private Collection $makes;

    public function __construct(Collection $makes)
    {
        $this->makes = $makes;
    }

    /**
     * Retorna array de marcas formatado para a view
     */
    public function getMakes(): array
    {
        return $this->makes->map(function ($make) {
            return [
                'id' => $make->id,
                'name' => $make->name,
                'slug' => $make->slug,
                'logo' => $make->logo_url,
                'country_origin' => $this->getCountryName($make->country_origin),
                'country_code' => $make->country_origin,
                'url' => route('vehicles.make', ['make' => $make->slug]),
                'models_count' => $make->models()->count(),
            ];
        })->toArray();
    }

    /**
     * Retorna marcas destacadas (grid de logos)
     */
    public function getFeaturedMakes(): array
    {
        // Pega as 12 primeiras marcas para o grid de logos
        return array_slice($this->getMakes(), 0, 12);
    }

    /**
     * Retorna modelos populares (mock precisa de 3)
     */
    public function getPopularModels(): array
    {
        // TODO: Implementar lógica de modelos populares
        // Por enquanto retorna dados mockados baseados no HTML
        return [
            [
                'name' => 'Toyota Corolla',
                'make_name' => 'Toyota',
                'slug' => 'toyota/corolla',
                'url' => route('vehicles.model', ['make' => 'toyota', 'model' => 'corolla']),
                'year_start' => 2000,
                'year_end' => 2025,
                'image' => '/images/placeholder/corolla-hero.jpg',
            ],
            [
                'name' => 'Chevrolet Onix',
                'make_name' => 'Chevrolet',
                'slug' => 'chevrolet/onix',
                'url' => route('vehicles.model', ['make' => 'chevrolet', 'model' => 'onix']),
                'year_start' => 2013,
                'year_end' => 2025,
                'image' => '/images/placeholder/onix-hero.jpg',
            ],
            [
                'name' => 'Hyundai HB20',
                'make_name' => 'Hyundai',
                'slug' => 'hyundai/hb20',
                'url' => route('vehicles.model', ['make' => 'hyundai', 'model' => 'hb20']),
                'year_start' => 2012,
                'year_end' => 2025,
                'image' => '/images/placeholder/hb20-hero.jpg',
            ],
        ];
    }

    /**
     * Retorna todas as marcas para a tabela
     */
    public function getAllMakesForTable(): array
    {
        return $this->getMakes();
    }

    /**
     * Retorna dados para SEO
     */
    public function getSeoData(): array
    {
        return [
            'title' => 'Veículos – Marcas, Modelos, Fichas Técnicas e Guias | Mercado Veículos',
            'description' => 'Catálogo completo de veículos por marca e modelo: fichas técnicas, anos, versões, motores e acesso rápido aos melhores guias de óleo, pneus, revisões, calibragem, consumo e manutenção.',
            'canonical' => route('vehicles.index'),
            'og_image' => 'https://mercadoveiculos.com/images/brands/generic/car-hero.png',
        ];
    }

    /**
     * Retorna breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Veículos', 'url' => null],
        ];
    }

    /**
     * Converte código do país para nome
     */
    private function getCountryName(?string $code): string
    {
        $countries = [
            'JP' => 'Japão',
            'US' => 'EUA',
            'DE' => 'Alemanha',
            'FR' => 'França',
            'IT' => 'Itália',
            'KR' => 'Coreia do Sul',
            'BR' => 'Brasil',
            'CN' => 'China',
            'IN' => 'Índia',
            'GB' => 'Reino Unido',
            'SE' => 'Suécia',
            'NL' => 'Holanda',
            
        ];

        return $countries[$code] ?? $code ?? 'N/A';
    }

    /**
     * Retorna estatísticas gerais
     */
    public function getStats(): array
    {
        return [
            'total_makes' => $this->makes->count(),
            'total_models' => $this->makes->sum(fn($m) => $m->models()->count()),
        ];
    }
}
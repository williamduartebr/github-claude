<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;

/**
 * ViewModel para página de uma marca específica
 * 
 * Rota: /veiculos/{make}
 * View: vehicles.make
 * Exemplo: /veiculos/toyota
 */
class VehicleMakeViewModel
{
    private $make;
    private Collection $models;

    public function __construct($make, Collection $models)
    {
        $this->make = $make;
        $this->models = $models;
    }

    /**
     * Retorna dados da marca
     */
    public function getMake(): array
    {
        return [
            'id' => $this->make->id,
            'name' => $this->make->name,
            'slug' => $this->make->slug,
            'logo' => $this->make->logo_url,
            'country_origin' => $this->make->country_origin,
            'description' => $this->getDescription(),
        ];
    }

    /**
     * Retorna todos os modelos formatados
     */
    public function getModels(): array
    {
        return $this->models->map(function ($model) {
            return [
                'id' => $model->id,
                'name' => $model->name,
                'slug' => $model->slug,
                'category' => $this->getCategoryLabel($model->category),
                'category_slug' => $model->category,
                'year_start' => $model->year_start,
                'year_end' => $model->year_end ?? date('Y'),
                'url' => route('vehicles.model', [
                    'make' => $this->make->slug,
                    'model' => $model->slug
                ]),
                'image' => $this->getModelImage($model),
            ];
        })->toArray();
    }

    /**
     * Retorna 3 modelos populares
     */
    public function getPopularModels(): array
    {
        // Pega os 3 primeiros modelos (ou implementar lógica de popularidade)
        return array_slice($this->getModels(), 0, 3);
    }

    /**
     * Retorna todos os modelos para a tabela
     */
    public function getAllModelsForTable(): array
    {
        return $this->getModels();
    }

    /**
     * Retorna categorias de guias para a marca
     */
    public function getGuideCategories(): array
    {
        return [
            ['name' => 'Óleo', 'slug' => 'oleo', 'url' => route('guides.make', ['category' => 'oleo', 'make' => $this->make->slug])],
            ['name' => 'Calibragem', 'slug' => 'calibragem', 'url' => route('guides.make', ['category' => 'calibragem', 'make' => $this->make->slug])],
            ['name' => 'Pneus', 'slug' => 'pneus', 'url' => route('guides.make', ['category' => 'pneus', 'make' => $this->make->slug])],
            ['name' => 'Consumo', 'slug' => 'consumo', 'url' => route('guides.make', ['category' => 'consumo', 'make' => $this->make->slug])],
            ['name' => 'Problemas', 'slug' => 'problemas', 'url' => route('guides.make', ['category' => 'problemas', 'make' => $this->make->slug])],
            ['name' => 'Revisão', 'slug' => 'revisao', 'url' => route('guides.make', ['category' => 'revisao', 'make' => $this->make->slug])],
            ['name' => 'Arrefecimento', 'slug' => 'arrefecimento', 'url' => route('guides.make', ['category' => 'arrefecimento', 'make' => $this->make->slug])],
            ['name' => 'Torque', 'slug' => 'torque', 'url' => route('guides.make', ['category' => 'torque', 'make' => $this->make->slug])],
            ['name' => 'Fluidos', 'slug' => 'fluidos', 'url' => route('guides.make', ['category' => 'fluidos', 'make' => $this->make->slug])],
            ['name' => 'Elétrica', 'slug' => 'eletrica', 'url' => route('guides.make', ['category' => 'eletrica', 'make' => $this->make->slug])],
            ['name' => 'Motores', 'slug' => 'motores', 'url' => route('guides.make', ['category' => 'motores', 'make' => $this->make->slug])],
            ['name' => 'Manutenção', 'slug' => 'manutencao', 'url' => route('guides.make', ['category' => 'manutencao', 'make' => $this->make->slug])],
        ];
    }

    /**
     * Retorna dados para SEO
     */
    public function getSeoData(): array
    {
        return [
            'title' => "{$this->make->name} – Modelos, Fichas Técnicas e Guias | Mercado Veículos",
            'description' => "Modelos da {$this->make->name} no Brasil: fichas técnicas, anos, versões e links rápidos para guias de óleo, pneus, revisões, calibragem, consumo e manutenção.",
            'canonical' => route('vehicles.make', ['make' => $this->make->slug]),
            'og_image' => $this->make->logo_url ?? "https://mercadoveiculos.com/images/brands/{$this->make->slug}/logo-{$this->make->slug}-hero.png",
        ];
    }

    /**
     * Retorna breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Veículos', 'url' => route('vehicles.index')],
            ['name' => $this->make->name, 'url' => null],
        ];
    }

    /**
     * Retorna estatísticas
     */
    public function getStats(): array
    {
        return [
            'total_models' => $this->models->count(),
        ];
    }

    /**
     * Retorna descrição da marca
     */
    private function getDescription(): string
    {
        return "Explore todos os modelos da {$this->make->name} no Brasil: fichas técnicas, anos, gerações, versões e guias práticos de manutenção, óleo, pneus, consumo, calibração e muito mais.";
    }

    /**
     * Converte categoria para label legível
     */
    private function getCategoryLabel(string $category): string
    {
        $labels = [
            'sedan' => 'Sedã',
            'sedan_compact' => 'Sedã compacto',
            'sedan_medium' => 'Sedã médio',
            'sedan_large' => 'Sedã grande',
            'hatchback' => 'Hatchback',
            'suv' => 'SUV',
            'suv_compact' => 'SUV compacto',
            'suv_medium' => 'SUV médio',
            'suv_large' => 'SUV grande',
            'pickup' => 'Picape',
            'van' => 'Van',
            'minivan' => 'Minivan',
            'coupe' => 'Cupê',
            'convertible' => 'Conversível',
            'wagon' => 'Perua',
            'sports' => 'Esportivo',
        ];

        return $labels[$category] ?? ucfirst($category);
    }

    /**
     * Retorna imagem do modelo (placeholder por enquanto)
     */
    private function getModelImage($model): string
    {
        // TODO: Implementar lógica de imagem real do modelo
        return "/images/placeholder/{$model->slug}-hero.jpg";
    }
}
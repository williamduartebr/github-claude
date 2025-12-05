<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\VehicleDataCenter\Domain\Services\VehicleGuideIntegrationService;

/**
 * ViewModel para página de uma marca específica
 * 
 * Rota: /veiculos/{make}
 * View: vehicles.make
 * Exemplo: /veiculos/toyota
 * 
 * ✅ REFINADO: Usa VehicleGuideIntegrationService para buscar dados reais do MongoDB
 * ✅ CORRIGIDO: Usa nome correto da rota (guide.category.make)
 */
class VehicleMakeViewModel
{
    private $make;
    private Collection $models;
    private VehicleGuideIntegrationService $guideIntegration;
    private ?Collection $guideCategories = null;

    public function __construct($make, Collection $models)
    {
        $this->make = $make;
        $this->models = $models;

        // Injetar service de integração
        $this->guideIntegration = app(VehicleGuideIntegrationService::class);

        // Buscar categorias reais do MongoDB (lazy load)
        $this->guideCategories = null;
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
                    'model' => $model->slug,
                ]),
                'image' => $this->getModelImage($model),
            ];
        })->toArray();
    }

    /**
     * Retorna modelos populares (primeiros 6)
     */
    public function getPopularModels(): array
    {
        return array_slice($this->getModels(), 0, 6);
    }

    /**
     * Retorna todos os modelos para tabela/grid
     */
    public function getAllModelsForTable(): array
    {
        return $this->getModels();
    }

    /**
     * ✅ REFINADO: Retorna categorias de guias REAIS do MongoDB
     * ✅ CORRIGIDO: Usa rota correta (guide.category.make)
     * Remove dados mockados e busca do banco
     */
    public function getGuideCategories(): array
    {
        // Lazy load - buscar apenas quando necessário
        if ($this->guideCategories === null) {
            $this->guideCategories = $this->guideIntegration->getGuideCategoriesByMake($this->make->slug);
        }

        // Se não houver guias para esta marca, retornar array vazio
        if ($this->guideCategories->isEmpty()) {
            return [];
        }

        // Mapear para formato esperado pela view
        return $this->guideCategories->map(function ($category) {
            return [
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon ?? '📄',
                'description' => $category->description ?? '',
                // ✅ CORRIGIDO: Rota correta é 'guide.category.make' (veja GuideDataCenter/Presentation/Routes/web.php)
                'url' => route('guide.category.make', [
                    'category' => $category->slug,
                    'make' => $this->make->slug
                ])
            ];
        })->toArray();
    }

    /**
     * Verifica se marca tem guias disponíveis
     */
    public function hasGuides(): bool
    {
        return $this->guideIntegration->hasGuides($this->make->slug);
    }

    /**
     * Retorna dados para SEO
     */
    public function getSeoData(): array
    {
        return [
            'title' => "{$this->make->name} — Catálogo Completo, Modelos e Fichas Técnicas | Mercado Veículos",
            'description' => "Explore todos os modelos da {$this->make->name} no Brasil: fichas técnicas, anos, gerações, versões e guias práticos de manutenção, óleo, pneus, consumo, calibração e muito mais.",
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
            'has_guides' => $this->hasGuides(),
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

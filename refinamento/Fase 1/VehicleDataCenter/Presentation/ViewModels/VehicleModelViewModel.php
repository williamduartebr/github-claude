<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\VehicleDataCenter\Domain\Services\VehicleGuideIntegrationService;

/**
 * ViewModel para página de um modelo específico
 * 
 * Rota: /veiculos/{make}/{model}
 * View: vehicles.model
 * Exemplo: /veiculos/toyota/corolla
 * 
 * ✅ REFINADO: Remove TODOS os mocks e usa dados reais
 */
class VehicleModelViewModel
{
    private $make;
    private $model;
    private Collection $versions;
    private VehicleGuideIntegrationService $guideIntegration;
    private ?Collection $quickGuides = null;
    private ?Collection $allGuideCategories = null;

    public function __construct($make, $model, Collection $versions)
    {
        $this->make = $make;
        $this->model = $model;
        $this->versions = $versions;
        
        // Injetar service de integração
        $this->guideIntegration = app(VehicleGuideIntegrationService::class);
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
        ];
    }

    /**
     * Retorna dados do modelo
     */
    public function getModel(): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'slug' => $this->model->slug,
            'year_start' => $this->model->year_start,
            'year_end' => $this->model->year_end ?? date('Y'),
            'category' => $this->model->category,
            'description' => $this->getDescription(),
            'image' => $this->getModelImage(),
        ];
    }

    /**
     * ✅ REFINADO: Retorna guias rápidos REAIS do MongoDB
     * Remove dados mockados
     */
    public function getQuickGuides(): array
    {
        // Lazy load
        if ($this->quickGuides === null) {
            $this->quickGuides = $this->guideIntegration->getQuickGuidesByModel(
                $this->make->slug,
                $this->model->slug,
                6
            );
        }

        // Se não houver guias, retornar array vazio
        if ($this->quickGuides->isEmpty()) {
            return [];
        }

        // Mapear para formato esperado pela view
        return $this->quickGuides->map(function($guide) {
            return [
                'title' => $guide->payload['title'] ?? $guide->full_title,
                'slug' => $guide->slug,
                'description' => $guide->payload['meta_description'] ?? '',
                'category' => $guide->category->name ?? '',
                'url' => $guide->url ?? route('guide.show', ['slug' => $guide->slug]),
            ];
        })->toArray();
    }

    /**
     * ✅ REFINADO: Retorna TODAS as categorias de guias do MongoDB
     * Remove array hardcoded de 15 categorias
     */
    public function getAllGuideCategories(): array
    {
        // Lazy load
        if ($this->allGuideCategories === null) {
            $this->allGuideCategories = $this->guideIntegration->getAllGuideCategories();
        }

        // Mapear para formato esperado pela view
        return $this->allGuideCategories->map(function($category) {
            return [
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon ?? '📄',
                'description' => $category->description ?? '',
                // ✅ CORRIGIDO: Rota correta é 'guide.category-make-model' (com hifens)
                'url' => route('guide.category-make-model', [
                    'category' => $category->slug,
                    'make' => $this->make->slug,
                    'model' => $this->model->slug
                ])
            ];
        })->toArray();
    }

    /**
     * ✅ REFINADO: Retorna versões agrupadas por ano usando $this->versions
     * Remove dados mockados e usa dados reais do banco
     */
    public function getVersionsByYear(): array
    {
        if ($this->versions->isEmpty()) {
            return [];
        }

        // Agrupar versões por ano (decrescente)
        $grouped = $this->versions
            ->sortByDesc('year')
            ->groupBy('year');

        return $grouped->map(function($versions, $year) {
            return [
                'year' => $year,
                'anchor' => "y{$year}",
                'title' => "{$this->model->name} {$year} — Versões",
                'versions' => $versions->map(function($version) use ($year) {
                    return [
                        'id' => $version->id,
                        'name' => $version->name,
                        'slug' => $version->slug,
                        'engine' => $version->engine_code ?? null,
                        'fuel' => $version->fuel_type ?? null,
                        'transmission' => $version->transmission ?? null,
                        'url' => route('vehicles.version', [
                            'make' => $this->make->slug,
                            'model' => $this->model->slug,
                            'year' => $year,
                            'version' => $version->slug
                        ]),
                    ];
                })->toArray()
            ];
        })->values()->toArray();
    }

    /**
     * ✅ REFINADO: Retorna lista de anos REAL baseada em $this->versions
     * Remove anos mockados e usa anos reais do banco
     */
    /**
     * ✅ REFINADO: Retorna lista dos ÚLTIMOS 2 ANOS com URLs completas
     * Redireciona para /veiculos/{make}/{model}/{year} em vez de anchor na mesma página
     */
    public function getYearsList(): array
    {
        if ($this->versions->isEmpty()) {
            return [];
        }

        // Extrair anos únicos, ordenar decrescente e pegar apenas últimos 2
        $years = $this->versions
            ->pluck('year')
            ->unique()
            ->sort()
            ->reverse()
            ->take(2) // ✅ Apenas últimos 2 anos
            ->values();

        return $years->map(function($year, $index) {
            return [
                'year' => $year,
                'label' => (string) $year,
                'url' => route('vehicles.year', [ // ✅ URL completa, não anchor
                    'make' => $this->make->slug,
                    'model' => $this->model->slug,
                    'year' => $year
                ]),
                'is_first' => $index === 0
            ];
        })->toArray();
    }

    /**
     * Retorna dados para SEO
     */
    public function getSeoData(): array
    {
        return [
            'title' => "{$this->make->name} {$this->model->name} — Modelos, Anos e Versões | Mercado Veículos",
            'description' => "Catálogo completo do {$this->make->name} {$this->model->name}: todos os anos, versões, motores, fichas técnicas e guias práticos (óleo, pneus, calibragem, revisões, consumo e mais). Página oficial do modelo no Mercado Veículos.",
            'canonical' => route('vehicles.model', [
                'make' => $this->make->slug,
                'model' => $this->model->slug
            ]),
            'og_image' => "/images/placeholder/{$this->model->slug}-full-hero.jpg",
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
            ['name' => $this->make->name, 'url' => route('vehicles.make', ['make' => $this->make->slug])],
            ['name' => $this->model->name, 'url' => null],
        ];
    }

    /**
     * Retorna estatísticas
     */
    public function getStats(): array
    {
        $yearStart = $this->versions->min('year') ?? $this->model->year_start;
        $yearEnd = $this->versions->max('year') ?? ($this->model->year_end ?? date('Y'));
        
        return [
            'total_versions' => $this->versions->count(),
            'year_start' => $yearStart,
            'year_end' => $yearEnd,
            'years_range' => $yearEnd - $yearStart + 1,
            'has_guides' => $this->guideIntegration->hasGuides($this->make->slug, $this->model->slug),
        ];
    }

    /**
     * Descrição do modelo
     */
    private function getDescription(): string
    {
        return "Explore todos os anos, versões e gerações do {$this->make->name} {$this->model->name}. Aqui você encontra catálogo completo, fichas técnicas detalhadas e acesso aos melhores guias de manutenção, óleo, pneus, consumo, problemas conhecidos e muito mais.";
    }

    /**
     * URL da imagem do modelo
     */
    private function getModelImage(): string
    {
        // TODO: Implementar lógica de imagem real
        return "/images/placeholder/{$this->model->slug}-full-hero.jpg";
    }
}
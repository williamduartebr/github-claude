<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

/**
 * ViewModel para ficha técnica de uma versão específica
 * 
 * Rota: /veiculos/{make}/{model}/{year}/{version}
 * View: vehicles.version
 * Exemplo: /veiculos/toyota/corolla/2003/gli-18
 */
class VehicleVersionViewModelEstatico
{
    private $version;

    public function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * Retorna dados completos da versão
     */
    public function getVersion(): array
    {
        return [
            'id' => $this->version->id,
            'name' => $this->version->name,
            'slug' => $this->version->slug,
            'year' => $this->version->year,
            'full_name' => $this->getFullName(),
            'description' => $this->getDescription(),
            'image' => $this->getImage(),
        ];
    }

    /**
     * Retorna dados da marca
     */
    public function getMake(): array
    {
        return [
            'id' => $this->version->model->make->id,
            'name' => $this->version->model->make->name,
            'slug' => $this->version->model->make->slug,
        ];
    }

    /**
     * Retorna dados do modelo
     */
    public function getModel(): array
    {
        return [
            'id' => $this->version->model->id,
            'name' => $this->version->model->name,
            'slug' => $this->version->model->slug,
        ];
    }

    /**
     * Retorna badges de qualidade
     */
    public function getBadges(): array
    {
        return [
            ['text' => 'Dados Verificados', 'color' => 'green', 'icon' => 'check'],
            ['text' => 'Especificações Oficiais', 'color' => 'blue', 'icon' => 'document'],
            ['text' => 'Atualizado 2025', 'color' => 'purple', 'icon' => 'refresh'],
        ];
    }

    /**
     * Retorna quick facts (4 infos rápidas)
     */
    public function getQuickFacts(): array
    {
        // TODO: Buscar dados reais do banco
        return [
            ['label' => 'Motor', 'value' => $this->version->engine_code ?? '1.8L • 4 cilindros'],
            ['label' => 'Potência', 'value' => $this->version->power ?? '~130–144 cv'],
            ['label' => 'Transmissão', 'value' => $this->version->transmission ?? 'Manual / Automática'],
            ['label' => 'Porta-malas', 'value' => $this->version->trunk_capacity ?? '~470 L'],
        ];
    }

    /**
     * Retorna ficha técnica principal
     */
    public function getMainSpecs(): array
    {
        // TODO: Buscar dados reais do banco
        return [
            ['label' => 'Motor', 'value' => '1.8L 4 cilindros (Flex)'],
            ['label' => 'Potência', 'value' => '~130 cv (Gasolina) • ~144 cv (Etanol)'],
            ['label' => 'Torque', 'value' => '~17,3 kgf·m'],
            ['label' => 'Transmissão', 'value' => 'Manual 5 marchas / Automática'],
            ['label' => 'Combustível', 'value' => 'Flex'],
            ['label' => 'Peso', 'value' => '~1200 kg'],
            ['label' => 'Porta-malas', 'value' => '~470 L'],
        ];
    }

    /**
     * Retorna cards laterais (óleo, pneus, tanque)
     */
    public function getSideCards(): array
    {
        // TODO: Buscar dados reais do banco
        return [
            [
                'title' => 'Óleo recomendado',
                'value' => '5W-30 (API SL/SM+)',
                'extra' => 'Volume: 4,2 L',
            ],
            [
                'title' => 'Pneus originais',
                'value' => '195/65 R15',
                'extra' => 'Equivalente: 205/60 R15',
            ],
            [
                'title' => 'Tanque',
                'value' => '~55 L',
                'extra' => null,
            ],
        ];
    }

    /**
     * Retorna fluidos e capacidades
     */
    public function getFluids(): array
    {
        // TODO: Buscar dados reais do banco
        return [
            ['emoji' => '💧', 'label' => 'Óleo do motor', 'value' => '5W-30 – 4,2 L'],
            ['emoji' => '🛑', 'label' => 'Fluído de freio', 'value' => 'DOT 4 – 0,6 L'],
            ['emoji' => '❄️', 'label' => 'Arrefecimento', 'value' => '6,5 L – G12 / Long Life'],
            ['emoji' => '⚙️', 'label' => 'Câmbio manual', 'value' => 'GL-4 – ~2,4 L'],
            ['emoji' => '🔄', 'label' => 'Câmbio automático', 'value' => 'ATF T-IV / WS'],
            ['emoji' => '🔋', 'label' => 'Bateria', 'value' => '60 Ah'],
        ];
    }

    /**
     * Retorna resumo de manutenção
     */
    public function getMaintenanceSummary(): array
    {
        // TODO: Buscar dados reais do banco
        return [
            ['km' => '10.000', 'items' => 'Óleo, filtro, inspeções.'],
            ['km' => '20.000', 'items' => 'Óleo, filtros, correias.'],
            ['km' => '40.000', 'items' => 'Óleo, filtros, velas, pneus.'],
        ];
    }

    /**
     * Retorna guias técnicos relacionados
     */
    public function getGuides(): array
    {
        $make = $this->version->model->make->slug;
        $model = $this->version->model->slug;
        $year = $this->version->year;

        return [
            ['emoji' => '🛢️', 'name' => 'Óleo Recomendado', 'url' => "/guias/oleo/{$make}/{$model}/{$year}"],
            ['emoji' => '🔧', 'name' => 'Calibragem', 'url' => "/guias/calibragem/{$make}/{$model}/{$year}"],
            ['emoji' => '🚗', 'name' => 'Pneus', 'url' => "/guias/pneus/{$make}/{$model}/{$year}"],
            ['emoji' => '📋', 'name' => 'Revisões', 'url' => "/guias/revisoes/{$make}/{$model}/{$year}"],
            ['emoji' => '⚠️', 'name' => 'Problemas', 'url' => "/guias/problemas/{$make}/{$model}/{$year}"],
            ['emoji' => '⛽', 'name' => 'Consumo', 'url' => "/guias/consumo/{$make}/{$model}/{$year}"],
            ['emoji' => '🔋', 'name' => 'Bateria', 'url' => "/guias/bateria/{$make}/{$model}/{$year}"],
            ['emoji' => '🔄', 'name' => 'Câmbio', 'url' => "/guias/cambio/{$make}/{$model}/{$year}"],
        ];
    }

    /**
     * Retorna dados para SEO
     */
    public function getSeoData(): array
    {
        $fullName = $this->getFullName();
        
        return [
            'title' => "{$fullName} – Ficha Técnica Completa | Mercado Veículos",
            'description' => "Ficha técnica completa do {$fullName}: motor, potência, medidas, capacidades, fluidos, revisões e links para todos os guias técnicos (óleo, pneus, calibragem, manutenção, consumo, bateria e muito mais).",
            'canonical' => route('vehicles.version', [
                'make' => $this->version->model->make->slug,
                'model' => $this->version->model->slug,
                'year' => $this->version->year,
                'version' => $this->version->slug,
            ]),
            'og_image' => $this->getImage(),
        ];
    }

    /**
     * Retorna breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        $make = $this->version->model->make;
        $model = $this->version->model;
        
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Veículos', 'url' => route('vehicles.index')],
            ['name' => $make->name, 'url' => route('vehicles.make', ['make' => $make->slug])],
            ['name' => $model->name, 'url' => route('vehicles.model', ['make' => $make->slug, 'model' => $model->slug])],
            ['name' => "{$this->version->name} {$this->version->year}", 'url' => null],
        ];
    }

    /**
     * Nome completo da versão
     */
    private function getFullName(): string
    {
        return "{$this->version->model->make->name} {$this->version->model->name} {$this->version->name} {$this->version->year}";
    }

    /**
     * Descrição da versão
     */
    private function getDescription(): string
    {
        $fullName = $this->getFullName();
        return "Ficha técnica completa do {$fullName}, incluindo motor, potência, dimensões, capacidades, fluidos e manutenção. Acesse também os guias completos de óleo, pneus, calibragem, consumo e muito mais.";
    }

    /**
     * URL da imagem
     */
    private function getImage(): string
    {
        // TODO: Implementar lógica de imagem real
        $make = $this->version->model->make->slug;
        $model = $this->version->model->slug;
        $year = $this->version->year;
        $version = $this->version->slug;
        
        return "/images/vehicles/{$make}/{$model}/{$year}/{$version}/hero.jpg";
    }
}
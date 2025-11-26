<?php

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;

/**
 * ViewModel para página de guia específico individual
 * 
 * Rota: /guias/{category}/{make}/{model-year}
 * View: guide.specific
 * Exemplo: /guias/oleo/toyota/corolla-2003
 */
class GuideSpecificViewModel
{
    private $guide;
    private $category;
    private $make;
    private $model;
    private $year;

    public function __construct($guide, $category, $make, $model, $year)
    {
        $this->guide = $guide;
        $this->category = $category;
        $this->make = $make;
        $this->model = $model;
        $this->year = $year;
    }

    /**
     * Retorna dados do guia
     */
    public function getGuide(): array
    {
        return [
            'id' => $this->guide->_id ?? null,
            'title' => $this->guide->title ?? $this->generateTitle(),
            'description' => $this->guide->description ?? $this->generateDescription(),
            'content' => $this->guide->content ?? null,
        ];
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
     * Retorna ano
     */
    public function getYear(): int
    {
        return $this->year ?? 2003;
    }

    /**
     * Retorna badges de qualidade
     */
    public function getBadges(): array
    {
        return [
            ['text' => '✓ Informações Verificadas', 'color' => 'green'],
            ['text' => '📋 Baseado em Especificações Oficiais', 'color' => 'blue'],
        ];
    }

    /**
     * Retorna especificações oficiais do óleo
     * 
     * TODO: Buscar do banco quando houver dados
     */
    public function getOfficialSpecs(): array
    {
        // Mock baseado no HTML
        return [
            ['label' => 'Viscosidade (manual)', 'value' => '5W-30 – Sintético ou Semissintético'],
            ['label' => 'Especificação API', 'value' => 'API SL / SM+'],
            ['label' => 'Volume total', 'value' => '4,2 litros (com filtro)'],
        ];
    }

    /**
     * Retorna óleos compatíveis
     * 
     * TODO: Buscar do banco quando houver dados
     */
    public function getCompatibleOils(): array
    {
        // Mock baseado no HTML
        return [
            ['name' => 'Mobil Super 5W-30', 'spec' => 'API SM'],
            ['name' => 'Shell Helix HX8 5W-30', 'spec' => 'API SN'],
            ['name' => 'Ipiranga F1 Master 5W-30', 'spec' => 'API SN'],
            ['name' => 'Petronas Syntium 7000 5W-30', 'spec' => 'API SN'],
            ['name' => 'Motul 8100 Eco-lite 5W-30', 'spec' => 'API SL/SM'],
        ];
    }

    /**
     * Retorna intervalos de troca
     */
    public function getChangeIntervals(): array
    {
        return [
            ['label' => 'Uso normal', 'value' => '10.000 km ou 12 meses (o que ocorrer primeiro)'],
            ['label' => 'Uso severo', 'value' => '5.000–7.000 km (cidade e trânsito intenso)'],
        ];
    }

    /**
     * Retorna nota sobre uso severo
     */
    public function getSevereUseNote(): string
    {
        return 'Uso severo inclui: predominância urbana, trajetos curtos, reboque, poeira, trânsito intenso.';
    }

    /**
     * Retorna guias relacionados (mesma categoria + make + model + year)
     * 
     * TODO: Buscar do banco quando houver dados
     */
    public function getRelatedGuides(): array
    {
        $make = $this->getMake();
        $model = $this->getModel();
        $year = $this->getYear();
        
        // Mock de guias relacionados
        return [
            ['name' => 'Calibragem', 'icon' => '🔧', 'url' => "/guias/calibragem/{$make['slug']}/{$model['slug']}-{$year}"],
            ['name' => 'Pneus', 'icon' => '🚗', 'url' => "/guias/pneus/{$make['slug']}/{$model['slug']}-{$year}"],
            ['name' => 'Revisão', 'icon' => '📋', 'url' => "/guias/revisao/{$make['slug']}/{$model['slug']}-{$year}"],
            ['name' => 'Problemas', 'icon' => '⚠️', 'url' => "/guias/problemas/{$make['slug']}/{$model['slug']}-{$year}"],
            ['name' => 'Consumo', 'icon' => '⛽', 'url' => "/guias/consumo/{$make['slug']}/{$model['slug']}-{$year}"],
            ['name' => 'Bateria', 'icon' => '🔋', 'url' => "/guias/bateria/{$make['slug']}/{$model['slug']}-{$year}"],
            ['name' => 'Câmbio', 'icon' => '⚙️', 'url' => "/guias/cambio/{$make['slug']}/{$model['slug']}-{$year}"],
            ['name' => 'Fluidos', 'icon' => '💧', 'url' => "/guias/fluidos/{$make['slug']}/{$model['slug']}-{$year}"],
        ];
    }

    /**
     * Retorna cluster de conteúdos essenciais
     * 
     * TODO: Buscar do banco quando houver dados
     */
    public function getEssentialCluster(): array
    {
        $make = $this->getMake();
        $model = $this->getModel();
        $year = $this->getYear();
        
        // Mock de cluster
        return [
            ['title' => "Ficha técnica – {$model['name']} {$year} GLi 1.8", 'icon' => '🚗', 'url' => "/veiculos/{$make['slug']}/{$model['slug']}/{$year}/gli-1-8"],
            ['title' => "Ficha Técnica do {$model['name']} {$year}", 'icon' => '📘', 'url' => "/veiculos/{$make['slug']}/{$model['slug']}/{$year}"],
            ['title' => 'Consumo Real — Motor 1.8', 'icon' => '⛽', 'url' => "/guias/consumo/{$make['slug']}/{$model['slug']}-{$year}"],
            ['title' => 'Problemas comuns (Geração 2002–2008)', 'icon' => '⚠️', 'url' => "/guias/problemas/{$make['slug']}/{$model['slug']}-2002-2008"],
            ['title' => 'Fluidos e capacidades', 'icon' => '💧', 'url' => "/guias/fluidos/{$make['slug']}/{$model['slug']}-{$year}"],
            ['title' => 'Motor alternativo — 1.6', 'icon' => '🔧', 'url' => "/guias/oleo/{$make['slug']}/{$model['slug']}-{$year}-1-6"],
            ['title' => "Óleo do {$model['name']} " . ($year - 1), 'icon' => '🔄', 'url' => "/guias/oleo/{$make['slug']}/{$model['slug']}-" . ($year - 1)],
            ['title' => "Óleo do {$model['name']} " . ($year + 1), 'icon' => '🔄', 'url' => "/guias/oleo/{$make['slug']}/{$model['slug']}-" . ($year + 1)],
        ];
    }

    /**
     * Retorna disclaimer importante
     */
    public function getDisclaimer(): string
    {
        return '⚠️ Importante: As informações são para fins informativos. Consulte sempre o manual do seu veículo e um profissional qualificado antes de realizar manutenções.';
    }

    /**
     * Retorna dados da equipe editorial
     */
    public function getEditorialInfo(): array
    {
        return [
            'title' => 'Equipe Editorial Mercado Veículos',
            'description' => 'Guia técnico desenvolvido com base em especificações oficiais da Toyota e manuais de serviço.',
            'methodology' => 'Nosso processo editorial rigoroso garante informações precisas e atualizadas, com revisão por especialistas automotivos.',
            'link_text' => 'Conheça nossa metodologia',
            'link_url' => 'https://mercadoveiculos.com/sobre/metodologia-editorial',
        ];
    }

    /**
     * Retorna dados para SEO
     */
    public function getSeoData(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();
        $year = $this->getYear();
        
        return [
            'title' => "{$category['name']} {$make['name']} {$model['name']} {$year} – Qual usar, Quantidade e Especificações | Mercado Veículos",
            'description' => "Guia completo do {$category['name']} do {$make['name']} {$model['name']} {$year}: viscosidade recomendada, volume correto, especificações API/ACEA, melhores marcas, intervalos de troca e tabela de capacidades.",
            'canonical' => "/guias/{$category['slug']}/{$make['slug']}/{$model['slug']}-{$year}/",
            'og_type' => 'article',
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
        $year = $this->getYear();
        
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $category['name'], 'url' => route('guide.category', ['category' => $category['slug']])],
            ['name' => $make['name'], 'url' => route('guides.make', ['category' => $category['slug'], 'make' => $make['slug']])],
            ['name' => "{$model['name']} {$year}", 'url' => null],
        ];
    }

    /**
     * Gera título automaticamente
     */
    private function generateTitle(): string
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();
        $year = $this->getYear();
        
        return "{$category['name']} Recomendado – {$make['name']} {$model['name']} {$year} (GLi 1.8)";
    }

    /**
     * Gera descrição automaticamente
     */
    private function generateDescription(): string
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();
        $year = $this->getYear();
        
        return "Guia técnico completo do {$category['name']} do {$make['name']} {$model['name']} {$year} GLi 1.8. Aqui você encontra a viscosidade oficial, volume correto, especificação API, equivalentes compatíveis e condições severas. Este conteúdo faz parte do cluster completo do {$model['name']} {$year}.";
    }
}
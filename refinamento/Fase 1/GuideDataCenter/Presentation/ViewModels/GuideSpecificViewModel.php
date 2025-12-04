<?php

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;

/**
 * ViewModel para página de guia específico individual
 * 
 * Rota: /guias/{category}/{make}/{model}/{year}/{version}
 * View: guide.specific
 * Exemplo: /guias/oleo/toyota/corolla/2025/gli
 */
class GuideSpecificViewModel
{
    private $guide;
    private $category;
    private $make;
    private $model;
    private int $year;
    private string $version;

    public function __construct($guide, $category, $make, $model, int $year, ?string $version = null)
    {
        $this->guide = $guide;
        $this->category = $category;
        $this->make = $make;
        $this->model = $model;
        $this->year = $year;
        $this->version = $version ?? 'gli'; // Fallback para 'gli'
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
        return $this->year;
    }

    /**
     * Retorna versão
     */
    public function getVersion(): string
    {
        return $this->version;
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
     * Retorna guias relacionados (mesma versão, outras categorias)
     * 
     * TODO: Buscar do banco quando houver dados
     */
    public function getRelatedGuides(): array
    {
        $make = $this->getMake();
        $model = $this->getModel();
        $year = $this->getYear();
        $version = $this->getVersion();

        return [
            ['name' => 'Calibragem', 'icon' => '🔧', 'url' => "/guias/calibragem/{$make['slug']}/{$model['slug']}/{$year}/{$version}"],
            ['name' => 'Pneus', 'icon' => '🚗', 'url' => "/guias/pneus/{$make['slug']}/{$model['slug']}/{$year}/{$version}"],
            ['name' => 'Revisão', 'icon' => '📋', 'url' => "/guias/revisao/{$make['slug']}/{$model['slug']}/{$year}/{$version}"],
            ['name' => 'Problemas', 'icon' => '⚠️', 'url' => "/guias/problemas/{$make['slug']}/{$model['slug']}/{$year}/{$version}"],
            ['name' => 'Consumo', 'icon' => '⛽', 'url' => "/guias/consumo/{$make['slug']}/{$model['slug']}/{$year}/{$version}"],
            ['name' => 'Bateria', 'icon' => '🔋', 'url' => "/guias/bateria/{$make['slug']}/{$model['slug']}/{$year}/{$version}"],
            ['name' => 'Câmbio', 'icon' => '⚙️', 'url' => "/guias/cambio/{$make['slug']}/{$model['slug']}/{$year}/{$version}"],
            ['name' => 'Fluidos', 'icon' => '💧', 'url' => "/guias/fluidos/{$make['slug']}/{$model['slug']}/{$year}/{$version}"],
        ];
    }

    /**
     * Retorna cluster de conteúdos essenciais
     * Links para ficha técnica + outras categorias + anos próximos
     * 
     * TODO: Buscar do banco quando houver dados
     */
    public function getEssentialCluster(): array
    {
        $make = $this->getMake();
        $model = $this->getModel();
        $year = $this->getYear();
        $version = $this->getVersion();

        return [
            // BLOCO 1: Ficha Técnica (Cross-link para vertente VEÍCULOS)
            [
                'title' => "Ficha técnica – {$model['name']} {$year} " . strtoupper($version),
                'icon' => '🚗',
                'url' => "/veiculos/{$make['slug']}/{$model['slug']}/{$year}/{$version}"
            ],
            [
                'title' => "Ficha Técnica do {$model['name']} {$year}",
                'icon' => '📘',
                'url' => "/veiculos/{$make['slug']}/{$model['slug']}/{$year}"
            ],

            // BLOCO 2: Outras Categorias (mesma versão/ano)
            [
                'title' => 'Consumo Real',
                'icon' => '⛽',
                'url' => "/guias/consumo/{$make['slug']}/{$model['slug']}/{$year}/{$version}"
            ],
            [
                'title' => 'Fluidos e capacidades',
                'icon' => '💧',
                'url' => "/guias/fluidos/{$make['slug']}/{$model['slug']}/{$year}/{$version}"
            ],
            [
                'title' => 'Calibragem de Pneus',
                'icon' => '🔧',
                'url' => "/guias/calibragem/{$make['slug']}/{$model['slug']}/{$year}/{$version}"
            ],
            [
                'title' => 'Pneus Recomendados',
                'icon' => '🛞',
                'url' => "/guias/pneus/{$make['slug']}/{$model['slug']}/{$year}/{$version}"
            ],
            [
                'title' => 'Bateria',
                'icon' => '🔋',
                'url' => "/guias/bateria/{$make['slug']}/{$model['slug']}/{$year}/{$version}"
            ],
            [
                'title' => 'Problemas comuns',
                'icon' => '⚠️',
                'url' => "/guias/problemas/{$make['slug']}/{$model['slug']}/{$year}/{$version}"
            ],

            // BLOCO 3: Anos Próximos (mesma categoria)
            [
                'title' => "Óleo do {$model['name']} " . ($year - 1),
                'icon' => '🔄',
                'url' => "/guias/oleo/{$make['slug']}/{$model['slug']}/" . ($year - 1) . "/{$version}"
            ],
            [
                'title' => "Óleo do {$model['name']} " . ($year + 1),
                'icon' => '🔄',
                'url' => "/guias/oleo/{$make['slug']}/{$model['slug']}/" . ($year + 1) . "/{$version}"
            ],
        ];
    }

    /**
     * Retorna disclaimer importante
     */
    public function getDisclaimer(): string
    {
        return 'Importante: As informações são para fins informativos. Consulte sempre o manual do seu veículo e um profissional qualificado antes de realizar manutenções.';
    }

    /**
     * Retorna dados da equipe editorial
     */
    public function getEditorialInfo(): array
    {
        $make = $this->getMake();

        return [
            'title' => 'Equipe Editorial Mercado Veículos',
            'description' => "Guia técnico desenvolvido com base em especificações oficiais da {$make['name']} e manuais de serviço.",
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
        $version = strtoupper($this->getVersion());

        return [
            'title' => "{$category['name']} {$make['name']} {$model['name']} {$year} {$version} – Qual usar, Quantidade e Especificações | Mercado Veículos",
            'description' => "Guia completo do {$category['name']} do {$make['name']} {$model['name']} {$year} {$version}: viscosidade recomendada, volume correto, especificações API/ACEA, melhores marcas, intervalos de troca e tabela de capacidades.",
            'canonical' => "/guias/{$category['slug']}/{$make['slug']}/{$model['slug']}/{$year}/{$this->version}",
            'og_type' => 'article',
            'og_image' => "/images/og/{$model['slug']}-{$year}.jpg",
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
        $version = strtoupper($this->getVersion());

        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $category['name'], 'url' => route('guide.category', ['category' => $category['slug']])],
            ['name' => $make['name'], 'url' => route('guides.make', ['category' => $category['slug'], 'make' => $make['slug']])],
            ['name' => "{$model['name']} {$year}", 'url' => route('guide.category.make.model', ['category' => $category['slug'], 'make' => $make['slug'], 'model' => $model['slug']])],
            ['name' => "{$model['name']} {$year} {$version}", 'url' => null],
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
        $version = strtoupper($this->getVersion());

        return "{$category['name']} Recomendado – {$make['name']} {$model['name']} {$year} {$version}";
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
        $version = strtoupper($this->getVersion());

        return "Guia técnico completo do {$category['name']} do {$make['name']} {$model['name']} {$year} {$version}. Aqui você encontra a viscosidade oficial, volume correto, especificação API, equivalentes compatíveis e condições severas. Este conteúdo faz parte do cluster completo do {$model['name']} {$year}.";
    }
}

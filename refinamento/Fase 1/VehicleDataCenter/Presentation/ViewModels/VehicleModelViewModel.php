<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;

/**
 * ViewModel para página de um modelo específico
 * 
 * Rota: /veiculos/{make}/{model}
 * View: vehicles.model
 * Exemplo: /veiculos/toyota/corolla
 */
class VehicleModelViewModel
{
    private $make;
    private $model;
    private Collection $versions;

    public function __construct($make, $model, Collection $versions)
    {
        $this->make = $make;
        $this->model = $model;
        $this->versions = $versions;
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
     * Retorna guias rápidos (6 principais)
     */
    public function getQuickGuides(): array
    {
        return [
            [
                'name' => 'Óleo',
                'slug' => 'oleo',
                'description' => 'Viscosidades e volumes',
                'url' => $this->getGuideUrl('oleo'),
            ],
            [
                'name' => 'Calibragem',
                'slug' => 'calibragem',
                'description' => 'Pressões por ano',
                'url' => $this->getGuideUrl('calibragem'),
            ],
            [
                'name' => 'Pneus',
                'slug' => 'pneus',
                'description' => 'Medidas originais',
                'url' => $this->getGuideUrl('pneus'),
            ],
            [
                'name' => 'Revisões',
                'slug' => 'revisao',
                'description' => 'Planos completos',
                'url' => $this->getGuideUrl('revisao'),
            ],
            [
                'name' => 'Consumo',
                'slug' => 'consumo',
                'description' => 'Dados reais',
                'url' => $this->getGuideUrl('consumo'),
            ],
            [
                'name' => 'Problemas',
                'slug' => 'problemas',
                'description' => 'Falhas mais comuns',
                'url' => $this->getGuideUrl('problemas'),
            ],
        ];
    }

    /**
     * Retorna todas as categorias de guias (15 categorias)
     */
    public function getAllGuideCategories(): array
    {
        return [
            ['name' => 'Óleo', 'slug' => 'oleo', 'url' => $this->getGuideUrl('oleo')],
            ['name' => 'Calibragem', 'slug' => 'calibragem', 'url' => $this->getGuideUrl('calibragem')],
            ['name' => 'Pneus', 'slug' => 'pneus', 'url' => $this->getGuideUrl('pneus')],
            ['name' => 'Revisão', 'slug' => 'revisao', 'url' => $this->getGuideUrl('revisao')],
            ['name' => 'Consumo', 'slug' => 'consumo', 'url' => $this->getGuideUrl('consumo')],
            ['name' => 'Problemas', 'slug' => 'problemas', 'url' => $this->getGuideUrl('problemas')],
            ['name' => 'Bateria', 'slug' => 'bateria', 'url' => $this->getGuideUrl('bateria')],
            ['name' => 'Câmbio', 'slug' => 'cambio', 'url' => $this->getGuideUrl('cambio')],
            ['name' => 'Arrefecimento', 'slug' => 'arrefecimento', 'url' => $this->getGuideUrl('arrefecimento')],
            ['name' => 'Torque', 'slug' => 'torque', 'url' => $this->getGuideUrl('torque')],
            ['name' => 'Fluidos', 'slug' => 'fluidos', 'url' => $this->getGuideUrl('fluidos')],
            ['name' => 'Elétrica', 'slug' => 'eletrica', 'url' => $this->getGuideUrl('eletrica')],
            ['name' => 'Versões', 'slug' => 'versoes', 'url' => $this->getGuideUrl('versoes')],
            ['name' => 'Motores', 'slug' => 'motores', 'url' => $this->getGuideUrl('motores')],
            ['name' => 'Manutenção', 'slug' => 'manutencao', 'url' => $this->getGuideUrl('manutencao')],
        ];
    }

    /**
     * Retorna versões agrupadas por ano
     * 
     * TODO: Implementar lógica de agrupamento real baseada no banco de dados
     * Por enquanto retorna estrutura mockada para exemplo
     */
    public function getVersionsByYear(): array
    {
        // TODO: Implementar agrupamento real
        // Agrupar $this->versions por year
        // Ordenar por ano decrescente
        // Retornar array formatado
        
        // MOCK para exemplo (baseado no HTML):
        return [
            [
                'year' => 2025,
                'anchor' => 'y2025',
                'title' => 'Corolla 2025 — Versões',
                'versions' => [
                    [
                        'name' => 'GLi 2.0',
                        'engine' => '2.0 Flex',
                        'transmission' => 'CVT',
                        'url' => route('vehicles.version', [
                            'make' => $this->make->slug,
                            'model' => $this->model->slug,
                            'year' => 2025,
                            'version' => 'gli-2-0'
                        ]),
                    ],
                    [
                        'name' => 'XEi 2.0',
                        'engine' => '2.0 Flex',
                        'transmission' => 'CVT',
                        'url' => route('vehicles.version', [
                            'make' => $this->make->slug,
                            'model' => $this->model->slug,
                            'year' => 2025,
                            'version' => 'xei-2-0'
                        ]),
                    ],
                    [
                        'name' => 'Altis Hybrid',
                        'engine' => 'Híbrido 1.8',
                        'transmission' => 'CVT',
                        'url' => route('vehicles.version', [
                            'make' => $this->make->slug,
                            'model' => $this->model->slug,
                            'year' => 2025,
                            'version' => 'altis-hybrid-1-8'
                        ]),
                    ],
                ],
            ],
            // TODO: Adicionar mais anos dinamicamente do banco
        ];
    }

    /**
     * Retorna lista de anos para seleção rápida
     * 
     * TODO: Implementar lógica dinâmica baseada em versões reais
     */
    public function getYearsList(): array
    {
        // TODO: Extrair anos únicos de $this->versions
        // Ordenar decrescente
        // Adicionar gerações (ranges de anos)
        
        // MOCK para exemplo:
        return [
            ['year' => 2025, 'anchor' => '#y2025', 'label' => '2025', 'type' => 'year'],
            ['year' => 2024, 'anchor' => '#y2024', 'label' => '2024', 'type' => 'year'],
            ['year' => 2023, 'anchor' => '#y2023', 'label' => '2023', 'type' => 'year'],
            ['year' => 2022, 'anchor' => '#y2022', 'label' => '2022', 'type' => 'year'],
            ['year' => 2021, 'anchor' => '#y2021', 'label' => '2021', 'type' => 'year'],
            ['year' => 2020, 'anchor' => '#y2020', 'label' => '2020', 'type' => 'year'],
            ['year' => 2019, 'anchor' => '#y2019', 'label' => '2019', 'type' => 'year'],
            ['range' => '2014-2018', 'anchor' => '#g2014-2018', 'label' => 'Geração 2014–2018', 'type' => 'generation'],
            ['range' => '2009-2013', 'anchor' => '#g2009-2013', 'label' => 'Geração 2009–2013', 'type' => 'generation'],
            ['range' => '2003-2008', 'anchor' => '#g2003-2008', 'label' => 'Geração 2003–2008', 'type' => 'generation'],
        ];
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
        return [
            'total_versions' => $this->versions->count(),
            'year_start' => $this->model->year_start,
            'year_end' => $this->model->year_end ?? date('Y'),
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

    /**
     * Gera URL de guia para categoria
     */
    private function getGuideUrl(string $categorySlug): string
    {
        // TODO: Quando implementar rota guide.model, usar route()
        // Por enquanto URL direta
        return "/guias/{$categorySlug}/{$this->make->slug}/{$this->model->slug}";
    }
}
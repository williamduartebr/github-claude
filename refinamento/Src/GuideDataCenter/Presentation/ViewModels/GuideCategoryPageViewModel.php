<?php

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;

/**
 * ViewModel para página de categoria de guias
 * 
 * Rota: /guias/{category}
 * View: guide.category
 * Exemplo: /guias/oleo
 */
class GuideCategoryPageViewModel
{
    private $category;
    private Collection $guides;
    private Collection $makes;

    public function __construct($category, Collection $guides, Collection $makes)
    {
        $this->category = $category;
        $this->guides = $guides;
        $this->makes = $makes;
    }

    /**
     * Retorna dados da categoria
     */
    public function getCategory(): array
    {
        return [
            'id' => $this->category->_id ?? null,
            'name' => $this->category->name ?? $this->getCategoryNameBySlug(),
            'slug' => $this->category->slug ?? $this->extractSlug(),
            'description' => $this->category->description ?? $this->getDefaultDescription(),
            'icon' => $this->category->icon ?? '📋',
        ];
    }

    /**
     * Retorna chips de categorias relacionadas
     */
    public function getRelatedCategories(): array
    {
        $slug = $this->category->slug ?? $this->extractSlug();
        
        // TODO: Buscar categorias relacionadas do banco
        $related = [
            'oleo' => [
                ['name' => 'Fluidos', 'slug' => 'fluidos'],
                ['name' => 'Motores', 'slug' => 'motores'],
            ],
            'calibragem' => [
                ['name' => 'Pneus', 'slug' => 'pneus'],
                ['name' => 'Manutenção', 'slug' => 'manutencao'],
            ],
            'pneus' => [
                ['name' => 'Calibragem', 'slug' => 'calibragem'],
                ['name' => 'Manutenção', 'slug' => 'manutencao'],
            ],
        ];
        
        return $related[$slug] ?? [];
    }

    /**
     * Retorna imagem hero da categoria
     */
    public function getHeroImage(): string
    {
        $slug = $this->category->slug ?? $this->extractSlug();
        
        $images = [
            'oleo' => '/images/placeholder/oil-hero.jpg',
            'calibragem' => '/images/placeholder/tire-hero.jpg',
            'pneus' => '/images/placeholder/tire-hero.jpg',
            'consumo' => '/images/placeholder/fuel-hero.jpg',
            'bateria' => '/images/placeholder/battery-hero.jpg',
        ];
        
        return $images[$slug] ?? '/images/placeholder/guide-hero.jpg';
    }

    /**
     * Retorna guias populares
     * 
     * TODO: Implementar busca real com ordenação por popularidade
     */
    public function getPopularGuides(): array
    {
        // TODO: Usar $this->guides quando houver dados
        $slug = $this->category->slug ?? $this->extractSlug();
        
        // Mock de guias populares
        $mocks = [
            'oleo' => [
                ['title' => 'Toyota Corolla 2003', 'specs' => '5W-30 • 4,2 L', 'url' => '/guias/oleo/toyota/corolla-2003'],
                ['title' => 'Honda Civic 2010', 'specs' => '10W-30 • 4,6 L', 'url' => '/guias/oleo/honda/civic-2010'],
                ['title' => 'Chevrolet Onix 2020', 'specs' => '5W-30 • 3,5 L', 'url' => '/guias/oleo/chevrolet/onix-2020'],
            ],
            'calibragem' => [
                ['title' => 'Toyota Corolla 2003', 'specs' => '32 PSI • Dianteira', 'url' => '/guias/calibragem/toyota/corolla-2003'],
                ['title' => 'Volkswagen Gol 2016', 'specs' => '30 PSI • Traseira', 'url' => '/guias/calibragem/volkswagen/gol-2016'],
                ['title' => 'Fiat Uno 2012', 'specs' => '28 PSI • Dianteira', 'url' => '/guias/calibragem/fiat/uno-2012'],
            ],
        ];
        
        return $mocks[$slug] ?? [];
    }

    /**
     * Retorna marcas para esta categoria
     * 
     * TODO: Buscar marcas que têm guias nesta categoria
     */
    public function getMakes(): array
    {
        // TODO: Usar $this->makes quando houver dados
        return [
            ['name' => 'Toyota', 'slug' => 'toyota'],
            ['name' => 'Honda', 'slug' => 'honda'],
            ['name' => 'Volkswagen', 'slug' => 'volkswagen'],
            ['name' => 'Chevrolet', 'slug' => 'chevrolet'],
            ['name' => 'Fiat', 'slug' => 'fiat'],
            ['name' => 'Hyundai', 'slug' => 'hyundai'],
        ];
    }

    /**
     * Retorna conteúdo evergreen (como escolher, dicas)
     */
    public function getEvergreenContent(): ?array
    {
        $slug = $this->category->slug ?? $this->extractSlug();
        
        $content = [
            'oleo' => [
                'title' => 'Como escolher o óleo correto',
                'text' => 'A escolha do óleo depende de três fatores principais: viscosidade (ex.: 5W-30), especificação (API/ACEA) e tipo (sintético, semissintético, mineral). Sempre priorize o que consta no manual do fabricante; quando em dúvida, escolha produtos que atendam ou superem a especificação API indicada.',
                'note' => 'Observação: Em veículos com garantia vigente, siga as orientações da concessionária.',
            ],
            'calibragem' => [
                'title' => 'Como calibrar corretamente',
                'text' => 'A calibragem correta dos pneus é fundamental para segurança, economia e durabilidade. Verifique sempre com os pneus frios (veículo parado por pelo menos 2 horas). A pressão recomendada está no manual do proprietário ou no adesivo na coluna da porta do motorista.',
                'note' => 'Observação: Ajuste a pressão conforme carga transportada.',
            ],
        ];
        
        return $content[$slug] ?? null;
    }

    /**
     * Retorna FAQs da categoria
     */
    public function getFaqs(): array
    {
        $slug = $this->category->slug ?? $this->extractSlug();
        
        $faqs = [
            'oleo' => [
                ['question' => 'Qual a diferença entre 5W-30 e 10W-40?', 'answer' => 'Viscosidade a frio (5W vs 10W) e viscosidade a quente (30 vs 40). Use a recomendação do fabricante; 5W-30 oferece melhor partida a frio e menor resistência a quente.'],
                ['question' => 'Posso misturar óleo sintético com mineral?', 'answer' => 'Misturar não é recomendado, mas em uma emergência pequena é aceitável. Troque por um produto homogêneo na próxima oportunidade.'],
                ['question' => 'Com que frequência trocar o óleo?', 'answer' => 'Intervalos típicos: 10.000 km (uso normal) ou conforme manual. Em uso severo, reduzir intervalos.'],
            ],
            'calibragem' => [
                ['question' => 'Qual a pressão correta dos pneus?', 'answer' => 'Varia por veículo e está no manual ou adesivo da porta. Tipicamente entre 28-32 PSI para carros de passeio.'],
                ['question' => 'Posso calibrar com pneu quente?', 'answer' => 'Não recomendado. A pressão aumenta com o aquecimento. Calibre sempre com pneus frios.'],
                ['question' => 'Com que frequência verificar?', 'answer' => 'Mensalmente ou antes de viagens longas.'],
            ],
        ];
        
        return $faqs[$slug] ?? [];
    }

    /**
     * Retorna dados para SEO
     */
    public function getSeoData(): array
    {
        $category = $this->getCategory();
        $slug = $category['slug'];
        
        $titles = [
            'oleo' => 'Óleo Automotivo – Guia completo por marca e modelo | Mercado Veículos',
            'calibragem' => 'Calibragem de Pneus – Pressão correta por veículo | Mercado Veículos',
            'pneus' => 'Pneus Automotivos – Medidas e especificações | Mercado Veículos',
        ];
        
        $descriptions = [
            'oleo' => 'Guia de óleo automotivo: especificações, viscosidades, volumes e recomendações por marca e modelo. Encontre óleos recomendados, tabelas de capacidades e guias práticos.',
            'calibragem' => 'Guia de calibragem: pressão correta dos pneus por marca e modelo. Encontre especificações, dicas de calibragem e tabelas de pressão recomendada.',
            'pneus' => 'Guia de pneus: medidas originais, equivalentes e especificações por veículo. Encontre o pneu correto para seu carro.',
        ];
        
        return [
            'title' => $titles[$slug] ?? "{$category['name']} – Guia Automotivo | Mercado Veículos",
            'description' => $descriptions[$slug] ?? "Guia completo de {$category['name']} por marca e modelo de veículo.",
            'canonical' => route('guide.category', ['category' => $slug]),
            'og_image' => $this->getHeroImage(),
        ];
    }

    /**
     * Retorna breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        $category = $this->getCategory();
        
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $category['name'], 'url' => null],
        ];
    }

    /**
     * Extrai slug da categoria
     */
    private function extractSlug(): string
    {
        if (is_object($this->category) && isset($this->category->slug)) {
            return $this->category->slug;
        }
        return 'oleo'; // Fallback
    }

    /**
     * Retorna nome da categoria pelo slug
     */
    private function getCategoryNameBySlug(): string
    {
        $names = [
            'oleo' => 'Óleo',
            'calibragem' => 'Calibragem',
            'pneus' => 'Pneus',
            'consumo' => 'Consumo',
            'problemas' => 'Problemas',
            'revisao' => 'Revisão',
            'arrefecimento' => 'Arrefecimento',
            'cambio' => 'Câmbio',
            'torque' => 'Torque',
            'fluidos' => 'Fluidos',
            'bateria' => 'Bateria',
            'eletrica' => 'Elétrica',
            'motores' => 'Motores',
            'manutencao' => 'Manutenção',
            'versoes' => 'Versões',
        ];
        
        $slug = $this->extractSlug();
        return $names[$slug] ?? ucfirst($slug);
    }

    /**
     * Retorna descrição padrão
     */
    private function getDefaultDescription(): string
    {
        $name = $this->getCategoryNameBySlug();
        return "Encontre as especificações de {$name} por marca e modelo. Selecione a marca e o modelo para ver o guia detalhado.";
    }
}
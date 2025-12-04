<?php

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;

/**
 * ViewModel para página de categoria de guias
 * 
 * Rota: /guias/{category}
 * View: guide.category
 * Exemplo: /guias/oleo
 * 
 * ✅ REFINADO - Sprint 4
 * - Removidos todos os mocks/arrays hardcoded
 * - Usa queries reais do MongoDB
 * - Integração com repositories
 */
class GuideCategoryPageViewModel
{
    private $category;
    private Collection $guides;
    private Collection $makes;
    private GuideCategoryRepositoryInterface $categoryRepo;
    private GuideRepositoryInterface $guideRepo;

    public function __construct(
        $category, 
        Collection $guides, 
        Collection $makes,
        ?GuideCategoryRepositoryInterface $categoryRepo = null,
        ?GuideRepositoryInterface $guideRepo = null
    ) {
        $this->category = $category;
        $this->guides = $guides;
        $this->makes = $makes;
        $this->categoryRepo = $categoryRepo ?? app(GuideCategoryRepositoryInterface::class);
        $this->guideRepo = $guideRepo ?? app(GuideRepositoryInterface::class);
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
     * ✅ REFINADO: Retorna categorias relacionadas REAIS do banco
     * 
     * Busca categorias que aparecem juntas nos mesmos veículos
     */
    public function getRelatedCategories(): array
    {
        $currentSlug = $this->category->slug ?? $this->extractSlug();
        
        // Buscar guias da categoria atual
        $currentGuides = $this->guideRepo->findByFilters([
            'category_slug' => $currentSlug,
            'limit' => 50
        ]);
        
        // Se não houver guias, retornar categorias ativas (fallback)
        if ($currentGuides->isEmpty()) {
            return $this->categoryRepo->getAllActive()
                ->where('slug', '!=', $currentSlug)
                ->take(3)
                ->map(fn($cat) => [
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ])
                ->values()
                ->toArray();
        }
        
        // Extrair make_slug + model_slug únicos dos guias atuais
        $vehicleKeys = $currentGuides->map(function($guide) {
            return $guide->make_slug . '|' . $guide->model_slug;
        })->unique();
        
        // Buscar outras categorias que possuem guias para esses veículos
        $relatedCategorySlugs = collect();
        
        foreach ($vehicleKeys as $key) {
            [$makeSlug, $modelSlug] = explode('|', $key);
            
            $otherGuides = $this->guideRepo->findByFilters([
                'make_slug' => $makeSlug,
                'model_slug' => $modelSlug,
                'limit' => 10
            ]);
            
            foreach ($otherGuides as $guide) {
                if ($guide->category_slug !== $currentSlug) {
                    $relatedCategorySlugs->push($guide->category_slug);
                }
            }
        }
        
        // Contar ocorrências e pegar top 3
        $topSlugs = $relatedCategorySlugs
            ->countBy()
            ->sortDesc()
            ->take(3)
            ->keys();
        
        // Buscar categorias por slug
        return $topSlugs->map(function($slug) {
            $category = $this->categoryRepo->findBySlug($slug);
            return $category ? [
                'name' => $category->name,
                'slug' => $category->slug,
            ] : null;
        })
        ->filter()
        ->values()
        ->toArray();
    }

    /**
     * ✅ REFINADO: Retorna imagem hero REAL ou fallback
     * 
     * Tenta buscar da categoria, senão usa placeholder
     */
    public function getHeroImage(): string
    {
        // 1. Se categoria tem imagem própria, usar
        if (!empty($this->category->image)) {
            return $this->category->image;
        }
        
        // 2. Fallback: placeholder genérico
        $slug = $this->category->slug ?? $this->extractSlug();
        return "/images/categories/{$slug}-hero.jpg";
    }

    /**
     * ✅ REFINADO: Retorna guias populares REAIS
     * 
     * Ordenados por views ou data de criação
     */
    public function getPopularGuides(): array
    {
        $categorySlug = $this->category->slug ?? $this->extractSlug();
        
        // Buscar guias da categoria ordenados por popularidade
        $popularGuides = $this->guideRepo->findByFilters([
            'category_slug' => $categorySlug,
            'limit' => 6,
            'order_by' => 'created_at', // ou 'views' se existir
            'order_direction' => 'desc'
        ]);
        
        return $popularGuides->map(function($guide) {
            // Extrair specs do payload ou criar descrição
            $specs = $this->extractSpecsFromGuide($guide);
            
            return [
                'title' => $guide->payload['title'] ?? $guide->full_title ?? "{$guide->make} {$guide->model}",
                'slug' => $guide->slug,
                'url' => route('guide.show', ['slug' => $guide->slug]),
                'make' => $guide->make,
                'model' => $guide->model,
                'year_range' => $this->formatYearRange($guide->year_start, $guide->year_end),
                'specs' => $specs, // ✅ ADICIONADO: specs para view
            ];
        })->toArray();
    }

    /**
     * Retorna marcas organizadas com logos
     */
    public function getMakes(): array
    {
        return $this->makes->map(function($make) {
            return [
                'slug' => $make->slug ?? $make['slug'],
                'name' => $make->name ?? $make['name'],
                'logo_url' => $make->logo_url ?? "/images/logos/{$make->slug}.svg",
                'url' => route('guide.category.make', [
                    'category' => $this->extractSlug(),
                    'make' => $make->slug ?? $make['slug']
                ]),
            ];
        })->toArray();
    }

    /**
     * ✅ REFINADO: Retorna conteúdo evergreen REAL
     * 
     * Busca informações da categoria ou usa texto padrão
     */
    public function getEvergreenContent(): array
    {
        $categoryData = $this->getCategory();
        
        return [
            'title' => "Sobre {$categoryData['name']}",
            'text' => $this->category->long_description ?? 
                        $this->category->description ?? 
                        "Encontre informações detalhadas sobre {$categoryData['name']} para diversos modelos de veículos.",
        ];
    }

    /**
     * ✅ REFINADO: Retorna FAQs da categoria ou FAQs genéricas
     */
    public function getFaqs(): array
    {
        // Se categoria tem FAQs próprias, retornar
        if (!empty($this->category->faqs) && is_array($this->category->faqs)) {
            return $this->category->faqs;
        }
        
        // Fallback: FAQs genéricas baseadas na categoria
        $slug = $this->extractSlug();
        $name = $this->getCategoryNameBySlug();
        
        return [
            [
                'question' => "Como encontrar informações de {$name}?",
                'answer' => "Selecione a marca e modelo do seu veículo para ver as especificações detalhadas de {$name}."
            ],
            [
                'question' => "Os dados são confiáveis?",
                'answer' => "Sim, todas as informações são baseadas em manuais oficiais e especificações dos fabricantes."
            ],
            [
                'question' => "Posso usar essas informações para outros anos?",
                'answer' => "Recomendamos sempre verificar o ano específico do seu veículo, pois especificações podem variar."
            ],
        ];
    }

    /**
     * ✅ REFINADO: Dados SEO REAIS da categoria
     */
    public function getSeoData(): array
    {
        $category = $this->getCategory();
        $slug = $category['slug'];
        
        // Se categoria tem meta tags próprias, usar
        if (!empty($this->category->meta_title)) {
            return [
                'title' => $this->category->meta_title,
                'description' => $this->category->meta_description ?? $category['description'],
                'canonical' => route('guide.category', ['category' => $slug]),
                'og_image' => $this->getHeroImage(),
            ];
        }
        
        // Fallback: gerar meta tags baseadas no nome da categoria
        return [
            'title' => "{$category['name']} por Marca e Modelo | Mercado Veículos",
            'description' => $category['description'] ?? "Guia completo de {$category['name']} por marca e modelo de veículo.",
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

    // ========================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ========================================

    /**
     * Extrai slug da categoria
     */
    private function extractSlug(): string
    {
        if (is_object($this->category) && isset($this->category->slug)) {
            return $this->category->slug;
        }
        return 'geral'; // Fallback seguro
    }

    /**
     * Retorna nome da categoria pelo slug (fallback)
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

    /**
     * Formata range de anos
     */
    private function formatYearRange(?int $yearStart, ?int $yearEnd): string
    {
        if (!$yearStart) {
            return 'Ano não especificado';
        }
        
        if (!$yearEnd || $yearStart === $yearEnd) {
            return (string) $yearStart;
        }
        
        return "{$yearStart} - {$yearEnd}";
    }

    /**
     * Extrai specs do guia para exibição resumida
     */
    private function extractSpecsFromGuide($guide): string
    {
        $specs = [];
        
        // Adicionar ano
        if ($guide->year_start) {
            $yearRange = $this->formatYearRange($guide->year_start, $guide->year_end);
            $specs[] = $yearRange;
        }
        
        // Adicionar versão se existir
        if (!empty($guide->version)) {
            $specs[] = $guide->version;
        }
        
        // Tentar extrair especificações do payload
        if (!empty($guide->payload)) {
            $payload = $guide->payload;
            
            // Para guia de óleo
            if (isset($payload['especificacoes']['tipo_oleo'])) {
                $specs[] = $payload['especificacoes']['tipo_oleo'];
            }
            
            // Para guia de pneus
            if (isset($payload['especificacoes']['medida'])) {
                $specs[] = $payload['especificacoes']['medida'];
            }
            
            // Para guia de calibragem
            if (isset($payload['especificacoes']['pressao_dianteiro'])) {
                $specs[] = "Diant: {$payload['especificacoes']['pressao_dianteiro']} PSI";
            }
            
            // Para consumo
            if (isset($payload['especificacoes']['consumo_cidade'])) {
                $specs[] = "Cidade: {$payload['especificacoes']['consumo_cidade']} km/l";
            }
        }
        
        // Se não conseguiu extrair nada, usar descrição genérica
        if (empty($specs)) {
            $categoryName = $this->category->name ?? $this->getCategoryNameBySlug();
            return "Guia de {$categoryName}";
        }
        
        return implode(' • ', $specs);
    }
}
<?php

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Illuminate\Support\Collection;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;

/**
 * ViewModel para página de categoria de guias
 * 
 * Rota: /guias/{category}?page=N
 * View: guide.category.index
 * Exemplo: /guias/oleo, /guias/oleo?page=2
 * 
 * ✅ ATUALIZADO - Com paginação real
 */
class GuideCategoryPageViewModel
{
    private $category;
    private Collection $guides;
    private Collection $makes;
    private int $currentPage;
    private int $totalPages;
    private int $totalGuides;
    private GuideCategoryRepositoryInterface $categoryRepo;
    private GuideRepositoryInterface $guideRepo;

    public function __construct(
        $category,
        Collection $guides,
        Collection $makes,
        int $currentPage = 1,
        int $totalPages = 1,
        int $totalGuides = 0,
        ?GuideCategoryRepositoryInterface $categoryRepo = null,
        ?GuideRepositoryInterface $guideRepo = null
    ) {
        $this->category = $category;
        $this->guides = $guides;
        $this->makes = $makes;
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->totalGuides = $totalGuides;
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
            'name' => $this->category->name ?? 'Categoria',
            'slug' => $this->category->slug ?? 'categoria',
            'description' => $this->category->description ?? '',
            'icon' => $this->category->icon ?? '📋',
        ];
    }

    /**
     * ✅ NOVO: Retorna dados de paginação formatados
     */
    public function getPagination(): array
    {
        $categorySlug = $this->category->slug ?? 'categoria';
        $baseUrl = route('guide.category', ['category' => $categorySlug]);

        // Gerar array de páginas para exibir (máximo 5)
        $pages = $this->generatePageNumbers();

        return [
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages,
            'total_guides' => $this->totalGuides,
            'per_page' => 6,
            'has_prev' => $this->currentPage > 1,
            'has_next' => $this->currentPage < $this->totalPages,
            'prev_url' => $this->currentPage > 1 ? $baseUrl . '?page=' . ($this->currentPage - 1) : null,
            'next_url' => $this->currentPage < $this->totalPages ? $baseUrl . '?page=' . ($this->currentPage + 1) : null,
            'first_url' => $baseUrl . '?page=1',
            'last_url' => $baseUrl . '?page=' . $this->totalPages,
            'pages' => $pages,
            'base_url' => $baseUrl,
        ];
    }

    /**
     * Gera números de páginas para exibir (máximo 5 páginas visíveis)
     * Exemplo: 1 [2] 3 4 5 ... 10
     */
    private function generatePageNumbers(): array
    {
        $pages = [];
        $current = $this->currentPage;
        $total = $this->totalPages;

        if ($total <= 7) {
            // Se tem 7 ou menos páginas, mostra todas
            for ($i = 1; $i <= $total; $i++) {
                $pages[] = [
                    'number' => $i,
                    'url' => route('guide.category', ['category' => $this->category->slug]) . '?page=' . $i,
                    'is_current' => $i === $current,
                ];
            }
        } else {
            // Lógica mais complexa para muitas páginas
            // Sempre mostra: primeira, última, e 5 ao redor da atual

            // Adiciona primeira página
            $pages[] = [
                'number' => 1,
                'url' => route('guide.category', ['category' => $this->category->slug]) . '?page=1',
                'is_current' => 1 === $current,
            ];

            // Adiciona "..." se necessário
            if ($current > 3) {
                $pages[] = ['number' => '...', 'url' => null, 'is_current' => false];
            }

            // Páginas ao redor da atual
            $start = max(2, $current - 1);
            $end = min($total - 1, $current + 1);

            for ($i = $start; $i <= $end; $i++) {
                $pages[] = [
                    'number' => $i,
                    'url' => route('guide.category', ['category' => $this->category->slug]) . '?page=' . $i,
                    'is_current' => $i === $current,
                ];
            }

            // Adiciona "..." se necessário
            if ($current < $total - 2) {
                $pages[] = ['number' => '...', 'url' => null, 'is_current' => false];
            }

            // Adiciona última página
            if ($total > 1) {
                $pages[] = [
                    'number' => $total,
                    'url' => route('guide.category', ['category' => $this->category->slug]) . '?page=' . $total,
                    'is_current' => $total === $current,
                ];
            }
        }

        return $pages;
    }

    /**
     * Retorna categorias relacionadas
     */
    public function getRelatedCategories(): array
    {
        $currentSlug = $this->category->slug ?? 'categoria';

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

    /**
     * Retorna imagem hero
     */
    public function getHeroImage(): string
    {
        if (!empty($this->category->image)) {
            return $this->category->image;
        }

        $slug = $this->category->slug ?? 'categoria';
        return "/images/categories/{$slug}-hero.jpg";
    }

    /**
     * Retorna guias populares (da página atual)
     */
    public function getPopularGuides(): array
    {
        return $this->guides->map(function ($guide) {
            $specs = $this->extractSpecsFromGuide($guide);

            return [
                'title' => $guide->full_title ?? "{$guide->make} {$guide->model} {$guide->version} {$guide->year_start}",
                'slug' => $guide->slug,
                'url' => $guide->url ?? route('guide.show', ['slug' => $guide->slug]),
                'make' => $guide->make,
                'model' => $guide->model,
                'year_range' => $guide->year_start . ($guide->year_end && $guide->year_end != $guide->year_start ? '-' . $guide->year_end : ''),
                'specs' => $specs,
            ];
        })->toArray();
    }

    /**
     * Extrai especificações do guia para exibir como resumo
     */
    private function extractSpecsFromGuide($guide): string
    {
        $parts = [];

        if ($guide->year_start) {
            $parts[] = $guide->year_start;
        }

        if (!empty($guide->version)) {
            $parts[] = $guide->version;
        }

        return implode(' • ', array_filter($parts)) ?: 'Veja detalhes';
    }

    /**
     * Retorna marcas disponíveis
     */
    public function getMakes(): array
    {
        return $this->makes->map(function ($make) {
            $categorySlug = $this->category->slug ?? 'categoria';
            return [
                'id' => $make->id,
                'name' => $make->name,
                'slug' => $make->slug,
                'logo' => $make->logo_url ?? "/images/logos/{$make->slug}.svg",
                'url' => route('guide.category.make', [
                    'category' => $categorySlug,
                    'make' => $make->slug
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
     * Retorna FAQs
     */
    public function getFaqs(): array
    {
        $categoryName = $this->category->name ?? 'esta categoria';

        return [
            [
                'question' => "Como encontrar informações de {$categoryName}?",
                'answer' => "Selecione a marca e modelo do seu veículo para ver as especificações detalhadas de {$categoryName}."
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
            ['name' => $this->category->name ?? 'Categoria', 'url' => null],
        ];
    }


}
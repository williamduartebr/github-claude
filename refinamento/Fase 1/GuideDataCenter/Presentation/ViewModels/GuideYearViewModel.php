<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

/**
 * ViewModel para página de ano (lista versões)
 * 
 * Rota: /guias/{category}/{make}/{model}/{year}
 * View: guide-data-center::guide.year
 * Exemplo: /guias/oleo/toyota/corolla/2025
 */
class GuideYearViewModel
{
    private $category;
    private $make;
    private $model;
    private string $year;

    public function __construct($category, $make, $model, string $year)
    {
        $this->category = $category;
        $this->make = $make;
        $this->model = $model;
        $this->year = $year;
    }

    public function getCategory(): array
    {
        return [
            'name' => $this->category->name ?? 'Óleo',
            'slug' => $this->category->slug ?? 'oleo',
        ];
    }

    public function getMake(): array
    {
        return [
            'name' => $this->make->name ?? 'Toyota',
            'slug' => $this->make->slug ?? 'toyota',
        ];
    }

    public function getModel(): array
    {
        return [
            'name' => $this->model->name ?? 'Corolla',
            'slug' => $this->model->slug ?? 'corolla',
        ];
    }

    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * Retorna versões disponíveis para este ano
     */
    public function getVersions(): array
    {
        $modelSlug = $this->model->slug ?? 'corolla';
        $year = (int) $this->year;

        // Mock de versões por modelo/ano
        $versions = [
            'corolla' => [
                2020 => [
                    ['version' => 'GLi', 'engine' => '2.0 Dynamic Force', 'url' => $this->buildUrl('gli')],
                    ['version' => 'XEi', 'engine' => '2.0 Dynamic Force', 'url' => $this->buildUrl('xei')],
                    ['version' => 'Altis', 'engine' => '2.0 Dynamic Force', 'url' => $this->buildUrl('altis')],
                ],
                2019 => [
                    ['version' => 'GLi', 'engine' => '1.8 VVT-i', 'url' => $this->buildUrl('gli')],
                    ['version' => 'XEi', 'engine' => '2.0 VVT-i', 'url' => $this->buildUrl('xei')],
                    ['version' => 'Altis', 'engine' => '2.0 VVT-i', 'url' => $this->buildUrl('altis')],
                    ['version' => 'XRS', 'engine' => '2.0 VVT-i', 'url' => $this->buildUrl('xrs')],
                ],
            ],
            'hilux' => [
                2020 => [
                    ['version' => 'SR', 'engine' => '2.8 Turbo Diesel', 'url' => $this->buildUrl('sr')],
                    ['version' => 'SRV', 'engine' => '2.8 Turbo Diesel', 'url' => $this->buildUrl('srv')],
                    ['version' => 'SRX', 'engine' => '2.8 Turbo Diesel', 'url' => $this->buildUrl('srx')],
                ],
            ],
        ];

        // Busca versões específicas
        if (isset($versions[$modelSlug][$year])) {
            return $versions[$modelSlug][$year];
        }

        // Versões padrão >= 2020 (Corolla)
        if ($modelSlug === 'corolla' && $year >= 2020) {
            return $versions['corolla'][2020];
        }

        // Versões padrão < 2020 (Corolla)
        if ($modelSlug === 'corolla' && $year < 2020) {
            return $versions['corolla'][2019];
        }

        // Hilux >= 2016
        if ($modelSlug === 'hilux' && $year >= 2016) {
            return $versions['hilux'][2020];
        }

        // Fallback genérico
        return [
            ['version' => 'Base', 'engine' => '1.0 Turbo', 'url' => $this->buildUrl('base')],
            ['version' => 'Plus', 'engine' => '1.0 Turbo', 'url' => $this->buildUrl('plus')],
        ];
    }

    public function getStats(): array
    {
        $versions = $this->getVersions();

        return [
            'total_versions' => count($versions),
        ];
    }

    public function getComplementaryCategories(): array
    {
        $all = [
            ['name' => 'Calibragem', 'slug' => 'calibragem'],
            ['name' => 'Pneus', 'slug' => 'pneus'],
            ['name' => 'Bateria', 'slug' => 'bateria'],
            ['name' => 'Correia', 'slug' => 'correia'],
            ['name' => 'Fluidos', 'slug' => 'fluidos'],
            ['name' => 'Revisão', 'slug' => 'revisao'],
        ];

        $currentSlug = $this->category->slug ?? 'oleo';
        return array_filter($all, fn($cat) => $cat['slug'] !== $currentSlug);
    }

    public function getSeoData(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();

        return [
            'title' => "{$category['name']} {$make['name']} {$model['name']} {$this->year} – Todas as versões",
            'description' => "Guias de {$category['name']} para {$make['name']} {$model['name']} {$this->year}. Escolha a versão do seu veículo.",
            'canonical' => "/guias/{$category['slug']}/{$make['slug']}/{$model['slug']}/{$this->year}",
            'og_image' => "/images/og/{$model['slug']}-{$this->year}.jpg",
        ];
    }

    public function getBreadcrumbs(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();

        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $category['name'], 'url' => route('guide.category', $category['slug'])],
            ['name' => $make['name'], 'url' => route('guide.category.make', ['category' => $category['slug'], 'make' => $make['slug']])],
            ['name' => $model['name'], 'url' => route('guide.category.make.model', ['category' => $category['slug'], 'make' => $make['slug'], 'model' => $model['slug']])],
            ['name' => $this->year, 'url' => null],
        ];
    }

    private function buildUrl(string $version): string
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();

        return "/guias/{$category['slug']}/{$make['slug']}/{$model['slug']}/{$this->year}/{$version}";
    }
}

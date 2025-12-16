<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Illuminate\Support\Str;

/**
 * 🔗 Seeder de Relacionamentos entre Guias
 * 
 * Popula os links_internal de cada guia com:
 * 1. Guias Relacionados (outras categorias do mesmo veículo)
 * 2. Conteúdos Essenciais (anos adjacentes, problemas, cluster)
 * 
 * Execute DEPOIS de criar os guias:
 * php artisan db:seed --class=Database\\Seeders\\GuideRelationshipsSeeder
 */
class GuideRelationshipsSeeder extends Seeder
{
    /**
     * Ícones das categorias
     */
    private array $categoryIcons = [
        'oleo' => '🛢️',
        'fluidos' => '💧',
        'calibragem' => '🔧',
        'pneus' => '🚗',
        'bateria' => '🔋',
        'manutencao' => '📋',
        'revisao' => '📋',
        'consumo' => '⛽',
        'transmissao' => '⚙️',
        'cambio' => '⚙️',
        'arrefecimento' => '❄️',
        'suspensao' => '🔩',
        'problemas-comuns' => '⚠️',
        'problemas' => '⚠️',
        'recalls' => '📢',
        'comparacoes' => '📊',
    ];

    public function run(): void
    {
        $this->command->info('🔗 POPULANDO RELACIONAMENTOS ENTRE GUIAS');
        $this->command->newLine();

        $guides = Guide::all();
        
        if ($guides->isEmpty()) {
            $this->command->error('❌ Nenhum guia encontrado! Execute os seeders de guias primeiro.');
            return;
        }

        $this->command->info("📊 Encontrados {$guides->count()} guias no banco");
        $this->command->newLine();

        $progressBar = $this->command->getOutput()->createProgressBar($guides->count());
        $progressBar->start();

        $updated = 0;

        foreach ($guides as $guide) {
            try {
                $linksInternal = $this->generateAllLinks($guide);
                
                if (!empty($linksInternal)) {
                    $guide->links_internal = $linksInternal;
                    $guide->save();
                    $updated++;
                }
                
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->command->error("\n❌ Erro ao processar {$guide->slug}: {$e->getMessage()}");
            }
        }

        $progressBar->finish();
        $this->command->newLine(2);
        
        $this->command->info("✅ Relacionamentos criados!");
        $this->command->info("📊 Guias atualizados: {$updated}");
        $this->command->newLine();
    }

    /**
     * Gera todos os links (Guias Relacionados + Conteúdos Essenciais)
     */
    private function generateAllLinks(Guide $guide): array
    {
        $links = [];

        // 1️⃣ GUIAS RELACIONADOS (outras categorias do mesmo veículo)
        $relatedGuides = $this->getRelatedGuides($guide);
        
        // 2️⃣ CONTEÚDOS ESSENCIAIS (cluster estratégico)
        $essentialLinks = $this->getEssentialLinks($guide);

        // Combinar todos os links
        return array_merge($relatedGuides, $essentialLinks);
    }

    /**
     * 1️⃣ GUIAS RELACIONADOS
     * Retorna guias de OUTRAS categorias do MESMO veículo
     */
    private function getRelatedGuides(Guide $guide): array
    {
        $links = [];

        // Buscar guias do mesmo veículo (make, model, year), mas categoria diferente
        $sameVehicleGuides = Guide::where('make', $guide->make)
            ->where('model', $guide->model)
            ->where('year_start', $guide->year_start)
            ->where('guide_category_id', '!=', $guide->guide_category_id)
            ->limit(12) // Máximo 12 categorias relacionadas
            ->get();

        foreach ($sameVehicleGuides as $relatedGuide) {
            $category = GuideCategory::find($relatedGuide->guide_category_id);
            
            if ($category) {
                $links[] = [
                    'title' => $category->name,
                    'url' => $relatedGuide->url ?? "/guias/{$relatedGuide->category_slug}/{$relatedGuide->make_slug}/{$relatedGuide->model_slug}/{$relatedGuide->year_start}",
                    'type' => 'related_guide',
                    'category' => 'guias_relacionados',
                    'icon' => $this->categoryIcons[$category->slug] ?? '📄',
                ];
            }
        }

        return $links;
    }

    /**
     * 2️⃣ CONTEÚDOS ESSENCIAIS
     * Retorna links estratégicos (ficha técnica, anos adjacentes, problemas, etc)
     */
    private function getEssentialLinks(Guide $guide): array
    {
        $links = [];

        // Link 1: Ficha Técnica da Versão
        if (!empty($guide->version)) {
            $links[] = [
                'title' => "Ficha técnica – {$guide->make} {$guide->model} {$guide->year_start} {$guide->version}",
                'url' => "/veiculos/{$guide->make_slug}/{$guide->model_slug}/{$guide->year_start}/{$guide->version_slug}",
                'type' => 'technical_sheet',
                'category' => 'conteudos_essenciais',
                'icon' => '🚗',
            ];
        }

        // Link 2: Ficha Técnica Geral do Ano
        $links[] = [
            'title' => "Ficha Técnica do {$guide->model} {$guide->year_start}",
            'url' => "/veiculos/{$guide->make_slug}/{$guide->model_slug}/{$guide->year_start}",
            'type' => 'technical_sheet_year',
            'category' => 'conteudos_essenciais',
            'icon' => '📘',
        ];

        // Link 3: Consumo Real
        $consumoGuide = Guide::where('make', $guide->make)
            ->where('model', $guide->model)
            ->where('year_start', $guide->year_start)
            ->whereHas('category', function($q) {
                $q->where('slug', 'consumo');
            })
            ->first();

        if ($consumoGuide) {
            $links[] = [
                'title' => "Consumo Real — Motor {$guide->motor}",
                'url' => $consumoGuide->url,
                'type' => 'consumption',
                'category' => 'conteudos_essenciais',
                'icon' => '⛽',
            ];
        }

        // Link 4: Problemas Comuns (geração)
        $yearStart = max(2000, $guide->year_start - 3);
        $yearEnd = $guide->year_start + 3;
        
        $problemsGuide = Guide::where('make', $guide->make)
            ->where('model', $guide->model)
            ->where('year_start', '>=', $yearStart)
            ->where('year_start', '<=', $yearEnd)
            ->whereHas('category', function($q) {
                $q->whereIn('slug', ['problemas-comuns', 'problemas']);
            })
            ->first();

        if ($problemsGuide) {
            $links[] = [
                'title' => "Problemas comuns (Geração {$yearStart}–{$yearEnd})",
                'url' => $problemsGuide->url,
                'type' => 'common_problems',
                'category' => 'conteudos_essenciais',
                'icon' => '⚠️',
            ];
        }

        // Link 5: Fluidos e Capacidades
        $fluidsGuide = Guide::where('make', $guide->make)
            ->where('model', $guide->model)
            ->where('year_start', $guide->year_start)
            ->whereHas('category', function($q) {
                $q->where('slug', 'fluidos');
            })
            ->first();

        if ($fluidsGuide && $guide->category_slug !== 'fluidos') {
            $links[] = [
                'title' => 'Fluidos e capacidades',
                'url' => $fluidsGuide->url,
                'type' => 'fluids',
                'category' => 'conteudos_essenciais',
                'icon' => '💧',
            ];
        }

        // Link 6 e 7: Anos adjacentes (anterior e posterior)
        $adjacentYears = [
            $guide->year_start - 1 => '🔄',
            $guide->year_start + 1 => '🔄',
        ];

        foreach ($adjacentYears as $year => $icon) {
            $adjacentGuide = Guide::where('make', $guide->make)
                ->where('model', $guide->model)
                ->where('year_start', $year)
                ->where('guide_category_id', $guide->guide_category_id) // Mesma categoria
                ->first();

            if ($adjacentGuide) {
                $category = GuideCategory::find($guide->guide_category_id);
                $categoryName = $category ? $category->name : 'Guia';
                
                $links[] = [
                    'title' => "{$categoryName} do {$guide->model} {$year}",
                    'url' => $adjacentGuide->url,
                    'type' => 'adjacent_year',
                    'category' => 'conteudos_essenciais',
                    'icon' => $icon,
                ];
            }
        }

        // Link 8: Motor alternativo (se houver)
        if (!empty($guide->motor)) {
            $alternativeEngine = Guide::where('make', $guide->make)
                ->where('model', $guide->model)
                ->where('year_start', $guide->year_start)
                ->where('guide_category_id', $guide->guide_category_id)
                ->where('motor', '!=', $guide->motor)
                ->first();

            if ($alternativeEngine) {
                $links[] = [
                    'title' => "Motor alternativo — {$alternativeEngine->motor}",
                    'url' => $alternativeEngine->url,
                    'type' => 'alternative_engine',
                    'category' => 'conteudos_essenciais',
                    'icon' => '🔧',
                ];
            }
        }

        return $links;
    }
}
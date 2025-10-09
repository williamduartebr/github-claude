<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;


use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class ValidateCarContentCommand extends Command
{
    protected $signature = 'review-schedule:validate-cars {--show-problems : Exibir detalhes dos problemas}';
    protected $description = 'Valida conteúdo específico de carros convencionais';

    public function handle(): int
    {
        $articles = Article::where('template', 'review_schedule_car')
            ->where('status', 'published')
            ->get();

        if ($articles->isEmpty()) {
            $this->info('Nenhum artigo de carro encontrado.');
            return self::SUCCESS;
        }

        $this->info("🔍 Analisando {$articles->count()} artigos de carros...");

        $problems = [
            'cronogramas_identicos' => [],
            'servicos_genericos' => [],
            'falta_especializacao' => [],
            'custos_inadequados' => []
        ];

        foreach ($articles as $article) {
            $this->analyzeCarArticle($article, $problems);
        }

        $this->displayCarResults($problems);

        return self::SUCCESS;
    }

    private function analyzeCarArticle(Article $article, array &$problems): void
    {
        $content = $article->content;
        $slug = $article->slug;

        // Verificar cronograma detalhado
        if (!empty($content['cronograma_detalhado'])) {
            $schedules = $content['cronograma_detalhado'];
            
            // Verificar cronogramas idênticos
            if (count($schedules) >= 2) {
                $firstServices = implode('|', $schedules[0]['servicos_principais'] ?? []);
                $duplicateCount = 0;
                
                foreach ($schedules as $schedule) {
                    $currentServices = implode('|', $schedule['servicos_principais'] ?? []);
                    if ($firstServices === $currentServices) {
                        $duplicateCount++;
                    }
                }
                
                if ($duplicateCount > 3) {
                    $problems['cronogramas_identicos'][] = $slug;
                }
            }

            // Verificar serviços genéricos
            $hasGenericServices = false;
            
            foreach ($schedules as $revision) {
                $services = implode(' ', $revision['servicos_principais'] ?? []);
                
                if (str_contains($services, 'Verificação minuciosa') ||
                    str_contains($services, 'Diagnóstico básico') ||
                    str_contains($services, 'Inspeção detalhada dos pneumáticos')) {
                    $hasGenericServices = true;
                    break;
                }
            }
            
            if ($hasGenericServices) {
                $problems['servicos_genericos'][] = $slug;
            }

            // Verificar falta de especializacao por marca/modelo
            $vehicleData = $article->extracted_entities ?? [];
            $make = strtolower($vehicleData['marca'] ?? '');
            $model = strtolower($vehicleData['modelo'] ?? '');
            
            // Premium brands should have different services than popular brands
            $premiumBrands = ['bmw', 'mercedes', 'audi', 'lexus'];
            $popularBrands = ['renault', 'fiat', 'chevrolet'];
            
            $hasSpecificServices = false;
            foreach ($schedules as $revision) {
                $services = implode(' ', $revision['servicos_principais'] ?? []);
                
                if (in_array($make, $premiumBrands)) {
                    // Premium should mention specific systems
                    if (str_contains($services, 'sistema') || 
                        str_contains($services, 'eletrônico') ||
                        str_contains($services, 'performance')) {
                        $hasSpecificServices = true;
                    }
                }
            }
            
            if (in_array($make, $premiumBrands) && !$hasSpecificServices) {
                $problems['falta_especializacao'][] = $slug;
            }
        }

        // Verificar custos adequados por marca
        if (!empty($content['visao_geral_revisoes'])) {
            $vehicleData = $article->extracted_entities ?? [];
            $make = strtolower($vehicleData['marca'] ?? '');
            
            $costs = [];
            foreach ($content['visao_geral_revisoes'] as $revision) {
                $cost = $revision['estimativa_custo'] ?? '';
                if (preg_match('/R\$ (\d+)/', $cost, $matches)) {
                    $costs[] = (int)$matches[1];
                }
            }
            
            if (!empty($costs)) {
                $maxCost = max($costs);
                $premiumBrands = ['bmw', 'mercedes', 'audi', 'lexus'];
                $popularBrands = ['renault', 'fiat', 'chevrolet'];
                
                if (in_array($make, $premiumBrands) && $maxCost < 800) {
                    $problems['custos_inadequados'][] = $slug;
                } elseif (in_array($make, $popularBrands) && $maxCost > 1500) {
                    $problems['custos_inadequados'][] = $slug;
                }
            }
        }
    }

    private function displayCarResults(array $problems): void
    {
        $totalProblems = array_sum(array_map('count', $problems));

        if ($totalProblems === 0) {
            $this->info('✅ Todos os artigos de carros estão corretos!');
            return;
        }

        $this->warn("⚠️  Encontrados {$totalProblems} problemas em carros:");

        if (!empty($problems['cronogramas_identicos'])) {
            $count = count($problems['cronogramas_identicos']);
            $this->error("❌ Cronogramas idênticos repetidos: {$count} artigos");
            if ($this->option('show-problems')) {
                foreach (array_slice($problems['cronogramas_identicos'], 0, 5) as $slug) {
                    $this->line("   - {$slug}");
                }
                if ($count > 5) {
                    $this->line("   - ... e mais " . ($count - 5) . " artigos");
                }
            }
        }

        if (!empty($problems['servicos_genericos'])) {
            $count = count($problems['servicos_genericos']);
            $this->error("❌ Serviços muito genéricos: {$count} artigos");
        }

        if (!empty($problems['falta_especializacao'])) {
            $count = count($problems['falta_especializacao']);
            $this->error("❌ Falta especialização por marca: {$count} artigos");
        }

        if (!empty($problems['custos_inadequados'])) {
            $count = count($problems['custos_inadequados']);
            $this->error("❌ Custos inadequados para a marca: {$count} artigos");
        }

        $this->newLine();
        $this->info('💡 COMANDO DE CORREÇÃO:');
        $this->line('   php artisan review-schedule:fix-car-detailed --limit=500 --force');
    }
}
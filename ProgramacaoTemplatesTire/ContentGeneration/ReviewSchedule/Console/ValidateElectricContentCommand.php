<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;


use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class ValidateElectricContentCommand extends Command
{
    protected $signature = 'review-schedule:validate-electrics {--show-problems : Exibir detalhes dos problemas}';
    protected $description = 'Valida conteúdo específico de veículos elétricos';

    public function handle(): int
    {
        $articles = Article::where('template', 'review_schedule_electric')
            ->where('status', 'published')
            ->get();

        if ($articles->isEmpty()) {
            $this->info('Nenhum artigo elétrico encontrado.');
            return self::SUCCESS;
        }

        $this->info("🔍 Analisando {$articles->count()} artigos elétricos...");

        $problems = [
            'servicos_combustao' => [],
            'cronogramas_identicos' => [],
            'falta_especializacao_eletrica' => [],
            'custos_inadequados' => []
        ];

        foreach ($articles as $article) {
            $this->analyzeElectricArticle($article, $problems);
        }

        $this->displayElectricResults($problems);

        return self::SUCCESS;
    }

    private function analyzeElectricArticle(Article $article, array &$problems): void
    {
        $content = $article->content;
        $slug = $article->slug;

        // Verificar cronograma detalhado
        if (!empty($content['cronograma_detalhado'])) {
            $schedules = $content['cronograma_detalhado'];
            
            // Verificar serviços de combustão em elétricos (CRÍTICO!)
            $hasCombustionServices = false;
            
            foreach ($schedules as $revision) {
                $services = implode(' ', $revision['servicos_principais'] ?? []);
                
                if (str_contains($services, 'óleo') ||
                    str_contains($services, 'combustível') ||
                    str_contains($services, 'injeção') ||
                    str_contains($services, 'embreagem') ||
                    str_contains($services, 'motor') && !str_contains($services, 'elétrico')) {
                    $hasCombustionServices = true;
                    break;
                }
            }
            
            if ($hasCombustionServices) {
                $problems['servicos_combustao'][] = $slug;
            }

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

            // Verificar especialização elétrica
            $hasElectricServices = false;
            
            foreach ($schedules as $revision) {
                $services = implode(' ', $revision['servicos_principais'] ?? []);
                
                if (str_contains($services, 'bateria') ||
                    str_contains($services, 'elétrico') ||
                    str_contains($services, 'regenerativo') ||
                    str_contains($services, 'alta tensão') ||
                    str_contains($services, 'conectores') ||
                    str_contains($services, 'carregamento')) {
                    $hasElectricServices = true;
                    break;
                }
            }
            
            if (!$hasElectricServices) {
                $problems['falta_especializacao_eletrica'][] = $slug;
            }
        }

        // Verificar custos (elétricos premium devem ser mais caros)
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
                $premiumBrands = ['tesla', 'mercedes', 'mercedes-benz', 'bmw', 'audi', 'porsche'];
                
                if (in_array($make, $premiumBrands) && $maxCost < 1000) {
                    $problems['custos_inadequados'][] = $slug;
                } elseif (!in_array($make, $premiumBrands) && $maxCost < 400) {
                    $problems['custos_inadequados'][] = $slug;
                }
            }
        }
    }

    private function displayElectricResults(array $problems): void
    {
        $totalProblems = array_sum(array_map('count', $problems));

        if ($totalProblems === 0) {
            $this->info('✅ Todos os artigos elétricos estão corretos!');
            return;
        }

        $this->warn("⚠️  Encontrados {$totalProblems} problemas em elétricos:");

        if (!empty($problems['servicos_combustao'])) {
            $count = count($problems['servicos_combustao']);
            $this->error("❌ CRÍTICO - Serviços de combustão em elétricos: {$count} artigos");
            if ($this->option('show-problems')) {
                foreach (array_slice($problems['servicos_combustao'], 0, 5) as $slug) {
                    $this->line("   - {$slug}");
                }
                if ($count > 5) {
                    $this->line("   - ... e mais " . ($count - 5) . " artigos");
                }
            }
        }

        if (!empty($problems['cronogramas_identicos'])) {
            $count = count($problems['cronogramas_identicos']);
            $this->error("❌ Cronogramas idênticos repetidos: {$count} artigos");
        }

        if (!empty($problems['falta_especializacao_eletrica'])) {
            $count = count($problems['falta_especializacao_eletrica']);
            $this->error("❌ Falta especialização elétrica: {$count} artigos");
        }

        if (!empty($problems['custos_inadequados'])) {
            $count = count($problems['custos_inadequados']);
            $this->error("❌ Custos inadequados para elétricos: {$count} artigos");
        }

        $this->newLine();
        $this->info('💡 COMANDO DE CORREÇÃO:');
        $this->line('   php artisan review-schedule:fix-electric-detailed --limit=500 --force');
    }
}
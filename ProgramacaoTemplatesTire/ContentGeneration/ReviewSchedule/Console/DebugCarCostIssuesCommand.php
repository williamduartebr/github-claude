<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;


use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class DebugCarCostIssuesCommand extends Command
{
    protected $signature = 'review-schedule:debug-car-costs {--fix : Corrigir automaticamente os problemas encontrados}';
    protected $description = 'Debug específico dos 2 artigos de carros com custos inadequados';

    public function handle(): int
    {
        $this->info('🔍 Investigando os 2 artigos de carros com custos inadequados...');

        $articles = Article::where('template', 'review_schedule_car')
            ->where('status', 'published')
            ->get();

        $problemArticles = [];

        foreach ($articles as $article) {
            if ($this->hasCostIssue($article)) {
                $problemArticles[] = $article;
            }
        }

        if (empty($problemArticles)) {
            $this->info('✅ Nenhum problema de custo encontrado!');
            return self::SUCCESS;
        }

        $this->warn("🔍 Encontrados " . count($problemArticles) . " artigos com problemas de custo:");
        $this->newLine();

        foreach ($problemArticles as $index => $article) {
            $this->displayArticleDetails($article, $index + 1);
            $this->newLine();
        }

        if ($this->option('fix')) {
            return $this->fixCostIssues($problemArticles);
        }

        $this->info('💡 Para corrigir automaticamente:');
        $this->line('   php artisan review-schedule:debug-car-costs --fix');

        return self::SUCCESS;
    }

    private function hasCostIssue(Article $article): bool
    {
        $content = $article->content;
        
        if (empty($content['visao_geral_revisoes'])) {
            return false;
        }

        $vehicleData = $article->extracted_entities ?? [];
        $make = strtolower($vehicleData['marca'] ?? '');
        
        $costs = [];
        foreach ($content['visao_geral_revisoes'] as $revision) {
            $cost = $revision['estimativa_custo'] ?? '';
            if (preg_match('/R\$ (\d+)/', $cost, $matches)) {
                $costs[] = (int)$matches[1];
            }
        }
        
        if (empty($costs)) {
            return false;
        }

        $maxCost = max($costs);
        $minCost = min($costs);
        
        // Definir marcas e faixas esperadas
        $premiumBrands = ['bmw', 'mercedes', 'mercedes-benz', 'audi', 'lexus', 'volvo', 'jaguar', 'land rover', 'porsche'];
        $popularBrands = ['chevrolet', 'ford', 'fiat', 'renault', 'volkswagen', 'hyundai', 'peugeot', 'citroën', 'dacia'];
        
        // Verificar inconsistências
        if (in_array($make, $premiumBrands)) {
            // Premium brands - custos devem ser maiores
            if ($maxCost < 800) {
                return true; // Muito barato para premium
            }
        } elseif (in_array($make, $popularBrands)) {
            // Popular brands - custos devem ser menores
            if ($maxCost > 1500) {
                return true; // Muito caro para popular
            }
        } else {
            // Marcas não mapeadas - verificar extremos
            if ($maxCost < 200 || $maxCost > 2000) {
                return true; // Valores extremos
            }
        }

        return false;
    }

    private function displayArticleDetails(Article $article, int $number): void
    {
        $content = $article->content;
        $vehicleData = $article->extracted_entities ?? [];
        
        $this->line("#{$number} - {$article->slug}");
        $this->line("Veículo: {$vehicleData['marca']} {$vehicleData['modelo']} {$vehicleData['ano']}");
        
        // Extrair custos atuais
        $costs = [];
        if (!empty($content['visao_geral_revisoes'])) {
            foreach ($content['visao_geral_revisoes'] as $revision) {
                $cost = $revision['estimativa_custo'] ?? '';
                if (preg_match('/R\$ (\d+)/', $cost, $matches)) {
                    $costs[] = (int)$matches[1];
                }
            }
        }

        if (!empty($costs)) {
            $minCost = min($costs);
            $maxCost = max($costs);
            $this->line("Custos atuais: R$ {$minCost} - R$ {$maxCost}");
        }

        // Analisar marca
        $make = strtolower($vehicleData['marca'] ?? '');
        $premiumBrands = ['bmw', 'mercedes', 'mercedes-benz', 'audi', 'lexus', 'volvo', 'jaguar', 'land rover', 'porsche'];
        $popularBrands = ['chevrolet', 'ford', 'fiat', 'renault', 'volkswagen', 'hyundai', 'peugeot', 'citroën', 'dacia'];
        
        if (in_array($make, $premiumBrands)) {
            $this->line("Categoria: PREMIUM (deveria ter custos mais altos)");
            $expectedRange = "R$ 800 - R$ 1500+";
        } elseif (in_array($make, $popularBrands)) {
            $this->line("Categoria: POPULAR (deveria ter custos moderados)");
            $expectedRange = "R$ 300 - R$ 1200";
        } else {
            $this->line("Categoria: NÃO MAPEADA (marca não reconhecida)");
            $expectedRange = "R$ 300 - R$ 1500";
        }
        
        $this->line("Faixa esperada: {$expectedRange}");

        // Verificar problema específico
        if (!empty($costs)) {
            $maxCost = max($costs);
            if (in_array($make, $premiumBrands) && $maxCost < 800) {
                $this->error("⚠️  PROBLEMA: Marca premium com custos muito baixos");
            } elseif (in_array($make, $popularBrands) && $maxCost > 1500) {
                $this->error("⚠️  PROBLEMA: Marca popular com custos muito altos");
            } elseif (!in_array($make, array_merge($premiumBrands, $popularBrands))) {
                $this->warn("⚠️  PROBLEMA: Marca não mapeada no sistema");
            }
        }
    }

    private function fixCostIssues(array $problemArticles): int
    {
        $this->info('🔧 Iniciando correção automática...');
        
        $fixed = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar(count($problemArticles));
        $progressBar->start();

        foreach ($problemArticles as $article) {
            try {
                $this->fixArticleCosts($article);
                $fixed++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("\nErro ao corrigir {$article->slug}: " . $e->getMessage());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Correção concluída!");
        $this->info("📊 Artigos corrigidos: {$fixed}");
        
        if ($errors > 0) {
            $this->warn("⚠️  Erros: {$errors}");
        }

        return self::SUCCESS;
    }

    private function fixArticleCosts(Article $article): void
    {
        $content = $article->content;
        $vehicleData = $article->extracted_entities ?? [];
        $make = strtolower($vehicleData['marca'] ?? '');

        // Determinar multiplicador correto
        $premiumBrands = ['bmw', 'mercedes', 'mercedes-benz', 'audi', 'lexus', 'volvo', 'jaguar', 'land rover', 'porsche'];
        $popularBrands = ['chevrolet', 'ford', 'fiat', 'renault', 'volkswagen', 'hyundai', 'peugeot', 'citroën', 'dacia'];
        
        $multiplier = 1.0;
        if (in_array($make, $premiumBrands)) {
            $multiplier = 1.4; // Premium +40%
        } elseif (in_array($make, $popularBrands)) {
            $multiplier = 0.85; // Popular -15%
        }

        // Corrigir visão geral
        if (!empty($content['visao_geral_revisoes'])) {
            foreach ($content['visao_geral_revisoes'] as &$revision) {
                $cost = $revision['estimativa_custo'] ?? '';
                if (preg_match('/R\$ (\d+) - R\$ (\d+)/', $cost, $matches)) {
                    $minCost = (int)($matches[1] * $multiplier);
                    $maxCost = (int)($matches[2] * $multiplier);
                    $revision['estimativa_custo'] = "R$ {$minCost} - R$ {$maxCost}";
                }
            }
        }

        // Corrigir cronograma detalhado
        if (!empty($content['cronograma_detalhado'])) {
            foreach ($content['cronograma_detalhado'] as &$revision) {
                $cost = $revision['estimativa_custo'] ?? '';
                if (preg_match('/R\$ (\d+) - R\$ (\d+)/', $cost, $matches)) {
                    $minCost = (int)($matches[1] * $multiplier);
                    $maxCost = (int)($matches[2] * $multiplier);
                    $revision['estimativa_custo'] = "R$ {$minCost} - R$ {$maxCost}";
                }
            }
        }

        $article->content = $content;
        $article->save();
    }
}
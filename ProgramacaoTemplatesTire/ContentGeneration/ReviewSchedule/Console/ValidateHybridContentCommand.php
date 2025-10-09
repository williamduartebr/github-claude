<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class ValidateHybridContentCommand extends Command
{
    protected $signature = 'review-schedule:validate-hybrids {--show-problems : Exibir detalhes dos problemas}';
    protected $description = 'Valida conteúdo específico de veículos híbridos';

    public function handle(): int
    {
        $articles = Article::where('template', 'review_schedule_hybrid')
            ->get();

        if ($articles->isEmpty()) {
            $this->info('Nenhum artigo híbrido encontrado.');
            return self::SUCCESS;
        }

        $this->info("🔍 Analisando {$articles->count()} artigos híbridos...");

        $problems = [
            'cronogramas_identicos' => [],
            'servicos_inadequados' => [],
            'falta_especializacao' => [],
            'custos_incorretos' => []
        ];

        foreach ($articles as $article) {
            $this->analyzeHybridArticle($article, $problems);
        }

        $this->displayHybridResults($problems);

        return self::SUCCESS;
    }

    private function analyzeHybridArticle(Article $article, array &$problems): void
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

            // Verificar serviços específicos
            $hasHybridServices = false;
            $hasInappropriateServices = false;
            
            foreach ($schedules as $revision) {
                $services = implode(' ', $revision['servicos_principais'] ?? []);
                
                // Verificar se tem serviços híbridos específicos
                if (str_contains($services, 'híbrido') || 
                    str_contains($services, 'bateria') || 
                    str_contains($services, 'regenerativo') ||
                    str_contains($services, 'e-CVT') ||
                    str_contains($services, 'alta tensão')) {
                    $hasHybridServices = true;
                }
                
                // Verificar serviços inadequados
                if (str_contains($services, 'ar-condicionado') ||
                    str_contains($services, 'Diagnóstico básico dos sistemas elétricos') ||
                    str_contains($services, 'Verificação minuciosa')) {
                    $hasInappropriateServices = true;
                }
            }
            
            if (!$hasHybridServices) {
                $problems['falta_especializacao'][] = $slug;
            }
            
            if ($hasInappropriateServices) {
                $problems['servicos_inadequados'][] = $slug;
            }
        }

        // Verificar custos (híbridos devem ser mais caros que convencionais)
        if (!empty($content['visao_geral_revisoes'])) {
            $costs = [];
            foreach ($content['visao_geral_revisoes'] as $revision) {
                $cost = $revision['estimativa_custo'] ?? '';
                // Extrair valor mínimo
                if (preg_match('/R\$ (\d+)/', $cost, $matches)) {
                    $costs[] = (int)$matches[1];
                }
            }
            
            if (!empty($costs) && max($costs) < 800) {
                $problems['custos_incorretos'][] = $slug;
            }
        }
    }

    private function displayHybridResults(array $problems): void
    {
        $totalProblems = array_sum(array_map('count', $problems));

        if ($totalProblems === 0) {
            $this->info('✅ Todos os artigos híbridos estão corretos!');
            return;
        }

        $this->warn("⚠️  Encontrados {$totalProblems} problemas em híbridos:");

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

        if (!empty($problems['servicos_inadequados'])) {
            $count = count($problems['servicos_inadequados']);
            $this->error("❌ Serviços inadequados para híbridos: {$count} artigos");
        }

        if (!empty($problems['falta_especializacao'])) {
            $count = count($problems['falta_especializacao']);
            $this->error("❌ Falta especialização híbrida: {$count} artigos");
        }

        if (!empty($problems['custos_incorretos'])) {
            $count = count($problems['custos_incorretos']);
            $this->error("❌ Custos inadequados para híbridos: {$count} artigos");
        }

        $this->newLine();
        $this->info('💡 COMANDO DE CORREÇÃO:');
        $this->line('   php artisan review-schedule:fix-hybrid-detailed --limit=500 --force');
    }
}
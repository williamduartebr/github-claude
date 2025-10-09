<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;



use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class ValidateMotorcycleContentCommand extends Command
{
    protected $signature = 'review-schedule:validate-motorcycles {--show-problems : Exibir detalhes dos problemas}';
    protected $description = 'Valida conteúdo específico de motocicletas';

    public function handle(): int
    {
        $articles = Article::where('template', 'review_schedule_motorcycle')
            ->get();

        if ($articles->isEmpty()) {
            $this->info('Nenhum artigo de motocicleta encontrado.');
            return self::SUCCESS;
        }

        $this->info("🔍 Analisando {$articles->count()} artigos de motocicletas...");

        $problems = [
            'ar_condicionado' => [],
            'arrefecimento' => [],
            'intervalos_incorretos' => [],
            'servicos_genericos' => []
        ];

        foreach ($articles as $article) {
            $this->analyzeArticle($article, $problems);
        }

        $this->displayResults($problems);

        return self::SUCCESS;
    }

    private function analyzeArticle(Article $article, array &$problems): void
    {
        $content = $article->content;
        $slug = $article->slug;

        // Verificar cronograma detalhado
        if (!empty($content['cronograma_detalhado'])) {
            foreach ($content['cronograma_detalhado'] as $revision) {
                $services = implode(' ', $revision['servicos_principais'] ?? []);
                
                // Problemas específicos
                if (str_contains($services, 'ar-condicionado') || str_contains($services, 'ar condicionado')) {
                    $problems['ar_condicionado'][] = $slug;
                }
                
                if (str_contains($services, 'arrefecimento') || str_contains($services, 'radiador')) {
                    $problems['arrefecimento'][] = $slug;
                }

                // Verificar intervalos (primeira revisão deve ser 1.000 km)
                $intervalo = $revision['intervalo'] ?? '';
                if ($revision['numero_revisao'] == 1 && !str_contains($intervalo, '1.000')) {
                    $problems['intervalos_incorretos'][] = $slug;
                }
            }
        }

        // Verificar se tem serviços muito genéricos
        if (!empty($content['cronograma_detalhado'])) {
            $allServices = [];
            foreach ($content['cronograma_detalhado'] as $revision) {
                $allServices = array_merge($allServices, $revision['servicos_principais'] ?? []);
            }
            
            $genericCount = 0;
            foreach ($allServices as $service) {
                if (str_contains($service, 'Verificação minuciosa') || 
                    str_contains($service, 'Diagnóstico básico') ||
                    str_contains($service, 'Inspeção detalhada dos pneumáticos')) {
                    $genericCount++;
                }
            }
            
            if ($genericCount > 10) { // Muitos serviços genéricos
                $problems['servicos_genericos'][] = $slug;
            }
        }
    }

    private function displayResults(array $problems): void
    {
        $totalProblems = array_sum(array_map('count', $problems));

        if ($totalProblems === 0) {
            $this->info('✅ Todos os artigos de motocicletas estão corretos!');
            return;
        }

        $this->warn("⚠️  Encontrados {$totalProblems} problemas:");

        if (!empty($problems['ar_condicionado'])) {
            $count = count($problems['ar_condicionado']);
            $this->error("❌ Ar-condicionado em motos: {$count} artigos");
            if ($this->option('show-problems')) {
                foreach ($problems['ar_condicionado'] as $slug) {
                    $this->line("   - {$slug}");
                }
            }
        }

        if (!empty($problems['arrefecimento'])) {
            $count = count($problems['arrefecimento']);
            $this->error("❌ Sistema de arrefecimento inadequado: {$count} artigos");
        }

        if (!empty($problems['intervalos_incorretos'])) {
            $count = count($problems['intervalos_incorretos']);
            $this->error("❌ Intervalos incorretos (1ª revisão): {$count} artigos");
        }

        if (!empty($problems['servicos_genericos'])) {
            $count = count($problems['servicos_genericos']);
            $this->error("❌ Serviços muito genéricos: {$count} artigos");
        }

        $this->newLine();
        $this->info('💡 COMANDO DE CORREÇÃO:');
        $this->line('   php artisan review-schedule:fix-motorcycle-detailed --limit=500 --force');
    }
}
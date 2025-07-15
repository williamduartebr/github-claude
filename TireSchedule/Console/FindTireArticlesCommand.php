<?php

namespace Src\ContentGeneration\TireSchedule\Console;

use Illuminate\Console\Command;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;

class FindTireArticlesCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     */
    protected $signature = 'find-tire-articles';

    /**
     * Descrição do comando.
     */
    protected $description = 'Encontra onde estão os 969 artigos de pneus';

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $this->info('🔍 Buscando artigos de pneus...');
        $this->line('');

        // Testar diferentes combinações de filtros
        $queries = [
            'domain = when_to_change_tires' => [
                'domain' => 'when_to_change_tires'
            ],
            'template = when_to_change_tires' => [
                'template' => 'when_to_change_tires'
            ],
            'domain = when_to_change_tires + status = draft' => [
                'domain' => 'when_to_change_tires',
                'status' => 'draft'
            ],
            'template = when_to_change_tires + status = draft' => [
                'template' => 'when_to_change_tires',
                'status' => 'draft'
            ],
            'domain = when_to_change_tires (qualquer status)' => [
                'domain' => 'when_to_change_tires'
            ],
            'template = when_to_change_tires (qualquer status)' => [
                'template' => 'when_to_change_tires'
            ],
            'título contém "quando-trocar-pneus"' => []
        ];

        $results = [];

        foreach ($queries as $description => $conditions) {
            try {
                if (empty($conditions)) {
                    // Query especial para título
                    $count = TempArticle::where('slug', 'like', 'quando-trocar-pneus%')->count();
                } else {
                    $query = TempArticle::query();
                    foreach ($conditions as $field => $value) {
                        $query->where($field, $value);
                    }
                    $count = $query->count();
                }
                
                $results[] = [$description, $count, $count > 900 ? '🎯' : ($count > 0 ? '⚠️' : '❌')];
            } catch (\Exception $e) {
                $results[] = [$description, 'ERRO: ' . $e->getMessage(), '❌'];
            }
        }

        $this->table(['Query', 'Resultados', 'Status'], $results);

        // Verificar alguns exemplos
        $this->line('');
        $this->info('📋 Exemplos de artigos encontrados:');

        $sampleArticles = TempArticle::where('slug', 'like', 'quando-trocar-pneus%')
            ->limit(5)
            ->get(['slug', 'domain', 'template', 'status', 'title']);

        if ($sampleArticles->isNotEmpty()) {
            $sampleData = [];
            foreach ($sampleArticles as $article) {
                $sampleData[] = [
                    substr($article->slug, 0, 40) . '...',
                    $article->domain ?? 'NULL',
                    $article->template ?? 'NULL',
                    $article->status ?? 'NULL'
                ];
            }
            $this->table(['Slug', 'Domain', 'Template', 'Status'], $sampleData);
        } else {
            $this->warn('❌ Nenhum artigo de exemplo encontrado!');
        }

        // Verificar distribuição por status
        $this->line('');
        $this->info('📊 Distribuição por status (slug like quando-trocar-pneus%):');
        
        try {
            $statusCounts = TempArticle::where('slug', 'like', 'quando-trocar-pneus%')
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get();

            $statusData = [];
            foreach ($statusCounts as $statusCount) {
                $statusData[] = [
                    $statusCount->status ?? 'NULL',
                    $statusCount->count
                ];
            }
            $this->table(['Status', 'Quantidade'], $statusData);
        } catch (\Exception $e) {
            $this->error('❌ Erro ao buscar status: ' . $e->getMessage());
        }

        // Verificar distribuição por domain
        $this->line('');
        $this->info('📊 Distribuição por domain (slug like quando-trocar-pneus%):');
        
        try {
            $domainCounts = TempArticle::where('slug', 'like', 'quando-trocar-pneus%')
                ->selectRaw('domain, count(*) as count')
                ->groupBy('domain')
                ->get();

            $domainData = [];
            foreach ($domainCounts as $domainCount) {
                $domainData[] = [
                    $domainCount->domain ?? 'NULL',
                    $domainCount->count
                ];
            }
            $this->table(['Domain', 'Quantidade'], $domainData);
        } catch (\Exception $e) {
            $this->error('❌ Erro ao buscar domains: ' . $e->getMessage());
        }

        // Verificar correções existentes para confirmar os slugs
        $this->line('');
        $this->info('🔍 Verificando correções existentes...');
        
        $correctionSlugs = ArticleCorrection::whereIn('correction_type', [
            ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
            ArticleCorrection::TYPE_TITLE_YEAR_FIX
        ])->distinct('article_slug')->pluck('article_slug');

        $this->info("📊 Slugs únicos nas correções: " . $correctionSlugs->count());

        // Verificar se os slugs das correções existem nos TempArticles
        $existingArticles = TempArticle::whereIn('slug', $correctionSlugs->toArray())->count();
        $this->info("📊 Artigos correspondentes encontrados: {$existingArticles}");

        if ($existingArticles != $correctionSlugs->count()) {
            $missingCount = $correctionSlugs->count() - $existingArticles;
            $this->warn("⚠️ {$missingCount} correções referenciam artigos que não existem mais!");
        }

        // Recomendação baseada nos resultados
        $this->line('');
        $this->info('💡 Recomendações:');
        
        $maxResult = collect($results)->where(2, '🎯')->first();
        if ($maxResult) {
            $this->info("✅ Use a query: {$maxResult[0]} (encontrou {$maxResult[1]} artigos)");
        } else {
            $this->warn('⚠️ Nenhuma query encontrou mais de 900 artigos');
            $this->info('🔍 Verificar se os artigos foram movidos ou removidos');
        }

        return Command::SUCCESS;
    }
}
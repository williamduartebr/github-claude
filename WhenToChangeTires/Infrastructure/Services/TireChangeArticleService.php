<?php

namespace Src\ContentGeneration\WhenToChangeTires\Infrastructure\Services;

use Src\ContentGeneration\WhenToChangeTires\Domain\Repositories\TireChangeArticleRepositoryInterface;
use Src\ContentGeneration\WhenToChangeTires\Domain\Entities\TireChangeArticle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TireChangeArticleService
{
    public function __construct(
        protected TireChangeArticleRepositoryInterface $repository
    ) {}

    /**
     * Obter dashboard de estatísticas
     */
    public function getDashboardStats(): array
    {
        $stats = $this->repository->getStatistics();

        return [
            'overview' => [
                'total_articles' => $stats['total_articles'],
                'created_today' => $stats['recent_activity']['created_today'],
                'created_this_week' => $stats['recent_activity']['created_this_week'],
                'average_quality' => round($stats['quality_metrics']['average_score'] ?? 0, 1)
            ],
            'status_distribution' => $stats['by_status'],
            'top_makes' => array_slice($stats['by_make'], 0, 5, true),
            'quality_overview' => [
                'high_quality' => $stats['quality_metrics']['high_quality'],
                'needs_improvement' => $stats['quality_metrics']['needs_improvement'],
                'claude_enhanced' => $stats['quality_metrics']['claude_enhanced']
            ],
            'recent_trends' => $this->getRecentTrends()
        ];
    }

    /**
     * Obter tendências recentes
     */
    protected function getRecentTrends(): array
    {
        $lastSevenDays = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = TireChangeArticle::whereDate('created_at', $date)->count();

            $lastSevenDays[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count
            ];
        }

        return $lastSevenDays;
    }

    /**
     * Preparar artigos para transferência
     */
    public function prepareForTransfer(array $articleIds): array
    {
        $articles = TireChangeArticle::whereIn('id', $articleIds)->get();
        $prepared = [];
        $errors = [];

        foreach ($articles as $article) {
            try {
                if ($article->generation_status !== 'claude_enhanced') {
                    $errors[] = "Artigo {$article->id} não está pronto para transferência";
                    continue;
                }

                if ($article->content_score < 7.0) {
                    $errors[] = "Artigo {$article->id} tem score baixo ({$article->content_score})";
                    continue;
                }

                $article->markAsReadyForTransfer();
                $prepared[] = $article->id;
            } catch (\Exception $e) {
                $errors[] = "Erro preparando artigo {$article->id}: " . $e->getMessage();
            }
        }

        Log::info("Artigos preparados para transferência", [
            'prepared_count' => count($prepared),
            'error_count' => count($errors)
        ]);

        return [
            'prepared' => $prepared,
            'errors' => $errors
        ];
    }

    /**
     * Executar limpeza de artigos antigos
     */
    public function cleanupOldArticles(int $daysOld = 90): array
    {
        $cutoffDate = now()->subDays($daysOld);

        // Contar artigos que serão deletados
        $toDelete = TireChangeArticle::where('created_at', '<', $cutoffDate)
            ->where('generation_status', 'error')
            ->count();

        if ($toDelete === 0) {
            return [
                'deleted' => 0,
                'message' => 'Nenhum artigo antigo encontrado para limpeza'
            ];
        }

        // Deletar apenas artigos com erro
        $deleted = TireChangeArticle::where('created_at', '<', $cutoffDate)
            ->where('generation_status', 'error')
            ->delete();

        Log::info("Limpeza de artigos antigos executada", [
            'deleted_count' => $deleted,
            'cutoff_date' => $cutoffDate->format('Y-m-d')
        ]);

        return [
            'deleted' => $deleted,
            'message' => "Deletados {$deleted} artigos com erro anteriores a " . $cutoffDate->format('d/m/Y')
        ];
    }

    /**
     * Validar integridade dos artigos
     */
    public function validateArticleIntegrity(): array
    {
        $issues = [];

        // Verificar artigos sem conteúdo
        $emptyContent = TireChangeArticle::whereNull('article_content')
            ->orWhere('article_content', '')
            ->count();

        if ($emptyContent > 0) {
            $issues[] = "{$emptyContent} artigos sem conteúdo";
        }

        // Verificar slugs duplicados
        $duplicateSlugs = TireChangeArticle::selectRaw('slug, COUNT(*) as count')
            ->groupBy('slug')
            ->having('count', '>', 1)
            ->count();

        if ($duplicateSlugs > 0) {
            $issues[] = "{$duplicateSlugs} slugs duplicados";
        }

        // Verificar artigos com score muito baixo
        $lowScore = TireChangeArticle::where('content_score', '<', 4.0)->count();

        if ($lowScore > 0) {
            $issues[] = "{$lowScore} artigos com score muito baixo";
        }

        return [
            'has_issues' => !empty($issues),
            'issues' => $issues,
            'total_articles_checked' => TireChangeArticle::count()
        ];
    }

    /**
     * Obter relatório de produção
     */
    public function getProductionReport(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $articles = TireChangeArticle::whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'totals' => [
                'articles_created' => $articles->count(),
                'total_words' => $articles->sum(function ($article) {
                    return str_word_count(strip_tags($article->article_content ?? ''));
                }),
                'average_score' => round($articles->avg('content_score'), 2)
            ],
            'by_status' => $articles->groupBy('generation_status')->map->count(),
            'by_make' => $articles->groupBy('make')->map->count(),
            'daily_production' => $this->getDailyProduction($articles, $startDate, $endDate)
        ];
    }

    /**
     * Obter produção diária
     */
    protected function getDailyProduction(Collection $articles, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $dailyStats = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dayArticles = $articles->filter(function ($article) use ($current) {
                return $article->created_at->format('Y-m-d') === $current->format('Y-m-d');
            });

            $dailyStats[$current->format('Y-m-d')] = [
                'count' => $dayArticles->count(),
                'average_score' => $dayArticles->count() > 0 ? round($dayArticles->avg('content_score'), 2) : 0
            ];

            $current->addDay();
        }

        return $dailyStats;
    }
}

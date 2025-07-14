<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;

class PunctuationStatsCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:punctuation-stats 
                           {--template= : Filtrar por template específico}
                           {--detailed : Mostrar estatísticas detalhadas}
                           {--export= : Exportar relatório para arquivo}
                           {--problems : Listar artigos com problemas confirmados}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Exibe estatísticas detalhadas do sistema de correção de pontuação (MongoDB compatível)';

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $this->info('📊 Relatório do Sistema de Correção de Pontuação v2.1');
        $this->line('=======================================================');
        $this->line('');

        try {
            // Estatísticas gerais
            $this->showGeneralStats();

            // Estatísticas por template se solicitado
            if ($this->option('template')) {
                $this->showTemplateStats($this->option('template'));
            }

            // Estatísticas detalhadas
            if ($this->option('detailed')) {
                $this->showDetailedStats();
            }

            // Listar problemas se solicitado
            if ($this->option('problems')) {
                $this->showProblemsFound();
            }

            // Exportar se solicitado
            if ($this->option('export')) {
                $this->exportReport();
            }
        } catch (\Exception $e) {
            $this->error('Erro ao gerar estatísticas: ' . $e->getMessage());
            $this->line('');
            $this->info('💡 Isso pode acontecer se o sistema ainda não foi inicializado.');
            $this->info('   Execute primeiro: php artisan articles:analyze-punctuation --limit=5 --dry-run');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Estatísticas gerais - MongoDB compatível
     */
    protected function showGeneralStats()
    {
        $this->info('📈 VISÃO GERAL');
        $this->line('=============');

        try {
            // Contagem segura de artigos
            $totalArticles = $this->safeCount(Article::where('status', 'published'));

            // Estatísticas de correções com verificação de existência
            $stats = $this->getDetailedStats();

            $this->table(['Métrica', 'Quantidade', 'Percentual'], [
                ['Total de artigos publicados', number_format($totalArticles), '100%'],
                ['Análises pendentes', $stats['pending_analysis'], $this->percentage($stats['pending_analysis'], $totalArticles)],
                ['Análises concluídas', $stats['completed_analysis'], $this->percentage($stats['completed_analysis'], $totalArticles)],
                ['Problemas confirmados', $stats['needs_correction'], $this->percentage($stats['needs_correction'], $totalArticles)],
                ['Correções pendentes', $stats['pending_fixes'], '-'],
                ['Correções aplicadas', $stats['completed_fixes'], '-'],
                ['Sem alterações necessárias', $stats['no_changes'], $this->percentage($stats['no_changes'], $stats['total_processed'])],
                ['Falhas', $stats['failed'], $this->percentage($stats['failed'], $stats['total_processed'])]
            ]);
        } catch (\Exception $e) {
            $this->warn('⚠️ Erro ao obter estatísticas gerais: ' . $e->getMessage());
            $this->info('Sistema provavelmente ainda não foi inicializado.');
        }

        $this->line('');
    }

    /**
     * Estatísticas por template - MongoDB compatível
     */
    protected function showTemplateStats($template)
    {
        $this->info("📋 ESTATÍSTICAS POR TEMPLATE: {$template}");
        $this->line('=====================================');

        try {
            // Contar artigos do template de forma segura
            $templateArticles = $this->safeCount(
                Article::where('status', 'published')->where('template', $template)
            );

            if ($templateArticles === 0) {
                $this->warn("Nenhum artigo encontrado para o template: {$template}");
                return;
            }

            // Buscar análises do template
            $analyzed = $this->countAnalysesByTemplate($template);
            $problems = $this->countProblemsByTemplate($template);
            $fixed = $this->countFixesByTemplate($template);

            $this->table(['Métrica', 'Quantidade'], [
                ['Artigos do template', number_format($templateArticles)],
                ['Analisados', $analyzed],
                ['Com problemas', $problems],
                ['Corrigidos', $fixed],
                ['Taxa de problemas', $this->percentage($problems, $analyzed)]
            ]);
        } catch (\Exception $e) {
            $this->error('Erro ao obter estatísticas do template: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Estatísticas detalhadas
     */
    protected function showDetailedStats()
    {
        $this->info('🔍 ESTATÍSTICAS DETALHADAS');
        $this->line('=========================');

        try {
            // Problemas por tipo
            $this->showProblemTypes();

            // Estatísticas por prioridade  
            $this->showPriorityStats();

            // Estatísticas temporais
            $this->showTemporalStats();
        } catch (\Exception $e) {
            $this->error('Erro ao gerar estatísticas detalhadas: ' . $e->getMessage());
        }
    }

    /**
     * Tipos de problemas encontrados - MongoDB compatível
     */
    protected function showProblemTypes()
    {
        $this->info('🏷️ TIPOS DE PROBLEMAS ENCONTRADOS:');

        try {
            $problemTypes = [
                'ponto_antes_enumeracao' => 'Pontos antes de enumerações',
                'ponto_antes_ou' => 'Pontos antes de "ou"',
                'fragmento_preposicao' => 'Fragmentos com preposições',
                'verbo_apos_ponto' => 'Verbos após pontos inadequados',
                'maiuscula_apos_parenteses' => 'Maiúsculas após parênteses',
                'muitos_fragmentos' => 'Muitos fragmentos',
                'conectivos_apos_ponto' => 'Conectivos após pontos'
            ];

            $typeStats = [];

            // Buscar análises concluídas
            $analyses = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->get();

            foreach ($problemTypes as $type => $description) {
                $count = $analyses->filter(function ($correction) use ($type) {
                    $issues = data_get($correction->original_data, 'local_analysis.issues', []);
                    return in_array($type, $issues);
                })->count();

                if ($count > 0) {
                    $typeStats[] = [$description, $count];
                }
            }

            if (!empty($typeStats)) {
                $this->table(['Tipo de Problema', 'Ocorrências'], $typeStats);
            } else {
                $this->line('Nenhum dado de tipos de problemas disponível.');
            }
        } catch (\Exception $e) {
            $this->error('Erro ao analisar tipos de problemas: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Estatísticas por prioridade
     */
    protected function showPriorityStats()
    {
        $this->info('🔥 DISTRIBUIÇÃO POR PRIORIDADE:');

        try {
            $queue = $this->getCorrectionQueue();

            $total = $queue['high_priority']->count() + $queue['medium_priority']->count() + $queue['low_priority']->count();

            if ($total > 0) {
                $this->table(['Prioridade', 'Quantidade', 'Percentual'], [
                    ['🔴 Alta', $queue['high_priority']->count(), $this->percentage($queue['high_priority']->count(), $total)],
                    ['🟡 Média', $queue['medium_priority']->count(), $this->percentage($queue['medium_priority']->count(), $total)],
                    ['🟢 Baixa', $queue['low_priority']->count(), $this->percentage($queue['low_priority']->count(), $total)]
                ]);
            } else {
                $this->line('Nenhuma correção pendente encontrada.');
            }
        } catch (\Exception $e) {
            $this->error('Erro ao analisar prioridades: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Estatísticas temporais - MongoDB compatível
     */
    protected function showTemporalStats()
    {
        $this->info('📅 ESTATÍSTICAS TEMPORAIS:');

        try {
            $today = $this->safeCount(ArticleCorrection::whereDate('created_at', today()));
            $thisWeek = $this->safeCount(ArticleCorrection::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]));
            $thisMonth = $this->safeCount(ArticleCorrection::whereMonth('created_at', now()->month));

            $this->table(['Período', 'Análises Criadas'], [
                ['Hoje', $today],
                ['Esta semana', $thisWeek],
                ['Este mês', $thisMonth]
            ]);
        } catch (\Exception $e) {
            $this->error('Erro ao calcular estatísticas temporais: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Lista artigos com problemas confirmados
     */
    protected function showProblemsFound()
    {
        $this->info('⚠️ ARTIGOS COM PROBLEMAS CONFIRMADOS:');
        $this->line('===================================');

        try {
            $problemArticles = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->where('correction_data.needs_correction', true)
                ->orderBy('created_at', 'asc')
                ->take(20)
                ->get();

            if ($problemArticles->isEmpty()) {
                $this->line('Nenhum artigo com problemas confirmados encontrado.');
                return;
            }

            $tableData = [];
            foreach ($problemArticles as $correction) {
                $priority = data_get($correction->correction_data, 'correction_priority', 'medium');
                $confidence = data_get($correction->correction_data, 'confidence_level', 'medium');
                $problems = count(data_get($correction->correction_data, 'problems_found', []));

                $priorityIcon = [
                    'high' => '🔴',
                    'medium' => '🟡',
                    'low' => '🟢'
                ][$priority] ?? '⚪';

                $tableData[] = [
                    $priorityIcon . ' ' . $correction->article_slug,
                    ucfirst($confidence),
                    $problems,
                    $correction->created_at->format('d/m H:i')
                ];
            }

            $this->table(['Artigo', 'Confiança', 'Problemas', 'Analisado'], $tableData);

            if ($problemArticles->count() === 20) {
                $this->line('... (mostrando apenas os primeiros 20)');
            }
        } catch (\Exception $e) {
            $this->error('Erro ao listar problemas: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Exporta relatório para arquivo
     */
    protected function exportReport()
    {
        $filename = $this->option('export');

        $this->info("📄 Exportando relatório para: {$filename}");

        try {
            $stats = $this->getDetailedStats();
            $report = [
                'generated_at' => now()->toISOString(),
                'system_version' => '2.1',
                'statistics' => $stats,
                'summary' => [
                    'total_articles' => $this->safeCount(Article::where('status', 'published')),
                    'analysis_completion_rate' => $this->percentage($stats['completed_analysis'], $stats['pending_analysis'] + $stats['completed_analysis']),
                    'problem_detection_rate' => $this->percentage($stats['needs_correction'], $stats['completed_analysis']),
                    'correction_success_rate' => $this->percentage($stats['completed_fixes'], $stats['needs_correction'])
                ]
            ];

            file_put_contents($filename, json_encode($report, JSON_PRETTY_PRINT));
            $this->info("✅ Relatório exportado com sucesso!");
        } catch (\Exception $e) {
            $this->error('Erro ao exportar relatório: ' . $e->getMessage());
        }
    }

    // ========================================
    // MÉTODOS AUXILIARES MONGODB COMPATÍVEIS
    // ========================================

    /**
     * Contagem segura compatível com MongoDB
     */
    protected function safeCount($query)
    {
        try {
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obter estatísticas detalhadas de forma segura
     */
    protected function getDetailedStats()
    {
        try {
            // Usar métodos diretos para evitar problemas com MongoDB
            $pendingAnalysis = $this->safeCount(
                ArticleCorrection::where('status', ArticleCorrection::STATUS_PENDING)
                    ->where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
            );

            $completedAnalysis = $this->safeCount(
                ArticleCorrection::where('status', ArticleCorrection::STATUS_COMPLETED)
                    ->where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
            );

            $needsCorrection = $this->safeCount(
                ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
                    ->where('status', ArticleCorrection::STATUS_COMPLETED)
                    ->where('correction_data.needs_correction', true)
            );

            $pendingFixes = $this->safeCount(
                ArticleCorrection::where('status', ArticleCorrection::STATUS_PENDING)
                    ->where('correction_type', ArticleCorrection::TYPE_INTRODUCTION_FIX)
            );

            $completedFixes = $this->safeCount(
                ArticleCorrection::where('status', ArticleCorrection::STATUS_COMPLETED)
                    ->where('correction_type', ArticleCorrection::TYPE_INTRODUCTION_FIX)
            );

            $noChanges = $this->safeCount(
                ArticleCorrection::where('status', ArticleCorrection::STATUS_NO_CHANGES ?? 'no_changes')
            );

            $failed = $this->safeCount(
                ArticleCorrection::where('status', ArticleCorrection::STATUS_FAILED)
            );

            $totalProcessed = $completedAnalysis + $noChanges + $failed;

            return [
                'pending_analysis' => $pendingAnalysis,
                'completed_analysis' => $completedAnalysis,
                'needs_correction' => $needsCorrection,
                'pending_fixes' => $pendingFixes,
                'completed_fixes' => $completedFixes,
                'no_changes' => $noChanges,
                'failed' => $failed,
                'total_processed' => $totalProcessed
            ];
        } catch (\Exception $e) {
            // Retornar estrutura padrão em caso de erro
            return [
                'pending_analysis' => 0,
                'completed_analysis' => 0,
                'needs_correction' => 0,
                'pending_fixes' => 0,
                'completed_fixes' => 0,
                'no_changes' => 0,
                'failed' => 0,
                'total_processed' => 0
            ];
        }
    }

    /**
     * Obter fila de correções de forma segura
     */
    protected function getCorrectionQueue()
    {
        try {
            $analyses = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->where('correction_data.needs_correction', true)
                ->orderBy('created_at', 'asc')
                ->get();

            return [
                'high_priority' => $analyses->filter(function ($analysis) {
                    return data_get($analysis->correction_data, 'correction_priority', 'medium') === 'high';
                }),
                'medium_priority' => $analyses->filter(function ($analysis) {
                    return data_get($analysis->correction_data, 'correction_priority', 'medium') === 'medium';
                }),
                'low_priority' => $analyses->filter(function ($analysis) {
                    return data_get($analysis->correction_data, 'correction_priority', 'medium') === 'low';
                })
            ];
        } catch (\Exception $e) {
            return [
                'high_priority' => collect(),
                'medium_priority' => collect(),
                'low_priority' => collect()
            ];
        }
    }

    /**
     * Contar análises por template
     */
    protected function countAnalysesByTemplate($template)
    {
        try {
            return ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
                ->get()
                ->filter(function ($analysis) use ($template) {
                    return data_get($analysis->original_data, 'template') === $template;
                })
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Contar problemas por template
     */
    protected function countProblemsByTemplate($template)
    {
        try {
            return ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->where('correction_data.needs_correction', true)
                ->get()
                ->filter(function ($analysis) use ($template) {
                    return data_get($analysis->original_data, 'template') === $template;
                })
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Contar correções por template
     */
    protected function countFixesByTemplate($template)
    {
        try {
            return ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_INTRODUCTION_FIX)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->get()
                ->filter(function ($fix) use ($template) {
                    return data_get($fix->original_data, 'template') === $template;
                })
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calcula percentual
     */
    protected function percentage($part, $total)
    {
        if ($total == 0) return '0%';
        return round(($part / $total) * 100, 1) . '%';
    }
}

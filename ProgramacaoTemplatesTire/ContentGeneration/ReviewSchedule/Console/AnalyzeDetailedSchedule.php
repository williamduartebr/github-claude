<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Eloquent\ReviewScheduleArticle;

class AnalyzeDetailedSchedule extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'review-schedule:analyze-detailed-schedule 
                            {--limit=1000 : Limit number of articles to analyze}
                            {--output=detailed_schedule_analysis.log : Output file for results}
                            {--only-broken : Show only articles with schedule issues}';

    /**
     * The console command description.
     */
    protected $description = 'Analyze cronograma_detalhado structure and completeness';

    private array $scheduleProblems = [];
    private array $statistics = [
        'total_analyzed' => 0,
        'total_with_schedule' => 0,
        'total_without_schedule' => 0,
        'incomplete_schedules' => 0,
        'complete_schedules' => 0,
        'schedule_issues' => [],
        'revision_count_distribution' => [],
        'missing_fields_count' => []
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int)$this->option('limit');
        $outputFile = $this->option('output');
        $onlyBroken = $this->option('only-broken');

        $this->info('🔍 Iniciando análise detalhada do cronograma_detalhado...');

        $articles = ReviewScheduleArticle::limit($limit)->get();
        
        $this->info("📊 Analisando cronograma_detalhado de {$articles->count()} artigos...");
        
        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->start();

        foreach ($articles as $article) {
            $this->analyzeArticleSchedule($article);
            $this->statistics['total_analyzed']++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->generateDetailedReport($outputFile);
        $this->displayDetailedStatistics();

        if ($onlyBroken && !empty($this->scheduleProblems)) {
            $this->displayBrokenSchedules();
        }
    }

    private function analyzeArticleSchedule($article): void
    {
        $content = $article->content;
        
        if (!is_array($content)) {
            // Tentar decodificar se for string
            if (is_string($content)) {
                $content = json_decode($content, true);
            }
            
            if (!is_array($content)) {
                $this->recordScheduleProblem($article, 'INVALID_CONTENT_TYPE', 'Conteúdo não é um array válido');
                return;
            }
        }

        // Verificar se cronograma_detalhado existe
        if (!isset($content['cronograma_detalhado'])) {
            $this->statistics['total_without_schedule']++;
            $this->recordScheduleProblem($article, 'MISSING_SCHEDULE', 'cronograma_detalhado não encontrado');
            return;
        }

        $this->statistics['total_with_schedule']++;
        $schedule = $content['cronograma_detalhado'];

        // Verificar se é um array
        if (!is_array($schedule)) {
            $this->recordScheduleProblem($article, 'SCHEDULE_NOT_ARRAY', 'cronograma_detalhado não é um array');
            return;
        }

        // Verificar se está vazio
        if (empty($schedule)) {
            $this->recordScheduleProblem($article, 'EMPTY_SCHEDULE', 'cronograma_detalhado está vazio');
            return;
        }

        // Analisar estrutura detalhada do cronograma
        $this->analyzeScheduleStructure($article, $schedule);
    }

    private function analyzeScheduleStructure($article, array $schedule): void
    {
        $revisionCount = count($schedule);
        $issues = [];
        
        // Distribuição de quantidade de revisões
        if (!isset($this->statistics['revision_count_distribution'][$revisionCount])) {
            $this->statistics['revision_count_distribution'][$revisionCount] = 0;
        }
        $this->statistics['revision_count_distribution'][$revisionCount]++;

        // Verificar quantidade mínima de revisões
        if ($revisionCount < 3) {
            $issues[] = "Apenas $revisionCount revisões (mínimo esperado: 3)";
            $this->incrementIssueCount('INSUFFICIENT_REVISIONS');
        }

        // Verificar estrutura de cada revisão
        $requiredFields = [
            'quilometragem' => 'Quilometragem',
            'tempo' => 'Tempo',
            'servicos_principais' => 'Serviços Principais',
            'verificacoes_complementares' => 'Verificações Complementares',
            'custo_estimado' => 'Custo Estimado'
        ];

        foreach ($schedule as $index => $revision) {
            $revisionNumber = $index + 1;
            
            if (!is_array($revision)) {
                $issues[] = "Revisão $revisionNumber: não é um array válido";
                $this->incrementIssueCount('REVISION_NOT_ARRAY');
                continue;
            }

            // Verificar campos obrigatórios
            foreach ($requiredFields as $field => $fieldName) {
                if (!isset($revision[$field])) {
                    $issues[] = "Revisão $revisionNumber: campo '$fieldName' ausente";
                    $this->incrementMissingFieldCount($field);
                }
            }

            // Verificações específicas por campo
            $this->analyzeRevisionFields($revision, $revisionNumber, $issues);
        }

        // Determinar se o cronograma está completo
        if (empty($issues)) {
            $this->statistics['complete_schedules']++;
        } else {
            $this->statistics['incomplete_schedules']++;
            $this->recordScheduleProblem($article, 'INCOMPLETE_SCHEDULE', implode('; ', $issues));
        }
    }

    private function analyzeRevisionFields(array $revision, int $revisionNumber, array &$issues): void
    {
        // Verificar serviços principais
        if (isset($revision['servicos_principais'])) {
            if (!is_array($revision['servicos_principais'])) {
                $issues[] = "Revisão $revisionNumber: 'servicos_principais' não é um array";
                $this->incrementIssueCount('SERVICES_NOT_ARRAY');
            } elseif (count($revision['servicos_principais']) < 2) {
                $serviceCount = count($revision['servicos_principais']);
                $issues[] = "Revisão $revisionNumber: menos de 2 serviços principais ($serviceCount)";
                $this->incrementIssueCount('INSUFFICIENT_SERVICES');
            }
        }

        // Verificar verificações complementares
        if (isset($revision['verificacoes_complementares'])) {
            if (!is_array($revision['verificacoes_complementares'])) {
                $issues[] = "Revisão $revisionNumber: 'verificacoes_complementares' não é um array";
                $this->incrementIssueCount('CHECKS_NOT_ARRAY');
            } elseif (empty($revision['verificacoes_complementares'])) {
                $issues[] = "Revisão $revisionNumber: verificações complementares vazias";
                $this->incrementIssueCount('EMPTY_CHECKS');
            }
        }

        // Verificar quilometragem
        if (isset($revision['quilometragem'])) {
            if (!is_string($revision['quilometragem']) && !is_numeric($revision['quilometragem'])) {
                $issues[] = "Revisão $revisionNumber: quilometragem em formato inválido";
                $this->incrementIssueCount('INVALID_MILEAGE_FORMAT');
            }
        }

        // Verificar tempo
        if (isset($revision['tempo'])) {
            if (!is_string($revision['tempo'])) {
                $issues[] = "Revisão $revisionNumber: tempo em formato inválido";
                $this->incrementIssueCount('INVALID_TIME_FORMAT');
            }
        }

        // Verificar custo estimado
        if (isset($revision['custo_estimado'])) {
            if (!is_string($revision['custo_estimado']) && !is_numeric($revision['custo_estimado'])) {
                $issues[] = "Revisão $revisionNumber: custo estimado em formato inválido";
                $this->incrementIssueCount('INVALID_COST_FORMAT');
            }
        }
    }

    private function recordScheduleProblem($article, string $issueType, string $description): void
    {
        $content = is_array($article->content) ? $article->content : json_decode($article->content, true);
        $vehicleInfo = $content['extracted_entities'] ?? [];
        
        $this->scheduleProblems[] = [
            'id' => $article->_id ?? $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'vehicle' => [
                'marca' => $vehicleInfo['marca'] ?? 'N/A',
                'modelo' => $vehicleInfo['modelo'] ?? 'N/A',
                'ano' => $vehicleInfo['ano'] ?? 'N/A',
                'tipo' => $vehicleInfo['tipo_veiculo'] ?? 'unknown'
            ],
            'issue_type' => $issueType,
            'description' => $description,
            'created_at' => $article->created_at ?? null
        ];

        $this->incrementIssueCount($issueType);
    }

    private function incrementIssueCount(string $issueType): void
    {
        if (!isset($this->statistics['schedule_issues'][$issueType])) {
            $this->statistics['schedule_issues'][$issueType] = 0;
        }
        $this->statistics['schedule_issues'][$issueType]++;
    }

    private function incrementMissingFieldCount(string $field): void
    {
        if (!isset($this->statistics['missing_fields_count'][$field])) {
            $this->statistics['missing_fields_count'][$field] = 0;
        }
        $this->statistics['missing_fields_count'][$field]++;
    }

    private function generateDetailedReport(string $outputFile): void
    {
        $logContent = "=== RELATÓRIO DETALHADO DE CRONOGRAMA_DETALHADO ===\n";
        $logContent .= "Data: " . now()->format('d/m/Y H:i:s') . "\n\n";
        
        $logContent .= "ESTATÍSTICAS GERAIS:\n";
        $logContent .= "- Total analisado: {$this->statistics['total_analyzed']}\n";
        $logContent .= "- Com cronograma: {$this->statistics['total_with_schedule']}\n";
        $logContent .= "- Sem cronograma: {$this->statistics['total_without_schedule']}\n";
        $logContent .= "- Cronogramas completos: {$this->statistics['complete_schedules']}\n";
        $logContent .= "- Cronogramas incompletos: {$this->statistics['incomplete_schedules']}\n\n";
        
        if ($this->statistics['total_with_schedule'] > 0) {
            $completionRate = round(($this->statistics['complete_schedules'] / $this->statistics['total_with_schedule']) * 100, 2);
            $logContent .= "- Taxa de completude: {$completionRate}%\n\n";
        }

        $logContent .= "DISTRIBUIÇÃO DE QUANTIDADE DE REVISÕES:\n";
        ksort($this->statistics['revision_count_distribution']);
        foreach ($this->statistics['revision_count_distribution'] as $count => $articles) {
            $logContent .= "- {$count} revisões: {$articles} artigos\n";
        }

        $logContent .= "\nTIPOS DE PROBLEMAS ENCONTRADOS:\n";
        arsort($this->statistics['schedule_issues']);
        foreach ($this->statistics['schedule_issues'] as $issue => $count) {
            $logContent .= "- {$issue}: {$count}\n";
        }

        $logContent .= "\nCAMPOS MAIS FREQUENTEMENTE AUSENTES:\n";
        arsort($this->statistics['missing_fields_count']);
        foreach ($this->statistics['missing_fields_count'] as $field => $count) {
            $logContent .= "- {$field}: {$count}\n";
        }

        $logContent .= "\n=== ARTIGOS COM PROBLEMAS NO CRONOGRAMA ===\n\n";
        
        foreach ($this->scheduleProblems as $problem) {
            $logContent .= "ID: {$problem['id']}\n";
            $logContent .= "Título: {$problem['title']}\n";
            $logContent .= "Veículo: {$problem['vehicle']['marca']} {$problem['vehicle']['modelo']} {$problem['vehicle']['ano']}\n";
            $logContent .= "Tipo de Problema: {$problem['issue_type']}\n";
            $logContent .= "Descrição: {$problem['description']}\n";
            $logContent .= str_repeat('-', 80) . "\n";
        }

        file_put_contents($outputFile, $logContent);
        
        $this->info("📄 Relatório detalhado salvo em: {$outputFile}");
    }

    private function displayDetailedStatistics(): void
    {
        $this->newLine();
        $this->info('📊 ESTATÍSTICAS DO CRONOGRAMA_DETALHADO:');
        
        $completionRate = $this->statistics['total_with_schedule'] > 0 ? 
            round(($this->statistics['complete_schedules'] / $this->statistics['total_with_schedule']) * 100, 2) : 0;
        
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total Analisado', $this->statistics['total_analyzed']],
                ['Com Cronograma', $this->statistics['total_with_schedule']],
                ['Sem Cronograma', $this->statistics['total_without_schedule']],
                ['Cronogramas Completos', $this->statistics['complete_schedules']],
                ['Cronogramas Incompletos', $this->statistics['incomplete_schedules']],
                ['Taxa de Completude', $completionRate . '%']
            ]
        );

        if (!empty($this->statistics['revision_count_distribution'])) {
            $this->newLine();
            $this->info('📈 DISTRIBUIÇÃO DE REVISÕES:');
            $revisionTable = [];
            ksort($this->statistics['revision_count_distribution']);
            foreach ($this->statistics['revision_count_distribution'] as $count => $articles) {
                $revisionTable[] = ["{$count} revisões", $articles];
            }
            $this->table(['Quantidade', 'Artigos'], $revisionTable);
        }

        if (!empty($this->statistics['schedule_issues'])) {
            $this->newLine();
            $this->info('🔥 PROBLEMAS MAIS COMUNS:');
            $issueTable = [];
            $topIssues = array_slice($this->statistics['schedule_issues'], 0, 10, true);
            foreach ($topIssues as $issue => $count) {
                $issueTable[] = [$issue, $count];
            }
            $this->table(['Tipo de Problema', 'Ocorrências'], $issueTable);
        }

        if (!empty($this->statistics['missing_fields_count'])) {
            $this->newLine();
            $this->info('❌ CAMPOS AUSENTES:');
            $fieldsTable = [];
            foreach ($this->statistics['missing_fields_count'] as $field => $count) {
                $fieldsTable[] = [$field, $count];
            }
            $this->table(['Campo', 'Ausente em'], $fieldsTable);
        }

        if (count($this->scheduleProblems) > 0) {
            $this->newLine();
            $this->warn("⚠️  Encontrados " . count($this->scheduleProblems) . " artigos com problemas no cronograma_detalhado!");
            $this->info("💡 Para corrigir os problemas use:");
            $this->line("   php artisan review-schedule:fix-detailed-schedule --limit=1000 --force");
        } else {
            $this->newLine();
            $this->info("✅ Todos os cronogramas analisados estão estruturados corretamente!");
        }
    }

    private function displayBrokenSchedules(): void
    {
        $this->newLine(2);
        $this->error('🚨 ARTIGOS COM PROBLEMAS NO CRONOGRAMA:');
        
        foreach ($this->scheduleProblems as $index => $problem) {
            $this->newLine();
            $indexNumber = $index + 1;
            $this->line("#$indexNumber - ID: {$problem['id']}");
            $this->line("Veículo: {$problem['vehicle']['marca']} {$problem['vehicle']['modelo']} {$problem['vehicle']['ano']}");
            $this->line("Problema: {$problem['issue_type']}");
            $this->warn("Descrição: {$problem['description']}");
            
            if ($index < count($this->scheduleProblems) - 1) {
                $this->line(str_repeat('-', 50));
            }
        }
    }
}
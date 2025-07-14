<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Src\ArticleGenerator\Domain\Services\WorkingDaysCalculatorService;
use Src\ArticleGenerator\Domain\Services\HumanTimeDistributionService;
use Src\ArticleGenerator\Domain\ValueObjects\WorkingHours;
use Src\ArticleGenerator\Domain\ValueObjects\PublishingPeriod;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

class CreateArticleSchedule extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:create-schedule 
                           {--start-date= : Data inicial para agendamento (formato Y-m-d)}
                           {--end-date= : Data final para agendamento (formato Y-m-d)}
                           {--days=30 : Número de dias úteis para o cronograma}
                           {--min-posts=50 : Mínimo de posts por dia}
                           {--max-posts=80 : Máximo de posts por dia}
                           {--articles-count= : Número específico de artigos para agendar}
                           {--imported-only : Criar cronograma apenas para artigos importados}
                           {--new-only : Criar cronograma apenas para artigos novos}
                           {--export-csv : Exportar cronograma para arquivo CSV}
                           {--export-json : Exportar cronograma para arquivo JSON}
                           {--preview-only : Apenas mostrar preview sem salvar}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Cria cronograma inteligente de publicação baseado em comportamento humano';

    private WorkingDaysCalculatorService $workingDaysCalculator;
    private HumanTimeDistributionService $timeDistribution;
    private WorkingHours $workingHours;

    /**
     * Execute o comando.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Criando cronograma inteligente de publicação...');

        // Inicializar serviços
        $this->initializeServices();

        // Validar e processar opções
        $options = $this->validateAndParseOptions();
        if ($options === null) {
            return Command::FAILURE;
        }

        // Obter artigos disponíveis para análise
        $availableArticles = $this->getAvailableArticles($options);

        // Criar período de publicação
        $publishingPeriod = $this->createPublishingPeriod($options, $availableArticles);

        // Gerar cronograma detalhado
        $schedule = $this->generateDetailedSchedule($publishingPeriod, $options);

        // Exibir resultados
        $this->displaySchedule($schedule, $publishingPeriod, $options);

        // Exportar se solicitado
        if (!$options['preview_only']) {
            $this->handleExports($schedule, $options);
        }

        return Command::SUCCESS;
    }

    /**
     * Inicializa os serviços necessários
     */
    private function initializeServices(): void
    {
        $this->workingHours = new WorkingHours();
        $this->workingDaysCalculator = new WorkingDaysCalculatorService($this->workingHours);
        $this->timeDistribution = new HumanTimeDistributionService($this->workingHours);
    }

    /**
     * Valida e processa as opções do comando
     */
    private function validateAndParseOptions(): ?array
    {
        try {
            // Processar datas
            $startDate = $this->option('start-date') 
                ? Carbon::createFromFormat('Y-m-d', $this->option('start-date'))
                : Carbon::tomorrow();

            $endDate = $this->option('end-date')
                ? Carbon::createFromFormat('Y-m-d', $this->option('end-date'))
                : null;

            $days = (int) $this->option('days');
            $minPosts = (int) $this->option('min-posts');
            $maxPosts = (int) $this->option('max-posts');
            $articlesCount = $this->option('articles-count') ? (int) $this->option('articles-count') : null;

            // Validações básicas
            if ($startDate->lt(Carbon::today())) {
                $this->error('Data inicial não pode ser no passado.');
                return null;
            }

            if ($endDate && $endDate->lt($startDate)) {
                $this->error('Data final deve ser posterior à data inicial.');
                return null;
            }

            if ($days <= 0 || $days > 90) {
                $this->error('Número de dias deve estar entre 1 e 90.');
                return null;
            }

            if ($minPosts <= 0 || $maxPosts <= 0 || $minPosts > $maxPosts) {
                $this->error('Valores de posts por dia inválidos.');
                return null;
            }

            if ($articlesCount && $articlesCount <= 0) {
                $this->error('Quantidade de artigos deve ser maior que zero.');
                return null;
            }

            // Ajustar para próximo dia útil
            $adjustedStartDate = $this->workingDaysCalculator->adjustToNextWorkingDay($startDate);
            
            if (!$adjustedStartDate->eq($startDate)) {
                $this->info("Data ajustada para próximo dia útil: {$adjustedStartDate->format('Y-m-d')}");
            }

            return [
                'start_date' => $adjustedStartDate,
                'end_date' => $endDate,
                'days' => $days,
                'min_posts' => $minPosts,
                'max_posts' => $maxPosts,
                'articles_count' => $articlesCount,
                'imported_only' => $this->option('imported-only'),
                'new_only' => $this->option('new-only'),
                'export_csv' => $this->option('export-csv'),
                'export_json' => $this->option('export-json'),
                'preview_only' => $this->option('preview-only'),
            ];

        } catch (\Exception $e) {
            $this->error("Erro ao validar opções: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Obtém artigos disponíveis para análise
     */
    private function getAvailableArticles(array $options): Collection
    {
        $query = TempArticle::where('status', 'draft');

        if ($options['imported_only']) {
            $query->whereNotNull('original_post_id');
            $this->info('Analisando apenas artigos importados.');
        } elseif ($options['new_only']) {
            $query->whereNull('original_post_id');
            $this->info('Analisando apenas artigos novos.');
        }

        $articles = $query->get();
        
        $this->info("Encontrados {$articles->count()} artigos disponíveis para análise.");
        
        return $articles;
    }

    /**
     * Cria período de publicação baseado nas opções
     */
    private function createPublishingPeriod(array $options, Collection $availableArticles): PublishingPeriod
    {
        if ($options['end_date']) {
            // Usar período específico entre datas
            return new PublishingPeriod(
                $options['start_date'],
                $options['end_date'],
                $options['min_posts'],
                $options['max_posts']
            );
        } elseif ($options['articles_count']) {
            // Criar período baseado na quantidade de artigos especificada
            return PublishingPeriod::createForArticleCount(
                $options['start_date'],
                $options['articles_count'],
                $options['min_posts'],
                $options['max_posts']
            );
        } elseif (!$availableArticles->isEmpty()) {
            // Criar período baseado nos artigos disponíveis
            return PublishingPeriod::createForArticleCount(
                $options['start_date'],
                $availableArticles->count(),
                $options['min_posts'],
                $options['max_posts']
            );
        } else {
            // Criar período baseado apenas nos dias especificados
            return PublishingPeriod::createForDays(
                $options['start_date'],
                $options['days'],
                $options['min_posts'],
                $options['max_posts']
            );
        }
    }

    /**
     * Gera cronograma detalhado
     */
    private function generateDetailedSchedule(PublishingPeriod $publishingPeriod, array $options): array
    {
        $workingDays = $publishingPeriod->getWorkingDaysList();
        $schedule = [];

        $this->info('Gerando cronograma detalhado...');
        $bar = $this->output->createProgressBar(count($workingDays));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->start();

        foreach ($workingDays as $index => $workingDay) {
            $bar->setMessage("Processando {$workingDay->format('Y-m-d')}...");
            
            // Calcular posts para este dia
            $postsForDay = $this->calculatePostsForDay(
                $index,
                count($workingDays),
                $publishingPeriod
            );

            // Gerar horários para o dia
            $daySchedule = $this->generateDaySchedule($workingDay, $postsForDay);

            $schedule[] = [
                'date' => $workingDay,
                'day_name' => $workingDay->format('l'),
                'day_of_week' => $workingDay->dayOfWeekIso,
                'posts_count' => $postsForDay,
                'slots' => $daySchedule,
                'first_post' => !empty($daySchedule) ? $daySchedule[0]['time'] : null,
                'last_post' => !empty($daySchedule) ? end($daySchedule)['time'] : null,
                'peak_hours_count' => count(array_filter($daySchedule, fn($slot) => $slot['is_peak_hour'])),
            ];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        return $schedule;
    }

    /**
     * Calcula quantidade de posts para um dia específico
     */
    private function calculatePostsForDay(int $dayIndex, int $totalDays, PublishingPeriod $publishingPeriod): int
    {
        $minPosts = $publishingPeriod->getMinPostsPerDay();
        $maxPosts = $publishingPeriod->getMaxPostsPerDay();

        // Variação baseada no dia da semana (segunda tem mais posts que sexta)
        $dayOfWeek = $publishingPeriod->getWorkingDaysList()[$dayIndex]->dayOfWeekIso;
        $dayVariation = $this->getDayOfWeekVariation($dayOfWeek);

        // Variação baseada na posição no período (início pode ter mais posts)
        $positionVariation = $this->getPositionVariation($dayIndex, $totalDays);

        // Calcular posts base
        $avgPosts = ($minPosts + $maxPosts) / 2;
        $adjustedPosts = round($avgPosts * $dayVariation * $positionVariation);

        // Garantir que está dentro dos limites
        return max($minPosts, min($maxPosts, $adjustedPosts));
    }

    /**
     * Variação baseada no dia da semana
     */
    private function getDayOfWeekVariation(int $dayOfWeek): float
    {
        $variations = [
            1 => 1.1,  // Segunda - mais posts
            2 => 1.2,  // Terça - pico
            3 => 1.1,  // Quarta - normal+
            4 => 1.0,  // Quinta - normal
            5 => 0.9,  // Sexta - menos posts
        ];

        return $variations[$dayOfWeek] ?? 1.0;
    }

    /**
     * Variação baseada na posição no período
     */
    private function getPositionVariation(int $dayIndex, int $totalDays): float
    {
        if ($totalDays <= 1) return 1.0;

        $position = $dayIndex / ($totalDays - 1); // 0.0 a 1.0

        // Início do período tem um pouco mais de atividade
        if ($position <= 0.2) {
            return 1.05; // 5% a mais no início
        } elseif ($position >= 0.8) {
            return 0.95; // 5% a menos no final
        } else {
            return 1.0; // Normal no meio
        }
    }

    /**
     * Gera cronograma para um dia específico
     */
    private function generateDaySchedule(Carbon $date, int $postsCount): array
    {
        if ($postsCount <= 0) {
            return [];
        }

        // Resetar timestamps para este dia
        $this->timeDistribution->resetUsedTimestamps();

        // Gerar slots temporais
        $slots = $this->timeDistribution->generateDaySchedule($date, $postsCount, 'mixed');

        // Converter para formato de exibição
        $daySchedule = [];
        foreach ($slots as $index => $slot) {
            $scheduledTime = $slot->getScheduledAt();
            
            $daySchedule[] = [
                'slot_number' => $index + 1,
                'time' => $scheduledTime->format('H:i:s'),
                'datetime' => $scheduledTime->format('Y-m-d H:i:s'),
                'hour' => $scheduledTime->hour,
                'is_peak_hour' => $slot->isPeakHour(),
                'is_working_hours' => $this->workingHours->isWithinWorkingHours($date->dayOfWeekIso, $scheduledTime->hour),
                'weight' => $this->workingHours->getHourWeight($scheduledTime->hour),
                'interval_from_previous' => $index > 0 
                    ? $scheduledTime->diffInMinutes($slots[$index - 1]->getScheduledAt())
                    : 0,
            ];
        }

        return $daySchedule;
    }

    /**
     * Exibe cronograma gerado
     */
    private function displaySchedule(array $schedule, PublishingPeriod $publishingPeriod, array $options): void
    {
        $this->info('=== CRONOGRAMA DE PUBLICAÇÃO GERADO ===');

        // Estatísticas gerais
        $this->displayGeneralStatistics($schedule, $publishingPeriod);

        // Resumo por dia da semana
        $this->displayWeeklyBreakdown($schedule);

        // Distribuição por hora
        $this->displayHourlyDistribution($schedule);

        // Detalhes dos primeiros dias (preview)
        $this->displayDailyDetails($schedule, 5);

        // Recomendações
        $this->displayRecommendations($schedule, $publishingPeriod);
    }

    /**
     * Exibe estatísticas gerais
     */
    private function displayGeneralStatistics(array $schedule, PublishingPeriod $publishingPeriod): void
    {
        $totalPosts = array_sum(array_column($schedule, 'posts_count'));
        $totalDays = count($schedule);
        $avgPostsPerDay = $totalDays > 0 ? round($totalPosts / $totalDays, 2) : 0;

        $postCounts = array_column($schedule, 'posts_count');
        $minPostsPerDay = !empty($postCounts) ? min($postCounts) : 0;
        $maxPostsPerDay = !empty($postCounts) ? max($postCounts) : 0;

        $this->table([
            'Métrica', 'Valor'
        ], [
            ['Período', $publishingPeriod->getStartDate()->format('Y-m-d') . ' até ' . $publishingPeriod->getEndDate()->format('Y-m-d')],
            ['Total de dias úteis', $totalDays],
            ['Total de posts agendados', $totalPosts],
            ['Média de posts por dia', $avgPostsPerDay],
            ['Mínimo por dia', $minPostsPerDay],
            ['Máximo por dia', $maxPostsPerDay],
            ['Capacidade total período', $publishingPeriod->getMaxArticleCapacity()],
            ['Utilização da capacidade', $publishingPeriod->getMaxArticleCapacity() > 0 ? round(($totalPosts / $publishingPeriod->getMaxArticleCapacity()) * 100, 1) . '%' : '0%'],
        ]);
    }

    /**
     * Exibe breakdown por dia da semana
     */
    private function displayWeeklyBreakdown(array $schedule): void
    {
        $this->info('=== DISTRIBUIÇÃO POR DIA DA SEMANA ===');

        $weeklyStats = [];
        $dayNames = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira', 
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira'
        ];

        foreach ($dayNames as $dayNum => $dayName) {
            $daySchedules = array_filter($schedule, fn($day) => $day['day_of_week'] === $dayNum);
            $postCounts = array_column($daySchedules, 'posts_count');
            
            $weeklyStats[] = [
                $dayName,
                count($daySchedules),
                !empty($postCounts) ? array_sum($postCounts) : 0,
                !empty($postCounts) ? round(array_sum($postCounts) / count($postCounts), 1) : 0,
            ];
        }

        $this->table([
            'Dia da Semana', 'Ocorrências', 'Total Posts', 'Média por Dia'
        ], $weeklyStats);
    }

    /**
     * Exibe distribuição por hora
     */
    private function displayHourlyDistribution(array $schedule): void
    {
        $this->info('=== DISTRIBUIÇÃO POR HORA DO DIA ===');

        $hourlyStats = [];
        
        // Contar posts por hora
        for ($hour = 7; $hour <= 21; $hour++) {
            $hourCount = 0;
            
            foreach ($schedule as $day) {
                $hourSlots = array_filter($day['slots'], fn($slot) => $slot['hour'] === $hour);
                $hourCount += count($hourSlots);
            }
            
            $hourlyStats[] = [
                sprintf('%02d:00', $hour),
                $hourCount,
                round($this->workingHours->getHourWeight($hour) * 100, 1) . '%',
                $this->workingHours->isPeakHour(2, $hour) ? 'Sim' : 'Não', // Usar terça como referência
            ];
        }

        $this->table([
            'Hora', 'Total Posts', 'Peso (%)', 'Horário de Pico'
        ], $hourlyStats);
    }

    /**
     * Exibe detalhes dos primeiros dias
     */
    private function displayDailyDetails(array $schedule, int $daysToShow): void
    {
        $this->info("=== DETALHES DOS PRIMEIROS {$daysToShow} DIAS ===");

        $daysShown = 0;
        foreach ($schedule as $day) {
            if ($daysShown >= $daysToShow) break;

            $this->line("📅 {$day['date']->format('Y-m-d')} ({$day['day_name']}) - {$day['posts_count']} posts");
            
            if (!empty($day['slots'])) {
                $this->line("   Primeiro post: {$day['first_post']} | Último post: {$day['last_post']} | Picos: {$day['peak_hours_count']}");
                
                // Mostrar primeiros slots do dia
                $slotsToShow = min(5, count($day['slots']));
                for ($i = 0; $i < $slotsToShow; $i++) {
                    $slot = $day['slots'][$i];
                    $peakIcon = $slot['is_peak_hour'] ? '⭐' : '  ';
                    $this->line("   {$peakIcon} #{$slot['slot_number']}: {$slot['time']}");
                }
                
                if (count($day['slots']) > $slotsToShow) {
                    $remaining = count($day['slots']) - $slotsToShow;
                    $this->line("   ... e mais {$remaining} posts");
                }
            }
            
            $this->newLine();
            $daysShown++;
        }
    }

    /**
     * Exibe recomendações
     */
    private function displayRecommendations(array $schedule, PublishingPeriod $publishingPeriod): void
    {
        $this->info('=== RECOMENDAÇÕES ===');

        $recommendations = [];
        
        $totalPosts = array_sum(array_column($schedule, 'posts_count'));
        $totalDays = count($schedule);
        $postCounts = array_column($schedule, 'posts_count');
        
        // Verificar distribuição
        if (!empty($postCounts)) {
            $stdDev = $this->calculateStandardDeviation($postCounts);
            $avgPosts = array_sum($postCounts) / count($postCounts);
            
            if ($stdDev / $avgPosts > 0.2) {
                $recommendations[] = 'Distribuição irregular detectada. Considere ajustar os limites de posts por dia.';
            }
        }

        // Verificar capacidade
        $utilizationRate = $publishingPeriod->getMaxArticleCapacity() > 0 
            ? ($totalPosts / $publishingPeriod->getMaxArticleCapacity()) * 100 
            : 0;
            
        if ($utilizationRate > 90) {
            $recommendations[] = 'Alta utilização da capacidade. Considere estender o período ou reduzir posts por dia.';
        } elseif ($utilizationRate < 50) {
            $recommendations[] = 'Baixa utilização da capacidade. Considere concentrar em menos dias ou aumentar posts por dia.';
        }

        // Verificar período
        if ($totalDays < 5) {
            $recommendations[] = 'Período muito curto. Considere estender para melhor distribuição natural.';
        } elseif ($totalDays > 60) {
            $recommendations[] = 'Período muito longo. Considere dividir em múltiplos cronogramas.';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Cronograma está bem equilibrado e dentro dos parâmetros recomendados.';
        }

        foreach ($recommendations as $recommendation) {
            $this->line("• {$recommendation}");
        }
    }

    /**
     * Manipula exportações do cronograma
     */
    private function handleExports(array $schedule, array $options): void
    {
        if ($options['export_csv']) {
            $this->exportToCsv($schedule);
        }

        if ($options['export_json']) {
            $this->exportToJson($schedule);
        }
    }

    /**
     * Exporta cronograma para CSV
     */
    private function exportToCsv(array $schedule): void
    {
        $filename = storage_path('app/schedule_' . date('Y-m-d_H-i-s') . '.csv');
        $file = fopen($filename, 'w');

        // Cabeçalho
        fputcsv($file, [
            'Data', 'Dia da Semana', 'Slot', 'Horário', 'Hora', 'Horário de Pico', 
            'Dentro Expediente', 'Peso', 'Intervalo Anterior (min)'
        ]);

        // Dados
        foreach ($schedule as $day) {
            foreach ($day['slots'] as $slot) {
                fputcsv($file, [
                    $day['date']->format('Y-m-d'),
                    $day['day_name'],
                    $slot['slot_number'],
                    $slot['time'],
                    $slot['hour'],
                    $slot['is_peak_hour'] ? 'Sim' : 'Não',
                    $slot['is_working_hours'] ? 'Sim' : 'Não',
                    round($slot['weight'], 4),
                    $slot['interval_from_previous'],
                ]);
            }
        }

        fclose($file);
        $this->info("Cronograma exportado para CSV: {$filename}");
    }

    /**
     * Exporta cronograma para JSON
     */
    private function exportToJson(array $schedule): void
    {
        $filename = storage_path('app/schedule_' . date('Y-m-d_H-i-s') . '.json');
        
        $jsonData = array_map(function($day) {
            return [
                'date' => $day['date']->format('Y-m-d'),
                'day_name' => $day['day_name'],
                'day_of_week' => $day['day_of_week'],
                'posts_count' => $day['posts_count'],
                'first_post' => $day['first_post'],
                'last_post' => $day['last_post'],
                'peak_hours_count' => $day['peak_hours_count'],
                'slots' => $day['slots'],
            ];
        }, $schedule);

        file_put_contents($filename, json_encode($jsonData, JSON_PRETTY_PRINT));
        $this->info("Cronograma exportado para JSON: {$filename}");
    }

    /**
     * Calcula desvio padrão
     */
    private function calculateStandardDeviation(array $values): float
    {
        $count = count($values);
        if ($count <= 1) return 0.0;

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values)) / $count;

        return sqrt($variance);
    }
}
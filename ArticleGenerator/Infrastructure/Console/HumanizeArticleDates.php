<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class HumanizeArticleDates extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:humanize-dates 
                            {--days=30 : Número de dias para distribuir os artigos}
                            {--start-date= : Data inicial para distribuição (formato Y-m-d)}
                            {--future : Usar datas futuras a partir de hoje}
                            {--imported-only : Humanizar apenas artigos importados (com original_post_id)}
                            {--new-only : Humanizar apenas artigos novos (sem original_post_id)}
                            {--update-only : Humanizar apenas a data de atualização (updated_at)}
                            {--force : Forçar humanização mesmo para artigos já processados anteriormente}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Ajusta as datas de criação/edição dos artigos para simular edição humana';

    /**
     * Horas de trabalho (formato 24h)
     */
    protected $workingHours = [
        // Segunda a sexta (8h às 18h, com maior concentração em horários comerciais)
        1 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]],
        2 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]],
        3 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]],
        4 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]],
        5 => ['start' => 8, 'end' => 17, 'peak' => [10, 14]],
        // Fim de semana (atividade reduzida)
        6 => ['start' => 10, 'end' => 16, 'peak' => [11, 15]], // Sábado
        7 => ['start' => 14, 'end' => 20, 'peak' => [16, 19]], // Domingo
    ];

    /**
     * Execute o comando.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando humanização de datas dos artigos...');

        // Configurar as datas
        $days = intval($this->option('days'));

        // Definir data inicial e final com base nas opções
        if ($this->option('future')) {
            // Se --future, a data inicial é hoje e a data final é X dias para frente
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->addDays($days)->endOfDay();
            $this->info("Modo futuro: Distribuindo artigos a partir de hoje até {$days} dias para frente.");
        } else {
            // Comportamento normal: data inicial é X dias atrás (ou personalizada) e a data final é hoje
            $startDate = $this->option('start-date')
                ? Carbon::createFromFormat('Y-m-d', $this->option('start-date'))->startOfDay()
                : Carbon::now()->subDays($days)->startOfDay();
            $endDate = Carbon::now();
        }

        // Determinar quais artigos processar
        $query = Article::query();

        if ($this->option('imported-only')) {
            $query->whereNotNull('original_post_id');
            $this->info('Processando apenas artigos importados.');
        } elseif ($this->option('new-only')) {
            $query->whereNull('original_post_id');
            $this->info('Processando apenas artigos novos.');
        }

        // Contar e processar em lotes
        $articlesCount = $query->count();

        if ($articlesCount === 0) {
            $this->warn('Nenhum artigo encontrado para processar.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$articlesCount} artigos para processar.");
        $this->info("Distribuindo artigos entre {$startDate->toDateString()} e {$endDate->toDateString()}.");

        $bar = $this->output->createProgressBar($articlesCount);
        $bar->start();

        $articlesPerDay = max(1, ceil($articlesCount / $days));
        $processedCount = 0;

        // Lista para armazenar as datas criadas para evitar duplicação exata
        $usedDates = [];

        // Processar artigos em lotes para evitar problemas de memória
        $perPage = 100;
        $page = 1;

        do {
            $articles = $query->forPage($page, $perPage)->get();

            if ($articles->isEmpty()) {
                break;
            }

            foreach ($articles as $article) {
                // Verificar se o artigo já foi humanizado anteriormente
                $alreadyHumanized = !empty($article->humanized_at);

                // Pular artigo já humanizado, a menos que --force esteja definido
                if ($alreadyHumanized && !$this->option('force')) {
                    if ($this->getOutput()->isVerbose()) {
                        $this->line("Artigo {$article->_id} já foi humanizado em " .
                            Carbon::parse($article->humanized_at)->format('Y-m-d H:i:s') .
                            ". Pulando...");
                    }
                    $bar->advance();
                    continue;
                }

                // Definir estratégia com base no tipo de artigo
                $isImportedArticle = !empty($article->original_post_id);

                // Para todos os artigos, calcular uma data de atualização humanizada
                $updatedDate = $this->generateHumanUpdatedDate($article, $endDate);

                // Preparar os dados para atualização
                $updateData = [
                    'updated_at' => $updatedDate,
                    'humanized_at' => now(), // Registrar quando foi humanizado
                ];

                // Para artigos novos (sem original_post_id), também humanizar created_at e published_at
                // desde que não esteja definida a opção --update-only
                if (!$isImportedArticle && !$this->option('update-only')) {
                    // Calcular qual dia este artigo deve ser publicado
                    $dayIndex = min($days - 1, floor($processedCount / $articlesPerDay));
                    $baseDate = (clone $startDate)->addDays($dayIndex);

                    // Adicionar variação para evitar publicações em massa no mesmo horário
                    $publicationDate = $this->generateHumanDate($baseDate, $usedDates);

                    $updateData['published_at'] = $publicationDate;
                    $updateData['created_at'] = $publicationDate;
                }

                // Atualizar o artigo
                Article::find($article->_id)
                    ->update($updateData);

                $processedCount++;
                $bar->advance();
            }

            // Limpar a memória
            $articles = null;
            gc_collect_cycles();

            // Avançar para a próxima página
            $page++;
        } while (true);

        $bar->finish();
        $this->newLine(2);

        // Calcular quantos artigos foram pulados por já terem sido humanizados
        $skippedCount = $articlesCount - $processedCount;

        $this->info("Concluído! {$processedCount} artigos foram atualizados com datas humanizadas.");

        if ($skippedCount > 0) {
            $this->line("{$skippedCount} artigos foram pulados por já terem sido humanizados anteriormente.");
            if (!$this->option('force')) {
                $this->line("Use a opção --force para re-humanizar todos os artigos.");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Gera uma data com horário que parece "humano" (durante horário comercial, com variações)
     *
     * @param Carbon $baseDate A data base para gerar o horário
     * @param array &$usedDates Lista de datas já utilizadas para evitar duplicação
     * @return Carbon
     */
    protected function generateHumanDate(Carbon $baseDate, array &$usedDates)
    {
        $attempts = 0;
        do {
            $date = clone $baseDate;

            // Obter configuração de horas de trabalho para este dia da semana
            $dayOfWeek = $date->dayOfWeekIso; // 1 (Segunda) até 7 (Domingo)
            $hours = $this->workingHours[$dayOfWeek];

            // Determinar se usaremos horário de pico (60% de chance) ou horário normal
            $usePeakHour = (rand(1, 100) <= 60);

            if ($usePeakHour) {
                // Horário de pico com maior probabilidade
                $hour = rand($hours['peak'][0], $hours['peak'][1]);
            } else {
                // Horário de trabalho normal
                $hour = rand($hours['start'], $hours['end']);
            }

            // Adicionar minutos e segundos para maior naturalidade
            $minute = rand(0, 59);
            $second = rand(0, 59);

            $date->setTime($hour, $minute, $second);

            // Verificar se esta data já foi usada (evitar duplicação exata)
            // Se já tentamos muitas vezes, aceitar uma data repetida para evitar loop infinito
            $attempts++;
            $dateKey = $date->format('Y-m-d H:i:s');
        } while (in_array($dateKey, $usedDates) && $attempts < 10);

        // Registrar esta data como usada
        $usedDates[] = $dateKey;

        return $date;
    }

    /**
     * Gera uma data de atualização humanizada para um artigo
     * 
     * @param Article $article O artigo a ser processado
     * @param Carbon $endDate A data limite máxima
     * @return Carbon A data de atualização
     */
    protected function generateHumanUpdatedDate($article, $endDate)
    {
        // Verificar se estamos no modo futuro
        $isFutureMode = $this->option('future');

        // Para artigos importados, a data de atualização será recente ou futura
        if (!empty($article->original_post_id)) {
            if ($isFutureMode) {
                // No modo futuro, definir uma data aleatória entre hoje e o limite futuro
                $daysForward = rand(0, $this->option('days'));
                $baseDate = Carbon::now()->addDays($daysForward);
            } else {
                // No modo normal, definir uma data recente (últimos 7 dias)
                $daysAgo = rand(0, 7);
                $baseDate = Carbon::now()->subDays($daysAgo);
            }

            // Obter configuração de horas de trabalho para este dia da semana
            $dayOfWeek = $baseDate->dayOfWeekIso;
            $hours = $this->workingHours[$dayOfWeek];

            // Escolher um horário comercial
            $hour = rand($hours['start'], $hours['end']);
            $minute = rand(0, 59);
            $second = rand(0, 59);

            $updatedDate = (clone $baseDate)->setTime($hour, $minute, $second);
        } else {
            // Para artigos novos, a atualização é normalmente próxima à publicação
            $baseDate = $article->published_at ?? $article->created_at ?? Carbon::now()->subDays(rand(1, 30));

            if ($isFutureMode) {
                // No modo futuro, adicionar dias para frente
                $daysForward = rand(1, $this->option('days'));
                $updatedDate = (clone $baseDate)->addDays($daysForward);
            } else {
                // No modo normal, adicionar algumas horas
                $hoursLater = rand(1, 48); // Entre 1 e 48 horas depois
                $updatedDate = (clone $baseDate)->addHours($hoursLater);
            }

            // Garantir que seja em horário comercial
            $dayOfWeek = $updatedDate->dayOfWeekIso;
            $hours = $this->workingHours[$dayOfWeek];

            // Se o horário cair fora do horário comercial, ajustar
            if ($updatedDate->hour < $hours['start'] || $updatedDate->hour > $hours['end']) {
                // Ajustar para o próximo dia útil
                if ($updatedDate->hour > $hours['end']) {
                    $updatedDate->addDay();
                }

                // Verificar se é fim de semana e ajustar se necessário
                if ($updatedDate->dayOfWeekIso == 6) { // Sábado
                    $updatedDate->addDay(2); // Pular para segunda
                } elseif ($updatedDate->dayOfWeekIso == 7) { // Domingo
                    $updatedDate->addDay(1); // Pular para segunda
                }

                // Definir para um horário comercial
                $newHour = rand($hours['peak'][0], $hours['peak'][1]); // Usar horário de pico
                $updatedDate->setTime($newHour, rand(0, 59), rand(0, 59));
            }
        }

        // No modo normal, garantir que a data não seja no futuro
        // No modo futuro, garantir que a data não seja além do limite definido
        if (!$isFutureMode && $updatedDate->gt(Carbon::now())) {
            return Carbon::now()->subHours(rand(1, 12));
        } elseif ($isFutureMode && $updatedDate->gt($endDate)) {
            return $endDate->copy()->subHours(rand(1, 6));
        }

        return $updatedDate;
    }
}

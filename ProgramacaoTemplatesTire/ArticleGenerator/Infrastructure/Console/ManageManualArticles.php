<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

class ManageManualArticles extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:manage-manual 
                           {--template=when_to_change_tires : Domínio específico a processar}
                           {--batch-size=50 : Número de artigos a processar por lote}
                           {--days=15 : Número de dias para distribuir os artigos}
                           {--dry-run : Simular a execução sem fazer alterações}
                           {--force : Processar artigos mesmo se já existirem no Article}
                           {--detailed : Exibir logs detalhados}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Gerencia artigos draft do domínio when_to_change_tires com distribuição humanizada';

    /**
     * Horas de trabalho (formato 24h)
     */
    protected array $workingHours = [
        // Segunda a sexta (8h às 18h, com maior concentração em horários comerciais)
        1 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]], // Segunda
        2 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]], // Terça
        3 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]], // Quarta
        4 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]], // Quinta
        5 => ['start' => 8, 'end' => 17, 'peak' => [10, 14]], // Sexta (término mais cedo)
        6 => ['start' => 10, 'end' => 16, 'peak' => [11, 15]], // Sábado (atividade reduzida)
        7 => ['start' => 14, 'end' => 20, 'peak' => [16, 19]], // Domingo (atividade reduzida)
    ];

    /**
     * Cache para MaintenanceCategories já processadas
     */
    private array $processedCategories = [];

    /**
     * Lista de datas já utilizadas para evitar duplicação
     */
    private array $usedDates = [];

    /**
     * Execute o comando.
     */
    public function handle(): int
    {
        $this->info('🚀 Iniciando gerenciamento de artigos manuais with_to_change_tires...');
        $this->showOptions();

        $stats = $this->processManualArticles();
        $this->showFinalResults($stats);

        return Command::SUCCESS;
    }

    /**
     * Mostra opções configuradas
     */
    private function showOptions(): void
    {
        $days = (int) $this->option('days');
        $endDate = Carbon::now()->addDays($days);

        $this->table(['Configuração', 'Valor'], [
            ['Template', $this->option('template')],
            ['Batch Size', $this->option('batch-size')],
            ['Dias de Distribuição', $this->option('days')],
            ['Data Inicial', Carbon::now()->format('Y-m-d')],
            ['Data Final', $endDate->format('Y-m-d')],
            ['Dry Run', $this->option('dry-run') ? 'Sim' : 'Não'],
            ['Force', $this->option('force') ? 'Sim' : 'Não'],
            ['Detailed', $this->option('detailed') ? 'Sim' : 'Não'],
        ]);

        if ($this->option('dry-run')) {
            $this->warn('⚠️  MODO SIMULAÇÃO - Nenhuma alteração será feita');
        }

        $this->line('');
    }

    /**
     * Processa todos os artigos manuais
     */
    private function processManualArticles(): array
    {
        $stats = [
            'total_found' => 0,
            'processed' => 0,
            'skipped' => 0,
            'errors' => 0,
            'already_exists' => 0,
        ];

        // Buscar artigos do domínio especificado com status draft
        $articles = $this->getManualArticles();
        $stats['total_found'] = $articles->count();

        if ($stats['total_found'] === 0) {
            $this->warn('❌ Nenhum artigo encontrado com os critérios especificados');
            return $stats;
        }

        $this->info("📊 Encontrados {$stats['total_found']} artigos para processar");
        $this->line('');

        // Calcular distribuição de artigos por dia
        $days = (int) $this->option('days');
        $articlesPerDay = max(1, ceil($stats['total_found'] / $days));

        $this->info("📅 Distribuindo {$articlesPerDay} artigos por dia ao longo de {$days} dias");
        $this->line('');

        $bar = $this->output->createProgressBar($stats['total_found']);
        $bar->start();

        $processedCount = 0;

        foreach ($articles->chunk($this->option('batch-size')) as $chunk) {
            foreach ($chunk as $article) {
                try {
                    $result = $this->processIndividualArticle($article, $processedCount, $articlesPerDay, $days);
                    $stats[$result]++;
                    $processedCount++;
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $this->logError($article, $e);
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $this->line('');

        return $stats;
    }

    /**
     * Busca artigos manuais baseado nos critérios
     */
    private function getManualArticles()
    {
        return TempArticle::where('template', $this->option('template'))
            ->where('status', 'draft')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Processa um artigo individual
     */
    private function processIndividualArticle($tempArticle, int $processedCount, int $articlesPerDay, int $days): string
    {
        // Verificar se já existe no Article (a menos que force seja usado)
        if (!$this->option('force') && $this->articleAlreadyExists($tempArticle)) {
            $this->detailedLog("Artigo {$tempArticle->slug} já existe no Article - pulando");
            return 'already_exists';
        }

        // Calcular qual dia este artigo deve ser publicado
        $dayIndex = min($days - 1, floor($processedCount / $articlesPerDay));
        $baseDate = Carbon::now()->addDays($dayIndex);

        // Gerar datas humanizadas
        $publishedAt = $this->generateHumanDate($baseDate);
        $createdAt = $publishedAt->copy();
        $updatedAt = $this->generateHumanUpdatedDate($publishedAt);

        $articleData = [
            'title' => $tempArticle->title,
            'slug' => $tempArticle->new_slug ?? $tempArticle->slug,
            'template' => $tempArticle->template,
            'category_id' => $tempArticle->category_id,
            'category_name' => $tempArticle->category_name,
            'category_slug' => $tempArticle->category_slug,
            'content' => $tempArticle->content,
            'extracted_entities' => $tempArticle->extracted_entities,
            'seo_data' => $tempArticle->seo_data,
            'metadata' => $tempArticle->metadata ?? [],
            'status' => 'scheduled',
            'original_post_id' => $tempArticle->original_post_id ?? null,
            'created_at' => $createdAt,
            'published_at' => $publishedAt,
            'updated_at' => $updatedAt,
            'scheduled_at' => null,
            'humanized_at' => Carbon::now(), // Registrar quando foi humanizado
        ];

        if (!$this->option('dry-run')) {
            $article = Article::create($articleData);
            // Atualizar MaintenanceCategory se necessário
            $this->updateMaintenanceCategoryIfNeeded($article);
        }

        $this->detailedLog("✅ Artigo processado: {$tempArticle->slug} - Publicado em: {$publishedAt->format('Y-m-d H:i:s')}");
        return 'processed';
    }

    /**
     * Gera uma data com horário que parece "humano" (durante horário comercial, com variações)
     */
    protected function generateHumanDate(Carbon $baseDate): Carbon
    {
        $attempts = 0;
        do {
            $date = $baseDate->copy();

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
            $attempts++;
            $dateKey = $date->format('Y-m-d H:i:s');
        } while (in_array($dateKey, $this->usedDates) && $attempts < 10);

        // Registrar esta data como usada
        $this->usedDates[] = $dateKey;

        return $date;
    }

    /**
     * Gera uma data de atualização humanizada para um artigo
     */
    protected function generateHumanUpdatedDate(Carbon $publishedAt): Carbon
    {
        // Para artigos novos, a atualização é normalmente próxima à publicação
        // Adicionar entre 1 e 48 horas depois da publicação
        $hoursLater = rand(1, 48);
        $updatedDate = $publishedAt->copy()->addHours($hoursLater);

        // Obter configuração de horas de trabalho para este dia da semana
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

            // Redefinir dayOfWeek e hours após ajuste
            $dayOfWeek = $updatedDate->dayOfWeekIso;
            $hours = $this->workingHours[$dayOfWeek];

            // Definir para um horário comercial
            $newHour = rand($hours['peak'][0], $hours['peak'][1]); // Usar horário de pico
            $updatedDate->setTime($newHour, rand(0, 59), rand(0, 59));
        }

        // Garantir que a data não seja muito distante no futuro
        $maxFutureDate = Carbon::now()->addDays((int) $this->option('days') + 5);
        if ($updatedDate->gt($maxFutureDate)) {
            return $maxFutureDate->copy()->subHours(rand(1, 6));
        }

        return $updatedDate;
    }

    /**
     * Verifica se o artigo já existe no Article
     */
    private function articleAlreadyExists($tempArticle): bool
    {
        $slug = $tempArticle->new_slug ?? $tempArticle->slug;

        $existsBy = Article::where('slug', $slug)->exists();

        if (!$existsBy && $tempArticle->original_post_id) {
            $existsBy = Article::where('original_post_id', $tempArticle->original_post_id)->exists();
        }

        return $existsBy;
    }

    /**
     * Atualiza MaintenanceCategory para to_follow = true se necessário
     */
    private function updateMaintenanceCategoryIfNeeded(Article $article): void
    {
        if (empty($article->category_slug)) {
            return;
        }

        // Evitar processamento duplicado da mesma categoria
        if (in_array($article->category_slug, $this->processedCategories)) {
            return;
        }

        try {
            $category = MaintenanceCategory::where('slug', $article->category_slug)
                ->where('to_follow', false)
                ->first();

            if ($category) {
                $category->update(['to_follow' => true]);
                $this->info("MaintenanceCategory '{$article->category_slug}' marcada como to_follow = true");
            }

            // Adicionar ao cache para evitar reprocessamento
            $this->processedCategories[] = $article->category_slug;
        } catch (\Exception $e) {
            $this->warn("Erro ao atualizar MaintenanceCategory '{$article->category_slug}': {$e->getMessage()}");
        }
    }

    /**
     * Log de erro
     */
    private function logError($article, \Exception $e): void
    {
        $message = "Erro ao processar artigo {$article->slug}: " . $e->getMessage();

        Log::error($message, [
            'article_id' => $article->_id,
            'article_slug' => $article->slug,
            'exception' => $e->getTraceAsString()
        ]);

        if ($this->option('detailed')) {
            $this->error($message);
        }
    }

    /**
     * Log detalhado
     */
    private function detailedLog(string $message): void
    {
        if ($this->option('detailed')) {
            $this->line($message);
        }
    }

    /**
     * Mostra resultados finais
     */
    private function showFinalResults(array $stats): void
    {
        $this->line('');
        $this->info('📊 RESULTADOS FINAIS');
        $this->line('====================');

        $this->table(['Métrica', 'Quantidade'], [
            ['📄 Total de artigos encontrados', $stats['total_found']],
            ['✅ Artigos processados', $stats['processed']],
            ['🔄 Artigos já existentes (pulados)', $stats['already_exists']],
            ['⏭️ Artigos ignorados', $stats['skipped']],
            ['❌ Erros durante processamento', $stats['errors']],
        ]);

        $totalProcessed = $stats['processed'];
        $successRate = $stats['total_found'] > 0
            ? round(($totalProcessed / $stats['total_found']) * 100, 2)
            : 0;

        $this->line('');
        $this->info("🎯 Taxa de sucesso: {$successRate}% ({$totalProcessed}/{$stats['total_found']})");

        if ($stats['errors'] > 0) {
            $this->warn("⚠️  {$stats['errors']} erros encontrados. Verifique os logs para detalhes.");
        }

        if ($totalProcessed > 0) {
            $this->info("✨ Processamento concluído com sucesso!");

            if (!$this->option('dry-run')) {
                $this->line('');
                $this->info('💡 Próximos passos recomendados:');
                $this->line('1. Verificar artigos processados no painel administrativo');
                $this->line('2. Executar sincronização com MySQL se necessário');
                $this->line('3. Monitorar a publicação dos artigos ao longo dos próximos dias');

                $endDate = Carbon::now()->addDays((int) $this->option('days'));
                $this->line("4. Último artigo será publicado em: {$endDate->format('Y-m-d')}");
            }
        }
    }
}

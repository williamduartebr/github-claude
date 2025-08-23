<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;
use Src\ArticleGenerator\Infrastructure\Services\ArticleCorrectionService;

class ProcessArticleCorrections extends Command
{

    /**     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:process-corrections 
                           {--type=introduction_fix : Tipo de correção a processar}
                           {--slug= : Processar apenas um artigo específico por slug}
                           {--limit=15 : Limite de correções a processar por execução}
                           {--priority=all : Prioridade (high/medium/low/all)}
                           {--create-for-slug= : Criar nova correção para um slug específico}
                           {--from-analysis : Criar correções baseadas em análises confirmadas}
                           {--dry-run : Apenas listar o que seria processado}
                           {--delay=5 : Delay em segundos entre processamentos}
                           {--stats : Mostrar apenas estatísticas}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Processa correções pendentes de artigos usando Claude API';

    protected $correctionService;

    public function __construct(ArticleCorrectionService $correctionService)
    {
        parent::__construct();
        $this->correctionService = $correctionService;
    }

    /**
     * Execute o comando.
     */
    public function handle()
    {
        // Mostrar estatísticas
        if ($this->option('stats')) {
            return $this->showStats();
        }

        // Criar correções baseadas em análises
        if ($this->option('from-analysis')) {
            return $this->createCorrectionsFromAnalysis();
        }

        // Verificar se é para criar nova correção
        if ($this->option('create-for-slug')) {
            return $this->createCorrectionForSlug();
        }

        // Verificar se é apenas para um slug específico
        if ($this->option('slug')) {
            return $this->processSingleSlug();
        }

        // Processar correções pendentes
        return $this->processPendingCorrections();
    }

    /**
     * Mostra estatísticas detalhadas
     */
    protected function showStats()
    {
        $this->info('📊 Estatísticas Detalhadas de Correções');
        $this->line('');

        $stats = ArticleCorrection::getStats();

        $this->table(['Categoria', 'Quantidade'], [
            ['📝 Análises pendentes', $stats['pending_analysis']],
            ['✅ Análises concluídas', $stats['completed_analysis']],
            ['🔥 Artigos que precisam correção', $stats['needs_correction']],
            ['⏳ Correções pendentes', $stats['pending_fixes']],
            ['✅ Correções concluídas', $stats['completed_fixes']],
            ['❌ Falhas', $stats['failed']]
        ]);

        // Estatísticas por prioridade
        $queue = ArticleCorrection::getCorrectionQueue();

        $this->line('');
        $this->info('🔥 Fila de Correções por Prioridade:');

        if ($queue['high_priority']->count() > 0) {
            $this->line("🔴 Alta prioridade: {$queue['high_priority']->count()}");
            $highPriority = $queue['high_priority']->take(5);
            foreach ($highPriority as $item) {
                $this->line("   • {$item->article_slug}");
            }
        }

        if ($queue['medium_priority']->count() > 0) {
            $this->line("🟡 Média prioridade: {$queue['medium_priority']->count()}");
        }

        if ($queue['low_priority']->count() > 0) {
            $this->line("🟢 Baixa prioridade: {$queue['low_priority']->count()}");
        }

        return Command::SUCCESS;
    }

    /**
     * Cria correções baseadas em análises confirmadas
     */
    protected function createCorrectionsFromAnalysis()
    {
        $this->info('🔄 Criando correções baseadas em análises confirmadas...');

        // Buscar análises que precisam de correção mas ainda não têm correções criadas
        $confirmedAnalyses = ArticleCorrection::needsCorrection()
            ->where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
            ->get()
            ->filter(function ($analysis) {
                // Verificar se já existe uma correção para este artigo
                $existingCorrection = ArticleCorrection::where('article_slug', $analysis->article_slug)
                    ->where('correction_type', ArticleCorrection::TYPE_INTRODUCTION_FIX)
                    ->exists();

                return !$existingCorrection;
            });

        if ($confirmedAnalyses->isEmpty()) {
            $this->info('ℹ️ Nenhuma análise confirmada sem correção encontrada.');
            return Command::SUCCESS;
        }

        $this->info("📝 Encontradas {$confirmedAnalyses->count()} análises para criar correções.");

        if ($this->option('dry-run')) {
            $tableData = [];
            foreach ($confirmedAnalyses as $analysis) {
                $priority = $analysis->correction_data['correction_priority'] ?? 'medium';
                $confidence = $analysis->correction_data['confidence_level'] ?? 'medium';
                $tableData[] = [
                    $analysis->article_slug,
                    $priority,
                    $confidence,
                    $analysis->created_at->format('d/m H:i')
                ];
            }
            $this->table(['Slug', 'Prioridade', 'Confiança', 'Analisado'], $tableData);
            return Command::SUCCESS;
        }

        $created = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($confirmedAnalyses as $analysis) {
            try {
                // Verificar novamente se já existe correção (race condition safety)
                $existingCorrection = ArticleCorrection::where('article_slug', $analysis->article_slug)
                    ->where('correction_type', ArticleCorrection::TYPE_INTRODUCTION_FIX)
                    ->exists();

                if ($existingCorrection) {
                    $skipped++;
                    $this->line("⚠️ Já existe: {$analysis->article_slug}");
                    continue;
                }

                $correction = $this->correctionService->fixIntroductionAndSeo($analysis->article_slug);

                if ($correction) {
                    $created++;
                    $this->info("✅ Correção criada: {$analysis->article_slug}");
                } else {
                    $skipped++;
                    $this->line("⚠️ Não foi possível criar: {$analysis->article_slug}");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("❌ Erro: {$analysis->article_slug} - {$e->getMessage()}");
                Log::error("Erro ao criar correção para {$analysis->article_slug}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("📊 Resultado:");
        $this->info("✅ Correções criadas: {$created}");
        $this->info("⚠️ Ignoradas (já existem): {$skipped}");
        $this->info("❌ Erros: {$errors}");

        return Command::SUCCESS;
    }

    /**
     * Cria uma nova correção para um slug específico
     */
    protected function createCorrectionForSlug()
    {
        $slug = $this->option('create-for-slug');
        $type = $this->option('type');

        $this->info("📝 Criando correção do tipo '{$type}' para o artigo: {$slug}");

        $correction = $this->correctionService->fixIntroductionAndSeo($slug);

        if ($correction) {
            $this->info("✅ Correção criada com sucesso! ID: {$correction->_id}");

            // Perguntar se quer processar imediatamente
            if ($this->confirm('Deseja processar esta correção agora?')) {
                $this->info('⚙️ Processando correção...');
                $success = $this->correctionService->processCorrection($correction);

                if ($success) {
                    $this->info('✅ Correção processada e aplicada com sucesso!');
                } else {
                    $this->error('❌ Falha ao processar a correção.');
                }
            }
        } else {
            $this->error('❌ Falha ao criar correção ou correção já existe.');
        }

        return Command::SUCCESS;
    }

    /**
     * Processa correção para um slug específico
     */
    protected function processSingleSlug()
    {
        $slug = $this->option('slug');
        $type = $this->option('type');

        $this->info("🔍 Buscando correções pendentes para: {$slug}");

        $correction = ArticleCorrection::where('article_slug', $slug)
            ->where('correction_type', $type)
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->first();

        if (!$correction) {
            $this->warn("⚠️ Nenhuma correção pendente encontrada para {$slug} do tipo {$type}");

            if ($this->confirm('Deseja criar uma nova correção?')) {
                return $this->createCorrectionForSlug();
            }

            return Command::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("🔍 [DRY RUN] Seria processada:");
            $this->table(['ID', 'Slug', 'Tipo', 'Status', 'Criado em'], [
                [$correction->_id, $correction->article_slug, $correction->correction_type, $correction->status, $correction->created_at]
            ]);
            return Command::SUCCESS;
        }

        $this->info("⚙️ Processando correção ID: {$correction->_id}");

        $success = $this->correctionService->processCorrection($correction);

        if ($success) {
            $this->info("✅ Correção processada com sucesso!");
        } else {
            $this->error("❌ Falha ao processar correção.");
        }

        return Command::SUCCESS;
    }

    /**
     * Processa correções pendentes em lote
     */
    protected function processPendingCorrections()
    {
        $type = $this->option('type');
        $limit = (int) $this->option('limit');
        $priority = $this->option('priority');
        $delay = (int) $this->option('delay');

        $this->info("⚙️ Processando correções pendentes do tipo: {$type}");

        // Construir query
        $query = ArticleCorrection::pending()->byType($type);

        if ($priority !== 'all') {
            $query->byPriority($priority);
        }

        $pendingCorrections = $query->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($pendingCorrections->isEmpty()) {
            $this->info("ℹ️ Nenhuma correção pendente encontrada do tipo: {$type}");
            return Command::SUCCESS;
        }

        $this->info("📝 Encontradas {$pendingCorrections->count()} correções pendentes.");

        if ($this->option('dry-run')) {
            $this->info("🔍 [DRY RUN] Correções que seriam processadas:");
            $tableData = [];
            foreach ($pendingCorrections as $correction) {
                $tableData[] = [
                    $correction->_id,
                    $correction->article_slug,
                    $correction->correction_type,
                    $correction->status,
                    $correction->created_at->format('d/m H:i')
                ];
            }
            $this->table(['ID', 'Slug', 'Tipo', 'Status', 'Criado em'], $tableData);
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($pendingCorrections->count());
        $bar->start();

        $processed = 0;
        $failed = 0;

        foreach ($pendingCorrections as $correction) {
            try {
                $this->newLine();
                $this->info("⚙️ Processando: {$correction->article_slug}");

                $success = $this->correctionService->processCorrection($correction);

                if ($success) {
                    $processed++;
                    $this->info("✅ Sucesso: {$correction->article_slug}");
                } else {
                    $failed++;
                    $this->error("❌ Falhou: {$correction->article_slug}");
                }

                // Delay para evitar rate limiting da API
                if ($delay > 0) {
                    sleep($delay);
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("❌ Erro: {$correction->article_slug} - {$e->getMessage()}");
                Log::error("Erro ao processar correção {$correction->_id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("📊 Processamento concluído:");
        $this->info("✅ Processadas com sucesso: {$processed}");
        $this->info("❌ Falharam: {$failed}");
        $this->info("📊 Total: " . ($processed + $failed));

        return Command::SUCCESS;
    }
}

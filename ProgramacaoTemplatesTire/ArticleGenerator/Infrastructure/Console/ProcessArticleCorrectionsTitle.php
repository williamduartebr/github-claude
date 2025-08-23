<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;
use Src\ArticleGenerator\Infrastructure\Services\ArticleCorrectionService;

class ProcessArticleCorrectionsTitle extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:process-corrections 
                           {--type=introduction_fix : Tipo de correção a processar}
                           {--slug= : Processar apenas um artigo específico por slug}
                           {--limit=10 : Limite de correções a processar por execução}
                           {--create-for-slug= : Criar nova correção para um slug específico}
                           {--dry-run : Apenas listar o que seria processado}';

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
     *
     * @return int
     */
    public function handle()
    {
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
     * Cria uma nova correção para um slug específico
     */
    protected function createCorrectionForSlug()
    {
        $slug = $this->option('create-for-slug');
        $type = $this->option('type');

        $this->info("Criando correção do tipo '{$type}' para o artigo: {$slug}");

        $correction = $this->correctionService->fixIntroductionAndSeo($slug);

        if ($correction) {
            $this->info("✅ Correção criada com sucesso! ID: {$correction->_id}");
            
            // Perguntar se quer processar imediatamente
            if ($this->confirm('Deseja processar esta correção agora?')) {
                $this->info('Processando correção...');
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

        $this->info("Buscando correções pendentes para: {$slug}");

        $correction = ArticleCorrection::where('article_slug', $slug)
            ->where('correction_type', $type)
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->first();

        if (!$correction) {
            $this->warn("Nenhuma correção pendente encontrada para {$slug} do tipo {$type}");
            
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

        $this->info("Processando correção ID: {$correction->_id}");

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

        $this->info("Buscando correções pendentes do tipo: {$type}");

        $pendingCorrections = ArticleCorrection::pending()
            ->byType($type)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($pendingCorrections->isEmpty()) {
            $this->info("Nenhuma correção pendente encontrada do tipo: {$type}");
            return Command::SUCCESS;
        }

        $this->info("Encontradas {$pendingCorrections->count()} correções pendentes.");

        if ($this->option('dry-run')) {
            $this->info("🔍 [DRY RUN] Correções que seriam processadas:");
            $tableData = [];
            foreach ($pendingCorrections as $correction) {
                $tableData[] = [
                    $correction->_id,
                    $correction->article_slug,
                    $correction->correction_type,
                    $correction->status,
                    $correction->created_at
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
                $this->info("\nProcessando: {$correction->article_slug}");

                $success = $this->correctionService->processCorrection($correction);

                if ($success) {
                    $processed++;
                    $this->info("✅ Sucesso: {$correction->article_slug}");
                } else {
                    $failed++;
                    $this->error("❌ Falhou: {$correction->article_slug}");
                }

                // Delay para evitar rate limiting da API
                sleep(2);

            } catch (\Exception $e) {
                $failed++;
                $this->error("❌ Erro: {$correction->article_slug} - {$e->getMessage()}");
                Log::error("Erro ao processar correção {$correction->_id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Processamento concluído:");
        $this->info("✅ Processadas com sucesso: {$processed}");
        $this->info("❌ Falharam: {$failed}");
        $this->info("📊 Total: " . ($processed + $failed));

        return Command::SUCCESS;
    }
}
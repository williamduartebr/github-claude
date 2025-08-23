<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;
use Src\ArticleGenerator\Infrastructure\Services\ArticleAnalysisService;

class ProcessArticleAnalysis extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:process-analysis 
                           {--type=punctuation_analysis : Tipo de análise a processar}
                           {--slug= : Processar apenas um artigo específico}
                           {--limit=20 : Limite de análises a processar por execução}
                           {--priority=all : Prioridade (high/medium/low/all)}
                           {--dry-run : Apenas listar o que seria processado}
                           {--delay=3 : Delay em segundos entre processamentos}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Processa análises pendentes usando Claude API para confirmar problemas';

    protected $analysisService;

    public function __construct(ArticleAnalysisService $analysisService)
    {
        parent::__construct();
        $this->analysisService = $analysisService;
    }

    /**
     * Execute o comando.
     */
    public function handle()
    {
        if ($this->option('slug')) {
            return $this->processSingleAnalysis();
        }

        return $this->processBulkAnalysis();
    }

    /**
     * Processa análise para um slug específico
     */
    protected function processSingleAnalysis()
    {
        $slug = $this->option('slug');
        $type = $this->option('type');

        $this->info("🔍 Buscando análise pendente para: {$slug}");

        $analysis = ArticleCorrection::where('article_slug', $slug)
            ->where('correction_type', $type)
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->first();

        if (!$analysis) {
            $this->warn("⚠️ Nenhuma análise pendente encontrada para {$slug}");
            return Command::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("🔍 [DRY RUN] Seria processada:");
            $this->displayAnalysisInfo($analysis);
            return Command::SUCCESS;
        }

        $this->info("⚙️ Processando análise ID: {$analysis->_id}");

        $success = $this->analysisService->processAnalysisWithClaude($analysis);

        if ($success) {
            $this->info("✅ Análise processada com sucesso!");
            $this->showAnalysisResult($analysis->fresh());
        } else {
            $this->error("❌ Falha ao processar análise.");
        }

        return Command::SUCCESS;
    }

    /**
     * Processa análises em lote
     */
    protected function processBulkAnalysis()
    {
        $type = $this->option('type');
        $limit = (int) $this->option('limit');
        $priority = $this->option('priority');
        $delay = (int) $this->option('delay');

        $this->info("⚙️ Processando análises pendentes do tipo: {$type}");

        // Construir query
        $query = ArticleCorrection::pending()->byType($type);

        if ($priority !== 'all') {
            $query->byPriority($priority);
        }

        $pendingAnalyses = $query->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($pendingAnalyses->isEmpty()) {
            $this->info("ℹ️ Nenhuma análise pendente encontrada.");
            return Command::SUCCESS;
        }

        $this->info("📝 Encontradas {$pendingAnalyses->count()} análises pendentes.");

        if ($this->option('dry-run')) {
            $this->info("🔍 [DRY RUN] Análises que seriam processadas:");
            $tableData = [];
            foreach ($pendingAnalyses as $analysis) {
                $issues = implode(', ', $analysis->original_data['local_analysis']['issues'] ?? []);
                $tableData[] = [
                    $analysis->_id,
                    $analysis->article_slug,
                    $analysis->created_at->format('d/m H:i'),
                    $issues
                ];
            }
            $this->table(['ID', 'Slug', 'Criado', 'Problemas Detectados'], $tableData);
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($pendingAnalyses->count());
        $bar->start();

        $processed = 0;
        $confirmed = 0;
        $rejected = 0;
        $failed = 0;

        foreach ($pendingAnalyses as $analysis) {
            try {
                $this->newLine();
                $this->info("⚙️ Processando: {$analysis->article_slug}");

                $success = $this->analysisService->processAnalysisWithClaude($analysis);

                if ($success) {
                    $processed++;
                    $refreshedAnalysis = $analysis->fresh();
                    
                    if ($refreshedAnalysis->correction_data['needs_correction'] ?? false) {
                        $confirmed++;
                        $this->info("✅ Confirmado: {$analysis->article_slug}");
                    } else {
                        $rejected++;
                        $this->line("🔍 Rejeitado: {$analysis->article_slug}");
                    }
                } else {
                    $failed++;
                    $this->error("❌ Falhou: {$analysis->article_slug}");
                }

                // Delay para evitar rate limiting
                if ($delay > 0) {
                    sleep($delay);
                }

            } catch (\Exception $e) {
                $failed++;
                $this->error("❌ Erro: {$analysis->article_slug} - {$e->getMessage()}");
                Log::error("Erro ao processar análise {$analysis->_id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("📊 Processamento de análises concluído:");
        $this->info("✅ Processadas com sucesso: {$processed}");
        $this->info("🔥 Problemas confirmados: {$confirmed}");
        $this->info("🔍 Problemas rejeitados: {$rejected}");
        $this->info("❌ Falharam: {$failed}");
        $this->info("📈 Taxa de confirmação: " . ($processed > 0 ? round(($confirmed / $processed) * 100, 1) : 0) . "%");

        if ($confirmed > 0) {
            $this->newLine();
            $this->info("💡 Próximo passo:");
            $this->line("Execute: php artisan articles:process-corrections --type=introduction_fix");
        }

        return Command::SUCCESS;
    }

    /**
     * Exibe informações da análise
     */
    protected function displayAnalysisInfo(ArticleCorrection $analysis)
    {
        $data = $analysis->original_data;
        
        $this->table(['Campo', 'Valor'], [
            ['ID', $analysis->_id],
            ['Slug', $analysis->article_slug],
            ['Título', $data['title'] ?? 'N/A'],
            ['Template', $data['template'] ?? 'N/A'],
            ['Problemas Locais', implode(', ', $data['local_analysis']['issues'] ?? [])],
            ['Texto (chars)', strlen($data['introducao'] ?? '')],
            ['Criado', $analysis->created_at->format('d/m/Y H:i:s')]
        ]);
    }

    /**
     * Mostra resultado da análise processada
     */
    protected function showAnalysisResult(ArticleCorrection $analysis)
    {
        if (!isset($analysis->correction_data)) {
            return;
        }

        $result = $analysis->correction_data;
        
        $this->newLine();
        $this->info("📋 Resultado da Análise:");
        
        $needsCorrection = $result['needs_correction'] ? '✅ SIM' : '❌ NÃO';
        $confidence = strtoupper($result['confidence_level'] ?? 'N/A');
        $priority = strtoupper($result['correction_priority'] ?? 'N/A');
        
        $this->line("Precisa correção: {$needsCorrection}");
        $this->line("Confiança: {$confidence}");
        $this->line("Prioridade: {$priority}");
        
        if (!empty($result['problems_found'])) {
            $this->newLine();
            $this->info("🔍 Problemas encontrados:");
            foreach ($result['problems_found'] as $problem) {
                $this->line("• {$problem['type']}: {$problem['description']}");
                if (!empty($problem['text_fragment'])) {
                    $this->line("  Trecho: \"{$problem['text_fragment']}\"");
                }
            }
        }
    }
}
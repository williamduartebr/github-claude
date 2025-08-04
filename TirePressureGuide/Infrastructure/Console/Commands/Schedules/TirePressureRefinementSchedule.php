<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands\Schedules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;
use Src\ContentGeneration\TirePressureGuide\Application\Services\SectionRefinementService;

/**
 * Schedule automático para refinamento de seções
 * 
 * EXECUÇÃO: A cada 2 minutos
 * PROCESSAMENTO: 1 artigo por execução
 * RATE LIMITING: 60 segundos entre chamadas Claude
 */
class TirePressureRefinementSchedule extends Command
{
    protected $signature = 'tire-pressure:refine-sections-schedule
                           {--batch= : Processar apenas artigos de um batch específico}
                           {--dry-run : Preview sem executar}';

    protected $description = 'Schedule automático para refinamento de seções com Claude 3.5 Sonnet';

    private SectionRefinementService $refinementService;

    public function __construct(SectionRefinementService $refinementService)
    {
        parent::__construct();
        $this->refinementService = $refinementService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $batchId = $this->option('batch');
        $isDryRun = $this->option('dry-run');

        try {
            // Verificar ambiente
            if (in_array(app()->environment(), ['local']) && !$isDryRun) {
                return 0;
            }

            // Lock para evitar execuções simultâneas
            $lockKey = 'tire_pressure_refinement_schedule_v2';
            $lock = Cache::lock($lockKey, 180); // 3 minutos

            if (!$lock->get()) {
                Log::info("Schedule de refinamento já em execução");
                return 0;
            }

            try {
                // Verificar rate limiting
                if (!$this->checkRateLimit()) {
                    Log::info("Rate limit ativo, pulando execução");
                    return 0;
                }

                // Buscar próximo artigo
                $article = $this->getNextArticle($batchId);

                if (!$article) {
                    Log::info("Nenhum artigo pendente para refinamento");
                    return 0;
                }

                // Log inicial
                Log::info("🚀 Iniciando refinamento de seções", [
                    'article_id' => $article->_id,
                    'vehicle' => $article->vehicle_data['vehicle_full_name'] ?? 'N/A',
                    'template' => $article->template_type,
                    'batch' => $article->refinement_batch_id
                ]);

                if ($isDryRun) {
                    $this->info("🔍 DRY RUN - Artigo seria processado:");
                    $this->displayArticleInfo($article);
                    return 0;
                }

                // Processar refinamento
                $startTime = microtime(true);
                $success = $this->refinementService->refineArticleSections($article);
                $duration = round(microtime(true) - $startTime, 2);

                if ($success) {
                    Log::info("✅ Refinamento concluído com sucesso", [
                        'article_id' => $article->_id,
                        'duration_seconds' => $duration
                    ]);

                    // Registrar estatísticas
                    $this->recordStats($article, true, $duration);
                } else {
                    Log::error("❌ Falha no refinamento", [
                        'article_id' => $article->_id,
                        'duration_seconds' => $duration
                    ]);

                    // Registrar estatísticas
                    $this->recordStats($article, false, $duration);
                }

                // Registrar uso para rate limiting
                $this->recordRateLimit();

                return 0;
            } finally {
                $lock->release();
            }
        } catch (\Exception $e) {
            Log::error("Erro crítico no schedule de refinamento", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Buscar próximo artigo para processar
     */
    private function getNextArticle(?string $batchId): ?TirePressureArticle
    {
        // Se batch específico foi fornecido
        if ($batchId) {
            return TirePressureArticle::inBatch($batchId)
                ->pendingRefinement()
                ->first();
        }

        // Priorizar artigos em batches ativos
        $articleInBatch = TirePressureArticle::whereNotNull('refinement_batch_id')
            ->pendingRefinement()
            ->orderBy('refinement_batch_position', 'asc')
            ->first();

        if ($articleInBatch) {
            return $articleInBatch;
        }

        // Buscar artigos sem batch (órfãos)
        return TirePressureArticle::readyForRefinement()
            ->whereNull('refinement_batch_id')
            ->pendingRefinement()
            ->orderBy('created_at', 'asc')
            ->first();
    }

    /**
     * Verificar rate limiting
     */
    private function checkRateLimit(): bool
    {
        $lastCall = Cache::get('tire_pressure_refinement_last_call');

        if (!$lastCall) {
            return true;
        }

        $secondsSinceLastCall = now()->diffInSeconds($lastCall);

        // Mínimo 60 segundos entre chamadas
        if ($secondsSinceLastCall < 60) {
            Log::info("Rate limit: apenas {$secondsSinceLastCall}s desde última chamada");
            return false;
        }

        return true;
    }

    /**
     * Registrar uso para rate limiting
     */
    private function recordRateLimit(): void
    {
        Cache::put('tire_pressure_refinement_last_call', now(), 3600);
    }

    /**
     * Registrar estatísticas
     */
    private function recordStats(TirePressureArticle $article, bool $success, float $duration): void
    {
        $statsKey = 'tire_pressure_refinement_stats_' . now()->format('Y-m-d');
        $stats = Cache::get($statsKey, [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'total_duration' => 0,
            'vehicles' => [],
            'templates' => [],
            'batches' => []
        ]);

        $stats['total']++;
        $stats[$success ? 'success' : 'failed']++;
        $stats['total_duration'] += $duration;

        // Contadores por veículo
        $vehicle = $article->vehicle_data['vehicle_full_name'] ?? 'Unknown';
        $stats['vehicles'][$vehicle] = ($stats['vehicles'][$vehicle] ?? 0) + 1;

        // Contadores por template
        $template = $article->template_type;
        $stats['templates'][$template] = ($stats['templates'][$template] ?? 0) + 1;

        // Contadores por batch
        if ($article->refinement_batch_id) {
            $batch = $article->refinement_batch_id;
            $stats['batches'][$batch] = ($stats['batches'][$batch] ?? 0) + 1;
        }

        Cache::put($statsKey, $stats, 86400); // 24 horas
    }

    /**
     * Mostrar informações do artigo (dry run)
     */
    private function displayArticleInfo(TirePressureArticle $article): void
    {
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $article->_id],
                ['Veículo', $article->vehicle_data['vehicle_full_name'] ?? 'N/A'],
                ['Template', $article->template_type],
                ['Slug', $article->slug],
                ['Batch', $article->refinement_batch_id ?? 'Sem batch'],
                ['Tentativas', $article->refinement_attempts ?? 0],
                ['vehicle_data v3.1', $article->vehicle_data_version === 'v3.1' ? '✅' : '❌']
            ]
        );

        // Verificar seções atuais
        $sections = [
            'intro' => !empty($article->sections_intro),
            'pressure_table' => !empty($article->sections_pressure_table),
            'how_to_calibrate' => !empty($article->sections_how_to_calibrate),
            'middle_content' => !empty($article->sections_middle_content),
            'faq' => !empty($article->sections_faq),
            'conclusion' => !empty($article->sections_conclusion)
        ];

        $this->newLine();
        $this->info("Status das Seções:");
        foreach ($sections as $section => $hasContent) {
            $status = $hasContent ? '✅ Preenchida' : '❌ Vazia';
            $this->line("  • {$section}: {$status}");
        }
    }

    /**
     * Registrar schedule no Kernel
     */
    public static function register($schedule): void
    {
        $schedule->command('tire-pressure:refine-sections-schedule')
            ->everyTwoMinutes()
            ->withoutOverlapping(180)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tire-pressure-refinement.log'));
    }
}

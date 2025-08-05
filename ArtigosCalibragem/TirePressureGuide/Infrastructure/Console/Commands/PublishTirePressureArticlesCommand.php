<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TirePressureGuide\Application\Services\TirePressureGuideApplicationService;

class PublishTirePressureArticlesCommand extends Command
{
    protected $signature = 'tire-pressure-guide:publish-to-temp 
                           {--status=claude_enhanced : Status of articles to publish}
                           {--limit=100 : Maximum number of articles to publish}
                           {--dry-run : Show what would be published without updating}
                           {--confirm : Skip confirmation prompt}';

    protected $description = 'Publish tire pressure calibration articles to TempArticle collection with refined Claude sections';

    public function handle(TirePressureGuideApplicationService $service): int
    {
        $status = $this->option('status');
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $skipConfirm = $this->option('confirm');

        $this->info("Publishing tire pressure calibration articles to TempArticle collection...");
        $this->info("Source Status: {$status}");
        $this->info("Limit: {$limit}");

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No articles will be published");
        }

        // Mostrar estatísticas atuais
        $stats = $service->getArticleStats();
        $this->info("Current articles in tire_pressure_articles:");
        $this->line("  Total: {$stats['total']}");
        $this->line("  Generated: {$stats['generated']}");
        $this->line("  Claude Enhanced: {$stats['claude_enhanced']}");
        $this->line("  Published: {$stats['published']}");
        $this->line("  Sections Ready for Refinement: {$stats['sections_ready']}");
        $this->line("  Sections Complete (Ready for Publish): {$stats['sections_complete']}");
        $this->newLine();

        // Verificar se há artigos com seções refinadas completas
        if ($stats['sections_complete'] === 0) {
            $this->warn("No articles found with complete Claude sections refinement");
            return self::SUCCESS;
        }

        if ($stats[$status] === 0) {
            $this->warn("No articles found with status: {$status}");
            return self::SUCCESS;
        }

        $articlesToPublish = min($stats[$status], $limit);

        if (!$skipConfirm && !$dryRun) {
            if (!$this->confirm("Do you want to publish {$articlesToPublish} tire pressure articles to TempArticle collection?")) {
                $this->info("Publication cancelled.");
                return self::SUCCESS;
            }
        }

        $this->info("Processing {$articlesToPublish} articles...");

        $progressBar = $this->output->createProgressBar();
        $progressBar->setFormat('verbose');

        $result = $service->publishToTempArticles(
            $status,
            $limit,
            $dryRun,
            function ($current, $total) use ($progressBar) {
                $progressBar->setMaxSteps($total);
                $progressBar->setProgress($current);
            }
        );

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Publication completed!");
        $this->info("Articles published to TempArticle: {$result->published}");
        $this->info("Articles skipped (duplicates): {$result->skipped}");
                        $this->info("Articles failed (incomplete sections refinement): {$result->failed}");

        if ($result->failed > 0) {
            $this->error("Articles failed: {$result->failed}");
        }

        // Mostrar estatísticas do TempArticle
        if (!$dryRun) {
            $tempArticleStats = $service->getTempArticleStats();
            $this->newLine();
            $this->info("TempArticle collection status:");
            $this->line("  Total articles: {$tempArticleStats['total']}");
            $this->line("  Tire pressure articles: {$tempArticleStats['tire_pressure_guide']}");
        }

        // Mostrar erros se houver
        if (!empty($result->errors)) {
            $this->newLine();
            $this->error("Errors encountered:");
            foreach (array_slice($result->errors, 0, 10) as $error) {
                $this->line("  - {$error}");
            }

            if (count($result->errors) > 10) {
                $this->line("  ... and " . (count($result->errors) - 10) . " more errors");
            }
        }

        // Sugestões finais
        if (!$dryRun && $result->published > 0) {
            $this->newLine();
            $this->info("Published articles are now available in the temp_articles collection.");
            $this->info("All articles were published with status 'draft' and category 'Calibragem de Pneus'.");
            $this->info("Content generated from refined Claude sections.");
        }

        return $result->failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
<?php

namespace Src\ContentGeneration\WhenToChangeTires\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\WhenToChangeTires\Application\Services\WhenToChangeTiresApplicationService;


class PublishTireArticlesCommand extends Command
{
    protected $signature = 'when-to-change-tires:publish-to-temp 
                           {--status=generated : Status of articles to publish}
                           {--limit=100 : Maximum number of articles to publish}
                           {--dry-run : Show what would be published without updating}
                           {--confirm : Skip confirmation prompt}';

    protected $description = 'Publish tire change articles to TempArticle collection with category and publication data';

    public function handle(WhenToChangeTiresApplicationService $service): int
    {
        $status = $this->option('status');
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $skipConfirm = $this->option('confirm');

        $this->info("Publishing tire change articles to TempArticle collection...");
        $this->info("Source Status: {$status}");
        $this->info("Limit: {$limit}");

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No articles will be published");
        }

        // Mostrar estatísticas atuais
        $stats = $service->getArticleStats();
        $this->info("Current articles in tire_change_articles:");
        $this->line("  Total: {$stats['total']}");
        $this->line("  Generated: {$stats['generated']}");
        $this->line("  Published: {$stats['published']}");
        $this->line("  Claude Enhanced: {$stats['claude_enhanced']}");
        $this->newLine();

        if ($stats[$status] === 0) {
            $this->warn("No articles found with status: {$status}");
            return self::SUCCESS;
        }

        $articlesToPublish = min($stats[$status], $limit);

        if (!$skipConfirm && !$dryRun) {
            if (!$this->confirm("Do you want to publish {$articlesToPublish} articles to TempArticle collection?")) {
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

        if ($result->failed > 0) {
            $this->error("Articles failed: {$result->failed}");
        }

        // Mostrar estatísticas do TempArticle
        if (!$dryRun) {
            $tempArticleStats = $service->getTempArticleStats();
            $this->newLine();
            $this->info("TempArticle collection status:");
            $this->line("  Total articles: {$tempArticleStats['total']}");
            $this->line("  Tire change articles: {$tempArticleStats['when_to_change_tires']}");
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
            $this->info("All articles were published with status 'draft' and category 'Pneus e Rodas'.");
        }

        return $result->failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
<?php

namespace Src\ArticleGenerator\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\ArticleGenerator\Infrastructure\Console\RescheduleArticles;
use Src\ArticleGenerator\Infrastructure\Console\AutoSyncVehicleData;
use Src\ArticleGenerator\Infrastructure\Console\SyncVehiclesToMySQL;
use Src\ArticleGenerator\Infrastructure\Console\AssignArticleAuthors;
use Src\ArticleGenerator\Infrastructure\Console\HumanizeArticleDates;
use Src\ArticleGenerator\Infrastructure\Console\MasterPunctuationFix;
use Src\ArticleGenerator\Infrastructure\Console\PublishDraftArticles;
use Src\ArticleGenerator\Infrastructure\Console\CorrectionHealthCheck;
use Src\ArticleGenerator\Infrastructure\Console\CreateArticleSchedule;
use Src\ArticleGenerator\Infrastructure\Console\ScheduleDraftArticles;
use Src\ArticleGenerator\Infrastructure\Console\UpdateRelatedArticles;
use Src\ArticleGenerator\Infrastructure\Console\ProcessArticleAnalysis;
use Src\ArticleGenerator\Infrastructure\Console\ProcessVehicleMetadata;

// Novos comandos do sistema de correção de pontuação
use Src\ArticleGenerator\Infrastructure\Console\FixImportedArticleDates;
use Src\ArticleGenerator\Infrastructure\Console\PunctuationStatsCommand;
use Src\ArticleGenerator\Infrastructure\Console\ProcessScheduledArticles;
use Src\ArticleGenerator\Infrastructure\Console\ProcessArticleCorrections;
use Src\ArticleGenerator\Infrastructure\Console\AnalyzeArticlesPunctuation;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Comandos existentes
                PublishDraftArticles::class,
                UpdateRelatedArticles::class,
                HumanizeArticleDates::class,
                AssignArticleAuthors::class,
                ProcessVehicleMetadata::class,
                SyncVehiclesToMySQL::class,
                FixImportedArticleDates::class,
                
                // Novos comandos - Sistema de Correção de Pontuação
                AnalyzeArticlesPunctuation::class,
                ProcessArticleAnalysis::class,
                ProcessArticleCorrections::class,
                MasterPunctuationFix::class,
                CorrectionHealthCheck::class,
                PunctuationStatsCommand::class,

                // Sistema de Agendamento Inteligente!
                AutoSyncVehicleData::class,
                CreateArticleSchedule::class,
                ProcessScheduledArticles::class,
                RescheduleArticles::class,
                ScheduleDraftArticles::class,
            ]);
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
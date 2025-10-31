<?php

namespace Src\GenericArticleGenerator\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\GenericArticleGenerator\Commands\PublishGeneratedHumanizedCommand;
use Src\GenericArticleGenerator\Commands\PublishGeneratedDirectCommand;
use Src\GenericArticleGenerator\Commands\SeedOilArticlesCommand;
use Src\GenericArticleGenerator\Infrastructure\Console\GenerateIntermediateCommand;
use Src\GenericArticleGenerator\Infrastructure\Console\GenerateStandardCommand;
use Src\GenericArticleGenerator\Infrastructure\Console\SeedGenerationTempArticlesCommand;

class GenerationCommandServiceProvider extends ServiceProvider
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
                SeedGenerationTempArticlesCommand::class,
                GenerateStandardCommand::class,
                GenerateIntermediateCommand::class,
                SeedOilArticlesCommand::class,
                PublishGeneratedDirectCommand::class,
                PublishGeneratedHumanizedCommand::class,

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

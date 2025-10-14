<?php

namespace Src\InfoArticleGenerator\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\InfoArticleGenerator\Infrastructure\Console\GenerateIntermediateCommand;
use Src\InfoArticleGenerator\Infrastructure\Console\GenerateStandardCommand;
use Src\InfoArticleGenerator\Infrastructure\Console\SeedGenerationTempArticlesCommand;

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

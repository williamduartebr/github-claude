<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Providers;


use Illuminate\Support\ServiceProvider;
use Src\ContentGeneration\TireCalibration\Infrastructure\Commands\CopyCalibrationArticlesCommand;


class TireCalibrationServiceProvider extends ServiceProvider
{
    protected $commands = [
        CopyCalibrationArticlesCommand::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerCommands();
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }
}

<?php
namespace Src\TestimonyCorrection\Providers;

use Illuminate\Support\ServiceProvider;
use Src\TestimonyCorrection\Infrastructure\Console\Commands\ProcessTestimoniesCommand;

class TestimonyCorrectionProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([ ProcessTestimoniesCommand::class ]);
        }
    }
    public function boot(): void {}
}

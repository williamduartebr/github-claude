<?php
namespace Src\UnifiedContentScheduler\Console\Schedules;

use Illuminate\Console\Scheduling\Schedule;

class GuideDataCenterSchedule
{
    public static function register(Schedule $schedule): void
    {
        $cmd = 'guide-data:generate --limit=4';
        $log = storage_path('logs/guide-data-generation.log');

        $schedule->command($cmd)
            ->cron('*/30 7-19 * * 1-5')
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo($log);

        $schedule->command($cmd)
            ->cron('0 */3 * * 1-5')
            ->timezone('America/Sao_Paulo')
            ->runInBackground()
            ->appendOutputTo($log);
    }
}

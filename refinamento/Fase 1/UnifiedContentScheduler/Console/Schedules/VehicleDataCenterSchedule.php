<?php
namespace Src\UnifiedContentScheduler\Console\Schedules;

use Illuminate\Console\Scheduling\Schedule;

class VehicleDataCenterSchedule
{
    public static function register(Schedule $schedule): void
    {
        $cmd = 'vehicle-data:generate --limit=3';
        $log = storage_path('logs/vehicle-data-generation.log');

        $schedule->command($cmd)
            ->cron('0 * * * 1-5')
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping(15)
            ->runInBackground()
            ->appendOutputTo($log);

        $schedule->command($cmd)
            ->cron('30 */2 * * 6,0')
            ->timezone('America/Sao_Paulo')
            ->runInBackground()
            ->appendOutputTo($log);
    }
}

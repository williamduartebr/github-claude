<?php
namespace Src\UnifiedContentScheduler\Console\Schedules;

use Illuminate\Console\Scheduling\Schedule;

class HumanizationEscalationSchedule
{
    public static function register(Schedule $schedule): void
    {
        $cmd = 'content:humanize --limit=1';
        $log = storage_path('logs/humanization-escalation.log');

        $schedule->command($cmd)
            ->cron('*/25 7-18 * * 1-5')
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo($log);

        $schedule->command($cmd)
            ->cron('5 13 * * 1-5')
            ->timezone('America/Sao_Paulo')
            ->runInBackground()
            ->appendOutputTo($log);

        $schedule->command($cmd)
            ->cron('*/45 19-23 * * 1-5')
            ->timezone('America/Sao_Paulo')
            ->runInBackground()
            ->appendOutputTo($log);

        $schedule->command($cmd)
            ->cron('0 */2 * * *')
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo($log);

        $schedule->command($cmd)
            ->cron('*/5 * * * 0')
            ->timezone('America/Sao_Paulo')
            ->runInBackground()
            ->appendOutputTo($log);
    }
}

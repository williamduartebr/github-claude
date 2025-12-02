<?php
namespace Src\UnifiedContentScheduler\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Src\UnifiedContentScheduler\Console\Schedules\VehicleDataCenterSchedule;
use Src\UnifiedContentScheduler\Console\Schedules\GuideDataCenterSchedule;
use Src\UnifiedContentScheduler\Console\Schedules\HumanizationEscalationSchedule;

class UnifiedContentSchedulerProvider extends ServiceProvider
{
    public function boot(Schedule $schedule): void
    {
        VehicleDataCenterSchedule::register($schedule);
        GuideDataCenterSchedule::register($schedule);
        HumanizationEscalationSchedule::register($schedule);
    }
}

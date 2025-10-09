<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands\Schedules;

use Illuminate\Console\Scheduling\Schedule;

/**
 * SimplePublishArticleSchedule - Registry Simples para Publicação
 * 
 * ESTRATÉGIA SIMPLES:
 * - Roda o comando de publicação em intervalos regulares
 * - Sem métricas, sem health checks, sem complicação
 * - Apenas executa quando o crontab chamar
 * 
 * @author Engenheiro de Software Elite
 * @version 1.0 - Simplicidade Total
 */
class SimplePublishArticleSchedule
{
    public static function register(Schedule $schedule): void
    {
        // Publicação principal - a cada 15 minutos durante horário comercial
        $schedule->command('articles-temp:publish-drafts-humanized --limit=1')
            ->weekdays()
            ->everyFifteenMinutes()
            ->timezone('America/Sao_Paulo')
            ->between('7:00', '17:00')
            ->appendOutputTo(storage_path('logs/publish-drafts-humanized.log')); // Salva logs para monitoramento

        // Publicação reduzida - a cada 30 minutos fora do horário comercial  
        $schedule->command('articles-temp:publish-drafts-humanized --limit=1')
            ->weekdays()
            ->everyThirtyMinutes()
            ->timezone('America/Sao_Paulo')
            ->between('17:00', '21:00')
            ->appendOutputTo(storage_path('logs/publish-drafts-humanized.log')); // Salva logs para monitoramento


        // Publicação fins de semana - a cada 30 minutos
        $schedule->command('articles-temp:publish-drafts-humanized --limit=1')
            ->saturdays()
            ->everyThirtyMinutes()
            ->timezone('America/Sao_Paulo')
            ->between('7:00', '12:00')
            ->appendOutputTo(storage_path('logs/publish-drafts-humanized.log')); // Salva logs para monitoramento
    }
}

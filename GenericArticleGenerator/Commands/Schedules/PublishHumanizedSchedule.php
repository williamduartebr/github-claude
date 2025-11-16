<?php

namespace Src\GenericArticleGenerator\Commands\Schedules;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;

/**
 * PublishHumanizedSchedule
 * 
 * Agendamento automático de publicação de artigos com datas humanizadas.
 * 
 * ESTRATÉGIA DE PUBLICAÇÃO:
 * - Segunda a Sexta: 6 execuções no horário comercial (9h às 18h30)
 * - Sábado: 3 execuções (10h, 13h, 16h)
 * - Domingo: SEM PUBLICAÇÃO
 * 
 * HORÁRIOS:
 * Segunda-Sexta: 09:00, 11:00, 13:00, 15:00, 17:00, 18:30
 * Sábado: 10:00, 13:00, 16:00
 * 
 * @author Claude Sonnet 4.5
 * @version 2.0 - Clean
 */
class PublishHumanizedSchedule
{
    private const LOG_FILE = 'logs/publish-humanized-weekdays.log';

    /**
     * Registrar tarefas agendadas de publicação humanizada
     */
    public static function register(Schedule $schedule): void
    {
        // ========================================
        // SEGUNDA A SEXTA - 6 PUBLICAÇÕES/DIA
        // ========================================

        // 05:00 UTC (09:00 horário local) - Início do expediente
        self::scheduleWeekdayPublication($schedule, '05:00', 'Início do expediente');

        // 07:00 UTC (11:00 horário local) - Meio da manhã
        self::scheduleWeekdayPublication($schedule, '07:00', 'Meio da manhã');

        // 09:00 UTC (13:00 horário local) - Após almoço
        self::scheduleWeekdayPublication($schedule, '09:00', 'Após almoço');

        // 11:00 UTC (15:00 horário local) - Meio da tarde
        self::scheduleWeekdayPublication($schedule, '11:00', 'Meio da tarde');

        // 13:00 UTC (17:00 horário local) - Final da tarde
        self::scheduleWeekdayPublication($schedule, '13:00', 'Final da tarde');

        // 14:30 UTC (18:30 horário local) - Fim do expediente
        self::scheduleWeekdayPublication($schedule, '14:30', 'Fim do expediente');

        // ========================================
        // SÁBADO - 3 PUBLICAÇÕES/DIA
        // ========================================

        // 06:00 UTC (10:00 horário local) - Manhã
        self::scheduleSaturdayPublication($schedule, '06:00', 'Manhã');

        // 09:00 UTC (13:00 horário local) - Tarde
        self::scheduleSaturdayPublication($schedule, '09:00', 'Tarde');

        // 12:00 UTC (16:00 horário local) - Final da tarde
        self::scheduleSaturdayPublication($schedule, '12:00', 'Final da tarde');

        // ========================================
        // MONITORAMENTO
        // ========================================

        // Verificar estoque - todo dia às 08:00
        $schedule->call(fn() => self::checkStock())
            ->dailyAt('08:00')
            ->timezone('America/Sao_Paulo');
    }

    /**
     * Agendar publicação de segunda a sexta
     */
    private static function scheduleWeekdayPublication(Schedule $schedule, string $time, string $label): void
    {
        $schedule->command('generated-article:publish-humanized --limit=1 --auto')
            ->weekdays()
            ->dailyAt($time)
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo(storage_path(self::LOG_FILE))
            ->before(fn() => self::logBefore($time, $label, 'weekday'))
            ->onSuccess(fn() => self::logSuccess($time))
            ->onFailure(fn() => self::logFailure($time));
    }

    /**
     * Agendar publicação de sábado
     */
    private static function scheduleSaturdayPublication(Schedule $schedule, string $time, string $label): void
    {
        $schedule->command('generated-article:publish-humanized --limit=1 --auto')
            ->saturdays()
            ->dailyAt($time)
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo(storage_path(self::LOG_FILE))
            ->before(fn() => self::logBefore($time, $label, 'saturday'))
            ->onSuccess(fn() => self::logSuccess($time))
            ->onFailure(fn() => self::logFailure($time));
    }

    /**
     * Log antes da execução
     */
    private static function logBefore(string $time, string $label, string $type): void
    {
        $available = self::getAvailableCount();

        Log::info("PublishHumanized: Iniciando publicação - {$label} ({$time})", [
            'type' => $type,
            'available' => $available,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);

        if ($available === 0) {
            Log::warning("PublishHumanized: NENHUM artigo disponível! Execute: php artisan temp-article:generate-standard");
        }
    }

    /**
     * Log sucesso
     */
    private static function logSuccess(string $time): void
    {
        Log::info("PublishHumanized: Publicação concluída com sucesso ({$time})", [
            'remaining' => self::getAvailableCount(),
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Log falha
     */
    private static function logFailure(string $time): void
    {
        Log::error("PublishHumanized: Falha na publicação ({$time})", [
            'available' => self::getAvailableCount(),
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Verificar estoque de artigos
     */
    private static function checkStock(): void
    {
        try {
            $available = self::getAvailableCount();
            $dailyNeed = 6; // Segunda-Sexta
            $daysOfStock = $available > 0 ? floor($available / $dailyNeed) : 0;

            $level = match (true) {
                $daysOfStock < 2 => '🔴 CRÍTICO',
                $daysOfStock < 5 => '🟡 BAIXO',
                $daysOfStock < 7 => '🟢 MODERADO',
                default => '🟢 SAUDÁVEL'
            };

            Log::info("PublishHumanized: Verificação de estoque", [
                'available' => $available,
                'days_of_stock' => $daysOfStock,
                'level' => $level,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            if ($daysOfStock < 2) {
                Log::critical("PublishHumanized: ESTOQUE CRÍTICO! Menos de 2 dias. Execute: php artisan temp-article:generate-standard --limit=30");
            } elseif ($daysOfStock < 5) {
                Log::warning("PublishHumanized: Estoque baixo. Considere gerar mais artigos.");
            }
        } catch (\Exception $e) {
            Log::error("PublishHumanized: Erro ao verificar estoque", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obter quantidade de artigos disponíveis
     */
    private static function getAvailableCount(): int
    {
        try {
            return GenerationTempArticle::where('generation_status', 'generated')
                ->whereNull('published_article_id')
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}

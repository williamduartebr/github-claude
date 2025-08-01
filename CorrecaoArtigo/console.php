<?php

use Illuminate\Foundation\Inspiring;
use App\Console\Schedules\RssSchedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Schedules\BackupSchedule;
use App\Console\Schedules\SitemapSchedule;
use App\Console\Schedules\OilTableSchedule;
use App\Console\Schedules\MaintenanceSchedule;
use App\Console\Schedules\PunctuationSchedule;
use App\Console\Schedules\ArticleSchedulingSchedule;

// ========================================
// IMPORTS DOS SCHEDULES ORGANIZADOS
// ========================================
use App\Console\Schedules\OilRecommendationSchedule;
use App\Console\Schedules\TireRecommendationSchedule;
use Src\ContentGeneration\TireSchedule\Console\Schedules\TireCorrectionSchedule;
use Src\ContentGeneration\ReviewSchedule\Console\Schedules\SyncBlogReviewSchedule;
use Src\ContentGeneration\ReviewSchedule\Console\Schedules\PriceCorrectionSchedule;
use Src\ContentGeneration\ReviewSchedule\Console\Schedules\IntroductionCorrectionSchedule;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands\Schedules\SyncBlogTiresPressureSchedule;
use Src\ContentGeneration\WhenToChangeTires\Infrastructure\Console\Commands\Schedules\SyncBlogWhenToChangeTiresSchedule;


// ========================================
// COMANDO PADRÃO DO LARAVEL
// ========================================
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ========================================
// REGISTRO DE TODOS OS SCHEDULES
// ========================================

// Método alternativo usando closure para garantir que temos a instância correta do Schedule
Schedule::macro('registerCustomSchedules', function () {
    // Sitemap: Geração e submissão de sitemaps
    SitemapSchedule::register($this);

    // RSS: Feeds de conteúdo
    RssSchedule::register($this);

    // Backups: MongoDB e MySQL
    BackupSchedule::register($this);

    // ========================================
    // AGENDAMENTO INTELIGENTE DE ARTIGOS
    // ========================================
    ArticleSchedulingSchedule::register($this);


    // Correção de Pontuação: Sistema inteligente de correções
    // PunctuationSchedule::register($this);

    // Manutenção: Limpeza e verificações do sistema
    // MaintenanceSchedule::register($this);

    // Recomendações de Óleo: Geração automatizada
    // OilRecommendationSchedule::register($this);

    // Recomendações de Pneus: Geração automatizada
    // TireRecommendationSchedule::register($this);

    // Tabelas de Óleo: Geração automatizada
    // OilTableSchedule::register($this);

    // Conogramas de Revisão: Sicronizar Data:
    // SyncBlogReviewSchedule::register($this);

    // Quando Trocar os Pneus: Sicronizar Data:
    // SyncBlogWhenToChangeTiresSchedule::register($this);

    // SyncBlogTiresPressureSchedule::register($this);


    // ========================================
    // Correções de Preço Conograma de Revisão
    // ========================================
    // PriceCorrectionSchedule::register($this);

    // ========================================
    // 🎨 CORREÇÕES DE INTRODUÇÃO E CONSIDERAÇÕES FINAIS
    // ========================================
    // IntroductionCorrectionSchedule::register($this);

    // ========================================
    // Correções de Guia sobre Quando Trocar Pneus
    // ========================================
    // TireCorrectionSchedule::register($this);
});

// Executar o registro dos schedules
Schedule::registerCustomSchedules();


// ========================================
// SCHEDULES LEGADOS (COMENTADOS)
// ========================================


Schedule::command('tire-pressure:correct-vehicle-data-schedule')
    ->cron('*/5 * * * *')
    // ->everyFiveMinutes()
    // ->withoutOverlapping()
    // ->runInBackground()
    ->appendOutputTo(storage_path('logs/tire-pressure-correction.log')); // Log dedicado


// Processar artigos agendados a cada 5 minutos
// Schedule::command('articles:process-scheduled --batch-size=30 --force')
//     ->everyFiveMinutes()
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->appendOutputTo(storage_path('logs/process-scheduled.log'));


// WordPress Sync - Desabilitado
// Schedule::command('app:sync-wordpress-posts')
//     ->everyFiveMinutes()
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->appendOutputTo(storage_path('logs/wordpress-sync.log'));

// Reset Failed Sync - Desabilitado
// Schedule::command('app:reset-failed-sync-posts')
//     ->everyFiveMinutes()
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->appendOutputTo(storage_path('logs/reset-failed-sync.log'));

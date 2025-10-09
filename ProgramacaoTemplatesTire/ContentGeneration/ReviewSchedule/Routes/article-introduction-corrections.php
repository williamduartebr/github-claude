<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Http\Controllers\ArticleIntroductionCorrectionController;

/*
|--------------------------------------------------------------------------
| Article Introduction Correction API Routes
|--------------------------------------------------------------------------
|
| Rotas para o sistema de correção humanizada de introdução e considerações
| finais. Todas as rotas são protegidas e devem ser acessadas com autenticação.
|
*/

Route::prefix('api/v1/article-corrections/introduction')
    ->middleware(['api', 'auth:sanctum']) // Protegido com Sanctum
    ->name('api.article-corrections.introduction.')
    ->group(function () {

        // 📊 Estatísticas gerais
        Route::get('/stats', [ArticleIntroductionCorrectionController::class, 'getStats'])
            ->name('stats');

        // 📈 Estatísticas detalhadas
        Route::get('/stats/detailed', [ArticleIntroductionCorrectionController::class, 'getDetailedStats'])
            ->name('stats.detailed');

        // 📋 Listar correções
        Route::get('/corrections', [ArticleIntroductionCorrectionController::class, 'listCorrections'])
            ->name('corrections.list');

        // 🔍 Buscar correção específica
        Route::get('/corrections/{slug}', [ArticleIntroductionCorrectionController::class, 'getCorrection'])
            ->name('corrections.show')
            ->where('slug', '[a-z0-9\-]+');

        // 🆕 Criar correção para artigo específico
        Route::post('/corrections', [ArticleIntroductionCorrectionController::class, 'createCorrection'])
            ->name('corrections.create');

        // ⚡ Processar correção específica
        Route::post('/corrections/{slug}/process', [ArticleIntroductionCorrectionController::class, 'processCorrection'])
            ->name('corrections.process')
            ->where('slug', '[a-z0-9\-]+');

        // 🔄 Reprocessar correção falhada
        Route::post('/corrections/{slug}/retry', [ArticleIntroductionCorrectionController::class, 'retryCorrection'])
            ->name('corrections.retry')
            ->where('slug', '[a-z0-9\-]+');

        // 🗑️ Deletar correção
        Route::delete('/corrections/{slug}', [ArticleIntroductionCorrectionController::class, 'deleteCorrection'])
            ->name('corrections.delete')
            ->where('slug', '[a-z0-9\-]+');

        // 📦 Operações em lote
        Route::prefix('bulk')->name('bulk.')->group(function () {
            
            // 🆕 Criar correções em lote
            Route::post('/create', [ArticleIntroductionCorrectionController::class, 'bulkCreateCorrections'])
                ->name('create');

            // ⚡ Processar em lote
            Route::post('/process', [ArticleIntroductionCorrectionController::class, 'bulkProcessCorrections'])
                ->name('process');

            // 🧹 Limpar duplicatas
            Route::post('/clean-duplicates', [ArticleIntroductionCorrectionController::class, 'cleanDuplicates'])
                ->name('clean-duplicates');

            // 🗑️ Deletar correções falhadas
            Route::delete('/failed', [ArticleIntroductionCorrectionController::class, 'deleteFailedCorrections'])
                ->name('delete-failed');
        });

        // 🔧 Operações administrativas
        Route::prefix('admin')->name('admin.')->group(function () {
            
            // 🔄 Reset sistema completo
            Route::post('/reset', [ArticleIntroductionCorrectionController::class, 'resetSystem'])
                ->name('reset');

            // 📊 Relatório de saúde do sistema
            Route::get('/health', [ArticleIntroductionCorrectionController::class, 'systemHealth'])
                ->name('health');

            // 🧪 Teste de conectividade com Claude API
            Route::post('/test-api', [ArticleIntroductionCorrectionController::class, 'testClaudeApi'])
                ->name('test-api');

            // 📋 Log de atividades recentes
            Route::get('/activity-log', [ArticleIntroductionCorrectionController::class, 'getActivityLog'])
                ->name('activity-log');
        });

        // 📈 Métricas e análises
        Route::prefix('analytics')->name('analytics.')->group(function () {
            
            // 📊 Performance por período
            Route::get('/performance', [ArticleIntroductionCorrectionController::class, 'getPerformanceMetrics'])
                ->name('performance');

            // 🎯 Taxa de sucesso
            Route::get('/success-rate', [ArticleIntroductionCorrectionController::class, 'getSuccessRate'])
                ->name('success-rate');

            // 📈 Tendências
            Route::get('/trends', [ArticleIntroductionCorrectionController::class, 'getTrends'])
                ->name('trends');

            // 🏆 Top artigos corrigidos
            Route::get('/top-corrections', [ArticleIntroductionCorrectionController::class, 'getTopCorrections'])
                ->name('top-corrections');
        });
    });

/*
|--------------------------------------------------------------------------
| Webhook Routes (sem autenticação para integrações)
|--------------------------------------------------------------------------
*/

Route::prefix('webhooks/article-corrections/introduction')
    ->middleware(['api'])
    ->name('webhooks.article-corrections.introduction.')
    ->group(function () {

        // 📡 Webhook para notificações de correções concluídas
        Route::post('/completed', [ArticleIntroductionCorrectionController::class, 'webhookCorrectionCompleted'])
            ->name('completed');

        // ⚠️ Webhook para notificações de falhas
        Route::post('/failed', [ArticleIntroductionCorrectionController::class, 'webhookCorrectionFailed'])
            ->name('failed');
    });
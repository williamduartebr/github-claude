<?php

use Illuminate\Support\Facades\Route;
use Src\AutoInfoCenter\Presentation\Controllers\ArticleController;
use Src\AutoInfoCenter\Presentation\Controllers\TestMockController;
use Src\AutoInfoCenter\Presentation\Controllers\InfoCategoryController;
use Src\AutoInfoCenter\Presentation\Controllers\RecentArticlesController;

Route::middleware('web')->group(function () {

    // Fallback para redirecionar de /info/c/ para /info com código 301
    Route::get('/info/center', function () {
        return redirect('/info/categorias', 301);
    });

    Route::get('/info/oleo-cambio-sintetico-vs-mineral-vale-a-pena-2025', function () {
        return redirect('/info/oleo-cambio-sintetico-vs-mineral-vale-a-pena', 301);
    });

    // ✅ NOVA ROTA: Últimos Artigos Publicados
    Route::get('/info/ultimos-artigos', [RecentArticlesController::class, 'index'])->name('info.recent-articles');

    Route::get('/info/categorias', [InfoCategoryController::class, 'index'])->name('info.category.index');
    // Artigo individual (slug)
    Route::get('/info/{slug}', [ArticleController::class, 'show'])->name('info.article.show');
    Route::get('/info/{slug}/amp', [ArticleController::class, 'amp'])->name('info.article.show.amp');
    // Rota para limpar cache do artigo
    Route::get('/info/{slug}/clear', [ArticleController::class, 'clear'])->name('info.article.clear');


    Route::get('/info/center/{show}', [InfoCategoryController::class, 'show'])->name('info.category.show');
    Route::get('/info/center/{show}/todos-modelos', [InfoCategoryController::class, 'allModels'])->name('info.category.all-models');


    // Route::get('/popular-categories', [InfoCategoryController::class, 'getPopularCategories'])
    //     ->name('info.category.popular-categories');


    Route::get('/info', function () {
        return redirect('/info/categorias', 301);
    });


    // Rotas de teste para mocks
    Route::prefix('test-mocks')->group(function () {
        // 📋 Rota original - Testa todos os mocks
        Route::get('/', [TestMockController::class, 'testAllMocks']);

        // 🆕 NOVA: Renderiza template específico
        Route::get('/render/{filename}', [TestMockController::class, 'renderTemplate']);

        // 🆕 NOVA: Debug dados processados (JSON detalhado)
        Route::get('/debug-processed/{filename}', [TestMockController::class, 'debugProcessedData']);

        // 📋 Rota original - Testa mock específico (deve ficar por último)
        Route::get('/{filename}', [TestMockController::class, 'testMock']);
    });

    // 🔍 Rota original de debug básico
    Route::get('debug-mock/{filename}', [TestMockController::class, 'debugMock']);
});

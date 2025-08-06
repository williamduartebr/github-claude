<?php

use Illuminate\Support\Facades\Route;
use Src\AutoInfoCenter\Presentation\Controllers\ArticleController;
use Src\AutoInfoCenter\Presentation\Controllers\InfoCategoryController;

Route::middleware('web')->group(function () {
    Route::get('/info/categorias', [InfoCategoryController::class, 'index'])->name('info.category.index');
    // Artigo individual (slug)
    Route::get('/info/{slug}', [ArticleController::class, 'show'])->name('info.article.show');
    Route::get('/info/{slug}/amp', [ArticleController::class, 'amp'])->name('info.article.show.amp');  
    
     // Rota para limpar cache do artigo
    Route::get('/info/{slug}/clear', [ArticleController::class, 'clearCache'])->name('info.article.clear-cache'); 

    Route::get('/info/center/{show}', [InfoCategoryController::class, 'show'])->name('info.category.show');
    Route::get('/info/center/{show}/todos-modelos', [InfoCategoryController::class, 'allModels'])->name('info.category.all-models');

    // Fallback para redirecionar de /info/c/ para /info com código 301
    Route::get('/info/center', function () {
        return redirect('/info', 301);
    });

    Route::get('/popular-categories', [InfoCategoryController::class, 'getPopularCategories'])
        ->name('info.category.popular-categories');
        
});

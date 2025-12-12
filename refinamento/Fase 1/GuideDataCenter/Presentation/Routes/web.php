<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\GuideDataCenter\Presentation\Controllers\GuideMakeController;
use Src\GuideDataCenter\Presentation\Controllers\GuideController;
use Src\GuideDataCenter\Presentation\Controllers\GuideCategoryController;
use Src\GuideDataCenter\Presentation\Controllers\GuideClusterController;
use Src\GuideDataCenter\Presentation\Controllers\GuideSearchController;

Route::prefix('guias')
    ->middleware(['web'])
    ->group(function () {

        // Busca
        Route::get('busca/search', [GuideSearchController::class, 'search'])->name('guide.search');
        Route::get('busca/autocomplete', [GuideSearchController::class, 'autocomplete'])->name('guide.autocomplete');
        Route::get('busca/advanced', [GuideSearchController::class, 'advanced'])->name('guide.search.advanced');

        // Categorias
        Route::get('categorias', [GuideCategoryController::class, 'all'])->name('guide.categories');
        Route::get('{category}', [GuideCategoryController::class, 'index'])->name('guide.category');

        // Index
        Route::get('/', [GuideController::class, 'index'])->name('guide.index');

        // GET /guias/marca/{make}
        // Exemplo: /guias/marca/toyota
        Route::get('marca/{make}', [GuideMakeController::class, 'index'])
            ->name('guide.make')
            ->where('make', '[a-z0-9\-]+');

        // NOVA ROTA: Marca + Modelo
        Route::get('/marca/{make}/{model}', [GuideController::class, 'showMakeModel'])
            ->name('guide.make.model');

        // Categoria + Marca
        Route::get('{category}/{make}', [GuideController::class, 'categoryMake'])
            ->name('guide.category.make')
            ->where(['category' => '[a-z0-9\-]+', 'make' => '[a-z0-9\-]+']);

        // Categoria + Marca
        Route::get('{category}/{make}', [GuideController::class, 'categoryMake'])
            ->name('guide.category.make')
            ->where(['category' => '[a-z0-9\-]+', 'make' => '[a-z0-9\-]+']);

        // Categoria + Marca + Modelo (lista anos)
        Route::get('{category}/{make}/{model}', [GuideController::class, 'categoryMakeModel'])
            ->name('guide.category.make.model')
            ->where(['category' => '[a-z0-9\-]+', 'make' => '[a-z0-9\-]+', 'model' => '[a-z0-9\-]+']);

        // ⭐ NOVO - Categoria + Marca + Modelo + Ano (lista versões)
        Route::get('{category}/{make}/{model}/{year}', [GuideController::class, 'showYear'])
            ->name('guide.year')
            ->where(['category' => '[a-z0-9\-]+', 'make' => '[a-z0-9\-]+', 'model' => '[a-z0-9\-]+', 'year' => '[0-9]{4}']);

        // ⭐ NOVO - Categoria + Marca + Modelo + Ano + Versão (guia completo)
        Route::get('{category}/{make}/{model}/{year}/{version}', [GuideController::class, 'showVersion'])
            ->name('guide.version')
            ->where(['category' => '[a-z0-9\-]+', 'make' => '[a-z0-9\-]+', 'model' => '[a-z0-9\-]+', 'year' => '[0-9]{4}', 'version' => '[a-z0-9\-]+']);

        // Por modelo
        Route::get('{make}/{model}/{year?}', [GuideController::class, 'byModel'])
            ->name('guide.byModel')
            ->where(['make' => '[a-z0-9\-]+', 'model' => '[a-z0-9\-]+', 'year' => '[0-9]+']);

        // Slug genérico (última rota)
        Route::get('{slug}', [GuideController::class, 'show'])
            ->name('guide.show')
            ->where('slug', '[a-z0-9\-]+');
    });

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\GuideDataCenter\Presentation\Controllers\GuideIndexController;
use Src\GuideDataCenter\Presentation\Controllers\GuideMakeController;
use Src\GuideDataCenter\Presentation\Controllers\GuideMakeModelController;
use Src\GuideDataCenter\Presentation\Controllers\GuideYearController;
use Src\GuideDataCenter\Presentation\Controllers\GuideSpecificController;
use Src\GuideDataCenter\Presentation\Controllers\GuideCategoryController;
use Src\GuideDataCenter\Presentation\Controllers\GuideCategoryMakeController;
use Src\GuideDataCenter\Presentation\Controllers\GuideCategoryMakeModelController;
use Src\GuideDataCenter\Presentation\Controllers\GuideSearchController;

/**
 * ✅ ROTAS REFATORADAS - GuideDataCenter
 * 
 * Cada controller agora tem UMA única responsabilidade (SRP)
 * Usando invokable controllers (__invoke) para simplificar
 */

Route::prefix('guias')
    ->middleware(['web'])
    ->name('guide.')
    ->group(function () {

        // ============================================================
        // BUSCA
        // ============================================================
        Route::get('busca/search', [GuideSearchController::class, 'search'])
            ->name('search');

        Route::get('busca/autocomplete', [GuideSearchController::class, 'autocomplete'])
            ->name('autocomplete');

        Route::get('busca/advanced', [GuideSearchController::class, 'advanced'])
            ->name('search.advanced');

        // ============================================================
        // PÁGINA INICIAL
        // ============================================================
        Route::get('/', GuideIndexController::class)
            ->name('index');

        // ============================================================
        // CATEGORIAS
        // ============================================================        
        Route::get('{category}', GuideCategoryController::class)
            ->name('category')
            ->where('category', '[a-z0-9\-]+');

        // ============================================================
        // MARCA
        // ============================================================
        // GET /guias/marca/{make}
        // Exemplo: /guias/marca/toyota
        Route::get('marca/{make}', GuideMakeController::class)
            ->name('make')
            ->where('make', '[a-z0-9\-]+');

        // ============================================================
        // MARCA + MODELO (todas categorias)
        // ============================================================
        // GET /guias/marca/{make}/{model}
        // Exemplo: /guias/marca/toyota/corolla
        Route::get('marca/{make}/{model}', GuideMakeModelController::class)
            ->name('make.model')
            ->where(['make' => '[a-z0-9\-]+', 'model' => '[a-z0-9\-]+']);

        // ============================================================
        // CATEGORIA + MARCA
        // ============================================================
        // GET /guias/{category}/{make}
        // Exemplo: /guias/oleo/toyota
        Route::get('{category}/{make}', GuideCategoryMakeController::class)
            ->name('category.make')
            ->where(['category' => '[a-z0-9\-]+', 'make' => '[a-z0-9\-]+']);

        // ============================================================
        // CATEGORIA + MARCA + MODELO (lista anos)
        // ============================================================
        // GET /guias/{category}/{make}/{model}
        // Exemplo: /guias/oleo/toyota/corolla
        Route::get('{category}/{make}/{model}', GuideCategoryMakeModelController::class)
            ->name('category.make.model')
            ->where([
                'category' => '[a-z0-9\-]+',
                'make' => '[a-z0-9\-]+',
                'model' => '[a-z0-9\-]+'
            ]);

        // ============================================================
        // CATEGORIA + MARCA + MODELO + ANO (lista versões)
        // ============================================================
        // GET /guias/{category}/{make}/{model}/{year}
        // Exemplo: /guias/oleo/toyota/corolla/2025
        Route::get('{category}/{make}/{model}/{year}', GuideYearController::class)
            ->name('year')
            ->where([
                'category' => '[a-z0-9\-]+',
                'make' => '[a-z0-9\-]+',
                'model' => '[a-z0-9\-]+',
                'year' => '[0-9]{4}'
            ]);

        // ============================================================
        // CATEGORIA + MARCA + MODELO + ANO + VERSÃO (guia completo)
        // ============================================================
        // GET /guias/{category}/{make}/{model}/{year}/{version}
        // Exemplo: /guias/oleo/toyota/corolla/2025/gli
        Route::get('{category}/{make}/{model}/{year}/{version}', GuideSpecificController::class)
            ->name('version')
            ->where([
                'category' => '[a-z0-9\-]+',
                'make' => '[a-z0-9\-]+',
                'model' => '[a-z0-9\-]+',
                'year' => '[0-9]{4}',
                'version' => '[a-z0-9\-]+'
            ]);

        // ============================================================
        // SLUG GENÉRICO (última rota - catch-all)
        // ============================================================
        // Esta rota deve ficar por último
        // Route::get('{slug}', [GuideController::class, 'show'])
        //     ->name('show')
        //     ->where('slug', '[a-z0-9\-]+');
    });

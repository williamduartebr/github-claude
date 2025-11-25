<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\GuideDataCenter\Presentation\Controllers\GuideController;
use Src\GuideDataCenter\Presentation\Controllers\GuideCategoryController;
use Src\GuideDataCenter\Presentation\Controllers\GuideClusterController;
use Src\GuideDataCenter\Presentation\Controllers\GuideSearchController;

/*
|--------------------------------------------------------------------------
| GuideDataCenter Routes
|--------------------------------------------------------------------------
|
| Rotas do módulo de guias automotivos.
| Prefixo: /guias
|
*/

Route::prefix('guias')
    ->middleware(['web'])
    ->group(function () {
        
        /*
        |--------------------------------------------------------------------------
        | Busca
        |--------------------------------------------------------------------------
        */
        
        // Busca de guias
        Route::get('busca/search', [GuideSearchController::class, 'search'])
            ->name('guide.search');
        
        // Autocomplete para busca
        Route::get('busca/autocomplete', [GuideSearchController::class, 'autocomplete'])
            ->name('guide.autocomplete');
        
        // Busca avançada
        Route::get('busca/advanced', [GuideSearchController::class, 'advanced'])
            ->name('guide.search.advanced');
        
        /*
        |--------------------------------------------------------------------------
        | Categorias
        |--------------------------------------------------------------------------
        */
        
        // Lista todas as categorias
        Route::get('categorias', [GuideCategoryController::class, 'all'])
            ->name('guide.categories');
        
        // Guias por categoria
        Route::get('categoria/{category}', [GuideCategoryController::class, 'index'])
            ->name('guide.category')
            ->where('category', '[a-z0-9\-]+');
        
        /*
        |--------------------------------------------------------------------------
        | Clusters
        |--------------------------------------------------------------------------
        */
        
        // Cluster por tipo
        Route::get('cluster/tipo/{type}', [GuideClusterController::class, 'byType'])
            ->name('guide.cluster.type')
            ->where('type', '[a-z\-]+');
        
        // Cluster por veículo
        Route::get('cluster/{make}/{model}/{year?}', [GuideClusterController::class, 'show'])
            ->name('guide.cluster')
            ->where([
                'make' => '[a-z0-9\-]+',
                'model' => '[a-z0-9\-]+',
                'year' => '[0-9\-]+'
            ]);
        
        /*
        |--------------------------------------------------------------------------
        | Listagem
        |--------------------------------------------------------------------------
        */
        
        // Lista todos os guias
        Route::get('/', [GuideController::class, 'index'])
            ->name('guide.index');
        
        /*
        |--------------------------------------------------------------------------
        | Guias por Veículo
        |--------------------------------------------------------------------------
        */
        
        // Guias por marca/modelo/ano
        Route::get('{make}/{model}/{year?}', [GuideController::class, 'byModel'])
            ->name('guide.byModel')
            ->where([
                'make' => '[a-z0-9\-]+',
                'model' => '[a-z0-9\-]+',
                'year' => '[0-9]+'
            ]);
        
        /*
        |--------------------------------------------------------------------------
        | Guia Individual
        |--------------------------------------------------------------------------
        */
        
        // Exibe guia por slug (deve ser a última rota para não conflitar)
        Route::get('{slug}', [GuideController::class, 'show'])
            ->name('guide.show')
            ->where('slug', '[a-z0-9\-]+');
    });

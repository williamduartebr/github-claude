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
| ⚠️ ORDEM DAS ROTAS É CRÍTICA!
| Rotas mais específicas devem vir ANTES das genéricas.
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
        
        // Guias por categoria (sem marca)
        // Exemplo: /guias/categoria/oleo
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
        
        // Lista todos os guias (página inicial)
        Route::get('/', [GuideController::class, 'index'])
            ->name('guide.index');
        
        /*
        |--------------------------------------------------------------------------
        | Guias por Categoria e Marca
        |--------------------------------------------------------------------------
        | ⚠️ IMPORTANTE: Esta rota deve vir ANTES de {category}/{make}/{model-year}
        | para não ser capturada pela rota mais específica.
        |
        | Exemplos:
        | - /guias/oleo/toyota
        | - /guias/calibragem/honda
        | - /guias/pneus/chevrolet
        */
        
        Route::get('{category}/{make}', [GuideController::class, 'categoryMake'])
            ->name('guides.make')
            ->where([
                'category' => '[a-z0-9\-]+',
                'make' => '[a-z0-9\-]+',
            ]);

        // ⭐ NOVO - Deve vir ANTES de {category}/{make}/{modelYear}
        Route::get('{category}/{make}/{model}', [GuideController::class, 'categoryMakeModel'])
            ->name('guide.category-make-model')
            ->where([
                'category' => '[a-z0-9\-]+',
                'make' => '[a-z0-9\-]+',
                'model' => '[a-z0-9\-]+',
            ]);

        
        /*
        |--------------------------------------------------------------------------
        | Guia Específico por Categoria, Marca, Modelo e Ano
        |--------------------------------------------------------------------------
        | ⚠️ CRÍTICO: Esta rota deve vir ANTES de {make}/{model}/{year?}
        | e ANTES de {slug} para não ser capturada por elas.
        |
        | Esta é a rota para guias específicos individuais.
        |
        | Exemplos:
        | - /guias/oleo/toyota/corolla-2003
        | - /guias/calibragem/honda/civic-2010
        | - /guias/pneus/volkswagen/gol-2016
        | - /guias/revisao/chevrolet/onix-2020
        |
        | Formato: {category}/{make}/{model-year}
        | O ano é parseado do final do model-year no controller.
        */
        
        Route::get('{category}/{make}/{modelYear}', [GuideController::class, 'specific'])
            ->name('guide.specific')
            ->where([
                'category' => '[a-z0-9\-]+',
                'make' => '[a-z0-9\-]+',
                'modelYear' => '[a-z0-9\-]+', // Ex: corolla-2003, civic-2010, hr-v-2018
            ]);
        
        /*
        |--------------------------------------------------------------------------
        | Guias por Veículo (Marca/Modelo/Ano)
        |--------------------------------------------------------------------------
        | Esta rota lista TODOS os guias de um veículo específico.
        | Diferente de guide.specific que mostra UM guia específico.
        */
        
        // Guias por marca/modelo/ano
        // Exemplo: /guias/toyota/corolla/2003
        Route::get('{make}/{model}/{year?}', [GuideController::class, 'byModel'])
            ->name('guide.byModel')
            ->where([
                'make' => '[a-z0-9\-]+',
                'model' => '[a-z0-9\-]+',
                'year' => '[0-9]+'
            ]);
        
        /*
        |--------------------------------------------------------------------------
        | Guia Individual por Slug
        |--------------------------------------------------------------------------
        | ⚠️ IMPORTANTE: Esta rota deve ser a ÚLTIMA para não capturar
        | as outras rotas acima, pois {slug} pode combinar com qualquer padrão.
        |
        | Esta rota é para guias com slugs únicos que não seguem
        | o padrão categoria/marca/modelo-ano.
        */
        
        // Exibe guia por slug
        // Exemplo: /guias/toyota-corolla-oleo-2003
        Route::get('{slug}', [GuideController::class, 'show'])
            ->name('guide.show')
            ->where('slug', '[a-z0-9\-]+');
    });
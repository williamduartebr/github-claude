<?php

use Illuminate\Support\Facades\Route;
use Src\VehicleDataCenter\Presentation\Controllers\VehicleController;
use Src\VehicleDataCenter\Presentation\Controllers\VehicleCatalogController;
use Src\VehicleDataCenter\Presentation\Controllers\VehicleSearchController;
use Src\VehicleDataCenter\Presentation\Controllers\VehicleSpecsController;
use Src\VehicleDataCenter\Presentation\Controllers\VehicleApiController;

// Public Routes
Route::prefix('veiculos')->name('vehicles.')->group(function () {

    // Listagem de marcas
    Route::get('/', [VehicleController::class, 'index'])->name('index');

    // Marca
    Route::get('/{make}', [VehicleController::class, 'showMake'])->name('make');

    // Modelo
    Route::get('/{make}/{model}', [VehicleController::class, 'showModel'])->name('model');

    // Ano
    Route::get('/{make}/{model}/{year}', [VehicleController::class, 'showYear'])->name('year');

    // Versão (ficha técnica completa)
    Route::get('/{make}/{model}/{year}/{version}', [VehicleController::class, 'showVersion'])->name('version');
});

// Catalog Routes
Route::prefix('catalogo')->name('catalog.')->group(function () {
    Route::get('/', [VehicleCatalogController::class, 'index'])->name('index');
    Route::get('/categoria/{category}', [VehicleCatalogController::class, 'category'])->name('category');
});

// Search Routes
Route::prefix('busca')->name('search.')->group(function () {
    Route::get('/', [VehicleSearchController::class, 'index'])->name('index');
    Route::get('/resultados', [VehicleSearchController::class, 'results'])->name('results');
    Route::get('/avancada', [VehicleSearchController::class, 'advanced'])->name('advanced');
});

// Specs Routes
Route::prefix('ficha-tecnica')->name('specs.')->group(function () {
    Route::get('/{versionId}', [VehicleSpecsController::class, 'show'])->name('show');
    Route::get('/{versionId}/json', [VehicleSpecsController::class, 'json'])->name('json');
    Route::get('/comparar', [VehicleSpecsController::class, 'compare'])->name('compare');
});

// API Routes
Route::prefix('api/vehicles')->name('api.vehicles.')->group(function () {

    // Health Check
    Route::get('/health', [VehicleApiController::class, 'healthCheck'])->name('health');

    // Get Vehicle Data
    Route::get('/{versionId}', [VehicleApiController::class, 'getVersion'])->name('get');

    // Search
    Route::get('/search', [VehicleApiController::class, 'search'])->name('search');

    // SEO Data
    Route::get('/{versionId}/seo', [VehicleApiController::class, 'getSeo'])->name('seo');

    // Quick Search
    Route::get('/quick-search', [VehicleController::class, 'quickSearch'])->name('quick_search');

    // By Category
    Route::get('/category/{category}', [VehicleController::class, 'byCategory'])->name('by_category');

    // Popular
    Route::get('/popular', [VehicleController::class, 'popular'])->name('popular');
});

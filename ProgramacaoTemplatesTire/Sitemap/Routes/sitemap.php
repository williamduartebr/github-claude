<?php

use Illuminate\Support\Facades\Route;
use Src\Sitemap\Presentation\Controllers\SitemapController;

/*
|--------------------------------------------------------------------------
| Sitemap Routes
|--------------------------------------------------------------------------
|
| Rotas para sitemaps públicos e APIs de gerenciamento
|
*/

// Rotas públicas dos sitemaps com middleware de otimização
Route::group(['middleware' => 'sitemap'], function () {
    // Sitemap principal
    Route::get('sitemap.xml', [SitemapController::class, 'index'])
        ->name('sitemap.index');
    
    // Sitemaps específicos
    Route::get('sitemap-{filename}.xml', [SitemapController::class, 'show'])
        ->where('filename', '[a-zA-Z0-9\-_]+')
        ->name('sitemap.show');
});

// APIs de gerenciamento
Route::group(['prefix' => 'api/sitemap'], function () {
    // Regenerar todos os sitemaps
    Route::post('regenerate', [SitemapController::class, 'regenerate'])
        ->name('api.sitemap.regenerate');
    
    // Submeter aos motores de busca
    Route::post('submit', [SitemapController::class, 'submit'])
        ->name('api.sitemap.submit');
    
    // Limpar cache
    Route::delete('cache', [SitemapController::class, 'clearCache'])
        ->name('api.sitemap.clear-cache');
    
    // Status dos sitemaps
    Route::get('status', [SitemapController::class, 'status'])
        ->name('api.sitemap.status');
});
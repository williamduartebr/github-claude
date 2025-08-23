<?php

use Illuminate\Support\Facades\Route;
use Src\ArticleGenerator\Presentation\Controllers\ArticleCorrectionController;

/*
|--------------------------------------------------------------------------
| Article Corrections API Routes
|--------------------------------------------------------------------------
|
| Rotas para o sistema de correção de artigos. Todas as rotas são
| prefixadas com 'api/v1/article-corrections'
|
*/

Route::prefix('api/v1/article-corrections')->middleware(['api'])->group(function () {
    
    // Listar todas as correções (com filtros opcionais)
    // GET /api/v1/article-corrections?status=pending&type=introduction_fix
    Route::get('/', [ArticleCorrectionController::class, 'index'])
        ->name('article-corrections.index');
    
    // Criar nova correção para um artigo
    // POST /api/v1/article-corrections
    Route::post('/', [ArticleCorrectionController::class, 'store'])
        ->name('article-corrections.store');
    
    // Visualizar correção específica
    // GET /api/v1/article-corrections/{id}
    Route::get('/{id}', [ArticleCorrectionController::class, 'show'])
        ->name('article-corrections.show');
    
    // Processar uma correção específica
    // POST /api/v1/article-corrections/{id}/process
    Route::post('/{id}/process', [ArticleCorrectionController::class, 'process'])
        ->name('article-corrections.process');
    
    // Processar múltiplas correções em lote
    // POST /api/v1/article-corrections/process-batch
    Route::post('/process-batch', [ArticleCorrectionController::class, 'processBatch'])
        ->name('article-corrections.process-batch');
    
    // Deletar correção (apenas pending ou failed)
    // DELETE /api/v1/article-corrections/{id}
    Route::delete('/{id}', [ArticleCorrectionController::class, 'destroy'])
        ->name('article-corrections.destroy');
    
    // Analisar artigo por slug e detectar problemas
    // GET /api/v1/article-corrections/analyze/{slug}
    Route::get('/analyze/{slug}', [ArticleCorrectionController::class, 'analyzeArticle'])
        ->name('article-corrections.analyze')
        ->where('slug', '[a-zA-Z0-9\-]+');
});

/*
|--------------------------------------------------------------------------
| Exemplos de uso das rotas
|--------------------------------------------------------------------------

# 1. Analisar um artigo para ver se precisa de correção
curl -X GET "http://localhost/api/v1/article-corrections/analyze/oleo-recomendado-renault-kwid"

# 2. Criar correção de introdução
curl -X POST "http://localhost/api/v1/article-corrections" \
  -H "Content-Type: application/json" \
  -d '{
    "article_slug": "oleo-recomendado-renault-kwid",
    "correction_type": "introduction_fix",
    "description": "Corrigir introdução mal formatada"
  }'

# 3. Listar correções pendentes
curl -X GET "http://localhost/api/v1/article-corrections?status=pending"

# 4. Processar uma correção
curl -X POST "http://localhost/api/v1/article-corrections/{id}/process"

# 5. Processar múltiplas correções
curl -X POST "http://localhost/api/v1/article-corrections/process-batch" \
  -H "Content-Type: application/json" \
  -d '{
    "correction_ids": ["id1", "id2", "id3"]
  }'

*/
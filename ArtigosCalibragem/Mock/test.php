<?php

use Illuminate\Support\Facades\Route;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\WordPressSync\Infrastructure\Eloquent\PostDetail;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\WordPressSync\Presentation\Controllers\PostDetailController;

use Src\UrlAnalytics\Presentation\Controllers\UrlAnalyticsController;
use Src\ArticleGenerator\Infrastructure\Services\ArticleGenerationService;
use Src\ContentGeneration\ReviewSchedule\Domain\Entities\ReviewScheduleArticle;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Entities\TireChangeArticle;
use Src\WordPressSync\Presentation\Controllers\PostDetailTireRecommendationController;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Repositories\MongoReviewScheduleArticleRepository;

Route::get('/test-api', function () {
    return view('test-api');
});


// Rotas existentes
Route::get('/extract-urls', [UrlAnalyticsController::class, 'saveUrlsToDatabase']);
Route::get('/post-details', [PostDetailController::class, 'postDetails']);
Route::get('/category-details', [PostDetailController::class, 'categoryDetails']);
Route::get('/category-update', [PostDetailController::class, 'updatePostCategories']);

// Novas rotas
Route::get('/approve-specific-categories', [PostDetailController::class, 'approveSpecificCategories']);
Route::get('/analise-posts', [PostDetailController::class, 'listAnalisePosts']);
Route::get('/approvePostsBySlugsPrefix', [PostDetailController::class, 'approvePostsBySlugsPrefix']);
Route::get('/approved-posts', [PostDetailController::class, 'listApprovedPosts']);
Route::get('/approve-post/{id}', [PostDetailController::class, 'approvePostById']);
Route::get('/approve-post-oil/{id}', [PostDetailController::class, 'approvePostByIdOil']);
Route::get('/remove-approval/{id}', [PostDetailController::class, 'removeApprovalById']);
Route::get('/approve-all-analysis-posts', [PostDetailController::class, 'approveAllAnalysisPosts']);
// Route::get('/set-api-approved-status', [PostDetailController::class, 'setApiApprovedStatus']);
// Route::get('/api-approved-posts', [PostDetailController::class, 'listApiApprovedPosts']);

Route::get('/add-template-for-oleo-recomendado', [PostDetailController::class, 'addTemplateForOleoRecomendado']);

Route::get('/artigos-temporarios', function () {
    return TempArticle::where('status', 'draft')->limit(10)->get();
});

Route::get('/artigos-temporarios-pneu', function () {
    return TempArticle::wh('title', 'new_processed_type', 'updated_at', 'extracted_entities')->get();
});

Route::get('/artigos', function () {
    return Article::limit(10)->get();
});

Route::get('/artigo/{slug}', function ($slug) {
    return Article::where('slug', $slug)->first();
});

Route::get('/tire/{slug}', function ($slug) {
    return TirePressureArticle::where('slug', $slug)->first();
});



Route::get('/artigo-temp/{slug}', function ($slug) {
    return TempArticle::where('new_slug', $slug)->first();
});

Route::get('/categorias', function () {
    return MaintenanceCategory::select(['id', 'name', 'slug', 'created_at', 'updated_at'])->get();
});

Route::get('/confirm-update-status', function () {
    $count = TempArticle::count();
    TempArticle::query()->update(['status' => 'draft']);
    return "Status atualizado para 'draft' em {$count} artigos.";
});

Route::get('/test-gerar-artigo/{id}', function ($id) {
    return app(ArticleGenerationService::class)->generateArticle($id);
});

Route::get('/template', function () {
    return app(\Src\AutoInfoCenter\Presentation\Controllers\InfoArticleModeloController::class)->view();
});

Route::get('/artigos-review-schedule', function () {
    return TempArticle::select()
        ->limit(10)
        ->get();
});

Route::get('/artigo/temp/{slug}', function ($slug) {
    return TempArticle::where('slug', $slug)->first();
});

Route::get('/content-generation/{slug}', function ($slug) {

    $articleRepository = app(MongoReviewScheduleArticleRepository::class);
    return $articleRepository->findBySlug($slug);
});

Route::get('/tire-generation/{slug}', function ($slug) {
    return TireChangeArticle::where('slug', $slug)->first();
});

Route::get('/vehicle-data/{id}', function ($id) {
    return \Src\VehicleData\Domain\Entities\VehicleData::findOrFail($id);
});


// Rotas para Pneus Recomendados
Route::get('/tire-recommendation/filter', [PostDetailTireRecommendationController::class, 'filterTireRecommendationPosts']);
Route::get('/tire-recommendation/approve', [PostDetailTireRecommendationController::class, 'approveTireRecommendationPosts']);
Route::get('/tire-recommendation/add-template', [PostDetailTireRecommendationController::class, 'addTemplateForTireRecommendation']);
Route::get('/tire-recommendation/stats', [PostDetailTireRecommendationController::class, 'getTireRecommendationStats']);


use Src\AutoInfoCenter\Presentation\Controllers\TestMockController;

// Rotas de teste para mocks
Route::prefix('test-mocks')->group(function () {
    Route::get('/', [TestMockController::class, 'testAllMocks']);
    Route::get('/{filename}', [TestMockController::class, 'testMock']);
});
Route::get('debug-mock/{filename}', [TestMockController::class, 'debugMock']);


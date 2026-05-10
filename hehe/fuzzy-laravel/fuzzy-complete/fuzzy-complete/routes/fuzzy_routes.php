<?php

// ═══════════════════════════════════════════════════════════════
// routes/web.php — tambahkan require ini di bagian bawah
// ═══════════════════════════════════════════════════════════════

// require __DIR__.'/fuzzy_web.php';

// ═══════════════════════════════════════════════════════════════
// routes/fuzzy_web.php — Web Admin Routes
// ═══════════════════════════════════════════════════════════════

use App\Http\Controllers\Admin\FuzzyLogicController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/fuzzy')
    ->name('admin.fuzzy.')
    ->middleware(['auth', 'admin']) // sesuaikan middleware admin kamu
    ->group(function () {

    // ── Halaman utama ──
    Route::get('/',    [FuzzyLogicController::class, 'index'])->name('index');
    Route::get('/audit', [FuzzyLogicController::class, 'auditLog'])->name('audit');

    // ── Komponen ──
    Route::get('/components',                      [FuzzyLogicController::class, 'getComponents'])->name('components');
    Route::post('/components',                     [FuzzyLogicController::class, 'storeComponent'])->name('components.store');
    Route::patch('/components/{component}/toggle', [FuzzyLogicController::class, 'toggleComponent'])->name('components.toggle');
    Route::delete('/components/{component}',       [FuzzyLogicController::class, 'deleteComponent'])->name('components.delete');

    // ── Konfigurasi Fuzzy ──
    Route::patch('/components/{component}/threshold',  [FuzzyLogicController::class, 'saveThreshold'])->name('threshold.save');
    Route::patch('/components/{component}/membership', [FuzzyLogicController::class, 'saveMembership'])->name('membership.save');

    // ── Rules ──
    Route::post('/components/{component}/rules',   [FuzzyLogicController::class, 'storeRule'])->name('rules.store');
    Route::patch('/rules/{rule}',                  [FuzzyLogicController::class, 'updateRule'])->name('rules.update');
    Route::delete('/rules/{rule}',                 [FuzzyLogicController::class, 'deleteRule'])->name('rules.delete');

    // ── Test & Chart preview ──
    Route::post('/components/{component}/test',    [FuzzyLogicController::class, 'test'])->name('test');
    Route::post('/chart-data',                     [FuzzyLogicController::class, 'chartData'])->name('chart');
});


// ═══════════════════════════════════════════════════════════════
// routes/api.php — tambahkan ini
// ═══════════════════════════════════════════════════════════════

use App\Http\Controllers\Api\RecommendationController;

Route::middleware('auth:sanctum')->group(function () {

    // ── Motor types & komponen (untuk Flutter) ──
    Route::get('/motor-types',                        [RecommendationController::class, 'motorTypes']);
    Route::get('/motor-types/{motorTypeId}/components',[RecommendationController::class, 'components']);

    // ── Rekomendasi ──
    Route::get('/recommendations/{motorId}',          [RecommendationController::class, 'show']);
    Route::get('/recommendations/{motorId}/scores',   [RecommendationController::class, 'scores']);
    Route::get('/recommendations/{motorId}/history',  [RecommendationController::class, 'history']);
});

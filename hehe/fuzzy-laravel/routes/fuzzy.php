<?php

use App\Http\Controllers\Admin\FuzzyLogicController;
use Illuminate\Support\Facades\Route;

// Taruh di dalam group middleware admin kamu
Route::prefix('admin/fuzzy')->name('admin.fuzzy.')->middleware(['auth', 'admin'])->group(function () {

    // Halaman utama
    Route::get('/',                                  [FuzzyLogicController::class, 'index'])->name('index');

    // Data komponen
    Route::get('/components',                        [FuzzyLogicController::class, 'getComponents'])->name('components');
    Route::post('/components',                       [FuzzyLogicController::class, 'storeComponent'])->name('components.store');
    Route::patch('/components/{component}/toggle',   [FuzzyLogicController::class, 'toggleComponent'])->name('components.toggle');
    Route::delete('/components/{component}',         [FuzzyLogicController::class, 'deleteComponent'])->name('components.delete');

    // Konfigurasi
    Route::patch('/components/{component}/threshold',   [FuzzyLogicController::class, 'saveThreshold'])->name('threshold.save');
    Route::patch('/components/{component}/membership',  [FuzzyLogicController::class, 'saveMembership'])->name('membership.save');

    // Rules
    Route::post('/components/{component}/rules',        [FuzzyLogicController::class, 'storeRule'])->name('rules.store');
    Route::patch('/rules/{rule}',                        [FuzzyLogicController::class, 'updateRule'])->name('rules.update');
    Route::delete('/rules/{rule}',                       [FuzzyLogicController::class, 'deleteRule'])->name('rules.delete');

    // Test & Chart
    Route::post('/components/{component}/test',      [FuzzyLogicController::class, 'test'])->name('test');
    Route::post('/chart-data',                       [FuzzyLogicController::class, 'chartData'])->name('chart');
});

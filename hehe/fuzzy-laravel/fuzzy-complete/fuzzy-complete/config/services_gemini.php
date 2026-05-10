<?php

// config/services.php
// Tambahkan key 'gemini' ke array yang sudah ada

return [

    // ... key lain yang sudah ada ...

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

];


// ─────────────────────────────────────────────────────────
// .env — tambahkan baris ini
// ─────────────────────────────────────────────────────────

// GEMINI_API_KEY=AIzaSy_isi_api_key_kamu_di_sini


// ─────────────────────────────────────────────────────────
// app/Providers/AppServiceProvider.php
// Tambahkan binding Service di method register()
// ─────────────────────────────────────────────────────────

/*
public function register(): void
{
    $this->app->singleton(\App\Services\FuzzyEngine::class);
    $this->app->singleton(\App\Services\GeminiService::class);
    $this->app->singleton(\App\Services\RecommendationService::class);
}
*/

<?php

namespace App\Providers;

use App\Events\VehicleStatusChecked;
use App\Listeners\CheckCriticalFuzzyStatusListener;
use App\Services\GeminiPromptBuilder;
use App\Services\GeminiService;
use App\Services\RecommendationService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GeminiPromptBuilder::class);
        $this->app->singleton(GeminiService::class);
        $this->app->singleton(RecommendationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan event → listener secara eksplisit
        // Listener ini mengirim notifikasi push FCM saat komponen kendaraan
        // dalam kondisi KRITIS (skor Fuzzy Mamdani >= 75).
        Event::listen(
            VehicleStatusChecked::class,
            CheckCriticalFuzzyStatusListener::class
        );
    }
}

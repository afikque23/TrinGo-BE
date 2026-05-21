<?php

namespace App\Providers;

use App\Services\GeminiPromptBuilder;
use App\Services\GeminiService;
use App\Services\RecommendationService;
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
        //
    }
}

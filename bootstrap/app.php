<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1/motorcycle',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            // 'device.id' => \App\Http\Middleware\DeviceIdentification::class, // Removed: Guest mode disabled
        ]);
        
        // Enable session and CSRF for API routes (needed for web admin panel)
        $middleware->statefulApi();
        
        // Encrypt cookies for API
        $middleware->encryptCookies(except: []);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Check service schedule reminders daily at 07:00 AM (Sebelum aktivitas)
        $schedule->command('reminders:check-service-schedules')
            ->dailyAt('07:00')
            ->timezone('Asia/Jakarta');
        
        $schedule->job(new \App\Jobs\CheckFuzzyWarningJob)
            ->dailyAt('07:00')
            ->timezone('Asia/Jakarta');
        
        // Also check at 17:00 PM (Setelah aktivitas/sore hari)
        $schedule->command('reminders:check-service-schedules')
            ->dailyAt('17:00')
            ->timezone('Asia/Jakarta');
            
        $schedule->job(new \App\Jobs\CheckFuzzyWarningJob)
            ->dailyAt('17:00')
            ->timezone('Asia/Jakarta');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


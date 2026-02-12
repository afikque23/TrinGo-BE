<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\AIConfigController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\FilterController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    /** @var User|null $user */
    $user = request()->user();
    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/templates', [TemplateController::class, 'index'])->name('admin.templates');
    Route::post('/templates', [TemplateController::class, 'store'])->name('admin.templates.store');
    Route::get('/templates/{id}/edit', [TemplateController::class, 'edit'])->name('admin.templates.edit');
    Route::match(['put', 'post'], '/templates/{id}', [TemplateController::class, 'update'])->name('admin.templates.update');
    Route::delete('/templates/{id}', [TemplateController::class, 'destroy'])->name('admin.templates.destroy');
    Route::get('/community', [CommunityController::class, 'index'])->name('admin.community');
    Route::get('/ai-config', [AIConfigController::class, 'index'])->name('admin.ai-config');
    
    // Notification Management Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('admin.notifications.store');
    Route::put('/notifications/{id}', [NotificationController::class, 'update'])->name('admin.notifications.update');
    Route::post('/notifications/{id}/toggle', [NotificationController::class, 'toggleStatus'])->name('admin.notifications.toggle');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('admin.notifications.destroy');
    
    // Filter Management Routes
    Route::get('/filters', [FilterController::class, 'index'])->name('admin.filters');
    Route::post('/filters', [FilterController::class, 'store'])->name('admin.filters.store');
    Route::put('/filters/{id}', [FilterController::class, 'update'])->name('admin.filters.update');
    Route::post('/filters/{id}/toggle', [FilterController::class, 'toggleStatus'])->name('admin.filters.toggle');
    Route::delete('/filters/{id}', [FilterController::class, 'destroy'])->name('admin.filters.destroy');
    
    // Service Type Management Routes (integrated in Filter Management)
    Route::post('/filters/service-types', [FilterController::class, 'storeServiceType'])->name('admin.filters.service-types.store');
    Route::put('/filters/service-types/{serviceType}', [FilterController::class, 'updateServiceType'])->name('admin.filters.service-types.update');
    Route::delete('/filters/service-types/{serviceType}', [FilterController::class, 'destroyServiceType'])->name('admin.filters.service-types.destroy');
    Route::patch('/filters/service-types/{serviceType}/toggle-status', [FilterController::class, 'toggleServiceTypeStatus'])->name('admin.filters.service-types.toggle-status');
    
    // Reminder Options Management Routes (integrated in Filter Management)
    Route::post('/filters/reminder-options', [FilterController::class, 'storeReminderOption'])->name('admin.filters.reminder-options.store');
    Route::put('/filters/reminder-options/{reminderOption}', [FilterController::class, 'updateReminderOption'])->name('admin.filters.reminder-options.update');
    Route::delete('/filters/reminder-options/{reminderOption}', [FilterController::class, 'destroyReminderOption'])->name('admin.filters.reminder-options.destroy');
    Route::patch('/filters/reminder-options/{reminderOption}/toggle-status', [FilterController::class, 'toggleReminderOptionStatus'])->name('admin.filters.reminder-options.toggle-status');
    
    // Notification Category Management Routes (integrated in Filter Management)
    Route::post('/filters/notification-categories', [FilterController::class, 'storeNotificationCategory'])->name('admin.filters.notification-categories.store');
    Route::put('/filters/notification-categories/{notificationCategory}', [FilterController::class, 'updateNotificationCategory'])->name('admin.filters.notification-categories.update');
    Route::delete('/filters/notification-categories/{notificationCategory}', [FilterController::class, 'destroyNotificationCategory'])->name('admin.filters.notification-categories.destroy');
    Route::patch('/filters/notification-categories/{notificationCategory}/toggle-status', [FilterController::class, 'toggleNotificationCategoryStatus'])->name('admin.filters.notification-categories.toggle-status');
    
    Route::get('/content', [ContentController::class, 'index'])->name('admin.content');
    Route::get('/konten-komunitas', [CommunityController::class, 'konten'])->name('admin.konten.komunitas');
    Route::get('/konten-komunitas/{id}', [CommunityController::class, 'detail'])->name('admin.konten.detail');
});

// Auth Routes for Web
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        if (\Illuminate\Support\Facades\Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            /** @var User $user */
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }
            
            \Illuminate\Support\Facades\Auth::logout();
            return back()->withErrors([
                'email' => 'Akun Anda tidak memiliki akses admin.',
            ]);
        }
        
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    })->name('login.submit');
});

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');


<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\NotificationCategoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationPreferenceController;
use App\Http\Controllers\Api\ReminderOptionController;
use App\Http\Controllers\Api\ServiceTypeController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ServiceHistoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are for mobile app and external API access
| Prefix: /api/v1/motorcycle
| All responses return JSON
|
| Route Structure:
| - Public Auth Routes: No authentication required
| - Public Content Routes: No authentication required  
| - Public App Routes: No auth but requires device_id (Guest Mode support)
| - Protected User Routes: Requires authentication (auth:sanctum,web)
| - Admin Routes: Requires authentication + admin role
|
*/

// ============================================================================
// PUBLIC ROUTES - No Authentication Required
// ============================================================================

// Public Auth Routes
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify-email');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('resend-otp');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
    Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('refresh-token');
});

// Public Content Routes (for Mobile App) - No authentication required
Route::prefix('public')->group(function () {
    Route::get('/contents/{type}', [ContentController::class, 'getByType']);
});

// ============================================================================
// APP ROUTES - Support Guest Mode (Device ID Required if Not Logged In)
// ============================================================================

Route::middleware(['device.id'])->group(function () {
    // Master Data - Reminder Options (Public read-only access)
    Route::get('/reminder-options', [ReminderOptionController::class, 'index']);
    
    // Vehicle Management
    Route::get('/vehicles/primary', [VehicleController::class, 'getPrimary']);
    Route::post('/vehicles/{id}/set-primary', [VehicleController::class, 'setPrimary']);
    Route::apiResource('vehicles', VehicleController::class);

    // Service History
    Route::get('/service-histories/cost-summary', [ServiceHistoryController::class, 'costSummary']);
    Route::apiResource('service-histories', ServiceHistoryController::class);

    // Service Management
    Route::get('/services/summary/{vehicle_id}', [ServiceController::class, 'summary']);
    Route::get('/services/cost-breakdown/{vehicle_id}', [ServiceController::class, 'costBreakdown']);
    Route::apiResource('services', ServiceController::class);

    // Service Schedules
    Route::get('/service-schedules/primary', [App\Http\Controllers\Api\ServiceScheduleController::class, 'primaryVehicleSchedules']);
    Route::get('/service-schedules/status/{vehicle_id}', [App\Http\Controllers\Api\ServiceScheduleController::class, 'evaluateStatus']);
    Route::apiResource('service-schedules', App\Http\Controllers\Api\ServiceScheduleController::class);

    // Trip Management
    Route::apiResource('trips', TripController::class);

    // Notifications (Mobile App)
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'show', 'destroy']);

    // Notification Categories (for Tab Navigation)
    Route::get('/notification-categories', [NotificationCategoryController::class, 'index']);

    // Notification Preferences (User Settings for Push Notifications)
    Route::get('/notification-preferences', [NotificationPreferenceController::class, 'index']);
    Route::patch('/notification-preferences', [NotificationPreferenceController::class, 'update']);
    Route::delete('/notification-preferences', [NotificationPreferenceController::class, 'destroy']);
});

// ============================================================================
// PROTECTED ROUTES - Authentication Required (User Routes)
// ============================================================================

Route::middleware('auth:sanctum,web')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});

// ============================================================================
// ADMIN ROUTES - Authentication + Admin Role Required
// ============================================================================

Route::prefix('admin')->middleware(['auth:sanctum,web', 'admin'])->group(function () {
    // Content Management (Web Admin Only)
    Route::apiResource('contents', ContentController::class);

    // Service Type Master Data (Web Admin Only)
    Route::patch('/service-types/{service_type}/toggle-status', [ServiceTypeController::class, 'toggleStatus']);
    Route::apiResource('service-types', ServiceTypeController::class);

    // Reminder Option Master Data (Web Admin Only)
    Route::patch('/reminder-options/{reminder_option}/toggle-status', [ReminderOptionController::class, 'toggleStatus']);
    Route::apiResource('reminder-options', ReminderOptionController::class);
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now(),
    ]);
});

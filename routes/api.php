<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\NotificationCategoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationPreferenceController;
use App\Http\Controllers\Api\ReminderOptionController;
use App\Http\Controllers\Api\ServiceTypeController;
use App\Http\Controllers\Api\MaintenanceRecommendationController;
use App\Http\Controllers\Api\MotorTypeController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ServiceHistoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TipsController;
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
| - Public Auth Routes: No authentication required (login, register, etc)
| - Public Content Routes: No authentication required (terms, privacy, etc)
| - Protected User Routes: Requires authentication (vehicles, services, trips, etc)
| - Admin Routes: Requires authentication + admin role
|
| Note: All user data endpoints now require authentication.
| Use refresh_token (valid for 90 days) for persistent login.
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
    Route::get('/contents', [ContentController::class, 'publicIndex']);
    Route::get('/contents/{type}', [ContentController::class, 'getByType']);

    // Public Tips - Browse without authentication
    Route::get('/tips', [TipsController::class, 'index']);
    Route::get('/tips/{tip}', [TipsController::class, 'show']);
});

// Public File Serving Routes - Serve uploaded files with proper headers
Route::get('/files/receipts/{filename}', [FileController::class, 'serveReceipt'])
    ->where('filename', '.*')
    ->name('files.receipt');
Route::get('/files/avatars/{filename}', [FileController::class, 'serveAvatar'])
    ->where('filename', '.*')
    ->name('files.avatar');

// ============================================================================
// PROTECTED ROUTES - Authentication Required (User Routes)
// ============================================================================

Route::middleware(['auth:sanctum,web'])->group(function () {
    // Fuzzy master data (for Mobile App)
    Route::get('/motor-types', [MotorTypeController::class, 'index']);
    Route::get('/motor-types/{slug}/components', [MotorTypeController::class, 'components']);

    // Master Data - Reminder Options
    Route::get('/reminder-options', [ReminderOptionController::class, 'index']);

    // Vehicle Management
    Route::get('/vehicles/primary', [VehicleController::class, 'getPrimary']);
    Route::get('/vehicles/primary/service-metrics', [VehicleController::class, 'getServiceMetrics']);
    Route::get('/vehicles/primary/usage-pattern', [VehicleController::class, 'getUsagePattern']);
    Route::get('/vehicles/primary/maintenance-recommendations', [MaintenanceRecommendationController::class, 'primary']);
    Route::get('/vehicles/{vehicle}/maintenance-recommendations', [MaintenanceRecommendationController::class, 'show']);

    // AI Recommendation endpoints for Mobile UI (3 separate screens)
    Route::prefix('motors/{motorId}')->group(function () {
        Route::get('/home-insight', [RecommendationController::class, 'homeInsight']);
        Route::get('/service-recommendation', [RecommendationController::class, 'serviceRecommendation']);
        Route::get('/scores', [RecommendationController::class, 'scores']);

        // Tracking endpoints
        Route::post('/tracking/start', [\App\Http\Controllers\Api\TrackingController::class, 'start']);
        Route::post('/tracking/stop', [\App\Http\Controllers\Api\TrackingController::class, 'stop']);
        Route::get('/tracking/status', [\App\Http\Controllers\Api\TrackingController::class, 'status']);
        Route::get('/tracking/last-location', [\App\Http\Controllers\Api\TrackingController::class, 'lastLocation']);

        // IoT Diagnostic endpoints — untuk debugging koneksi ESP32
        Route::get('/iot-diagnostic', [\App\Http\Controllers\Api\IotDiagnosticController::class, 'check']);
        Route::get('/iot-diagnostic/simulate-telemetry', [\App\Http\Controllers\Api\IotDiagnosticController::class, 'simulateTelemetry']);
    });

    // IoT Diagnostic: MQTT broker ping (tidak spesifik motor)
    Route::get('/iot-diagnostic/ping-mqtt', [\App\Http\Controllers\Api\IotDiagnosticController::class, 'pingMqtt']);

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
    Route::post('/service-schedules/check-reminders/{vehicle_id}', [App\Http\Controllers\Api\ServiceScheduleController::class, 'checkReminders']);
    Route::post('/service-schedules/{schedule_id}/reset-reminder', [App\Http\Controllers\Api\ServiceScheduleController::class, 'resetReminder']);
    Route::post('/service-schedules/{schedule_id}/complete', [App\Http\Controllers\Api\ServiceScheduleController::class, 'complete']);
    Route::apiResource('service-schedules', App\Http\Controllers\Api\ServiceScheduleController::class);

    // Trip Management
    Route::post('/trips/manual-distance', [TripController::class, 'addManualDistance']);
    Route::apiResource('trips', TripController::class);

    // Tips Management (Maintenance Tips & Tricks)
    Route::get('/tips/my', [TipsController::class, 'myTips']);
    Route::post('/tips/{tip}/like', [TipsController::class, 'like']);
    Route::post('/tips/{tip}/bookmark', [TipsController::class, 'bookmark']);
    Route::post('/tips/{tip}/share', [TipsController::class, 'share']);
    Route::post('/tips/{tip}/rate', [TipsController::class, 'rate']);
    Route::post('/tips/{tip}/use-template', [TipsController::class, 'useTemplate']);
    Route::apiResource('tips', TipsController::class);

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

    // Device Token Management (FCM Push Notification) - Requires auth
    Route::post('/device-tokens/register', [DeviceTokenController::class, 'register']);
    Route::post('/device-tokens/unregister', [DeviceTokenController::class, 'unregister']);
    Route::get('/device-tokens/status', [DeviceTokenController::class, 'status']);

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // User Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::post('/update', [ProfileController::class, 'update']); // Use POST for file upload support
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar']);
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

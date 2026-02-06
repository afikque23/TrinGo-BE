<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ServiceHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // Vehicle Management Routes
    // IMPORTANT: Specific routes MUST come before apiResource
    Route::get('/vehicles/primary', [VehicleController::class, 'getPrimary']);
    Route::post('/vehicles/{id}/set-primary', [VehicleController::class, 'setPrimary']);
    
    Route::apiResource('vehicles', VehicleController::class);

    // Service History Routes (Sprint 5)
    // IMPORTANT: Specific routes MUST come before apiResource
    Route::get('/service-histories/cost-summary', [ServiceHistoryController::class, 'costSummary']);
    
    Route::apiResource('service-histories', ServiceHistoryController::class);

    // Other protected routes will go here
    // Example:
    // Route::apiResource('trips', TripController::class);
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now(),
    ]);
});

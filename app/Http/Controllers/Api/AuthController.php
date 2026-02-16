<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest as AuthLoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Models\OtpVerification;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Notification;
use App\Models\Trip;
use App\Models\FuelLog;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            // Generate OTP for email verification
            $otp = OtpVerification::createOtp(
                $user->email,
                'email_verification',
                10
            );

            // Send OTP via email
            try {
                Mail::to($user->email)->send(new \App\Mail\OtpMail($otp->otp, 'email_verification', $user->name));
            } catch (\Exception $mailError) {
                Log::warning('Email sending failed: ' . $mailError->getMessage());
            }
            
            // For development, return OTP in response (REMOVE IN PRODUCTION!)
            $responseData = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                'otp' => $otp->otp, // REMOVE THIS IN PRODUCTION!
                'expires_at' => $otp->expires_at,
            ];

            return $this->createdResponse($responseData, 'Registrasi berhasil. Kode OTP telah dikirim ke email Anda.');
            
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return $this->errorResponse('Registrasi gagal. Silakan coba lagi.', 500);
        }
    }

    /**
     * Verify email with OTP
     */
    public function verifyEmail(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $otpRecord = OtpVerification::verifyOtp(
                $request->email,
                $request->otp,
                $request->type
            );

            if (!$otpRecord) {
                return $this->errorResponse('Kode OTP tidak valid atau sudah kadaluarsa', 400);
            }

            // Mark OTP as used
            $otpRecord->markAsUsed();

            // If email verification, update user
            if ($request->type === 'email_verification') {
                $user = User::where('email', $request->email)->first();
                
                if ($user) {
                    $user->update([
                        'email_verified_at' => now(),
                    ]);

                    // Generate access token (30 minutes)
                    $token = $user->createToken('auth_token', ['*'], now()->addMinutes(30))->plainTextToken;

                    // Generate refresh token (30 days)
                    $refreshToken = $user->generateRefreshToken(30);

                    // Sync device data if device_id provided
                    $deviceId = $request->input('device_id');
                    $syncResult = $this->syncDeviceData($user, $deviceId);

                    return $this->successResponse([
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'email_verified_at' => $user->email_verified_at,
                        ],
                        'access_token' => $token,
                        'refresh_token' => $refreshToken,
                        'token_type' => 'Bearer',
                        'expires_in' => 1800, // 30 minutes
                        'sync_info' => $syncResult, // Info sinkronisasi data device
                    ], 'Email berhasil diverifikasi');
                }
            }

            return $this->successResponse(null, 'OTP berhasil diverifikasi');
            
        } catch (\Exception $e) {
            Log::error('OTP verification error: ' . $e->getMessage());
            return $this->errorResponse('Verifikasi OTP gagal', 500);
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'type' => 'required|in:email_verification,password_reset',
        ]);

        try {
            // Generate new OTP
            $otp = OtpVerification::createOtp(
                $request->email,
                $request->type,
                10
            );

            // Send OTP via email
            try {
                $user = User::where('email', $request->email)->first();
                Mail::to($request->email)->send(new \App\Mail\OtpMail($otp->otp, $request->type, $user?->name));
            } catch (\Exception $mailError) {
                Log::warning('Email sending failed: ' . $mailError->getMessage());
            }

            // For development (REMOVE IN PRODUCTION!)
            return $this->successResponse([
                'otp' => $otp->otp, // REMOVE THIS IN PRODUCTION!
                'expires_at' => $otp->expires_at,
            ], 'Kode OTP baru telah dikirim ke email Anda');
            
        } catch (\Exception $e) {
            Log::error('Resend OTP error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengirim ulang OTP', 500);
        }
    }

    /**
     * Login user
     */
    public function login(AuthLoginRequest $request): JsonResponse
    {
        try {
            // Check credentials
            if (!Auth::attempt($request->only('email', 'password'))) {
                return $this->errorResponse('Email atau password salah', 401);
            }

            $user = User::where('email', $request->email)->first();

            // Check if user is active
            if (!$user->is_active) {
                return $this->errorResponse('Akun Anda telah dinonaktifkan', 403);
            }

            // Check if email is verified
            if (!$user->email_verified_at) {
                // Generate OTP
                $otp = OtpVerification::createOtp($user->email, 'email_verification', 10);
                
                return $this->errorResponse('Email belum diverifikasi. Kode OTP telah dikirim ke email Anda.', 403, [
                    'requires_verification' => true,
                    'otp' => $otp->otp, // REMOVE IN PRODUCTION!
                ]);
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Generate access token (30 minutes)
            $token = $user->createToken('auth_token', ['*'], now()->addMinutes(30))->plainTextToken;

            // Generate refresh token (30 days)
            $refreshToken = $user->generateRefreshToken(30);

            // Update device info if provided
            $deviceId = null;
            if ($request->has('device_id') || $request->has('device_name')) {
                $deviceId = $request->input('device_id');
                $user->updateDeviceInfo(
                    $deviceId,
                    $request->input('device_name')
                );
            }

            // Sync device data to user account
            $syncResult = $this->syncDeviceData($user, $deviceId);

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'email_verified_at' => $user->email_verified_at,
                    'last_login_at' => $user->last_login_at,
                ],
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 1800, // 30 minutes in seconds
                'sync_info' => $syncResult, // Info sinkronisasi data device
            ], 'Login berhasil');
            
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return $this->errorResponse('Login gagal', 500);
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        try {
            // Find user by email or from token if authenticated
            $email = $request->input('email');
            $user = $email ? User::where('email', $email)->first() : $request->user();

            if (!$user) {
                return $this->errorResponse('User tidak ditemukan', 404);
            }

            // Verify refresh token
            if (!$user->verifyRefreshToken($request->refresh_token)) {
                return $this->errorResponse('Refresh token tidak valid atau sudah kadaluarsa. Silakan login kembali.', 401);
            }

            // Revoke old access tokens
            $user->tokens()->delete();

            // Generate new access token (30 minutes)
            $newAccessToken = $user->createToken('auth_token', ['*'], now()->addMinutes(30))->plainTextToken;

            // Generate new refresh token (30 days)
            $newRefreshToken = $user->generateRefreshToken(30);

            // Update device info if provided
            if ($request->has('device_id') || $request->has('device_name')) {
                $user->updateDeviceInfo(
                    $request->input('device_id'),
                    $request->input('device_name')
                );
            }

            return $this->successResponse([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 1800, // 30 minutes in seconds
            ], 'Token berhasil diperbaharui');

        } catch (\Exception $e) {
            Log::error('Refresh token error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memperbaharui token', 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Delete all access tokens
            $user->tokens()->delete();
            
            // Revoke refresh token
            $user->revokeRefreshToken();
            
            return $this->successResponse(null, 'Logout berhasil');
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return $this->errorResponse('Logout gagal', 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'email_verified_at' => $user->email_verified_at,
                    'phone_verified_at' => $user->phone_verified_at,
                    'last_login_at' => $user->last_login_at,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at,
                ],
            ], 'Data user berhasil diambil');
        } catch (\Exception $e) {
            Log::error('Get user error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil data user', 500);
        }
    }

    /**
     * Forgot password - send OTP
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            // Generate OTP for password reset
            $otp = OtpVerification::createOtp(
                $request->email,
                'password_reset',
                10
            );

            // Send OTP via email
            try {
                Mail::to($user->email)->send(new \App\Mail\OtpMail($otp->otp, 'password_reset', $user->name));
            } catch (\Exception $mailError) {
                Log::warning('Email sending failed: ' . $mailError->getMessage());
            }

            // For development (REMOVE IN PRODUCTION!)
            return $this->successResponse([
                'otp' => $otp->otp, // REMOVE THIS IN PRODUCTION!
                'expires_at' => $otp->expires_at,
            ], 'Kode OTP untuk reset password telah dikirim ke email Anda');
            
        } catch (\Exception $e) {
            Log::error('Forgot password error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengirim kode OTP', 500);
        }
    }

    /**
     * Reset password with OTP
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            // Verify OTP
            $otpRecord = OtpVerification::verifyOtp(
                $request->email,
                $request->otp,
                'password_reset'
            );

            if (!$otpRecord) {
                return $this->errorResponse('Kode OTP tidak valid atau sudah kadaluarsa', 400);
            }

            // Update password
            $user = User::where('email', $request->email)->first();
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Mark OTP as used
            $otpRecord->markAsUsed();

            // Revoke all tokens
            $user->tokens()->delete();

            return $this->successResponse(null, 'Password berhasil direset. Silakan login dengan password baru Anda.');
            
        } catch (\Exception $e) {
            Log::error('Reset password error: ' . $e->getMessage());
            return $this->errorResponse('Reset password gagal', 500);
        }
    }

    /**
     * Change password (for authenticated user)
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = $request->user();

            // Check current password
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->errorResponse('Password lama tidak sesuai', 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Revoke all tokens except current
            $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

            return $this->successResponse(null, 'Password berhasil diubah');
            
        } catch (\Exception $e) {
            Log::error('Change password error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengubah password', 500);
        }
    }

    /**
     * Sync device data to user account when login
     * 
     * Sinkronisasi semua data yang dibuat dengan device_id ke user_id saat login
     * 
     * @param User $user
     * @param string|null $deviceId
     * @return array Summary of synced data
     */
    protected function syncDeviceData(User $user, ?string $deviceId): array
    {
        if (empty($deviceId)) {
            return [
                'synced' => false,
                'message' => 'No device_id provided',
            ];
        }

        try {
            DB::beginTransaction();

            $syncedCount = [
                'vehicles' => 0,
                'notifications' => 0,
                'trips' => 0,
                'fuel_logs' => 0,
            ];

            // Sync Vehicles
            $vehiclesUpdated = Vehicle::where('device_id', $deviceId)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);
            $syncedCount['vehicles'] = $vehiclesUpdated;

            // Sync Notifications
            $notificationsUpdated = Notification::where('device_id', $deviceId)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);
            $syncedCount['notifications'] = $notificationsUpdated;

            // Sync Trips
            $tripsUpdated = Trip::where('device_id', $deviceId)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);
            $syncedCount['trips'] = $tripsUpdated;

            // Sync Fuel Logs
            $fuelLogsUpdated = FuelLog::where('device_id', $deviceId)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);
            $syncedCount['fuel_logs'] = $fuelLogsUpdated;

            DB::commit();

            $totalSynced = array_sum($syncedCount);

            Log::info("Device data synced for user {$user->id}", [
                'device_id' => $deviceId,
                'synced_count' => $syncedCount,
                'total' => $totalSynced,
            ]);

            return [
                'synced' => true,
                'total_synced' => $totalSynced,
                'details' => $syncedCount,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Device data sync error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'device_id' => $deviceId,
            ]);

            return [
                'synced' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }
}

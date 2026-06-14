<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use App\Models\Vehicle;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'location' => 'nullable|string|max:200',
            'device_id' => 'nullable|string|uuid',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $validator->errors()
            );
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'location' => $validated['location'] ?? null,
                'role' => 'user',
                'is_active' => true,
                'device_id' => $validated['device_id'] ?? null,
                'device_name' => $validated['device_name'] ?? null,
                'last_login_at' => now(),
            ]);

            // Sync guest mode data to user if device_id provided
            if (isset($validated['device_id'])) {
                $this->syncGuestDataToUser($user->id, $validated['device_id']);
            }

            // Generate tokens
            $accessToken = $user->createToken('auth_token')->plainTextToken;
            $refreshToken = $user->generateRefreshToken();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => new ProfileResource($user),
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => config('sanctum.expiration', 60) * 60, // in seconds
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration error: ' . $e->getMessage());

            return $this->errorResponse(
                'Registration failed',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Login user.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_id' => 'nullable|string|uuid',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $validator->errors()
            );
        }

        try {
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return $this->errorResponse(
                    'Invalid credentials',
                    401,
                    'Email or password is incorrect'
                );
            }

            if (!$user->is_active) {
                return $this->errorResponse(
                    'Account inactive',
                    403,
                    'Your account has been deactivated. Please contact support.'
                );
            }

            DB::beginTransaction();

            // Update device info and last login
            $user->update([
                'device_id' => $request->input('device_id'),
                'device_name' => $request->input('device_name'),
                'last_login_at' => now(),
            ]);

            // Sync guest mode data to user if device_id provided
            if ($request->has('device_id')) {
                $this->syncGuestDataToUser($user->id, $request->input('device_id'));
            }

            // Revoke all previous tokens
            $user->tokens()->delete();

            // Generate new tokens
            $accessToken = $user->createToken('auth_token')->plainTextToken;
            $refreshToken = $user->generateRefreshToken();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => new ProfileResource($user),
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => config('sanctum.expiration', 60) * 60,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Login error: ' . $e->getMessage());

            return $this->errorResponse(
                'Login failed',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Logout user (revoke tokens).
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse(
                    'Unauthenticated',
                    401
                );
            }

            // Revoke all tokens
            $user->tokens()->delete();

            // Revoke refresh token
            $user->revokeRefreshToken();

            return $this->successResponse(
                null,
                'Logout successful'
            );

        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());

            return $this->errorResponse(
                'Logout failed',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Refresh access token using refresh token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $validator->errors()
            );
        }

        try {
            $refreshToken = $request->input('refresh_token');

            // Find user by refresh token (need to hash it first)
            $user = User::where('refresh_token', hash('sha256', $refreshToken))
                ->where('refresh_token_expires_at', '>', now())
                ->first();

            if (!$user) {
                return $this->errorResponse(
                    'Invalid refresh token',
                    401,
                    'The refresh token is invalid or has expired'
                );
            }

            // Verify refresh token
            if (!$user->verifyRefreshToken($refreshToken)) {
                return $this->errorResponse(
                    'Invalid refresh token',
                    401,
                    'The refresh token verification failed'
                );
            }

            // Revoke old access tokens
            $user->tokens()->delete();

            // Generate new access token
            $accessToken = $user->createToken('auth_token')->plainTextToken;

            // Optionally rotate refresh token
            $newRefreshToken = $user->generateRefreshToken();

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'access_token' => $accessToken,
                    'refresh_token' => $newRefreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => config('sanctum.expiration', 60) * 60,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Token refresh error: ' . $e->getMessage());

            return $this->errorResponse(
                'Token refresh failed',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Get current authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse(
                    'Unauthenticated',
                    401
                );
            }

            return $this->successResponse(
                new ProfileResource($user),
                'User profile retrieved successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error fetching user profile',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Sync guest mode data (vehicles, trips, etc.) to authenticated user.
     * 
     * @param int $userId
     * @param string $deviceId
     * @return void
     */
    private function syncGuestDataToUser(int $userId, string $deviceId): void
    {
        try {
            // Sync vehicles from device_id to user_id
            Vehicle::where('device_id', $deviceId)
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);

            // Sync FCM tokens
            DB::table('device_tokens')
                ->where('device_id', $deviceId)
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);

            // Sync notifications
            DB::table('notifications')
                ->where('device_id', $deviceId)
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);

            Log::info("Guest data synced successfully for device_id: {$deviceId} to user_id: {$userId}");

        } catch (\Exception $e) {
            Log::error("Failed to sync guest data: " . $e->getMessage());
            // Don't throw exception, just log it
        }
    }
}

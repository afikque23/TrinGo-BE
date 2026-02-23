<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * Get authenticated user profile with stats.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return $this->errorResponse('Unauthenticated', 401);
            }

            return $this->successResponse(
                new ProfileResource($user),
                'Profile retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error fetching profile', 500, $e->getMessage());
        }
    }

    /**
     * Update authenticated user profile.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return $this->errorResponse('Unauthenticated', 401);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ],
                'phone' => 'nullable|string|max:20',
                'location' => 'nullable|string|max:200',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'current_password' => 'required_with:new_password|string',
                'new_password' => 'nullable|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $validated = $validator->validated();
            $oldAvatar = $user->avatar;

            // Handle password change
            if (isset($validated['new_password'])) {
                if (!Hash::check($validated['current_password'], $user->password)) {
                    return $this->errorResponse('Current password is incorrect', 400);
                }
                $validated['password'] = Hash::make($validated['new_password']);
                unset($validated['current_password'], $validated['new_password'], $validated['new_password_confirmation']);
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
                    Storage::disk('public')->delete($oldAvatar);
                }
                $path = $request->file('avatar')->store('avatars', 'public');
                $validated['avatar'] = $path;
            }

            // Update user profile
            $user->update($validated);

            return $this->successResponse(
                new ProfileResource($user->fresh()),
                'Profile updated successfully'
            );
        } catch (\Exception $e) {
            if (isset($validated['avatar']) && Storage::disk('public')->exists($validated['avatar'])) {
                Storage::disk('public')->delete($validated['avatar']);
            }
            return $this->errorResponse('Error updating profile', 500, $e->getMessage());
        }
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return $this->errorResponse('Unauthenticated', 401);
            }

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->update(['avatar' => null]);

            return $this->successResponse(
                new ProfileResource($user->fresh()),
                'Avatar deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error deleting avatar', 500, $e->getMessage());
        }
    }
}

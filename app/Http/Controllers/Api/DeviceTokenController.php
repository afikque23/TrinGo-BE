<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Traits\ApiResponse;
use App\Traits\HasOwnerIdentification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceTokenController extends Controller
{
    use ApiResponse, HasOwnerIdentification;

    /**
     * Register atau update FCM token untuk device.
     * Endpoint ini dipanggil dari Flutter saat:
     * - App pertama kali dibuka (after login)
     * - FCM token di-refresh
     * - User login/logout
     * 
     * Requires authentication.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string|max:500',
            'device_type' => 'sometimes|string|in:android,ios',
            'device_name' => 'sometimes|string|max:200',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $owner = $this->getOwnerData($request);

        $tokenData = [
            'user_id' => $owner['user_id'],
            'device_id' => $owner['device_id'] ?? $request->header('X-Device-ID'),
            'fcm_token' => $request->fcm_token,
            'device_type' => $request->device_type ?? 'android',
            'device_name' => $request->device_name,
        ];

        $deviceToken = DeviceToken::registerToken($tokenData);

        return $this->successResponse([
            'id' => $deviceToken->id,
            'device_type' => $deviceToken->device_type,
            'is_active' => $deviceToken->is_active,
        ], 'FCM token berhasil didaftarkan');
    }

    /**
     * Hapus FCM token (saat user logout atau uninstall).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unregister(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $deleted = DeviceToken::where('fcm_token', $request->fcm_token)
            ->update(['is_active' => false]);

        if ($deleted) {
            return $this->successResponse(null, 'FCM token berhasil dihapus');
        }

        return $this->errorResponse('FCM token tidak ditemukan', 404);
    }

    /**
     * Cek status FCM token (untuk debugging di Flutter).
     * Requires authentication.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        $owner = $this->getOwnerData($request);

        $query = DeviceToken::query();
        $query->where('user_id', $owner['user_id']);

        $tokens = $query->active()->get(['id', 'device_type', 'device_name', 'is_active', 'last_used_at', 'created_at']);

        return $this->successResponse([
            'registered_devices' => $tokens->count(),
            'devices' => $tokens,
        ], 'Status device token berhasil diambil');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\RecommendationService;
use App\Traits\ApiResponse;
use App\Traits\HasOwnerIdentification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MaintenanceRecommendationController extends Controller
{
    use ApiResponse, HasOwnerIdentification;

    public function primary(Request $request, RecommendationService $service): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $vehicle = Vehicle::query()
                ->where('user_id', $user->id)
                ->where('is_primary', true)
                ->first();

            if (!$vehicle) {
                return $this->errorResponse('Motor utama belum ditetapkan', 404);
            }

            $data = $service->getVehicleRecommendations($user->id, $vehicle);
            return $this->successResponse($data, 'Rekomendasi perawatan berhasil diambil');
        } catch (\Throwable $e) {
            Log::error('Error fetching maintenance recommendations (primary): ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil rekomendasi perawatan', 500, ['error' => $e->getMessage()]);
        }
    }

    public function show(Request $request, Vehicle $vehicle, RecommendationService $service): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }
            if (!$this->canAccessModel($vehicle, $request)) {
                return $this->unauthorizedResponse('Unauthorized');
            }

            $data = $service->getVehicleRecommendations($user->id, $vehicle);
            return $this->successResponse($data, 'Rekomendasi perawatan berhasil diambil');
        } catch (\Throwable $e) {
            Log::error('Error fetching maintenance recommendations: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil rekomendasi perawatan', 500, ['error' => $e->getMessage()]);
        }
    }
}

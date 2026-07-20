<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\ServiceHistory;
use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;
use App\Services\RecommendationService;
use App\Traits\ApiResponse;
use App\Traits\HasOwnerIdentification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecommendationController extends Controller
{
    use ApiResponse;
    use HasOwnerIdentification;

    public function homeInsight(Request $request, int $motorId, RecommendationService $service): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $vehicle = Vehicle::query()
                ->where('id', $motorId)
                ->where('user_id', $user->id)
                ->first();

            if (!$vehicle) {
                return $this->notFoundResponse('Motor tidak ditemukan');
            }

            $result = $service->getVehicleRecommendations($user->id, $vehicle);
            $sections = is_array($result['sections'] ?? null) ? $result['sections'] : [];

            $data = [
                'wawasan_pintar' => $sections['wawasan_pintar'] ?? [],
                'insight_sistem' => $sections['insight_sistem'] ?? null,
                'fuzzy_scores' => $result['component_scores'] ?? [],
                'fuzzy_statuses' => $result['component_statuses'] ?? [],
                'monitored_summary' => $result['monitoring_summary'] ?? null,
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'from_cache' => (bool) ($result['used_cache'] ?? false),
                    'generated_at' => $this->formatGeneratedAt($result['generated_at'] ?? null),
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error fetching home insight: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil home insight', 500, ['error' => $e->getMessage()]);
        }
    }

    public function serviceRecommendation(Request $request, int $motorId, RecommendationService $service): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $vehicle = Vehicle::query()
                ->where('id', $motorId)
                ->where('user_id', $user->id)
                ->first();

            if (!$vehicle) {
                return $this->notFoundResponse('Motor tidak ditemukan');
            }

            $result = $service->getVehicleRecommendations($user->id, $vehicle);
            $sections = is_array($result['sections'] ?? null) ? $result['sections'] : [];

            $data = [
                'ringkasan_kondisi' => $sections['ringkasan_kondisi'] ?? null,
                'rekomendasi_komponen' => $sections['rekomendasi_komponen'] ?? [],
                'tips_mandiri' => $sections['tips_mandiri'] ?? null,
                'monitored_summary' => $result['monitoring_summary'] ?? null,
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'from_cache' => (bool) ($result['used_cache'] ?? false),
                    'generated_at' => $this->formatGeneratedAt($result['generated_at'] ?? null),
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error fetching service recommendation: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil service recommendation', 500, ['error' => $e->getMessage()]);
        }
    }

    public function scores(Request $request, int $motorId, RecommendationService $service): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $vehicle = Vehicle::query()
                ->where('id', $motorId)
                ->where('user_id', $user->id)
                ->first();

            if (!$vehicle) {
                return $this->notFoundResponse('Motor tidak ditemukan');
            }

            $snapshot = $service->getVehicleFuzzySnapshot($vehicle);

            return response()->json([
                'success' => true,
                'data' => [
                    'fuzzy_scores' => $snapshot['component_scores'] ?? [],
                ],
                'meta' => [
                    'from_cache' => false,
                    'generated_at' => now()->toISOString(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error fetching fuzzy scores: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil scores', 500, ['error' => $e->getMessage()]);
        }
    }

    public function markServiceComplete(Request $request, int $motorId): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $vehicle = Vehicle::query()
                ->where('id', $motorId)
                ->where('user_id', $user->id)
                ->first();

            if (!$vehicle) {
                return $this->notFoundResponse('Motor tidak ditemukan');
            }

            $validated = $request->validate([
                'component_name' => 'required|string',
                'performed_at' => 'required|date',
                'odometer' => 'required|integer|min:0',
                'service_provider' => 'nullable|string|max:150',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();
            try {
                $componentName = trim($validated['component_name']);
                $serviceType = ServiceType::firstOrCreate(
                    ['name' => $componentName],
                    ['is_active' => true]
                );

                $history = new ServiceHistory([
                    'vehicle_id' => $vehicle->id,
                    'service_type' => $componentName,
                    'performed_at' => $validated['performed_at'],
                    'odometer' => $validated['odometer'],
                    'service_provider' => $validated['service_provider'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);
                $history->service_type_id = $serviceType->id;
                $history->save();

                if ($validated['odometer'] > $vehicle->odometer) {
                    $vehicle->odometer = $validated['odometer'];
                    $vehicle->save();
                }

                \Illuminate\Support\Facades\Cache::forget('fuzzy_snapshot_' . $vehicle->id);
                \Illuminate\Support\Facades\Cache::forget('recommendation_cache_' . $user->id . '_' . $vehicle->id);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Servis komponen berhasil dicatat',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Throwable $e) {
            Log::error('Error marking service complete: ' . $e->getMessage());
            return $this->errorResponse('Gagal mencatat servis komponen', 500, ['error' => $e->getMessage()]);
        }
    }

    private function formatGeneratedAt(mixed $value): ?string
    {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->toISOString();
        }

        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return null;
    }
}

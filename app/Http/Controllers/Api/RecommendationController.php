<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteFuzzyServiceRequest;
use App\Models\AiRecommendationCache;
use App\Models\MotorType;
use App\Models\Vehicle;
use App\Models\ServiceHistory;
use App\Models\ServiceType;
use Carbon\Carbon;
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

    public function markServiceComplete(CompleteFuzzyServiceRequest $request, int $motorId): JsonResponse
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

            $validated = $request->validated();
            $componentName = trim((string) $validated['component_name']);
            if ($componentName === '') {
                return $this->errorResponse('Nama komponen tidak boleh kosong.', 422);
            }

            $motorTypeSlug = strtolower(trim((string) $vehicle->tipe_motor));
            $componentConfig = null;
            if ($motorTypeSlug !== '') {
                $motorType = MotorType::query()->where('slug', $motorTypeSlug)->first();
                if ($motorType) {
                    $componentConfig = $motorType->componentConfigs()
                        ->where('is_active', true)
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($componentName)])
                        ->first();
                }
            }

            if (!$componentConfig) {
                return $this->errorResponse(
                    'Komponen tidak valid untuk tipe motor ini.',
                    422,
                    ['component_name' => ['Pilih komponen sesuai daftar rekomendasi.']]
                );
            }

            $performedAt = Carbon::parse((string) $validated['performed_at'])->toDateString();
            $requiresOdometer = $this->componentRequiresMileage($componentConfig->active_vars ?? []);
            $odometerInput = $validated['odometer'] ?? null;

            if ($requiresOdometer && $odometerInput === null) {
                return $this->errorResponse(
                    'Odometer wajib diisi untuk komponen berbasis jarak tempuh.',
                    422,
                    ['odometer' => ['Wajib diisi karena komponen ini memakai variabel jarak tempuh.']]
                );
            }

            $serviceOdometer = $odometerInput !== null ? (int) $odometerInput : null;

            // Historical backfill is allowed: service odometer may be lower than current vehicle odometer.
            // Vehicle odometer remains monotonic and will only be updated when service odometer is higher.

            $result = DB::transaction(function () use ($validated, $user, $vehicle, $componentName, $performedAt, $serviceOdometer) {
                $lockedVehicle = Vehicle::query()
                    ->where('id', $vehicle->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $serviceType = ServiceType::query()
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($componentName)])
                    ->first();

                if (!$serviceType) {
                    $serviceType = ServiceType::create([
                        'name' => $componentName,
                        'is_active' => true,
                    ]);
                }

                // Protect against accidental double tap: detect exact duplicate submitted very recently.
                $duplicate = ServiceHistory::query()
                    ->where('vehicle_id', $lockedVehicle->id)
                    ->whereRaw('LOWER(service_type) = ?', [mb_strtolower($componentName)])
                    ->whereDate('performed_at', $performedAt)
                    ->where('odometer', $serviceOdometer)
                    ->where('created_at', '>=', now()->subMinutes(2))
                    ->latest('id')
                    ->first();

                if ($duplicate) {
                    return ['history' => $duplicate, 'created' => false];
                }

                $history = ServiceHistory::create([
                    'vehicle_id' => $lockedVehicle->id,
                    'service_type_id' => $serviceType->id,
                    'service_type' => $componentName,
                    'performed_at' => $performedAt,
                    'odometer' => $serviceOdometer,
                    'service_provider' => $validated['service_provider'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                if ($serviceOdometer !== null && $serviceOdometer > (int) ($lockedVehicle->odometer ?? 0)) {
                    $lockedVehicle->update([
                        'odometer' => $serviceOdometer,
                    ]);
                }

                AiRecommendationCache::query()
                    ->where('user_id', $user->id)
                    ->where('vehicle_id', $lockedVehicle->id)
                    ->delete();

                return ['history' => $history, 'created' => true];
            });

            return response()->json([
                'success' => true,
                'message' => $result['created']
                    ? 'Servis komponen berhasil dicatat.'
                    : 'Permintaan duplikat terdeteksi. Data servis sebelumnya digunakan.',
                'data' => [
                    'service_history_id' => $result['history']->id,
                    'component_name' => $componentName,
                    'performed_at' => $performedAt,
                    'odometer' => $serviceOdometer,
                    'duplicate' => !$result['created'],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error marking service complete: ' . $e->getMessage());
            return $this->errorResponse('Gagal mencatat servis komponen', 500, ['error' => $e->getMessage()]);
        }
    }

    private function componentRequiresMileage(array $activeVars): bool
    {
        foreach ($activeVars as $var) {
            $key = strtolower(trim((string) $var));
            if (in_array($key, ['jarak', 'jarak_tempuh', 'mileage', 'distance_since_service_km', 'distance'], true)) {
                return true;
            }
        }

        return false;
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

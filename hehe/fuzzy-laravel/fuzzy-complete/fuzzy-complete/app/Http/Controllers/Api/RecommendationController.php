<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiRecommendation;
use App\Models\ComponentConfig;
use App\Models\MotorType;
use App\Services\FuzzyEngine;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    public function __construct(
        private RecommendationService $service,
        private FuzzyEngine           $fuzzy,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/recommendations/{motorId}
    // Dipanggil Flutter saat buka halaman Home atau Service
    // ─────────────────────────────────────────────────────────────────────────
    public function show(Request $request, int $motorId): JsonResponse
    {
        $validated = $request->validate([
            'motor_type_id'       => 'required|exists:motor_types,id',
            'odometer'            => 'required|numeric|min:0',
            'avg_speed'           => 'required|numeric|min:0',
            'jarak_sejak_servis'  => 'required|numeric|min:0',
            'servis_date'         => 'nullable|date',
            'durasi_hari'         => 'nullable|integer|min:0',
            'avg_km_hari'         => 'nullable|numeric|min:0',
            'force_refresh'       => 'nullable|boolean',
        ]);

        $userId       = Auth::id();
        $forceRefresh = $validated['force_refresh'] ?? false;

        // Cek apakah perlu refresh
        if (!$forceRefresh) {
            $forceRefresh = $this->service->shouldRefresh($userId, $motorId, $validated);
        }

        $result = $this->service->getRecommendation(
            userId:       $userId,
            motorId:      $motorId,
            motorTypeId:  $validated['motor_type_id'],
            iotData:      $validated,
            forceRefresh: $forceRefresh,
        );

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/recommendations/{motorId}/scores
    // Hanya skor Fuzzy, tanpa Gemini — untuk update cepat di UI
    // ─────────────────────────────────────────────────────────────────────────
    public function scores(Request $request, int $motorId): JsonResponse
    {
        $validated = $request->validate([
            'motor_type_id'      => 'required|exists:motor_types,id',
            'jarak_sejak_servis' => 'required|numeric|min:0',
            'durasi_hari'        => 'nullable|integer|min:0',
            'avg_speed'          => 'nullable|numeric|min:0',
            'avg_km_hari'        => 'nullable|numeric|min:0',
            'servis_date'        => 'nullable|date',
        ]);

        $inputs = [
            'jarak'      => $validated['jarak_sejak_servis'],
            'durasi'     => $validated['durasi_hari'] ?? 0,
            'kecepatan'  => $validated['avg_speed'] ?? 0,
            'intensitas' => $validated['avg_km_hari'] ?? 0,
        ];

        $scores = $this->fuzzy->calculateAll($validated['motor_type_id'], $inputs);

        return response()->json([
            'success' => true,
            'data'    => [
                'scores'       => $scores,
                'calculated_at'=> now()->toISOString(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/recommendations/{motorId}/history
    // Riwayat rekomendasi user
    // ─────────────────────────────────────────────────────────────────────────
    public function history(int $motorId): JsonResponse
    {
        $history = AiRecommendation::where('user_id', Auth::id())
            ->where('motor_id', $motorId)
            ->latest()
            ->limit(10)
            ->get(['id', 'fingerprint', 'reuse_count', 'last_used_at', 'created_at']);

        return response()->json(['success' => true, 'data' => $history]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/motor-types/{motorTypeId}/components
    // Daftar komponen yang dipantau untuk jenis motor tertentu
    // ─────────────────────────────────────────────────────────────────────────
    public function components(int $motorTypeId): JsonResponse
    {
        $components = ComponentConfig::where('motor_type_id', $motorTypeId)
            ->where('is_active', true)
            ->get(['id', 'name', 'status', 'warn', 'critical', 'reset_interval', 'active_vars']);

        return response()->json(['success' => true, 'data' => $components]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/motor-types
    // Semua jenis motor yang tersedia
    // ─────────────────────────────────────────────────────────────────────────
    public function motorTypes(): JsonResponse
    {
        $types = MotorType::where('is_active', true)->get(['id', 'name', 'slug']);
        return response()->json(['success' => true, 'data' => $types]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MotorType;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MotorTypeController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $rows = MotorType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            return $this->successResponse($rows, 'Daftar tipe motor berhasil diambil');
        } catch (\Throwable $e) {
            Log::error('Error fetching motor types: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil daftar tipe motor', 500, ['error' => $e->getMessage()]);
        }
    }

    public function components(string $slug): JsonResponse
    {
        try {
            $motorType = MotorType::query()
                ->where('slug', strtolower($slug))
                ->first();

            if (!$motorType) {
                return $this->notFoundResponse('Tipe motor tidak ditemukan');
            }

            $components = $motorType->componentConfigs()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'motor_type_id',
                    'name',
                    'warn',
                    'critical',
                    'reset_interval',
                    'active_vars',
                    'is_active',
                    'is_custom',
                    'updated_at',
                ]);

            return $this->successResponse([
                'motor_type' => [
                    'id' => $motorType->id,
                    'name' => $motorType->name,
                    'slug' => $motorType->slug,
                ],
                'components' => $components,
            ], 'Daftar komponen berhasil diambil');
        } catch (\Throwable $e) {
            Log::error('Error fetching motor type components: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil daftar komponen', 500, ['error' => $e->getMessage()]);
        }
    }
}

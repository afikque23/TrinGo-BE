<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memvalidasi Device ID pada endpoint public
 * 
 * Aturan:
 * - Jika user sudah login (auth:sanctum), tidak perlu device_id
 * - Jika user belum login, device_id WAJIB ada
 * - Device ID bisa dari header X-Device-ID atau body request
 * 
 * Gunakan middleware ini untuk endpoint public yang memerlukan identifikasi
 */
class DeviceIdentification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user sudah login, tidak perlu validasi device_id
        if ($request->user()) {
            return $next($request);
        }
        
        // Ambil device_id dari header atau body
        $deviceId = $request->header('X-Device-ID') ?? $request->input('device_id');
        
        // Validasi device_id harus ada
        if (empty($deviceId)) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID is required. Please provide X-Device-ID header or device_id in request body.',
                'error_code' => 'DEVICE_ID_REQUIRED'
            ], 400);
        }
        
        // Validasi format device_id (UUID atau minimal 10 karakter)
        if (strlen($deviceId) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Device ID format. Device ID must be at least 10 characters.',
                'error_code' => 'INVALID_DEVICE_ID'
            ], 400);
        }
        
        // Tambahkan device_id ke request untuk digunakan di controller
        $request->merge(['device_id' => $deviceId]);
        
        return $next($request);
    }
}

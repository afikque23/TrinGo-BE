<?php

namespace App\Traits;

use Illuminate\Http\Request;

/**
 * Trait untuk handle identifikasi owner data (user_id atau device_id)
 * Digunakan untuk mendukung Guest Mode
 */
trait HasOwnerIdentification
{
    /**
     * Get owner filter untuk query (user_id atau device_id)
     * 
     * @param Request $request
     * @return array ['type' => 'user'|'device', 'id' => mixed, 'column' => 'user_id'|'device_id']
     */
    protected function getOwnerFilter(Request $request): array
    {
        $user = $request->user();
        
        if ($user) {
            return [
                'type' => 'user',
                'id' => $user->id,
                'column' => 'user_id',
            ];
        }
        
        // Guest mode - gunakan device_id
        $deviceId = $request->header('X-Device-ID') ?? $request->input('device_id');
        
        return [
            'type' => 'device',
            'id' => $deviceId,
            'column' => 'device_id',
        ];
    }

    /**
     * Apply owner filter to query builder
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyOwnerFilter($query, Request $request)
    {
        $owner = $this->getOwnerFilter($request);
        return $query->where($owner['column'], $owner['id']);
    }

    /**
     * Get owner data untuk create/update
     * 
     * @param Request $request
     * @return array ['user_id' => mixed, 'device_id' => mixed]
     */
    protected function getOwnerData(Request $request): array
    {
        $user = $request->user();
        
        if ($user) {
            return [
                'user_id' => $user->id,
                'device_id' => null, // Tidak perlu device_id jika sudah login
            ];
        }
        
        // Guest mode
        $deviceId = $request->header('X-Device-ID') ?? $request->input('device_id');
        
        return [
            'user_id' => null,
            'device_id' => $deviceId,
        ];
    }

    /**
     * Check if current request owner can access the model
     * 
     * @param mixed $model Model instance yang punya user_id/device_id
     * @param Request $request
     * @return bool
     */
    protected function canAccessModel($model, Request $request): bool
    {
        $owner = $this->getOwnerFilter($request);
        
        // Check berdasarkan column (user_id atau device_id)
        if ($owner['column'] === 'user_id') {
            return $model->user_id == $owner['id'];
        } else {
            return $model->device_id == $owner['id'];
        }
    }
}

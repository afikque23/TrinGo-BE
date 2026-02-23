<?php

namespace App\Traits;

use Illuminate\Http\Request;

/**
 * Trait untuk handle identifikasi owner data berdasarkan user_id
 * Note: Guest mode sudah dihapus, semua data harus punya user_id
 */
trait HasOwnerIdentification
{
    /**
     * Get owner filter untuk query (user_id only)
     * 
     * @param Request $request
     * @return array ['type' => 'user', 'id' => mixed, 'column' => 'user_id']
     */
    protected function getOwnerFilter(Request $request): array
    {
        $user = $request->user();
        
        if (!$user) {
            throw new \Exception('Unauthorized - User must be authenticated');
        }
        
        return [
            'type' => 'user',
            'id' => $user->id,
            'column' => 'user_id',
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
        
        if (!$user) {
            throw new \Exception('Unauthorized - User must be authenticated');
        }
        
        return [
            'user_id' => $user->id,
            'device_id' => $request->header('X-Device-ID') ?? $request->input('device_id'), // For tracking only
        ];
    }

    /**
     * Check if current request owner can access the model
     * 
     * @param mixed $model Model instance yang punya user_id
     * @param Request $request
     * @return bool
     */
    protected function canAccessModel($model, Request $request): bool
    {
        $owner = $this->getOwnerFilter($request);
        
        // Check berdasarkan user_id only
        return $model->user_id == $owner['id'];
    }
}

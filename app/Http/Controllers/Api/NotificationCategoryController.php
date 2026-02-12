<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class NotificationCategoryController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of active notification categories.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = NotificationCategory::active()
            ->ordered()
            ->get(['key', 'name', 'icon', 'color']);

        return $this->successResponse(
            $categories,
            'Notification categories retrieved successfully'
        );
    }
}

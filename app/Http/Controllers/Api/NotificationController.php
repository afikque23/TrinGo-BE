<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ApiResponse;
use App\Traits\HasOwnerIdentification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    use ApiResponse, HasOwnerIdentification;

    /**
     * Display a listing of notifications for authenticated user or device.
     * Supports both authenticated users and guest mode (device_id).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Build query based on owner (user_id or device_id)
        $query = Notification::query();
        $this->applyOwnerFilter($query, $request);
        
        $query->with(['category', 'vehicle'])->latest();

        // Filter by category if provided
        if ($request->has('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }

        // Filter by unread status if provided
        if ($request->has('unread') && filter_var($request->unread, FILTER_VALIDATE_BOOLEAN)) {
            $query->unread();
        }

        // Paginate results
        $perPage = $request->get('per_page', 10);
        $notifications = $query->paginate($perPage);

        // Get unread count
        $unreadQuery = Notification::query();
        $this->applyOwnerFilter($unreadQuery, $request);
        $unreadCount = $unreadQuery->unread()->count();

        return $this->successResponse([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
            'unread_count' => $unreadCount,
        ], 'Notifications retrieved successfully');
    }

    /**
     * Mark a notification as read.
     * Validates ownership before marking.
     *
     * @param Request $request
     * @param Notification $notification
     * @return JsonResponse
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        // Validate ownership
        if (!$this->canAccessModel($notification, $request)) {
            return $this->errorResponse(
                'Anda tidak memiliki akses ke notifikasi ini.',
                403
            );
        }

        $notification->markAsRead();

        return $this->successResponse(
            $notification->fresh(),
            'Notification marked as read'
        );
    }

    /**
     * Mark all notifications as read for authenticated user or device.
     * Supports both authenticated users and guest mode (device_id).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        // Build query based on owner
        $query = Notification::query();
        $this->applyOwnerFilter($query, $request);
        
        $updated = $query->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $this->successResponse(
            ['updated_count' => $updated],
            'All notifications marked as read'
        );
    }

    /**
     * Display the specified notification.
     * Validates ownership before showing.
     *
     * @param Request $request
     * @param Notification $notification
     * @return JsonResponse
     */
    public function show(Request $request, Notification $notification): JsonResponse
    {
        // Validate ownership
        if (!$this->canAccessModel($notification, $request)) {
            return $this->errorResponse(
                'Anda tidak memiliki akses ke notifikasi ini.',
                403
            );
        }

        $notification->load(['category', 'vehicle']);

        return $this->successResponse(
            $notification,
            'Notification retrieved successfully'
        );
    }

    /**
     * Remove the specified notification from storage.
     * Validates ownership before deleting.
     *
     * @param Request $request
     * @param Notification $notification
     * @return JsonResponse
     */
    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        // Validate ownership
        if (!$this->canAccessModel($notification, $request)) {
            return $this->errorResponse(
                'Anda tidak memiliki akses ke notifikasi ini.',
                403
            );
        }

        $notification->delete();

        return $this->successResponse(
            null,
            'Notification deleted successfully'
        );
    }
}

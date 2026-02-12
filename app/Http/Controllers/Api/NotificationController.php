<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of notifications for authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Build query
        $query = Notification::where('user_id', $user->id)
            ->with(['category', 'vehicle'])
            ->latest();

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
        $unreadCount = Notification::where('user_id', $user->id)
            ->unread()
            ->count();

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
     *
     * @param Notification $notification
     * @return JsonResponse
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Authorize
        Gate::authorize('update', $notification);

        $notification->markAsRead();

        return $this->successResponse(
            $notification->fresh(),
            'Notification marked as read'
        );
    }

    /**
     * Mark all notifications as read for authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $updated = Notification::where('user_id', $user->id)
            ->unread()
            ->update([
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
     *
     * @param Notification $notification
     * @return JsonResponse
     */
    public function show(Notification $notification): JsonResponse
    {
        // Authorize
        Gate::authorize('view', $notification);

        $notification->load(['category', 'vehicle']);

        return $this->successResponse(
            $notification,
            'Notification retrieved successfully'
        );
    }

    /**
     * Remove the specified notification from storage.
     *
     * @param Notification $notification
     * @return JsonResponse
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Authorize
        Gate::authorize('delete', $notification);

        $notification->delete();

        return $this->successResponse(
            null,
            'Notification deleted successfully'
        );
    }
}

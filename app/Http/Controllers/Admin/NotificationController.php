<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationCategory;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Display notification management page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $templates = NotificationTemplate::with(['category', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'category' => $template->category ? [
                        'key' => $template->category->key,
                        'name' => $template->category->name
                    ] : null,
                    'category_key' => $template->category_key,
                    'trigger_type' => $template->trigger_type,
                    'threshold_value' => $template->threshold_value,
                    'priority' => $template->priority,
                    'channel' => $template->channel,
                    'message_template' => $template->message_template,
                    'is_active' => (bool) $template->is_active,
                    'created_at' => $template->created_at ? $template->created_at->format('Y-m-d H:i:s') : null,
                ];
            });

        $categories = NotificationCategory::active()
            ->ordered()
            ->get()
            ->map(function($category) {
                return [
                    'id' => $category->id,
                    'key' => $category->key,
                    'name' => $category->name,
                    'icon' => $category->icon,
                    'color' => $category->color
                ];
            });

        // Get all users for test push notification dropdown
        $users = \App\Models\User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('admin.manajemen_notifikasi.manajemen_notifikasi', compact('templates', 'categories', 'users'));
    }

    /**
     * Store a newly created notification template.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_key' => 'required|string|exists:notification_categories,key',
            'trigger_type' => 'nullable|string|max:255',
            'threshold_value' => 'nullable|integer|min:0',
            'priority' => 'required|in:low,normal,high,critical',
            'channel' => 'required|in:in_app,push,email',
            'message_template' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        NotificationTemplate::create([
            'name' => $request->name,
            'category_key' => $request->category_key,
            'trigger_type' => $request->trigger_type,
            'threshold_value' => $request->threshold_value,
            'priority' => $request->priority,
            'channel' => $request->channel,
            'message_template' => $request->message_template,
            'is_active' => $request->has('is_active'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.notifications')
            ->with('success', 'Notifikasi berhasil ditambahkan');
    }

    /**
     * Update the specified notification template.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_key' => 'required|string|exists:notification_categories,key',
            'trigger_type' => 'nullable|string|max:255',
            'threshold_value' => 'nullable|integer|min:0',
            'priority' => 'required|in:low,normal,high,critical',
            'channel' => 'required|in:in_app,push,email',
            'message_template' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $template->update([
            'name' => $request->name,
            'category_key' => $request->category_key,
            'trigger_type' => $request->trigger_type,
            'threshold_value' => $request->threshold_value,
            'priority' => $request->priority,
            'channel' => $request->channel,
            'message_template' => $request->message_template,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.notifications')
            ->with('success', 'Notifikasi berhasil diperbarui');
    }

    /**
     * Toggle notification template status.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->is_active = !$template->is_active;
        $template->save();

        return redirect()->route('admin.notifications')
            ->with('success', 'Status notifikasi berhasil diubah');
    }

    /**
     * Remove the specified notification template.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('admin.notifications')
            ->with('success', 'Notifikasi berhasil dihapus');
    }

    /**
     * Test push notification ke user tertentu.
     * Mengirim notifikasi test dengan variabel contoh.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id Template ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function testPush(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        
        // Ambil target user dari request, default ke admin yang login
        $userId = $request->input('user_id', Auth::id());
        $targetUser = User::findOrFail($userId);

        $notificationService = app(NotificationService::class);
        $result = $notificationService->testPush($template, $targetUser);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi test berhasil dikirim!',
            'data' => [
                'notification_id' => $result['notification']->id,
                'template_name' => $template->name,
                'target_user' => $targetUser->name,
                'message_preview' => $result['message_preview'],
                'push_sent' => $result['push_sent'],
                'push_result' => $result['push_result'],
                'channel' => $template->channel,
            ],
        ]);
    }

    /**
     * Test push notification ke device ID tertentu (untuk guest mode testing).
     * 
     * @deprecated Since persistent login implementation (Feb 2026)
     * @deprecated Guest mode has been removed. Use testPush() instead which sends to all user devices.
     * @deprecated This endpoint kept for backward compatibility but should not be used.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function testPushToDevice(Request $request, $id)
    {
        // Return deprecation notice
        return response()->json([
            'success' => false,
            'message' => 'This endpoint is deprecated. Guest mode has been removed. Use /test-push endpoint instead which sends to all authenticated user devices.',
            'deprecated_since' => '2026-02-20',
            'alternative' => 'POST /admin/notifications/{id}/test-push',
        ], 410); // 410 Gone - indicates the resource is no longer available
        
        /* DEPRECATED CODE - Kept for reference
        $request->validate([
            'device_id' => 'required|string|uuid',
        ]);

        $template = NotificationTemplate::findOrFail($id);
        $deviceId = $request->input('device_id');

        // Check if device has active FCM token
        $deviceToken = \App\Models\DeviceToken::where('device_id', $deviceId)
            ->where('is_active', true)
            ->first();

        if (!$deviceToken) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID tidak ditemukan atau tidak memiliki FCM token aktif',
            ], 404);
        }

        // Get vehicle for this device (optional)
        $vehicle = \App\Models\Vehicle::where('device_id', $deviceId)
            ->where('is_primary', true)
            ->first();

        $notificationService = app(\App\Services\NotificationService::class);
        
        // Generate sample variables
        $sampleVariables = $notificationService->getSampleVariables();
        
        // Override vehicle name if we have actual vehicle
        if ($vehicle) {
            $sampleVariables['vehicle_name'] = $vehicle->title ?? $vehicle->make . ' ' . $vehicle->model;
            $sampleVariables['current_km'] = number_format($vehicle->odometer ?? 0);
        }

        // Send notification to device
        $notification = $notificationService->sendFromTemplate(
            $template,
            $sampleVariables,
            null, // No user for guest mode
            $deviceId,
            $vehicle
        );

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim notifikasi test',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi test berhasil dikirim ke device!',
            'data' => [
                'notification_id' => $notification->id,
                'device_id' => $deviceId,
                'device_token' => substr($deviceToken->fcm_token, 0, 30) . '...',
                'vehicle_name' => $sampleVariables['vehicle_name'] ?? 'Motor Anda',
                'push_sent' => $notification->push_sent,
                'push_success' => $notification->push_success,
                'channel' => $template->channel,
                'title' => $notification->title,
                'message' => $notification->message,
            ],
        ]);
        */
    }

    /**
     * Preview pesan template dengan variabel contoh.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewMessage(Request $request)
    {
        $messageTemplate = $request->input('message_template', '');

        $notificationService = app(NotificationService::class);
        $sampleVariables = $notificationService->getSampleVariables();
        $preview = $notificationService->parseTemplate($messageTemplate, $sampleVariables);

        return response()->json([
            'success' => true,
            'data' => [
                'original' => $messageTemplate,
                'preview' => $preview,
                'variables_used' => $sampleVariables,
            ],
        ]);
    }

    /**
     * Dapatkan daftar variabel yang tersedia.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVariables()
    {
        return response()->json([
            'success' => true,
            'data' => NotificationService::AVAILABLE_VARIABLES,
        ]);
    }
}


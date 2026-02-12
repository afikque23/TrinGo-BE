<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationCategory;
use App\Models\NotificationTemplate;
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

        return view('admin.manajemen_notifikasi.manajemen_notifikasi', compact('templates', 'categories'));
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
}


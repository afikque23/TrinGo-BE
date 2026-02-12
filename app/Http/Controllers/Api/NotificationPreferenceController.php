<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationCategory;
use App\Models\NotificationPreference;
use App\Models\NotificationTemplate;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationPreferenceController extends Controller
{
    use ApiResponse;

    /**
     * Get all notification preferences for the authenticated user.
     * Returns all templates grouped by category with user's preference status.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        // Get all active categories
        $categories = NotificationCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $preferences = [];

        foreach ($categories as $category) {
            // Get all active templates in this category
            $templates = NotificationTemplate::where('category_key', $category->key)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            $templatePreferences = [];

            foreach ($templates as $template) {
                // Check if user has a specific preference for this template
                $preference = NotificationPreference::where('user_id', $user->id)
                    ->where('template_id', $template->id)
                    ->first();

                $isEnabled = NotificationPreference::isEnabledFor($user, $template);

                $templatePreferences[] = [
                    'template_id' => $template->id,
                    'template_name' => $template->name,
                    'trigger_type' => $template->trigger_type,
                    'priority' => $template->priority,
                    'is_enabled' => $isEnabled,
                    'has_specific_preference' => $preference !== null,
                ];
            }

            // Check if user has a category-level preference
            $categoryPreference = NotificationPreference::where('user_id', $user->id)
                ->where('category_key', $category->key)
                ->whereNull('template_id')
                ->first();

            $preferences[] = [
                'category' => [
                    'key' => $category->key,
                    'name' => $category->name,
                    'icon' => $category->icon,
                    'color' => $category->color,
                ],
                'category_enabled' => $categoryPreference ? $categoryPreference->is_enabled : true,
                'has_category_preference' => $categoryPreference !== null,
                'templates' => $templatePreferences,
            ];
        }

        return $this->successResponse(
            $preferences,
            'Notification preferences retrieved successfully'
        );
    }

    /**
     * Update notification preference for a template or category.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'nullable|exists:notification_templates,id',
            'category_key' => 'nullable|exists:notification_categories,key',
            'is_enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation error',
                422,
                $validator->errors()
            );
        }

        // Must provide either template_id or category_key, but not both
        if ((!$request->template_id && !$request->category_key) || 
            ($request->template_id && $request->category_key)) {
            return $this->errorResponse(
                'Must provide either template_id or category_key, but not both',
                422,
                null
            );
        }

        $user = Auth::user();

        // Prepare the data for updateOrCreate
        $conditions = ['user_id' => $user->id];
        $data = ['is_enabled' => $request->is_enabled];

        if ($request->template_id) {
            // Template-specific preference
            $conditions['template_id'] = $request->template_id;
            $data['template_id'] = $request->template_id;
            $data['category_key'] = null;

            $template = NotificationTemplate::find($request->template_id);
            $message = 'Notification preference for template "' . $template->name . '" updated successfully';
        } else {
            // Category-level preference
            $conditions['category_key'] = $request->category_key;
            $conditions['template_id'] = null;
            $data['category_key'] = $request->category_key;
            $data['template_id'] = null;

            $category = NotificationCategory::where('key', $request->category_key)->first();
            $message = 'Notification preference for category "' . $category->name . '" updated successfully';
        }

        $preference = NotificationPreference::updateOrCreate(
            $conditions,
            $data
        );

        return $this->successResponse(
            [
                'preference_id' => $preference->id,
                'is_enabled' => $preference->is_enabled,
            ],
            $message
        );
    }

    /**
     * Delete a specific notification preference (reset to default).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'nullable|exists:notification_templates,id',
            'category_key' => 'nullable|exists:notification_categories,key',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation error',
                422,
                $validator->errors()
            );
        }

        // Must provide either template_id or category_key, but not both
        if ((!$request->template_id && !$request->category_key) || 
            ($request->template_id && $request->category_key)) {
            return $this->errorResponse(
                'Must provide either template_id or category_key, but not both',
                422,
                null
            );
        }

        $user = Auth::user();
        $query = NotificationPreference::where('user_id', $user->id);

        if ($request->template_id) {
            $query->where('template_id', $request->template_id);
            $message = 'Template preference reset to default';
        } else {
            $query->where('category_key', $request->category_key)
                  ->whereNull('template_id');
            $message = 'Category preference reset to default';
        }

        $deleted = $query->delete();

        if ($deleted) {
            return $this->successResponse(null, $message);
        }

        return $this->errorResponse('No preference found to delete', 404, null);
    }
}

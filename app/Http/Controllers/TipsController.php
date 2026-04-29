<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTipRequest;
use App\Http\Requests\UpdateTipRequest;
use App\Http\Requests\TipActionRequest;
use App\Http\Requests\TipShareRequest;
use App\Http\Requests\TipRatingRequest;
use App\Http\Requests\TipUseTemplateRequest;
use App\Http\Resources\TipResource;
use App\Http\Resources\TipCollection;
use App\Http\Resources\TipDetailResource;
use App\Models\Tip;
use App\Models\TipTool;
use App\Models\TipStep;
use App\Models\TipTag;
use App\Models\TipLike;
use App\Models\TipBookmark;
use App\Models\TipShare;
use App\Models\TipRating;
use App\Models\ServiceSchedule;
use App\Models\Vehicle;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TipsController extends Controller
{
    use ApiResponse;

    /**
     * List tips/templates owned by the authenticated user.
     * GET /api/v1/motorcycle/tips/my
     */
    public function myTips(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Tip::with(['user', 'tags'])
            ->where('user_id', $user->id);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('brand')) {
            $query->byBrand($request->brand);
        }

        if ($request->filled('riding_style')) {
            $query->byRidingStyle($request->riding_style);
        }

        if ($request->filled('tags')) {
            $query->byTags($request->tags);
        }

        if ($request->filled('hashtags')) {
            $query->byHashtags($request->hashtags);
        }

        if ($request->filled('status')) {
            $allowedStatuses = ['pending_review', 'published', 'rejected'];
            $statusFilters = is_array($request->status)
                ? $request->status
                : explode(',', (string) $request->status);

            $statusFilters = array_values(array_unique(array_map(
                static fn ($status) => trim((string) $status),
                $statusFilters
            )));

            $validStatuses = array_values(array_intersect($statusFilters, $allowedStatuses));

            if (empty($validStatuses)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('status', $validStatuses);
            }
        }

        $sortBy = $request->input('sort_by', 'latest');
        $query->sortBy($sortBy);

        $perPage = min($request->input('limit', 10), 50);
        $tips = $query->paginate($perPage);

        return $this->successResponse(
            new TipCollection($tips),
            'Tips saya berhasil diambil'
        );
    }

    /**
     * Display a listing of tips with filters.
     * GET /api/v1/motorcycle/tips
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tip::with(['user', 'tags']);

        $authenticatedUserId = $request->user()?->id;
        $requestedUserId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $isOwnTipsRequest = $authenticatedUserId !== null
            && $requestedUserId !== null
            && $requestedUserId === (int) $authenticatedUserId;

        if (!$isOwnTipsRequest) {
            // Default list (public/authenticated browsing) only shows published tips.
            $query->published();
        }

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('brand')) {
            $query->byBrand($request->brand);
        }

        if ($request->filled('riding_style')) {
            $query->byRidingStyle($request->riding_style);
        }

        if ($request->filled('tags')) {
            $query->byTags($request->tags);
        }

        if ($request->filled('hashtags')) {
            $query->byHashtags($request->hashtags);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $allowedStatuses = ['pending_review', 'published', 'rejected'];
            $statusFilters = is_array($request->status)
                ? $request->status
                : explode(',', (string) $request->status);

            $statusFilters = array_values(array_unique(array_map(
                static fn ($status) => trim((string) $status),
                $statusFilters
            )));

            $validStatuses = array_values(array_intersect($statusFilters, $allowedStatuses));

            if (empty($validStatuses)) {
                // Keep behavior predictable for unknown status values.
                $query->whereRaw('1 = 0');
            } elseif ($isOwnTipsRequest) {
                $query->whereIn('status', $validStatuses);
            } elseif (!in_array('published', $validStatuses, true)) {
                // Non-owner requests cannot fetch non-published records.
                $query->whereRaw('1 = 0');
            }
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'latest');
        $query->sortBy($sortBy);

        // Pagination
        $perPage = min($request->input('limit', 10), 50);
        $tips = $query->paginate($perPage);

        return $this->successResponse(
            new TipCollection($tips),
            'Tips berhasil diambil'
        );
    }

    /**
     * Display the specified tip.
     * GET /api/v1/motorcycle/tips/{id}
     *
     * @param Request $request
     * @param Tip $tip
     * @return JsonResponse
     */
    public function show(Request $request, Tip $tip): JsonResponse
    {
        // Check if tip is published or owned by current user
        $userId = $request->user()?->id;
        
        if ($tip->status !== 'published' && $tip->user_id !== $userId) {
            return $this->notFoundResponse('Tips tidak ditemukan');
        }

        $tip->load(['user', 'tags', 'tools', 'steps']);

        // Increment views count
        $tip->incrementViews();

        return $this->successResponse(
            new TipDetailResource($tip),
            'Detail tips berhasil diambil'
        );
    }

    /**
     * Store a newly created tip.
     * POST /api/v1/motorcycle/tips
     *
     * @param StoreTipRequest $request
     * @return JsonResponse
     */
    public function store(StoreTipRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Create tip
            $tip = Tip::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'vehicle_brand' => $request->input('vehicle.brand'),
                'vehicle_model' => $request->input('vehicle.model'),
                'vehicle_year' => $request->input('vehicle.year'),
                'riding_style' => $request->input('vehicle.riding_style'),
                'estimated_time' => $request->estimated_time,
                'interval_distance_km' => $request->input('maintenance_interval.distance_km'),
                'interval_time_months' => $request->input('maintenance_interval.time_months'),
                'important_notes' => $request->important_notes,
                'hashtags' => $request->hashtags,
                'is_copyable' => $request->input('is_copyable', true),
                // No admin review flow is implemented yet; publish immediately so it appears in public/mobile.
                'status' => 'published',
            ]);

            // Create tools
            foreach ($request->tools as $index => $toolData) {
                TipTool::create([
                    'tip_id' => $tip->id,
                    'name' => $toolData['name'],
                    'is_optional' => $toolData['is_optional'] ?? false,
                    'order' => $index + 1,
                ]);
            }

            // Create steps
            foreach ($request->steps as $index => $stepData) {
                TipStep::create([
                    'tip_id' => $tip->id,
                    'step_number' => $index + 1,
                    'title' => $stepData['title'],
                    'description' => $stepData['description'],
                ]);
            }

            // Auto-create tags based on riding style
            $this->autoCreateTags($tip);

            DB::commit();

            return $this->createdResponse(
                [
                    'id' => $tip->id,
                    'title' => $tip->title,
                    'status' => $tip->status,
                    'created_at' => $tip->created_at->toISOString(),
                ],
                'Tips berhasil dibuat'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Gagal membuat tips: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update the specified tip.
     * PUT /api/v1/motorcycle/tips/{id}
     *
     * @param UpdateTipRequest $request
     * @param Tip $tip
     * @return JsonResponse
     */
    public function update(UpdateTipRequest $request, Tip $tip): JsonResponse
    {
        // Check ownership
        if ($tip->user_id !== $request->user()->id) {
            return $this->errorResponse(
                'Anda tidak memiliki izin untuk mengupdate tips ini',
                403
            );
        }

        DB::beginTransaction();
        
        try {
            // Update tip
            $updateData = [];
            
            if ($request->filled('title')) {
                $updateData['title'] = $request->title;
            }
            
            if ($request->filled('description')) {
                $updateData['description'] = $request->description;
            }
            
            if ($request->filled('vehicle.brand')) {
                $updateData['vehicle_brand'] = $request->input('vehicle.brand');
            }
            
            if ($request->filled('vehicle.model')) {
                $updateData['vehicle_model'] = $request->input('vehicle.model');
            }
            
            if ($request->filled('vehicle.year')) {
                $updateData['vehicle_year'] = $request->input('vehicle.year');
            }
            
            if ($request->filled('vehicle.riding_style')) {
                $updateData['riding_style'] = $request->input('vehicle.riding_style');
            }
            
            if ($request->has('estimated_time')) {
                $updateData['estimated_time'] = $request->estimated_time;
            }
            
            if ($request->has('maintenance_interval.distance_km')) {
                $updateData['interval_distance_km'] = $request->input('maintenance_interval.distance_km');
            }
            
            if ($request->has('maintenance_interval.time_months')) {
                $updateData['interval_time_months'] = $request->input('maintenance_interval.time_months');
            }
            
            if ($request->has('important_notes')) {
                $updateData['important_notes'] = $request->important_notes;
            }
            
            if ($request->has('hashtags')) {
                $updateData['hashtags'] = $request->hashtags;
            }
            
            if ($request->has('is_copyable')) {
                $updateData['is_copyable'] = $request->is_copyable;
            }

            $tip->update($updateData);

            // Update tools if provided
            if ($request->filled('tools')) {
                $tip->tools()->delete();
                foreach ($request->tools as $index => $toolData) {
                    TipTool::create([
                        'tip_id' => $tip->id,
                        'name' => $toolData['name'],
                        'is_optional' => $toolData['is_optional'] ?? false,
                        'order' => $index + 1,
                    ]);
                }
            }

            // Update steps if provided
            if ($request->filled('steps')) {
                $tip->steps()->delete();
                foreach ($request->steps as $index => $stepData) {
                    TipStep::create([
                        'tip_id' => $tip->id,
                        'step_number' => $index + 1,
                        'title' => $stepData['title'],
                        'description' => $stepData['description'],
                    ]);
                }
            }

            DB::commit();

            return $this->successResponse(
                [
                    'id' => $tip->id,
                    'title' => $tip->title,
                    'updated_at' => $tip->updated_at->toISOString(),
                ],
                'Tips berhasil diupdate'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Gagal mengupdate tips: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified tip.
     * DELETE /api/v1/motorcycle/tips/{id}
     *
     * @param Request $request
     * @param Tip $tip
     * @return JsonResponse
     */
    public function destroy(Request $request, Tip $tip): JsonResponse
    {
        // Check ownership
        if ($tip->user_id !== $request->user()->id) {
            return $this->errorResponse(
                'Anda tidak memiliki izin untuk menghapus tips ini',
                403
            );
        }

        $tip->delete();

        return $this->successResponse(
            null,
            'Tips berhasil dihapus'
        );
    }

    /**
     * Like or unlike a tip.
     * POST /api/v1/motorcycle/tips/{id}/like
     *
     * @param TipActionRequest $request
     * @param Tip $tip
     * @return JsonResponse
     */
    public function like(TipActionRequest $request, Tip $tip): JsonResponse
    {
        $userId = $request->user()?->id;
        $deviceId = $request->header('X-Device-ID');

        if (!$userId && !$deviceId) {
            return $this->errorResponse(
                'User ID atau Device ID harus tersedia',
                400
            );
        }

        $action = $request->action;

        if (!in_array($action, ['like', 'unlike'], true)) {
            return $this->errorResponse(
                'Action tidak valid untuk endpoint like',
                422
            );
        }

        $identityQuery = TipLike::where('tip_id', $tip->id);
        if ($userId) {
            $identityQuery->where('user_id', $userId);
        } else {
            $identityQuery->where('device_id', $deviceId);
        }

        if ($action === 'like') {
            // Idempotent: do not duplicate like/counter if already liked.
            if (!$identityQuery->exists()) {
                TipLike::create([
                    'tip_id' => $tip->id,
                    'user_id' => $userId,
                    'device_id' => $deviceId,
                ]);
            }

            $newCount = TipLike::where('tip_id', $tip->id)->count();
            DB::table('tips')->where('id', $tip->id)->update(['likes_count' => $newCount]);

            return $this->successResponse(
                [
                    'is_liked' => true,
                    'likes_count' => $newCount,
                ],
                'Tips berhasil dilike'
            );

        } else {
            $identityQuery->delete();

            $newCount = TipLike::where('tip_id', $tip->id)->count();
            DB::table('tips')->where('id', $tip->id)->update(['likes_count' => $newCount]);

            return $this->successResponse(
                [
                    'is_liked' => false,
                    'likes_count' => $newCount,
                ],
                'Like berhasil dihapus'
            );
        }
    }

    /**
     * Bookmark or unbookmark a tip.
     * POST /api/v1/motorcycle/tips/{id}/bookmark
     *
     * @param TipActionRequest $request
     * @param Tip $tip
     * @return JsonResponse
     */
    public function bookmark(TipActionRequest $request, Tip $tip): JsonResponse
    {
        $userId = $request->user()?->id;
        $deviceId = $request->header('X-Device-ID');

        if (!$userId && !$deviceId) {
            return $this->errorResponse(
                'User ID atau Device ID harus tersedia',
                400
            );
        }

        $action = $request->action;

        if (!in_array($action, ['bookmark', 'unbookmark'], true)) {
            return $this->errorResponse(
                'Action tidak valid untuk endpoint bookmark',
                422
            );
        }

        $identityQuery = TipBookmark::where('tip_id', $tip->id);
        if ($userId) {
            $identityQuery->where('user_id', $userId);
        } else {
            $identityQuery->where('device_id', $deviceId);
        }

        if ($action === 'bookmark') {
            // Idempotent: do not duplicate bookmark/counter if already bookmarked.
            if (!$identityQuery->exists()) {
                TipBookmark::create([
                    'tip_id' => $tip->id,
                    'user_id' => $userId,
                    'device_id' => $deviceId,
                ]);
            }

            $newCount = TipBookmark::where('tip_id', $tip->id)->count();
            DB::table('tips')->where('id', $tip->id)->update(['bookmarks_count' => $newCount]);

            return $this->successResponse(
                [
                    'is_bookmarked' => true,
                    'bookmarks_count' => $newCount,
                ],
                'Tips berhasil disimpan'
            );

        } else {
            $identityQuery->delete();

            $newCount = TipBookmark::where('tip_id', $tip->id)->count();
            DB::table('tips')->where('id', $tip->id)->update(['bookmarks_count' => $newCount]);

            return $this->successResponse(
                [
                    'is_bookmarked' => false,
                    'bookmarks_count' => $newCount,
                ],
                'Bookmark berhasil dihapus'
            );
        }
    }

    /**
     * Track sharing of a tip.
     * POST /api/v1/motorcycle/tips/{id}/share
     *
     * @param TipShareRequest $request
     * @param Tip $tip
     * @return JsonResponse
     */
    public function share(TipShareRequest $request, Tip $tip): JsonResponse
    {
        $userId = $request->user()?->id;
        $deviceId = $request->header('X-Device-ID');

        // Create share record
        TipShare::create([
            'tip_id' => $tip->id,
            'user_id' => $userId,
            'device_id' => $deviceId,
            'platform' => $request->platform,
        ]);

        // Increment share count
        $tip->increment('shares_count');

        return $this->successResponse(
            [
                'shares_count' => $tip->fresh()->shares_count,
            ],
            'Share berhasil dicatat'
        );
    }

    /**
     * Rate or update rating for a tip.
     * POST /api/v1/motorcycle/tips/{id}/rate
     *
     * @param TipRatingRequest $request
     * @param Tip $tip
     * @return JsonResponse
     */
    public function rate(TipRatingRequest $request, Tip $tip): JsonResponse
    {
        $userId = $request->user()?->id;

        if (!$userId) {
            return $this->errorResponse(
                'User harus login untuk memberikan rating',
                401
            );
        }

        // Create or update rating
        $rating = TipRating::updateOrCreate(
            [
                'tip_id' => $tip->id,
                'user_id' => $userId,
            ],
            [
                'rating' => $request->rating,
            ]
        );

        // Update average rating and count
        $tip->updateAverageRating();
        $tip = $tip->fresh();

        return $this->successResponse(
            [
                'user_rating' => $rating->rating,
                'average_rating' => (float) $tip->rating,
                'ratings_count' => $tip->ratings_count,
            ],
            'Rating berhasil disimpan'
        );
    }

    /**
     * Create a service schedule from tip template.
     * POST /api/v1/motorcycle/tips/{id}/use-template
     *
     * @param TipUseTemplateRequest $request
     * @param Tip $tip
     * @return JsonResponse
     */
    public function useTemplate(TipUseTemplateRequest $request, Tip $tip): JsonResponse
    {
        // Validate vehicle ownership
        $vehicle = Vehicle::find($request->vehicle_id);
        
        if (!$vehicle) {
            return $this->notFoundResponse('Kendaraan tidak ditemukan');
        }

        // Check if vehicle belongs to user
        $userId = $request->user()?->id;
        $deviceId = $request->header('X-Device-ID');
        
        if ($userId && $vehicle->user_id !== $userId) {
            return $this->errorResponse(
                'Kendaraan tidak ditemukan atau bukan milik Anda',
                403
            );
        } elseif ($deviceId && $vehicle->device_id !== $deviceId) {
            return $this->errorResponse(
                'Kendaraan tidak ditemukan atau bukan milik Anda',
                403
            );
        }

        // Create service schedule
        $scheduleData = [
            'vehicle_id' => $request->vehicle_id,
            'service_name' => $tip->title,
            'description' => $request->notes ?? $tip->description,
            'schedule_type' => $request->schedule_type,
        ];

        if ($request->schedule_type === 'interval') {
            $scheduleData['interval_type'] = $request->interval_type;
            
            if ($request->interval_type === 'distance' || $request->interval_type === 'both') {
                $scheduleData['interval_distance'] = $request->interval_value;
            }
            
            if ($request->interval_type === 'time' || $request->interval_type === 'both') {
                $scheduleData['interval_months'] = $request->interval_value;
            }
        }

        if ($request->filled('start_date')) {
            $scheduleData['next_service_date'] = $request->start_date;
        }

        $schedule = ServiceSchedule::create($scheduleData);

        // Increment usage count
        $tip->incrementUsage();

        return $this->createdResponse(
            [
                'schedule_id' => $schedule->id,
                'tip_id' => $tip->id,
                'vehicle_id' => $vehicle->id,
                'next_maintenance_date' => $schedule->next_service_date,
                'created_at' => $schedule->created_at->toISOString(),
            ],
            'Jadwal perawatan berhasil dibuat dari template'
        );
    }

    /**
     * Auto-create tags based on tip properties.
     *
     * @param Tip $tip
     * @return void
     */
    private function autoCreateTags(Tip $tip): void
    {
        $tags = [];

        // Create riding style tag
        $ridingStyleTag = TipTag::firstOrCreate(
            [
                'name' => $tip->riding_style,
                'type' => 'riding_style',
            ],
            [
                'color' => '#2B7FFF',
                'icon' => 'directions_bike',
            ]
        );
        $tags[] = $ridingStyleTag->id;

        // Attach tags to tip
        $tip->tags()->attach($tags);
    }
}

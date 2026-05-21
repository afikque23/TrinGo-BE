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
use App\Models\TipSearchLog;
use App\Models\ServiceSchedule;
use App\Models\Vehicle;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class TipsController extends Controller
{
    use ApiResponse;

    /**
     * Apply personalized recommendation sorting to a tips query.
     *
     * Signals used:
     * - User/device implicit feedback: like (+2) & bookmark (+3) -> tag weights
     * - Recent search keywords
     * - Popularity and recency
     */
    private function applyRecommendedSorting(Builder $query, Request $request): Builder
    {
        $userId = $request->user()?->id;
        $deviceId = $request->header('X-Device-ID');

        // Fallback: if we have no identity, just use popular.
        if (!$userId && (!$deviceId || trim((string) $deviceId) === '')) {
            return $query->sortBy('popular');
        }

        // -------------------------
        // 1) Build user tag weights
        // -------------------------
        $likeTagCountsQuery = DB::table('tip_likes')
            ->join('tip_tag_pivot', 'tip_likes.tip_id', '=', 'tip_tag_pivot.tip_id');
        $bookmarkTagCountsQuery = DB::table('tip_bookmarks')
            ->join('tip_tag_pivot', 'tip_bookmarks.tip_id', '=', 'tip_tag_pivot.tip_id');

        if ($userId) {
            $likeTagCountsQuery->where('tip_likes.user_id', $userId);
            $bookmarkTagCountsQuery->where('tip_bookmarks.user_id', $userId);
        } else {
            $likeTagCountsQuery->where('tip_likes.device_id', $deviceId);
            $bookmarkTagCountsQuery->where('tip_bookmarks.device_id', $deviceId);
        }

        $likeTagCounts = $likeTagCountsQuery
            ->select('tip_tag_pivot.tip_tag_id', DB::raw('COUNT(*) as c'))
            ->groupBy('tip_tag_pivot.tip_tag_id')
            ->pluck('c', 'tip_tag_id')
            ->all();

        $bookmarkTagCounts = $bookmarkTagCountsQuery
            ->select('tip_tag_pivot.tip_tag_id', DB::raw('COUNT(*) as c'))
            ->groupBy('tip_tag_pivot.tip_tag_id')
            ->pluck('c', 'tip_tag_id')
            ->all();

        $tagWeights = [];
        foreach ($likeTagCounts as $tagId => $count) {
            $tagWeights[(int) $tagId] = ($tagWeights[(int) $tagId] ?? 0) + ((int) $count * 2);
        }
        foreach ($bookmarkTagCounts as $tagId => $count) {
            $tagWeights[(int) $tagId] = ($tagWeights[(int) $tagId] ?? 0) + ((int) $count * 3);
        }

        arsort($tagWeights);
        $tagWeights = array_slice($tagWeights, 0, 12, true);

        // -------------------------
        // 2) Recent search keywords
        // -------------------------
        $keywordQuery = TipSearchLog::query()->orderByDesc('created_at');
        if ($userId) {
            $keywordQuery->where('user_id', $userId);
        } else {
            $keywordQuery->whereNull('user_id')->where('device_id', $deviceId);
        }

        $keywords = $keywordQuery
            ->where('created_at', '>=', now()->subDays(30))
            ->limit(8)
            ->pluck('keyword')
            ->filter(fn ($k) => is_string($k) && $k !== '')
            ->unique()
            ->values()
            ->take(4)
            ->all();

        // -------------------------
        // 3) Compose scoring formula
        // -------------------------
        $bindings = [];
        $scoreParts = [];

        // Tag score from a subquery to avoid ONLY_FULL_GROUP_BY issues.
        if (!empty($tagWeights)) {
            $tagExprParts = [];
            $tagBindings = [];
            foreach ($tagWeights as $tagId => $weight) {
                $tagExprParts[] = 'SUM(CASE WHEN tpp.tip_tag_id = ? THEN ? ELSE 0 END)';
                $tagBindings[] = (int) $tagId;
                $tagBindings[] = (int) $weight;
            }

            $tagExpr = implode(' + ', $tagExprParts);
            $tagScoreSub = DB::table('tip_tag_pivot as tpp')
                ->select('tpp.tip_id')
                ->selectRaw($tagExpr . ' AS tag_score', $tagBindings)
                ->whereIn('tpp.tip_tag_id', array_keys($tagWeights))
                ->groupBy('tpp.tip_id');

            $query->leftJoinSub($tagScoreSub, 'tag_scores', function ($join) {
                $join->on('tag_scores.tip_id', '=', 'tips.id');
            });

            $scoreParts[] = '(COALESCE(tag_scores.tag_score, 0) * 1.0)';
        }

        // Search score: boost items matching recent search keywords.
        foreach ($keywords as $keyword) {
            $kwLike = '%' . mb_strtolower($keyword) . '%';
            $scoreParts[] = '(' .
                'CASE WHEN LOWER(tips.title) LIKE ? THEN 2 ELSE 0 END + ' .
                'CASE WHEN LOWER(tips.description) LIKE ? THEN 1 ELSE 0 END + ' .
                'CASE WHEN LOWER(CAST(tips.hashtags AS CHAR)) LIKE ? THEN 1 ELSE 0 END'
            . ')';
            $bindings[] = $kwLike;
            $bindings[] = $kwLike;
            $bindings[] = $kwLike;
        }

        // Popularity score.
        $scoreParts[] = '((LOG(1 + tips.likes_count) * 1.0) + (LOG(1 + tips.bookmarks_count) * 1.5) + (LOG(1 + tips.views_count) * 0.2)) * 0.8';

        // Recency score.
        $scoreParts[] = '(1 / (1 + TIMESTAMPDIFF(DAY, tips.created_at, NOW()))) * 0.3';

        // Penalty if already interacted.
        if ($userId) {
            $query->leftJoin('tip_likes as my_likes', function ($join) use ($userId) {
                $join->on('my_likes.tip_id', '=', 'tips.id')
                    ->where('my_likes.user_id', '=', $userId);
            });
            $query->leftJoin('tip_bookmarks as my_bookmarks', function ($join) use ($userId) {
                $join->on('my_bookmarks.tip_id', '=', 'tips.id')
                    ->where('my_bookmarks.user_id', '=', $userId);
            });
        } else {
            $query->leftJoin('tip_likes as my_likes', function ($join) use ($deviceId) {
                $join->on('my_likes.tip_id', '=', 'tips.id')
                    ->where('my_likes.device_id', '=', $deviceId);
            });
            $query->leftJoin('tip_bookmarks as my_bookmarks', function ($join) use ($deviceId) {
                $join->on('my_bookmarks.tip_id', '=', 'tips.id')
                    ->where('my_bookmarks.device_id', '=', $deviceId);
            });
        }

        $scoreParts[] = '-(CASE WHEN my_likes.id IS NULL THEN 0 ELSE 5 END)';
        $scoreParts[] = '-(CASE WHEN my_bookmarks.id IS NULL THEN 0 ELSE 7 END)';

        $scoreExpr = empty($scoreParts) ? '0' : implode(' + ', $scoreParts);

        return $query
            ->select('tips.*')
            ->selectRaw($scoreExpr . ' AS reco_score', $bindings)
            ->orderByDesc('reco_score')
            ->orderByDesc('tips.created_at');
    }

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
            TipSearchLog::logKeyword($user->id, $request->header('X-Device-ID'), (string) $request->search, 'tips_my');
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
        if ($sortBy === 'recommended') {
            $this->applyRecommendedSorting($query, $request);
        } else {
            $query->sortBy($sortBy);
        }

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
            TipSearchLog::logKeyword($request->user()?->id, $request->header('X-Device-ID'), (string) $request->search, 'tips_index');
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
        if ($sortBy === 'recommended') {
            $this->applyRecommendedSorting($query, $request);
        } else {
            $query->sortBy($sortBy);
        }

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

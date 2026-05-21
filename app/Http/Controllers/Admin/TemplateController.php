<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use App\Models\TipStep;
use App\Models\TipTag;
use App\Models\TipTool;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    /**
     * Display maintenance templates
     */
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'all');
        $brand = (string) $request->query('brand', 'all');
        // Default to all-time so admin sees all user/admin tips.
        $range = (string) $request->query('range', 'all');
        $search = Tip::normalizeKeyword((string) $request->query('q', ''));

        $rangeStart = null;
        if ($range === '7d') {
            $rangeStart = now()->subDays(7);
        } elseif ($range === '30d') {
            $rangeStart = now()->subDays(30);
        } elseif ($range === '90d') {
            $rangeStart = now()->subDays(90);
        }

        $tipsQuery = Tip::query()
            ->with(['user'])
            ->withCount(['steps', 'tools'])
            ->latest();

        if ($brand !== '' && $brand !== 'all') {
            $tipsQuery->where('vehicle_brand', $brand);
        }

        if ($status !== '' && $status !== 'all') {
            $tipsQuery->where('status', $status);
        }

        if ($search !== '') {
            $tipsQuery->search($search);
        }

        if ($rangeStart !== null) {
            $tipsQuery->where('created_at', '>=', $rangeStart);
        }

        $tips = $tipsQuery->paginate(10)->withQueryString();

        $brandOptions = Tip::query()
            ->select('vehicle_brand')
            ->whereNotNull('vehicle_brand')
            ->distinct()
            ->orderBy('vehicle_brand')
            ->pluck('vehicle_brand')
            ->values();

        $uploadedTodayQuery = Tip::query()->whereDate('created_at', now()->toDateString());
        if ($brand !== '' && $brand !== 'all') {
            $uploadedTodayQuery->where('vehicle_brand', $brand);
        }

        $autoPublishedQuery = Tip::query()->where('status', 'published');
        $flaggedQuery = Tip::query()->where('status', 'rejected');
        $deletedQuery = Tip::onlyTrashed();

        if ($brand !== '' && $brand !== 'all') {
            $autoPublishedQuery->where('vehicle_brand', $brand);
            $flaggedQuery->where('vehicle_brand', $brand);
            $deletedQuery->where('vehicle_brand', $brand);
        }

        if ($rangeStart !== null) {
            $autoPublishedQuery->where('created_at', '>=', $rangeStart);
            $flaggedQuery->where('created_at', '>=', $rangeStart);
            $deletedQuery->where('deleted_at', '>=', $rangeStart);
        }

        $stats = [
            'uploaded_today' => $uploadedTodayQuery->count(),
            'auto_published' => $autoPublishedQuery->count(),
            'flagged' => $flaggedQuery->count(),
            'deleted' => $deletedQuery->count(),
        ];

        return view('admin.template_perawatan.template_perawatan', [
            'tips' => $tips,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'brand' => $brand,
                'range' => $range,
                'q' => $search,
            ],
            'brandOptions' => $brandOptions,
        ]);
    }

    /**
     * Store new template
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'description' => ['required', 'string', 'min:20', 'max:500'],
            'vehicle_brand' => ['required', 'string', 'max:50'],
            'vehicle_model' => ['required', 'string', 'min:2', 'max:100'],
            'vehicle_year' => ['required', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'riding_style' => ['required', 'string', 'max:50'],
            'is_copyable' => ['required', 'boolean'],

            'hashtags' => ['nullable', 'string'],

            'steps' => ['required', 'array', 'min:3'],
            'steps.*' => ['required', 'string', 'max:200'],

            'tools' => ['required', 'array', 'min:1'],
            'tools.*' => ['required', 'string', 'max:200'],

            'interval_distance_km' => ['nullable', 'integer', 'min:100', 'max:50000'],
            'interval_time_months' => ['nullable', 'integer', 'min:1', 'max:60'],
            'important_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $hashtags = null;
        if ($request->filled('hashtags')) {
            $raw = (string) $request->input('hashtags');
            $hashtags = Tip::normalizeHashtags(
                array_map('trim', explode(',', $raw))
            );
        }

        /** @var int $userId */
        $userId = (int) $request->user()->id;

        return DB::transaction(function () use ($validated, $hashtags, $userId) {

            $tip = Tip::create([
                'user_id' => $userId,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'vehicle_brand' => $validated['vehicle_brand'],
                'vehicle_model' => $validated['vehicle_model'],
                'vehicle_year' => $validated['vehicle_year'],
                'riding_style' => $validated['riding_style'],
                'interval_distance_km' => $validated['interval_distance_km'] ?? null,
                'interval_time_months' => $validated['interval_time_months'] ?? null,
                'important_notes' => $validated['important_notes'] ?? null,
                'hashtags' => $hashtags,
                'is_copyable' => (bool) $validated['is_copyable'],
                // Admin creates should be visible immediately to public/mobile.
                'status' => 'published',
            ]);

            foreach ($validated['tools'] as $index => $toolName) {
                TipTool::create([
                    'tip_id' => $tip->id,
                    'name' => $toolName,
                    'is_optional' => false,
                    'order' => $index + 1,
                ]);
            }

            foreach ($validated['steps'] as $index => $stepTitle) {
                TipStep::create([
                    'tip_id' => $tip->id,
                    'step_number' => $index + 1,
                    'title' => $stepTitle,
                    // Admin UI matches mobile (single field). Persist as title + same description.
                    'description' => $stepTitle,
                ]);
            }

            $this->syncRidingStyleTag($tip);

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil ditambahkan dan dipublish',
                'data' => [
                    'id' => $tip->id,
                ],
            ]);
        });
    }

    /**
     * Get template data for editing
     */
    public function edit(int $id): JsonResponse
    {
        $tip = Tip::query()
            ->with(['steps', 'tools'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tip->id,
                'title' => $tip->title,
                'description' => $tip->description,
                'vehicle_brand' => $tip->vehicle_brand,
                'vehicle_model' => $tip->vehicle_model,
                'vehicle_year' => $tip->vehicle_year,
                'riding_style' => $tip->riding_style,
                'is_copyable' => (bool) $tip->is_copyable,
                'hashtags' => $tip->hashtags ?? [],
                'steps' => $tip->steps->map(fn (TipStep $step) => $step->title)->values(),
                'tools' => $tip->tools->map(fn (TipTool $tool) => $tool->name)->values(),
                'interval_distance_km' => $tip->interval_distance_km,
                'interval_time_months' => $tip->interval_time_months,
                'important_notes' => $tip->important_notes,
                'status' => $tip->status,
            ],
        ]);
    }

    /**
     * Update template
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tip = Tip::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'description' => ['required', 'string', 'min:20', 'max:500'],
            'vehicle_brand' => ['required', 'string', 'max:50'],
            'vehicle_model' => ['required', 'string', 'min:2', 'max:100'],
            'vehicle_year' => ['required', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'riding_style' => ['required', 'string', 'max:50'],
            'is_copyable' => ['required', 'boolean'],

            'hashtags' => ['nullable', 'string'],

            'steps' => ['required', 'array', 'min:3'],
            'steps.*' => ['required', 'string', 'max:200'],

            'tools' => ['required', 'array', 'min:1'],
            'tools.*' => ['required', 'string', 'max:200'],

            'interval_distance_km' => ['nullable', 'integer', 'min:100', 'max:50000'],
            'interval_time_months' => ['nullable', 'integer', 'min:1', 'max:60'],
            'important_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $hashtags = null;
        if ($request->filled('hashtags')) {
            $raw = (string) $request->input('hashtags');
            $hashtags = Tip::normalizeHashtags(
                array_map('trim', explode(',', $raw))
            );
        }

        return DB::transaction(function () use ($tip, $validated, $hashtags) {
            $tip->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'vehicle_brand' => $validated['vehicle_brand'],
                'vehicle_model' => $validated['vehicle_model'],
                'vehicle_year' => $validated['vehicle_year'],
                'riding_style' => $validated['riding_style'],
                'interval_distance_km' => $validated['interval_distance_km'] ?? null,
                'interval_time_months' => $validated['interval_time_months'] ?? null,
                'important_notes' => $validated['important_notes'] ?? null,
                'hashtags' => $hashtags,
                'is_copyable' => (bool) $validated['is_copyable'],
                // Ensure remains visible after admin update.
                'status' => 'published',
            ]);

            $tip->tools()->delete();
            foreach ($validated['tools'] as $index => $toolName) {
                TipTool::create([
                    'tip_id' => $tip->id,
                    'name' => $toolName,
                    'is_optional' => false,
                    'order' => $index + 1,
                ]);
            }

            $tip->steps()->delete();
            foreach ($validated['steps'] as $index => $stepTitle) {
                TipStep::create([
                    'tip_id' => $tip->id,
                    'step_number' => $index + 1,
                    'title' => $stepTitle,
                    'description' => $stepTitle,
                ]);
            }

            $this->syncRidingStyleTag($tip);

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diperbarui',
            ]);
        });
    }

    /**
     * Delete template
     */
    public function destroy(int $id)
    {
        $tip = Tip::findOrFail($id);
        $tip->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template berhasil dihapus',
            ]);
        }

        return redirect()
            ->route('admin.templates')
            ->with('success', 'Template berhasil dihapus');
    }

    private function syncRidingStyleTag(Tip $tip): void
    {
        $tag = TipTag::firstOrCreate(
            [
                'name' => $tip->riding_style,
                'type' => 'riding_style',
            ],
            [
                'color' => '#2B7FFF',
                'icon' => 'directions_bike',
            ]
        );

        $existingRidingStyleIds = $tip->tags()
            ->where('type', 'riding_style')
            ->pluck('tip_tags.id')
            ->all();

        if (!empty($existingRidingStyleIds)) {
            $tip->tags()->detach($existingRidingStyleIds);
        }

        $tip->tags()->syncWithoutDetaching([$tag->id]);
    }
}

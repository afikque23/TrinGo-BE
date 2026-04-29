<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    private function normalizeBodyForType(string $type, string $body): string
    {
        if ($type !== 'faq') {
            return $body;
        }

        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return $body;
        }

        if (!is_array($decoded)) {
            return $body;
        }

        $normalized = [];

        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (array_key_exists('question', $item) || array_key_exists('answer', $item)) {
                $question = (string) ($item['question'] ?? '');
                $answer = (string) ($item['answer'] ?? '');

                $normalized[] = [
                    'question' => $question,
                    'answer' => $answer,
                ];
                continue;
            }

            if (array_key_exists('section', $item) || array_key_exists('content', $item)) {
                $question = (string) ($item['section'] ?? '');
                $answer = (string) ($item['content'] ?? '');

                $normalized[] = [
                    'question' => $question,
                    'answer' => $answer,
                ];
            }
        }

        if ($normalized === []) {
            return $body;
        }

        return json_encode($normalized, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get all published contents (Public endpoint).
     * Useful for mobile to fetch all static pages dynamically.
     */
    public function publicIndex(): JsonResponse
    {
        try {
            $contents = Content::published()
                ->orderBy('type')
                ->ordered()
                ->orderBy('created_at', 'desc')
                ->get();

            $grouped = $contents->groupBy('type')->map(
                fn ($items) => ContentResource::collection($items)->values()
            );

            return response()->json([
                'success' => true,
                'message' => 'Contents fetched successfully',
                'data' => $grouped,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contents',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of all contents (Admin only).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $contents = Content::orderBy('type')
                ->orderBy('order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Contents fetched successfully',
                'data' => ContentResource::collection($contents)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created content.
     *
     * @param  \App\Http\Requests\StoreContentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreContentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if (($validated['type'] ?? null) === 'faq') {
                // Enforce single canonical FAQ content.
                Content::query()->where('type', 'faq')->delete();
                $validated['slug'] = 'faq';
            }

            // Auto-generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title']);
            }

            if (isset($validated['body']) && isset($validated['type'])) {
                $validated['body'] = $this->normalizeBodyForType($validated['type'], $validated['body']);
            }

            // Set default status if not provided
            if (!isset($validated['status'])) {
                $validated['status'] = 'draft';
            }

            // Set default order if not provided
            if (!isset($validated['order'])) {
                $validated['order'] = 0;
            }

            $content = Content::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Content created successfully',
                'data' => new ContentResource($content)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified content.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $content = Content::find($id);

            if (!$content) {
                return response()->json([
                    'success' => false,
                    'message' => 'Content not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Content fetched successfully',
                'data' => new ContentResource($content)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified content.
     *
     * @param  \App\Http\Requests\UpdateContentRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateContentRequest $request, string $id): JsonResponse
    {
        try {
            $content = Content::find($id);

            if (!$content) {
                return response()->json([
                    'success' => false,
                    'message' => 'Content not found',
                ], 404);
            }

            $validated = $request->validated();

            $type = $validated['type'] ?? $content->type;

            if ($type === 'faq') {
                // Enforce single canonical FAQ content and stable slug.
                Content::query()
                    ->where('type', 'faq')
                    ->where('id', '!=', $content->id)
                    ->delete();

                $validated['slug'] = 'faq';
            }

            if (isset($validated['body'])) {
                $validated['body'] = $this->normalizeBodyForType($type, $validated['body']);
            }

            $content->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Content updated successfully',
                'data' => new ContentResource($content->fresh())
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified content.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $content = Content::find($id);

            if (!$content) {
                return response()->json([
                    'success' => false,
                    'message' => 'Content not found',
                ], 404);
            }

            $content->delete();

            return response()->json([
                'success' => true,
                'message' => 'Content deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get published contents by type (Public endpoint).
     *
     * @param  string  $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByType(string $type): JsonResponse
    {
        try {
            $validTypes = ['terms', 'privacy', 'guide', 'about', 'faq', 'system_info', 'support'];
            
            if (!in_array($type, $validTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid content type',
                ], 400);
            }

            $contents = Content::published()
                ->ofType($type)
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Contents fetched successfully',
                'data' => ContentResource::collection($contents)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contents',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

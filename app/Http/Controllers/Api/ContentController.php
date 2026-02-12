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

            // Auto-generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title']);
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

            // Auto-generate slug if title is updated but slug is not provided
            if (isset($validated['title']) && !isset($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title']);
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
            $validTypes = ['terms', 'privacy', 'guide', 'about', 'faq', 'system_info'];
            
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

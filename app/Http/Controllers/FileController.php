<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Serve receipt images with proper headers
     */
    public function serveReceipt(string $filename): Response|StreamedResponse
    {
        $path = 'receipts/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        $file = Storage::disk('public')->get($path);
        $fullPath = Storage::disk('public')->path($path);
        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        $fileSize = Storage::disk('public')->size($path);

        return response($file)
            ->header('Content-Type', $mimeType)
            ->header('Content-Length', $fileSize)
            ->header('Cache-Control', 'public, max-age=31536000')
            ->header('Accept-Ranges', 'bytes');
    }

    /**
     * Serve avatar images with proper headers
     */
    public function serveAvatar(string $filename): Response|StreamedResponse
    {
        $path = 'avatars/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        $file = Storage::disk('public')->get($path);
        $fullPath = Storage::disk('public')->path($path);
        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        $fileSize = Storage::disk('public')->size($path);

        return response($file)
            ->header('Content-Type', $mimeType)
            ->header('Content-Length', $fileSize)
            ->header('Cache-Control', 'public, max-age=31536000')
            ->header('Accept-Ranges', 'bytes');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Display maintenance templates
     */
    public function index()
    {
        return view('admin.template_perawatan.template_perawatan');
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_template' => 'required|string|max:255',
                'deskripsi_teknis' => 'nullable|string',
                'langkah' => 'nullable|array',
                'langkah.*' => 'nullable|string',
                'interval_km' => 'required|integer|min:0',
                'interval_waktu' => 'required|string',
                'tingkat_kesulitan' => 'required|string',
                'jenis_motor' => 'required|string',
                'digunakan_ai' => 'required|boolean',
            ]);

            // TODO: Save to database when model is ready
            // Convert langkah array to JSON for storage
            // $validated['langkah_perawatan'] = json_encode(array_filter($request->input('langkah', [])));
            
            return response()->json([
                'success' => true,
                'message' => 'Template berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan template: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get template data for editing
     */
    public function edit($id)
    {
        try {
            // TODO: Fetch template from database
            // Dummy data for now
            $template = [
                'id' => $id,
                'nama_template' => 'Ganti Oli Rutin Harian',
                'deskripsi_teknis' => 'Penggantian oli mesin secara berkala untuk menjaga performa optimal kendaraan.',
                'langkah' => [
                    'Panaskan mesin terlebih dahulu',
                    'Buka baut penutup oli',
                    'Kuras oli lama'
                ],
                'interval_km' => 2000,
                'interval_waktu' => '1 bulan',
                'tingkat_kesulitan' => 'Mudah',
                'jenis_motor' => 'Matic',
                'digunakan_ai' => 1
            ];
            
            return response()->json([
                'success' => true,
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data template'
            ], 404);
        }
    }

    /**
     * Update template
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nama_template' => 'required|string|max:255',
                'deskripsi_teknis' => 'nullable|string',
                'langkah' => 'nullable|array',
                'langkah.*' => 'nullable|string',
                'interval_km' => 'required|integer|min:0',
                'interval_waktu' => 'required|string',
                'tingkat_kesulitan' => 'required|string',
                'jenis_motor' => 'required|string',
                'digunakan_ai' => 'required|boolean',
            ]);

            // TODO: Update database when model is ready
            // Convert langkah array to JSON for storage
            // $validated['langkah_perawatan'] = json_encode(array_filter($request->input('langkah', [])));
            
            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui template: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete template
     */
    public function destroy($id)
    {
        // TODO: Delete from database when model is ready
        
        return redirect()->route('admin.templates')
            ->with('success', 'Template berhasil dihapus');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceTypeController extends Controller
{
    /**
     * Display a listing of service types.
     */
    public function index()
    {
        $serviceTypes = ServiceType::orderBy('name')->get()->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'is_active' => $type->is_active,
                'services_count' => $type->services()->count(),
                'created_at' => $type->created_at->format('d M Y'),
            ];
        });

        return view('admin.service_types.index', compact('serviceTypes'));
    }

    /**
     * Store a newly created service type.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:service_types,name',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Gagal menambahkan jenis service. Periksa kembali data yang diisi.');
        }

        ServiceType::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.service-types.index')
            ->with('success', 'Jenis service berhasil ditambahkan.');
    }

    /**
     * Update the specified service type.
     */
    public function update(Request $request, ServiceType $serviceType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:service_types,name,' . $serviceType->id,
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Gagal memperbarui jenis service. Periksa kembali data yang diisi.');
        }

        $serviceType->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.service-types.index')
            ->with('success', 'Jenis service berhasil diperbarui.');
    }

    /**
     * Remove the specified service type.
     */
    public function destroy(ServiceType $serviceType)
    {
        // Check if service type is being used
        if ($serviceType->services()->exists()) {
            return redirect()->back()
                ->with('error', 'Jenis service tidak dapat dihapus karena masih digunakan oleh ' . $serviceType->services()->count() . ' data service.');
        }

        $serviceType->delete();

        return redirect()->route('admin.service-types.index')
            ->with('success', 'Jenis service berhasil dihapus.');
    }

    /**
     * Toggle active status of service type.
     */
    public function toggleStatus(ServiceType $serviceType)
    {
        $serviceType->update([
            'is_active' => !$serviceType->is_active
        ]);

        $status = $serviceType->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.service-types.index')
            ->with('success', "Jenis service berhasil {$status}.");
    }
}

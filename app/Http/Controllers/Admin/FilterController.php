<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationCategory;
use App\Models\ServiceType;
use App\Models\ReminderOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FilterController extends Controller
{
    /**
     * Display filter management page
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'filters'); // 'filters', 'service-types', or 'reminder-options'
        
        // Sample data for filters
        $filters = [
            [
                'id' => 1,
                'name' => 'Jenis Motor',
                'description' => 'Filter berdasarkan tipe motor (Matic, Sport, dll)',
                'type' => 'Dropdown',
                'used_in' => ['Template'],
                'options_count' => 5,
                'order' => 1,
                'status' => 'active'
            ],
            [
                'id' => 2,
                'name' => 'Interval Service',
                'description' => 'Range jarak tempuh untuk service',
                'type' => 'Range',
                'used_in' => ['Template', 'AI'],
                'options_count' => null,
                'order' => 2,
                'status' => 'active'
            ],
            [
                'id' => 3,
                'name' => 'Kategori Konten',
                'description' => 'Multi-select kategori untuk tips perawatan',
                'type' => 'Multi-select',
                'used_in' => ['Template'],
                'options_count' => 7,
                'order' => 3,
                'status' => 'active'
            ],
            [
                'id' => 4,
                'name' => 'Tampilkan di Dashboard',
                'description' => 'Toggle untuk menampilkan item di dashboard',
                'type' => 'Toggle',
                'used_in' => ['Template', 'AI'],
                'options_count' => null,
                'order' => 4,
                'status' => 'active'
            ],
            [
                'id' => 5,
                'name' => 'Brand Motor',
                'description' => 'Filter berdasarkan brand motor',
                'type' => 'Dropdown',
                'used_in' => ['Template', 'AI'],
                'options_count' => 8,
                'order' => 5,
                'status' => 'active'
            ],
            [
                'id' => 6,
                'name' => 'Status Moderasi',
                'description' => 'Status konten untuk admin moderasi',
                'type' => 'Multi-select',
                'used_in' => ['Template'],
                'options_count' => 4,
                'order' => 6,
                'status' => 'inactive'
            ],
        ];

        // Get service types with services count
        // Check if migration has been run (service_type_id column exists)
        $serviceTypes = ServiceType::query();
        
        try {
            $serviceTypes = $serviceTypes->withCount('services');
        } catch (\Exception $e) {
            // Column doesn't exist yet, skip counting
        }
        
        $serviceTypes = $serviceTypes->orderBy('created_at', 'desc')->get();

        // Get reminder options grouped by unit
        $reminderOptions = ReminderOption::orderBy('unit', 'asc')
            ->orderBy('value', 'asc')
            ->get();

        // Get notification categories
        $notificationCategories = NotificationCategory::orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.manajemen_filter.manajemen_filter', compact('filters', 'serviceTypes', 'reminderOptions', 'notificationCategories', 'activeTab'));
    }

    /**
     * Store a new filter
     */
    public function store(Request $request)
    {
        // Implementation for creating new filter
        return redirect()->route('admin.filters')->with('success', 'Filter berhasil ditambahkan');
    }

    /**
     * Update filter
     */
    public function update(Request $request, $id)
    {
        // Implementation for updating filter
        return redirect()->route('admin.filters')->with('success', 'Filter berhasil diperbarui');
    }

    /**
     * Toggle filter status
     */
    public function toggleStatus($id)
    {
        // Find filter and toggle its status
        $filters = session('filters', []);
        foreach ($filters as &$filter) {
            if ($filter['id'] == $id) {
                $filter['status'] = $filter['status'] === 'active' ? 'inactive' : 'active';
                break;
            }
        }
        session(['filters' => $filters]);
        
        return redirect()->route('admin.filters')->with('success', 'Status filter berhasil diubah');
    }

    /**
     * Delete filter
     */
    public function destroy($id)
    {
        // Implementation for deleting filter
        return redirect()->route('admin.filters')->with('success', 'Filter berhasil dihapus');
    }

    // ==================== SERVICE TYPES METHODS ====================

    /**
     * Store a new service type
     */
    public function storeServiceType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:service_types,name',
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Nama jenis service wajib diisi.',
            'name.unique' => 'Nama jenis service sudah digunakan.',
            'name.max' => 'Nama jenis service maksimal 100 karakter.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Gagal menambahkan jenis service. Periksa kembali input Anda.');
        }

        ServiceType::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.filters', ['tab' => 'service-types'])
            ->with('success', 'Jenis service berhasil ditambahkan.');
    }

    /**
     * Update service type
     */
    public function updateServiceType(Request $request, ServiceType $serviceType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:service_types,name,' . $serviceType->id,
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Nama jenis service wajib diisi.',
            'name.unique' => 'Nama jenis service sudah digunakan.',
            'name.max' => 'Nama jenis service maksimal 100 karakter.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Gagal memperbarui jenis service. Periksa kembali input Anda.');
        }

        $serviceType->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.filters', ['tab' => 'service-types'])
            ->with('success', 'Jenis service berhasil diperbarui.');
    }

    /**
     * Delete service type
     */
    public function destroyServiceType(ServiceType $serviceType)
    {
        // Check if service type is being used
        if ($serviceType->services()->exists()) {
            return redirect()->route('admin.filters', ['tab' => 'service-types'])
                ->with('error', 'Jenis service tidak dapat dihapus karena masih digunakan oleh ' . $serviceType->services()->count() . ' data service.');
        }

        $serviceType->delete();

        return redirect()->route('admin.filters', ['tab' => 'service-types'])
            ->with('success', 'Jenis service berhasil dihapus.');
    }

    /**
     * Toggle service type status
     */
    public function toggleServiceTypeStatus(ServiceType $serviceType)
    {
        $serviceType->update([
            'is_active' => !$serviceType->is_active
        ]);

        return redirect()->route('admin.filters', ['tab' => 'service-types'])
            ->with('success', 'Status jenis service berhasil diubah.');
    }

    // ===== REMINDER OPTIONS CRUD =====

    /**
     * Store a new reminder option
     */
    public function storeReminderOption(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:100',
            'value' => 'required|integer|min:1|max:999999',
            'unit' => 'required|in:km,years,months,weeks,days',
            'is_active' => 'boolean'
        ], [
            'label.required' => 'Label wajib diisi.',
            'value.required' => 'Nilai wajib diisi.',
            'value.integer' => 'Nilai harus berupa angka.',
            'value.min' => 'Nilai minimal 1.',
            'unit.required' => 'Unit wajib dipilih.',
            'unit.in' => 'Unit tidak valid.'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.filters', ['tab' => 'reminder-options'])
                ->withErrors($validator)
                ->withInput();
        }

        // Check duplicate value + unit
        $exists = ReminderOption::where('value', $request->value)
            ->where('unit', $request->unit)
            ->exists();

        if ($exists) {
            return redirect()->route('admin.filters', ['tab' => 'reminder-options'])
                ->with('error', 'Kombinasi nilai dan unit sudah ada.')
                ->withInput();
        }

        ReminderOption::create([
            'label' => $request->label,
            'value' => $request->value,
            'unit' => $request->unit,
            'is_active' => $request->has('is_active') ? true : false
        ]);

        return redirect()->route('admin.filters', ['tab' => 'reminder-options'])
            ->with('success', 'Opsi pengingat berhasil ditambahkan.');
    }

    /**
     * Update reminder option
     */
    public function updateReminderOption(Request $request, ReminderOption $reminderOption)
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:100',
            'value' => 'required|integer|min:1|max:999999',
            'unit' => 'required|in:km,years,months,weeks,days',
            'is_active' => 'boolean'
        ], [
            'label.required' => 'Label wajib diisi.',
            'value.required' => 'Nilai wajib diisi.',
            'value.integer' => 'Nilai harus berupa angka.',
            'value.min' => 'Nilai minimal 1.',
            'unit.required' => 'Unit wajib dipilih.',
            'unit.in' => 'Unit tidak valid.'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.filters', ['tab' => 'reminder-options'])
                ->withErrors($validator)
                ->withInput();
        }

        // Check duplicate value + unit (exclude current)
        $exists = ReminderOption::where('value', $request->value)
            ->where('unit', $request->unit)
            ->where('id', '!=', $reminderOption->id)
            ->exists();

        if ($exists) {
            return redirect()->route('admin.filters', ['tab' => 'reminder-options'])
                ->with('error', 'Kombinasi nilai dan unit sudah ada.')
                ->withInput();
        }

        $reminderOption->update([
            'label' => $request->label,
            'value' => $request->value,
            'unit' => $request->unit,
            'is_active' => $request->has('is_active') ? true : false
        ]);

        return redirect()->route('admin.filters', ['tab' => 'reminder-options'])
            ->with('success', 'Opsi pengingat berhasil diperbarui.');
    }

    /**
     * Delete reminder option
     */
    public function destroyReminderOption(ReminderOption $reminderOption)
    {
        // TODO: Check if reminder option is being used
        // if ($reminderOption->reminders()->exists()) {
        //     return redirect()->route('admin.filters', ['tab' => 'reminder-options'])
        //         ->with('error', 'Opsi pengingat tidak dapat dihapus karena masih digunakan.');
        // }

        $reminderOption->delete();

        return redirect()->route('admin.filters', ['tab' => 'reminder-options'])
            ->with('success', 'Opsi pengingat berhasil dihapus.');
    }

    /**
     * Toggle reminder option status
     */
    public function toggleReminderOptionStatus(ReminderOption $reminderOption)
    {
        $reminderOption->update([
            'is_active' => !$reminderOption->is_active
        ]);

        return redirect()->route('admin.filters', ['tab' => 'reminder-options'])
            ->with('success', 'Status opsi pengingat berhasil diubah.');
    }

    // ========================================
    // NOTIFICATION CATEGORY MANAGEMENT
    // ========================================

    /**
     * Store new notification category
     */
    public function storeNotificationCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:notification_categories,key',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.filters', ['tab' => 'notification-categories'])
                ->withErrors($validator)
                ->withInput();
        }

        NotificationCategory::create([
            'name' => $request->name,
            'key' => $request->key,
            'icon' => $request->icon,
            'color' => $request->color,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active') ? true : false
        ]);

        return redirect()->route('admin.filters', ['tab' => 'notification-categories'])
            ->with('success', 'Kategori notifikasi berhasil ditambahkan.');
    }

    /**
     * Update notification category
     */
    public function updateNotificationCategory(Request $request, NotificationCategory $notificationCategory)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:notification_categories,key,' . $notificationCategory->id,
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.filters', ['tab' => 'notification-categories'])
                ->withErrors($validator)
                ->withInput();
        }

        $notificationCategory->update([
            'name' => $request->name,
            'key' => $request->key,
            'icon' => $request->icon,
            'color' => $request->color,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active') ? true : false
        ]);

        return redirect()->route('admin.filters', ['tab' => 'notification-categories'])
            ->with('success', 'Kategori notifikasi berhasil diperbarui.');
    }

    /**
     * Delete notification category
     */
    public function destroyNotificationCategory(NotificationCategory $notificationCategory)
    {
        // Check if category is being used
        if ($notificationCategory->notificationTemplates()->exists() || $notificationCategory->notifications()->exists()) {
            return redirect()->route('admin.filters', ['tab' => 'notification-categories'])
                ->with('error', 'Kategori notifikasi tidak dapat dihapus karena masih digunakan.');
        }

        $notificationCategory->delete();

        return redirect()->route('admin.filters', ['tab' => 'notification-categories'])
            ->with('success', 'Kategori notifikasi berhasil dihapus.');
    }

    /**
     * Toggle notification category status
     */
    public function toggleNotificationCategoryStatus(NotificationCategory $notificationCategory)
    {
        $notificationCategory->update([
            'is_active' => !$notificationCategory->is_active
        ]);

        return redirect()->route('admin.filters', ['tab' => 'notification-categories'])
            ->with('success', 'Status kategori notifikasi berhasil diubah.');
    }
}

<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Vehicle;
use App\Models\NotificationTemplate;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Artisan;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Send Test Notification to Flutter App ===\n\n";

$deviceId = 'd40436f2-ec64-44f2-b8be-d0f65754607c';
$vehicleId = 12; // motor A

echo "📱 Device ID: $deviceId\n";
echo "🏍️  Vehicle ID: $vehicleId\n\n";

// Ambil kendaraan
$vehicle = Vehicle::find($vehicleId);
if (!$vehicle) {
    echo "❌ Vehicle tidak ditemukan!\n";
    exit(1);
}

echo "✅ Vehicle: {$vehicle->name}\n";
echo "   Odometer: {$vehicle->odometer_km} km\n\n";

// Cari template trip
$template = NotificationTemplate::where('category_key', 'trip')
    ->where('is_active', true)
    ->first();

if (!$template) {
    echo "⚠️  Template kategori 'trip' tidak ditemukan. Membuat template baru...\n";
    
    // Ambil kategori trip
    $category = \App\Models\NotificationCategory::where('key', 'trip')->first();
    if (!$category) {
        echo "❌ Kategori 'trip' tidak ditemukan! Run seeder dulu.\n";
        exit(1);
    }
    
    $template = NotificationTemplate::create([
        'name' => 'Test Trip Notification',
        'category_key' => 'trip',
        'trigger_type' => 'manual',
        'message_template' => '🔔 Halo! Ini test notifikasi untuk {vehicle_name}. Odometer: {current_km} km.',
        'priority' => 'normal',
        'channel' => 'push',
        'is_active' => true,
    ]);
    
    echo "✅ Template baru dibuat (ID: {$template->id})\n\n";
} else {
    echo "✅ Template: {$template->name}\n";
    echo "   Message: " . substr($template->message_template, 0, 80) . "...\n\n";
}

// Kirim notifikasi
echo "📤 Mengirim notifikasi...\n";

$notificationService = app(NotificationService::class);

try {
    $notification = $notificationService->sendFromTemplate(
        $template,
        [
            'vehicle_name' => $vehicle->name ?: 'Motor Anda',
            'current_km' => number_format($vehicle->odometer_km ?? 0, 0, ',', '.'),
            'distance' => '15.5',
            'duration' => '25 menit',
            'avg_speed' => '37.2',
        ],
        null, // user
        $deviceId,
        $vehicle
    );
    
    if ($notification) {
        echo "\n✅ NOTIFIKASI BERHASIL DIKIRIM!\n";
        echo "   ID: {$notification->id}\n";
        echo "   Title: {$notification->title}\n";
        echo "   Message: {$notification->message}\n";
        echo "   Category: {$notification->category_key}\n";
        echo "   Device ID: {$notification->device_id}\n";
        echo "   Push Sent: " . ($notification->push_sent ? 'YES' : 'NO') . "\n";
        echo "   Push Success: " . ($notification->push_success ? 'YES' : 'NO') . "\n\n";
        
        echo "🎉 SELESAI! Refresh Flutter app untuk melihat notifikasi.\n";
        echo "   Atau cek push notification di device.\n\n";
        
        echo "🔍 Verifikasi:\n";
        echo "   mysql> SELECT id, title, message FROM notifications WHERE device_id = '$deviceId' ORDER BY created_at DESC LIMIT 1;\n";
        
    } else {
        echo "\n❌ GAGAL MENGIRIM NOTIFIKASI!\n";
        echo "   Kemungkinan template tidak aktif atau user preference menonaktifkan notifikasi.\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

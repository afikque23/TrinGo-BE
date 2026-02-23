# ⚠️ DEPRECATED - Guest Mode Documentation

## 🚨 Important Notice

**Guest Mode has been DEPRECATED and REMOVED as of February 20, 2026.**

This system has been replaced with **Persistent Login** for better security and user experience.

### Why Deprecated?

1. ✅ **Better UX**: Users don't need to login repeatedly (like Shopee, Tokopedia)
2. ✅ **Cross-Device Sync**: Data automatically syncs across devices
3. ✅ **Better Security**: All data tied to authenticated users only
4. ✅ **Simplified Architecture**: No complex device_id ownership logic

### Migration Required

All applications using Guest Mode must migrate to **Persistent Login System**.

📖 **See new documentation**: [PERSISTENT_LOGIN_GUIDE.md](./PERSISTENT_LOGIN_GUIDE.md)

---

## Old Documentation (For Reference Only)

# ⚠️ DEPRECATED - Guest Mode Documentation

## 🚨 IMPORTANT NOTICE

**Guest Mode has been DEPRECATED and removed from the system as of February 20, 2026.**

The system now uses **Persistent Login** instead of Guest Mode. All users are required to have an account, but they only need to login once (session persists for 90 days).

## 📖 Migration to Persistent Login

Please refer to **PERSISTENT_LOGIN_GUIDE.md** for the new implementation.

### Key Changes:

1. **No Guest Mode**: Users must register and login
2. **Persistent Login**: Refresh token valid for 90 days
3. **Cross-Device Sync**: Data syncs across all devices (tied to user_id)
4. **user_id Required**: All data now requires user_id (NOT NULL)
5. **device_id**: Now only used for tracking, not for ownership

---

## ❌ Old Guest Mode Implementation (DEPRECATED)

The following documentation is kept for reference only. **DO NOT USE THIS IMPLEMENTATION.**

# 🔥 Guest Mode Implementation - Motorcycle Management API (DEPRECATED)

## 📋 Overview

Implementasi Guest Mode memungkinkan pengguna menggunakan aplikasi **tanpa login** menggunakan `device_id` sebagai identitas. Setelah login, semua data yang dibuat saat mode guest akan otomatis tersinkronisasi ke akun user.

## 🎯 Tujuan Sistem

- ✅ Aplikasi dapat digunakan tanpa login
- ✅ Login hanya digunakan untuk identitas dan sinkronisasi data
- ✅ Endpoint public (mobile app) tidak tergantung auth
- ✅ Endpoint admin tetap menggunakan middleware auth
- ✅ Keamanan data tetap terjaga dengan validasi device_id

## 🏗️ Arsitektur

### 1. Database Schema

Tabel yang mendukung Guest Mode memiliki kolom:

- `user_id` (nullable) - untuk user yang sudah login
- `device_id` (nullable) - untuk guest user
- **Constraint**: Minimal salah satu (user_id atau device_id) harus ada

#### Tabel yang Dimodifikasi:

- `vehicles`
- `notifications`
- `trips`
- `fuel_logs`

### 2. Middleware

**DeviceIdentification Middleware** (`app/Http/Middleware/DeviceIdentification.php`)

- Memvalidasi keberadaan device_id pada request public
- Device ID bisa dari header `X-Device-ID` atau body request
- Jika user sudah login, tidak perlu device_id

### 3. Routes Structure

```php
// ✅ PUBLIC - No Auth Required
Route::prefix('auth')->group(...);
Route::prefix('public')->group(...); // Content only

// ✅ PUBLIC + Device ID Required (Guest Mode)
Route::prefix('public')->middleware(['device.id'])->group(function () {
    // Vehicles, Services, Schedules, Notifications, dll
});

// 🔐 PROTECTED - Auth Required (User)
Route::middleware('auth:sanctum,web')->group(...);

// 🔐 ADMIN - Auth + Admin Role
Route::prefix('admin')->middleware(['auth:sanctum,web', 'admin'])->group(...);
```

## 📱 Cara Penggunaan

### A. Sebagai Guest (Tanpa Login)

#### 1. Generate Device ID di Flutter

```dart
import 'package:device_info_plus/device_info_plus.dart';
import 'package:uuid/uuid.dart';

class DeviceService {
  static Future<String> getDeviceId() async {
    final prefs = await SharedPreferences.getInstance();

    // Cek apakah sudah ada device_id tersimpan
    String? deviceId = prefs.getString('device_id');

    if (deviceId == null) {
      // Generate UUID baru untuk device
      deviceId = const Uuid().v4();
      await prefs.setString('device_id', deviceId);
    }

    return deviceId;
  }
}
```

#### 2. Gunakan Device ID pada Setiap Request

**Cara 1: Menggunakan Header** (Recommended)

```dart
final response = await http.get(
  Uri.parse('http://localhost/api/v1/motorcycle/public/vehicles'),
  headers: {
    'Accept': 'application/json',
    'X-Device-ID': deviceId,
  },
);
```

**Cara 2: Menggunakan Body Request**

```dart
final response = await http.post(
  Uri.parse('http://localhost/api/v1/motorcycle/public/vehicles'),
  headers: {'Accept': 'application/json'},
  body: jsonEncode({
    'device_id': deviceId,
    'title': 'Honda Beat 2020',
    // ... field lainnya
  }),
);
```

### B. Login dan Sinkronisasi Data

#### 1. Login dengan Device ID

```dart
final response = await http.post(
  Uri.parse('http://localhost/api/v1/motorcycle/auth/login'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'email': 'user@example.com',
    'password': 'password123',
    'device_id': deviceId, // Kirim device_id saat login
    'device_name': 'My Android Phone', // Optional
  }),
);

// Response akan mengandung sync_info
{
  "success": true,
  "data": {
    "user": {...},
    "access_token": "...",
    "refresh_token": "...",
    "sync_info": {
      "synced": true,
      "total_synced": 15,
      "details": {
        "vehicles": 3,
        "notifications": 5,
        "trips": 7,
        "fuel_logs": 0
      }
    }
  }
}
```

#### 2. Setelah Login - Gunakan Access Token

```dart
final response = await http.get(
  Uri.parse('http://localhost/api/v1/motorcycle/public/vehicles'),
  headers: {
    'Accept': 'application/json',
    'Authorization': 'Bearer $accessToken',
    // device_id tidak diperlukan lagi setelah login
  },
);
```

## 🔒 Keamanan

### 1. Validasi Device ID

- Device ID minimal 10 karakter
- Disarankan menggunakan UUID v4
- Device A tidak dapat akses data Device B

### 2. Validasi Ownership

Setiap controller memvalidasi:

```php
// Cek apakah data milik user atau device yang melakukan request
if (!$this->canAccessModel($model, $request)) {
    return $this->errorResponse('Unauthorized', 403);
}
```

### 3. Data Isolation

- User A tidak dapat akses data User B
- Device A tidak dapat akses data Device B
- Data hanya dapat diakses oleh owner-nya (user_id atau device_id)

## 📊 Sinkronisasi Data

### Proses Automatic Sync

Sinkronisasi terjadi otomatis saat:

1. **Login** - Method `AuthController@login`
2. **Email Verification** - Method `AuthController@verifyEmail`

### Fungsi Sync

```php
protected function syncDeviceData(User $user, ?string $deviceId): array
{
    // 1. Cari semua data dengan device_id yang sama
    // 2. Update semua record: device_id → user_id
    // 3. Return summary
}
```

### Data yang Disinkronkan:

- ✅ Vehicles
- ✅ Notifications
- ✅ Trips
- ✅ Fuel Logs
- ⚠️ Services & Schedules (melalui vehicles)

## 🧪 Testing dengan Postman

### Import Collection

Import file: `Motorcycle_Management_API.postman_collection.json`

Collection ini sudah include:

- 🔥 **Public Endpoints (Guest Mode)** - Semua endpoint public dengan device_id
- 🔐 **Authentication dengan Device Sync** - Login dengan sync data
- 🏍️ **Protected Endpoints** - Endpoint yang butuh auth

### Setup Environment

Buat environment dengan variable:

```
base_url: http://localhost/api/v1/motorcycle
device_id: guest-device-12345678-abcd-1234-abcd-123456789012
access_token: (will be set after login)
```

### Testing Flow

1. **Create Vehicle as Guest**
    - Endpoint: `POST /public/vehicles`
    - Header: `X-Device-ID: {device_id}`

2. **Get Vehicles as Guest**
    - Endpoint: `GET /public/vehicles`
    - Header: `X-Device-ID: {device_id}`

3. **Register & Verify**
    - `POST /auth/register`
    - `POST /auth/verify-email` (with device_id)

4. **Login with Device Sync**
    - `POST /auth/login` (with device_id)
    - Check `sync_info` di response

5. **Access as Authenticated User**
    - Gunakan `Authorization: Bearer {token}`
    - Data yang dibuat saat guest sudah ter-sync

## 🚀 Migration & Setup

### 1. Run Migrations

```bash
php artisan migrate
```

Migrations yang dibuat:

- `2026_02_13_000001_add_device_id_to_vehicles_table.php`
- `2026_02_13_000002_add_device_id_to_notifications_table.php`
- `2026_02_13_000003_add_device_id_to_trips_table.php`
- `2026_02_13_000004_add_device_id_to_fuel_logs_table.php`

### 2. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## 📝 Code Structure

### Files Created/Modified

#### New Files:

- `app/Http/Middleware/DeviceIdentification.php` - Middleware validasi device_id
- `app/Traits/HasOwnerIdentification.php` - Trait untuk ownership logic
- `database/migrations/2026_02_13_*.php` - Migrations untuk device_id

#### Modified Files:

- `routes/api.php` - Restructured dengan public/admin routes
- `app/Http/Controllers/Api/AuthController.php` - Added syncDeviceData()
- `app/Http/Controllers/VehicleController.php` - Support device_id
- `app/Http/Controllers/ServiceController.php` - Support device_id
- `app/Http/Controllers/Api/ServiceScheduleController.php` - Support device_id
- `app/Http/Controllers/Api/NotificationController.php` - Support device_id
- `app/Http/Controllers/ServiceHistoryController.php` - Support device_id
- `bootstrap/app.php` - Register middleware alias

## 🎓 Best Practices

### 1. Device ID Generation

✅ **DO:**

- Gunakan UUID v4
- Simpan di secure storage (SharedPreferences, Keychain)
- Generate sekali dan reuse

❌ **DON'T:**

- Gunakan device serial number (privacy issue)
- Generate baru setiap app launch
- Hardcode di source code

### 2. Error Handling

```dart
try {
  final response = await makeRequest();
  if (response.statusCode == 400) {
    // Device ID required or invalid
    final deviceId = await DeviceService.getDeviceId();
    // Retry with device_id
  }
} catch (e) {
  // Handle error
}
```

### 3. State Management

- Simpan `isLoggedIn` status
- Simpan `device_id` untuk guest mode
- Clear `device_id` dari header setelah login (optional)

## 🔄 Migration dari Data Lama

Jika ada data existing dengan `user_id` yang sudah ada:

```sql
-- Data lama tidak perlu migrasi
-- Kolom device_id akan NULL untuk data existing
-- System tetap akan recognize data berdasarkan user_id
```

## 📞 Support & Troubleshooting

### Error: "Device ID is required"

**Solution:** Pastikan header `X-Device-ID` atau field `device_id` di-set

### Error: "Invalid Device ID format"

**Solution:** Device ID minimal 10 karakter, gunakan UUID v4

### Data tidak ter-sync setelah login

**Solution:**

1. Pastikan `device_id` dikirim saat login/verify
2. Check response `sync_info`
3. Check logs: `storage/logs/laravel.log`

### Device tidak bisa akses data setelah login

**Solution:**

- Setelah login, gunakan `Authorization: Bearer {token}`
- Tidak perlu `X-Device-ID` lagi

## 📜 License

Untuk keperluan Tugas Akhir - Motorcycle Management System

---

**Created:** February 13, 2026
**Version:** 1.0.0
**Author:** Motorcycle Management API Team

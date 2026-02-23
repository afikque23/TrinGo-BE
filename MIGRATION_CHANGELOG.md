# 🔄 Migration Changelog - Guest Mode to Persistent Login

## Date: February 20, 2026

## 🎯 Summary

Sistem telah diubah dari **Guest Mode** (device_id-based) menjadi **Persistent Login** (user_id-based) untuk meningkatkan user experience dan keamanan.

## ✅ Changes Made

### 1. Authentication & Tokens

- ✅ Refresh token diperpanjang: 30 hari → **90 hari** (persistent login)
- ✅ Access token tetap 30 menit (security best practice)
- ✅ Hapus `syncDeviceData()` dari AuthController
- ✅ Response login/verify tidak ada `sync_info` lagi

### 2. Routes & Middleware

- ✅ Semua endpoint user data sekarang **butuh authentication** (auth:sanctum,web)
- ✅ Middleware `device.id` di-deprecate (tidak digunakan lagi)
- ✅ Public endpoints hanya untuk content (terms, privacy policy, dll)

### 3. Database

- ✅ Migration baru: `2026_02_20_074312_remove_guest_mode_make_user_id_required.php`
- ✅ `user_id` sekarang **NOT NULL** (required) untuk:
    - `vehicles`
    - `notifications`
    - `trips`
    - `fuel_logs`
- ✅ Data guest (WHERE user_id IS NULL) dihapus otomatis saat migrate
- ✅ Constraint `check_vehicle_owner` dan `check_notification_owner` dihapus
- ℹ️ Kolom `device_id` tetap ada untuk **tracking only** (bukan ownership)

### 4. Code Changes

#### Files Modified:

```
app/Http/Controllers/Api/AuthController.php          - Hapus sync logic, perpanjang token 90 hari
app/Http/Controllers/VehicleController.php           - Update comments, hapus guest mode references
app/Http/Controllers/TripController.php              - Update comments, hapus device_id dari notifikasi
app/Http/Controllers/ServiceHistoryController.php    - Update comments, hapus guest mode references
app/Http/Controllers/ServiceController.php           - Update comments, hapus guest mode references
app/Http/Controllers/Api/NotificationController.php  - Update comments, hapus guest mode references
app/Http/Controllers/Api/DeviceTokenController.php   - Simplify logic, hanya user_id
app/Traits/HasOwnerIdentification.php                - Hanya user_id, throw exception jika tidak auth
routes/api.php                                       - Semua endpoint butuh auth:sanctum,web
database/migrations/2026_02_20_*_remove_guest_mode.php - New migration
```

#### Files Created:

```
PERSISTENT_LOGIN_GUIDE.md                            - Complete persistent login documentation
MIGRATION_CHANGELOG.md                               - This file
POSTMAN_UPDATE_GUIDE.md                              - Guide untuk update Postman collection
database/migrations/2026_02_20_074312_remove_guest_mode_make_user_id_required.php
```

#### Files Deprecated:

```
GUEST_MODE_DOCUMENTATION.md                          - Marked as deprecated
app/Http/Middleware/DeviceIdentification.php         - No longer used
```

### 5. Models

- ✅ `device_id` tetap di fillable tapi hanya untuk tracking
- ✅ Semua query ownership sekarang hanya pakai `user_id`
- ✅ Trait `HasOwnerIdentification` throw exception jika user tidak authenticated

## 🚀 How to Apply

### Backend (Laravel)

```bash
# Pull latest changes
git pull origin dev-arya

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Run migration (CAUTION: Will delete guest data!)
php artisan migrate

# Check migration status
php artisan migrate:status
```

### Frontend (Flutter)

**Required Changes:**

1. **Install Secure Storage**

    ```yaml
    # pubspec.yaml
    dependencies:
        flutter_secure_storage: ^9.0.0
    ```

2. **Implement Auth Storage**
    - Create `lib/services/auth_storage.dart`
    - See: `PERSISTENT_LOGIN_GUIDE.md` section "Setup Secure Storage"

3. **Implement Auto-Refresh HTTP Client**
    - Create `lib/services/api_client.dart`
    - See: `PERSISTENT_LOGIN_GUIDE.md` section "HTTP Client dengan Auto-Refresh"

4. **Update App Initialization**
    - Check login status on app launch
    - Auto-navigate ke home jika sudah login
    - See: `PERSISTENT_LOGIN_GUIDE.md` section "App Initialization"

5. **Remove Device ID Logic**
    - Hapus semua kode yang kirim `X-Device-ID` header untuk ownership
    - Device ID hanya optional untuk tracking (device_id, device_name)

## ⚠️ Breaking Changes

### API Changes

#### Removed Endpoints:

- ❌ All `/public/vehicles`, `/public/services`, etc → Now require auth

#### Changed Behavior:

- ❌ Guest mode tidak bisa create data
- ✅ Harus login untuk create/view data
- ✅ Data otomatis sync antar device (via user_id)

### Response Changes:

**Login / Verify Email:**

```json
// BEFORE
{
  "data": {
    "access_token": "...",
    "refresh_token": "...",
    "expires_in": 1800,
    "sync_info": {                    // ❌ REMOVED
      "synced": true,
      "total_synced": 5,
      "details": {...}
    }
  }
}

// AFTER
{
  "data": {
    "access_token": "...",
    "refresh_token": "...",
    "expires_in": 1800               // ✅ Cleaner response
  }
}
```

**Refresh Token Duration:**

```
BEFORE: 30 days
AFTER:  90 days  ✅ Persistent login
```

## 📋 Checklist untuk Developer

### Backend Developer

- [x] Clear cache (config, route, app)
- [x] Update all controllers (remove guest mode references)
- [x] Test login flow
- [x] Test API endpoints (semua butuh auth)
- [x] Create Postman update guide
- [x] Test API endpoints (semua butuh auth)
- [x] Update Postman collection (remove guest mode examples)

### Frontend Developer

- [ ] Install `flutter_secure_storage`
- [ ] Implement `AuthStorage` service
- [ ] Implement `ApiClient` with auto-refresh
- [ ] Update login flow (save tokens)
- [ ] Update app initialization (check login status)
- [ ] Remove device_id ownership logic
- [ ] Test cross-device data sync
- [ ] Handle session expired gracefully

### Testing

- [ ] Test login → save tokens → close app → reopen (should auto-login)
- [ ] Test access token auto-refresh (wait 30 min or mock)
- [ ] Test session expired (after 90 days or revoke token manually)
- [ ] Test logout → clear tokens → show login screen
- [ ] Test data sync antar device (login di 2 device berbeda)

## 🔍 Verification

### Check User is Always Authenticated

```bash
# Test endpoint tanpa token (should fail)
curl http://localhost:8000/api/v1/motorcycle/vehicles

# Response: 401 Unauthorized ✅

# Test dengan token (should succeed)
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/v1/motorcycle/vehicles

# Response: 200 OK with data ✅
```

### Check Database

```sql
-- All data should have user_id (NOT NULL)
SELECT COUNT(*) FROM vehicles WHERE user_id IS NULL;
-- Should return: 0

SELECT COUNT(*) FROM notifications WHERE user_id IS NULL;
-- Should return: 0

-- Device ID optional (for tracking)
SELECT device_id FROM users WHERE device_id IS NOT NULL;
-- Can have values (tracking last device)
```

## 📞 Support

### Common Issues

**Q: "Guest data hilang setelah migrate?"**
A: Ya, data guest (user_id = NULL) dihapus otomatis. Ini expected behavior karena guest mode dihapus.

**Q: "Aplikasi Flutter crash saat akses endpoint?"**
A: Pastikan sudah implement auth dan kirim Authorization header dengan bearer token.

**Q: "User harus login berulang?"**
A: Implement auto-refresh mechanism di HTTP client. Lihat PERSISTENT_LOGIN_GUIDE.md

**Q: "Data tidak sync antar device?"**
A: Data sekarang otomatis sync karena tied ke user_id. Pastikan login dengan akun yang sama.

---

**Migration Completed:** February 20, 2026
**Version:** 2.0.0
**Breaking Changes:** Yes
**Rollback:** Use `php artisan migrate:rollback` (will restore guest mode constraints)

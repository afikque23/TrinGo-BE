# Postman Collection Update Guide - Persistent Login

## Perubahan dari Guest Mode ke Persistent Login

### 1. Update Collection Description

**Old:**

```
Complete API collection with Refresh Token & Primary Vehicle System
```

**New:**

```
Motorcycle Management API v3.0 - Persistent Login System (90 days refresh token)
All endpoints require authentication. Login once and stay logged in for 90 days.
```

### 2. Update Pre-Request Script

**Hapus** script auto-inject X-Device-ID (lines 20-42 di collection).

**Ganti dengan:**

```javascript
// Auto-refresh token if needed
const accessToken =
    pm.collectionVariables.get("access_token") ||
    pm.environment.get("access_token");

if (accessToken) {
    console.log("✅ Using authenticated mode with Bearer token");
} else {
    console.log("⚠️ No access_token found. Please login first.");
}
```

### 3. Update Collection Variables

**Hapus variable:**

- `device_id` (tidak lagi digunakan untuk ownership)

**Keep variables:**

- `base_url`
- `access_token`
- `refresh_token`
- `trip_id`
- `device_token_id`

### 4. Update Login & Refresh Token Descriptions

#### Login (3. Login)

**Update description:**

```
Login dan mendapat access_token (30 menit) + refresh_token (90 hari untuk persistent login).
User tetap login selama 90 hari kecuali logout atau token expired.
```

#### Refresh Token (4. Refresh Token ⭐)

**Update description:**

```
Refresh access token menggunakan refresh token. Dapat access_token & refresh_token baru.
Refresh token valid selama 90 hari, otomatis diperpanjang setiap refresh.
Call endpoint ini sebelum access token expired (30 menit) untuk seamless experience.
```

### 5. Hapus Section "Guest Mode"

**Hapus seluruh folder:**

- 🔥 Public Endpoints (Guest Mode) - (lines ~4100-4280)
- 🔐 Authentication dengan Device Sync - (lines ~4285-4400)

Kedua section ini tidak lagi relevan karena semua endpoint sekarang require authentication.

### 6. Update Endpoint Descriptions

Untuk semua endpoint di folder berikut, tambahkan note:

**Vehicles, Services, Trips, Notifications, dll:**

```
⚠️ REQUIRES AUTHENTICATION
Set Authorization header: Bearer {{access_token}}
```

### 7. Update Request Body Examples

**Login request - tambahkan optional fields:**

```json
{
    "email": "user@example.com",
    "password": "password123",
    "device_id": "optional-for-tracking",
    "device_name": "My Android Phone"
}
```

**Note:** `device_id` dan `device_name` sekarang OPTIONAL, hanya untuk tracking/analytics.

### 8. Test Flow Baru

1. **Register** → Dapat OTP
2. **Verify Email** → Dapat access_token + refresh_token (90 hari)
3. **Get Vehicles** → Gunakan access_token
4. **Refresh Token** (sebelum 30 menit) → Dapat token baru
5. **Logout** → Revoke semua tokens

### 9. Update Device Tokens Section

**Update description:**

```
FCM Device Token management for push notifications.
REQUIRES AUTHENTICATION - users must login before registering FCM token.
device_id is optional, only used for tracking multiple devices per user.
```

### 10. Common Headers untuk Testing

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{access_token}}
```

**Jangan gunakan lagi:**

- ❌ X-Device-ID (tidak lagi diperlukan untuk ownership)

---

## Quick Update Steps

### Option 1: Manual Update di Postman UI

1. Buka Postman
2. Import collection terbaru
3. Edit Pre-request Scripts (Collection level)
4. Update descriptions untuk endpoints key
5. Delete "Guest Mode" folders
6. Test dengan Login → Get Vehicles

### Option 2: Export & Import Baru

1. Backup collection lama
2. Export collection saat ini
3. Edit file JSON:
    - Find & Replace "Supports both authenticated users and guest mode" → "Requires authentication"
    - Delete guest mode sections
    - Update pre-request script
4. Import kembali ke Postman

### Option 3: Gunakan Collection Updated (Jika tersedia)

Import file: `Motorcycle_Management_API_v3_Persistent_Login.postman_collection.json`

---

## Testing Checklist

- [ ] Register user baru
- [ ] Verify email dengan OTP
- [ ] Login berhasil (dapat tokens)
- [ ] Get vehicles dengan Bearer token
- [ ] Refresh token before expired
- [ ] Create trip/service dengan auth
- [ ] Logout berhasil
- [ ] Access denied tanpa token

---

**Version:** 3.0.0 (Persistent Login)  
**Date:** February 20, 2026  
**Migration from:** v2.0 (Guest Mode)

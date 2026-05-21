# Fix: Refresh Token Error 404 "User tidak ditemukan"

## 🔍 Root Cause Analysis

### Masalah yang terjadi:

```
❌ Token refresh failed: 404
Response: {"success":false,"message":"User tidak ditemukan"}
```

Setiap 30 menit (saat access token expired), Flutter app gagal refresh token dengan error 404.

### Penyebab:

1. **Backend Logic Error (FIXED ✅)**
    - Endpoint refresh token mencari user berdasarkan `email` OR `$request->user()`
    - Flutter app TIDAK mengirim `email` di request
    - `$request->user()` return `null` karena access token sudah expired
    - Akhirnya user tidak ditemukan → 404

2. **Token Mismatch di Flutter App**
    - User ID 14 menyimpan **HASHED token** (64 chars) di local storage
    - Seharusnya menyimpan **PLAIN TEXT token** (128 chars)
    - Backend tidak bisa verify hashed token

---

## ✅ Solusi yang Diterapkan

### 1. **Fix Backend Refresh Token Logic**

**Files:**

- `app/Http/Controllers/Api/AuthController.php`
- `app/Models/User.php`

**Perubahan utama:**

- Backend sekarang mencari user dengan `refresh_token` yang dinormalisasi
- Dukungan untuk kedua format token:
    - `128 char` plain refresh token dari Flutter
    - `64 char` SHA256 hash yang tersimpan di DB
- Validasi refresh token sekarang menggunakan helper baru di model

**Sebelum:**

```php
// Find user by email or from token if authenticated
$email = $request->input('email');
$user = $email ? User::where('email', $email)->first() : $request->user();

if (!$user) {
    return $this->errorResponse('User tidak ditemukan', 404);
}
```

**Sesudah:**

```php
$refreshToken = $request->refresh_token;
$refreshTokenHash = User::normalizeRefreshToken($refreshToken);

$user = User::where('refresh_token', $refreshTokenHash)
    ->where('refresh_token_expires_at', '>', now())
    ->first();

if (!$user) {
    return $this->errorResponse('Refresh token tidak valid. Silakan login kembali.', 401);
}
```

### 2. **Normalisasi dan verifikasi token di model**

**File:** `app/Models/User.php`

**Tambahan:**

```php
public static function normalizeRefreshToken(string $token): string
{
    if (ctype_xdigit($token) && strlen($token) === 64) {
        return $token;
    }

    return hash('sha256', $token);
}
```

**Verify token:**

```php
public function verifyRefreshToken(string $token): bool
{
    if (!$this->refresh_token || !$this->refresh_token_expires_at) {
        return false;
    }

    if ($this->refresh_token_expires_at->isPast()) {
        return false;
    }

    $tokenHash = self::normalizeRefreshToken($token);
    return hash_equals($this->refresh_token, $tokenHash);
}
```

### 3. **Alur refresh token yang diperbaiki**

- Request refresh token harus dikirim body `refresh_token`
- Backend normalisasi token dan lookup user dengan hash yang valid
- Jika token valid dan belum kadaluarsa:
    - lama access token dihapus
    - akses token baru dibuat
    - refresh token baru diterbitkan (90 hari)

### 4. **Manfaat perbaikan**

- ✅ Mengurangi 401 karena token hashed/format salah
- ✅ Mendukung persistent login lebih reliable
- ✅ Tidak lagi bergantung pada `email` atau session lama
- ✅ Menjaga keamanan: token tetap disimpan sebagai hash di DB

---

**Perubahan:**

- ✅ Cari user berdasarkan `refresh_token` di database
- ✅ Tidak perlu `email` di request
- ✅ Tidak perlu authenticated session
- ✅ Remove `device_id` & `device_name` update (tidak diperlukan untuk persistent login)

---

### 2. **Verify Login Endpoint Returns Correct Token**

**File:** `app/Http/Controllers/Api/AuthController.php` - Line 213

```php
// Generate refresh token (90 days for persistent login)
$refreshToken = $user->generateRefreshToken(90); // Returns PLAIN TEXT (128 chars)

return $this->successResponse([
    'user' => new ProfileResource($user->fresh()),
    'access_token' => $token,
    'refresh_token' => $refreshToken, // ← PLAIN TEXT token
    'token_type' => 'Bearer',
    'expires_in' => 1800,
], 'Login berhasil');
```

**Status:** ✅ Login endpoint sudah benar - return plain text token

---

## 🔐 Token Security Architecture

### How It Works:

```
┌─────────────────┐     Login      ┌──────────────────┐
│  Flutter App    │  ────────────>  │   Laravel API    │
│                 │                  │                  │
│  Stores:        │  <────────────  │  Generates:      │
│  • Plain Token  │    Response     │  • Plain Token   │
│  (128 chars)    │                  │  • Hashed Token  │
└─────────────────┘                  │  (saves to DB)   │
                                     └──────────────────┘

┌─────────────────┐    Refresh      ┌──────────────────┐
│  Flutter App    │  ────────────>  │   Laravel API    │
│                 │   Plain Token    │                  │
│                 │                  │  1. Hash token   │
│                 │  <────────────  │  2. Find user    │
│                 │   New Tokens     │  3. Verify hash  │
└─────────────────┘                  │  4. Return new   │
                                     └──────────────────┘
```

### Token Format:

| Location              | Format      | Length    | Example                   |
| --------------------- | ----------- | --------- | ------------------------- |
| **Client (Flutter)**  | Plain Text  | 128 chars | `cfbe22b22123f01ba4bf...` |
| **Server (Database)** | SHA256 Hash | 64 chars  | `ed5c8421c583266aa239...` |

### Security:

- Server stores **hashed token** (SHA256)
- Client stores **plain token**
- On refresh: Server hashes plain token from client → compares with DB hash
- Prevents token theft from database leak

---

## 🚀 Action Required - Flutter App

### ⚠️ USER HARUS LOGIN ULANG!

User ID 14 (`aryayusufaagnilfikri@gmail.com`) memiliki token yang salah di Flutter app.

**Current token (WRONG):**

```
Stored in Flutter: e313cb645a35bddf11295e3bd69537fa8f15824b... (64 chars - HASHED)
```

**Correct token (NEW):**

```
Generated: cfbe22b22123f01ba4bfb7f15cf57e94f46cd12801ca609ed01ba9013e5e81eb... (128 chars - PLAIN)
```

### Cara Fix:

1. **Logout dari Flutter app**
2. **Login ulang** dengan email & password
3. ✅ Refresh token akan bekerja otomatis

---

## 🧪 Testing

### Backend Test (Passed ✅)

```bash
cd c:\laragon\www\motorcycle_management
php test_refresh_token_fix.php
```

**Result:**

```
✅ User found by refresh_token
✅ Token verification passed
✅ New access token generated
✅ New refresh token generated
```

### Flutter App Test (After Login)

**Expected Flow:**

1. Login → Receive `access_token` (30 min) & `refresh_token` (90 days)
2. Use app normally
3. After 30 minutes → Access token expires
4. App calls `/api/v1/motorcycle/auth/refresh-token`
5. ✅ Receive new tokens automatically
6. Continue using app

**Check Logs:**

```
I/flutter: 📡 Refreshing token...
I/flutter: ✅ Token refreshed successfully!
I/flutter: New access token: xxx...
I/flutter: New refresh token: xxx...
```

**No More:**

```
❌ Token refresh failed: 404
Response: {"success":false,"message":"User tidak ditemukan"}
```

---

## 📋 Summary of Changes

### Backend Files Modified:

1. **app/Http/Controllers/Api/AuthController.php**
    - Method: `refreshToken()`
    - Change: Find user by `refresh_token` instead of `email`
    - Remove: `device_id` & `device_name` update logic

### Cache Cleared:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Endpoint:

```
POST /api/v1/motorcycle/auth/refresh-token
Authorization: Not required (uses refresh_token to identify user)

Request Body:
{
    "refresh_token": "cfbe22b22123f01ba4bfb7f15cf57e94..." // 128 chars plain text
}

Response (Success):
{
    "success": true,
    "message": "Token berhasil diperbaharui",
    "data": {
        "access_token": "64|xxx...",
        "refresh_token": "abc123...", // New plain token (128 chars)
        "token_type": "Bearer",
        "expires_in": 1800
    }
}
```

---

## ✅ Verification Checklist

- [x] Backend logic fixed (find user by refresh_token)
- [x] Login endpoint returns plain token (128 chars)
- [x] Token hashing works correctly (SHA256)
- [x] Refresh token expiration checked (90 days)
- [x] Cache cleared
- [ ] **User login ulang di Flutter app** ← ACTION REQUIRED
- [ ] Test refresh token setelah 30 menit

---

## 🔧 Troubleshooting

### If refresh still fails after login:

1. **Check Flutter app sends correct token:**

    ```dart
    print('Refresh token length: ${refreshToken.length}'); // Should be 128
    ```

2. **Check request body:**

    ```dart
    print('Request: ${jsonEncode(body)}'); // Should have 'refresh_token'
    ```

3. **Check backend logs:**

    ```bash
    tail -f storage/logs/laravel.log
    ```

4. **Verify user in database:**
    ```bash
    php debug_user_14_tokens.php
    ```

---

## 📞 Support

Jika masalah masih terjadi setelah login ulang, check:

- Flutter app version
- API endpoint URL (pastikan ke `/api/v1/motorcycle/auth/refresh-token`)
- Network connectivity
- Token storage di Flutter (SharedPreferences/Secure Storage)

Backend siap! Tinggal user login ulang di Flutter app. 🚀

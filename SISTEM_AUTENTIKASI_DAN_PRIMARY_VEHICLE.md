# Dokumentasi Sistem Autentikasi & Primary Vehicle

## Motorcycle Management Backend API

---

## 📋 Daftar Isi

1. [Overview Sistem](#overview-sistem)
2. [Arsitektur Autentikasi](#arsitektur-autentikasi)
3. [Konsep Primary Vehicle](#konsep-primary-vehicle)
4. [API Endpoints](#api-endpoints)
5. [Flow Penggunaan](#flow-penggunaan)
6. [Database Schema](#database-schema)
7. [Best Practices](#best-practices)

---

## 🎯 Overview Sistem

Backend aplikasi manajemen perawatan sepeda motor ini dirancang dengan pendekatan **UX realistis**, di mana pengguna tidak perlu login berulang kali setiap membuka aplikasi, namun sistem tetap menggunakan akun sebagai identitas dan pemilik data.

### Fitur Utama:

-   ✅ **Login Sekali** - Pengguna hanya perlu login di awal
-   ✅ **Auto-Refresh Token** - Access token diperbaharui otomatis tanpa mengganggu user
-   ✅ **Long Session** - Refresh token berlaku hingga 30 hari
-   ✅ **Primary Vehicle** - Motor utama sebagai konteks default aplikasi
-   ✅ **Multi-Device Support** - Tracking perangkat untuk keamanan
-   ✅ **Seamless Experience** - Langsung ke dashboard tanpa hambatan

---

## 🔐 Arsitektur Autentikasi

### Token System

Backend menggunakan **Dual Token Strategy**:

#### 1. Access Token

-   **Durasi**: 30 menit
-   **Penggunaan**: Setiap request API
-   **Format**: Bearer Token (Laravel Sanctum)
-   **Keamanan**: Short-lived untuk meminimalkan risiko

#### 2. Refresh Token

-   **Durasi**: 30 hari (customizable)
-   **Penggunaan**: Refresh access token
-   **Format**: Hash SHA-256 token
-   **Storage**: Database (kolom `refresh_token` di tabel `users`)

### Alur Token

```
Login/Register
    ↓
Generate Access Token (30 min) + Refresh Token (30 days)
    ↓
Frontend menyimpan kedua token
    ↓
Setiap request gunakan Access Token
    ↓
Jika Access Token expired → Auto-call /refresh-token
    ↓
Generate Access Token baru + Refresh Token baru
    ↓
Update token di frontend
    ↓
Continue seamlessly
```

### Kapan User Perlu Login Ulang?

User **HANYA** perlu login ulang jika:

-   ✗ Refresh token expired (30 hari tidak buka app)
-   ✗ Logout manual
-   ✗ Ganti perangkat (opsional, tergantung device tracking)
-   ✗ Reset password
-   ✗ Revoke session oleh admin

---

## 🏍️ Konsep Primary Vehicle

### Apa itu Primary Vehicle?

**Primary Vehicle** (Motor Utama) adalah motor yang ditetapkan sebagai konteks default aplikasi. Setiap user bisa memiliki banyak motor, tapi **hanya satu** yang menjadi motor utama.

### Keuntungan Primary Vehicle:

1. **Seamless UX**: Dashboard, tracking, reminder otomatis menggunakan motor utama
2. **Simplified API**: Frontend tidak perlu kirim `vehicle_id` di setiap request
3. **Smart Default**: Motor pertama user otomatis jadi motor utama
4. **Easy Switch**: User bisa ganti motor utama kapan saja

### Auto-Set Logic:

```php
// Saat user menambah motor pertama kali
if ($existingVehicleCount === 0) {
    $vehicle->is_primary = true;
}
```

### Layar Pilih Motor

Layar ini **TIDAK DIHAPUS**, namun hanya muncul pada kondisi:

1. ✅ Pertama kali pakai app (belum ada primary vehicle)
2. ✅ User klik "Ganti Motor" secara sadar
3. ✅ User menambah motor baru dan ingin set sebagai primary

---

## 🚀 API Endpoints

### Authentication Endpoints

#### 1. Register

```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "081234567890",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Registrasi berhasil. Kode OTP telah dikirim ke email Anda.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "081234567890"
        },
        "otp": "123456"
    }
}
```

---

#### 2. Verify Email

```http
POST /api/auth/verify-email
Content-Type: application/json

{
    "email": "john@example.com",
    "otp": "123456",
    "type": "email_verification"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Email berhasil diverifikasi",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "081234567890",
            "email_verified_at": "2026-01-01T10:30:00.000000Z"
        },
        "access_token": "1|abc123...",
        "refresh_token": "def456...",
        "token_type": "Bearer",
        "expires_in": 1800
    }
}
```

---

#### 3. Login

```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123",
    "device_id": "device-uuid-123",
    "device_name": "iPhone 13"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "081234567890",
            "avatar": null,
            "email_verified_at": "2026-01-01T10:30:00.000000Z",
            "last_login_at": "2026-01-01T12:00:00.000000Z"
        },
        "access_token": "2|xyz789...",
        "refresh_token": "ghi012...",
        "token_type": "Bearer",
        "expires_in": 1800
    }
}
```

---

#### 4. Refresh Token ⭐

```http
POST /api/auth/refresh-token
Content-Type: application/json

{
    "refresh_token": "ghi012...",
    "email": "john@example.com",
    "device_id": "device-uuid-123",
    "device_name": "iPhone 13"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Token berhasil diperbaharui",
    "data": {
        "access_token": "3|new123...",
        "refresh_token": "new456...",
        "token_type": "Bearer",
        "expires_in": 1800
    }
}
```

**Error Response (Refresh Token Expired):**

```json
{
    "success": false,
    "message": "Refresh token tidak valid atau sudah kadaluarsa. Silakan login kembali."
}
```

---

#### 5. Logout

```http
POST /api/auth/logout
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "message": "Logout berhasil"
}
```

**Action Backend:**

-   ✓ Hapus semua access token user
-   ✓ Revoke refresh token
-   ✓ Clear device info

---

#### 6. Get Current User

```http
GET /api/auth/me
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "message": "Data user berhasil diambil",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "081234567890",
            "avatar": null,
            "email_verified_at": "2026-01-01T10:30:00.000000Z",
            "phone_verified_at": null,
            "last_login_at": "2026-01-01T12:00:00.000000Z",
            "is_active": true,
            "created_at": "2026-01-01T10:00:00.000000Z"
        }
    }
}
```

---

### Primary Vehicle Endpoints

#### 7. Get Primary Vehicle ⭐

```http
GET /api/vehicles/primary
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "message": "Data motor utama berhasil diambil",
    "data": {
        "id": 1,
        "user_id": 1,
        "title": "Honda PCX Saya",
        "make": "Honda",
        "model": "PCX 160",
        "year": 2023,
        "tipe_motor": "matic",
        "vin": "MH1JF8116MK123456",
        "odometer": 5000,
        "license_plate": "B 1234 XYZ",
        "color": "Hitam",
        "photo_url": "vehicles/1234567890_pcx.jpg",
        "is_primary": true,
        "created_at": "2026-01-01T10:00:00.000000Z",
        "updated_at": "2026-01-01T10:00:00.000000Z",
        "service_intervals": [...],
        "service_histories": [...],
        "fuel_logs": [...],
        "reminders": [...]
    }
}
```

**Error Response (No Primary Vehicle):**

```json
{
    "success": false,
    "message": "Belum ada motor utama. Silakan pilih motor utama terlebih dahulu."
}
```

---

#### 8. Set Primary Vehicle ⭐

```http
POST /api/vehicles/{id}/set-primary
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "message": "Motor utama berhasil diubah",
    "data": {
        "id": 2,
        "user_id": 1,
        "title": "Yamaha NMAX Saya",
        "make": "Yamaha",
        "model": "NMAX 155",
        "year": 2024,
        "is_primary": true,
        ...
    }
}
```

**Action Backend:**

-   ✓ Unset semua motor lain sebagai primary (`is_primary = false`)
-   ✓ Set motor ini sebagai primary (`is_primary = true`)
-   ✓ Transactional (DB::beginTransaction)

---

#### 9. List All Vehicles

```http
GET /api/vehicles
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "message": "Daftar kendaraan berhasil diambil",
    "data": [
        {
            "id": 1,
            "title": "Honda PCX Saya",
            "is_primary": true,
            ...
        },
        {
            "id": 2,
            "title": "Yamaha NMAX Saya",
            "is_primary": false,
            ...
        }
    ]
}
```

---

#### 10. Add Vehicle

```http
POST /api/vehicles
Authorization: Bearer {access_token}
Content-Type: multipart/form-data

{
    "title": "Motor Baru",
    "make": "Honda",
    "model": "Vario 160",
    "year": 2024,
    "tipe_motor": "matic",
    "odometer": 0,
    "license_plate": "B 5678 ABC",
    "color": "Merah",
    "photo": [file]
}
```

**Response:**

```json
{
    "success": true,
    "message": "Kendaraan berhasil ditambahkan dan ditetapkan sebagai motor utama",
    "data": {
        "id": 3,
        "title": "Motor Baru",
        "is_primary": true,
        ...
    }
}
```

**Auto-Primary Logic:**

-   Jika motor pertama user → otomatis `is_primary = true`
-   Jika bukan motor pertama → `is_primary = false`

---

## 📱 Flow Penggunaan

### Flow 1: Pertama Kali Registrasi

```
User membuka app
    ↓
Tampilan Register
    ↓
User isi form & submit → POST /auth/register
    ↓
Tampilan Verifikasi OTP
    ↓
User input OTP → POST /auth/verify-email
    ↓
✓ Dapat access_token + refresh_token
    ↓
Frontend simpan token di secure storage
    ↓
Cek primary vehicle → GET /vehicles/primary
    ↓
⚠ Error 404: Belum ada motor utama
    ↓
Tampilan Tambah Motor Pertama
    ↓
User tambah motor → POST /vehicles
    ↓
✓ Motor otomatis jadi primary
    ↓
Redirect ke Dashboard dengan primary vehicle
```

---

### Flow 2: Login (Sudah Pernah Register)

```
User membuka app
    ↓
Tampilan Login
    ↓
User isi email & password → POST /auth/login
    ↓
✓ Dapat access_token + refresh_token
    ↓
Frontend simpan token
    ↓
Auto-fetch primary vehicle → GET /vehicles/primary
    ↓
✓ Ada primary vehicle
    ↓
Langsung ke Dashboard (no interruption!)
```

---

### Flow 3: Buka App Lagi (Token Masih Valid)

```
User membuka app
    ↓
Frontend cek refresh token di storage
    ↓
✓ Refresh token masih valid
    ↓
Auto-call → POST /auth/refresh-token
    ↓
✓ Dapat access_token baru + refresh_token baru
    ↓
Update token di storage
    ↓
Auto-fetch primary vehicle → GET /vehicles/primary
    ↓
Langsung ke Dashboard (seamless!)
```

**Penting:** Proses refresh token **tidak terlihat** oleh user. Terjadi di background.

---

### Flow 4: Access Token Expired (Saat Pakai App)

```
User sedang pakai app (sudah 31 menit)
    ↓
User klik fitur (request API)
    ↓
⚠ API return 401 Unauthorized (token expired)
    ↓
Frontend intercept error → auto-call POST /auth/refresh-token
    ↓
✓ Dapat access_token baru
    ↓
Retry request sebelumnya dengan token baru
    ↓
✓ Success (user tidak sadar ada yang error!)
```

**Implementation (Frontend - contoh Axios):**

```javascript
axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response.status === 401) {
            const refreshToken = await storage.getRefreshToken();
            const response = await axios.post("/auth/refresh-token", {
                refresh_token: refreshToken,
            });

            await storage.saveTokens(
                response.data.access_token,
                response.data.refresh_token
            );

            // Retry original request
            return axios.request(error.config);
        }
        return Promise.reject(error);
    }
);
```

---

### Flow 5: Ganti Motor Utama

```
User di Dashboard (motor Honda PCX sebagai primary)
    ↓
User klik "Ganti Motor" di menu
    ↓
Tampilan Daftar Motor → GET /vehicles
    ↓
User pilih Yamaha NMAX
    ↓
POST /vehicles/2/set-primary
    ↓
✓ Backend set NMAX sebagai primary, unset PCX
    ↓
Update UI Dashboard dengan motor NMAX
    ↓
Semua fitur otomatis pakai NMAX (tracking, reminder, dll)
```

---

### Flow 6: Logout Manual

```
User klik Logout
    ↓
POST /auth/logout dengan access_token
    ↓
Backend:
  - Hapus semua access token
  - Revoke refresh token
  - Clear device info
    ↓
Frontend:
  - Hapus token dari storage
  - Redirect ke halaman Login
```

---

### Flow 7: Refresh Token Expired (30 Hari Tidak Buka App)

```
User buka app setelah 31 hari
    ↓
Frontend cek refresh token
    ↓
Auto-call POST /auth/refresh-token
    ↓
⚠ Error 401: Refresh token expired
    ↓
Frontend hapus token lama
    ↓
Redirect ke halaman Login
    ↓
User login ulang
```

---

## 🗄️ Database Schema

### Table: users

| Column                       | Type         | Description                    |
| ---------------------------- | ------------ | ------------------------------ |
| id                           | bigint       | Primary key                    |
| name                         | varchar(150) | Nama user                      |
| email                        | varchar(180) | Email (unique)                 |
| phone                        | varchar(20)  | No. HP (nullable, unique)      |
| password                     | varchar      | Hashed password                |
| email_verified_at            | timestamp    | Waktu verifikasi email         |
| phone_verified_at            | timestamp    | Waktu verifikasi phone         |
| **refresh_token**            | text         | **Hash SHA-256 refresh token** |
| **refresh_token_expires_at** | timestamp    | **Expiry refresh token**       |
| **device_id**                | varchar      | **ID perangkat**               |
| **device_name**              | varchar      | **Nama perangkat**             |
| avatar                       | varchar      | URL foto profil                |
| is_active                    | boolean      | Status aktif                   |
| last_login_at                | timestamp    | Terakhir login                 |
| remember_token               | varchar      | Laravel remember token         |
| created_at                   | timestamp    | Waktu dibuat                   |
| updated_at                   | timestamp    | Waktu diupdate                 |

### Table: personal_access_tokens (Laravel Sanctum)

| Column         | Type        | Description                   |
| -------------- | ----------- | ----------------------------- |
| id             | bigint      | Primary key                   |
| tokenable_type | varchar     | Polymorphic type (User)       |
| tokenable_id   | bigint      | User ID                       |
| name           | varchar     | Token name                    |
| token          | varchar(64) | Hashed token                  |
| abilities      | text        | Permissions                   |
| **expires_at** | timestamp   | **Token expiry (30 minutes)** |
| last_used_at   | timestamp   | Last usage                    |
| created_at     | timestamp   | Created time                  |
| updated_at     | timestamp   | Updated time                  |

### Table: vehicles

| Column         | Type         | Description                      |
| -------------- | ------------ | -------------------------------- |
| id             | bigint       | Primary key                      |
| user_id        | bigint       | Foreign key ke users             |
| title          | varchar(200) | Nama motor                       |
| make           | varchar(100) | Merk (Honda, Yamaha, dll)        |
| model          | varchar(100) | Model (PCX, NMAX, dll)           |
| year           | integer      | Tahun                            |
| tipe_motor     | enum         | matic, sport, bebek              |
| vin            | varchar(50)  | Vehicle Identification Number    |
| odometer       | integer      | Odometer saat ini (km)           |
| license_plate  | varchar(20)  | Plat nomor                       |
| color          | varchar(50)  | Warna                            |
| photo_url      | varchar      | URL foto motor                   |
| **is_primary** | boolean      | **Motor utama (default: false)** |
| deleted_at     | timestamp    | Soft delete                      |
| created_at     | timestamp    | Created time                     |
| updated_at     | timestamp    | Updated time                     |

**Index:**

-   `user_id` (untuk query vehicles by user)
-   `is_primary` (untuk quick lookup primary vehicle)
-   Unique: `(user_id, is_primary)` conditional (hanya 1 primary per user)

---

## ✅ Best Practices

### Frontend Implementation

#### 1. Token Storage

```javascript
// ❌ JANGAN simpan di localStorage (XSS vulnerable)
localStorage.setItem("token", token);

// ✅ Gunakan secure storage
// - iOS: Keychain
// - Android: EncryptedSharedPreferences
// - React Native: @react-native-keychain
// - Flutter: flutter_secure_storage
```

#### 2. Auto-Refresh Logic

```javascript
// Refresh token sebelum expired
// Jangan tunggu sampai benar-benar expired

// ❌ JANGAN
if (tokenExpired) {
    refreshToken();
}

// ✅ LAKUKAN
const expiresAt = getTokenExpiresAt(); // dari JWT payload
const now = Date.now();
const timeLeft = expiresAt - now;

if (timeLeft < 5 * 60 * 1000) {
    // 5 menit sebelum expired
    refreshToken();
}
```

#### 3. Loading State

```javascript
// Saat auto-refresh, jangan show loading spinner
// User tidak boleh tahu ada proses refresh

// ❌ JANGAN
setLoading(true);
await refreshToken();
setLoading(false);

// ✅ LAKUKAN (silent refresh)
axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response.status === 401) {
            await silentRefreshToken(); // no UI feedback
            return retryRequest(error.config);
        }
    }
);
```

#### 4. Primary Vehicle Context

```javascript
// ✅ Gunakan state management (Redux, Zustand, Context API)
const PrimaryVehicleContext = createContext();

function App() {
    const [primaryVehicle, setPrimaryVehicle] = useState(null);

    useEffect(() => {
        // Fetch primary vehicle saat app load
        fetchPrimaryVehicle().then(setPrimaryVehicle);
    }, []);

    return (
        <PrimaryVehicleContext.Provider value={primaryVehicle}>
            {/* All components can access primary vehicle */}
        </PrimaryVehicleContext.Provider>
    );
}
```

---

### Backend Security

#### 1. Refresh Token Hashing

```php
// ✅ SELALU hash refresh token di database
$rawToken = bin2hex(random_bytes(64)); // Plain token untuk user
$hashedToken = hash('sha256', $rawToken); // Hash untuk database

$user->update([
    'refresh_token' => $hashedToken, // Simpan yang hash
]);

return $rawToken; // Return yang plain ke user
```

#### 2. Token Expiry

```php
// ✅ Gunakan expires_at di Laravel Sanctum
$token = $user->createToken(
    'auth_token',
    ['*'],
    now()->addMinutes(30) // Expiry 30 menit
);
```

#### 3. Rate Limiting

```php
// ✅ Batasi refresh token endpoint
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/auth/refresh-token', ...);
});
// Max 10 request per menit
```

#### 4. Device Tracking

```php
// ✅ Track device untuk security
if ($user->device_id && $user->device_id !== $request->device_id) {
    // Optional: kirim email notifikasi
    Mail::to($user->email)->send(new NewDeviceLoginMail());
}

$user->updateDeviceInfo($request->device_id, $request->device_name);
```

---

### Testing Checklist

#### Autentikasi

-   [ ] Register → OTP → Verify → Dapat token
-   [ ] Login → Dapat access + refresh token
-   [ ] Access token expired → Auto-refresh → Continue seamlessly
-   [ ] Refresh token expired → Redirect ke login
-   [ ] Logout → Revoke semua token
-   [ ] Login di device baru → Update device info

#### Primary Vehicle

-   [ ] Motor pertama → Auto primary
-   [ ] GET /vehicles/primary → Return primary vehicle
-   [ ] POST /vehicles/{id}/set-primary → Switch primary
-   [ ] Hapus primary vehicle → ???(edge case, boleh error atau set motor lain jadi primary)
-   [ ] User tanpa motor → GET /vehicles/primary return 404

#### Edge Cases

-   [ ] User punya 2 motor primary (data corrupt) → Backend fix otomatis?
-   [ ] Refresh token di-revoke manual → Login ulang
-   [ ] Concurrent refresh token request → Race condition?
-   [ ] Token expired mid-request → Retry logic

---

## 🎓 Konsep Penting

### Perbedaan Access Token & Refresh Token

| Aspect         | Access Token          | Refresh Token       |
| -------------- | --------------------- | ------------------- |
| **Durasi**     | Short (30 min)        | Long (30 days)      |
| **Penggunaan** | Setiap API request    | Hanya untuk refresh |
| **Format**     | JWT (Laravel Sanctum) | Random hash         |
| **Storage**    | Memory/Secure storage | Secure storage      |
| **Keamanan**   | Medium (short-lived)  | High (long-lived)   |
| **Revoke**     | Delete dari DB        | Update kolom user   |

### Mengapa Dual Token?

1. **Keamanan**: Access token expired cepat, jadi kalau di-intercept attacker, lifetime pendek
2. **UX**: Refresh token panjang, user tidak perlu login berulang
3. **Flexibility**: Bisa revoke refresh token tanpa revoke semua session
4. **Standard**: OAuth 2.0 standard

---

## 🔧 Konfigurasi

### Laravel Sanctum

**File**: `config/sanctum.php`

```php
return [
    'expiration' => 30, // Access token expiry (minutes)
    // null = no expiration
];
```

### Environment Variables

```env
# Session
SESSION_LIFETIME=120

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SPA_URL=http://localhost:3000

# Token Expiry (custom)
ACCESS_TOKEN_EXPIRY=30 # minutes
REFRESH_TOKEN_EXPIRY=30 # days
```

---

## 📞 Troubleshooting

### Problem: Access token selalu expired

**Solution:**

```php
// Pastikan expiry diset saat create token
$user->createToken('auth_token', ['*'], now()->addMinutes(30));
```

---

### Problem: Refresh token tidak tervalidasi

**Solution:**

```php
// Pastikan hashing konsisten
// Generate
$rawToken = bin2hex(random_bytes(64));
$hashedToken = hash('sha256', $rawToken);

// Verify
$isValid = hash_equals($storedHash, hash('sha256', $inputToken));
```

---

### Problem: Primary vehicle tidak ter-set otomatis

**Solution:**

```php
// Check logic di VehicleController::store
$existingVehicleCount = Vehicle::where('user_id', $user->id)->count();
if ($existingVehicleCount === 0) {
    $validated['is_primary'] = true;
}
```

---

### Problem: User punya 2 motor primary

**Solution:**

```php
// Buat method untuk fix
public function fixMultiplePrimary(User $user)
{
    $primaryVehicles = Vehicle::where('user_id', $user->id)
        ->where('is_primary', true)
        ->get();

    if ($primaryVehicles->count() > 1) {
        // Keep oldest, unset others
        $primaryVehicles->skip(1)->each(function ($vehicle) {
            $vehicle->update(['is_primary' => false]);
        });
    }
}
```

---

## 🚀 Deployment Checklist

-   [ ] Set `ACCESS_TOKEN_EXPIRY` dan `REFRESH_TOKEN_EXPIRY` di env
-   [ ] Hapus `otp` dari response di production (ada di AuthController)
-   [ ] Setup HTTPS (wajib untuk token security)
-   [ ] Enable rate limiting di `/auth/refresh-token`
-   [ ] Setup log monitoring untuk suspicious token activity
-   [ ] Configure Laravel Sanctum untuk production domain
-   [ ] Test refresh token flow end-to-end
-   [ ] Test primary vehicle flow end-to-end
-   [ ] Setup database backup (refresh token data penting)
-   [ ] Configure session timeout

---

## 📚 Referensi

-   [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
-   [OAuth 2.0 Refresh Token Flow](https://oauth.net/2/grant-types/refresh-token/)
-   [JWT Best Practices](https://tools.ietf.org/html/rfc8725)

---

## 👨‍💻 Maintainer

Backend API ini siap production dengan sistem autentikasi yang aman dan UX yang seamless.

**Versi**: 1.0.0  
**Last Updated**: 2026-01-01

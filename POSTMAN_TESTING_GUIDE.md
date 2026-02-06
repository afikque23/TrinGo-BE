# 🚀 Postman Testing Guide

## Motorcycle Management API v2.0

---

## 📥 Import ke Postman

### 1. Import Collection

-   Buka Postman
-   Klik **Import** di kiri atas
-   Drag & drop file: `Motorcycle_Management_API.postman_collection.json`
-   Klik **Import**

### 2. Import Environment

-   Klik **Import** lagi
-   Drag & drop file: `Motorcycle_Management_Local.postman_environment.json`
-   Klik **Import**

### 3. Aktifkan Environment

-   Di kanan atas Postman, pilih dropdown environment
-   Pilih **"Motorcycle Management - Local v2"**

---

## 🎯 Test Flow

### **Flow 1: Register → Verify → Add Vehicle → Primary**

Jalankan request secara berurutan:

#### 1️⃣ **Register**

```
Authentication → 1. Register
```

-   **Method**: POST
-   **Endpoint**: `/auth/register`
-   **Auto-save**: OTP & email ke environment
-   ✅ Check Console untuk OTP

#### 2️⃣ **Verify Email**

```
Authentication → 2. Verify Email
```

-   **Method**: POST
-   **Endpoint**: `/auth/verify-email`
-   **Auto-use**: OTP dari register
-   **Auto-save**: `access_token` & `refresh_token`
-   ✅ Sekarang sudah punya token untuk request selanjutnya

#### 3️⃣ **Add Vehicle (Auto Primary)**

```
Vehicles - Primary System → 1. Add Vehicle (Auto Primary)
```

-   **Method**: POST
-   **Endpoint**: `/vehicles`
-   **Auto-save**: `vehicle_id`
-   ✅ Check response: `is_primary: true`

#### 4️⃣ **Get Primary Vehicle**

```
Vehicles - Primary System → 2. Get Primary Vehicle ⭐
```

-   **Method**: GET
-   **Endpoint**: `/vehicles/primary`
-   ✅ Konfirmasi motor yang barusan ditambah adalah primary

---

### **Flow 2: Test Refresh Token**

#### 1️⃣ **Refresh Token**

```
Authentication → 4. Refresh Token ⭐
```

-   **Method**: POST
-   **Endpoint**: `/auth/refresh-token`
-   **Auto-use**: `refresh_token` dari environment
-   **Auto-save**: Token baru (access + refresh)
-   ✅ Token lama otomatis diganti dengan yang baru

#### 2️⃣ **Test Token Baru**

```
Authentication → 5. Get Current User
```

-   **Method**: GET
-   **Endpoint**: `/auth/me`
-   ✅ Gunakan token baru, harus berhasil

---

### **Flow 3: Multi Vehicle & Switch Primary**

#### 1️⃣ **Add Second Vehicle**

```
Vehicles - Primary System → 5. Add Second Vehicle
```

-   **Method**: POST
-   **Endpoint**: `/vehicles`
-   **Auto-save**: `vehicle_id_2`
-   ✅ Check: `is_primary: false` (karena sudah ada primary)

#### 2️⃣ **List All Vehicles**

```
Vehicles - Primary System → 3. List All Vehicles
```

-   **Method**: GET
-   **Endpoint**: `/vehicles`
-   ✅ Lihat 2 motor, 1 primary & 1 non-primary

#### 3️⃣ **Set Second Vehicle as Primary**

```
Vehicles - Primary System → 6. Set as Primary Vehicle ⭐
```

-   **Method**: POST
-   **Endpoint**: `/vehicles/{{vehicle_id_2}}/set-primary`
-   ✅ Motor kedua sekarang jadi primary

#### 4️⃣ **Verify Primary Changed**

```
Vehicles - Primary System → 2. Get Primary Vehicle ⭐
```

-   **Method**: GET
-   **Endpoint**: `/vehicles/primary`
-   ✅ Sekarang return motor kedua (NMAX)

#### 5️⃣ **List Again**

```
Vehicles - Primary System → 3. List All Vehicles
```

-   **Method**: GET
-   ✅ Motor pertama (PCX): `is_primary: false`
-   ✅ Motor kedua (NMAX): `is_primary: true`

---

## 🔑 Environment Variables

Collection ini otomatis set/get variable:

| Variable        | Set By                 | Used By                   |
| --------------- | ---------------------- | ------------------------- |
| `access_token`  | Login, Verify, Refresh | Semua protected endpoints |
| `refresh_token` | Login, Verify          | Refresh Token             |
| `otp`           | Register, Resend OTP   | Verify Email              |
| `reset_otp`     | Forgot Password        | Reset Password            |
| `test_email`    | Register               | Verify, Refresh           |
| `vehicle_id`    | Add Vehicle            | Update, Delete, Get by ID |
| `vehicle_id_2`  | Add Second Vehicle     | Set Primary               |

Semua variable ini **otomatis tersimpan** saat response sukses! ✨

---

## ⚙️ Authorization

Collection menggunakan **Bearer Token** otomatis.

-   Semua request di folder **Authentication** & **Vehicles** menggunakan `{{access_token}}`
-   Request **Refresh Token** tidak pakai auth (public endpoint)
-   Request **Health Check** tidak pakai auth

---

## 📊 Test Scripts

Collection ini punya test scripts yang otomatis:

### Register

```javascript
// Auto-save OTP & email
if (pm.response.code === 201) {
    pm.environment.set("otp", response.data.otp);
    pm.environment.set("test_email", email);
}
```

### Verify Email / Login

```javascript
// Auto-save tokens
if (pm.response.code === 200) {
    pm.environment.set("access_token", response.data.access_token);
    pm.environment.set("refresh_token", response.data.refresh_token);
}
```

### Add Vehicle

```javascript
// Auto-save vehicle_id
if (pm.response.code === 201) {
    pm.environment.set("vehicle_id", response.data.id);
}
```

---

## 💡 Tips & Tricks

### 1. **Lihat Console Log**

Klik **Console** di bawah Postman untuk melihat log auto-save:

```
✅ OTP saved: 123456
✅ Access Token saved
✅ Refresh Token saved
✅ Vehicle created, ID: 1
```

### 2. **Check Environment Variables**

Klik icon **Environment Quick Look** (mata) di kanan atas untuk lihat semua variable.

### 3. **Test Expired Token**

Manual hapus `access_token` dari environment, lalu test Refresh Token endpoint.

### 4. **Reset Testing**

Hapus semua variable kecuali `base_url`:

-   Klik Environment
-   Hapus value `access_token`, `refresh_token`, dll
-   Start dari Register lagi

### 5. **Multiple Users**

Untuk test multi-user:

1. Ganti `test_email` di request Register
2. Run flow lengkap
3. Simpan token di variable berbeda

---

## 🚨 Troubleshooting

### Error: 401 Unauthorized

**Cause**: Token expired atau tidak ada
**Fix**:

1. Check environment `access_token` ada isinya
2. Coba Refresh Token dulu
3. Kalau gagal, Login ulang

### Error: 404 Not Found (Primary Vehicle)

**Cause**: Belum ada motor ditambahkan
**Fix**: Run "Add Vehicle" dulu

### Error: OTP Invalid

**Cause**: OTP expired (10 menit) atau salah
**Fix**: Run "Resend OTP" dulu

### Token tidak auto-save

**Cause**: Test script error
**Fix**:

1. Check Console untuk error
2. Pastikan response success (200/201)
3. Check response structure sesuai

---

## 📖 Request Details

### Authentication

#### Register

```json
POST /api/auth/register
Body:
{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "081234567890",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Login

```json
POST /api/auth/login
Body:
{
    "email": "test@example.com",
    "password": "password123",
    "device_id": "device-uuid-123",
    "device_name": "Postman Test"
}
```

#### Refresh Token ⭐

```json
POST /api/auth/refresh-token
Body:
{
    "refresh_token": "{{refresh_token}}",
    "email": "test@example.com",
    "device_id": "device-uuid-123",
    "device_name": "Postman Test"
}
```

### Vehicles

#### Get Primary Vehicle ⭐

```
GET /api/vehicles/primary
Authorization: Bearer {{access_token}}
```

#### Set Primary ⭐

```
POST /api/vehicles/{{vehicle_id}}/set-primary
Authorization: Bearer {{access_token}}
```

#### Add Vehicle

```json
POST /api/vehicles
Authorization: Bearer {{access_token}}
Body:
{
    "title": "Honda PCX Saya",
    "make": "Honda",
    "model": "PCX 160",
    "year": 2023,
    "tipe_motor": "matic",
    "odometer": 5000,
    "license_plate": "B 1234 XYZ",
    "color": "Hitam"
}
```

---

## ✅ Test Checklist

Setelah import, test flow berikut:

-   [ ] Register user baru
-   [ ] Verify email dengan OTP
-   [ ] Check access_token & refresh_token tersimpan
-   [ ] Add vehicle pertama (auto primary)
-   [ ] Get primary vehicle
-   [ ] Refresh token
-   [ ] Get current user (dengan token baru)
-   [ ] Add vehicle kedua (tidak auto primary)
-   [ ] List all vehicles
-   [ ] Set vehicle kedua sebagai primary
-   [ ] Verify primary sudah berubah
-   [ ] Logout
-   [ ] Test token invalid setelah logout

---

## 🎉 Ready to Test!

Collection dan environment siap digunakan. Jalankan flow dari atas ke bawah untuk test lengkap sistem autentikasi & primary vehicle.

**Happy Testing!** 🚀

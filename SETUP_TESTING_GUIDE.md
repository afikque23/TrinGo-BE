# 🏍️ Setup & Testing Guide - Vehicle Management API

## ✅ Fitur yang Sudah Diimplementasikan

### 1. **CRUD Vehicle Management**

-   ✅ Tambah Kendaraan Baru (Create)
-   ✅ Lihat Daftar Kendaraan (Read List)
-   ✅ Lihat Detail Kendaraan (Read Detail)
-   ✅ Update Data Kendaraan (Update)
-   ✅ Hapus Kendaraan (Delete)

### 2. **Autentikasi & Keamanan**

-   ✅ JWT Authentication dengan Sanctum
-   ✅ Setiap kendaraan terikat ke `user_id`
-   ✅ User hanya bisa akses kendaraan miliknya

### 3. **Model Data Kendaraan**

-   ✅ id (auto-generated)
-   ✅ user_id (relasi ke user)
-   ✅ title (nama_kendaraan)
-   ✅ make (merek)
-   ✅ model (model)
-   ✅ year (tahun)
-   ✅ tipe_motor (matic/manual/sport) ⭐ **NEW**
-   ✅ photo_url (foto_kendaraan)
-   ✅ odometer
-   ✅ license_plate
-   ✅ color
-   ✅ vin
-   ✅ notes
-   ✅ created_at & updated_at

### 4. **Service Intervals (Auto-Generated)** ⭐ **NEW**

Sistem otomatis membuat jadwal servis saat kendaraan ditambahkan:

#### Tipe Matic:

-   Ganti Oli: setiap 2,000 km
-   Cek Rem: setiap 3,000 km
-   Ganti Oli CVT: setiap 5,000 km
-   Servis Besar: setiap 10,000 km

#### Tipe Manual:

-   Ganti Oli: setiap 2,500 km
-   Cek Rem: setiap 3,500 km
-   Ganti Oli Gardan: setiap 6,000 km
-   Servis Besar: setiap 12,000 km

#### Tipe Sport:

-   Ganti Oli: setiap 3,000 km
-   Cek Rem: setiap 4,000 km
-   Tune Up Mesin: setiap 5,000 km
-   Ganti Oli Gardan: setiap 7,000 km
-   Servis Besar: setiap 15,000 km

### 5. **Upload Foto Kendaraan**

-   ✅ Validasi format (JPEG, JPG, PNG, WEBP)
-   ✅ Validasi ukuran max 5MB
-   ✅ Auto-delete foto lama saat update
-   ✅ Storage di `storage/app/public/vehicles/`

### 6. **Validasi & Error Handling**

-   ✅ Form Request Validation
-   ✅ Custom error messages (Bahasa Indonesia)
-   ✅ Consistent JSON Response format
-   ✅ Database Transaction (rollback on error)
-   ✅ Logging system

---

## 🚀 Setup & Installation

### 1. Database Migration

```bash
php artisan migrate:fresh --seed
```

### 2. Storage Link (untuk akses foto)

```bash
php artisan storage:link
```

### 3. Jalankan Server

```bash
php artisan serve
```

Server akan berjalan di: `http://localhost:8000`

---

## 🧪 Testing

### Metode 1: HTML Tester (Paling Mudah)

1. Buka browser: `http://localhost:8000/vehicle-test.html`
2. Login terlebih dahulu (default: test@example.com / password123)
3. Isi form dan upload foto kendaraan
4. Klik "Tambah Kendaraan"

### Metode 2: Postman

#### Step 1: Login

```
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "password123"
}
```

Copy `access_token` dari response.

#### Step 2: Tambah Kendaraan

```
POST http://localhost:8000/api/vehicles
Authorization: Bearer {your_token}
Content-Type: multipart/form-data

Body (form-data):
- title: Honda Beat 2023
- make: Honda
- model: Beat
- year: 2023
- tipe_motor: matic
- odometer: 5000
- license_plate: B 1234 XYZ
- color: Merah
- photo: [pilih file]
- notes: Motor pribadi
```

#### Step 3: Lihat Daftar Kendaraan

```
GET http://localhost:8000/api/vehicles
Authorization: Bearer {your_token}
```

#### Step 4: Lihat Detail Kendaraan

```
GET http://localhost:8000/api/vehicles/1
Authorization: Bearer {your_token}
```

#### Step 5: Update Kendaraan

```
POST http://localhost:8000/api/vehicles/1
Authorization: Bearer {your_token}
Content-Type: multipart/form-data

Body (form-data):
- _method: PUT
- odometer: 7500
- notes: Sudah ganti oli
```

#### Step 6: Hapus Kendaraan

```
DELETE http://localhost:8000/api/vehicles/1
Authorization: Bearer {your_token}
```

---

## 📁 File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── VehicleController.php          # Main controller CRUD
│   └── Requests/
│       ├── StoreVehicleRequest.php        # Validation create
│       └── UpdateVehicleRequest.php       # Validation update
├── Models/
│   ├── Vehicle.php                        # Model Vehicle
│   └── ServiceInterval.php                # Model Service Interval
└── Traits/
    └── ApiResponse.php                    # Response helper

database/
└── migrations/
    ├── 2024_01_01_000001_create_vehicles_table.php
    └── 2024_01_01_000010_create_service_intervals_table.php

routes/
└── api.php                                # API Routes

public/
└── vehicle-test.html                      # HTML Tester

storage/
└── app/
    └── public/
        └── vehicles/                      # Foto kendaraan disimpan di sini
```

---

## 🎯 Testing Scenarios

### Scenario 1: Happy Path

1. ✅ Login berhasil
2. ✅ Tambah kendaraan matic dengan foto
3. ✅ Service intervals auto-generated (4 items)
4. ✅ Lihat daftar kendaraan
5. ✅ Update odometer
6. ✅ Hapus kendaraan

### Scenario 2: Validation Error

1. ❌ Tambah kendaraan tanpa title (error)
2. ❌ Tambah kendaraan dengan tipe_motor invalid (error)
3. ❌ Upload foto > 5MB (error)
4. ❌ Upload file bukan gambar (error)

### Scenario 3: Authorization

1. ❌ Akses endpoint tanpa token (401 Unauthorized)
2. ❌ User A coba akses kendaraan User B (404 Not Found)

### Scenario 4: Tipe Motor Berbeda

1. ✅ Tambah kendaraan matic → dapat 4 service intervals
2. ✅ Tambah kendaraan manual → dapat 4 service intervals (berbeda)
3. ✅ Tambah kendaraan sport → dapat 5 service intervals (berbeda)
4. ✅ Update tipe_motor → service intervals regenerated

---

## 📊 Response Format

### Success Response

```json
{
  "success": true,
  "message": "Kendaraan berhasil ditambahkan",
  "data": {
    "id": 1,
    "title": "Honda Beat 2023",
    "tipe_motor": "matic",
    "service_intervals": [...]
  }
}
```

### Error Response (Validation)

```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "title": ["Nama kendaraan wajib diisi"],
        "tipe_motor": [
            "Tipe motor harus salah satu dari: matic, manual, atau sport"
        ]
    }
}
```

### Error Response (Not Found)

```json
{
    "success": false,
    "message": "Kendaraan tidak ditemukan"
}
```

---

## 🔐 Security Features

1. **JWT Authentication**: Semua endpoint dilindungi
2. **User Isolation**: User hanya bisa CRUD kendaraan sendiri
3. **File Upload Security**:
    - Type validation
    - Size limit (5MB)
    - Unique filename
4. **SQL Injection Prevention**: Eloquent ORM
5. **XSS Prevention**: Laravel auto-escape
6. **CSRF Protection**: API menggunakan Sanctum

---

## 🎨 Frontend Integration

### Contoh Fetch API (JavaScript)

```javascript
// Login
const loginResponse = await fetch("http://localhost:8000/api/auth/login", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    body: JSON.stringify({
        email: "test@example.com",
        password: "password123",
    }),
});
const { data } = await loginResponse.json();
const token = data.access_token;

// Tambah Kendaraan
const formData = new FormData();
formData.append("title", "Honda Beat 2023");
formData.append("tipe_motor", "matic");
formData.append("photo", fileInput.files[0]);

const vehicleResponse = await fetch("http://localhost:8000/api/vehicles", {
    method: "POST",
    headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
    },
    body: formData,
});
```

---

## 📝 Notes

-   Foto kendaraan disimpan di `storage/app/public/vehicles/`
-   Akses foto via URL: `http://localhost:8000/storage/vehicles/filename.jpg`
-   Service intervals di-generate otomatis berdasarkan tipe_motor
-   Soft delete enabled (data tidak benar-benar dihapus)
-   Jika update tipe_motor, service intervals akan di-regenerate

---

## 🚀 Next Features (Siap Dikembangkan)

1. ✅ Model & Relasi sudah ada:

    - Service History
    - Fuel Logs
    - Reminders
    - Trips & Trip Points
    - Documents

2. 🔜 Yang bisa ditambahkan:
    - Notifikasi push untuk reminder
    - Analisis biaya perawatan
    - Export data ke PDF/Excel
    - Sharing kendaraan ke user lain
    - Dashboard statistik

---

## 📞 Support

Jika ada error atau pertanyaan:

1. Cek `storage/logs/laravel.log`
2. Pastikan database sudah di-migrate
3. Pastikan storage link sudah dibuat
4. Cek permission folder storage/

---

**Selamat mencoba! 🎉**

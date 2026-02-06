# Sprint 5 - Quick Test Guide

# Riwayat Servis, Biaya, dan Struk

## Prerequisites

1. Server Laravel sudah berjalan
2. Database sudah di-migrate
3. User sudah terdaftar dan memiliki motor utama (primary vehicle)
4. Token autentikasi tersedia

---

## Quick Test Flow

### Step 1: Login & Setup

```bash
# 1. Login untuk mendapatkan token
POST http://localhost/api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}

# Response - simpan token
{
  "success": true,
  "data": {
    "token": "1|abc123..."
  }
}

# 2. Cek primary vehicle (pastikan sudah ada)
GET http://localhost/api/vehicles/primary
Authorization: Bearer 1|abc123...

# Jika belum ada, set primary vehicle
POST http://localhost/api/vehicles/1/set-primary
Authorization: Bearer 1|abc123...
```

---

### Step 2: Tambah Riwayat Servis

```bash
# Test 1: Servis dengan data lengkap + foto struk
POST http://localhost/api/service-histories
Authorization: Bearer 1|abc123...
Content-Type: multipart/form-data

service_type: Ganti Oli Mesin
performed_at: 2026-01-01
odometer: 5000
cost: 150000
currency: IDR
service_provider: Bengkel Jaya Motor
notes: Ganti oli Shell AX7 10W-40, kondisi mesin bagus
receipt_photo: [FILE: struk.jpg]

# Expected Response: 201 Created
{
  "success": true,
  "message": "Riwayat servis berhasil ditambahkan.",
  "data": {
    "service_history": {
      "id": 1,
      "service_type": "Ganti Oli Mesin",
      "performed_at": "2026-01-01",
      "odometer": 5000,
      "cost": 150000,
      "currency": "IDR",
      "service_provider": "Bengkel Jaya Motor",
      "receipt_url": "http://localhost/storage/receipts/...",
      "notes": "Ganti oli Shell AX7 10W-40, kondisi mesin bagus",
      "created_at": "2026-01-01T..."
    }
  }
}

# Test 2: Servis tanpa foto struk (opsional)
POST http://localhost/api/service-histories
Authorization: Bearer 1|abc123...
Content-Type: application/json

{
  "service_type": "Cuci Motor",
  "performed_at": "2026-01-02",
  "cost": 20000
}

# Test 3: Servis dengan odometer tapi tanpa biaya
POST http://localhost/api/service-histories
Authorization: Bearer 1|abc123...
Content-Type: application/json

{
  "service_type": "Servis Gratis",
  "performed_at": "2025-12-25",
  "odometer": 4500,
  "notes": "Servis gratis dari dealer"
}
```

---

### Step 3: Lihat Daftar Riwayat Servis

```bash
# Ambil semua riwayat servis (sorted newest first)
GET http://localhost/api/service-histories
Authorization: Bearer 1|abc123...

# Expected Response: 200 OK
{
  "success": true,
  "message": "Riwayat servis berhasil diambil.",
  "data": {
    "service_histories": [
      {
        "id": 2,
        "service_type": "Cuci Motor",
        "performed_at": "2026-01-02",
        ...
      },
      {
        "id": 1,
        "service_type": "Ganti Oli Mesin",
        "performed_at": "2026-01-01",
        ...
      },
      {
        "id": 3,
        "service_type": "Servis Gratis",
        "performed_at": "2025-12-25",
        ...
      }
    ],
    "total": 3,
    "vehicle": {
      "id": 1,
      "name": "Honda Vario 160",
      "plate_number": "B 1234 XYZ"
    }
  }
}
```

---

### Step 4: Lihat Detail Riwayat Servis

```bash
# Ambil detail servis berdasarkan ID
GET http://localhost/api/service-histories/1
Authorization: Bearer 1|abc123...

# Expected Response: 200 OK
{
  "success": true,
  "message": "Detail riwayat servis berhasil diambil.",
  "data": {
    "service_history": {
      "id": 1,
      "service_type": "Ganti Oli Mesin",
      "performed_at": "2026-01-01",
      "odometer": 5000,
      "cost": 150000,
      "currency": "IDR",
      "service_provider": "Bengkel Jaya Motor",
      "receipt_url": "http://localhost/storage/receipts/...",
      "notes": "Ganti oli Shell AX7 10W-40",
      "created_at": "2026-01-01T...",
      "updated_at": "2026-01-01T..."
    }
  }
}
```

---

### Step 5: Update Riwayat Servis

```bash
# Update data servis
PUT http://localhost/api/service-histories/1
Authorization: Bearer 1|abc123...
Content-Type: application/json

{
  "cost": 175000,
  "notes": "Ganti oli Shell AX7 10W-40 + filter oli"
}

# Expected Response: 200 OK
{
  "success": true,
  "message": "Riwayat servis berhasil diperbarui.",
  "data": {
    "service_history": {
      "id": 1,
      "service_type": "Ganti Oli Mesin",
      "performed_at": "2026-01-01",
      "odometer": 5000,
      "cost": 175000,
      "notes": "Ganti oli Shell AX7 10W-40 + filter oli",
      ...
    }
  }
}

# Update dengan foto struk baru
PUT http://localhost/api/service-histories/1
Authorization: Bearer 1|abc123...
Content-Type: multipart/form-data

receipt_photo: [FILE: struk_baru.jpg]
```

---

### Step 6: Analisis Biaya (Cost Summary)

```bash
# Test 1: Ringkasan semua waktu
GET http://localhost/api/service-histories/cost-summary
Authorization: Bearer 1|abc123...

# Expected Response: 200 OK
{
  "success": true,
  "message": "Ringkasan biaya servis berhasil diambil.",
  "data": {
    "summary": {
      "period": "all",
      "year": null,
      "month": null,
      "total_cost": 345000,
      "total_services": 3,
      "average_cost": 115000,
      "currency": "IDR",
      "last_service_date": "2026-01-02"
    },
    "cost_by_service_type": [
      {
        "service_type": "Ganti Oli Mesin",
        "total_cost": 175000,
        "count": 1,
        "average_cost": 175000
      },
      {
        "service_type": "Cuci Motor",
        "total_cost": 20000,
        "count": 1,
        "average_cost": 20000
      },
      {
        "service_type": "Servis Gratis",
        "total_cost": 0,
        "count": 1,
        "average_cost": 0
      }
    ],
    "cost_by_month": [],
    "most_expensive_service": {
      "id": 1,
      "service_type": "Ganti Oli Mesin",
      "cost": 175000,
      "performed_at": "2026-01-01"
    },
    "vehicle": {
      "id": 1,
      "name": "Honda Vario 160",
      "plate_number": "B 1234 XYZ"
    }
  }
}

# Test 2: Ringkasan per tahun 2026
GET http://localhost/api/service-histories/cost-summary?period=year&year=2026
Authorization: Bearer 1|abc123...

# Expected Response: dengan cost_by_month diisi
{
  "success": true,
  "data": {
    "summary": {
      "period": "year",
      "year": 2026,
      ...
    },
    "cost_by_month": [
      {
        "month": "2026-01",
        "total_cost": 195000,
        "count": 2
      }
    ],
    ...
  }
}

# Test 3: Ringkasan per bulan
GET http://localhost/api/service-histories/cost-summary?period=month&year=2026&month=1
Authorization: Bearer 1|abc123...
```

---

### Step 7: Hapus Riwayat Servis

```bash
# Hapus servis
DELETE http://localhost/api/service-histories/1
Authorization: Bearer 1|abc123...

# Expected Response: 200 OK
{
  "success": true,
  "message": "Riwayat servis berhasil dihapus.",
  "data": null
}

# Cek lagi daftar (servis sudah tidak ada)
GET http://localhost/api/service-histories
Authorization: Bearer 1|abc123...
```

---

## Test Cases untuk Validasi

### Test Validation Errors

```bash
# Test 1: Service type kosong (should fail)
POST http://localhost/api/service-histories
Authorization: Bearer 1|abc123...
Content-Type: application/json

{
  "performed_at": "2026-01-01"
}

# Expected: 422 Validation Error
{
  "success": false,
  "message": "Validasi gagal.",
  "errors": {
    "service_type": ["Jenis servis wajib diisi."]
  }
}

# Test 2: Tanggal masa depan (should fail)
POST http://localhost/api/service-histories
Authorization: Bearer 1|abc123...
Content-Type: application/json

{
  "service_type": "Ganti Oli",
  "performed_at": "2027-12-31"
}

# Expected: 422 Validation Error
{
  "errors": {
    "performed_at": ["Tanggal servis tidak boleh di masa depan."]
  }
}

# Test 3: Biaya negatif (should fail)
POST http://localhost/api/service-histories
Authorization: Bearer 1|abc123...
Content-Type: application/json

{
  "service_type": "Ganti Oli",
  "performed_at": "2026-01-01",
  "cost": -50000
}

# Expected: 422 Validation Error
{
  "errors": {
    "cost": ["Biaya tidak boleh bernilai negatif."]
  }
}

# Test 4: Odometer negatif (should fail)
POST http://localhost/api/service-histories
Authorization: Bearer 1|abc123...
Content-Type: application/json

{
  "service_type": "Ganti Oli",
  "performed_at": "2026-01-01",
  "odometer": -1000
}

# Expected: 422 Validation Error
{
  "errors": {
    "odometer": ["Kilometer tidak boleh bernilai negatif."]
  }
}

# Test 5: File upload bukan gambar (should fail)
POST http://localhost/api/service-histories
Authorization: Bearer 1|abc123...
Content-Type: multipart/form-data

service_type: Ganti Oli
performed_at: 2026-01-01
receipt_photo: [FILE: dokumen.pdf]

# Expected: 422 Validation Error
{
  "errors": {
    "receipt_photo": ["File harus berupa gambar."]
  }
}

# Test 6: File terlalu besar > 5MB (should fail)
POST http://localhost/api/service-histories
Authorization: Bearer 1|abc123...
Content-Type: multipart/form-data

service_type: Ganti Oli
performed_at: 2026-01-01
receipt_photo: [FILE: large_image.jpg (6MB)]

# Expected: 422 Validation Error
{
  "errors": {
    "receipt_photo": ["Ukuran gambar maksimal 5MB."]
  }
}
```

---

## Test Authorization & Security

```bash
# Test 1: Tanpa token (should fail)
GET http://localhost/api/service-histories

# Expected: 401 Unauthorized
{
  "message": "Unauthenticated."
}

# Test 2: Token invalid (should fail)
GET http://localhost/api/service-histories
Authorization: Bearer invalid_token_123

# Expected: 401 Unauthorized

# Test 3: Akses servis user lain (should fail)
# Asumsi: service_id 999 milik user lain
GET http://localhost/api/service-histories/999
Authorization: Bearer 1|abc123...

# Expected: 404 Not Found
{
  "success": false,
  "message": "Riwayat servis tidak ditemukan.",
  "data": null
}

# Test 4: Tanpa primary vehicle (should fail)
# Setup: hapus primary vehicle dulu
DELETE http://localhost/api/vehicles/1/unset-primary

GET http://localhost/api/service-histories
Authorization: Bearer 1|abc123...

# Expected: 404 Not Found
{
  "success": false,
  "message": "Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu.",
  "data": null
}
```

---

## Test dengan cURL

```bash
# Setup variables
TOKEN="1|abc123..."
BASE_URL="http://localhost/api"

# 1. Get all service histories
curl -X GET "$BASE_URL/service-histories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# 2. Add new service history
curl -X POST "$BASE_URL/service-histories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "service_type": "Ganti Oli",
    "performed_at": "2026-01-01",
    "odometer": 5000,
    "cost": 150000,
    "service_provider": "Bengkel Jaya",
    "notes": "Ganti oli Shell AX7"
  }'

# 3. Add with file upload
curl -X POST "$BASE_URL/service-histories" \
  -H "Authorization: Bearer $TOKEN" \
  -F "service_type=Ganti Oli" \
  -F "performed_at=2026-01-01" \
  -F "odometer=5000" \
  -F "cost=150000" \
  -F "receipt_photo=@/path/to/struk.jpg"

# 4. Get cost summary
curl -X GET "$BASE_URL/service-histories/cost-summary" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# 5. Update service history
curl -X PUT "$BASE_URL/service-histories/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "cost": 175000,
    "notes": "Updated notes"
  }'

# 6. Delete service history
curl -X DELETE "$BASE_URL/service-histories/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

## Expected Database State

Setelah test selesai, cek database:

```sql
-- Cek data service histories
SELECT
  sh.id,
  sh.service_type,
  sh.performed_at,
  sh.odometer,
  sh.cost_cents,
  sh.currency,
  sh.service_provider,
  sh.receipt_url,
  sh.notes,
  v.name as vehicle_name,
  u.email as owner_email
FROM service_histories sh
JOIN vehicles v ON sh.vehicle_id = v.id
JOIN users u ON v.user_id = u.id
WHERE sh.deleted_at IS NULL
ORDER BY sh.performed_at DESC;

-- Cek total biaya per user
SELECT
  u.email,
  v.name as vehicle,
  COUNT(sh.id) as total_services,
  SUM(sh.cost_cents) / 100 as total_cost,
  AVG(sh.cost_cents) / 100 as avg_cost
FROM users u
JOIN vehicles v ON u.id = v.user_id AND v.is_primary = 1
LEFT JOIN service_histories sh ON v.id = sh.vehicle_id
WHERE sh.deleted_at IS NULL
GROUP BY u.id, v.id;
```

---

## Success Criteria

✅ **CRUD Operations**

-   [x] Dapat menambah riwayat servis
-   [x] Dapat melihat daftar riwayat servis
-   [x] Dapat melihat detail riwayat servis
-   [x] Dapat update riwayat servis
-   [x] Dapat menghapus riwayat servis

✅ **File Upload**

-   [x] Dapat upload foto struk
-   [x] Foto disimpan dengan benar
-   [x] URL foto dapat diakses
-   [x] Update foto menghapus foto lama
-   [x] Delete servis menghapus foto

✅ **Cost Summary**

-   [x] Dapat melihat ringkasan semua waktu
-   [x] Dapat filter per tahun
-   [x] Dapat filter per bulan
-   [x] Cost by service type benar
-   [x] Cost by month benar (yearly)
-   [x] Most expensive service benar

✅ **Validation**

-   [x] Service type wajib diisi
-   [x] Tanggal wajib diisi
-   [x] Tanggal tidak boleh masa depan
-   [x] Biaya tidak boleh negatif
-   [x] Odometer tidak boleh negatif
-   [x] File harus gambar
-   [x] File max 5MB

✅ **Security**

-   [x] Token wajib untuk semua endpoint
-   [x] User hanya akses motor utamanya
-   [x] Primary vehicle check di semua endpoint
-   [x] File upload aman

✅ **Primary Vehicle Integration**

-   [x] Otomatis gunakan primary vehicle
-   [x] Tidak perlu kirim vehicle_id
-   [x] Error jika primary vehicle belum ada

---

## Common Issues & Solutions

### Issue 1: "Motor utama belum ditetapkan"

**Solution:** Set primary vehicle terlebih dahulu

```bash
POST http://localhost/api/vehicles/{id}/set-primary
Authorization: Bearer {token}
```

### Issue 2: File upload tidak berhasil

**Solution:**

1. Pastikan menggunakan Content-Type: multipart/form-data
2. Cek ukuran file (max 5MB)
3. Cek format file (jpeg, jpg, png, webp)
4. Pastikan storage/app/public/receipts/ writable

### Issue 3: Receipt URL 404

**Solution:**

1. Jalankan: `php artisan storage:link`
2. Pastikan symbolic link sudah dibuat

### Issue 4: Cost tidak muncul di summary

**Solution:**

1. Pastikan cost_cents tidak NULL di database
2. Pastikan ada data servis dengan biaya

### Issue 5: Validation error tidak muncul

**Solution:**

1. Cek Content-Type header
2. Pastikan Accept: application/json

---

## Next Steps

Setelah semua test berhasil:

1. ✅ Integrasi dengan frontend mobile
2. ✅ Implementasi reminder berdasarkan service history
3. ✅ Export report (PDF/Excel)
4. ✅ Dashboard analytics
5. ✅ Notification system

---

**Test Status:** ✅ Ready for Testing  
**Version:** 1.0.0 (Sprint 5)  
**Last Updated:** January 2, 2026

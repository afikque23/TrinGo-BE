# 🔧 Backend Fix Applied - Service Schedule Interval Value

## ✅ Perbaikan yang Sudah Dilakukan

### 1. **Database Migration** (2 files)

- `2026_02_22_000001_add_interval_columns_to_service_schedules_table.php`
    - Menambah kolom: `interval_value`, `last_service_mileage`, `last_service_date`
- `2026_02_22_000002_fix_service_schedule_interval_values.php`
    - Mempopulasi `interval_value` untuk data yang sudah ada

### 2. **Model ServiceSchedule** ✅

- ✅ Tambah `interval_value`, `last_service_mileage`, `last_service_date` ke `$fillable`
- ✅ Tambah casting untuk kolom baru
- ✅ Tambah accessor `getIntervalValueAttribute()` untuk auto-calculate jika value = 0

### 3. **Request Validation** ✅

- ✅ `StoreServiceScheduleRequest`: Tambah validasi untuk kolom baru
- ✅ `UpdateServiceScheduleRequest`: Tambah validasi untuk kolom baru

### 4. **Controller (ServiceScheduleController)** ✅

- ✅ **store()**: Auto-calculate `interval_value` saat create
- ✅ **index()**: Accessor otomatis calculate interval_value jika 0
- ✅ **update()**: Recalculate `interval_value` saat update target/last service

### 5. **API Resource (ServiceScheduleResource)** ✅

- ✅ Include `interval_value`, `last_service_mileage`, `last_service_date` di response

---

## 🚀 Cara Menjalankan Perbaikan

### Step 1: Run Migrations

Jalankan perintah berikut di terminal Laravel:

```bash
php artisan migrate
```

Output yang diharapkan:

```
Migrating: 2026_02_22_000001_add_interval_columns_to_service_schedules_table
Migrated:  2026_02_22_000001_add_interval_columns_to_service_schedules_table (XX.XXms)
Migrating: 2026_02_22_000002_fix_service_schedule_interval_values
Migrated:  2026_02_22_000002_fix_service_schedule_interval_values (XX.XXms)
```

### Step 2: Verifikasi Database

Cek struktur tabel dan data:

```sql
-- 1. Cek struktur tabel
DESCRIBE service_schedules;

-- 2. Cek data interval_value sudah terisi
SELECT
    id,
    service_name,
    schedule_type,
    interval_value,
    last_service_mileage,
    target_km,
    last_service_date,
    target_date
FROM service_schedules
LIMIT 10;

-- 3. Verifikasi tidak ada interval_value = 0 untuk schedule aktif
SELECT COUNT(*) as zero_intervals
FROM service_schedules
WHERE (interval_value = 0 OR interval_value IS NULL)
  AND is_active = 1;
-- Harusnya: 0
```

### Step 3: Test API dengan Postman

#### Test 1: GET Service Schedules

```http
GET http://localhost:8000/api/v1/motorcycle/service-schedules?vehicle_id=13
Authorization: Bearer {access_token}
```

**Expected Response:**

```json
{
  "data": [
    {
      "id": 1,
      "service_name": "Ganti Oli",
      "schedule_type": "km",
      "interval_value": 1000,  // ✅ HARUS > 0
      "last_service_mileage": 0,
      "target_km": 1000,
      "last_service_date": null,
      "target_date": null,
      ...
    }
  ]
}
```

#### Test 2: POST Create Schedule

```http
POST http://localhost:8000/api/v1/motorcycle/service-schedules
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "vehicle_id": 13,
  "service_type_id": 1,
  "service_name": "Ganti Oli Mesin",
  "schedule_type": "km",
  "interval_value": 1000,
  "last_service_mileage": 0,
  "target_km": 1000,
  "reminder_option_id": 1,
  "notes": "Setiap 1000 km"
}
```

**Expected Response:**

```json
{
  "success": true,
  "message": "Service schedule created successfully.",
  "data": {
    "id": 2,
    "service_name": "Ganti Oli Mesin",
    "schedule_type": "km",
    "interval_value": 1000,  // ✅ HARUS ADA
    "last_service_mileage": 0,
    "target_km": 1000,
    ...
  }
}
```

#### Test 3: POST Create Schedule (Auto-calculate)

Test dengan TIDAK mengirim `interval_value`, backend harus auto-calculate:

```http
POST http://localhost:8000/api/v1/motorcycle/service-schedules
Content-Type: application/json

{
  "vehicle_id": 13,
  "service_type_id": 2,
  "service_name": "Service Berkala",
  "schedule_type": "km",
  "last_service_mileage": 500,
  "target_km": 1500,
  "reminder_option_id": 1
}
```

**Expected Response:** `interval_value` = 1000 (1500 - 500)

### Step 4: Test di Flutter Mobile App

1. **Buka app** dan masuk ke halaman **Jadwal Servis**
2. **Verifikasi tampilan**:
    - ✅ Interval Servis: "Setiap 1000 km" (bukan "Setiap 0 km")
    - ✅ Persentase progress muncul dengan benar (misal: 15%)
    - ✅ Progress bar terisi sesuai persentase

3. **Buat jadwal baru**:
    - Pilih tipe: Jarak (km)
    - Terakhir servis: 0 km
    - Target: 1000 km
    - **Expected**: Interval otomatis = 1000 km

4. **Update odometer** kendaraan
    - Masukkan odometer baru (misal: 150 km)
    - **Expected**: Progress di jadwal servis berubah ke 15%

---

## 🧪 Testing Checklist

- [ ] Migration berhasil dijalankan tanpa error
- [ ] Database memiliki kolom baru: `interval_value`, `last_service_mileage`, `last_service_date`
- [ ] Data lama sudah memiliki `interval_value > 0`
- [ ] API GET `/service-schedules` mengembalikan `interval_value > 0`
- [ ] API POST `/service-schedules` auto-calculate jika `interval_value` tidak dikirim
- [ ] API POST `/service-schedules` accept manual `interval_value`
- [ ] API PUT `/service-schedules/{id}` recalculate saat target/last diubah
- [ ] Mobile app menampilkan "Setiap X km" bukan "Setiap 0 km"
- [ ] Mobile app menampilkan persentase progress dengan benar

---

## 📊 Auto-calculation Logic

### For Mileage-based (`schedule_type = 'km'`)

```
interval_value = target_km - last_service_mileage

Example:
- last_service_mileage: 0 km
- target_km: 1000 km
- interval_value: 1000 km ✅
```

### For Time-based (`schedule_type = 'time'`)

```
interval_value = DATEDIFF(target_date, last_service_date)

Example:
- last_service_date: 2026-02-01
- target_date: 2026-05-01
- interval_value: 89 days ✅
```

---

## 🐛 Troubleshooting

### Problem: Migration error "column already exists"

**Solution:**

```bash
# Rollback dan re-run
php artisan migrate:rollback --step=1
php artisan migrate
```

### Problem: Existing data masih punya interval_value = 0

**Solution:**

```sql
-- Run manual SQL fix
UPDATE service_schedules
SET interval_value = target_km - COALESCE(last_service_mileage, 0)
WHERE schedule_type = 'km'
  AND (interval_value = 0 OR interval_value IS NULL);
```

### Problem: API masih return interval_value = 0

**Cek:**

1. Cache mungkin masih aktif → Restart server: `php artisan serve`
2. Clear Laravel cache: `php artisan cache:clear`
3. Cek database langsung apakah data sudah benar

---

## 📁 Files Modified

1. `database/migrations/2026_02_22_000001_add_interval_columns_to_service_schedules_table.php` ✅ NEW
2. `database/migrations/2026_02_22_000002_fix_service_schedule_interval_values.php` ✅ NEW
3. `app/Models/ServiceSchedule.php` ✅ UPDATED
4. `app/Http/Controllers/Api/ServiceScheduleController.php` ✅ UPDATED
5. `app/Http/Requests/StoreServiceScheduleRequest.php` ✅ UPDATED
6. `app/Http/Requests/UpdateServiceScheduleRequest.php` ✅ UPDATED
7. `app/Http/Resources/ServiceScheduleResource.php` ✅ UPDATED

---

**Last Updated**: February 22, 2026  
**Status**: ✅ Ready to Deploy  
**Impact**: HIGH - Fixes core schedule tracking functionality

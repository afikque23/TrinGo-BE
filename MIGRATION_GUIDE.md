# 🔧 Panduan Migration & Testing

## 📌 Issue yang Diperbaiki

**Error:** `Column not found: service_histories.service_type_id`

**Root Cause:** Tabel `service_histories` menggunakan kolom `service_type` (string) untuk menyimpan nama jenis service, bukan menggunakan foreign key `service_type_id` ke tabel `service_types`.

---

## ✅ Solusi yang Diimplementasikan

### 1. **Migration File**

📁 `database/migrations/2024_01_01_000012_add_service_type_id_to_service_histories.php`

**Fitur:**

- ✅ Menambahkan kolom `service_type_id` (nullable, foreign key)
- ✅ Auto-migrate data lama: matching `service_type` string dengan `service_types.name`
- ✅ Index untuk performa query
- ✅ Constraint `restrict` untuk mencegah delete service type yang masih digunakan

**Catatan:** Kolom `service_type` (string) tetap ada untuk backward compatibility.

---

### 2. **Model Updates**

**📁 `app/Models/ServiceHistory.php`**

- ✅ Tambah `service_type_id` ke `$fillable`
- ✅ Tambah relasi `serviceType()` → belongsTo ServiceType

**📁 `app/Models/ServiceType.php`**

- ✅ Sudah ada relasi `services()` → hasMany ServiceHistory

---

### 3. **Controller Fix**

**📁 `app/Http/Controllers/Admin/FilterController.php`**

- ✅ Tambah try-catch untuk handle `withCount('services')` jika kolom belum ada
- ✅ Setelah migration dijalankan, counting akan berfungsi normal

---

### 4. **Postman Collection Update**

**📁 `Motorcycle_Management_API.postman_collection.json`**

**Service Types:**

- ✅ Fix endpoint toggle: `POST toggle-active` → `PATCH toggle-status`
- ✅ Update semua request sesuai dokumentasi API

**Reminder Options (NEW):**

- ✅ 1. Get Active Reminder Options (Mobile) ⭐
- ✅ 2. Get All Reminder Options (Admin)
- ✅ 3. Create Reminder Option
- ✅ 4. Get Reminder Option Detail
- ✅ 5. Update Reminder Option
- ✅ 6. Toggle Status
- ✅ 7. Delete Reminder Option

---

## 🚀 Langkah-Langkah Eksekusi

### **Step 1: Run Migration**

```bash
php artisan migrate
```

**Output yang diharapkan:**

```
Migrating: 2024_01_01_000012_add_service_type_id_to_service_histories
Migrated:  2024_01_01_000012_add_service_type_id_to_service_histories (X ms)
```

**Apa yang dilakukan migration ini:**

1. Menambah kolom `service_type_id` ke tabel `service_histories`
2. Otomatis mengisi `service_type_id` berdasarkan matching `service_type` string → `service_types.name`
3. Contoh:
    - `service_histories.service_type = "Ganti Oli"` → akan di-set `service_type_id = 1` (jika `service_types.id = 1, name = "Ganti Oli"`)

---

### **Step 2: Verify Database**

```sql
-- Cek struktur tabel
DESCRIBE service_histories;

-- Cek apakah data sudah di-migrate
SELECT
    id,
    service_type,
    service_type_id
FROM service_histories
LIMIT 10;

-- Cek relasi dengan join
SELECT
    sh.id,
    sh.service_type AS old_string,
    st.name AS new_relation,
    sh.service_type_id
FROM service_histories sh
LEFT JOIN service_types st ON sh.service_type_id = st.id
LIMIT 10;
```

**Expected Result:**

- Kolom `service_type_id` sudah ada ✅
- Records yang match sudah punya `service_type_id` (not null) ✅
- Records yang tidak match masih null (jika ada service_type string yang tidak ada di master data)

---

### **Step 3: Test Web Admin**

1. **Buka halaman Filter Management:**

    ```
    http://127.0.0.1:8000/admin/filters?tab=service-types
    ```

2. **Verify:**
    - ✅ Tidak ada error SQL
    - ✅ List service types ditampilkan
    - ✅ Kolom "Jumlah Digunakan" menampilkan angka (setelah migration)

3. **Test CRUD:**
    - ✅ Tambah service type baru
    - ✅ Edit service type
    - ✅ Toggle status aktif/non-aktif
    - ✅ Hapus service type (cek proteksi: tidak bisa hapus jika masih digunakan)

---

### **Step 4: Test API dengan Postman**

#### **Import Postman Collection:**

1. Buka Postman
2. Import file: `Motorcycle_Management_API.postman_collection.json`
3. Import environment: `Motorcycle_Management_Local.postman_environment.json`

#### **Test Service Types API:**

1. **Login dulu:**
    - Run: `Authentication → 2. Login`
    - Access token akan auto-save ke environment

2. **Get Active Service Types (Mobile):**

    ```
    GET /api/service-types?active=1
    ```

    - ✅ Response hanya service type yang `is_active = true`
    - ✅ Data: id, name, description, services_count

3. **Create Service Type:**

    ```
    POST /api/service-types
    {
      "name": "Ganti CVT",
      "description": "Penggantian CVT dan komponennya",
      "is_active": true
    }
    ```

    - ✅ Response 201 Created
    - ✅ Service type ID auto-save ke environment variable

4. **Toggle Status:**

    ```
    PATCH /api/service-types/{id}/toggle-status
    ```

    - ✅ Status berubah dari active → inactive atau sebaliknya

5. **Delete (Protection Test):**

    ```
    DELETE /api/service-types/1
    ```

    - ✅ Jika ID 1 (Ganti Oli) masih digunakan → Response 422
    - ✅ Message: "Jenis service tidak dapat dihapus karena masih digunakan"

#### **Test Reminder Options API:**

1. **Get Active Reminder Options (Mobile):**

    ```
    GET /api/reminder-options?active=1&unit=km
    ```

    - ✅ Response hanya reminder options dengan unit km

2. **Create Reminder Option:**

    ```
    POST /api/reminder-options
    {
      "label": "100 km sebelum",
      "value": 100,
      "unit": "km",
      "is_active": true
    }
    ```

    - ✅ Response 201 Created

3. **Create Duplicate (Validation Test):**

    ```
    POST /api/reminder-options
    {
      "label": "100 km lagi",
      "value": 100,
      "unit": "km"
    }
    ```

    - ✅ Response 422
    - ✅ Message: "Kombinasi nilai dan unit pengingat sudah ada."

4. **Test Invalid Unit:**

    ```
    POST /api/reminder-options
    {
      "value": 50,
      "unit": "meters"
    }
    ```

    - ✅ Response 422
    - ✅ Message: "Unit pengingat harus km atau days."

---

## 🎯 Expected Results

### **Database:**

- ✅ Kolom `service_type_id` ada di tabel `service_histories`
- ✅ Foreign key constraint aktif
- ✅ Data lama sudah di-migrate (jika nama match)

### **Web Admin:**

- ✅ Halaman Filter Management tidak error
- ✅ Tab "Jenis Service" menampilkan data dengan counter usage
- ✅ CRUD berfungsi normal
- ✅ Delete protection berfungsi

### **API:**

- ✅ Semua endpoint Service Types berfungsi
- ✅ Semua endpoint Reminder Options berfungsi
- ✅ Validation rules berjalan (unique, enum unit, positive value)
- ✅ Toggle status endpoint menggunakan PATCH (bukan POST)
- ✅ Response format konsisten dengan `ApiResponse` trait

---

## 📊 Data Migration Notes

**Jika ada service_type string yang TIDAK MATCH dengan master data:**

```sql
-- Cek records yang tidak ter-migrate
SELECT DISTINCT service_type
FROM service_histories
WHERE service_type_id IS NULL;
```

**Solusi:**

1. Tambahkan service type yang missing ke tabel `service_types`
2. Re-run migration update query atau manual update:

```sql
UPDATE service_histories sh
INNER JOIN service_types st ON sh.service_type = st.name
SET sh.service_type_id = st.id
WHERE sh.service_type_id IS NULL;
```

---

## 🔄 Backward Compatibility

**Kolom `service_type` (string) tetap ada** untuk:

- ✅ Backward compatibility dengan mobile app versi lama
- ✅ Data historis yang tidak match dengan master data
- ✅ Migrasi bertahap (tidak breaking)

**Future Plan:**

1. Mobile app update: gunakan `service_type_id` + relasi
2. Setelah semua data ter-migrate → bisa set `service_type_id` jadi NOT NULL
3. Deprecate kolom `service_type` (string)

---

## 🐛 Troubleshooting

### **Error: Migration fails**

```
SQLSTATE[23000]: Integrity constraint violation
```

**Solusi:** Ada data di `service_histories` yang `service_type` tidak ada di `service_types.name`

```sql
-- Tambahkan service types yang missing
INSERT INTO service_types (name, description, created_at, updated_at)
SELECT DISTINCT service_type, '', NOW(), NOW()
FROM service_histories
WHERE service_type NOT IN (SELECT name FROM service_types);
```

### **Error: withCount still fails**

**Solusi:** Clear cache dan restart server

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

---

## ✅ Checklist Completion

- [x] Migration file created
- [x] ServiceHistory model updated
- [x] ServiceType model relationship verified
- [x] FilterController fixed with try-catch
- [x] Postman collection updated (Service Types)
- [x] Postman collection updated (Reminder Options - NEW)
- [x] Documentation updated

---

## 📞 Support

Jika masih ada error setelah menjalankan langkah-langkah di atas:

1. Share error message lengkap
2. Share hasil query verification database
3. Share log Laravel di `storage/logs/laravel.log`

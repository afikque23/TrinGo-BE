# 📮 Postman Collection - Vehicle Management API

## 🎯 Yang Sudah Ditambahkan

### ✅ Folder "Vehicles" dengan 10 Request:

1. **Get All Vehicles** - Ambil semua kendaraan user
2. **Create Vehicle** - Tambah kendaraan baru (dengan upload foto)
3. **Get Vehicle Detail** - Detail kendaraan spesifik
4. **Update Vehicle** - Update data kendaraan
5. **Delete Vehicle** - Hapus kendaraan
6. **🆕 Set Primary Vehicle** - Set kendaraan utama (motor harian)
7. **🆕 Get Primary Vehicle** - Ambil kendaraan utama
8. **Create Matic Vehicle** - Contoh tambah motor matic
9. **Create Manual Vehicle** - Contoh tambah motor manual
10. **Create Sport Vehicle** - Contoh tambah motor sport

### 🔧 Features:

-   ✅ Auto-save token setelah login
-   ✅ Auto-save vehicle_id setelah create
-   ✅ Auto-save primary_vehicle_id setelah set primary
-   ✅ **🆕 Auto-validation vehicle_id** sebelum set primary
-   ✅ **🆕 Error prevention** jika vehicle_id kosong
-   ✅ Auto-test service intervals generation
-   ✅ Support upload foto (multipart/form-data)
-   ✅ Proper base URL: `http://localhost:8000/api/v1/motorcycle`

---

## 📥 Cara Import ke Postman

### 1. Import Collection

1. Buka Postman
2. Klik **Import** (tombol di kiri atas)
3. Pilih **File** tab
4. Drag & drop atau browse file: `Motorcycle_Management_API.postman_collection.json`
5. Klik **Import**

### 2. Import Environment

1. Klik **Import** lagi
2. Pilih **File** tab
3. Drag & drop atau browse file: `Motorcycle_Management_Local.postman_environment.json`
4. Klik **Import**

### 3. Aktifkan Environment

1. Di kanan atas Postman, ada dropdown environment
2. Pilih **"Motorcycle Management - Local"**
3. Environment aktif ditandai dengan warna hijau

---

## 🚀 Cara Menggunakan

### Step 1: Login

1. Buka folder **Auth**
2. Jalankan request **"3. Login"**
3. Body sudah terisi:
    ```json
    {
        "email": "test@example.com",
        "password": "password123"
    }
    ```
4. Klik **Send**
5. Token otomatis tersimpan di environment variable `{{token}}`

### Step 2: Tambah Kendaraan

1. Buka folder **Vehicles**
2. Jalankan request **"2. Create Vehicle"**
3. Di tab **Body**, field sudah terisi contoh data
4. (Optional) Untuk upload foto:
    - Klik field `photo`
    - Klik **Select Files**
    - Pilih foto kendaraan Anda
5. Klik **Send**
6. Vehicle ID otomatis tersimpan di environment variable `{{vehicle_id}}`

### Step 3: Lihat Daftar Kendaraan

1. Jalankan request **"1. Get All Vehicles"**
2. Klik **Send**
3. Response menampilkan semua kendaraan dengan service intervals

### Step 4: Lihat Detail Kendaraan

1. Jalankan request **"3. Get Vehicle Detail"**
2. URL otomatis menggunakan `{{vehicle_id}}` dari step 2
3. Klik **Send**
4. Response menampilkan detail lengkap + relasi (service history, fuel logs, reminders)

### Step 5: Update Kendaraan

1. Jalankan request **"4. Update Vehicle"**
2. Di tab **Body**, ubah field yang ingin diupdate (contoh: odometer, notes)
3. Klik **Send**
4. Response menampilkan data ter-update

### Step 6: Pilih Kendaraan Aktif (FITUR BARU) 🎯

1. Jalankan request **"6. Select Active Vehicle"**
2. URL menggunakan `{{vehicle_id}}` (dari vehicle yang sudah dibuat)
3. Klik **Send**
4. Response menampilkan kendaraan yang dipilih
5. Kendaraan ini akan menjadi konteks untuk semua fitur lain (dashboard, tracking, dll)

💡 **Konsep Active Vehicle:**

-   User bisa punya banyak motor, tapi hanya 1 yang aktif
-   Motor aktif digunakan untuk semua fitur tanpa perlu kirim vehicle_id lagi
-   Frontend tidak perlu tracking motor mana yang sedang dipakai

### Step 7: Cek Kendaraan Aktif (FITUR BARU) 🎯

1. Jalankan request **"7. Get Active Vehicle"**
2. Klik **Send**
3. Response menampilkan data lengkap motor yang sedang aktif:
    - Detail kendaraan
    - Service intervals (perawatan mendatang)
    - Service histories (5 terakhir)
    - Fuel logs (5 terakhir)
    - Reminders (yang belum selesai)

💡 **Use Case:**

-   Saat user buka aplikasi → call endpoint ini
-   Jika belum ada active vehicle → redirect ke vehicle selector
-   Jika sudah ada → langsung tampilkan dashboard dengan data motor aktif

### Step 8: Hapus Kendaraan

1. Jalankan request **"5. Delete Vehicle"**
2. Klik **Send**
3. Response sukses menunjukkan kendaraan terhapus

---

## 🎨 Test Different Motor Types

### Test Matic (4 Service Intervals)

-   Jalankan request **"8. Create Matic Vehicle"**
-   Otomatis generate:
    -   Ganti Oli: 2000 km
    -   Cek Rem: 3000 km
    -   Ganti Oli CVT: 5000 km
    -   Servis Besar: 10000 km

### Test Manual (4 Service Intervals)

-   Jalankan request **"9. Create Manual Vehicle"**
-   Otomatis generate:
    -   Ganti Oli: 2500 km
    -   Cek Rem: 3500 km
    -   Ganti Oli Gardan: 6000 km
    -   Servis Besar: 12000 km

### Test Sport (5 Service Intervals)

-   Jalankan request **"10. Create Sport Vehicle"**
-   Otomatis generate:
    -   Ganti Oli: 3000 km
    -   Cek Rem: 4000 km
    -   Tune Up Mesin: 5000 km
    -   Ganti Oli Gardan: 7000 km
    -   Servis Besar: 15000 km

---

## 📊 Environment Variables

Collection menggunakan variable berikut:

| Variable                | Description                 | Auto-set          |
| ----------------------- | --------------------------- | ----------------- |
| `{{base_url}}`          | API base URL                | ❌ Manual         |
| `{{token}}`             | JWT access token            | ✅ After login    |
| `{{vehicle_id}}`        | Last created vehicle ID     | ✅ After create   |
| `{{active_vehicle_id}}` | Currently active vehicle ID | ✅ After select   |
| `{{otp}}`               | Email verification OTP      | ✅ After register |
| `{{test_email}}`        | Test email address          | ❌ Manual         |

---

## 🔍 Cara Melihat Environment Variables

1. Klik icon **Environment** (👁️) di kanan atas
2. Pilih **"Motorcycle Management - Local"**
3. Lihat semua variables dan nilainya
4. Anda bisa edit manual jika diperlukan

---

## 🧪 Testing Features

### Auto-test Scripts

Request **Create Matic/Manual/Sport Vehicle** memiliki test otomatis:

```javascript
// Test jumlah service intervals
pm.test("Has 4 service intervals for matic", function () {
    pm.expect(response.data.service_intervals).to.have.lengthOf(4);
});
```

Lihat hasil test di tab **Test Results** setelah send request.

---

## 📝 Tips & Tricks

### 1. Copy Token Manual

Jika token tidak auto-save:

1. Jalankan login request
2. Copy nilai `token` dari response
3. Klik icon **Environment** (👁️)
4. Paste ke field `token`

### 2. Upload Multiple Photos

Untuk test upload foto berbeda:

1. Buat duplicate request (klik kanan → Duplicate)
2. Ganti foto di field `photo`
3. Jalankan request

### 3. Test Error Cases

Edit request body untuk test validation:

-   Hapus field `title` → Error: "Nama kendaraan wajib diisi"
-   Ganti `tipe_motor` jadi `"automatic"` → Error: "Tipe motor harus..."
-   Upload file > 5MB → Error: "Ukuran gambar maksimal 5MB"

### 4. Quick Run All Requests

1. Klik folder **Vehicles**
2. Klik **Run** (tombol di kanan)
3. Pilih requests yang ingin dijalankan
4. Klik **Run Vehicles**
5. Lihat hasil batch test

---

## 🐛 Troubleshooting

### Error: "Unauthenticated"

**Solusi:**

1. Login dulu di folder **Auth → 3. Login**
2. Pastikan environment **"Motorcycle Management - Local"** aktif
3. Cek variable `{{token}}` ada isinya

### Error: "Vehicle not found"

**Solusi:**

1. Pastikan sudah create vehicle terlebih dahulu
2. Cek variable `{{vehicle_id}}` ada isinya
3. Atau ganti `{{vehicle_id}}` dengan ID manual (contoh: `1`)

### Error: "Base URL not found"

**Solusi:**

1. Pastikan environment **aktif** (dropdown di kanan atas)
2. Cek variable `{{base_url}}` = `http://localhost:8000/api/v1/motorcycle`

### Photo Upload Tidak Berfungsi

**Solusi:**

1. Pastikan request menggunakan **Body → form-data**
2. Field `photo` type harus **File** (bukan Text)
3. Pastikan file format: JPEG, JPG, PNG, WEBP
4. Pastikan ukuran < 5MB

---

## 📚 Request Structure

### GET Requests

```
GET {{base_url}}/vehicles
Authorization: Bearer {{token}}
Accept: application/json
```

### POST Requests (form-data)

```
POST {{base_url}}/vehicles
Authorization: Bearer {{token}}
Accept: application/json
Content-Type: multipart/form-data

Body:
- title: Honda Beat 2023
- tipe_motor: matic
- photo: [file]
```

### PUT Requests (form-data with \_method)

```
POST {{base_url}}/vehicles/{{vehicle_id}}
Authorization: Bearer {{token}}
Accept: application/json
Content-Type: multipart/form-data

Body:
- _method: PUT
- odometer: 7500
```

### DELETE Requests

```
DELETE {{base_url}}/vehicles/{{vehicle_id}}
Authorization: Bearer {{token}}
Accept: application/json
```

---

## ✅ Expected Responses

### Success (201 Created)

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

### Error (422 Validation)

```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "title": ["Nama kendaraan wajib diisi"]
    }
}
```

### Error (404 Not Found)

```json
{
    "success": false,
    "message": "Kendaraan tidak ditemukan"
}
```

---

## 🎯 Testing Checklist

-   [ ] Import collection successfully
-   [ ] Import environment successfully
-   [ ] Environment aktif (hijau di kanan atas)
-   [ ] Login berhasil (token tersimpan)
-   [ ] Create matic vehicle (4 intervals)
-   [ ] Create manual vehicle (4 intervals)
-   [ ] Create sport vehicle (5 intervals)
-   [ ] Upload foto berhasil
-   [ ] Get all vehicles
-   [ ] Get vehicle detail
-   [ ] Update vehicle
-   [ ] Delete vehicle
-   [ ] **🆕 Select active vehicle**
-   [ ] **🆕 Get active vehicle data**
-   [ ] Test validation error
-   [ ] Test unauthorized error

---

## 🎯 Vehicle Selector Testing Scenario

### Scenario 1: First Time User (Belum Ada Active Vehicle)

1. **Login** → Token tersimpan
2. **Get Active Vehicle** → Response 404 "Belum ada kendaraan aktif"
3. **Create Vehicle** → Motor baru dibuat, vehicle_id tersimpan
4. **Select Active Vehicle** → Motor dipilih sebagai aktif
5. **Get Active Vehicle** → Response 200 dengan data lengkap motor

### Scenario 2: Existing User (Sudah Ada Active Vehicle)

1. **Login** → Token tersimpan
2. **Get Active Vehicle** → Response 200 dengan motor yang sedang aktif
3. **Get All Vehicles** → Tampilkan list semua motor
4. **Select Active Vehicle** (pilih motor lain) → Ganti motor aktif
5. **Get Active Vehicle** → Response 200 dengan motor baru

### Scenario 3: Hapus Active Vehicle

1. **Get Active Vehicle** → Response 200 dengan motor aktif (id: 5)
2. **Delete Vehicle** (id: 5) → Hapus motor aktif
3. **Get Active Vehicle** → Response 404 "Kendaraan aktif tidak ditemukan"
4. **Select Active Vehicle** (pilih motor lain) → Set motor baru

### Scenario 4: Security Test (Coba Pilih Motor Orang Lain)

1. **Login User A** → Token A tersimpan
2. **Create Vehicle** → Vehicle ID: 10 (milik User A)
3. **Login User B** → Token B tersimpan (replace token A)
4. **Select Active Vehicle** (id: 10) → Response 404 "Kendaraan tidak ditemukan atau bukan milik Anda"
5. ✅ **Security works!** User B tidak bisa pilih motor milik User A

---

## 💡 Tips & Best Practices

### 1. Test Flow yang Realistis

```
Login → Get Active Vehicle → Jika 404 → Get All Vehicles → Select Active Vehicle
```

### 2. Gunakan Collection Runner

Untuk test otomatis:

1. Klik **Collections** > **Run collection**
2. Pilih requests yang mau dijalankan berurutan
3. Atur delay antar request (500ms recommended)
4. Klik **Run**

### 3. Duplikat Environment untuk Multiple Users

1. Klik environment → Duplicate
2. Rename: "User A", "User B"
3. Test multi-user scenario dengan ganti environment

### 4. Monitor Console Log

Di Postman Console (View → Show Postman Console):

-   Token yang tersimpan
-   Vehicle ID yang tersimpan
-   Active vehicle ID yang tersimpan
-   Response data

---

**Happy Testing! 🚀**

Jika ada pertanyaan atau issue, cek dokumentasi lengkap di:

-   `VEHICLE_SELECTOR_API.md` - **🆕 Dokumentasi lengkap Vehicle Selector**
-   `VEHICLE_API_GUIDE.md`
-   `QUICK_TEST.md`
-   `SETUP_TESTING_GUIDE.md`

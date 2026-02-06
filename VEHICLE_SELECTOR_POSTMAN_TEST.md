# 🧪 Vehicle Selector - Postman Quick Test

## ⚡ Quick Test Flow (5 Menit)

### 1️⃣ Login & Get Token

```
Request: Auth > 3. Login
Method: POST
Body: ✅ Sudah terisi
Action: Send
Result: Token auto-saved ke {{token}}
```

### 2️⃣ Cek Active Vehicle (Pertama Kali)

```
Request: Vehicles > 7. Get Active Vehicle
Method: GET
Action: Send
Expected: ❌ 404 "Belum ada kendaraan aktif"
```

### 3️⃣ Lihat Daftar Kendaraan

```
Request: Vehicles > 1. Get All Vehicles
Method: GET
Action: Send
Expected: ✅ List semua motor user (bisa kosong)
```

### 4️⃣ Buat Kendaraan Baru

```
Request: Vehicles > 2. Create Vehicle
Method: POST
Body: ✅ Sudah terisi
Action: Send
Result: Vehicle ID auto-saved ke {{vehicle_id}}
```

### 5️⃣ Pilih Kendaraan Aktif

```
Request: Vehicles > 6. Select Active Vehicle
Method: POST
URL: /vehicles/{{vehicle_id}}/select
Action: Send
Expected: ✅ 200 dengan data motor + service intervals
Result: Active vehicle ID auto-saved ke {{active_vehicle_id}}
```

### 6️⃣ Verifikasi Active Vehicle

```
Request: Vehicles > 7. Get Active Vehicle
Method: GET
Action: Send
Expected: ✅ 200 dengan data lengkap motor aktif
Response includes:
- Vehicle details
- Service intervals
- Service histories (5 terakhir)
- Fuel logs (5 terakhir)
- Reminders
```

---

## 🎯 Test Scenarios

### Scenario A: Happy Path (User Pertama Kali)

1. ✅ Login
2. ❌ Get Active Vehicle → 404 (belum ada)
3. ✅ Create Vehicle → Motor baru
4. ✅ Select Active Vehicle → Set motor aktif
5. ✅ Get Active Vehicle → Data lengkap

**Time:** ~2 menit

---

### Scenario B: Switch Active Vehicle

1. ✅ Get Active Vehicle → Motor A aktif
2. ✅ Get All Vehicles → List semua motor
3. ✅ Create Vehicle → Buat motor B
4. ✅ Select Active Vehicle (motor B) → Ganti motor aktif
5. ✅ Get Active Vehicle → Motor B sekarang aktif

**Time:** ~2 menit

---

### Scenario C: Delete Active Vehicle

1. ✅ Get Active Vehicle → Motor aktif (id: X)
2. ✅ Delete Vehicle (id: X) → Hapus motor aktif
3. ❌ Get Active Vehicle → 404 (motor dihapus)
4. ✅ Select Active Vehicle (motor lain) → Set motor baru
5. ✅ Get Active Vehicle → Motor baru aktif

**Time:** ~2 menit

---

### Scenario D: Security Test (Multi User)

**User A:**

1. ✅ Login (email: test@example.com)
2. ✅ Create Vehicle → Vehicle ID: 10
3. 📝 Copy token A & vehicle ID

**User B:**

1. ✅ Login (email: test2@example.com)
2. 📝 Copy token B
3. ❌ Select Active Vehicle (id: 10) → 404 "Bukan milik Anda"
4. ✅ **Security works!**

**Time:** ~3 menit

---

## 📋 Response Examples

### ✅ Success: Select Active Vehicle

```json
{
    "success": true,
    "message": "Kendaraan aktif berhasil dipilih",
    "data": {
        "id": 3,
        "title": "Honda Vario 125",
        "make": "Honda",
        "model": "Vario 125",
        "year": 2023,
        "tipe_motor": "matic",
        "odometer": 5000,
        "license_plate": "B 1234 XYZ",
        "service_intervals": [
            {
                "id": 1,
                "service_name": "Ganti Oli Mesin",
                "next_due_km": 6000,
                "is_active": true
            }
        ]
    }
}
```

### ✅ Success: Get Active Vehicle

```json
{
  "success": true,
  "message": "Data kendaraan aktif berhasil diambil",
  "data": {
    "id": 3,
    "title": "Honda Vario 125",
    "odometer": 5000,
    "service_intervals": [...],
    "service_histories": [...],
    "fuel_logs": [...],
    "reminders": [...]
  }
}
```

### ❌ Error: No Active Vehicle

```json
{
    "success": false,
    "message": "Belum ada kendaraan aktif. Silakan pilih kendaraan terlebih dahulu."
}
```

### ❌ Error: Not Your Vehicle

```json
{
    "success": false,
    "message": "Kendaraan tidak ditemukan atau bukan milik Anda"
}
```

### ❌ Error: Active Vehicle Deleted

```json
{
    "success": false,
    "message": "Kendaraan aktif tidak ditemukan. Silakan pilih kendaraan lain."
}
```

---

## 🔍 Environment Variables Check

Setelah test, verifikasi variable tersimpan:

```
{{token}} = eyJ0eXAiOiJKV1QiLCJhbGc...
{{vehicle_id}} = 3
{{active_vehicle_id}} = 3
```

**Cara cek:**

1. Klik icon 👁️ (Environment) di kanan atas
2. Pilih "Motorcycle Management - Local"
3. Scroll ke variable yang ingin dicek

---

## 🐛 Troubleshooting

### Problem: 401 Unauthorized

**Solution:**

-   Token expired atau invalid
-   Re-login untuk get token baru
-   Check environment variable {{token}}

### Problem: Route /vehicles/active return 404

**Solution:**

-   Check route order di `routes/api.php`
-   Route `/vehicles/active` harus SEBELUM `apiResource`
-   Run: `php artisan route:list --path=vehicles`

### Problem: Cannot select vehicle (404)

**Solution:**

-   Pastikan vehicle milik user yang login
-   Check {{vehicle_id}} ada di environment
-   Verify dengan "Get All Vehicles"

### Problem: Auto-save tidak jalan

**Solution:**

-   Check Scripts tab di request
-   Pastikan ada "Test" script
-   Lihat Postman Console (View > Show Postman Console)

---

## ✅ Checklist Lengkap

### Setup

-   [ ] Import Postman collection
-   [ ] Import environment file
-   [ ] Activate environment (hijau di kanan atas)
-   [ ] Run health check endpoint

### Vehicle Selector Tests

-   [ ] Login & token tersimpan
-   [ ] Get active vehicle (first time) → 404
-   [ ] Create vehicle
-   [ ] Select active vehicle → 200
-   [ ] Verify active vehicle → 200 dengan data lengkap
-   [ ] Switch active vehicle
-   [ ] Delete active vehicle
-   [ ] Multi-user security test

### Validation

-   [ ] Select non-existent vehicle → 404
-   [ ] Select other user's vehicle → 404
-   [ ] Get active after deletion → 404

---

## 🎓 Pro Tips

### 1. Use Collection Runner

Test semua sekaligus:

```
Collections > Run > Select requests > Run
```

### 2. Check Console

Monitor auto-saved variables:

```
View > Show Postman Console
```

### 3. Duplicate Environment

Test multi-user:

```
Environment > Duplicate > Rename "User A", "User B"
```

### 4. Export Results

Save test results:

```
Runner > Run Complete > Export Results
```

---

## 📞 Need Help?

**Documentation:**

-   `VEHICLE_SELECTOR_API.md` - Full API documentation
-   `POSTMAN_GUIDE.md` - Complete Postman guide
-   `VEHICLE_API_GUIDE.md` - Vehicle API details

**Commands:**

```bash
# Check routes
php artisan route:list --path=vehicles

# Check database
php artisan tinker
>>> User::find(1)->activeVehicle

# Clear cache
php artisan cache:clear
php artisan config:clear
```

---

**Happy Testing! 🚀**

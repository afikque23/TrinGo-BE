# 🧪 Quick API Test - Vehicle Management

## Test Credentials

```
Email: test@example.com
Password: password123
```

## Quick Test Commands (cURL)

### 1. Login & Get Token

```bash
curl -X POST http://localhost:8000/api/v1/motorcycle/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"test@example.com\",\"password\":\"password123\"}"
```

**Expected Response:**

```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": { ... },
    "access_token": "YOUR_TOKEN_HERE",
    "token_type": "Bearer"
  }
}
```

**Copy the `access_token` for next requests!**

---

### 2. Create Vehicle (with all fields)

```bash
curl -X POST http://localhost:8000/api/v1/motorcycle/vehicles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "title=Honda Beat 2023" \
  -F "make=Honda" \
  -F "model=Beat" \
  -F "year=2023" \
  -F "tipe_motor=matic" \
  -F "odometer=5000" \
  -F "license_plate=B 1234 XYZ" \
  -F "color=Merah" \
  -F "notes=Motor pribadi sehari-hari"
```

**Expected Response:**

```json
{
  "success": true,
  "message": "Kendaraan berhasil ditambahkan",
  "data": {
    "id": 1,
    "title": "Honda Beat 2023",
    "tipe_motor": "matic",
    "service_intervals": [
      {
        "service_name": "Ganti Oli",
        "interval_km": 2000,
        "next_due_km": 7000
      },
      ...
    ]
  }
}
```

---

### 3. Get All Vehicles

```bash
curl -X GET http://localhost:8000/api/v1/motorcycle/vehicles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

---

### 4. Get Vehicle Detail (ID=1)

```bash
curl -X GET http://localhost:8000/api/v1/motorcycle/vehicles/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

---

### 5. Update Vehicle (ID=1)

```bash
curl -X POST http://localhost:8000/api/v1/motorcycle/vehicles/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "_method=PUT" \
  -F "odometer=7500" \
  -F "notes=Sudah ganti oli"
```

---

### 6. Delete Vehicle (ID=1)

```bash
curl -X DELETE http://localhost:8000/api/v1/motorcycle/vehicles/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

---

## Test Different Motor Types

### Matic Motor

```bash
curl -X POST http://localhost:8000/api/vehicles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "title=Honda Vario 160" \
  -F "make=Honda" \
  -F "tipe_motor=matic" \
  -F "odometer=3000"
```

**Intervals Generated:** Ganti Oli (2000km), Cek Rem (3000km), Ganti Oli CVT (5000km), Servis Besar (10000km)

### Manual Motor

```bash
curl -X POST http://localhost:8000/api/vehicles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "title=Honda Supra X 125" \
  -F "make=Honda" \
  -F "tipe_motor=manual" \
  -F "odometer=8000"
```

**Intervals Generated:** Ganti Oli (2500km), Cek Rem (3500km), Ganti Oli Gardan (6000km), Servis Besar (12000km)

### Sport Motor

```bash
curl -X POST http://localhost:8000/api/vehicles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "title=Honda CBR 150R" \
  -F "make=Honda" \
  -F "tipe_motor=sport" \
  -F "odometer=12000"
```

**Intervals Generated:** 5 different intervals including Tune Up Mesin

---

## Test Error Cases

### 1. Missing Required Field (title)

```bash
curl -X POST http://localhost:8000/api/vehicles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "tipe_motor=matic"
```

**Expected:** 422 Validation Error

### 2. Invalid tipe_motor

```bash
curl -X POST http://localhost:8000/api/vehicles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "title=Test Motor" \
  -F "tipe_motor=automatic"
```

**Expected:** 422 Validation Error

### 3. No Authorization Token

```bash
curl -X GET http://localhost:8000/api/vehicles \
  -H "Accept: application/json"
```

**Expected:** 401 Unauthorized

---

## Browser Test

### 1. Open HTML Tester

```
http://localhost:8000/vehicle-test.html
```

### 2. Steps:

1. Login dengan credentials di atas
2. Copy token yang muncul
3. Isi form kendaraan
4. Upload foto (optional)
5. Klik "Tambah Kendaraan"

---

## Postman Import

Import collection file:

```
Motorcycle_Management_API.postman_collection.json
```

Import environment:

```
Motorcycle_Management_Local.postman_environment.json
```

Update environment variable `access_token` setelah login.

---

## Check Database

```bash
# Login ke MySQL
mysql -u root -p

# Use database
use motorcycle_management;

# Check vehicles
SELECT id, title, tipe_motor, odometer FROM vehicles;

# Check service intervals
SELECT vehicle_id, service_name, interval_km, next_due_km
FROM service_intervals
WHERE is_active = 1;
```

---

## Health Check

```bash
curl http://localhost:8000/api/health
```

**Expected:**

```json
{
    "success": true,
    "message": "API is running",
    "timestamp": "2024-12-30T10:00:00.000000Z"
}
```

---

## ✅ Success Indicators

1. **Login Success**: Dapat token JWT
2. **Create Vehicle**: Status 201 dengan service_intervals
3. **Get Vehicles**: Status 200 dengan array data
4. **Get Detail**: Status 200 dengan relasi lengkap
5. **Update**: Status 200 dengan data ter-update
6. **Delete**: Status 200 dengan message success

---

## 🐛 Troubleshooting

### Error: 401 Unauthorized

-   Pastikan token valid dan tidak expired
-   Cek format header: `Authorization: Bearer YOUR_TOKEN`

### Error: 404 Not Found

-   Pastikan ID kendaraan ada dan milik user yang login
-   Cek route dengan: `php artisan route:list`

### Error: 500 Internal Server Error

-   Cek log: `storage/logs/laravel.log`
-   Pastikan database terkoneksi
-   Pastikan migration sudah di-run

### Foto tidak muncul

-   Pastikan sudah run: `php artisan storage:link`
-   Cek permission folder: `storage/app/public/vehicles`
-   Akses via: `http://localhost:8000/storage/vehicles/filename.jpg`

---

**Happy Testing! 🚀**

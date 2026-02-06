# API Vehicle Management - MotorCare

## Base URL

```
http://localhost:8000/api/v1/motorcycle
```

## Authentication

Semua endpoint vehicle memerlukan JWT token di header:

```
Authorization: Bearer {your_token}
```

---

## Endpoints

### 1. **Get All Vehicles** (Ambil Daftar Kendaraan)

```
GET /vehicles
```

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Daftar kendaraan berhasil diambil",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "title": "Honda Beat 2023",
            "make": "Honda",
            "model": "Beat",
            "year": 2023,
            "tipe_motor": "matic",
            "vin": null,
            "odometer": 5000,
            "license_plate": "B 1234 XYZ",
            "color": "Merah",
            "photo_url": "vehicles/1735567890_honda_beat.jpg",
            "notes": "Motor pribadi sehari-hari",
            "created_at": "2024-12-30T10:00:00.000000Z",
            "updated_at": "2024-12-30T10:00:00.000000Z",
            "service_intervals": [
                {
                    "id": 1,
                    "vehicle_id": 1,
                    "service_name": "Ganti Oli",
                    "service_type": "oil_change",
                    "interval_km": 2000,
                    "next_due_km": 7000,
                    "next_due_date": null,
                    "description": "Ganti oli mesin matic",
                    "is_active": true
                }
            ]
        }
    ]
}
```

---

### 2. **Create Vehicle** (Tambah Kendaraan Baru)

```
POST /vehicles
```

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data
```

**Body (form-data):**
| Key | Type | Required | Description |
|-----|------|----------|-------------|
| title | string | Yes | Nama kendaraan (max 200 karakter) |
| make | string | No | Merek motor (Honda, Yamaha, dll) |
| model | string | No | Model motor (Beat, Vario, dll) |
| year | integer | No | Tahun pembuatan (1900-2026) |
| tipe_motor | string | Yes | Jenis motor: `matic`, `manual`, atau `sport` |
| vin | string | No | Vehicle Identification Number (unique) |
| odometer | integer | No | Kilometer saat ini (default 0) |
| license_plate | string | No | Nomor plat kendaraan |
| color | string | No | Warna kendaraan |
| photo | file | No | Foto kendaraan (jpeg,jpg,png,webp, max 5MB) |
| notes | string | No | Catatan tambahan (max 1000 karakter) |

**Example Request:**

```
title: Honda Beat 2023
make: Honda
model: Beat
year: 2023
tipe_motor: matic
odometer: 5000
license_plate: B 1234 XYZ
color: Merah
photo: [file]
notes: Motor pribadi sehari-hari
```

**Response Success (201):**

```json
{
    "success": true,
    "message": "Kendaraan berhasil ditambahkan",
    "data": {
        "id": 1,
        "user_id": 1,
        "title": "Honda Beat 2023",
        "make": "Honda",
        "model": "Beat",
        "year": 2023,
        "tipe_motor": "matic",
        "odometer": 5000,
        "license_plate": "B 1234 XYZ",
        "color": "Merah",
        "photo_url": "vehicles/1735567890_honda_beat.jpg",
        "notes": "Motor pribadi sehari-hari",
        "created_at": "2024-12-30T10:00:00.000000Z",
        "updated_at": "2024-12-30T10:00:00.000000Z",
        "service_intervals": [
            {
                "id": 1,
                "vehicle_id": 1,
                "service_name": "Ganti Oli",
                "service_type": "oil_change",
                "interval_km": 2000,
                "next_due_km": 7000,
                "description": "Ganti oli mesin matic",
                "is_active": true
            },
            {
                "id": 2,
                "vehicle_id": 1,
                "service_name": "Cek Rem",
                "service_type": "brake_check",
                "interval_km": 3000,
                "next_due_km": 8000,
                "description": "Pemeriksaan sistem rem",
                "is_active": true
            },
            {
                "id": 3,
                "vehicle_id": 1,
                "service_name": "Ganti Oli CVT",
                "service_type": "cvt_oil_change",
                "interval_km": 5000,
                "next_due_km": 10000,
                "description": "Ganti oli transmisi CVT",
                "is_active": true
            },
            {
                "id": 4,
                "vehicle_id": 1,
                "service_name": "Servis Besar",
                "service_type": "major_service",
                "interval_km": 10000,
                "next_due_km": 15000,
                "description": "Servis menyeluruh kendaraan",
                "is_active": true
            }
        ]
    }
}
```

**Response Error (422 - Validation Error):**

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

---

### 3. **Get Vehicle Detail** (Detail Kendaraan)

```
GET /vehicles/{id}
```

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Response Success (200):**

```json
{
  "success": true,
  "message": "Detail kendaraan berhasil diambil",
  "data": {
    "id": 1,
    "user_id": 1,
    "title": "Honda Beat 2023",
    "make": "Honda",
    "model": "Beat",
    "year": 2023,
    "tipe_motor": "matic",
    "odometer": 5000,
    "license_plate": "B 1234 XYZ",
    "color": "Merah",
    "photo_url": "vehicles/1735567890_honda_beat.jpg",
    "notes": "Motor pribadi sehari-hari",
    "created_at": "2024-12-30T10:00:00.000000Z",
    "updated_at": "2024-12-30T10:00:00.000000Z",
    "service_intervals": [...],
    "service_histories": [...],
    "fuel_logs": [...],
    "reminders": [...]
  }
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Kendaraan tidak ditemukan"
}
```

---

### 4. **Update Vehicle** (Update Kendaraan)

```
POST /vehicles/{id}
```

_Note: Gunakan POST dengan `_method=PUT` untuk multipart/form-data_

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data
```

**Body (form-data):**
Sama seperti Create Vehicle, tapi semua field bersifat optional (hanya kirim yang ingin diupdate).

**Special field:**

```
_method: PUT
```

**Example Request:**

```
_method: PUT
odometer: 7500
notes: Sudah ganti oli
```

**Response Success (200):**

```json
{
  "success": true,
  "message": "Kendaraan berhasil diperbarui",
  "data": {
    "id": 1,
    "odometer": 7500,
    "notes": "Sudah ganti oli",
    ...
  }
}
```

---

### 5. **Delete Vehicle** (Hapus Kendaraan)

```
DELETE /vehicles/{id}
```

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Kendaraan berhasil dihapus",
    "data": null
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Kendaraan tidak ditemukan"
}
```

---

## Service Intervals (Auto-Generated)

Saat kendaraan dibuat, sistem otomatis membuat jadwal servis berdasarkan `tipe_motor`:

### Matic

-   Ganti Oli: setiap 2,000 km
-   Cek Rem: setiap 3,000 km
-   Ganti Oli CVT: setiap 5,000 km
-   Servis Besar: setiap 10,000 km

### Manual

-   Ganti Oli: setiap 2,500 km
-   Cek Rem: setiap 3,500 km
-   Ganti Oli Gardan: setiap 6,000 km
-   Servis Besar: setiap 12,000 km

### Sport

-   Ganti Oli: setiap 3,000 km
-   Cek Rem: setiap 4,000 km
-   Tune Up Mesin: setiap 5,000 km
-   Ganti Oli Gardan: setiap 7,000 km
-   Servis Besar: setiap 15,000 km

---

## Error Codes

| Code | Description           |
| ---- | --------------------- |
| 200  | Success               |
| 201  | Created               |
| 400  | Bad Request           |
| 401  | Unauthorized          |
| 404  | Not Found             |
| 422  | Validation Error      |
| 500  | Internal Server Error |

---

## Testing Flow

1. **Login** terlebih dahulu untuk mendapatkan token
2. **Tambah Kendaraan** dengan endpoint POST /vehicles
3. **Lihat Daftar Kendaraan** dengan GET /vehicles
4. **Lihat Detail** kendaraan spesifik dengan GET /vehicles/{id}
5. **Update** kendaraan dengan POST /vehicles/{id} + \_method=PUT
6. **Hapus** kendaraan dengan DELETE /vehicles/{id}

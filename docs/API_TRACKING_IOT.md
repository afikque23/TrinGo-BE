# Dokumentasi REST API: IoT Motorcycle Tracking System

Dokumentasi ini menjelaskan endpoints API yang digunakan untuk melakukan tracking perjalanan sepeda motor menggunakan perangkat IoT yang terintegrasi dengan MQTT.

## Base URL
```text
http://<server-domain>/api/v1/motorcycle
```

## Autentikasi
Seluruh endpoint ini membutuhkan autentikasi menggunakan **Laravel Sanctum**. Kirimkan token melalui Header HTTP:
```http
Authorization: Bearer <your_access_token>
Accept: application/json
```

---

## 1. Memeriksa Status Tracking Motor
Digunakan untuk mengecek apakah sepeda motor tertentu sedang dalam perjalanan aktif (tracking aktif) atau tidak, beserta data trip yang sedang berlangsung.

* **URL:** `/motors/{motorId}/tracking/status`
* **Method:** `GET`
* **URL Params:** `motorId` (ID kendaraan, integer)

### Response (Jika sedang aktif):
* **Status Code:** `200 OK`
```json
{
    "is_tracking": true,
    "active_trip": {
        "id": 12,
        "vehicle_id": 5,
        "status": "active",
        "started_by": 2,
        "start_at": "2026-05-25T15:30:00.000000Z",
        "end_at": null,
        "duration_minutes": null,
        "distance_meters": 0,
        "start_odometer": 12050,
        "end_odometer": null,
        "avg_speed_kph": null,
        "max_speed_kph": null,
        "notes": null,
        "device_id": "IOT-DEV-9921",
        "created_at": "2026-05-25T15:30:00.000000Z",
        "updated_at": "2026-05-25T15:30:00.000000Z",
        "deleted_at": null
    }
}
```

### Response (Jika tidak aktif):
* **Status Code:** `200 OK`
```json
{
    "is_tracking": false,
    "active_trip": null
}
```

---

## 2. Memulai Trip Baru
Digunakan untuk memulai trip baru. Endpoint ini juga akan mempublikasikan pesan perintah `start` ke topik MQTT untuk menginstruksikan perangkat IoT agar mulai mengirimkan data GPS.

* **URL:** `/motors/{motorId}/tracking/start`
* **Method:** `POST`
* **URL Params:** `motorId` (ID kendaraan, integer)

### Response (Sukses):
* **Status Code:** `200 OK`
```json
{
    "message": "Tracking started successfully",
    "trip_id": 12,
    "status": "active"
}
```

### Response (Error - Trip masih aktif):
* **Status Code:** `400 Bad Request`
```json
{
    "message": "Terdapat trip yang masih aktif",
    "trip_id": 12,
    "status": "active"
}
```

---

## 3. Menghentikan Trip Aktif
Digunakan untuk menyelesaikan trip yang sedang berlangsung. Endpoint ini **tidak memerlukan payload dari Mobile App**. 

Saat dipanggil, backend akan:
1. Mempublikasikan pesan perintah `stop` ke topik MQTT agar perangkat IoT berhenti mengirim data koordinat.
2. **Secara mandiri menghitung total jarak tempuh** berdasarkan titik koordinat (IoT telemetri) menggunakan rumus Haversine.
3. Menghitung durasi perjalanan, kecepatan rata-rata, kecepatan maksimal, serta memperbarui odometer kendaraan secara otomatis.
4. Mengembalikan ringkasan data perjalanan yang sudah diolah.

Tidak ada tanggungan dari sisi Mobile maupun IoT terkait manajemen hitungan jarak maupun update odometer.

* **URL:** `/motors/{motorId}/tracking/stop`
* **Method:** `POST`
* **URL Params:** `motorId` (ID kendaraan, integer)
* **Body/Payload:** *(Kosong / Tanpa Payload)*

### Response (Sukses):
* **Status Code:** `200 OK`
```json
{
    "message": "Tracking stopped successfully",
    "trip": {
        "id": 12,
        "vehicle_id": 5,
        "status": "completed",
        "started_by": 2,
        "start_at": "2026-05-25T15:30:00.000000Z",
        "end_at": "2026-05-25T16:15:30.000000Z",
        "duration_minutes": 45,
        "distance_meters": 15400,
        "start_odometer": 12050,
        "end_odometer": 12065,
        "avg_speed_kph": 20.53,
        "max_speed_kph": 45.00,
        "notes": null,
        "device_id": "IOT-DEV-9921",
        "created_at": "2026-05-25T15:30:00.000000Z",
        "updated_at": "2026-05-25T16:15:30.000000Z",
        "deleted_at": null
    }
}
```

### Response (Error - Tidak ada trip aktif):
* **Status Code:** `404 Not Found`
```json
{
    "message": "Tidak ada trip yang sedang aktif"
}
```

---

## 4. Mengambil Koordinat Lokasi Terakhir (IoT)
Digunakan untuk mengambil koordinat lokasi terbaru, kecepatan, arah heading, dan akurasi GPS yang dikirimkan oleh perangkat IoT ke server/telemetri.

* **URL:** `/motors/{motorId}/tracking/last-location`
* **Method:** `GET`
* **URL Params:** `motorId` (ID kendaraan, integer)

### Response (Sukses):
* **Status Code:** `200 OK`
```json
{
    "latitude": -6.2088,
    "longitude": 106.8456,
    "speed_kph": 35,
    "heading_deg": 180,
    "altitude": 12.5,
    "accuracy_meters": 3.2,
    "telemetry_at": "2026-05-25T16:15:00.000Z"
}
```

### Response (Jika belum ada data GPS yang masuk):
* **Status Code:** `200 OK`
```json
{
    "latitude": null,
    "longitude": null,
    "speed_kph": null,
    "heading_deg": null,
    "altitude": null,
    "accuracy_meters": null,
    "telemetry_at": null
}
```

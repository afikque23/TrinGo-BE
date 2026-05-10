# Flutter — Trip History (Riwayat Perjalanan) API Guide

Dokumen ini untuk tim Frontend (Flutter) agar fitur **Riwayat Perjalanan** mengambil data dari backend (bukan dari local storage saja).

## Base

- **Base URL (local):** `http://localhost/api/v1/motorcycle`
    - Catatan: di Laragon biasanya juga bisa `http://motorcycle-management.test/api/v1/motorcycle` tergantung vhost.
- Semua endpoint di bawah ini ada di group auth: `auth:sanctum,web`.

## Auth

Gunakan Bearer token dari endpoint login/refresh.

- Header wajib:
    - `Authorization: Bearer <access_token>`
    - `Accept: application/json`

## Bind ESP32 ke Kendaraan (WAJIB untuk mode MQTT)

Supaya telemetry MQTT `vehicle/{deviceId}/telemetry` masuk ke **kendaraan yang sama** yang dipakai mobile, FE perlu menyimpan `device_id` ke record kendaraan.

### Endpoint

- `PATCH /vehicles/{id}` (atau `PUT`, sesuai implementasi client)

### Body

```json
{
    "device_id": "esp32-motor-a"
}
```

### Notes

- `device_id` harus **unik** (kalau sudah dipakai kendaraan lain, API akan balas error validasi).
- Setelah bind sukses, publish MQTT ke topic: `vehicle/esp32-motor-a/telemetry`.

Catatan: saat user mengganti **motor utama** lewat endpoint `POST /vehicles/{id}/set-primary`, backend akan otomatis memindahkan `device_id` dari motor utama lama ke motor utama baru **jika** motor utama baru belum punya `device_id`. Ini membuat data MQTT selalu masuk ke motor utama tanpa perlu ubah kode ESP.

Jika dapat response `422` dengan pesan `Device ID sudah terpakai oleh kendaraan lain`, berarti ada kendaraan lain di DB yang sudah memakai `device_id` itu (sering terjadi karena data demo/seeder). Solusi: pastikan `device_id` yang bentrok dibebaskan (set `null`/ubah) di record yang salah.

### cURL contoh

```bash
curl -X PATCH \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>" \
    -H "Content-Type: application/json" \
    -d "{\"device_id\":\"esp32-motor-a\"}" \
    "http://localhost/api/v1/motorcycle/vehicles/13"
```

## Trip List (Riwayat Perjalanan)

Ambil daftar perjalanan milik user (berdasarkan kendaraan yang dimiliki user).

### Endpoint

- `GET /trips`

### Query Params

- `vehicle_id` (opsional, integer): filter trips hanya untuk 1 kendaraan
- `limit` (opsional, default 20)
- `offset` (opsional, default 0)

### Response (PENTING: bentuk data)

Backend mengembalikan **`data` sebagai array** trips.

```json
{
    "success": true,
    "message": "Trips retrieved successfully",
    "data": [
        {
            "id": 29,
            "vehicle_id": 13,
            "vehicle": {
                "id": 13,
                "title": "Motor A",
                "make": "vario",
                "model": "vario 125",
                "license_plate": null
            },
            "started_by": 14,
            "start_at": "2026-05-04T07:44:51+00:00",
            "end_at": null,
            "distance_meters": 0,
            "distance_km": null,
            "duration_minutes": null,
            "notes": "Auto-started from MQTT telemetry",
            "points_count": 3,
            "points": [
                {
                    "id": 44,
                    "trip_id": 29,
                    "sequence": 1,
                    "latitude": "-6.2000000",
                    "longitude": "106.8160000",
                    "speed_kph": 10,
                    "recorded_at": "2026-05-04T07:44:51+00:00"
                }
            ],
            "created_at": "2026-05-04T07:44:51+00:00",
            "updated_at": "2026-05-04T07:44:51+00:00"
        }
    ],
    "meta": {
        "total": 17,
        "limit": 20,
        "offset": 0
    }
}
```

### Notes untuk UI

- Trip **ongoing**: `end_at = null` (misal trip yang auto-start dari MQTT).
- `points` saat ini selalu ikut dipulangkan di list (karena backend `with(['vehicle','points'])`).
    - FE bisa menampilkan polyline dari `points`.
    - Jika hanya butuh ringkasan, tetap parse aman walau `points` besar.
- `points_count` adalah jumlah point (akan `>= 1` bila ada rekaman lokasi).

### cURL contoh

```bash
curl -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  "http://localhost/api/v1/motorcycle/trips?limit=20&offset=0"
```

Filter per kendaraan:

```bash
curl -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  "http://localhost/api/v1/motorcycle/trips?vehicle_id=13&limit=20&offset=0"
```

## Trip Detail

Ambil 1 trip beserta points.

### Endpoint

- `GET /trips/{id}`

### cURL contoh

```bash
curl -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  "http://localhost/api/v1/motorcycle/trips/29"
```

## Membuat Trip dari Mobile (opsional)

Kalau mobile tracking sendiri dan ingin upload histori perjalanan.

### Endpoint

- `POST /trips`

### Catatan payload (ringkas)

Backend menganggap `total_distance` dari mobile adalah **kilometer**, lalu dikonversi menjadi `distance_meters`.
Trip points dikirim lewat array `points`.

> Detail payload mengikuti validasi `StoreTripRequest` (lihat request class bila perlu menyesuaikan FE).

## Manual Distance (opsional)

Jika user input jarak tanpa tracking.

### Endpoint

- `POST /trips/manual-distance`

## Integrasi MQTT → Trip History (untuk mode ESP32)

Agar data perjalanan muncul dari ESP32, backend akan menulis `trip_points` dari telemetry MQTT.

### Topic format

- `vehicle/{deviceId}/telemetry`
    - contoh: `vehicle/esp32-motor-a/telemetry`

### Syarat supaya trip muncul di API

1. `MQTT_TRIP_POINTS_ENABLED=true` di `.env` (atau config `mqtt.trip_points.enabled`).
2. Command subscriber jalan terus:
    - `php artisan mqtt:subscribe --topic=vehicle/+/telemetry`
3. Record kendaraan yang dipakai mobile **harus** punya `vehicles.device_id` yang sama dengan `{deviceId}`.

### Penting: device_id harus unik

`vehicles.device_id` sebaiknya **unik**. Jika ada duplikat, telemetry bisa nyasar ke kendaraan lain.

## Checklist FE (biar tidak lagi 0)

1. Pastikan screen Riwayat Perjalanan benar-benar memanggil `GET /trips`.
2. Parse response:
    - `json['data']` => harus dianggap **List** (array).
    - bukan `json['data']['data']`.
3. Pastikan request pakai Bearer token terbaru (sesuai alur refresh token di project).
4. Untuk debugging cepat:
    - Panggil `GET /vehicles` untuk ambil `id` kendaraan.
    - Panggil `GET /trips?vehicle_id=<id>`.

---

Jika FE butuh contoh response real dari environment tertentu, kirimkan log network (URL + status + body) supaya bisa dicocokkan parsingnya.

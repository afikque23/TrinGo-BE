# MQTT Trip Seeder + Trip Point Ingest

Dokumen singkat untuk demo data MQTT + menyalurkan telemetry MQTT ke riwayat trip.

## 1) Seeder demo (mqtt_messages + vehicle + trip + trip_points)

Jalankan:

```bash
php artisan db:seed --class=MqttTripSeeder
```

Seeder akan membuat:

- 1 `vehicles` dengan `device_id=demo-route-kost-polines`
- 1 `trips` selesai (end_at terisi) agar mudah terlihat di riwayat
- `trip_points` membentuk rute **Kost (A) → POLINES (B)** (polyline ada belokan)
- `mqtt_messages` topic `vehicle/demo-route-kost-polines/telemetry`

## 2) Bridge telemetry MQTT -> trip_points (riwayat trip)

Secara default **mati**. Aktifkan dengan env:

```env
MQTT_TRIP_POINTS_ENABLED=true
```

Rule:

- Jika ada telemetry masuk di topic `vehicle/{deviceId}/telemetry`
- Laravel mencari `vehicles.device_id = {deviceId}`
- Jika ditemukan dan ada **trip aktif** (record `trips` dengan `vehicle_id` sama dan `end_at` masih null)
- Maka Laravel menambahkan 1 baris ke `trip_points`

Implementasi: `app/Services/TelemetryIngestService.php`.

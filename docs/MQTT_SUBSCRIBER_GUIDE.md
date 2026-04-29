# MQTT Subscriber (Laravel)

Dokumen ini menjelaskan cara menjadikan Laravel sebagai **MQTT subscriber** untuk menjadi “jembatan” (bridge) dan **menyimpan data** dari broker MQTT ke database.

## Gambaran arsitektur

- Device / sensor publish data ke **MQTT Broker** (Mosquitto/EMQX/HiveMQ, dll).
- Laravel jalan sebagai **subscriber daemon** (via Artisan command).
- Setiap pesan yang masuk disimpan ke tabel `mqtt_messages`.

## 1) Konfigurasi .env

Tambahkan (atau sesuaikan) variabel berikut di `.env`:

```env
MQTT_HOST=127.0.0.1
MQTT_PORT=1883
MQTT_USERNAME=
MQTT_PASSWORD=

# Bisa wildcard: vehicle/+/telemetry atau vehicle/#
MQTT_TOPICS=vehicle/+/telemetry

# 0/1/2
MQTT_QOS=0

# Jika broker pakai TLS (biasanya port 8883)
MQTT_USE_TLS=false
MQTT_TLS_SELF_SIGNED_ALLOWED=false

MQTT_CONNECT_TIMEOUT=10
MQTT_SOCKET_TIMEOUT=5
MQTT_KEEP_ALIVE=10
MQTT_RECONNECT_DELAY_SECONDS=5

# Opsional (kalau kosong, akan digenerate otomatis)
MQTT_CLIENT_ID=
```

Konfigurasi ini dibaca dari `config/mqtt.php`.

## 2) Buat tabel penyimpanan

Jalankan migrasi:

```bash
php artisan migrate
```

Tabel yang dibuat: `mqtt_messages`.

## 3) Menjalankan subscriber

Menjalankan dengan topic dari `.env`:

```bash
php artisan mqtt:subscribe
```

Override topic lewat parameter (bisa lebih dari satu):

```bash
php artisan mqtt:subscribe --topic=vehicle/+/telemetry --topic=alert/# --qos=1
```

Mode verbose (lihat preview payload):

```bash
php artisan mqtt:subscribe -v
```

Catatan:

- Default command akan mencoba decode JSON dan menyimpan ke kolom `payload_json` jika valid.
- Untuk mematikan parsing JSON: `--json=0`
- Untuk tidak menyimpan ke DB (sekadar debug): `--store=0`

## 4) Menjalankan sebagai service/daemon (produksi)

### Opsi A (Linux): Supervisor

Jalankan `php artisan mqtt:subscribe` via Supervisor agar auto-restart.

### Opsi B (Windows / Laragon)

Cara paling sederhana: jalankan command di terminal yang dedicated dan biarkan tetap jalan.

Untuk yang lebih rapi, biasanya memakai salah satu:

- **NSSM** (Non-Sucking Service Manager) untuk jadikan command sebagai Windows service.
- **Task Scheduler** untuk auto-start pada startup (kurang ideal untuk proses yang harus selalu hidup).

Minimal yang penting:

- Pastikan proses auto-restart saat crash.
- Pastikan log tersimpan (lihat `storage/logs/laravel.log`).

## 5) Melanjutkan “bridge” ke tabel domain

Selain menyimpan payload mentah di `mqtt_messages`, subscriber juga melakukan **bridge** ke tabel domain:

- Jika topic cocok dengan pola `vehicle/{deviceId}/telemetry`, maka Laravel akan mencari `vehicles.device_id = {deviceId}`
- Jika ditemukan, kolom lokasi terakhir di tabel `vehicles` akan di-update (`last_latitude`, `last_longitude`, `last_speed_kph`, `last_heading_deg`, dll)

Implementasi ada di `app/Services/TelemetryIngestService.php` dan dipanggil oleh command `mqtt:subscribe`.

Tahap berikut (opsional) biasanya:

- Tentukan format payload (JSON fields apa saja)
- Mapping topic -> entity (mis. `vehicle/{deviceId}/telemetry` -> update tabel `vehicles`, `trip_points`, dll)

Kalau kamu share contoh topic + contoh payload JSON, aku bisa bantu bikin parser + penyimpanan ke tabel yang kamu mau.

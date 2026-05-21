# Flutter Integration Guide: Vehicle Tracking API

Dokumentasi ini menjelaskan cara mengintegrasikan fitur **Start** dan **Stop** pelacakan (tracking) perjalanan (trip) kendaraan dari aplikasi Flutter ke backend Laravel.

## 📌 Daftar Endpoint API

Semua endpoint bersifat **Protected** (membutuhkan header `Authorization: Bearer {token}`).

| Method | Endpoint                                | Deskripsi                                                                                          |
| :----- | :-------------------------------------- | :------------------------------------------------------------------------------------------------- |
| `GET`  | `/api/motors/{motorId}/tracking/status` | Mengecek apakah saat ini ada trip yang sedang berjalan (aktif) untuk motor tersebut.               |
| `POST` | `/api/motors/{motorId}/tracking/start`  | Memulai trip baru. Akan otomatis mengirimkan _command start_ via MQTT ke perangkat/IoT.            |
| `POST` | `/api/motors/{motorId}/tracking/stop`   | Mengakhiri trip yang sedang berjalan. Mengirim _command stop_ via MQTT dan menghitung durasi trip. |

---

## 1. Contoh Model Dart (DTO)

Buat class model untuk mem-parsing response dari API.

```dart
class TrackingStatus {
  final bool isTracking;
  final int? activeTripId;

  TrackingStatus({required this.isTracking, this.activeTripId});

  factory TrackingStatus.fromJson(Map<string, dynamic> json) {
    return TrackingStatus(
      isTracking: json['is_tracking'] ?? false,
      activeTripId: json['active_trip'] != null ? json['active_trip']['id'] : null,
    );
  }
}
```

---

## 2. Implementasi API Service di Flutter

Contoh menggunakan package `dio` atau `http`. Berikut ini adalah ilustrasi pemanggilan endpoint-nya:

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class TrackingApiService {
  final String baseUrl = "https://domain-anda.com/api";
  final String token; // Token auth dari user yang sedang login

  TrackingApiService(this.token);

  Map<string, String> get _headers => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': 'Bearer $token',
  };

  /// 1. Cek Status Tracking
  Future<trackingstatus> checkStatus(int motorId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/motors/$motorId/tracking/status'),
      headers: _headers,
    );

    if (response.statusCode == 200) {
      final json = jsonDecode(response.body);
      return TrackingStatus.fromJson(json);
    } else {
      throw Exception('Gagal memuat status tracking');
    }
  }

  /// 2. Mulai Tracking (Start)
  Future<bool> startTracking(int motorId) async {
    final response = await http.post(
      Uri.parse('$baseUrl/motors/$motorId/tracking/start'),
      headers: _headers,
    );

    if (response.statusCode == 200) {
      return true;
    } else if (response.statusCode == 400) {
      // Kasus: Trip masih aktif
      return false;
    } else {
      throw Exception('Gagal memulai tracking');
    }
  }

  /// 3. Berhentikan Tracking (Stop)
  Future<map<string, dynamic>> stopTracking(int motorId) async {
    final response = await http.post(
      Uri.parse('$baseUrl/motors/$motorId/tracking/stop'),
      headers: _headers,
    );

    if (response.statusCode == 200) {
      final json = jsonDecode(response.body);
      return json['trip']; // Mengembalikan data summary trip (termasuk duration_minutes)
    } else {
      throw Exception('Gagal menghentikan tracking');
    }
  }
}
```

---

## 3. Alur Penggunaan di UI (State Management)

Jika Anda menggunakan `setState` standar atau state management seperti `Provider` / `BLoC` / `GetX`, alurnya kira-kira seperti ini:

1. **Saat Layar (Page/Screen) Tracker Dibuka (InitState):**
    - Tampilkan _loading indicator_.
    - Panggil fungsi `checkStatus()`.
    - Update variable boolean `isTracking` di UI berdasarkan respons API.
    - Sembunyikan loading. Jika `isTracking == true`, tombol di layar menunjukkan **"Stop Tracking"**. Jika `false`, tampilkan **"Start Tracking"**.

2. **Saat Tombol Ditekan:**
    - **Kondisi A (Kondisi saat ini "Start Tracking"):**
        - Panggil `startTracking(motorId)`.
        - Tampilkan loading modal / progress bar.
        - Jika berhasil (Try-Catch lolos), ubah status `isTracking = true` sehingga tombol berubah jadi **"Stop Tracking"**.
        - Munculkan _Snackbar / Toast_ "Tracking dimulai".
    - **Kondisi B (Kondisi saat ini "Stop Tracking"):**
        - Panggil `stopTracking(motorId)`.
        - Tampilkan loading.
        - Jika berhasil, API akan mereturn summary trip (termasuk `duration_minutes`).
        - Ubah status `isTracking = false` (tombol kembali menjadi Start).
        - (Opsional) Tampilkan Dialog atau Halaman "Trip Selesai" dengan parameter durasi.

## 4. Efek Samping ke IoT (Broker MQTT)

Perlu diinformasikan kepada tim hardware/IoT bahwa ketika API Start / Stop dipanggil Flutter, backend Laravel secara asinkron (otomatis) akan mem-publish pesan ke MQTT bertopik `tringgo/device/{deviceId}/command`.

Format Payload yang dikirim Backend:

```json
{
    "command": "start", // atau "stop"
    "trip_id": 123
}
```

IoT Device tidak perlu merespon ke Flutter atau Backend perihal instruksi ini, melainkan harus mulai mem-publish rekam jejak koordinatnya saat instruksi `"start"` diterima.

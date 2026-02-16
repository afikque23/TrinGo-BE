# Integrasi Flutter — Vehicle API (Primary & Guest)

Panduan singkat untuk menghubungkan aplikasi Flutter ke API Vehicle pada Motorcycle Management.
Semua contoh menggunakan package `http` dan `shared_preferences`.

Base URL API (gunakan environment/dev):

```yaml
dependencies:
    http: ^0.13.0
    shared_preferences: ^2.0.0
    uuid: ^3.0.0
```

## 10. Authentication (Register / Login / Forgot Password / Logout)

Simpan satu kali UUID untuk identifikasi perangkat guest.

```dart

    if (id == null) {
      id = const Uuid().v4();
      await prefs.setString(_key, id);
    }
    return id!;
  }
}
```

## 3. Helper API Client — Header Otomatis (Token / Device ID)

Helper yang mengirim `Authorization` jika ada token, kalau tidak akan pakai `X-Device-ID`.

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiClient {
  final String base;
  ApiClient(this.base);

  Future<Map<String, String>> _headers() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('access_token');
    if (token != null && token.isNotEmpty) {
      return {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      };
    }

    final deviceId = prefs.getString('device_id') ?? await DeviceService.getDeviceId();
    return {
      'Accept': 'application/json',
      'X-Device-ID': deviceId,
      'Content-Type': 'application/json',
    };
  }

  Future<http.Response> get(String path) async {
    final h = await _headers();
    return http.get(Uri.parse('$base$path'), headers: h);
  }

  Future<http.Response> post(String path, Map body) async {
    final h = await _headers();
    return http.post(Uri.parse('$base$path'), headers: h, body: jsonEncode(body));
  }

  Future<http.Response> put(String path, Map body) async {
    final h = await _headers();
    return http.put(Uri.parse('$base$path'), headers: h, body: jsonEncode(body));
  }

  Future<http.Response> delete(String path) async {
    final h = await _headers();
    return http.delete(Uri.parse('$base$path'), headers: h);
  }
}
```

Gunakan `ApiClient('http://localhost:8000/api/v1/motorcycle')`.

## 4. Flow: Guest (tanpa login)

Contoh membuat kendaraan sebagai guest — device akan dilampirkan otomatis oleh `ApiClient`.

```dart
final api = ApiClient('http://localhost:8000/api/v1/motorcycle');

Future<void> createVehicleGuest() async {
  final body = {
    'title': 'Honda Beat 2020',
    'make': 'Honda',
    'model': 'Beat',
    'year': 2020,
    'tipe_motor': 'matic',
    'odometer': 5000,
    'license_plate': 'B 1234 XYZ',
    'color': 'Merah'
  };

  final res = await api.post('/vehicles', body);
  if (res.statusCode == 201) {
    print('Vehicle created: ' + res.body);
  } else {
    print('Failed: ' + res.statusCode.toString() + ' ' + res.body);
  }
}
```

Get all vehicles as guest:

```dart
Future<void> getVehiclesGuest() async {
  final res = await api.get('/vehicles');
  if (res.statusCode == 200) {
    print(res.body);
  } else {
    print('Error: ${res.statusCode}');
  }
}
```

Catatan: endpoint internal di server menggunakan owner filter berdasarkan `device_id` ketika tidak ada token.

## 5. Flow: Authenticated (Primary Mode)

Langkah umum:

1. Login (kirim `device_id` juga untuk sinkronisasi)
2. Simpan `access_token` di `SharedPreferences`
3. Gunakan token untuk request berikutnya

Contoh login (mengembalikan token dan mungkin `sync_info`):

```dart
Future<void> login(String email, String password) async {
  final deviceId = await DeviceService.getDeviceId();
  final response = await http.post(
    Uri.parse('http://localhost:8000/api/v1/motorcycle/auth/login'),
    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
    body: jsonEncode({
      'email': email,
      'password': password,
      'device_id': deviceId,
      'device_name': 'My Flutter Device',
    }),
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body)['data'];
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('access_token', data['access_token']);
    // optional: store refresh_token, user info
  } else {
    throw Exception('Login failed: ${response.body}');
  }
}
```

Setelah login, `ApiClient` akan otomatis mengirim header `Authorization`.

```dart
Future<void> createVehicleAuth() async {
  final api = ApiClient('http://localhost:8000/api/v1/motorcycle');
  final res = await api.post('/vehicles', {
    'title': 'Honda PCX Saya',
    'make': 'Honda',
    'model': 'PCX 160',
    'year': 2023,
    'tipe_motor': 'matic',
    'odometer': 2000,
  if (res.statusCode == 201) print('Created');
}
```

```dart
Future<void> setPrimary(int vehicleId) async {
  final api = ApiClient('http://localhost:8000/api/v1/motorcycle');
  final res = await api.post('/vehicles/$vehicleId/set-primary', {});
  if (res.statusCode == 200) print('Primary set');
}
```

## 6. Update & Delete

Update vehicle:

```dart
await api.put('/vehicles/$id', {'title': 'Updated Title', 'odometer': 3000});
```

Delete vehicle:

```dart
await api.delete('/vehicles/$id');
```

## 7. Handling Errors & Edge Cases

- Jika response berisi `DEVICE_ID_REQUIRED`, pastikan `device_id` tersimpan dan header `X-Device-ID` dikirim.
- Jika mendapatkan constraint error (check constraint), pastikan migration sudah dijalankan dan `device_id` column ada.
- Untuk retry: saat status 401, lakukan refresh token flow (jika tersedia) atau minta user login ulang.

## 8. Tips & Best Practices

- Simpan `device_id` aman di `SharedPreferences` dan jangan generate ulang setiap kali.
- Kirim `device_id` pada login agar data guest dapat disinkronisasi.
- Gunakan HTTP interceptors (dio atau wrapper) untuk menambahkan header global.
- Uji kedua flow (guest dan authenticated) pada perangkat nyata.

---

Dokumentasi ini fokus pada integrasi Vehicle API. Jika mau, saya bisa tambahkan contoh lengkap menggunakan `dio` + interceptor dan contoh UI sederhana di Flutter.

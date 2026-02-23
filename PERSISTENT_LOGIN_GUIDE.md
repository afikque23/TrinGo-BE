# 🔐 Persistent Login Guide - Motorcycle Management API

## 📋 Overview

Sistem **Persistent Login** memungkinkan pengguna tetap login tanpa perlu memasukkan kredensial berulang kali (seperti Shopee, Tokopedia, dll). User hanya perlu login sekali, dan aplikasi akan otomatis memperpanjang sesi menggunakan **refresh token**.

## 🎯 Tujuan Sistem

- ✅ User hanya login sekali
- ✅ Token otomatis diperpanjang (tidak perlu login berulang)
- ✅ Data tersinkronisasi di semua device karena terikat ke user_id
- ✅ Login ulang hanya diperlukan saat token expired (90 hari) atau logout
- ✅ Keamanan tetap terjaga dengan short-lived access token + long-lived refresh token

## 🏗️ Arsitektur Token

### 1. Two-Token System

#### Access Token

- **Durasi**: 30 menit
- **Fungsi**: Akses ke API endpoints
- **Storage**: Memory / Secure storage (jangan di SharedPreferences biasa)
- **Auto-refresh**: Ya, sebelum expired

#### Refresh Token

- **Durasi**: 90 hari (persistent login)
- **Fungsi**: Mendapatkan access token baru
- **Storage**: Secure storage only (Keychain iOS / Keystore Android)
- **Auto-refresh**: Ya, dapat refresh token baru setiap kali refresh

### 2. Database Schema

Semua data user terikat ke `user_id` (NOT NULL):

- `vehicles.user_id` - Required
- `services.user_id` - Required (via vehicle)
- `trips.user_id` - Required
- `notifications.user_id` - Required
- `fuel_logs.user_id` - Required

**Device ID** hanya untuk tracking device yang digunakan (optional):

- `users.device_id` - Tracking last device
- `device_tokens.device_id` - For FCM push notifications

## 📱 Implementasi di Flutter

### A. Setup Secure Storage

```dart
// pubspec.yaml
dependencies:
  flutter_secure_storage: ^9.0.0
  http: ^1.1.0

// lib/services/auth_storage.dart
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class AuthStorage {
  static const _storage = FlutterSecureStorage();

  // Keys
  static const _accessTokenKey = 'access_token';
  static const _refreshTokenKey = 'refresh_token';
  static const _userIdKey = 'user_id';

  // Save tokens after login
  static Future<void> saveTokens({
    required String accessToken,
    required String refreshToken,
    required int userId,
  }) async {
    await Future.wait([
      _storage.write(key: _accessTokenKey, value: accessToken),
      _storage.write(key: _refreshTokenKey, value: refreshToken),
      _storage.write(key: _userIdKey, value: userId.toString()),
    ]);
  }

  // Get access token
  static Future<String?> getAccessToken() async {
    return await _storage.read(key: _accessTokenKey);
  }

  // Get refresh token
  static Future<String?> getRefreshToken() async {
    return await _storage.read(key: _refreshTokenKey);
  }

  // Check if logged in
  static Future<bool> isLoggedIn() async {
    final refreshToken = await getRefreshToken();
    return refreshToken != null && refreshToken.isNotEmpty;
  }

  // Clear all tokens (logout)
  static Future<void> clearTokens() async {
    await _storage.deleteAll();
  }
}
```

### B. HTTP Client dengan Auto-Refresh

```dart
// lib/services/api_client.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'auth_storage.dart';

class ApiClient {
  static const baseUrl = 'http://your-api.com/api/v1/motorcycle';

  // Make authenticated request with auto-refresh
  static Future<http.Response> get(String endpoint) async {
    return _makeRequest('GET', endpoint);
  }

  static Future<http.Response> post(String endpoint, {Map<String, dynamic>? body}) async {
    return _makeRequest('POST', endpoint, body: body);
  }

  static Future<http.Response> _makeRequest(
    String method,
    String endpoint,
    {Map<String, dynamic>? body}
  ) async {
    String? accessToken = await AuthStorage.getAccessToken();

    // Make request
    var response = await _sendRequest(method, endpoint, accessToken, body);

    // If 401 Unauthorized, try to refresh token
    if (response.statusCode == 401) {
      final refreshed = await _refreshAccessToken();

      if (refreshed) {
        // Retry request with new token
        accessToken = await AuthStorage.getAccessToken();
        response = await _sendRequest(method, endpoint, accessToken, body);
      } else {
        // Refresh failed, user must login again
        await AuthStorage.clearTokens();
        // Navigate to login screen
        throw Exception('Session expired. Please login again.');
      }
    }

    return response;
  }

  static Future<http.Response> _sendRequest(
    String method,
    String endpoint,
    String? accessToken,
    Map<String, dynamic>? body,
  ) async {
    final uri = Uri.parse('$baseUrl$endpoint');
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (accessToken != null) 'Authorization': 'Bearer $accessToken',
    };

    switch (method) {
      case 'GET':
        return await http.get(uri, headers: headers);
      case 'POST':
        return await http.post(
          uri,
          headers: headers,
          body: body != null ? jsonEncode(body) : null,
        );
      default:
        throw Exception('Unsupported method');
    }
  }

  // Refresh access token using refresh token
  static Future<bool> _refreshAccessToken() async {
    try {
      final refreshToken = await AuthStorage.getRefreshToken();
      if (refreshToken == null) return false;

      final response = await http.post(
        Uri.parse('$baseUrl/auth/refresh-token'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'refresh_token': refreshToken}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body)['data'];

        // Save new tokens
        await AuthStorage.saveTokens(
          accessToken: data['access_token'],
          refreshToken: data['refresh_token'],
          userId: 0, // Keep existing user_id
        );

        return true;
      }

      return false;
    } catch (e) {
      print('Refresh token error: $e');
      return false;
    }
  }
}
```

### C. Login Flow

```dart
// lib/screens/login_screen.dart
Future<void> login(String email, String password) async {
  try {
    final response = await http.post(
      Uri.parse('${ApiClient.baseUrl}/auth/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
        'device_id': await DeviceInfo.getDeviceId(), // Optional for tracking
        'device_name': await DeviceInfo.getDeviceName(),
      }),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body)['data'];

      // Save tokens to secure storage
      await AuthStorage.saveTokens(
        accessToken: data['access_token'],
        refreshToken: data['refresh_token'],
        userId: data['user']['id'],
      );

      // Navigate to home
      Navigator.pushReplacementNamed(context, '/home');
    } else {
      // Handle error
      final error = jsonDecode(response.body)['message'];
      showError(error);
    }
  } catch (e) {
    showError('Login failed: $e');
  }
}
```

### D. App Initialization (Check Login Status)

```dart
// lib/main.dart
class MyApp extends StatefulWidget {
  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  bool? _isLoggedIn;

  @override
  void initState() {
    super.initState();
    _checkLoginStatus();
  }

  Future<void> _checkLoginStatus() async {
    final isLoggedIn = await AuthStorage.isLoggedIn();
    setState(() {
      _isLoggedIn = isLoggedIn;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoggedIn == null) {
      return MaterialApp(home: SplashScreen());
    }

    return MaterialApp(
      home: _isLoggedIn! ? HomeScreen() : LoginScreen(),
      routes: {
        '/login': (context) => LoginScreen(),
        '/home': (context) => HomeScreen(),
      },
    );
  }
}
```

### E. Logout

```dart
Future<void> logout() async {
  try {
    final accessToken = await AuthStorage.getAccessToken();

    // Call logout API to revoke tokens on server
    await http.post(
      Uri.parse('${ApiClient.baseUrl}/auth/logout'),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $accessToken',
      },
    );
  } catch (e) {
    print('Logout API error: $e');
  } finally {
    // Always clear local tokens
    await AuthStorage.clearTokens();

    // Navigate to login
    Navigator.pushReplacementNamed(context, '/login');
  }
}
```

## 🔒 Keamanan

### 1. Token Storage

- ✅ **Access Token**: Secure storage (tidak di SharedPreferences biasa)
- ✅ **Refresh Token**: Secure storage with encryption (Keychain/Keystore)
- ❌ **Jangan**: Simpan token di plain text atau log

### 2. HTTPS Only

- Semua komunikasi API harus menggunakan HTTPS
- Tidak pernah kirim token melalui HTTP

### 3. Token Expiry

- Access token: 30 menit (short-lived for security)
- Refresh token: 90 hari (balance antara UX dan security)
- Server-side dapat revoke token kapan saja (logout, security breach, dll)

### 4. Device Tracking

- `device_id` dan `device_name` hanya untuk analytics dan tracking
- Tidak digunakan untuk authorization

## 📊 Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    PERSISTENT LOGIN FLOW                    │
└─────────────────────────────────────────────────────────────┘

1. FIRST TIME LOGIN
   User Input Credentials
          ↓
   POST /auth/login (email, password, device_id)
          ↓
   Server: Validate & Create Tokens
          ↓
   Response: access_token (30min) + refresh_token (90days)
          ↓
   App: Save to Secure Storage
          ↓
   Navigate to Home

2. APP LAUNCH (Already Logged In)
   App Start
          ↓
   Check: refresh_token exists?
          ↓
   YES → Auto-navigate to Home
   NO  → Show Login Screen

3. API REQUEST
   Make API Call
          ↓
   Add Header: Authorization: Bearer {access_token}
          ↓
   Response 401 Unauthorized?
          ↓
   YES → Auto Refresh Token
   NO  → Return Response

4. AUTO REFRESH TOKEN
   POST /auth/refresh-token (refresh_token)
          ↓
   Server: Validate & Create New Tokens
          ↓
   Response: new access_token + new refresh_token
          ↓
   Save New Tokens
          ↓
   Retry Original Request

5. TOKEN EXPIRED (>90 days)
   Refresh Token Failed
          ↓
   Clear Local Tokens
          ↓
   Navigate to Login Screen
```

## 🧪 Testing dengan Postman

### 1. Login dan Save Tokens

```
POST {{base_url}}/auth/login
Body:
{
  "email": "user@example.com",
  "password": "password123"
}

Tests (auto-save tokens):
pm.test("Save tokens", function () {
    var data = pm.response.json().data;
    pm.environment.set("access_token", data.access_token);
    pm.environment.set("refresh_token", data.refresh_token);
});
```

### 2. Request dengan Auto-Refresh

```
GET {{base_url}}/vehicles
Authorization: Bearer {{access_token}}

// Jika 401, manual refresh:
POST {{base_url}}/auth/refresh-token
Body:
{
  "refresh_token": "{{refresh_token}}"
}
```

## 🚀 Migration dari Guest Mode

Jika sebelumnya menggunakan guest mode dengan device_id:

### Run Migration

```bash
php artisan migrate
```

Migration akan:

1. ❌ Hapus data guest (WHERE user_id IS NULL)
2. ✅ Buat user_id jadi required (NOT NULL)
3. ✅ Hapus constraint yang memaksa salah satu (user_id atau device_id) harus ada

### Update Flutter App

1. **Hapus** logic device_id untuk data ownership
2. **Tambah** secure storage untuk tokens
3. **Implementasi** auto-refresh mechanism
4. **Update** splash screen untuk check login status

## 📝 Best Practices

### 1. Token Management

```dart
// ✅ DO
- Store in secure storage (flutter_secure_storage)
- Auto-refresh before expiry
- Clear tokens on logout
- Handle 401 errors gracefully

// ❌ DON'T
- Store in SharedPreferences (not secure)
- Hardcode tokens
- Log tokens in console
- Keep tokens after logout
```

### 2. User Experience

```dart
// ✅ DO
- Show loading indicator saat refresh token
- Auto-navigate to home jika sudah login
- Show friendly message saat session expired
- Keep user in same screen after auto-refresh

// ❌ DON'T
- Force user to login setiap app launch
- Show error popup on auto-refresh
- Logout user without confirmation
- Lose user's work during refresh
```

### 3. Error Handling

```dart
try {
  final response = await ApiClient.get('/vehicles');
  // Handle success
} on SessionExpiredException {
  // Navigate to login
  Navigator.pushReplacementNamed(context, '/login');
} catch (e) {
  // Handle other errors
  showError(e.toString());
}
```

## 🔄 Comparison: Before vs After

| Feature           | Guest Mode (Before)    | Persistent Login (After)  |
| ----------------- | ---------------------- | ------------------------- |
| Login Required    | No (optional)          | Yes (mandatory for data)  |
| Data Ownership    | device_id or user_id   | user_id only              |
| Cross-Device Sync | Manual (via login)     | Automatic (always synced) |
| Token Duration    | 30 days refresh        | 90 days refresh           |
| First Launch      | Can use immediately    | Must login first          |
| Data Persistence  | Per device until login | Per account always        |
| Security          | Medium                 | High                      |
| UX                | Good for trial         | Better for regular use    |

## 📞 Support & Troubleshooting

### Issue: "Session expired" too frequently

**Solution**:

- Check server clock sync
- Verify token storage is secure
- Check auto-refresh implementation

### Issue: "Unauthorized" after login

**Solution**:

- Verify tokens are saved correctly
- Check Authorization header format: `Bearer {token}`
- Ensure endpoint requires auth in routes

### Issue: Data tidak sync antar device

**Solution**:

- Confirm user_id sama di kedua device
- Check API endpoint returns correct user data
- Verify database user_id NOT NULL

---

**Created:** February 20, 2026
**Version:** 2.0.0 (Persistent Login)
**Migration from:** Guest Mode v1.0

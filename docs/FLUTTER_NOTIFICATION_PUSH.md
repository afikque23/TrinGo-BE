# Push Notification & Dynamic Notification - Flutter Integration Guide

## 📋 Daftar Isi

- [Overview](#overview)
- [Arsitektur Sistem](#arsitektur-sistem)
- [Setup Firebase di Flutter](#-setup-firebase-di-flutter)
- [Setup Backend Laravel](#-setup-backend-laravel)
- [API Endpoints](#-api-endpoints)
- [Flutter Implementation](#-flutter-implementation)
- [Notification Service (Flutter)](#-notification-service-flutter)
- [FCM Token Management](#-fcm-token-management)
- [In-App Notification List](#-in-app-notification-list)
- [Notification Preferences](#-notification-preferences)
- [Handle Push Notification](#-handle-push-notification)
- [Data Models (Dart)](#-data-models-dart)
- [Complete Provider Example](#-complete-provider-example)
- [Testing & Debugging](#-testing--debugging)
- [Troubleshooting](#-troubleshooting)

---

## 🎯 Overview

Sistem notifikasi dinamis TringGo mengirim notifikasi ke Flutter melalui:

1. **Push Notification (FCM)** — pesan muncul di status bar meski app tertutup
2. **In-App Notification** — disimpan di database, ditampilkan di halaman notifikasi
3. **Push + In-App** — kombinasi keduanya

### Fitur Utama:

- **Template Dinamis** — pesan bisa diubah dari admin panel dengan variabel `{vehicle_name}`, `{km_remaining}`, dll
- **Preferensi User** — user bisa on/off notifikasi per kategori atau per template
- **Multi-Device** — satu user bisa mendaftarkan banyak device (HP, tablet)
- **Guest Mode** — mendukung notifikasi tanpa login via `X-Device-ID`
- **Kategori** — notifikasi dikelompokkan (servis, perjalanan, sistem, dll)

---

## 🏗️ Arsitektur Sistem

```
┌─────────────┐     FCM Push      ┌──────────────────┐
│  Flutter App │ ◄──────────────── │  Firebase Cloud   │
│              │                   │  Messaging (FCM)  │
└──────┬───────┘                   └────────┬──────────┘
       │ REST API                           │
       │                                    │
┌──────▼───────┐                   ┌────────▼──────────┐
│  Laravel API │ ──────────────►   │  FCM HTTP v1 API  │
│  (Backend)   │   Send Message    │  (Google)         │
└──────────────┘                   └───────────────────┘
```

**Flow:**

1. Flutter app start → daftarkan FCM token ke backend via `POST /device-tokens/register`
2. Backend evaluasi jadwal servis (via scheduler/cron)
3. Backend kirim notifikasi: simpan ke DB + kirim via FCM
4. Flutter terima push notification → tampilkan di status bar
5. User tap notifikasi → buka halaman detail
6. Flutter fetch daftar notifikasi dari `GET /notifications`

---

## 🔥 Setup Firebase di Flutter

### 1. Install Dependencies

Tambahkan di `pubspec.yaml`:

```yaml
dependencies:
    firebase_core: ^3.12.1
    firebase_messaging: ^15.2.4
    flutter_local_notifications: ^18.0.1 # untuk custom notification display
    http: ^1.2.0
    shared_preferences: ^2.3.4
```

### 2. Setup Firebase Project

1. Buka [Firebase Console](https://console.firebase.google.com/)
2. Buat project baru atau gunakan yang sudah ada
3. Tambahkan app Android:
    - Package name: `com.yourapp.tringgo` (sesuaikan)
    - Download `google-services.json` → letakkan di `android/app/`
4. Tambahkan app iOS (opsional):
    - Download `GoogleService-Info.plist` → letakkan di `ios/Runner/`

### 3. Konfigurasi Android

**`android/build.gradle`:**

```gradle
buildscript {
    dependencies {
        classpath 'com.google.gms:google-services:4.4.2'
    }
}
```

**`android/app/build.gradle`:**

```gradle
apply plugin: 'com.google.gms.google-services'

android {
    defaultConfig {
        minSdkVersion 21
    }
}
```

### 4. Notification Channel (Android)

Buat file `android/app/src/main/res/values/strings.xml`:

```xml
<resources>
    <string name="default_notification_channel_id">tringgo_default</string>
</resources>
```

Di `AndroidManifest.xml`, tambahkan di dalam `<application>`:

```xml
<meta-data
    android:name="com.google.firebase.messaging.default_notification_channel_id"
    android:value="tringgo_default" />

<!-- Custom icon (opsional) -->
<meta-data
    android:name="com.google.firebase.messaging.default_notification_icon"
    android:resource="@drawable/ic_notification" />
```

### 5. Initialize Firebase di Flutter

```dart
// main.dart
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'services/notification_service.dart';

// Handler untuk background messages (HARUS top-level function)
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print('Background message: ${message.messageId}');
  // Jangan lakukan heavy operation di sini
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();

  // Set background handler
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

  // Initialize notification service
  await NotificationService.instance.initialize();

  runApp(const MyApp());
}
```

---

## 🔧 Setup Backend Laravel

### 1. Download Service Account Key

1. Buka Firebase Console → Project Settings → Service Accounts
2. Klik **"Generate New Private Key"**
3. Download file JSON
4. Simpan di: `storage/app/firebase/service-account.json`

### 2. Konfigurasi `.env`

```env
FCM_CREDENTIALS_PATH=storage/app/firebase/service-account.json
FCM_PROJECT_ID=your-firebase-project-id
```

### 3. Jalankan Migration

```bash
php artisan migrate
```

Ini akan membuat:

- Kolom `fcm_token` dan `fcm_token_updated_at` di tabel `users`
- Tabel `device_tokens` untuk menyimpan FCM token semua device

### 4. (Opsional) Update Template Notifikasi

```bash
php artisan db:seed --class=NotificationTemplateSeeder
```

---

## 📡 API Endpoints

### Base URL

```
https://your-domain.com/api/v1/motorcycle
```

### Headers yang Diperlukan

```http
Content-Type: application/json
Accept: application/json
X-Device-ID: <unique-device-id>          # Wajib untuk guest mode
Authorization: Bearer <token>             # Wajib untuk user yang login
```

---

### 1. Device Token (FCM)

#### `POST /device-tokens/register`

Daftarkan FCM token. **Panggil saat:**

- App pertama kali dibuka
- FCM token di-refresh (event `onTokenRefresh`)
- User baru login

**Request Body:**

```json
{
    "fcm_token": "dXhY7abc...(FCM token dari Firebase SDK)",
    "device_type": "android",
    "device_name": "Samsung Galaxy A54"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "FCM token berhasil didaftarkan",
    "data": {
        "id": 1,
        "device_type": "android",
        "is_active": true
    }
}
```

---

#### `POST /device-tokens/unregister`

Hapus FCM token. **Panggil saat:**

- User logout
- App di-uninstall (cleanup)

**Request Body:**

```json
{
    "fcm_token": "dXhY7abc..."
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "FCM token berhasil dihapus"
}
```

---

#### `GET /device-tokens/status`

Cek status device token (untuk debugging).

**Response (200):**

```json
{
    "success": true,
    "message": "Status device token berhasil diambil",
    "data": {
        "registered_devices": 2,
        "devices": [
            {
                "id": 1,
                "device_type": "android",
                "device_name": "Samsung Galaxy A54",
                "is_active": true,
                "last_used_at": "2026-02-19T10:30:00.000000Z",
                "created_at": "2026-02-15T08:00:00.000000Z"
            }
        ]
    }
}
```

---

### 2. Notifications (In-App)

#### `GET /notifications`

Ambil daftar notifikasi user/device.

**Query Parameters:**

| Parameter  | Type    | Default | Description                                       |
| ---------- | ------- | ------- | ------------------------------------------------- |
| `category` | string  | `all`   | Filter kategori: `service`, `trip`, `system`, dll |
| `unread`   | boolean | `false` | Hanya tampilkan yang belum dibaca                 |
| `per_page` | integer | `10`    | Jumlah per halaman                                |
| `page`     | integer | `1`     | Nomor halaman                                     |

**Response (200):**

```json
{
    "success": true,
    "message": "Notifications retrieved successfully",
    "data": {
        "data": [
            {
                "id": 1,
                "user_id": 5,
                "vehicle_id": 3,
                "category_key": "service",
                "template_id": 1,
                "title": "Pengingat Servis Rutin - Honda Beat 2023",
                "message": "🔧 Honda Beat 2023 sudah menempuh 15.500 km. Waktunya Ganti Oli! Sisa 500 km sebelum jadwal.",
                "data_payload": {
                    "template_id": 1,
                    "category_key": "service",
                    "trigger_type": "km_before_interval",
                    "variables": {
                        "vehicle_name": "Honda Beat 2023",
                        "current_km": "15.500",
                        "service_type": "Ganti Oli",
                        "km_remaining": "500"
                    }
                },
                "priority": "high",
                "sent_via": "push",
                "is_read": false,
                "read_at": null,
                "created_at": "2026-02-19T10:30:00.000000Z",
                "category": {
                    "key": "service",
                    "name": "Servis",
                    "icon": "build",
                    "color": "#FF9800"
                },
                "vehicle": {
                    "id": 3,
                    "title": "Honda Beat 2023"
                }
            }
        ],
        "meta": {
            "current_page": 1,
            "last_page": 3,
            "per_page": 10,
            "total": 25
        },
        "unread_count": 8
    }
}
```

---

#### `GET /notifications/{id}`

Detail satu notifikasi.

---

#### `PATCH /notifications/{id}/read`

Tandai satu notifikasi sebagai sudah dibaca.

**Response (200):**

```json
{
    "success": true,
    "message": "Notification marked as read",
    "data": {
        "id": 1,
        "is_read": true,
        "read_at": "2026-02-19T10:35:00.000000Z"
    }
}
```

---

#### `PATCH /notifications/read-all`

Tandai semua notifikasi sebagai dibaca.

**Response (200):**

```json
{
    "success": true,
    "message": "All notifications marked as read",
    "data": {
        "updated_count": 8
    }
}
```

---

#### `DELETE /notifications/{id}`

Hapus satu notifikasi.

---

### 3. Notification Categories

#### `GET /notification-categories`

Ambil daftar kategori untuk tab navigation.

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "key": "all",
            "name": "Semua",
            "icon": "notifications",
            "color": "#6B7C4F"
        },
        {
            "key": "service",
            "name": "Servis",
            "icon": "build",
            "color": "#FF9800"
        },
        {
            "key": "trip",
            "name": "Perjalanan",
            "icon": "directions_bike",
            "color": "#2196F3"
        },
        {
            "key": "system",
            "name": "Sistem",
            "icon": "settings",
            "color": "#9E9E9E"
        }
    ]
}
```

---

### 4. Notification Preferences

#### `GET /notification-preferences`

Ambil pengaturan notifikasi user (memerlukan auth).

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "category": {
                "key": "service",
                "name": "Servis",
                "icon": "build",
                "color": "#FF9800"
            },
            "category_enabled": true,
            "templates": [
                {
                    "template_id": 1,
                    "template_name": "Pengingat Servis Rutin",
                    "trigger_type": "km_before_interval",
                    "priority": "high",
                    "is_enabled": true,
                    "has_specific_preference": false
                },
                {
                    "template_id": 4,
                    "template_name": "Peringatan Servis Terlambat",
                    "trigger_type": "overdue",
                    "priority": "critical",
                    "is_enabled": true,
                    "has_specific_preference": false
                }
            ]
        }
    ]
}
```

---

#### `PATCH /notification-preferences`

Update preferensi notifikasi.

**Per Kategori:**

```json
{
    "category_key": "service",
    "is_enabled": false
}
```

**Per Template:**

```json
{
    "template_id": 1,
    "is_enabled": false
}
```

---

## 📱 Flutter Implementation

### Project Structure (Rekomendasi)

```
lib/
├── main.dart
├── models/
│   ├── notification_model.dart
│   ├── notification_category.dart
│   └── notification_preference.dart
├── services/
│   ├── notification_service.dart     # FCM + local notification
│   ├── api_service.dart              # HTTP client
│   └── device_token_service.dart     # FCM token management
├── providers/
│   └── notification_provider.dart    # State management
├── screens/
│   ├── notification_list_screen.dart
│   ├── notification_detail_screen.dart
│   └── notification_settings_screen.dart
└── widgets/
    ├── notification_card.dart
    ├── notification_badge.dart
    └── category_tabs.dart
```

---

## 🔔 Notification Service (Flutter)

```dart
// lib/services/notification_service.dart
import 'dart:convert';
import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'device_token_service.dart';

class NotificationService {
  NotificationService._();
  static final NotificationService instance = NotificationService._();

  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  /// Initialize semua notification service
  Future<void> initialize() async {
    // 1. Request permission
    await _requestPermission();

    // 2. Setup local notifications (untuk custom display)
    await _setupLocalNotifications();

    // 3. Setup FCM handlers
    _setupFCMHandlers();

    // 4. Get & register FCM token
    await _registerFCMToken();

    // 5. Listen token refresh
    _messaging.onTokenRefresh.listen((newToken) {
      _registerFCMToken(token: newToken);
    });
  }

  /// Request notification permission (iOS & Android 13+)
  Future<void> _requestPermission() async {
    NotificationSettings settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
      provisional: false,
    );

    print('Permission status: ${settings.authorizationStatus}');
  }

  /// Setup local notification channels
  Future<void> _setupLocalNotifications() async {
    // Android channels
    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');

    // iOS settings
    const iosSettings = DarwinInitializationSettings(
      requestAlertPermission: false, // sudah dari FCM
      requestBadgePermission: false,
      requestSoundPermission: false,
    );

    await _localNotifications.initialize(
      const InitializationSettings(
        android: androidSettings,
        iOS: iosSettings,
      ),
      onDidReceiveNotificationResponse: (response) {
        // Handle user tap di notifikasi lokal
        _handleNotificationTap(response.payload);
      },
    );

    // Buat notification channels untuk Android
    final androidPlugin = _localNotifications
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>();

    if (androidPlugin != null) {
      await androidPlugin.createNotificationChannel(
        const AndroidNotificationChannel(
          'tringgo_service',
          'Servis & Perawatan',
          description: 'Notifikasi pengingat servis kendaraan',
          importance: Importance.high,
        ),
      );
      await androidPlugin.createNotificationChannel(
        const AndroidNotificationChannel(
          'tringgo_trip',
          'Perjalanan',
          description: 'Notifikasi terkait perjalanan',
          importance: Importance.defaultImportance,
        ),
      );
      await androidPlugin.createNotificationChannel(
        const AndroidNotificationChannel(
          'tringgo_system',
          'Sistem',
          description: 'Notifikasi sistem & update',
          importance: Importance.low,
        ),
      );
    }
  }

  /// Setup FCM message handlers
  void _setupFCMHandlers() {
    // Foreground messages (app sedang terbuka)
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('Foreground message: ${message.messageId}');
      _showLocalNotification(message);
    });

    // User tap notification saat app di background
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print('Notification opened: ${message.messageId}');
      _handleNotificationData(message.data);
    });

    // Cek apakah app dibuka dari notifikasi (terminated state)
    _messaging.getInitialMessage().then((message) {
      if (message != null) {
        print('App opened from notification: ${message.messageId}');
        _handleNotificationData(message.data);
      }
    });
  }

  /// Tampilkan notifikasi lokal saat app di foreground
  Future<void> _showLocalNotification(RemoteMessage message) async {
    final notification = message.notification;
    if (notification == null) return;

    final categoryKey = message.data['category_key'] ?? 'system';
    final channelId = 'tringgo_$categoryKey';

    await _localNotifications.show(
      message.hashCode,
      notification.title,
      notification.body,
      NotificationDetails(
        android: AndroidNotificationDetails(
          channelId,
          _getChannelName(categoryKey),
          icon: '@mipmap/ic_launcher',
          importance: Importance.high,
          priority: Priority.high,
        ),
        iOS: const DarwinNotificationDetails(
          presentAlert: true,
          presentBadge: true,
          presentSound: true,
        ),
      ),
      payload: jsonEncode(message.data),
    );
  }

  /// Get channel name berdasarkan category
  String _getChannelName(String categoryKey) {
    switch (categoryKey) {
      case 'service':
        return 'Servis & Perawatan';
      case 'trip':
        return 'Perjalanan';
      default:
        return 'Sistem';
    }
  }

  /// Handle tap notification
  void _handleNotificationTap(String? payload) {
    if (payload == null) return;
    final data = jsonDecode(payload) as Map<String, dynamic>;
    _handleNotificationData(data);
  }

  /// Navigate berdasarkan data notifikasi
  void _handleNotificationData(Map<String, dynamic> data) {
    final notificationId = data['notification_id'];
    final categoryKey = data['category_key'];
    final vehicleId = data['vehicle_id'];

    // TODO: Implementasi navigasi ke halaman yang sesuai
    // Contoh:
    // if (categoryKey == 'service') {
    //   NavigationService.navigateTo('/service-schedules');
    // } else {
    //   NavigationService.navigateTo('/notifications/$notificationId');
    // }

    print('Navigate: category=$categoryKey, notification=$notificationId');
  }

  /// Register FCM token ke backend
  Future<void> _registerFCMToken({String? token}) async {
    token ??= await _messaging.getToken();
    if (token == null) return;

    await DeviceTokenService.register(token);

    // Simpan token lokal untuk referensi
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('fcm_token', token);

    print('FCM Token registered: ${token.substring(0, 20)}...');
  }

  /// Unregister FCM token (saat logout)
  Future<void> unregisterToken() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('fcm_token');

    if (token != null) {
      await DeviceTokenService.unregister(token);
      await prefs.remove('fcm_token');
    }
  }

  /// Get current FCM token
  Future<String?> getToken() async {
    return await _messaging.getToken();
  }
}
```

---

## 🔑 FCM Token Management

```dart
// lib/services/device_token_service.dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:device_info_plus/device_info_plus.dart';  // tambahkan di pubspec.yaml

class DeviceTokenService {
  static const String _baseUrl = 'https://your-domain.com/api/v1/motorcycle';

  /// Register FCM token ke backend
  static Future<bool> register(String fcmToken) async {
    try {
      final headers = await _getHeaders();
      final deviceInfo = await _getDeviceInfo();

      final response = await http.post(
        Uri.parse('$_baseUrl/device-tokens/register'),
        headers: headers,
        body: jsonEncode({
          'fcm_token': fcmToken,
          'device_type': Platform.isIOS ? 'ios' : 'android',
          'device_name': deviceInfo,
        }),
      );

      if (response.statusCode == 200) {
        print('✅ FCM token registered successfully');
        return true;
      } else {
        print('❌ FCM register failed: ${response.body}');
        return false;
      }
    } catch (e) {
      print('❌ FCM register error: $e');
      return false;
    }
  }

  /// Unregister FCM token dari backend
  static Future<bool> unregister(String fcmToken) async {
    try {
      final headers = await _getHeaders();

      final response = await http.post(
        Uri.parse('$_baseUrl/device-tokens/unregister'),
        headers: headers,
        body: jsonEncode({
          'fcm_token': fcmToken,
        }),
      );

      return response.statusCode == 200;
    } catch (e) {
      print('❌ FCM unregister error: $e');
      return false;
    }
  }

  /// Cek status device tokens
  static Future<Map<String, dynamic>?> getStatus() async {
    try {
      final headers = await _getHeaders();

      final response = await http.get(
        Uri.parse('$_baseUrl/device-tokens/status'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body)['data'];
      }
    } catch (e) {
      print('❌ Status check error: $e');
    }
    return null;
  }

  /// Build headers dengan auth token & device ID
  static Future<Map<String, String>> _getHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final authToken = prefs.getString('auth_token');
    final deviceId = prefs.getString('device_id');

    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (authToken != null) 'Authorization': 'Bearer $authToken',
      if (deviceId != null) 'X-Device-ID': deviceId,
    };
  }

  /// Get device name untuk identifikasi
  static Future<String> _getDeviceInfo() async {
    final deviceInfo = DeviceInfoPlugin();

    if (Platform.isAndroid) {
      final android = await deviceInfo.androidInfo;
      return '${android.brand} ${android.model}';
    } else if (Platform.isIOS) {
      final ios = await deviceInfo.iosInfo;
      return ios.utsname.machine;
    }
    return 'Unknown Device';
  }
}
```

---

## 📋 In-App Notification List

```dart
// lib/services/notification_api_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class NotificationApiService {
  static const String _baseUrl = 'https://your-domain.com/api/v1/motorcycle';

  /// Ambil daftar notifikasi
  static Future<Map<String, dynamic>> getNotifications({
    String category = 'all',
    bool unreadOnly = false,
    int page = 1,
    int perPage = 10,
  }) async {
    final headers = await _getHeaders();
    final queryParams = {
      if (category != 'all') 'category': category,
      if (unreadOnly) 'unread': 'true',
      'page': page.toString(),
      'per_page': perPage.toString(),
    };

    final uri = Uri.parse('$_baseUrl/notifications')
        .replace(queryParameters: queryParams);

    final response = await http.get(uri, headers: headers);
    return jsonDecode(response.body);
  }

  /// Ambil kategori notifikasi
  static Future<List<dynamic>> getCategories() async {
    final headers = await _getHeaders();
    final response = await http.get(
      Uri.parse('$_baseUrl/notification-categories'),
      headers: headers,
    );
    return jsonDecode(response.body)['data'];
  }

  /// Tandai notifikasi sebagai dibaca
  static Future<bool> markAsRead(int notificationId) async {
    final headers = await _getHeaders();
    final response = await http.patch(
      Uri.parse('$_baseUrl/notifications/$notificationId/read'),
      headers: headers,
    );
    return response.statusCode == 200;
  }

  /// Tandai semua notifikasi sebagai dibaca
  static Future<int> markAllAsRead() async {
    final headers = await _getHeaders();
    final response = await http.patch(
      Uri.parse('$_baseUrl/notifications/read-all'),
      headers: headers,
    );
    if (response.statusCode == 200) {
      return jsonDecode(response.body)['data']['updated_count'];
    }
    return 0;
  }

  /// Hapus notifikasi
  static Future<bool> deleteNotification(int notificationId) async {
    final headers = await _getHeaders();
    final response = await http.delete(
      Uri.parse('$_baseUrl/notifications/$notificationId'),
      headers: headers,
    );
    return response.statusCode == 200;
  }

  /// Ambil jumlah unread (untuk badge)
  static Future<int> getUnreadCount() async {
    final result = await getNotifications(perPage: 1);
    return result['data']?['unread_count'] ?? 0;
  }

  static Future<Map<String, String>> _getHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final authToken = prefs.getString('auth_token');
    final deviceId = prefs.getString('device_id');

    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (authToken != null) 'Authorization': 'Bearer $authToken',
      if (deviceId != null) 'X-Device-ID': deviceId,
    };
  }
}
```

---

## ⚙️ Notification Preferences

```dart
// lib/services/notification_preference_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class NotificationPreferenceService {
  static const String _baseUrl = 'https://your-domain.com/api/v1/motorcycle';

  /// Ambil semua preferensi notifikasi user
  static Future<List<dynamic>> getPreferences() async {
    final headers = await _getHeaders();
    final response = await http.get(
      Uri.parse('$_baseUrl/notification-preferences'),
      headers: headers,
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body)['data'];
    }
    return [];
  }

  /// Toggle kategori on/off
  static Future<bool> toggleCategory(String categoryKey, bool enabled) async {
    final headers = await _getHeaders();
    final response = await http.patch(
      Uri.parse('$_baseUrl/notification-preferences'),
      headers: headers,
      body: jsonEncode({
        'category_key': categoryKey,
        'is_enabled': enabled,
      }),
    );
    return response.statusCode == 200;
  }

  /// Toggle template tertentu on/off
  static Future<bool> toggleTemplate(int templateId, bool enabled) async {
    final headers = await _getHeaders();
    final response = await http.patch(
      Uri.parse('$_baseUrl/notification-preferences'),
      headers: headers,
      body: jsonEncode({
        'template_id': templateId,
        'is_enabled': enabled,
      }),
    );
    return response.statusCode == 200;
  }

  static Future<Map<String, String>> _getHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final authToken = prefs.getString('auth_token');

    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (authToken != null) 'Authorization': 'Bearer $authToken',
    };
  }
}
```

---

## 🔔 Handle Push Notification

### Data Payload dari FCM

Saat Laravel mengirim push notification, Flutter akan terima data ini:

```json
{
    "notification": {
        "title": "Pengingat Servis Rutin - Honda Beat 2023",
        "body": "🔧 Honda Beat 2023 sudah menempuh 15.500 km. Waktunya Ganti Oli!"
    },
    "data": {
        "notification_id": "42",
        "category_key": "service",
        "priority": "high",
        "vehicle_id": "3",
        "click_action": "NOTIFICATION_CLICK"
    },
    "android": {
        "priority": "HIGH",
        "notification": {
            "channel_id": "tringgo_service",
            "sound": "default"
        }
    }
}
```

### Navigasi berdasarkan data

```dart
void handleNotificationNavigation(Map<String, dynamic> data) {
  final categoryKey = data['category_key'];
  final notificationId = data['notification_id'];
  final vehicleId = data['vehicle_id'];

  switch (categoryKey) {
    case 'service':
      // Buka halaman jadwal servis
      Navigator.pushNamed(context, '/service-schedules', arguments: {
        'vehicleId': vehicleId,
      });
      break;

    case 'trip':
      // Buka halaman perjalanan
      Navigator.pushNamed(context, '/trips');
      break;

    default:
      // Buka detail notifikasi
      Navigator.pushNamed(context, '/notifications/$notificationId');
      break;
  }

  // Auto-mark as read
  if (notificationId != null) {
    NotificationApiService.markAsRead(int.parse(notificationId));
  }
}
```

---

## 📊 Data Models (Dart)

```dart
// lib/models/notification_model.dart

class NotificationModel {
  final int id;
  final int? userId;
  final int? vehicleId;
  final String categoryKey;
  final int? templateId;
  final String title;
  final String message;
  final Map<String, dynamic>? dataPayload;
  final String priority;
  final String sentVia;
  final bool isRead;
  final DateTime? readAt;
  final DateTime createdAt;
  final NotificationCategoryModel? category;

  NotificationModel({
    required this.id,
    this.userId,
    this.vehicleId,
    required this.categoryKey,
    this.templateId,
    required this.title,
    required this.message,
    this.dataPayload,
    required this.priority,
    required this.sentVia,
    required this.isRead,
    this.readAt,
    required this.createdAt,
    this.category,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id'],
      userId: json['user_id'],
      vehicleId: json['vehicle_id'],
      categoryKey: json['category_key'] ?? '',
      templateId: json['template_id'],
      title: json['title'] ?? '',
      message: json['message'] ?? '',
      dataPayload: json['data_payload'],
      priority: json['priority'] ?? 'normal',
      sentVia: json['sent_via'] ?? 'in_app',
      isRead: json['is_read'] ?? false,
      readAt: json['read_at'] != null ? DateTime.parse(json['read_at']) : null,
      createdAt: DateTime.parse(json['created_at']),
      category: json['category'] != null
          ? NotificationCategoryModel.fromJson(json['category'])
          : null,
    );
  }

  /// Cek apakah notifikasi high priority
  bool get isHighPriority => priority == 'high' || priority == 'critical';

  /// Waktu relatif (contoh: "5 menit lalu")
  String get timeAgo {
    final diff = DateTime.now().difference(createdAt);
    if (diff.inMinutes < 1) return 'Baru saja';
    if (diff.inMinutes < 60) return '${diff.inMinutes} menit lalu';
    if (diff.inHours < 24) return '${diff.inHours} jam lalu';
    if (diff.inDays < 7) return '${diff.inDays} hari lalu';
    return '${createdAt.day}/${createdAt.month}/${createdAt.year}';
  }
}

class NotificationCategoryModel {
  final String key;
  final String name;
  final String? icon;
  final String? color;

  NotificationCategoryModel({
    required this.key,
    required this.name,
    this.icon,
    this.color,
  });

  factory NotificationCategoryModel.fromJson(Map<String, dynamic> json) {
    return NotificationCategoryModel(
      key: json['key'] ?? '',
      name: json['name'] ?? '',
      icon: json['icon'],
      color: json['color'],
    );
  }
}
```

---

## 🎯 Complete Provider Example

```dart
// lib/providers/notification_provider.dart
import 'package:flutter/foundation.dart';
import '../models/notification_model.dart';
import '../services/notification_api_service.dart';

class NotificationProvider extends ChangeNotifier {
  List<NotificationModel> _notifications = [];
  List<NotificationCategoryModel> _categories = [];
  String _selectedCategory = 'all';
  int _unreadCount = 0;
  bool _isLoading = false;
  bool _hasMore = true;
  int _currentPage = 1;

  // Getters
  List<NotificationModel> get notifications => _notifications;
  List<NotificationCategoryModel> get categories => _categories;
  String get selectedCategory => _selectedCategory;
  int get unreadCount => _unreadCount;
  bool get isLoading => _isLoading;
  bool get hasMore => _hasMore;

  /// Load kategori notifikasi (panggil sekali saat init)
  Future<void> loadCategories() async {
    try {
      final data = await NotificationApiService.getCategories();
      _categories = [
        NotificationCategoryModel(key: 'all', name: 'Semua'),
        ...data.map((c) => NotificationCategoryModel.fromJson(c)),
      ];
      notifyListeners();
    } catch (e) {
      debugPrint('Error loading categories: $e');
    }
  }

  /// Load notifikasi (halaman pertama)
  Future<void> loadNotifications({bool refresh = false}) async {
    if (_isLoading) return;

    _isLoading = true;
    if (refresh) {
      _currentPage = 1;
      _hasMore = true;
    }
    notifyListeners();

    try {
      final result = await NotificationApiService.getNotifications(
        category: _selectedCategory,
        page: _currentPage,
      );

      final data = result['data'];
      final items = (data['data'] as List)
          .map((n) => NotificationModel.fromJson(n))
          .toList();

      if (refresh || _currentPage == 1) {
        _notifications = items;
      } else {
        _notifications.addAll(items);
      }

      _unreadCount = data['unread_count'] ?? 0;
      _hasMore = data['meta']['current_page'] < data['meta']['last_page'];
      _currentPage++;
    } catch (e) {
      debugPrint('Error loading notifications: $e');
    }

    _isLoading = false;
    notifyListeners();
  }

  /// Load halaman berikutnya (infinite scroll)
  Future<void> loadMore() async {
    if (!_hasMore || _isLoading) return;
    await loadNotifications();
  }

  /// Ganti kategori tab
  Future<void> selectCategory(String categoryKey) async {
    _selectedCategory = categoryKey;
    await loadNotifications(refresh: true);
  }

  /// Tandai satu notifikasi sebagai dibaca
  Future<void> markAsRead(int notificationId) async {
    final success = await NotificationApiService.markAsRead(notificationId);
    if (success) {
      final index = _notifications.indexWhere((n) => n.id == notificationId);
      if (index != -1 && !_notifications[index].isRead) {
        // Buat notifikasi baru dengan isRead=true (immutable pattern)
        _notifications[index] = NotificationModel(
          id: _notifications[index].id,
          userId: _notifications[index].userId,
          vehicleId: _notifications[index].vehicleId,
          categoryKey: _notifications[index].categoryKey,
          templateId: _notifications[index].templateId,
          title: _notifications[index].title,
          message: _notifications[index].message,
          dataPayload: _notifications[index].dataPayload,
          priority: _notifications[index].priority,
          sentVia: _notifications[index].sentVia,
          isRead: true,
          readAt: DateTime.now(),
          createdAt: _notifications[index].createdAt,
          category: _notifications[index].category,
        );
        _unreadCount = (_unreadCount - 1).clamp(0, 999);
        notifyListeners();
      }
    }
  }

  /// Tandai semua sebagai dibaca
  Future<void> markAllAsRead() async {
    final count = await NotificationApiService.markAllAsRead();
    if (count > 0) {
      _unreadCount = 0;
      _notifications = _notifications.map((n) => NotificationModel(
        id: n.id,
        userId: n.userId,
        vehicleId: n.vehicleId,
        categoryKey: n.categoryKey,
        templateId: n.templateId,
        title: n.title,
        message: n.message,
        dataPayload: n.dataPayload,
        priority: n.priority,
        sentVia: n.sentVia,
        isRead: true,
        readAt: DateTime.now(),
        createdAt: n.createdAt,
        category: n.category,
      )).toList();
      notifyListeners();
    }
  }

  /// Hapus notifikasi
  Future<void> deleteNotification(int notificationId) async {
    final success = await NotificationApiService.deleteNotification(notificationId);
    if (success) {
      final notification = _notifications.firstWhere((n) => n.id == notificationId);
      _notifications.removeWhere((n) => n.id == notificationId);
      if (!notification.isRead) {
        _unreadCount = (_unreadCount - 1).clamp(0, 999);
      }
      notifyListeners();
    }
  }

  /// Refresh unread count (dipanggil setelah terima push)
  Future<void> refreshUnreadCount() async {
    _unreadCount = await NotificationApiService.getUnreadCount();
    notifyListeners();
  }
}
```

---

## 📱 Contoh Halaman Notifikasi

```dart
// lib/screens/notification_list_screen.dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/notification_provider.dart';
import '../models/notification_model.dart';

class NotificationListScreen extends StatefulWidget {
  const NotificationListScreen({super.key});

  @override
  State<NotificationListScreen> createState() => _NotificationListScreenState();
}

class _NotificationListScreenState extends State<NotificationListScreen> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    final provider = context.read<NotificationProvider>();
    provider.loadCategories();
    provider.loadNotifications(refresh: true);

    // Infinite scroll
    _scrollController.addListener(() {
      if (_scrollController.position.pixels >=
          _scrollController.position.maxScrollExtent - 200) {
        provider.loadMore();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0A0A0A),
      appBar: AppBar(
        title: const Text('Notifikasi'),
        backgroundColor: const Color(0xFF0A0A0A),
        actions: [
          TextButton(
            onPressed: () {
              context.read<NotificationProvider>().markAllAsRead();
            },
            child: const Text('Baca Semua', style: TextStyle(color: Color(0xFF6B7C4F))),
          ),
        ],
      ),
      body: Consumer<NotificationProvider>(
        builder: (context, provider, child) {
          return Column(
            children: [
              // Category Tabs
              _buildCategoryTabs(provider),

              // Notification List
              Expanded(
                child: provider.isLoading && provider.notifications.isEmpty
                    ? const Center(child: CircularProgressIndicator())
                    : provider.notifications.isEmpty
                        ? _buildEmptyState()
                        : RefreshIndicator(
                            onRefresh: () => provider.loadNotifications(refresh: true),
                            child: ListView.builder(
                              controller: _scrollController,
                              itemCount: provider.notifications.length +
                                  (provider.hasMore ? 1 : 0),
                              itemBuilder: (context, index) {
                                if (index >= provider.notifications.length) {
                                  return const Center(
                                    child: Padding(
                                      padding: EdgeInsets.all(16),
                                      child: CircularProgressIndicator(),
                                    ),
                                  );
                                }
                                return _buildNotificationCard(
                                  provider.notifications[index],
                                  provider,
                                );
                              },
                            ),
                          ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildCategoryTabs(NotificationProvider provider) {
    return Container(
      height: 44,
      margin: const EdgeInsets.symmetric(vertical: 8),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: provider.categories.length,
        itemBuilder: (context, index) {
          final category = provider.categories[index];
          final isSelected = provider.selectedCategory == category.key;

          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: FilterChip(
              label: Text(category.name),
              selected: isSelected,
              onSelected: (_) => provider.selectCategory(category.key),
              selectedColor: const Color(0xFF6B7C4F),
              backgroundColor: const Color(0xFF1A1A2E),
              labelStyle: TextStyle(
                color: isSelected ? Colors.white : Colors.grey,
                fontSize: 13,
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildNotificationCard(
    NotificationModel notification,
    NotificationProvider provider,
  ) {
    return Dismissible(
      key: Key('notif_${notification.id}'),
      direction: DismissDirection.endToStart,
      background: Container(
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.only(right: 20),
        color: Colors.red,
        child: const Icon(Icons.delete, color: Colors.white),
      ),
      onDismissed: (_) => provider.deleteNotification(notification.id),
      child: InkWell(
        onTap: () {
          provider.markAsRead(notification.id);
          // Navigate to detail or related screen
        },
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: notification.isRead
                ? const Color(0xFF0A0A0A)
                : const Color(0xFF0F1420),
            border: Border(
              bottom: BorderSide(
                color: Colors.white.withOpacity(0.05),
              ),
              left: notification.isRead
                  ? BorderSide.none
                  : const BorderSide(
                      color: Color(0xFF6B7C4F),
                      width: 3,
                    ),
            ),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Icon
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: _getCategoryColor(notification.categoryKey)
                      .withOpacity(0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(
                  _getCategoryIcon(notification.categoryKey),
                  color: _getCategoryColor(notification.categoryKey),
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),

              // Content
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            notification.title,
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 14,
                              fontWeight: notification.isRead
                                  ? FontWeight.normal
                                  : FontWeight.w600,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (notification.isHighPriority)
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.red.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: const Text(
                              'PENTING',
                              style: TextStyle(
                                color: Colors.red,
                                fontSize: 9,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      notification.message,
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.6),
                        fontSize: 13,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 6),
                    Text(
                      notification.timeAgo,
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.3),
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.notifications_off_outlined,
            size: 64,
            color: Colors.white.withOpacity(0.2),
          ),
          const SizedBox(height: 16),
          Text(
            'Belum ada notifikasi',
            style: TextStyle(
              color: Colors.white.withOpacity(0.4),
              fontSize: 16,
            ),
          ),
        ],
      ),
    );
  }

  IconData _getCategoryIcon(String categoryKey) {
    switch (categoryKey) {
      case 'service':
        return Icons.build;
      case 'trip':
        return Icons.directions_bike;
      case 'system':
        return Icons.settings;
      default:
        return Icons.notifications;
    }
  }

  Color _getCategoryColor(String categoryKey) {
    switch (categoryKey) {
      case 'service':
        return const Color(0xFFFF9800);
      case 'trip':
        return const Color(0xFF2196F3);
      case 'system':
        return const Color(0xFF9E9E9E);
      default:
        return const Color(0xFF6B7C4F);
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }
}
```

---

## 📱 Contoh Notification Badge (Widget)

```dart
// lib/widgets/notification_badge.dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/notification_provider.dart';

class NotificationBadge extends StatelessWidget {
  final Widget child;

  const NotificationBadge({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return Consumer<NotificationProvider>(
      builder: (context, provider, _) {
        return Stack(
          alignment: Alignment.center,
          children: [
            child,
            if (provider.unreadCount > 0)
              Positioned(
                top: 0,
                right: 0,
                child: Container(
                  padding: const EdgeInsets.all(4),
                  decoration: const BoxDecoration(
                    color: Colors.red,
                    shape: BoxShape.circle,
                  ),
                  constraints: const BoxConstraints(
                    minWidth: 18,
                    minHeight: 18,
                  ),
                  child: Text(
                    provider.unreadCount > 99
                        ? '99+'
                        : provider.unreadCount.toString(),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ),
          ],
        );
      },
    );
  }
}

// Penggunaan di AppBar:
// actions: [
//   NotificationBadge(
//     child: IconButton(
//       icon: const Icon(Icons.notifications_outlined),
//       onPressed: () => Navigator.pushNamed(context, '/notifications'),
//     ),
//   ),
// ],
```

---

## ⚙️ Contoh Halaman Pengaturan Notifikasi

```dart
// lib/screens/notification_settings_screen.dart
import 'package:flutter/material.dart';
import '../services/notification_preference_service.dart';

class NotificationSettingsScreen extends StatefulWidget {
  const NotificationSettingsScreen({super.key});

  @override
  State<NotificationSettingsScreen> createState() =>
      _NotificationSettingsScreenState();
}

class _NotificationSettingsScreenState
    extends State<NotificationSettingsScreen> {
  List<dynamic> _preferences = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadPreferences();
  }

  Future<void> _loadPreferences() async {
    final prefs = await NotificationPreferenceService.getPreferences();
    setState(() {
      _preferences = prefs;
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0A0A0A),
      appBar: AppBar(
        title: const Text('Pengaturan Notifikasi'),
        backgroundColor: const Color(0xFF0A0A0A),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _preferences.length,
              itemBuilder: (context, index) {
                final pref = _preferences[index];
                final category = pref['category'];
                final templates = pref['templates'] as List;

                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Category Header + Toggle
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: const Color(0xFF111111),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: Colors.white.withOpacity(0.1),
                        ),
                      ),
                      child: Column(
                        children: [
                          Row(
                            children: [
                              Text(
                                category['name'],
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const Spacer(),
                              Switch(
                                value: pref['category_enabled'] ?? true,
                                onChanged: (value) async {
                                  await NotificationPreferenceService
                                      .toggleCategory(
                                    category['key'],
                                    value,
                                  );
                                  _loadPreferences();
                                },
                                activeColor: const Color(0xFF6B7C4F),
                              ),
                            ],
                          ),

                          // Template items
                          ...templates.map((template) {
                            return Padding(
                              padding: const EdgeInsets.only(top: 8),
                              child: Row(
                                children: [
                                  const SizedBox(width: 16),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          template['template_name'],
                                          style: TextStyle(
                                            color:
                                                Colors.white.withOpacity(0.8),
                                            fontSize: 14,
                                          ),
                                        ),
                                        Text(
                                          _getPriorityLabel(
                                              template['priority']),
                                          style: TextStyle(
                                            color:
                                                Colors.white.withOpacity(0.4),
                                            fontSize: 12,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Switch(
                                    value: template['is_enabled'] ?? true,
                                    onChanged: pref['category_enabled'] == true
                                        ? (value) async {
                                            await NotificationPreferenceService
                                                .toggleTemplate(
                                              template['template_id'],
                                              value,
                                            );
                                            _loadPreferences();
                                          }
                                        : null,
                                    activeColor: const Color(0xFF6B7C4F),
                                  ),
                                ],
                              ),
                            );
                          }),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],
                );
              },
            ),
    );
  }

  String _getPriorityLabel(String? priority) {
    switch (priority) {
      case 'critical':
        return 'Prioritas: Kritis';
      case 'high':
        return 'Prioritas: Tinggi';
      case 'normal':
        return 'Prioritas: Normal';
      case 'low':
        return 'Prioritas: Rendah';
      default:
        return '';
    }
  }
}
```

---

## 🧪 Testing & Debugging

### 1. Test dari Admin Panel

1. Login ke admin panel
2. Buka **Manajemen Notifikasi**
3. Klik tombol **Test Push** pada template
4. Notifikasi test akan:
    - Disimpan ke database (terlihat di app)
    - Dikirim via FCM ke device admin (jika FCM dikonfigurasi)

### 2. Test API via cURL

```bash
# Register FCM token
curl -X POST http://localhost:8000/api/v1/motorcycle/device-tokens/register \
  -H "Content-Type: application/json" \
  -H "X-Device-ID: test-device-123" \
  -d '{"fcm_token": "test_token_abc123", "device_type": "android"}'

# Get notifications
curl http://localhost:8000/api/v1/motorcycle/notifications \
  -H "Accept: application/json" \
  -H "X-Device-ID: test-device-123"

# Check device token status
curl http://localhost:8000/api/v1/motorcycle/device-tokens/status \
  -H "Accept: application/json" \
  -H "X-Device-ID: test-device-123"
```

### 3. Debug di Flutter

```dart
// Cek FCM token
final token = await FirebaseMessaging.instance.getToken();
print('FCM Token: $token');

// Cek permission status
final settings = await FirebaseMessaging.instance.getNotificationSettings();
print('Permission: ${settings.authorizationStatus}');

// Cek device token di backend
final status = await DeviceTokenService.getStatus();
print('Registered devices: ${status?['registered_devices']}');
```

### 4. Force Push Test (tanpa FCM setup)

Notifikasi tetap disimpan di database meski FCM belum dikonfigurasi. Jadi kamu bisa:

1. Test UI halaman notifikasi tanpa FCM
2. Push notification akan dikirim saat FCM sudah dikonfigurasi
3. Log error FCM bisa dicek di `storage/logs/laravel.log`

---

## 🔧 Troubleshooting

### FCM Token Tidak Terdaftar

- Pastikan `google-services.json` sudah benar
- Pastikan Firebase.initializeApp() dipanggil sebelum getToken()
- Cek permission (terutama Android 13+ wajib minta)

### Push Tidak Sampai ke Device

- Cek FCM credentials di backend: `storage/app/firebase/service-account.json`
- Cek `FCM_PROJECT_ID` di `.env`
- Cek log Laravel: `storage/logs/laravel.log`
- Pastikan device token aktif: panggil `GET /device-tokens/status`

### Notifikasi Foreground Tidak Muncul

- Pastikan `FlutterLocalNotificationsPlugin` sudah disetup
- Firebase FCM secara default **tidak menampilkan** notifikasi saat app di foreground
- Gunakan `onMessage` listener + `flutter_local_notifications` untuk menampilkannya

### Badge Count Tidak Update

- Panggil `refreshUnreadCount()` setelah terima push notification
- Di `onMessage` handler, tambahkan: `provider.refreshUnreadCount()`

### Guest Mode Tidak Dapat Notifikasi

- Pastikan header `X-Device-ID` selalu dikirim di setiap request
- Device token harus didaftarkan dengan `X-Device-ID` yang sama
- Notifikasi harus dikirim ke `device_id` yang sama

---

## 📋 Checklist Integrasi

- [ ] Firebase project sudah disetup
- [ ] `google-services.json` sudah di `android/app/`
- [ ] Dependencies di `pubspec.yaml` sudah ditambahkan
- [ ] `Firebase.initializeApp()` dipanggil di `main()`
- [ ] Background handler sudah disetup (`onBackgroundMessage`)
- [ ] FCM token didaftarkan ke backend saat app start
- [ ] Token refresh listener aktif (`onTokenRefresh`)
- [ ] Foreground notification handler + local notifications
- [ ] Halaman daftar notifikasi dengan infinite scroll
- [ ] Category tabs navigation
- [ ] Mark as read (single & all)
- [ ] Swipe to delete
- [ ] Notification badge di app bar
- [ ] Notification preferences/settings screen
- [ ] Unregister token saat logout
- [ ] Deep link navigation dari push tap
- [ ] Backend: `service-account.json` sudah di `storage/app/firebase/`
- [ ] Backend: `FCM_CREDENTIALS_PATH` & `FCM_PROJECT_ID` di `.env`
- [ ] Backend: Migration sudah dijalankan (`php artisan migrate`)

---

## 📝 Variabel Template yang Tersedia

Variabel yang bisa digunakan di template pesan admin. Di Flutter, ini sudah otomatis terisi oleh backend.

| Variabel           | Contoh Nilai     | Keterangan                |
| ------------------ | ---------------- | ------------------------- |
| `{vehicle_name}`   | Honda Beat 2023  | Nama kendaraan            |
| `{vehicle_plate}`  | B 1234 XYZ       | Plat nomor                |
| `{vehicle_type}`   | Matic            | Tipe motor                |
| `{current_km}`     | 15.500           | Kilometer sekarang        |
| `{vehicle_color}`  | Merah            | Warna kendaraan           |
| `{vehicle_year}`   | 2023             | Tahun kendaraan           |
| `{service_type}`   | Ganti Oli        | Jenis servis              |
| `{service_name}`   | Servis 16.000 km | Nama jadwal servis        |
| `{km_remaining}`   | 500              | KM tersisa sebelum servis |
| `{km_overdue}`     | 200              | KM terlambat              |
| `{target_km}`      | 16.000           | Target KM servis          |
| `{target_date}`    | 15 Mar 2026      | Target tanggal servis     |
| `{days_remaining}` | 14               | Hari tersisa              |
| `{last_service}`   | 01 Jan 2026      | Tanggal servis terakhir   |
| `{workshop_name}`  | Bengkel AHASS    | Nama bengkel              |
| `{distance}`       | 25,5             | Jarak perjalanan (km)     |
| `{duration}`       | 45 menit         | Durasi perjalanan         |
| `{avg_speed}`      | 35               | Kecepatan rata-rata       |
| `{user_name}`      | Budi Santoso     | Nama pengguna             |
| `{app_name}`       | TringGo      | Nama aplikasi             |
| `{date_now}`       | 19 Feb 2026      | Tanggal sekarang          |

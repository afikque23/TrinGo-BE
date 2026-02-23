# Push Notification with Persistent Login

**Version:** 1.0  
**Date:** February 21, 2026  
**System:** User-Based Push Notification (Cross-Device Support)

---

## Overview

Dengan **persistent login system**, notifikasi sekarang berbasis **user_id** bukan device_id. Ini memungkinkan:

✅ **Cross-Device Notifications** - Notifikasi sampai ke semua device user  
✅ **No Device ID Required** - Tidak perlu track device_id manual  
✅ **Auto-Sync** - User dapat login di multiple device, semua terima notifikasi  
✅ **Persistent** - Notifikasi tetap sampai meski user ganti device

---

## Perubahan dari Guest Mode

### ❌ Before (Guest Mode - Device ID Based)

```php
// Kirim ke device tertentu
$notification = NotificationService::sendFromTemplate(
    $template,
    $variables,
    user: null,              // ❌ Tidak ada user
    deviceId: $deviceId,     // ❌ Harus kirim device_id
    vehicle: $vehicle
);

// FCM kirim ke 1 device
FcmNotificationService::sendToDeviceId($deviceId, ...);
```

**Masalah:**

- User ganti device → notifikasi hilang
- Harus track device_id manual
- Tidak support cross-device
- Data terisolasi per device

### ✅ After (Persistent Login - User ID Based)

```php
// Kirim ke user (semua device)
$notification = NotificationService::sendFromTemplate(
    $template,
    $variables,
    user: $user,             // ✅ Kirim user object
    deviceId: null,          // ✅ Tidak perlu device_id
    vehicle: $vehicle
);

// FCM kirim ke SEMUA device user
FcmNotificationService::sendToUser($user->id, ...);
```

**Keuntungan:**

- User ganti device → notifikasi tetap sampai
- Tidak perlu track device_id
- Auto support cross-device
- Data terpusat per user

---

## Arsitektur Notification System

### 1. FCM Token Registration

**Flow:**

```
User Login di Device A
  ↓
Get FCM Token from Firebase SDK
  ↓
POST /api/v1/motorcycle/device-tokens/register
  Headers: Authorization: Bearer {access_token}
  Body: {
    "fcm_token": "fA9xz...",
    "device_type": "android",
    "device_name": "Samsung Galaxy S21"
  }
  ↓
Backend: Save to device_tokens table
  - user_id: 123 (from authenticated user)
  - fcm_token: fA9xz...
  - device_type: android
  - is_active: true
  ↓
Success: FCM token registered for user_id 123
```

**Database Structure:**

```sql
device_tokens table:
- id
- user_id          -- ✅ User yang login (NOT NULL)
- device_id        -- Optional UUID untuk identifikasi device
- fcm_token        -- FCM token dari Firebase SDK
- device_type      -- android/ios
- device_name      -- Samsung Galaxy S21, iPhone 13, etc
- is_active        -- true/false
- last_used_at     -- Last time token was used
- created_at
- updated_at
```

**Backend Controller:**

```php
// DeviceTokenController.php
public function register(Request $request)
{
    $validated = $request->validate([
        'fcm_token' => 'required|string',
        'device_type' => 'required|in:android,ios,web',
        'device_name' => 'nullable|string',
    ]);

    $user = $request->user(); // ✅ Get authenticated user

    $deviceToken = DeviceToken::registerToken([
        'user_id' => $user->id,           // ✅ User-based
        'fcm_token' => $validated['fcm_token'],
        'device_type' => $validated['device_type'],
        'device_name' => $validated['device_name'] ?? null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'FCM token registered',
        'data' => $deviceToken,
    ]);
}
```

### 2. Sending Notifications

**Flow:**

```
Trigger Event (e.g., Service reminder at 200 KM before)
  ↓
NotificationService::sendFromTemplate(
    template: ServiceReminderTemplate,
    variables: ['km_remaining' => 200, ...],
    user: $vehicle->user,      // ✅ User object
    vehicle: $vehicle
)
  ↓
1. Check template is active
2. Check user notification preferences
3. Parse template with variables
4. Save to notifications table (user_id = 123)
  ↓
5. IF channel = 'push':
   FcmNotificationService::sendToUser($user->id, ...)
     ↓
   Query: Get ALL fcm_tokens WHERE user_id = 123 AND is_active = 1
     Result: [
       "fA9xz...",  // Device A (Samsung)
       "gB8yw...",  // Device B (iPhone)
       "hC7xv..."   // Device C (Tablet)
     ]
     ↓
   Loop through tokens:
     - Send to fA9xz... → ✅ Success
     - Send to gB8yw... → ✅ Success
     - Send to hC7xv... → ❌ Failed (token expired)
     ↓
   Auto-disable failed token
   Return total_sent = 2
     ↓
6. Update notification: push_sent=true, push_success=true
```

**Backend Service:**

```php
// FcmNotificationService.php

/**
 * Kirim ke SEMUA device user (cross-device support)
 */
public function sendToUser(
    int $userId,
    string $title,
    string $body,
    array $data = [],
    string $categoryKey = 'default',
    string $priority = 'normal'
): int {
    // ✅ Get ALL active FCM tokens for this user
    $tokens = DeviceToken::getTokensForUser($userId);

    if (empty($tokens)) {
        Log::info("No FCM tokens for user {$userId}");
        return 0;
    }

    $successCount = 0;

    // ✅ Send to ALL devices
    foreach ($tokens as $token) {
        if ($this->sendToDevice($token, $title, $body, $data, $categoryKey, $priority)) {
            $successCount++;
        }
    }

    return $successCount; // e.g., 3 devices received notification
}
```

**DeviceToken Model:**

```php
// DeviceToken.php

/**
 * Get semua FCM tokens untuk user tertentu
 */
public static function getTokensForUser(int $userId): array
{
    return static::where('user_id', $userId)
        ->where('is_active', true)
        ->whereNotNull('fcm_token')
        ->pluck('fcm_token')
        ->toArray();
}
```

---

## Flutter Implementation

### 1. Register FCM Token on Login

**File:** `lib/services/auth_service.dart`

```dart
import 'package:firebase_messaging/firebase_messaging.dart';

class AuthService {
  final ApiClient _apiClient;
  final FirebaseMessaging _messaging = FirebaseMessaging.instance;

  /// Login and register FCM token
  Future<Map<String, dynamic>?> login(String email, String password) async {
    try {
      // Step 1: Login to get access token
      final response = await _apiClient.post(
        '/auth/login',
        body: {'email': email, 'password': password},
        requiresAuth: false,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final tokens = data['data'];

        // Step 2: Save tokens
        await saveTokens(
          accessToken: tokens['access_token'],
          refreshToken: tokens['refresh_token'],
        );
        await saveUserData(tokens['user']);

        // Step 3: Register FCM token for this user
        await registerFcmToken();

        return tokens;
      }

      return null;
    } catch (e) {
      print('Login error: $e');
      return null;
    }
  }

  /// Register FCM token for push notifications
  Future<void> registerFcmToken() async {
    try {
      // Get FCM token from Firebase SDK
      final fcmToken = await _messaging.getToken();

      if (fcmToken == null) {
        print('❌ FCM token is null');
        return;
      }

      print('📱 FCM Token: $fcmToken');

      // Get device info
      final deviceInfo = await _getDeviceInfo();

      // Register to backend
      final response = await _apiClient.post(
        '/device-tokens/register',
        body: {
          'fcm_token': fcmToken,
          'device_type': deviceInfo['type'],
          'device_name': deviceInfo['name'],
        },
        requiresAuth: true,
      );

      if (response.statusCode == 200) {
        print('✅ FCM token registered successfully');
      } else {
        print('❌ FCM token registration failed: ${response.body}');
      }
    } catch (e) {
      print('❌ FCM token registration error: $e');
    }
  }

  /// Get device information
  Future<Map<String, String>> _getDeviceInfo() async {
    // Use device_info_plus package
    final deviceInfo = DeviceInfoPlugin();

    if (Platform.isAndroid) {
      final androidInfo = await deviceInfo.androidInfo;
      return {
        'type': 'android',
        'name': '${androidInfo.brand} ${androidInfo.model}',
      };
    } else if (Platform.isIOS) {
      final iosInfo = await deviceInfo.iosInfo;
      return {
        'type': 'ios',
        'name': '${iosInfo.name} ${iosInfo.model}',
      };
    }

    return {'type': 'unknown', 'name': 'Unknown Device'};
  }
}
```

### 2. Handle Token Refresh

**File:** `lib/main.dart`

```dart
import 'package:firebase_messaging/firebase_messaging.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();

  // Listen for FCM token refresh
  FirebaseMessaging.instance.onTokenRefresh.listen((newToken) {
    print('🔄 FCM Token refreshed: $newToken');

    // Re-register new token to backend
    _registerFcmToken(newToken);
  });

  runApp(MyApp());
}

Future<void> _registerFcmToken(String fcmToken) async {
  final authService = AuthService(ApiClient());
  await authService.registerFcmToken();
}
```

### 3. Handle Incoming Notifications

**File:** `lib/services/notification_handler.dart`

```dart
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

class NotificationHandler {
  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  Future<void> initialize() async {
    // Request permission (iOS)
    await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    // Initialize local notifications
    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosSettings = DarwinInitializationSettings();
    const initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: _onNotificationTap,
    );

    // Handle foreground messages
    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);

    // Handle background messages
    FirebaseMessaging.onBackgroundMessage(_firebaseBackgroundHandler);

    // Handle notification taps when app is in background/terminated
    FirebaseMessaging.onMessageOpenedApp.listen(_handleNotificationTap);
  }

  /// Handle notification when app is in foreground
  void _handleForegroundMessage(RemoteMessage message) {
    print('📩 Foreground notification: ${message.notification?.title}');

    // Show local notification
    _showLocalNotification(message);
  }

  /// Show local notification
  Future<void> _showLocalNotification(RemoteMessage message) async {
    final notification = message.notification;
    final data = message.data;

    if (notification == null) return;

    // Get category from data
    final categoryKey = data['category_key'] ?? 'default';

    // Android notification details
    final androidDetails = AndroidNotificationDetails(
      'mototracker_$categoryKey',
      categoryKey.toUpperCase(),
      channelDescription: 'Notifications for $categoryKey',
      importance: Importance.high,
      priority: Priority.high,
      icon: '@mipmap/ic_launcher',
    );

    // iOS notification details
    const iosDetails = DarwinNotificationDetails();

    final details = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    await _localNotifications.show(
      notification.hashCode,
      notification.title,
      notification.body,
      details,
      payload: json.encode(data),
    );
  }

  /// Handle notification tap
  void _onNotificationTap(NotificationResponse response) {
    if (response.payload == null) return;

    final data = json.decode(response.payload!);
    print('🔔 Notification tapped: $data');

    // Navigate based on notification data
    _navigateToScreen(data);
  }

  /// Handle notification tap when app opened from background
  void _handleNotificationTap(RemoteMessage message) {
    print('🔔 App opened from notification: ${message.data}');
    _navigateToScreen(message.data);
  }

  /// Navigate to appropriate screen based on notification data
  void _navigateToScreen(Map<String, dynamic> data) {
    final categoryKey = data['category_key'];
    final vehicleId = data['vehicle_id'];

    // Navigate based on category
    switch (categoryKey) {
      case 'service':
        // Navigate to service screen
        navigatorKey.currentState?.pushNamed(
          '/service-detail',
          arguments: {'vehicle_id': vehicleId},
        );
        break;
      case 'trip':
        // Navigate to trip screen
        navigatorKey.currentState?.pushNamed('/trips');
        break;
      // ... other categories
    }
  }
}

/// Background message handler (must be top-level function)
@pragma('vm:entry-point')
Future<void> _firebaseBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print('📩 Background notification: ${message.notification?.title}');
}
```

---

## Backend API Endpoints

### 1. Register FCM Token

```http
POST /api/v1/motorcycle/device-tokens/register
Authorization: Bearer {access_token}
Content-Type: application/json

{
    "fcm_token": "fA9xz3qB8yC7wD6v...",
    "device_type": "android",
    "device_name": "Samsung Galaxy S21"
}
```

**Response:**

```json
{
    "success": true,
    "message": "FCM token registered",
    "data": {
        "id": 15,
        "user_id": 123,
        "fcm_token": "fA9xz...",
        "device_type": "android",
        "device_name": "Samsung Galaxy S21",
        "is_active": true,
        "created_at": "2026-02-21T10:30:00.000000Z"
    }
}
```

### 2. Get User's Device Tokens

```http
GET /api/v1/motorcycle/device-tokens
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "device_type": "android",
            "device_name": "Samsung Galaxy S21",
            "is_active": true,
            "last_used_at": "2026-02-21T10:30:00.000000Z"
        },
        {
            "id": 16,
            "device_type": "ios",
            "device_name": "iPhone 13 Pro",
            "is_active": true,
            "last_used_at": "2026-02-21T09:15:00.000000Z"
        }
    ]
}
```

### 3. Delete Device Token (Logout from specific device)

```http
DELETE /api/v1/motorcycle/device-tokens/{id}
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "message": "Device token deleted"
}
```

---

## Admin Panel - No Device ID Needed

### ❌ Before (Guest Mode Form)

```html
<!-- Old admin form with device_id -->
<form>
    <input name="user_id" placeholder="User ID (optional)" />
    <input name="device_id" placeholder="Device ID (required for guest)" />
    <textarea name="message"></textarea>
    <button>Send</button>
</form>
```

### ✅ After (Persistent Login Form)

```html
<!-- New admin form - user-based only -->
<form>
    <select name="user_id" required>
        <option value="">Pilih User</option>
        @foreach($users as $user)
        <option value="{{ $user->id }}">
            {{ $user->name }} ({{ $user->email }})
        </option>
        @endforeach
    </select>

    <textarea name="message" required></textarea>

    <button>Send to All User Devices</button>
</form>
```

**Backend Processing:**

```php
// NotificationController.php (Admin)
public function sendCustomNotification(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'title' => 'required|string',
        'message' => 'required|string',
        'category_key' => 'required|string',
    ]);

    $user = User::find($validated['user_id']);

    // ✅ Kirim ke SEMUA device user
    $notification = $this->notificationService->sendDirect(
        title: $validated['title'],
        message: $validated['message'],
        categoryKey: $validated['category_key'],
        priority: 'high',
        channel: 'push',
        user: $user,  // ✅ Only user, no device_id
    );

    return response()->json([
        'success' => true,
        'message' => 'Notification sent to all user devices',
        'data' => $notification,
    ]);
}
```

---

## Use Cases & Examples

### Use Case 1: Service Reminder (200 KM Before)

**Scenario:**

- User has 3 devices: Samsung phone, iPhone, Tablet
- Vehicle needs service at 10,000 KM
- Current KM: 9,800 KM (200 KM before)

**Flow:**

```php
// Backend: ServiceScheduleEvaluator.php
$vehicle = Vehicle::find(1); // user_id = 123
$user = $vehicle->user;

$notification = NotificationService::sendFromTemplate(
    template: $serviceReminderTemplate,
    variables: [
        'vehicle_name' => 'Honda Beat 2023',
        'service_type' => 'Ganti Oli',
        'km_remaining' => 200,
        'target_km' => 10000,
    ],
    user: $user,      // ✅ user_id = 123
    vehicle: $vehicle
);

// FcmNotificationService sends to:
// Device 1 (Samsung): ✅ Sent
// Device 2 (iPhone): ✅ Sent
// Device 3 (Tablet): ✅ Sent
```

**Result:**

- 📱 Samsung: "Waktunya servis! Ganti Oli - 200 KM lagi"
- 📱 iPhone: "Waktunya servis! Ganti Oli - 200 KM lagi"
- 📱 Tablet: "Waktunya servis! Ganti Oli - 200 KM lagi"

### Use Case 2: User Switches Device

**Scenario:**

- User login di device baru (Laptop)
- Logout dari device lama (Phone)

**Flow:**

```
1. User login di Laptop
   → POST /auth/login
   → Save access_token
   → Register FCM token (Laptop)
   → device_tokens: user_id=123, fcm_token=xyz (Laptop)

2. Notification sent
   → sendToUser(123)
   → Get tokens: [Phone token, Laptop token]
   → Send to Phone: ✅
   → Send to Laptop: ✅

3. User logout dari Phone
   → POST /auth/logout
   → Revoke tokens
   → Set device_tokens.is_active=false (Phone)

4. Next notification
   → sendToUser(123)
   → Get tokens: [Laptop token] (Phone disabled)
   → Send to Laptop: ✅
```

### Use Case 3: Cross-Device Synchronization

**Scenario:**

- User menandai notifikasi "Read" di Phone
- Status sync ke Tablet

**Implementation:**

```dart
// Mark as read on Phone
await apiClient.post('/notifications/123/read');

// Backend updates database
Notification::find(123)->update(['read_at' => now()]);

// Tablet refreshes notifications
final notifications = await apiClient.get('/notifications');

// Tablet sees notification 123 as read ✅
```

---

## Testing Guide

### Test 1: Single Device

```bash
# 1. Login di device A
POST /auth/login
Body: {"email": "test@example.com", "password": "password123"}

# 2. Register FCM token
POST /device-tokens/register
Headers: Authorization: Bearer {access_token}
Body: {
  "fcm_token": "fA9xz...",
  "device_type": "android",
  "device_name": "Samsung Galaxy S21"
}

# 3. Trigger notification (manual via admin or auto via event)
# Check: Device A receives notification ✅
```

### Test 2: Multiple Devices

```bash
# 1. Login di device A (Samsung)
POST /auth/login → get access_token
POST /device-tokens/register → fcm_token_A

# 2. Login di device B (iPhone) with SAME user
POST /auth/login → get access_token
POST /device-tokens/register → fcm_token_B

# 3. Check database
SELECT * FROM device_tokens WHERE user_id = 123;
# Result:
# id=1, user_id=123, fcm_token=fA9xz (Samsung)
# id=2, user_id=123, fcm_token=gB8yw (iPhone)

# 4. Trigger notification
# Check: Both Samsung AND iPhone receive notification ✅
```

### Test 3: Device Switch

```bash
# 1. Login di device A → Register FCM token A
# 2. Trigger notification → Device A receives ✅
# 3. Logout dari device A
POST /auth/logout
# device_tokens: is_active=false for token A

# 4. Login di device B → Register FCM token B
# 5. Trigger notification → Device B receives ✅, Device A NOT ❌
```

---

## Troubleshooting

### Issue 1: Notification not received

**Check:**

```sql
-- 1. Check if FCM token registered
SELECT * FROM device_tokens WHERE user_id = 123;

-- 2. Check if token is active
SELECT * FROM device_tokens WHERE user_id = 123 AND is_active = 1;

-- 3. Check notification sent
SELECT * FROM notifications WHERE user_id = 123 ORDER BY created_at DESC;

-- 4. Check push result
SELECT push_sent, push_success FROM notifications WHERE id = 456;
```

**Solutions:**

- Token not registered → Call `/device-tokens/register`
- Token inactive → Re-register token
- push_sent=false → Check FCM credentials
- push_success=false → Token expired, re-register

### Issue 2: Duplicate notifications

**Cause:** Multiple tokens for same device

**Solution:**

```php
// DeviceToken::registerToken() auto-handles this
// It disables old tokens before creating new one

public static function registerToken(array $data): self
{
    $conditions = ['user_id' => $data['user_id']];

    // ✅ Disable old tokens
    static::where($conditions)->update(['is_active' => false]);

    // ✅ Create new token
    return static::create($data);
}
```

### Issue 3: Notification sent to wrong user

**Check:**

```php
// Make sure using authenticated user
$user = $request->user(); // ✅ Correct
$user = User::find($customId); // ❌ Wrong if not from auth

// Send notification
$notification = NotificationService::sendFromTemplate(
    ...,
    user: $user,  // ✅ Use authenticated user
);
```

---

## Migration from Device ID to User ID

### Database Migration

```php
// Already done in: 2026_02_20_074312_remove_guest_mode_make_user_id_required.php

Schema::table('device_tokens', function (Blueprint $table) {
    // user_id is now NOT NULL
    $table->unsignedBigInteger('user_id')->nullable(false)->change();

    // device_id is now OPTIONAL (for device identification only)
    $table->uuid('device_id')->nullable()->change();
});

Schema::table('notifications', function (Blueprint $table) {
    // user_id is now NOT NULL
    $table->unsignedBigInteger('user_id')->nullable(false)->change();

    // device_id is now OPTIONAL (for historical tracking only)
    $table->uuid('device_id')->nullable()->change();
});
```

### Code Migration

**Before:**

```php
// ❌ Old way - device_id based
$notification = NotificationService::sendFromTemplate(
    $template,
    $variables,
    user: null,
    deviceId: $request->header('X-Device-ID'),
    vehicle: $vehicle
);
```

**After:**

```php
// ✅ New way - user_id based
$user = $request->user(); // From auth middleware

$notification = NotificationService::sendFromTemplate(
    $template,
    $variables,
    user: $user,
    deviceId: null,
    vehicle: $vehicle
);
```

---

## Summary

### ✅ Checklist

**Backend:**

- ✅ FCM token registration endpoint: `/device-tokens/register`
- ✅ User-based notification: `sendToUser($userId)`
- ✅ Cross-device support: Send to all user's devices
- ✅ Auto-disable invalid tokens
- ✅ Track last_used_at for each token

**Flutter:**

- ✅ Register FCM token on login
- ✅ Re-register on token refresh
- ✅ Handle foreground/background/terminated notifications
- ✅ Navigate based on notification category

**Admin Panel:**

- ✅ User-based forms (no device_id required)
- ✅ Send to all user devices automatically
- ✅ Track notification delivery status

**Testing:**

- ✅ Single device notification
- ✅ Multiple devices notification
- ✅ Device switch notification
- ✅ Cross-device synchronization

---

## Related Documentation

- [PERSISTENT_LOGIN_GUIDE.md](PERSISTENT_LOGIN_GUIDE.md) - Main persistent login implementation
- [LOGOUT_IMPLEMENTATION.md](LOGOUT_IMPLEMENTATION.md) - Logout with token revocation
- [POSTMAN_COLLECTION_UPDATE.md](POSTMAN_COLLECTION_UPDATE.md) - API testing

---

**✅ Push notification system siap dengan persistent login!**

**Key Points:**

- 📱 Kirim notifikasi berdasarkan **user_id**, bukan device_id
- 🔄 Otomatis kirim ke **semua device** user
- 🚀 Cross-device support out of the box
- 🔒 Secure dengan authentication required

_Last updated: February 21, 2026_

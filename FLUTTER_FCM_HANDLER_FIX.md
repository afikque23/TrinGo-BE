# Flutter FCM Handler Debug & Fix

## 🔍 Problem Analysis

### Backend Status: ✅ PERFECT

```
✅ FCM push sent successfully (40+ notifications)
✅ push_sent = YES
✅ push_success = YES
✅ Priority = HIGH
✅ Channel ID = mototracker_trip
```

### Flutter Status: ❌ NOT RECEIVING

```
❌ Flutter logs show NO "Foreground message received"
❌ No "Background message" logs
❌ FCM message NOT arriving at app
```

### User Confirmed:

- ✅ Local notification test WORKS (pop-up shows)
- ❌ FCM push from backend DOESN'T show pop-up
- ✅ Channels created: "default, service, trip, alert, insight"

---

## 🎯 Root Cause

FCM messages ARE being sent by backend but NOT reaching Flutter app. This means:

1. **FCM token mismatch** - Device registered with old token
2. **Message handler not setup** - `FirebaseMessaging.onMessage` not listening
3. **App in background** - Background handler not showing notification

---

## ✅ Solution: Add Proper FCM Message Handlers

Add this to your Flutter app's `main.dart`:

```dart
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

// Background message handler (MUST be top-level function)
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print("📩 Background message received: ${message.notification?.title}");

  // Show notification even in background
  if (message.notification != null) {
    await _showNotification(message);
  }
}

// Helper function to show notification
Future<void> _showNotification(RemoteMessage message) async {
  final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin =
      FlutterLocalNotificationsPlugin();

  String categoryKey = message.data['category_key'] ?? 'trip';

  await flutterLocalNotificationsPlugin.show(
    message.notification.hashCode,
    message.notification?.title,
    message.notification?.body,
    NotificationDetails(
      android: AndroidNotificationDetails(
        'mototracker_$categoryKey', // Match backend channel ID
        'Notifications',
        channelDescription: 'App notifications',
        importance: Importance.high, // ← HIGH importance for heads-up
        priority: Priority.high,
        showWhen: true,
        enableVibration: true,
        playSound: true,
      ),
    ),
    payload: message.data['notification_id']?.toString(),
  );
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  await Firebase.initializeApp();

  // Initialize notification channels
  await NotificationService.initialize();

  // ⚠️ IMPORTANT: Set background message handler BEFORE runApp()
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

  // ⚠️ IMPORTANT: Listen for FOREGROUND messages
  FirebaseMessaging.onMessage.listen((RemoteMessage message) {
    print('🔔 Foreground message received!');
    print('   Title: ${message.notification?.title}');
    print('   Body: ${message.notification?.body}');
    print('   Data: ${message.data}');

    // Show notification even when app is open
    if (message.notification != null) {
      _showNotification(message);
    }
  });

  // Handle notification tapped (app opened from notification)
  FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
    print('🔔 Notification tapped! Opening app...');
    print('   Data: ${message.data}');
    // TODO: Navigate to relevant screen based on message.data
  });

  // Check if app was opened from a terminated state notification
  RemoteMessage? initialMessage = await FirebaseMessaging.instance.getInitialMessage();
  if (initialMessage != null) {
    print('🔔 App opened from terminated state notification');
    print('   Data: ${initialMessage.data}');
    // TODO: Handle navigation
  }

  runApp(const MyApp());
}
```

---

## 🔍 Debug Steps

### 1. Check if FCM Token Matches Backend

Run this in your Flutter app's debug screen:

```dart
String? token = await FirebaseMessaging.instance.getToken();
print('📱 Current FCM Token: $token');
```

Then compare with backend:

```bash
php -r "require 'vendor/autoload.php'; \$app=require 'bootstrap/app.php'; \$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); \$token = \\App\\Models\\DeviceToken::where('device_id', 'd40436f2-ec64-44f2-b8be-d0f65754607c')->first(); echo 'Backend FCM Token: ' . substr(\$token->fcm_token, 0, 50) . '...' . PHP_EOL;"
```

**If tokens DON'T match**: Re-register token from Flutter to backend.

### 2. Add Logging to See if Messages Arrive

Add this to test if messages are arriving:

```dart
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  print('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  print('🔔 FCM MESSAGE RECEIVED!');
  print('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  print('Title: ${message.notification?.title}');
  print('Body: ${message.notification?.body}');
  print('Data Keys: ${message.data.keys.join(", ")}');
  print('Category: ${message.data['category_key']}');
  print('Notification ID: ${message.data['notification_id']}');
  print('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

  // Log to check if it's actually arriving
  if (message.notification != null) {
    print('✅ Has notification payload - will show pop-up');
  } else {
    print('⚠️ Data-only message - need to manually show notification');
  }
});
```

### 3. Test FCM from Backend

After adding the handlers above, run this from backend:

```bash
php check_fcm_token.php
```

**Expected Flutter logs**:

```
🔔 FCM MESSAGE RECEIVED!
Title: Test Push Notification 🔔
Body: Ini test push notification...
Category: test
✅ Has notification payload - will show pop-up
```

**If you still see NOTHING in Flutter logs**, then:

- FCM token is wrong/expired
- Firebase project ID mismatch
- App package name doesn't match Firebase config

### 4. Force Token Refresh

Add this button to your debug screen:

```dart
ElevatedButton(
  onPressed: () async {
    // Delete old token
    await FirebaseMessaging.instance.deleteToken();

    // Get new token
    String? newToken = await FirebaseMessaging.instance.getToken();

    // Re-register to backend
    await _registerTokenToBackend(newToken);

    print('✅ Token refreshed: ${newToken?.substring(0, 30)}...');
  },
  child: Text('🔄 Refresh FCM Token'),
)
```

---

## 🎯 Quick Test Checklist

After adding the fixes above, test with these steps:

### Test 1: Backend Send Test

```bash
cd C:\laragon\www\motorcycle_management
php check_fcm_token.php
```

**Expected**:

- ✅ Flutter console shows: "🔔 FCM MESSAGE RECEIVED!"
- ✅ Pop-up notification appears on device
- ✅ Notification appears in drawer

### Test 2: Manual Distance Add

1. Open Flutter app
2. Add manual distance (e.g., 10 km)
3. Watch Flutter console

**Expected**:

- ✅ Flutter shows: "🔔 FCM MESSAGE RECEIVED! Title: Perjalanan Selesai - Motor Anda"
- ✅ Pop-up appears within 2-3 seconds
- ✅ Notification in drawer

###Test 3: Foreground vs Background

1. **Foreground** (app open): Should show via `FirebaseMessaging.onMessage` handler
2. **Background** (app minimized): Should show via `_firebaseMessagingBackgroundHandler`
3. **Terminated** (app closed completely): Should show via system tray, handle on tap via `getInitialMessage()`

---

## 📝 Expected Behavior

| Scenario                   | Handler                       | Should Show Pop-Up?                      |
| -------------------------- | ----------------------------- | ---------------------------------------- |
| App open (foreground)      | `FirebaseMessaging.onMessage` | ✅ YES (via flutter_local_notifications) |
| App minimized (background) | `onBackgroundMessage`         | ✅ YES (via system)                      |
| App closed (terminated)    | Firebase system               | ✅ YES (via system)                      |
| Screen off                 | Background handler            | ✅ YES                                   |

---

## 🐛 Common Issues

### Issue: "No FCM logs in Flutter console"

**Cause**: Message handler not setup or token mismatch

**Solution**:

1. Add `FirebaseMessaging.onMessage.listen()` in main()
2. Verify token matches backend
3. Re-register device token

### Issue: "Notification arrives but no pop-up"

**Cause**: Channel importance not HIGH

**Solution**: Already fixed in NotificationService.dart (channels created with `Importance.high`)

### Issue: "Pop-up only shows when app is closed"

**Cause**: Foreground handler not showing notification

**Solution**: Call `_showNotification(message)` in `FirebaseMessaging.onMessage` handler

---

## ✅ Final Checklist

Before declaring "COMPLETE", verify:

- [ ] `FirebaseMessaging.onMessage.listen()` added to main.dart
- [ ] `FirebaseMessaging.onBackgroundMessage()` set before runApp()
- [ ] Helper function `_showNotification()` created
- [ ] All notification channels created with `Importance.high`
- [ ] FCM token in Flutter matches backend database
- [ ] Test from backend shows FCM logs in Flutter console
- [ ] Pop-up appears in ALL scenarios (foreground/background/terminated)

---

**Status After Fix**:

- Backend: ✅ Already perfect
- Flutter: 🔧 Need to add message handlers (5-10 minutes)

**Expected Result**: Pop-up notifications will show EVERY time odometer is updated! 🎉

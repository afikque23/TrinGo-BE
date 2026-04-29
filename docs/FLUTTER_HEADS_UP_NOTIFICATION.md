# Flutter Heads-Up Notification Setup 📱

## ⚠️ Problem

Notifikasi muncul di **notification drawer** tapi **TIDAK muncul sebagai pop-up/heads-up notification** saat app dibuka.

![Notification Drawer](https://via.placeholder.com/400x200?text=Notifikasi+Muncul+di+Drawer)

✅ **Backend sudah benar** - FCM push berhasil dikirim (cek Laravel log)  
❌ **Flutter belum setup** - Notification channels tidak pakai importance HIGH

---

## ✅ Solusi: Setup Notification Channels dengan Importance HIGH

### 1. Install Dependencies

```yaml
# pubspec.yaml
dependencies:
    firebase_messaging: ^14.7.9
    flutter_local_notifications: ^16.3.0
```

Run:

```bash
flutter pub get
```

---

### 2. Buat Notification Service

**File**: `lib/services/notification_service.dart`

```dart
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

class NotificationService {
  static final FlutterLocalNotificationsPlugin _flutterLocalNotificationsPlugin =
      FlutterLocalNotificationsPlugin();

  /// Initialize notification channels
  static Future<void> initialize() async {
    // ==========================================
    // 1. BUAT NOTIFICATION CHANNELS (PENTING!)
    // ==========================================

    // Channel untuk Service Reminders (HIGH priority - akan pop-up)
    const AndroidNotificationChannel serviceChannel = AndroidNotificationChannel(
      'mototracker_service', // ID harus sama dengan backend
      'Service Reminders',
      description: 'Pengingat servis motor',
      importance: Importance.high, // ← PENTING: HIGH importance
      enableVibration: true,
      playSound: true,
      showBadge: true,
    );

    // Channel untuk Trip Notifications (HIGH priority - akan pop-up)
    const AndroidNotificationChannel tripChannel = AndroidNotificationChannel(
      'mototracker_trip', // ID harus sama dengan backend
      'Trip Notifications',
      description: 'Notifikasi perjalanan selesai',
      importance: Importance.high, // ← PENTING: HIGH importance
      enableVibration: true,
      playSound: true,
      showBadge: true,
    );

    // Channel untuk Alerts (MAX priority - akan pop-up dengan suara keras)
    const AndroidNotificationChannel alertChannel = AndroidNotificationChannel(
      'mototracker_alert', // ID harus sama dengan backend
      'Alert Notifications',
      description: 'Peringatan penting',
      importance: Importance.max, // ← CRITICAL alerts
      enableVibration: true,
      playSound: true,
      showBadge: true,
    );

    // Channel untuk Insights (LOW priority - TIDAK pop-up)
    const AndroidNotificationChannel insightChannel = AndroidNotificationChannel(
      'mototracker_insight', // ID harus sama dengan backend
      'Insights',
      description: 'Tips dan insight berkendara',
      importance: Importance.low, // ← Low importance, tidak pop-up
      playSound: false,
    );

    // ==========================================
    // 2. CREATE CHANNELS DI ANDROID
    // ==========================================

    final androidPlugin = _flutterLocalNotificationsPlugin
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();

    await androidPlugin?.createNotificationChannel(serviceChannel);
    await androidPlugin?.createNotificationChannel(tripChannel);
    await androidPlugin?.createNotificationChannel(alertChannel);
    await androidPlugin?.createNotificationChannel(insightChannel);

    // ==========================================
    // 3. INITIALIZE PLUGIN
    // ==========================================

    const AndroidInitializationSettings initializationSettingsAndroid =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    const DarwinInitializationSettings initializationSettingsIOS =
        DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    const InitializationSettings initializationSettings = InitializationSettings(
      android: initializationSettingsAndroid,
      iOS: initializationSettingsIOS,
    );

    await _flutterLocalNotificationsPlugin.initialize(
      initializationSettings,
      onDidReceiveNotificationResponse: (NotificationResponse details) {
        // Handle notification tap
        print('🔔 Notification tapped: ${details.payload}');
        // TODO: Navigate to specific screen based on payload
      },
    );

    // ==========================================
    // 4. REQUEST PERMISSIONS (Android 13+)
    // ==========================================

    await androidPlugin?.requestPermission();

    print('✅ Notification channels initialized');
  }

  /// Handle foreground notifications to show heads-up
  static Future<void> handleForegroundMessage(RemoteMessage message) async {
    RemoteNotification? notification = message.notification;
    AndroidNotification? android = message.notification?.android;

    if (notification != null && android != null) {
      print('📩 Foreground notification: ${notification.title}');

      String categoryKey = message.data['category_key'] ?? 'trip';

      // Show heads-up notification even when app is in foreground
      await _flutterLocalNotificationsPlugin.show(
        notification.hashCode,
        notification.title,
        notification.body,
        NotificationDetails(
          android: AndroidNotificationDetails(
            'mototracker_$categoryKey', // Match channel ID
            'Notifications',
            channelDescription: 'App notifications',
            importance: Importance.high, // ← PENTING untuk heads-up
            priority: Priority.high,
            showWhen: true,
            enableVibration: true,
            playSound: true,
            icon: '@mipmap/ic_launcher',
          ),
          iOS: const DarwinNotificationDetails(
            presentAlert: true,
            presentBadge: true,
            presentSound: true,
          ),
        ),
        payload: message.data['notification_id']?.toString(),
      );
    }
  }
}
```

---

### 3. Update main.dart

**File**: `lib/main.dart`

```dart
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'services/notification_service.dart';

// Background message handler (HARUS di top-level, bukan di class)
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print("📩 Background message: ${message.notification?.title}");
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // ==========================================
  // 1. INITIALIZE FIREBASE
  // ==========================================
  await Firebase.initializeApp();

  // ==========================================
  // 2. INITIALIZE NOTIFICATION CHANNELS
  // ==========================================
  await NotificationService.initialize();

  // ==========================================
  // 3. SET BACKGROUND MESSAGE HANDLER
  // ==========================================
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

  // ==========================================
  // 4. HANDLE FOREGROUND NOTIFICATIONS
  // ==========================================
  FirebaseMessaging.onMessage.listen((RemoteMessage message) {
    print('🔔 Foreground message: ${message.notification?.title}');
    NotificationService.handleForegroundMessage(message);
  });

  // ==========================================
  // 5. HANDLE NOTIFICATION TAP (App Opened)
  // ==========================================
  FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
    print('🔔 Notification tapped (app opened): ${message.notification?.title}');
    // TODO: Navigate to specific screen
  });

  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'MotoTracker',
      theme: ThemeData(
        primarySwatch: Colors.blue,
      ),
      home: const HomePage(),
    );
  }
}
```

---

### 4. Update AndroidManifest.xml

**File**: `android/app/src/main/AndroidManifest.xml`

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">

  <!-- Permissions -->
  <uses-permission android:name="android.permission.INTERNET"/>
  <uses-permission android:name="android.permission.VIBRATE"/>
  <uses-permission android:name="android.permission.RECEIVE_BOOT_COMPLETED"/>

  <!-- Android 13+ notification permission -->
  <uses-permission android:name="android.permission.POST_NOTIFICATIONS"/>

  <application
      android:label="MotoTracker"
      android:name="${applicationName}"
      android:icon="@mipmap/ic_launcher">

    <!-- ========================================== -->
    <!-- DEFAULT NOTIFICATION CHANNEL              -->
    <!-- ========================================== -->
    <meta-data
        android:name="com.google.firebase.messaging.default_notification_channel_id"
        android:value="mototracker_trip" />

    <meta-data
        android:name="com.google.firebase.messaging.default_notification_icon"
        android:resource="@mipmap/ic_launcher" />

    <!-- ActivityMain... -->

  </application>
</manifest>
```

---

### 5. Request Notification Permission (Android 13+)

**File**: `lib/screens/home_screen.dart` atau di splash screen

```dart
import 'package:firebase_messaging/firebase_messaging.dart';

class HomePage extends StatefulWidget {
  @override
  _HomePageState createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  @override
  void initState() {
    super.initState();
    _requestNotificationPermission();
  }

  Future<void> _requestNotificationPermission() async {
    FirebaseMessaging messaging = FirebaseMessaging.instance;

    NotificationSettings settings = await messaging.requestPermission(
      alert: true,
      announcement: false,
      badge: true,
      carPlay: false,
      criticalAlert: false,
      provisional: false,
      sound: true,
    );

    print('User granted permission: ${settings.authorizationStatus}');

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      print('✅ Notification permission granted');
    } else if (settings.authorizationStatus == AuthorizationStatus.provisional) {
      print('⚠️ Provisional permission granted');
    } else {
      print('❌ Notification permission denied');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('MotoTracker')),
      body: Center(child: Text('Home Page')),
    );
  }
}
```

---

## 🔍 Debugging Checklist

Jika heads-up notification **masih tidak muncul** setelah setup di atas:

### 1. ✅ Check Phone Settings

**Android Settings → Apps → MotoTracker → Notifications**

- Pastikan **semua channels enabled**
- Pastikan **"Pop on screen"** atau **"Floating notification"** enabled
- Pastikan **"Sound"** enabled

### 2. ✅ Check Do Not Disturb Mode

- Matikan **Do Not Disturb** mode
- Atau tambahkan app ke **exception list** di DND settings

### 3. ✅ Check Backend Priority

Run di Laravel tinker atau MySQL:

```sql
SELECT name, category_key, priority, channel
FROM notification_templates
WHERE is_active = 1;
```

**Pastikan**: Template "Perjalanan Selesai" punya `priority = 'high'`

### 4. ✅ Check FCM Payload (Laravel Log)

```bash
tail -f storage/logs/laravel.log
```

Log harus show:

```
"android.priority": "HIGH"
"notification_priority": "PRIORITY_HIGH"
```

Jika masih `"NORMAL"`, berarti backend belum update. Check `FcmNotificationService.php` line 94.

### 5. ✅ Test Battery Optimization

**Settings → Battery → Battery Optimization**

- Cari app "MotoTracker"
- Set to **"Don't optimize"**

Battery optimization bisa block heads-up notifications!

### 6. ✅ Test Push dari Backend

```bash
cd C:\laragon\www\motorcycle_management
php check_fcm_token.php
```

Harus muncul pop-up notification di HP!

---

## 📊 Notification Importance Levels

| Importance           | Behavior                              | Use Case                                    |
| -------------------- | ------------------------------------- | ------------------------------------------- |
| `Importance.max`     | Pop-up, sound, heads-up di semua mode | Critical alerts (BBM habis, servis overdue) |
| `Importance.high`    | Pop-up, sound, heads-up               | Trip selesai, servis reminder               |
| `Importance.default` | Notif tray, sound saja                | Default notifications                       |
| `Importance.low`     | Notif tray, no sound                  | Insights, tips                              |
| `Importance.min`     | Hidden, statusbar only                | Background sync                             |

---

## 📝 Mapping Backend → Flutter Channels

| Backend Category | Flutter Channel ID    | Importance        |
| ---------------- | --------------------- | ----------------- |
| `service`        | `mototracker_service` | `Importance.high` |
| `trip`           | `mototracker_trip`    | `Importance.high` |
| `alert`          | `mototracker_alert`   | `Importance.max`  |
| `insight`        | `mototracker_insight` | `Importance.low`  |

**Channel ID harus SAMA** antara backend (FcmNotificationService.php) dan Flutter (NotificationService.dart)!

---

## ✅ Testing Steps

### 1. Test dari Backend

```bash
php check_fcm_token.php
```

**Expected**: Pop-up notification muncul di HP ✅

### 2. Test Trip Completion

1. Buka Flutter app
2. Start trip
3. Add distance (manual atau GPS)
4. Finish trip

**Expected**:

- ✅ Pop-up notification muncul
- ✅ Notification muncul di drawer
- ✅ Tap notification buka app

### 3. Test Foreground vs Background

**Foreground** (app terbuka):

- Pop-up harus muncul via `flutter_local_notifications`

**Background** (app di minimize):

- Pop-up harus muncul via Firebase system tray

---

## 🎉 Hasil Akhir

**Before** (HANYA di drawer):

```
📱 Notification Drawer
  └─ Trip selesai (15 min ago)
  └─ Trip selesai (1 hour ago)
```

**After** (Pop-up + Drawer):

```
🔔 Pop-up Notification
   Perjalanan Selesai - Motor Anda
   ✅ Perjalanan selesai! Motor Anda menempuh 15.5 km...
   [TAP TO OPEN]

📱 Notification Drawer
  └─ Trip selesai (now)
```

---

## 📚 Resources

- [Firebase Messaging Flutter](https://firebase.flutter.dev/docs/messaging/overview/)
- [Flutter Local Notifications](https://pub.dev/packages/flutter_local_notifications)
- [Android Notification Channels](https://developer.android.com/develop/ui/views/notifications/channels)

---

**Status**: 🎉 **COMPLETE! Backend + Flutter Setup Ready!**

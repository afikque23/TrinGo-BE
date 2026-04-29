# Quick Reference - Cara Cek Log Notifikasi

## 📋 Commands untuk Cek Log

### 1. Lihat Log Terbaru (Last 50 lines)

```powershell
Get-Content storage\logs\laravel.log -Tail 50
```

### 2. Follow Log Real-Time (Live Monitoring)

```powershell
Get-Content storage\logs\laravel.log -Wait -Tail 10
```

### 3. Filter Log Notifikasi Saja

```powershell
Get-Content storage\logs\laravel.log | Select-String -Pattern "NotificationService","FcmNotificationService"
```

### 4. Cek Last 100 Lines + Filter Notification

```powershell
Get-Content storage\logs\laravel.log -Tail 100 | Select-String -Pattern "Push notification"
```

### 5. Follow + Filter (Monitor Live Notification Logs)

```powershell
Get-Content storage\logs\laravel.log -Wait -Tail 20 | Select-String -Pattern "NotificationService|FcmNotificationService"
```

---

## 🔍 Keyword yang Perlu Dicari

| Keyword                                                      | Artinya                           |
| ------------------------------------------------------------ | --------------------------------- |
| `FcmNotificationService: Push notification berhasil dikirim` | ✅ FCM push berhasil              |
| `NotificationService: Push notification result`              | Result dari push (success/fail)   |
| `Updated notification with push result`                      | Database updated with push status |
| `Firebase credentials file tidak ditemukan`                  | ❌ FCM credentials error          |
| `FCM tidak dikonfigurasi`                                    | ❌ FCM service not initialized    |
| `Push notification gagal dikirim`                            | ❌ FCM send failed                |

---

## 🗄️ Cek Database Notifikasi

### Via PHP Script (Quick)

```bash
php diagnose_notification.php
```

Atau manual query:

```bash
php -r "require 'vendor/autoload.php'; \$app=require 'bootstrap/app.php'; \$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); \$n = \\App\\Models\\Notification::orderBy('created_at','desc')->take(5)->get(['id','title','push_sent','push_success','created_at']); foreach(\$n as \$x) { echo \"ID {\$x->id}: {\$x->title} | Sent: \" . (\$x->push_sent ? 'YES' : 'NO') . \" | Success: \" . (\$x->push_success ? 'YES' : 'NO') . PHP_EOL; }"
```

### Via MySQL

```sql
SELECT id, title, device_id, push_sent, push_success, created_at
FROM notifications
ORDER BY created_at DESC
LIMIT 10;
```

---

## 📊 Interpretasi Log

### ✅ Log Sukses (Backend Bekerja Normal)

```
[2026-02-20 01:41:32] local.INFO: FcmNotificationService: Push notification berhasil dikirim.
[2026-02-20 01:41:32] local.INFO: NotificationService: Push notification result {"notification_id":34,"success":true,"total_sent":1}
[2026-02-20 01:41:32] local.DEBUG: NotificationService: Updated notification with push result {"notification_id":34,"push_sent":1,"push_success":1}
```

**Artinya**: Backend sudah benar, FCM push terkirim ke Firebase

**Jika masih tidak muncul pop-up**, masalahnya di **Flutter** (channel belum setup)

### ❌ Log Error FCM Credentials

```
[2026-02-19 12:41:00] local.WARNING: FcmNotificationService: Firebase credentials file tidak ditemukan.
```

**Solusi**:

1. Check `.env` - pastikan `FCM_CREDENTIALS_PATH` di-comment atau kosong
2. Run: `php artisan config:clear`

### ❌ Log Error Invalid Token

```
[2026-02-20 01:45:00] local.WARNING: FcmNotificationService: Push notification gagal dikirim. {"status":404}
```

**Artinya**: FCM token invalid/expired

**Solusi**: User perlu re-register FCM token dari Flutter app

---

## 🎯 Diagnosis Lengkap

Run script diagnosis otomatis:

```bash
php diagnose_notification.php
```

Output akan show:

1. ✅/❌ Template configuration
2. ✅/❌ Recent notification push status
3. 📋 FCM payload structure
4. 💡 Solusi jika ada masalah

---

## 📱 Jika Pop-Up Tidak Muncul Tapi Log Sukses

**Cek ini:**

1. **Backend logs show success?** → ✅ (lihat log di atas)
2. **`push_sent` and `push_success` = YES?** → ✅ (cek via `diagnose_notification.php`)
3. **Template priority = high/critical?** → ✅ (sudah HIGH)
4. **Flutter notification channel setup?** → ❌ **INI MASALAHNYA!**

**Solusi**: Buka [FLUTTER_HEADS_UP_NOTIFICATION.md](FLUTTER_HEADS_UP_NOTIFICATION.md)

Pastikan di Flutter ada:

```dart
const AndroidNotificationChannel tripChannel = AndroidNotificationChannel(
  'mototracker_trip', // Harus sama dengan backend!
  'Trip Notifications',
  importance: Importance.high, // ← WAJIB HIGH/MAX untuk pop-up!
);

await androidPlugin?.createNotificationChannel(tripChannel);
```

**Tanpa ini, Android TIDAK akan menampilkan heads-up notification!**

---

## 🚨 Common Issues

| Gejala                                 | Penyebab                              | Solusi                                         |
| -------------------------------------- | ------------------------------------- | ---------------------------------------------- |
| Notif masuk drawer tapi tidak pop-up   | Flutter channel importance bukan HIGH | Setup channel dengan `Importance.high`         |
| Log show "credentials tidak ditemukan" | FCM credentials path error            | Comment `FCM_CREDENTIALS_PATH` di `.env`       |
| `push_sent = NO` di database           | FCM service tidak jalan               | Check `FcmNotificationService::isConfigured()` |
| Log show 404/invalid token             | FCM token expired                     | Re-register token dari app                     |

---

## 📞 Quick Troubleshooting Commands

```bash
# 1. Check FCM configuration
php debug_fcm.php

# 2. Check FCM token
php check_fcm_token.php

# 3. Send test notification
php send_test_notification.php

# 4. Diagnose notification setup
php diagnose_notification.php

# 5. Clear cache
php artisan config:clear && php artisan cache:clear
```

---

**Status Saat Ini**:

- ✅ Backend: 100% OK (FCM push berhasil dikirim)
- ❌ Flutter: Perlu setup notification channels dengan `Importance.high`

**Next Step**: Implement Flutter setup dari [FLUTTER_HEADS_UP_NOTIFICATION.md](FLUTTER_HEADS_UP_NOTIFICATION.md)

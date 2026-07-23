# Notification Category Filter API - Complete Guide

## 📋 Overview

API untuk memfilter notifikasi berdasarkan **kategori** sudah tersedia dan siap digunakan. Sistem ini mendukung filter berdasarkan:

- ✅ **Kategori** (service, trip, alert, insight, system)
- ✅ **Status Baca** (unread/read)
- ✅ **Kombinasi Filter** (category + unread)
- ✅ **Guest Mode** dengan header `X-Device-ID`

---

## 🎯 Kategori Notifikasi

| Key       | Nama Kategori         | Deskripsi                                | Use Case                                |
| --------- | --------------------- | ---------------------------------------- | --------------------------------------- |
| `service` | Servis                | Notifikasi terkait jadwal servis motor   | Pengingat servis, servis jatuh tempo    |
| `trip`    | Perjalanan            | Notifikasi terkait perjalanan & tracking | Perjalanan selesai, odometer ter-update |
| `alert`   | Peringatan            | Notifikasi prioritas tinggi              | Servis darurat, servis terlambat        |
| `insight` | Insight & Rekomendasi | Tips & insight pola penggunaan           | Rekomendasi perawatan, tips hemat BBM   |
| `system`  | Sistem                | Notifikasi sistem & pengumuman           | Update aplikasi, maintenance            |

---

## 📡 API Endpoints

### Base Endpoint

```
GET /api/v1/motorcycle/notifications
```

### Query Parameters

| Parameter  | Type    | Required | Default | Description                                                             |
| ---------- | ------- | -------- | ------- | ----------------------------------------------------------------------- |
| `category` | string  | No       | `all`   | Filter by category key: `service`, `trip`, `alert`, `insight`, `system` |
| `unread`   | boolean | No       | `false` | Filter unread only: `true` / `false`                                    |
| `per_page` | integer | No       | `10`    | Items per page (1-100)                                                  |
| `page`     | integer | No       | `1`     | Page number for pagination                                              |

### Headers (Guest Mode)

```http
X-Device-ID: <your-device-uuid>
Authorization: Bearer <token>  # Optional jika sudah login
```

---

## 💡 Contoh Request & Response

### 1. Get All Notifications (No Filter)

```http
GET /notifications?per_page=10
```

**Response:**

```json
{
    "success": true,
    "message": "Notifications retrieved successfully",
    "data": [
        {
            "id": 15,
            "category_key": "trip",
            "category_name": "Perjalanan",
            "title": "Perjalanan Selesai",
            "message": "✅ Perjalanan selesai! Honda Beat 2023 menempuh 15.5 km dalam 25 menit. Kecepatan rata-rata: 37.2 km/h. Odometer sekarang: 5,350 km.",
            "is_read": false,
            "created_at": "2026-02-19T14:30:00+07:00"
        },
        {
            "id": 14,
            "category_key": "service",
            "category_name": "Servis",
            "title": "Pengingat Servis",
            "message": "⏰ Waktunya ganti oli! Honda Beat sudah menempuh 950 km dari servis terakhir.",
            "is_read": false,
            "created_at": "2026-02-19T10:00:00+07:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 3,
        "per_page": 10,
        "total": 25
    },
    "unread_count": 5
}
```

---

### 2. Filter by Category: Service

```http
GET /notifications?category=service&per_page=20
```

**Response:**

```json
{
    "success": true,
    "message": "Notifications retrieved successfully",
    "data": [
        {
            "id": 14,
            "category_key": "service",
            "category_name": "Servis",
            "title": "Pengingat Servis",
            "message": "⏰ Waktunya ganti oli! Honda Beat sudah menempuh 950 km dari servis terakhir.",
            "priority": "normal",
            "is_read": false,
            "created_at": "2026-02-19T10:00:00+07:00"
        },
        {
            "id": 10,
            "category_key": "service",
            "category_name": "Servis",
            "title": "Pengingat Servis",
            "message": "⏰ Servis berikutnya dalam 200 km lagi. Target servis: 6,000 km.",
            "priority": "normal",
            "is_read": true,
            "created_at": "2026-02-18T09:00:00+07:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 20,
        "total": 8
    },
    "unread_count": 5
}
```

---

### 3. Filter by Category: Trip (Perjalanan)

```http
GET /notifications?category=trip&per_page=20
```

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "category_key": "trip",
            "category_name": "Perjalanan",
            "title": "Perjalanan Selesai",
            "message": "✅ Perjalanan selesai! Honda Beat 2023 menempuh 15.5 km dalam 25 menit. Kecepatan rata-rata: 37.2 km/h. Odometer sekarang: 5,350 km.",
            "priority": "normal",
            "vehicle": {
                "id": 1,
                "title": "Honda Beat 2023",
                "odometer": 5350
            },
            "is_read": false,
            "created_at": "2026-02-19T14:30:00+07:00"
        },
        {
            "id": 12,
            "category_key": "trip",
            "title": "Jarak Ditambahkan",
            "message": "✅ Jarak 25.5 km berhasil ditambahkan. Odometer sekarang: 5,334.5 km.",
            "is_read": false,
            "created_at": "2026-02-18T18:00:00+07:00"
        }
    ],
    "meta": {
        "total": 10
    },
    "unread_count": 5
}
```

---

### 4. Filter Unread Only

```http
GET /notifications?unread=true
```

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "category_key": "trip",
            "message": "✅ Perjalanan selesai! ...",
            "is_read": false
        },
        {
            "id": 14,
            "category_key": "service",
            "message": "⏰ Waktunya ganti oli! ...",
            "is_read": false
        }
    ],
    "unread_count": 5
}
```

---

### 5. Kombinasi Filter: Service + Unread ⭐

```http
GET /notifications?category=service&unread=true
```

**Use Case:** Badge count untuk tab "Servis" yang belum dibaca

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 14,
            "category_key": "service",
            "category_name": "Servis",
            "title": "Pengingat Servis",
            "message": "⏰ Waktunya ganti oli! Honda Beat sudah menempuh 950 km dari servis terakhir.",
            "is_read": false,
            "created_at": "2026-02-19T10:00:00+07:00"
        }
    ],
    "meta": {
        "total": 3
    },
    "unread_count": 3 // Badge count untuk tab Servis
}
```

---

### 6. Alert Notifications (Priority High)

```http
GET /notifications?category=alert
```

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 20,
            "category_key": "alert",
            "category_name": "Peringatan",
            "title": "Servis Darurat!",
            "message": "🚨 DARURAT! Honda Beat sudah terlambat 500 km dari jadwal servis. Segera servis!",
            "priority": "high",
            "is_read": false,
            "created_at": "2026-02-19T15:00:00+07:00"
        }
    ],
    "unread_count": 5
}
```

---

## 🔥 Guest Mode Examples

### Register FCM Token (Guest)

```http
POST /device-tokens/register
Header: X-Device-ID: guest-device-12345...

{
    "fcm_token": "dXhY7abc...FakeToken",
    "platform": "android",
    "device_name": "Samsung Galaxy A54"
}
```

### Get Trip Notifications (Guest)

```http
GET /notifications?category=trip&per_page=20
Header: X-Device-ID: guest-device-12345...
```

### Get Unread Service Alerts (Guest)

```http
GET /notifications?category=service&unread=true
Header: X-Device-ID: guest-device-12345...
```

**Response sama dengan authenticated mode!**

---

## 🎨 UI Implementation Guide (Flutter)

### Tab Navigation dengan Badge Count

```dart
class NotificationScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 6,
      child: Scaffold(
        appBar: AppBar(
          title: Text('Notifikasi'),
          bottom: TabBar(
            isScrollable: true,
            tabs: [
              _buildTab('Semua', unreadCount: 5),
              _buildTab('Servis', categoryKey: 'service', unreadCount: 3),
              _buildTab('Perjalanan', categoryKey: 'trip', unreadCount: 1),
              _buildTab('Peringatan', categoryKey: 'alert', unreadCount: 1),
              _buildTab('Insight', categoryKey: 'insight', unreadCount: 0),
              _buildTab('Sistem', categoryKey: 'system', unreadCount: 0),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            NotificationListView(category: 'all'),
            NotificationListView(category: 'service'),
            NotificationListView(category: 'trip'),
            NotificationListView(category: 'alert'),
            NotificationListView(category: 'insight'),
            NotificationListView(category: 'system'),
          ],
        ),
      ),
    );
  }

  Widget _buildTab(String label, {String? categoryKey, int unreadCount = 0}) {
    return Tab(
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(label),
          if (unreadCount > 0) ...[
            SizedBox(width: 4),
            Container(
              padding: EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: Colors.red,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                unreadCount > 99 ? '99+' : unreadCount.toString(),
                style: TextStyle(fontSize: 10, color: Colors.white),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
```

### Fetch Notifications by Category

```dart
class NotificationService {
  final ApiService _api;

  Future<NotificationListResponse> getNotifications({
    String category = 'all',
    bool? unreadOnly,
    int perPage = 20,
    int page = 1,
  }) async {
    final queryParams = {
      'per_page': perPage.toString(),
      'page': page.toString(),
    };

    if (category != 'all') {
      queryParams['category'] = category;
    }

    if (unreadOnly == true) {
      queryParams['unread'] = 'true';
    }

    final response = await _api.get(
      '/notifications',
      queryParameters: queryParams,
    );

    return NotificationListResponse.fromJson(response.data);
  }

  // Get badge count untuk tab tertentu
  Future<int> getUnreadCount({String? category}) async {
    final response = await getNotifications(
      category: category ?? 'all',
      unreadOnly: true,
      perPage: 1, // Hanya ambil meta
    );

    return response.unreadCount;
  }
}
```

### Badge Count Update (Real-time)

```dart
class NotificationProvider extends ChangeNotifier {
  Map<String, int> _badgeCounts = {
    'all': 0,
    'service': 0,
    'trip': 0,
    'alert': 0,
    'insight': 0,
    'system': 0,
  };

  Future<void> updateBadgeCounts() async {
    _badgeCounts['all'] = await _service.getUnreadCount();
    _badgeCounts['service'] = await _service.getUnreadCount(category: 'service');
    _badgeCounts['trip'] = await _service.getUnreadCount(category: 'trip');
    _badgeCounts['alert'] = await _service.getUnreadCount(category: 'alert');
    _badgeCounts['insight'] = await _service.getUnreadCount(category: 'insight');
    _badgeCounts['system'] = await _service.getUnreadCount(category: 'system');

    notifyListeners();
  }

  int getBadgeCount(String category) => _badgeCounts[category] ?? 0;
}
```

---

## 📊 Use Cases

### 1. Dashboard - Latest Notifications (All Categories)

```http
GET /notifications?per_page=5
```

Tampilkan 5 notifikasi terbaru dari semua kategori.

---

### 2. Tab "Servis" - Service Notifications Only

```http
GET /notifications?category=service&per_page=20
```

Tampilkan hanya notifikasi servis untuk tab khusus.

---

### 3. Badge Count - Unread Service Alerts

```http
GET /notifications?category=service&unread=true&per_page=1
```

Ambil `unread_count` dari response untuk badge count.

---

### 4. Alert Center - High Priority Only

```http
GET /notifications?category=alert&per_page=10
```

Tampilkan notifikasi prioritas tinggi di alert center.

---

### 5. Trip History - Travel Notifications

```http
GET /notifications?category=trip&per_page=50
```

Riwayat notifikasi perjalanan (perjalanan selesai, odometer update).

---

## 🔍 Backend Implementation

### Controller (Already Implemented!)

```php
// app/Http/Controllers/Api/NotificationController.php

public function index(Request $request): JsonResponse
{
    $query = Notification::query();
    $this->applyOwnerFilter($query, $request);

    $query->with(['category', 'vehicle'])->latest();

    // Filter by category
    if ($request->has('category') && $request->category !== 'all') {
        $query->byCategory($request->category);
    }

    // Filter by unread status
    if ($request->has('unread') && filter_var($request->unread, FILTER_VALIDATE_BOOLEAN)) {
        $query->unread();
    }

    $perPage = $request->get('per_page', 10);
    $notifications = $query->paginate($perPage);

    // Get unread count
    $unreadQuery = Notification::query();
    $this->applyOwnerFilter($unreadQuery, $request);
    $unreadCount = $unreadQuery->unread()->count();

    return $this->successResponse([
        'data' => $notifications->items(),
        'meta' => [...],
        'unread_count' => $unreadCount,
    ]);
}
```

### Model Scope (Already Implemented!)

```php
// app/Models/Notification.php

public function scopeByCategory($query, string $categoryKey)
{
    return $query->whereHas('category', function ($q) use ($categoryKey) {
        $q->where('key', $categoryKey);
    });
}

public function scopeUnread($query)
{
    return $query->whereNull('read_at');
}
```

---

## 🧪 Cara Testing Notifikasi - Step by Step

### Metode 1: Test via Admin Panel (Paling Mudah! ✅)

**Langkah-langkah:**

#### **1. Login ke Admin Panel**

```
http://localhost/admin/login
```

#### **2. Buat/Cek Template Notifikasi**

- Menu: **Notifikasi** → **Template Notifikasi**
- Klik **Tambah Template**
- Isi form:
    ```
    Nama Template: Test Trip Notification
    Kategori: Perjalanan
    Judul: Perjalanan Selesai
    Pesan: ✅ Perjalanan selesai! {vehicle_name} menempuh {distance} km. Odometer: {current_km} km.
    ```
- **Simpan**

#### **3. Kirim Notifikasi Manual**

- Menu: **Notifikasi** → **Kirim Notifikasi**
- Pilih:
    - Template: **Test Trip Notification**
    - Kendaraan: _Pilih salah satu kendaraan_
    - Penerima: _Pilih user/guest_
- Klik **Kirim**
- ✅ **Notifikasi terkirim!**

#### **4. Cek Hasilnya**

- **Via API**: GET `/api/v1/motorcycle/notifications`
- **Via Admin**: Menu **Notifikasi** → **Daftar Notifikasi**
- **Via Flutter**: Refresh notification list

---

### Metode 2: Test via Postman API

#### **1. Import Postman Collection**

**File Collection:**

```
Motorcycle_Management_API.postman_collection.json
Motorcycle_Management_Local.postman_environment.json
```

**Cara Import:**

1. Buka Postman
2. Klik **Import** → Drag & drop 2 file di atas
3. Pilih environment: **Motorcycle Management Local**
4. ✅ Siap test!

#### **2. Setup Environment**

Pastikan variabel environment sudah benar:

```javascript
base_url: http://localhost/api/v1/motorcycle
token: Bearer <your-jwt-token>
device_id: <your-device-uuid>  // Untuk guest mode
```

#### **3. Test Endpoint Notifikasi**

**📂 Folder: Notifications & Device Tokens**

**Basic Tests:**

| #   | Request               | Endpoint                                          | Fungsi                 |
| --- | --------------------- | ------------------------------------------------- | ---------------------- |
| 1   | Get All Notifications | `GET /notifications`                              | Ambil semua notifikasi |
| 2   | Get Unread Only       | `GET /notifications?unread=true`                  | Filter belum dibaca    |
| 3a  | Filter: Service       | `GET /notifications?category=service`             | Notifikasi servis      |
| 3b  | Filter: Trip          | `GET /notifications?category=trip`                | Notifikasi perjalanan  |
| 3c  | Filter: Alert         | `GET /notifications?category=alert`               | Notifikasi peringatan  |
| 3d  | Filter: Insight       | `GET /notifications?category=insight`             | Notifikasi insight     |
| 3e  | Filter: System        | `GET /notifications?category=system`              | Notifikasi sistem      |
| 4a  | Service + Unread      | `GET /notifications?category=service&unread=true` | Kombinasi filter       |
| 4b  | Trip + Unread         | `GET /notifications?category=trip&unread=true`    | Kombinasi filter       |
| 4c  | Alert + Unread        | `GET /notifications?category=alert&unread=true`   | Kombinasi filter       |
| 5   | Mark as Read          | `POST /notifications/{id}/read`                   | Tandai sudah dibaca    |
| 6   | Mark All Read         | `POST /notifications/mark-all-read`               | Tandai semua dibaca    |

**Guest Mode Tests:**

| #   | Request                       | Header                       | Fungsi                 |
| --- | ----------------------------- | ---------------------------- | ---------------------- |
| 7a  | Register Device Token (Guest) | `X-Device-ID: {{device_id}}` | Daftar FCM token       |
| 7b  | Get Notifications (Guest)     | `X-Device-ID: {{device_id}}` | Ambil notifikasi guest |
| 7c  | Filter by Category (Guest)    | `X-Device-ID: {{device_id}}` | Filter kategori guest  |
| 7d  | Mark as Read (Guest)          | `X-Device-ID: {{device_id}}` | Tandai dibaca guest    |

#### **4. Expected Response**

**✅ Success Response:**

```json
{
    "success": true,
    "message": "Notifications retrieved successfully",
    "data": [
        {
            "id": 15,
            "category_key": "trip",
            "category_name": "Perjalanan",
            "title": "Perjalanan Selesai",
            "message": "✅ Perjalanan selesai! Honda Beat menempuh 15.5 km.",
            "is_read": false,
            "created_at": "2026-02-19T14:30:00+07:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 25
    },
    "unread_count": 5
}
```

**❌ Error Response:**

```json
{
    "success": false,
    "message": "Invalid category",
    "errors": {
        "category": [
            "Category must be one of: service, trip, alert, insight, system"
        ]
    }
}
```

---

### Metode 3: Test Automated Trigger (Real Scenario)

#### **Test Notifikasi Trip Completion**

**1. Buat Trip Baru**

```http
POST /api/v1/motorcycle/trips
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "vehicle_id": 1,
  "start_latitude": -6.2088,
  "start_longitude": 106.8456,
  "start_address": "Jakarta Pusat",
  "tracking_mode": "gps"
}
```

**2. Finish Trip**

```http
POST /api/v1/motorcycle/trips/{trip_id}/finish
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "end_latitude": -6.1751,
  "end_longitude": 106.8650,
  "end_address": "Monas, Jakarta",
  "distance": 15.5,
  "duration_minutes": 25,
  "average_speed": 37.2
}
```

**3. Cek Notifikasi Otomatis**

```http
GET /api/v1/motorcycle/notifications?category=trip&unread=true
```

**Expected Output:**

```json
{
    "data": [
        {
            "id": 16,
            "category_key": "trip",
            "title": "Perjalanan Selesai",
            "message": "✅ Perjalanan selesai! Honda Beat menempuh 15.5 km dalam 25 menit. Odometer: 5,365 km.",
            "is_read": false,
            "created_at": "2026-02-19T15:45:00+07:00"
        }
    ]
}
```

✅ **Notifikasi otomatis terkirim saat trip selesai!**

---

### Metode 4: Test FCM Push Notification (Mobile)

#### **Prerequisites:**

- Flutter app sudah terinstall FCM
- Device token sudah ter-register

#### **1. Register FCM Token**

```http
POST /api/v1/motorcycle/device-tokens
Authorization: Bearer {{token}}  # Atau X-Device-ID untuk guest
Content-Type: application/json

{
  "fcm_token": "your-fcm-token-from-firebase",
  "device_type": "android",
  "device_name": "Samsung Galaxy S21"
}
```

#### **2. Trigger Notifikasi**

Gunakan salah satu metode di atas (admin panel / finish trip / API manual).

#### **3. Cek Push Notification**

- **Android**: Muncul di notification tray
- **iOS**: Muncul di notification center
- **Flutter**: Firebase `onMessage` handler terpanggil

**Firebase Debug Log:**

```
[FCM] Message received: Perjalanan Selesai
[FCM] Title: Perjalanan Selesai
[FCM] Body: ✅ Perjalanan selesai! Honda Beat menempuh 15.5 km...
```

---

### Metode 5: Test Filter by Category (Flutter)

#### **Code Example:**

```dart
// Get trip notifications only
final response = await http.get(
  Uri.parse('$baseUrl/notifications?category=trip&unread=true'),
  headers: {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  },
);

final data = jsonDecode(response.body);
print('Trip notifications: ${data['data'].length}');
print('Unread count: ${data['unread_count']}');
```

**Expected Output:**

```
Trip notifications: 3
Unread count: 3
```

---

### 🎯 Quick Testing Workflow

**Untuk Test Cepat (5 menit):**

1. ✅ **Admin Panel**: Login → Kirim notifikasi manual (pilih template "Trip")
2. ✅ **Postman**: GET `/notifications?category=trip` → Lihat notifikasi muncul
3. ✅ **Mark as Read**: POST `/notifications/{id}/read`
4. ✅ **Verify**: GET `/notifications?unread=true` → Count berkurang

**Untuk Test Lengkap (15 menit):**

1. ✅ Test semua kategori (service, trip, alert, insight, system)
2. ✅ Test kombinasi filter (category + unread)
3. ✅ Test guest mode dengan `X-Device-ID`
4. ✅ Test pagination (per_page, page)
5. ✅ Test mark all as read
6. ✅ Test badge count accuracy

---

## ✅ Testing Checklist

### Manual Testing via Postman

- [ ] Get all notifications without filter
- [ ] Filter by category: `service`
- [ ] Filter by category: `trip`
- [ ] Filter by category: `alert`
- [ ] Filter by category: `insight`
- [ ] Filter by category: `system`
- [ ] Filter unread only
- [ ] Kombinasi: `category=service&unread=true`
- [ ] Kombinasi: `category=trip&unread=true`
- [ ] Kombinasi: `category=alert&unread=true`
- [ ] Pagination: `per_page=20&page=2`
- [ ] Guest mode: dengan header `X-Device-ID`
- [ ] Badge count accuracy

### Guest Mode Testing

- [ ] Register FCM token sebagai guest
- [ ] Get notifications (guest mode)
- [ ] Filter by category (guest mode)
- [ ] Mark as read (guest mode)
- [ ] Login → Check data sync

---

## 🎯 Performance Tips

### 1. Optimize Badge Count Queries

Jangan panggil API untuk setiap tab secara terpisah. Gunakan single endpoint dengan multiple filters:

```dart
// ❌ Bad - 6 API calls
final allCount = await getUnreadCount();
final serviceCount = await getUnreadCount(category: 'service');
final tripCount = await getUnreadCount(category: 'trip');
// ...

// ✅ Good - 1 API call with post-processing
final allNotifications = await getNotifications(unreadOnly: true, perPage: 100);
final serviceCount = allNotifications.data.where((n) => n.categoryKey == 'service').length;
final tripCount = allNotifications.data.where((n) => n.categoryKey == 'trip').length;
```

### 2. Cache Badge Counts

Update badge count hanya saat:

- App dibuka
- Notifikasi baru diterima (via FCM)
- User mark as read/read all
- User switch tab (interval 30 detik)

### 3. Pagination

Gunakan pagination untuk performa:

```dart
// Load 20 per page
final notifications = await getNotifications(
  category: 'service',
  perPage: 20,
  page: currentPage,
);
```

---

## ➕ Menambah Kategori Baru (Tanpa Coding!)

### 📝 Overview

Sistem notifikasi dirancang **sangat fleksibel**. Anda bisa menambah kategori baru (misalnya "Repair", "Fuel", "Insurance") **tanpa perlu coding sama sekali** untuk setup awal. Coding hanya diperlukan jika ingin **automated trigger**.

---

### 🎯 Flow Lengkap: Tambah Kategori "Repair"

#### **Step 1: Tambah Kategori di Admin Panel** (NO CODING ✅)

1. Login ke Admin Panel
2. Navigasi: **Manajemen Filter** → Tab **"Kategori Notifikasi"**
3. Klik tombol **"Tambah Kategori"**
4. Isi form:
    ```
    Nama Kategori: Perbaikan
    Key: repair
    Icon: 🔧 (atau nama icon)
    Color: #FF6B35
    Urutan: 6
    Status: ✅ Aktif
    ```
5. Klik **"Simpan"**

**Hasil:**

- ✅ Kategori "repair" muncul di database
- ✅ API `/notification-categories` otomatis return kategori baru
- ✅ Filter `?category=repair` langsung bisa digunakan
- ✅ Tab "Perbaikan" bisa ditambahkan di Flutter

---

#### **Step 2: Buat Template Notifikasi** (NO CODING ✅)

1. Navigasi: **Admin Panel** → **Notifikasi** → **Template**
2. Klik **"Tambah Template"**
3. Isi form:

    ```
    Nama Template: Perbaikan Selesai
    Kategori: Perbaikan (pilih dari dropdown)
    Trigger Type: repair_completed
    Channel: Push + In-App
    Priority: Normal

    Pesan Template:
    🔧 Perbaikan {vehicle_name} selesai!
    Bengkel: {workshop_name}
    Biaya: Rp {repair_cost}
    Odometer: {current_km} km

    Status: ✅ Aktif
    ```

4. Klik **"Simpan"**

**Hasil:**

- ✅ Template tersimpan dengan `category_key = 'repair'`
- ✅ Template siap digunakan untuk kirim notifikasi
- ✅ Variabel `{vehicle_name}`, `{workshop_name}`, dll akan diganti otomatis

---

#### **Step 3: Kirim Notifikasi Manual** (NO CODING ✅)

**Via Admin Panel:**

1. Buka **Admin Panel** → **Notifikasi** → **Kirim Notifikasi**
2. Pilih template "Perbaikan Selesai"
3. Pilih user target / vehicle
4. Isi variabel yang dibutuhkan
5. Klik **"Kirim"**

**Via API (untuk testing):**

```bash
POST /admin/notifications/send
{
  "template_id": 10,
  "user_id": 5,
  "vehicle_id": 3,
  "variables": {
    "repair_cost": "500000",
    "workshop_name": "Bengkel Jaya Motor"
  }
}
```

**Hasil:**

- ✅ Notifikasi terkirim dengan `category_key = 'repair'`
- ✅ Muncul di tab "Perbaikan" di mobile app
- ✅ Filter `?category=repair` menampilkan notifikasi ini
- ✅ Push notification terkirim (jika channel = push)

---

### 🔧 Kapan Perlu Coding?

**TIDAK PERLU CODING untuk:**

- ✅ Tambah kategori baru di admin
- ✅ Buat template notifikasi
- ✅ Kirim notifikasi manual via admin panel
- ✅ Filter notifikasi berdasarkan kategori baru
- ✅ Tampilkan tab baru di Flutter (update UI saja)

**PERLU CODING untuk:**

- ❌ **Automated trigger** (notifikasi otomatis tanpa user action)
- ❌ **Custom logic** untuk trigger berdasarkan event tertentu
- ❌ **Variabel baru** yang belum ada di sistem

---

### 📌 Contoh: Automated Trigger untuk "Repair"

Jika ingin notifikasi "Perbaikan Selesai" **otomatis terkirim** saat admin/user mark perbaikan sebagai complete, baru perlu coding:

#### **1. Tambah Method di Service (Coding Required)**

```php
// app/Services/RepairService.php

public function completeRepair($repairId)
{
    $repair = Repair::findOrFail($repairId);
    $repair->update(['status' => 'completed']);

    // Trigger notifikasi otomatis
    $this->sendRepairCompletedNotification($repair);

    return $repair;
}

private function sendRepairCompletedNotification($repair)
{
    $notificationService = app(\App\Services\NotificationService::class);

    // Cari template kategori "repair"
    $template = \App\Models\NotificationTemplate::where('category_key', 'repair')
        ->where('trigger_type', 'repair_completed')
        ->where('is_active', true)
        ->first();

    if (!$template) {
        Log::info('No active template for repair_completed');
        return;
    }

    // Kirim notifikasi
    $notificationService->sendFromTemplate(
        $template,
        [
            'repair_cost' => number_format($repair->cost),
            'workshop_name' => $repair->workshop_name,
            'current_km' => number_format($repair->vehicle->odometer),
        ],
        $repair->vehicle->user,  // User
        $repair->vehicle->device_id,  // Device ID (guest)
        $repair->vehicle  // Vehicle
    );
}
```

#### **2. Panggil Method saat Event Terjadi**

```php
// app/Http/Controllers/RepairController.php

public function markAsCompleted($id)
{
    $repairService = app(\App\Services\RepairService::class);
    $repair = $repairService->completeRepair($id);

    return response()->json([
        'success' => true,
        'message' => 'Perbaikan selesai dan notifikasi terkirim',
        'data' => $repair,
    ]);
}
```

**Hasil:**

- ✅ Setiap kali perbaikan selesai → notifikasi otomatis terkirim
- ✅ Notifikasi masuk kategori "repair"
- ✅ Push notification + in-app notification

---

### 🎨 Tambah Tab di Flutter (Update UI)

```dart
// lib/screens/notification_screen.dart

TabBar(
  tabs: [
    Tab(text: 'Semua'),
    Tab(text: 'Servis'),
    Tab(text: 'Perjalanan'),
    Tab(text: 'Peringatan'),
    Tab(text: 'Perbaikan'), // ← Tab baru (NO CODING di backend!)
    Tab(text: 'Sistem'),
  ],
),

// Di TabBarView:
TabBarView(
  children: [
    NotificationListView(category: 'all'),
    NotificationListView(category: 'service'),
    NotificationListView(category: 'trip'),
    NotificationListView(category: 'alert'),
    NotificationListView(category: 'repair'), // ← Filter by 'repair'
    NotificationListView(category: 'system'),
  ],
)
```

**API Call:**

```dart
// Otomatis filter kategori repair
GET /notifications?category=repair
```

---

### ✅ Summary: Kategori Baru Tanpa Coding

| Tahap                    | Lokasi                            | Coding?    | Tools          |
| ------------------------ | --------------------------------- | ---------- | -------------- |
| **1. Tambah Kategori**   | Admin Panel → Manajemen Filter    | ❌ NO      | Web UI         |
| **2. Buat Template**     | Admin Panel → Notifikasi Template | ❌ NO      | Web UI         |
| **3. Kirim Manual**      | Admin Panel → Kirim Notifikasi    | ❌ NO      | Web UI / API   |
| **4. Filter API**        | `/notifications?category=repair`  | ❌ NO      | Auto-available |
| **5. Tab di Flutter**    | Update UI TabBar                  | ❌ NO\*    | Flutter code   |
| **6. Automated Trigger** | Backend Service Layer             | ✅ **YES** | PHP coding     |

> \*) Update Flutter UI bukan "backend coding", hanya update parameter category di API call

---

### 📋 Checklist: Tambah Kategori "Fuel" (Bahan Bakar)

- [ ] **Admin Panel**: Tambah kategori `key=fuel`, nama "Bahan Bakar" ✅ No coding
- [ ] **Admin Panel**: Buat template "Isi BBM Tercatat" dengan `category_key=fuel` ✅ No coding
- [ ] **Flutter**: Tambah tab "Bahan Bakar" di NotificationScreen ✅ Update UI
- [ ] **API**: Test filter `?category=fuel` via Postman ✅ Auto-available
- [ ] **Kirim Manual**: Test kirim notifikasi via admin panel ✅ No coding
- [ ] **(Optional) Automated**: Coding trigger saat user isi BBM ⚠️ Perlu coding

---

### 🚀 Kesimpulan

**Untuk USE CASE NORMAL (manual send):**

```
Tambah kategori baru → NO CODING NEEDED! ✅
Tinggal pakai Admin Panel web interface
```

**Untuk AUTOMATED TRIGGER:**

```
Perlu coding di backend service layer ⚠️
Tapi template & kategori tetap pakai Admin Panel
```

**Contoh Real:**

1. Admin buat kategori "Insurance" (key: `insurance`) → **NO CODING**
2. Admin buat template "Asuransi Jatuh Tempo" → **NO CODING**
3. Admin kirim notifikasi manual ke user tertentu → **NO CODING**
4. User lihat notifikasi di tab "Asuransi" → **NO CODING** (Flutter update UI saja)
5. **(Optional)** Sistem otomatis kirim 7 hari sebelum jatuh tempo → **PERLU CODING** (scheduling logic)

---

## 📚 Related Documentation

- [FLUTTER_NOTIFICATION_PUSH.md](FLUTTER_NOTIFICATION_PUSH.md) - Flutter integration guide
- [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - Complete API docs
- [POSTMAN_TRIP_NOTIFICATION_UPDATE.md](POSTMAN_TRIP_NOTIFICATION_UPDATE.md) - Postman collection update

---

## � Variabel Template Notifikasi

### Variabel yang Tersedia (Built-in)

Saat membuat template notifikasi di admin panel, Anda bisa menggunakan variabel berikut yang **otomatis diganti** saat notifikasi dikirim:

#### 🚗 **Kendaraan**

| Variabel          | Deskripsi         | Contoh Output        |
| ----------------- | ----------------- | -------------------- |
| `{vehicle_name}`  | Nama kendaraan    | Honda Beat 2023      |
| `{vehicle_plate}` | Plat nomor        | B 1234 XYZ           |
| `{vehicle_type}`  | Tipe motor        | matic, manual, sport |
| `{current_km}`    | Odometer saat ini | 5,350                |
| `{vehicle_color}` | Warna kendaraan   | Hitam                |
| `{vehicle_year}`  | Tahun kendaraan   | 2023                 |

#### 🔧 **Servis**

| Variabel           | Deskripsi                   | Contoh Output          |
| ------------------ | --------------------------- | ---------------------- |
| `{service_type}`   | Jenis servis                | Ganti Oli              |
| `{service_name}`   | Nama jadwal servis          | Servis Berkala 5000 KM |
| `{km_remaining}`   | KM tersisa sebelum servis   | 150                    |
| `{km_overdue}`     | KM terlambat                | 200                    |
| `{target_km}`      | Target KM servis berikutnya | 6,000                  |
| `{target_date}`    | Tanggal target servis       | 2026-03-15             |
| `{days_remaining}` | Hari tersisa                | 7                      |
| `{last_service}`   | Tanggal servis terakhir     | 2026-01-10             |
| `{workshop_name}`  | Nama bengkel                | Bengkel Jaya Motor     |

#### 🛣️ **Perjalanan**

| Variabel      | Deskripsi           | Contoh Output |
| ------------- | ------------------- | ------------- |
| `{distance}`  | Jarak perjalanan    | 15.5          |
| `{duration}`  | Durasi perjalanan   | 25 menit      |
| `{avg_speed}` | Kecepatan rata-rata | 37.2          |

#### 👤 **Pengguna**

| Variabel      | Deskripsi     | Contoh Output |
| ------------- | ------------- | ------------- |
| `{user_name}` | Nama pengguna | John Doe      |

#### 📅 **Umum**

| Variabel     | Deskripsi        | Contoh Output |
| ------------ | ---------------- | ------------- |
| `{app_name}` | Nama aplikasi    | TringGo   |
| `{date_now}` | Tanggal sekarang | 2026-02-19    |

---

### 🎨 Contoh Penggunaan Variabel

#### **Template: Perjalanan Selesai**

```
✅ Perjalanan selesai! {vehicle_name} menempuh {distance} km dalam {duration}.
Kecepatan rata-rata: {avg_speed} km/h.
Odometer sekarang: {current_km} km.
```

**Output:**

```
✅ Perjalanan selesai! Honda Beat 2023 menempuh 15.5 km dalam 25 menit.
Kecepatan rata-rata: 37.2 km/h.
Odometer sekarang: 5,350 km.
```

#### **Template: Pengingat Servis**

```
⏰ Waktunya {service_type}!
{vehicle_name} sudah menempuh {km_remaining} km dari servis terakhir.
Target servis: {target_km} km.
```

**Output:**

```
⏰ Waktunya Ganti Oli!
Honda Beat 2023 sudah menempuh 950 km dari servis terakhir.
Target servis: 6,000 km.
```

---

### ➕ Menambah Variabel Baru (Perlu Coding)

Jika variabel yang tersedia **tidak cukup** untuk kategori baru Anda (misalnya kategori "Insurance" butuh variabel `{insurance_expiry}`), maka perlu coding di backend.

#### **Langkah-langkah:**

**1. Tambahkan ke Konstanta `AVAILABLE_VARIABLES`**

```php
// app/Services/NotificationService.php

public const AVAILABLE_VARIABLES = [
    // ... variabel existing ...

    // Asuransi (kategori baru)
    'insurance_type'   => 'Jenis asuransi (All Risk, TLO, dll)',
    'insurance_expiry' => 'Tanggal kadaluarsa asuransi',
    'insurance_provider' => 'Nama perusahaan asuransi',
    'premium_amount'   => 'Nominal premi asuransi',
];
```

**2. Update Method `parseTemplate()` (Jika Perlu Custom Logic)**

Jika variabel baru membutuhkan formatting khusus:

```php
// app/Services/NotificationService.php

private function parseTemplate(string $template, array $variables, ?Vehicle $vehicle = null): string
{
    // ... existing code ...

    // Custom formatting untuk insurance_expiry
    if (isset($variables['insurance_expiry'])) {
        $expiryDate = \Carbon\Carbon::parse($variables['insurance_expiry']);
        $variables['insurance_expiry'] = $expiryDate->format('d F Y');
    }

    // ... rest of code ...
}
```

**3. Kirim Variabel Saat Trigger**

```php
$notificationService->sendFromTemplate(
    $template,
    [
        'insurance_type' => 'All Risk',
        'insurance_expiry' => '2026-06-15',
        'insurance_provider' => 'PT Asuransi Jaya',
        'premium_amount' => 'Rp 2.500.000',
    ],
    $user,
    $deviceId,
    $vehicle
);
```

**Hasil:**

```
Template: "🔔 Asuransi {insurance_type} Anda di {insurance_provider} akan berakhir pada {insurance_expiry}. Premium: {premium_amount}"

Output: "🔔 Asuransi All Risk Anda di PT Asuransi Jaya akan berakhir pada 15 Juni 2026. Premium: Rp 2.500.000"
```

---

## �🚀 Ready to Use!

API filter notifikasi **sudah siap digunakan** tanpa perlu coding tambahan di backend. Tinggal:

1. ✅ Import Postman collection yang sudah di-update
2. ✅ Test semua endpoint filter
3. ✅ Implementasi di Flutter dengan UI tab navigation
4. ✅ Setup FCM untuk push notification

Semua endpoint sudah support **Guest Mode** dengan header `X-Device-ID`! 🎉

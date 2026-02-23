# Update Postman Collection - Trip & Notification Endpoints

## 📦 Update Summary

Postman Collection telah di-update dengan endpoint baru untuk **Trip Management** dan **Device Token (FCM Push Notification)** dengan full support untuk **Guest Mode** menggunakan header `X-Device-ID`.

---

## ✨ Endpoint Baru yang Ditambahkan

### 1. **Trips** (Main Section)

Folder baru dengan 5 endpoint:

| Endpoint                         | Method | Description                       |
| -------------------------------- | ------ | --------------------------------- |
| `POST /trips`                    | POST   | Simpan trip dari GPS tracking     |
| `POST /trips/manual-distance` ⭐ | POST   | Tambah jarak manual tanpa GPS     |
| `GET /trips`                     | GET    | List semua trip dengan pagination |
| `GET /trips/{id}`                | GET    | Detail trip dengan GPS points     |
| `DELETE /trips/{id}`             | DELETE | Hapus trip (soft delete)          |

**Fitur Khusus:**

- ✅ Auto-update odometer saat `status=completed`
- ✅ Kirim notifikasi dengan odometer **terbaru** setelah trip
- ✅ Support GPS tracking dengan multiple points
- ✅ Support manual distance entry
- ✅ Pagination & filter by vehicle_id

---

### 2. **Device Tokens (FCM Push)** (Main Section)

Folder baru dengan 3 endpoint:

| Endpoint                          | Method | Description                  |
| --------------------------------- | ------ | ---------------------------- |
| `POST /device-tokens/register` ⭐ | POST   | Register FCM token           |
| `POST /device-tokens/unregister`  | POST   | Hapus FCM token              |
| `GET /device-tokens/status`       | GET    | Cek status token (debugging) |

**Kapan Panggil:**

- ✅ Register: Saat app dibuka pertama kali, token refresh, atau user login
- ✅ Unregister: Saat user logout atau app uninstall

---

### 3. **Guest Mode Sections** 🔥

#### **Trips (Guest)**

5 endpoint yang sama dengan main section, tapi dengan:

- `auth: noauth`
- Header `X-Device-ID: {{device_id}}` **wajib**

#### **Device Tokens (Guest)**

3 endpoint FCM dengan guest mode support:

- Register token tanpa login
- Unregister token sebagai guest
- Cek status token sebagai guest

---

## 🎯 Test Scripts Auto-Save

Semua endpoint **Create/Register** dilengkapi auto-save test script:

### Create Trip / Manual Distance

```javascript
const response = pm.response.json();
if (response.success && response.data) {
    pm.collectionVariables.set("trip_id", response.data.id);
    console.log("✅ Trip created, ID:", response.data.id);
    console.log("📊 New odometer:", response.new_odometer);
}
```

### Register FCM Token

```javascript
const response = pm.response.json();
if (response.success && response.data) {
    pm.collectionVariables.set("device_token_id", response.data.id);
    console.log("✅ FCM Token registered, ID:", response.data.id);
}
```

---

## 🔧 Variables Baru

Ditambahkan 2 collection variables:

```json
{
    "trip_id": "",
    "device_token_id": ""
}
```

Variables ini **auto-filled** dari response API.

---

## 📝 Contoh Request Body

### 1. Create Trip (GPS Tracking)

```json
{
    "vehicle_id": 1,
    "start_time": "2026-02-19T08:00:00+07:00",
    "end_time": "2026-02-19T08:30:00+07:00",
    "total_distance": 15.5,
    "duration": 1800,
    "average_speed": 31.0,
    "max_speed": 50.0,
    "status": "completed",
    "notes": "Perjalanan ke kantor",
    "points": [
        {
            "latitude": -6.2088,
            "longitude": 106.8456,
            "speed": 0,
            "altitude": 10,
            "accuracy": 5,
            "timestamp": "2026-02-19T08:00:00+07:00"
        },
        {
            "latitude": -6.215,
            "longitude": 106.85,
            "speed": 0,
            "altitude": 15,
            "accuracy": 5,
            "timestamp": "2026-02-19T08:30:00+07:00"
        }
    ]
}
```

### 2. Add Manual Distance ⭐

```json
{
    "vehicle_id": 1,
    "distance_km": 25.5,
    "trip_date": "2026-02-19",
    "notes": "Perjalanan pulang kampung"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Jarak berhasil ditambahkan",
    "data": {
        "id": 10,
        "vehicle_id": 1,
        "distance_meters": 25500,
        "start_odometer": 5000,
        "end_odometer": 5025.5
    },
    "distance_added": 25.5,
    "old_odometer": 5000,
    "new_odometer": 5025.5
}
```

### 3. Register FCM Token

```json
{
    "fcm_token": "dXhY7abc...FakeToken123",
    "platform": "android",
    "device_name": "Samsung Galaxy A54"
}
```

**Response:**

```json
{
    "success": true,
    "message": "FCM token berhasil didaftarkan",
    "data": {
        "id": 5,
        "fcm_token": "dXhY7abc...FakeToken123",
        "platform": "android",
        "device_name": "Samsung Galaxy A54",
        "is_active": true
    }
}
```

---

## 🚀 How to Use

### A. Authenticated Mode (Login User)

1. Login via `POST /auth/login`
2. Token otomatis tersimpan di `{{access_token}}`
3. Call endpoint Trip/Device Token seperti biasa

### B. Guest Mode (No Login)

1. Set `{{device_id}}` di collection variables (UUID)
2. Header `X-Device-ID` **otomatis** di-inject via pre-request script
3. Call endpoint yang ada di folder **🔥 Public Endpoints (Guest Mode)**

---

## 📊 Flow Notifikasi Trip

```
1. User selesai trip (GPS tracking ATAU tambah manual)
   ↓
2. Backend update odometer kendaraan
   ↓
3. Backend reload vehicle untuk ambil odometer terbaru
   ↓
4. Backend cari template notifikasi kategori "trip"
   ↓
5. Backend kirim notifikasi dengan variabel:
   - {distance}: Jarak trip (15.5 km)
   - {duration}: Durasi (25 menit)
   - {avg_speed}: Kecepatan rata-rata (37.2 km/h)
   - {current_km}: Odometer TERBARU (5,350 km) ✅
   ↓
6. Push notification dikirim ke FCM token yang terdaftar
   ↓
7. Flutter app terima push: "Perjalanan selesai! Honda Beat menempuh 15.5 km. Odometer sekarang: 5,350 km"
```

---

## 🔥 Perbedaan Trip vs Manual Distance

| Aspek               | GPS Tracking (`POST /trips`)    | Manual Distance (`POST /trips/manual-distance`) |
| ------------------- | ------------------------------- | ----------------------------------------------- |
| **GPS Points**      | ✅ Wajib (min 1 point)          | ❌ Tidak perlu (auto-create dummy point)        |
| **Accuracy**        | ✅ Akurat dari GPS              | ⚠️ User input manual                            |
| **Duration**        | ✅ Hitung dari start-end time   | ⚠️ Tidak ada (same timestamp)                   |
| **Speed**           | ✅ Rata-rata & max speed        | ❌ Null                                         |
| **Use Case**        | Tracking real-time              | Lupa nyalakan tracking, input dari luar app     |
| **Odometer Update** | ✅ Ya (saat status=completed)   | ✅ Ya (otomatis)                                |
| **Notifikasi**      | ✅ Ya (dengan odometer terbaru) | ✅ Ya (dengan odometer terbaru)                 |

---

## 🎯 Testing Checklist

### Trips

- [ ] Create trip GPS tracking → odometer ter-update?
- [ ] Create trip → notifikasi terkirim dengan odometer baru?
- [ ] Add manual distance → odometer ter-update?
- [ ] Add manual distance → notifikasi terkirim?
- [ ] List trips dengan pagination
- [ ] Filter trips by vehicle_id
- [ ] Get trip detail dengan GPS points
- [ ] Delete trip

### Device Tokens

- [ ] Register FCM token (authenticated)
- [ ] Register FCM token (guest)
- [ ] Unregister token
- [ ] Get token status
- [ ] Check multiple devices untuk 1 user

### Guest Mode → Login Sync

- [ ] Register FCM token sebagai guest
- [ ] Create trip sebagai guest
- [ ] Login dengan device_id yang sama
- [ ] Cek: FCM token ter-sync ke user?
- [ ] Cek: Trip ter-sync ke user?

---

## 📚 Related Documentation

- [FLUTTER_NOTIFICATION_PUSH.md](docs/FLUTTER_NOTIFICATION_PUSH.md) - Flutter integration guide
- [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - Complete API docs
- [TripController.php](app/Http/Controllers/TripController.php#L142-L153) - Trip odometer update logic
- [NotificationService.php](app/Services/NotificationService.php) - Template system

---

## ⚙️ Environment Variables Required

```env
# Firebase Cloud Messaging
FCM_CREDENTIALS_PATH=storage/app/firebase/service-account.json
FCM_PROJECT_ID=mototracker-76e10

# App Config
APP_NAME=MotoTracker
```

---

## 🎉 Ready to Test!

Import Postman collection terbaru dan test:

1. **Guest Mode**: Register FCM token → Create trip → Cek notifikasi
2. **Authenticated**: Login → Add manual distance → Cek odometer & notifikasi
3. **Device Sync**: Guest trip → Login → Cek data ter-sync

Semua endpoint sudah dilengkapi description & example yang jelas! 🚀

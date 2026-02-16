# Service Schedule API Documentation

## 📋 Overview

Service Schedule API menyediakan sistem manajemen jadwal servis kendaraan dengan evaluasi status dinamis berdasarkan odometer dan tanggal saat ini.

## ✨ Fitur Utama

### 1. **Status Dinamis**

- **Darurat (Critical)**: Jadwal sudah melewati target atau terlambat
- **Segera (Warning)**: Jadwal akan jatuh tempo dalam reminder threshold
- **Aman (Normal)**: Masih jauh dari jatuh tempo

### 2. **Primary Vehicle Integration**

- Otomatis menggunakan kendaraan utama (primary vehicle)
- Status berubah otomatis ketika ganti primary vehicle
- Odometer real-time dari vehicle saat ini

### 3. **Flexible Reminder**

- `reminder_option_id` bersifat **OPTIONAL**
- Bisa buat jadwal tanpa reminder
- Default: 500km untuk KM-based, 7 hari untuk time-based

### 4. **Dual Schedule Type**

- **KM-based**: Target berdasarkan kilometer
- **Time-based**: Target berdasarkan tanggal

---

## 🔥 Endpoint Baru: Get Primary Vehicle Schedules

**Endpoint paling recommended untuk mobile app!**

### Request

```http
GET /api/v1/motorcycle/service-schedules/primary
Headers:
  X-Device-ID: {{device_id}}
  Authorization: Bearer {{access_token}}  (optional jika sudah login)
```

### Response Success

```json
{
    "success": true,
    "message": "Primary vehicle schedules retrieved successfully.",
    "data": {
        "vehicle": {
            "id": 2,
            "title": "Honda PCX Saya",
            "make": "Honda",
            "model": "PCX 160",
            "current_odometer": 5000,
            "license_plate": "B 1234 XYZ"
        },
        "schedules": [
            {
                "id": 1,
                "service_type_id": 1,
                "service_name": "Ganti Oli",
                "schedule_type": "km",
                "target_km": 8000,
                "current_km": 5000,
                "remaining_km": 3000,
                "status": "normal",
                "status_label": "Aman",
                "message": "Ganti Oli masih 3000 km lagi.",
                "notes": "Ganti oli rutin setiap 4000 km"
            },
            {
                "id": 2,
                "service_type_id": 5,
                "service_name": "Service Berkala",
                "schedule_type": "time",
                "target_date": "2026-03-15",
                "current_date": "2026-02-16",
                "remaining_days": 27,
                "status": "normal",
                "status_label": "Aman",
                "message": "Service Berkala masih 27 hari lagi.",
                "notes": "Check berkala 6 bulan"
            },
            {
                "id": 3,
                "service_type_id": 2,
                "service_name": "Tune Up",
                "schedule_type": "km",
                "target_km": 5200,
                "current_km": 5000,
                "remaining_km": 200,
                "status": "warning",
                "status_label": "Segera",
                "message": "Tune Up akan segera jatuh tempo dalam 200 km.",
                "notes": null
            },
            {
                "id": 4,
                "service_type_id": 3,
                "service_name": "Ganti Ban",
                "schedule_type": "km",
                "target_km": 4800,
                "current_km": 5000,
                "remaining_km": -200,
                "status": "critical",
                "status_label": "Darurat",
                "message": "Ganti Ban sudah melewati target! Segera lakukan service.",
                "notes": "Ban sudah tipis"
            }
        ],
        "summary": {
            "total": 4,
            "critical": 1,
            "warning": 1,
            "normal": 2
        }
    }
}
```

### Keunggulan Endpoint Ini:

✅ **Auto Primary Vehicle** - Tidak perlu pass vehicle_id  
✅ **Real-time Status** - Berdasarkan odometer saat ini  
✅ **Sorted by Priority** - Critical > Warning > Normal  
✅ **Summary Statistics** - Total per status  
✅ **Dynamic Update** - Ganti primary = ganti jadwal otomatis

---

## 📍 All Service Schedule Endpoints

### 1. Create Service Schedule (KM-based)

```http
POST /api/v1/motorcycle/service-schedules
Content-Type: application/json

{
  "vehicle_id": 2,
  "service_type_id": 1,
  "schedule_type": "km",
  "target_km": 10000,
  "reminder_option_id": 1,  // OPTIONAL - bisa null
  "notes": "Ganti oli mesin"
}
```

### 2. Create Service Schedule (Time-based)

```http
POST /api/v1/motorcycle/service-schedules
Content-Type: application/json

{
  "vehicle_id": 2,
  "service_type_id": 5,
  "schedule_type": "time",
  "target_date": "2026-12-31",
  "notes": "Service berkala tahunan"
  // reminder_option_id tidak disertakan = no reminder
}
```

### 3. List All Schedules

```http
GET /api/v1/motorcycle/service-schedules?vehicle_id=2&is_active=1
```

### 4. Get Schedule Detail

```http
GET /api/v1/motorcycle/service-schedules/{id}
```

### 5. Update Schedule

```http
PUT /api/v1/motorcycle/service-schedules/{id}
Content-Type: application/json

{
  "target_km": 12000,
  "notes": "Updated notes"
}
```

### 6. Delete Schedule

```http
DELETE /api/v1/motorcycle/service-schedules/{id}
```

### 7. Get Primary Vehicle Schedules ⭐ NEW

```http
GET /api/v1/motorcycle/service-schedules/primary
```

### 8. Evaluate Status for Specific Vehicle

```http
GET /api/v1/motorcycle/service-schedules/status/{vehicle_id}
```

---

## 🎯 Use Cases

### Dashboard Mobile App

```javascript
// Get jadwal motor utama dengan status real-time
GET / service - schedules / primary;

// Response langsung bisa display:
// - Total jadwal
// - Berapa yang darurat
// - Berapa yang segera
// - List lengkap dengan status masing-masing
```

### Vehicle Switching

```javascript
// User ganti primary vehicle
POST / vehicles / 2 / set - primary;

// Langsung request lagi
GET / service - schedules / primary;

// Response otomatis menunjukkan jadwal motor yang baru
// dengan odometer dari motor yang baru
```

### Create Schedule Without Reminder

```json
{
    "vehicle_id": 2,
    "service_type_id": 1,
    "schedule_type": "km",
    "target_km": 15000
    // Tidak ada reminder_option_id = jadwal tanpa pengingat
}
```

---

## 🔔 Status Logic

### KM-based Schedule

```
remaining_km = target_km - current_km

Status:
- remaining_km <= 0        → CRITICAL (Darurat)
- remaining_km <= threshold → WARNING (Segera)
- remaining_km > threshold  → NORMAL (Aman)

Default threshold: 500 km (jika no reminder_option_id)
```

### Time-based Schedule

```
remaining_days = target_date - today

Status:
- remaining_days < 0        → CRITICAL (Darurat - terlambat)
- remaining_days <= threshold → WARNING (Segera)
- remaining_days > threshold  → NORMAL (Aman)

Default threshold: 7 days (jika no reminder_option_id)
```

---

## 📱 Integration Example (Flutter)

```dart
// Get primary vehicle schedules
Future<ScheduleResponse> getPrimarySchedules() async {
  final response = await dio.get(
    '/service-schedules/primary',
    options: Options(
      headers: {
        'X-Device-ID': deviceId,
        'Authorization': 'Bearer $accessToken',
      },
    ),
  );
  return ScheduleResponse.fromJson(response.data);
}

// Display in UI with color coding
Color getStatusColor(String status) {
  switch (status) {
    case 'critical':
      return Colors.red;
    case 'warning':
      return Colors.orange;
    case 'normal':
      return Colors.green;
    default:
      return Colors.grey;
  }
}
```

---

## ⚠️ Important Notes

1. **reminder_option_id is OPTIONAL**
    - Bisa create schedule tanpa reminder
    - Default threshold akan digunakan (500km / 7 days)

2. **Status is Dynamic**
    - Dihitung real-time berdasarkan odometer saat ini
    - Update odometer → status otomatis berubah

3. **Primary Vehicle Auto-Switch**
    - Set primary vehicle baru → jadwal otomatis ikut vehicle baru
    - Tidak perlu manual switch schedule

4. **Guest Mode Support**
    - Semua endpoint support X-Device-ID header
    - Data akan sync ke user saat login

---

## 🚀 Migration Notes

Database column `reminder_option_id` sudah diubah menjadi **NULLABLE**:

```sql
ALTER TABLE service_schedules
MODIFY reminder_option_id BIGINT UNSIGNED NULL;
```

---

## 📊 Response Status Codes

| Code | Description                |
| ---- | -------------------------- |
| 200  | Success                    |
| 201  | Created                    |
| 404  | Vehicle/Schedule not found |
| 422  | Validation error           |
| 500  | Server error               |

---

**Last Updated**: February 16, 2026  
**Version**: 2.0

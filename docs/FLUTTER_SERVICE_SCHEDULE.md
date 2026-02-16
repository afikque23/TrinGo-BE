# Service Schedules — Complete integration guide (API + Flutter)

Dokumentasi lengkap untuk fitur Service Schedules, termasuk hubungan dengan vehicle, Guest Mode, reminder options, status evaluation, contoh request/response, dan model Dart siap pakai.

Base URL (dev): `http://localhost:8000/api/v1/motorcycle`

**Terminologi singkat**

- `schedule`: aturan service (berbasis KM atau waktu)
- `primary vehicle`: kendaraan utama user; beberapa endpoint (mis. `/service-schedules/primary`) menggunakan ini
- `guest mode`: penggunaan API tanpa user login, diidentifikasi lewat header `X-Device-ID`
- `reminder_option`: opsi pengingat (unit: km/days/weeks/months/years)

---

## 1. Authentication & headers

- Authenticated user: `Authorization: Bearer <access_token>`
- Guest mode (device): `X-Device-ID: <device_uuid>` — wajib untuk operasi guest (create/list untuk device)
- Header umum: `Content-Type: application/json`

Notes:

- Jika terdapat `device_id` pada request (guest), data dibuat terkait device. Saat user login (device sync), server akan mengaitkan atau memindahkan data ke `user_id` sesuai sinkronisasi.

---

## 2. Endpoint detail, schema, dan contoh

Semua body dan response menggunakan JSON. Field wajib ditandai.

### Reminder Options Endpoints

- GET `/reminder-options/active` ⭐
    - Auth: Bearer atau Guest (`X-Device-ID`)
    - Purpose: Mendapatkan list reminder options aktif untuk dropdown
    - Response:
      {
      "success": true,
      "data": [
      {
      "id": 1,
      "label": "100 km sebelum",
      "value": 100,
      "unit": "km",
      "display_text": "100 km sebelum"
      },
      {
      "id": 5,
      "label": "3 hari sebelum",
      "value": 3,
      "unit": "days",
      "display_text": "3 hari sebelum"
      },
      {
      "id": 6,
      "label": "7 hari sebelum",
      "value": 7,
      "unit": "days",
      "display_text": "7 hari sebelum"
      }
      // ... more options
      ]
      }
    - Note: Filter reminder options berdasarkan schedule_type di Flutter:
        - Untuk schedule_type "km": gunakan reminder dengan unit "km"
        - Untuk schedule_type "time": gunakan reminder dengan unit "days", "weeks", "months", "years"

### Service Schedule Endpoints

- POST `/service-schedules`
    - Auth: Bearer atau Guest (`X-Device-ID`)
    - Purpose: Create schedule (KM or time)
    - Request JSON (KM-based dengan reminder):
      {
      "vehicle_id": 1, // integer, required
      "service_type_id": 1, // required
      "schedule_type": "km", // "km" | "time"
      "target_km": 10000, // required for km
      "reminder_option_id": 2, // optional, gunakan ID dari /reminder-options/active dengan unit "km"
      "notes": "Service rutin",
      "is_active": true
      }
    - Request JSON (time-based dengan reminder):
      {
      "vehicle_id": 1,
      "service_type_id": 1,
      "schedule_type": "time",
      "target_date": "2026-06-30", // ISO 8601, required for time
      "reminder_option_id": 6, // optional, gunakan ID dari /reminder-options/active dengan unit "days"/"weeks"/"months"/"years"
      "notes": "Service 6 bulan",
      "is_active": true
      }
    - Success response: 201 Created
      {
      "success": true,
      "data": {
      "id": 123,
      "vehicle_id": 1,
      "service_type": { "id": 1, "name": "Ganti Oli" },
      "schedule_type": "time",
      "target_date": "2026-06-30",
      "reminder_option": {
      "id": 6,
      "label": "7 hari sebelum",
      "value": 7,
      "unit": "days"
      },
      "is_active": true,
      "notes": "Service 6 bulan"
      },
      "message": "Schedule created"
      }

- GET `/service-schedules`
    - Auth: Bearer or Guest
    - Query params: `vehicle_id`, `is_active`, `schedule_type`, `page`, `limit`
    - Response: paginated list of schedules with evaluation fields (see sample below)

- GET `/service-schedules/{id}`
    - Auth: Bearer or Guest
    - Response: schedule object with evaluation fields

- PUT `/service-schedules/{id}`
    - Auth: Bearer or Guest (if schedule belongs to device)
    - Supports partial updates. Validate changed fields server-side.

- DELETE `/service-schedules/{id}`
    - Auth: Bearer or Guest
    - Soft-delete expected (server-side)

- GET `/service-schedules/primary` ⭐
    - Auth: Bearer or Guest
    - Purpose: Return all schedules for current primary vehicle (for user or device), with live evaluation fields.
    - Use case: dashboard, widget, homescreen

- GET `/service-schedules/status/{vehicle_id}`
    - Auth: Bearer or Guest
    - Purpose: Evaluate all active schedules for `vehicle_id` and return their statuses

---

## 3. Data model (server-side, conceptual)

+- Schedule fields (representative)

- `id` (int)
- `vehicle_id` (int)
- `owner_type` ("user" | "device") // internal: marks data owner
- `owner_id` (user_id or device_id)
- `schedule_type` ("km" | "time")
- `target_value` (int) // for km
- `start_odometer` (int)
- `current_value` (int) // calculated at read time
- `target_date` (date) // for time schedules
- `reminder_option_id` (int|null)
- `is_active` (bool)
- `created_at`, `updated_at`

Reminder options (master data):
+- `id`, `value`, `unit` (km/days/weeks/months/years), `is_active`, `display_text`

---

## 4. Status evaluation — aturan & contoh

Server-side evaluation (yang Flutter harus kembalikan ke UI):

-- For `km` schedules:

- compute `distance_remaining = target_value - current_odometer`
- `critical` if `distance_remaining <= 0`
- `warning` if `0 < distance_remaining <= reminder_threshold_km` (threshold based on reminder_option or default)
- `normal` otherwise

-- For `time` schedules:

- compute `days_remaining = (target_date - today).days`
- `critical` if `days_remaining < 0` (overdue) or `days_remaining == 0`
- `warning` if `0 < days_remaining <= reminder_threshold_days`
- `normal` otherwise

Returned evaluation fields (recommended) for each schedule:

- `status`: `critical` | `warning` | `normal`
- `next_due_in`: integer (km or days) — negative = overdue
- `current_value`: current odometer reading (if km-based)

Example response items:

```json
// KM-based schedule dengan reminder 200 km
{
  "schedule_id": 12,
  "service_name": "Ganti Oli",
  "schedule_type": "km",
  "target_km": 10000,
  "current_km": 9800,
  "remaining_km": 200,
  "reminder_threshold": 200,
  "status": "warning",     // warning karena remaining (200) <= threshold (200)
  "message": "Ganti Oli akan segera jatuh tempo dalam 200 km."
}

// Time-based schedule dengan reminder 7 hari
{
  "schedule_id": 13,
  "service_name": "Service Berkala",
  "schedule_type": "time",
  "target_date": "2026-02-23",
  "current_date": "2026-02-16",
  "remaining_days": 7,
  "reminder_threshold": 7,
  "status": "warning",     // warning karena remaining (7) <= threshold (7)
  "message": "Service Berkala akan jatuh tempo dalam 7 hari."
}

// Time-based schedule overdue (tanpa reminder)
{
  "schedule_id": 14,
  "service_name": "Ganti Ban",
  "schedule_type": "time",
  "target_date": "2026-02-10",
  "current_date": "2026-02-16",
  "remaining_days": -6,
  "reminder_threshold": 7,   // default karena tidak ada reminder_option_id
  "status": "critical",      // critical karena overdue
  "message": "Ganti Ban sudah terlambat 6 hari! Segera lakukan service."
}
```

**Cara kerja reminder threshold:**

- Untuk KM schedules: jika `remaining_km <= reminder_threshold`, status = `warning`
- Untuk Time schedules: jika `remaining_days <= reminder_threshold`, status = `warning`
- Threshold default: 500 km (untuk KM) dan 7 hari (untuk Time) jika tidak ada reminder_option_id
- Threshold dari reminder_option: nilai diambil dari `value` dan `unit` (dikonversi ke km atau hari)

---

## 5. Guest Mode specifics

-- Identification: client must send `X-Device-ID` header on guest requests.
-- Data ownership: schedules created in guest mode are attached to `device_id` (owner_type: device).
-- Device -> User sync: when user registers/logins with the same device, server should offer:

- automatic merge (attach device records to user),
- conflict resolution policy (e.g., keep latest),
- `sync_info` in login/verify-email response describing how many records moved.

When user logs in from different device: no device data merged unless device_id matches or server has explicit sync flow.

Integration notes for Flutter:
-- Store `device_id` persistently (secure storage) before creating guest schedules.
-- After login, refresh local cache by calling `/service-schedules/primary` to pick up merged schedules.

---

## 6. Notifications & reminders

-- Server can generate push notifications or scheduled notifications when status becomes `warning` or `critical`.
-- Client should also schedule local notifications using `next_due_in` to show reminders even offline.

---

## 7. Error handling (common)

-- 400 Bad Request: validation errors (missing fields, invalid date)
-- 401 Unauthorized: missing/invalid token for protected routes
-- 403 Forbidden: trying to edit resource not owned by user/device
-- 404 Not Found: schedule id not found
-- 429 Too Many Requests: rate limits

Excerpt error response format:
{
"success": false,
"message": "Validation failed",
"errors": { "vehicle_id": ["The vehicle_id field is required."] }
}

---

## 8. Flutter integration — models & helpers (Dart)

Letakkan file berikut di `lib/models/schedule.dart` dan `lib/services/schedule_api.dart`.

`lib/models/schedule.dart`

```dart
class Schedule {
  final int id;
  final int vehicleId;
  final String scheduleType; // 'km' or 'time'
  final int? targetValue;
  final int? currentValue;
  final String? targetDate; // ISO string
  final String status; // 'critical'|'warning'|'normal'
  final int nextDueIn; // km or days (negative = overdue)

  Schedule({
    required this.id,
    required this.vehicleId,
    required this.scheduleType,
    this.targetValue,
    this.currentValue,
    this.targetDate,
    required this.status,
    required this.nextDueIn,
  });

  factory Schedule.fromJson(Map<String, dynamic> json) => Schedule(
    id: json['id'] as int,
    vehicleId: json['vehicle_id'] as int,
    scheduleType: json['schedule_type'] as String,
    targetValue: json['target_value'] as int?,
    currentValue: json['current_value'] as int?,
    targetDate: json['target_date'] as String?,
    status: json['status'] as String? ?? 'normal',
    nextDueIn: (json['next_due_in'] is int) ? json['next_due_in'] as int : int.parse('${json['next_due_in'] ?? 0}'),
  );

  Map<String, dynamic> toJson() => {
    'id': id,
    'vehicle_id': vehicleId,
    'schedule_type': scheduleType,
    'target_value': targetValue,
    'current_value': currentValue,
    'target_date': targetDate,
    'status': status,
    'next_due_in': nextDueIn,
  };
}

class ReminderOption {
  final int id;
  final String label;
  final int value;
  final String unit; // 'km', 'days', 'weeks', 'months', 'years'
  final String displayText;

  ReminderOption({
    required this.id,
    required this.label,
    required this.value,
    required this.unit,
    required this.displayText,
  });

  factory ReminderOption.fromJson(Map<String, dynamic> json) => ReminderOption(
    id: json['id'] as int,
    label: json['label'] as String,
    value: json['value'] as int,
    unit: json['unit'] as String,
    displayText: json['display_text'] as String,
  );

  // Helper untuk filter berdasarkan schedule type
  bool isValidFor(String scheduleType) {
    if (scheduleType == 'km') {
      return unit == 'km';
    } else {
      return ['days', 'weeks', 'months', 'years'].contains(unit);
    }
  }
}
```

`lib/services/schedule_api.dart`

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/schedule.dart';

Map<String,String> authHeaders({String? token, String? deviceId}){
  final headers = {'Content-Type':'application/json'};
  if(token!=null) headers['Authorization'] = 'Bearer $token';
  else if(deviceId!=null) headers['X-Device-ID'] = deviceId;
  return headers;
}

class ScheduleApi {
  final String baseUrl;
  final String? token;
  final String? deviceId;

  ScheduleApi(this.baseUrl, {this.token, this.deviceId});

  // Fetch reminder options (for dropdown)
  Future<List<ReminderOption>> fetchReminderOptions() async {
    final uri = Uri.parse('\$baseUrl/reminder-options/active');
    final resp = await http.get(uri, headers: authHeaders(token: token, deviceId: deviceId));
    if(resp.statusCode==200){
      final body = json.decode(resp.body) as Map<String,dynamic>;
      final data = body['data'] as List<dynamic>;
      return data.map((e) => ReminderOption.fromJson(e as Map<String,dynamic>)).toList();
    }
    throw Exception('Failed to load reminder options: ${resp.statusCode}');
  }

  // Filter reminder options by schedule type
  Future<List<ReminderOption>> fetchReminderOptionsFor(String scheduleType) async {
    final allOptions = await fetchReminderOptions();
    return allOptions.where((opt) => opt.isValidFor(scheduleType)).toList();
  }

  Future<List<Schedule>> fetchPrimary() async {
    final uri = Uri.parse('\$baseUrl/service-schedules/primary');
    final resp = await http.get(uri, headers: authHeaders(token: token, deviceId: deviceId));
    if(resp.statusCode==200){
      final body = json.decode(resp.body) as Map<String,dynamic>;
      final data = body['data'] as List<dynamic>;
      return data.map((e) => Schedule.fromJson(e as Map<String,dynamic>)).toList();
    }
    throw Exception('Failed to load primary schedules: ${resp.statusCode}');
  }

  Future<List<Schedule>> evaluateStatus(int vehicleId) async {
    final uri = Uri.parse('\$baseUrl/service-schedules/status/$vehicleId');
    final resp = await http.get(uri, headers: authHeaders(token: token, deviceId: deviceId));
    if(resp.statusCode==200){
      final body = json.decode(resp.body) as Map<String,dynamic>;
      final data = body['data'] as List<dynamic>;
      return data.map((e) => Schedule.fromJson(e as Map<String,dynamic>)).toList();
    }
    throw Exception('Failed to evaluate status: ${resp.statusCode}');
  }

  Future<Schedule> create(Map<String,dynamic> payload) async {
    final uri = Uri.parse('\$baseUrl/service-schedules');
    final resp = await http.post(uri, headers: authHeaders(token: token, deviceId: deviceId), body: json.encode(payload));
    if(resp.statusCode==201 || resp.statusCode==200) {
      final body = json.decode(resp.body) as Map<String,dynamic>;
      return Schedule.fromJson(body['data'] as Map<String,dynamic>);
    }
    throw Exception('Create schedule failed: ${resp.statusCode}');
  }

  Future<void> update(int id, Map<String,dynamic> payload) async {
    final uri = Uri.parse('\$baseUrl/service-schedules/$id');
    final resp = await http.put(uri, headers: authHeaders(token: token, deviceId: deviceId), body: json.encode(payload));
    if(!(resp.statusCode==200)) throw Exception('Update failed: ${resp.statusCode}');
  }

  Future<void> delete(int id) async {
    final uri = Uri.parse('\$baseUrl/service-schedules/$id');
    final resp = await http.delete(uri, headers: authHeaders(token: token, deviceId: deviceId));
    if(!(resp.statusCode==200 || resp.statusCode==204)) throw Exception('Delete failed: ${resp.statusCode}');
  }
}
```

## 9. UI mapping & examples

### A. Status mapping

- Map `status` to UI elements:
    - `critical` → color: `Colors.red`, icon: `Icons.error`
    - `warning` → color: `Colors.amber`, icon: `Icons.warning`
    - `normal` → color: `Colors.green`, icon: `Icons.check_circle`

### B. Schedule tile widget

```dart
Widget scheduleTile(Schedule s){
  final color = s.status=='critical' ? Colors.red : s.status=='warning' ? Colors.amber : Colors.green;
  return ListTile(
    leading: Icon(Icons.directions_motorcycle, color: color),
    title: Text(s.scheduleType=='km' ? 'Service by ${s.targetValue} km' : 'Service on ${s.targetDate}'),
    subtitle: Text(s.nextDueIn < 0 ? 'Overdue by ${-s.nextDueIn} ${s.scheduleType=="km"?"km":"days"}' : 'Due in ${s.nextDueIn} ${s.scheduleType=="km"?"km":"days"}'),
    trailing: Icon(Icons.chevron_right),
  );
}
```

### C. Create schedule with reminder (complete example)

```dart
// 1. Fetch reminder options for schedule type
final api = ScheduleApi('http://localhost:8000/api/v1/motorcycle', token: userToken);

// Untuk time-based schedule
String scheduleType = 'time';
List<ReminderOption> reminderOptions = await api.fetchReminderOptionsFor(scheduleType);

// 2. Show dropdown dengan reminder options
ReminderOption? selectedReminder; // null = no reminder
// Display reminderOptions.map((e) => DropdownMenuItem(value: e, child: Text(e.displayText)))

// 3. Create schedule dengan reminder
Map<String, dynamic> payload = {
  'vehicle_id': 7,
  'service_type_id': 3,
  'schedule_type': 'time',
  'target_date': '2026-06-30',
  'reminder_option_id': selectedReminder?.id, // bisa null kalau user tidak pilih reminder
  'notes': 'Service berkala 6 bulan',
  'is_active': true,
};

try {
  final schedule = await api.create(payload);
  print('Schedule created with reminder: ${schedule.id}');
} catch (e) {
  print('Error: $e');
}
```

### D. Real-world usage example

```dart
// Example: Create time-based schedule dengan reminder 7 hari sebelum
void createTimeScheduleWithReminder() async {
  final api = ScheduleApi(baseUrl, token: accessToken);

  // Fetch reminder options untuk time schedules
  final timeReminders = await api.fetchReminderOptionsFor('time');

  // Cari reminder "7 hari sebelum"
  final reminder7Days = timeReminders.firstWhere(
    (r) => r.value == 7 && r.unit == 'days',
    orElse: () => timeReminders.first, // fallback ke reminder pertama
  );

  // Create schedule
  final payload = {
    'vehicle_id': primaryVehicle.id,
    'service_type_id': serviceTypes.first.id, // Ambil dari dropdown
    'schedule_type': 'time',
    'target_date': '2026-12-31',
    'reminder_option_id': reminder7Days.id, // ⭐ Gunakan reminder ID
    'notes': 'Service akhir tahun',
    'is_active': true,
  };

  final newSchedule = await api.create(payload);
  print('✅ Schedule created! Status: ${newSchedule.status}');
}
```

```

## 10. Background sync & best practices

- On app start: ensure `device_id` exists; if not, generate UUID and save to secure storage.
- Fetch `/service-schedules/primary` after login and after primary vehicle change.
- When creating schedules in guest mode, store pending local copies and reconcile after login using server `sync_info`.

## 11. Next steps I can do for you

- Generate `lib/models/schedule.dart` and `lib/services/schedule_api.dart` files in the repo now.
- Add JSON Schema files for each endpoint.
- Create a small Flutter example project with UI for primary schedules.

---

Dokumen lengkap berada di: [docs/FLUTTER_SERVICE_SCHEDULE.md](docs/FLUTTER_SERVICE_SCHEDULE.md)
```

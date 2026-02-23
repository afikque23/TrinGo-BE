# Manual Reminder Feature - Implementation Complete ✅

## Overview

Fitur manual reminder telah berhasil diimplementasikan untuk service schedules. User dapat mengatur custom reminder threshold (dalam km atau hari) saat membuat atau mengupdate jadwal servis kendaraan.

## Database Changes

### New Columns in `service_schedules` Table

- `reminder_threshold` (integer, nullable): Jarak (km) atau waktu (hari) sebelum jadwal untuk trigger notifikasi
- `reminder_sent` (boolean, default: false): Flag untuk menandai apakah notifikasi sudah dikirim
- `reminder_sent_at` (timestamp, nullable): Waktu saat notifikasi terakhir dikirim

### Updated Columns

- `reminder_option_id` (bigint unsigned, **nullable**): ID dari predefined reminder option
    - **IMPORTANT**: Field ini sekarang nullable untuk support custom reminder
    - User dapat pilih **antara** predefined reminder option **ATAU** custom reminder threshold
    - Jika `reminder_threshold` diisi, maka `reminder_option_id` boleh NULL

### New Index

- `idx_schedule_reminders`: Index untuk performance reminder checks pada kolom `vehicle_id`, `is_active`, `reminder_threshold`, `reminder_sent`

## API Endpoints

### 1. Create Service Schedule (Enhanced)

**Endpoint:** `POST /api/v1/motorcycle/service-schedules`

**Request Body (Custom Reminder):**

```json
{
    "vehicle_id": 1,
    "service_type_id": 2,
    "service_name": "Ganti Oli",
    "schedule_type": "km",
    "interval_value": 3000,
    "last_service_mileage": 5000,
    "target_km": 8000,
    "reminder_option_id": null,
    "reminder_threshold": 50,
    "notes": "Custom reminder 50 km sebelum servis"
}
```

**Request Body (Predefined Reminder Option):**

```json
{
    "vehicle_id": 1,
    "service_type_id": 2,
    "service_name": "Ganti Oli",
    "schedule_type": "km",
    "interval_value": 3000,
    "last_service_mileage": 5000,
    "target_km": 8000,
    "reminder_option_id": 3,
    "reminder_threshold": null,
    "notes": "Menggunakan predefined reminder option"
}
```

**Request Body (No Reminder):**

````json
{
  "vehicle_id": 1,
  "service_type_id": 2,
  "service_name": "Ganti Oli",
  "schedule_type": "km",
  "interval_value": 3000,
  "last_service_mileage": 5000,
  "target_km": 8000,
  "reminder_option_id": null,
  "reminder_threshold": null,
  "notes": "Tanpa reminder"

```json
{
    "success": true,
    "message": "Service schedule created successfully.",
    "data": {
        "id": 1,
        "vehicle_id": 1,
        "service_type": {
            "id": 2,
            "name": "Oil Change"
        },
        "service_name": "Ganti Oli",
        "schedule_type": "km",
        "interval_value": 3000,
        "target_km": 8000,
        "reminder_threshold": 50,
        "reminder_sent": false,
        "reminder_sent_at": null,
        "is_active": true,
        "notes": "Reminder 50 km sebelum servis",
        "created_at": "2026-02-23T10:00:00.000000Z",
        "updated_at": "2026-02-23T10:00:00.000000Z"
    }
}
````

### 2. Update Service Schedule (Enhanced)

**Endpoint:** `PUT /api/v1/motorcycle/service-schedules/{id}`

**Request Body:** (sama seperti create, semua field optional)

```json
{
    "reminder_threshold": 100
}
```

### 3. Check Reminders (NEW)

**Endpoint:** `POST /api/v1/motorcycle/service-schedules/check-reminders/{vehicle_id}`

**Purpose:** Dipanggil dari mobile app saat:

- User membuka app
- Odometer diupdate (GPS atau manual)
- Manual refresh

**Request Body:**

```json
{
    "current_odometer": 7950
}
```

**Response:**

```json
{
    "success": true,
    "message": "Reminder notifications have been sent.",
    "data": {
        "vehicle_id": 1,
        "current_odometer": 7950,
        "reminders_triggered": 1,
        "reminders": [
            {
                "schedule_id": 1,
                "service_type": "Ganti Oli",
                "current_odometer": 7950,
                "target_km": 8000,
                "remaining_km": 50,
                "message": "Servis Ganti Oli dalam 50 km lagi"
            }
        ]
    }
}
```

### 4. Reset Reminder Flag (NEW)

**Endpoint:** `POST /api/v1/motorcycle/service-schedules/{schedule_id}/reset-reminder`

**Purpose:** Reset reminder flag saat schedule diupdate dengan target baru atau saat user ingin receive reminder lagi

**Response:**

```json
{
    "success": true,
    "message": "Reminder flag reset successfully.",
    "data": {
        "schedule_id": 1
    }
}
```

## Validation Rules

### `reminder_threshold` Field

**For KM-based schedules:**

- Nullable (boleh kosong)
- Must be integer
- Minimum: 1 km
- Maximum: 1000 km
- Must be less than `interval_value`

**For Time-based schedules:**

- Nullable (boleh kosong)
- Must be integer
- Minimum: 1 day
- Maximum: 60 days
- Must be less than `interval_value`

**Example Validation Errors:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "reminder_threshold": ["Reminder threshold untuk km maksimal 1000."]
    }
}
```

## Reminder Logic

### KM-based Reminder

**Calculation:**

```
reminder_trigger_odometer = target_km - reminder_threshold
```

**Example:**

- Target: 8000 km
- Threshold: 50 km
- Trigger at: 8000 - 50 = 7950 km

When vehicle's odometer reaches or exceeds 7950 km, notification will be sent.

### Time-based Reminder

**Calculation:**

```
reminder_trigger_date = target_date - reminder_threshold (days)
```

**Example:**

- Target: 2026-03-01
- Threshold: 3 days
- Trigger at: 2026-02-26

When current date reaches or exceeds 2026-02-26, notification will be sent.

## Notification Format

### FCM Notification Payload

```json
{
    "notification": {
        "title": "🔔 Pengingat Servis",
        "body": "Kendaraan Anda akan mencapai jadwal servis dalam 50 km lagi"
    },
    "data": {
        "type": "schedule_reminder",
        "schedule_id": "1",
        "current_odometer": "7950",
        "target_km": "8000",
        "remaining_km": "50",
        "service_type": "Ganti Oli",
        "click_action": "NOTIFICATION_CLICK",
        "category_key": "service"
    },
    "android": {
        "priority": "HIGH",
        "notification": {
            "channel_id": "mototracker_service",
            "sound": "default",
            "notification_priority": "PRIORITY_HIGH"
        }
    }
}
```

## Backend Services

### ServiceScheduleReminderService

Located at: `app/Services/ServiceScheduleReminderService.php`

**Methods:**

1. `checkKmBasedReminders(int $vehicleId, int $currentOdometer): array`
    - Check KM-based reminders untuk specific vehicle
    - Dipanggil dari API endpoint atau saat odometer update

2. `checkDateBasedReminders(): array`
    - Check semua date-based reminders
    - Dipanggil dari scheduled job

3. `checkAllPendingReminders(): array`
    - Check semua pending reminders (km + date)
    - Dipanggil dari scheduled job

4. `resetReminderFlag(int $scheduleId): bool`
    - Reset reminder flag untuk schedule tertentu

## Scheduled Job

### Command: `reminders:check-service-schedules`

**File:** `app/Console/Commands/CheckServiceReminders.php`

**Schedule:** (Defined in `bootstrap/app.php`)

- Daily at 08:00 AM (Asia/Jakarta timezone)
- Every 6 hours

**Purpose:**

- Automatically check date-based reminders
- Check km-based reminders untuk semua vehicles

**Manual Run:**

```bash
php artisan reminders:check-service-schedules
```

**Output:**

```
Checking service schedule reminders...
✓ KM-based reminders sent: 2
✓ Date-based reminders sent: 1
✓ Total reminders sent: 3
```

## Usage Flow

### Mobile App Integration

**1. Saat Membuat Jadwal Servis:**

```dart
// User input custom reminder threshold
final reminderThreshold = 50; // km atau hari

// API call
final response = await http.post(
  '/api/v1/motorcycle/service-schedules',
  body: {
    'vehicle_id': 1,
    'service_type_id': 2,
    'schedule_type': 'km',
    'target_km': 8000,
    'reminder_threshold': reminderThreshold,
  },
);
```

**2. Saat Odometer Diupdate:**

```dart
// After updating vehicle odometer
await http.post(
  '/api/v1/motorcycle/service-schedules/check-reminders/$vehicleId',
  body: {
    'current_odometer': currentOdometer,
  },
);
```

**3. Saat App Dibuka:**

```dart
// On app startup
final vehicle = await getCurrentVehicle();
await http.post(
  '/api/v1/motorcycle/service-schedules/check-reminders/${vehicle.id}',
  body: {
    'current_odometer': vehicle.currentKm,
  },
);
```

**4. Saat Schedule Diupdate dengan Target Baru:**

```dart
// After updating schedule target
await http.post(
  '/api/v1/motorcycle/service-schedules/$scheduleId/reset-reminder',
);
```

## Example Scenarios

### Scenario 1: KM-based Reminder

1. User membuat jadwal: "Ganti Oli" dengan target 8000 km
2. User set reminder threshold: 50 km
3. System calculate trigger point: 8000 - 50 = 7950 km
4. Vehicle odometer saat ini: 7800 km (belum trigger)
5. User berkendara, odometer jadi 7950 km
6. App call check-reminders endpoint
7. Backend detect threshold tercapai → kirim FCM notification
8. Flag `reminder_sent` = true
9. User tidak akan menerima notification lagi untuk schedule ini sampai di-reset

### Scenario 2: Time-based Reminder

1. User membuat jadwal: "Periodic Service" dengan target 2026-03-01
2. User set reminder threshold: 3 hari
3. System calculate trigger date: 2026-02-26
4. Scheduled job berjalan setiap 6 jam
5. Pada tanggal 2026-02-26 jam 08:00, job detect threshold tercapai
6. Backend kirim FCM notification
7. Flag `reminder_sent` = true

### Scenario 3: Update Target (Reset Reminder)

1. User sudah menerima reminder untuk "Ganti Oli" (7950 km)
2. User lakukan service di bengkel pada 7980 km
3. User update schedule dengan target baru: 10980 km (7980 + 3000)
4. App call reset-reminder endpoint
5. Flag `reminder_sent` = false, `reminder_sent_at` = null
6. User akan menerima reminder lagi saat odometer mencapai 10930 km (10980 - 50)

## Edge Cases Handled

### 1. Multiple Schedules dengan Reminders

- System akan check semua active schedules
- Notifications dikirim untuk setiap schedule yang threshold-nya tercapai

### 2. Odometer Mundur

- Validation di vehicle update endpoint mencegah odometer mundur
- Jika terjadi reset odometer, user harus update semua schedules

### 3. Reminder Sudah Dikirim

- Flag `reminder_sent` mencegah duplicate notifications
- Harus di-reset manual atau otomatis saat target diupdate

### 4. Threshold Lebih Besar dari Interval

- Validation rule mencegah ini
- Error message: "Reminder threshold harus kurang dari interval value."

### 5. Negative Remaining (Melewati Target)

- Date-based: Tidak kirim notif jika sudah melewati target date
- KM-based: Tetap kirim notif, tapi remaining bisa negatif di response

## Files Changed/Created

### New Files:

1. `app/Services/ServiceScheduleReminderService.php`
2. `app/Console/Commands/CheckServiceReminders.php`
3. `database/migrations/2026_02_23_034419_add_reminder_threshold_to_service_schedules_table.php`
4. `database/migrations/2026_02_23_042659_fix_reminder_option_id_nullable_in_service_schedules.php` ✅ **FIX**

### Modified Files:

1. `app/Models/ServiceSchedule.php` - Added fillable & casts
2. `app/Http/Controllers/Api/ServiceScheduleController.php` - Added methods
3. `app/Http/Requests/StoreServiceScheduleRequest.php` - Added validation & fixed nullable
4. `app/Http/Requests/UpdateServiceScheduleRequest.php` - Added validation & fixed nullable
5. `app/Http/Resources/ServiceScheduleResource.php` - Added fields
6. `routes/api.php` - Added new routes
7. `bootstrap/app.php` - Added scheduled task

## Important Fixes Applied

### Fix: `reminder_option_id` NOT NULL Issue

**Problem:** Field `reminder_option_id` was still NOT NULL in database despite existing migration, preventing custom reminders without predefined options.

**Solution:** Created new migration `2026_02_23_042659_fix_reminder_option_id_nullable_in_service_schedules.php` that:

1. Drops foreign key constraint
2. Uses raw SQL `ALTER TABLE` to modify column to nullable
3. Re-adds foreign key with `onDelete('set null')`

**Verification:** Field now accepts NULL values, allowing schedules with only `reminder_threshold` (custom reminder) without requiring `reminder_option_id`.

## Testing Checklist

- [x] Migration runs successfully
- [ ] Create schedule with reminder_threshold
- [ ] Update schedule reminder_threshold
- [ ] Check reminders endpoint (KM-based)
- [ ] Check reminders with actual notification send
- [ ] Reset reminder flag
- [ ] Validation for max threshold (km: 1000, time: 60)
- [ ] Scheduled command runs successfully
- [ ] Notification received on mobile device

## Server Setup Requirements

### Cron Job Setup

Add to server crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

This will run the Laravel scheduler every minute, which then executes the reminder check command according to the schedule defined in `bootstrap/app.php`.

### Verify Scheduled Tasks

```bash
php artisan schedule:list
```

Expected output:

```
0 8 * * *  reminders:check-service-schedules ....... Next Due: 1 day from now
0 */6 * * *  reminders:check-service-schedules ..... Next Due: 3 hours from now
```

## Monitoring & Logs

### Log Entries

Successful reminder:

```
[2026-02-23 10:00:00] local.INFO: Reminder notification sent for schedule #1: 2 devices notified
```

Failed reminder:

```
[2026-02-23 10:00:00] local.ERROR: Failed to send reminder notification for schedule #1: [error message]
```

Scheduled job completion:

```
[2026-02-23 08:00:00] local.INFO: Service reminders check completed: 5 reminders sent {"km_based":3,"date_based":2}
```

## Future Enhancements

1. **Smart Reminders**: ML-based prediction untuk service needs
2. **Multi-Criteria**: Reminder berdasarkan km DAN time (whichever comes first)
3. **Snooze Feature**: User bisa snooze reminder untuk beberapa hari/km
4. **Reminder History**: Track semua reminders yang pernah dikirim
5. **Custom Notification Templates**: User bisa customize notification message
6. **Escalation**: Kirim reminder kedua jika service tidak dilakukan dalam X hari

---

**Implementation Date:** February 23, 2026  
**Status:** ✅ Complete & Ready for Testing  
**Version:** 1.0.0

# Cara Edit Pesan Notifikasi dari Web Admin

## 📝 Overview

Semua pesan notifikasi **TIDAK lagi hardcoded** di backend. Semuanya menggunakan **template dari database** yang bisa di-edit melalui **Manajemen Notifikasi** di web admin panel.

## ✅ Notifikasi Yang Menggunakan Template

### 1. **Pengingat Servis (Berbasis KM)**

- **Trigger:** `schedule_reminder_km`
- **Kategori:** Service
- **Default Message:** `🔧 Pengingat Servis: {service_name} dalam {km_remaining} km lagi! Target: {target_km} km. Odometer saat ini: {current_km} km.`

**Variabel yang Tersedia:**

- `{service_name}` - Nama jadwal servis (contoh: Ganti Oli)
- `{service_type}` - Tipe servis (sama dengan service_name)
- `{km_remaining}` - KM tersisa sebelum servis (contoh: 50)
- `{target_km}` - Target KM untuk servis (contoh: 8000)
- `{current_km}` - Odometer saat ini (contoh: 7950)
- `{vehicle_name}` - Nama kendaraan (contoh: Honda Beat 2023)

### 2. **Pengingat Servis (Berbasis Waktu)**

- **Trigger:** `schedule_reminder_time`
- **Kategori:** Service
- **Default Message:** `🔧 Pengingat Servis: {service_name} dalam {days_remaining} hari lagi! Jadwal: {target_date}. Jangan sampai terlambat!`

**Variabel yang Tersedia:**

- `{service_name}` - Nama jadwal servis
- `{service_type}` - Tipe servis
- `{days_remaining}` - Hari tersisa sebelum servis (contoh: 3)
- `{target_date}` - Tanggal target servis (contoh: 23/02/2026)
- `{vehicle_name}` - Nama kendaraan

## 🎨 Cara Edit Pesan di Web Admin

### Langkah 1: Buka Manajemen Notifikasi

1. Login ke admin panel
2. Buka menu **Manajemen Notifikasi**

### Langkah 2: Edit Template

1. Cari template **"Pengingat Servis (KM)"** atau **"Pengingat Servis (Waktu)"**
2. Klik tombol **Edit** (ikon pensil)
3. Edit field **"Isi Template Pesan"**
4. Gunakan variabel dengan format `{nama_variabel}`
5. Klik **Simpan**

### Contoh Pengeditan:

**Pesan Original:**

```
🔧 Pengingat Servis: {service_name} dalam {km_remaining} km lagi! Target: {target_km} km. Odometer saat ini: {current_km} km.
```

**Pesan Custom (Contoh 1):**

```
⚠️ Halo! Motor Anda akan mencapai jadwal {service_name} dalam {km_remaining} km lagi. Segera booking bengkel!
```

**Pesan Custom (Contoh 2):**

```
🏍️ {vehicle_name} Anda memerlukan servis {service_name}. Sisa jarak: {km_remaining} km dari target {target_km} km.
```

**Pesan Custom (Contoh 3 - Waktu):**

```
📅 Reminder: Jadwal {service_name} tinggal {days_remaining} hari lagi (tanggal {target_date})! Jangan lupa ya!
```

## 🔧 Testing Template

Setelah mengedit template:

1. Klik tombol **"Test Push"**
2. Pilih user target
3. Klik **"Kirim Test Push"**
4. Cek notifikasi di mobile app

Variabel akan otomatis diganti dengan data contoh saat testing.

## 📱 Hasil di Mobile App

Saat user menerima notifikasi, variabel akan diganti dengan data real:

**Template:**

```
🔧 Pengingat Servis: {service_name} dalam {km_remaining} km lagi!
```

**Hasil di Notifikasi:**

```
🔧 Pengingat Servis: Ganti Oli dalam 50 km lagi!
```

## 🔄 Fallback Mechanism

Jika template tidak ditemukan atau tidak aktif, system akan menggunakan pesan default hardcoded sebagai fallback:

**KM-based:**

```
🔧 Pengingat Servis: [Nama Servis] dalam [XX] km lagi!
```

**Time-based:**

```
🔧 Pengingat Servis: [Nama Servis] dalam [XX] hari lagi!
```

## ⚙️ Technical Details

### Database Table

- **Table:** `notification_templates`
- **Category:** `service`
- **Trigger Types:**
    - `schedule_reminder_km` - Reminder berbasis kilometer
    - `schedule_reminder_time` - Reminder berbasis waktu/tanggal

### Backend Service

File: `app/Services/ServiceScheduleReminderService.php`

Method yang digunakan:

- `sendReminderNotificationFromTemplate()` - Kirim menggunakan template database
- `sendFallbackNotification()` - Kirim dengan pesan default jika template tidak ada

### Template Parsing

System menggunakan `NotificationService::sendFromTemplate()` untuk:

1. Load template dari database
2. Replace variabel dengan data real
3. Kirim via FCM Push Notification

## 📚 Dokumentasi Terkait

- [MANUAL_REMINDER_IMPLEMENTATION.md](MANUAL_REMINDER_IMPLEMENTATION.md) - Dokumentasi lengkap fitur manual reminder
- [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - API endpoints
- [NOTIFICATION_CATEGORY_FILTER_API.md](NOTIFICATION_CATEGORY_FILTER_API.md) - Notification system

---

**Seeded By:** `ServiceReminderNotificationSeeder`  
**Last Updated:** February 23, 2026  
**Status:** ✅ Template-based (Editable via Admin Panel)

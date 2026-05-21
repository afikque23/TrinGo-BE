# 🏍️ TrinGo Backend API (TringGo-BE)

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![MQTT](https://img.shields.io/badge/MQTT-660066?style=for-the-badge&logo=mqtt&logoColor=white)

TringGo-BE adalah backend server berbasis **Laravel** untuk aplikasi manajemen sepeda motor pintar (TringGo). Sistem ini menyediakan layanan RESTful API untuk aplikasi mobile (Flutter) yang mencakup manajemen kendaraan, pelacakan perjalanan (IoT), notifikasi pintar, dan rekomendasi perawatan menggunakan AI (Fuzzy Logic & Gemini).

## ✨ Fitur Utama

- **🔐 Autentikasi & Otorisasi:** Fitur login dengan token (Laravel Sanctum), mendukung persisten login dan token rotation.
- **🏍️ Manajemen Kendaraan:** Data multi-kendaraan dengan dukungan profil pengguna.
- **📍 Trip Tracking via MQTT:** Pelacakan perjalanan kendaraan real-time ("Start" & "Stop") dengan integrasi broker MQTT (`tringgo/device/...`).
- **🤖 Rekomendasi Pintar (AI):** Bantuan analisa perawatan motor menggunakan Fuzzy Logic dan Google Gemini API.
- **🔔 Push Notifications (FCM):** Notifikasi jadwal servis, pengingat, dan event kendaraan langsung ke aplikasi Flutter.
- **🛠 Manajemen Servis & Tips:** Jadwal servis, manajemen komponen, serta portal edukasi/tips perawatan.

## 🛠️ Tech Stack

- **Framework:** Laravel 12.x
- **Language:** PHP 8.2+
- **Database:** MySQL
- **IoT Protocol:** MQTT (`php-mqtt/client`)
- **Third Party:** Google Gemini, Firebase Cloud Messaging (FCM)

## 🚀 Panduan Instalasi (Local Development)

Jika tim pengembang (`arya` / `aji`) ingin menjalankan project ini di lokal, ikuti langkah berikut:

1. **Clone repository & pindah ke branch masing-masing:**

    ```bash
    git clone https://github.com/afikque23/TrinGo-BE.git
    cd TrinGo-BE
    git checkout <nama-branch-kamu> # (contoh: git checkout arya)
    ```

2. **Install dependencies:**

    ```bash
    composer install
    ```

3. **Duplikat file environment dan konfigurasikan:**

    ```bash
    cp .env.example .env
    ```

    > 💡 **Info:** Buka file `.env` dan pastikan Anda mengisi kredensial database (`DB_*`) dan kredensial broker MQTT (`MQTT_*`) dengan benar.

4. **Generate Application Key:**

    ```bash
    php artisan key:generate
    ```

5. **Jalankan Migrasi Database:**

    ```bash
    php artisan migrate
    ```

    _(Gunakan perintah `php artisan migrate --seed` jika membutuhkan dummy data)_

6. **Jalankan Server Lokal:**
    ```bash
    php artisan serve
    ```

## 📚 Dokumentasi Ekstra

Anda bisa membaca kumpulan panduan teknis yang lebih komprehensif pada folder `docs/`, di antaranya:

- [📖 Tracking API Integration Guide](docs/FLUTTER_TRACKING_API.md)
- [📖 Authentication & Token Rotation](docs/TOKEN_ROTATION_IMPLEMENTATION_COMPLETE.md)
- [📖 Push Notification FCM Guide](docs/FCM_INTEGRATION_COMPLETE.md)

_Note: Anda bisa meng-import Postman Collection yang juga tersedia di dalam direktori `docs/` untuk keperluan uji coba endpoint API._

---

_Developed with ❤️ by the TrinGo Team._

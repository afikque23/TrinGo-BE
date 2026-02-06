# Backend - Aplikasi Manajemen Perawatan Motor

Struktur ini adalah kerangka backend yang rapi dan mudah diadaptasi — disusun agar sesuai praktik umum Laravel/Express tetapi ringan untuk dikembangkan.

Folder penting:

-   `app/` : kode aplikasi (Models, Http Controllers, Requests, Middleware, Console)
-   `routes/` : definisi routing API
-   `database/migrations` : file migrasi
-   `database/seeders` : seeder awal
-   `config/` : konfigurasi environment-agnostic
-   `docs/` : dokumentasi arsitektur dan ERD

Gunakan README ini sebagai titik awal. Sesuaikan dengan stack pilihan (Laravel atau Node.js + Express).

---

## 🚀 Sprint 5 - Riwayat Servis, Biaya, dan Struk

**Status:** ✅ Production Ready (January 2, 2026)

Sprint 5 menyediakan backend lengkap untuk pencatatan riwayat servis dan analisis biaya perawatan kendaraan.

### 📚 Documentation

-   **[SPRINT_5_README.md](SPRINT_5_README.md)** - Quick start guide
-   **[SPRINT_5_API_DOCUMENTATION.md](SPRINT_5_API_DOCUMENTATION.md)** - Complete API documentation
-   **[SPRINT_5_QUICK_TEST.md](SPRINT_5_QUICK_TEST.md)** - Testing guide
-   **[SPRINT_5_IMPLEMENTATION.md](SPRINT_5_IMPLEMENTATION.md)** - Technical details
-   **[SPRINT_5_COMPLETE.md](SPRINT_5_COMPLETE.md)** - Project summary
-   **[SPRINT_5_FILE_INDEX.md](SPRINT_5_FILE_INDEX.md)** - File reference

### ✨ Features

-   ✅ CRUD riwayat servis lengkap
-   ✅ Upload foto struk (opsional)
-   ✅ Analisis biaya (total, average, breakdown)
-   ✅ Filter periode (all time, yearly, monthly)
-   ✅ Auto-detect primary vehicle
-   ✅ Comprehensive validation & security

### 🔗 Quick Links

```bash
# API Endpoints
GET    /api/service-histories              # List
POST   /api/service-histories              # Create
GET    /api/service-histories/{id}         # Detail
PUT    /api/service-histories/{id}         # Update
DELETE /api/service-histories/{id}         # Delete
GET    /api/service-histories/cost-summary # Analysis
```

Lihat [SPRINT_5_README.md](SPRINT_5_README.md) untuk panduan lengkap.

---

## 📋 Previous Sprints

-   **Sprint 1-2:** Authentication & OTP Verification
-   **Sprint 3:** Vehicle Management & Primary Vehicle
-   **Sprint 4:** Trip Tracking (if implemented)
-   **Sprint 5:** Service History, Cost, & Receipts ✅

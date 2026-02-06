# Backend - Aplikasi Manajemen Perawatan Motor

Struktur ini adalah kerangka backend yang rapi dan mudah diadaptasi — disusun agar sesuai praktik umum Laravel namun ringan untuk dikembangkan.

Folder penting:

- `app/` : kode aplikasi (Models, Http Controllers, Requests, Middleware, Console)
- `routes/` : definisi routing API
- `database/migrations` : file migrasi
- `database/seeders` : seeder awal
- `config/` : konfigurasi

## 🚀 Sprint 5 - Riwayat Servis, Biaya, dan Struk

**Status:** ✅ Production Ready (January 2, 2026)

Fitur utama:

- CRUD riwayat servis
- Upload foto struk
- Analisis biaya (total, rata-rata, breakdown)
- Filter periode (all, year, month)

Quick start:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Lihat file `SPRINT_5_README.md` untuk dokumentasi API dan panduan pengujian.

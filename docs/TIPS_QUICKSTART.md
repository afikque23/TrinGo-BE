# Quick Start Guide - Tips API

## 🚀 Langkah Cepat untuk Mulai

### 1. Start MySQL Server

Buka Laragon dan start MySQL service.

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Run Seeder (Optional)

```bash
php artisan db:seed --class=TipTagSeeder
```

### 4. Test API

#### Import Postman Collection

Import file `Tips_API.postman_collection.json` ke Postman.

#### Set Environment Variables

- `base_url`: `http://localhost` (sesuaikan dengan setup Laragon Anda)
- `access_token`: Token dari login endpoint

#### Test Endpoints

**1. Browse Tips (Public - No Auth)**

```
GET http://localhost/api/v1/motorcycle/public/tips
GET http://localhost/api/v1/motorcycle/public/tips?search=oli
GET http://localhost/api/v1/motorcycle/public/tips?hashtags=oli mesin,daily rider
```

**2. Create Tip (Requires Auth)**

```
POST http://localhost/api/v1/motorcycle/tips
Authorization: Bearer {your_token}

Body: Lihat di Postman collection
```

**3. Like Tip**

```
POST http://localhost/api/v1/motorcycle/tips/1/like
Authorization: Bearer {your_token}

{
  "action": "like"
}
```

## 📊 Database Tables Created

1. `tips` - Main tips table
2. `tip_tags` - Tags/labels
3. `tip_tag_pivot` - Relationship table
4. `tip_tools` - Required tools
5. `tip_steps` - Step-by-step instructions
6. `tip_likes` - Like tracking
7. `tip_bookmarks` - Bookmark tracking
8. `tip_shares` - Share tracking

## 🎯 Key Features

✅ CRUD Tips (Create, Read, Update, Delete)
✅ Filter by brand, riding style, tags
✅ Filter by hashtags (comma-separated)
✅ Search by title/description/hashtags
✅ Sort by latest, popular, rating
✅ Like/Unlike functionality
✅ Bookmark/Unbookmark functionality
✅ Share tracking
✅ Convert tip to service schedule
✅ Guest mode support (device_id)
✅ Auto-generate tags
✅ Stats tracking (likes, views, shares, etc)

## 🔑 Authentication

### Register User

```
POST http://localhost/api/v1/motorcycle/auth/register
```

### Login

```
POST http://localhost/api/v1/motorcycle/auth/login
```

Gunakan `access_token` dari response untuk authenticated endpoints.

## 📝 Response Format

### Success Response

```json
{
  "success": true,
  "message": "Tips berhasil diambil",
  "data": { ... }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

## 🐛 Troubleshooting

### Migration Error

- Pastikan MySQL server running
- Check database config di `.env`
- Coba: `php artisan config:clear`

### Authentication Error

- Pastikan token valid
- Token format: `Bearer {token}`
- Token expires setelah waktu tertentu

### 404 Not Found

- Check route dengan: `php artisan route:list | grep tips`
- Pastikan URL benar

### 422 Validation Error (Hashtags)

- Maksimal 10 hashtag per request
- Setiap hashtag harus 2-30 karakter setelah normalisasi
- Input `#` diperbolehkan, backend akan normalisasi otomatis

## 📚 Documentation Files

- `TIPS_API_IMPLEMENTATION.md` - Full implementation details
- `docs/FLUTTER_TIPS_HASHTAG_SEARCH.md` - Panduan integrasi FE untuk hashtag + search
- `Tips_API.postman_collection.json` - Postman collection

## 📌 API Contract Update

- Field `difficulty` sudah dihapus dari create/update tips
- Response tips tidak lagi mengirim object `difficulty`
- Gunakan `estimated_time` sebagai field terpisah jika diperlukan UI
- Validasi `title` untuk create/update diperbarui menjadi minimal 5 karakter
- Validasi `tools` untuk create/update menetapkan minimal 1 alat

## 🎉 Ready to Go!

Setelah migration berhasil, API siap digunakan!

Happy coding! 🚀

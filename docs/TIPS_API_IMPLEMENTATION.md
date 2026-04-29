# Tips Perawatan Motor API - Implementation Documentation

## 📋 Summary

API untuk fitur Tips Perawatan Motor telah selesai diimplementasikan berdasarkan dokumentasi dari Frontend team. Implementasi mengikuti pattern dan best practices yang sudah ada di project ini.

## ✅ Files Created

### Database Migrations (8 files)

- `2026_03_14_000001_create_tips_table.php` - Table utama untuk tips
- `2026_03_14_000002_create_tip_tags_table.php` - Table untuk tag/label tips
- `2026_03_14_000003_create_tip_tag_pivot_table.php` - Pivot table untuk relasi tips dan tags
- `2026_03_14_000004_create_tip_tools_table.php` - Table untuk alat yang dibutuhkan
- `2026_03_14_000005_create_tip_steps_table.php` - Table untuk langkah-langkah tips
- `2026_03_14_000006_create_tip_likes_table.php` - Table untuk tracking likes
- `2026_03_14_000007_create_tip_bookmarks_table.php` - Table untuk tracking bookmarks
- `2026_03_14_000008_create_tip_shares_table.php` - Table untuk tracking shares

### Models (7 files)

- `app/Models/Tip.php` - Model utama dengan relationships dan scopes
- `app/Models/TipTag.php` - Model untuk tags
- `app/Models/TipTool.php` - Model untuk tools
- `app/Models/TipStep.php` - Model untuk steps
- `app/Models/TipLike.php` - Model untuk likes
- `app/Models/TipBookmark.php` - Model untuk bookmarks
- `app/Models/TipShare.php` - Model untuk shares

### Request Validation (5 files)

- `app/Http/Requests/StoreTipRequest.php` - Validasi untuk create tips
- `app/Http/Requests/UpdateTipRequest.php` - Validasi untuk update tips
- `app/Http/Requests/TipActionRequest.php` - Validasi untuk like/bookmark actions
- `app/Http/Requests/TipShareRequest.php` - Validasi untuk share tracking
- `app/Http/Requests/TipUseTemplateRequest.php` - Validasi untuk create schedule dari template

### Resources (3 files)

- `app/Http/Resources/TipResource.php` - Resource untuk list items
- `app/Http/Resources/TipDetailResource.php` - Resource untuk detail view
- `app/Http/Resources/TipCollection.php` - Resource collection dengan pagination

### Controller

- `app/Http/Controllers/TipsController.php` - Controller dengan semua endpoints

### Seeder

- `database/seeders/TipTagSeeder.php` - Seeder untuk initial tags

## 🚀 API Endpoints Implemented

### Public Endpoints (No Auth Required)

```
GET    /api/v1/motorcycle/public/tips           - List tips dengan filters
GET    /api/v1/motorcycle/public/tips/{id}      - Detail tips
```

### Protected Endpoints (Require Authentication)

```
GET    /api/v1/motorcycle/tips                  - List tips dengan filters (authenticated user)
POST   /api/v1/motorcycle/tips                  - Create new tip
GET    /api/v1/motorcycle/tips/{id}             - Get tip detail
PUT    /api/v1/motorcycle/tips/{id}             - Update tip
DELETE /api/v1/motorcycle/tips/{id}             - Delete tip
POST   /api/v1/motorcycle/tips/{id}/like        - Like/unlike tip
POST   /api/v1/motorcycle/tips/{id}/bookmark    - Bookmark/unbookmark tip
POST   /api/v1/motorcycle/tips/{id}/share       - Track share
POST   /api/v1/motorcycle/tips/{id}/use-template - Create schedule from tip template
```

## 📝 Features Implemented

### 1. Tips Management

- ✅ Create, Read, Update, Delete tips
- ✅ Support untuk guest mode (device_id)
- ✅ Auto-generate tags based on riding style
- ✅ Soft deletes untuk tips
- ✅ Status: pending_review, published, rejected

### 2. Filtering & Searching

- ✅ Search by title/description/hashtags (partial match, case-insensitive)
- ✅ Filter by brand
- ✅ Filter by riding style
- ✅ Filter by tags
- ✅ Filter by hashtags (comma-separated, OR semantics)
- ✅ Filter by user_id

### 3. Sorting

- ✅ Latest (default)
- ✅ Popular (by views + likes)
- ✅ Rating
- ✅ Relevance

### 4. Interactions

- ✅ Like/Unlike dengan counter
- ✅ Bookmark/Unbookmark dengan counter
- ✅ Share tracking dengan platform info
- ✅ View counting

### 5. Template to Schedule

- ✅ Convert tip ke service schedule
- ✅ Support interval dan one_time schedules
- ✅ Usage tracking

### 6. Stats & Analytics

- ✅ Rating (avg)
- ✅ Likes count
- ✅ Bookmarks count
- ✅ Shares count
- ✅ Views count
- ✅ Success percentage (usage vs success)

## 🔧 Next Steps (To Be Done by User)

### 1. Start MySQL Server

```bash
# Di Laragon, start MySQL service
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Run Seeder (Optional)

```bash
php artisan db:seed --class=TipTagSeeder
```

Atau tambahkan ke `database/seeders/DatabaseSeeder.php`:

```php
public function run(): void
{
    $this->call([
        // ... existing seeders
        TipTagSeeder::class,
    ]);
}
```

### 4. Testing API

#### Test dengan Postman:

**1. Get List Tips (Public)**

```
GET http://localhost/api/v1/motorcycle/public/tips
GET http://localhost/api/v1/motorcycle/public/tips?search=oli&brand=Honda
GET http://localhost/api/v1/motorcycle/public/tips?hashtags=oli mesin,daily rider
```

**2. Create Tip (Authenticated)**

```
POST http://localhost/api/v1/motorcycle/tips
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Cara Efisien Ganti Oli untuk Pemakaian Harian",
  "description": "Metode ganti oli yang terbukti memperpanjang umur mesin hingga 30%",
  "vehicle": {
    "brand": "Honda",
    "model": "PCX 160",
    "year": 2023,
    "riding_style": "Harian / Commuter"
  },
  "estimated_time": "30 menit",
  "tools": [
    {
      "name": "Kunci Ring 17",
      "is_optional": false
    },
    {
      "name": "Wadah Oli Bekas",
      "is_optional": false
    }
  ],
  "steps": [
    {
      "title": "Persiapan",
      "description": "Siapkan semua alat yang diperlukan"
    },
    {
      "title": "Posisikan Motor",
      "description": "Parkirkan motor di tempat yang rata"
    },
    {
      "title": "Kuras Oli Lama",
      "description": "Lepaskan baut pembuangan oli"
    }
  ],
  "maintenance_interval": {
    "distance_km": 2000,
    "time_months": 3
  },
  "important_notes": "Pastikan oli yang digunakan sesuai spesifikasi pabrikan",
  "hashtags": ["Oli Mesin", "Perawatan Rutin"]
}
```

**3. Like Tip**

```
POST http://localhost/api/v1/motorcycle/tips/{id}/like
Authorization: Bearer {token}
Content-Type: application/json

{
  "action": "like"
}
```

**4. Use as Template**

```
POST http://localhost/api/v1/motorcycle/tips/{id}/use-template
Authorization: Bearer {token}
Content-Type: application/json

{
  "vehicle_id": 1,
  "schedule_type": "interval",
  "interval_type": "distance",
  "interval_value": 2000,
  "notes": "Ganti oli rutin"
}
```

## 🎯 Key Features & Design Decisions

### 1. Guest Mode Support

- Like, bookmark, dan share mendukung `user_id` (authenticated) atau `device_id` (guest)
- Menggunakan header `X-Device-ID` untuk guest identification

### 2. Auto Tag Generation

- Saat create tip, otomatis generate tag untuk riding_style
- Menggunakan `firstOrCreate` untuk menghindari duplicate tags

### 3. Stats Tracking

- Counter disimpan di table `tips` untuk performance
- Increment/decrement menggunakan query atomic
- Success percentage dihitung on-the-fly via accessor

### 4. Soft Deletes

- Tips menggunakan soft deletes
- Related data (tools, steps) akan tetap terhapus (cascade)
- Like, bookmark, share tetap ada untuk analytics

### 5. Published Status

- Tips harus berstatus 'published' untuk tampil di public
- Owner bisa lihat tips sendiri dengan status apapun
- Admin bisa approve/reject (future enhancement)

### 6. Pagination

- Default 10 items per page
- Max 50 items per page
- Response format sesuai dokumentasi FE

### 7. Hashtag Normalization Contract

- Input create/update hashtag boleh dengan atau tanpa simbol `#`
- Backend melakukan normalisasi sebelum simpan: trim, hapus prefix `#` berulang, rapikan multiple spaces
- Backend melakukan deduplikasi case-insensitive (contoh: `Oli Mesin` dan `oli mesin` dianggap sama)
- Validasi hashtag item setelah normalisasi: panjang 2-30 karakter, maksimal 10 item
- Parameter `search` juga mencari ke field hashtags
- Parameter `hashtags` menerima comma-separated values dan menggunakan OR semantics

### 8. Validation Contract Update (18 Mar 2026)

- Validasi field `title` untuk endpoint `POST /tips` dan `PUT /tips/{id}` diperbarui menjadi minimal 5 karakter
- Validasi field `tools` untuk endpoint `POST /tips` dan `PUT /tips/{id}` tetap minimal 1 item (minimal 1 alat)
- Pesan error validasi judul juga diperbarui agar konsisten dengan aturan baru (minimal 5 karakter)

## 📚 Database Schema Highlights

### Tips Table

- Menyimpan semua info utama tip
- Counter fields untuk performance
- JSON field untuk hashtags
- Enum untuk status
- Soft deletes

### Relationships

- `tips` -> `users` (belongsTo)
- `tips` -> `tip_tags` (belongsToMany)
- `tips` -> `tip_tools` (hasMany)
- `tips` -> `tip_steps` (hasMany)
- `tips` -> `tip_likes` (hasMany)
- `tips` -> `tip_bookmarks` (hasMany)
- `tips` -> `tip_shares` (hasMany)

## ⚠️ Notes

1. **Database Connection**: Pastikan MySQL server running sebelum migrate
2. **Authentication**: Endpoint yang butuh auth menggunakan Sanctum middleware
3. **File Upload**: Image upload untuk steps belum diimplementasi (Phase 3)
4. **Admin Features**: Approve/reject tips belum diimplementasi
5. **AI Recommendation**: Tag "Rekomendasi AI" perlu algoritma tersendiri (future)
6. **Trending Algorithm**: Perlu background job untuk update trending tags (future)

## 🎉 Completion Status

✅ Phase 1 (MVP) - COMPLETED

- GET /tips (list with filters)
- GET /tips/{id} (detail)
- POST /tips (create)
- POST /tips/{id}/like
- POST /tips/{id}/bookmark

✅ Phase 2 - COMPLETED

- PUT /tips/{id} (update)
- DELETE /tips/{id}
- POST /tips/{id}/use-template
- POST /tips/{id}/share

🔄 Phase 3 (Enhancement) - NOT STARTED

- Image upload for steps
- Comments/reviews system
- Report inappropriate content
- AI-based recommendation algorithm
- Search with autocomplete

---

## 📞 Questions or Issues?

Jika ada pertanyaan atau menemukan bug:

1. Check error logs di `storage/logs/laravel.log`
2. Test dengan Postman collection
3. Verify database tables created correctly
4. Check authentication token valid

Selamat mencoba! 🚀

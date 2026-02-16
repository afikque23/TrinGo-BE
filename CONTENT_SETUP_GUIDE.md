# Content Management System - Setup & Testing Guide

## 📋 Overview

Sistem manajemen konten dinamis untuk MotoTracker yang memungkinkan admin mengelola konten statis aplikasi mobile.

## 🚀 Quick Start

### 1. Run Migration

```bash
php artisan migrate
```

Output yang diharapkan:

```
Migrating: 2024_02_11_000001_create_contents_table
Migrated:  2024_02_11_000001_create_contents_table (XX.XXms)
```

### 2. Seed Sample Data (Optional)

```bash
php artisan db:seed --class=ContentSeeder
```

Ini akan membuat 6 sample content:

- Syarat dan Ketentuan (terms)
- Kebijakan Privasi (privacy)
- Panduan Pengguna (guide)
- Tentang MotoTracker (about)
- FAQ (faq)
- Cara Sistem Bekerja (system_info)

### 3. Verify Database

```bash
php artisan tinker
```

Lalu jalankan:

```php
Content::count(); // Should return 6 if seeded
Content::published()->count(); // Should return 6 (all published)
Content::ofType('terms')->first()->title; // "Syarat dan Ketentuan"
```

## 🧪 Testing API

### Option 1: Using Postman

1. Import file: `Content_Management_API.postman_collection.json`
2. Set variable `base_url` ke `http://localhost:8000/api`
3. Set variable `admin_token` dengan Sanctum token Anda
4. Test endpoints

### Option 2: Using cURL

#### Test Public Endpoint (No Auth)

```bash
# Get published Terms & Conditions
curl -X GET "http://localhost:8000/api/public/contents/terms" \
  -H "Accept: application/json"
```

Expected Response:

```json
{
    "success": true,
    "message": "Contents fetched successfully",
    "data": [
        {
            "id": 1,
            "title": "Syarat dan Ketentuan",
            "slug": "syarat-dan-ketentuan",
            "type": "terms",
            "body": "...",
            "status": "published",
            "order": 1,
            "created_at": "2024-02-11 10:00:00",
            "updated_at": "2024-02-11 10:00:00"
        }
    ]
}
```

#### Test Admin Endpoint (Requires Auth)

**Step 1: Get Admin Token**

```bash
# Login as admin
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@mototracker.com",
    "password": "your_password"
  }'
```

**Step 2: Use Token to Create Content**

```bash
curl -X POST "http://localhost:8000/api/admin/contents" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "New Content",
    "type": "guide",
    "body": "[{\"section\":\"Test\",\"content\":\"This is a test\"}]",
    "status": "published",
    "order": 7
  }'
```

**Step 3: Get All Contents (Admin)**

```bash
curl -X GET "http://localhost:8000/api/admin/contents" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

**Step 4: Update Content**

```bash
curl -X PUT "http://localhost:8000/api/admin/contents/7" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Updated Content Title",
    "status": "draft"
  }'
```

**Step 5: Delete Content**

```bash
curl -X DELETE "http://localhost:8000/api/admin/contents/7" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

### Option 3: Using Laravel Tinker

```bash
php artisan tinker
```

```php
// Create content
$content = Content::create([
    'title' => 'Test via Tinker',
    'type' => 'guide',
    'body' => json_encode([['section' => 'Test', 'content' => 'Hello']]),
    'status' => 'published',
    'order' => 10
]);

// Get published contents by type
$terms = Content::published()->ofType('terms')->ordered()->get();

// Update content
$content->update(['status' => 'draft']);

// Delete content
$content->delete();
```

## 📊 Verification Checklist

### Database

- [ ] Table `contents` created with correct schema
- [ ] Indexes created on (type, status, order)
- [ ] Sample data seeded successfully

### Models

- [ ] Content model has fillable fields
- [ ] Auto slug generation works
- [ ] Query scopes work (published, ofType, ordered)

### API Endpoints

- [ ] GET `/api/public/contents/{type}` works without auth
- [ ] GET `/api/admin/contents` requires auth
- [ ] POST `/api/admin/contents` creates content with validation
- [ ] GET `/api/admin/contents/{id}` returns single content
- [ ] PUT `/api/admin/contents/{id}` updates content
- [ ] DELETE `/api/admin/contents/{id}` deletes content

### Validation

- [ ] Title is required
- [ ] Type must be valid enum value
- [ ] Body is required
- [ ] Status must be draft or published
- [ ] Slug is unique
- [ ] Order is integer >= 0

### Business Logic

- [ ] Public endpoint only returns published content
- [ ] Contents ordered by order field (ascending)
- [ ] Slug auto-generated from title if empty
- [ ] Slug uniqueness enforced with auto-increment suffix

## 🐛 Troubleshooting

### Problem: Migration fails

**Solution:**

```bash
php artisan migrate:fresh
php artisan db:seed --class=ContentSeeder
```

### Problem: Auth middleware not working

**Check:**

1. Sanctum installed: `composer require laravel/sanctum`
2. Middleware registered in `app/Http/Kernel.php`
3. Token generated for user
4. Token passed in Authorization header

### Problem: 404 on admin routes

**Check:**

1. Route registered in `routes/api.php`
2. Check.role middleware exists
3. User has 'admin' role

### Problem: Public endpoint returns empty array

**Reason:** No published content for that type

**Solution:**

```bash
php artisan tinker
```

```php
Content::where('type', 'terms')->update(['status' => 'published']);
```

## 📝 File Structure Created

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── ContentController.php
│   ├── Requests/
│   │   ├── StoreContentRequest.php
│   │   └── UpdateContentRequest.php
│   └── Resources/
│       └── ContentResource.php
├── Models/
│   └── Content.php
database/
├── migrations/
│   └── 2024_02_11_000001_create_contents_table.php
└── seeders/
    └── ContentSeeder.php
routes/
└── api.php (updated)
```

## 🔧 Configuration

No additional configuration needed. System uses default Laravel settings:

- Database: Set in `.env` file
- Authentication: Laravel Sanctum
- API prefix: `/api` (default)

## 🚦 Next Steps

1. **Web Admin Integration:**
    - Connect existing `manajemen_konten.blade.php` to API
    - Implement AJAX calls to CRUD endpoints
    - Add success/error notifications

2. **Mobile App Integration:**
    - Use `/api/public/contents/{type}` endpoint
    - Parse JSON body field
    - Render dynamic sections

3. **Enhancements:**
    - Add search functionality
    - Implement content versioning
    - Add rich text editor
    - Multi-language support

## 📚 Documentation

- Full API Documentation: `CONTENT_API_DOCUMENTATION.md`
- Postman Collection: `Content_Management_API.postman_collection.json`

## ✅ Success Indicators

System is working correctly if:

1. ✅ Migration runs without errors
2. ✅ Seeder populates 6 sample contents
3. ✅ Public endpoint returns published contents
4. ✅ Admin endpoints require authentication
5. ✅ Validation works on create/update
6. ✅ Slug auto-generated uniquely
7. ✅ JSON responses follow consistent format

---

**Version:** 1.0.0  
**Last Updated:** February 11, 2026  
**Status:** ✅ Ready for Production

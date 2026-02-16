# Content Management API Documentation

## Overview

Dynamic Content Management System untuk MotoTracker - memungkinkan admin mengelola konten statis aplikasi mobile melalui API.

## Arsitektur

- **Framework**: Laravel 10
- **Authentication**: Laravel Sanctum
- **Architecture Pattern**: Clean Architecture (Controllers → Services → Models)
- **Response Format**: RESTful JSON

---

## Database Schema

### Table: `contents`

| Column     | Type                | Description                                                 |
| ---------- | ------------------- | ----------------------------------------------------------- |
| id         | BIGINT              | Primary key                                                 |
| title      | VARCHAR(255)        | Judul konten                                                |
| slug       | VARCHAR(255) UNIQUE | URL-friendly identifier                                     |
| type       | VARCHAR(255)        | Tipe konten: terms, privacy, guide, about, faq, system_info |
| body       | LONGTEXT            | Isi konten (JSON format)                                    |
| status     | ENUM                | draft, published                                            |
| order      | INTEGER             | Urutan tampilan (default: 0)                                |
| created_at | TIMESTAMP           | Waktu dibuat                                                |
| updated_at | TIMESTAMP           | Waktu diupdate                                              |

---

## API Endpoints

### Admin Endpoints (Authentication Required)

#### 1. Get All Contents

```
GET /api/admin/contents
```

**Headers:**

```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

**Response (200):**

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

---

#### 2. Create New Content

```
POST /api/admin/contents
```

**Headers:**

```
Authorization: Bearer {sanctum_token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**

```json
{
    "title": "Kebijakan Privasi",
    "slug": "kebijakan-privasi",
    "type": "privacy",
    "body": "{\"section\":\"Intro\",\"content\":\"...\"}",
    "status": "published",
    "order": 2
}
```

**Validation Rules:**

- `title`: required, string, max:255
- `slug`: optional, string, max:255, unique
- `type`: required, in:[terms, privacy, guide, about, faq, system_info]
- `body`: required, string
- `status`: optional, in:[draft, published] (default: draft)
- `order`: optional, integer, min:0 (default: 0)

**Response (201):**

```json
{
    "success": true,
    "message": "Content created successfully",
    "data": {
        "id": 2,
        "title": "Kebijakan Privasi",
        "slug": "kebijakan-privasi",
        "type": "privacy",
        "body": "...",
        "status": "published",
        "order": 2,
        "created_at": "2024-02-11 11:00:00",
        "updated_at": "2024-02-11 11:00:00"
    }
}
```

---

#### 3. Get Single Content

```
GET /api/admin/contents/{id}
```

**Headers:**

```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

**Response (200):**

```json
{
    "success": true,
    "message": "Content fetched successfully",
    "data": {
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
}
```

**Response (404):**

```json
{
    "success": false,
    "message": "Content not found"
}
```

---

#### 4. Update Content

```
PUT /api/admin/contents/{id}
```

**Headers:**

```
Authorization: Bearer {sanctum_token}
Content-Type: application/json
Accept: application/json
```

**Request Body (Partial Update Supported):**

```json
{
    "title": "Syarat & Ketentuan (Updated)",
    "status": "draft"
}
```

**Validation Rules:**

- `title`: sometimes, required, string, max:255
- `slug`: sometimes, nullable, string, max:255, unique (except current)
- `type`: sometimes, required, in:[terms, privacy, guide, about, faq, system_info]
- `body`: sometimes, required, string
- `status`: sometimes, nullable, in:[draft, published]
- `order`: sometimes, nullable, integer, min:0

**Response (200):**

```json
{
    "success": true,
    "message": "Content updated successfully",
    "data": {
        "id": 1,
        "title": "Syarat & Ketentuan (Updated)",
        "slug": "syarat-dan-ketentuan",
        "type": "terms",
        "body": "...",
        "status": "draft",
        "order": 1,
        "created_at": "2024-02-11 10:00:00",
        "updated_at": "2024-02-11 12:00:00"
    }
}
```

---

#### 5. Delete Content

```
DELETE /api/admin/contents/{id}
```

**Headers:**

```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

**Response (200):**

```json
{
    "success": true,
    "message": "Content deleted successfully"
}
```

**Response (404):**

```json
{
    "success": false,
    "message": "Content not found"
}
```

---

### Public Endpoints (No Authentication)

#### 6. Get Published Contents by Type

```
GET /api/public/contents/{type}
```

**Parameters:**

- `type`: terms | privacy | guide | about | faq | system_info

**Example:**

```
GET /api/public/contents/terms
```

**Headers:**

```
Accept: application/json
```

**Response (200):**

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

**Response (400):**

```json
{
    "success": false,
    "message": "Invalid content type"
}
```

**Note:**

- Hanya mengembalikan konten dengan `status = 'published'`
- Hasil diurutkan berdasarkan field `order` (ascending)
- Mobile app menggunakan endpoint ini untuk fetch konten

---

## Content Types

| Type          | Description         | Use Case                                    |
| ------------- | ------------------- | ------------------------------------------- |
| `terms`       | Syarat & Ketentuan  | Legal agreement                             |
| `privacy`     | Kebijakan Privasi   | Privacy policy                              |
| `guide`       | Panduan Pengguna    | User manual / tutorial                      |
| `about`       | Tentang Aplikasi    | App information                             |
| `faq`         | FAQ                 | Frequently asked questions                  |
| `system_info` | Cara Sistem Bekerja | System transparency / algorithm explanation |

---

## Body Format (JSON)

### Recommended Structure

#### For Terms, Privacy, Guide, About, System Info:

```json
[
    {
        "section": "Judul Bagian",
        "content": "Paragraf penjelasan...",
        "items": ["Point 1", "Point 2"]
    }
]
```

#### For FAQ:

```json
[
    {
        "question": "Pertanyaan 1?",
        "answer": "Jawaban detail..."
    },
    {
        "question": "Pertanyaan 2?",
        "answer": "Jawaban detail..."
    }
]
```

---

## Installation & Setup

### 1. Run Migration

```bash
php artisan migrate
```

### 2. Seed Sample Data (Optional)

```bash
php artisan db:seed --class=ContentSeeder
```

### 3. Test API

```bash
# Get published contents
curl -X GET "http://localhost:8000/api/public/contents/terms"

# Admin: Create new content (requires token)
curl -X POST "http://localhost:8000/api/admin/contents" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Content",
    "type": "guide",
    "body": "{\"test\":true}",
    "status": "published"
  }'
```

---

## Integration with Mobile App

### Example Usage (Flutter/React Native)

```javascript
// Fetch Terms & Conditions
async function fetchTerms() {
    const response = await fetch(
        "https://api.mototracker.com/api/public/contents/terms",
    );
    const data = await response.json();

    if (data.success) {
        const contents = data.data;
        // Render contents
        contents.forEach((content) => {
            const sections = JSON.parse(content.body);
            renderSections(sections);
        });
    }
}

// Fetch FAQ
async function fetchFAQ() {
    const response = await fetch(
        "https://api.mototracker.com/api/public/contents/faq",
    );
    const data = await response.json();

    if (data.success) {
        const faqs = JSON.parse(data.data[0].body);
        renderFAQs(faqs);
    }
}
```

---

## Error Responses

### Validation Error (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "title": ["Judul konten wajib diisi."],
        "type": ["Tipe konten tidak valid."]
    }
}
```

### Unauthorized (401)

```json
{
    "message": "Unauthenticated."
}
```

### Server Error (500)

```json
{
    "success": false,
    "message": "Failed to create content",
    "error": "Database connection error"
}
```

---

## Model Features

### Auto Slug Generation

```php
// Otomatis generate slug dari title jika tidak diisi
Content::create([
    'title' => 'Syarat dan Ketentuan',
    'type' => 'terms',
    'body' => '...'
]);
// slug akan otomatis: "syarat-dan-ketentuan"
```

### Query Scopes

```php
// Get published contents
$published = Content::published()->get();

// Get by type
$terms = Content::ofType('terms')->get();

// Get ordered
$ordered = Content::ordered()->get();

// Combine scopes
$contents = Content::published()->ofType('faq')->ordered()->get();
```

---

## Security

1. **Admin Endpoints**: Dilindungi dengan `auth:sanctum` dan `check.role:admin` middleware
2. **Public Endpoints**: Hanya menampilkan konten dengan status `published`
3. **Validation**: Semua input divalidasi menggunakan Form Request
4. **Slug Uniqueness**: Otomatis generate unique slug
5. **SQL Injection**: Protected by Eloquent ORM

---

## Performance Optimization

1. **Database Index**: Added composite index on `(type, status, order)`
2. **Resource Classes**: Consistent response format dengan minimal data transfer
3. **Eager Loading**: Dapat ditambahkan jika ada relasi di masa depan

---

## Future Enhancements

- [ ] Content versioning (revision history)
- [ ] Multi-language support (i18n)
- [ ] Rich text editor integration
- [ ] Image/media upload for content
- [ ] Scheduled publishing (publish_at field)
- [ ] Content analytics (view count, engagement)
- [ ] Search functionality
- [ ] Content categories/tags

---

## Support

Untuk pertanyaan atau bug report, silakan hubungi tim development.

**Version**: 1.0.0
**Last Updated**: February 11, 2026

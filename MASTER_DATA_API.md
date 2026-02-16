# Master Data API Documentation

## 📋 Overview

Sistem Master Data untuk mengelola **Service Type** (Jenis Servis) dan **Reminder Option** (Opsi Pengingat) secara dinamis melalui API.

**Tujuan:** Web Admin dapat mengelola data master, dan aplikasi mobile dapat mengambil data dropdown tanpa hardcoded values.

---

## 🔐 Authentication

Semua endpoint memerlukan **Bearer Token** melalui Laravel Sanctum:

```http
Authorization: Bearer {token}
```

---

## 🛠️ A. Service Type API

### 1. List Service Types

**Endpoint:** `GET /api/service-types`

**Query Parameters:**

- `active` (optional): `1` (hanya aktif) | `0` (hanya non-aktif)
- `per_page` (optional): Jumlah per halaman (default: 15)

**Request Example:**

```http
GET /api/service-types?active=1&per_page=20
Authorization: Bearer {token}
```

**Response Success (200):**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Ganti Oli",
      "description": "Penggantian oli mesin",
      "is_active": true,
      "services_count": 15,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    },
    {
      "id": 2,
      "name": "Tune Up",
      "description": "Tune up mesin",
      "is_active": true,
      "services_count": 8,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

### 2. Get Single Service Type

**Endpoint:** `GET /api/service-types/{id}`

**Response Success (200):**

```json
{
    "success": true,
    "message": "Detail jenis service berhasil diambil.",
    "data": {
        "id": 1,
        "name": "Ganti Oli",
        "description": "Penggantian oli mesin",
        "is_active": true,
        "services_count": 15,
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T00:00:00Z"
    }
}
```

---

### 3. Create Service Type

**Endpoint:** `POST /api/service-types`

**Request Body:**

```json
{
    "name": "Ganti Ban",
    "description": "Penggantian ban motor",
    "is_active": true
}
```

**Validation Rules:**

- `name`: required, string, max:100, unique
- `description`: nullable, string, max:500
- `is_active`: boolean (default: true)

**Response Success (201):**

```json
{
    "success": true,
    "message": "Jenis service berhasil ditambahkan.",
    "data": {
        "id": 9,
        "name": "Ganti Ban",
        "description": "Penggantian ban motor",
        "is_active": true,
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T00:00:00Z"
    }
}
```

**Response Error (422):**

```json
{
    "success": false,
    "message": "Nama jenis service sudah digunakan.",
    "errors": {
        "name": ["Nama jenis service sudah digunakan."]
    }
}
```

---

### 4. Update Service Type

**Endpoint:** `PUT /api/service-types/{id}`

**Request Body:**

```json
{
    "name": "Ganti Ban Depan",
    "description": "Penggantian ban depan motor",
    "is_active": false
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Jenis service berhasil diperbarui.",
    "data": {
        "id": 9,
        "name": "Ganti Ban Depan",
        "description": "Penggantian ban depan motor",
        "is_active": false,
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T10:30:00Z"
    }
}
```

---

### 5. Toggle Service Type Status

**Endpoint:** `PATCH /api/service-types/{id}/toggle-status`

**Response Success (200):**

```json
{
    "success": true,
    "message": "Jenis service berhasil dinonaktifkan.",
    "data": {
        "id": 9,
        "name": "Ganti Ban Depan",
        "is_active": false,
        "...": "..."
    }
}
```

---

### 6. Delete Service Type

**Endpoint:** `DELETE /api/service-types/{id}`

**Response Success (200):**

```json
{
    "success": true,
    "message": "Jenis service berhasil dihapus.",
    "data": null
}
```

**Response Error (422) - Masih Digunakan:**

```json
{
    "success": false,
    "message": "Jenis service tidak dapat dihapus karena masih digunakan pada data servis."
}
```

---

## ⏰ B. Reminder Option API

### 1. List Reminder Options

**Endpoint:** `GET /api/reminder-options`

**Query Parameters:**

- `active` (optional): `1` (hanya aktif) | `0` (hanya non-aktif)
- `unit` (optional): `km` | `days`
- `per_page` (optional): Jumlah per halaman (default: 15)

**Request Example:**

```http
GET /api/reminder-options?active=1&unit=km
Authorization: Bearer {token}
```

**Response Success (200):**

```json
{
  "data": [
    {
      "id": 1,
      "label": "100 km sebelum",
      "value": 100,
      "unit": "km",
      "is_active": true,
      "display_text": "100 km sebelum",
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    },
    {
      "id": 2,
      "label": "200 km sebelum",
      "value": 200,
      "unit": "km",
      "is_active": true,
      "display_text": "200 km sebelum",
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

### 2. Get Single Reminder Option

**Endpoint:** `GET /api/reminder-options/{id}`

**Response Success (200):**

```json
{
    "success": true,
    "message": "Detail opsi pengingat berhasil diambil.",
    "data": {
        "id": 1,
        "label": "100 km sebelum",
        "value": 100,
        "unit": "km",
        "is_active": true,
        "display_text": "100 km sebelum",
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T00:00:00Z"
    }
}
```

---

### 3. Create Reminder Option

**Endpoint:** `POST /api/reminder-options`

**Request Body:**

```json
{
    "label": "300 km sebelum",
    "value": 300,
    "unit": "km",
    "is_active": true
}
```

**Validation Rules:**

- `label`: required, string, max:100
- `value`: required, integer, min:1, max:999999
- `unit`: required, enum (km, days)
- `is_active`: boolean (default: true)

**Response Success (201):**

```json
{
    "success": true,
    "message": "Opsi pengingat berhasil ditambahkan.",
    "data": {
        "id": 10,
        "label": "300 km sebelum",
        "value": 300,
        "unit": "km",
        "is_active": true,
        "display_text": "300 km sebelum",
        "created_at": "2024-01-01T12:00:00Z",
        "updated_at": "2024-01-01T12:00:00Z"
    }
}
```

**Response Error (422) - Duplicate:**

```json
{
    "success": false,
    "message": "Kombinasi nilai dan unit pengingat sudah ada."
}
```

---

### 4. Update Reminder Option

**Endpoint:** `PUT /api/reminder-options/{id}`

**Request Body:**

```json
{
    "label": "350 km sebelum",
    "value": 350,
    "unit": "km",
    "is_active": false
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Opsi pengingat berhasil diperbarui.",
    "data": {
        "id": 10,
        "label": "350 km sebelum",
        "value": 350,
        "unit": "km",
        "is_active": false,
        "display_text": "350 km sebelum",
        "created_at": "2024-01-01T12:00:00Z",
        "updated_at": "2024-01-01T13:30:00Z"
    }
}
```

---

### 5. Toggle Reminder Option Status

**Endpoint:** `PATCH /api/reminder-options/{id}/toggle-status`

**Response Success (200):**

```json
{
    "success": true,
    "message": "Opsi pengingat berhasil diaktifkan.",
    "data": {
        "id": 10,
        "is_active": true,
        "...": "..."
    }
}
```

---

### 6. Delete Reminder Option

**Endpoint:** `DELETE /api/reminder-options/{id}`

**Response Success (200):**

```json
{
    "success": true,
    "message": "Opsi pengingat berhasil dihapus.",
    "data": null
}
```

---

## 📊 Use Cases

### Mobile App - Dropdown Service Type

```javascript
// Fetch active service types for dropdown
const response = await fetch("/api/service-types?active=1", {
    headers: {
        Authorization: `Bearer ${token}`,
    },
});

const serviceTypes = response.data.map((type) => ({
    value: type.id,
    label: type.name,
}));
```

### Mobile App - Dropdown Reminder Options

```javascript
// Fetch active reminder options (km only)
const response = await fetch("/api/reminder-options?active=1&unit=km", {
    headers: {
        Authorization: `Bearer ${token}`,
    },
});

const reminderOptions = response.data.map((option) => ({
    value: option.id,
    label: option.display_text,
}));
```

### Web Admin - Manage Master Data

- Admin dapat CRUD service types dan reminder options
- Toggle status aktif/non-aktif tanpa menghapus data
- Validasi otomatis untuk duplikasi dan data yang sedang digunakan

---

## 🗄️ Database Structure

### Service Types Table

```sql
id              BIGINT UNSIGNED PRIMARY KEY
name            VARCHAR(100) UNIQUE
description     TEXT NULL
is_active       BOOLEAN DEFAULT TRUE
created_at      TIMESTAMP
updated_at      TIMESTAMP

INDEX: is_active
```

### Reminder Options Table

```sql
id              BIGINT UNSIGNED PRIMARY KEY
label           VARCHAR(100)
value           INTEGER
unit            ENUM('km', 'days')
is_active       BOOLEAN DEFAULT TRUE
created_at      TIMESTAMP
updated_at      TIMESTAMP

INDEX: is_active, unit
UNIQUE: (value, unit)
```

---

## 🚀 Implementation Steps

1. **Run Migration:**

```bash
php artisan migrate
```

2. **Test Endpoints:**

- Import Postman collection (jika ada)
- Test semua CRUD operations
- Verify validations

3. **Mobile Integration:**

- Fetch service types untuk form tambah servis
- Fetch reminder options untuk form tambah reminder
- Cache results jika perlu

---

## ✅ Features

- ✅ Clean & modular architecture
- ✅ FormRequest validation dengan pesan bahasa Indonesia
- ✅ API Resource untuk response formatting
- ✅ Soft filtering (active/unit parameters)
- ✅ Pagination support
- ✅ Duplicate prevention
- ✅ Delete protection (jika data masih digunakan)
- ✅ Toggle status endpoint
- ✅ Sanctum authentication
- ✅ Scalable codebase

---

## 📝 Notes

- Semua endpoint menggunakan `auth:sanctum` middleware
- Response format konsisten menggunakan `ApiResponse` trait
- Service Type relationship ke `ServiceHistory` model
- Reminder Option siap untuk relationship ke `Reminder` model (jika sudah dibuat)

# Sprint 5 - Riwayat Servis, Biaya, dan Struk

> Backend API untuk pencatatan riwayat servis dan analisis biaya perawatan sepeda motor

## 🚀 Quick Start

### Prerequisites

-   Laravel 10.x sudah berjalan
-   Database configured & migrated
-   User sudah login dan memiliki primary vehicle

### API Endpoints

```
Base URL: http://localhost/api
Authentication: Bearer Token
```

| Method | Endpoint                          | Description                |
| ------ | --------------------------------- | -------------------------- |
| GET    | `/service-histories`              | List semua riwayat servis  |
| POST   | `/service-histories`              | Tambah riwayat servis baru |
| GET    | `/service-histories/{id}`         | Detail riwayat servis      |
| PUT    | `/service-histories/{id}`         | Update riwayat servis      |
| DELETE | `/service-histories/{id}`         | Hapus riwayat servis       |
| GET    | `/service-histories/cost-summary` | Analisis biaya servis      |

## 📝 Quick Example

### Tambah Riwayat Servis

```bash
POST /api/service-histories
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "service_type": "Ganti Oli",
  "performed_at": "2026-01-01",
  "odometer": 5000,
  "cost": 150000,
  "service_provider": "Bengkel Jaya",
  "notes": "Ganti oli Shell AX7",
  "receipt_photo": [FILE]  # Optional
}
```

### Lihat Ringkasan Biaya

```bash
GET /api/service-histories/cost-summary?period=month&year=2026&month=1
Authorization: Bearer {token}
```

## ✨ Key Features

-   ✅ **CRUD Lengkap** - Create, Read, Update, Delete riwayat servis
-   ✅ **Upload Foto Struk** - Opsional, max 5MB
-   ✅ **Analisis Biaya** - Total, average, breakdown per jenis servis
-   ✅ **Period Filter** - All time, yearly, monthly
-   ✅ **Primary Vehicle Auto** - Tidak perlu kirim vehicle_id
-   ✅ **Validation** - Input validation lengkap
-   ✅ **Soft Delete** - Data tidak benar-benar dihapus

## 📦 Required Fields

| Field            | Type    | Required | Notes                                      |
| ---------------- | ------- | -------- | ------------------------------------------ |
| service_type     | string  | ✅       | Max 120 karakter                           |
| performed_at     | date    | ✅       | Format: YYYY-MM-DD, tidak boleh masa depan |
| odometer         | integer | ❌       | Min: 0                                     |
| cost             | numeric | ❌       | Min: 0                                     |
| currency         | string  | ❌       | Default: IDR, 3 karakter                   |
| service_provider | string  | ❌       | Max 150 karakter                           |
| notes            | string  | ❌       | Max 1000 karakter                          |
| receipt_photo    | file    | ❌       | jpg/png/webp, max 5MB                      |

## 📊 Response Format

### Success Response

```json
{
    "success": true,
    "message": "Riwayat servis berhasil ditambahkan.",
    "data": {
        "service_history": {
            "id": 1,
            "service_type": "Ganti Oli",
            "performed_at": "2026-01-01",
            "odometer": 5000,
            "cost": 150000,
            "currency": "IDR",
            "service_provider": "Bengkel Jaya",
            "receipt_url": "http://localhost/storage/receipts/...",
            "notes": "Ganti oli Shell AX7",
            "created_at": "2026-01-01T10:00:00+07:00"
        }
    }
}
```

### Error Response

```json
{
    "success": false,
    "message": "Motor utama belum ditetapkan.",
    "data": null
}
```

## 🔒 Security

-   Semua endpoint dilindungi Sanctum authentication
-   User hanya bisa akses servis milik motor utamanya
-   Primary vehicle check di setiap request
-   File upload validation (type & size)

## 📖 Documentation

-   **Full API Docs**: [SPRINT_5_API_DOCUMENTATION.md](SPRINT_5_API_DOCUMENTATION.md)
-   **Quick Test Guide**: [SPRINT_5_QUICK_TEST.md](SPRINT_5_QUICK_TEST.md)
-   **Implementation Details**: [SPRINT_5_IMPLEMENTATION.md](SPRINT_5_IMPLEMENTATION.md)

## 🧪 Testing

```bash
# Setup
TOKEN="your_token_here"
BASE_URL="http://localhost/api"

# Test: Add service history
curl -X POST "$BASE_URL/service-histories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "service_type": "Ganti Oli",
    "performed_at": "2026-01-01",
    "cost": 150000
  }'

# Test: Get cost summary
curl -X GET "$BASE_URL/service-histories/cost-summary" \
  -H "Authorization: Bearer $TOKEN"
```

## 🎯 Cost Analysis Features

### Summary Metrics

-   Total cost per period
-   Total number of services
-   Average cost per service
-   Last service date

### Breakdown

-   Cost by service type
-   Cost by month (yearly view)
-   Most expensive service

### Period Filters

-   `all` - All time
-   `year` - Per tahun specific
-   `month` - Per bulan specific

## 💡 Tips

### Currency Format

```javascript
// Backend menerima dan mengirim dalam Rupiah
cost: 150000; // Rp 150.000

// Backend internal simpan dalam cent untuk akurasi
cost_cents: 15000000;
```

### Date Format

```javascript
// Input & Output: YYYY-MM-DD
performed_at: "2026-01-01";

// Tidak boleh masa depan
performed_at: "2027-01-01"; // ❌ Error
```

### File Upload

```javascript
// Form Data
const formData = new FormData();
formData.append("receipt_photo", file);

// Supported: jpg, jpeg, png, webp
// Max size: 5MB
```

## ⚠️ Common Errors

### 404 - Motor utama belum ditetapkan

```json
{
    "success": false,
    "message": "Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu."
}
```

**Solution:** Set primary vehicle via `POST /api/vehicles/{id}/set-primary`

### 422 - Validation Error

```json
{
    "success": false,
    "message": "Validasi gagal.",
    "errors": {
        "cost": ["Biaya tidak boleh bernilai negatif."]
    }
}
```

**Solution:** Perbaiki input sesuai error message

### 401 - Unauthorized

```json
{
    "message": "Unauthenticated."
}
```

**Solution:** Login ulang untuk mendapatkan token baru

## 📁 File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── ServiceHistoryController.php
│   └── Requests/
│       ├── StoreServiceHistoryRequest.php
│       └── UpdateServiceHistoryRequest.php
├── Models/
│   └── ServiceHistory.php
└── Traits/
    └── ApiResponse.php

database/
└── migrations/
    └── 2024_01_01_000004_create_service_histories_table.php

routes/
└── api.php

storage/
└── app/
    └── public/
        └── receipts/  # Receipt photos
```

## 🔄 Workflow

```
1. User Login → Get token
2. Set Primary Vehicle (if not set)
3. Add Service History
   ├─ Fill service data
   ├─ (Optional) Upload receipt photo
   └─ Submit
4. View Service List (sorted newest first)
5. View Cost Analysis
   ├─ Total spending
   ├─ Average cost
   ├─ Breakdown by service type
   └─ Monthly comparison
```

## 🚦 Status

-   ✅ **Development**: Complete
-   ✅ **Testing**: Ready
-   ✅ **Documentation**: Complete
-   ✅ **Production**: Ready

## 📞 Need Help?

1. Check [API Documentation](SPRINT_5_API_DOCUMENTATION.md)
2. Try [Quick Test Guide](SPRINT_5_QUICK_TEST.md)
3. Review [Implementation Details](SPRINT_5_IMPLEMENTATION.md)

---

**Version:** 1.0.0  
**Sprint:** 5 - Riwayat Servis, Biaya, dan Struk  
**Date:** January 2, 2026  
**Status:** ✅ Production Ready

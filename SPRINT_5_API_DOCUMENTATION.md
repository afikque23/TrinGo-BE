# Sprint 5 API Documentation

# Riwayat Servis, Biaya, dan Struk

## Deskripsi

Sprint 5 menyediakan backend untuk pencatatan riwayat servis dan analisis biaya perawatan kendaraan. Semua endpoint secara otomatis menggunakan motor utama (primary vehicle) pengguna sebagai konteks, sehingga frontend tidak perlu mengirimkan `vehicle_id`.

---

## Base URL

```
http://localhost/api
```

---

## Authentication

Semua endpoint Sprint 5 dilindungi oleh autentikasi Sanctum. Sertakan token pada setiap request:

```
Authorization: Bearer {your_token}
```

---

## Endpoints

### 1. Daftar Riwayat Servis

Menampilkan semua riwayat servis untuk motor utama pengguna, diurutkan dari servis terbaru ke terlama.

**Endpoint:**

```
GET /api/service-histories
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Riwayat servis berhasil diambil.",
    "data": {
        "service_histories": [
            {
                "id": 1,
                "service_type": "Ganti Oli",
                "performed_at": "2026-01-01",
                "odometer": 5000,
                "cost": 150000,
                "currency": "IDR",
                "service_provider": "Bengkel Jaya Motor",
                "receipt_url": "http://localhost/storage/receipts/1735891234_1_struk.jpg",
                "notes": "Ganti oli Motul 10W-40",
                "created_at": "2026-01-01T10:00:00+07:00"
            },
            {
                "id": 2,
                "service_type": "Servis Berkala",
                "performed_at": "2025-12-15",
                "odometer": 4500,
                "cost": 300000,
                "currency": "IDR",
                "service_provider": "AHASS Resmi",
                "receipt_url": null,
                "notes": null,
                "created_at": "2025-12-15T14:30:00+07:00"
            }
        ],
        "total": 2,
        "vehicle": {
            "id": 1,
            "name": "Honda Vario 160",
            "plate_number": "B 1234 XYZ"
        }
    }
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu.",
    "data": null
}
```

---

### 2. Tambah Riwayat Servis

Menambahkan riwayat servis baru untuk motor utama pengguna.

**Endpoint:**

```
POST /api/service-histories
```

**Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**

```
service_type: string (required) - Jenis servis (max 120 karakter)
performed_at: date (required) - Tanggal servis (format: YYYY-MM-DD, tidak boleh masa depan)
odometer: integer (optional) - Kilometer kendaraan (min: 0)
cost: numeric (optional) - Biaya servis (min: 0)
currency: string (optional) - Kode mata uang (default: IDR, 3 karakter)
service_provider: string (optional) - Nama bengkel/provider (max 150 karakter)
notes: string (optional) - Catatan tambahan (max 1000 karakter)
receipt_photo: file (optional) - Foto struk (jpg, jpeg, png, webp, max 5MB)
```

**Contoh Request:**

```
service_type=Ganti Oli
performed_at=2026-01-01
odometer=5000
cost=150000
currency=IDR
service_provider=Bengkel Jaya Motor
notes=Ganti oli Motul 10W-40
receipt_photo=[FILE]
```

**Response Success (201):**

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
            "service_provider": "Bengkel Jaya Motor",
            "receipt_url": "http://localhost/storage/receipts/1735891234_1_struk.jpg",
            "notes": "Ganti oli Motul 10W-40",
            "created_at": "2026-01-01T10:00:00+07:00"
        }
    }
}
```

**Response Error (422 - Validation):**

```json
{
    "success": false,
    "message": "Validasi gagal.",
    "errors": {
        "service_type": ["Jenis servis wajib diisi."],
        "performed_at": ["Tanggal servis tidak boleh di masa depan."],
        "cost": ["Biaya tidak boleh bernilai negatif."],
        "odometer": ["Kilometer tidak boleh bernilai negatif."]
    }
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu.",
    "data": null
}
```

---

### 3. Detail Riwayat Servis

Menampilkan detail riwayat servis berdasarkan ID.

**Endpoint:**

```
GET /api/service-histories/{id}
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Detail riwayat servis berhasil diambil.",
    "data": {
        "service_history": {
            "id": 1,
            "service_type": "Ganti Oli",
            "performed_at": "2026-01-01",
            "odometer": 5000,
            "cost": 150000,
            "currency": "IDR",
            "service_provider": "Bengkel Jaya Motor",
            "receipt_url": "http://localhost/storage/receipts/1735891234_1_struk.jpg",
            "notes": "Ganti oli Motul 10W-40",
            "created_at": "2026-01-01T10:00:00+07:00",
            "updated_at": "2026-01-01T10:00:00+07:00"
        }
    }
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Riwayat servis tidak ditemukan.",
    "data": null
}
```

---

### 4. Update Riwayat Servis

Memperbarui riwayat servis yang sudah ada.

**Endpoint:**

```
PUT/PATCH /api/service-histories/{id}
```

**Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**

```
service_type: string (optional) - Jenis servis
performed_at: date (optional) - Tanggal servis
odometer: integer (optional) - Kilometer kendaraan
cost: numeric (optional) - Biaya servis
currency: string (optional) - Kode mata uang
service_provider: string (optional) - Nama bengkel
notes: string (optional) - Catatan
receipt_photo: file (optional) - Foto struk baru (akan menggantikan yang lama)
```

**Catatan:**

-   Semua field bersifat opsional
-   Jika `receipt_photo` baru diunggah, foto lama akan dihapus
-   Field yang tidak dikirim tidak akan diubah

**Response Success (200):**

```json
{
    "success": true,
    "message": "Riwayat servis berhasil diperbarui.",
    "data": {
        "service_history": {
            "id": 1,
            "service_type": "Ganti Oli + Filter",
            "performed_at": "2026-01-01",
            "odometer": 5000,
            "cost": 180000,
            "currency": "IDR",
            "service_provider": "Bengkel Jaya Motor",
            "receipt_url": "http://localhost/storage/receipts/1735891234_1_struk.jpg",
            "notes": "Ganti oli Motul 10W-40 + filter oli",
            "updated_at": "2026-01-02T09:15:00+07:00"
        }
    }
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Riwayat servis tidak ditemukan.",
    "data": null
}
```

---

### 5. Hapus Riwayat Servis

Menghapus riwayat servis (soft delete).

**Endpoint:**

```
DELETE /api/service-histories/{id}
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Riwayat servis berhasil dihapus.",
    "data": null
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Riwayat servis tidak ditemukan.",
    "data": null
}
```

**Catatan:**

-   Soft delete: data tidak benar-benar dihapus dari database
-   Foto struk akan dihapus dari storage

---

### 6. Ringkasan Biaya Servis

Menampilkan analisis dan ringkasan biaya servis dengan berbagai periode.

**Endpoint:**

```
GET /api/service-histories/cost-summary
```

**Headers:**

```
Authorization: Bearer {token}
```

**Query Parameters:**

```
period: string (optional) - Periode analisis
  - "all" (default): Semua waktu
  - "year": Per tahun
  - "month": Per bulan

year: integer (optional) - Tahun untuk filter (default: tahun sekarang)
month: integer (optional) - Bulan untuk filter (1-12, default: bulan sekarang)
```

**Contoh Request:**

1. Semua waktu:

```
GET /api/service-histories/cost-summary
```

2. Per tahun 2026:

```
GET /api/service-histories/cost-summary?period=year&year=2026
```

3. Per bulan Januari 2026:

```
GET /api/service-histories/cost-summary?period=month&year=2026&month=1
```

**Response Success (200) - All Time:**

```json
{
    "success": true,
    "message": "Ringkasan biaya servis berhasil diambil.",
    "data": {
        "summary": {
            "period": "all",
            "year": null,
            "month": null,
            "total_cost": 1500000,
            "total_services": 10,
            "average_cost": 150000,
            "currency": "IDR",
            "last_service_date": "2026-01-01"
        },
        "cost_by_service_type": [
            {
                "service_type": "Ganti Oli",
                "total_cost": 600000,
                "count": 4,
                "average_cost": 150000
            },
            {
                "service_type": "Servis Berkala",
                "total_cost": 600000,
                "count": 2,
                "average_cost": 300000
            },
            {
                "service_type": "Ganti Ban",
                "total_cost": 300000,
                "count": 1,
                "average_cost": 300000
            }
        ],
        "cost_by_month": [],
        "most_expensive_service": {
            "id": 5,
            "service_type": "Servis Berkala",
            "cost": 350000,
            "performed_at": "2025-12-15"
        },
        "vehicle": {
            "id": 1,
            "name": "Honda Vario 160",
            "plate_number": "B 1234 XYZ"
        }
    }
}
```

**Response Success (200) - Yearly:**

```json
{
    "success": true,
    "message": "Ringkasan biaya servis berhasil diambil.",
    "data": {
        "summary": {
            "period": "year",
            "year": 2026,
            "month": null,
            "total_cost": 900000,
            "total_services": 6,
            "average_cost": 150000,
            "currency": "IDR",
            "last_service_date": "2026-06-15"
        },
        "cost_by_service_type": [
            {
                "service_type": "Ganti Oli",
                "total_cost": 600000,
                "count": 4,
                "average_cost": 150000
            },
            {
                "service_type": "Ganti Ban",
                "total_cost": 300000,
                "count": 1,
                "average_cost": 300000
            }
        ],
        "cost_by_month": [
            {
                "month": "2026-01",
                "total_cost": 300000,
                "count": 2
            },
            {
                "month": "2026-03",
                "total_cost": 150000,
                "count": 1
            },
            {
                "month": "2026-06",
                "total_cost": 450000,
                "count": 3
            }
        ],
        "most_expensive_service": {
            "id": 8,
            "service_type": "Ganti Ban",
            "cost": 300000,
            "performed_at": "2026-06-10"
        },
        "vehicle": {
            "id": 1,
            "name": "Honda Vario 160",
            "plate_number": "B 1234 XYZ"
        }
    }
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu.",
    "data": null
}
```

---

## Alur Penggunaan

### 1. Setup Awal

```
1. Login → Dapatkan token
2. Set motor utama (jika belum ada)
```

### 2. Tambah Riwayat Servis

```
POST /api/service-histories
- Kirim data servis + (opsional) foto struk
- Tidak perlu kirim vehicle_id
```

### 3. Lihat Riwayat

```
GET /api/service-histories
- Tampilkan daftar lengkap
- Diurutkan dari terbaru
```

### 4. Analisis Biaya

```
GET /api/service-histories/cost-summary
- Lihat total pengeluaran
- Filter per bulan/tahun
- Analisis per jenis servis
```

---

## Validasi dan Business Rules

### Validasi Input

1. **service_type** (required)

    - Wajib diisi
    - Maksimal 120 karakter
    - Contoh: "Ganti Oli", "Servis Berkala", "Ganti Ban"

2. **performed_at** (required)

    - Wajib diisi
    - Format: YYYY-MM-DD
    - Tidak boleh di masa depan

3. **odometer** (optional)

    - Harus angka positif
    - Tidak boleh negatif
    - Unit: kilometer

4. **cost** (optional)

    - Harus angka positif
    - Tidak boleh negatif
    - Disimpan dalam cent/sen untuk presisi

5. **currency** (optional)

    - Default: "IDR"
    - Harus 3 karakter (ISO 4217)

6. **service_provider** (optional)

    - Maksimal 150 karakter
    - Nama bengkel/provider

7. **notes** (optional)

    - Maksimal 1000 karakter
    - Catatan bebas

8. **receipt_photo** (optional)
    - Format: jpeg, jpg, png, webp
    - Maksimal 5MB
    - Disimpan di storage/app/public/receipts/

### Business Rules

1. **Primary Vehicle Required**

    - Semua operasi memerlukan motor utama
    - Otomatis menggunakan primary vehicle
    - Error 404 jika primary vehicle belum diset

2. **Authorization**

    - User hanya bisa akses servis milik motor utamanya
    - Security check di setiap endpoint

3. **File Management**

    - Foto struk disimpan di storage
    - Update/delete akan hapus foto lama
    - File naming: timestamp_userId_originalName

4. **Soft Delete**

    - Data tidak benar-benar dihapus
    - Foto struk dihapus dari storage

5. **Cost Storage**
    - Biaya disimpan dalam cent (dikali 100)
    - Menghindari floating point error
    - Response dikembalikan dalam format normal

---

## Error Handling

### Error Codes

-   **200**: Success
-   **201**: Created
-   **401**: Unauthorized (token invalid/expired)
-   **404**: Not Found (motor utama atau servis tidak ditemukan)
-   **422**: Validation Error
-   **500**: Server Error

### Common Errors

1. **Motor Utama Belum Ada (404)**

```json
{
    "success": false,
    "message": "Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu.",
    "data": null
}
```

**Solusi:** Set primary vehicle terlebih dahulu via `POST /api/vehicles/{id}/set-primary`

2. **Validasi Gagal (422)**

```json
{
    "success": false,
    "message": "Validasi gagal.",
    "errors": {
        "service_type": ["Jenis servis wajib diisi."],
        "cost": ["Biaya tidak boleh bernilai negatif."]
    }
}
```

**Solusi:** Perbaiki input sesuai error message

3. **Unauthorized (401)**

```json
{
    "success": false,
    "message": "Unauthenticated.",
    "data": null
}
```

**Solusi:** Login ulang untuk mendapatkan token baru

---

## Contoh Integrasi

### JavaScript/Fetch API

```javascript
// 1. Tambah Riwayat Servis
const formData = new FormData();
formData.append("service_type", "Ganti Oli");
formData.append("performed_at", "2026-01-01");
formData.append("odometer", "5000");
formData.append("cost", "150000");
formData.append("service_provider", "Bengkel Jaya");
formData.append("notes", "Ganti oli Motul");
// Jika ada foto
if (receiptFile) {
    formData.append("receipt_photo", receiptFile);
}

fetch("http://localhost/api/service-histories", {
    method: "POST",
    headers: {
        Authorization: `Bearer ${token}`,
    },
    body: formData,
})
    .then((res) => res.json())
    .then((data) => console.log(data));

// 2. Ambil Daftar Riwayat
fetch("http://localhost/api/service-histories", {
    method: "GET",
    headers: {
        Authorization: `Bearer ${token}`,
    },
})
    .then((res) => res.json())
    .then((data) => {
        const histories = data.data.service_histories;
        // Tampilkan di UI
    });

// 3. Ringkasan Biaya Bulanan
fetch(
    "http://localhost/api/service-histories/cost-summary?period=month&year=2026&month=1",
    {
        method: "GET",
        headers: {
            Authorization: `Bearer ${token}`,
        },
    }
)
    .then((res) => res.json())
    .then((data) => {
        const summary = data.data.summary;
        console.log(`Total biaya: Rp ${summary.total_cost}`);
        console.log(`Rata-rata: Rp ${summary.average_cost}`);
    });
```

### Flutter/Dart

```dart
// 1. Tambah Riwayat Servis
Future<void> addServiceHistory() async {
  final uri = Uri.parse('http://localhost/api/service-histories');
  final request = http.MultipartRequest('POST', uri);

  request.headers['Authorization'] = 'Bearer $token';
  request.fields['service_type'] = 'Ganti Oli';
  request.fields['performed_at'] = '2026-01-01';
  request.fields['odometer'] = '5000';
  request.fields['cost'] = '150000';
  request.fields['notes'] = 'Ganti oli Motul';

  // Jika ada foto
  if (receiptFile != null) {
    request.files.add(
      await http.MultipartFile.fromPath('receipt_photo', receiptFile.path)
    );
  }

  final response = await request.send();
  final responseData = await response.stream.bytesToString();
  final json = jsonDecode(responseData);

  if (json['success']) {
    print('Berhasil tambah servis');
  }
}

// 2. Ambil Ringkasan Biaya
Future<Map<String, dynamic>> getCostSummary() async {
  final response = await http.get(
    Uri.parse('http://localhost/api/service-histories/cost-summary?period=year&year=2026'),
    headers: {
      'Authorization': 'Bearer $token',
    },
  );

  if (response.statusCode == 200) {
    final json = jsonDecode(response.body);
    return json['data'];
  }
  throw Exception('Gagal mengambil ringkasan');
}
```

---

## Testing dengan Postman

### Setup Environment

Buat environment variable:

```
base_url = http://localhost/api
token = (akan diisi setelah login)
primary_vehicle_id = (akan diisi setelah set primary)
service_history_id = (akan diisi setelah create)
```

### Test Collection

1. **Login**

    - POST `{{base_url}}/auth/login`
    - Save token ke environment

2. **Set Primary Vehicle** (jika belum)

    - POST `{{base_url}}/vehicles/{id}/set-primary`
    - Header: `Authorization: Bearer {{token}}`

3. **Create Service History**

    - POST `{{base_url}}/service-histories`
    - Header: `Authorization: Bearer {{token}}`
    - Body: form-data
    - Save service_history_id

4. **Get All Service Histories**

    - GET `{{base_url}}/service-histories`
    - Header: `Authorization: Bearer {{token}}`

5. **Get Cost Summary - All Time**

    - GET `{{base_url}}/service-histories/cost-summary`

6. **Get Cost Summary - Yearly**

    - GET `{{base_url}}/service-histories/cost-summary?period=year&year=2026`

7. **Get Cost Summary - Monthly**

    - GET `{{base_url}}/service-histories/cost-summary?period=month&year=2026&month=1`

8. **Update Service History**

    - PUT `{{base_url}}/service-histories/{{service_history_id}}`

9. **Delete Service History**
    - DELETE `{{base_url}}/service-histories/{{service_history_id}}`

---

## Database Schema

### Table: service_histories

```sql
CREATE TABLE service_histories (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  vehicle_id BIGINT NOT NULL,
  service_type VARCHAR(120) NOT NULL,
  performed_at DATE NOT NULL,
  odometer INT NULL,
  cost_cents INT UNSIGNED NULL,
  currency VARCHAR(3) DEFAULT 'IDR',
  service_provider VARCHAR(150) NULL,
  receipt_url VARCHAR(255) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  deleted_at TIMESTAMP NULL,

  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
  INDEX idx_vehicle_date (vehicle_id, performed_at),
  INDEX idx_service_type (service_type)
);
```

### Relasi

-   `service_histories.vehicle_id` → `vehicles.id`
-   Relasi: One-to-Many (satu kendaraan memiliki banyak riwayat servis)

---

## Tips Penggunaan

### 1. Best Practices

-   **Konsisten dalam pencatatan**: Catat setiap servis untuk analisis akurat
-   **Upload struk**: Memudahkan verifikasi biaya di kemudian hari
-   **Isi odometer**: Penting untuk tracking interval servis
-   **Detail notes**: Catat sparepart atau hal khusus
-   **Currency**: Gunakan kode ISO 4217 yang valid

### 2. Analisis Biaya

-   Gunakan `period=year` untuk melihat tren tahunan
-   Gunakan `period=month` untuk analisis bulanan detail
-   Monitor `average_cost` untuk deteksi anomali
-   Bandingkan `cost_by_service_type` untuk optimasi

### 3. Performance

-   Pagination otomatis untuk list besar (akan diimplementasi jika perlu)
-   Index database pada `vehicle_id` dan `performed_at`
-   Soft delete untuk audit trail

### 4. Security

-   Setiap user hanya bisa akses servis motor utamanya
-   Token wajib untuk semua operasi
-   File upload dibatasi ukuran dan tipe

---

## Roadmap & Future Features

Sprint berikutnya dapat menambahkan:

1. **Reminder Otomatis**

    - Notifikasi berdasarkan jarak/waktu
    - Prediksi servis berikutnya

2. **Export Data**

    - PDF report
    - Excel export
    - Statistik grafik

3. **Integrasi GPS**

    - Auto-tracking odometer
    - Korelasi dengan trip data

4. **Multi-Currency**

    - Konversi otomatis
    - Rate tracking

5. **Service Recommendation**
    - AI-based prediction
    - Cost optimization

---

## Support & Contact

Untuk pertanyaan atau bug report, silakan hubungi tim development.

**Version:** 1.0.0 (Sprint 5)  
**Last Updated:** January 2, 2026

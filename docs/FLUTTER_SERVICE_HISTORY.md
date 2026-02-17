# Service History API - Flutter Integration Guide

## 📋 Daftar Isi

- [Overview](#overview)
- [Quick Reference](#-quick-reference)
- [Fitur Utama](#fitur-utama)
- [Authentication & Authorization](#authentication--authorization)
- [Endpoints](#endpoints)
- [Data Models](#data-models)
- [Flutter Integration](#flutter-integration)
- [Error Handling](#error-handling)
- [Troubleshooting](#-troubleshooting)
- [Testing](#testing)
- [Best Practices](#-best-practices)

---

## 🎯 Overview

Service History API mengelola riwayat servis kendaraan (motor) dengan fitur lengkap termasuk:

- CRUD riwayat servis
- Upload foto struk/receipt
- Analisis biaya servis
- Filter berdasarkan periode
- Mendukung mode guest (device_id) dan authenticated user

API ini secara otomatis terhubung dengan **primary vehicle** milik user/device.

---

## ⚡ Quick Reference

### Field Names yang Benar ✅

**⚠️ PENTING:** Service History API menggunakan field names yang berbeda dari Service API!

| Field              | Type              | Required | Description                                  |
| ------------------ | ----------------- | -------- | -------------------------------------------- |
| `performed_at`     | date (YYYY-MM-DD) | ✅ Yes   | Tanggal servis dilakukan                     |
| `service_type`     | string (max 120)  | ✅ Yes   | Jenis servis (text bebas)                    |
| `odometer`         | integer           | ❌ No    | Kilometer saat servis                        |
| `cost`             | numeric           | ❌ No    | Biaya dalam rupiah (nilai asli, bukan cents) |
| `currency`         | string (3 chars)  | ❌ No    | Default: IDR                                 |
| `service_provider` | string (max 150)  | ❌ No    | Nama bengkel                                 |
| `notes`            | string (max 1000) | ❌ No    | Catatan tambahan                             |
| `receipt_photo`    | file              | ❌ No    | Foto struk (JPG/PNG/WebP, max 5MB)           |

### Example Request Body (Correct) ✅

```dart
final formData = FormData.fromMap({
  'service_type': 'Ganti Oli',
  'performed_at': '2024-02-15',      // ✅ Ini field yang benar!
  'odometer': 5000,
  'cost': 150000,                    // ✅ Dalam rupiah (bukan cents)
  'service_provider': 'Bengkel Motor Jaya',
  'notes': 'Service rutin bulanan',
});
```

### Common Mistakes ❌

```dart
// ❌ WRONG - Jangan gunakan service_date
final formData = FormData.fromMap({
  'service_type': 'Ganti Oli',
  'service_date': '2024-02-15',     // ❌ Salah! Gunakan performed_at
});

// ❌ WRONG - Cost dalam cents
final formData = FormData.fromMap({
  'cost': 15000000,                  // ❌ Salah! Gunakan nilai asli: 150000
});
```

---

## ✨ Fitur Utama

1. **Auto Primary Vehicle Detection**
    - Semua operasi otomatis menggunakan motor utama user/device
    - Tidak perlu mengirim `vehicle_id` di body

2. **Receipt Photo Upload**
    - Support upload foto struk servis
    - Format: JPEG, JPG, PNG, WebP
    - Max size: 5MB
    - Auto storage management

3. **Cost Analytics**
    - Total biaya per periode
    - Rata-rata biaya per service
    - Grouping by service type
    - Grouping by month (yearly view)
    - Most expensive service

4. **Flexible Period Filter**
    - All time
    - Yearly
    - Monthly

5. **Guest Mode Support**
    - Guest user bisa menggunakan semua fitur dengan `X-Device-ID`

---

## 🔐 Authentication & Authorization

### Guest Mode (Tanpa Login)

```http
X-Device-ID: unique-device-identifier
```

### Authenticated Mode (Dengan Login)

```http
Authorization: Bearer {access_token}
```

**Aturan:**

- Guest mode: wajib kirim `X-Device-ID` header
- Authenticated mode: wajib kirim `Authorization` header
- Jika keduanya ada, prioritas authenticated mode
- Semua endpoint support kedua mode

---

## 🛣️ Endpoints

### Base URL

```
https://yourdomain.com/api
```

### 1. Get All Service Histories

Mengambil semua riwayat servis untuk motor utama.

**Endpoint:** `GET /service-histories`

**Headers:**

```http
Accept: application/json
X-Device-ID: your-device-id  # (Guest Mode)
# atau
Authorization: Bearer {token}  # (Authenticated Mode)
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
                "performed_at": "2024-02-15",
                "odometer": 5000,
                "cost": 150000,
                "currency": "IDR",
                "service_provider": "Bengkel Motor Jaya",
                "receipt_url": "http://yourdomain.com/storage/receipts/1708012345_123_receipt.jpg",
                "notes": "Service rutin bulanan",
                "created_at": "2024-02-15T10:30:00+07:00"
            },
            {
                "id": 2,
                "service_type": "Tune Up",
                "performed_at": "2024-01-10",
                "odometer": 4500,
                "cost": 250000,
                "currency": "IDR",
                "service_provider": "Honda AHASS",
                "receipt_url": null,
                "notes": null,
                "created_at": "2024-01-10T14:20:00+07:00"
            }
        ],
        "total": 2,
        "vehicle": {
            "id": 1,
            "name": "Honda Beat 2020",
            "plate_number": "B 1234 XYZ"
        }
    }
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu."
}
```

---

### 2. Create Service History

Menambahkan riwayat servis baru untuk motor utama.

**Endpoint:** `POST /service-histories`

**Headers:**

```http
Accept: application/json
Content-Type: multipart/form-data
X-Device-ID: your-device-id  # (Guest Mode)
# atau
Authorization: Bearer {token}  # (Authenticated Mode)
```

**Request Body (Form Data):**

```
service_type: "Ganti Oli"                    # Required, max 120 chars
performed_at: "2024-02-15"                   # Required, format: YYYY-MM-DD, not future date
odometer: 5000                               # Optional, integer, min 0
cost: 150000                                 # Optional, numeric (dalam rupiah)
currency: "IDR"                              # Optional, 3 chars, default: IDR
service_provider: "Bengkel Motor Jaya"       # Optional, max 150 chars
notes: "Service rutin bulanan"               # Optional, max 1000 chars
receipt_photo: [file]                        # Optional, image (jpeg,jpg,png,webp), max 5MB
```

**Response Success (201):**

```json
{
    "success": true,
    "message": "Riwayat servis berhasil ditambahkan.",
    "data": {
        "service_history": {
            "id": 3,
            "service_type": "Ganti Oli",
            "performed_at": "2024-02-15",
            "odometer": 5000,
            "cost": 150000,
            "currency": "IDR",
            "service_provider": "Bengkel Motor Jaya",
            "receipt_url": "http://yourdomain.com/storage/receipts/1708012345_123_receipt.jpg",
            "notes": "Service rutin bulanan",
            "created_at": "2024-02-15T10:30:00+07:00"
        }
    }
}
```

**Validation Errors (422):**

```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "service_type": ["Jenis servis wajib diisi."],
        "performed_at": ["Tanggal servis tidak boleh di masa depan."],
        "cost": ["Biaya tidak boleh bernilai negatif."],
        "receipt_photo": ["Ukuran gambar maksimal 5MB."]
    }
}
```

---

### 3. Get Service History Detail

Mengambil detail satu riwayat servis.

**Endpoint:** `GET /service-histories/{id}`

**Headers:**

```http
Accept: application/json
X-Device-ID: your-device-id  # (Guest Mode)
# atau
Authorization: Bearer {token}  # (Authenticated Mode)
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
            "performed_at": "2024-02-15",
            "odometer": 5000,
            "cost": 150000,
            "currency": "IDR",
            "service_provider": "Bengkel Motor Jaya",
            "receipt_url": "http://yourdomain.com/storage/receipts/1708012345_123_receipt.jpg",
            "notes": "Service rutin bulanan",
            "created_at": "2024-02-15T10:30:00+07:00",
            "updated_at": "2024-02-15T10:30:00+07:00"
        }
    }
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Riwayat servis tidak ditemukan."
}
```

---

### 4. Update Service History

Memperbarui riwayat servis yang sudah ada.

**Endpoint:** `PUT /service-histories/{id}`

**Headers:**

```http
Accept: application/json
Content-Type: multipart/form-data
X-Device-ID: your-device-id  # (Guest Mode)
# atau
Authorization: Bearer {token}  # (Authenticated Mode)
```

**Request Body (Form Data):**

```
_method: "PUT"                               # Required untuk multipart/form-data
service_type: "Ganti Oli + Filter"           # Optional, max 120 chars
performed_at: "2024-02-15"                   # Optional, format: YYYY-MM-DD
odometer: 5200                               # Optional, integer, min 0
cost: 175000                                 # Optional, numeric
currency: "IDR"                              # Optional, 3 chars
service_provider: "Bengkel Motor Jaya"       # Optional, max 150 chars
notes: "Update: Ganti filter juga"           # Optional, max 1000 chars
receipt_photo: [file]                        # Optional, akan replace foto lama
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Riwayat servis berhasil diperbarui.",
    "data": {
        "service_history": {
            "id": 1,
            "service_type": "Ganti Oli + Filter",
            "performed_at": "2024-02-15",
            "odometer": 5200,
            "cost": 175000,
            "currency": "IDR",
            "service_provider": "Bengkel Motor Jaya",
            "receipt_url": "http://yourdomain.com/storage/receipts/1708012399_123_new_receipt.jpg",
            "notes": "Update: Ganti filter juga",
            "updated_at": "2024-02-15T11:45:00+07:00"
        }
    }
}
```

**Notes:**

- Semua field bersifat optional (partial update)
- Upload receipt_photo baru akan menghapus foto lama
- Gunakan `_method: PUT` untuk multipart/form-data

---

### 5. Delete Service History

Menghapus riwayat servis (soft delete).

**Endpoint:** `DELETE /service-histories/{id}`

**Headers:**

```http
Accept: application/json
X-Device-ID: your-device-id  # (Guest Mode)
# atau
Authorization: Bearer {token}  # (Authenticated Mode)
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Riwayat servis berhasil dihapus."
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Riwayat servis tidak ditemukan."
}
```

**Notes:**

- Soft delete (data tidak benar-benar terhapus dari database)
- Foto receipt akan dihapus dari storage

---

### 6. Get Cost Summary ⭐ ANALYTICS

Mendapatkan analisis biaya servis dengan berbagai filter periode.

**Endpoint:** `GET /service-histories/cost-summary`

**Headers:**

```http
Accept: application/json
X-Device-ID: your-device-id  # (Guest Mode)
# atau
Authorization: Bearer {token}  # (Authenticated Mode)
```

**Query Parameters:**

```
period: "all" | "year" | "month"    # Optional, default: "all"
year: 2024                           # Optional, default: current year (jika period=year/month)
month: 2                             # Optional, default: current month (jika period=month)
```

**Example Requests:**

```http
# All time summary
GET /service-histories/cost-summary

# Yearly summary (2024)
GET /service-histories/cost-summary?period=year&year=2024

# Monthly summary (February 2024)
GET /service-histories/cost-summary?period=month&year=2024&month=2
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
            "total_cost": 2450000,
            "total_services": 8,
            "average_cost": 306250,
            "currency": "IDR",
            "last_service_date": "2024-02-15"
        },
        "cost_by_service_type": [
            {
                "service_type": "Ganti Oli",
                "total_cost": 900000,
                "count": 6,
                "average_cost": 150000
            },
            {
                "service_type": "Tune Up",
                "total_cost": 500000,
                "count": 2,
                "average_cost": 250000
            },
            {
                "service_type": "Ganti Ban",
                "total_cost": 1050000,
                "count": 1,
                "average_cost": 1050000
            }
        ],
        "cost_by_month": [],
        "most_expensive_service": {
            "id": 5,
            "service_type": "Ganti Ban",
            "cost": 1050000,
            "performed_at": "2024-01-20"
        },
        "vehicle": {
            "id": 1,
            "name": "Honda Beat 2020",
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
            "year": 2024,
            "month": null,
            "total_cost": 2450000,
            "total_services": 8,
            "average_cost": 306250,
            "currency": "IDR",
            "last_service_date": "2024-02-15"
        },
        "cost_by_service_type": [
            {
                "service_type": "Ganti Oli",
                "total_cost": 900000,
                "count": 6,
                "average_cost": 150000
            },
            {
                "service_type": "Tune Up",
                "total_cost": 500000,
                "count": 2,
                "average_cost": 250000
            }
        ],
        "cost_by_month": [
            {
                "month": "2024-01",
                "total_cost": 1550000,
                "count": 4
            },
            {
                "month": "2024-02",
                "total_cost": 900000,
                "count": 4
            }
        ],
        "most_expensive_service": {
            "id": 5,
            "service_type": "Ganti Ban",
            "cost": 1050000,
            "performed_at": "2024-01-20"
        },
        "vehicle": {
            "id": 1,
            "name": "Honda Beat 2020",
            "plate_number": "B 1234 XYZ"
        }
    }
}
```

**Response Success (200) - Monthly:**

```json
{
    "success": true,
    "message": "Ringkasan biaya servis berhasil diambil.",
    "data": {
        "summary": {
            "period": "month",
            "year": 2024,
            "month": 2,
            "total_cost": 900000,
            "total_services": 4,
            "average_cost": 225000,
            "currency": "IDR",
            "last_service_date": "2024-02-15"
        },
        "cost_by_service_type": [
            {
                "service_type": "Ganti Oli",
                "total_cost": 600000,
                "count": 4,
                "average_cost": 150000
            }
        ],
        "cost_by_month": [],
        "most_expensive_service": {
            "id": 8,
            "service_type": "Ganti Oli",
            "cost": 150000,
            "performed_at": "2024-02-15"
        },
        "vehicle": {
            "id": 1,
            "name": "Honda Beat 2020",
            "plate_number": "B 1234 XYZ"
        }
    }
}
```

---

## 📊 Data Models

### ServiceHistory Model

```dart
class ServiceHistory {
  final int id;
  final String serviceType;
  final DateTime performedAt;
  final int? odometer;
  final double? cost;
  final String currency;
  final String? serviceProvider;
  final String? receiptUrl;
  final String? notes;
  final DateTime createdAt;
  final DateTime? updatedAt;

  ServiceHistory({
    required this.id,
    required this.serviceType,
    required this.performedAt,
    this.odometer,
    this.cost,
    required this.currency,
    this.serviceProvider,
    this.receiptUrl,
    this.notes,
    required this.createdAt,
    this.updatedAt,
  });

  factory ServiceHistory.fromJson(Map<String, dynamic> json) {
    try {
      // Validate required fields
      if (json['id'] == null) throw FormatException('Missing required field: id');
      if (json['service_type'] == null) throw FormatException('Missing required field: service_type');
      if (json['performed_at'] == null) throw FormatException('Missing required field: performed_at');
      if (json['created_at'] == null) throw FormatException('Missing required field: created_at');

      return ServiceHistory(
        id: json['id'] as int,
        serviceType: json['service_type'] as String,
        performedAt: DateTime.parse(json['performed_at'] as String),
        odometer: json['odometer'] as int?,
        cost: json['cost'] != null ? (json['cost'] as num).toDouble() : null,
        currency: (json['currency'] as String?) ?? 'IDR',
        serviceProvider: json['service_provider'] as String?,
        receiptUrl: json['receipt_url'] as String?,
        notes: json['notes'] as String?,
        createdAt: DateTime.parse(json['created_at'] as String),
        updatedAt: json['updated_at'] != null
            ? DateTime.parse(json['updated_at'] as String)
            : null,
      );
    } catch (e) {
      throw FormatException('Failed to parse ServiceHistory: $e');
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'service_type': serviceType,
      'performed_at': performedAt.toIso8601String().split('T')[0], // YYYY-MM-DD
      'odometer': odometer,
      'cost': cost,
      'currency': currency,
      'service_provider': serviceProvider,
      'receipt_url': receiptUrl,
      'notes': notes,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }

  // Helper method untuk format tanggal
  String get formattedDate {
    return '${performedAt.day.toString().padLeft(2, '0')}/${performedAt.month.toString().padLeft(2, '0')}/${performedAt.year}';
  }

  // Helper method untuk format biaya
  String get formattedCost {
    if (cost == null) return '-';
    return 'Rp ${cost!.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]}.')}';
  }
}
```

### CostSummary Model

```dart
class CostSummary {
  final String period;
  final int? year;
  final int? month;
  final double totalCost;
  final int totalServices;
  final double averageCost;
  final String currency;
  final DateTime? lastServiceDate;

  CostSummary({
    required this.period,
    this.year,
    this.month,
    required this.totalCost,
    required this.totalServices,
    required this.averageCost,
    required this.currency,
    this.lastServiceDate,
  });

  factory CostSummary.fromJson(Map<String, dynamic> json) {
    return CostSummary(
      period: json['period'],
      year: json['year'],
      month: json['month'],
      totalCost: (json['total_cost'] ?? 0).toDouble(),
      totalServices: json['total_services'] ?? 0,
      averageCost: (json['average_cost'] ?? 0).toDouble(),
      currency: json['currency'] ?? 'IDR',
      lastServiceDate: json['last_service_date'] != null
          ? DateTime.parse(json['last_service_date'])
          : null,
    );
  }
}

class CostByServiceType {
  final String serviceType;
  final double totalCost;
  final int count;
  final double averageCost;

  CostByServiceType({
    required this.serviceType,
    required this.totalCost,
    required this.count,
    required this.averageCost,
  });

  factory CostByServiceType.fromJson(Map<String, dynamic> json) {
    return CostByServiceType(
      serviceType: json['service_type'],
      totalCost: (json['total_cost'] ?? 0).toDouble(),
      count: json['count'] ?? 0,
      averageCost: (json['average_cost'] ?? 0).toDouble(),
    );
  }
}

class CostByMonth {
  final String month; // Format: YYYY-MM
  final double totalCost;
  final int count;

  CostByMonth({
    required this.month,
    required this.totalCost,
    required this.count,
  });

  factory CostByMonth.fromJson(Map<String, dynamic> json) {
    return CostByMonth(
      month: json['month'],
      totalCost: (json['total_cost'] ?? 0).toDouble(),
      count: json['count'] ?? 0,
    );
  }
}
```

---

## 🔧 Flutter Integration

### 1. API Service Setup

```dart
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ServiceHistoryApiService {
  final Dio _dio;
  final String baseUrl;

  ServiceHistoryApiService({
    required this.baseUrl,
    Dio? dio,
  }) : _dio = dio ?? Dio() {
    _dio.options.baseUrl = baseUrl;
    _dio.options.headers['Accept'] = 'application/json';

    // Interceptor untuk auto add auth headers
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final prefs = await SharedPreferences.getInstance();
          final token = prefs.getString('access_token');
          final deviceId = prefs.getString('device_id');

          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          } else if (deviceId != null && deviceId.isNotEmpty) {
            options.headers['X-Device-ID'] = deviceId;
          }

          return handler.next(options);
        },
        onError: (error, handler) async {
          // Handle 401 Unauthorized
          if (error.response?.statusCode == 401) {
            // Redirect to login or clear session
          }
          return handler.next(error);
        },
      ),
    );
  }
```

### 2. Get All Service Histories

```dart
  Future<List<ServiceHistory>> getAllServiceHistories() async {
    try {
      final response = await _dio.get('/service-histories');

      if (response.data['success'] == true) {
        final List<dynamic> histories = response.data['data']['service_histories'];
        return histories.map((json) => ServiceHistory.fromJson(json)).toList();
      }

      throw Exception(response.data['message'] ?? 'Failed to load service histories');
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        throw Exception('Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu.');
      }
      throw Exception(e.response?.data['message'] ?? 'Network error');
    }
  }
```

### 3. Create Service History

```dart
  Future<ServiceHistory> createServiceHistory({
    required String serviceType,
    required DateTime performedAt,
    int? odometer,
    double? cost,
    String currency = 'IDR',
    String? serviceProvider,
    String? notes,
    String? receiptPhotoPath, // Local file path
  }) async {
    try {
      // Validate input
      if (serviceType.isEmpty) {
        throw Exception('Service type tidak boleh kosong');
      }
      if (performedAt.isAfter(DateTime.now())) {
        throw Exception('Tanggal servis tidak boleh di masa depan');
      }

      final formData = FormData.fromMap({
        'service_type': serviceType,
        'performed_at': performedAt.toIso8601String().split('T')[0], // YYYY-MM-DD
        if (odometer != null) 'odometer': odometer,
        if (cost != null) 'cost': cost,
        'currency': currency,
        if (serviceProvider != null && serviceProvider.isNotEmpty)
          'service_provider': serviceProvider,
        if (notes != null && notes.isNotEmpty) 'notes': notes,
        if (receiptPhotoPath != null)
          'receipt_photo': await MultipartFile.fromFile(
            receiptPhotoPath,
            filename: receiptPhotoPath.split('/').last,
          ),
      });

      final response = await _dio.post(
        '/service-histories',
        data: formData,
      );

      if (response.data['success'] == true) {
        final serviceData = response.data['data']['service_history'];
        if (serviceData == null) {
          throw Exception('Invalid response: service_history data is null');
        }
        return ServiceHistory.fromJson(serviceData);
      }

      throw Exception(response.data['message'] ?? 'Failed to create service history');
    } on DioException catch (e) {
      if (e.response?.statusCode == 422) {
        // Validation errors
        final errors = e.response?.data['errors'] as Map<String, dynamic>?;
        if (errors != null) {
          final errorMessages = errors.values
              .expand((messages) => messages as List)
              .join('\n');
          throw Exception(errorMessages);
        }
        throw Exception('Validation error');
      }
      if (e.response?.statusCode == 404) {
        throw Exception('Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu.');
      }
      throw Exception(e.response?.data['message'] ?? 'Network error: ${e.message}');
    } on FormatException catch (e) {
      throw Exception('Data format error: ${e.message}');
    } catch (e) {
      throw Exception('Unexpected error: $e');
    }
  }
```

### 4. Update Service History

```dart
  Future<ServiceHistory> updateServiceHistory({
    required int id,
    String? serviceType,
    DateTime? performedAt,
    int? odometer,
    double? cost,
    String? currency,
    String? serviceProvider,
    String? notes,
    String? receiptPhotoPath,
  }) async {
    try {
      final formData = FormData.fromMap({
        '_method': 'PUT', // Required for multipart/form-data
        if (serviceType != null) 'service_type': serviceType,
        if (performedAt != null)
          'performed_at': performedAt.toIso8601String().split('T')[0],
        if (odometer != null) 'odometer': odometer,
        if (cost != null) 'cost': cost,
        if (currency != null) 'currency': currency,
        if (serviceProvider != null) 'service_provider': serviceProvider,
        if (notes != null) 'notes': notes,
        if (receiptPhotoPath != null)
          'receipt_photo': await MultipartFile.fromFile(
            receiptPhotoPath,
            filename: receiptPhotoPath.split('/').last,
          ),
      });

      final response = await _dio.post(
        '/service-histories/$id',
        data: formData,
      );

      if (response.data['success'] == true) {
        return ServiceHistory.fromJson(response.data['data']['service_history']);
      }

      throw Exception(response.data['message'] ?? 'Failed to update service history');
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        throw Exception('Riwayat servis tidak ditemukan');
      }
      throw Exception(e.response?.data['message'] ?? 'Network error');
    }
  }
```

### 5. Delete Service History

```dart
  Future<void> deleteServiceHistory(int id) async {
    try {
      final response = await _dio.delete('/service-histories/$id');

      if (response.data['success'] != true) {
        throw Exception(response.data['message'] ?? 'Failed to delete service history');
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        throw Exception('Riwayat servis tidak ditemukan');
      }
      throw Exception(e.response?.data['message'] ?? 'Network error');
    }
  }
```

### 6. Get Cost Summary

```dart
  Future<Map<String, dynamic>> getCostSummary({
    String period = 'all', // 'all', 'year', 'month'
    int? year,
    int? month,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'period': period,
        if (year != null) 'year': year,
        if (month != null) 'month': month,
      };

      final response = await _dio.get(
        '/service-histories/cost-summary',
        queryParameters: queryParams,
      );

      if (response.data['success'] == true) {
        final data = response.data['data'];

        return {
          'summary': CostSummary.fromJson(data['summary']),
          'cost_by_service_type': (data['cost_by_service_type'] as List)
              .map((json) => CostByServiceType.fromJson(json))
              .toList(),
          'cost_by_month': (data['cost_by_month'] as List)
              .map((json) => CostByMonth.fromJson(json))
              .toList(),
          'most_expensive_service': data['most_expensive_service'] != null
              ? ServiceHistory.fromJson(data['most_expensive_service'])
              : null,
          'vehicle': data['vehicle'],
        };
      }

      throw Exception(response.data['message'] ?? 'Failed to load cost summary');
    } on DioException catch (e) {
      throw Exception(e.response?.data['message'] ?? 'Network error');
    }
  }
}
```

---

## 🎨 Flutter UI Examples

### 1. Service History List Screen

````dart
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class ServiceHistoryListScreen extends StatefulWidget {
  @override
  _ServiceHistoryListScreenState createState() => _ServiceHistoryListScreenState();
}

class _ServiceHistoryListScreenState extends State<ServiceHistoryListScreen> {
  final ServiceHistoryApiService _apiService = ServiceHistoryApiService(
    baseUrl: 'https://yourdomain.com/api',
  );

  List<ServiceHistory> _histories = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadServiceHistories();
  }

  Future<void> _loadServiceHistories() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final histories = await _apiService.getAllServiceHistories();
      setState(() {
        _histories = histories;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _deleteServiceHistory(int id) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Konfirmasi Hapus'),
        content: Text('Yakin ingin menghapus riwayat servis ini?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text('Batal'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text('Hapus'),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
          ),
        ],
      ),
    );

    if (confirm == true) {
      try {
        await _apiService.deleteServiceHistory(id);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Riwayat servis berhasil dihapus')),
        );
        _loadServiceHistories(); // Reload list
      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal menghapus: ${e.toString()}')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Riwayat Servis'),
        actions: [
          IconButton(
            icon: Icon(Icons.add),
            onPressed: () async {
              final result = await Navigator.pushNamed(
                context,
                '/service-history/create',
              );
              if (result == true) {
                _loadServiceHistories();
              }
            },
          ),
        ],
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(_error!),
                      SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadServiceHistories,
                        child: Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : _histories.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.build, size: 64, color: Colors.grey),
                          SizedBox(height: 16),
                          Text('Belum ada riwayat servis'),
                          SizedBox(height: 8),
                          ElevatedButton(
                            onPressed: () async {
                              final result = await Navigator.pushNamed(
                                context,
                                '/service-history/create',
                              );
                              if (result == true) {
                                _loadServiceHistories();
                              }
                            },
                            child: Text('Tambah Servis'),
                          ),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _loadServiceHistories,
                      child: ListView.builder(
                        itemCount: _histories.length,
                        itemBuilder: (context, index) {
                          final history = _histories[index];
                          return ServiceHistoryCard(
                            history: history,
                            onTap: () async {
                              final result = await Navigator.pushNamed(
                                context,
                                '/service-history/detail',
                                arguments: history.id,
                              );
                              if (result == true) {
                                _loadServiceHistories();
                              }
                            },
                            onDelete: () => _deleteServiceHistory(history.id),
                          );
                        },
                      ),
                    ),
    );
  }
}

class ServiceHistoryCard extends StatelessWidget {
  final ServiceHistory history;
  final VoidCallback onTap;
  final VoidCallback onDelete;

  const ServiceHistoryCard({
    Key? key,
    required this.history,
    required this.onTap,
    required this.onDelete,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final dateFormat = DateFormat('dd MMM yyyy');
    final currencyFormat = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );

    return Card(

### 4. Service History Detail Screen (Flutter)

Dokumentasi ini menunjukkan cara mengambil dan menampilkan detail satu riwayat servis (`GET /service-histories/{id}`) di Flutter.

#### 1) Endpoint

```http
GET /service-histories/{id}
Headers:
  Accept: application/json
  X-Device-ID: {device_id}    # (Guest mode) or
  Authorization: Bearer {token}
````

Response `data.service_history` mengandung field yang sama seperti model `ServiceHistory`.

#### 2) Fetch detail (Dio)

```dart
Future<ServiceHistory> getServiceHistoryDetail(int id) async {
  final response = await _dio.get('/service-histories/$id');

  if (response.data['success'] == true) {
    final serviceData = response.data['data']['service_history'];
    if (serviceData == null) throw Exception('service_history is null');
    return ServiceHistory.fromJson(serviceData);
  }

  throw Exception(response.data['message'] ?? 'Failed to load detail');
}
```

> Catatan: field tanggal yang benar untuk Service History adalah `performed_at` (format `YYYY-MM-DD`).

#### 3) Detail Screen Widget (contoh)

```dart
class ServiceHistoryDetailScreen extends StatefulWidget {
  final int id;
  const ServiceHistoryDetailScreen({required this.id, Key? key}) : super(key: key);

  @override
  _ServiceHistoryDetailScreenState createState() => _ServiceHistoryDetailScreenState();
}

class _ServiceHistoryDetailScreenState extends State<ServiceHistoryDetailScreen> {
  final ServiceHistoryApiService _api = ServiceHistoryApiService(baseUrl: 'https://yourdomain.com/api');
  ServiceHistory? _history;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final h = await _api.getServiceHistoryDetail(widget.id);
      setState(() { _history = h; });
    } catch (e) {
      setState(() { _error = e.toString(); });
    } finally {
      setState(() { _loading = false; });
    }
  }

  Future<void> _confirmDelete() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: Text('Hapus Riwayat'),
        content: Text('Yakin ingin menghapus riwayat servis ini?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: Text('Hapus', style: TextStyle(color: Colors.red))),
        ],
      ),
    );

    if (ok == true) {
      try {
        await _api.deleteServiceHistory(widget.id);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Riwayat berhasil dihapus')));
        Navigator.pop(context, true);
      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal menghapus: $e')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return Scaffold(appBar: AppBar(title: Text('Detail Servis')), body: Center(child: CircularProgressIndicator()));
    if (_error != null) return Scaffold(appBar: AppBar(title: Text('Detail Servis')), body: Center(child: Text(_error!)));
    if (_history == null) return Scaffold(appBar: AppBar(title: Text('Detail Servis')), body: Center(child: Text('Data tidak tersedia')));

    final h = _history!;
    final currencyFormat = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

    return Scaffold(
      appBar: AppBar(
        title: Text('Detail Servis'),
        actions: [
          IconButton(icon: Icon(Icons.edit), onPressed: () async {
            final res = await Navigator.pushNamed(context, '/service-history/edit', arguments: h.id);
            if (res == true) _load();
          }),
          IconButton(icon: Icon(Icons.delete), onPressed: _confirmDelete),
        ],
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(h.serviceType, style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
          SizedBox(height: 8),
          Text('Tanggal: ${h.performedAt.toIso8601String().split('T')[0]}'),
          if (h.odometer != null) Text('Kilometer: ${h.odometer} km'),
          if (h.cost != null) Text('Biaya: ${currencyFormat.format(h.cost)}', style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold)),
          if (h.serviceProvider != null) ...[SizedBox(height: 8), Text('Bengkel: ${h.serviceProvider}')],
          SizedBox(height: 12),
          Text('Catatan', style: TextStyle(fontWeight: FontWeight.bold)),
          SizedBox(height: 4),
          Text(h.notes ?? '-'),
          SizedBox(height: 12),
          if (h.receiptUrl != null) ...[
            Text('Struk', style: TextStyle(fontWeight: FontWeight.bold)),
            SizedBox(height: 8),
            GestureDetector(
              onTap: () => Navigator.pushNamed(context, '/image-view', arguments: h.receiptUrl),
              child: Hero(tag: h.receiptUrl!, child: Image.network(h.receiptUrl!, height: 250, width: double.infinity, fit: BoxFit.cover)),
            ),
          ],
        ]),
      ),
    );
  }
}

#### 4) Notes & Best Practices

- Selalu cek `service_history` null sebelum mem-parsing response.
- `performed_at` dikirim/diterima sebagai string `YYYY-MM-DD` — gunakan `DateTime.parse()`.
- Tampilkan placeholder bila `receipt_url` null.
- Setelah update/hapus, lakukan reload data di parent screen.

### 2. Create Service History Screen
        onTap: onTap,
        child: Padding(
          padding: EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      history.serviceType,
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                    ),
                  ),
                  IconButton(
                    icon: Icon(Icons.delete, color: Colors.red),
                    onPressed: onDelete,
                    padding: EdgeInsets.zero,
                    constraints: BoxConstraints(),
                  ),
                ],
              ),
              SizedBox(height: 8),
              Row(
                children: [
                  Icon(Icons.calendar_today, size: 16, color: Colors.grey),
                  SizedBox(width: 8),
                  Text(dateFormat.format(history.performedAt)),
                ],
              ),
              if (history.odometer != null) ...[
                SizedBox(height: 4),
                Row(
                  children: [
                    Icon(Icons.speed, size: 16, color: Colors.grey),
                    SizedBox(width: 8),
                    Text('${history.odometer} km'),
                  ],
                ),
              ],
              if (history.cost != null) ...[
                SizedBox(height: 4),
                Row(
                  children: [
                    Icon(Icons.attach_money, size: 16, color: Colors.grey),
                    SizedBox(width: 8),
                    Text(
                      currencyFormat.format(history.cost),
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: Colors.green,
                      ),
                    ),
                  ],
                ),
              ],
              if (history.serviceProvider != null) ...[
                SizedBox(height: 4),
                Row(
                  children: [
                    Icon(Icons.store, size: 16, color: Colors.grey),
                    SizedBox(width: 8),
                    Text(history.serviceProvider!),
                  ],
                ),
              ],
              if (history.receiptUrl != null) ...[
                SizedBox(height: 8),
                Row(
                  children: [
                    Icon(Icons.receipt, size: 16, color: Colors.blue),
                    SizedBox(width: 8),
                    Text(
                      'Struk tersedia',
                      style: TextStyle(color: Colors.blue),
                    ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
```

### 2. Create Service History Screen

```dart
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';

class CreateServiceHistoryScreen extends StatefulWidget {
  @override
  _CreateServiceHistoryScreenState createState() =>
      _CreateServiceHistoryScreenState();
}

class _CreateServiceHistoryScreenState
    extends State<CreateServiceHistoryScreen> {
  final _formKey = GlobalKey<FormState>();
  final ServiceHistoryApiService _apiService = ServiceHistoryApiService(
    baseUrl: 'https://yourdomain.com/api',
  );

  // Form controllers
  final _serviceTypeController = TextEditingController();
  final _odometerController = TextEditingController();
  final _costController = TextEditingController();
  final _serviceProviderController = TextEditingController();
  final _notesController = TextEditingController();

  DateTime _selectedDate = DateTime.now();
  File? _receiptPhoto;
  bool _isSubmitting = false;

  Future<void> _selectDate(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2000),
      lastDate: DateTime.now(),
    );
    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
      });
    }
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final pickedFile = await showModalBottomSheet<XFile?>(
      context: context,
      builder: (context) => SafeArea(
        child: Wrap(
          children: [
            ListTile(
              leading: Icon(Icons.photo_camera),
              title: Text('Ambil Foto'),
              onTap: () async {
                Navigator.pop(
                  context,
                  await picker.pickImage(source: ImageSource.camera),
                );
              },
            ),
            ListTile(
              leading: Icon(Icons.photo_library),
              title: Text('Pilih dari Galeri'),
              onTap: () async {
                Navigator.pop(
                  context,
                  await picker.pickImage(source: ImageSource.gallery),
                );
              },
            ),
          ],
        ),
      ),
    );

    if (pickedFile != null) {
      setState(() {
        _receiptPhoto = File(pickedFile.path);
      });
    }
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      await _apiService.createServiceHistory(
        serviceType: _serviceTypeController.text,
        performedAt: _selectedDate,
        odometer: _odometerController.text.isNotEmpty
            ? int.parse(_odometerController.text)
            : null,
        cost: _costController.text.isNotEmpty
            ? double.parse(_costController.text)
            : null,
        serviceProvider: _serviceProviderController.text.isNotEmpty
            ? _serviceProviderController.text
            : null,
        notes: _notesController.text.isNotEmpty
            ? _notesController.text
            : null,
        receiptPhotoPath: _receiptPhoto?.path,
      );

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Riwayat servis berhasil ditambahkan')),
      );

      Navigator.pop(context, true); // Return true to indicate success
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal menambahkan: ${e.toString()}')),
      );
    } finally {
      setState(() {
        _isSubmitting = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Tambah Riwayat Servis'),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _serviceTypeController,
              decoration: InputDecoration(
                labelText: 'Jenis Servis *',
                hintText: 'Contoh: Ganti Oli, Tune Up',
                border: OutlineInputBorder(),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Jenis servis wajib diisi';
                }
                if (value.length > 120) {
                  return 'Maksimal 120 karakter';
                }
                return null;
              },
            ),
            SizedBox(height: 16),
            InkWell(
              onTap: () => _selectDate(context),
              child: InputDecorator(
                decoration: InputDecoration(
                  labelText: 'Tanggal Servis *',
                  border: OutlineInputBorder(),
                  suffixIcon: Icon(Icons.calendar_today),
                ),
                child: Text(
                  DateFormat('dd MMMM yyyy').format(_selectedDate),
                ),
              ),
            ),
            SizedBox(height: 16),
            TextFormField(
              controller: _odometerController,
              decoration: InputDecoration(
                labelText: 'Kilometer (Opsional)',
                hintText: 'Contoh: 5000',
                border: OutlineInputBorder(),
                suffixText: 'km',
              ),
              keyboardType: TextInputType.number,
              validator: (value) {
                if (value != null && value.isNotEmpty) {
                  final odometer = int.tryParse(value);
                  if (odometer == null || odometer < 0) {
                    return 'Kilometer harus berupa angka positif';
                  }
                }
                return null;
              },
            ),
            SizedBox(height: 16),
            TextFormField(
              controller: _costController,
              decoration: InputDecoration(
                labelText: 'Biaya (Opsional)',
                hintText: 'Contoh: 150000',
                border: OutlineInputBorder(),
                prefixText: 'Rp ',
              ),
              keyboardType: TextInputType.number,
              validator: (value) {
                if (value != null && value.isNotEmpty) {
                  final cost = double.tryParse(value);
                  if (cost == null || cost < 0) {
                    return 'Biaya harus berupa angka positif';
                  }
                }
                return null;
              },
            ),
            SizedBox(height: 16),
            TextFormField(
              controller: _serviceProviderController,
              decoration: InputDecoration(
                labelText: 'Nama Bengkel (Opsional)',
                hintText: 'Contoh: Bengkel Motor Jaya',
                border: OutlineInputBorder(),
              ),
              validator: (value) {
                if (value != null && value.length > 150) {
                  return 'Maksimal 150 karakter';
                }
                return null;
              },
            ),
            SizedBox(height: 16),
            TextFormField(
              controller: _notesController,
              decoration: InputDecoration(
                labelText: 'Catatan (Opsional)',
                hintText: 'Tambahkan catatan...',
                border: OutlineInputBorder(),
              ),
              maxLines: 3,
              maxLength: 1000,
              validator: (value) {
                if (value != null && value.length > 1000) {
                  return 'Maksimal 1000 karakter';
                }
                return null;
              },
            ),
            SizedBox(height: 16),
            Card(
              child: Padding(
                padding: EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Foto Struk (Opsional)',
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    SizedBox(height: 8),
                    if (_receiptPhoto != null) ...[
                      Stack(
                        children: [
                          Image.file(
                            _receiptPhoto!,
                            height: 200,
                            width: double.infinity,
                            fit: BoxFit.cover,
                          ),
                          Positioned(
                            top: 8,
                            right: 8,
                            child: IconButton(
                              icon: Icon(Icons.close, color: Colors.white),
                              onPressed: () {
                                setState(() {
                                  _receiptPhoto = null;
                                });
                              },
                              style: IconButton.styleFrom(
                                backgroundColor: Colors.black54,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ] else ...[
                      OutlinedButton.icon(
                        onPressed: _pickImage,
                        icon: Icon(Icons.photo_camera),
                        label: Text('Tambah Foto Struk'),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'Format: JPEG, JPG, PNG, WebP (Max 5MB)',
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ],
                  ],
                ),
              ),
            ),
            SizedBox(height: 24),
            ElevatedButton(
              onPressed: _isSubmitting ? null : _submitForm,
              child: _isSubmitting
                  ? SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : Text('Simpan'),
              style: ElevatedButton.styleFrom(
                padding: EdgeInsets.symmetric(vertical: 16),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _serviceTypeController.dispose();
    _odometerController.dispose();
    _costController.dispose();
    _serviceProviderController.dispose();
    _notesController.dispose();
    super.dispose();
  }
}
```

### 3. Cost Summary Screen

```dart
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:fl_chart/fl_chart.dart';

class CostSummaryScreen extends StatefulWidget {
  @override
  _CostSummaryScreenState createState() => _CostSummaryScreenState();
}

class _CostSummaryScreenState extends State<CostSummaryScreen> {
  final ServiceHistoryApiService _apiService = ServiceHistoryApiService(
    baseUrl: 'https://yourdomain.com/api',
  );

  String _selectedPeriod = 'all';
  int _selectedYear = DateTime.now().year;
  int _selectedMonth = DateTime.now().month;

  Map<String, dynamic>? _summaryData;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadCostSummary();
  }

  Future<void> _loadCostSummary() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await _apiService.getCostSummary(
        period: _selectedPeriod,
        year: _selectedPeriod != 'all' ? _selectedYear : null,
        month: _selectedPeriod == 'month' ? _selectedMonth : null,
      );

      setState(() {
        _summaryData = data;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Widget _buildPeriodSelector() {
    return Card(
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Periode',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            SizedBox(height: 8),
            SegmentedButton<String>(
              segments: [
                ButtonSegment(value: 'all', label: Text('Semua')),
                ButtonSegment(value: 'year', label: Text('Tahun')),
                ButtonSegment(value: 'month', label: Text('Bulan')),
              ],
              selected: {_selectedPeriod},
              onSelectionChanged: (Set<String> newSelection) {
                setState(() {
                  _selectedPeriod = newSelection.first;
                });
                _loadCostSummary();
              },
            ),
            if (_selectedPeriod == 'year' || _selectedPeriod == 'month') ...[
              SizedBox(height: 16),
              DropdownButtonFormField<int>(
                value: _selectedYear,
                decoration: InputDecoration(
                  labelText: 'Tahun',
                  border: OutlineInputBorder(),
                ),
                items: List.generate(5, (index) {
                  final year = DateTime.now().year - index;
                  return DropdownMenuItem(
                    value: year,
                    child: Text(year.toString()),
                  );
                }),
                onChanged: (value) {
                  if (value != null) {
                    setState(() {
                      _selectedYear = value;
                    });
                    _loadCostSummary();
                  }
                },
              ),
            ],
            if (_selectedPeriod == 'month') ...[
              SizedBox(height: 16),
              DropdownButtonFormField<int>(
                value: _selectedMonth,
                decoration: InputDecoration(
                  labelText: 'Bulan',
                  border: OutlineInputBorder(),
                ),
                items: List.generate(12, (index) {
                  final month = index + 1;
                  return DropdownMenuItem(
                    value: month,
                    child: Text(DateFormat.MMMM('id_ID')
                        .format(DateTime(2024, month))),
                  );
                }),
                onChanged: (value) {
                  if (value != null) {
                    setState(() {
                      _selectedMonth = value;
                    });
                    _loadCostSummary();
                  }
                },
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryCard() {
    final summary = _summaryData!['summary'] as CostSummary;
    final currencyFormat = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );

    return Card(
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Ringkasan Biaya',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Total Biaya',
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                    SizedBox(height: 4),
                    Text(
                      currencyFormat.format(summary.totalCost),
                      style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: Colors.green,
                          ),
                    ),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      'Total Servis',
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                    SizedBox(height: 4),
                    Text(
                      '${summary.totalServices}x',
                      style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                    ),
                  ],
                ),
              ],
            ),
            Divider(height: 32),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Rata-rata per Servis'),
                Text(
                  currencyFormat.format(summary.averageCost),
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
              ],
            ),
            if (summary.lastServiceDate != null) ...[
              SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Servis Terakhir'),
                  Text(
                    DateFormat('dd MMM yyyy').format(summary.lastServiceDate!),
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildCostByServiceType() {
    final costByType = _summaryData!['cost_by_service_type'] as List<CostByServiceType>;

    if (costByType.isEmpty) {
      return SizedBox.shrink();
    }

    final currencyFormat = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );

    return Card(
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Biaya per Jenis Servis',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            SizedBox(height: 16),
            ...costByType.map((item) => Padding(
                  padding: EdgeInsets.only(bottom: 12),
                  child: Row(
                    children: [
                      Expanded(
                        flex: 2,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              item.serviceType,
                              style: TextStyle(fontWeight: FontWeight.bold),
                            ),
                            Text(
                              '${item.count}x • Avg: ${currencyFormat.format(item.averageCost)}',
                              style: Theme.of(context).textTheme.bodySmall,
                            ),
                          ],
                        ),
                      ),
                      Expanded(
                        flex: 1,
                        child: Text(
                          currencyFormat.format(item.totalCost),
                          textAlign: TextAlign.right,
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.green,
                          ),
                        ),
                      ),
                    ],
                  ),
                )),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Analisis Biaya Servis'),
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(_error!),
                      SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadCostSummary,
                        child: Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadCostSummary,
                  child: ListView(
                    padding: EdgeInsets.all(16),
                    children: [
                      _buildPeriodSelector(),
                      SizedBox(height: 16),
                      _buildSummaryCard(),
                      SizedBox(height: 16),
                      _buildCostByServiceType(),
                    ],
                  ),
                ),
    );
  }
}
```

---

## ⚠️ Error Handling

### Common Errors

| Status Code | Error Message                  | Penanganan                                                            |
| ----------- | ------------------------------ | --------------------------------------------------------------------- |
| 404         | Motor utama belum ditetapkan   | Redirect user ke halaman vehicle management untuk set primary vehicle |
| 404         | Riwayat servis tidak ditemukan | Service history dengan ID tersebut tidak ada atau bukan milik user    |
| 422         | Validation error               | Tampilkan error messages per field ke user                            |
| 401         | Unauthorized                   | Token expired atau invalid, redirect ke login                         |
| 500         | Internal server error          | Tampilkan generic error message, retry option                         |

### Error Response Structure

```json
{
    "success": false,
    "message": "Error message here",
    "errors": {
        "field_name": ["Error message 1", "Error message 2"]
    }
}
```

### Flutter Error Handler

```dart
class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, dynamic>? errors;

  ApiException(this.message, {this.statusCode, this.errors});

  @override
  String toString() => message;
}

void handleApiError(DioException error, BuildContext context) {
  if (error.response != null) {
    final statusCode = error.response!.statusCode;
    final data = error.response!.data;

    switch (statusCode) {
      case 404:
        if (data['message']?.contains('Motor utama belum ditetapkan') == true) {
          // Redirect to vehicle management
          Navigator.pushReplacementNamed(context, '/vehicles');
          showDialog(
            context: context,
            builder: (context) => AlertDialog(
              title: Text('Motor Utama Belum Ditetapkan'),
              content: Text('Silakan atur motor utama terlebih dahulu.'),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: Text('OK'),
                ),
              ],
            ),
          );
        }
        break;

      case 422:
        // Validation errors
        final errors = data['errors'] as Map<String, dynamic>?;
        if (errors != null) {
          final errorMessages = errors.values
              .expand((messages) => messages as List)
              .join('\n');
          showDialog(
            context: context,
            builder: (context) => AlertDialog(
              title: Text('Validasi Gagal'),
              content: Text(errorMessages),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: Text('OK'),
                ),
              ],
            ),
          );
        }
        break;

      case 401:
        // Unauthorized - redirect to login
        Navigator.pushReplacementNamed(context, '/login');
        break;

      default:
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(data['message'] ?? 'Terjadi kesalahan')),
        );
    }
  } else {
    // Network error
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Tidak dapat terhubung ke server')),
    );
  }
}
```

---

## 🔧 Troubleshooting

### Common Issues & Solutions

#### 1. ❌ Error: `type 'Null' is not a subtype of type 'String' in type cast`

**Penyebab:**

- Response dari API mengandung null value untuk field yang di-expect sebagai non-nullable
- Field required tidak ada di response JSON

**Solusi:**

```dart
// ❌ WRONG - Tidak aman
factory ServiceHistory.fromJson(Map<String, dynamic> json) {
  return ServiceHistory(
    id: json['id'],  // Bisa error jika null
    serviceType: json['service_type'],  // Bisa error jika null
    // ...
  );
}

// ✅ CORRECT - Dengan null safety
factory ServiceHistory.fromJson(Map<String, dynamic> json) {
  try {
    // Validate required fields first
    if (json['id'] == null) throw FormatException('Missing required field: id');
    if (json['service_type'] == null) throw FormatException('Missing required field: service_type');

    return ServiceHistory(
      id: json['id'] as int,  // Type casting yang aman
      serviceType: json['service_type'] as String,
      cost: json['cost'] != null ? (json['cost'] as num).toDouble() : null,
      // ...
    );
  } catch (e) {
    throw FormatException('Failed to parse ServiceHistory: $e');
  }
}
```

**Debugging Tips:**

```dart
// Print response untuk lihat data yang sebenarnya
print('Response data: ${response.data}');
print('Service history: ${response.data['data']['service_history']}');

// Pastikan semua field ada
final serviceData = response.data['data']['service_history'];
if (serviceData == null) {
  throw Exception('service_history data is null');
}
```

---

#### 2. ❌ Error: Field name mismatch (`performed_at` vs `service_date`)

**Masalah:**
Ada confusion antara 2 API yang berbeda:

- **Service API** menggunakan `service_date` ← untuk Service entitas yang berbeda
- **Service History API** menggunakan `performed_at` ← ini yang benar untuk Service History

**Klarifikasi:**

| Endpoint                  | Field Name     | Description                                  |
| ------------------------- | -------------- | -------------------------------------------- |
| `POST /services`          | `service_date` | Untuk Service entity (bukan Service History) |
| `POST /service-histories` | `performed_at` | ✅ **Yang benar untuk Service History**      |

**Solusi:**

```dart
// ✅ CORRECT - Untuk Service History
final formData = FormData.fromMap({
  'service_type': serviceType,
  'performed_at': performedAt.toIso8601String().split('T')[0], // ✅ Ini yang benar
  'odometer': odometer,
  'cost': cost,
  // ...
});

// ❌ WRONG - Jangan gunakan service_date untuk Service History
final formData = FormData.fromMap({
  'service_type': serviceType,
  'service_date': performedAt.toIso8601String().split('T')[0], // ❌ Salah!
  // ...
});
```

---

#### 3. ❌ Error: `Motor utama belum ditetapkan`

**Penyebab:**
User/device belum set primary vehicle.

**Solusi:**

```dart
try {
  final histories = await _apiService.getAllServiceHistories();
  // ...
} on Exception catch (e) {
  if (e.toString().contains('Motor utama belum ditetapkan')) {
    // Redirect ke vehicle management
    Navigator.pushReplacementNamed(context, '/vehicles');

    // Show dialog
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Motor Utama Belum Ditetapkan'),
        content: Text('Silakan atur motor utama terlebih dahulu untuk menggunakan fitur ini.'),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              // Navigate to set primary vehicle
            },
            child: Text('Atur Sekarang'),
          ),
        ],
      ),
    );
  } else {
    // Handle other errors
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(e.toString())),
    );
  }
}
```

---

#### 4. ❌ Upload Foto Receipt Gagal (Error 422 atau 413)

**Penyebab Umum:**

- File terlalu besar (> 5MB)
- Format file tidak didukung
- File path invalid

**Solusi:**

```dart
// 1. Compress image sebelum upload
import 'package:flutter_image_compress/flutter_image_compress.dart';

Future<File?> compressAndValidateImage(File file) async {
  // Check file size
  final fileSize = await file.length();
  if (fileSize > 5 * 1024 * 1024) {
    // Compress if > 5MB
    final result = await FlutterImageCompress.compressAndGetFile(
      file.absolute.path,
      '${file.parent.path}/compressed_${file.uri.pathSegments.last}',
      quality: 70,
      minWidth: 1024,
      minHeight: 1024,
    );
    return result != null ? File(result.path) : null;
  }
  return file;
}

// 2. Validate file type
bool isValidImageFile(File file) {
  final extension = file.path.split('.').last.toLowerCase();
  return ['jpg', 'jpeg', 'png', 'webp'].contains(extension);
}

// 3. Use in upload
Future<void> uploadReceipt() async {
  if (_receiptPhoto == null) return;

  // Validate
  if (!isValidImageFile(_receiptPhoto!)) {
    throw Exception('Format file tidak didukung. Gunakan JPG, PNG, atau WebP.');
  }

  // Compress
  final compressedFile = await compressAndValidateImage(_receiptPhoto!);
  if (compressedFile == null) {
    throw Exception('Gagal memproses gambar');
  }

  // Upload
  await _apiService.createServiceHistory(
    // ...
    receiptPhotoPath: compressedFile.path,
  );
}
```

---

#### 5. ❌ Date Parsing Error

**Penyebab:**
Format tanggal tidak sesuai atau timezone issue.

**Solusi:**

```dart
// ✅ CORRECT - Parse dan format yang benar
DateTime parseDate(String dateString) {
  try {
    // API returns: "2024-02-15" (YYYY-MM-DD)
    return DateTime.parse(dateString);
  } catch (e) {
    throw FormatException('Invalid date format: $dateString');
  }
}

// ✅ CORRECT - Send to API
String formatDateForApi(DateTime date) {
  // Convert to YYYY-MM-DD (tanpa timestamp)
  return date.toIso8601String().split('T')[0];
}

// Example usage
final performedAt = DateTime.now();
final formData = FormData.fromMap({
  'performed_at': formatDateForApi(performedAt), // "2024-02-15"
});
```

---

#### 6. ❌ Network Timeout atau Connection Error

**Solusi:**

```dart
// Add timeout configuration
class ServiceHistoryApiService {
  ServiceHistoryApiService({
    required this.baseUrl,
    Dio? dio,
  }) : _dio = dio ?? Dio() {
    _dio.options.baseUrl = baseUrl;
    _dio.options.connectTimeout = Duration(seconds: 30);
    _dio.options.receiveTimeout = Duration(seconds: 30);
    _dio.options.sendTimeout = Duration(seconds: 30);

    // Add retry interceptor
    _dio.interceptors.add(
      RetryInterceptor(
        dio: _dio,
        retries: 3,
        retryDelays: [
          Duration(seconds: 1),
          Duration(seconds: 2),
          Duration(seconds: 3),
        ],
      ),
    );
  }
}
```

---

#### 7. 🐛 Debug Checklist

Jika masih ada error, cek:

- [ ] **Base URL benar?**

    ```dart
    print('Base URL: ${_dio.options.baseUrl}');
    ```

- [ ] **Headers lengkap?**

    ```dart
    print('Headers: ${_dio.options.headers}');
    ```

- [ ] **Request body sesuai?**

    ```dart
    print('Request data: ${formData.fields}');
    ```

- [ ] **Response structure sesuai?**

    ```dart
    print('Full response: ${response.data}');
    print('Success: ${response.data['success']}');
    print('Data: ${response.data['data']}');
    ```

- [ ] **Primary vehicle sudah di-set?**

    ```dart
    // Test get primary vehicle first
    final primaryVehicle = await vehicleApi.getPrimaryVehicle();
    print('Primary vehicle: $primaryVehicle');
    ```

- [ ] **Authentication/Device-ID valid?**
    ```dart
    final prefs = await SharedPreferences.getInstance();
    print('Token: ${prefs.getString('access_token')}');
    print('Device ID: ${prefs.getString('device_id')}');
    ```

---

## 🧪 Testing

### Testing dengan Postman

1. **Setup Environment Variables:**

```json
{
    "base_url": "https://yourdomain.com/api",
    "device_id": "your-device-id",
    "access_token": "your-access-token"
}
```

2. **Test Scenarios:**

#### Get All Service Histories - Guest Mode

```http
GET {{base_url}}/service-histories
Accept: application/json
X-Device-ID: {{device_id}}
```

#### Create Service History - Guest Mode

```http
POST {{base_url}}/service-histories
Accept: application/json
X-Device-ID: {{device_id}}
Content-Type: multipart/form-data

service_type: Ganti Oli
performed_at: 2024-02-15
odometer: 5000
cost: 150000
service_provider: Bengkel Motor Jaya
notes: Service rutin bulanan
receipt_photo: [file]
```

#### Get Cost Summary - All Time

```http
GET {{base_url}}/service-histories/cost-summary
Accept: application/json
X-Device-ID: {{device_id}}
```

#### Get Cost Summary - Monthly

```http
GET {{base_url}}/service-histories/cost-summary?period=month&year=2024&month=2
Accept: application/json
X-Device-ID: {{device_id}}
```

---

## 📝 Best Practices

### 1. Image Handling

- **Compress images** sebelum upload untuk menghemat bandwidth
- **Cache receipt images** di local storage untuk offline viewing
- **Lazy load images** di list view

```dart
import 'package:flutter_image_compress/flutter_image_compress.dart';

Future<File> compressImage(File file) async {
  final result = await FlutterImageCompress.compressAndGetFile(
    file.absolute.path,
    file.absolute.path.replaceAll('.jpg', '_compressed.jpg'),
    quality: 70,
    minWidth: 1024,
    minHeight: 1024,
  );
  return File(result!.path);
}
```

### 2. Offline Support

- **Cache service histories** menggunakan local database (Hive, SQLite)
- **Queue create/update operations** when offline
- **Sync when online**

### 3. Pagination (Future Enhancement)

Saat ini API belum support pagination, tapi siap untuk paginasi:

```dart
Future<List<ServiceHistory>> getServiceHistories({
  int page = 1,
  int perPage = 20,
}) async {
  final response = await _dio.get(
    '/service-histories',
    queryParameters: {
      'page': page,
      'per_page': perPage,
    },
  );
  // ...
}
```

### 4. Performance

- **Debounce search/filter** operations
- **Use ListView.builder** for large lists
- **Implement pull-to-refresh**
- **Show shimmer loading** untuk better UX

---

## 📚 Related APIs

- **Vehicle API**: Untuk manage primary vehicle
- **Service Type Master Data**: Untuk dropdown jenis servis
- **Service Schedules API**: Untuk reminder servis

---

## 🆘 Support

Untuk pertanyaan atau masalah, hubungi:

- GitHub Issues: [Repository URL]
- Email: support@yourdomain.com

---

**Last Updated:** February 2024
**API Version:** 1.0

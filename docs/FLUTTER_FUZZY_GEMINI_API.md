# Flutter API — Fuzzy & AI (Gemini) Recommendations

Dokumentasi ini untuk tim Flutter agar bisa mengonsumsi API **Fuzzy (skor kondisi komponen)** dan **AI Recommendation (Gemini, server-side)**.

## Base URL

Semua endpoint di bawah memiliki prefix:

- `{{HOST}}/api/v1/motorcycle`

Contoh (local Laragon):

- `http://localhost/api/v1/motorcycle`

## Authentication

Mayoritas endpoint pada dokumen ini memakai middleware `auth:sanctum,web` (wajib login).

### Login

- **POST** `/auth/login`
- Header: `Accept: application/json`
- Body (JSON):

```json
{
    "email": "user@mail.com",
    "password": "password",
    "device_id": "optional",
    "device_name": "optional"
}
```

Response sukses (contoh):

```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": { "id": 1, "name": "..." },
        "access_token": "<SANCTUM_TOKEN>",
        "refresh_token": "<REFRESH_TOKEN>",
        "token_type": "Bearer",
        "expires_in": 1800
    }
}
```

### Header untuk request authenticated

Kirim token di header:

- `Authorization: Bearer <access_token>`
- `Accept: application/json`

### Refresh Token

Jika `access_token` expired:

- **POST** `/auth/refresh-token`
- Body (JSON):

```json
{
    "refresh_token": "<REFRESH_TOKEN>"
}
```

Response sukses (contoh):

```json
{
    "success": true,
    "message": "Token berhasil diperbaharui",
    "data": {
        "access_token": "<NEW_ACCESS_TOKEN>",
        "refresh_token": "<NEW_REFRESH_TOKEN>",
        "token_type": "Bearer",
        "expires_in": 1800
    }
}
```

## Konsep Data

### Skor Fuzzy

- Range skor: **0..100** (semakin kecil = semakin buruk/kritis)
- Status dihitung dari skor dan threshold:

$$
\text{status} =
\begin{cases}
\text{critical} & \text{jika } score \le critical\\
\text{warning} & \text{jika } score \le warn\\
\text{normal} & \text{selain itu}
\end{cases}
$$

Nilai `warn` dan `critical` bisa diambil dari endpoint master data komponen motor.

### Output AI (Gemini)

Flutter **tidak memanggil Gemini langsung**. Flutter hanya memanggil endpoint API aplikasi, dan server akan:

- Hitung skor Fuzzy
- (Opsional) Panggil Gemini untuk menyusun rekomendasi berbasis skor
- Cache hasil rekomendasi agar tidak selalu memanggil Gemini
- Jika Gemini gagal / belum dikonfigurasi, server akan mengembalikan **fallback** yang formatnya sama

## Endpoint — Fuzzy Master Data

> Semua endpoint di bawah: **Authenticated**

### 1) List motor types

- **GET** `/motor-types`

Response sukses:

```json
{
    "success": true,
    "message": "Daftar tipe motor berhasil diambil",
    "data": [
        { "id": 1, "name": "Scooter", "slug": "scooter" },
        { "id": 2, "name": "Sport", "slug": "sport" }
    ]
}
```

### 2) List komponen + threshold per motor type

- **GET** `/motor-types/{slug}/components`

Response sukses (contoh ringkas):

```json
{
    "success": true,
    "message": "Daftar komponen berhasil diambil",
    "data": {
        "motor_type": { "id": 1, "name": "Scooter", "slug": "scooter" },
        "components": [
            {
                "id": 10,
                "motor_type_id": 1,
                "name": "oli_mesin",
                "warn": 60,
                "critical": 40,
                "reset_interval": 30,
                "active_vars": [
                    "distance_since_service_km",
                    "duration_since_service_days"
                ],
                "is_active": true,
                "is_custom": false,
                "updated_at": "2026-05-11T10:00:00.000000Z"
            }
        ]
    }
}
```

Jika `slug` tidak ditemukan:

```json
{
    "success": false,
    "message": "Tipe motor tidak ditemukan"
}
```

## Endpoint — Fuzzy Scores & AI Recommendations (Gemini)

> Semua endpoint di bawah: **Authenticated**

Catatan penting:

- `motorId` di path adalah **ID Vehicle/Motor** (tabel `vehicles`).
- Jika `motorId` bukan milik user yang login, server mengembalikan **404** `Motor tidak ditemukan`.

### 3) Fuzzy scores (tanpa Gemini)

Dipakai untuk refresh cepat UI (tanpa menunggu Gemini).

- **GET** `/motors/{motorId}/scores`

Response sukses:

```json
{
    "success": true,
    "data": {
        "fuzzy_scores": {
            "aki": 78.2,
            "ban": 55.0,
            "oli_mesin": 35.4
        }
    },
    "meta": {
        "from_cache": false,
        "generated_at": "2026-05-11T12:34:56.789Z"
    }
}
```

### 4) Home Insight (screen 1)

- **GET** `/motors/{motorId}/home-insight`

Response sukses:

```json
{
    "success": true,
    "data": {
        "wawasan_pintar": [
            {
                "judul": "Servis Oli Mesin Segera",
                "isi": "...",
                "prioritas": "critical"
            }
        ],
        "insight_sistem": {
            "label": "Penggunaan Moderat",
            "isi": "..."
        },
        "fuzzy_scores": {
            "oli_mesin": 35.4,
            "ban": 55.0
        }
    },
    "meta": {
        "from_cache": true,
        "generated_at": "2026-05-11T09:00:00.000Z"
    }
}
```

### 5) Service Recommendation (screen 2)

- **GET** `/motors/{motorId}/service-recommendation`

Response sukses:

```json
{
    "success": true,
    "data": {
        "ringkasan_kondisi": "Motor memerlukan perhatian pada Oli Mesin...",
        "rekomendasi_komponen": [
            {
                "komponen": "Oli Mesin",
                "prioritas": "critical",
                "saran": "...",
                "estimasi_waktu": "secepatnya"
            }
        ],
        "tips_mandiri": "Periksa tekanan ban dan level oli..."
    },
    "meta": {
        "from_cache": false,
        "generated_at": "2026-05-11T12:30:00.000Z"
    }
}
```

## Error Handling (format umum)

Sebagian endpoint memakai trait `ApiResponse`, format error umumnya:

```json
{
    "success": false,
    "message": "...",
    "errors": { "optional": "..." }
}
```

Kasus yang paling sering:

- **401 Unauthorized**: token kosong/tidak valid/expired
- **404 Not Found**: motor tidak ditemukan / bukan milik user
- **500 Server Error**: error internal (biasanya disertai log server)

## Catatan Cache & Performa

Untuk endpoint AI (`/home-insight`, `/service-recommendation`), server dapat mengembalikan `meta.from_cache=true`.

Aturan refresh cache (ringkas):

- Cache lebih dari 3 hari
- Konfigurasi fuzzy berubah
- Status komponen berubah
- Perubahan skor >= `services.gemini.score_delta_trigger` (default 5)

Jika Gemini tidak tersedia/limit/error:

- Server mengembalikan **fallback** dengan struktur yang sama.

## Rekomendasi Flow di Flutter (praktis)

- Saat membuka halaman: panggil `/motors/{motorId}/scores` dulu untuk tampil cepat.
- Setelah itu, panggil:
    - Home: `/motors/{motorId}/home-insight`
    - Rekomendasi servis: `/motors/{motorId}/service-recommendation`
- Gunakan `meta.generated_at` untuk menampilkan “terakhir diperbarui”.

## Contoh Implementasi Flutter (Dio) — ringkas

```dart
final dio = Dio(BaseOptions(
  baseUrl: 'https://example.com/api/v1/motorcycle',
  headers: {
    'Accept': 'application/json',
    'Authorization': 'Bearer $accessToken',
  },
));

final res = await dio.get('/motors/$motorId/home-insight');
final data = res.data['data'];
final wawasan = (data['wawasan_pintar'] as List?) ?? [];
```

> Untuk refresh token: jika dapat 401, panggil `/auth/refresh-token`, simpan token baru, lalu retry request.

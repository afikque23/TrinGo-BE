# User Profile API Documentation

## Overview

API untuk manajemen profil pengguna dengan statistik lengkap (total motor, total trip, total kilometer).

## Base URL

```
/api/v1/motorcycle/profile
```

## Authentication

Semua endpoint memerlukan authentication Bearer token.

```
Authorization: Bearer {access_token}
```

---

## Endpoints

### 1. Get User Profile

**GET** `/profile`

Mendapatkan data profil pengguna yang sedang login beserta statistik.

#### Response Success (200)

```json
{
    "success": true,
    "message": "Profile retrieved successfully",
    "data": {
        "id": 1,
        "name": "Ahmad Rifai",
        "email": "ahmad.rifai@example.com",
        "phone": "+62 812 3456 7890",
        "location": "Jakarta, Indonesia",
        "avatar": "http://localhost:8000/storage/avatars/photo.jpg",
        "role": "user",
        "is_active": true,
        "email_verified_at": "2024-02-20T10:30:00+07:00",
        "phone_verified_at": null,
        "last_login_at": "2024-02-20T14:20:00+07:00",
        "created_at": "2024-01-15T08:00:00+07:00",
        "updated_at": "2024-02-20T14:20:00+07:00",
        "stats": {
            "total_vehicles": 2
        }
    }
}
```

#### Stats Explanation

- **total_vehicles**: Jumlah total motor yang dimiliki user

---

### 2. Update Profile

**POST** `/profile/update`

Update data profil pengguna. Menggunakan POST karena support upload file (avatar).

#### Request Headers

```
Content-Type: multipart/form-data
Authorization: Bearer {access_token}
```

#### Request Body (Form Data)

| Field                     | Type   | Required | Description                                     |
| ------------------------- | ------ | -------- | ----------------------------------------------- |
| name                      | string | No       | Nama lengkap                                    |
| email                     | string | No       | Email (harus unique)                            |
| phone                     | string | No       | Nomor telepon                                   |
| location                  | string | No       | Lokasi/alamat                                   |
| avatar                    | file   | No       | Foto profil (jpeg, png, jpg, gif, max 2MB)      |
| current_password          | string | Yes\*    | Password saat ini (\*wajib jika ganti password) |
| new_password              | string | No       | Password baru (min 8 karakter)                  |
| new_password_confirmation | string | No       | Konfirmasi password baru                        |

#### Example Request (Update Name & Phone)

```bash
curl -X POST http://localhost:8000/api/v1/motorcycle/profile/update \
  -H "Authorization: Bearer {token}" \
  -F "name=Ahmad Rifai Updated" \
  -F "phone=+62 812 9999 8888"
```

#### Example Request (Update Avatar)

```bash
curl -X POST http://localhost:8000/api/v1/motorcycle/profile/update \
  -H "Authorization: Bearer {token}" \
  -F "avatar=@/path/to/photo.jpg"
```

#### Example Request (Change Password)

```bash
curl -X POST http://localhost:8000/api/v1/motorcycle/profile/update \
  -H "Authorization: Bearer {token}" \
  -F "current_password=oldpassword123" \
  -F "new_password=newpassword123" \
  -F "new_password_confirmation=newpassword123"
```

#### Response Success (200)

```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "id": 1,
        "name": "Ahmad Rifai Updated",
        "email": "ahmad.rifai@example.com",
        "phone": "+62 812 9999 8888",
        "location": "Jakarta, Indonesia",
        "avatar": "http://localhost:8000/storage/avatars/new-photo.jpg",
        "role": "user",
        "is_active": true,
        "email_verified_at": "2024-02-20T10:30:00+07:00",
        "phone_verified_at": null,
        "last_login_at": "2024-02-20T14:20:00+07:00",
        "created_at": "2024-01-15T08:00:00+07:00",
        "updated_at": "2024-02-20T15:45:00+07:00",
        "stats": {
            "total_vehicles": 2
        }
    }
}
```

#### Response Error - Invalid Current Password (400)

```json
{
    "success": false,
    "message": "Current password is incorrect",
    "data": null
}
```

#### Response Error - Validation Failed (422)

```json
{
    "success": false,
    "message": "Validation failed",
    "data": {
        "email": ["The email has already been taken."],
        "new_password": ["The new password must be at least 8 characters."]
    }
}
```

---

### 3. Delete Avatar

**DELETE** `/profile/avatar`

Menghapus foto profil pengguna.

#### Response Success (200)

```json
{
    "success": true,
    "message": "Avatar deleted successfully",
    "data": {
        "id": 1,
        "name": "Ahmad Rifai",
        "email": "ahmad.rifai@example.com",
        "phone": "+62 812 3456 7890",
        "location": "Jakarta, Indonesia",
        "avatar": null,
        "role": "user",
        "is_active": true,
        "email_verified_at": "2024-02-20T10:30:00+07:00",
        "phone_verified_at": null,
        "last_login_at": "2024-02-20T14:20:00+07:00",
        "created_at": "2024-01-15T08:00:00+07:00",
        "updated_at": "2024-02-20T16:00:00+07:00",
        "stats": {
            "total_vehicles": 2
        }
    }
}
```

---

## Updated Authentication Endpoints

Login dan Register sekarang juga return profile dengan stats.

### Login Response (Updated)

```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "Ahmad Rifai",
            "email": "ahmad.rifai@example.com",
            "phone": "+62 812 3456 7890",
            "location": "Jakarta, Indonesia",
            "avatar": "http://localhost:8000/storage/avatars/photo.jpg",
            "role": "user",
            "is_active": true,
            "email_verified_at": "2024-02-20T10:30:00+07:00",
            "phone_verified_at": null,
            "last_login_at": "2024-02-20T14:20:00+07:00",
            "created_at": "2024-01-15T08:00:00+07:00",
            "updated_at": "2024-02-20T14:20:00+07:00",
            "stats": {
                "total_vehicles": 2
            }
        },
        "access_token": "1|xxxxxx...",
        "refresh_token": "xxxxxx...",
        "token_type": "Bearer",
        "expires_in": 1800,
        "sync_info": {
            "synced": true,
            "total_synced": 5,
            "details": {
                "vehicles": 2,
                "notifications": 3,
                "trips": 0,
                "fuel_logs": 0
            }
        }
    }
}
```

### GET /auth/me Response (Updated)

Same structure sebagai response di atas (user profile dengan stats).

---

## Error Responses

### 401 Unauthenticated

```json
{
    "success": false,
    "message": "Unauthenticated",
    "data": null
}
```

### 422 Validation Error

```json
{
    "success": false,
    "message": "Validation failed",
    "data": {
        "field_name": ["Error message"]
    }
}
```

### 500 Internal Server Error

```json
{
    "success": false,
    "message": "Error message",
    "data": "Detailed error (only in development)"
}
```

---

## Integration dengan Flutter

### 1. Model User dengan Stats

```dart
class UserProfile {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? location;
  final String? avatar;
  final ProfileStats stats;
  final DateTime? emailVerifiedAt;
  final DateTime? lastLoginAt;

  UserProfile({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.location,
    this.avatar,
    required this.stats,
    this.emailVerifiedAt,
    this.lastLoginAt,
  });

  factory UserProfile.fromJson(Map<String, dynamic> json) {
    return UserProfile(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      phone: json['phone'],
      location: json['location'],
      avatar: json['avatar'],
      stats: ProfileStats.fromJson(json['stats']),
      emailVerifiedAt: json['email_verified_at'] != null
        ? DateTime.parse(json['email_verified_at'])
        : null,
      lastLoginAt: json['last_login_at'] != null
        ? DateTime.parse(json['last_login_at'])
        : null,
    );
  }
}

class ProfileStats {
  final int totalVehicles;

  ProfileStats({
    required this.totalVehicles,
  });

  factory ProfileStats.fromJson(Map<String, dynamic> json) {
    return ProfileStats(
      totalVehicles: json['total_vehicles'] ?? 0,
    );
  }
}
```

### 2. API Service

```dart
class ProfileService {
  final String baseUrl = 'http://localhost:8000/api/v1/motorcycle';

  Future<UserProfile> getProfile(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/profile'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return UserProfile.fromJson(data['data']);
    } else {
      throw Exception('Failed to load profile');
    }
  }

  Future<UserProfile> updateProfile({
    required String token,
    String? name,
    String? email,
    String? phone,
    String? location,
    File? avatarFile,
  }) async {
    var request = http.MultipartRequest(
      'POST',
      Uri.parse('$baseUrl/profile/update'),
    );

    request.headers['Authorization'] = 'Bearer $token';
    request.headers['Accept'] = 'application/json';

    if (name != null) request.fields['name'] = name;
    if (email != null) request.fields['email'] = email;
    if (phone != null) request.fields['phone'] = phone;
    if (location != null) request.fields['location'] = location;

    if (avatarFile != null) {
      request.files.add(
        await http.MultipartFile.fromPath('avatar', avatarFile.path),
      );
    }

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return UserProfile.fromJson(data['data']);
    } else {
      throw Exception('Failed to update profile');
    }
  }

  Future<UserProfile> deleteAvatar(String token) async {
    final response = await http.delete(
      Uri.parse('$baseUrl/profile/avatar'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return UserProfile.fromJson(data['data']);
    } else {
      throw Exception('Failed to delete avatar');
    }
  }
}
```

---

## Testing dengan Postman

1. **Import Collection**: Import file `Motorcycle_Management_API.postman_collection.json`
2. **Set Environment**: Gunakan environment `Motorcycle_Management_Local`
3. **Login**: Jalankan endpoint "Login" untuk mendapatkan `access_token`
4. **Test Profile Endpoints**:
    - Get Profile
    - Update Profile (dengan form-data)
    - Delete Avatar

---

## Database Schema

### Users Table (Updated)

```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  phone VARCHAR(20) NULL,
  location VARCHAR(200) NULL,
  avatar VARCHAR(255) NULL,
  role VARCHAR(50) DEFAULT 'user',
  password VARCHAR(255) NOT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  email_verified_at TIMESTAMP NULL,
  phone_verified_at TIMESTAMP NULL,
  last_login_at TIMESTAMP NULL,
  refresh_token VARCHAR(255) NULL,
  refresh_token_expires_at TIMESTAMP NULL,
  device_id VARCHAR(255) NULL,
  device_name VARCHAR(255) NULL,
  fcm_token TEXT NULL,
  fcm_token_updated_at TIMESTAMP NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
```

---

## Notes

- Avatar disimpan di `storage/app/public/avatars/`
- URL avatar otomatis di-generate: `url('storage/' . $user->avatar)`
- Pastikan `php artisan storage:link` sudah dijalankan
- Max file size untuk avatar: 2MB
- Format avatar yang didukung: jpeg, png, jpg, gif

---

## Change Log (Today - 2024-02-20)

### ✅ Additions

1. **Profile Management API**
    - GET /profile - Get user profile dengan stats
    - POST /profile/update - Update profile dengan avatar upload
    - DELETE /profile/avatar - Delete avatar
2. **ProfileResource**
    - Return profile data dengan stats (total_vehicles, total_trips, total_km)
    - URL avatar otomatis di-generate
3. **Database Migration**
    - Added `location` column to users table
    - Ensured `phone` and `avatar` columns exist

4. **AuthController Updates**
    - Login response now includes ProfileResource dengan stats
    - Register/VerifyEmail response includes ProfileResource
    - GET /auth/me now returns ProfileResource dengan stats

5. **Postman Collection**
    - Added "User Profile" folder dengan 3 endpoints
    - Auto-save access_token pada response

### 🔄 Modified

- AuthController di namespace `App\Http\Controllers\Api`
- User model: Added `location` to $fillable

---

## Support

Untuk pertanyaan atau issue, hubungi development team.

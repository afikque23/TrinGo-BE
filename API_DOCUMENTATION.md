# API Authentication Documentation

## Motorcycle Management System

Base URL: `http://localhost/api`

---

## 📋 Table of Contents

1. [Register](#1-register)
2. [Verify Email](#2-verify-email)
3. [Resend OTP](#3-resend-otp)
4. [Login](#4-login)
5. [Logout](#5-logout)
6. [Get User Profile](#6-get-user-profile)
7. [Forgot Password](#7-forgot-password)
8. [Reset Password](#8-reset-password)
9. [Change Password](#9-change-password)

---

## 🔐 Authentication

API menggunakan Laravel Sanctum untuk autentikasi token.

### Token Usage

Untuk endpoint yang memerlukan autentikasi, tambahkan header:

```
Authorization: Bearer {your_token}
```

---

## API Endpoints

### 1. Register

Mendaftarkan user baru dan mengirim OTP untuk verifikasi email.

**Endpoint:** `POST /api/auth/register`

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "081234567890",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response Success (201):**

```json
{
    "success": true,
    "message": "Registrasi berhasil. Silakan verifikasi email Anda dengan kode OTP yang telah dikirim.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "081234567890"
        },
        "otp": "123456",
        "expires_at": "2024-01-01T10:10:00.000000Z"
    }
}
```

**Response Error (422):**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["Email sudah terdaftar"],
        "password": ["Password minimal 8 karakter"]
    }
}
```

---

### 2. Verify Email

Memverifikasi email dengan kode OTP.

**Endpoint:** `POST /api/auth/verify-email`

**Request Body:**

```json
{
    "email": "john@example.com",
    "otp": "123456",
    "type": "email_verification"
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Email berhasil diverifikasi",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "081234567890",
            "email_verified_at": "2024-01-01T10:15:00.000000Z"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

**Response Error (400):**

```json
{
    "success": false,
    "message": "Kode OTP tidak valid atau sudah kadaluarsa"
}
```

---

### 3. Resend OTP

Mengirim ulang kode OTP.

**Endpoint:** `POST /api/auth/resend-otp`

**Request Body:**

```json
{
    "email": "john@example.com",
    "type": "email_verification"
}
```

**Types:** `email_verification`, `password_reset`

**Response Success (200):**

```json
{
    "success": true,
    "message": "Kode OTP baru telah dikirim ke email Anda",
    "data": {
        "otp": "654321",
        "expires_at": "2024-01-01T10:20:00.000000Z"
    }
}
```

---

### 4. Login

Login user dengan email dan password.

**Endpoint:** `POST /api/auth/login`

**Request Body:**

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "081234567890",
            "avatar": null,
            "email_verified_at": "2024-01-01T10:15:00.000000Z",
            "last_login_at": "2024-01-01T10:30:00.000000Z"
        },
        "token": "2|xyz789...",
        "token_type": "Bearer"
    }
}
```

**Response Error - Wrong Credentials (401):**

```json
{
    "success": false,
    "message": "Email atau password salah"
}
```

**Response Error - Email Not Verified (403):**

```json
{
    "success": false,
    "message": "Email belum diverifikasi. Kode OTP telah dikirim ke email Anda.",
    "errors": {
        "requires_verification": true,
        "otp": "123456"
    }
}
```

---

### 5. Logout

Logout user dan hapus token aktif.

**Endpoint:** `POST /api/auth/logout`

**Headers:**

```
Authorization: Bearer {token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Logout berhasil",
    "data": null
}
```

---

### 6. Get User Profile

Mendapatkan data user yang sedang login.

**Endpoint:** `GET /api/auth/me`

**Headers:**

```
Authorization: Bearer {token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Data user berhasil diambil",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "081234567890",
            "avatar": null,
            "email_verified_at": "2024-01-01T10:15:00.000000Z",
            "phone_verified_at": null,
            "last_login_at": "2024-01-01T10:30:00.000000Z",
            "is_active": true,
            "created_at": "2024-01-01T10:00:00.000000Z"
        }
    }
}
```

---

### 7. Forgot Password

Meminta kode OTP untuk reset password.

**Endpoint:** `POST /api/auth/forgot-password`

**Request Body:**

```json
{
    "email": "john@example.com"
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Kode OTP untuk reset password telah dikirim ke email Anda",
    "data": {
        "otp": "789012",
        "expires_at": "2024-01-01T11:00:00.000000Z"
    }
}
```

---

### 8. Reset Password

Reset password dengan kode OTP.

**Endpoint:** `POST /api/auth/reset-password`

**Request Body:**

```json
{
    "email": "john@example.com",
    "otp": "789012",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Password berhasil direset. Silakan login dengan password baru Anda.",
    "data": null
}
```

**Response Error (400):**

```json
{
    "success": false,
    "message": "Kode OTP tidak valid atau sudah kadaluarsa"
}
```

---

### 9. Change Password

Mengganti password (user harus login).

**Endpoint:** `POST /api/auth/change-password`

**Headers:**

```
Authorization: Bearer {token}
```

**Request Body:**

```json
{
    "current_password": "password123",
    "password": "newpassword456",
    "password_confirmation": "newpassword456"
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Password berhasil diubah",
    "data": null
}
```

**Response Error (400):**

```json
{
    "success": false,
    "message": "Password lama tidak sesuai"
}
```

---

## 🧪 Testing with Postman

### 1. Import Collection

Buat Postman Collection dengan endpoints di atas.

### 2. Environment Variables

Tambahkan variables:

-   `base_url`: `http://localhost/api`
-   `token`: (akan di-set otomatis setelah login)

### 3. Auth Flow

1. **Register** → Dapatkan OTP
2. **Verify Email** → Dapatkan token
3. **Login** → Dapatkan token baru
4. Gunakan token untuk endpoint yang memerlukan auth

---

## 🔒 Security Notes

### ⚠️ PRODUCTION CHECKLIST:

1. **Remove OTP from Response**

    - Hapus `'otp' => $otp->otp` dari semua response
    - OTP hanya dikirim via email, tidak dikembalikan di API

2. **Setup Email Service**

    - Configure `.env` untuk mail driver (SMTP/Mailgun/SES)
    - Buat Mail class untuk mengirim OTP

3. **Rate Limiting**

    - Tambahkan throttle middleware untuk login attempts
    - Batasi resend OTP (max 3x per 10 menit)

4. **Environment Variables**

```env
# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@motorcyclemanagement.com
MAIL_FROM_NAME="Motorcycle Management"

# OTP Configuration
OTP_EXPIRY_MINUTES=10
OTP_MAX_ATTEMPTS=5
```

5. **Additional Security**
    - Enable CORS properly
    - Use HTTPS in production
    - Add request validation
    - Log suspicious activities
    - Implement 2FA (optional)

---

## 📝 Response Format

Semua response mengikuti format standar:

### Success Response

```json
{
    "success": true,
    "message": "string",
    "data": {} | [] | null
}
```

### Error Response

```json
{
    "success": false,
    "message": "string",
    "errors": {} | null
}
```

---

## 🔧 Error Codes

| Code | Description      |
| ---- | ---------------- |
| 200  | Success          |
| 201  | Created          |
| 400  | Bad Request      |
| 401  | Unauthorized     |
| 403  | Forbidden        |
| 404  | Not Found        |
| 422  | Validation Error |
| 500  | Server Error     |

---

## 📞 Support

Untuk pertanyaan atau masalah, silakan buka issue di repository atau hubungi tim development.

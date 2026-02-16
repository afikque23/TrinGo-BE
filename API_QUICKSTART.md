# 🚀 Quick Start - API Authentication

## ✅ Setup Sudah Selesai!

Sistem API authentication sudah siap digunakan dengan fitur:

-   ✅ Register dengan email verification
-   ✅ Login dengan token authentication
-   ✅ OTP verification system
-   ✅ Forgot & reset password
-   ✅ Change password
-   ✅ User profile management

---

## 📋 Yang Sudah Dibuat

### 1. Database Tables

-   ✅ `users` - User accounts
-   ✅ `otp_verifications` - OTP codes
-   ✅ `personal_access_tokens` - Sanctum tokens

### 2. API Endpoints (10 endpoints)

```
POST   /api/auth/register
POST   /api/auth/verify-email
POST   /api/auth/resend-otp
POST   /api/auth/login
POST   /api/auth/logout (protected)
GET    /api/auth/me (protected)
POST   /api/auth/forgot-password
POST   /api/auth/reset-password
POST   /api/auth/change-password (protected)
GET    /api/health
```

### 3. Files Created

-   ✅ `app/Http/Controllers/Api/AuthController.php` - Main controller
-   ✅ `app/Models/OtpVerification.php` - OTP model
-   ✅ `app/Traits/ApiResponse.php` - Response helper
-   ✅ `app/Http/Requests/Auth/*Request.php` - Validation classes (5 files)
-   ✅ `routes/api.php` - API routes
-   ✅ `public/api-test.html` - Interactive API tester

### 4. Documentation

-   ✅ `API_DOCUMENTATION.md` - Complete API docs
-   ✅ `API_TEST_GUIDE.md` - Testing guide
-   ✅ `database/ER_DIAGRAM.html` - Database diagram

---

## 🧪 Testing API

### Option 1: Browser (Recommended for beginners)

1. Buka browser
2. Akses: `http://localhost:8000/api-test.html`
3. Test semua endpoint dengan UI interaktif

### Option 2: Postman

1. Import collection dari `API_DOCUMENTATION.md`
2. Set environment variable: `base_url = http://localhost:8000/api`
3. Test endpoints

### Option 3: cURL

```bash
# Health check
curl http://localhost:8000/api/health

# Register
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@test.com","password":"password123","password_confirmation":"password123"}'
```

---

## 🔑 Authentication Flow

### 1. Register New User

```
POST /api/auth/register
→ Returns: user data + OTP code (for development)
```

### 2. Verify Email with OTP

```
POST /api/auth/verify-email
→ Returns: user data + auth token
→ Save this token!
```

### 3. Login (for existing users)

```
POST /api/auth/login
→ Returns: user data + auth token
```

### 4. Use Token for Protected Endpoints

```
Add header: Authorization: Bearer {your_token}
```

---

## 📝 Example Test Flow

### Scenario: Register → Verify → Login → Get Profile

1. **Register**

    ```json
    POST /api/auth/register
    {
      "name": "John Doe",
      "email": "john@test.com",
      "password": "password123",
      "password_confirmation": "password123"
    }
    ```

    Response will include OTP (e.g., "123456")

2. **Verify Email**

    ```json
    POST /api/auth/verify-email
    {
      "email": "john@test.com",
      "otp": "123456",
      "type": "email_verification"
    }
    ```

    Save the token from response!

3. **Get Profile**

    ```
    GET /api/auth/me
    Header: Authorization: Bearer {token}
    ```

4. **Logout**
    ```
    POST /api/auth/logout
    Header: Authorization: Bearer {token}
    ```

---

## ⚠️ Important Notes

### Development Mode

-   OTP codes are returned in API responses (for easy testing)
-   **REMOVE THIS IN PRODUCTION!**

### Production Checklist

1. Remove OTP from responses in `AuthController.php`
2. Setup email service (SMTP/Mailgun)
3. Create email templates for OTP
4. Add rate limiting
5. Enable HTTPS
6. Configure CORS properly

### Email Configuration

Edit `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

---

## 🐛 Troubleshooting

### Error: "Route not found"

-   Check if API routes are loaded in `bootstrap/app.php`
-   Run: `php artisan route:list --path=api`

### Error: "Unauthenticated"

-   Check if token is included in Authorization header
-   Format: `Bearer {token}` (with space!)

### Error: "CSRF token mismatch"

-   API routes don't need CSRF token
-   Make sure you're using `/api/` prefix

### OTP Not Working

-   Check database: `SELECT * FROM otp_verifications;`
-   OTP expires after 10 minutes
-   Max 5 attempts allowed

---

## 📚 Next Steps

1. **Test All Endpoints**

    - Open `http://localhost:8000/api-test.html`
    - Test register → verify → login flow

2. **Build Vehicle API**

    - Create `VehicleController`
    - Add CRUD endpoints
    - Use same authentication

3. **Add Email Service**

    - Setup SMTP
    - Create OTP email template
    - Remove OTP from responses

4. **Frontend Integration**
    - Use token in localStorage
    - Add interceptor for auth header
    - Handle 401 errors (redirect to login)

---

## 🎯 Available Routes

View all routes:

```bash
php artisan route:list
```

View API routes only:

```bash
php artisan route:list --path=api
```

---

## 📞 Need Help?

-   Read: `API_DOCUMENTATION.md` - Complete API reference
-   Read: `API_TEST_GUIDE.md` - Testing examples
-   Check: `storage/logs/laravel.log` - Error logs
-   Test UI: `http://localhost:8000/api-test.html`

Happy coding! 🚀

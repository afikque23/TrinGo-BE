# Test API Authentication

## Prerequisites

-   Server running (Laragon/xampp)
-   Database migrated

## Quick Test Commands

### 1. Health Check

```bash
curl http://localhost/api/health
```

### 2. Register New User

```bash
curl -X POST http://localhost/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"name\":\"Test User\",\"email\":\"test@example.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}"
```

### 3. Verify Email (use OTP from register response)

```bash
curl -X POST http://localhost/api/auth/verify-email \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"test@example.com\",\"otp\":\"123456\",\"type\":\"email_verification\"}"
```

### 4. Login

```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"test@example.com\",\"password\":\"password123\"}"
```

### 5. Get Profile (use token from login)

```bash
curl http://localhost/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

### 6. Logout

```bash
curl -X POST http://localhost/api/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## Testing Flow

1. **Register** → Save OTP from response
2. **Verify Email** with OTP → Save token
3. **Login** → Get new token
4. **Get Profile** with token
5. **Logout**

## Postman Collection

Import this JSON to Postman:

```json
{
    "info": {
        "name": "Motorcycle Management API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Auth",
            "item": [
                {
                    "name": "Register",
                    "request": {
                        "method": "POST",
                        "header": [
                            { "key": "Accept", "value": "application/json" }
                        ],
                        "url": "{{base_url}}/auth/register",
                        "body": {
                            "mode": "raw",
                            "raw": "{\"name\":\"John Doe\",\"email\":\"john@example.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}",
                            "options": { "raw": { "language": "json" } }
                        }
                    }
                },
                {
                    "name": "Verify Email",
                    "request": {
                        "method": "POST",
                        "header": [
                            { "key": "Accept", "value": "application/json" }
                        ],
                        "url": "{{base_url}}/auth/verify-email",
                        "body": {
                            "mode": "raw",
                            "raw": "{\"email\":\"john@example.com\",\"otp\":\"123456\",\"type\":\"email_verification\"}",
                            "options": { "raw": { "language": "json" } }
                        }
                    }
                },
                {
                    "name": "Login",
                    "request": {
                        "method": "POST",
                        "header": [
                            { "key": "Accept", "value": "application/json" }
                        ],
                        "url": "{{base_url}}/auth/login",
                        "body": {
                            "mode": "raw",
                            "raw": "{\"email\":\"john@example.com\",\"password\":\"password123\"}",
                            "options": { "raw": { "language": "json" } }
                        }
                    }
                },
                {
                    "name": "Get Profile",
                    "request": {
                        "method": "GET",
                        "header": [
                            { "key": "Accept", "value": "application/json" },
                            {
                                "key": "Authorization",
                                "value": "Bearer {{token}}"
                            }
                        ],
                        "url": "{{base_url}}/auth/me"
                    }
                },
                {
                    "name": "Logout",
                    "request": {
                        "method": "POST",
                        "header": [
                            { "key": "Accept", "value": "application/json" },
                            {
                                "key": "Authorization",
                                "value": "Bearer {{token}}"
                            }
                        ],
                        "url": "{{base_url}}/auth/logout"
                    }
                }
            ]
        }
    ],
    "variable": [
        { "key": "base_url", "value": "http://localhost/api" },
        { "key": "token", "value": "" }
    ]
}
```

## Expected Results

### ✅ Success Responses

-   Register: 201 with user data and OTP
-   Verify: 200 with user data and token
-   Login: 200 with user data and token
-   Profile: 200 with user details
-   Logout: 200 with success message

### ❌ Common Errors

-   422: Validation error (check request body)
-   401: Unauthorized (wrong credentials or no token)
-   403: Forbidden (email not verified or account inactive)
-   500: Server error (check logs)

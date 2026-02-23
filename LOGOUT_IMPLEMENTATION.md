# Logout Implementation with Persistent Login

**Version:** 1.0  
**Date:** February 21, 2026  
**System:** 90-Day Persistent Login with Token Revocation

---

## Overview

Sistem logout yang terintegrasi dengan persistent login memastikan:

- ✅ Semua access tokens dicabut (revoked)
- ✅ Refresh token dihapus dari database
- ✅ Local storage dibersihkan di Flutter
- ✅ User harus login ulang untuk mengakses aplikasi
- ✅ Session cross-device ditutup (jika logout all devices)

---

## Backend API

### Endpoint

```
POST /api/v1/motorcycle/auth/logout
```

### Headers

```http
Authorization: Bearer {access_token}
Content-Type: application/json
```

### Request Body

**Tidak ada body required**

### Response

**Success (200 OK):**

```json
{
    "success": true,
    "message": "Logout successful",
    "data": null
}
```

**Error - Unauthenticated (401):**

```json
{
    "success": false,
    "message": "Unauthenticated",
    "data": null
}
```

**Error - Server Error (500):**

```json
{
    "success": false,
    "message": "Logout failed",
    "data": "Error details"
}
```

---

## Backend Implementation

### AuthController.php

```php
/**
 * Logout user (revoke tokens).
 */
public function logout(Request $request): JsonResponse
{
    try {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse(
                'Unauthenticated',
                401
            );
        }

        // Revoke all access tokens (Sanctum)
        $user->tokens()->delete();

        // Revoke refresh token
        $user->revokeRefreshToken();

        return $this->successResponse(
            null,
            'Logout successful'
        );

    } catch (\Exception $e) {
        Log::error('Logout error: ' . $e->getMessage());

        return $this->errorResponse(
            'Logout failed',
            500,
            $e->getMessage()
        );
    }
}
```

### What Happens:

1. **Validate Authentication**
    - Check apakah user masih authenticated
    - Return 401 jika tidak ada user

2. **Revoke All Access Tokens**
    - `$user->tokens()->delete()` menghapus semua Sanctum tokens
    - Semua device yang menggunakan access token ini langsung terlogout

3. **Revoke Refresh Token**
    - `$user->revokeRefreshToken()` set refresh_token = NULL di database
    - Prevent user refresh token untuk mendapatkan access token baru

4. **Return Success**
    - Konfirmasi logout berhasil

---

## Flutter Implementation

### 1. Create Logout Service

**File:** `lib/services/auth_service.dart`

```dart
import 'package:shared_preferences/shared_preferences.dart';
import 'api_client.dart';

class AuthService {
  final ApiClient _apiClient;

  AuthService(this._apiClient);

  /// Logout current user
  Future<bool> logout() async {
    try {
      // Step 1: Call backend logout API
      final response = await _apiClient.post('/auth/logout');

      if (response.statusCode == 200) {
        // Step 2: Clear local storage
        await clearLocalStorage();

        print('✅ Logout successful');
        return true;
      } else {
        print('❌ Logout failed: ${response.body}');

        // Even if API fails, clear local storage
        await clearLocalStorage();
        return false;
      }
    } catch (e) {
      print('❌ Logout error: $e');

      // Even if network error, clear local storage
      await clearLocalStorage();
      return false;
    }
  }

  /// Clear all stored tokens and user data
  Future<void> clearLocalStorage() async {
    final prefs = await SharedPreferences.getInstance();

    // Remove tokens
    await prefs.remove('access_token');
    await prefs.remove('refresh_token');
    await prefs.remove('token_expire_at');

    // Remove user data
    await prefs.remove('user_id');
    await prefs.remove('user_email');
    await prefs.remove('user_name');
    await prefs.remove('user_phone');

    // Remove preferences
    await prefs.remove('primary_vehicle_id');
    await prefs.remove('fcm_token');

    print('🗑️ Local storage cleared');
  }

  /// Check if user is logged in
  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final accessToken = prefs.getString('access_token');
    return accessToken != null && accessToken.isNotEmpty;
  }
}
```

### 2. Update ApiClient for Logout

**File:** `lib/services/api_client.dart`

```dart
class ApiClient {
  final String baseUrl = 'http://localhost:8000/api/v1/motorcycle';

  /// POST request with auto token injection
  Future<http.Response> post(
    String endpoint, {
    Map<String, dynamic>? body,
    bool requiresAuth = true,
  }) async {
    final url = Uri.parse('$baseUrl$endpoint');
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (requiresAuth) {
      final token = await _getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }
    }

    final response = await http.post(
      url,
      headers: headers,
      body: body != null ? json.encode(body) : null,
    );

    // Don't auto-refresh on logout endpoint
    if (response.statusCode == 401 && endpoint != '/auth/logout') {
      return await _handleTokenExpired(endpoint, 'POST', body: body);
    }

    return response;
  }

  Future<String?> _getAccessToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('access_token');
  }
}
```

### 3. UI Implementation - Logout Button

**File:** `lib/screens/profile_screen.dart`

```dart
import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'login_screen.dart';

class ProfileScreen extends StatelessWidget {
  final AuthService _authService = AuthService(ApiClient());

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Profile'),
      ),
      body: ListView(
        children: [
          // ... other profile items ...

          // Logout Button
          ListTile(
            leading: Icon(Icons.logout, color: Colors.red),
            title: Text(
              'Logout',
              style: TextStyle(
                color: Colors.red,
                fontWeight: FontWeight.bold,
              ),
            ),
            onTap: () => _showLogoutDialog(context),
          ),
        ],
      ),
    );
  }

  /// Show confirmation dialog before logout
  void _showLogoutDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (BuildContext dialogContext) {
        return AlertDialog(
          title: Text('Logout'),
          content: Text('Apakah Anda yakin ingin keluar?'),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
              child: Text('Batal'),
            ),
            TextButton(
              onPressed: () async {
                Navigator.pop(dialogContext); // Close dialog
                await _performLogout(context);
              },
              style: TextButton.styleFrom(
                foregroundColor: Colors.red,
              ),
              child: Text('Logout'),
            ),
          ],
        );
      },
    );
  }

  /// Perform logout action
  Future<void> _performLogout(BuildContext context) async {
    // Show loading indicator
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return Center(
          child: CircularProgressIndicator(),
        );
      },
    );

    // Perform logout
    final success = await _authService.logout();

    // Hide loading
    Navigator.pop(context);

    // Navigate to login screen
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (context) => LoginScreen()),
      (route) => false, // Remove all previous routes
    );

    // Show feedback
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          success
            ? '✅ Logout berhasil'
            : '⚠️ Logout berhasil (offline)',
        ),
        duration: Duration(seconds: 2),
      ),
    );
  }
}
```

### 4. Update Main.dart - Check Login Status

**File:** `lib/main.dart`

```dart
import 'package:flutter/material.dart';
import 'services/auth_service.dart';
import 'screens/login_screen.dart';
import 'screens/home_screen.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Motorcycle Management',
      theme: ThemeData(
        primarySwatch: Colors.blue,
      ),
      home: SplashScreen(),
    );
  }
}

class SplashScreen extends StatefulWidget {
  @override
  _SplashScreenState createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  final AuthService _authService = AuthService(ApiClient());

  @override
  void initState() {
    super.initState();
    _checkLoginStatus();
  }

  Future<void> _checkLoginStatus() async {
    // Simulate splash screen delay
    await Future.delayed(Duration(seconds: 2));

    // Check if user is logged in
    final isLoggedIn = await _authService.isLoggedIn();

    // Navigate to appropriate screen
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (context) => isLoggedIn ? HomeScreen() : LoginScreen(),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.motorcycle, size: 100, color: Colors.blue),
            SizedBox(height: 20),
            CircularProgressIndicator(),
          ],
        ),
      ),
    );
  }
}
```

---

## Logout Flow Diagram

```
┌─────────────┐
│   User      │
│  Tap Logout │
└──────┬──────┘
       │
       ▼
┌─────────────────────┐
│ Show Confirmation   │
│     Dialog          │
└──────┬──────────────┘
       │
       ▼
┌─────────────────────┐
│  User Confirms      │
│  "Logout"           │
└──────┬──────────────┘
       │
       ▼
┌─────────────────────────────────┐
│  Flutter: Show Loading          │
└──────┬──────────────────────────┘
       │
       ▼
┌─────────────────────────────────┐
│  Call Backend API:              │
│  POST /auth/logout              │
│  Headers: Bearer {access_token} │
└──────┬──────────────────────────┘
       │
       ▼
┌─────────────────────────────────┐
│  Backend: Validate Token        │
└──────┬──────────────────────────┘
       │
       ├─── Token Valid ─────────┐
       │                         │
       ▼                         ▼
┌─────────────────────┐   ┌─────────────────────┐
│ Revoke all access   │   │ Return 401          │
│ tokens (Sanctum)    │   │ Unauthenticated     │
└──────┬──────────────┘   └──────┬──────────────┘
       │                         │
       ▼                         │
┌─────────────────────┐          │
│ Revoke refresh token│          │
│ (set NULL in DB)    │          │
└──────┬──────────────┘          │
       │                         │
       ▼                         │
┌─────────────────────┐          │
│ Return 200 OK       │          │
└──────┬──────────────┘          │
       │                         │
       └────────┬────────────────┘
                │
                ▼
┌─────────────────────────────────┐
│  Flutter: Clear Local Storage   │
│  - access_token                 │
│  - refresh_token                │
│  - user data                    │
│  - preferences                  │
└──────┬──────────────────────────┘
       │
       ▼
┌─────────────────────────────────┐
│  Navigate to Login Screen       │
│  (Remove all previous routes)   │
└──────┬──────────────────────────┘
       │
       ▼
┌─────────────────────────────────┐
│  Show Success Message           │
│  User must login again          │
└─────────────────────────────────┘
```

---

## Important Considerations

### 1. **Offline Logout**

Jika logout dipanggil saat offline (no internet):

```dart
try {
  await _apiClient.post('/auth/logout');
} catch (e) {
  // Network error - still clear local storage
  await clearLocalStorage();
}
```

**Why?** User tetap bisa logout dari aplikasi meski backend tidak terpanggil. Token akan expired eventually (90 hari).

### 2. **Token Already Expired**

Jika user logout tapi access token sudah expired:

```dart
if (response.statusCode == 401) {
  // Token expired - still clear local storage
  await clearLocalStorage();
  return false; // Logout "failed" di backend tapi local tetap clear
}
```

### 3. **Logout All Devices**

Current implementation: `$user->tokens()->delete()` logout **semua device**.

Untuk logout **current device only**, modifikasi backend:

```php
// Logout current device only
$request->user()->currentAccessToken()->delete();
$user->revokeRefreshToken(); // Optional: keep or remove
```

### 4. **Prevent Auto-Refresh During Logout**

Di ApiClient, pastikan jangan auto-refresh token saat logout:

```dart
if (response.statusCode == 401 && endpoint != '/auth/logout') {
  return await _handleTokenExpired(...);
}
```

**Why?** Kita ingin logout, bukan refresh token lagi.

---

## Security Best Practices

### 1. **Always Revoke Tokens**

```php
// ✅ CORRECT: Revoke both access & refresh tokens
$user->tokens()->delete();
$user->revokeRefreshToken();

// ❌ WRONG: Only clear local storage (tokens still valid)
// Just clearing Flutter storage without calling API
```

### 2. **Clear Local Storage Regardless**

```dart
// ✅ CORRECT: Clear even if API fails
try {
  await logout();
} finally {
  await clearLocalStorage();
}

// ❌ WRONG: Don't clear if API fails
if (success) {
  await clearLocalStorage();
}
```

### 3. **Remove All Navigation Stack**

```dart
// ✅ CORRECT: Remove all previous routes
Navigator.pushAndRemoveUntil(
  context,
  MaterialPageRoute(builder: (context) => LoginScreen()),
  (route) => false,
);

// ❌ WRONG: User can press back to access protected screens
Navigator.push(
  context,
  MaterialPageRoute(builder: (context) => LoginScreen()),
);
```

---

## Testing Guide

### Backend Testing (Postman)

**1. Login First:**

```http
POST /api/v1/motorcycle/auth/login
Content-Type: application/json

{
    "email": "test@example.com",
    "password": "password123"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "access_token": "1|xyz...",
        "refresh_token": "abc123..."
    }
}
```

**2. Copy Access Token to Authorization**

**3. Test Logout:**

```http
POST /api/v1/motorcycle/auth/logout
Authorization: Bearer 1|xyz...
```

**Expected Response:**

```json
{
    "success": true,
    "message": "Logout successful",
    "data": null
}
```

**4. Verify Token Revoked:**

```http
GET /api/v1/motorcycle/vehicles/primary
Authorization: Bearer 1|xyz...
```

**Expected Response:**

```json
{
    "message": "Unauthenticated."
}
```

**5. Verify Refresh Token Revoked:**

```http
POST /api/v1/motorcycle/auth/refresh
Content-Type: application/json

{
    "refresh_token": "abc123..."
}
```

**Expected Response:**

```json
{
    "success": false,
    "message": "Invalid refresh token"
}
```

### Flutter Testing

**Test Case 1: Normal Logout**

```
1. Login dengan test@example.com
2. Navigate ke Profile screen
3. Tap Logout button
4. Konfirmasi dialog
5. ✅ Verify: Navigated to Login screen
6. ✅ Verify: Local storage cleared
7. ✅ Verify: Cannot access protected screens
```

**Test Case 2: Logout Offline**

```
1. Login dengan test@example.com
2. Turn off internet/wifi
3. Navigate ke Profile screen
4. Tap Logout button
5. Konfirmasi dialog
6. ✅ Verify: Navigated to Login screen (even offline)
7. ✅ Verify: Local storage cleared
8. ✅ Verify: Show message "Logout berhasil (offline)"
```

**Test Case 3: Logout with Expired Token**

```
1. Login dengan test@example.com
2. Wait 30 minutes (access token expired)
3. Navigate ke Profile screen
4. Tap Logout button
5. ✅ Verify: Still logout successfully
6. ✅ Verify: Local storage cleared
```

**Test Case 4: Auto-Login Prevention After Logout**

```
1. Login dengan test@example.com
2. Logout
3. Close app
4. Reopen app
5. ✅ Verify: Shows Login screen (not auto-login)
6. ✅ Verify: No tokens in local storage
```

---

## Error Handling

### Common Errors

| Error               | Cause                    | Solution                                        |
| ------------------- | ------------------------ | ----------------------------------------------- |
| 401 Unauthenticated | Token expired or invalid | Still clear local storage, redirect to login    |
| 500 Server Error    | Backend exception        | Still clear local storage, show error message   |
| Network Error       | No internet connection   | Still clear local storage, show offline message |
| Timeout             | Slow connection          | Retry or clear local storage after timeout      |

### Error Response Examples

**401 - Token Expired:**

```json
{
    "success": false,
    "message": "Unauthenticated",
    "data": null
}
```

**500 - Server Error:**

```json
{
    "success": false,
    "message": "Logout failed",
    "data": "Database connection error"
}
```

---

## Integration with Persistent Login

### Token Lifecycle

```
┌──────────────────────────────────────────────────────────┐
│                    TOKEN LIFECYCLE                       │
└──────────────────────────────────────────────────────────┘

1. LOGIN
   ├── Generate access_token (30 min)
   ├── Generate refresh_token (90 days)
   └── Save to local storage

2. NORMAL USE (within 30 minutes)
   ├── Use access_token for API calls
   └── Success response

3. ACCESS TOKEN EXPIRED (after 30 min)
   ├── API returns 401
   ├── Auto call refresh endpoint
   ├── Get new access_token (30 min)
   ├── Keep same refresh_token (90 days)
   └── Retry original request

4. REFRESH TOKEN EXPIRED (after 90 days)
   ├── Refresh endpoint returns 401
   ├── Clear local storage
   └── Redirect to Login screen

5. LOGOUT
   ├── Call logout API
   ├── Revoke all access_tokens
   ├── Revoke refresh_token
   ├── Clear local storage
   └── Redirect to Login screen
```

### Persistent Login + Logout = Perfect Balance

| Feature            | Persistent Login               | Logout                       |
| ------------------ | ------------------------------ | ---------------------------- |
| **Goal**           | Keep user logged in 90 days    | Remove all session data      |
| **Benefit**        | Convenience, no frequent login | Security, manual session end |
| **Implementation** | Auto-refresh tokens            | Revoke all tokens            |
| **User Action**    | None (automatic)               | Manual tap logout            |

**Best of both worlds:**

- User stays logged in for 90 days (convenience)
- User can manually logout anytime (security)
- Tokens auto-refresh silently (seamless UX)
- Logout completely clears session (clean exit)

---

## Complete Code Example

### auth_service.dart (Complete)

```dart
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'api_client.dart';

class AuthService {
  final ApiClient _apiClient;

  AuthService(this._apiClient);

  /// Login user
  Future<Map<String, dynamic>?> login(String email, String password) async {
    try {
      final response = await _apiClient.post(
        '/auth/login',
        body: {'email': email, 'password': password},
        requiresAuth: false,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final tokens = data['data'];

        // Save tokens to local storage
        await saveTokens(
          accessToken: tokens['access_token'],
          refreshToken: tokens['refresh_token'],
        );

        // Save user data
        await saveUserData(tokens['user']);

        return tokens;
      }

      return null;
    } catch (e) {
      print('Login error: $e');
      return null;
    }
  }

  /// Logout user
  Future<bool> logout() async {
    try {
      final response = await _apiClient.post('/auth/logout');

      if (response.statusCode == 200) {
        await clearLocalStorage();
        print('✅ Logout successful');
        return true;
      } else {
        await clearLocalStorage();
        print('❌ Logout failed: ${response.body}');
        return false;
      }
    } catch (e) {
      await clearLocalStorage();
      print('❌ Logout error: $e');
      return false;
    }
  }

  /// Refresh access token
  Future<bool> refreshToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final refreshToken = prefs.getString('refresh_token');

      if (refreshToken == null) return false;

      final response = await _apiClient.post(
        '/auth/refresh',
        body: {'refresh_token': refreshToken},
        requiresAuth: false,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final tokens = data['data'];

        await saveTokens(
          accessToken: tokens['access_token'],
          refreshToken: tokens['refresh_token'],
        );

        return true;
      }

      return false;
    } catch (e) {
      print('Refresh error: $e');
      return false;
    }
  }

  /// Save tokens to local storage
  Future<void> saveTokens({
    required String accessToken,
    required String refreshToken,
  }) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('access_token', accessToken);
    await prefs.setString('refresh_token', refreshToken);

    final expireAt = DateTime.now().add(Duration(minutes: 30));
    await prefs.setString('token_expire_at', expireAt.toIso8601String());
  }

  /// Save user data to local storage
  Future<void> saveUserData(Map<String, dynamic> user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('user_id', user['id']);
    await prefs.setString('user_email', user['email']);
    await prefs.setString('user_name', user['name']);
    if (user['phone'] != null) {
      await prefs.setString('user_phone', user['phone']);
    }
  }

  /// Clear all local storage
  Future<void> clearLocalStorage() async {
    final prefs = await SharedPreferences.getInstance();

    // Remove tokens
    await prefs.remove('access_token');
    await prefs.remove('refresh_token');
    await prefs.remove('token_expire_at');

    // Remove user data
    await prefs.remove('user_id');
    await prefs.remove('user_email');
    await prefs.remove('user_name');
    await prefs.remove('user_phone');

    // Remove preferences
    await prefs.remove('primary_vehicle_id');
    await prefs.remove('fcm_token');

    print('🗑️ Local storage cleared');
  }

  /// Check if user is logged in
  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final accessToken = prefs.getString('access_token');
    return accessToken != null && accessToken.isNotEmpty;
  }

  /// Get current user data from local storage
  Future<Map<String, dynamic>?> getCurrentUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userId = prefs.getInt('user_id');

    if (userId == null) return null;

    return {
      'id': userId,
      'email': prefs.getString('user_email'),
      'name': prefs.getString('user_name'),
      'phone': prefs.getString('user_phone'),
    };
  }
}
```

---

## Troubleshooting

### Issue 1: Logout API returns 401

**Cause:** Access token already expired

**Solution:**

```dart
// Don't retry with refresh, just clear local storage
if (response.statusCode == 401) {
  await clearLocalStorage();
  return false; // Logged out locally even if API failed
}
```

### Issue 2: User can still access app after logout

**Cause:** Local storage not cleared or navigation stack not removed

**Solution:**

```dart
// Clear storage
await clearLocalStorage();

// Remove all navigation stack
Navigator.pushAndRemoveUntil(
  context,
  MaterialPageRoute(builder: (context) => LoginScreen()),
  (route) => false,
);
```

### Issue 3: Tokens still valid after logout

**Cause:** Backend not revoking tokens properly

**Solution:**

```php
// Make sure both methods are called
$user->tokens()->delete();        // Revoke access tokens
$user->revokeRefreshToken();      // Revoke refresh token
```

### Issue 4: Auto-refresh triggered during logout

**Cause:** ApiClient trying to refresh 401 response from logout

**Solution:**

```dart
// Exclude logout endpoint from auto-refresh
if (response.statusCode == 401 && endpoint != '/auth/logout') {
  return await _handleTokenExpired(...);
}
```

---

## Summary

### ✅ Checklist Implementasi

**Backend:**

- ✅ Logout endpoint available: `POST /auth/logout`
- ✅ Revoke all access tokens: `$user->tokens()->delete()`
- ✅ Revoke refresh token: `$user->revokeRefreshToken()`
- ✅ Return success response

**Flutter:**

- ✅ Create AuthService with logout method
- ✅ Call logout API with Bearer token
- ✅ Clear all local storage (tokens + user data)
- ✅ Navigate to Login screen (remove all routes)
- ✅ Show success/error message
- ✅ Handle offline logout
- ✅ Prevent auto-refresh during logout

**Testing:**

- ✅ Test normal logout flow
- ✅ Test offline logout
- ✅ Test logout with expired token
- ✅ Verify tokens revoked in backend
- ✅ Verify local storage cleared
- ✅ Verify cannot access protected screens

---

## Related Documentation

- [PERSISTENT_LOGIN_GUIDE.md](PERSISTENT_LOGIN_GUIDE.md) - Main persistent login implementation
- [POSTMAN_COLLECTION_UPDATE.md](POSTMAN_COLLECTION_UPDATE.md) - API testing guide
- [MIGRATION_CHANGELOG.md](MIGRATION_CHANGELOG.md) - Backend migration details

---

**✅ Logout implementation siap diintegrasikan dengan persistent login system!**

_Last updated: February 21, 2026_

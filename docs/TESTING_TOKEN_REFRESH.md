# 🧪 End-to-End Testing Plan: Token Refresh Fix

**Purpose**: Validate that token rotation & refresh mechanism works correctly  
**Duration**: ~1 hour  
**Tester**: QA Team  
**Status**: READY TO EXECUTE

---

## 📋 Pre-Test Checklist

**Before starting tests**, verify:

- [ ] Backend team finished implementation
- [ ] Database schema updated (revoked, revoked_at columns)
- [ ] Cleanup task scheduled
- [ ] Rate limiting added
- [ ] Cache cleared (`php artisan cache:clear`)
- [ ] Laravel logs accessible

**Environment**:

- Backend: `http://10.0.2.2:8000` (Android) or `http://localhost:8000` (iOS)
- API Version: `/api/v1/motorcycle`
- Testing tool: Postman atau curl

---

## 🧪 TEST SUITE 1: Backend API Tests

### Test 1.1: Fresh Login

**Objective**: Verify login endpoint returns valid tokens

**Steps**:

1. Open Postman / Terminal
2. Send POST request to `/auth/login`

**Request**:

```bash
POST /api/v1/motorcycle/auth/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "password123"
}
```

**Expected Response** (200 OK):

```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 5,
            "email": "test@example.com",
            "name": "Test User"
        },
        "access_token": "abc123def456...", // ~50 chars
        "refresh_token": "xyz789uvw012...", // ~128 chars
        "token_type": "Bearer",
        "expires_in": 1800 // 30 minutes
    }
}
```

**Verify**:

- ✅ Status code 200
- ✅ Both tokens returned
- ✅ Access token shorter than refresh token
- ✅ expires_in = 1800 (30 minutes)

**Save for next tests**:

- `access_token` → Save as `$ACCESS_TOKEN`
- `refresh_token` → Save as `$REFRESH_TOKEN_1`

---

### Test 1.2: First Token Refresh

**Objective**: Verify refresh endpoint returns NEW tokens and revokes old one

**Steps**:

1. Use `$REFRESH_TOKEN_1` from Test 1.1
2. Send refresh request

**Request**:

```bash
POST /api/v1/motorcycle/auth/refresh-token
Content-Type: application/json

{
  "refresh_token": "$REFRESH_TOKEN_1"
}
```

**Expected Response** (200 OK):

```json
{
    "success": true,
    "message": "Token berhasil diperbaharui",
    "data": {
        "access_token": "new_abc123def456...",
        "refresh_token": "new_xyz789uvw012...",
        "token_type": "Bearer",
        "expires_in": 1800
    }
}
```

**Verify**:

- ✅ Status code 200
- ✅ NEW access_token (different from original)
- ✅ NEW refresh_token (different from original)
- ✅ expires_in = 1800

**Important**:

- ❌ Do NOT use old tokens
- ✅ Use NEW tokens from this response

**Save for next tests**:

- `refresh_token` → Save as `$REFRESH_TOKEN_2`

---

### Test 1.3: Try Using OLD Refresh Token

**Objective**: Verify old token is revoked and cannot be reused

**Steps**:

1. Try to refresh with `$REFRESH_TOKEN_1` (the OLD token)
2. Should FAIL

**Request**:

```bash
POST /api/v1/motorcycle/auth/refresh-token
Content-Type: application/json

{
  "refresh_token": "$REFRESH_TOKEN_1"  # OLD token!
}
```

**Expected Response** (401 Unauthorized):

```json
{
    "success": false,
    "message": "Refresh token tidak valid. Silakan login kembali."
}
```

**Verify**:

- ✅ Status code 401 (NOT 200)
- ✅ Error message about invalid/expired token
- ❌ No new tokens returned

**This confirms**: ✅ Old token properly revoked!

---

### Test 1.4: Refresh with NEW Token (From Test 1.2)

**Objective**: Verify NEW token from Test 1.2 works

**Steps**:

1. Use `$REFRESH_TOKEN_2` from Test 1.2
2. Send refresh request

**Request**:

```bash
POST /api/v1/motorcycle/auth/refresh-token
Content-Type: application/json

{
  "refresh_token": "$REFRESH_TOKEN_2"  # NEW token from Test 1.2
}
```

**Expected Response** (200 OK):

```json
{
    "success": true,
    "data": {
        "access_token": "another_new_abc123...",
        "refresh_token": "another_new_xyz789...",
        "expires_in": 1800
    }
}
```

**Verify**:

- ✅ Status code 200
- ✅ Returns YET ANOTHER new token pair
- ✅ Different from all previous tokens

**This confirms**: ✅ Token rotation working!

---

### Test 1.5: Invalid Token Handling

**Objective**: Verify error handling for invalid/malformed tokens

**Request** (with garbage token):

```bash
POST /api/v1/motorcycle/auth/refresh-token
Content-Type: application/json

{
  "refresh_token": "invalid_garbage_token_12345"
}
```

**Expected Response** (401 Unauthorized):

```json
{
    "success": false,
    "message": "Refresh token tidak valid. Silakan login kembali."
}
```

**Verify**:

- ✅ Status code 401 (not 500)
- ✅ Graceful error handling

---

### Test 1.6: Missing Refresh Token

**Objective**: Verify validation

**Request** (missing refresh_token):

```bash
POST /api/v1/motorcycle/auth/refresh-token
Content-Type: application/json

{}
```

**Expected Response** (422 Unprocessable Entity):

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "refresh_token": ["The refresh_token field is required."]
    }
}
```

**Verify**:

- ✅ Status code 422
- ✅ Clear validation error message

---

### Test 1.7: Rate Limiting Test

**Objective**: Verify rate limiting works (if implemented)

**Steps**:

1. Send 15 refresh requests in quick succession
2. After 10th request, should be rate limited

**Expected** (11th request and beyond):

```
Status: 429 Too Many Requests
Message: Too many requests. Please try again in X seconds.
```

**Verify**:

- ✅ First 10 requests: 200 or 401 (depending on token validity)
- ✅ 11th+ requests: 429 (rate limited)

---

## 🧪 TEST SUITE 2: Database Verification

### Test 2.1: Token Revocation in Database

**Objective**: Verify tokens are properly marked as revoked

**Queries**:

```sql
-- 1. Check if old tokens are revoked
SELECT id, user_id, revoked, revoked_at, created_at
FROM tokens
WHERE user_id = 5
ORDER BY created_at DESC
LIMIT 10;

-- Expected pattern:
-- id=100: revoked=0, revoked_at=NULL, created_at=2026-05-15 14:00:00 (CURRENT)
-- id=99:  revoked=1, revoked_at=2026-05-15 14:05:00 (OLD, revoked)
-- id=98:  revoked=1, revoked_at=2026-05-15 14:10:00 (OLDER, revoked)

-- 2. Count active vs revoked tokens
SELECT
  revoked,
  COUNT(*) as count
FROM tokens
WHERE user_id = 5
GROUP BY revoked;

-- Expected:
-- revoked=0: 1 (latest active token)
-- revoked=1: many (old revoked tokens)

-- 3. Verify no active expired tokens
SELECT COUNT(*) as expired_active_count
FROM tokens
WHERE revoked = FALSE
AND expires_at < NOW();

-- Expected: 0
```

**Verify in Database**:

- ✅ Old tokens marked `revoked=true`
- ✅ Old tokens have `revoked_at` timestamp
- ✅ Only latest token is `revoked=false`
- ✅ No expired active tokens

---

### Test 2.2: Index Performance

**Objective**: Verify indexes are working

**Query**:

```sql
-- Check if indexes exist
SHOW INDEX FROM tokens;

-- Should see these indexes:
-- - PRIMARY (id)
-- - idx_refresh_token_revoked
-- - idx_expires_at
```

**Verify**:

- ✅ All indexes exist
- ✅ Cardinality > 0 (shows they're being used)

---

## 🧪 TEST SUITE 3: Mobile App Testing

### Test 3.1: Fresh Login on Mobile

**Device**: iOS/Android device or emulator  
**App**: Flutter Motorcycle Management app

**Steps**:

1. Force logout from app (clear all data if needed)
2. Go to login screen
3. Enter credentials: `test@example.com` / `password123`
4. Tap Login

**Expected**:

- ✅ Login successful
- ✅ Redirected to dashboard
- ✅ No 401 errors in logs
- ✅ Tokens stored securely

**Check Logs** (if debugging):

```
I/flutter: ✅ Login successful!
I/flutter: Access token: abc123... (length: 50)
I/flutter: Refresh token: xyz789... (length: 128)
I/flutter: Tokens saved to secure storage
```

---

### Test 3.2: Use App for 30+ Minutes

**Objective**: Trigger automatic token refresh

**Steps**:

1. Keep app open
2. Use features normally (trips, profile, etc.)
3. Let app run for at least 30 minutes
4. Trigger API request after 30 mins

**Expected**:

- ✅ Access token expires after 30 minutes
- ✅ App automatically requests refresh
- ✅ New tokens received
- ✅ API request succeeds with new token
- ✅ No 401 errors
- ✅ User doesn't notice anything

**Check Logs** (around 30 min mark):

```
I/flutter: 📡 Access token about to expire, refreshing...
I/flutter: POST /api/v1/motorcycle/auth/refresh-token
I/flutter: ✅ Token refresh successful!
I/flutter: New access token: new_abc123...
I/flutter: New refresh token: new_xyz789...
I/flutter: Retrying original request...
I/flutter: ✅ API request succeeded
```

**❌ Should NOT see**:

```
I/flutter: ❌ 401 Unauthenticated
I/flutter: ❌ Refresh token tidak valid
I/flutter: ❌ User tidak ditemukan
```

---

### Test 3.3: App in Background

**Objective**: Verify refresh works when app is minimized

**Steps**:

1. Open app, login successfully
2. Minimize app (go to home screen)
3. Wait 30 minutes
4. Bring app back to foreground
5. Trigger an API request

**Expected**:

- ✅ App automatically refreshes token while in background
- ✅ Request succeeds with new token
- ✅ No 401 errors

---

### Test 3.4: App Close & Reopen

**Objective**: Verify persistent login works

**Steps**:

1. Login successfully
2. Close app completely (swipe away)
3. Wait 5 minutes
4. Reopen app

**Expected**:

- ✅ App loads previously stored tokens from secure storage
- ✅ Directly goes to dashboard (no login screen)
- ✅ API requests work immediately
- ✅ No "Unauthenticated" errors

**Verify Logs**:

```
I/flutter: 🚀 App started
I/flutter: ✅ Tokens found in secure storage
I/flutter: User ID: 5
I/flutter: Attempting to load user profile...
I/flutter: ✅ Dashboard loaded successfully
```

---

### Test 3.5: Logout & Re-login

**Objective**: Verify tokens are cleared and new session starts

**Steps**:

1. Open app (logged in)
2. Go to Settings → Logout
3. Confirm logout
4. Login again with same credentials

**Expected**:

- ✅ Logout successful
- ✅ Redirected to login screen
- ✅ All tokens cleared from storage
- ✅ Can login again
- ✅ Receive new tokens
- ✅ No conflicts with old tokens

---

## 📊 TEST SUITE 4: Log Monitoring

### Test 4.1: Monitor Token Operations

**Command** (in terminal):

```bash
cd c:\laragon\www\motorcycle_management
tail -f storage/logs/laravel.log | grep -E "(refresh|token|401)"
```

**While tests running, should see**:

**For successful refresh**:

```
[2026-05-15 14:30:00] local.INFO: Token refresh attempt {"token_hash":"ed5c842...","ip":"127.0.0.1"}
[2026-05-15 14:30:01] local.INFO: Old token revoked {"user_id":5,"token_id":100}
[2026-05-15 14:30:01] local.INFO: Token refreshed successfully {"user_id":5,"old_token_revoked":true}
```

**For failed refresh**:

```
[2026-05-15 14:30:10] local.WARNING: Refresh token invalid/expired/revoked {"token_hash":"abc123...","possible_cause":"token_not_found_or_revoked"}
```

**Verify**:

- ✅ All token operations logged
- ✅ No ERROR level messages (only INFO/WARNING)
- ✅ Each refresh shows revocation happening
- ✅ Timestamps make sense

---

### Test 4.2: Check for 401 Errors

**Command**:

```bash
tail -f storage/logs/laravel.log | grep "401"
```

**Expected**:

- ❌ ZERO 401 errors during normal usage
- ✅ Only expected 401 when using revoked/invalid tokens (Test 1.3)

**If seeing many 401s**:

```
⚠️  Problem detected!
- Check if token validation is too strict
- Check if expiry time is too short
- Check if database connection issues
```

---

## 📈 Test Results Template

**Use this to track test results**:

```
TEST EXECUTION SUMMARY
======================

Date: May 15, 2026
Tester: [Name]
Duration: [X minutes]
Environment: [Staging/Production]

TEST SUITE 1: Backend API Tests
  Test 1.1 (Fresh Login): ✅ PASS
  Test 1.2 (First Refresh): ✅ PASS
  Test 1.3 (Old Token Revoked): ✅ PASS
  Test 1.4 (New Token Works): ✅ PASS
  Test 1.5 (Invalid Token): ✅ PASS
  Test 1.6 (Missing Token): ✅ PASS
  Test 1.7 (Rate Limiting): ✅ PASS (or N/A)

TEST SUITE 2: Database Verification
  Test 2.1 (Token Revocation): ✅ PASS
  Test 2.2 (Index Performance): ✅ PASS

TEST SUITE 3: Mobile App Testing
  Test 3.1 (Fresh Login): ✅ PASS
  Test 3.2 (30 Min Auto-Refresh): ✅ PASS
  Test 3.3 (Background Refresh): ✅ PASS
  Test 3.4 (Persistent Login): ✅ PASS
  Test 3.5 (Logout & Re-login): ✅ PASS

TEST SUITE 4: Log Monitoring
  Test 4.1 (Token Logs): ✅ PASS
  Test 4.2 (No 401 Errors): ✅ PASS

OVERALL RESULT: ✅ ALL TESTS PASSED

Issues Found: None
Recommendations: [Any improvements noted]
Next Steps: Ready for production deployment
```

---

## ⚠️ FAILURE SCENARIOS

**If any test fails**, follow troubleshooting:

### Issue: Test 1.3 FAILS (Old token still works)

**Possible Causes**:

1. Revocation logic not implemented
2. Validation not checking `revoked` column
3. Code not deployed

**Debug**:

```sql
-- Check if old token is marked revoked
SELECT * FROM tokens WHERE id = [old_token_id];
-- Should show: revoked = 1

-- Check refresh endpoint code
cat app/Http/Controllers/Api/AuthController.php | grep -A 20 "refreshToken"
-- Should show: where revoked, false
```

---

### Issue: Test 3.2 FAILS (Manual refresh shows 401)

**Possible Causes**:

1. Token validation too strict
2. Token expiration time wrong
3. Database time sync issue

**Debug**:

```bash
# Check server time
date

# Check token expiry in database
mysql > SELECT NOW(), expires_at FROM tokens ORDER BY created_at DESC LIMIT 1;

# Should be: expires_at > NOW()
```

---

### Issue: Test 3.4 FAILS (Cannot reopen app)

**Possible Causes**:

1. Tokens not properly saved to secure storage
2. FlutterSecureStorage not initialized
3. Tokens corrupted in storage

**Debug**:

```dart
// Check if tokens exist
final accessToken = await authStorage.getAccessToken();
final refreshToken = await authStorage.getRefreshToken();

print('Access token: ${accessToken ?? "NULL"}');
print('Refresh token: ${refreshToken ?? "NULL"}');

// If NULL, tokens weren't saved
```

---

## ✅ Sign-Off Criteria

**Before marking tests COMPLETE**:

- [ ] All 16 tests executed
- [ ] Test results template filled
- [ ] 0 critical failures
- [ ] All 401 errors are EXPECTED (revoked tokens only)
- [ ] Logs show proper token rotation
- [ ] Mobile app works without issues
- [ ] No database errors in logs

**If all above checked**: ✅ **READY FOR PRODUCTION**

---

## 📞 Escalation Path

**If > 2 tests fail**:

1. Stop testing
2. Review implementation with backend team
3. Check database schema
4. Review code changes
5. Re-run failed tests
6. Escalate to tech lead if still failing

---

**Test Owner**: QA Team  
**Backend Owner**: Backend Team (for fixing)  
**Timeline**: ~1 hour  
**Next Step**: Production deployment (if all tests pass)

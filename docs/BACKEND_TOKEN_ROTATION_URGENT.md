# 🚨 URGENT: Token Rotation Implementation - Backend Team

**Status**: HIGH PRIORITY | **Timeline**: 1-2 hours | **Deadline**: TODAY  
**Date**: May 15, 2026 | **Target**: Production Deployment

---

## 📢 EXECUTIVE SUMMARY

**CRITICAL ISSUE**: Mobile users stuck in 401 loop saat token refresh, tidak bisa continue session.

**ROOT CAUSE**:

- Refresh token tidak di-rotate → old token tetap valid setelah refresh
- Tidak ada revocation mechanism
- Validasi token tidak robust

**IMMEDIATE SOLUTION**: Token rotation + revocation + logging (1-2 hours implementation)

**IMPACT**:

- ❌ Users unable to continue session (must login again)
- ❌ Bad user experience, frustration
- ✅ FIX akan solve 80% token issues

---

## 🎯 WHAT TO DO RIGHT NOW

### PHASE 1: Quick Fixes (1 hour) - DO THIS FIRST

#### Step 1: Database Schema (10 mins)

**Run these SQL commands**:

```sql
-- Add missing columns if not exist
ALTER TABLE tokens ADD COLUMN IF NOT EXISTS revoked BOOLEAN DEFAULT FALSE;
ALTER TABLE tokens ADD COLUMN IF NOT EXISTS revoked_at TIMESTAMP NULL;

-- Add indexes untuk faster queries
CREATE INDEX IF NOT EXISTS idx_refresh_token_revoked
ON tokens(refresh_token, revoked);

CREATE INDEX IF NOT EXISTS idx_expires_at
ON tokens(expires_at);

-- Verify columns exist
DESCRIBE tokens;
```

#### Step 2: Implement Token Revocation (30 mins)

**File**: `app/Http/Controllers/Api/AuthController.php`

**Find this method**: `refreshToken(Request $request)`

**Replace the entire method with**:

```php
/**
 * Refresh access token using refresh token
 */
public function refreshToken(Request $request)
{
    // Validate request
    $request->validate([
        'refresh_token' => 'required|string',
    ]);

    $oldRefreshToken = $request->input('refresh_token');

    \Log::info('Token refresh attempt', [
        'token_hash' => hash('sha256', $oldRefreshToken),
        'ip' => $request->ip(),
        'timestamp' => now()
    ]);

    try {
        // Find user by refresh token
        $tokenRecord = DB::table('tokens')
            ->where('refresh_token', $oldRefreshToken)
            ->where('revoked', false)  // ✅ Only non-revoked
            ->where('expires_at', '>', now())  // ✅ Only non-expired
            ->first();

        if (!$tokenRecord) {
            \Log::warning('Refresh token invalid/expired/revoked', [
                'token_hash' => hash('sha256', $oldRefreshToken),
                'possible_cause' => 'token_not_found_or_revoked'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Refresh token tidak valid. Silakan login kembali.'
            ], 401);
        }

        // ✅ CRITICAL: Revoke old token BEFORE generating new ones
        DB::table('tokens')
            ->where('id', $tokenRecord->id)
            ->update([
                'revoked' => true,
                'revoked_at' => now(),
            ]);

        \Log::info('Old token revoked', [
            'user_id' => $tokenRecord->user_id,
            'token_id' => $tokenRecord->id
        ]);

        // Get user
        $user = User::find($tokenRecord->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        // Generate NEW access token
        $newAccessToken = $user->createToken('auth_token', ['*'], now()->addMinutes(30))
            ->plainTextToken;

        // Generate NEW refresh token (90 days)
        $newRefreshToken = $user->generateRefreshToken(90);

        \Log::info('Token refreshed successfully', [
            'user_id' => $user->id,
            'old_token_revoked' => true,
            'new_tokens_issued' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil diperbaharui',
            'data' => [
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 1800,  // 30 minutes
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Token refresh error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal memperbarui token'
        ], 500);
    }
}
```

#### Step 3: Add Comprehensive Logging (20 mins)

**File**: `app/Http/Controllers/Api/AuthController.php`

**Find method**: `login(AuthLoginRequest $request)`

**Add this after successful login** (find where you return tokens):

```php
\Log::info('User login successful', [
    'user_id' => $user->id,
    'email' => $user->email,
    'ip_address' => $request->ip(),
    'access_token_issued' => true,
    'refresh_token_issued' => true,
    'refresh_expires_days' => 90,
    'timestamp' => now()
]);
```

**And in logout endpoint** (if exists):

```php
public function logout(Request $request)
{
    try {
        $user = $request->user();

        if ($user) {
            // Revoke all tokens for this user
            $user->revokeRefreshToken();

            \Log::info('User logout', [
                'user_id' => $user->id,
                'timestamp' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);

    } catch (\Exception $e) {
        \Log::error('Logout error: ' . $e->getMessage());
        return response()->json(['success' => false], 500);
    }
}
```

#### Step 4: Clear Cache & Test (10 mins)

```bash
# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Test API endpoint (use Postman atau curl)
```

---

### PHASE 2: Robustness (30 mins) - DO THIS AFTER PHASE 1

#### Step 5: Setup Token Cleanup Task

**Create file**: `app/Console/Commands/CleanupExpiredTokens.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupExpiredTokens extends Command
{
    protected $signature = 'tokens:cleanup';
    protected $description = 'Delete expired and revoked tokens';

    public function handle()
    {
        try {
            $before = DB::table('tokens')->count();

            // Delete tokens expired > 90 days ago
            $deleted1 = DB::table('tokens')
                ->where('expires_at', '<', now()->subDays(90))
                ->delete();

            // Delete revoked tokens > 7 days ago
            $deleted2 = DB::table('tokens')
                ->where('revoked', true)
                ->where('revoked_at', '<', now()->subDays(7))
                ->delete();

            $totalDeleted = $deleted1 + $deleted2;
            $after = DB::table('tokens')->count();

            Log::info('Token cleanup completed', [
                'deleted' => $totalDeleted,
                'before' => $before,
                'after' => $after,
                'expired_deleted' => $deleted1,
                'revoked_deleted' => $deleted2
            ]);

            $this->info("✅ Token cleanup completed");
            $this->info("Tokens before: {$before}");
            $this->info("Tokens deleted: {$totalDeleted}");
            $this->info("Tokens after: {$after}");

        } catch (\Exception $e) {
            Log::error('Token cleanup error: ' . $e->getMessage());
            $this->error("Error: " . $e->getMessage());
        }
    }
}
```

**Register in**: `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    // ... existing schedules ...

    // ✅ Add this line
    $schedule->command('tokens:cleanup')->daily()->at('02:00');
}
```

**Test immediately**:

```bash
php artisan tokens:cleanup
# Should output: ✅ Token cleanup completed
```

#### Step 6: Add Rate Limiting

**File**: `routes/api.php`

**Find the refresh token route**, add middleware:

```php
// Before
Route::post('/auth/refresh-token', [AuthController::class, 'refreshToken']);

// After
Route::post('/auth/refresh-token', [AuthController::class, 'refreshToken'])
    ->middleware(['throttle:10,60']);  // 10 attempts per 60 seconds
```

---

## 🧪 TESTING - DO THIS RIGHT NOW

### Test 1: Database Schema Check

```bash
mysql -u root motorcycle_management
```

```sql
DESCRIBE tokens;
-- Should show: revoked (TINYINT), revoked_at (TIMESTAMP)

SELECT * FROM tokens LIMIT 1;
-- Verify columns exist
```

### Test 2: Fresh Login Test

```bash
curl -X POST http://localhost:8000/api/v1/motorcycle/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'

# Save the response:
# access_token: ABC123... (short-lived, 30 min)
# refresh_token: XYZ789... (long-lived, 90 days)
```

### Test 3: Refresh Token Test (FIRST TIME)

```bash
curl -X POST http://localhost:8000/api/v1/motorcycle/auth/refresh-token \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "refresh_token": "XYZ789..."
  }'

# Expected response:
# {
#   "success": true,
#   "data": {
#     "access_token": "NEW_ABC123...",
#     "refresh_token": "NEW_XYZ789...",
#     "expires_in": 1800
#   }
# }
```

### Test 4: Refresh Token Test (SECOND TIME - SHOULD FAIL)

```bash
curl -X POST http://localhost:8000/api/v1/motorcycle/auth/refresh-token \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "refresh_token": "XYZ789..."  # OLD token from Test 3
  }'

# Expected response (401):
# {
#   "success": false,
#   "message": "Refresh token tidak valid. Silakan login kembali."
# }
```

### Test 5: Check Logs

```bash
# Monitor logs realtime
tail -f storage/logs/laravel.log | grep -E "(refresh|token)"

# Should see:
# [2026-05-15 14:30:00] local.INFO: Token refresh attempt ...
# [2026-05-15 14:30:01] local.INFO: Old token revoked ...
# [2026-05-15 14:30:01] local.INFO: Token refreshed successfully ...
```

### ✅ Success Criteria

All tests should pass:

- ✅ Test 1: Database schema correct
- ✅ Test 2: Login returns both tokens
- ✅ Test 3: First refresh returns NEW tokens
- ✅ Test 4: Old token CANNOT be reused (401)
- ✅ Test 5: Logs show token operations

---

## 🚀 DEPLOYMENT CHECKLIST

**Before going to PRODUCTION**:

- [ ] All 5 code changes implemented
- [ ] Database schema updated
- [ ] Cleanup task scheduled
- [ ] Rate limiting added
- [ ] All 5 tests passing
- [ ] Logs checked (no errors)
- [ ] Cache cleared
- [ ] Ready for backend deployment

**Post-Deployment**:

- [ ] Monitor logs for 401 errors (should be ZERO)
- [ ] Test fresh login from mobile
- [ ] Check token refresh works automatically
- [ ] Verify no "User tidak ditemukan" errors
- [ ] Monitor for 24 hours

---

## 📞 CRITICAL: What Happens During Refresh

```
BEFORE FIX (❌ BROKEN):
────────────────────────
1. User requests refresh with old_token
2. Backend finds old_token in DB (✅ found)
3. Backend returns SAME old_token back
4. Old token NEVER revoked
5. User can refresh infinite times with same token
6. Next refresh with old token works again
7. Infinite loop possible

AFTER FIX (✅ FIXED):
────────────────────────
1. User requests refresh with old_token
2. Backend finds old_token in DB (✅ found)
3. Backend REVOKES old_token (sets revoked=true)
4. Backend generates NEW access_token + NEW refresh_token
5. Old token no longer valid
6. Next refresh MUST use new_token
7. If user tries old_token → 401 Unauthorized
8. Token rotation working ✅
```

---

## 🔍 TROUBLESHOOTING

### Issue: Getting 404 "User tidak ditemukan"

**Solution**:

1. Check if user exists: `SELECT * FROM users WHERE id = X`
2. Check if refresh_token matches: `SELECT * FROM tokens WHERE user_id = X`
3. Verify token is not NULL

### Issue: Getting 401 After Login

**Possible Causes**:

1. Token expiration check too strict
2. Token not properly saved in DB
3. Database connection issue

**Debug**:

```bash
# Check token exists after login
mysql > SELECT * FROM tokens WHERE user_id = 5 ORDER BY created_at DESC LIMIT 1;
```

### Issue: Cleanup Task Not Running

**Fix**:

```bash
# Test manually
php artisan tokens:cleanup

# Check if scheduled
php artisan schedule:list

# Run scheduler in foreground (for testing)
php artisan schedule:work
```

---

## 📊 MONITORING QUERIES

**Run these AFTER implementation** to verify everything works:

```sql
-- 1. Count tokens by status
SELECT
  revoked,
  COUNT(*) as count,
  COUNT(DISTINCT user_id) as unique_users
FROM tokens
GROUP BY revoked;

-- Expected:
-- revoked=0 (FALSE): Active tokens
-- revoked=1 (TRUE): Revoked/old tokens

-- 2. Check if cleanup working
SELECT
  COUNT(*) as old_tokens_still_in_db
FROM tokens
WHERE expires_at < NOW()
AND revoked = FALSE;

-- Expected: 0 atau very few

-- 3. Check token age distribution
SELECT
  DATEDIFF(NOW(), created_at) as days_old,
  COUNT(*) as count
FROM tokens
WHERE revoked = FALSE
GROUP BY DATEDIFF(NOW(), created_at)
ORDER BY days_old;

-- Expected: Most tokens < 90 days old
```

---

## ✨ EXPECTED RESULT

**After successful implementation**:

✅ **Users can login** → Receive access + refresh tokens  
✅ **Users can use app** → Tokens work for 30+ minutes  
✅ **Auto-refresh works** → App automatically refreshes when expired  
✅ **No 401 loops** → Users don't get stuck  
✅ **Token rotation works** → Old tokens revoked on refresh  
✅ **Cleanup works** → Database stays clean  
✅ **Logging works** → Can debug any issues

---

## 📞 QUESTIONS?

If issues arise during implementation:

1. **Check logs first**: `tail -f storage/logs/laravel.log`
2. **Run tests from Testing section above**
3. **Verify database schema**
4. **Review code changes line-by-line**

**This is HIGH PRIORITY** - do this before other work.

---

**Implementation Owner**: Backend Team  
**Timeline**: 1-2 hours  
**Deployment**: After QA approval  
**Review**: After 24h monitoring

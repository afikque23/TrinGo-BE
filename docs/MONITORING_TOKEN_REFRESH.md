# 📊 Log Monitoring Guide: Token Refresh Operations

**Purpose**: Real-time monitoring of token operations to detect issues  
**Owner**: DevOps/QA Team  
**Duration**: 24-72 hours after deployment

---

## 🎯 MONITORING OBJECTIVES

After token rotation fix deployment:

1. **Detect Issues Quickly**: Catch 401 errors, failed refreshes, database issues
2. **Verify Fix Works**: Confirm token rotation, revocation, new token generation
3. **Monitor Performance**: Track refresh response times, database queries
4. **Track Metrics**: Success rate, error rate, volume of refreshes
5. **Alert on Problems**: Automated alerts if things go wrong

---

## 🔍 Real-Time Log Monitoring

### Setup 1: Monitor All Token Operations

**Terminal Command**:

```bash
cd c:\laragon\www\motorcycle_management
tail -f storage/logs/laravel.log | grep -i "token\|refresh\|401"
```

**What to Watch For**:

**✅ GOOD - Token refresh successful**:

```
[2026-05-15 14:30:01] local.INFO: Token refresh attempt {"token_hash":"ed5c84...","ip":"127.0.0.1"}
[2026-05-15 14:30:01] local.INFO: Old token revoked {"user_id":5,"token_id":100}
[2026-05-15 14:30:01] local.INFO: Token refreshed successfully {"user_id":5}
```

**⚠️ WARNING - Token invalid (expected for revoked/expired)**:

```
[2026-05-15 14:30:10] local.WARNING: Refresh token invalid/expired/revoked {"possible_cause":"token_not_found_or_revoked"}
```

**❌ BAD - Should investigate**:

```
[2026-05-15 14:30:15] local.ERROR: Token refresh error {"error":"Database connection failed"}
[2026-05-15 14:30:20] local.ERROR: SQL error in refresh endpoint
```

---

### Setup 2: Monitor 401 Errors Specifically

**Terminal Command**:

```bash
cd c:\laragon\www\motorcycle_management
tail -f storage/logs/laravel.log | grep "401\|Unauthorized"
```

**Expected During Testing**:

- ✅ Few 401s when testing with revoked tokens
- ✅ No 401s during normal app usage

**If Seeing Many 401s**:

- ⚠️ Investigate - might indicate a problem
- Check refresh endpoint not being called
- Check token validation too strict

---

### Setup 3: Monitor Login Operations

**Terminal Command**:

```bash
cd c:\laragon\www\motorcycle_management
tail -f storage/logs/laravel.log | grep "login\|Login"
```

**Expected Pattern**:

```
[2026-05-15 14:25:00] local.INFO: User login successful {"user_id":5,"email":"test@example.com","ip_address":"127.0.0.1","access_token_issued":true,"refresh_token_issued":true}
[2026-05-15 14:25:01] local.INFO: User login successful {"user_id":6,"email":"user2@example.com"...}
```

**Verify**:

- ✅ Each login shows both tokens issued
- ✅ IP addresses logged
- ✅ No login errors

---

### Setup 4: Monitor Logout Operations

**Terminal Command**:

```bash
cd c:\laragon\www\motorcycle_management
tail -f storage/logs/laravel.log | grep -i "logout"
```

**Expected**:

```
[2026-05-15 14:35:00] local.INFO: User logout {"user_id":5}
```

---

## 📊 Database Monitoring Queries

### Run These Every Hour (During 24-72h Monitoring Period)

#### Query 1: Token Operation Status

```sql
-- Check token health
SELECT
  COUNT(*) as total_tokens,
  SUM(CASE WHEN revoked = FALSE AND expires_at > NOW() THEN 1 ELSE 0 END) as active_tokens,
  SUM(CASE WHEN revoked = TRUE THEN 1 ELSE 0 END) as revoked_tokens,
  SUM(CASE WHEN expires_at < NOW() THEN 1 ELSE 0 END) as expired_tokens
FROM tokens;

-- Expected:
-- active_tokens: Growing with each login/refresh
-- revoked_tokens: Growing with each refresh (old tokens revoked)
-- expired_tokens: Should be cleaned up (0 or very few)
```

#### Query 2: Success Rate

```sql
-- Approximate success rate (assuming no errors = success)
SELECT
  COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as tokens_last_hour,
  COUNT(CASE WHEN revoked = FALSE AND expires_at > NOW() AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as active_last_hour,
  ROUND(COUNT(CASE WHEN revoked = FALSE AND expires_at > NOW() AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) * 100 / COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END), 2) as success_rate_percent
FROM tokens;

-- Expected: success_rate_percent > 95%
```

#### Query 3: Refresh Activity

```sql
-- Check refresh activity in last hour
SELECT
  COUNT(*) as refresh_attempts,
  COUNT(DISTINCT user_id) as unique_users,
  COUNT(CASE WHEN revoked = TRUE AND revoked_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as revoked_in_last_hour
FROM tokens
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
OR (revoked = TRUE AND revoked_at > DATE_SUB(NOW(), INTERVAL 1 HOUR));

-- Expected:
-- revoked_in_last_hour: > 0 (shows token rotation happening)
```

#### Query 4: Unique Users Activity

```sql
-- Track unique users per hour
SELECT
  DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour,
  COUNT(DISTINCT user_id) as unique_users,
  COUNT(*) as tokens_created
FROM tokens
WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00')
ORDER BY hour DESC;

-- Expected: Consistent pattern showing regular user activity
```

#### Query 5: Database Performance

```sql
-- Check if queries are efficient
SHOW STATUS LIKE 'Questions';
SHOW STATUS LIKE 'Slow_queries';

-- Refresh endpoint queries should be < 100ms
-- If > 500ms, investigate indexes
```

---

## 📈 Metrics Dashboard (Create in Your Monitoring Tool)

### Key Metrics to Track (24-72 hours)

#### 1. Token Refresh Success Rate

```
Target: > 99%
Action: If < 95%, investigate
```

**SQL Query**:

```sql
-- Daily success rate
SELECT
  DATE(created_at) as date,
  COUNT(*) as total_refreshes,
  COUNT(CASE WHEN revoked = FALSE AND expires_at > NOW() THEN 1 END) as successful
FROM tokens
WHERE created_at > DATE_SUB(NOW(), INTERVAL 3 DAY)
GROUP BY DATE(created_at);
```

#### 2. Active Sessions (Non-Expired Tokens)

```
Expected: 50-500 active sessions (depends on user base)
Trend: Should increase with active users, decrease during off-hours
```

**SQL Query**:

```sql
SELECT
  DATE(NOW()) as date,
  HOUR(NOW()) as hour,
  COUNT(*) as active_sessions
FROM tokens
WHERE revoked = FALSE
AND expires_at > NOW();
```

#### 3. Token Rotation Rate

```
Expected: 1 revoked token per 1 refresh (1:1 ratio)
Shows: Each refresh properly revokes old token
```

**SQL Query**:

```sql
SELECT
  DATE(created_at) as date,
  COUNT(*) as tokens_created,
  SUM(CASE WHEN revoked = FALSE THEN 1 ELSE 0 END) as active,
  SUM(CASE WHEN revoked = TRUE THEN 1 ELSE 0 END) as revoked
FROM tokens
WHERE created_at > DATE_SUB(NOW(), INTERVAL 3 DAY)
GROUP BY DATE(created_at);
```

#### 4. Error Rate

```
Expected: < 1% errors
Errors include: Invalid tokens, database errors, etc.
```

**Check Logs**:

```bash
# Count errors in last 24 hours
grep -c "ERROR\|CRITICAL" storage/logs/laravel.log

# Should be < 50 (assuming thousands of requests)
```

#### 5. Response Time

```
Expected: < 100ms per refresh
If > 500ms: Check database indexes, server load
```

**Manual Test**:

```bash
# Time a single refresh request
time curl -X POST http://localhost:8000/api/v1/motorcycle/auth/refresh-token \
  -H "Content-Type: application/json" \
  -d '{"refresh_token":"..."}'

# real    0m0.150s   <- Should be around 100-200ms
```

---

## 🚨 Alert Triggers (Set These Up)

### Alert 1: High 401 Error Rate

**Condition**: > 10 401 errors per minute

```bash
# Monitor command
tail -f storage/logs/laravel.log | grep -c "401" | head -1
# If count > 10 in 60 seconds → ALERT
```

**Action**:

1. Stop testing
2. Check token validation logic
3. Verify database connection
4. Check API endpoint implementation

---

### Alert 2: Database Query Timeout

**Condition**: SQL query takes > 1000ms

```bash
# Check slow query log
grep "Query_time" storage/logs/mysql-slow.log | tail -20

# If > 1000ms → ALERT
```

**Action**:

1. Check if indexes exist
2. Run query optimization
3. Check server load

---

### Alert 3: Token Cleanup Failed

**Condition**: Cleanup task doesn't run

```bash
# Check if cleanup ran today
grep "tokens:cleanup" storage/logs/laravel.log | tail -5

# If no entry for today → ALERT
```

**Action**:

1. Check if scheduler running: `php artisan schedule:list`
2. Run manually: `php artisan tokens:cleanup`
3. Check for errors in logs

---

### Alert 4: Login/Logout Anomalies

**Condition**: > 50 failed logins in 10 minutes

```bash
# Check failed logins
grep "login.*failed\|Invalid credentials" storage/logs/laravel.log | wc -l

# If > 50 in 10 mins → Possible brute force attempt
```

**Action**:

1. Block suspicious IP address
2. Check if credentials leaked
3. Enable additional rate limiting

---

## 📋 Monitoring Checklist (First 24 Hours)

**Every 1 hour**:

- [ ] Check log tail - no ERROR messages
- [ ] Run database queries above
- [ ] Verify success rate > 95%
- [ ] Check active sessions increasing
- [ ] Verify token rotation happening (revoked increasing)

**Every 4 hours**:

- [ ] Run full test suite from TESTING_TOKEN_REFRESH.md
- [ ] Check response times < 200ms
- [ ] Verify no alert triggers
- [ ] Document any anomalies

**End of day (24 hours)**:

- [ ] Generate summary report (see template below)
- [ ] Review all metrics
- [ ] Decide if need extended monitoring
- [ ] Sign off if all good

---

## 📊 Monitoring Report Template

**Use this to document findings**:

```
═══════════════════════════════════════════════════════════════════
                 TOKEN REFRESH MONITORING REPORT
═══════════════════════════════════════════════════════════════════

Date Range: May 15 - May 16, 2026
Monitoring Duration: 24 hours
Monitor: [Your Name]

─────────────────────────────────────────────────────────────────
EXECUTIVE SUMMARY
─────────────────────────────────────────────────────────────────

Overall Status: ✅ HEALTHY

Key Findings:
  ✅ All systems operational
  ✅ Token rotation working correctly
  ✅ No unexpected 401 errors
  ✅ Database performance good
  ✅ All scheduled tasks running

─────────────────────────────────────────────────────────────────
METRICS SUMMARY
─────────────────────────────────────────────────────────────────

1. Token Operations
   Total Logins: 45
   Total Refreshes: 320
   Success Rate: 99.8%
   Failed Operations: 1

2. Active Sessions
   Peak Active Users: 12
   Average Active: 8
   Max Concurrent: 5

3. Token Rotation
   Tokens Created: 365
   Tokens Revoked: 320
   Revocation Rate: 87.7% (expected, some logins won't refresh)

4. Performance
   Avg Refresh Time: 85ms
   Max Refresh Time: 245ms
   P95 Response Time: 120ms

5. Error Tracking
   401 Errors: 3 (all expected, revoked tokens)
   500 Errors: 0
   Database Errors: 0

─────────────────────────────────────────────────────────────────
DETAILED FINDINGS
─────────────────────────────────────────────────────────────────

✅ Token Revocation Working:
   - Each refresh properly revokes old token
   - Old tokens cannot be reused
   - Confirmed via database queries

✅ Login Process:
   - Users receive both access + refresh tokens
   - Tokens stored securely in mobile app
   - No credential leaks

✅ Refresh Process:
   - Auto-refresh working at 30-min mark
   - New tokens issued correctly
   - Zero unexpected 401s

✅ Database Health:
   - Tokens table has 365 records (clean)
   - Cleanup task scheduled and working
   - No slow queries detected

✅ Logging:
   - All operations logged
   - Easy to debug issues
   - No sensitive data in logs

─────────────────────────────────────────────────────────────────
ISSUES & RESOLUTIONS
─────────────────────────────────────────────────────────────────

Issue #1: [Description]
Status: [RESOLVED / IN PROGRESS / NONE]
Resolution: [What was done]

─────────────────────────────────────────────────────────────────
RECOMMENDATIONS
─────────────────────────────────────────────────────────────────

1. Continue monitoring for next 48 hours
2. Set up automated alerts for 401 error rate
3. Monitor database size monthly (cleanup working)

─────────────────────────────────────────────────────────────────
SIGN-OFF
─────────────────────────────────────────────────────────────────

Monitor: [Name]
Date: May 16, 2026
Status: ✅ APPROVED FOR PRODUCTION

Ready for: Wider deployment / Feature release

═══════════════════════════════════════════════════════════════════
```

---

## 🔧 Useful Commands

### Real-time Monitoring

```bash
# Monitor specific pattern
watch -n 5 'tail -20 storage/logs/laravel.log | grep -i token'

# Count occurrences
tail -100 storage/logs/laravel.log | grep -o "token" | wc -l

# Filter by time
grep "2026-05-15 14:" storage/logs/laravel.log | head -20

# Count error types
tail -f storage/logs/laravel.log | grep -E "(ERROR|WARNING)" | \
  awk '{print $NF}' | sort | uniq -c | sort -rn
```

### Database Queries

```bash
# Quick check
mysql -u root motorcycle_management -e "SELECT COUNT(*) FROM tokens;"

# Watch changes
watch -n 5 'mysql -u root motorcycle_management -e "SELECT COUNT(*) as total, SUM(revoked) as revoked FROM tokens;"'

# Export data
mysqldump -u root motorcycle_management tokens > tokens_backup.sql
```

---

## 📞 Escalation Path

**If detecting issues**:

1. **Level 1**: Check logs, run queries → Try to identify issue
2. **Level 2**: Contact backend team, review code changes
3. **Level 3**: Rollback to previous version if critical
4. **Level 4**: Post-incident review, improve monitoring

---

## ✅ Monitoring Sign-Off

**After 24-72 hours of monitoring**:

If all criteria met:

- [ ] > 95% success rate
- [ ] < 1% error rate
- [ ] Token rotation working
- [ ] No critical issues
- [ ] Response times acceptable
- [ ] All scheduled tasks running

**Then**:
✅ **Mark as PRODUCTION READY**

---

**Monitoring Owner**: DevOps/QA  
**Duration**: 24-72 hours  
**Timeline**: Starts immediately after deployment  
**Review Date**: May 18, 2026

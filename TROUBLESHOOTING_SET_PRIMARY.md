# 🐛 Troubleshooting: Route Not Found - set-primary

## ❌ Error Message

```json
{
    "message": "The route api/v1/motorcycle/vehicles//set-primary could not be found."
}
```

## 🔍 Root Cause

Perhatikan ada **double slash** `//` di URL:

```
api/v1/motorcycle/vehicles//set-primary
                         ^^
```

Ini berarti variable `{{vehicle_id}}` di Postman **kosong atau tidak ter-set**.

---

## ✅ Solution: Set vehicle_id di Postman

### Option 1: Run "Create Vehicle" Request Dulu

1. **Buka folder Vehicles di Postman**
2. **Jalankan request "2. Create Vehicle"**
3. **Klik Send**
4. Vehicle ID akan **otomatis tersimpan** ke environment variable `{{vehicle_id}}`
5. **Sekarang jalankan "6. Set Primary Vehicle"**

### Option 2: Set vehicle_id Manual

1. **Klik icon Environment** (👁️) di kanan atas Postman
2. **Pilih "Motorcycle Management - Local"**
3. **Scroll ke variable `vehicle_id`**
4. **Set value**, contoh: `1`, `2`, `3`, dll
5. **Save**
6. **Jalankan request "6. Set Primary Vehicle"**

### Option 3: Gunakan ID dari "Get All Vehicles"

1. **Jalankan "1. Get All Vehicles"**
2. **Copy ID motor** dari response:
    ```json
    {
        "data": [
            {
                "id": 5, // <-- Copy ID ini
                "title": "Honda Vario 125"
            }
        ]
    }
    ```
3. **Set ke environment variable** (Option 2 di atas)
4. **Atau ganti URL langsung**:
    ```
    {{base_url}}/vehicles/5/set-primary
    ```

---

## 🧪 Quick Test

### Test 1: Cek Environment Variable

```javascript
// Di Postman Console (View > Show Postman Console)
console.log("vehicle_id:", pm.environment.get("vehicle_id"));
```

**Expected Output:**

```
vehicle_id: 3
```

**Jika undefined:**

```
vehicle_id: undefined  // ❌ Variable tidak ada
```

### Test 2: Create Vehicle Dulu

```bash
# Request Order:
1. Login                    → Token tersimpan
2. Create Vehicle           → vehicle_id tersimpan  ✅
3. Set Primary Vehicle      → Gunakan vehicle_id
```

### Test 3: Hardcode ID untuk Testing

```
# Ganti URL sementara dari:
{{base_url}}/vehicles/{{vehicle_id}}/set-primary

# Jadi:
{{base_url}}/vehicles/1/set-primary
```

---

## 🔧 Postman Environment Check

### Cara Verifikasi Environment Variables:

1. **Klik icon Environment** (👁️)
2. **Pilih environment aktif**
3. **Check variables:**

| Variable     | Initial Value       | Current Value       | Status     |
| ------------ | ------------------- | ------------------- | ---------- |
| `base_url`   | http://localhost... | http://localhost... | ✅ OK      |
| `token`      |                     | eyJ0eXAiOiJKV1Q...  | ✅ OK      |
| `vehicle_id` |                     | **EMPTY**           | ❌ Not Set |

**Fix:** Set `vehicle_id` dengan value yang valid (integer)

---

## 📋 Step-by-Step Fix

### Scenario A: Belum Punya Motor

```bash
1. POST /auth/login
   → Save token

2. POST /vehicles
   Body: {
     "title": "Honda Vario 125",
     "tipe_motor": "matic"
   }
   → Save vehicle_id (auto dari test script)

3. POST /vehicles/{{vehicle_id}}/set-primary
   → Success! ✅
```

### Scenario B: Sudah Punya Motor

```bash
1. POST /auth/login
   → Save token

2. GET /vehicles
   → Lihat list motor & copy ID

3. Set vehicle_id manual:
   - Klik Environment
   - Set vehicle_id = 5 (contoh)
   - Save

4. POST /vehicles/{{vehicle_id}}/set-primary
   → Success! ✅
```

### Scenario C: Direct ID (No Variable)

```bash
# Edit URL langsung di Postman:
POST {{base_url}}/vehicles/5/set-primary

# Atau test via curl:
curl -X POST \
  http://localhost/api/v1/motorcycle/vehicles/5/set-primary \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 🎯 Verification

Setelah fix, response harus seperti ini:

**Success (200):**

```json
{
  "success": true,
  "message": "Motor utama berhasil diubah",
  "data": {
    "id": 5,
    "title": "Honda Vario 125",
    "is_primary": true,
    "service_intervals": [...]
  }
}
```

**Error (404) - Motor Tidak Ada:**

```json
{
    "success": false,
    "message": "Kendaraan tidak ditemukan atau bukan milik Anda"
}
```

---

## 💡 Pro Tips

### 1. Enable Postman Console

```
View > Show Postman Console
```

Lihat semua variable yang tersimpan saat request dijalankan.

### 2. Check Test Scripts

Request "Create Vehicle" punya test script:

```javascript
if (pm.response.code === 201) {
    const response = pm.response.json();
    if (response.data && response.data.id) {
        pm.environment.set("vehicle_id", response.data.id); // Auto-save
        console.log("Vehicle ID saved:", response.data.id);
    }
}
```

### 3. Manual Fallback

Tambahkan di Pre-request Script:

```javascript
// Set default jika vehicle_id kosong
if (!pm.environment.get("vehicle_id")) {
    pm.environment.set("vehicle_id", "1"); // Default value
}
```

### 4. Debug URL

Tambahkan console.log di Pre-request Script:

```javascript
const vehicleId = pm.environment.get("vehicle_id");
console.log("Current vehicle_id:", vehicleId);
console.log(
    "URL will be:",
    pm.variables.replaceIn("{{base_url}}/vehicles/{{vehicle_id}}/set-primary")
);
```

---

## 🚨 Common Mistakes

### Mistake 1: Wrong Variable Name

```javascript
// ❌ Wrong
{
    {
        vehicleId;
    }
}
{
    {
        vehicle - id;
    }
}
{
    {
        VehicleId;
    }
}

// ✅ Correct
{
    {
        vehicle_id;
    }
}
```

### Mistake 2: Variable di Wrong Scope

```
Check scope hierarchy:
1. Global
2. Collection
3. Environment  ← vehicle_id should be here
4. Data
5. Local
```

### Mistake 3: Environment Not Active

```
Check di dropdown kanan atas Postman:
✅ "Motorcycle Management - Local" (hijau)
❌ "No Environment" (merah)
```

---

## 📞 Still Not Working?

### Check Route Registration

```bash
php artisan route:list --path=vehicles
```

**Expected Output:**

```
POST   api/v1/motorcycle/vehicles/{id}/set-primary   VehicleController@setPrimary
```

### Check Database

```bash
php artisan tinker
>>> Vehicle::all()
>>> Vehicle::find(1)  // Check if vehicle exists
```

### Check Environment File

```bash
# .env
APP_URL=http://localhost
```

### Clear Cache

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

---

## ✅ Final Checklist

-   [ ] Token tersimpan di environment
-   [ ] Motor sudah dibuat (POST /vehicles)
-   [ ] vehicle_id tersimpan (check console)
-   [ ] Environment aktif (hijau di kanan atas)
-   [ ] URL tidak ada double slash
-   [ ] Motor milik user yang login

---

**Problem Solved! 🎉**

Jika masih error, cek:

1. `PRIMARY_VEHICLE_API.md` - Full API documentation
2. `POSTMAN_GUIDE.md` - Complete testing guide
3. Postman Console untuk debug

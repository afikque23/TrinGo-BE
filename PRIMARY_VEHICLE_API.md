# 🏍️ PRIMARY VEHICLE (Motor Utama) - DOKUMENTASI BACKEND

## 🎯 Konsep Primary Vehicle

**Primary Vehicle** adalah motor utama yang secara otomatis digunakan sebagai konteks default untuk semua fitur aplikasi (dashboard, tracking, reminder, servis). Sistem ini meniru kebiasaan pengguna di dunia nyata yang langsung menggunakan motor harian tanpa memilih motor terlebih dahulu.

### 🔑 Karakteristik Utama:

1. **Auto-set Primary**: Motor pertama yang ditambahkan user **otomatis menjadi motor utama**
2. **Hanya 1 Primary**: Setiap user hanya bisa punya **1 motor utama** pada satu waktu
3. **Persistent**: Motor utama tetap sama sampai user **secara eksplisit mengubahnya**
4. **Default Context**: Semua fitur utama (dashboard, tracking, dll) otomatis pakai motor utama
5. **Simple UX**: Frontend tidak perlu handle logic pemilihan motor yang rumit

---

## 📊 Database Schema

### Field `is_primary` di Tabel `vehicles`

```sql
ALTER TABLE vehicles ADD COLUMN is_primary BOOLEAN DEFAULT FALSE;
CREATE INDEX idx_user_primary ON vehicles(user_id, is_primary);
```

**Keuntungan design ini:**

-   ✅ Query cepat dengan composite index
-   ✅ Jelas motor mana yang primary (tidak perlu join ke users)
-   ✅ Bisa query semua motor primary dengan mudah
-   ✅ Tidak ada foreign key circular dependency

---

## 🔄 Flow Diagram Implementasi

```
[ USER BUKA APLIKASI ]
        |
        v
[ SUDAH LOGIN ? ]
   |          |
  NO         YES
   |          |
[ LOGIN ]     v
              |
     [ GET /vehicles/primary ]
         |           |
        404         200
         |           |
   [ BELUM ADA ]  [ SUDAH ADA ]
   [ MOTOR ]      [ MOTOR UTAMA ]
         |           |
         v           v
[ GET ALL VEHICLES ] [ DASHBOARD ]
         |           [ MOTOR UTAMA ]
         v                 |
   [ ADA MOTOR? ]          |
    |        |             |
   NO       YES            |
    |        |             |
[ TAMBAH ]  [ PILIH ]      |
[ MOTOR  ]  [ MOTOR ]      |
  BARU      DARI LIST      |
    |        |             |
    v        v             |
[ POST /vehicles ]         |
    |                      |
    v                      |
[ AUTO SET PRIMARY ]       |
    |                      |
    v                      |
[ DASHBOARD MOTOR ] <------+
        |
        v
[ USER TEKAN "GANTI MOTOR" ]
        |
        v
[ GET ALL VEHICLES ]
        |
        v
[ USER PILIH MOTOR LAIN ]
        |
        v
[ POST /vehicles/{id}/set-primary ]
        |
        v
[ MOTOR BARU JADI PRIMARY ]
        |
        v
[ DASHBOARD MOTOR BARU ]
```

---

## 📍 API ENDPOINTS

### Base URL

```
http://localhost/api/v1/motorcycle
```

### Authentication

Semua endpoint butuh Bearer Token:

```
Authorization: Bearer {token}
```

---

## 1. GET Primary Vehicle

```http
GET /vehicles/primary
```

Mengambil motor utama user yang sedang login.

**Response Sukses (200):**

```json
{
  "success": true,
  "message": "Data motor utama berhasil diambil",
  "data": {
    "id": 3,
    "user_id": 1,
    "title": "Honda Vario 125",
    "make": "Honda",
    "model": "Vario 125",
    "year": 2023,
    "tipe_motor": "matic",
    "odometer": 5000,
    "license_plate": "B 1234 XYZ",
    "is_primary": true,
    "service_intervals": [...],
    "service_histories": [...],
    "fuel_logs": [...],
    "reminders": [...]
  }
}
```

**Response Error (404) - Belum Ada Motor:**

```json
{
    "success": false,
    "message": "Belum ada motor utama. Silakan pilih motor utama terlebih dahulu."
}
```

**Use Case:**

-   Saat user buka aplikasi → call endpoint ini
-   Jika 200 → Langsung tampilkan dashboard dengan motor utama
-   Jika 404 → Redirect ke halaman pilih/tambah motor

---

## 2. POST Set Primary Vehicle

```http
POST /vehicles/{id}/set-primary
```

Mengubah motor utama user. Hanya boleh dipanggil saat user **secara eksplisit** ingin ganti motor utama (misalnya dari menu "Ganti Motor").

**URL Parameters:**

-   `id` (integer, required) - ID motor yang akan dijadikan motor utama

**Request:**

```javascript
POST /vehicles/5/set-primary
Headers: {
  "Authorization": "Bearer {token}",
  "Accept": "application/json"
}
```

**Backend Logic:**

1. Verifikasi motor milik user yang login
2. Set semua motor user lain jadi `is_primary = false`
3. Set motor ini jadi `is_primary = true`
4. Return motor dengan relationships

**Response Sukses (200):**

```json
{
  "success": true,
  "message": "Motor utama berhasil diubah",
  "data": {
    "id": 5,
    "title": "Honda CBR 150R",
    "is_primary": true,
    "service_intervals": [...]
  }
}
```

**Response Error (404):**

```json
{
    "success": false,
    "message": "Kendaraan tidak ditemukan atau bukan milik Anda"
}
```

---

## 3. POST Create Vehicle (Auto-set Primary)

```http
POST /vehicles
```

Saat user menambahkan motor baru:

-   **Motor pertama** → Otomatis `is_primary = true`
-   **Motor kedua dst** → `is_primary = false` (tidak otomatis ganti)

**Request:**

```javascript
POST /vehicles
Headers: {
  "Content-Type": "multipart/form-data",
  "Authorization": "Bearer {token}"
}

Body (FormData): {
  "title": "Honda Beat 2023",
  "tipe_motor": "matic",  // required
  "make": "Honda",
  "model": "Beat",
  "year": 2023,
  "odometer": 1000,
  "photo": <file>
}
```

**Backend Logic:**

```php
// Cek apakah ini motor pertama user
$existingVehicleCount = Vehicle::where('user_id', $user->id)->count();

if ($existingVehicleCount === 0) {
    $validated['is_primary'] = true;  // Auto-set primary
}

$vehicle = Vehicle::create($validated);
```

**Response (201) - Motor Pertama:**

```json
{
    "success": true,
    "message": "Kendaraan berhasil ditambahkan dan ditetapkan sebagai motor utama",
    "data": {
        "id": 1,
        "title": "Honda Beat 2023",
        "is_primary": true
    }
}
```

**Response (201) - Motor Tambahan:**

```json
{
    "success": true,
    "message": "Kendaraan berhasil ditambahkan",
    "data": {
        "id": 2,
        "title": "Yamaha Aerox",
        "is_primary": false
    }
}
```

---

## 🎨 Frontend Implementation

### 1. Saat App Start / User Login

```javascript
async function checkPrimaryVehicle() {
    try {
        const response = await fetch(
            "http://localhost/api/v1/motorcycle/vehicles/primary",
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: "application/json",
                },
            }
        );

        const result = await response.json();

        if (response.ok) {
            // User sudah punya motor utama
            setPrimaryVehicle(result.data);
            navigateToDashboard();
        } else {
            // Belum ada motor utama (404)
            // Cek apakah user punya motor
            const vehiclesResponse = await fetch(".../vehicles");
            const vehicles = vehiclesResponse.data;

            if (vehicles.length === 0) {
                // Belum punya motor sama sekali
                navigateToAddVehicle();
            } else {
                // Punya motor tapi belum set primary
                navigateToSelectPrimary(vehicles);
            }
        }
    } catch (error) {
        console.error("Error checking primary vehicle:", error);
    }
}
```

### 2. Saat User Tambah Motor Pertama

```javascript
async function addFirstVehicle(vehicleData) {
    const formData = new FormData();
    formData.append("title", vehicleData.title);
    formData.append("tipe_motor", vehicleData.tipe_motor);
    // ... field lainnya

    const response = await fetch(
        "http://localhost/api/v1/motorcycle/vehicles",
        {
            method: "POST",
            headers: { Authorization: `Bearer ${token}` },
            body: formData,
        }
    );

    const result = await response.json();

    if (result.success) {
        // Motor pertama otomatis jadi primary
        // Message: "Kendaraan berhasil ditambahkan dan ditetapkan sebagai motor utama"

        setPrimaryVehicle(result.data);
        showSuccessMessage("Motor utama berhasil ditambahkan!");
        navigateToDashboard();
    }
}
```

### 3. Saat User Ganti Motor Utama

```javascript
// Tombol "Ganti Motor" di navbar/settings
async function showSwitchVehicle() {
    // Tampilkan list semua motor user
    const response = await fetch("http://localhost/api/v1/motorcycle/vehicles");
    const vehicles = response.data;

    // Highlight motor yang sedang primary
    vehicles.forEach((vehicle) => {
        if (vehicle.is_primary) {
            markAsPrimary(vehicle);
        }
    });

    showVehicleSelector(vehicles);
}

// User klik motor lain
async function switchPrimaryVehicle(newVehicleId) {
    const response = await fetch(
        `http://localhost/api/v1/motorcycle/vehicles/${newVehicleId}/set-primary`,
        {
            method: "POST",
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json",
            },
        }
    );

    const result = await response.json();

    if (result.success) {
        setPrimaryVehicle(result.data);
        showSuccessMessage("Motor utama berhasil diubah!");

        // Refresh dashboard dengan motor baru
        window.location.reload();
    }
}
```

### 4. Dashboard Component (Always Use Primary)

```javascript
function Dashboard() {
    const [primaryVehicle, setPrimaryVehicle] = useState(null);

    useEffect(() => {
        loadPrimaryVehicle();
    }, []);

    async function loadPrimaryVehicle() {
        const response = await fetch(".../vehicles/primary");
        const result = await response.json();

        if (result.success) {
            setPrimaryVehicle(result.data);
        } else {
            // Redirect ke vehicle selector
            navigate("/select-vehicle");
        }
    }

    return (
        <div>
            <h1>Dashboard - {primaryVehicle?.title}</h1>
            <p>Odometer: {primaryVehicle?.odometer} km</p>

            {/* Button untuk ganti motor */}
            <button onClick={showSwitchVehicle}>🔄 Ganti Motor</button>

            {/* Service reminders dari motor utama */}
            <ServiceReminders intervals={primaryVehicle?.service_intervals} />
        </div>
    );
}
```

---

## 🔒 Business Rules

### ✅ Rule 1: Hanya 1 Primary per User

```php
// Saat set primary, unset yang lain
Vehicle::where('user_id', $user->id)
    ->where('id', '!=', $vehicleId)
    ->update(['is_primary' => false]);

Vehicle::where('id', $vehicleId)
    ->update(['is_primary' => true]);
```

### ✅ Rule 2: Motor Pertama Auto-Primary

```php
$vehicleCount = Vehicle::where('user_id', $user->id)->count();

if ($vehicleCount === 0) {
    $validated['is_primary'] = true;
}
```

### ✅ Rule 3: Primary Tidak Berubah Otomatis

Motor utama **TIDAK** berubah saat:

-   User tracking perjalanan
-   User servis motor lain
-   User lihat detail motor lain

Motor utama **HANYA** berubah saat:

-   User **eksplisit** panggil `POST /vehicles/{id}/set-primary`
-   Dari menu "Ganti Motor" atau sejenisnya

### ✅ Rule 4: Validasi Kepemilikan

```php
// Pastikan motor milik user yang login
$vehicle = Vehicle::where('user_id', $user->id)
    ->where('id', $id)
    ->firstOrFail();
```

---

## 🎯 Keuntungan Sistem Ini

### Untuk User:

-   ✅ **Tidak perlu pilih motor** setiap buka aplikasi
-   ✅ **Langsung pakai motor harian** (motor utama)
-   ✅ **Hanya ganti** saat benar-benar mau pakai motor lain
-   ✅ **Mirip kebiasaan nyata**: orang tidak pilih motor setiap hari

### Untuk Frontend:

-   ✅ **Tidak perlu tracking** motor mana yang sedang dipakai
-   ✅ **Tidak perlu kirim** `vehicle_id` di setiap request
-   ✅ **Logic sederhana**: GET primary → show dashboard
-   ✅ **Less state management**: backend handle motor utama

### Untuk Backend:

-   ✅ **Simple query**: `WHERE user_id = X AND is_primary = true`
-   ✅ **No circular dependency**: tidak ada FK ke users
-   ✅ **Index optimal**: composite index (user_id, is_primary)
-   ✅ **Clear ownership**: jelas motor mana yang primary

---

## 🚫 Perbedaan dengan Active Vehicle

| Aspek           | Active Vehicle ❌            | Primary Vehicle ✅                  |
| --------------- | ---------------------------- | ----------------------------------- |
| **Storage**     | `active_vehicle_id` di users | `is_primary` di vehicles            |
| **Set Primary** | Manual select                | Auto-set untuk motor pertama        |
| **Ganti Motor** | Sering (context switching)   | Jarang (hanya saat explicit change) |
| **User Flow**   | Pilih motor → use            | Use motor utama langsung            |
| **Philosophy**  | Context management           | Default vehicle                     |

---

## 📝 Migration Code

```php
// Migration: add_is_primary_to_vehicles_table
public function up(): void
{
    Schema::table('vehicles', function (Blueprint $table) {
        $table->boolean('is_primary')
            ->default(false)
            ->after('photo_url')
            ->comment('Primary motor for the user');

        $table->index(['user_id', 'is_primary']);
    });
}

public function down(): void
{
    Schema::table('vehicles', function (Blueprint $table) {
        $table->dropIndex(['user_id', 'is_primary']);
        $table->dropColumn('is_primary');
    });
}
```

---

## 🧪 Testing Scenarios

### Scenario 1: User Baru (Belum Ada Motor)

1. Login → Token OK
2. GET /vehicles/primary → **404** "Belum ada motor utama"
3. POST /vehicles (tambah motor pertama) → **201** + `is_primary: true`
4. GET /vehicles/primary → **200** + data motor utama

### Scenario 2: User Tambah Motor Kedua

1. GET /vehicles/primary → **200** Motor A (primary)
2. POST /vehicles (tambah motor B) → **201** + `is_primary: false`
3. GET /vehicles/primary → **200** Motor A (tetap primary)

### Scenario 3: User Ganti Motor Utama

1. GET /vehicles/primary → **200** Motor A (primary)
2. POST /vehicles/5/set-primary → **200** Motor B (jadi primary)
3. GET /vehicles/primary → **200** Motor B (primary baru)

### Scenario 4: User Hapus Motor Utama

1. GET /vehicles/primary → **200** Motor A (id: 3, primary)
2. DELETE /vehicles/3 → **200** Motor A dihapus
3. GET /vehicles/primary → **404** "Belum ada motor utama"
4. POST /vehicles/5/set-primary → **200** Set motor lain jadi primary

---

## 💡 Best Practices

### Frontend:

1. **Call GET /primary on app start** - Cek motor utama saat aplikasi dibuka
2. **Cache primary vehicle** - Simpan di state management (Redux/Context)
3. **Show "Ganti Motor" button** - Di navbar/settings untuk switch motor
4. **Handle 404 gracefully** - Redirect ke add/select vehicle jika belum ada primary

### Backend:

1. **Always verify ownership** - `WHERE user_id = $user->id`
2. **Use transactions** - Saat set primary (unset lain + set baru)
3. **Index optimization** - Composite index (user_id, is_primary)
4. **Clear messages** - Bedakan message motor pertama vs motor tambahan

---

## 📞 Support

**Dokumentasi Lain:**

-   `VEHICLE_API_GUIDE.md` - Complete vehicle API documentation
-   `POSTMAN_GUIDE.md` - Postman testing guide
-   `PRIMARY_VEHICLE_POSTMAN_TEST.md` - Quick test scenarios

**Artisan Commands:**

```bash
# Check routes
php artisan route:list --path=vehicles

# Check database
php artisan tinker
>>> User::find(1)->primaryVehicle

# Run migration
php artisan migrate

# Rollback
php artisan migrate:rollback
```

---

**Sistem Primary Vehicle siap digunakan! 🏍️✨**

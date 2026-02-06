# 🎯 VEHICLE SELECTOR API - DOKUMENTASI FRONTEND

## Konsep Active Vehicle

Sistem ini menggunakan konsep **Active Vehicle** di mana setiap user memilih satu kendaraan aktif yang akan digunakan sebagai konteks untuk semua fitur aplikasi (dashboard, tracking, reminder, riwayat servis).

### Keuntungan:

-   ✅ Frontend tidak perlu kirim `vehicle_id` di setiap request
-   ✅ Menghindari kesalahan pemilihan kendaraan
-   ✅ User experience lebih sederhana
-   ✅ Otomatis menggunakan kendaraan yang sedang dipilih

---

## 📍 BASE URL

```
http://localhost/api/v1/motorcycle
```

---

## 🔐 AUTHENTICATION

Semua endpoint memerlukan Bearer Token:

```
Authorization: Bearer {token_dari_login}
```

---

## 📋 ENDPOINTS

### 1. **POST - Pilih Kendaraan Aktif**

```
POST /vehicles/{id}/select
```

Endpoint ini digunakan untuk memilih kendaraan aktif. Hanya kendaraan milik user sendiri yang bisa dipilih.

**URL Parameters:**

-   `id` (integer, required) - ID kendaraan yang akan dijadikan aktif

**Headers:**

```json
{
    "Authorization": "Bearer {token}",
    "Accept": "application/json"
}
```

**Contoh Request:**

```javascript
// Pilih kendaraan dengan ID 3 sebagai aktif
const response = await fetch(
    "http://localhost/api/v1/motorcycle/vehicles/3/select",
    {
        method: "POST",
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
        },
    }
);

const data = await response.json();
```

**Response Sukses (200):**

```json
{
    "success": true,
    "message": "Kendaraan aktif berhasil dipilih",
    "data": {
        "id": 3,
        "user_id": 1,
        "title": "Honda Vario 125",
        "make": "Honda",
        "model": "Vario 125",
        "year": 2023,
        "tipe_motor": "matic",
        "vin": "ABC123456",
        "odometer": 5000,
        "license_plate": "B 1234 XYZ",
        "color": "Hitam",
        "photo_url": "vehicles/1703856000_motor.jpg",
        "created_at": "2024-12-30T10:00:00.000000Z",
        "updated_at": "2024-12-30T10:00:00.000000Z",
        "service_intervals": [
            {
                "id": 1,
                "service_name": "Ganti Oli Mesin",
                "next_due_km": 6000,
                "is_active": true
            }
        ]
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

### 2. **GET - Ambil Kendaraan Aktif**

```
GET /vehicles/active
```

Endpoint ini digunakan untuk mengambil data kendaraan yang sedang aktif. Biasanya dipanggil saat aplikasi dibuka atau saat user masuk ke dashboard.

**Headers:**

```json
{
    "Authorization": "Bearer {token}",
    "Accept": "application/json"
}
```

**Contoh Request:**

```javascript
const response = await fetch(
    "http://localhost/api/v1/motorcycle/vehicles/active",
    {
        method: "GET",
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
        },
    }
);

const data = await response.json();
```

**Response Sukses (200):**

```json
{
    "success": true,
    "message": "Data kendaraan aktif berhasil diambil",
    "data": {
        "id": 3,
        "user_id": 1,
        "title": "Honda Vario 125",
        "make": "Honda",
        "model": "Vario 125",
        "year": 2023,
        "tipe_motor": "matic",
        "vin": "ABC123456",
        "odometer": 5000,
        "license_plate": "B 1234 XYZ",
        "color": "Hitam",
        "photo_url": "vehicles/1703856000_motor.jpg",
        "created_at": "2024-12-30T10:00:00.000000Z",
        "updated_at": "2024-12-30T10:00:00.000000Z",
        "service_intervals": [
            {
                "id": 1,
                "service_name": "Ganti Oli Mesin",
                "service_type": "oil_change",
                "interval_km": 1000,
                "next_due_km": 6000,
                "description": "Ganti oli mesin rutin",
                "is_active": true
            }
        ],
        "service_histories": [
            {
                "id": 10,
                "service_type": "oil_change",
                "performed_at": "2024-12-25",
                "odometer": 5000,
                "cost_cents": 15000000,
                "service_provider": "Bengkel A"
            }
        ],
        "fuel_logs": [
            {
                "id": 5,
                "filled_at": "2024-12-28",
                "odometer": 5200,
                "liters": 4.5,
                "cost_cents": 6000000
            }
        ],
        "reminders": [
            {
                "id": 2,
                "title": "Ganti Ban Depan",
                "due_date": "2025-01-15",
                "is_completed": false
            }
        ]
    }
}
```

**Response Error - Belum Pilih Kendaraan (404):**

```json
{
    "success": false,
    "message": "Belum ada kendaraan aktif. Silakan pilih kendaraan terlebih dahulu."
}
```

**Response Error - Kendaraan Aktif Dihapus (404):**

```json
{
    "success": false,
    "message": "Kendaraan aktif tidak ditemukan. Silakan pilih kendaraan lain."
}
```

---

## 🔄 FLOW PENGGUNAAN DI FRONTEND

### 1. **Saat User Login Pertama Kali**

```javascript
// 1. Login user
const loginResponse = await login(email, password);
const token = loginResponse.data.token;

// 2. Ambil daftar kendaraan user
const vehiclesResponse = await fetch(
    "http://localhost/api/v1/motorcycle/vehicles",
    {
        headers: { Authorization: `Bearer ${token}` },
    }
);
const vehicles = vehiclesResponse.data;

// 3. Cek apakah user sudah punya active vehicle
const activeResponse = await fetch(
    "http://localhost/api/v1/motorcycle/vehicles/active",
    {
        headers: { Authorization: `Bearer ${token}` },
    }
);

if (activeResponse.success) {
    // User sudah punya kendaraan aktif, langsung ke dashboard
    const activeVehicle = activeResponse.data;
    navigateToDashboard(activeVehicle);
} else {
    // User belum pilih kendaraan, tampilkan vehicle selector
    showVehicleSelector(vehicles);
}
```

### 2. **Saat User Memilih Kendaraan**

```javascript
// User pilih kendaraan dari list
async function selectVehicle(vehicleId) {
    try {
        const response = await fetch(
            `http://localhost/api/v1/motorcycle/vehicles/${vehicleId}/select`,
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
            // Kendaraan berhasil dipilih
            const activeVehicle = result.data;

            // Simpan di state management (Redux/Context)
            setActiveVehicle(activeVehicle);

            // Redirect ke dashboard
            navigateToDashboard();
        }
    } catch (error) {
        console.error("Error selecting vehicle:", error);
    }
}
```

### 3. **Saat Aplikasi Dibuka Kembali**

```javascript
// Saat app start (useEffect atau componentDidMount)
useEffect(() => {
    async function loadActiveVehicle() {
        try {
            const response = await fetch(
                "http://localhost/api/v1/motorcycle/vehicles/active",
                {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                }
            );

            const result = await response.json();

            if (result.success) {
                // Set active vehicle ke state
                setActiveVehicle(result.data);
            } else {
                // User belum pilih atau kendaraan dihapus
                // Redirect ke vehicle selector
                navigateToVehicleSelector();
            }
        } catch (error) {
            console.error("Error loading active vehicle:", error);
        }
    }

    loadActiveVehicle();
}, []);
```

### 4. **Ganti Kendaraan Aktif**

```javascript
// Tampilkan tombol "Ganti Motor" di navbar/settings
async function changeActiveVehicle(newVehicleId) {
    try {
        const response = await fetch(
            `http://localhost/api/v1/motorcycle/vehicles/${newVehicleId}/select`,
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
            // Update state
            setActiveVehicle(result.data);

            // Refresh halaman atau reload data
            window.location.reload();
        }
    } catch (error) {
        console.error("Error changing vehicle:", error);
    }
}
```

---

## 🎨 CONTOH IMPLEMENTASI REACT

### **Vehicle Selector Component**

```jsx
import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";

function VehicleSelector() {
    const [vehicles, setVehicles] = useState([]);
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();
    const token = localStorage.getItem("token");

    useEffect(() => {
        fetchVehicles();
    }, []);

    const fetchVehicles = async () => {
        try {
            const response = await fetch(
                "http://localhost/api/v1/motorcycle/vehicles",
                {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                }
            );
            const data = await response.json();
            if (data.success) {
                setVehicles(data.data);
            }
        } catch (error) {
            console.error("Error fetching vehicles:", error);
        }
    };

    const handleSelectVehicle = async (vehicleId) => {
        setLoading(true);
        try {
            const response = await fetch(
                `http://localhost/api/v1/motorcycle/vehicles/${vehicleId}/select`,
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
                // Simpan active vehicle di localStorage atau state management
                localStorage.setItem(
                    "activeVehicle",
                    JSON.stringify(result.data)
                );

                // Redirect ke dashboard
                navigate("/dashboard");
            }
        } catch (error) {
            console.error("Error selecting vehicle:", error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="vehicle-selector">
            <h2>Pilih Motor Anda</h2>
            <div className="vehicle-list">
                {vehicles.map((vehicle) => (
                    <div
                        key={vehicle.id}
                        className="vehicle-card"
                        onClick={() => handleSelectVehicle(vehicle.id)}
                    >
                        {vehicle.photo_url && (
                            <img
                                src={`http://localhost/storage/${vehicle.photo_url}`}
                                alt={vehicle.title}
                            />
                        )}
                        <h3>{vehicle.title}</h3>
                        <p>
                            {vehicle.make} {vehicle.model} ({vehicle.year})
                        </p>
                        <p>Plat: {vehicle.license_plate}</p>
                        <p>Odometer: {vehicle.odometer} km</p>
                    </div>
                ))}
            </div>
            {loading && <div>Loading...</div>}
        </div>
    );
}

export default VehicleSelector;
```

### **Dashboard Component**

```jsx
import React, { useState, useEffect } from "react";

function Dashboard() {
    const [activeVehicle, setActiveVehicle] = useState(null);
    const [loading, setLoading] = useState(true);
    const token = localStorage.getItem("token");

    useEffect(() => {
        fetchActiveVehicle();
    }, []);

    const fetchActiveVehicle = async () => {
        try {
            const response = await fetch(
                "http://localhost/api/v1/motorcycle/vehicles/active",
                {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                }
            );

            const result = await response.json();

            if (result.success) {
                setActiveVehicle(result.data);
            } else {
                // Redirect ke vehicle selector jika belum ada active vehicle
                window.location.href = "/select-vehicle";
            }
        } catch (error) {
            console.error("Error fetching active vehicle:", error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <div>Loading...</div>;
    if (!activeVehicle) return null;

    return (
        <div className="dashboard">
            <div className="active-vehicle-card">
                <h2>{activeVehicle.title}</h2>
                {activeVehicle.photo_url && (
                    <img
                        src={`http://localhost/storage/${activeVehicle.photo_url}`}
                        alt={activeVehicle.title}
                    />
                )}
                <p>Plat: {activeVehicle.license_plate}</p>
                <p>Odometer: {activeVehicle.odometer} km</p>
            </div>

            <div className="service-reminders">
                <h3>Reminder Perawatan</h3>
                {activeVehicle.service_intervals?.map((interval) => (
                    <div key={interval.id}>
                        <p>{interval.service_name}</p>
                        <p>Next: {interval.next_due_km} km</p>
                    </div>
                ))}
            </div>

            <div className="recent-services">
                <h3>Riwayat Servis</h3>
                {activeVehicle.service_histories?.map((service) => (
                    <div key={service.id}>
                        <p>{service.service_type}</p>
                        <p>{service.performed_at}</p>
                        <p>Rp {service.cost_cents / 100}</p>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default Dashboard;
```

---

## ⚠️ ERROR HANDLING

| Status Code | Message                         | Handling                                       |
| ----------- | ------------------------------- | ---------------------------------------------- |
| 404         | Kendaraan tidak ditemukan       | Tampilkan error & redirect ke daftar kendaraan |
| 404         | Belum ada kendaraan aktif       | Redirect ke vehicle selector                   |
| 404         | Kendaraan aktif tidak ditemukan | Auto-clear & redirect ke vehicle selector      |
| 401         | Unauthenticated                 | Redirect ke login                              |
| 500         | Server error                    | Tampilkan error message                        |

---

## 💡 TIPS UNTUK FRONTEND

1. **Caching Active Vehicle**

    - Simpan data active vehicle di state management (Redux/Context)
    - Refresh data saat user ganti kendaraan atau pull-to-refresh

2. **Automatic Fallback**

    - Jika API return 404 (belum ada active vehicle), otomatis redirect ke vehicle selector
    - Jika kendaraan aktif dihapus, clear state & redirect ke selector

3. **Loading States**

    - Tampilkan skeleton/loading saat fetch active vehicle
    - Disable UI saat proses select vehicle

4. **Offline Support**

    - Cache active vehicle data di localStorage
    - Sync saat online kembali

5. **UX Best Practices**
    - Tampilkan tombol "Ganti Motor" di navbar/settings
    - Konfirmasi sebelum ganti motor (opsional)
    - Tampilkan indikator motor aktif di semua halaman

---

## 🔒 KEAMANAN

✅ **Validasi Kepemilikan**

-   Backend otomatis cek `user_id` sebelum set active vehicle
-   Hanya kendaraan milik user sendiri yang bisa dipilih

✅ **Token Authentication**

-   Semua endpoint butuh Bearer Token
-   Token expired = auto logout

✅ **Data Integrity**

-   Jika active vehicle dihapus, otomatis clear dari user
-   Soft delete pada kendaraan tetap maintain data integrity

---

## 📞 SUPPORT

Jika ada pertanyaan atau kendala integrasi, silakan hubungi tim backend.

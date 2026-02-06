# Quick Start Guide

## Motorcycle Management API - Autentikasi & Primary Vehicle

---

## 🚀 Setup Backend (5 Menit)

### 1. Clone & Install Dependencies

```bash
cd motorcycle_management
composer install
```

### 2. Database Setup

```bash
# Buat database di MySQL
mysql -u root -p
CREATE DATABASE motorcycle_management;
exit;

# Jalankan migration
php artisan migrate
```

### 3. Start Server

```bash
php artisan serve
# API running di http://localhost:8000
```

---

## 📱 Test Flow dengan Postman/Insomnia

### Flow 1: Register → Verify → Add Vehicle

#### Step 1: Register

```http
POST http://localhost:8000/api/auth/register
Content-Type: application/json

{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "081234567890",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "user": {...},
        "otp": "123456"  // Copy OTP ini
    }
}
```

#### Step 2: Verify Email

```http
POST http://localhost:8000/api/auth/verify-email
Content-Type: application/json

{
    "email": "test@example.com",
    "otp": "123456",  // OTP dari step 1
    "type": "email_verification"
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "user": {...},
        "access_token": "1|xxxx",  // Save ini
        "refresh_token": "yyyy",    // Save ini
        "token_type": "Bearer",
        "expires_in": 1800
    }
}
```

#### Step 3: Add Vehicle (akan otomatis jadi primary)

```http
POST http://localhost:8000/api/vehicles
Authorization: Bearer {access_token dari step 2}
Content-Type: application/json

{
    "title": "Honda PCX Saya",
    "make": "Honda",
    "model": "PCX 160",
    "year": 2023,
    "tipe_motor": "matic",
    "odometer": 5000,
    "license_plate": "B 1234 XYZ",
    "color": "Hitam"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Kendaraan berhasil ditambahkan dan ditetapkan sebagai motor utama",
    "data": {
        "id": 1,
        "is_primary": true,  // ✓ Otomatis primary
        ...
    }
}
```

#### Step 4: Get Primary Vehicle

```http
GET http://localhost:8000/api/vehicles/primary
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "message": "Data motor utama berhasil diambil",
    "data": {
        "id": 1,
        "title": "Honda PCX Saya",
        "is_primary": true,
        ...
    }
}
```

---

### Flow 2: Refresh Token

#### Step 1: Wait 31 minutes (atau test langsung)

```bash
# Simulasi access token expired
# Dalam production, frontend akan auto-detect ini
```

#### Step 2: Refresh Token

```http
POST http://localhost:8000/api/auth/refresh-token
Content-Type: application/json

{
    "refresh_token": "yyyy",  // Refresh token dari verify/login
    "email": "test@example.com"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Token berhasil diperbaharui",
    "data": {
        "access_token": "2|new_token", // Token baru
        "refresh_token": "new_refresh", // Refresh token baru
        "token_type": "Bearer",
        "expires_in": 1800
    }
}
```

#### Step 3: Test dengan token baru

```http
GET http://localhost:8000/api/auth/me
Authorization: Bearer {access_token baru}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Test User",
            ...
        }
    }
}
```

---

### Flow 3: Ganti Primary Vehicle

#### Step 1: Add motor kedua

```http
POST http://localhost:8000/api/vehicles
Authorization: Bearer {access_token}
Content-Type: application/json

{
    "title": "Yamaha NMAX Saya",
    "make": "Yamaha",
    "model": "NMAX 155",
    "year": 2024,
    "tipe_motor": "matic",
    "odometer": 1000,
    "license_plate": "B 5678 ABC",
    "color": "Merah"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Kendaraan berhasil ditambahkan",
    "data": {
        "id": 2,
        "is_primary": false,  // Tidak otomatis primary (sudah ada motor primary)
        ...
    }
}
```

#### Step 2: List semua motor

```http
GET http://localhost:8000/api/vehicles
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Honda PCX Saya",
            "is_primary": true // ✓ Primary
        },
        {
            "id": 2,
            "title": "Yamaha NMAX Saya",
            "is_primary": false // ✗ Bukan primary
        }
    ]
}
```

#### Step 3: Set motor kedua sebagai primary

```http
POST http://localhost:8000/api/vehicles/2/set-primary
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "message": "Motor utama berhasil diubah",
    "data": {
        "id": 2,
        "title": "Yamaha NMAX Saya",
        "is_primary": true // ✓ Sekarang primary
    }
}
```

#### Step 4: Verify primary vehicle sudah berubah

```http
GET http://localhost:8000/api/vehicles/primary
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 2, // ✓ Sudah motor NMAX
        "title": "Yamaha NMAX Saya",
        "is_primary": true
    }
}
```

#### Step 5: Check list lagi (optional)

```http
GET http://localhost:8000/api/vehicles
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Honda PCX Saya",
            "is_primary": false // ✓ Sudah tidak primary
        },
        {
            "id": 2,
            "title": "Yamaha NMAX Saya",
            "is_primary": true // ✓ Sekarang primary
        }
    ]
}
```

---

### Flow 4: Logout

```http
POST http://localhost:8000/api/auth/logout
Authorization: Bearer {access_token}
```

**Response:**

```json
{
    "success": true,
    "message": "Logout berhasil"
}
```

**Test token sudah invalid:**

```http
GET http://localhost:8000/api/auth/me
Authorization: Bearer {access_token lama}
```

**Response:**

```json
{
    "message": "Unauthenticated."
}
```

---

## 🔧 Frontend Implementation Guide

### React Native Example

#### 1. Install Dependencies

```bash
npm install axios @react-native-async-storage/async-storage
# or
yarn add axios @react-native-async-storage/async-storage
```

#### 2. Create API Service

```javascript
// src/services/api.js
import axios from "axios";
import AsyncStorage from "@react-native-async-storage/async-storage";

const API_BASE_URL = "http://localhost:8000/api";

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        "Content-Type": "application/json",
    },
});

// Request Interceptor: Attach access token
api.interceptors.request.use(
    async (config) => {
        const accessToken = await AsyncStorage.getItem("access_token");
        if (accessToken) {
            config.headers.Authorization = `Bearer ${accessToken}`;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Response Interceptor: Auto-refresh token on 401
api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config;

        // If 401 and not already retried
        if (error.response?.status === 401 && !originalRequest._retry) {
            originalRequest._retry = true;

            try {
                const refreshToken = await AsyncStorage.getItem(
                    "refresh_token"
                );
                const email = await AsyncStorage.getItem("email");

                if (!refreshToken || !email) {
                    // No refresh token, redirect to login
                    throw new Error("No refresh token");
                }

                // Call refresh token endpoint
                const response = await axios.post(
                    `${API_BASE_URL}/auth/refresh-token`,
                    {
                        refresh_token: refreshToken,
                        email: email,
                    }
                );

                const { access_token, refresh_token: newRefreshToken } =
                    response.data.data;

                // Save new tokens
                await AsyncStorage.setItem("access_token", access_token);
                await AsyncStorage.setItem("refresh_token", newRefreshToken);

                // Retry original request with new token
                originalRequest.headers.Authorization = `Bearer ${access_token}`;
                return api(originalRequest);
            } catch (refreshError) {
                // Refresh token failed, logout user
                await AsyncStorage.clear();
                // Navigate to login screen (gunakan navigation service)
                return Promise.reject(refreshError);
            }
        }

        return Promise.reject(error);
    }
);

export default api;
```

#### 3. Auth Functions

```javascript
// src/services/auth.js
import api from "./api";
import AsyncStorage from "@react-native-async-storage/async-storage";

export const register = async (name, email, phone, password) => {
    const response = await api.post("/auth/register", {
        name,
        email,
        phone,
        password,
        password_confirmation: password,
    });
    return response.data;
};

export const verifyEmail = async (email, otp) => {
    const response = await api.post("/auth/verify-email", {
        email,
        otp,
        type: "email_verification",
    });

    const { access_token, refresh_token, user } = response.data.data;

    // Save tokens
    await AsyncStorage.setItem("access_token", access_token);
    await AsyncStorage.setItem("refresh_token", refresh_token);
    await AsyncStorage.setItem("email", user.email);

    return response.data;
};

export const login = async (email, password, deviceId, deviceName) => {
    const response = await api.post("/auth/login", {
        email,
        password,
        device_id: deviceId,
        device_name: deviceName,
    });

    const { access_token, refresh_token, user } = response.data.data;

    // Save tokens
    await AsyncStorage.setItem("access_token", access_token);
    await AsyncStorage.setItem("refresh_token", refresh_token);
    await AsyncStorage.setItem("email", user.email);

    return response.data;
};

export const logout = async () => {
    await api.post("/auth/logout");
    await AsyncStorage.clear();
};

export const isAuthenticated = async () => {
    const refreshToken = await AsyncStorage.getItem("refresh_token");
    return !!refreshToken;
};
```

#### 4. Vehicle Functions

```javascript
// src/services/vehicle.js
import api from "./api";

export const getPrimaryVehicle = async () => {
    const response = await api.get("/vehicles/primary");
    return response.data.data;
};

export const getAllVehicles = async () => {
    const response = await api.get("/vehicles");
    return response.data.data;
};

export const addVehicle = async (vehicleData) => {
    const response = await api.post("/vehicles", vehicleData);
    return response.data.data;
};

export const setPrimaryVehicle = async (vehicleId) => {
    const response = await api.post(`/vehicles/${vehicleId}/set-primary`);
    return response.data.data;
};
```

#### 5. App Entry Point

```javascript
// App.js
import React, { useEffect, useState } from "react";
import { NavigationContainer } from "@react-navigation/native";
import { createNativeStackNavigator } from "@react-navigation/native-stack";
import { isAuthenticated } from "./services/auth";
import { getPrimaryVehicle } from "./services/vehicle";

// Screens
import LoginScreen from "./screens/LoginScreen";
import RegisterScreen from "./screens/RegisterScreen";
import VerifyEmailScreen from "./screens/VerifyEmailScreen";
import AddVehicleScreen from "./screens/AddVehicleScreen";
import DashboardScreen from "./screens/DashboardScreen";

const Stack = createNativeStackNavigator();

export default function App() {
    const [isLoading, setIsLoading] = useState(true);
    const [isAuth, setIsAuth] = useState(false);
    const [hasPrimaryVehicle, setHasPrimaryVehicle] = useState(false);

    useEffect(() => {
        checkAuthStatus();
    }, []);

    const checkAuthStatus = async () => {
        try {
            const authenticated = await isAuthenticated();
            setIsAuth(authenticated);

            if (authenticated) {
                // Check if user has primary vehicle
                try {
                    await getPrimaryVehicle();
                    setHasPrimaryVehicle(true);
                } catch (error) {
                    // No primary vehicle
                    setHasPrimaryVehicle(false);
                }
            }
        } catch (error) {
            console.error("Auth check error:", error);
        } finally {
            setIsLoading(false);
        }
    };

    if (isLoading) {
        return <LoadingScreen />;
    }

    return (
        <NavigationContainer>
            <Stack.Navigator>
                {!isAuth ? (
                    // Auth Stack
                    <>
                        <Stack.Screen name="Login" component={LoginScreen} />
                        <Stack.Screen
                            name="Register"
                            component={RegisterScreen}
                        />
                        <Stack.Screen
                            name="VerifyEmail"
                            component={VerifyEmailScreen}
                        />
                    </>
                ) : !hasPrimaryVehicle ? (
                    // No primary vehicle, show add vehicle
                    <Stack.Screen
                        name="AddVehicle"
                        component={AddVehicleScreen}
                        options={{ title: "Tambah Motor Pertama" }}
                    />
                ) : (
                    // Main App Stack
                    <>
                        <Stack.Screen
                            name="Dashboard"
                            component={DashboardScreen}
                        />
                        {/* Other screens */}
                    </>
                )}
            </Stack.Navigator>
        </NavigationContainer>
    );
}
```

#### 6. Dashboard Screen Example

```javascript
// screens/DashboardScreen.js
import React, { useEffect, useState } from "react";
import { View, Text, Button } from "react-native";
import { getPrimaryVehicle } from "../services/vehicle";
import { logout } from "../services/auth";

export default function DashboardScreen({ navigation }) {
    const [vehicle, setVehicle] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadPrimaryVehicle();
    }, []);

    const loadPrimaryVehicle = async () => {
        try {
            const data = await getPrimaryVehicle();
            setVehicle(data);
        } catch (error) {
            console.error("Error loading primary vehicle:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleLogout = async () => {
        await logout();
        navigation.reset({ index: 0, routes: [{ name: "Login" }] });
    };

    if (loading) {
        return <Text>Loading...</Text>;
    }

    return (
        <View>
            <Text>Motor Utama: {vehicle?.title}</Text>
            <Text>
                Model: {vehicle?.make} {vehicle?.model}
            </Text>
            <Text>Odometer: {vehicle?.odometer} km</Text>

            <Button
                title="Ganti Motor"
                onPress={() => navigation.navigate("VehicleSelector")}
            />

            <Button title="Logout" onPress={handleLogout} />
        </View>
    );
}
```

---

## ✅ Testing Checklist

Setelah implementasi, test flow berikut:

### ✓ Flow Register & Login

-   [ ] Register → Dapat OTP
-   [ ] Verify OTP → Dapat token
-   [ ] Login → Dapat token
-   [ ] Login dengan email belum verified → Error + kirim OTP

### ✓ Flow Token

-   [ ] Request API dengan access token → Success
-   [ ] Access token expired → Auto-refresh → Success
-   [ ] Refresh token → Dapat token baru
-   [ ] Logout → Token invalid

### ✓ Flow Primary Vehicle

-   [ ] Add motor pertama → Otomatis primary
-   [ ] GET /vehicles/primary → Return motor primary
-   [ ] Add motor kedua → Tidak otomatis primary
-   [ ] Set motor kedua sebagai primary → Motor pertama jadi non-primary
-   [ ] GET /vehicles/primary → Return motor baru

### ✓ Edge Cases

-   [ ] User tanpa motor → GET /vehicles/primary return 404
-   [ ] Set non-existent vehicle sebagai primary → Error 404
-   [ ] Set vehicle milik user lain sebagai primary → Error 404

---

## 🐛 Common Issues

### Issue 1: Token expired immediately

**Cause**: Sanctum expiration tidak di-set
**Fix**:

```php
$token = $user->createToken('auth_token', ['*'], now()->addMinutes(30));
```

### Issue 2: CORS error di frontend

**Cause**: CORS tidak dikonfigurasi
**Fix**:

```bash
# Update config/cors.php
'paths' => ['api/*'],
'allowed_origins' => ['http://localhost:3000'],
```

### Issue 3: Refresh token selalu invalid

**Cause**: Token tidak di-hash konsisten
**Fix**: Check hash di `User::generateRefreshToken()` dan `User::verifyRefreshToken()`

---

## 📚 Next Steps

Setelah autentikasi & primary vehicle selesai, implement fitur berikutnya:

1. **Dashboard Endpoint** - GET /dashboard (auto-use primary vehicle)
2. **Trip Tracking** - POST /trips (auto-associate dengan primary vehicle)
3. **Service History** - GET /service-histories (filter by primary vehicle)
4. **Reminders** - GET /reminders (filter by primary vehicle)

Semua endpoint otomatis pakai primary vehicle tanpa perlu parameter tambahan! 🎉

---

## 🎉 Done!

Backend sudah siap production dengan:

-   ✅ Refresh token system (30 days)
-   ✅ Auto-refresh seamless
-   ✅ Primary vehicle concept
-   ✅ No interruption UX

**Happy coding!** 🚀

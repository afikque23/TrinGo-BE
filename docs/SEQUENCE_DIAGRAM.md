# SEQUENCE DIAGRAM - SISTEM SMART MOTORCYCLE MANAGEMENT

Dokumen ini menjelaskan alur interaksi antar komponen sistem melalui Sequence Diagram untuk setiap skenario utama.

---

## 1. SEQUENCE DIAGRAM: Alur Registrasi & Login

```mermaid
sequenceDiagram
    actor User as User (Mobile App)
    participant App as Flutter App
    participant API as Backend API<br/>(Laravel)
    participant DB as Database<br/>(MySQL)
    participant Email as Email Service

    User->>App: Klik Register
    App->>API: POST /auth/register<br/>(email, password, phone)

    API->>DB: Cek email duplicate
    DB-->>API: Email belum terdaftar

    API->>DB: INSERT ke tabel users<br/>(create new user)
    DB-->>API: User ID = 123

    API->>DB: INSERT ke tabel otp_verifications<br/>(generate OTP code)
    DB-->>API: OTP saved

    API->>Email: Send OTP email<br/>(to user email)
    Email-->>API: Email sent

    API-->>App: 200 OK<br/>{message: 'OTP sent'}
    App-->>User: Tampilkan form verifikasi OTP

    User->>App: Input OTP & Klik Verifikasi
    App->>API: POST /auth/verify-email<br/>(email, otp_code)

    API->>DB: SELECT otp_verifications<br/>WHERE email & code match
    DB-->>API: OTP valid & not expired

    API->>DB: UPDATE users SET email_verified_at
    DB-->>API: User verified

    API-->>App: 200 OK<br/>{message: 'Email verified'}
    App-->>User: Tampilkan login form

    User->>App: Input email & password, Klik Login
    App->>API: POST /auth/login<br/>(email, password)

    API->>DB: SELECT users WHERE email
    DB-->>API: User found

    API->>API: Verify password hash<br/>(bcrypt match)

    API->>DB: INSERT INTO personal_access_tokens<br/>(create Sanctum token)
    DB-->>API: Token created

    API-->>App: 200 OK<br/>{access_token, refresh_token, expires_in}
    App->>App: Simpan token ke SharedPreferences
    App-->>User: Login berhasil!<br/>Redirect ke Dashboard
```

---

## 2. SEQUENCE DIAGRAM: Alur Smart Maintenance - AI Recommendation

```mermaid
sequenceDiagram
    actor User as User (Mobile App)
    participant App as Flutter App
    participant API as Backend API<br/>(Laravel)
    participant DB as Database<br/>(MySQL)
    participant Fuzzy as Fuzzy Logic<br/>Engine
    participant Gemini as Google Gemini<br/>(External API)

    User->>App: Buka tab "Home Insight"
    App->>API: GET /motors/{motorId}/home-insight<br/>Header: Authorization Bearer Token

    API->>DB: SELECT vehicles WHERE id = motorId
    DB-->>API: Vehicle data (odometer, last_service)

    API->>DB: SELECT service_histories<br/>WHERE vehicle_id<br/>ORDER BY created_at DESC
    DB-->>API: Service records

    API->>DB: SELECT motor_type_components<br/>WHERE motor_type_id
    DB-->>API: Component list<br/>(Oli, Filter, Busi, V-Belt, etc)

    API->>Fuzzy: Calculate component scores<br/>(current_odometer vs interval)
    Fuzzy->>Fuzzy: Compute Fuzzy Rules<br/>(Low/Medium/High membership)
    Fuzzy-->>API: Component scores array<br/>(0-100%)

    API->>API: Build Prompt<br/>for Gemini
    Note over API: Prompt Template:<br/>"Motor kami punya kondisi oli 75%,<br/>busi 45%, filter 80%.<br/>Berikan rekomendasi layak jalan?"

    API->>Gemini: POST /api/generateContent<br/>(model: gemini-pro,<br/>prompt: assembled text)

    Gemini->>Gemini: Process natural language<br/>dengan LLM
    Gemini-->>API: Response text insight<br/>"Oli masih bagus, tapi busi segera<br/>diganti, filter udara sudah perlu bersih..."

    API->>DB: INSERT INTO ai_recommendation_caches<br/>(store result for 24hr)
    DB-->>API: Cache saved

    API-->>App: 200 OK<br/>{insight, component_scores,<br/>recommendations, cached_at}

    App->>App: Render UI<br/>- Hero text Insight<br/>- Component score cards<br/>- CTA "Pesan Servis"

    App-->>User: Tampilkan Home Insight<br/>dengan rekomendasi AI
```

---

## 3. SEQUENCE DIAGRAM: Alur GPS Tracking & Telemetry

```mermaid
sequenceDiagram
    actor User as User (Mobile App)
    participant App as Flutter App
    participant GPS as GPS Service<br/>(Location Plugin)
    participant MQTT as MQTT Broker
    participant API as Backend API
    participant Worker as Laravel Worker<br/>(Background Job)
    participant DB as Database

    User->>App: Tekan tombol "Mulai Berkendara"
    App->>API: POST /motors/{motorId}/tracking/start

    API->>DB: INSERT INTO trips<br/>(status: 'active', started_at: now)
    DB-->>API: Trip ID = 567

    API-->>App: 200 OK {trip_id: 567}

    App->>GPS: Initialize location stream<br/>(interval: 10 seconds)

    loop Every 10 seconds
        GPS->>GPS: Get current location<br/>(latitude, longitude, accuracy)
        GPS-->>App: Location object {lat, lng}

        App->>MQTT: Publish to topic<br/>"motorcycle/tracking/{user_id}/{device_id}"<br/>Payload: {lat, lng, accuracy, timestamp}

        MQTT->>MQTT: Broker receives message
        MQTT->>Worker: Trigger subscriber listener

        Worker->>Worker: Extract GPS payload
        Worker->>Worker: Calculate distance<br/>haversine formula<br/>(prev_point → current_point)

        Worker->>DB: INSERT INTO trip_points<br/>(trip_id, lat, lng, distance_meters)
        DB-->>Worker: Saved

        Worker->>DB: UPDATE trips<br/>SET total_distance += distance_meters
        DB-->>Worker: Updated
    end

    User->>App: Tekan tombol "Hentikan Berkendara"
    App->>GPS: Stop location stream

    App->>API: POST /motors/{motorId}/tracking/stop

    API->>DB: SELECT trips WHERE id = 567
    DB-->>API: Trip data (total_distance, duration)

    API->>DB: SELECT vehicles WHERE id = motorId
    DB-->>API: Vehicle odometer value

    API->>DB: UPDATE vehicles<br/>SET odometer += trip.total_distance
    DB-->>API: Vehicle odometer updated

    API->>DB: UPDATE trips<br/>SET status: 'completed', ended_at: now
    DB-->>API: Trip finalized

    API-->>App: 200 OK<br/>{trip_summary: {distance, duration, cost_estimate}}

    App-->>User: Tampilkan Trip Summary<br/>dengan detail jarak & waktu
```

---

## 4. SEQUENCE DIAGRAM: Alur Service Schedule Reminder & Notification

```mermaid
sequenceDiagram
    participant Scheduler as Laravel Scheduler<br/>(Cron Job)
    participant API as Backend API
    participant DB as Database
    participant FCM as Firebase FCM<br/>(Cloud Messaging)
    participant User as User Smartphone

    Note over Scheduler: Setiap hari jam 06:00 pagi

    Scheduler->>API: Trigger command<br/>artisan check:service-reminders

    API->>DB: SELECT service_schedules<br/>WHERE vehicle.user_id IS NOT NULL
    DB-->>API: List of schedules (100 records)

    loop For each service schedule
        API->>DB: SELECT vehicles<br/>WHERE id = schedule.vehicle_id
        DB-->>API: Vehicle (odometer, name)

        API->>API: Calculate reminder status:<br/>percent = (current_odometer<br/>/ max_interval_km) * 100

        alt percent >= threshold (e.g., 80%)
            API->>API: Service sudah wajib dilakukan

            alt is_notified = false
                API->>DB: SELECT device_tokens<br/>WHERE user_id
                DB-->>API: List FCM tokens

                API->>API: Compose notification message<br/>"Motor mu sudah 80% jarak servis.<br/>Pesan servis sekarang!"

                loop For each device token
                    API->>FCM: POST /send<br/>{token, title, body, data}
                    FCM-->>API: 200 Success
                    FCM->>User: Push notification received
                    User-->>User: Notification bell rings<br/>(Heads-up display)
                end

                API->>DB: UPDATE service_schedules<br/>SET is_notified = true<br/>notification_sent_at = now
                DB-->>API: Updated
            end
        else
            API->>API: Service masih aman, skip
        end
    end

    Scheduler-->>API: Cron job completed
```

---

## 5. SEQUENCE DIAGRAM: Alur Tips/Content Interaction

```mermaid
sequenceDiagram
    actor User as User (Mobile App)
    participant App as Flutter App
    participant API as Backend API
    participant DB as Database

    User->>App: Buka tab "Tips & Trik"
    App->>API: GET /public/tips<br/>(no auth required)

    API->>DB: SELECT tips WHERE status='published'<br/>LIMIT 20
    DB-->>API: Tips list

    API-->>App: 200 OK {tips: [...]}
    App-->>User: Tampilkan grid tips

    User->>App: Klik tombol search "Ganti Oli"
    App->>API: GET /tips?search=ganti%20oli

    API->>DB: SELECT tips<br/>WHERE title LIKE '%ganti%oli%'<br/>OR description LIKE '%ganti%oli%'
    DB-->>API: Filtered tips

    API-->>App: 200 OK {tips: [...]}
    App-->>User: Tampilkan hasil pencarian

    User->>App: Klik tip "Cara Ganti Oli Berkualitas"
    App->>API: GET /tips/{tip_id}

    API->>DB: SELECT tips, tip_tags, tip_steps, tip_tools
    DB-->>API: Full tip details

    API-->>App: 200 OK {tip: {title, content, steps: [...], tools: [...]}}
    App-->>User: Tampilkan detail tip lengkap

    User->>App: Klik icon "Like" ❤️
    App->>API: POST /tips/{tip_id}/like<br/>Header: Authorization Bearer Token

    API->>DB: INSERT INTO tip_likes<br/>(user_id, tip_id)
    DB-->>API: Inserted

    API->>DB: UPDATE tips SET likes_count += 1
    DB-->>API: Updated

    API-->>App: 200 OK {liked: true}
    App->>App: Ubah icon like jadi filled ❤️
    App-->>User: Visual feedback "Sudah di-like"

    User->>App: Klik tombol "Terapkan Template"
    App->>API: POST /tips/{tip_id}/use-template<br/>(vehicle_id, reminder_type)

    API->>DB: INSERT INTO service_schedules<br/>(dari template tip ini)
    DB-->>API: Schedule created

    API-->>App: 200 OK {schedule_id, message: 'Schedule dibuat dari template'}
    App-->>User: "Jadwal servis dibuat! Lihat di menu Servis"
```

---

## 6. SEQUENCE DIAGRAM: Alur Admin Panel - Manage Content

```mermaid
sequenceDiagram
    actor Admin as Admin User
    participant Web as Admin Web Panel
    participant API as Backend API
    participant DB as Database
    participant Cache as Redis Cache

    Admin->>Web: Login ke admin panel
    Web->>API: POST /auth/login (role: admin)
    API->>DB: Verify user role = 'admin'
    DB-->>API: User is admin
    API-->>Web: Token granted

    Admin->>Web: Buka menu "Kelola Konten"
    Web->>API: GET /admin/contents

    API->>Cache: Check cache key 'admin_contents'
    alt Cache HIT
        Cache-->>API: Cached contents
    else Cache MISS
        API->>DB: SELECT contents
        DB-->>API: Contents list
        API->>Cache: Store result (5 min TTL)
    end

    API-->>Web: 200 OK {contents: [...]}
    Web-->>Admin: Tampilkan tabel konten

    Admin->>Web: Klik "Edit" pada content "Terms & Conditions"
    Web->>Web: Tampilkan form editor (rich text)

    Admin->>Web: Edit teks dan klik "Simpan"
    Web->>API: PATCH /admin/contents/{content_id}<br/>{title, body}

    API->>DB: UPDATE contents SET title, body, updated_at
    DB-->>API: Updated successfully

    API->>Cache: Invalidate cache 'admin_contents'
    Cache-->>API: Cache cleared

    API-->>Web: 200 OK {message: 'Content updated'}
    Web-->>Admin: Toast success "Konten berhasil diperbarui"
```

---

## Ringkasan Alur Proses

| Alur                   | Aktor              | Endpoint                        | Teknologi                  |
| ---------------------- | ------------------ | ------------------------------- | -------------------------- |
| **Registrasi & Login** | User               | `/auth/register`, `/auth/login` | Sanctum, Email Service     |
| **AI Recommendation**  | User               | `/motors/{id}/home-insight`     | Fuzzy Logic, Gemini API    |
| **GPS Tracking**       | User + System      | `/tracking/start/stop`          | MQTT, Location Plugin      |
| **Service Reminder**   | System + Scheduler | `artisan check:reminders`       | Cron Job, FCM              |
| **Tips Management**    | User               | `/tips`, `/tips/{id}/like`      | Database, Full-text Search |
| **Admin Management**   | Admin              | `/admin/contents`               | Database, Cache            |

---

**Catatan untuk Skripsi:**

- Diagram ini bisa Anda masukkan ke Bab 4 (Perancangan Sistem) sebagai visual sequence diagram
- Jelaskan setiap tahap interaksi dan teknologi yang digunakan
- Sertakan daftar HTTP methods dan status codes yang relevan

# DOKUMENTASI TEKNIS: FUZZY LOGIC & AI GEMINI INTEGRATION

Bagian ini menjelaskan algoritma cerdas dan integrasi AI yang menjadi inti dari fitur Smart Maintenance.

---

## 1. FUZZY LOGIC SYSTEM (Logika Samar/Kabur)

### 1.1 Konsep Dasar Fuzzy Logic

Fuzzy Logic adalah metodologi perhitungan yang memungkinkan nilai kebenaran berada di antara 0 dan 1 (tidak hanya True/False seperti logika klasik). Sistem ini cocok untuk mengukur kondisi yang "bersifat gradual" seperti umur komponen motor.

**Contoh:**

- Logika Klasik: Oli "sudah kotor" (True/False)
- Fuzzy Logic: Oli "70% kotor" (Nilai antara 0-1)

### 1.2 Membership Function

Membership function menentukan derajat keanggotaan suatu nilai dalam sebuah kategori.

**Untuk Komponen Motor, kami gunakan 3 kategori:**

```
Kondisi Komponen:
  - GREEN (Aman): 0% - 40% pemakaian
  - YELLOW (Waspada): 40% - 70% pemakaian
  - RED (Kritis): 70% - 100% pemakaian
```

**Visualisasi Membership Function:**

```
Derajat Keanggotaan (μ)
    1.0 |     ╱╲           ╱╲           ╱╲
        |    ╱  ╲         ╱  ╲         ╱  ╲
    0.7 |   ╱    ╲       ╱    ╲       ╱    ╲
        |  ╱      ╲     ╱      ╲     ╱      ╲
    0.5 | ╱        ╲   ╱        ╲   ╱        ╲
        |╱          ╲ ╱          ╲ ╱          ╲
    0.0 |____________╲____________╱___________╱____
        0%          40%          70%         100%  Pemakaian

        [GREEN]    [YELLOW]      [RED]
        (Aman)    (Waspada)    (Kritis)
```

### 1.3 Fuzzy Rules (Aturan)

Aturan fuzzy mendefinisikan hubungan antara masukan dan keluaran:

```
Rule 1: IF (Odometer Usage = GREEN) THEN Status = AMAN
Rule 2: IF (Odometer Usage = YELLOW) THEN Status = WASPADA
Rule 3: IF (Odometer Usage = RED) THEN Status = KRITIS

Rule 4: IF (Time Since Service = GREEN) AND (Odometer = GREEN)
        THEN Status = AMAN
Rule 5: IF (Time Since Service = YELLOW) OR (Odometer = YELLOW)
        THEN Status = WASPADA
Rule 6: IF (Time Since Service = RED) OR (Odometer = RED)
        THEN Status = KRITIS
```

### 1.4 Implementasi di Backend Laravel

**File: `app/Services/FuzzyLogicService.php`**

```php
<?php
namespace App\Services;

class FuzzyLogicService
{
    /**
     * Hitung skor kondisi komponen menggunakan Fuzzy Logic
     *
     * @param string $componentSlug - Nama komponen (oli, busi, filter, etc)
     * @param float $currentOdometer - Odometer saat ini
     * @param datetime $lastServiceDate - Tanggal servis terakhir
     * @return array {status: 'GREEN|YELLOW|RED', score: 0-100, recommendation: string}
     */
    public function evaluateComponentHealth(
        $componentSlug,
        $currentOdometer,
        $lastServiceDate
    ) {
        // 1. Get component configuration dari database
        $config = MotorTypeComponent::where('slug', $componentSlug)
            ->with('fuzzyConfig')
            ->first();

        if (!$config) {
            return ['status' => 'UNKNOWN', 'score' => 0];
        }

        // 2. Calculate Odometer Usage Percentage
        $odometerPercent = ($currentOdometer / $config->max_interval_km) * 100;

        // 3. Calculate Time Usage Percentage
        $daysSinceService = now()->diffInDays($lastServiceDate);
        $timePercent = ($daysSinceService / ($config->max_interval_months * 30)) * 100;

        // 4. Get membership degrees (Fuzzification)
        $odometerMembership = $this->calculateMembership($odometerPercent);
        $timeMembership = $this->calculateMembership($timePercent);

        // 5. Apply Fuzzy Rules (Inference)
        $resultStatus = $this->applyFuzzyRules(
            $odometerMembership,
            $timeMembership
        );

        // 6. Calculate final score (Defuzzification)
        $finalScore = $this->calculateFuzzyScore(
            $odometerMembership,
            $timeMembership,
            $resultStatus
        );

        return [
            'component' => $config->component_name,
            'status' => $resultStatus,
            'score' => round($finalScore, 2),
            'odometer_usage_pct' => round($odometerPercent, 2),
            'time_usage_pct' => round($timePercent, 2),
            'recommendation' => $this->getRecommendation($resultStatus, $finalScore)
        ];
    }

    /**
     * Hitung membership degree untuk nilai tertentu
     * Gunakan triangular membership function
     */
    private function calculateMembership($value)
    {
        $thresholds = [
            'green' => ['min' => 0, 'peak' => 20, 'max' => 40],
            'yellow' => ['min' => 35, 'peak' => 55, 'max' => 75],
            'red' => ['min' => 70, 'peak' => 85, 'max' => 100]
        ];

        $result = [];

        // Calculate triangular membership for GREEN
        if ($value < $thresholds['green']['peak']) {
            $result['green'] = ($value - $thresholds['green']['min']) /
                              ($thresholds['green']['peak'] - $thresholds['green']['min']);
        } else {
            $result['green'] = ($thresholds['green']['max'] - $value) /
                              ($thresholds['green']['max'] - $thresholds['green']['peak']);
        }
        $result['green'] = max(0, min(1, $result['green']));

        // Calculate triangular membership for YELLOW
        if ($value < $thresholds['yellow']['peak']) {
            $result['yellow'] = ($value - $thresholds['yellow']['min']) /
                               ($thresholds['yellow']['peak'] - $thresholds['yellow']['min']);
        } else {
            $result['yellow'] = ($thresholds['yellow']['max'] - $value) /
                               ($thresholds['yellow']['max'] - $thresholds['yellow']['peak']);
        }
        $result['yellow'] = max(0, min(1, $result['yellow']));

        // Calculate triangular membership for RED
        if ($value < $thresholds['red']['peak']) {
            $result['red'] = ($value - $thresholds['red']['min']) /
                            ($thresholds['red']['peak'] - $thresholds['red']['min']);
        } else {
            $result['red'] = ($thresholds['red']['max'] - $value) /
                            ($thresholds['red']['max'] - $thresholds['red']['peak']);
        }
        $result['red'] = max(0, min(1, $result['red']));

        return $result;
    }

    /**
     * Apply Fuzzy Rules (Inference Engine)
     */
    private function applyFuzzyRules($odometerMem, $timeMem)
    {
        // Rule: IF (odometer = RED) OR (time = RED) THEN result = RED
        if ($odometerMem['red'] > 0.5 || $timeMem['red'] > 0.5) {
            return 'RED';
        }

        // Rule: IF (odometer = YELLOW) OR (time = YELLOW) THEN result = YELLOW
        if ($odometerMem['yellow'] > 0.5 || $timeMem['yellow'] > 0.5) {
            return 'YELLOW';
        }

        // Rule: IF (odometer = GREEN) AND (time = GREEN) THEN result = GREEN
        return 'GREEN';
    }

    /**
     * Convert fuzzy result to numeric score (Defuzzification)
     * Menggunakan Center of Gravity (CoG) method
     */
    private function calculateFuzzyScore($odometerMem, $timeMem, $status)
    {
        // Weighted average dari membership degrees
        $score = (
            ($odometerMem['red'] * 100) +
            ($odometerMem['yellow'] * 70) +
            ($odometerMem['green'] * 20)
        ) / 3;

        // Tambah bobot dari time-based membership
        $score = ($score + (
            ($timeMem['red'] * 100) +
            ($timeMem['yellow'] * 70) +
            ($timeMem['green'] * 20)
        ) / 3) / 2;

        return $score;
    }

    /**
     * Get human-readable recommendation
     */
    private function getRecommendation($status, $score)
    {
        if ($status === 'RED') {
            return "⚠️ KRITIS - Segera lakukan perbaikan/penggantian komponen ini!";
        } elseif ($status === 'YELLOW') {
            return "⚠️ WASPADA - Monitor keadaan, rencanakan servis dalam waktu dekat";
        } else {
            return "✅ AMAN - Kondisi baik, lanjutkan perawatan rutin";
        }
    }
}
```

### 1.5 Contoh Kasus Fuzzy Logic

**Skenario: Evaluasi Kondisi Oli**

| Parameter           | Nilai                      | Perhitungan           |
| ------------------- | -------------------------- | --------------------- |
| Max interval oli    | 5000 km                    | Batas maksimal        |
| Odometer saat ini   | 3500 km                    | 70% dari max          |
| Servis oli terakhir | 45 hari lalu               | 75% dari 60 hari      |
| Membership GREEN    | 0.2                        | Score: 20%            |
| Membership YELLOW   | 0.7                        | Score: 70% (Dominant) |
| Membership RED      | 0.1                        | Score: 10%            |
| **Hasil Akhir**     | **YELLOW**                 | **Status: Waspada**   |
| **Score Numerik**   | **63.33**                  | **(0-100 scale)**     |
| **Rekomendasi**     | Monitor, rencanakan servis | User harus aware      |

---

## 2. GOOGLE GEMINI AI INTEGRATION

### 2.1 Konsep Prompt Engineering

Google Gemini adalah Large Language Model (LLM) yang dapat memahami konteks dan menghasilkan teks natural language. Kami mengintegrasikannya untuk mengubah skor Fuzzy menjadi rekomendasi yang humanis dan actionable.

**Flow:**

```
Fuzzy Logic Scores
     ↓
Prompt Template (Instruction Injection)
     ↓
Google Gemini API
     ↓
Natural Language Insight
     ↓
Display to User
```

### 2.2 Prompt Template

**File: `resources/prompts/maintenance_insight.txt`**

```
Anda adalah mekanik motor profesional dengan pengalaman 20 tahun.
Anda memberikan konsultasi kepada pemilik motor tentang kondisi kendaraannya.

DATA KENDARAAN:
- Merek/Tipe: {motor_type}
- Odometer Saat Ini: {current_odometer} km
- Terakhir Servis: {last_service_date}

KONDISI KOMPONEN (Fuzzy Logic Scores):
{component_scores}

INSTRUKSI:
1. Buat "Home Insight" yang singkat (2-3 baris) dengan tone santai tapi profesional
2. Highlight komponen yang paling urgent (status RED)
3. Berikan saran actionable: "Segera kunjungi bengkel X untuk ganti oli"
4. Gunakan emoji untuk visual appeal
5. Bahasa Indonesia, santai, mudah dipahami

OUTPUT FORMAT:
Insight: [3 baris narasi]
TopPriority: [Komponen paling critical]
NextSteps: [Langkah konkrit yang harus dilakukan]
```

### 2.3 Implementasi di Backend Laravel

**File: `app/Services/GeminiAIService.php`**

```php
<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeminiAIService
{
    private $apiKey;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Generate Home Insight untuk vehicle menggunakan Fuzzy Scores
     *
     * @param $vehicleId
     * @param array $fuzzyScores
     * @return string
     */
    public function generateHomeInsight($vehicleId, array $fuzzyScores)
    {
        // 1. Cek cache dulu (24 jam)
        $cacheKey = "ai_insight_vehicle_{$vehicleId}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 2. Build component scores text
        $componentText = $this->formatComponentScores($fuzzyScores);

        // 3. Baca prompt template
        $promptTemplate = file_get_contents(
            resource_path('prompts/maintenance_insight.txt')
        );

        // 4. Inject data ke prompt
        $prompt = strtr($promptTemplate, [
            '{motor_type}' => $vehicle->brand . ' ' . $vehicle->type,
            '{current_odometer}' => $vehicle->odometer,
            '{last_service_date}' => $lastServiceDate->format('d-m-Y'),
            '{component_scores}' => $componentText,
        ]);

        // 5. Call Gemini API
        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/gemini-pro:generateContent", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 500,
                    ]
                ], [
                    'key' => $this->apiKey
                ]);

            if ($response->failed()) {
                throw new \Exception('Gemini API Error: ' . $response->body());
            }

            // 6. Extract insight dari response
            $content = $response->json('contents.0.parts.0.text');

            // 7. Simpan ke cache (24 jam)
            Cache::put($cacheKey, $content, 60 * 60 * 24);

            return $content;

        } catch (\Exception $e) {
            \Log::error('Gemini API Error', [
                'vehicle_id' => $vehicleId,
                'error' => $e->getMessage()
            ]);

            // Fallback response jika API error
            return $this->generateFallbackInsight($fuzzyScores);
        }
    }

    /**
     * Format component scores menjadi teks readable
     */
    private function formatComponentScores(array $scores)
    {
        $text = "";
        foreach ($scores as $component => $data) {
            $status = $data['status'];
            $score = $data['score'];
            $emoji = match($status) {
                'RED' => '🔴',
                'YELLOW' => '🟡',
                'GREEN' => '🟢',
                default => '⚪'
            };

            $text .= sprintf(
                "%s %s: %.0f%% (%s)\n",
                $emoji,
                ucfirst($component),
                $score,
                $status
            );
        }
        return trim($text);
    }

    /**
     * Fallback response jika API Gemini error
     */
    private function generateFallbackInsight(array $scores)
    {
        $criticalComponents = collect($scores)
            ->filter(fn($s) => $s['status'] === 'RED')
            ->pluck('component');

        if ($criticalComponents->isEmpty()) {
            return "✅ Motor Anda dalam kondisi aman. Lanjutkan perawatan rutin untuk menjaga performa optimal.";
        }

        $components = $criticalComponents->join(', ');
        return "⚠️ Komponen {$components} sudah mencapai batas kritis. Segera kunjungi bengkel untuk perbaikan/penggantian.";
    }
}
```

### 2.4 API Integration Endpoint

**File: `app/Http/Controllers/RecommendationController.php`**

```php
<?php
namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use App\Services\FuzzyLogicService;
use App\Services\GeminiAIService;

class RecommendationController extends Controller
{
    public function homeInsight(Request $request, $motorId)
    {
        $fuzzyService = new FuzzyLogicService();
        $geminiService = new GeminiAIService();

        // 1. Get vehicle data
        $vehicle = Vehicle::where('id', $motorId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // 2. Calculate all component scores
        $components = [
            'oli' => [
                'odometer' => $vehicle->odometer,
                'last_service' => $vehicle->serviceHistories()
                    ->where('service_type_id', 1) // Oli change type
                    ->latest()
                    ->first()?->service_date
            ],
            'busi' => [...],
            'filter_udara' => [...],
            'v_belt' => [...],
        ];

        $fuzzyScores = [];
        foreach ($components as $component => $data) {
            $fuzzyScores[$component] = $fuzzyService->evaluateComponentHealth(
                $component,
                $data['odometer'],
                $data['last_service']
            );
        }

        // 3. Generate AI insight
        $insight = $geminiService->generateHomeInsight($motorId, $fuzzyScores);

        // 4. Return response
        return response()->json([
            'success' => true,
            'data' => [
                'insight' => $insight,
                'component_scores' => $fuzzyScores,
                'vehicle_status' => $vehicle->odometer,
                'generated_at' => now()
            ]
        ]);
    }

    public function scores(Request $request, $motorId)
    {
        $fuzzyService = new FuzzyLogicService();
        $vehicle = Vehicle::where('id', $motorId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Evaluate all components
        $scores = $fuzzyService->evaluateAllComponents($vehicle);

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle_name' => $vehicle->name,
                'current_odometer' => $vehicle->odometer,
                'components' => $scores,
            ]
        ]);
    }
}
```

### 2.5 Response JSON Example

```json
{
    "success": true,
    "data": {
        "insight": "🟢 Motor Honda CB150R Anda dalam kondisi prima! Oli masih bagus, hanya busi saja yang mulai memburuk. Rencanakan ganti busi dalam 2-3 minggu ke depan untuk performa maksimal.",
        "component_scores": {
            "oli": {
                "component": "Oli Mesin",
                "status": "GREEN",
                "score": 25.5,
                "recommendation": "✅ AMAN - Lanjutkan perawatan rutin"
            },
            "busi": {
                "component": "Busi",
                "status": "YELLOW",
                "score": 68.3,
                "recommendation": "⚠️ WASPADA - Monitor keadaan, rencanakan servis"
            },
            "filter_udara": {
                "component": "Filter Udara",
                "status": "YELLOW",
                "score": 55.0,
                "recommendation": "⚠️ WASPADA - Bersihkan atau ganti soon"
            },
            "v_belt": {
                "component": "V-Belt",
                "status": "GREEN",
                "score": 30.0,
                "recommendation": "✅ AMAN - Kondisi baik"
            }
        },
        "generated_at": "2026-07-11T10:30:00Z"
    }
}
```

---

## 3. ALGORITMA PERBANDINGAN

### 3.1 Fuzzy vs Traditional Logic

| Aspek           | Traditional Logic                | Fuzzy Logic                       |
| --------------- | -------------------------------- | --------------------------------- |
| Output          | True/False (0/1)                 | Kontinyu (0-1)                    |
| Kondisi Oli     | "Sudah kotor" atau "Belum"       | "70% kotor"                       |
| Keputusan       | Biner                            | Graduated                         |
| User Experience | Kejutan (tiba-tiba minta servis) | Progressive (peringatan bertahap) |
| Akurasi         | Lebih rendah                     | Lebih tinggi                      |

### 3.2 Fuzzy vs Machine Learning

| Aspek                | Fuzzy Logic                | Machine Learning           |
| -------------------- | -------------------------- | -------------------------- |
| Data Requirement     | Sedikit (expert knowledge) | Banyak (training data)     |
| Interpretability     | Tinggi (rules jelas)       | Rendah (black box)         |
| Training Time        | Cepat                      | Lambat                     |
| Real-time Prediction | Ya                         | Bisa, tapi kompleks        |
| Use Case             | Kontrol sistem, diagnosis  | Classification, prediction |

---

## 4. Performa & Optimasi

### 4.1 Caching Strategy

```php
// Cache Fuzzy Scores selama 24 jam
Cache::remember("fuzzy_scores_vehicle_{$vehicleId}", 60*60*24, function() {
    return $fuzzyService->evaluateAllComponents($vehicle);
});

// Cache Gemini Insight selama 24 jam
Cache::remember("ai_insight_vehicle_{$vehicleId}", 60*60*24, function() {
    return $geminiService->generateHomeInsight($vehicleId, $scores);
});
```

### 4.2 Rate Limiting Gemini API

```php
// Limit Gemini API calls ke 10 per hari per user
RateLimiter::attempt(
    "gemini_insight_user_{$userId}",
    limit: 10,
    callback: function() {
        return $geminiService->generateHomeInsight(...);
    },
    decay: 60*60*24 // 1 hari
);
```

### 4.3 Benchmark Performance

| Operasi                              | Waktu Tempuh |
| ------------------------------------ | ------------ |
| Fuzzy Score Calculation (1 komponen) | ~10ms        |
| Fuzzy Score (5 komponen)             | ~50ms        |
| Gemini API Call (dari cache)         | <1ms         |
| Gemini API Call (fresh)              | ~2-3 detik   |
| Full Insight Generation              | ~3-3.5 detik |

---

## 5. Testing & Validation

### 5.1 Unit Test Fuzzy Logic

```php
public function testFuzzyLogicGreenComponent()
{
    $service = new FuzzyLogicService();
    $result = $service->evaluateComponentHealth(
        'oli',
        $currentOdometer = 2000,
        $lastServiceDate = now()->subDays(20)
    );

    $this->assertEquals('GREEN', $result['status']);
    $this->assertLessThan(50, $result['score']);
}

public function testFuzzyLogicRedComponent()
{
    $service = new FuzzyLogicService();
    $result = $service->evaluateComponentHealth(
        'oli',
        $currentOdometer = 4800,
        $lastServiceDate = now()->subDays(55)
    );

    $this->assertEquals('RED', $result['status']);
    $this->assertGreaterThan(75, $result['score']);
}
```

### 5.2 Integration Test Gemini API

```php
public function testGeminiAPIIntegration()
{
    $fuzzyScores = [
        'oli' => ['status' => 'GREEN', 'score' => 25],
        'busi' => ['status' => 'YELLOW', 'score' => 65],
    ];

    $service = new GeminiAIService();
    $insight = $service->generateHomeInsight(1, $fuzzyScores);

    $this->assertIsString($insight);
    $this->assertStringContainsString('Motor', $insight);
    $this->assertStringContainsString('busi', strtolower($insight));
}
```

---

**Catatan untuk Skripsi:**

- Jelaskan membership function dengan diagram visual
- Tunjukkan contoh kasus nyata Fuzzy Logic calculation
- Dokumentasikan API request-response Google Gemini
- Sertakan test cases untuk validation
- Diskusikan trade-off antara accuracy vs performance

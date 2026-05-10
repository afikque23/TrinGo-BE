# Fuzzy Logic + Gemini — Instalasi Lengkap

## File yang perlu dicopy ke proyekmu

```
app/
  Models/
    MotorType.php              → app/Models/
    ComponentConfig.php        → app/Models/
    FuzzyVariable.php          → app/Models/
    FuzzyRule.php              → app/Models/
    FuzzyAuditLog.php          → app/Models/
    AiRecommendation.php       → app/Models/

  Services/
    FuzzyEngine.php            → app/Services/
    GeminiService.php          → app/Services/
    RecommendationService.php  → app/Services/

  Http/Controllers/
    Admin/FuzzyLogicController.php   → app/Http/Controllers/Admin/
    Api/RecommendationController.php → app/Http/Controllers/Api/

database/
  migrations/
    2024_01_01_..._create_fuzzy_tables.php → database/migrations/
  seeders/
    FuzzyLogicSeeder.php       → database/seeders/

resources/views/admin/fuzzy/
  index.blade.php              → resources/views/admin/fuzzy/
  _modals.blade.php            → resources/views/admin/fuzzy/

public/js/fuzzy/
  admin.js                     → public/js/fuzzy/

routes/
  fuzzy_routes.php             → baca instruksi di bawah
```

---

## Langkah Setup

### 1. Jalankan migration
```bash
php artisan migrate
```

### 2. Isi data awal
```bash
php artisan db:seed --class=FuzzyLogicSeeder
```

### 3. Tambahkan routes

Di `routes/web.php`, tambahkan di bagian bawah:
```php
// Admin routes Fuzzy Logic
Route::prefix('admin/fuzzy')->name('admin.fuzzy.')->middleware(['auth','admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'index'])->name('index');
    Route::get('/audit', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'auditLog'])->name('audit');
    Route::get('/components', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'getComponents'])->name('components');
    Route::post('/components', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'storeComponent'])->name('components.store');
    Route::patch('/components/{component}/toggle', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'toggleComponent'])->name('components.toggle');
    Route::delete('/components/{component}', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'deleteComponent'])->name('components.delete');
    Route::patch('/components/{component}/threshold', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'saveThreshold'])->name('threshold.save');
    Route::patch('/components/{component}/membership', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'saveMembership'])->name('membership.save');
    Route::post('/components/{component}/rules', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'storeRule'])->name('rules.store');
    Route::patch('/rules/{rule}', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'updateRule'])->name('rules.update');
    Route::delete('/rules/{rule}', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'deleteRule'])->name('rules.delete');
    Route::post('/components/{component}/test', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'test'])->name('test');
    Route::post('/chart-data', [App\Http\Controllers\Admin\FuzzyLogicController::class, 'chartData'])->name('chart');
});
```

Di `routes/api.php`, tambahkan:
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/motor-types', [App\Http\Controllers\Api\RecommendationController::class, 'motorTypes']);
    Route::get('/motor-types/{motorTypeId}/components', [App\Http\Controllers\Api\RecommendationController::class, 'components']);
    Route::get('/recommendations/{motorId}', [App\Http\Controllers\Api\RecommendationController::class, 'show']);
    Route::get('/recommendations/{motorId}/scores', [App\Http\Controllers\Api\RecommendationController::class, 'scores']);
    Route::get('/recommendations/{motorId}/history', [App\Http\Controllers\Api\RecommendationController::class, 'history']);
});
```

### 4. Tambahkan Gemini API key

Di file `.env`:
```
GEMINI_API_KEY=AIzaSy_isi_api_key_kamu_di_sini
```

Di `config/services.php`, tambahkan:
```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
],
```

### 5. Register Service di AppServiceProvider

Di `app/Providers/AppServiceProvider.php`:
```php
public function register(): void
{
    $this->app->singleton(\App\Services\FuzzyEngine::class);
    $this->app->singleton(\App\Services\GeminiService::class);
    $this->app->singleton(\App\Services\RecommendationService::class);
}
```

### 6. Tambahkan link di sidebar admin

Di file sidebar Blade-mu, tambahkan:
```html
<a href="{{ route('admin.fuzzy.index') }}" 
   class="{{ request()->routeIs('admin.fuzzy.*') ? 'active' : '' }}">
  Fuzzy Logic
</a>
```

---

## Cara Flutter panggil API

```dart
// Ambil rekomendasi lengkap (Fuzzy + Gemini)
final response = await http.get(
  Uri.parse('$baseUrl/api/recommendations/$motorId'
    '?motor_type_id=$motorTypeId'
    '&odometer=$odometer'
    '&avg_speed=$avgSpeed'
    '&jarak_sejak_servis=$jarakSejak'
    '&servis_date=$servisDate'
    '&avg_km_hari=$avgKmHari'),
  headers: {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  },
);

// Ambil skor Fuzzy saja (lebih cepat, tanpa Gemini)
final scoresResponse = await http.get(
  Uri.parse('$baseUrl/api/recommendations/$motorId/scores'
    '?motor_type_id=$motorTypeId'
    '&jarak_sejak_servis=$jarakSejak'
    '&durasi_hari=$durasi'
    '&avg_speed=$avgSpeed'
    '&avg_km_hari=$avgKmHari'),
  headers: {'Authorization': 'Bearer $token'},
);
```

---

## Struktur response API ke Flutter

```json
{
  "success": true,
  "data": {
    "fuzzy_scores": {
      "Oli Mesin": { "score": 74.3, "label": "Perlu Servis", "membership": {...} },
      "Ban":       { "score": 12.1, "label": "Baik", "membership": {...} }
    },
    "recommendation": {
      "insight_singkat": "Oli mesin perlu diganti dalam waktu dekat.",
      "prioritas_utama": "Oli Mesin",
      "estimasi_servis": "Dalam 1-2 minggu",
      "rekomendasi": [...],
      "catatan_pola": "Pola berkendara ringan...",
      "tips_mandiri": "Periksa tekanan ban secara rutin."
    },
    "meta": {
      "fingerprint": "olimesin_500-1k_ringan",
      "from_cache": false,
      "generated_at": "2026-05-09T10:00:00Z"
    }
  }
}
```

---

## Cara dapat Gemini API key (gratis)

1. Buka https://aistudio.google.com/
2. Login dengan Google
3. Klik "Get API key"
4. Buat project baru → Generate API key
5. Copy ke .env

Free tier: 15 request/menit, 1 juta token/hari — lebih dari cukup untuk TA.

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model = 'gemini-1.5-flash'; // gratis & cepat
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Generate rekomendasi dari hasil skor Fuzzy Logic
     *
     * @param array $fuzzyScores  ['Oli Mesin' => ['score'=>74,'label'=>'Perlu Servis'], ...]
     * @param array $motorData    ['jenis'=>'Matic','odometer'=>8450,'avg_km_hari'=>11.1,...]
     * @return array|null
     */
    public function generateRecommendation(array $fuzzyScores, array $motorData): ?array
    {
        $prompt = $this->buildPrompt($fuzzyScores, $motorData);

        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.3,  // lebih konsisten
                        'maxOutputTokens' => 800,
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');
            return $this->parseResponse($text);

        } catch (\Exception $e) {
            Log::error('Gemini API exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Susun prompt ke Gemini — ringkas agar hemat token
     */
    private function buildPrompt(array $fuzzyScores, array $motorData): string
    {
        // Format skor jadi tabel ringkas
        $scoreLines = collect($fuzzyScores)
            ->map(fn($r, $name) => "- {$name}: {$r['score']}/100 ({$r['label']})")
            ->implode("\n");

        $jenis      = $motorData['jenis']       ?? 'Motor';
        $odometer   = $motorData['odometer']    ?? '-';
        $avgKm      = $motorData['avg_km_hari'] ?? '-';
        $avgSpeed   = $motorData['avg_speed']   ?? '-';
        $pola       = $motorData['pola']        ?? 'tidak diketahui';

        return <<<PROMPT
Kamu adalah asisten perawatan sepeda motor. Berikan rekomendasi singkat berdasarkan skor kondisi berikut.
Skor 0=sempurna, 100=harus segera servis.

DATA MOTOR:
- Jenis: {$jenis}
- Odometer: {$odometer} km
- Rata-rata: {$avgKm} km/hari, kecepatan {$avgSpeed} km/h
- Pola berkendara: {$pola}

SKOR KOMPONEN (dari Fuzzy Logic):
{$scoreLines}

Balas HANYA dengan JSON valid berikut, tanpa teks di luar JSON:
{
  "insight_singkat": "1-2 kalimat untuk home screen, bahasa santai",
  "prioritas_utama": "nama komponen paling mendesak atau null jika semua baik",
  "estimasi_servis": "perkiraan kapan harus ke bengkel, contoh: dalam 1-2 minggu",
  "rekomendasi": [
    {
      "komponen": "nama",
      "skor": 0,
      "status": "baik|perlu_servis|segera_servis",
      "urgensi": "rendah|sedang|tinggi",
      "pesan": "saran 1-2 kalimat, bahasa santai",
      "estimasi_biaya": "range harga servis dalam rupiah atau null"
    }
  ],
  "catatan_pola": "1 kalimat tentang pola berkendara dan dampaknya",
  "tips_mandiri": "1 tips perawatan yang bisa dilakukan sendiri tanpa ke bengkel"
}
PROMPT;
    }

    /**
     * Parse response JSON dari Gemini
     */
    private function parseResponse(?string $text): ?array
    {
        if (!$text) return null;

        // Bersihkan markdown code block kalau ada
        $clean = preg_replace('/```json\s*|\s*```/', '', $text);
        $clean = trim($clean);

        try {
            $data = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
            return $data;
        } catch (\JsonException $e) {
            Log::error('Gemini JSON parse error', ['text' => $text, 'error' => $e->getMessage()]);
            return null;
        }
    }
}

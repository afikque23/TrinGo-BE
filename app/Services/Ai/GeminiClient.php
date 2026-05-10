<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class GeminiClient
{
    /**
     * @return array{model:string|null, text:string|null, raw:array|null}
     */
    public function generate(string $prompt): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.0-flash');

        if (!$apiKey) {
            return ['model' => $model, 'text' => null, 'raw' => null];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::timeout(20)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($url . '?key=' . $apiKey, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => (float) config('services.gemini.temperature', 0.4),
                    'maxOutputTokens' => (int) config('services.gemini.max_output_tokens', 900),
                ],
            ]);

        if (!$response->ok()) {
            return ['model' => $model, 'text' => null, 'raw' => $response->json()];
        }

        $json = $response->json();
        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

        return ['model' => $model, 'text' => $text, 'raw' => $json];
    }
}

<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReverseGeocodeService
{
    /**
     * Reverse-geocode coordinates to a human readable address.
     *
     * @return array{address: string|null, raw: array<mixed>|null}
     */
    public function reverse(float $latitude, float $longitude): array
    {
        $provider = (string) config('mqtt.geocode.provider', 'nominatim');

        if ($provider !== 'nominatim') {
            Log::warning('Unsupported geocode provider.', ['provider' => $provider]);
            return ['address' => null, 'raw' => null];
        }

        $url = (string) config('mqtt.geocode.nominatim_url', 'https://nominatim.openstreetmap.org/reverse');
        $userAgent = (string) config('mqtt.geocode.user_agent', 'motorcycle-management/1.0');
        $acceptLanguage = (string) config('mqtt.geocode.accept_language', 'id');

        try {
            $response = Http::timeout((int) config('mqtt.geocode.timeout_seconds', 8))
                ->retry(2, 300)
                ->withHeaders([
                    'User-Agent' => $userAgent,
                    'Accept-Language' => $acceptLanguage,
                ])
                ->get($url, [
                    'format' => 'jsonv2',
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'zoom' => 18,
                    'addressdetails' => 1,
                ]);

            /** @var Response $response */

            if (!$response->successful()) {
                Log::warning('Reverse geocode failed.', [
                    'status' => $response->status(),
                    'body' => substr((string) $response->body(), 0, 300),
                ]);
                return ['address' => null, 'raw' => null];
            }

            $json = $response->json();
            if (!is_array($json)) {
                return ['address' => null, 'raw' => null];
            }

            $address = $json['display_name'] ?? null;
            if (!is_string($address) || trim($address) === '') {
                $address = null;
            }

            return ['address' => $address, 'raw' => $json];
        } catch (\Throwable $e) {
            Log::warning('Reverse geocode exception: ' . $e->getMessage());
            return ['address' => null, 'raw' => null];
        }
    }
}

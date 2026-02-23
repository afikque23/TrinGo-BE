<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengirim FCM Push Notification.
 * Menggunakan Firebase Cloud Messaging HTTP v1 API.
 */
class FcmNotificationService
{
    private ?string $accessToken = null;
    private ?string $projectId = null;
    private bool $isConfigured = false;

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initialize FCM configuration
     */
    private function initialize(): void
    {
        $fcmConfig = config('services.fcm');
        $credentialsPath = $fcmConfig['credentials_path'] ?? null;

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            Log::warning('FcmNotificationService: Firebase credentials file tidak ditemukan.');
            return;
        }

        $this->projectId = $fcmConfig['project_id'] ?? '';
        $this->isConfigured = true;
    }

    /**
     * Kirim notification ke single device token
     *
     * @param string $deviceToken FCM token dari device
     * @param string $title Judul notifikasi
     * @param string $body Isi notifikasi
     * @param array $data Data tambahan
     * @param string $categoryKey Category: service, trip, alert, insight, system
     * @param string $priority Priority: low, normal, high, critical
     * @return bool
     */
    public function sendToDevice(
        string $deviceToken,
        string $title,
        string $body,
        array $data = [],
        string $categoryKey = 'default',
        string $priority = 'normal'
    ): bool {
        if (!$this->isConfigured) {
            Log::warning('FcmNotificationService: FCM tidak dikonfigurasi.');
            return false;
        }

        try {
            // Ambil access token
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                Log::error('FcmNotificationService: Gagal mendapatkan access token.');
                return false;
            }

            // Tambahkan category_key ke data
            $data['category_key'] = $categoryKey;
            $data['click_action'] = 'NOTIFICATION_CLICK';

            // Kirim request ke FCM
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_map('strval', $data), // FCM requires all data values to be strings
                    'android' => [
                        // Set HIGH priority untuk high/critical agar muncul heads-up notification
                        'priority' => in_array($priority, ['high', 'critical']) ? 'HIGH' : 'NORMAL',
                        'notification' => [
                            'channel_id' => 'mototracker_' . $categoryKey,
                            'sound' => 'default',
                            'notification_priority' => $priority === 'critical' ? 'PRIORITY_MAX' : 'PRIORITY_HIGH',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                Log::info("FcmNotificationService: Push notification berhasil dikirim.", [
                    'token' => substr($deviceToken, 0, 20) . '...',
                    'title' => $title,
                ]);

                // Update last_used_at
                DeviceToken::where('fcm_token', $deviceToken)->update(['last_used_at' => now()]);

                return true;
            }

            // Handle error response
            $statusCode = $response->status();
            $errorBody = $response->json();

            Log::warning("FcmNotificationService: Push notification gagal dikirim.", [
                'status' => $statusCode,
                'error' => $errorBody,
                'token' => substr($deviceToken, 0, 20) . '...',
            ]);

            // Nonaktifkan token jika invalid
            if ($statusCode === 404 || $statusCode === 400) {
                DeviceToken::where('fcm_token', $deviceToken)->update(['is_active' => false]);
                Log::info("FcmNotificationService: FCM token dinonaktifkan karena invalid.");
            }

            return false;

        } catch (\Exception $e) {
            Log::error('FcmNotificationService: Exception saat kirim push notification.', [
                'error' => $e->getMessage(),
                'token' => substr($deviceToken, 0, 20) . '...',
            ]);
            return false;
        }
    }

    /**
     * Kirim notification ke user_id (semua device user tersebut)
     *
     * @param int $userId User ID
     * @param string $title Judul notifikasi
     * @param string $body Isi notifikasi
     * @param array $data Data tambahan
     * @param string $categoryKey Category key
     * @param string $priority Priority
     * @return int Jumlah device yang berhasil dikirim
     */
    public function sendToUser(
        int $userId,
        string $title,
        string $body,
        array $data = [],
        string $categoryKey = 'default',
        string $priority = 'normal'
    ): int {
        $tokens = DeviceToken::getTokensForUser($userId);

        if (empty($tokens)) {
            Log::info("FcmNotificationService: Tidak ada FCM token untuk user {$userId}");
            return 0;
        }

        $successCount = 0;
        foreach ($tokens as $token) {
            if ($this->sendToDevice($token, $title, $body, $data, $categoryKey, $priority)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Kirim notification ke device_id (guest mode)
     *
     * @param string $deviceId Device ID (UUID)
     * @param string $title Judul notifikasi
     * @param string $body Isi notifikasi
     * @param array $data Data tambahan
     * @param string $categoryKey Category key
     * @param string $priority Priority
     * @return bool
     */
    public function sendToDeviceId(
        string $deviceId,
        string $title,
        string $body,
        array $data = [],
        string $categoryKey = 'default',
        string $priority = 'normal'
    ): bool {
        $tokens = DeviceToken::getTokensForDevice($deviceId);

        if (empty($tokens)) {
            Log::info("FcmNotificationService: Tidak ada FCM token untuk device {$deviceId}");
            return false;
        }

        $success = false;
        foreach ($tokens as $token) {
            if ($this->sendToDevice($token, $title, $body, $data, $categoryKey, $priority)) {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Get Firebase access token dari service account
     */
    private function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $credentialsPath = config('services.fcm.credentials_path');

            if (!file_exists($credentialsPath)) {
                return null;
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);

            if (!isset($credentials['private_key']) || !isset($credentials['client_email'])) {
                Log::error('FcmNotificationService: Invalid service account credentials.');
                return null;
            }

            // Create JWT
            $now = time();
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $payload = json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]);

            $base64UrlHeader = $this->base64UrlEncode($header);
            $base64UrlPayload = $this->base64UrlEncode($payload);

            $signature = '';
            openssl_sign(
                $base64UrlHeader . '.' . $base64UrlPayload,
                $signature,
                $credentials['private_key'],
                OPENSSL_ALGO_SHA256
            );

            $base64UrlSignature = $this->base64UrlEncode($signature);
            $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;

            // Exchange JWT for access token
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'] ?? null;
                return $this->accessToken;
            }

            Log::error('FcmNotificationService: Gagal mendapatkan access token.', [
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('FcmNotificationService: Exception saat mendapatkan access token.', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Check if FCM is configured
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }
}

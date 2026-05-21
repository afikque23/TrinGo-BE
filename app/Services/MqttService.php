<?php

namespace App\Services;

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use Illuminate\Support\Facades\Log;

class MqttService
{
    /**
     * Publish a message to an MQTT topic.
     *
     * @param string $topic
     * @param string $payload
     * @param int $qos
     * @param bool $retain
     * @return void
     */
    public function publish(string $topic, string $payload, int $qos = 0, bool $retain = false): void
    {
        try {
            $host = (string) config('mqtt.host');
            $port = (int) config('mqtt.port');
            
            $clientId = (string) (config('mqtt.client_id', 'laravel_pub_') . uniqid());
            $username = config('mqtt.username');
            $password = config('mqtt.password');

            $settings = (new ConnectionSettings())
                ->setKeepAliveInterval(10)
                ->setConnectTimeout(5)
                ->setSocketTimeout(5);
                
            if ($username) {
                $settings->setUsername($username);
            }
            
            if ($password) {
                $settings->setPassword($password);
            }

            $client = new MqttClient($host, $port, $clientId);
            $client->connect($settings, true);
            
            $client->publish($topic, $payload, $qos, $retain);
            
            $client->disconnect();
        } catch (\Exception $e) {
            Log::error('MQTT Publish failed: ' . $e->getMessage(), [
                'topic' => $topic,
                'payload' => $payload
            ]);
        }
    }
}

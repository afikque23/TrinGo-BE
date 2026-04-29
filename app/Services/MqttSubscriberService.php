<?php

namespace App\Services;

use Illuminate\Support\Str;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class MqttSubscriberService
{
    public function subscribe(array $topics, int $qos, callable $onMessage): void
    {
        $host = (string) config('mqtt.host');
        $port = (int) config('mqtt.port');

        $clientId = (string) (config('mqtt.client_id') ?: $this->generateClientId());

        $username = config('mqtt.username');
        $password = config('mqtt.password');

        $useTls = (bool) config('mqtt.use_tls');
        $selfSignedAllowed = (bool) config('mqtt.tls_self_signed_allowed');

        $connectTimeout = (int) config('mqtt.connect_timeout');
        $socketTimeout = (int) config('mqtt.socket_timeout');
        $keepAlive = (int) config('mqtt.keep_alive');

        $settings = (new ConnectionSettings())
            ->setConnectTimeout($connectTimeout)
            ->setSocketTimeout($socketTimeout)
            ->setKeepAliveInterval($keepAlive)
            ->setUseTls($useTls)
            ->setTlsSelfSignedAllowed($selfSignedAllowed);

        if (!empty($username)) {
            $settings = $settings->setUsername((string) $username);
        }

        if (!empty($password)) {
            $settings = $settings->setPassword((string) $password);
        }

        $client = new MqttClient($host, $port, $clientId, MqttClient::MQTT_3_1_1);

        // Clean session: true (do not rely on broker session state)
        $client->connect($settings, true);

        foreach ($topics as $topic) {
            $client->subscribe(
                $topic,
                function (string $receivedTopic, string $message, bool $retained, array $matchedWildcards) use ($onMessage, $qos): void {
                    // php-mqtt/client v2 passes: (topic, message, retained, matchedWildcards)
                    // QoS is not included in the callback parameters, so we forward the configured QoS.
                    $onMessage($receivedTopic, $message, $retained, $qos);
                },
                $qos
            );
        }

        $client->loop(true);

        // If loop returns, ensure we disconnect.
        $client->disconnect();
    }

    private function generateClientId(): string
    {
        $app = (string) config('app.name', 'laravel');
        $host = gethostname() ?: 'host';

        return Str::slug($app) . '-mqtt-sub-' . Str::slug($host) . '-' . Str::lower(Str::random(6));
    }
}

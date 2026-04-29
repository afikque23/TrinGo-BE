<?php

namespace App\Console\Commands;

use App\Models\MqttMessage;
use App\Services\MqttSubscriberService;
use App\Services\TelemetryIngestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MqttSubscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:subscribe
        {--topic=* : Topic(s) to subscribe (repeatable). If omitted, uses MQTT_TOPICS/config(mqtt.topics).}
        {--qos= : QoS level (0, 1, or 2). Defaults to MQTT_QOS/config(mqtt.qos).}
        {--store=1 : Store incoming messages to database (1/0).}
        {--json=1 : Try to decode payload as JSON and store in payload_json (1/0).}
        {--reconnect-delay= : Reconnect delay in seconds. Defaults to MQTT_RECONNECT_DELAY_SECONDS/config.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to MQTT topic(s) and store incoming data to the database.';

    public function __construct(
        protected MqttSubscriberService $subscriber,
        protected TelemetryIngestService $telemetry,
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $topics = (array) $this->option('topic');
        if (empty($topics)) {
            $topics = (array) config('mqtt.topics', []);
        }

        $topics = array_values(array_filter(array_map('trim', $topics), fn ($t) => $t !== ''));

        if (empty($topics)) {
            $this->error('No MQTT topics provided. Use --topic=... or set MQTT_TOPICS in .env');
            return Command::FAILURE;
        }

        $qos = $this->option('qos');
        $qos = $qos === null || $qos === '' ? (int) config('mqtt.qos', 0) : (int) $qos;

        if ($qos < 0 || $qos > 2) {
            $this->error('Invalid QoS. Allowed values: 0, 1, 2.');
            return Command::FAILURE;
        }

        $store = (string) $this->option('store') !== '0';
        $parseJson = (string) $this->option('json') !== '0';

        $reconnectDelay = $this->option('reconnect-delay');
        $reconnectDelay = $reconnectDelay === null || $reconnectDelay === ''
            ? (int) config('mqtt.reconnect_delay_seconds', 5)
            : max(1, (int) $reconnectDelay);

        $this->info('MQTT subscriber starting...');
        $this->line('Topics: ' . implode(', ', $topics));
        $this->line('QoS: ' . $qos);
        $this->line('Store to DB: ' . ($store ? 'yes' : 'no'));
        $this->line('Parse JSON: ' . ($parseJson ? 'yes' : 'no'));

        while (true) {
            try {
                $this->info('Connecting and subscribing...');

                $this->subscriber->subscribe(
                    topics: $topics,
                    qos: $qos,
                    onMessage: function (string $topic, string $payload, bool $retained, int $receivedQos) use ($store, $parseJson): void {
                        try {
                            $receivedAt = now();
                            $payloadJson = null;

                            if ($parseJson) {
                                $decoded = json_decode($payload, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $payloadJson = $decoded;
                                }
                            }

                            $savedMessage = null;
                            if ($store) {
                                $address = null;
                                $mapsUrl = null;
                                $dataForAddress = is_array($payloadJson) ? $payloadJson : null;
                                if (!is_array($dataForAddress)) {
                                    $decoded = json_decode($payload, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $dataForAddress = $decoded;
                                    }
                                }

                                if (is_array($dataForAddress)) {
                                    $a = $dataForAddress['address'] ?? null;
                                    $m = $dataForAddress['maps_url'] ?? null;
                                    if (is_string($a) && trim($a) !== '') {
                                        $address = $a;
                                    }
                                    if (is_string($m) && trim($m) !== '') {
                                        $mapsUrl = $m;
                                    }
                                }

                                $savedMessage = MqttMessage::create([
                                    'topic' => $topic,
                                    'qos' => $receivedQos,
                                    'retained' => $retained,
                                    'payload' => $payload,
                                    'payload_json' => $payloadJson,
                                    'address' => $address,
                                    'maps_url' => $mapsUrl,
                                    'received_at' => $receivedAt,
                                    'meta' => [
                                        'payload_length' => strlen($payload),
                                    ],
                                ]);
                            }

                            // Bridge telemetry into domain model (vehicles) based on topic device_id.
                            // This is independent from --store/--json options.
                            $decodedForIngest = $payloadJson;
                            if (!is_array($decodedForIngest)) {
                                $decoded = json_decode($payload, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $decodedForIngest = $decoded;
                                }
                            }
                            $this->telemetry->ingest($topic, $payload, $decodedForIngest, $receivedAt);

                            if ($this->output->isVerbose()) {
                                $preview = substr($payload, 0, 200);
                                $this->line("[{$topic}] qos={$receivedQos} retained=" . ($retained ? '1' : '0') . ' payload=' . $preview);
                            }
                        } catch (\Throwable $e) {
                            Log::error('MQTT message handling failed: ' . $e->getMessage(), [
                                'topic' => $topic,
                            ]);
                        }
                    }
                );

                // If subscribe() ever returns, we'll reconnect.
                $this->warn('MQTT loop ended; reconnecting...');
            } catch (\Throwable $e) {
                $this->error('MQTT subscriber crashed: ' . $e->getMessage());
                Log::error('MQTT subscriber crashed: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }

            $this->line("Waiting {$reconnectDelay}s before reconnect...");
            sleep($reconnectDelay);
        }
    }
}

<?php

return [
    'host' => env('MQTT_HOST', '127.0.0.1'),
    'port' => (int) env('MQTT_PORT', 1883),

    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),

    // If not set, a client id will be generated at runtime.
    'client_id' => env('MQTT_CLIENT_ID'),

    // Comma-separated list of topics. Supports wildcards (+ and #).
    'topics' => array_values(array_filter(array_map('trim', explode(',', (string) env('MQTT_TOPICS', ''))))),

    'qos' => (int) env('MQTT_QOS', 0),

    'use_tls' => (bool) env('MQTT_USE_TLS', false),
    'tls_self_signed_allowed' => (bool) env('MQTT_TLS_SELF_SIGNED_ALLOWED', false),

    'connect_timeout' => (int) env('MQTT_CONNECT_TIMEOUT', 10),
    'socket_timeout' => (int) env('MQTT_SOCKET_TIMEOUT', 5),
    'keep_alive' => (int) env('MQTT_KEEP_ALIVE', 10),

    // If the connection drops, the command will wait this many seconds before reconnecting.
    'reconnect_delay_seconds' => (int) env('MQTT_RECONNECT_DELAY_SECONDS', 5),

    // Optional: append incoming telemetry points into trip_points for the active trip (end_at is null).
    'trip_points' => [
        'enabled' => (bool) env('MQTT_TRIP_POINTS_ENABLED', false),
    ],
];

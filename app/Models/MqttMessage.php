<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MqttMessage extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'topic',
        'qos',
        'retained',
        'payload',
        'payload_json',
        'address',
        'maps_url',
        'received_at',
        'meta',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'qos' => 'integer',
        'retained' => 'boolean',
        'payload_json' => 'array',
        'received_at' => 'datetime',
        'meta' => 'array',
    ];
}

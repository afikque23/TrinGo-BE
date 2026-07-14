<?php

namespace App\Services\Fuzzy;

final class DefaultFuzzyConfig
{
    /**
     * Default fuzzy config used when creating new component configs.
     */
    public static function make(): array
    {
        return [
            'inputs' => [
                'distance_since_service_km' => [
                    'universe' => [0, 5000],
                    'sets' => [
                        'low' => ['type' => 'linDown', 'params' => [1000, 2500]],
                        'medium' => ['type' => 'tri', 'params' => [1000, 2500, 4000]],
                        'high' => ['type' => 'linUp', 'params' => [2500, 4000]],
                    ],
                ],
                'duration_since_service_days' => [
                    'universe' => [0, 180],
                    'sets' => [
                        'low' => ['type' => 'linDown', 'params' => [30, 90]],
                        'medium' => ['type' => 'tri', 'params' => [30, 90, 150]],
                        'high' => ['type' => 'linUp', 'params' => [90, 150]],
                    ],
                ],
                'avg_speed_kph' => [
                    'universe' => [0, 120],
                    'sets' => [
                        'low' => ['type' => 'linDown', 'params' => [20, 60]],
                        'medium' => ['type' => 'tri', 'params' => [20, 60, 100]],
                        'high' => ['type' => 'linUp', 'params' => [60, 100]],
                    ],
                ],
                'intensity_km_per_day' => [
                    'universe' => [0, 10],
                    'sets' => [
                        'low' => ['type' => 'linDown', 'params' => [2, 5]],
                        'medium' => ['type' => 'tri', 'params' => [2, 5, 8]],
                        'high' => ['type' => 'linUp', 'params' => [5, 8]],
                    ],
                ],
            ],
            'output' => [
                'universe' => [0, 100],
                'sets' => [
                    'bad' => ['type' => 'trap', 'params' => [0, 0, 20, 45]],
                    'fair' => ['type' => 'tri', 'params' => [35, 55, 75]],
                    'good' => ['type' => 'trap', 'params' => [65, 80, 100, 100]],
                ],
            ],
            'rules' => [
                ['any' => [
                    ['var' => 'distance_since_service_km', 'is' => 'high'],
                    ['var' => 'duration_since_service_days', 'is' => 'high'],
                ], 'then' => 'bad'],

                ['all' => [
                    ['var' => 'distance_since_service_km', 'is' => 'high'],
                    ['var' => 'intensity_km_per_day', 'is' => 'high'],
                ], 'then' => 'bad'],

                ['all' => [
                    ['var' => 'duration_since_service_days', 'is' => 'high'],
                    ['var' => 'avg_speed_kph', 'is' => 'high'],
                ], 'then' => 'bad'],

                ['all' => [
                    ['var' => 'distance_since_service_km', 'is' => 'medium'],
                    ['var' => 'duration_since_service_days', 'is' => 'medium'],
                ], 'then' => 'fair'],

                ['any' => [
                    ['var' => 'intensity_km_per_day', 'is' => 'medium'],
                    ['var' => 'avg_speed_kph', 'is' => 'medium'],
                ], 'then' => 'fair'],

                ['all' => [
                    ['var' => 'distance_since_service_km', 'is' => 'low'],
                    ['var' => 'duration_since_service_days', 'is' => 'low'],
                    ['var' => 'intensity_km_per_day', 'is' => 'low'],
                ], 'then' => 'good'],
            ],
        ];
    }
}

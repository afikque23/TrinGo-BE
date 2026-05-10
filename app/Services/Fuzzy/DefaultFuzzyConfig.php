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
                        'low' => ['type' => 'trap', 'params' => [0, 0, 500, 1500]],
                        'medium' => ['type' => 'tri', 'params' => [1000, 2500, 4000]],
                        'high' => ['type' => 'trap', 'params' => [3000, 4500, 5000, 5000]],
                    ],
                ],
                'duration_since_service_days' => [
                    'universe' => [0, 180],
                    'sets' => [
                        'low' => ['type' => 'trap', 'params' => [0, 0, 7, 30]],
                        'medium' => ['type' => 'tri', 'params' => [20, 60, 100]],
                        'high' => ['type' => 'trap', 'params' => [80, 120, 180, 180]],
                    ],
                ],
                'avg_speed_kph' => [
                    'universe' => [0, 120],
                    'sets' => [
                        'low' => ['type' => 'trap', 'params' => [0, 0, 20, 35]],
                        'medium' => ['type' => 'tri', 'params' => [30, 55, 80]],
                        'high' => ['type' => 'trap', 'params' => [70, 90, 120, 120]],
                    ],
                ],
                'intensity_km_per_day' => [
                    'universe' => [0, 120],
                    'sets' => [
                        'low' => ['type' => 'trap', 'params' => [0, 0, 10, 25]],
                        'medium' => ['type' => 'tri', 'params' => [20, 45, 70]],
                        'high' => ['type' => 'trap', 'params' => [60, 85, 120, 120]],
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

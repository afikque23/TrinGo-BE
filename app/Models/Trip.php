<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'status',
        'started_by',
        'start_at',
        'end_at',
        'duration_minutes',
        'distance_meters',
        'start_odometer',
        'end_odometer',
        'avg_speed_kph',
        'max_speed_kph',
        'notes',
        // Sumber data
        'source',
        // Parameter konteks perjalanan (auto-detect GPS / kalibrasi manual)
        'kondisi_lalu_lintas',
        'medan',
        'gaya_berkendara',
        // Parameter manual
        'beban',
        'ada_penumpang',
        // Data sensor tambahan
        'elevation_gain',
        'idle_time_minutes',
        'rough_road_count',
        'hard_acceleration_count',
        'hard_braking_count',
        // Kalibrasi & scoring
        'is_calibrated',
        'service_score_factor',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'distance_meters' => 'integer',
        'start_odometer' => 'integer',
        'end_odometer' => 'integer',
        'avg_speed_kph' => 'decimal:2',
        'max_speed_kph' => 'decimal:2',
        'ada_penumpang' => 'boolean',
        'elevation_gain' => 'integer',
        'idle_time_minutes' => 'integer',
        'rough_road_count' => 'integer',
        'hard_acceleration_count' => 'integer',
        'hard_braking_count' => 'integer',
        'is_calibrated' => 'boolean',
        'service_score_factor' => 'decimal:2',
    ];

    /**
     * Get the vehicle that owns the trip.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who started the trip.
     */
    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    /**
     * Get the trip points for the trip.
     */
    public function points(): HasMany
    {
        return $this->hasMany(TripPoint::class)->orderBy('sequence');
    }

    /**
     * Calculate trip duration in minutes
     */
    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->start_at || !$this->end_at) {
            return null;
        }
        return $this->start_at->diffInMinutes($this->end_at);
    }

    /**
     * Get distance in kilometers
     */
    public function getDistanceKmAttribute(): ?float
    {
        if (!$this->distance_meters) {
            return null;
        }
        return round($this->distance_meters / 1000, 2);
    }

    /**
     * Auto-detect kondisi lalu lintas dari rata-rata kecepatan GPS.
     * Macet: <20 km/h | Sedang: 20-40 km/h | Lancar: >40 km/h
     */
    public static function detectKondisiLaluLintas(?float $avgSpeedKph): ?string
    {
        if ($avgSpeedKph === null) return null;
        if ($avgSpeedKph < 20) return 'macet';
        if ($avgSpeedKph <= 40) return 'sedang';
        return 'lancar';
    }

    /**
     * Auto-detect medan dari total elevation gain.
     * Datar: <50m | Campuran: 50-150m | Berbukit: >150m
     */
    public static function detectMedan(?int $elevationGain): ?string
    {
        if ($elevationGain === null) return null;
        if ($elevationGain < 50) return 'datar';
        if ($elevationGain <= 150) return 'campuran';
        return 'berbukit';
    }

    /**
     * Auto-detect gaya berkendara dari rata-rata kecepatan GPS.
     * Pelan: <40 km/h | Normal: 40-60 km/h | Agresif: >60 km/h
     */
    public static function detectGayaBerkendara(?float $avgSpeedKph): ?string
    {
        if ($avgSpeedKph === null) return null;
        if ($avgSpeedKph < 40) return 'pelan';
        if ($avgSpeedKph <= 60) return 'normal';
        return 'agresif';
    }

    /**
     * Hitung service score factor berdasarkan parameter konteks perjalanan.
     * Factor > 1 berarti lebih cepat aus, < 1 berarti lebih awet.
     */
    public function calculateServiceScoreFactor(): float
    {
        $factor = 1.00;

        // Bobot kondisi lalu lintas (macet = mesin idle lama)
        $factor *= match($this->kondisi_lalu_lintas) {
            'macet'  => 1.50,
            'sedang' => 1.20,
            'lancar' => 1.00,
            default  => 1.00,
        };

        // Bobot medan
        $factor *= match($this->medan) {
            'berbukit' => 1.40,
            'campuran' => 1.20,
            'datar'    => 1.00,
            default    => 1.00,
        };

        // Bobot gaya berkendara
        $factor *= match($this->gaya_berkendara) {
            'agresif' => 1.50,
            'normal'  => 1.00,
            'pelan'   => 0.90,
            default   => 1.00,
        };

        // Bobot beban bawaan
        $factor *= match($this->beban) {
            'berat'  => 1.30,
            'sedang' => 1.10,
            'ringan' => 1.00,
            default  => 1.00,
        };

        // Bobot penumpang
        if ($this->ada_penumpang) {
            $factor *= 1.15;
        }

        // Bobot jalan rusak (per 10 deteksi = +5%)
        if ($this->rough_road_count > 0) {
            $factor *= (1 + min($this->rough_road_count / 10, 0.30) * 0.05);
        }

        return round($factor, 2);
    }
}

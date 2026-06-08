<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier',
        'otp',
        'type',
        'expires_at',
        'is_used',
        'verified_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_used' => 'boolean',
        'attempts' => 'integer',
    ];

    /**
     * Check if OTP is valid
     */
    public function isValid(): bool
    {
        $maxAttempts = (int) config('otp.max_attempts', 5);

        return !$this->is_used
            && $this->expires_at->isFuture()
            && $this->attempts < $maxAttempts;
    }

    /**
     * Mark OTP as used
     */
    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Increment attempts
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Generate random OTP
     */
    public static function generateOtp(int $length = 6): string
    {
        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Create new OTP
     */
    public static function createOtp(string $identifier, string $type = 'email_verification', int $expiryMinutes = 10): self
    {
        // Delete old unused OTPs for this identifier and type
        self::where('identifier', $identifier)
            ->where('type', $type)
            ->where('is_used', false)
            ->delete();

        return self::create([
            'identifier' => $identifier,
            'otp' => self::generateOtp(),
            'type' => $type,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);
    }

    /**
     * Verify OTP
     */
    public static function verifyOtp(string $identifier, string $otp, string $type): ?self
    {
        $otpRecord = self::where('identifier', $identifier)
            ->where('type', $type)
            ->where('is_used', false)
            ->orderByDesc('id')
            ->first();

        if (!$otpRecord || !$otpRecord->isValid()) {
            return null;
        }

        if (!hash_equals((string) $otpRecord->otp, (string) $otp)) {
            $otpRecord->incrementAttempts();
            return null;
        }

        return $otpRecord;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    public const MAX_ATTEMPTS = 5;

    public const LOCKOUT_MINUTES = 15;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'login_attempts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'ip_address',
        'attempted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    /**
     * Count recent failed attempts for a given email+IP within the last $minutes.
     */
    public static function countRecentAttempts(string $email, string $ip, int $minutes = 15): int
    {
        return static::where('email', strtolower($email))
            ->where('ip_address', $ip)
            ->where('attempted_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    /** Remove attempts that can no longer contribute to the lockout window. */
    public static function pruneExpiredAttempts(string $email, string $ip, int $minutes = self::LOCKOUT_MINUTES): void
    {
        static::where('email', strtolower($email))
            ->where('ip_address', $ip)
            ->where('attempted_at', '<=', now()->subMinutes($minutes))
            ->delete();
    }

    /** Return remaining lockout time, rounded up to a whole minute. */
    public static function lockoutMinutesRemaining(string $email, string $ip): int
    {
        $recentAttempts = static::where('email', strtolower($email))
            ->where('ip_address', $ip)
            ->where('attempted_at', '>', now()->subMinutes(self::LOCKOUT_MINUTES))
            ->orderByDesc('attempted_at')
            ->limit(self::MAX_ATTEMPTS)
            ->get(['attempted_at']);

        if ($recentAttempts->count() < self::MAX_ATTEMPTS) {
            return 0;
        }

        $lockoutEndsAt = $recentAttempts->first()->attempted_at->copy()->addMinutes(self::LOCKOUT_MINUTES);

        if (now()->gte($lockoutEndsAt)) {
            return 0;
        }

        $secondsRemaining = $lockoutEndsAt->getTimestamp() - now()->getTimestamp();

        return max(1, (int) ceil($secondsRemaining / 60));
    }

    /**
     * Record a new failed login attempt.
     */
    public static function record(string $email, string $ip): void
    {
        static::create([
            'email'        => strtolower($email),
            'ip_address'   => $ip,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Clear all attempts for a given email+IP (after successful login).
     */
    public static function clearAttempts(string $email, string $ip): void
    {
        static::where('email', strtolower($email))
            ->where('ip_address', $ip)
            ->delete();
    }
}

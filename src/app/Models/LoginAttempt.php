<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
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

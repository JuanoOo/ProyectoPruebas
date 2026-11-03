<?php

namespace App\Http\Middleware;

use App\Models\LoginAttempt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLoginAttempts
{
    /**
     * Maximum number of failed login attempts allowed.
     */
    private int $maxAttempts = 5;

    /**
     * Lockout window and duration in minutes.
     */
    private int $decayMinutes = 15;

    /**
     * Handle an incoming request.
     *
     * Blocks access to the login POST route if the account+IP combination
     * has exceeded the maximum number of failed attempts within the decay window.
     * (HU-05)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $email = strtolower((string) $request->input('email', ''));
        $ip    = $request->ip();

        if ($email && $ip) {
            $attempts = LoginAttempt::countRecentAttempts($email, $ip, $this->decayMinutes);

            if ($attempts >= $this->maxAttempts) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Too many login attempts. Please try again later.',
                    ], Response::HTTP_TOO_MANY_REQUESTS);
                }

                return redirect()->route('login')
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => 'Tu cuenta ha sido bloqueada temporalmente por demasiados intentos fallidos. Por favor, inténtalo de nuevo en ' . $this->decayMinutes . ' minutos.',
                    ]);
            }
        }

        return $next($request);
    }
}

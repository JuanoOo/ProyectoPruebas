<?php

namespace App\Http\Middleware;

use App\Models\LoginAttempt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLoginAttempts
{
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
            LoginAttempt::pruneExpiredAttempts($email, $ip);
            $remainingMinutes = LoginAttempt::lockoutMinutesRemaining($email, $ip);

            if ($remainingMinutes > 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => "Demasiados intentos. Espera {$remainingMinutes} min para volver a intentar.",
                    ], Response::HTTP_TOO_MANY_REQUESTS);
                }

                return redirect()->route('login')
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => "Tu cuenta fue bloqueada temporalmente por demasiados intentos fallidos. Espera {$remainingMinutes} min para volver a intentar.",
                    ]);
            }
        }

        return $next($request);
    }
}

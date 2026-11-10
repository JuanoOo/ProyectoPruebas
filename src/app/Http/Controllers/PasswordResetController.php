<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    private const GENERIC_REQUEST_STATUS = 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación en breve.';

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::sendResetLink([
            'email' => strtolower($validated['email']),
        ]);

        // Keep the response identical for known and unknown email addresses.
        return back()->with('status', self::GENERIC_REQUEST_STATUS);
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)->mixedCase()->numbers(),
            ],
        ]);

        $status = Password::reset(
            [
                ...$validated,
                'email' => strtolower($validated['email']),
            ],
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password])->save();
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Tu contraseña ha sido restablecida exitosamente. Ahora puedes iniciar sesión.');
        }

        return back()->withErrors([
            'email' => 'El enlace de recuperación no es válido o ha expirado. Solicita uno nuevo.',
        ]);
    }
}

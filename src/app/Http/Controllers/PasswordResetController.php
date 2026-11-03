<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    // -------------------------------------------------------------------------
    // HU-03: Forgot Password — show form
    // -------------------------------------------------------------------------

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // -------------------------------------------------------------------------
    // HU-03: Forgot Password — handle submission
    // -------------------------------------------------------------------------

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower($request->email);

        // Always show success message to prevent user enumeration
        $user = User::where('email', $email)->first();

        if ($user) {
            // Delete any existing token for this email
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            // Generate secure token
            $token = Str::random(64);

            DB::table('password_reset_tokens')->insert([
                'email'      => $email,
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]);

            // Build reset URL
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $email,
            ]);

            // Log the email (MAIL_MAILER=log)
            \Illuminate\Support\Facades\Log::info('Password reset link', [
                'to'  => $email,
                'url' => $resetUrl,
            ]);

            // If a real mail driver is configured, send the email
            // Mail::to($email)->send(new \App\Mail\PasswordResetMail($resetUrl));
        }

        return back()->with('status', 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación en breve.');
    }

    // -------------------------------------------------------------------------
    // HU-03: Reset Password — show form
    // -------------------------------------------------------------------------

    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    // -------------------------------------------------------------------------
    // HU-03: Reset Password — handle submission
    // -------------------------------------------------------------------------

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers(),
            ],
        ]);

        $email = strtolower($request->email);

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $record) {
            return back()->withErrors(['email' => 'El token de recuperación no es válido o ha expirado.']);
        }

        // HU-03: Token expires in 30 minutes
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->diffInMinutes(now()) > 30) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return back()->withErrors(['email' => 'El token de recuperación ha expirado. Por favor, solicita uno nuevo.']);
        }

        // Validate token (it's hashed)
        if (! Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'El token de recuperación no es válido o ha expirado.']);
        }

        // Update password
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'No se encontró ninguna cuenta asociada a ese correo.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        // HU-03: Invalidate token after use (single-use)
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('login')
            ->with('status', 'Tu contraseña ha sido restablecida exitosamente. Ahora puedes iniciar sesión.');
    }
}

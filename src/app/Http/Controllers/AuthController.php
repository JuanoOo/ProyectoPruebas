<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // -------------------------------------------------------------------------
    // HU-01: Register
    // -------------------------------------------------------------------------

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers(),
            ],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => strtolower($request->email),
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    // -------------------------------------------------------------------------
    // HU-02: Login
    // -------------------------------------------------------------------------

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower($request->email);
        $ip    = $request->ip();

        // Attempt authentication
        if (Auth::attempt(['email' => $email, 'password' => $request->password], $request->boolean('remember'))) {
            // HU-05: Successful login resets counter
            LoginAttempt::clearAttempts($email, $ip);

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // HU-05: Record failed attempt
        LoginAttempt::record($email, $ip);

        $remainingMinutes = LoginAttempt::lockoutMinutesRemaining($email, $ip);

        if ($remainingMinutes > 0) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => "Tu cuenta fue bloqueada temporalmente por demasiados intentos fallidos. Espera {$remainingMinutes} min para volver a intentar."]);
        }

        // HU-02: Generic error message (do not reveal which field failed)
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Las credenciales proporcionadas son incorrectas.']);
    }

    // -------------------------------------------------------------------------
    // HU-04: Logout
    // -------------------------------------------------------------------------

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // -------------------------------------------------------------------------
    // Dashboard (área privada)
    // -------------------------------------------------------------------------

    public function dashboard()
    {
        return response()->view('dashboard', ['user' => Auth::user()])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}

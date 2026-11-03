<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

// ── Public routes ────────────────────────────────────────────────────────────

// Home → redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

// HU-01: Register
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// HU-02: Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware(\App\Http\Middleware\CheckLoginAttempts::class);

// HU-03: Forgot / Reset password
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// ── Authenticated routes ─────────────────────────────────────────────────────

Route::middleware('auth')->group(function () {
    // Dashboard (área privada)
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // HU-04: Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

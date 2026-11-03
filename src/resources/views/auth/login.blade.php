@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<div class="container">
    <div class="auth-card card shadow-sm border-0">
        <div class="brand-header text-center">
            <i class="bi bi-shield-lock-fill display-6 d-block mb-2"></i>
            <h4 class="mb-0">Iniciar sesión</h4>
            <small class="opacity-75">Accede a tu cuenta</small>
        </div>

        <div class="card-body p-4">
            {{-- Success status (e.g., after password reset) --}}
            @if (session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                </div>
            @endif

            {{-- Validation errors (HU-02: generic message) --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        <i class="bi bi-envelope me-1 text-primary"></i>Correo electrónico
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="correo@ejemplo.com"
                    >
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">
                        <i class="bi bi-lock me-1 text-primary"></i>Contraseña
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        required
                        placeholder="Tu contraseña"
                    >
                </div>

                {{-- Remember me --}}
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            class="form-check-input"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <label for="remember" class="form-check-label text-muted small">Recordarme</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-decoration-none small">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión
                    </button>
                </div>
            </form>
        </div>

        <div class="card-footer bg-light text-center py-3">
            <small>¿No tienes cuenta? <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Regístrate aquí</a></small>
        </div>
    </div>
</div>
@endsection

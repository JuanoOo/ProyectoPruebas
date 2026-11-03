@extends('layouts.app')

@section('title', 'Registro')

@section('content')
<div class="container">
    <div class="auth-card card shadow-sm border-0">
        <div class="brand-header text-center">
            <i class="bi bi-person-plus-fill display-6 d-block mb-2"></i>
            <h4 class="mb-0">Crear cuenta</h4>
            <small class="opacity-75">Completa el formulario para registrarte</small>
        </div>

        <div class="card-body p-4">
            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                {{-- Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">
                        <i class="bi bi-person me-1 text-primary"></i>Nombre completo
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        placeholder="Tu nombre completo"
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

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
                        placeholder="correo@ejemplo.com"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        placeholder="Mínimo 8 caracteres"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text text-muted small">
                        Mínimo 8 caracteres, incluyendo mayúsculas, minúsculas y números.
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold">
                        <i class="bi bi-lock-fill me-1 text-primary"></i>Confirmar contraseña
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        required
                        placeholder="Repite tu contraseña"
                    >
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Registrarse
                    </button>
                </div>
            </form>
        </div>

        <div class="card-footer bg-light text-center py-3">
            <small>¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Inicia sesión aquí</a></small>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Recuperar contraseña')

@section('content')
<div class="container">
    <div class="auth-card card shadow-sm border-0">
        <div class="brand-header text-center">
            <i class="bi bi-key-fill display-6 d-block mb-2"></i>
            <h4 class="mb-0">Recuperar contraseña</h4>
            <small class="opacity-75">Te enviaremos un enlace de recuperación</small>
        </div>

        <div class="card-body p-4">
            {{-- Status message (always shown, generic) --}}
            @if (session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <p class="text-muted mb-4">
                Ingresa tu correo electrónico y, si existe una cuenta asociada, recibirás instrucciones para restablecer tu contraseña.
            </p>

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf

                <div class="mb-4">
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
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-send me-2"></i>Enviar enlace de recuperación
                    </button>
                </div>
            </form>
        </div>

        <div class="card-footer bg-light text-center py-3">
            <small><a href="{{ route('login') }}" class="text-decoration-none fw-semibold">
                <i class="bi bi-arrow-left me-1"></i>Volver al inicio de sesión
            </a></small>
        </div>
    </div>
</div>
@endsection

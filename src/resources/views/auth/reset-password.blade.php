@extends('layouts.app')

@section('title', 'Restablecer contraseña')

@section('content')
<div class="container">
    <div class="auth-card card shadow-sm border-0">
        <div class="brand-header text-center">
            <i class="bi bi-shield-check display-6 d-block mb-2"></i>
            <h4 class="mb-0">Nueva contraseña</h4>
            <small class="opacity-75">Ingresa y confirma tu nueva contraseña</small>
        </div>

        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" novalidate>
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                {{-- New Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">
                        <i class="bi bi-lock me-1 text-primary"></i>Nueva contraseña
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        autofocus
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
                        <i class="bi bi-lock-fill me-1 text-primary"></i>Confirmar nueva contraseña
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        required
                        placeholder="Repite tu nueva contraseña"
                    >
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Restablecer contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

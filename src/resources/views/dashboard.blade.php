@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Welcome card --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-person-fill text-primary fs-3"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">¡Bienvenido, {{ $user->name }}!</h4>
                            <p class="text-muted mb-0 small">
                                <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                            </p>
                        </div>
                    </div>
                    <p class="text-muted">
                        Has iniciado sesión correctamente en el área privada. Esta página sólo es accesible para usuarios autenticados (HU-02 / HU-04).
                    </p>
                </div>
            </div>

            {{-- Sprint 1 Status --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-list-check me-2 text-success"></i>Sprint 1 — Módulo de Autenticación
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex align-items-center">
                        <span class="badge bg-success me-3">HU-01</span>
                        Registro de usuarios con validación y bcrypt
                        <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <span class="badge bg-success me-3">HU-02</span>
                        Inicio de sesión con mensajes genéricos
                        <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <span class="badge bg-success me-3">HU-03</span>
                        Recuperación de contraseña con token de un solo uso (30 min)
                        <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <span class="badge bg-success me-3">HU-04</span>
                        Cierre de sesión seguro (invalida sesión en servidor)
                        <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <span class="badge bg-success me-3">HU-05</span>
                        Bloqueo tras 5 intentos fallidos en 15 min por IP+cuenta
                        <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                    </li>
                </ul>
            </div>

            {{-- Logout --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                </button>
            </form>

        </div>
    </div>
</div>
@endsection

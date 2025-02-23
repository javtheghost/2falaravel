@extends('layouts.app') <!-- Ajusta a tu layout principal si es necesario -->

@section('title', 'Sesión Expirada')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-red-500">419</h1>
        <p class="text-xl mt-4">Tu sesión ha expirado.</p>
        <p class="mt-2 text-gray-600">Tu sesión ha caducado debido a inactividad. Por favor, recarga la página o vuelve a iniciar sesión.</p>
        <a href="{{ route('home') }}" class="mt-6 inline-block px-6 py-3 bg-blue-500 text-white rounded-lg shadow-lg hover:bg-blue-600">
            Iniciar Sesión
        </a>
    </div>
</div>
@endsection

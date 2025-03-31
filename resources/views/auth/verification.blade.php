@extends('layouts.app')

@section('title', 'Verificación')

@section('content')
<div class="flex justify-center items-center min-h-screen bg-gray-50">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Verifica tu identidad</h1>
            <p class="text-gray-600 mt-2">Hemos enviado un mensaje de texto a:</p>
            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <p class="font-medium text-gray-700">Teléfono terminado en <span class="font-bold">{{ $phone_last_digits ? substr($phone_last_digits, -2) : '****' }}</span></p>
            </div>
        </div>

        {{-- Mostrar mensaje de error o éxito --}}
        @if(session('error_message'))
            <div class="text-center text-red-600 mb-4">
                <p class="text-lg font-semibold">Demasiados intentos fallidos</p>
                <p>Por favor, vuelve a loguearte e intenta nuevamente.</p>
            </div>
        @endif

        {{-- Formulario de verificación --}}
        <form method="POST" action="{{ route('auth.storeve') }}" id="verification-form">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" id="remaining_time" value="{{ $remaining_time ?? 0 }}">
            <input type="hidden" id="is_blocked" value="{{ $is_blocked ? 'true' : 'false' }}">
            <input type="hidden" name="secure_token" value="{{ encrypt(time()) }}">

            <div class="mb-6">
                <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-2">
                    Ingresa el código de 6 dígitos
                </label>
                <input type="text" id="verification_code" name="verification_code"
                    class="w-full px-4 py-3 border {{ $is_blocked ? 'border-red-500 bg-gray-100' : 'border-gray-300' }} rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-xl tracking-widest"
                    inputmode="numeric" pattern="\d{6}" maxlength="6" required autofocus
                    {{ $is_blocked ? 'disabled' : '' }}
                    oninput="validateCode(this)">
                
                @error('verification_code')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" id="submit-btn" class="w-full py-3 px-4 {{ $is_blocked ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700' }} text-white font-medium rounded-md transition duration-200" 
                {{ $is_blocked ? 'disabled' : '' }}>
                Continuar
            </button>
        </form>

        {{-- Mostrar botón de redirección si el usuario está bloqueado --}}
        @if($is_blocked)
            <div class="mt-4 text-center">
                <p class="text-red-600">Has agotado los intentos, por favor vuelve a iniciar sesión.</p>
                <a href="{{ route('login.index') }}" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Iniciar sesión
                </a>
            </div>
        @endif

        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">¿Problemas para verificar? <a href="#" class="text-blue-600 hover:underline">Contactar soporte</a></p>
            <p class="mt-2 text-xs text-gray-400">© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</div>
@endsection

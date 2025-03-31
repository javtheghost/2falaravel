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

        {{-- Mostrar mensajes de error o éxito --}}
        @if(session('error'))
        <div class="bg-red-50 p-4 rounded-lg mb-4">
            <p class="text-red-600 font-medium">
                <i class="fas fa-lock mr-2"></i>
                {{ session('error') }}
            </p>
        </div>
        @endif

        @if(session('success'))
        <div class="bg-green-50 p-4 rounded-lg mb-4">
            <p class="text-green-600 font-medium">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </p>
        </div>
        @endif

     
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

        <div class="mt-8 text-center">
            @if($can_resend)
                <form method="POST" action="{{ route('auth.resend') }}" id="resend-form">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="secure_token" value="{{ encrypt(time()) }}">
                    <button type="submit" id="resend-btn" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Reenviar código
                    </button>
                </form>
            @else
                <p class="text-sm text-gray-500">
                    Podrás reenviar el código en: <span id="resend-countdown">{{ floor($remaining_time/60) }}:{{ str_pad($remaining_time%60, 2, '0', STR_PAD_LEFT) }}</span>
                    @if(isset($unlock_time))
                        (a las {{ $unlock_time }})
                    @endif
                </p>
            @endif
            <div id="resend-feedback" class="mt-2 text-sm"></div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">¿Problemas para verificar? <a href="#" class="text-blue-600 hover:underline">Contactar soporte</a></p>
            <p class="mt-2 text-xs text-gray-400">© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</div>

<script>
    // Función para formatear el tiempo (MM:SS)
    function formatTime(totalSeconds) {
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    let remainingTime = document.getElementById('remaining_time').value;
    let countdownDisplay = document.getElementById('resend-countdown');

    if (remainingTime > 0) {
    let countdownTimer = setInterval(() => {
        remainingTime--;
        countdownDisplay.innerText = formatTime(remainingTime);

        if (remainingTime <= 0) {
            clearInterval(countdownTimer);
            document.getElementById('resend-feedback').innerText = "Ahora puedes reenviar el código.";
        }
    }, 1000);
}

</script>
@endsection

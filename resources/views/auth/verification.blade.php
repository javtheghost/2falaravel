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

        @if($is_blocked)
        <div class="bg-red-50 p-4 rounded-lg mb-4">
            <p class="text-red-600 font-medium">
                <i class="fas fa-lock mr-2"></i>
                Bloqueado por seguridad. Disponible en <span class="block-message-time">{{ floor($remaining_time/60) }}:{{ str_pad($remaining_time%60, 2, '0', STR_PAD_LEFT) }}</span>
            </p>
            @if(isset($unlock_time))
            <div class="mt-2 flex items-center text-sm text-gray-600">
                <i class="fas fa-clock mr-2"></i>
                <span>Se desbloqueará a las <span id="unlock-time">{{ $unlock_time }}</span></span>
            </div>
            @endif
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
                </p>
            @endif
            <div id="resend-feedback" class="mt-2"></div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">¿Problemas para verificar? <a href="#" class="text-blue-600 hover:underline">Contactar soporte</a></p>
            <p class="mt-2 text-xs text-gray-400">© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
    // Función para formatear el tiempo (MM:SS)
    function formatTime(totalSeconds) {
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    // Función para actualizar todos los contadores
    function updateAllCounters(seconds) {
        const formattedTime = formatTime(seconds);
        
        // Actualizar todos los elementos del contador
        const countdownElements = [
            document.getElementById('countdown'),
            document.getElementById('resend-countdown'),
            document.querySelector('.block-message-time')
        ];
        
        countdownElements.forEach(element => {
            if (element) {
                element.textContent = formattedTime;
            }
        });
    }

    // Iniciar el contador
    function startCountdown() {
        let timeLeft = parseInt(document.getElementById('remaining_time').value) || 0;
        const isBlocked = document.getElementById('is_blocked').value === 'true';
        
        // Actualizar inmediatamente
        if (timeLeft > 0) {
            updateAllCounters(timeLeft);
            
            const countdownInterval = setInterval(() => {
                timeLeft--;
                updateAllCounters(timeLeft);
                
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    // Recargar después de 1 segundo para actualizar el estado
                    setTimeout(() => window.location.reload(), 1000);
                }
            }, 1000);
        }
    }

    // Validación del código en tiempo real
    function validateCode(input) {
        // Solo permite números y limita a 6 dígitos
        input.value = input.value.replace(/\D/g, '').substring(0, 6);
        const submitBtn = document.getElementById('submit-btn');
        const isBlocked = document.getElementById('is_blocked').value === 'true';
        submitBtn.disabled = input.value.length !== 6 || isBlocked;
    }

    // Manejar el reenvío de código
    document.getElementById('resend-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('resend-btn');
        const feedbackDiv = document.getElementById('resend-feedback');
        
        btn.disabled = true;
        btn.innerHTML = 'Enviando... <i class="fas fa-spinner fa-spin ml-1"></i>';
        
        try {
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    email: this.querySelector('input[name="email"]').value,
                    secure_token: this.querySelector('input[name="secure_token"]').value
                })
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                feedbackDiv.innerHTML = '<p class="text-green-600 text-sm"><i class="fas fa-check-circle mr-1"></i> Nuevo código enviado</p>';
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(data.message || 'Error al reenviar el código');
            }
        } catch (error) {
            feedbackDiv.innerHTML = `<p class="text-red-600 text-sm"><i class="fas fa-exclamation-circle mr-1"></i> ${error.message}</p>`;
            btn.disabled = false;
            btn.innerHTML = 'Reenviar código';
            setTimeout(() => feedbackDiv.innerHTML = '', 3000);
        }
    });

    // Iniciar el contador cuando la página cargue
    document.addEventListener('DOMContentLoaded', startCountdown);
</script>
@endsection
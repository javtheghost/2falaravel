<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VerifyController extends Controller
{
    
    public function create(Request $request)
{
    $user = User::where('email', $request->email)->firstOrFail();

    // Verificar si está bloqueado debido a demasiados intentos fallidos
    $isBlocked = $user->failed_attempts >= 3;
    $expiresAt = $isBlocked ? $user->codem_expires_at : null;

    // Calcular el tiempo restante si está bloqueado (máximo 5 minutos)
    $remainingTime = $isBlocked ? max(0, $expiresAt->diffInSeconds(now())) : 0;

    // Si el tiempo de bloqueo ya expiró, resetear los intentos
    if ($isBlocked && $remainingTime <= 0) {
        $user->update(['failed_attempts' => 0]);
        $isBlocked = false;
    }

    // Si el usuario está bloqueado, pasar un mensaje de error
    if ($isBlocked) {
        session()->flash('error_message', 'Demasiados intentos fallidos, por favor vuelve a loguearte.');
    }

    return view('auth.verification', [
        'email' => $user->email,
        'phone_last_digits' => substr($user->phone_number, -2),
        'remaining_time' => $remainingTime,
        'is_blocked' => $isBlocked,
        'unlock_time' => $isBlocked ? $expiresAt->format('H:i:s') : null,
        'can_resend' => !$isBlocked || $remainingTime <= 0
    ]);
}
    

    public function store(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|numeric|digits:6',
            'email' => 'required|email|exists:users,email'
        ]);
    
        $user = User::where('email', $request->email)->firstOrFail();
    
                // Verificar si el código ha expirado
        if ($user->hasExpiredCode()) {
            $user->invalidateCode();
            return back()->withErrors([
                'verification_code' => 'El código ha expirado. Por favor solicita uno nuevo.',
                'expired' => true
            ]);
        }

        // Verificar intentos fallidos
        if ($user->failed_attempts >= 3) {
            // Si el código ha fallado 3 veces, debemos esperar a que pase el tiempo del bloqueo
            $remainingTime = max(0, min($user->codem_expires_at->diffInSeconds(now()), 300)); // Tiempo máximo 5 minutos
            return back()->withErrors([
                'verification_code' => 'Debes esperar a que expire el código actual.',
                'remaining_time' => $remainingTime
            ]);
        }

        // Verificar si el código es correcto
        if (!Hash::check($request->verification_code, $user->codem)) {
            $user->increment('failed_attempts');

            // Si llegamos a 3 intentos fallidos, bloquear al usuario por 5 minutos
            if ($user->failed_attempts >= 3) {
                $user->update(['codem_expires_at' => now()->addMinutes(5)]);
                return back()->withErrors([
                    'verification_code' => 'Demasiados intentos fallidos. Cuenta bloqueada por 5 minutos.',
                    'remaining_time' => 300 // Tiempo bloqueado
                ]);
            }

            return back()->withErrors([
                'verification_code' => 'Código incorrecto. Intentos restantes: '. (3 - $user->failed_attempts)
            ]);
        }

        // Si todo es correcto, reiniciar los intentos fallidos
        $user->update(['failed_attempts' => 0]);

        return back()->with('success', 'Código verificado correctamente.');

    
        // Código válido - proceder con autenticación
        $user->update([
            'is_verified' => true,
            'phone_verified' => true,
            'codem' => null,
            'codem_expires_at' => null,
            'failed_attempts' => 0
        ]);
    
        Auth::login($user);
        return redirect()->route('home')->with('success', '¡Bienvenido!');
    }
    
}
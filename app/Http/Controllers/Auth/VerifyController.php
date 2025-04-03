<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VerifyController extends Controller
{
    public function create(Request $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $isBlocked = $user->failed_attempts >= 3;
        $expiresAt = $isBlocked ? $user->codem_expires_at : null;
        $remainingTime = $isBlocked ? max(0, $expiresAt->diffInSeconds(now())) : 0;

        if ($isBlocked && $remainingTime <= 0) {
            $user->update(['failed_attempts' => 0]);
            $isBlocked = false;
        }

        if ($isBlocked) {
            Log::warning("Usuario bloqueado: {$user->email} - Tiempo restante: {$remainingTime} segundos");
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

        if ($user->hasExpiredCode()) {
            $user->invalidateCode();
            Log::info("Código expirado para usuario {$user->email}");
            return back()->withErrors(['verification_code' => 'El código ha expirado. Por favor solicita uno nuevo.']);
        }

        if ($user->failed_attempts >= 3) {
            $remainingTime = max(0, min($user->codem_expires_at->diffInSeconds(now()), 300));
            Log::warning("Intentos fallidos para usuario {$user->email} - Bloqueado por {$remainingTime} segundos");
            return back()->withErrors(['verification_code' => 'Debes esperar a que expire el código actual.']);
        }

        if (!Hash::check($request->verification_code, $user->codem)) {
            $user->increment('failed_attempts');
            Log::error("Código incorrecto para usuario {$user->email} - Intentos restantes: " . (3 - $user->failed_attempts));
            return back()->withErrors(['verification_code' => 'Código incorrecto. Intentos restantes: ' . (3 - $user->failed_attempts)]);
        }

        $user->update([
            'is_verified' => true,
            'phone_verified' => true,
            'codem' => null,
            'codem_expires_at' => null,
            'failed_attempts' => 0
        ]);

        Log::info("Usuario verificado correctamente: {$user->email}");
        Auth::login($user);
        return redirect()->route('home')->with('success', '¡Bienvenido!');
    }
}

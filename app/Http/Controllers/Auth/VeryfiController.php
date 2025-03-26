<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
class VeryfiController extends Controller
{
    public function create(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return redirect()->route('login.index');
        }

        return view('auth.verification', [
            'email' => $user->email,
            'phone_last_digits' => substr($user->phone_number, -4) // Muestra últimos 4 dígitos
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|numeric|digits:6',
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        // Verificar intentos fallidos
        if ($user->failed_attempts >= 3) {
            return back()->withErrors([
                'verification_code' => 'Demasiados intentos. Inicia sesión nuevamente.'
            ]);
        }

        // Verificar expiración
        if (now()->gt($user->codem_expires_at)) {
            return back()->withErrors(['verification_code' => 'Código expirado']);
        }

        // Verificar código
        if (!Hash::check($request->verification_code, $user->codem)) {
            $user->increment('failed_attempts');
            return back()->withErrors(['verification_code' => 'Código incorrecto']);
        }

        // Autenticación exitosa
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
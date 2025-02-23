<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\URL;

class VeryfiController extends Controller
{
    /**
     * Muestra la vista de verificación de código.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $email = $request->email;
        $user = User::where('email', $request->email)->first();
        if ($user) {
            // Pasar los valores de correo electrónico y contraseña a la vista
            return view('auth.verification', ['email' => $email]);
        } else {
            return redirect()->route('login.index');
        }
    }



    public function store(Request $request)
    {
        $request->validate([
            'verification_code' => ['required', 'numeric', 'digits:6'],
            'email' => ['required', 'email'],
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return back()->withErrors(['email' => 'Usuario no encontrado']);
        }
    
        // Verificar si el código ha expirado
        if ($user->codem_expires_at && now()->greaterThan($user->codem_expires_at)) {
            return back()->withErrors(['verification_code' => 'El código ha expirado. Solicita uno nuevo.']);
        }
    
        // Verificar intentos fallidos
        if ($user->failed_attempts >= 3) {
            return back()->withErrors([
                'verification_code' => 'Demasiados intentos fallidos. Solicita un nuevo código.'
            ]);
        }
    
        // Verificar si el código ingresado es correcto
        if (!password_verify($request->verification_code, $user->codem)) {
            $user->increment('failed_attempts'); // Incrementar intentos fallidos
    
            // Si se alcanzaron los 3 intentos fallidos, mostrar mensaje específico
            if ($user->failed_attempts >= 3) {
                return back()->withErrors([
                    'verification_code' => 'Has alcanzado el límite de intentos. Solicita un nuevo código.'
                ]);
            }
    
            return back()->withErrors(['verification_code' => 'Código incorrecto. Intenta nuevamente.']);
        }
    
        // Si el código es correcto, limpiar datos
        $user->update([
            'codem' => null,
            'codem_expires_at' => null,
            'failed_attempts' => 0,
        ]);
    
        // Iniciar sesión y redirigir al usuario
        Auth::login($user);
    
        return redirect()->route('home')->with('verify2fa_success', 'Código de confirmación correcto');
    }
    
}

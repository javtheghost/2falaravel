<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\SmsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        return $request->user();
    }

    public function create()
    {
        Log::info('Acceso a página de inicio de sesión');
        return view('auth.login');
    }
    public function store(Request $request)
    {
        $this->validateLoginRequest($request);
    
        try {
            $user = $this->getUserByEmail($request->email);
    
            if (!$user || !$this->isValidPassword($request->password, $user->password)) {
                return $this->handleFailedLogin();
            }
    
            Log::info('Usuario validado: ' . $user->email);
    
            // Verificar si el código aún es válido
            if ($user->code_expires_at && now()->lt($user->code_expires_at)) {
                return back()->withErrors(['sms' => 'Ya has solicitado un código SMS recientemente. Inténtalo más tarde.']);
            }
    
            // Generar nuevo código y enviarlo
            $newCode = rand(100000, 999999); // Código de 6 dígitos
            $smsController = new SmsController();
            $smsResult = $smsController->sendVerificationCode($user, $newCode);
    
            if ($smsResult) {
                // Guardar el nuevo código y tiempo de expiración en la base de datos
                $user->update([
                    'verification_code' => $newCode,
                    'code_expires_at' => now()->addMinutes(5) // Expira en 5 minutos
                ]);
    
                // Guardar email en sesión para verificación posterior
                $request->session()->put('verifying_user', $user->id);
                return redirect()->route('auth.verification', ['email' => $user->email]);
            }
    
            throw new \Exception('No se pudo enviar el código de verificación');
    
        } catch (\Exception $e) {
            Log::error('Error en login: ' . $e->getMessage());
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }
    

    private function validateLoginRequest(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'g-recaptcha-response' => 'required|captcha',
        ], [
            'g-recaptcha-response.required' => 'Completa el reCAPTCHA',
            'g-recaptcha-response.captcha' => 'reCAPTCHA inválido',
        ]);
    }

    private function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    private function isValidPassword($inputPassword, $storedPassword)
    {
        return Hash::check($inputPassword, $storedPassword);
    }

    private function handleFailedLogin()
    {
        Log::warning('Intento de inicio de sesión fallido');
        return back()->withErrors(['message' => 'Credenciales incorrectas']);
    }

    public function logout(Request $request)
    {
        Log::info('Cierre de sesión: ' . Auth::user()->email);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login.index')->with('logout_success', 'Sesión cerrada');
    }
}
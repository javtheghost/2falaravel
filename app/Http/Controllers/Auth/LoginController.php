<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

            // Generar y enviar código SMS directamente
            $smsController = new SmsController();
            if ($smsController->sendVerificationCode($user)) {
                return redirect()->route('auth.verification', ['email' => $user->email]);
            }

            throw new \Exception('Error al enviar el código de verificación');

        } catch (ValidationException $e) {
            Log::error('Error de validación: ' . $e->getMessage());
            throw $e;
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
        throw new \Exception('Credenciales incorrectas');
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
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{

    /**
     * Muestra la vista del formulario de registro.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {

        return view('auth.register');
    }

    /**
     * Almacena un nuevo usuario después de validar
     * la información del formulario de registro.
     *
     * @return \Illuminate\Http\RedirectResponse
     */


     public function register()
     {
         $validatedData = $this->validateUserData();
 
         // Generar token seguro (40 caracteres) y su hash
         $verificationToken = bin2hex(random_bytes(20));
         $hashedToken = Hash::make($verificationToken);
 
         $user = User::create([
             'name' => $validatedData['name'],
             'email' => $validatedData['email'],
             'password' => Hash::make($validatedData['password']),
             'verification_token' => $hashedToken,
             'verification_token_expires_at' => now()->addHours(24),
             'phone_number' => '+52' . ltrim($validatedData['phone_number'], '+'),

         ]);
 
      
         Log::info("Usuario registrado exitosamente: {$user->email}");
 
         return redirect()
             ->route('login.index')
             ->with('success', 'Registro exitoso. Por favor, inicia sesión.');
     }
 
     private function validateUserData()
     {
         return request()->validate(
             [
                 'name' => ['required', 'string', 'max:255'],
                 'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
                 'password' => [
                     'required',
                     'confirmed',
                     'min:8',
                     'regex:/^(?=.*[0-9])(?=.*[^a-zA-Z0-9])/',
                 ],
                 'g-recaptcha-response' => 'required|captcha',
                 'phone_number' => 'required|string|digits_between:10,15',
             ],
             [
                 'g-recaptcha-response.required' => 'Por favor, completa el campo reCAPTCHA.',
                 'g-recaptcha-response.captcha' => 'El campo reCAPTCHA no es válido. Por favor, inténtalo de nuevo.',
                 'password.regex' => 'La contraseña debe tener al menos 8 caracteres e incluir al menos un número y un carácter especial.',
             ]
         );
     }

}

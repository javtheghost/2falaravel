<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
         // Validar los datos
         $validatedData = $this->validateUserData();

         // Crear el usuario
         $user = User::create([
             'name' => $validatedData['name'],
             'email' => $validatedData['email'],
             'password' => bcrypt($validatedData['password']),
         ]);

         // Registrar en el log
         Log::info("Usuario registrado exitosamente: {$user->email}");

         // Redirigir al formulario de login con un mensaje de éxito
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
             ],
             [
                 'g-recaptcha-response.required' => 'Por favor, completa el campo reCAPTCHA.',
                 'g-recaptcha-response.captcha' => 'El campo reCAPTCHA no es válido. Por favor, inténtalo de nuevo.',
                 'password.regex' => 'La contraseña debe tener al menos 8 caracteres e incluir al menos un número y un carácter especial.',
             ]
         );
     }



}

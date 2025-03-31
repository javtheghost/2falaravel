<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register', [
            'trialMode' => config('app.trial_mode'),  // Determina si el modo de prueba está activado
            'allowedNumbers' => config('app.twilio_trial_numbers', []),  // Números permitidos en el modo prueba
            'defaultNumber' => config('app.twilio_trial_numbers')[0] ?? null,  // Número predeterminado, si existe
        ]);
    }
    
    public function register()
    {
        $validatedData = $this->validateUserData();
    
        // Crear el usuario con el número telefónico formateado
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'phone_number' => $this->formatPhoneNumber($validatedData['phone_number']),
            'verification_token' => Hash::make(bin2hex(random_bytes(20))),
            'verification_token_expires_at' => now()->addHours(24),
        ]);
    
        Log::info("Usuario registrado: {$user->email}");
        return redirect()->route('login.index')->with('success', 'Registro exitoso. Por favor, inicia sesión.');
    }

    private function validateUserData()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/', // Expresión regular para un correo válido
            ],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[0-9])(?=.*[!@#$%^&*(),.?":{}|<>]).*$/', // Al menos un número y un carácter especial
            ],
            'g-recaptcha-response' => 'required|captcha',
            'phone_number' => ['required', 'string'],
        ];
    
        if (config('app.trial_mode')) {
            // Validación para que solo se acepten números específicos en modo prueba
            $rules['phone_number'][] = function ($attribute, $value, $fail) {
                if (!in_array($value, config('app.twilio_trial_numbers'))) {
                    $fail('Solo se permiten registros con el siguiente número para esta práctica: '.implode(', ', config('app.twilio_trial_numbers')));
                }
            };
        } else {
            // Validación para aceptar solo números de 10 dígitos
            $rules['phone_number'] = array_merge($rules['phone_number'], [
                'digits:10',
                'regex:/^[0-9]{10}$/', // Expresión regular para asegurar que el número solo tenga 10 dígitos numéricos
            ]);
        }
    
        return request()->validate($rules, [
            'g-recaptcha-response.required' => 'Por favor, completa el campo reCAPTCHA.',
            'g-recaptcha-response.captcha' => 'El campo reCAPTCHA no es válido. Por favor, inténtalo de nuevo.',
            'password.regex' => 'La contraseña debe tener al menos 8 caracteres, un número y un carácter especial.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'email.regex' => 'El correo electrónico debe tener un formato válido (ej. ejemplo@dominio.com).',
            'email.unique' => 'Este correo electrónico ya está registrado. Intenta con otro.',
            'phone_number.digits' => 'El número telefónico debe tener 10 dígitos.',
            'phone_number.regex' => 'El número telefónico debe contener solo números de 10 dígitos.',
        ]);
    }

    protected function formatPhoneNumber($number)
    {
        return '+52' . ltrim($number, '+');
    }
}

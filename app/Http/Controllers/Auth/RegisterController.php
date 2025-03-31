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
            'trialMode' => config('app.trial_mode'),
            'allowedNumbers' => config('app.twilio_trial_numbers'),
            'defaultNumber' => config('app.twilio_trial_numbers')[0] ?? null
        ]);
    }

    public function register()
    {
        $validatedData = $this->validateUserData();

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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[0-9])(?=.*[^a-zA-Z0-9])/',
            ],
            'g-recaptcha-response' => 'required|captcha',
            'phone_number' => ['required', 'string'],
        ];

        if (config('app.trial_mode')) {
            $rules['phone_number'][] = function ($attribute, $value, $fail) {
                if (!in_array($value, config('app.twilio_trial_numbers'))) {
                    $fail('Solo se permiten registros con los siguientes números durante el periodo de prueba: '.implode(', ', config('app.twilio_trial_numbers')));
                }
            };
        } else {
            $rules['phone_number'] = array_merge($rules['phone_number'], [
                'digits:10',
                'regex:/^[0-9]{10}$/'
            ]);
        }

        return request()->validate($rules, [
            'g-recaptcha-response.required' => 'Por favor, completa el campo reCAPTCHA.',
            'g-recaptcha-response.captcha' => 'El campo reCAPTCHA no es válido. Por favor, inténtalo de nuevo.',
            'password.regex' => 'La contraseña debe tener al menos 8 caracteres, un número y un carácter especial.',
            'phone_number.digits' => 'El número telefónico debe tener 10 dígitos.',
            'phone_number.regex' => 'El número telefónico debe tener 10 dígitos numéricos.',
        ]);
    }

    private function formatPhoneNumber($number)
    {
        return '+52' . ltrim($number, '+');
    }
}
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register', [
            'trialMode' => config('app.trial_mode'),
            'allowedNumbers' => config('app.twilio_trial_numbers')
        ]);
    }

    public function register()
    {
        try {
            $validatedData = $this->validateUserData();
            
            $user = $this->createUser($validatedData);
            
            Log::info("Nuevo usuario registrado", ['email' => $user->email]);
            
            return redirect()
                ->route('login.index')
                ->with('success', 'Registro exitoso. Por favor, inicia sesión.');
                
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Error en registro: " . $e->getMessage());
            return back()
                ->with('error', 'Ocurrió un error durante el registro. Por favor, intenta nuevamente.')
                ->withInput();
        }
    }

    protected function validateUserData()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[0-9])(?=.*[^a-zA-Z0-9])/',
            ],
            'g-recaptcha-response' => 'required|captcha',
            'phone_number' => $this->getPhoneValidationRules(),
        ];

        $messages = [
            'password.regex' => 'La contraseña debe contener al menos un número y un carácter especial.',
            'phone_number.in' => 'Número télefono permitido: :values',
        ];

        return Validator::make(request()->all(), $rules, $messages)->validate();
    }

    protected function getPhoneValidationRules()
    {
        $rules = ['required', 'string', 'digits:10'];
        
        if (config('app.trial_mode')) {
            $rules[] = Rule::in(config('app.twilio_trial_numbers'));
        }
        
        return $rules;
    }

    protected function createUser(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone_number' => $this->formatPhoneNumber($data['phone_number']),
            'verification_token' => Hash::make(bin2hex(random_bytes(20))),
            'verification_token_expires_at' => now()->addHours(24),
        ]);
    }

    protected function formatPhoneNumber($number)
    {
        return '+52' . ltrim($number, '+');
    }
}
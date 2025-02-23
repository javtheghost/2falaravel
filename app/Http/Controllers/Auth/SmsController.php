<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsController extends Controller
{
    /**
     * Muestra el formulario de verificación de teléfono o
     * envía un código de verificación si ya existe un número de teléfono asociado al usuario autenticado.
     */
    public function create(Request $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();

        Log::info('Usuario encontrado: ' . $user->email);

        return view('auth.phone', [
            'email' => $request->email,
            'password' => $request->password,
        ]);
    }

    /**
     * Almacena el número de teléfono proporcionado por el usuario,
     * genera y envía un código de verificación por SMS.
     */
    public function store(Request $request)
    {
        // Laravel lanza automáticamente ValidationException si falla la validación
        $request->validate([
            'phone_number' => ['required', 'numeric'],
        ]);

        $rawCode = mt_rand(100000, 999999);
        $code = password_hash($rawCode, PASSWORD_DEFAULT);

        $user = User::where('email', $request->input('email'))->firstOrFail();

        Log::info('Correo electrónico: ' . $request->input('email'));

        $phoneNumber = $request->input('phone_number');
        $phoneNumberWithCountryCode = '+52' . ltrim($phoneNumber, '+');

        $user->update([
            'codem' => $code,
            'codem_expires_at' => now()->addMinutes(3), // Expira en 3 minutos
            'failed_attempts' => 0, // Reiniciar intentos fallidos
            'phone_number' => $phoneNumberWithCountryCode,
        ]);

        Log::info("Código de verificación generado para {$user->email}");
        Log::info("Número de teléfono actualizado: $phoneNumberWithCountryCode");

        // Enviar SMS (si hay un error, Laravel lo propagará)
        $this->sendSms($phoneNumberWithCountryCode, "Su código de verificación: $rawCode");

        return redirect()->route('auth.verification', ['email' => $user->email]);
    }

    /**
     * Envía un mensaje de texto (SMS) con el código de
     * verificación al número de teléfono especificado.
     */
    protected function sendSms($to, $body)
    {
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        $twilio->messages->create($to, [
            'from' => env('TWILIO_PHONE_NUMBER'),
            'body' => $body,
        ]);

        Log::info("SMS enviado correctamente a: $to");
    }
}

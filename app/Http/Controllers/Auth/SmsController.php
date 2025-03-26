<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsController extends Controller
{
    public function sendVerificationCode(User $user)
    {
        try {
            // Generar código de 6 dígitos
            $rawCode = random_int(100000, 999999);
            
            // Actualizar usuario con el nuevo código
            $user->update([
                'codem' => bcrypt($rawCode),
                'codem_expires_at' => now()->addMinutes(5),
                'failed_attempts' => 0
            ]);

            // Enviar SMS
            $this->sendSms($user->phone_number, "Tu código de verificación: $rawCode");

            Log::info("Código enviado a: {$user->phone_number}");
            return true;

        } catch (\Exception $e) {
            Log::error("Error enviando SMS: " . $e->getMessage());
            return false;
        }
    }

    protected function sendSms($to, $body)
    {
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        $twilio->messages->create($to, [
            'from' => env('TWILIO_PHONE_NUMBER'),
            'body' => $body,
        ]);
    }
}
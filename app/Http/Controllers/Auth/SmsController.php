<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    /**
     * Genera y envía un código de verificación por SMS
     * @param User $user
     * @return bool
     */
    public function sendVerificationCode(User $user)
    {
        try {
            // Validar que el usuario tenga número telefónico
            if (empty($user->phone_number)) {
                throw new \Exception('El usuario no tiene número telefónico registrado');
            }

            // Generar código de 6 dígitos
            $rawCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Actualizar usuario con el nuevo código
            $user->update([
                'codem' => bcrypt($rawCode),
                'codem_expires_at' => now()->addMinutes(5),
                'failed_attempts' => 0, // Resetear intentos fallidos
                'is_verified' => false // Asegurar que no está verificado
            ]);

            // Enviar SMS
            $this->sendSms($user->phone_number, "Tu código de verificación: $rawCode");

            Log::info("Código enviado a usuario {$user->id} - Tel: {$user->phone_number}");
            return true;

        } catch (\Exception $e) {
            Log::error("Error enviando SMS a usuario {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reenvía el código de verificación
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendCode(Request $request)
    {
        // Validar entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos'
            ], 400);
        }

        try {
            $user = User::where('email', $request->email)->firstOrFail();

            // Verificar si el código anterior ya expiró
            if ($user->codem_expires_at && now()->lt($user->codem_expires_at)) {
                $remainingTime = now()->diffInSeconds($user->codem_expires_at);
                
                // No permitir reenvío si aún no expira el código anterior
                if ($remainingTime > 60) { // 1 minuto de gracia
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes esperar a que expire el código actual',
                        'remaining_time' => $remainingTime
                    ], 429);
                }
            }

            // Enviar nuevo código
            if ($this->sendVerificationCode($user)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Código reenviado exitosamente'
                ]);
            }

            throw new \Exception('Error al generar el código');

        } catch (\Exception $e) {
            Log::error("Error reenviando código: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al reenviar el código'
            ], 500);
        }
    }

    /**
     * Envía un mensaje SMS usando Twilio
     * @param string $to Número telefónico
     * @param string $body Cuerpo del mensaje
     * @throws \Twilio\Exceptions\TwilioException
     */
    protected function sendSms($to, $body)
    {
        // Validar número telefónico
        if (empty($to)) {
            throw new \InvalidArgumentException('Número telefónico no puede estar vacío');
        }

        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        
        $twilio->messages->create($to, [
            'from' => env('TWILIO_PHONE_NUMBER'),
            'body' => $body,
        ]);

        Log::debug("SMS enviado a $to: " . substr($body, 0, 20) . "...");
    }
}
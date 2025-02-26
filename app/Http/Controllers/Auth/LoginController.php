<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        return $request->user();
    }

    /**
     * Muestra la vista del formulario de inicio de sesión.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        Log::info('Acceso a la página de inicio de sesión.');
        return view('auth.login');
    }

    /**
     * Autentica al usuario a partir de las credenciales proporcionadas
     * y gestiona la redirección según el tipo de usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validación de entrada
        $this->validateLoginRequest($request);

        try {
            // Verificación de credenciales del usuario
            $user = $this->getUserByEmail($request->email);

            if (!$user) {
                Log::warning('Intento de inicio de sesión fallido: usuario no encontrado - ' . $request->email);
                return $this->handleFailedLogin();
            }

            if (!$this->isValidPassword($request->password, $user->password)) {
                Log::warning('Intento de inicio de sesión fallido: contraseña incorrecta para - ' . $request->email);
                return $this->handleFailedLogin();
            }

            Log::info('Usuario Logeado exitosamente: ' . $user->email);

            // Crear un token de verificación
            $verificationToken = Str::random(10);  // la longitud del token
            $user->verification_token = $verificationToken;
            $user->save();  // Guarda el token en la base de datos

            // Comprobar si el usuario ha completado el proceso de 2FA
            if (!$user->is_verified) {
                // Crear la URL firmada temporal con el token y expiración de 10 minutos
                $signedUrl = URL::temporarySignedRoute('auth.phone',
                    now()->addMinutes(10),
                    [
                        'email' => $user->email,
                        'verification_token' => $verificationToken
                    ]
                );

                Log::info('URL firmada generada para 2FA: ' . $signedUrl);  // Log de la URL firmada

                // Redirigir al proceso de verificación 2FA con la URL firmada
                return redirect()->to($signedUrl);
            }

            // Si el usuario ya está verificado, redirigir al home o página correspondiente
            Log::info('Usuario verificado, redirigiendo a home: ' . $user->email);
            return redirect()->route('home')->with('login_success', 'Inicio de sesión exitoso.');

        } catch (ValidationException $e) {
            Log::error('Error de validación en el inicio de sesión: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error de autenticación: ' . $e->getMessage());
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    // Método para validar la solicitud de login
    private function validateLoginRequest(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'g-recaptcha-response' => 'required|captcha',
        ], [
            'g-recaptcha-response.required' => 'Por favor, completa el campo reCAPTCHA.',
            'g-recaptcha-response.captcha' => 'El campo reCAPTCHA no es válido. Por favor, inténtalo de nuevo.',
        ]);
    }

    // Método para obtener al usuario por email
    private function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    // Método para verificar la contraseña del usuario
    private function isValidPassword($inputPassword, $storedPassword)
    {
        return Hash::check($inputPassword, $storedPassword);
    }

    // Método para manejar un inicio de sesión fallido
    private function handleFailedLogin()
    {
        Log::warning('Intento de inicio de sesión fallido debido a credenciales incorrectas.');
        throw new \Exception('El correo electrónico o la contraseña son incorrectos. Por favor, inténtalo de nuevo.');
    }

    public function logout(Request $request)
    {
        // Log de la acción de logout
        Log::info('Usuario cerró sesión: ' . Auth::user()->email);

        // Cierra la sesión del usuario
        Auth::logout();

        // Opcionalmente, puedes invalidar la sesión para prevenir ataques de fijación de sesión
        $request->session()->invalidate();

        // Regenera el token de la sesión
        $request->session()->regenerateToken();

        // Redirige al usuario a la página de inicio o login
        Log::info('Redirigiendo a la página de login después del cierre de sesión.');
        return redirect()->route('login.index')->with('logout_success', 'Sesión cerrada con éxito.');
    }
}

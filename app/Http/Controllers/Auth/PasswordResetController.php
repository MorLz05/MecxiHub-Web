<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Kreait\Firebase\Contract\Auth;
use Google\Cloud\Firestore\FirestoreClient;
use Ramsey\Uuid\Uuid;

class PasswordResetController extends Controller
{
    protected Auth $auth;
    protected FirestoreClient $firestoreDb;

    public function __construct(Auth $auth, FirestoreClient $firestoreDb)
    {
        $this->auth = $auth;
        $this->firestoreDb = $firestoreDb;
    }

    /* public function __construct()
    {
        try {
            // 1. Encontrar el archivo de credenciales
            $credentialsFile = env('FIREBASE_CREDENTIALS', 'firebase-credentials.json');

            $possiblePaths = [
                storage_path('app/' . $credentialsFile),
                storage_path($credentialsFile),
                base_path($credentialsFile),
                base_path('storage/app/' . $credentialsFile),
                base_path('storage/app/firebase-credentials.json'),
            ];

            $credentialsPath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $credentialsPath = $path;
                    break;
                }
            }

            if (!$credentialsPath) {
                throw new \Exception('No se encontró el archivo de credenciales en: ' . implode(', ', $possiblePaths));
            }

            Log::info('Credenciales encontradas en: ' . $credentialsPath);

            // 2. Leer y validar el JSON
            $jsonContent = file_get_contents($credentialsPath);
            $credentials = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('El archivo de credenciales no es un JSON válido');
            }

            // 3. Inicializar Firebase Auth
            $factory = (new \Kreait\Firebase\Factory)->withServiceAccount($credentialsPath);
            $this->auth = $factory->createAuth();
            Log::info('Firebase Auth inicializado correctamente');

            // 4. Inicializar Firestore con diferentes métodos
            // Método 1: Usando la variable de entorno GOOGLE_APPLICATION_CREDENTIALS
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

            // Método 2: Usando la configuración directa
            try {
                $config = [
                    'keyFilePath' => $credentialsPath,
                    'projectId' => $credentials['project_id'],
                ];

                $this->firestoreDb = new \Google\Cloud\Firestore\FirestoreClient($config);
                Log::info('Firestore inicializado con configuración directa');
            } catch (\Exception $e1) {
                Log::warning('Error con configuración directa: ' . $e1->getMessage());

                // Método 3: Intentar sin configuración (usando variable de entorno)
                try {
                    $this->firestoreDb = new \Google\Cloud\Firestore\FirestoreClient([
                        'projectId' => $credentials['project_id']
                    ]);
                    Log::info('Firestore inicializado usando variable de entorno');
                } catch (\Exception $e2) {
                    Log::error('Error con variable de entorno: ' . $e2->getMessage());

                    // Método 4: Usar el factory de Firebase
                    try {
                        $this->firestoreDb = $factory->createFirestore()->database();
                        Log::info('Firestore inicializado usando factory');
                    } catch (\Exception $e3) {
                        throw new \Exception('No se pudo inicializar Firestore: ' . $e3->getMessage());
                    }
                }
            }

            // Verificar conexión
            try {
                $test = $this->firestoreDb->collection('password_resets')->limit(1)->documents();
                Log::info('Conexión a Firestore verificada exitosamente');
            } catch (\Exception $e) {
                Log::warning('No se pudo verificar la conexión a Firestore: ' . $e->getMessage());
                // Continuamos aunque falle la verificación
            }
        } catch (\Exception $e) {
            Log::error('Error inicializando PasswordResetController: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Si falla, intentamos con la configuración mínima
            try {
                $credentialsPath = base_path('storage/app/firebase-credentials.json');
                if (file_exists($credentialsPath)) {
                    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
                    $factory = (new \Kreait\Firebase\Factory)->withServiceAccount($credentialsPath);
                    $this->auth = $factory->createAuth();
                    $this->firestoreDb = $factory->createFirestore()->database();
                    Log::info('Firebase inicializado con configuración de emergencia');
                }
            } catch (\Exception $e2) {
                throw $e; // Lanzar el error original
            }
        }
    } */

    /**
     * Enviar enlace de restablecimiento de contraseña
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = trim($request->input('email'));

        try {
            Log::info('Solicitud de restablecimiento para: ' . $email);

            // 1. Verificar que el usuario existe en Firebase
            try {
                $user = $this->auth->getUserByEmail($email);
                Log::info('Usuario encontrado: ' . $user->uid);
            } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
                Log::warning('Usuario no encontrado: ' . $email);
                return back()->withErrors(['email' => 'No encontramos una cuenta con este correo electrónico.']);
            }

            // 2. Generar token único
            $token = Uuid::uuid4()->toString();
            $expiresAt = Carbon::now()->addHours(2); // Token válido por 2 horas

            // 3. Guardar token en Firestore
            $resetRequest = [
                'email' => $email,
                'uid' => $user->uid,
                'token' => $token,
                'createdAt' => Carbon::now()->toIso8601String(),
                'expiresAt' => $expiresAt->toIso8601String(),
                'used' => false,
                'ip' => $request->ip()
            ];

            $this->firestoreDb
                ->collection('password_resets')
                ->document($token)
                ->set($resetRequest);

            Log::info('Token guardado en Firestore: ' . $token);

            // 4. Enviar email con el enlace
            $this->sendResetEmail($email, $token);

            Log::info('Email enviado exitosamente a: ' . $email);

            return back()->with('success', 'Hemos enviado un enlace de restablecimiento a tu correo electrónico. Revisa tu bandeja de entrada y SPAM.');
        } catch (\Exception $e) {
            Log::error('Error al enviar enlace de restablecimiento: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->withErrors(['error' => 'Ocurrió un error al procesar tu solicitud. Intenta nuevamente.']);
        }
    }

    /**
     * Mostrar formulario para cambiar contraseña
     */
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return redirect()->route('login')->withErrors(['error' => 'Enlace inválido.']);
        }

        try {
            // Verificar token en Firestore
            $resetDoc = $this->firestoreDb
                ->collection('password_resets')
                ->document($token)
                ->snapshot();

            if (!$resetDoc->exists()) {
                return redirect()->route('login')->withErrors(['error' => 'Enlace inválido o expirado.']);
            }

            $resetData = $resetDoc->data();

            // Verificar que el email coincida
            if ($resetData['email'] !== $email) {
                return redirect()->route('login')->withErrors(['error' => 'Enlace inválido.']);
            }

            // Verificar que no haya sido usado
            if ($resetData['used']) {
                return redirect()->route('login')->withErrors(['error' => 'Este enlace ya ha sido utilizado. Solicita uno nuevo.']);
            }

            // Verificar que no haya expirado
            $expiresAt = Carbon::parse($resetData['expiresAt']);
            if (Carbon::now()->greaterThan($expiresAt)) {
                return redirect()->route('login')->withErrors(['error' => 'El enlace ha expirado. Solicita uno nuevo.']);
            }

            return view('auth.passwords.reset', [
                'token' => $token,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Error al verificar token: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['error' => 'Error al verificar el enlace.']);
        }
    }

    /**
     * Actualizar la contraseña
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed'
        ]);

        $token = $request->input('token');
        $email = $request->input('email');
        $newPassword = $request->input('password');

        try {
            Log::info('Intentando resetear contraseña para: ' . $email);

            // 1. Verificar token
            $resetDoc = $this->firestoreDb
                ->collection('password_resets')
                ->document($token)
                ->snapshot();

            if (!$resetDoc->exists()) {
                return back()->withErrors(['error' => 'Enlace inválido o expirado.']);
            }

            $resetData = $resetDoc->data();

            // Validar token
            if ($resetData['email'] !== $email || $resetData['used']) {
                return back()->withErrors(['error' => 'Enlace inválido.']);
            }

            $expiresAt = Carbon::parse($resetData['expiresAt']);
            if (Carbon::now()->greaterThan($expiresAt)) {
                return back()->withErrors(['error' => 'El enlace ha expirado. Solicita uno nuevo.']);
            }

            // 2. Actualizar contraseña en Firebase Auth
            $this->auth->updateUser($resetData['uid'], [
                'password' => $newPassword
            ]);

            Log::info('Contraseña actualizada para usuario: ' . $resetData['uid']);

            // 3. Marcar token como usado
            $this->firestoreDb
                ->collection('password_resets')
                ->document($token)
                ->update([
                    ['path' => 'used', 'value' => true],
                    ['path' => 'usedAt', 'value' => Carbon::now()->toIso8601String()]
                ]);

            // 4. Registrar acción
            $this->firestoreDb
                ->collection('registros')
                ->add([
                    'tipo' => 'password_reset',
                    'accion' => 'exitosa',
                    'detalles' => ['email' => $email],
                    'fecha' => Carbon::now()->toIso8601String(),
                    'realizadoPor' => $resetData['uid']
                ]);

            Log::info('Password reset completado para: ' . $email);

            return redirect()->route('login')->with('success', 'Contraseña actualizada exitosamente. Ahora puedes iniciar sesión con tu nueva contraseña.');
        } catch (\Exception $e) {
            Log::error('Error al resetear contraseña: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->withErrors(['error' => 'Error al actualizar la contraseña: ' . $e->getMessage()]);
        }
    }

    /**
     * Enviar email con enlace de restablecimiento usando SendGrid
     */
    private function sendResetEmail($email, $token)
    {
        try {
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $email]);
            $apiKey = env('SENDGRID_API_KEY');

            if (!$apiKey) {
                throw new \Exception('SENDGRID_API_KEY no configurada en .env');
            }

            $fromEmail = env('MAIL_FROM_ADDRESS', 'soportemecxihub@gmail.com');
            $fromName = env('MAIL_FROM_NAME', 'MecxiHub');

            // Contenido HTML del email
            $htmlContent = view('emails.password-reset', [
                'token' => $token,
                'email' => $email,
                'resetUrl' => $resetUrl
            ])->render();

            // Construir petición a SendGrid
            $url = 'https://api.sendgrid.com/v3/mail/send';

            $data = [
                'personalizations' => [
                    [
                        'to' => [['email' => $email]],
                        'subject' => 'Restablecer contraseña - MecxiHub'
                    ]
                ],
                'from' => [
                    'email' => $fromEmail,
                    'name' => $fromName
                ],
                'reply_to' => [
                    'email' => $fromEmail,
                    'name' => $fromName
                ],
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => $htmlContent
                    ]
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                Log::info('Email enviado exitosamente a: ' . $email . ' (HTTP ' . $httpCode . ')');
                return true;
            } else {
                Log::error('Error al enviar email: HTTP ' . $httpCode . ' - ' . $response);
                throw new \Exception('Error al enviar email: ' . $response);
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar email: ' . $e->getMessage());
            throw $e;
        }
    }
}

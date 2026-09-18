<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Auth;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class SeguridadController extends Controller
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
            $credentialsFile = env('FIREBASE_CREDENTIALS', 'mecxihub-db-firebase-adminsdk-fbsvc-acf0185b95.json');

            $possiblePaths = [
                storage_path('app/' . $credentialsFile),
                storage_path($credentialsFile),
                base_path($credentialsFile),
                base_path('storage/app/' . $credentialsFile),
            ];

            $credentialsPath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $credentialsPath = $path;
                    break;
                }
            }

            if ($credentialsPath) {
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

                $jsonContent = file_get_contents($credentialsPath);
                $credentials = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $factory = (new \Kreait\Firebase\Factory)->withServiceAccount($credentialsPath);
                    $this->auth = $factory->createAuth();

                    $config = [
                        'keyFilePath' => $credentialsPath,
                        'projectId' => $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID'),
                    ];
                    $this->firestoreDb = new FirestoreClient($config);
                    Log::info('Firebase inicializado en SeguridadController');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error inicializando SeguridadController: ' . $e->getMessage());
            $this->firestoreDb = null;
            $this->auth = null;
        }
    } */

    public function index()
    {
        $user = session('firebase_user');
        return view('taller.seguridad', compact('user'));
    }

    public function updateEmail(Request $request)
    {
        try {
            $user = session('firebase_user');
            if (!$user) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
            }

            $validated = $request->validate([
                'email' => 'required|email|max:255',
            ]);

            $uid = $user['uid'];
            $oldEmail = $user['email'];
            $newEmail = $validated['email'];

            Log::info('Intentando cambiar email para taller: ' . $uid . ' de ' . $oldEmail . ' a ' . $newEmail);

            // Verificar si el nuevo email ya existe
            try {
                $this->auth->getUserByEmail($newEmail);
                return back()->withErrors(['email' => 'Este correo electrónico ya está registrado.']);
            } catch (UserNotFound $e) {
                // Email no existe, es válido
            }

            // Actualizar en Firebase Authentication
            $this->auth->updateUser($uid, [
                'email' => $newEmail
            ]);

            Log::info('Email actualizado en Auth para taller: ' . $uid);

            // Actualizar en Firestore
            if ($this->firestoreDb) {
                // Actualizar en colección de usuarios
                $this->firestoreDb
                    ->collection('usuarios')
                    ->document($uid)
                    ->update([
                        ['path' => 'email', 'value' => $newEmail]
                    ]);

                Log::info('Email actualizado en Firestore para usuario: ' . $uid);

                // Actualizar en colección de talleres si existe taller_id
                $usuarioSnapshot = $this->firestoreDb->collection('usuarios')->document($uid)->snapshot();
                if ($usuarioSnapshot->exists()) {
                    $usuarioData = $usuarioSnapshot->data();
                    $tallerId = $usuarioData['taller_id'] ?? null;

                    if ($tallerId) {
                        $this->firestoreDb
                            ->collection('talleres')
                            ->document($tallerId)
                            ->update([
                                ['path' => 'email', 'value' => $newEmail]
                            ]);
                        Log::info('Email actualizado en taller: ' . $tallerId);
                    }
                }
            }

            // Cerrar sesión y redirigir al login
            session()->forget('firebase_user');

            return redirect()
                ->route('login')
                ->with('success', 'Correo electrónico actualizado exitosamente. Por favor, inicia sesión con tu nueva dirección de correo.');
        } catch (\Exception $e) {
            Log::error('Error actualizando email: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar el correo: ' . $e->getMessage()]);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $user = session('firebase_user');
            if (!$user) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
            }

            $validated = $request->validate([
                'password_actual' => 'required|string',
                'password_nueva' => 'required|min:6|confirmed',
            ]);

            $email = $user['email'];
            $currentPassword = $validated['password_actual'];
            $newPassword = $validated['password_nueva'];

            Log::info('Intentando cambiar contraseña para taller: ' . $email);

            $apiKey = env('FIREBASE_API_KEY');

            if (!$apiKey) {
                return back()->withErrors(['error' => 'Error de configuración. Contacta al administrador.']);
            }

            // 1. Verificar la contraseña actual
            $signInUrl = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$apiKey}";

            $ch = curl_init($signInUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'email' => $email,
                'password' => $currentPassword,
                'returnSecureToken' => true
            ]));

            $signInResponse = curl_exec($ch);
            $signInHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($signInHttpCode !== 200) {
                return back()->withErrors(['password_actual' => 'La contraseña actual es incorrecta.']);
            }

            $signInData = json_decode($signInResponse, true);
            $idToken = $signInData['idToken'] ?? null;

            if (!$idToken) {
                return back()->withErrors(['error' => 'Error de autenticación.']);
            }

            // 2. Actualizar la contraseña
            $updateUrl = "https://identitytoolkit.googleapis.com/v1/accounts:update?key={$apiKey}";

            $ch = curl_init($updateUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'idToken' => $idToken,
                'password' => $newPassword,
                'returnSecureToken' => true
            ]));

            $updateResponse = curl_exec($ch);
            $updateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $updateData = json_decode($updateResponse, true);

            if ($updateHttpCode !== 200 || isset($updateData['error'])) {
                $errorMsg = $updateData['error']['message'] ?? 'Error desconocido';
                return back()->withErrors(['error' => 'Error al cambiar la contraseña: ' . $errorMsg]);
            }

            Log::info('Contraseña actualizada exitosamente para taller: ' . $email);

            // Cerrar sesión y redirigir al login
            session()->forget('firebase_user');

            return redirect()
                ->route('login')
                ->with('success', 'Contraseña actualizada exitosamente. Por favor, inicia sesión con tu nueva contraseña.');
        } catch (\Exception $e) {
            Log::error('Error actualizando contraseña: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar la contraseña: ' . $e->getMessage()]);
        }
    }
}

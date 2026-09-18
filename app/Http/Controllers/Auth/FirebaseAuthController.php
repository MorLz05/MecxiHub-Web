<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\EmailExistsException;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class FirebaseAuthController extends Controller
{
    protected $auth;
    protected $firestore;
    protected $firestoreDb;

    public function __construct()
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

            if (!$credentialsPath) {
                throw new \Exception('No se encontró el archivo de credenciales');
            }

            Log::info('Credenciales encontradas en: ' . $credentialsPath);

            $jsonContent = file_get_contents($credentialsPath);
            $credentials = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('El archivo de credenciales no es un JSON válido');
            }

            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->auth = $factory->createAuth();
            Log::info('Firebase Auth creado correctamente');

            try {
                $config = [
                    'keyFilePath' => $credentialsPath,
                    'projectId' => $credentials['project_id'],
                ];

                $this->firestoreDb = new FirestoreClient($config);
                $this->firestore = $factory->createFirestore();

                Log::info('Firestore creado correctamente con projectId: ' . $credentials['project_id']);

                $testCollection = $this->firestoreDb->collection('usuarios')->limit(1)->documents();
                Log::info('Conexión a Firestore verificada exitosamente');
            } catch (\Exception $e) {
                Log::error('Error al crear Firestore: ' . $e->getMessage());
                try {
                    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
                    $config = [
                        'projectId' => $credentials['project_id'],
                    ];
                    $this->firestoreDb = new FirestoreClient($config);
                    Log::info('Firestore creado con configuración alternativa');
                } catch (\Exception $e2) {
                    Log::error('Error al crear Firestore con configuración alternativa: ' . $e2->getMessage());
                    $this->firestoreDb = null;
                    $this->firestore = null;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error inicializando Firebase: ' . $e->getMessage());
            throw $e;
        }
    }

    public function showRegisterForm()
    {
        if (view()->exists('conductor.register')) {
            return view('conductor.register');
        }
        if (view()->exists('auth.register')) {
            return view('auth.register');
        }
        abort(404, 'Vista de registro no encontrada');
    }

    public function showLoginForm()
    {
        if (view()->exists('conductor.login')) {
            return view('conductor.login');
        }
        if (view()->exists('auth.login')) {
            return view('auth.login');
        }
        abort(404, 'Vista de login no encontrada');
    }

    public function register(Request $request)
    {
        // Si viene del flujo de calificación, forzar rol conductor
        if (session()->has('calificacion_pendiente_token')) {
            $request->merge(['rol' => 'conductor']);
        }

        $rol = $request->input('rol', 'conductor');

        if ($rol === 'conductor') {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|min:6|confirmed',
                'terms' => 'accepted',
            ]);

            $nombreCompleto = $validated['name'];
            $email = $validated['email'];
            $password = $validated['password'];
        } else {
            // Rol: administrador (taller)
            $validated = $request->validate([
                'taller_nombre' => 'required|string|max:255',
                /* 'taller_direccion' => 'required|string|max:255',
            'taller_telefono' => 'required|string|max:20', */
                'email' => 'required|email|max:255',
                'password' => 'required|min:6|confirmed',
                'terms' => 'accepted',
            ]);

            $nombreCompleto = $validated['taller_nombre'];
            $email = $validated['email'];
            $password = $validated['password'];
            $tallerData = [
                'nombre' => $validated['taller_nombre'],
                /* 'direccion' => $validated['taller_direccion'],
            'telefono' => $validated['taller_telefono'], */
            ];
        }

        try {
            Log::info('Intentando registrar usuario: ' . $email . ' con rol: ' . $rol);

            $userProperties = [
                'email' => $email,
                'password' => $password,
                'displayName' => $nombreCompleto,
                'disabled' => false,
            ];

            $createdUser = $this->auth->createUser($userProperties);
            Log::info('Usuario creado en Auth con UID: ' . $createdUser->uid);

            if (!$this->firestoreDb) {
                Log::error('Firestore no disponible');
                return back()->withErrors(['error' => 'Error en el sistema. Intenta más tarde.']);
            }

            $userData = [
                'uid' => $createdUser->uid,
                'email' => $email,
                'nombre_completo' => $nombreCompleto,
                'usuario' => $this->generateUsername($nombreCompleto, $email),
                'rol' => $rol === 'conductor' ? 'Conductor' : 'Administrador',
                'activo' => true,
                'fecha_creacion' => now()->toDateTimeString(),
            ];

            // Guardar usuario en Firestore
            $this->firestoreDb
                ->collection('usuarios')
                ->document($createdUser->uid)
                ->set($userData);

            Log::info('✅ Usuario guardado en Firestore con ID: ' . $createdUser->uid);

            // Si es administrador, crear el taller
            if ($rol === 'taller') {
                // Obtener el número del último taller
                $talleresCollection = $this->firestoreDb->collection('talleres');
                $query = $talleresCollection->orderBy('fecha_creacion', 'desc')->limit(1)->documents();
                $nextNumber = 1;
                foreach ($query as $doc) {
                    if ($doc->exists()) {
                        $docId = $doc->id();
                        if (preg_match('/taller_(\d+)/', $docId, $matches)) {
                            $nextNumber = intval($matches[1]) + 1;
                        }
                    }
                }

                $tallerId = 'taller_' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                $tallerDocData = [
                    'nombre' => $tallerData['nombre'],
                    'email' => $email,
                    'direccion' => $tallerData['direccion'] ?? '',
                    'telefono' => $tallerData['telefono'] ?? '',
                    'verificado' => false,
                    'fecha_creacion' => now()->toDateTimeString(),
                    'usuario_id' => $createdUser->uid,
                ];

                $this->firestoreDb
                    ->collection('talleres')
                    ->document($tallerId)
                    ->set($tallerDocData);

                Log::info('✅ Taller creado en Firestore con ID: ' . $tallerId);

                // Actualizar usuario con taller_id
                $this->firestoreDb
                    ->collection('usuarios')
                    ->document($createdUser->uid)
                    ->update([
                        ['path' => 'taller_id', 'value' => $tallerId]
                    ]);

                Log::info('✅ Usuario actualizado con taller_id: ' . $tallerId);
            }

            // Iniciar sesión automáticamente
            try {
                $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);
                $uid = $signInResult->firebaseUserId();
                Log::info('Usuario autenticado automáticamente: ' . $uid);

                // Obtener datos actualizados del usuario
                $userDoc = $this->firestoreDb
                    ->collection('usuarios')
                    ->document($uid)
                    ->snapshot();

                $userDataFromFirestore = $userDoc->data();
                $nombreCompletoFinal = $userDataFromFirestore['nombre_completo'] ?? $nombreCompleto;
                $rolFinal = $userDataFromFirestore['rol'] ?? 'Conductor';

                session([
                    'firebase_user' => [
                        'uid' => $uid,
                        'email' => $email,
                        'nombre_completo' => $nombreCompletoFinal,
                        'rol' => $rolFinal,
                        'usuario' => $userDataFromFirestore['usuario'] ?? null,
                    ]
                ]);

                Log::info('✅ Sesión iniciada para: ' . $nombreCompletoFinal);

                // ✅ Redirigir respetando prioridad: calificación > rol
                return $this->redirigirSegunContexto(
                    $uid,
                    $email,
                    $nombreCompletoFinal,
                    $rolFinal
                );
            } catch (\Exception $e) {
                Log::error('Error al iniciar sesión automáticamente: ' . $e->getMessage());
                return redirect()
                    ->route('login')
                    ->with('success', '¡Cuenta creada exitosamente! Ahora puedes iniciar sesión.');
            }
        } catch (EmailExistsException $e) {
            Log::error('Email ya registrado: ' . $email);

            $mensaje = 'Este correo electrónico ya está registrado. Por favor, inicia sesión o utiliza otro correo.';

            // ¿Es una cuenta de Google?
            try {
                $existingUser = $this->auth->getUserByEmail($email);
                $providers = array_map(fn($p) => $p->providerId ?? null, $existingUser->providerData ?? []);
                $tieneGoogle   = in_array('google.com', $providers, true);
                $tienePassword = in_array('password', $providers, true);

                if ($tieneGoogle && !$tienePassword) {
                    $mensaje = 'Este correo ya está registrado con Google. Por favor, inicia sesión usando el botón "Continuar con Google".';
                }
            } catch (\Exception $ex) {
                // Silencioso
            }

            return back()
                ->withInput()
                ->withErrors(['email' => $mensaje]);
        } catch (\Exception $e) {
            Log::error('Error en registro: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());

            $errorMessage = 'Error al crear la cuenta. Por favor, intenta nuevamente.';
            $errorString = $e->getMessage();

            if (
                strpos($errorString, 'EMAIL_EXISTS') !== false ||
                strpos($errorString, 'email already exists') !== false ||
                strpos($errorString, 'email is already in use') !== false
            ) {
                $errorMessage = 'Este correo electrónico ya está registrado. Por favor, inicia sesión o utiliza otro correo.';
            } elseif (strpos($errorString, 'WEAK_PASSWORD') !== false) {
                $errorMessage = 'La contraseña es demasiado débil. Debe tener al menos 6 caracteres e incluir letras y números.';
            } elseif (strpos($errorString, 'EMAIL_NOT_FOUND') !== false) {
                $errorMessage = 'El correo electrónico no está registrado.';
            } elseif (strpos($errorString, 'INVALID_PASSWORD') !== false) {
                $errorMessage = 'La contraseña es incorrecta.';
            } elseif (strpos($errorString, 'TOO_MANY_ATTEMPTS') !== false) {
                $errorMessage = 'Demasiados intentos fallidos. Por favor, espera unos minutos e intenta nuevamente.';
            } elseif (strpos($errorString, 'NETWORK_ERROR') !== false) {
                $errorMessage = 'Error de conexión. Verifica tu conexión a internet e intenta nuevamente.';
            }

            return back()
                ->withInput()
                ->withErrors(['error' => $errorMessage]);
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            Log::info('Intentando login: ' . $validated['email']);

            $signInResult = $this->auth->signInWithEmailAndPassword(
                $validated['email'],
                $validated['password']
            );

            $uid = $signInResult->firebaseUserId();
            Log::info('Usuario autenticado: ' . $uid);

            if ($this->firestoreDb) {
                try {
                    $userDoc = $this->firestoreDb
                        ->collection('usuarios')
                        ->document($uid)
                        ->snapshot();

                    if (!$userDoc->exists()) {
                        Log::warning('Usuario no encontrado en Firestore: ' . $uid);
                        $userData = [
                            'uid' => $uid,
                            'email' => $validated['email'],
                            'nombre_completo' => $signInResult->data()['displayName'] ?? $validated['email'],
                            'usuario' => $this->generateUsername($validated['email'], $validated['email']),
                            'rol' => 'Conductor',
                            'activo' => true,
                            'fecha_creacion' => now()->toDateTimeString(),
                        ];

                        $this->firestoreDb
                            ->collection('usuarios')
                            ->document($uid)
                            ->set($userData);

                        Log::info('Documento creado para usuario existente: ' . $uid);
                    }

                    $userData = $userDoc->data();

                    if (!$userData['activo']) {
                        return back()->withErrors(['error' => 'Tu cuenta está desactivada. Contacta al administrador.']);
                    }

                    session([
                        'firebase_user' => [
                            'uid' => $uid,
                            'email' => $validated['email'],
                            'nombre_completo' => $userData['nombre_completo'],
                            'rol' => $userData['rol'],
                            'usuario' => $userData['usuario'] ?? null,
                        ]
                    ]);

                    Log::info('Sesión iniciada para: ' . $userData['nombre_completo']);

                    // ✅ Redirigir respetando prioridad: calificación > rol
                    return $this->redirigirSegunContexto(
                        $uid,
                        $validated['email'],
                        $userData['nombre_completo'],
                        $userData['rol']
                    );
                } catch (\Exception $e) {
                    Log::error('Error al leer Firestore: ' . $e->getMessage());
                    $userDisplayName = $signInResult->data()['displayName'] ?? $validated['email'];
                    session([
                        'firebase_user' => [
                            'uid' => $uid,
                            'email' => $validated['email'],
                            'nombre_completo' => $userDisplayName,
                            'rol' => 'Conductor',
                        ]
                    ]);

                    // ✅ Redirigir respetando prioridad: calificación > rol
                    return $this->redirigirSegunContexto(
                        $uid,
                        $validated['email'],
                        $userDisplayName,
                        'Conductor'
                    );
                }
            } else {
                $userDisplayName = $signInResult->data()['displayName'] ?? $validated['email'];
                session([
                    'firebase_user' => [
                        'uid' => $uid,
                        'email' => $validated['email'],
                        'nombre_completo' => $userDisplayName,
                        'rol' => 'Conductor',
                    ]
                ]);

                // ✅ Redirigir respetando prioridad: calificación > rol
                return $this->redirigirSegunContexto(
                    $uid,
                    $validated['email'],
                    $userDisplayName,
                    'Conductor'
                );
            }
        } catch (UserNotFound $e) {
            Log::error('Usuario no encontrado: ' . $validated['email']);
            return back()->withErrors(['email' => 'Usuario no encontrado.']);
        } catch (\Exception $e) {
            Log::error('Error en login: ' . $e->getMessage());

            $errorMessage = 'Credenciales incorrectas. Por favor, verifica tu correo y contraseña.';

            // Detectar cuenta de Google
            if (
                strpos($e->getMessage(), 'INVALID_LOGIN_CREDENTIALS') !== false ||
                strpos($e->getMessage(), 'INVALID_PASSWORD') !== false
            ) {
                // Verificar si el email existe y es de Google
                try {
                    $existingUser = $this->auth->getUserByEmail($validated['email']);
                    $providers = array_map(fn($p) => $p->providerId ?? null, $existingUser->providerData ?? []);
                    $tienePassword = in_array('password', $providers, true);
                    $tieneGoogle   = in_array('google.com', $providers, true);

                    if ($tieneGoogle && !$tienePassword) {
                        $errorMessage = 'Esta cuenta fue creada con Google. Por favor, inicia sesión con el botón "Continuar con Google".';
                    } elseif (strpos($e->getMessage(), 'INVALID_PASSWORD') !== false) {
                        $errorMessage = 'Contraseña incorrecta. Por favor, inténtalo nuevamente.';
                    }
                } catch (\Exception $ex) {
                    if (strpos($e->getMessage(), 'INVALID_PASSWORD') !== false) {
                        $errorMessage = 'Contraseña incorrecta. Por favor, inténtalo nuevamente.';
                    }
                }
            } elseif (strpos($e->getMessage(), 'EMAIL_NOT_FOUND') !== false) {
                $errorMessage = 'No encontramos una cuenta con este correo electrónico.';
            } elseif (strpos($e->getMessage(), 'TOO_MANY_ATTEMPTS') !== false) {
                $errorMessage = 'Demasiados intentos fallidos. Por favor, espera unos minutos e intenta nuevamente.';
            } elseif (strpos($e->getMessage(), 'USER_DISABLED') !== false) {
                $errorMessage = 'Tu cuenta ha sido desactivada. Por favor, contacta al administrador.';
            }

            return back()
                ->withInput()
                ->withErrors(['email' => $errorMessage]);
        }
    }

    public function logout(Request $request)
    {
        $email = session('firebase_user.email', 'unknown');
        Log::info('Cerrando sesión: ' . $email);
        session()->forget('firebase_user');
        return redirect()->route('login')->with('success', 'Sesión cerrada exitosamente.');
    }

    /**
     * Después de autenticar al usuario, si venía de un flujo de calificación,
     * lo regresamos a procesar el token.
     */
    private function redirigirDespuesDeAuth()
    {
        // Si hay un token de calificación pendiente
        if (session()->has('calificacion_pendiente_token')) {
            $token = session()->pull('calificacion_pendiente_token');
            session()->forget('calificacion_pendiente_taller_id');

            return redirect()->route('calificacion.form', ['token' => $token]);
        }

        // Redirect normal
        return redirect()->intended(route('dashboard'));
    }

    private function generateUsername($name, $email)
    {
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($name));
        $emailPrefix = explode('@', $email)[0];
        $emailPrefix = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($emailPrefix));

        $baseUsername = !empty($cleanName) ? $cleanName : $emailPrefix;
        $baseUsername = substr($baseUsername, 0, 10);

        $username = $baseUsername;
        $counter = 1;

        try {
            if ($this->firestoreDb) {
                do {
                    $existingUser = $this->firestoreDb
                        ->collection('usuarios')
                        ->where('usuario', '=', $username)
                        ->documents();

                    if (!$existingUser->isEmpty()) {
                        $username = $baseUsername . '_' . $counter;
                        $counter++;
                    } else {
                        break;
                    }
                } while (true);
            } else {
                $username = $baseUsername . '_' . time();
            }
        } catch (\Exception $e) {
            $username = $baseUsername . '_' . time();
        }

        return $username;
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = session('firebase_user');
            if (!$user) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
            }

            $validated = $request->validate([
                'nombre_completo' => 'required|string|max:255',
                'email' => 'required|email|max:255',
            ]);

            $uid = $user['uid'];
            $oldEmail = $user['email'];

            $updateProperties = [
                'displayName' => $validated['nombre_completo'],
            ];

            if ($validated['email'] !== $oldEmail) {
                $updateProperties['email'] = $validated['email'];
                try {
                    $this->auth->getUserByEmail($validated['email']);
                    return back()->withErrors(['email' => 'Este correo electrónico ya está registrado.']);
                } catch (UserNotFound $e) {
                    // Email no existe, es válido
                }
            }

            $this->auth->updateUser($uid, $updateProperties);

            if ($this->firestoreDb) {
                $updates = [
                    ['path' => 'nombre_completo', 'value' => $validated['nombre_completo']],
                ];

                if ($validated['email'] !== $oldEmail) {
                    $updates[] = ['path' => 'email', 'value' => $validated['email']];
                }

                $this->firestoreDb
                    ->collection('usuarios')
                    ->document($uid)
                    ->update($updates);
            }

            $sessionData = session('firebase_user');
            $sessionData['nombre_completo'] = $validated['nombre_completo'];
            if ($validated['email'] !== $oldEmail) {
                $sessionData['email'] = $validated['email'];
            }
            session(['firebase_user' => $sessionData]);

            $message = 'Perfil actualizado exitosamente.';
            if ($validated['email'] !== $oldEmail) {
                $message .= ' Se ha cambiado tu correo electrónico. Por favor, inicia sesión nuevamente con tu nueva dirección de correo.';
                session()->forget('firebase_user');
                return redirect()->route('login')->with('success', $message);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el perfil: ' . $e->getMessage()]);
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

            // Cerrar sesión y redirigir al login
            session()->forget('firebase_user');

            return redirect()
                ->route('login')
                ->with('success', 'Contraseña actualizada exitosamente. Por favor, inicia sesión con tu nueva contraseña.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar la contraseña: ' . $e->getMessage()]);
        }
    }

    /**
     * Decide a dónde redirigir después de autenticar.
     * Prioridad:
     *   1. Si hay un token de calificación pendiente → vuelve al flujo de calificación
     *   2. Si es Administrador → dashboard del taller
     *   3. Si es Conductor → dashboard del conductor
     */
    private function redirigirSegunContexto($uid, $email, $nombreCompleto, $rol)
    {
        // 1. ¿Venía del flujo de calificación (correo "Calificar mi experiencia")?
        if (session()->has('calificacion_pendiente_token')) {
            $token = session()->pull('calificacion_pendiente_token');
            session()->forget('calificacion_pendiente_taller_id');

            return redirect()
                ->route('calificacion.form', ['token' => $token])
                ->with('success', '¡Bienvenido ' . $nombreCompleto . '! Ahora puedes calificar tu experiencia.');
        }

        // 2. Redirección normal según rol
        if ($rol === 'Administrador') {
            return redirect()
                ->route('taller.dashboard')
                ->with('success', '¡Bienvenido ' . $nombreCompleto . '!');
        }

        return redirect()
            ->route('dashboard')
            ->with('success', '¡Bienvenido ' . $nombreCompleto . '!');
    }

    /**
     * Login/Registro con Google.
     * Recibe el ID Token de Firebase desde el frontend, lo verifica,
     * y crea o autentica al usuario.
     */
    public function googleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
            'rol'      => 'nullable|string|in:conductor,taller',
        ]);

        try {
            Log::info('Intento de login con Google');

            // 1. Verificar el ID Token con Firebase Admin SDK
            $verifiedIdToken = $this->auth->verifyIdToken($request->input('id_token'));
            $uid   = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');
            $name  = $verifiedIdToken->claims()->get('name') ?? $email;
            $picture = $verifiedIdToken->claims()->get('picture');

            Log::info('Token verificado. UID: ' . $uid . ' Email: ' . $email);

            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener el correo desde Google.',
                ], 422);
            }

            // 2. ¿Existe ya en Firestore?
            if (!$this->firestoreDb) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error en el sistema. Intenta más tarde.',
                ], 500);
            }

            $userRef  = $this->firestoreDb->collection('usuarios')->document($uid);
            $userSnap = $userRef->snapshot();

            $rolSolicitado = $request->input('rol', 'conductor');
            $rolFinal      = $rolSolicitado === 'taller' ? 'Administrador' : 'Conductor';

            if (!$userSnap->exists()) {
                // === NUEVO USUARIO: crearlo ===
                Log::info('Usuario nuevo desde Google, creando en Firestore: ' . $uid);

                $userData = [
                    'uid'             => $uid,
                    'email'           => $email,
                    'nombre_completo' => $name,
                    'usuario'         => $this->generateUsername($name, $email),
                    'rol'             => $rolFinal,
                    'activo'          => true,
                    'auth_provider'   => 'google',
                    'foto_perfil'     => $picture,
                    'fecha_creacion'  => now()->toDateTimeString(),
                ];

                $userRef->set($userData);

                // Si es taller, crear documento en 'talleres'
                if ($rolSolicitado === 'taller') {
                    $this->crearTallerParaUsuario($uid, $email, $name, $userRef);
                }

                Log::info('✅ Usuario Google creado: ' . $uid);
            } else {
                // === USUARIO EXISTENTE ===
                $userData = $userSnap->data();

                if (isset($userData['activo']) && !$userData['activo']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tu cuenta está desactivada. Contacta al administrador.',
                    ], 403);
                }

                // Vincular Google si la cuenta venía de password
                $updates = [];
                if (!isset($userData['auth_provider']) || $userData['auth_provider'] !== 'google') {
                    $updates[] = ['path' => 'auth_provider', 'value' => 'google'];
                }
                if ($picture && (!isset($userData['foto_perfil']) || $userData['foto_perfil'] !== $picture)) {
                    $updates[] = ['path' => 'foto_perfil', 'value' => $picture];
                }
                if (!empty($updates)) {
                    $userRef->update($updates);
                    Log::info('Usuario existente vinculado a Google: ' . $uid);
                }

                $rolFinal = $userData['rol'] ?? 'Conductor';
            }

            // 3. Guardar sesión
            $userDoc = $userRef->snapshot();
            $userDataFresh = $userDoc->data();

            session([
                'firebase_user' => [
                    'uid'             => $uid,
                    'email'           => $userDataFresh['email'] ?? $email,
                    'nombre_completo' => $userDataFresh['nombre_completo'] ?? $name,
                    'rol'             => $userDataFresh['rol'] ?? $rolFinal,
                    'usuario'         => $userDataFresh['usuario'] ?? null,
                    'auth_provider'   => 'google',
                ],
            ]);

            Log::info('✅ Sesión Google iniciada para: ' . ($userDataFresh['nombre_completo'] ?? $name));

            // 4. Determinar URL de redirección
            $redirectUrl = $this->obtenerRedirectSegunContexto(
                $uid,
                $userDataFresh['email'] ?? $email,
                $userDataFresh['nombre_completo'] ?? $name,
                $userDataFresh['rol'] ?? $rolFinal
            );

            return response()->json([
                'success'      => true,
                'redirect_url' => $redirectUrl,
                'message'      => '¡Bienvenido ' . ($userDataFresh['nombre_completo'] ?? $name) . '!',
            ]);
        } catch (\Kreait\Firebase\Exception\Auth\InvalidIdToken $e) {
            Log::error('ID Token inválido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Token de Google inválido. Intenta de nuevo.',
            ], 401);
        } catch (\Kreait\Firebase\Exception\Auth\ExpiredIdToken $e) {
            Log::error('ID Token expirado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'El token de Google expiró. Intenta de nuevo.',
            ], 401);
        } catch (\Exception $e) {
            Log::error('Error en googleLogin: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar sesión con Google. Intenta de nuevo.',
            ], 500);
        }
    }

    /**
     * Crea el documento del taller cuando un usuario de Google se registra como taller.
     */
    private function crearTallerParaUsuario($uid, $email, $nombreTaller, $userRef)
    {
        try {
            // Obtener el último taller para generar el ID
            $talleresCollection = $this->firestoreDb->collection('talleres');
            $query = $talleresCollection->orderBy('fecha_creacion', 'desc')->limit(1)->documents();
            $nextNumber = 1;
            foreach ($query as $doc) {
                if ($doc->exists()) {
                    $docId = $doc->id();
                    if (preg_match('/taller_(\d+)/', $docId, $matches)) {
                        $nextNumber = intval($matches[1]) + 1;
                    }
                }
            }

            $tallerId = 'taller_' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $tallerData = [
                'nombre'         => $nombreTaller,
                'email'          => $email,
                'direccion'      => '',
                'telefono'       => '',
                'verificado'     => false,
                'fecha_creacion' => now()->toDateTimeString(),
                'usuario_id'     => $uid,
            ];

            $this->firestoreDb->collection('talleres')->document($tallerId)->set($tallerData);

            $userRef->update([
                ['path' => 'taller_id', 'value' => $tallerId],
            ]);

            Log::info('✅ Taller creado desde Google: ' . $tallerId);
        } catch (\Exception $e) {
            Log::error('Error al crear taller desde Google: ' . $e->getMessage());
        }
    }

    /**
     * Devuelve la URL de redirección según contexto (calificación > rol).
     * Versión reutilizable que retorna string en vez de RedirectResponse.
     */
    private function obtenerRedirectSegunContexto($uid, $email, $nombreCompleto, $rol)
    {
        if (session()->has('calificacion_pendiente_token')) {
            $token = session()->pull('calificacion_pendiente_token');
            session()->forget('calificacion_pendiente_taller_id');
            return route('calificacion.form', ['token' => $token]);
        }

        if ($rol === 'Administrador') {
            return route('taller.dashboard');
        }

        return route('dashboard');
    }

    public function showCuenta()
    {
        return view('conductor.cuenta');
    }

    // Vista temporal para el dashboard del taller
    public function tallerDashboard()
    {
        return view('taller.dashboard');
    }

    public function conductorDashboard()
    {
        $stats = (new \App\Http\Controllers\Conductor\Dashboard\ServicioController())->stats();
        return view('conductor.dashboard.resumen', compact('stats'));
    }
}

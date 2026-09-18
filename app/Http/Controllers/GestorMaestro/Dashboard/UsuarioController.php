<?php

namespace App\Http\Controllers\GestorMaestro\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\EmailExistsException;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Auth;

class UsuarioController extends Controller
{
    protected $auth;
    protected $firestoreDb;

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

            if (!$credentialsPath) {
                throw new \Exception('No se encontró el archivo de credenciales');
            }

            Log::info('Credenciales encontradas en: ' . $credentialsPath);

            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->auth = $factory->createAuth();
            Log::info('Firebase Auth creado correctamente');

            try {
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

                $jsonContent = file_get_contents($credentialsPath);
                $credentials = json_decode($jsonContent, true);

                $config = [
                    'keyFilePath' => $credentialsPath,
                    'projectId' => $credentials['project_id'] ?? 'mecxihub-db',
                ];

                $this->firestoreDb = new FirestoreClient($config);
                Log::info('Firestore creado correctamente en UsuarioController');

            } catch (\Exception $e) {
                Log::error('Error al crear Firestore: ' . $e->getMessage());
                $this->firestoreDb = null;
            }

        } catch (\Exception $e) {
            Log::error('Error inicializando Firebase en UsuarioController: ' . $e->getMessage());
            $this->auth = null;
            $this->firestoreDb = null;
        }
    } */

    /**
     * Mostrar la lista de usuarios con paginación
     */
    public function index(Request $request)
    {
        try {
            Log::info('=== INICIANDO INDEX DE USUARIOS ===');

            if (!session()->has('firebase_user')) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
            }

            if (!$this->firestoreDb) {
                Log::error('Firestore no disponible');
                return redirect()->route('gestor.cuenta')->with('error', 'Firestore no está disponible.');
            }

            $search = $request->input('search', '');
            $filterRol = $request->input('rol', '');
            $page = $request->input('page', 1);
            $perPage = 15;

            Log::info('Parámetros: search=' . $search . ', filterRol=' . $filterRol . ', page=' . $page);

            // Obtener todos los usuarios
            $usuariosSnapshot = $this->firestoreDb
                ->collection('usuarios')
                ->documents();

            $usuarios = [];
            foreach ($usuariosSnapshot as $doc) {
                if ($doc->exists()) {
                    $usuarioData = $doc->data();
                    $usuarioData['uid'] = $doc->id();
                    $usuarios[] = $usuarioData;
                }
            }

            // Aplicar filtros
            $usuarios = $this->applyFilters($usuarios, $search, $filterRol);

            // Ordenar por fecha de creación (más reciente primero)
            usort($usuarios, function ($a, $b) {
                $dateA = isset($a['fecha_creacion']) ? strtotime($a['fecha_creacion']) : 0;
                $dateB = isset($b['fecha_creacion']) ? strtotime($b['fecha_creacion']) : 0;
                return $dateB - $dateA;
            });

            // Calcular paginación
            $total = count($usuarios);
            $totalPages = ceil($total / $perPage);

            if ($page < 1) $page = 1;
            if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

            $offset = ($page - 1) * $perPage;
            $usuariosPaginados = array_slice($usuarios, $offset, $perPage);

            Log::info('Usuarios encontrados: ' . $total . ', página: ' . $page);

            return view('GestorMaestro.Usuario.index', compact(
                'usuariosPaginados',
                'search',
                'filterRol',
                'page',
                'totalPages',
                'total'
            ));
        } catch (\Exception $e) {
            Log::error('Error en index de UsuarioController: ' . $e->getMessage());
            return redirect()->route('gestor.cuenta')->with('error', 'Error al cargar los usuarios.');
        }
    }

    /**
     * Aplicar filtros a los usuarios
     */
    private function applyFilters($usuarios, $search, $filterRol)
    {
        return array_filter($usuarios, function ($usuario) use ($search, $filterRol) {
            // Filtro de rol
            if (!empty($filterRol)) {
                $userRol = $usuario['rol'] ?? '';
                if ($userRol !== $filterRol) {
                    return false;
                }
            }

            // Búsqueda por texto
            if (!empty($search)) {
                $searchLower = strtolower($search);
                $match = false;

                if (isset($usuario['nombre_completo']) && stripos($usuario['nombre_completo'], $search) !== false) {
                    $match = true;
                }
                if (isset($usuario['email']) && stripos($usuario['email'], $search) !== false) {
                    $match = true;
                }
                if (isset($usuario['usuario']) && stripos($usuario['usuario'], $search) !== false) {
                    $match = true;
                }
                if (isset($usuario['uid']) && stripos($usuario['uid'], $search) !== false) {
                    $match = true;
                }

                if (!$match) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Mostrar el formulario para crear un nuevo usuario
     */
    public function create()
    {
        try {
            if (!session()->has('firebase_user')) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
            }

            return view('GestorMaestro.Usuario.create');
        } catch (\Exception $e) {
            Log::error('Error en create de UsuarioController: ' . $e->getMessage());
            return redirect()->route('gestor.usuarios')->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Almacenar un nuevo usuario
     */
    public function store(Request $request)
    {
        try {
            if (!session()->has('firebase_user')) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
            }

            // Validar datos
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|min:6|confirmed',
                'rol' => 'required|in:GestorMaestro,Conductor,Administrador',
                'activo' => 'boolean',
            ]);

            Log::info('Intentando crear usuario: ' . $validated['email']);

            // 1. Crear usuario en Firebase Authentication
            $userProperties = [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'displayName' => $validated['name'],
                'disabled' => false,
            ];

            $createdUser = $this->auth->createUser($userProperties);
            Log::info('Usuario creado en Auth con UID: ' . $createdUser->uid);

            // 2. Guardar datos en Firestore
            if ($this->firestoreDb) {
                try {
                    $usuarioData = [
                        'uid' => $createdUser->uid,
                        'email' => $validated['email'],
                        'nombre_completo' => $validated['name'],
                        'usuario' => $this->generateUsername($validated['name'], $validated['email']),
                        'rol' => $validated['rol'],
                        'activo' => isset($validated['activo']) ? (bool)$validated['activo'] : true,
                        'fecha_creacion' => now()->toDateTimeString(),
                        'fecha_registro' => now()->toDateTimeString(),
                        'creado_por' => session('firebase_user.uid'),
                    ];

                    $this->firestoreDb
                        ->collection('usuarios')
                        ->document($createdUser->uid)
                        ->set($usuarioData);

                    Log::info('✅ Usuario guardado en Firestore con ID: ' . $createdUser->uid);

                    return redirect()
                        ->route('gestor.usuarios')
                        ->with('success', 'Usuario ' . $validated['name'] . ' creado exitosamente con rol ' . $validated['rol']);
                } catch (\Exception $e) {
                    Log::error('❌ Error al guardar en Firestore: ' . $e->getMessage());

                    try {
                        $this->auth->deleteUser($createdUser->uid);
                        Log::info('Usuario eliminado de Auth por fallo en Firestore');
                    } catch (\Exception $e2) {
                        Log::error('Error al eliminar usuario de Auth: ' . $e2->getMessage());
                    }

                    return back()
                        ->withInput()
                        ->with('error', 'Error al guardar el usuario en la base de datos: ' . $e->getMessage());
                }
            } else {
                Log::error('Firestore no disponible');
                return back()
                    ->withInput()
                    ->with('error', 'Firestore no está disponible.');
            }
        } catch (EmailExistsException $e) {
            Log::error('Email ya registrado: ' . $request->email);
            return back()
                ->withInput()
                ->withErrors(['email' => 'Este correo electrónico ya está registrado.']);
        } catch (\Exception $e) {
            Log::error('Error en store de UsuarioController: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar el estado activo/inactivo de un usuario
     */
    public function toggleActivo($uid)
    {
        try {
            if (!session()->has('firebase_user')) {
                return response()->json(['error' => 'No autorizado'], 401);
            }

            if (!$this->firestoreDb) {
                return response()->json(['error' => 'Firestore no disponible'], 500);
            }

            $userRef = $this->firestoreDb->collection('usuarios')->document($uid);
            $userDoc = $userRef->snapshot();

            if (!$userDoc->exists()) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            $userData = $userDoc->data();
            $nuevoEstado = !(isset($userData['activo']) && $userData['activo'] === true);

            // Actualizar en Firestore
            $userRef->set([
                'activo' => $nuevoEstado,
                'fecha_actualizacion' => now()->toDateTimeString()
            ], ['merge' => true]);

            // Actualizar también en Authentication
            try {
                if ($nuevoEstado) {
                    $this->auth->enableUser($uid);
                } else {
                    $this->auth->disableUser($uid);
                }
                Log::info('Usuario ' . $uid . ' ' . ($nuevoEstado ? 'habilitado' : 'deshabilitado') . ' en Authentication');
            } catch (\Exception $e) {
                Log::error('Error al actualizar estado en Auth: ' . $e->getMessage());
            }

            Log::info('Usuario ' . $uid . ' activo actualizado a: ' . ($nuevoEstado ? 'true' : 'false'));

            return response()->json([
                'success' => true,
                'activo' => $nuevoEstado,
                'message' => $nuevoEstado ? 'Usuario activado exitosamente' : 'Usuario desactivado'
            ]);
        } catch (\Exception $e) {
            Log::error('Error en toggleActivo: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar el estado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generar nombre de usuario único
     */
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
}

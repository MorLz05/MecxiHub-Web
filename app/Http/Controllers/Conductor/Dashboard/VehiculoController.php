<?php

namespace App\Http\Controllers\Conductor\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Auth;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Factory;

class VehiculoController extends Controller
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
                base_path('storage/app/firebase-credentials.json'),
            ];

            $credentialsPath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $credentialsPath = realpath($path); // Normalizar ruta
                    break;
                }
            }

            if (!$credentialsPath) {
                Log::error('No se encontró el archivo de credenciales');
                $this->firestoreDb = null;
                $this->auth = null;
                return;
            }

            Log::info('Credenciales encontradas en VehiculoController: ' . $credentialsPath);

            $jsonContent = file_get_contents($credentialsPath);
            $credentials = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('El archivo de credenciales no es un JSON válido');
                $this->firestoreDb = null;
                $this->auth = null;
                return;
            }

            // Inicializar Firebase Auth
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->auth = $factory->createAuth();
            Log::info('Firebase Auth inicializado correctamente en VehiculoController');

            // ---- Inicializar Firestore con fallbacks (igual que en PasswordResetController) ----
            $this->firestoreDb = null;

            // Método 1: Usando configuración directa con keyFilePath
            try {
                $config = [
                    'keyFilePath' => $credentialsPath,
                    'projectId' => $credentials['project_id'],
                ];
                $this->firestoreDb = new FirestoreClient($config);
                Log::info('Firestore inicializado con configuración directa en VehiculoController');
            } catch (\Exception $e1) {
                Log::warning('Error con configuración directa: ' . $e1->getMessage());

                // Método 2: Usando variable de entorno GOOGLE_APPLICATION_CREDENTIALS
                try {
                    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
                    $this->firestoreDb = new FirestoreClient([
                        'projectId' => $credentials['project_id']
                    ]);
                    Log::info('Firestore inicializado usando variable de entorno en VehiculoController');
                } catch (\Exception $e2) {
                    Log::warning('Error con variable de entorno: ' . $e2->getMessage());

                    // Método 3: Usando el factory de Firebase (Kreait)
                    try {
                        $this->firestoreDb = $factory->createFirestore()->database();
                        Log::info('Firestore inicializado usando factory en VehiculoController');
                    } catch (\Exception $e3) {
                        Log::error('No se pudo inicializar Firestore: ' . $e3->getMessage());
                        $this->firestoreDb = null;
                    }
                }
            }

            // ⚠️ NO HACER NINGUNA CONSULTA DE VERIFICACIÓN (evita timeouts)

        } catch (\Exception $e) {
            Log::error('Error inicializando VehiculoController: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->firestoreDb = null;
            $this->auth = null;
        }
    } */

    public function index()
    {
        $user = session('firebase_user');
        $vehiculos = [];

        if (!$this->firestoreDb) {
            Log::error('Firestore no disponible en VehiculoController::index');
            return view('conductor.dashboard.vehiculos', compact('vehiculos'))
                ->with('error', 'Error de conexión con la base de datos.');
        }

        try {
            // Consulta simple sin orderBy (rápida)
            $vehiculosCollection = $this->firestoreDb
                ->collection('vehiculos')
                ->where('usuario_id', '=', $user['uid'])
                ->documents();

            foreach ($vehiculosCollection as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $data['id'] = $doc->id();
                    $vehiculos[] = $data;
                }
            }

            // Ordenar en PHP: activos primero, luego por fecha_registro descendente
            usort($vehiculos, function ($a, $b) {
                if ($a['activo'] != $b['activo']) {
                    return $a['activo'] ? -1 : 1;
                }
                $fechaA = $a['fecha_registro'] ?? '';
                $fechaB = $b['fecha_registro'] ?? '';
                return strcmp($fechaB, $fechaA);
            });
        } catch (\Exception $e) {
            Log::error('Error obteniendo vehículos: ' . $e->getMessage());
            return view('conductor.dashboard.vehiculos', compact('vehiculos'))
                ->with('error', 'Error al cargar los vehículos.');
        }

        return view('conductor.dashboard.vehiculos', compact('vehiculos'));
    }

    public function create()
    {
        return view('conductor.dashboard.vehiculos-crear');
    }

    public function store(Request $request)
    {
        $user = session('firebase_user');

        $validated = $request->validate([
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'año' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'placas' => 'required|string|max:20',
            'color' => 'required|string|max:30',
            'vin' => 'nullable|string|max:17',
            'kilometraje_actual' => 'nullable|integer|min:0',
            'activo' => 'sometimes|boolean',
        ]);

        if (!$this->firestoreDb) {
            return back()->withErrors(['error' => 'Error de conexión con la base de datos.']);
        }

        try {
            $vehiculoData = [
                'usuario_id' => $user['uid'],
                'marca' => $validated['marca'],
                'modelo' => $validated['modelo'],
                'año' => (int)$validated['año'],
                'placas' => strtoupper($validated['placas']),
                'color' => $validated['color'],
                'vin' => $validated['vin'] ?? '',
                'kilometraje_actual' => (int)($validated['kilometraje_actual'] ?? 0),
                'activo' => $request->has('activo'),
                'fecha_registro' => now()->toDateTimeString(),
                'ultimo_servicio' => null,
            ];

            $docRef = $this->firestoreDb->collection('vehiculos')->newDocument();
            $docRef->set($vehiculoData);

            Log::info('Vehículo creado: ' . $docRef->id() . ' para usuario: ' . $user['uid']);

            return redirect()
                ->route('cuenta.vehiculos')
                ->with('success', 'Vehículo agregado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error creando vehículo: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al guardar el vehículo: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $user = session('firebase_user');

        if (!$this->firestoreDb) {
            return redirect()->route('cuenta.vehiculos')->with('error', 'Error de conexión.');
        }

        try {
            $docRef = $this->firestoreDb->collection('vehiculos')->document($id);
            $snapshot = $docRef->snapshot();

            if (!$snapshot->exists()) {
                return redirect()->route('cuenta.vehiculos')->with('error', 'Vehículo no encontrado.');
            }

            $vehiculo = $snapshot->data();
            $vehiculo['id'] = $id;

            if ($vehiculo['usuario_id'] !== $user['uid']) {
                return redirect()->route('cuenta.vehiculos')->with('error', 'No tienes permiso para ver este vehículo.');
            }

            // Obtener servicios SIN orderBy
            $servicios = [];
            try {
                $serviciosCollection = $this->firestoreDb
                    ->collection('servicios')
                    ->where('vehiculo_id', '=', $id)
                    ->documents();

                foreach ($serviciosCollection as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        $data['id'] = $doc->id();
                        $servicios[] = $data;
                    }
                }

                // Ordenar en PHP y tomar los 5 más recientes
                usort($servicios, function ($a, $b) {
                    $fechaA = $a['fecha_servicio'] ?? '';
                    $fechaB = $b['fecha_servicio'] ?? '';
                    return strcmp($fechaB, $fechaA);
                });
                $servicios = array_slice($servicios, 0, 5);
            } catch (\Exception $e) {
                Log::error('Error obteniendo servicios del vehículo: ' . $e->getMessage());
            }

            return view('conductor.dashboard.vehiculo-detalle', compact('vehiculo', 'servicios'));
        } catch (\Exception $e) {
            Log::error('Error obteniendo vehículo: ' . $e->getMessage());
            return redirect()->route('cuenta.vehiculos')->with('error', 'Error al cargar el vehículo.');
        }
    }

    public function edit($id)
    {
        $user = session('firebase_user');

        if (!$this->firestoreDb) {
            return redirect()->route('cuenta.vehiculos')->with('error', 'Error de conexión.');
        }

        try {
            $docRef = $this->firestoreDb->collection('vehiculos')->document($id);
            $snapshot = $docRef->snapshot();

            if (!$snapshot->exists()) {
                return redirect()->route('cuenta.vehiculos')->with('error', 'Vehículo no encontrado.');
            }

            $vehiculo = $snapshot->data();
            $vehiculo['id'] = $id;

            if ($vehiculo['usuario_id'] !== $user['uid']) {
                return redirect()->route('cuenta.vehiculos')->with('error', 'No tienes permiso para editar este vehículo.');
            }

            return view('conductor.dashboard.vehiculos-editar', compact('vehiculo'));
        } catch (\Exception $e) {
            Log::error('Error obteniendo vehículo para editar: ' . $e->getMessage());
            return redirect()->route('cuenta.vehiculos')->with('error', 'Error al cargar el vehículo.');
        }
    }

    public function update(Request $request, $id)
    {
        $user = session('firebase_user');

        $validated = $request->validate([
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'año' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'placas' => 'required|string|max:20',
            'color' => 'required|string|max:30',
            'vin' => 'nullable|string|max:17',
            'kilometraje_actual' => 'nullable|integer|min:0',
            'activo' => 'sometimes|boolean',
        ]);

        if (!$this->firestoreDb) {
            return back()->withErrors(['error' => 'Error de conexión con la base de datos.']);
        }

        try {
            $docRef = $this->firestoreDb->collection('vehiculos')->document($id);
            $snapshot = $docRef->snapshot();

            if (!$snapshot->exists()) {
                return back()->withErrors(['error' => 'Vehículo no encontrado.']);
            }

            $vehiculo = $snapshot->data();

            if ($vehiculo['usuario_id'] !== $user['uid']) {
                return back()->withErrors(['error' => 'No tienes permiso para editar este vehículo.']);
            }

            $updates = [
                ['path' => 'marca', 'value' => $validated['marca']],
                ['path' => 'modelo', 'value' => $validated['modelo']],
                ['path' => 'año', 'value' => (int)$validated['año']],
                ['path' => 'placas', 'value' => strtoupper($validated['placas'])],
                ['path' => 'color', 'value' => $validated['color']],
                ['path' => 'vin', 'value' => $validated['vin'] ?? ''],
                ['path' => 'kilometraje_actual', 'value' => (int)($validated['kilometraje_actual'] ?? 0)],
                ['path' => 'activo', 'value' => $request->has('activo')],
            ];

            $docRef->update($updates);

            Log::info('Vehículo actualizado: ' . $id);

            return redirect()
                ->route('cuenta.vehiculos')
                ->with('success', 'Vehículo actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error actualizando vehículo: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar el vehículo: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $user = session('firebase_user');

        if (!$this->firestoreDb) {
            return back()->withErrors(['error' => 'Error de conexión.']);
        }

        try {
            $docRef = $this->firestoreDb->collection('vehiculos')->document($id);
            $snapshot = $docRef->snapshot();

            if (!$snapshot->exists()) {
                return back()->withErrors(['error' => 'Vehículo no encontrado.']);
            }

            $vehiculo = $snapshot->data();

            if ($vehiculo['usuario_id'] !== $user['uid']) {
                return back()->withErrors(['error' => 'No tienes permiso para eliminar este vehículo.']);
            }

            $docRef->update([['path' => 'activo', 'value' => false]]);

            Log::info('Vehículo desactivado: ' . $id);

            return redirect()
                ->route('cuenta.vehiculos')
                ->with('success', 'Vehículo eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error eliminando vehículo: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el vehículo.']);
        }
    }

    public function servicios($vehiculoId)
    {
        $user = session('firebase_user');

        if (!$this->firestoreDb) {
            return redirect()->route('cuenta.vehiculos')->with('error', 'Error de conexión.');
        }

        try {
            $vehiculoRef = $this->firestoreDb->collection('vehiculos')->document($vehiculoId);
            $vehiculoSnapshot = $vehiculoRef->snapshot();

            if (!$vehiculoSnapshot->exists()) {
                return redirect()->route('cuenta.vehiculos')->with('error', 'Vehículo no encontrado.');
            }

            $vehiculo = $vehiculoSnapshot->data();
            $vehiculo['id'] = $vehiculoId;

            if ($vehiculo['usuario_id'] !== $user['uid']) {
                return redirect()->route('cuenta.vehiculos')->with('error', 'No tienes permiso.');
            }

            $servicios = [];
            try {
                $serviciosCollection = $this->firestoreDb
                    ->collection('servicios')
                    ->where('vehiculo_id', '=', $vehiculoId)
                    ->documents();

                foreach ($serviciosCollection as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        $data['id'] = $doc->id();
                        $servicios[] = $data;
                    }
                }

                usort($servicios, function ($a, $b) {
                    $fechaA = $a['fecha_servicio'] ?? '';
                    $fechaB = $b['fecha_servicio'] ?? '';
                    return strcmp($fechaB, $fechaA);
                });
            } catch (\Exception $e) {
                Log::error('Error obteniendo servicios: ' . $e->getMessage());
            }

            return view('conductor.dashboard.vehiculo-servicios', compact('vehiculo', 'servicios'));
        } catch (\Exception $e) {
            Log::error('Error obteniendo servicios: ' . $e->getMessage());
            return redirect()->route('cuenta.vehiculos')->with('error', 'Error al cargar los servicios.');
        }
    }
}

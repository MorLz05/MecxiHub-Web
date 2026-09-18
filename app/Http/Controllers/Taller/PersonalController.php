<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class PersonalController extends Controller
{
    protected $firestoreDb;

    public function __construct(FirestoreClient $firestoreDb)
    {
        $this->firestoreDb = $firestoreDb;
    }

    /*  public function __construct()
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
                    $config = [
                        'keyFilePath' => $credentialsPath,
                        'projectId' => $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID'),
                    ];
                    $this->firestoreDb = new FirestoreClient($config);
                    Log::info('Firestore inicializado en PersonalController');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error inicializando PersonalController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }
    } */

    private function obtenerTallerIdDelUsuario()
    {
        $user = session('firebase_user');
        if (!$user || !$this->firestoreDb) return null;

        try {
            $doc = $this->firestoreDb->collection('usuarios')->document($user['uid'])->snapshot();
            if ($doc->exists()) {
                return $doc->data()['taller_id'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo taller_id: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Listado de personal del taller
     */
    public function index(Request $request)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();

        if (!$tallerId) {
            return view('taller.personal.index', [
                'personal' => [],
                'search' => '',
                'filterEstado' => '',
                'total' => 0,
                'sinTaller' => true,
            ]);
        }

        $search = $request->input('search', '');
        $filterEstado = $request->input('estado', '');

        $personal = [];

        try {
            $snapshot = $this->firestoreDb
                ->collection('talleres')
                ->document($tallerId)
                ->collection('personal')
                ->documents();

            foreach ($snapshot as $doc) {
                if (!$doc->exists()) continue;
                $data = $doc->data();
                $data['id'] = $doc->id();
                $personal[] = $data;
            }
        } catch (\Exception $e) {
            Log::warning('No se pudo cargar personal: ' . $e->getMessage());
        }

        // Filtros
        $personal = array_filter($personal, function ($p) use ($search, $filterEstado) {
            if ($filterEstado !== '') {
                $activo = isset($p['activo']) ? (bool) $p['activo'] : true;
                if ($filterEstado === 'activo' && !$activo) return false;
                if ($filterEstado === 'inactivo' && $activo) return false;
            }

            if (!empty($search)) {
                $match = false;
                $campos = ['nombre', 'especialidad', 'puesto', 'email', 'telefono'];
                foreach ($campos as $c) {
                    if (isset($p[$c]) && stripos((string) $p[$c], $search) !== false) {
                        $match = true;
                        break;
                    }
                }
                if (!$match) return false;
            }

            return true;
        });

        // Ordenar por nombre
        usort($personal, function ($a, $b) {
            return strcasecmp($a['nombre'] ?? '', $b['nombre'] ?? '');
        });

        return view('taller.personal.index', [
            'personal' => array_values($personal),
            'search' => $search,
            'filterEstado' => $filterEstado,
            'total' => count($personal),
            'sinTaller' => false,
        ]);
    }

    /**
     * Guardar nuevo miembro del personal
     */
    public function store(Request $request)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->firestoreDb) {
            return back()->withInput()->with('error', 'Acceso no autorizado.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'especialidad' => 'nullable|string|max:150',
            'puesto' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'notas' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
        ]);

        try {
            $data = [
                'nombre' => trim($validated['nombre']),
                'especialidad' => trim($validated['especialidad'] ?? ''),
                'puesto' => trim($validated['puesto'] ?? ''),
                'telefono' => trim($validated['telefono'] ?? ''),
                'email' => trim($validated['email'] ?? ''),
                'notas' => trim($validated['notas'] ?? ''),
                'activo' => true,
                'fecha_registro' => now()->toDateTimeString(),
                'registrado_por' => session('firebase_user.uid'),
            ];

            $personalId = 'persona_' . uniqid();

            $data['id'] = $personalId;

            $this->firestoreDb
                ->collection('talleres')
                ->document($tallerId)
                ->collection('personal')
                ->document($personalId)
                ->set($data);

            return back()->with('success', 'Miembro del personal agregado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error agregando personal: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al agregar: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar miembro
     */
    public function update(Request $request, $id)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->firestoreDb) {
            return back()->withInput()->with('error', 'Acceso no autorizado.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'especialidad' => 'nullable|string|max:150',
            'puesto' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'notas' => 'nullable|string|max:500',
        ]);

        try {
            $ref = $this->firestoreDb
                ->collection('talleres')
                ->document($tallerId)
                ->collection('personal')
                ->document($id);

            if (!$ref->snapshot()->exists()) {
                return back()->with('error', 'Miembro no encontrado.');
            }

            $ref->set([
                'nombre' => trim($validated['nombre']),
                'especialidad' => trim($validated['especialidad'] ?? ''),
                'puesto' => trim($validated['puesto'] ?? ''),
                'telefono' => trim($validated['telefono'] ?? ''),
                'email' => trim($validated['email'] ?? ''),
                'notas' => trim($validated['notas'] ?? ''),
                'fecha_actualizacion' => now()->toDateTimeString(),
            ], ['merge' => true]);

            return back()->with('success', 'Miembro actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error actualizando personal: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar miembro
     */
    public function destroy($id)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->firestoreDb) {
            return back()->with('error', 'Acceso no autorizado.');
        }

        try {
            $ref = $this->firestoreDb
                ->collection('talleres')
                ->document($tallerId)
                ->collection('personal')
                ->document($id);

            if (!$ref->snapshot()->exists()) {
                return back()->with('error', 'Miembro no encontrado.');
            }

            $ref->delete();

            return back()->with('success', 'Miembro eliminado.');
        } catch (\Exception $e) {
            Log::error('Error eliminando personal: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar.');
        }
    }

    /**
     * Toggle activo/inactivo
     */
    public function toggleActivo($id)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->firestoreDb) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        try {
            $ref = $this->firestoreDb
                ->collection('talleres')
                ->document($tallerId)
                ->collection('personal')
                ->document($id);

            $doc = $ref->snapshot();
            if (!$doc->exists()) {
                return response()->json(['error' => 'Miembro no encontrado'], 404);
            }

            $data = $doc->data();
            $nuevoEstado = !(isset($data['activo']) && $data['activo'] === true);

            $ref->set([
                'activo' => $nuevoEstado,
                'fecha_actualizacion' => now()->toDateTimeString(),
            ], ['merge' => true]);

            return response()->json([
                'success' => true,
                'activo' => $nuevoEstado,
                'message' => $nuevoEstado ? 'Miembro activado' : 'Miembro desactivado',
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggle activo personal: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar'], 500);
        }
    }

    /**
     * Obtener lista de personal activo del taller
     * (helper reutilizable)
     */
    public static function obtenerPersonalActivoDelTaller($firestoreDb, $tallerId)
    {
        $personal = [];
        if (!$firestoreDb || !$tallerId) return $personal;

        try {
            $snapshot = $firestoreDb
                ->collection('talleres')
                ->document($tallerId)
                ->collection('personal')
                ->documents();

            foreach ($snapshot as $doc) {
                if (!$doc->exists()) continue;
                $data = $doc->data();
                $activo = isset($data['activo']) ? (bool) $data['activo'] : true;
                if (!$activo) continue;

                $personal[] = [
                    'id' => $doc->id(),
                    'nombre' => $data['nombre'] ?? 'Sin nombre',
                    'puesto' => $data['puesto'] ?? '',
                    'especialidad' => $data['especialidad'] ?? '',
                ];
            }
        } catch (\Exception $e) {
            Log::warning('No se pudo cargar personal activo: ' . $e->getMessage());
        }

        // Ordenar por nombre
        usort($personal, fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));

        return $personal;
    }
}

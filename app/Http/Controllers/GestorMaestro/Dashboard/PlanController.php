<?php

namespace App\Http\Controllers\GestorMaestro\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    protected $firestoreDb;

    public function __construct(FirestoreClient $firestoreDb)
    {
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

            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

            $jsonContent = file_get_contents($credentialsPath);
            $credentials = json_decode($jsonContent, true);

            $config = [
                'keyFilePath' => $credentialsPath,
                'projectId' => $credentials['project_id'] ?? 'mecxihub-db',
            ];

            $this->firestoreDb = new FirestoreClient($config);
            Log::info('Firestore creado correctamente en PlanController');
        } catch (\Exception $e) {
            Log::error('Error inicializando Firestore en PlanController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }
    } */

    /**
     * Listar planes con paginación y filtros
     */
    public function index(Request $request)
    {
        try {
            if (!session()->has('firebase_user')) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
            }

            if (!$this->firestoreDb) {
                return redirect()->route('gestor.cuenta')->with('error', 'Firestore no está disponible.');
            }

            $search = $request->input('search', '');
            $filterEstado = $request->input('estado', '');
            $filterTipo = $request->input('tipo_usuario', '');
            $page = (int) $request->input('page', 1);
            $perPage = 15;

            $planes = [];

            try {
                // Si la colección no existe, Firestore devuelve un snapshot vacío (no lanza error)
                $snapshot = $this->firestoreDb->collection('planes')->documents();

                foreach ($snapshot as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        $data['id'] = $doc->id();
                        $planes[] = $data;
                    }
                }
            } catch (\Exception $e) {
                // Si por alguna razón la colección no existe o hay error, tratamos como vacío
                Log::warning('Colección planes vacía o inaccesible: ' . $e->getMessage());
                $planes = [];
            }

            // Aplicar filtros
            $planes = $this->applyFilters($planes, $search, $filterEstado, $filterTipo);

            // Ordenar: por orden ascendente si existe, si no por fecha de creación desc
            usort($planes, function ($a, $b) {
                $ordenA = $a['orden'] ?? 999;
                $ordenB = $b['orden'] ?? 999;
                if ($ordenA === $ordenB) {
                    $dateA = isset($a['fecha_creacion']) ? strtotime($a['fecha_creacion']) : 0;
                    $dateB = isset($b['fecha_creacion']) ? strtotime($b['fecha_creacion']) : 0;
                    return $dateB - $dateA;
                }
                return $ordenA - $ordenB;
            });

            // Paginación
            $total = count($planes);
            $totalPages = (int) ceil($total / $perPage);
            if ($totalPages < 1) $totalPages = 1;

            if ($page < 1) $page = 1;
            if ($page > $totalPages) $page = $totalPages;

            $offset = ($page - 1) * $perPage;
            $planesPaginados = array_slice($planes, $offset, $perPage);

            return view('GestorMaestro.Plan.index', compact(
                'planesPaginados',
                'search',
                'filterEstado',
                'filterTipo',
                'page',
                'totalPages',
                'total'
            ));
        } catch (\Exception $e) {
            Log::error('Error en index de PlanController: ' . $e->getMessage());
            return redirect()->route('gestor.cuenta')->with('error', 'Error al cargar los planes.');
        }
    }

    /**
     * Filtros de búsqueda
     */
    private function applyFilters($planes, $search, $filterEstado, $filterTipo = '')
    {
        return array_filter($planes, function ($plan) use ($search, $filterEstado, $filterTipo) {

            // Filtro por estado
            if ($filterEstado !== '') {
                $activo = isset($plan['activo']) ? (bool) $plan['activo'] : true;
                if ($filterEstado === 'activo' && !$activo) return false;
                if ($filterEstado === 'inactivo' && $activo) return false;
            }

            //  Filtro por tipo de usuario
            if ($filterTipo !== '') {
                $tipos = $plan['tipos_usuario'] ?? [];
                if (!is_array($tipos) || !in_array($filterTipo, $tipos, true)) {
                    return false;
                }
            }

            // Búsqueda por texto
            if (!empty($search)) {
                $match = false;
                $campos = ['nombre', 'descripcion', 'id'];

                foreach ($campos as $campo) {
                    if (isset($plan[$campo]) && stripos((string) $plan[$campo], $search) !== false) {
                        $match = true;
                        break;
                    }
                }

                if (!$match && isset($plan['caracteristicas']) && is_array($plan['caracteristicas'])) {
                    foreach ($plan['caracteristicas'] as $car) {
                        if (stripos((string) $car, $search) !== false) {
                            $match = true;
                            break;
                        }
                    }
                }

                // Buscar también en tipos de usuario
                if (!$match && isset($plan['tipos_usuario']) && is_array($plan['tipos_usuario'])) {
                    foreach ($plan['tipos_usuario'] as $tipo) {
                        if (stripos((string) $tipo, $search) !== false) {
                            $match = true;
                            break;
                        }
                    }
                }

                if (!$match) return false;
            }

            return true;
        });
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        if (!session()->has('firebase_user')) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
        }

        return view('GestorMaestro.Plan.create');
    }

    /**
     * Guardar nuevo plan
     */
    public function store(Request $request)
    {
        try {
            if (!session()->has('firebase_user')) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
            }

            if (!$this->firestoreDb) {
                return back()->withInput()->with('error', 'Firestore no está disponible.');
            }

            $validated = $request->validate([
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string|max:500',
                'precio' => 'required|numeric|min:0',
                'moneda' => 'required|string|max:10',
                'duracion_dias' => 'required|integer|min:1',
                'caracteristicas' => 'nullable|array',
                'caracteristicas.*' => 'nullable|string|max:200',
                'tipos_usuario' => 'required|array|min:1',
                'tipos_usuario.*' => 'in:Conductor,Administrador,Taller,GestorMaestro',
                'activo' => 'nullable|boolean',
                'destacado' => 'nullable|boolean',
                'orden' => 'nullable|integer|min:0',
            ]);

            // Limpiar características vacías
            $caracteristicas = array_values(array_filter(
                $validated['caracteristicas'] ?? [],
                fn($c) => trim((string) $c) !== ''
            ));

            // Generar ID legible (slug) único
            $planId = Str::slug($validated['nombre']);
            $planId = $this->ensureUniqueId($planId);

            $planData = [
                'id' => $planId,
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? '',
                'precio' => (float) $validated['precio'],
                'moneda' => strtoupper($validated['moneda']),
                'duracion_dias' => (int) $validated['duracion_dias'],
                'caracteristicas' => $caracteristicas,
                'tipos_usuario' => array_values($validated['tipos_usuario']),
                'activo' => isset($validated['activo']) ? (bool) $validated['activo'] : true,
                'destacado' => isset($validated['destacado']) ? (bool) $validated['destacado'] : false,
                'orden' => isset($validated['orden']) ? (int) $validated['orden'] : 0,
                'fecha_creacion' => now()->toDateTimeString(),
                'creado_por' => session('firebase_user.uid'),
            ];

            // Esto crea la colección automáticamente si no existe
            $this->firestoreDb
                ->collection('planes')
                ->document($planId)
                ->set($planData);

            Log::info('Plan creado: ' . $planId);

            return redirect()
                ->route('gestor.planes')
                ->with('success', 'Plan "' . $validated['nombre'] . '" creado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error en store de PlanController: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al crear el plan: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!session()->has('firebase_user')) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
        }

        if (!$this->firestoreDb) {
            return redirect()->route('gestor.planes')->with('error', 'Firestore no está disponible.');
        }

        $doc = $this->firestoreDb->collection('planes')->document($id)->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('gestor.planes')->with('error', 'Plan no encontrado.');
        }

        $plan = $doc->data();
        $plan['id'] = $doc->id();

        return view('GestorMaestro.Plan.edit', compact('plan'));
    }

    /**
     * Actualizar plan
     */
    public function update(Request $request, $id)
    {
        try {
            if (!session()->has('firebase_user')) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
            }

            if (!$this->firestoreDb) {
                return back()->with('error', 'Firestore no está disponible.');
            }

            $validated = $request->validate([
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string|max:500',
                'precio' => 'required|numeric|min:0',
                'moneda' => 'required|string|max:10',
                'duracion_dias' => 'required|integer|min:1',
                'caracteristicas' => 'nullable|array',
                'caracteristicas.*' => 'nullable|string|max:200',
                'tipos_usuario' => 'required|array|min:1',
                'tipos_usuario.*' => 'in:Conductor,Administrador,Taller,GestorMaestro',
                'activo' => 'nullable|boolean',
                'destacado' => 'nullable|boolean',
                'orden' => 'nullable|integer|min:0',
            ]);

            $caracteristicas = array_values(array_filter(
                $validated['caracteristicas'] ?? [],
                fn($c) => trim((string) $c) !== ''
            ));

            $updateData = [
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? '',
                'precio' => (float) $validated['precio'],
                'moneda' => strtoupper($validated['moneda']),
                'duracion_dias' => (int) $validated['duracion_dias'],
                'caracteristicas' => $caracteristicas,
                'tipos_usuario' => array_values($validated['tipos_usuario']),
                'activo' => isset($validated['activo']) ? (bool) $validated['activo'] : true,
                'destacado' => isset($validated['destacado']) ? (bool) $validated['destacado'] : false,
                'orden' => isset($validated['orden']) ? (int) $validated['orden'] : 0,
                'fecha_actualizacion' => now()->toDateTimeString(),
            ];

            $this->firestoreDb
                ->collection('planes')
                ->document($id)
                ->set($updateData, ['merge' => true]);

            return redirect()
                ->route('gestor.planes')
                ->with('success', 'Plan actualizado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error en update de PlanController: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar el plan: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar plan
     */
    public function destroy($id)
    {
        try {
            if (!session()->has('firebase_user')) {
                return response()->json(['error' => 'No autorizado'], 401);
            }

            if (!$this->firestoreDb) {
                return response()->json(['error' => 'Firestore no disponible'], 500);
            }

            $doc = $this->firestoreDb->collection('planes')->document($id)->snapshot();
            if (!$doc->exists()) {
                return response()->json(['error' => 'Plan no encontrado'], 404);
            }

            $this->firestoreDb->collection('planes')->document($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plan eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error en destroy de PlanController: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle activo desde la tabla
     */
    public function toggleActivo($id)
    {
        try {
            if (!session()->has('firebase_user')) {
                return response()->json(['error' => 'No autorizado'], 401);
            }

            if (!$this->firestoreDb) {
                return response()->json(['error' => 'Firestore no disponible'], 500);
            }

            $ref = $this->firestoreDb->collection('planes')->document($id);
            $doc = $ref->snapshot();

            if (!$doc->exists()) {
                return response()->json(['error' => 'Plan no encontrado'], 404);
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
                'message' => $nuevoEstado ? 'Plan activado' : 'Plan desactivado'
            ]);
        } catch (\Exception $e) {
            Log::error('Error en toggleActivo plan: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Asegura que el ID (slug) sea único
     */
    private function ensureUniqueId($baseId)
    {
        if (empty($baseId)) {
            $baseId = 'plan-' . time();
        }

        $id = $baseId;
        $counter = 1;

        while (true) {
            $doc = $this->firestoreDb->collection('planes')->document($id)->snapshot();
            if (!$doc->exists()) break;
            $id = $baseId . '-' . $counter;
            $counter++;
        }

        return $id;
    }
}

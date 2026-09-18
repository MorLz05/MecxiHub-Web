<?php

namespace App\Http\Controllers\GestorMaestro\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Factory;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;

class TallerController extends Controller
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
            // Buscar el archivo de credenciales
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

            // Leer el contenido del JSON
            $jsonContent = file_get_contents($credentialsPath);
            $credentials = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('El archivo de credenciales no es un JSON válido');
            }

            // Crear el factory para Auth
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->auth = $factory->createAuth();
            Log::info('Firebase Auth creado correctamente');

            // Configurar Firestore
            try {
                $config = [
                    'keyFilePath' => $credentialsPath,
                    'projectId' => $credentials['project_id'],
                ];

                $this->firestoreDb = new FirestoreClient($config);
                Log::info('Firestore creado correctamente en GestorTallerController con projectId: ' . $credentials['project_id']);
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
                }
            }
        } catch (\Exception $e) {
            Log::error('Error inicializando Firebase en GestorTallerController: ' . $e->getMessage());
            throw $e;
        }
    } */

    /**
     * Mostrar la lista de todos los talleres con paginación
     */
    public function index(Request $request)
    {
        try {
            // Verificar sesión
            if (!session()->has('firebase_user')) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
            }

            // Verificar que sea GestorMaestro
            $userRole = session('firebase_user.rol', '');
            if ($userRole !== 'GestorMaestro') {
                return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder.');
            }

            // Verificar si Firestore está disponible
            if (!$this->firestoreDb) {
                return redirect()->route('gestor.cuenta')->with('error', 'Firestore no está disponible.');
            }

            // Obtener parámetros de búsqueda y filtros
            $search = $request->input('search', '');
            $filterVerificado = $request->input('verificado', '');
            $page = $request->input('page', 1);
            $perPage = 15;

            // Obtener todos los talleres
            $talleresSnapshot = $this->firestoreDb
                ->collection('talleres')
                ->documents();

            $talleres = [];
            foreach ($talleresSnapshot as $doc) {
                if ($doc->exists()) {
                    $tallerData = $doc->data();
                    $tallerData['id'] = $doc->id();
                    $talleres[] = $tallerData;
                }
            }

            // Aplicar filtros
            $talleres = $this->applyFilters($talleres, $search, $filterVerificado);

            // Ordenar por fecha de creación (más reciente primero)
            usort($talleres, function ($a, $b) {
                $dateA = isset($a['fecha_creacion']) ? strtotime($a['fecha_creacion']) : 0;
                $dateB = isset($b['fecha_creacion']) ? strtotime($b['fecha_creacion']) : 0;
                return $dateB - $dateA;
            });

            // Calcular total para paginación
            $total = count($talleres);
            $totalPages = ceil($total / $perPage);

            // Asegurar que la página actual sea válida
            if ($page < 1) $page = 1;
            if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

            // Obtener el slice para la página actual
            $offset = ($page - 1) * $perPage;
            $talleresPaginados = array_slice($talleres, $offset, $perPage);

            Log::info('Talleres encontrados: ' . $total . ', página: ' . $page);

            return view('GestorMaestro.Taller.index', compact(
                'talleresPaginados',
                'search',
                'filterVerificado',
                'page',
                'totalPages',
                'total'
            ));
        } catch (\Exception $e) {
            Log::error('Error en index de GestorTallerController: ' . $e->getMessage());
            return redirect()->route('gestor.cuenta')->with('error', 'Error al cargar los talleres.');
        }
    }

    /**
     * Aplicar filtros a los talleres
     */
    private function applyFilters($talleres, $search, $filterVerificado)
    {
        return array_filter($talleres, function ($taller) use ($search, $filterVerificado) {
            // Filtro de verificado
            if ($filterVerificado !== '' && $filterVerificado !== null) {
                $isVerified = isset($taller['verificado']) ? (bool)$taller['verificado'] : false;
                $filterBool = $filterVerificado === 'true' ? true : false;
                if ($isVerified !== $filterBool) {
                    return false;
                }
            }

            // Búsqueda por texto
            if (!empty($search)) {
                $searchLower = strtolower($search);
                $match = false;

                // Buscar en nombre
                if (isset($taller['nombre']) && stripos($taller['nombre'], $search) !== false) {
                    $match = true;
                }
                // Buscar en dirección
                if (isset($taller['direccion']) && stripos($taller['direccion'], $search) !== false) {
                    $match = true;
                }
                // Buscar en email
                if (isset($taller['email']) && stripos($taller['email'], $search) !== false) {
                    $match = true;
                }
                // Buscar en especialidades
                if (isset($taller['especialidades']) && is_array($taller['especialidades'])) {
                    foreach ($taller['especialidades'] as $especialidad) {
                        if (stripos($especialidad, $search) !== false) {
                            $match = true;
                            break;
                        }
                    }
                }

                if (!$match) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Mostrar un taller específico
     */
    public function show($id)
    {
        try {
            // Verificar sesión
            if (!session()->has('firebase_user')) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
            }

            $userRole = session('firebase_user.rol', '');
            if ($userRole !== 'GestorMaestro') {
                return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder.');
            }

            if (!$this->firestoreDb) {
                return redirect()->route('gestor.talleres')->with('error', 'Firestore no está disponible.');
            }

            $tallerDoc = $this->firestoreDb
                ->collection('talleres')
                ->document($id)
                ->snapshot();

            if (!$tallerDoc->exists()) {
                return redirect()->route('gestor.talleres')->with('error', 'Taller no encontrado.');
            }

            $tallerData = $tallerDoc->data();
            $tallerData['id'] = $id;

            // Normalizar campos
            $tallerData['nombre'] = $tallerData['nombre'] ?? 'Sin nombre';
            $tallerData['direccion'] = $tallerData['direccion'] ?? '';
            $tallerData['telefono'] = $tallerData['telefono'] ?? '';
            $tallerData['email'] = $tallerData['email'] ?? '';
            $tallerData['descripcion'] = $tallerData['descripcion'] ?? '';
            $tallerData['verificado'] = $tallerData['verificado'] ?? false;
            $tallerData['logo_url'] = $tallerData['logo_url'] ?? null;
            $tallerData['latitud'] = $tallerData['latitud'] ?? null;
            $tallerData['longitud'] = $tallerData['longitud'] ?? null;
            $tallerData['especialidades'] = $tallerData['especialidades'] ?? [];
            $tallerData['horario'] = $tallerData['horario'] ?? [];

            // Cargar imágenes del establecimiento
            $imagenes = [];
            try {
                $imgSnapshot = $this->firestoreDb
                    ->collection('talleres')
                    ->document($id)
                    ->collection('imagenes')
                    ->documents();

                foreach ($imgSnapshot as $img) {
                    if ($img->exists()) {
                        $imgData = $img->data();
                        $imgData['id'] = $img->id();
                        $imagenes[] = $imgData;
                    }
                }

                usort($imagenes, fn($a, $b) =>
                strtotime($b['fecha_subida'] ?? '0') - strtotime($a['fecha_subida'] ?? '0'));
            } catch (\Exception $e) {
                Log::warning('Error cargando imágenes del taller: ' . $e->getMessage());
            }

            // Cargar reseñas para calcular el rating real
            $resenas = [];
            $promedio = 0;
            $totalResenas = 0;
            $distribucion = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
            $porcentajes = [];
            try {
                // 1. Subcolección: talleres/{id}/resenas
                $subSnapshot = $this->firestoreDb
                    ->collection('talleres')
                    ->document($id)
                    ->collection('resenas')
                    ->documents();

                foreach ($subSnapshot as $r) {
                    if ($r->exists()) {
                        $rData = $r->data();
                        $rData['id'] = $r->id();
                        $rData['_origen'] = 'subcoleccion';
                        $resenas[] = $rData;
                    }
                }

                // 2. Colección raíz: resenas (filtrando por taller_id)
                $rootSnapshot = $this->firestoreDb
                    ->collection('resenas')
                    ->where('taller_id', '=', $id)
                    ->documents();

                foreach ($rootSnapshot as $r) {
                    if ($r->exists()) {
                        $rData = $r->data();
                        $rData['id'] = $r->id();
                        $rData['_origen'] = 'coleccion_raiz';
                        $resenas[] = $rData;
                    }
                }

                // Ordenar por fecha descendente
                usort($resenas, function ($a, $b) {
                    $fechaA = $a['fecha_resena'] ?? $a['fecha'] ?? '0';
                    $fechaB = $b['fecha_resena'] ?? $b['fecha'] ?? '0';
                    return strtotime($fechaB) - strtotime($fechaA);
                });

                // Calcular promedio y distribución
                $totalResenas = count($resenas);
                $suma = 0;
                foreach ($resenas as $r) {
                    // Aceptar AMBOS nombres: 'rating' (subcolección) o 'calificacion' (raíz)
                    $c = (int) ($r['rating'] ?? $r['calificacion'] ?? 0);
                    if ($c >= 1 && $c <= 5) {
                        $suma += $c;
                        $distribucion[$c]++;
                    }
                }
                $promedio = $totalResenas > 0 ? round($suma / $totalResenas, 1) : 0;
                foreach ($distribucion as $est => $cant) {
                    $porcentajes[$est] = $totalResenas > 0 ? round(($cant / $totalResenas) * 100) : 0;
                }
            } catch (\Exception $e) {
                Log::warning('Error cargando reseñas del taller: ' . $e->getMessage());
            }

            // Datos derivados
            $tallerData['rating'] = $promedio;
            $tallerData['resenas_count'] = $totalResenas;
            $tallerData['distribucion'] = $distribucion;
            $tallerData['porcentajes'] = $porcentajes;

            // Especialidades: derivar de servicios_count
            $tallerData['especialidades'] = [];
            if (!empty($tallerData['servicios_count']) && is_array($tallerData['servicios_count'])) {
                foreach ($tallerData['servicios_count'] as $s) {
                    if (is_array($s) && !empty($s['nombre'])) {
                        $tallerData['especialidades'][] = $s['nombre'];
                    }
                }
                // Si no hay servicios, no hay especialidades
            }
            return view('GestorMaestro.Taller.show', compact('tallerData', 'imagenes', 'resenas'));
        } catch (\Exception $e) {
            Log::error('Error en show de GestorTallerController: ' . $e->getMessage());
            return redirect()->route('gestor.talleres')->with('error', 'Error al cargar el taller.');
        }
    }

    /**
     * Actualizar el estado de verificación de un taller
     */
    public function toggleVerificado(Request $request, $id)
    {
        try {
            // Verificar sesión
            if (!session()->has('firebase_user')) {
                return response()->json(['error' => 'No autorizado'], 401);
            }

            $userRole = session('firebase_user.rol', '');
            if ($userRole !== 'GestorMaestro') {
                return response()->json(['error' => 'No tienes permisos'], 403);
            }

            if (!$this->firestoreDb) {
                return response()->json(['error' => 'Firestore no disponible'], 500);
            }

            // Obtener el estado del request (si viene del body)
            $newVerificado = $request->input('verificado');

            // Si no viene en el body, usar el valor por defecto (toggle)
            if ($newVerificado === null) {
                // Obtener el estado actual
                $tallerDoc = $this->firestoreDb
                    ->collection('talleres')
                    ->document($id)
                    ->snapshot();

                if (!$tallerDoc->exists()) {
                    return response()->json(['error' => 'Taller no encontrado'], 404);
                }

                $tallerData = $tallerDoc->data();
                $newVerificado = !(isset($tallerData['verificado']) && $tallerData['verificado'] === true);
            } else {
                // Convertir a booleano
                $newVerificado = filter_var($newVerificado, FILTER_VALIDATE_BOOLEAN);
            }

            $tallerRef = $this->firestoreDb->collection('talleres')->document($id);
            $tallerDoc = $tallerRef->snapshot();

            if (!$tallerDoc->exists()) {
                return response()->json(['error' => 'Taller no encontrado'], 404);
            }

            $tallerData = $tallerDoc->data();

            // Preparar datos para actualizar
            $updateData = [
                'verificado' => $newVerificado,
                'fecha_actualizacion' => now()->toDateTimeString()
            ];

            // Si se está verificando, actualizar fecha de vencimiento del plan (1 mes después)
            if ($newVerificado) {
                // Calcular fecha de vencimiento (1 mes después)
                $fechaVencimiento = now()->addMonth()->toDateTimeString();
                $updateData['plan'] = [
                    'estado' => 'activo',
                    'fecha_vencimiento' => $fechaVencimiento,
                    'nombre' => $tallerData['plan']['nombre'] ?? 'Basico'
                ];
            } else {
                // Si se desverifica, limpiar fecha de vencimiento
                if (isset($tallerData['plan'])) {
                    $plan = $tallerData['plan'];
                    $plan['fecha_vencimiento'] = null;
                    $plan['estado'] = 'inactivo';
                    $updateData['plan'] = $plan;
                } else {
                    $updateData['plan'] = [
                        'estado' => 'inactivo',
                        'fecha_vencimiento' => null,
                        'nombre' => 'Basico'
                    ];
                }
            }

            // Actualizar en Firestore
            $tallerRef->set($updateData, ['merge' => true]);

            // Verificar que se actualizó correctamente
            $updatedDoc = $tallerRef->snapshot();
            $updatedData = $updatedDoc->data();

            Log::info('Taller ' . $id . ' verificado actualizado a: ' . ($newVerificado ? 'true' : 'false'));
            Log::info('Datos actualizados: ' . json_encode($updateData));

            return response()->json([
                'success' => true,
                'verificado' => $newVerificado,
                'message' => $newVerificado ? 'Taller verificado exitosamente' : 'Verificación removida',
                'data' => $updatedData
            ]);
        } catch (\Exception $e) {
            Log::error('Error en toggleVerificado de GestorTallerController: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Error al actualizar el estado: ' . $e->getMessage()
            ], 500);
        }
    }
}

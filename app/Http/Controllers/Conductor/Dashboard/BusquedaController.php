<?php

namespace App\Http\Controllers\Conductor\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class BusquedaController extends Controller
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

            if ($credentialsPath) {
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
                $jsonContent = file_get_contents($credentialsPath);
                $credentials = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->firestoreDb = new FirestoreClient([
                        'keyFilePath' => $credentialsPath,
                        'projectId' => $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error inicializando BusquedaController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }
    } */

    /**
     * Listado de talleres con filtros y paginación
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $distanciaMax = (float) $request->input('distancia', 10);
        $calificacionMin = $request->input('calificacion', '');
        $orden = $request->input('orden', 'relevancia');
        $page = (int) $request->input('page', 1);
        $perPage = 15;

        // Ubicación del usuario (opcional)
        $userLat = $request->input('user_lat');
        $userLng = $request->input('user_lng');
        $userLat = is_numeric($userLat) ? (float) $userLat : null;
        $userLng = is_numeric($userLng) ? (float) $userLng : null;

        $talleres = $this->cargarTalleres();

        // Calcular distancia si tenemos la ubicación del usuario
        foreach ($talleres as &$t) {
            $t['distancia_km'] = null;
            if (
                $userLat !== null && $userLng !== null
                && !empty($t['latitud']) && !empty($t['longitud'])
            ) {
                $t['distancia_km'] = $this->haversine(
                    $userLat,
                    $userLng,
                    (float) $t['latitud'],
                    (float) $t['longitud']
                );
            }
        }
        unset($t);

        // Aplicar filtros
        $talleres = array_filter($talleres, function ($t) use ($search, $distanciaMax, $calificacionMin, $userLat) {

            // Búsqueda por texto
            if (!empty($search)) {
                $hay = false;
                $campos = ['nombre', 'descripcion', 'direccion', 'email', 'telefono'];
                foreach ($campos as $c) {
                    if (isset($t[$c]) && stripos((string) $t[$c], $search) !== false) {
                        $hay = true;
                        break;
                    }
                }
                // También buscar en servicios si existen
                if (!$hay && !empty($t['servicios_count']) && is_array($t['servicios_count'])) {
                    foreach ($t['servicios_count'] as $s) {
                        if (stripos((string) ($s['nombre'] ?? ''), $search) !== false) {
                            $hay = true;
                            break;
                        }
                    }
                }
                if (!$hay) return false;
            }

            // Filtro por distancia (solo si tenemos ubicación del usuario)
            if ($userLat !== null && $t['distancia_km'] !== null) {
                if ($t['distancia_km'] > $distanciaMax) return false;
            }

            // Filtro por calificación mínima
            if ($calificacionMin !== '') {
                $rating = (float) ($t['calificacion_promedio'] ?? 0);
                if ($rating < (float) $calificacionMin) return false;
            }

            return true;
        });

        $talleres = array_values($talleres);

        // Ordenar
        usort($talleres, function ($a, $b) use ($orden, $userLat) {
            switch ($orden) {
                case 'distancia':
                    if ($userLat === null) return 0;
                    $da = $a['distancia_km'] ?? PHP_FLOAT_MAX;
                    $db = $b['distancia_km'] ?? PHP_FLOAT_MAX;
                    return $da <=> $db;

                case 'calificacion':
                case 'reviews':
                    $ra = (float) ($a['calificacion_promedio'] ?? 0);
                    $rb = (float) ($b['calificacion_promedio'] ?? 0);
                    return $rb <=> $ra;

                case 'relevancia':
                default:
                    // Relevancia: verificados primero, luego mejor calificación
                    $va = ($a['verificado'] ?? false) ? 1 : 0;
                    $vb = ($b['verificado'] ?? false) ? 1 : 0;
                    if ($va !== $vb) return $vb <=> $va;

                    $ra = (float) ($a['calificacion_promedio'] ?? 0);
                    $rb = (float) ($b['calificacion_promedio'] ?? 0);
                    return $rb <=> $ra;
            }
        });

        // Paginación
        $total = count($talleres);
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page < 1) $page = 1;
        if ($page > $totalPages) $page = $totalPages;

        $offset = ($page - 1) * $perPage;
        $talleresPaginados = array_slice($talleres, $offset, $perPage);

        return view('conductor.busqueda', [
            'talleres' => $talleresPaginados,
            'search' => $search,
            'distanciaMax' => $distanciaMax,
            'calificacionMin' => $calificacionMin,
            'orden' => $orden,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'userLat' => $userLat,
            'userLng' => $userLng,
        ]);
    }

    /**
     * Cargar todos los talleres desde Firestore
     */
    private function cargarTalleres()
    {
        $talleres = [];

        if (!$this->firestoreDb) return $talleres;

        try {
            $snapshot = $this->firestoreDb->collection('talleres')->documents();

            foreach ($snapshot as $doc) {
                if (!$doc->exists()) continue;
                $data = $doc->data();

                // Normalizar campos
                $data['id'] = $doc->id();
                $data['nombre'] = $data['nombre'] ?? 'Taller';
                $data['descripcion'] = $data['descripcion'] ?? '';
                $data['direccion'] = $data['direccion'] ?? '';
                $data['verificado'] = $data['verificado'] ?? false;
                $data['latitud'] = $data['latitud'] ?? null;
                $data['longitud'] = $data['longitud'] ?? null;
                $data['servicios_count'] = $data['servicios_count'] ?? [];
                $data['calificacion_promedio'] = $data['calificacion_promedio'] ?? 0;
                $data['total_resenas'] = $data['total_resenas'] ?? 0;
                $data['email'] = $data['email'] ?? '';
                $data['telefono'] = $data['telefono'] ?? '';

                // 👇 Prioridad: LOGO primero, luego galería, luego null
                $data['imagen_principal'] = $data['logo_url'] ?? null;

                // Si no hay logo, buscar la primera imagen del establecimiento como fallback
                if (empty($data['imagen_principal'])) {
                    try {
                        $imgSnapshot = $this->firestoreDb
                            ->collection('talleres')
                            ->document($doc->id())
                            ->collection('imagenes')
                            ->limit(1)
                            ->documents();

                        foreach ($imgSnapshot as $img) {
                            if ($img->exists()) {
                                $data['imagen_principal'] = $img->data()['url'] ?? null;
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        // sin imagen
                    }
                }

                $talleres[] = $data;
            }
        } catch (\Exception $e) {
            Log::warning('Error cargando talleres: ' . $e->getMessage());
        }

        return $talleres;
    }

    /**
     * Fórmula de Haversine (distancia entre 2 puntos geográficos en km)
     */
    private function haversine($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}

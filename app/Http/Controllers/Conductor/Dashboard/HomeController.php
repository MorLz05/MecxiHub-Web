<?php

namespace App\Http\Controllers\Conductor\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class HomeController extends Controller
{
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
            Log::error('Error inicializando HomeController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }
    }

    /**
     * Página principal del conductor
     */
    public function index(Request $request)
    {
        // Ubicación del usuario (opcional)
        $userLat = $request->input('user_lat');
        $userLng = $request->input('user_lng');
        $userLat = is_numeric($userLat) ? (float) $userLat : null;
        $userLng = is_numeric($userLng) ? (float) $userLng : null;

        $talleres = $this->cargarTalleres($userLat, $userLng);

        // Ordenar: por distancia si hay ubicación, si no por rating
        usort($talleres, function ($a, $b) use ($userLat) {
            // Si hay ubicación del usuario y ambos tienen distancia, ordenar por distancia
            if ($userLat !== null) {
                $da = $a['distancia_km'] ?? PHP_FLOAT_MAX;
                $db = $b['distancia_km'] ?? PHP_FLOAT_MAX;
                if ($da !== $db) return $da <=> $db;
            }

            // Fallback: ordenar por rating (mejor valorados primero)
            $ra = (float) ($a['calificacion_promedio'] ?? 0);
            $rb = (float) ($b['calificacion_promedio'] ?? 0);
            if ($ra !== $rb) return $rb <=> $ra;

            // Desempate: por total de reseñas
            return ((int) ($b['total_resenas'] ?? 0)) <=> ((int) ($a['total_resenas'] ?? 0));
        });

        // Tomar solo los primeros 3 (o 6, ajusta a tu gusto)
        $talleres = array_slice($talleres, 0, 3);

        return view('conductor.principal', [
            'talleres' => $talleres,
            'userLat' => $userLat,
            'userLng' => $userLng,
            'tieneUbicacion' => $userLat !== null && $userLng !== null,
        ]);
    }

    /**
     * Cargar todos los talleres desde Firestore (con imágenes y reseñas)
     */
    private function cargarTalleres($userLat = null, $userLng = null)
    {
        $talleres = [];

        if (!$this->firestoreDb) return $talleres;

        try {
            $snapshot = $this->firestoreDb->collection('talleres')->documents();

            foreach ($snapshot as $doc) {
                if (!$doc->exists()) continue;
                $data = $doc->data();

                // Normalizar
                $data['id'] = $doc->id();
                $data['nombre'] = $data['nombre'] ?? 'Taller';
                $data['direccion'] = $data['direccion'] ?? '';
                $data['telefono'] = $data['telefono'] ?? '';
                $data['verificado'] = $data['verificado'] ?? false;
                $data['logo_url'] = $data['logo_url'] ?? null;
                $data['latitud'] = $data['latitud'] ?? null;
                $data['longitud'] = $data['longitud'] ?? null;
                $data['servicios_count'] = $data['servicios_count'] ?? [];

                // Solo talleres con plan activo o verificados (ajusta según tu lógica de negocio)
                // Comento para que veas todos durante pruebas
                // if (!($data['verificado'] ?? false)) continue;

                // Imagen principal: logo primero, luego primera imagen de galería
                $data['imagen_principal'] = $data['logo_url'];

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

                // Rating: primero verificar el campo desnormalizado, luego calcular
                $rating = (float) ($data['rating_promedio'] ?? $data['calificacion_promedio'] ?? 0);
                $totalResenas = (int) ($data['total_resenas'] ?? 0);

                // Si no hay rating desnormalizado, calcularlo sumando reseñas
                if ($rating === 0.0 || $totalResenas === 0) {
                    try {
                        // Buscar en la colección raíz de reseñas
                        $resenasSnap = $this->firestoreDb
                            ->collection('resenas')
                            ->where('taller_id', '=', $doc->id())
                            ->documents();

                        $suma = 0;
                        $count = 0;
                        foreach ($resenasSnap as $r) {
                            if ($r->exists()) {
                                $cal = (int) ($r->data()['calificacion'] ?? $r->data()['rating'] ?? 0);
                                if ($cal >= 1 && $cal <= 5) {
                                    $suma += $cal;
                                    $count++;
                                }
                            }
                        }

                        // También buscar en subcolección (por si acaso)
                        if ($count === 0) {
                            try {
                                $subSnap = $this->firestoreDb
                                    ->collection('talleres')
                                    ->document($doc->id())
                                    ->collection('resenas')
                                    ->documents();

                                foreach ($subSnap as $r) {
                                    if ($r->exists()) {
                                        $cal = (int) ($r->data()['rating'] ?? $r->data()['calificacion'] ?? 0);
                                        if ($cal >= 1 && $cal <= 5) {
                                            $suma += $cal;
                                            $count++;
                                        }
                                    }
                                }
                            } catch (\Exception $e) {}
                        }

                        if ($count > 0) {
                            $rating = round($suma / $count, 1);
                            $totalResenas = $count;
                        }
                    } catch (\Exception $e) {
                        // sin reseñas
                    }
                }

                $data['calificacion_promedio'] = $rating;
                $data['total_resenas'] = $totalResenas;

                // Especialidades (primeros 2 servicios)
                $data['especialidades_str'] = '';
                if (!empty($data['servicios_count']) && is_array($data['servicios_count'])) {
                    $nombres = [];
                    foreach (array_slice($data['servicios_count'], 0, 2) as $s) {
                        if (!empty($s['nombre'])) $nombres[] = $s['nombre'];
                    }
                    $data['especialidades_str'] = implode(', ', $nombres);
                }

                // Calcular distancia si tenemos coordenadas del usuario y del taller
                $data['distancia_km'] = null;
                if (
                    $userLat !== null && $userLng !== null
                    && !empty($data['latitud']) && !empty($data['longitud'])
                ) {
                    $data['distancia_km'] = round($this->haversine(
                        $userLat, $userLng,
                        (float) $data['latitud'], (float) $data['longitud']
                    ), 1);
                }

                $talleres[] = $data;
            }
        } catch (\Exception $e) {
            Log::warning('Error cargando talleres en HomeController: ' . $e->getMessage());
        }

        return $talleres;
    }

    /**
     * Haversine
     */
    private function haversine($lat1, $lng1, $lat2, $lng2)
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}
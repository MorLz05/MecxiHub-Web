<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class DashboardController extends Controller
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
                    $config = [
                        'keyFilePath' => $credentialsPath,
                        'projectId' => $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID'),
                    ];
                    $this->firestoreDb = new FirestoreClient($config);
                    Log::info('Firestore inicializado en DashboardController');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error inicializando DashboardController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }
    } */

    public function index()
    {
        $user = session('firebase_user');

        // Valores por defecto
        $tallerData = [
            'verificado' => false,
            'plan' => 'Sin plan',
            'nombre' => $user['nombre_completo'] ?? 'Mi taller',
            'id' => null,
        ];

        $stats = [
            'ordenes_total' => 0,
            'ordenes_hoy' => 0,
            'activas' => 0,
            'completadas' => 0,
            'canceladas' => 0,
            'pendientes' => 0,
            'calificacion' => 0,
            'total_resenas' => 0,
        ];

        $ordenesHoy = [];
        $ordenesActivas = [];
        $resenasRecientes = [];

        if ($this->firestoreDb && $user) {
            try {
                // ============================================================
                // 1. Datos del taller (verificado, plan, nombre)
                // ============================================================
                $usuarioDoc = $this->firestoreDb->collection('usuarios')->document($user['uid'])->snapshot();
                $tallerId = null;

                if ($usuarioDoc->exists()) {
                    $tallerId = $usuarioDoc->data()['taller_id'] ?? null;
                }

                if ($tallerId) {
                    $tallerDoc = $this->firestoreDb->collection('talleres')->document($tallerId)->snapshot();

                    if ($tallerDoc->exists()) {
                        $raw = $tallerDoc->data();
                        $raw['id'] = $tallerId;

                        // Normalizar "plan" a string
                        $plan = $raw['plan'] ?? null;
                        if (is_array($plan)) {
                            $planNombre = $plan['nombre'] ?? $plan['name'] ?? $plan['titulo'] ?? 'Sin plan';
                        } elseif (is_string($plan)) {
                            $planNombre = $plan;
                        } else {
                            $planNombre = 'Sin plan';
                        }

                        $tallerData = [
                            'verificado' => (bool)($raw['verificado'] ?? false),
                            'plan' => $planNombre,
                            'nombre' => $raw['nombre'] ?? $user['nombre_completo'] ?? 'Mi taller',
                            'id' => $tallerId,
                            'logo_url' => $raw['logo_url'] ?? null,
                        ];

                        // Guardar en sesión
                        session(['taller_data' => $tallerData]);
                        session(['taller_data_raw' => $raw]);
                    }

                    // ============================================================
                    // 2. Órdenes del taller (todas)
                    // ============================================================
                    $ordenes = [];
                    try {
                        $snapshot = $this->firestoreDb
                            ->collection('ordenes_trabajo')
                            ->where('taller_id', '=', $tallerId)
                            ->documents();

                        foreach ($snapshot as $doc) {
                            if (!$doc->exists()) continue;
                            $data = $doc->data();
                            $data['id'] = $doc->id();
                            $ordenes[] = $data;
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error cargando órdenes: ' . $e->getMessage());
                    }

                    // Ordenar por fecha más reciente
                    usort($ordenes, function ($a, $b) {
                        $da = strtotime($a['fecha_orden'] ?? '0');
                        $db = strtotime($b['fecha_orden'] ?? '0');
                        return $db - $da;
                    });

                    // Calcular stats
                    $hoy = now()->format('Y-m-d');
                    foreach ($ordenes as $orden) {
                        $estado = $orden['estado'] ?? 'Pendiente';
                        $stats['ordenes_total']++;

                        if ($estado === 'Listo') $stats['completadas']++;
                        elseif ($estado === 'Cancelado') $stats['canceladas']++;
                        elseif ($estado === 'Pendiente') $stats['pendientes']++;
                        elseif ($estado === 'En reparación') $stats['activas']++;

                        // Órdenes activas (Pendiente + En reparación)
                        if (in_array($estado, ['Pendiente', 'En reparación'], true)) {
                            $ordenesActivas[] = $orden;
                        }

                        // Órdenes de hoy
                        $fechaOrden = $orden['fecha_orden'] ?? '';
                        if (str_starts_with($fechaOrden, $hoy)) {
                            $stats['ordenes_hoy']++;
                            $ordenesHoy[] = $orden;
                        }
                    }

                    // Limitar órdenes activas a las 5 más recientes
                    $ordenesActivas = array_slice($ordenesActivas, 0, 5);

                    // ============================================================
                    // 3. Reseñas del taller (subcolección)
                    // ============================================================
                    try {
                        $resenasSnap = $this->firestoreDb
                            ->collection('talleres')
                            ->document($tallerId)
                            ->collection('resenas')
                            ->documents();

                        foreach ($resenasSnap as $doc) {
                            if (!$doc->exists()) continue;
                            $r = $doc->data();
                            $r['id'] = $doc->id();
                            $resenasRecientes[] = $r;
                        }

                        // Ordenar por fecha desc
                        usort($resenasRecientes, function ($a, $b) {
                            $fa = strtotime($a['fecha'] ?? '0');
                            $fb = strtotime($b['fecha'] ?? '0');
                            return $fb - $fa;
                        });

                        // Calcular promedio
                        $totalResenas = count($resenasRecientes);
                        $suma = 0;
                        foreach ($resenasRecientes as $r) {
                            $c = (int) ($r['rating'] ?? 0);
                            if ($c >= 1 && $c <= 5) $suma += $c;
                        }

                        $stats['total_resenas'] = $totalResenas;
                        $stats['calificacion'] = $totalResenas > 0 ? round($suma / $totalResenas, 1) : 0;

                        // Limitar a las 5 más recientes para el dashboard
                        $resenasRecientes = array_slice($resenasRecientes, 0, 5);
                    } catch (\Exception $e) {
                        Log::warning('Error cargando reseñas: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error en DashboardController: ' . $e->getMessage());
            }
        }

        return view('taller.dashboard', compact(
            'tallerData',
            'stats',
            'ordenesHoy',
            'ordenesActivas',
            'resenasRecientes'
        ));
    }
}

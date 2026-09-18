<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

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
                    Log::info('Firestore inicializado en Taller\PlanController');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error inicializando Taller\PlanController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }
    } */

    /**
     * Lista todos los planes dirigidos al tipo de usuario "Taller"
     */
    public function index()
    {
        $user = session('firebase_user');
        $planes = [];
        $planActualId = null;

        if ($this->firestoreDb) {
            try {
                // 1. Obtener el taller del usuario para saber su plan actual
                $usuarioDoc = $this->firestoreDb->collection('usuarios')->document($user['uid'])->snapshot();

                if ($usuarioDoc->exists()) {
                    $usuarioData = $usuarioDoc->data();
                    $tallerId = $usuarioData['taller_id'] ?? null;

                    if ($tallerId) {
                        $tallerDoc = $this->firestoreDb->collection('talleres')->document($tallerId)->snapshot();
                        if ($tallerDoc->exists()) {
                            $tallerData = $tallerDoc->data();
                            // Guardamos el ID del plan actual del taller (puede ser null)
                            $planActualId = $tallerData['plan']['id'] ?? $tallerData['plan_id'] ?? null;
                        }
                    }
                }

                // 2. Obtener todos los planes de Firestore
                $snapshot = $this->firestoreDb->collection('planes')->documents();

                foreach ($snapshot as $doc) {
                    if (!$doc->exists()) continue;

                    $data = $doc->data();
                    $data['id'] = $doc->id();

                    // Solo planes activos
                    $activo = $data['activo'] ?? true;
                    if (!$activo) continue;

                    // Filtrar solo los que van dirigidos a "Taller"
                    $tipos = $data['tipos_usuario'] ?? [];
                    if (!is_array($tipos) || !in_array('Taller', $tipos, true)) {
                        continue;
                    }

                    $planes[] = $data;
                }

                // 3. Ordenar: primero por "orden" ascendente, luego por precio ascendente
                usort($planes, function ($a, $b) {
                    $ordenA = $a['orden'] ?? 999;
                    $ordenB = $b['orden'] ?? 999;
                    if ($ordenA === $ordenB) {
                        return ($a['precio'] ?? 0) <=> ($b['precio'] ?? 0);
                    }
                    return $ordenA <=> $ordenB;
                });
            } catch (\Exception $e) {
                Log::error('Error obteniendo planes para taller: ' . $e->getMessage());
            }
        }

        // Marcar cuál es el plan actual del taller
        foreach ($planes as &$plan) {
            $plan['es_plan_actual'] = ($planActualId && $plan['id'] === $planActualId);
        }
        unset($plan);

        return view('taller.planes', compact('planes'));
    }
}

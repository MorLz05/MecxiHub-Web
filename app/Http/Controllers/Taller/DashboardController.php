<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class DashboardController extends Controller
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
    }

    public function index()
    {
        $user = session('firebase_user');
        $tallerData = [
            'verificado' => false,
            'plan' => null,
            'nombre' => $user['nombre_completo'] ?? 'Mi taller'
        ];

        if ($this->firestoreDb) {
            try {
                // Obtener datos del taller
                $usuariosRef = $this->firestoreDb->collection('usuarios')->document($user['uid']);
                $usuarioSnapshot = $usuariosRef->snapshot();

                if ($usuarioSnapshot->exists()) {
                    $usuarioData = $usuarioSnapshot->data();
                    $tallerId = $usuarioData['taller_id'] ?? null;

                    if ($tallerId) {
                        $tallerRef = $this->firestoreDb->collection('talleres')->document($tallerId);
                        $tallerSnapshot = $tallerRef->snapshot();

                        if ($tallerSnapshot->exists()) {
                            $tallerData = $tallerSnapshot->data();
                            $tallerData['id'] = $tallerId;

                            // Guardar en sesión para uso futuro
                            session(['taller_data' => $tallerData]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error obteniendo datos del taller: ' . $e->getMessage());
            }
        }

        return view('taller.dashboard', compact('tallerData'));
    }
}

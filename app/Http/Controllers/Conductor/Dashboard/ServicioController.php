<?php

namespace App\Http\Controllers\Conductor\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Google\Cloud\Firestore\FirestoreClient;

class ServicioController extends Controller
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
                $creds = json_decode(file_get_contents($credentialsPath), true);
                $this->firestoreDb = new FirestoreClient([
                    'keyFilePath' => $credentialsPath,
                    'projectId' => $creds['project_id'] ?? env('FIREBASE_PROJECT_ID'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('ServicioController init: ' . $e->getMessage());
        }
    } */

    /**
     * Lista todos los servicios (órdenes) del conductor actual.
     */
    public function index()
    {
        $uid = session('firebase_user.uid');
        if (!$uid || !$this->firestoreDb) {
            return view('conductor.dashboard.servicios', [
                'ordenes' => [],
                'stats' => ['total' => 0, 'activos' => 0, 'listos' => 0, 'cancelados' => 0],
            ]);
        }

        $ordenes = [];

        try {
            $snapshot = $this->firestoreDb
                ->collection('ordenes_trabajo')
                ->where('cliente_uid', '=', $uid)
                ->documents();

            foreach ($snapshot as $doc) {
                if (!$doc->exists()) continue;
                $data = $doc->data();
                $data['id'] = $doc->id();
                $ordenes[] = $data;
            }

            usort(
                $ordenes,
                fn($a, $b) =>
                strtotime($b['fecha_orden'] ?? '0') - strtotime($a['fecha_orden'] ?? '0')
            );
        } catch (\Exception $e) {
            Log::warning('Error cargando servicios del conductor: ' . $e->getMessage());
        }

        // Stats
        $stats = ['total' => count($ordenes), 'activos' => 0, 'listos' => 0, 'cancelados' => 0];
        foreach ($ordenes as $o) {
            $est = $o['estado'] ?? 'Pendiente';
            if (in_array($est, ['Pendiente', 'En reparación'])) $stats['activos']++;
            elseif ($est === 'Listo') $stats['listos']++;
            elseif ($est === 'Cancelado') $stats['cancelados']++;
        }

        return view('conductor.dashboard.servicios', [
            'ordenes' => $ordenes,
            'stats' => $stats,
        ]);
    }

    /**
     * Detalle de una orden específica del conductor.
     */
    public function show($id)
    {
        $uid = session('firebase_user.uid');
        if (!$uid || !$this->firestoreDb) {
            return redirect()->route('cuenta.servicios.index')->with('error', 'Acceso no autorizado.');
        }

        $doc = $this->firestoreDb->collection('ordenes_trabajo')->document($id)->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('cuenta.servicios.index')->with('error', 'Servicio no encontrado.');
        }

        $orden = $doc->data();
        $orden['id'] = $doc->id();

        if (($orden['cliente_uid'] ?? null) !== $uid) {
            return redirect()->route('cuenta.servicios.index')->with('error', 'No tienes permiso para ver este servicio.');
        }

        // Datos del taller
        $tallerData = [];
        if (!empty($orden['taller_id'])) {
            try {
                $tDoc = $this->firestoreDb->collection('talleres')->document($orden['taller_id'])->snapshot();
                if ($tDoc->exists()) {
                    $tallerData = $tDoc->data();
                }
            } catch (\Exception $e) {
                Log::warning('Error cargando taller del servicio: ' . $e->getMessage());
            }
        }

        // ¿Ya dejó reseña?
        $yaReseno = false;
        if (!empty($orden['taller_id'])) {
            try {
                $resenas = $this->firestoreDb
                    ->collection('talleres')
                    ->document($orden['taller_id'])
                    ->collection('resenas')
                    ->where('usuario_id', '=', $uid)
                    ->limit(1)
                    ->documents();

                foreach ($resenas as $r) {
                    if ($r->exists()) {
                        $yaReseno = true;
                        break;
                    }
                }
            } catch (\Exception $e) {
            }
        }

        $fallas = $orden['fallas_multiples'] ?? ($orden['falla'] ?? []);
        if (isset($orden['falla']) && is_array($orden['falla']) && !isset($orden['falla'][0])) {
            $fallas = [$orden['falla']];
        }

        return view('conductor.dashboard.servicio-detalle', [
            'orden' => $orden,
            'fallas' => $fallas,
            'tallerData' => $tallerData,
            'yaReseno' => $yaReseno,
        ]);
    }

    /**
     * Estadísticas para el resumen (usado por resumen.blade.php).
     */
    public function stats()
    {
        $uid = session('firebase_user.uid');
        if (!$uid || !$this->firestoreDb) {
            return ['total' => 0, 'activos' => 0, 'listos' => 0, 'cancelados' => 0, 'talleres_visitados' => 0, 'ultimos' => []];
        }

        $ordenes = [];
        try {
            $snapshot = $this->firestoreDb
                ->collection('ordenes_trabajo')
                ->where('cliente_uid', '=', $uid)
                ->documents();

            foreach ($snapshot as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $data['id'] = $doc->id();
                    $ordenes[] = $data;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error stats conductor: ' . $e->getMessage());
        }

        usort(
            $ordenes,
            fn($a, $b) =>
            strtotime($b['fecha_orden'] ?? '0') - strtotime($a['fecha_orden'] ?? '0')
        );

        $stats = [
            'total' => count($ordenes),
            'activos' => 0,
            'listos' => 0,
            'cancelados' => 0,
            'talleres_visitados' => 0,
            'ultimos' => array_slice($ordenes, 0, 4),
        ];

        $talleresSet = [];
        foreach ($ordenes as $o) {
            $est = $o['estado'] ?? 'Pendiente';
            if (in_array($est, ['Pendiente', 'En reparación'])) $stats['activos']++;
            elseif ($est === 'Listo') $stats['listos']++;
            elseif ($est === 'Cancelado') $stats['cancelados']++;

            if (!empty($o['taller_id'])) $talleresSet[$o['taller_id']] = true;
        }
        $stats['talleres_visitados'] = count($talleresSet);

        return $stats;
    }
}

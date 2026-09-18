<?php

namespace App\Http\Controllers\Conductor\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Google\Cloud\Firestore\FirestoreClient;

class NotificacionController extends Controller
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
            Log::error('NotificacionController init: ' . $e->getMessage());
        }
    } */

    /**
     * Lista todas las notificaciones del usuario logueado.
     */
    public function index()
    {
        $uid = session('firebase_user.uid');
        if (!$uid) {
            return redirect()->route('login');
        }

        $notificaciones = [];
        $noLeidas = 0;

        if ($this->firestoreDb) {
            try {
                // Firestore no soporta OR en where fácilmente en SDK PHP para
                // ordenar + filtrar, así que traemos todas del usuario y ordenamos en PHP.
                // Si el volumen crece, agregar índice compuesto (usuario_id + fecha).
                $snapshot = $this->firestoreDb
                    ->collection('notificaciones')
                    ->where('usuario_id', '=', $uid)
                    ->limit(100)
                    ->documents();

                foreach ($snapshot as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        $data['id'] = $doc->id();
                        $data['fecha_humana'] = $this->formatearFecha($data['fecha'] ?? null);
                        $notificaciones[] = $data;

                        if (empty($data['leida'])) {
                            $noLeidas++;
                        }
                    }
                }

                usort(
                    $notificaciones,
                    fn($a, $b) =>
                    strtotime($b['fecha'] ?? '0') - strtotime($a['fecha'] ?? '0')
                );
            } catch (\Exception $e) {
                Log::warning('Error cargando notificaciones: ' . $e->getMessage());
            }
        }

        // Cachear count en sesión para badge del navbar
        session(['notif_no_leidas' => $noLeidas]);

        return view('conductor.dashboard.notificaciones', [
            'notificaciones' => $notificaciones,
            'noLeidas' => $noLeidas,
        ]);
    }

    /**
     * Marcar una notificación como leída.
     */
    public function marcarLeida($id)
    {
        $uid = session('firebase_user.uid');
        if (!$uid || !$this->firestoreDb) {
            return response()->json(['ok' => false], 400);
        }

        try {
            $ref = $this->firestoreDb->collection('notificaciones')->document($id);
            $doc = $ref->snapshot();

            if ($doc->exists() && ($doc->data()['usuario_id'] ?? null) === $uid) {
                $ref->set(['leida' => true, 'leida_en' => Carbon::now()->toIso8601String()], ['merge' => true]);
                return response()->json(['ok' => true]);
            }
        } catch (\Exception $e) {
            Log::warning('Error marcando notif leída: ' . $e->getMessage());
        }

        return response()->json(['ok' => false], 400);
    }

    /**
     * Marcar todas como leídas.
     */
    public function marcarTodasLeidas()
    {
        $uid = session('firebase_user.uid');
        if (!$uid || !$this->firestoreDb) {
            return back()->with('error', 'No autorizado.');
        }

        try {
            $snapshot = $this->firestoreDb
                ->collection('notificaciones')
                ->where('usuario_id', '=', $uid)
                ->where('leida', '=', false)
                ->documents();

            $count = 0;
            $ahora = Carbon::now()->toIso8601String();

            foreach ($snapshot as $doc) {
                if ($doc->exists()) {
                    $doc->reference()->set(
                        ['leida' => true, 'leida_en' => $ahora],
                        ['merge' => true]
                    );
                    $count++;
                }
            }

            session(['notif_no_leidas' => 0]);

            return back()->with('success', "Se marcaron {$count} notificaciones como leídas.");
        } catch (\Exception $e) {
            Log::error('Error marcando todas: ' . $e->getMessage());
            return back()->with('error', 'Error al marcar notificaciones.');
        }
    }

    /**
     * Elimina una notificación.
     */
    public function eliminar($id)
    {
        $uid = session('firebase_user.uid');
        if (!$uid || !$this->firestoreDb) {
            return back()->with('error', 'No autorizado.');
        }

        try {
            $ref = $this->firestoreDb->collection('notificaciones')->document($id);
            $doc = $ref->snapshot();

            if ($doc->exists() && ($doc->data()['usuario_id'] ?? null) === $uid) {
                $ref->delete();
                return back()->with('success', 'Notificación eliminada.');
            }

            return back()->with('error', 'No se pudo eliminar la notificación.');
        } catch (\Exception $e) {
            Log::error('Error eliminando notif: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar.');
        }
    }

    /**
     * Endpoint AJAX: cuenta de no leídas (para el badge del navbar).
     */
    public function contarNoLeidas()
    {
        $uid = session('firebase_user.uid');
        if (!$uid || !$this->firestoreDb) {
            return response()->json(['count' => 0]);
        }

        try {
            $snapshot = $this->firestoreDb
                ->collection('notificaciones')
                ->where('usuario_id', '=', $uid)
                ->where('leida', '=', false)
                ->documents();

            $count = 0;
            foreach ($snapshot as $doc) {
                if ($doc->exists()) $count++;
            }

            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['count' => 0]);
        }
    }

    private function formatearFecha($fecha)
    {
        if (!$fecha) return 'Reciente';
        try {
            return Carbon::parse($fecha)->diffForHumans();
        } catch (\Exception $e) {
            return 'Reciente';
        }
    }
}

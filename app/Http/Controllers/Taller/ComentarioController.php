<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Google\Cloud\Firestore\FirestoreClient;

class ComentarioController extends Controller
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
            Log::error('Error inicializando ComentarioController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }
    } */

    /**
     * Lista todas las reseñas del taller con estadísticas.
     */
    public function index(Request $request)
    {
        if (!$this->firestoreDb) {
            abort(503, 'Servicio no disponible');
        }

        $tallerId = $this->obtenerTallerIdActual();

        if (!$tallerId) {
            abort(403, 'No tienes un taller asignado.');
        }

        // 1. Info del taller
        $tallerDoc = $this->firestoreDb->collection('talleres')->document($tallerId)->snapshot();
        if (!$tallerDoc->exists()) {
            abort(404, 'Taller no encontrado');
        }
        $taller = $tallerDoc->data();
        $taller['id'] = $tallerId;
        $taller['nombre'] = $taller['nombre'] ?? 'Mi taller';

        // 2. Reseñas
        $resenas = [];
        try {
            $snapshot = $this->firestoreDb
                ->collection('talleres')
                ->document($tallerId)
                ->collection('resenas')
                ->documents();

            foreach ($snapshot as $doc) {
                if ($doc->exists()) {
                    $r = $doc->data();
                    $r['id'] = $doc->id();
                    $resenas[] = $r;
                }
            }

            usort(
                $resenas,
                fn($a, $b) =>
                strtotime($b['fecha'] ?? '0') - strtotime($a['fecha'] ?? '0')
            );
        } catch (\Exception $e) {
            Log::warning('Error cargando reseñas del taller: ' . $e->getMessage());
        }

        // 3. Estadísticas
        $totalResenas = count($resenas);
        $suma = 0;
        $distribucion = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $sinResponder = 0;
        $respondidas = 0;

        foreach ($resenas as $r) {
            $c = (int) ($r['rating'] ?? 0);
            if ($c >= 1 && $c <= 5) {
                $suma += $c;
                $distribucion[$c]++;
            }
            if (!empty($r['respuesta_taller'])) {
                $respondidas++;
            } else {
                $sinResponder++;
            }
        }

        $promedio = $totalResenas > 0 ? round($suma / $totalResenas, 1) : 0;
        $porcentajes = [];
        foreach ($distribucion as $est => $cant) {
            $porcentajes[$est] = $totalResenas > 0 ? round(($cant / $totalResenas) * 100) : 0;
        }

        // 4. Filtros (opcionales por query string)
        $filtroRating = $request->input('rating'); // 1..5 o null
        $filtroEstado = $request->input('estado'); // 'respondidas' | 'pendientes' | null

        if ($filtroRating) {
            $resenas = array_filter($resenas, fn($r) => (int)($r['rating'] ?? 0) === (int)$filtroRating);
            $resenas = array_values($resenas);
        }
        if ($filtroEstado === 'respondidas') {
            $resenas = array_filter($resenas, fn($r) => !empty($r['respuesta_taller']));
            $resenas = array_values($resenas);
        } elseif ($filtroEstado === 'pendientes') {
            $resenas = array_filter($resenas, fn($r) => empty($r['respuesta_taller']));
            $resenas = array_values($resenas);
        }

        return view('taller.comentarios', [
            'taller' => $taller,
            'resenas' => $resenas,
            'promedio' => $promedio,
            'totalResenas' => $totalResenas,
            'distribucion' => $distribucion,
            'porcentajes' => $porcentajes,
            'sinResponder' => $sinResponder,
            'respondidas' => $respondidas,
            'filtroRating' => $filtroRating,
            'filtroEstado' => $filtroEstado,
        ]);
    }

    /**
     * Guardar la respuesta del taller a una reseña.
     */
    public function responder(Request $request, $resenaId)
    {
        if (!$this->firestoreDb) {
            return back()->with('error', 'Servicio no disponible.');
        }

        $validated = $request->validate([
            'respuesta' => 'required|string|min:3|max:1000',
        ], [
            'respuesta.required' => 'La respuesta no puede estar vacía.',
            'respuesta.min' => 'La respuesta debe tener al menos 3 caracteres.',
            'respuesta.max' => 'La respuesta no puede superar los 1000 caracteres.',
        ]);

        $tallerId = $this->obtenerTallerIdActual();
        if (!$tallerId) {
            return back()->with('error', 'No tienes un taller asignado.');
        }

        try {
            $resenaRef = $this->firestoreDb
                ->collection('talleres')
                ->document($tallerId)
                ->collection('resenas')
                ->document($resenaId);

            $resenaDoc = $resenaRef->snapshot();
            if (!$resenaDoc->exists()) {
                return back()->with('error', 'Reseña no encontrada.');
            }

            $resenaRef->set([
                'respuesta_taller' => trim($validated['respuesta']),
                'respuesta_fecha' => Carbon::now()->toIso8601String(),
            ], ['merge' => true]);

            $resenaData = $resenaDoc->data();
            $usuarioIdResena = $resenaData['usuario_id'] ?? null;

            if ($usuarioIdResena) {
                $tallerDoc = $this->firestoreDb->collection('talleres')->document($tallerId)->snapshot();
                $tallerNombre = $tallerDoc->exists()
                    ? ($tallerDoc->data()['nombre'] ?? 'El taller')
                    : 'El taller';

                $notifService = new \App\Services\NotificacionService($this->firestoreDb);
                $notifService->notificarRespuestaTaller(
                    $usuarioIdResena,
                    $tallerId,
                    $tallerNombre,
                    $resenaId,
                    $validated['respuesta']
                );
            }

            return back()->with('success', 'Respuesta publicada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error respondiendo reseña: ' . $e->getMessage());
            return back()->with('error', 'No se pudo publicar la respuesta. Intenta de nuevo.');
        }
    }

    /**
     * Eliminar la respuesta del taller a una reseña.
     */
    public function eliminarRespuesta($resenaId)
    {
        if (!$this->firestoreDb) {
            return back()->with('error', 'Servicio no disponible.');
        }

        $tallerId = $this->obtenerTallerIdActual();
        if (!$tallerId) {
            return back()->with('error', 'No tienes un taller asignado.');
        }

        try {
            $resenaRef = $this->firestoreDb
                ->collection('talleres')
                ->document($tallerId)
                ->collection('resenas')
                ->document($resenaId);

            $resenaRef->set([
                'respuesta_taller' => null,
                'respuesta_fecha' => null,
            ], ['merge' => true]);

            return back()->with('success', 'Respuesta eliminada.');
        } catch (\Exception $e) {
            Log::error('Error eliminando respuesta: ' . $e->getMessage());
            return back()->with('error', 'No se pudo eliminar la respuesta.');
        }
    }

    /**
     * Obtiene el taller_id del usuario en sesión.
     * Primero intenta desde session('firebase_user.taller_id'),
     * si no está, lo busca en Firestore.
     */
    private function obtenerTallerIdActual()
    {
        // 1. Directo de la sesión (si lo agregaste al hacer login)
        $tallerId = session('firebase_user.taller_id');
        if ($tallerId) {
            return $tallerId;
        }

        // 2. Fallback: leer el usuario en Firestore
        $uid = session('firebase_user.uid');
        if (!$uid || !$this->firestoreDb) {
            return null;
        }

        try {
            $userDoc = $this->firestoreDb->collection('usuarios')->document($uid)->snapshot();
            if ($userDoc->exists()) {
                $data = $userDoc->data();
                $tallerId = $data['taller_id'] ?? null;

                if ($tallerId) {
                    // Cachear en sesión para próximas veces
                    $sessionData = session('firebase_user');
                    $sessionData['taller_id'] = $tallerId;
                    session(['firebase_user' => $sessionData]);
                }

                return $tallerId;
            }
        } catch (\Exception $e) {
            Log::warning('Error obteniendo taller_id: ' . $e->getMessage());
        }

        return null;
    }
}

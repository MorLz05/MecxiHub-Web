<?php

namespace App\Http\Controllers\Conductor\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class PerfilTallerController extends Controller
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
            Log::error('Error inicializando PerfilTallerController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }
    }

    /**
     * Mostrar el perfil público de un taller
     */
    public function show(Request $request, $id)
    {
        if (!$this->firestoreDb) {
            abort(503, 'Servicio no disponible');
        }

        // 1. Cargar el taller
        $tallerDoc = $this->firestoreDb->collection('talleres')->document($id)->snapshot();

        if (!$tallerDoc->exists()) {
            abort(404, 'Taller no encontrado');
        }

        $taller = $tallerDoc->data();
        $taller['id'] = $id;
        $taller['nombre'] = $taller['nombre'] ?? 'Taller';
        $taller['descripcion'] = $taller['descripcion'] ?? '';
        $taller['direccion'] = $taller['direccion'] ?? '';
        $taller['telefono'] = $taller['telefono'] ?? '';
        $taller['email'] = $taller['email'] ?? '';
        $taller['verificado'] = $taller['verificado'] ?? false;
        $taller['logo_url'] = $taller['logo_url'] ?? null;
        $taller['latitud'] = $taller['latitud'] ?? null;
        $taller['longitud'] = $taller['longitud'] ?? null;
        $taller['servicios_count'] = $taller['servicios_count'] ?? [];
        $taller['horario'] = $taller['horario'] ?? [];

        // 2. Cargar imágenes de la subcolección
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

            // Ordenar por fecha desc
            usort($imagenes, function ($a, $b) {
                return strtotime($b['fecha_subida'] ?? '0') - strtotime($a['fecha_subida'] ?? '0');
            });
        } catch (\Exception $e) {
            Log::warning('Error cargando imágenes: ' . $e->getMessage());
        }

        // 3. Cargar reseñas
        $resenas = [];
        try {
            $resenasSnapshot = $this->firestoreDb
                ->collection('talleres')
                ->document($id)
                ->collection('resenas')
                ->documents();

            foreach ($resenasSnapshot as $resena) {
                if ($resena->exists()) {
                    $r = $resena->data();
                    $r['id'] = $resena->id();
                    $resenas[] = $r;
                }
            }

            usort($resenas, fn($a, $b) => strtotime($b['fecha'] ?? '0') - strtotime($a['fecha'] ?? '0'));
        } catch (\Exception $e) {
            Log::warning('Error cargando reseñas: ' . $e->getMessage());
        }

        // 4. Calcular estadísticas de calificación
        $totalResenas = count($resenas);
        $suma = 0;
        $distribucion = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($resenas as $r) {
            $c = (int) ($r['rating'] ?? 0);
            if ($c >= 1 && $c <= 5) {
                $suma += $c;
                $distribucion[$c]++;
            }
        }
        $promedio = $totalResenas > 0 ? round($suma / $totalResenas, 1) : 0;
        $porcentajes = [];
        foreach ($distribucion as $est => $cant) {
            $porcentajes[$est] = $totalResenas > 0 ? round(($cant / $totalResenas) * 100) : 0;
        }

        // 5. Cargar servicios desde servicios_count (ya están dentro del taller)
        $servicios = [];
        foreach ($taller['servicios_count'] as $s) {
            if (!is_array($s)) continue;
            if (empty($s['activo']) && isset($s['activo'])) continue; // solo activos si existe el campo
            $servicios[] = [
                'nombre' => $s['nombre'] ?? 'Servicio',
                'descripcion' => $s['descripcion'] ?? '',
                'precio' => $s['precio'] ?? null,
                'icono' => $this->iconoParaServicio($s['nombre'] ?? ''),
            ];
        }

        // 6. Extraer "especialidades" (nombres de servicios) para los chips
        $especialidades = array_slice(array_map(fn($s) => $s['nombre'], $servicios), 0, 4);

        // 7. Distancia (si el usuario tiene ubicación en sesión o query)
        $userLat = $request->input('user_lat');
        $userLng = $request->input('user_lng');
        $distancia = null;
        if (
            is_numeric($userLat) && is_numeric($userLng)
            && !empty($taller['latitud']) && !empty($taller['longitud'])
        ) {
            $distancia = round($this->haversine(
                (float) $userLat,
                (float) $userLng,
                (float) $taller['latitud'],
                (float) $taller['longitud']
            ), 1);
        }

        $estaLogueado = session()->has('firebase_user');
        $yaReseno = false;
        $abrirFormResena = $request->boolean('calificar');
        $tokenCalificacion = $request->input('token');

        if ($estaLogueado) {
            $uid = session('firebase_user.uid');

            // Si viene un token, solo marcamos yaReseno si ESA orden ya fue reseñada
            if ($tokenCalificacion) {
                $ordenId = null;
                try {
                    $tokenDoc = $this->firestoreDb->collection('calificaciones')->document($tokenCalificacion)->snapshot();
                    if ($tokenDoc->exists()) {
                        $ordenId = $tokenDoc->data()['orden_id'] ?? null;
                    }
                } catch (\Exception $e) {
                }

                if ($ordenId) {
                    foreach ($resenas as $r) {
                        if (
                            ($r['usuario_id'] ?? null) === $uid
                            && ($r['orden_id'] ?? null) === $ordenId
                        ) {
                            $yaReseno = true;
                            break;
                        }
                    }
                }
            } else {
                // Sin token: solo bloqueamos si tiene una reseña "suelta" (sin orden_id)
                foreach ($resenas as $r) {
                    if (
                        ($r['usuario_id'] ?? null) === $uid
                        && empty($r['orden_id'])
                    ) {
                        $yaReseno = true;
                        break;
                    }
                }
            }
        }

        return view('conductor.perfil-taller', [
            'taller' => $taller,
            'imagenes' => $imagenes,
            'resenas' => $resenas,
            'promedio' => $promedio,
            'totalResenas' => $totalResenas,
            'distribucion' => $distribucion,
            'porcentajes' => $porcentajes,
            'servicios' => $servicios,
            'especialidades' => $especialidades,
            'distancia' => $distancia,
            'estaLogueado' => $estaLogueado,
            'yaReseno' => $yaReseno,
            'abrirFormResena' => $abrirFormResena,
            'tokenCalificacion' => $tokenCalificacion,
        ]);
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

    /**
     * Icono automático según el nombre del servicio
     */
    private function iconoParaServicio($nombre)
    {
        $nombre = strtolower($nombre);

        $mapa = [
            'freno' => 'fa-solid fa-circle-stop',
            'suspension' => 'fa-solid fa-wrench',
            'suspensión' => 'fa-solid fa-wrench',
            'alinea' => 'fa-solid fa-car-side',
            'balanceo' => 'fa-solid fa-car-side',
            'aceite' => 'fa-solid fa-oil-can',
            'motor' => 'fa-solid fa-gear',
            'electric' => 'fa-solid fa-bolt',
            'diagnos' => 'fa-solid fa-laptop-medical',
            'aire' => 'fa-solid fa-fan',
            'transmis' => 'fa-solid fa-cogs',
            'escape' => 'fa-solid fa-smog',
            'llanta' => 'fa-solid fa-circle',
            'pintura' => 'fa-solid fa-spray-can',
            'lavado' => 'fa-solid fa-droplet',
            'clima' => 'fa-solid fa-snowflake',
        ];

        foreach ($mapa as $clave => $icono) {
            if (str_contains($nombre, $clave)) return $icono;
        }

        return 'fa-solid fa-screwdriver-wrench';
    }

    public function storeResena(Request $request, $id)
    {
        if (!$this->firestoreDb) {
            return back()->with('error', 'Servicio no disponible. Intenta más tarde.');
        }

        // 1. Validación
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comentario' => 'required|string|min:10|max:1000',
            'token' => 'nullable|string',
        ], [
            'rating.required' => 'Debes seleccionar una calificación.',
            'rating.min' => 'La calificación mínima es 1 estrella.',
            'rating.max' => 'La calificación máxima es 5 estrellas.',
            'comentario.required' => 'El comentario es obligatorio.',
            'comentario.min' => 'El comentario debe tener al menos 10 caracteres.',
            'comentario.max' => 'El comentario no puede superar los 1000 caracteres.',
        ]);

        // 2. Usuario logueado
        $uid = session('firebase_user.uid');
        $nombre = session('firebase_user.nombre_completo', 'Usuario');
        $email = session('firebase_user.email', '');

        if (!$uid) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para dejar una reseña.');
        }

        // 3. Verificar taller
        $tallerRef = $this->firestoreDb->collection('talleres')->document($id);
        $tallerDoc = $tallerRef->snapshot();
        if (!$tallerDoc->exists()) {
            return back()->with('error', 'Taller no encontrado.');
        }

        // 4. Verificar que no haya reseñado ya
        $tokenCalificacion = $validated['token'] ?? null;
        $ordenId = null;

        // Si viene token, obtener el orden_id asociado
        if (!empty($tokenCalificacion)) {
            try {
                $tokenDoc = $this->firestoreDb->collection('calificaciones')->document($tokenCalificacion)->snapshot();
                if ($tokenDoc->exists()) {
                    $ordenId = $tokenDoc->data()['orden_id'] ?? null;
                }
            } catch (\Exception $e) {
                Log::warning('No se pudo leer token de calificación: ' . $e->getMessage());
            }
        }

        // Verificar si ya existe una reseña de este usuario para esta orden
        $yaResenoEstaOrden = false;

        if ($ordenId) {
            // Bloqueo estricto: mismo usuario + misma orden
            $existentes = $tallerRef->collection('resenas')
                ->where('usuario_id', '=', $uid)
                ->where('orden_id', '=', $ordenId)
                ->limit(1)
                ->documents();

            foreach ($existentes as $doc) {
                if ($doc->exists()) {
                    $yaResenoEstaOrden = true;
                    break;
                }
            }
        } else {
            // Fallback: si no hay token ni orden_id (reseña directa desde el perfil),
            // bloqueamos solo si ya dejó una reseña "suelta" (sin orden) para el mismo taller
            $existentes = $tallerRef->collection('resenas')
                ->where('usuario_id', '=', $uid)
                ->limit(5)
                ->documents();

            foreach ($existentes as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    // Si tiene orden_id, es de otro servicio, permitir
                    // Si no tiene orden_id, es "suelta" → bloquear solo la primera
                    if (empty($data['orden_id'])) {
                        $yaResenoEstaOrden = true;
                        break;
                    }
                }
            }
        }

        if ($yaResenoEstaOrden) {
            return redirect()->route('taller.perfil', ['id' => $id])
                ->with('error', 'Ya has dejado una reseña para este servicio.');
        }

        // 5. Iniciales del nombre
        $iniciales = '';
        foreach (preg_split('/\s+/', trim($nombre)) as $p) {
            if (!empty($p)) $iniciales .= mb_strtoupper(mb_substr($p, 0, 1));
            if (mb_strlen($iniciales) >= 2) break;
        }
        if (empty($iniciales)) $iniciales = 'U';

        // 6. Crear reseña en subcolección
        try {
            $resenaData = [
                'usuario_id' => $uid,
                'usuario_nombre' => $nombre,
                'usuario_email' => $email,
                'usuario_inicial' => $iniciales,
                'rating' => (int) $validated['rating'],
                'comentario' => trim($validated['comentario']),
                'respuesta_taller' => null,
                'respuesta_fecha' => null,
                'fecha' => Carbon::now()->toIso8601String(),
                'likes' => 0,
                'orden_id' => $ordenId,
                'folio' => $tokenCalificacion
                    ? ($this->obtenerFolioDelToken($tokenCalificacion) ?? null)
                    : null,
                'calificacion_token' => $tokenCalificacion,
            ];

            $tallerRef->collection('resenas')->add($resenaData);

            // 7. Recalcular promedio en el doc del taller (contador incremental)
            $this->recalcularRating($tallerRef);

            // 8. Marcar el token como usado si vino del correo
            if (!empty($validated['token'])) {
                try {
                    $this->firestoreDb->collection('calificaciones')
                        ->document($validated['token'])
                        ->set([
                            'usado' => true,
                            'usado_en' => Carbon::now()->toIso8601String(),
                            'usado_por_uid' => $uid,
                        ], ['merge' => true]);
                } catch (\Exception $e) {
                    Log::warning('No se pudo marcar el token como usado: ' . $e->getMessage());
                }
            }

            return redirect()
                ->route('taller.perfil', ['id' => $id])
                ->with('success', '¡Gracias por tu reseña! Ya está publicada.')
                ->withFragment('seccion-resenas');
        } catch (\Exception $e) {
            Log::error('Error guardando reseña: ' . $e->getMessage());
            return back()->with('error', 'No se pudo guardar tu reseña. Intenta de nuevo.');
        }
    }

    /**
     * Recalcula el promedio y total de reseñas del taller.
     */
    private function recalcularRating($tallerRef)
    {
        $resenas = $tallerRef->collection('resenas')->documents();
        $total = 0;
        $suma = 0;

        foreach ($resenas as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                if (isset($data['rating'])) {
                    $suma += (int) $data['rating'];
                    $total++;
                }
            }
        }

        $promedio = $total > 0 ? round($suma / $total, 1) : 0;

        $tallerRef->set([
            'rating_promedio' => $promedio,
            'total_resenas' => $total,
        ], ['merge' => true]);
    }

    /**
     * Obtiene el folio asociado a un token de calificación.
     */
    private function obtenerFolioDelToken(string $token): ?string
    {
        try {
            $doc = $this->firestoreDb->collection('calificaciones')->document($token)->snapshot();
            if ($doc->exists()) {
                return $doc->data()['folio'] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning('Error obteniendo folio del token: ' . $e->getMessage());
        }
        return null;
    }
}

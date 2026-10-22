<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class DashboardController extends Controller
{
    protected $firestoreDb;

    public function __construct(FirestoreClient $firestoreDb)
    {
        $this->firestoreDb = $firestoreDb;
    }

    private function sanitize(string $value, int $max = 2000): string
    {
        $value = strip_tags($value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = str_replace(['<', '>', '{', '}'], '', $value);
        $value = preg_replace('/\s+/u', ' ', trim($value));
        return mb_substr($value, 0, $max);
    }

    /**
     * Lee el taller con cache de 5 min en sesión.
     */
    private function obtenerTallerCacheado(string $tallerId): ?array
    {
        $cacheKey   = 'taller_raw_' . $tallerId;
        $cacheTsKey = 'taller_raw_ts_' . $tallerId;
        $ttl = 300; // 5 min

        $cache = session($cacheKey);
        $ts = (int) session($cacheTsKey, 0);

        if ($cache && (time() - $ts) < $ttl) {
            return $cache;
        }

        try {
            $doc = $this->firestoreDb->collection('talleres')->document($tallerId)->snapshot();
            if (!$doc->exists()) return null;

            $data = $doc->data();
            $data['id'] = $tallerId;

            session([
                $cacheKey   => $data,
                $cacheTsKey => time(),
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::warning('Error leyendo taller: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Invalida el cache del taller (llamar desde updateInfo, updateHorario, etc.)
     */
    public static function invalidarCacheTaller(string $tallerId): void
    {
        session()->forget(['taller_raw_' . $tallerId, 'taller_raw_ts_' . $tallerId]);
    }

    public function index()
    {
        $user = session('firebase_user');

        $tallerData = [
            'verificado' => false,
            'plan' => 'Sin plan',
            'nombre' => $user['nombre_completo'] ?? 'Mi taller',
            'id' => null,
            'logo_url' => null,
        ];

        $stats = [
            'ordenes_total' => 0,
            'ordenes_hoy'   => 0,
            'activas'       => 0,
            'completadas'   => 0,
            'canceladas'    => 0,
            'pendientes'    => 0,
            'calificacion'  => 0,
            'total_resenas' => 0,
        ];

        $ordenesHoy       = [];
        $ordenesActivas   = [];
        $resenasRecientes = [];

        if (!$this->firestoreDb || !$user) {
            return view('taller.dashboard', compact(
                'tallerData', 'stats', 'ordenesHoy', 'ordenesActivas', 'resenasRecientes'
            ));
        }

        try {
            // ============================================================
            // 1. taller_id desde sesión (ahorra 1 read)
            // ============================================================
            $tallerId = $user['taller_id'] ?? null;

            if (!$tallerId) {
                $usuarioDoc = $this->firestoreDb->collection('usuarios')->document($user['uid'])->snapshot();
                if ($usuarioDoc->exists()) {
                    $tallerId = $usuarioDoc->data()['taller_id'] ?? null;
                    if ($tallerId) {
                        $sessionData = session('firebase_user');
                        $sessionData['taller_id'] = $tallerId;
                        session(['firebase_user' => $sessionData]);
                    }
                }
            }

            if (!$tallerId) {
                return view('taller.dashboard', compact(
                    'tallerData', 'stats', 'ordenesHoy', 'ordenesActivas', 'resenasRecientes'
                ));
            }

            // ============================================================
            // 2. Taller cacheado (ahorra 1 read si hay cache)
            // ============================================================
            $raw = $this->obtenerTallerCacheado($tallerId);

            if ($raw) {
                $plan = $raw['plan'] ?? null;
                if (is_array($plan)) {
                    $planNombre = $plan['nombre'] ?? $plan['name'] ?? $plan['titulo'] ?? 'Sin plan';
                } elseif (is_string($plan) && $plan !== '') {
                    $planNombre = $plan;
                } else {
                    $planNombre = 'Sin plan';
                }

                $logoRaw = $raw['logo_url'] ?? null;
                $logoValido = $logoRaw && preg_match('#^https?://#i', $logoRaw) ? $logoRaw : null;

                $tallerData = [
                    'verificado' => (bool) ($raw['verificado'] ?? false),
                    'plan'       => $this->sanitize($planNombre, 80),
                    'nombre'     => $this->sanitize($raw['nombre'] ?? $user['nombre_completo'] ?? 'Mi taller', 150),
                    'id'         => $tallerId,
                    'logo_url'   => $logoValido,
                ];

                session(['taller_data' => $tallerData]);
                session(['taller_data_raw' => $raw]);

                // 🔥 FIX CLAVE: usar los contadores ya guardados en el taller
                $stats['total_resenas'] = (int) ($raw['total_resenas'] ?? 0);
                $stats['calificacion']  = (float) ($raw['rating_promedio'] ?? 0);
            }

            // ============================================================
            // 3. Stats de órdenes (contadores incrementales o query acotada)
            // ============================================================
            // Si ya mantienes stats_ordenes en el taller, úsalos:
            if (!empty($raw['stats_ordenes']) && is_array($raw['stats_ordenes'])) {
                $stats['ordenes_total'] = (int) ($raw['stats_ordenes']['total'] ?? 0);
                $stats['pendientes']    = (int) ($raw['stats_ordenes']['pendientes'] ?? 0);
                $stats['activas']       = (int) ($raw['stats_ordenes']['activas'] ?? 0);
                $stats['completadas']   = (int) ($raw['stats_ordenes']['completadas'] ?? 0);
                $stats['canceladas']    = (int) ($raw['stats_ordenes']['canceladas'] ?? 0);
            }

            // ============================================================
            // 4. Órdenes activas — solo las 5 más recientes
            // ============================================================
            $activas = collect();
            foreach (['Pendiente', 'En reparación'] as $estado) {
                try {
                    $snap = $this->firestoreDb
                        ->collection('ordenes_trabajo')
                        ->where('taller_id', '=', $tallerId)
                        ->where('estado', '=', $estado)
                        ->orderBy('fecha_orden', 'desc')
                        ->limit(5)
                        ->documents();

                    foreach ($snap as $doc) {
                        if (!$doc->exists()) continue;
                        $activas->push(array_merge(['id' => $doc->id()], $doc->data()));
                    }
                } catch (\Exception $e) {
                    Log::warning("Error cargando activas ($estado): " . $e->getMessage());
                }
            }

            $ordenesActivas = $activas
                ->sortByDesc(fn($o) => strtotime($o['fecha_orden'] ?? '0'))
                ->take(5)
                ->map(function ($o) {
                    $o['folio'] = $this->sanitize($o['folio'] ?? '', 40);
                    $o['cliente_nombre'] = $this->sanitize($o['cliente_nombre'] ?? '', 150);
                    if (!empty($o['vehiculo']) && is_array($o['vehiculo'])) {
                        $o['vehiculo']['marca']  = $this->sanitize($o['vehiculo']['marca'] ?? '', 80);
                        $o['vehiculo']['modelo'] = $this->sanitize($o['vehiculo']['modelo'] ?? '', 80);
                        $o['vehiculo']['placas'] = strtoupper($this->sanitize($o['vehiculo']['placas'] ?? '', 20));
                        $o['vehiculo']['color']  = $this->sanitize($o['vehiculo']['color'] ?? '', 50);
                    }
                    return $o;
                })
                ->values()
                ->all();

            // ============================================================
            // 5. Órdenes de hoy — query con rango de fechas
            // ============================================================
            $hoyInicio = now()->startOfDay()->format('Y-m-d\TH:i:s');
            $hoyFin    = now()->endOfDay()->format('Y-m-d\TH:i:s');

            try {
                $hoySnap = $this->firestoreDb
                    ->collection('ordenes_trabajo')
                    ->where('taller_id', '=', $tallerId)
                    ->where('fecha_orden', '>=', $hoyInicio)
                    ->where('fecha_orden', '<=', $hoyFin)
                    ->orderBy('fecha_orden', 'desc')
                    ->limit(20)
                    ->documents();

                foreach ($hoySnap as $doc) {
                    if (!$doc->exists()) continue;
                    $data = $doc->data();
                    $data['id'] = $doc->id();
                    $data['folio'] = $this->sanitize($data['folio'] ?? '', 40);
                    $data['cliente_nombre'] = $this->sanitize($data['cliente_nombre'] ?? '', 150);
                    if (!empty($data['vehiculo']) && is_array($data['vehiculo'])) {
                        $data['vehiculo']['marca']  = $this->sanitize($data['vehiculo']['marca'] ?? '', 80);
                        $data['vehiculo']['modelo'] = $this->sanitize($data['vehiculo']['modelo'] ?? '', 80);
                        $data['vehiculo']['placas'] = strtoupper($this->sanitize($data['vehiculo']['placas'] ?? '', 20));
                    }
                    $ordenesHoy[] = $data;
                }

                $stats['ordenes_hoy'] = count($ordenesHoy);
            } catch (\Exception $e) {
                Log::warning('Error cargando órdenes de hoy: ' . $e->getMessage());
            }

            // ============================================================
            // 6. Reseñas — solo las últimas 5 (promedio ya viene del taller)
            // ============================================================
            try {
                $resenasSnap = $this->firestoreDb
                    ->collection('talleres')
                    ->document($tallerId)
                    ->collection('resenas')
                    ->orderBy('fecha', 'desc')
                    ->limit(5)
                    ->documents();

                foreach ($resenasSnap as $doc) {
                    if (!$doc->exists()) continue;
                    $r = $doc->data();
                    $r['id'] = $doc->id();

                    $r['usuario_nombre']   = $this->sanitize($r['usuario_nombre'] ?? '', 150);
                    $r['comentario']       = $this->sanitize($r['comentario'] ?? '', 1000);
                    $r['respuesta_taller'] = !empty($r['respuesta_taller'])
                        ? $this->sanitize($r['respuesta_taller'], 1000)
                        : null;
                    $r['usuario_inicial']  = $this->sanitize($r['usuario_inicial'] ?? '', 3);

                    $resenasRecientes[] = $r;
                }
            } catch (\Exception $e) {
                Log::warning('Error cargando reseñas: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('Error en DashboardController: ' . $e->getMessage());
        }

        return view('taller.dashboard', compact(
            'tallerData', 'stats', 'ordenesHoy', 'ordenesActivas', 'resenasRecientes'
        ));
    }
}

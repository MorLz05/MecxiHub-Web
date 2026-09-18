<?php

namespace App\Services;

use Carbon\Carbon;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    protected $firestoreDb;

    public function __construct(?FirestoreClient $firestoreDb = null)
    {
        if ($firestoreDb) {
            $this->firestoreDb = $firestoreDb;
            return;
        }

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
            Log::error('NotificacionService init error: ' . $e->getMessage());
        }
    }

    /**
     * Crea una notificación para un usuario.
     */
    public function crear(string $usuarioId, array $data): ?string
    {
        if (!$this->firestoreDb || !$usuarioId) {
            return null;
        }

        try {
            $doc = $this->firestoreDb->collection('notificaciones')->add(array_merge([
                'usuario_id' => $usuarioId,
                'titulo'     => 'Notificación',
                'mensaje'    => '',
                'tipo'       => 'general',
                'icono'      => 'fa-solid fa-bell',
                'color'      => 'blue',
                'leida'      => false,
                'fecha'      => Carbon::now()->toIso8601String(),
            ], $data));

            return $doc->id();
        } catch (\Exception $e) {
            Log::error('Error creando notificación: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Notificación: servicio listo (con opción de calificar).
     */
    public function notificarServicioListo(string $usuarioId, string $tallerId, string $tallerNombre, string $folio, ?string $token = null)
    {
        $url = null;
        if ($token) {
            $url = route('calificacion.form', ['token' => $token]);
        }

        return $this->crear($usuarioId, [
            'tipo'               => 'servicio_listo',
            'titulo'             => '¡Tu auto está listo! 🚗',
            'mensaje'            => "El taller {$tallerNombre} terminó el servicio de la orden {$folio}. ¡Cuéntanos cómo te fue!",
            'icono'              => 'fa-solid fa-car-side',
            'color'              => 'orange',
            'taller_id'          => $tallerId,
            'taller_nombre'      => $tallerNombre,
            'folio'              => $folio,
            'calificacion_token' => $token,
            'url'                => $url,
        ]);
    }

    /**
     * Notificación: orden creada (Pendiente / En reparación).
     */
    public function notificarCambioEstado(string $usuarioId, string $tallerId, string $tallerNombre, string $folio, string $estadoNuevo)
    {
        $mapa = [
            'Pendiente'     => [
                'titulo' => 'Servicio registrado 📋',
                'icono'  => 'fa-solid fa-clipboard-list',
                'color'  => 'blue',
                'msg'    => "{$tallerNombre} registró la orden {$folio}. Pronto comenzarán a trabajar en tu auto.",
            ],
            'En reparación' => [
                'titulo' => 'Tu auto está en reparación 🔧',
                'icono'  => 'fa-solid fa-wrench',
                'color'  => 'orange',
                'msg'    => "{$tallerNombre} comenzó a trabajar en la orden {$folio}.",
            ],
            'Listo'         => [
                'titulo' => '¡Tu auto está listo! 🚗',
                'icono'  => 'fa-solid fa-car-side',
                'color'  => 'green',
                'msg'    => "{$tallerNombre} terminó el servicio de la orden {$folio}. ¡Cuéntanos cómo te fue!",
            ],
            'Cancelado'     => [
                'titulo' => 'Servicio cancelado',
                'icono'  => 'fa-solid fa-circle-xmark',
                'color'  => 'red',
                'msg'    => "La orden {$folio} fue cancelada por {$tallerNombre}.",
            ],
        ];

        if (!isset($mapa[$estadoNuevo])) {
            return null;
        }

        $info = $mapa[$estadoNuevo];

        return $this->crear($usuarioId, [
            'tipo'          => 'orden_' . strtolower(str_replace(' ', '_', $estadoNuevo)),
            'titulo'        => $info['titulo'],
            'mensaje'       => $info['msg'],
            'icono'         => $info['icono'],
            'color'         => $info['color'],
            'taller_id'     => $tallerId,
            'taller_nombre' => $tallerNombre,
            'folio'         => $folio,
            'url'           => route('cuenta.servicios.detalle', ['id' => $folio]),
        ]);
    }

    /**
     * Notificación: el taller respondió a tu reseña.
     */
    public function notificarRespuestaTaller(string $usuarioId, string $tallerId, string $tallerNombre, string $resenaId, string $preview)
    {
        return $this->crear($usuarioId, [
            'tipo'          => 'respuesta_taller',
            'titulo'        => 'El taller respondió tu reseña 💬',
            'mensaje'       => "{$tallerNombre} respondió: \"" . mb_substr($preview, 0, 90) . (mb_strlen($preview) > 90 ? '...' : '') . '"',
            'icono'         => 'fa-solid fa-reply',
            'color'         => 'green',
            'taller_id'     => $tallerId,
            'taller_nombre' => $tallerNombre,
            'resena_id'     => $resenaId,
            'url'           => route('taller.perfil', ['id' => $tallerId]) . '#seccion-resenas',
        ]);
    }
}

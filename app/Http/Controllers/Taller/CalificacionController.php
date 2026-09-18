<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Google\Cloud\Firestore\FirestoreClient;

class CalificacionController extends Controller
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
            Log::error('Error inicializando CalificacionController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }
    }

    /**
     * ============================================
     * NUEVO: El cliente hace clic en el enlace del correo.
     * Redirige al perfil del taller con el form de reseña abierto.
     * ============================================
     */
    public function showForm($token)
    {
        if (!$this->firestoreDb) {
            abort(503, 'Servicio no disponible');
        }

        // 1. Buscar el token
        $tokenDoc = $this->firestoreDb->collection('calificaciones')->document($token)->snapshot();

        if (!$tokenDoc->exists()) {
            return redirect()->route('home')
                ->with('error', 'El enlace de calificación no es válido.');
        }

        $data = $tokenDoc->data();

        // 2. Validar expiración
        if (!empty($data['expiresAt'])) {
            try {
                if (Carbon::parse($data['expiresAt'])->isPast()) {
                    return redirect()->route('home')
                        ->with('error', 'Este enlace de calificación ha expirado.');
                }
            } catch (\Exception $e) {
                Log::warning('Error parseando expiresAt: ' . $e->getMessage());
            }
        }

        // 3. Validar que no esté usado
        if (!empty($data['usado'])) {
            // Si ya está usado, igual lo mandamos al perfil (por si quiere ver las reseñas)
            return redirect()->route('taller.perfil', ['id' => $data['taller_id']])
                ->with('info', 'Ya calificaste este servicio. ¡Gracias!');
        }

        // 4. Si no está logueado → guardar token en sesión y mandar a login
        if (!session()->has('firebase_user')) {
            session([
                'calificacion_pendiente_token' => $token,
                'calificacion_pendiente_taller_id' => $data['taller_id'],
            ]);

            return redirect()->route('login')
                ->with('info', 'Inicia sesión o regístrate para calificar tu experiencia.');
        }

        // 5. Ya logueado → validar que el email coincida (opcional, pero buena práctica)
        $userEmail = session('firebase_user.email');
        if (!empty($data['cliente_email']) && $data['cliente_email'] !== $userEmail) {
            // Permitimos continuar pero lo registramos; o puedes bloquearlo
            Log::info("Calificación: el email del token ({$data['cliente_email']}) no coincide con el logueado ({$userEmail})");
        }

        // 6. Redirigir al perfil con flag para abrir el form
        return redirect()->route('taller.perfil', [
            'id' => $data['taller_id'],
            'calificar' => 1,
            'token' => $token,
        ])->withFragment('seccion-resenas');
    }

    /**
     * ============================================
     * NUEVO: el POST del token (no se usa realmente, la reseña se guarda
     * desde PerfilTallerController::storeResena). Aquí solo redirigimos.
     * ============================================
     */
    public function store(Request $request, $token)
    {
        // Redirigir al showForm que ya maneja todo
        return redirect()->route('calificacion.form', ['token' => $token]);
    }

    /**
     * Enviar email de solicitud de calificación al cliente.
     * Se llama cuando la orden pasa a "Listo".
     *
     * @param array $orden  Documento de la orden (ya con fecha_entrega_real)
     * @param string $tallerId
     * @param \Google\Cloud\Firestore\FirestoreClient $firestoreDb
     * @return bool
     */
    public static function enviarSolicitudCalificacion($orden, $tallerId, $firestoreDb)
    {
        try {
            $email = $orden['cliente_email'] ?? null;

            if (empty($email)) {
                Log::info('Orden sin email de cliente, no se envía solicitud de calificación: ' . ($orden['folio'] ?? '?'));
                return false;
            }

            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Email de cliente inválido: ' . $email);
                return false;
            }

            // Obtener datos del taller para personalizar el correo
            $tallerData = [];
            try {
                $tallerDoc = $firestoreDb->collection('talleres')->document($tallerId)->snapshot();
                if ($tallerDoc->exists()) {
                    $tallerData = $tallerDoc->data();
                }
            } catch (\Exception $e) {
                Log::warning('No se pudieron cargar datos del taller para email: ' . $e->getMessage());
            }

            // Generar token único
            $token = Uuid::uuid4()->toString();
            $expiresAt = Carbon::now()->addDays(15);
            $folio = $orden['folio'] ?? '';
            $fechaOrden = $orden['fecha_orden'] ?? Carbon::now()->toDateTimeString();

            // Construir URL de calificación
            $urlCalificacion = route('calificacion.form', [
                'token' => $token,
                'folio' => $folio,
            ]);

            // Guardar token en Firestore
            $docData = [
                'token' => $token,
                'folio' => $folio,
                'taller_id' => $tallerId,
                'taller_nombre' => $tallerData['nombre'] ?? 'Taller',
                'cliente_email' => $email,
                'cliente_nombre' => $orden['cliente_nombre'] ?? '',
                'orden_id' => $orden['id'] ?? $folio,
                'fecha_orden' => $fechaOrden,
                'fecha_entrega_real' => $orden['fecha_entrega_real'] ?? Carbon::now()->toDateTimeString(),
                'createdAt' => Carbon::now()->toIso8601String(),
                'expiresAt' => $expiresAt->toIso8601String(),
                'usado' => false,
            ];

            $firestoreDb->collection('calificaciones')->document($token)->set($docData);

            // Justo después de guardar el token en Firestore:
            try {
                $usuarioId = $orden['cliente_uid'] ?? $orden['usuario_id'] ?? null;

                if ($usuarioId) {
                    $notifService = new \App\Services\NotificacionService($firestoreDb);
                    $notifService->notificarServicioListo(
                        $usuarioId,
                        $tallerId,
                        $tallerData['nombre'] ?? 'el taller',
                        $folio,
                        $token
                    );
                    Log::info('Notificación de servicio listo creada para uid: ' . $usuarioId);
                } else {
                    Log::warning('No se pudo crear notificación: la orden no tiene cliente_uid');
                }
            } catch (\Exception $e) {
                Log::warning('Error creando notificación de servicio listo: ' . $e->getMessage());
            }

            // Guardar referencia del token en la orden
            $firestoreDb
                ->collection('ordenes_trabajo')
                ->document($orden['id'] ?? $folio)
                ->set([
                    'calificacion_token' => $token,
                    'calificacion_expira' => $expiresAt->toIso8601String(),
                    'calificacion_solicitada_en' => Carbon::now()->toIso8601String(),
                ], ['merge' => true]);

            // Enviar el email
            $enviado = self::enviarEmail($email, $token, $folio, $urlCalificacion, $tallerData, $orden);

            if ($enviado) {
                Log::info('Solicitud de calificación enviada a: ' . $email . ' (folio: ' . $folio . ')');

                // Registrar en el log de envíos
                $firestoreDb->collection('calificaciones')->document($token)->set([
                    'email_enviado' => true,
                    'email_enviado_en' => Carbon::now()->toIso8601String(),
                ], ['merge' => true]);
            }

            return $enviado;
        } catch (\Exception $e) {
            Log::error('❌ Error enviando solicitud de calificación: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return false;
        }
    }

    /**
     * Enviar el email con SendGrid
     */
    private static function enviarEmail($email, $token, $folio, $urlCalificacion, $tallerData, $orden)
    {
        try {
            $apiKey = env('SENDGRID_API_KEY');
            if (!$apiKey) {
                throw new \Exception('SENDGRID_API_KEY no configurada');
            }

            $fromEmail = env('MAIL_FROM_ADDRESS', 'soportemecxihub@gmail.com');
            $fromName = env('MAIL_FROM_NAME', 'MecxiHub');

            // Renderizar la vista del email
            $htmlContent = view('emails.solicitud-calificacion', [
                'token' => $token,
                'folio' => $folio,
                'urlCalificacion' => $urlCalificacion,
                'tallerData' => $tallerData,
                'orden' => $orden,
                'clienteNombre' => $orden['cliente_nombre'] ?? '',
            ])->render();

            $asunto = '¿Cómo fue tu experiencia en ' . ($tallerData['nombre'] ?? 'el taller') . '?';

            $data = [
                'personalizations' => [
                    [
                        'to' => [['email' => $email]],
                        'subject' => $asunto,
                    ]
                ],
                'from' => [
                    'email' => $fromEmail,
                    'name' => $fromName,
                ],
                'reply_to' => [
                    'email' => $fromEmail,
                    'name' => $fromName,
                ],
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => $htmlContent,
                    ]
                ],
            ];

            $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                Log::info('Email de calificación enviado (HTTP ' . $httpCode . ')');
                return true;
            }

            Log::error('Error SendGrid: HTTP ' . $httpCode . ' - ' . $response);
            if ($curlError) {
                Log::error('Error cURL: ' . $curlError);
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Error enviando email de calificación: ' . $e->getMessage());
            return false;
        }
    }
}

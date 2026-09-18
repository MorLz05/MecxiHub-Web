<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Google\Cloud\Firestore\FirestoreClient;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Webhook;

class PagoController extends Controller
{
    protected $firestoreDb;

    public function __construct(FirestoreClient $firestoreDb)
    {
        $this->firestoreDb = $firestoreDb;

        // Configurar Stripe
        Stripe::setApiKey(config('services.stripe.secret'));
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
            Log::error('Error inicializando PagoController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }

        // Configurar Stripe
        Stripe::setApiKey(config('services.stripe.secret'));
    } */

    /**
     * Crea una sesión de pago con Stripe Checkout
     * Frontend hace click en "Pagar ahora" → POST aquí → devuelve URL de Stripe
     */
    public function crearSesion(Request $request)
    {
        try {
            $user = session('firebase_user');
            if (!$user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            $validated = $request->validate([
                'plan_id' => 'required|string',
            ]);

            $planId = $validated['plan_id'];

            // 1. Obtener datos del plan
            $planDoc = $this->firestoreDb->collection('planes')->document($planId)->snapshot();
            if (!$planDoc->exists()) {
                return response()->json(['error' => 'Plan no encontrado'], 404);
            }
            $plan = $planDoc->data();
            $plan['id'] = $planDoc->id();

            // Validar que esté activo
            if (!($plan['activo'] ?? true)) {
                return response()->json(['error' => 'Este plan no está disponible'], 400);
            }

            // 2. Obtener taller del usuario
            $usuarioDoc = $this->firestoreDb->collection('usuarios')->document($user['uid'])->snapshot();
            if (!$usuarioDoc->exists()) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
            $tallerId = $usuarioDoc->data()['taller_id'] ?? null;
            if (!$tallerId) {
                return response()->json(['error' => 'No tienes un taller asignado'], 400);
            }

            // 3. Calcular precio en centavos
            $moneda = strtoupper($plan['moneda'] ?? 'MXN');
            $precioCentavos = (int) round(($plan['precio'] ?? 0) * 100);

            if ($precioCentavos <= 0) {
                return response()->json(['error' => 'Precio inválido'], 400);
            }

            // 4. Crear sesión de Checkout en Stripe
            $session = CheckoutSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($moneda),
                        'unit_amount' => $precioCentavos,
                        'product_data' => [
                            'name' => $plan['nombre'] ?? 'Plan',
                            'description' => $plan['descripcion'] ?? '',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('taller.planes.exito') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('taller.planes') . '?cancelado=1',
                'customer_email' => $user['email'] ?? null,
                'metadata' => [
                    'plan_id' => $planId,
                    'taller_id' => $tallerId,
                    'uid' => $user['uid'],
                ],
                'client_reference_id' => $tallerId,
            ]);

            // 5. Guardar registro temporal en Firestore
            $this->firestoreDb->collection('pagos_pendientes')->document($session->id)->set([
                'session_id' => $session->id,
                'plan_id' => $planId,
                'plan_nombre' => $plan['nombre'] ?? '',
                'plan_precio' => $plan['precio'] ?? 0,
                'moneda' => $moneda,
                'taller_id' => $tallerId,
                'uid' => $user['uid'],
                'email' => $user['email'] ?? '',
                'estado' => 'pendiente',
                'fecha_creacion' => Carbon::now()->toIso8601String(),
            ]);

            return response()->json([
                'success' => true,
                'url' => $session->url,
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creando sesión de pago: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Página de éxito (Stripe redirige aquí después del pago)
     */
    public function exito(Request $request)
    {
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            try {
                // Verificar la sesión con Stripe
                $session = CheckoutSession::retrieve($sessionId);

                if ($session->payment_status === 'paid') {
                    // El webhook también se dispara, pero este es un "backup"
                    $this->procesarPagoExitoso($session);

                    return redirect()
                        ->route('taller.planes')
                        ->with('success', '¡Pago procesado exitosamente! Tu plan ya está activo.');
                }
            } catch (\Exception $e) {
                Log::error('Error verificando sesión: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('taller.planes')
            ->with('success', 'Pago procesado. Actualizando tu plan...');
    }

    /**
     * Webhook de Stripe (para confirmar pago desde el servidor)
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Webhook signature error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Manejar el evento
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                if ($session->payment_status === 'paid') {
                    $this->procesarPagoExitoso($session);
                }
                break;

            case 'checkout.session.expired':
                $session = $event->data->object;
                try {
                    $this->firestoreDb->collection('pagos_pendientes')->document($session->id)->set([
                        'estado' => 'expirado',
                    ], ['merge' => true]);
                } catch (\Exception $e) {
                }
                break;
        }

        return response()->json(['received' => true]);
    }

    /**
     * Procesar pago exitoso: actualizar plan del taller
     */
    private function procesarPagoExitoso($session)
    {
        try {
            $metadata = $session->metadata ?? null;
            if (!$metadata) {
                Log::warning('Sesión sin metadata: ' . $session->id);
                return;
            }

            $planId = $metadata->plan_id ?? null;
            $tallerId = $metadata->taller_id ?? null;

            if (!$planId || !$tallerId) {
                Log::warning('Faltan datos en metadata: ' . json_encode($metadata));
                return;
            }

            // Idempotencia: si ya se procesó, no repetir
            $pagoDoc = $this->firestoreDb->collection('pagos_pendientes')->document($session->id)->snapshot();
            if ($pagoDoc->exists() && ($pagoDoc->data()['estado'] ?? '') === 'completado') {
                Log::info('Pago ya procesado: ' . $session->id);
                return;
            }

            // Cargar plan
            $planDoc = $this->firestoreDb->collection('planes')->document($planId)->snapshot();
            if (!$planDoc->exists()) {
                Log::error('Plan no encontrado en procesar pago: ' . $planId);
                return;
            }
            $plan = $planDoc->data();

            // Calcular fecha de vencimiento
            $dias = (int) ($plan['duracion_dias'] ?? 30);
            $fechaVencimiento = Carbon::now()->addDays($dias);

            // Actualizar el taller
            $this->firestoreDb->collection('talleres')->document($tallerId)->set([
                'plan' => [
                    'id' => $planId,
                    'nombre' => $plan['nombre'] ?? 'Plan',
                    'estado' => 'activo',
                    'fecha_inicio' => Carbon::now()->toIso8601String(),
                    'fecha_vencimiento' => $fechaVencimiento->toIso8601String(),
                    'precio' => $plan['precio'] ?? 0,
                    'moneda' => $plan['moneda'] ?? 'MXN',
                ],
                'fecha_actualizacion_plan' => Carbon::now()->toIso8601String(),
            ], ['merge' => true]);

            // Registrar el pago completado
            $this->firestoreDb->collection('pagos_pendientes')->document($session->id)->set([
                'estado' => 'completado',
                'fecha_completado' => Carbon::now()->toIso8601String(),
                'stripe_payment_intent' => $session->payment_intent ?? null,
                'stripe_customer' => $session->customer ?? null,
                'amount_total' => $session->amount_total ?? 0,
                'currency' => $session->currency ?? '',
            ], ['merge' => true]);

            // También guardarlo en historial de pagos
            $this->firestoreDb->collection('pagos')->document($session->id)->set([
                'session_id' => $session->id,
                'taller_id' => $tallerId,
                'plan_id' => $planId,
                'plan_nombre' => $plan['nombre'] ?? '',
                'monto' => ($session->amount_total ?? 0) / 100,
                'moneda' => strtoupper($session->currency ?? 'MXN'),
                'stripe_payment_intent' => $session->payment_intent ?? null,
                'email' => $session->customer_details->email ?? '',
                'estado' => 'completado',
                'fecha_pago' => Carbon::now()->toIso8601String(),
                'fecha_vencimiento_plan' => $fechaVencimiento->toIso8601String(),
            ]);

            Log::info('✅ Pago procesado: ' . $session->id . ' para taller: ' . $tallerId);
        } catch (\Exception $e) {
            Log::error('Error procesando pago exitoso: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}

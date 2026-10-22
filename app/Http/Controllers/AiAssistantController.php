<?php

namespace App\Http\Controllers;

use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AiAssistantController extends Controller
{
    public function __construct(private readonly FirestoreClient $firestore)
    {
    }

    public function context(Request $request): JsonResponse
    {
        $user = session('firebase_user');
        if (!$user || empty($user['uid'])) {
            return response()->json(['message' => 'Inicia sesión para usar MecxiBot.'], 401);
        }
        $validated = $request->validate([
            'message' => 'nullable|string|max:2000',
        ]);
        $message = $validated['message'] ?? '';

        try {
            if (($user['rol'] ?? '') === 'Conductor') {
                return response()->json(['context' => $this->conductorContext($user['uid'], $message)]);
            }

            if (($user['rol'] ?? '') === 'Administrador') {
                return response()->json(['context' => $this->workshopContext($user['uid'], $message)]);
            }

            return response()->json(['message' => 'Tu cuenta no tiene acceso a este asistente.'], 403);
        } catch (\Throwable $exception) {
            Log::error('Error al preparar contexto de MecxiBot.', [
                'uid' => $user['uid'],
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'No se pudo consultar la información autorizada. Intenta de nuevo.',
            ], 503);
        }
    }

    public function confirmOrderAction(Request $request): JsonResponse
    {
        $user = session('firebase_user');
        if (!$user || empty($user['uid'])) {
            return response()->json(['message' => 'Inicia sesión para continuar.'], 401);
        }
        if (($user['rol'] ?? '') !== 'Administrador') {
            return response()->json(['message' => 'Solo el personal del taller puede gestionar órdenes.'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:create_order_draft,update_order_draft',
            'arguments' => 'required|array',
        ]);

        try {
            $workshopId = $this->authorizedWorkshopId($user['uid']);
            $result = $validated['type'] === 'create_order_draft'
                ? $this->createOrder($workshopId, $user['uid'], $validated['arguments'])
                : $this->updateOrder($workshopId, $validated['arguments']);

            return response()->json(['message' => $result]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (\Throwable $exception) {
            Log::error('Error al confirmar propuesta de orden de MecxiBot.', [
                'uid' => $user['uid'],
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'No se pudo guardar la orden. No se confirmó ningún cambio.',
            ], 500);
        }
    }

    private function conductorContext(string $uid, string $message): string
    {
        $vehicles = [];
        $vehicleIds = [];
        $vehicleSnapshots = $this->firestore->collection('vehiculos')
            ->where('usuario_id', '=', $uid)
            ->documents();

        foreach ($vehicleSnapshots as $document) {
            if (!$document->exists()) {
                continue;
            }
            $data = $document->data();
            $id = $document->id();
            $vehicleIds[] = $id;
            $vehicles[$id] = [
                'id' => $id,
                'marca' => $data['marca'] ?? '',
                'modelo' => $data['modelo'] ?? '',
                'año' => $data['año'] ?? '',
                'kilometraje_actual' => $data['kilometraje_actual'] ?? null,
                'ultimo_servicio' => $this->jsonSafeValue($data['ultimo_servicio'] ?? null),
            ];
        }

        $orders = [];
        $ordersSnapshot = $this->firestore->collection('ordenes_trabajo')
            ->where('cliente_uid', '=', $uid)
            ->limit(20)
            ->documents();
        foreach ($ordersSnapshot as $document) {
            if (!$document->exists()) {
                continue;
            }
            $order = $document->data();
            $orders[] = $this->safeOrderSummary($order);
        }

        $services = [];
        foreach ($vehicleIds as $vehicleId) {
            $snapshot = $this->firestore->collection('servicios')
                ->where('vehiculo_id', '=', $vehicleId)
                ->limit(30)
                ->documents();
            foreach ($snapshot as $document) {
                if (!$document->exists()) {
                    continue;
                }
                $service = $document->data();
                $services[] = [
                    'fecha' => $this->jsonSafeValue($service['fecha_servicio'] ?? $service['fecha'] ?? null),
                    'tipo' => $service['tipo'] ?? $service['servicio'] ?? null,
                    'descripcion' => $service['descripcion'] ?? null,
                    'vehiculo' => $vehicles[$vehicleId]['marca'] . ' ' . $vehicles[$vehicleId]['modelo'],
                ];
            }
        }

        return json_encode([
            'tipo' => 'conductor',
            'vehiculos_de_la_cuenta' => array_values($vehicles),
            'ordenes_vinculadas' => array_slice($orders, 0, 20),
            'servicios_de_vehiculos_de_la_cuenta' => array_slice($services, 0, 30),
            'talleres_publicos' => $this->publicWorkshopDirectory(),
            'instruccion' => 'Los vehículos, órdenes y servicios pertenecen exclusivamente al usuario autenticado. Nunca infieras ni reveles datos de otros usuarios ni órdenes privadas de talleres. La ausencia de vehículos, órdenes o servicios solo significa que no hay registros previos en la cuenta; no impide responder la consulta actual ni analizar los adjuntos recibidos. No sugieras que el conductor registre por su cuenta un vehículo o servicio. Si quiere que su vehículo aparezca en su historial, puede llevarlo a un taller para que realice el servicio; el taller registra la orden/servicio y entonces aparecerá en los servicios recientes de la cuenta. talleres_publicos contiene únicamente información pública de talleres; si preguntan qué talleres existen o cuáles atienden una especialidad, responde solo con nombres y especialidades presentes ahí. No inventes talleres ni atribuyas servicios que no estén listados. Si la lista está vacía o no hay coincidencias, dilo claramente.',
            'consulta' => mb_substr($message, 0, 1000),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function publicWorkshopDirectory(): array
    {
        $workshops = [];
        foreach ($this->firestore->collection('talleres')->documents() as $document) {
            if (!$document->exists()) {
                continue;
            }

            $data = $document->data();
            $services = [];
            $registeredServices = $data['servicios_count'] ?? [];
            foreach (is_array($registeredServices) ? $registeredServices : [] as $service) {
                if (!is_array($service) || (isset($service['activo']) && !$service['activo'])) {
                    continue;
                }
                $name = trim(strip_tags((string) ($service['nombre'] ?? '')));
                if ($name !== '') {
                    $services[] = mb_substr($name, 0, 100);
                }
            }

            $workshops[] = [
                'nombre' => mb_substr(trim(strip_tags((string) ($data['nombre'] ?? 'Taller'))), 0, 120),
                'especialidades_y_servicios' => array_values(array_unique($services)),
                'direccion' => mb_substr(trim(strip_tags((string) ($data['direccion'] ?? ''))), 0, 180),
                'calificacion' => (float) ($data['rating_promedio'] ?? $data['calificacion_promedio'] ?? 0),
                'verificado' => (bool) ($data['verificado'] ?? false),
                'perfil' => route('taller.perfil', ['id' => $document->id()]),
            ];
        }

        return $workshops;
    }

    private function workshopContext(string $uid, string $message): string
    {
        $profile = $this->firestore->collection('usuarios')->document($uid)->snapshot();
        if (!$profile->exists()) {
            throw new \RuntimeException('No se encontró el perfil autorizado.');
        }
        $workshopId = $profile->data()['taller_id'] ?? null;
        if (!is_string($workshopId) || trim($workshopId) === '') {
            throw new \RuntimeException('El usuario no tiene taller asignado.');
        }

        $orders = [];
        $snapshot = $this->firestore->collection('ordenes_trabajo')
            ->where('taller_id', '=', $workshopId)
            ->limit(50)
            ->documents();
        foreach ($snapshot as $document) {
            if (!$document->exists()) {
                continue;
            }
            $orders[] = $this->safeOrderSummary($document->data());
        }

        return json_encode([
            'tipo' => 'taller',
            'ordenes_del_taller_autorizado' => array_slice($orders, 0, 50),
            'instruccion' => 'Este contexto contiene únicamente órdenes del taller del usuario autenticado. No incluye datos de contacto de clientes. No afirmes haber consultado información que no aparece aquí.',
            'consulta' => mb_substr($message, 0, 1000),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function safeOrderSummary(array $order): array
    {
        $vehicle = $order['vehiculo'] ?? [];
        $budget = $order['presupuesto'] ?? [];
        $faults = $order['fallas_multiples'] ?? (isset($order['falla']) ? [$order['falla']] : []);

        return [
            'folio' => $order['folio'] ?? null,
            'estado' => $order['estado'] ?? null,
            'fecha_orden' => $this->jsonSafeValue($order['fecha_orden'] ?? null),
            'fecha_entrega_estimada' => $this->jsonSafeValue($order['fecha_entrega_estimada'] ?? null),
            'vehiculo' => is_array($vehicle) ? array_intersect_key($vehicle, array_flip(['marca', 'modelo', 'anio', 'año', 'kilometraje'])) : [],
            'fallas' => is_array($faults) ? array_map(static function ($fault) {
                if (!is_array($fault)) {
                    return [];
                }
                return array_intersect_key($fault, array_flip(['categoria', 'descripcion', 'prioridad']));
            }, $faults) : [],
            'presupuesto' => is_array($budget) ? array_intersect_key($budget, array_flip(['costo_estimado', 'anticipo'])) : [],
        ];
    }

    private function jsonSafeValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }
        if ($value instanceof \JsonSerializable) {
            return $this->jsonSafeValue($value->jsonSerialize());
        }
        if (is_array($value)) {
            return array_map(fn ($item) => $this->jsonSafeValue($item), $value);
        }
        return is_scalar($value) || $value === null ? $value : (string) $value;
    }

    private function authorizedWorkshopId(string $uid): string
    {
        $profile = $this->firestore->collection('usuarios')->document($uid)->snapshot();
        $workshopId = $profile->exists() ? ($profile->data()['taller_id'] ?? null) : null;
        if (!is_string($workshopId) || trim($workshopId) === '') {
            throw new \InvalidArgumentException('No se encontró un taller autorizado para esta cuenta.');
        }
        return $workshopId;
    }

    private function createOrder(string $workshopId, string $uid, array $arguments): string
    {
        $validator = Validator::make($arguments, [
            'client_name' => ['required', 'string', 'min:2', 'max:150', 'regex:/^[^\<\>\{\}]*$/u'],
            'client_phone' => ['nullable', 'string', 'regex:/^[\d\s\+\-\(\)]{7,20}$/'],
            'client_email' => 'nullable|email|max:150',
            'make' => ['required', 'string', 'max:80', 'regex:/^[^\<\>\{\}]*$/u'],
            'model' => ['required', 'string', 'max:80', 'regex:/^[^\<\>\{\}]*$/u'],
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'plates' => ['required', 'string', 'max:20', 'regex:/^[^\<\>\{\}]*$/u'],
            'mileage' => 'required|integer|min:0',
            'color' => ['nullable', 'string', 'max:50', 'regex:/^[^\<\>\{\}]*$/u'],
            'faults' => 'required|array|min:1',
            'faults.*.category' => ['required', 'string', 'max:100', 'regex:/^[^\<\>\{\}]*$/u'],
            'faults.*.description' => ['required', 'string', 'max:500', 'regex:/^[^\<\>\{\}]*$/u'],
            'faults.*.priority' => 'nullable|in:Baja,Media,Alta,Urgente',
            'faults.*.price' => 'nullable|numeric|min:0',
            'estimated_cost' => 'required|numeric|min:0',
            'advance' => 'required|numeric|min:0',
            'entry_date' => 'nullable|date',
            'delivery_date' => 'required|date|after_or_equal:today',
            'priority' => 'nullable|in:Baja,Media,Alta,Urgente',
            'payment_method' => 'nullable|in:Efectivo,Tarjeta de Débito/Crédito,Transferencia',
        ]);
        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
        $data = $validator->validated();
        if ($data['advance'] > $data['estimated_cost']) {
            throw new \InvalidArgumentException('El anticipo no puede ser mayor al costo estimado.');
        }
        if (isset($data['entry_date']) && \Carbon\Carbon::parse($data['delivery_date'])->lt(\Carbon\Carbon::parse($data['entry_date']))) {
            throw new \InvalidArgumentException('La fecha de entrega no puede ser anterior a la fecha de ingreso.');
        }

        $clientUid = null;
        $clientVehicleId = null;
        if (!empty($data['client_email'])) {
            $clientSnapshot = $this->firestore->collection('usuarios')
                ->where('email', '=', trim($data['client_email']))
                ->where('rol', '=', 'Conductor')
                ->limit(1)
                ->documents();
            foreach ($clientSnapshot as $clientDocument) {
                if ($clientDocument->exists()) {
                    $clientUid = $clientDocument->data()['uid'] ?? $clientDocument->id();
                    break;
                }
            }

            if ($clientUid) {
                $vehicleSnapshot = $this->firestore->collection('vehiculos')
                    ->where('usuario_id', '=', $clientUid)
                    ->where('placas', '=', strtoupper(trim($data['plates'])))
                    ->limit(1)
                    ->documents();
                foreach ($vehicleSnapshot as $vehicleDocument) {
                    if ($vehicleDocument->exists()) {
                        $clientVehicleId = $vehicleDocument->id();
                        break;
                    }
                }
            }
        }

        $folio = $this->generateOrderFolio($workshopId);
        $faults = array_map(static fn ($fault) => [
            'categoria' => $fault['category'],
            'descripcion' => $fault['description'],
            'prioridad' => $fault['priority'] ?? 'Media',
            'precio' => (float) ($fault['price'] ?? 0),
        ], $data['faults']);
        $now = now();
        $entryDate = isset($data['entry_date']) ? \Carbon\Carbon::parse($data['entry_date']) : $now;

        $this->firestore->collection('ordenes_trabajo')->document($folio)->set([
            'folio' => $folio,
            'taller_id' => $workshopId,
            'estado' => 'Pendiente',
            'cliente_nombre' => $data['client_name'],
            'cliente_telefono' => preg_replace('/[^\d\s\+\-\(\)]/', '', $data['client_phone'] ?? ''),
            'cliente_email' => $data['client_email'] ?? '',
            'cliente_uid' => $clientUid,
            'cliente_registrado' => $clientUid !== null,
            'vehiculo_cliente_id' => $clientVehicleId,
            'vehiculo' => [
                'marca' => $data['make'],
                'modelo' => $data['model'],
                'anio' => (int) $data['year'],
                'color' => $data['color'] ?? '',
                'placas' => strtoupper($data['plates']),
                'kilometraje' => (int) $data['mileage'],
            ],
            'fallas_multiples' => $faults,
            'presupuesto' => [
                'anticipo' => (float) $data['advance'],
                'costo_estimado' => (float) $data['estimated_cost'],
                'metodo_pago' => $data['payment_method'] ?? 'Efectivo',
            ],
            'precio_final' => (float) $data['estimated_cost'],
            'fecha_orden' => $entryDate->toDateTimeString(),
            'fecha_entrega_estimada' => \Carbon\Carbon::parse($data['delivery_date'])->toDateTimeString(),
            'asignacion' => [
                'modo' => 'none',
                'responsable_general' => null,
            ],
            'creado_por' => $uid,
            'fecha_creacion' => $now->toDateTimeString(),
        ]);

        if ($clientUid) {
            try {
                $workshop = $this->firestore->collection('talleres')->document($workshopId)->snapshot();
                $workshopName = $workshop->exists() ? ($workshop->data()['nombre'] ?? 'El taller') : 'El taller';
                (new \App\Services\NotificacionService($this->firestore))->crear($clientUid, [
                    'tipo' => 'orden_creada',
                    'titulo' => 'Nuevo servicio registrado 📋',
                    'mensaje' => "{$workshopName} creó la orden {$folio} para tu {$data['make']} {$data['model']} (" . strtoupper($data['plates']) . "). Sigue el progreso desde tu cuenta.",
                    'icono' => 'fa-solid fa-clipboard-list',
                    'color' => 'blue',
                    'taller_id' => $workshopId,
                    'taller_nombre' => $workshopName,
                    'orden_id' => $folio,
                    'folio' => $folio,
                    'url' => route('cuenta.servicios.detalle', ['id' => $folio]),
                ]);
            } catch (\Throwable $exception) {
                Log::warning('No se pudo notificar al conductor por una orden creada con MecxiBot.', [
                    'uid' => $uid,
                    'exception' => $exception::class,
                ]);
            }
        }
        \Illuminate\Support\Facades\Cache::flush();

        return "La orden $folio se creó y guardó correctamente. Entra a la sección Órdenes para completar cualquier dato que haya quedado pendiente y verifica que el registro aparezca con la información correcta.";
    }

    private function updateOrder(string $workshopId, array $arguments): string
    {
        $folio = $arguments['folio'] ?? null;
        $changes = $arguments['changes'] ?? null;
        if (!is_string($folio) || trim($folio) === '' || !is_array($changes) || $changes === []) {
            throw new \InvalidArgumentException('La propuesta de edición no es válida.');
        }
        if (array_diff(array_keys($changes), ['vehicle', 'faults', 'dates', 'budget'])) {
            throw new \InvalidArgumentException('Solo se permite cambiar vehículo, fallas, fechas y presupuesto.');
        }

        $matches = [];
        foreach ($this->firestore->collection('ordenes_trabajo')->where('taller_id', '=', $workshopId)->documents() as $document) {
            if ($document->exists() && strcasecmp((string) ($document->data()['folio'] ?? ''), trim($folio)) === 0) {
                $matches[] = $document;
            }
        }
        if (count($matches) !== 1) {
            throw new \InvalidArgumentException(count($matches) === 0
                ? 'No se encontró esa orden dentro de tu taller.'
                : 'El folio no identifica una orden única. No se realizó ningún cambio.');
        }

        $order = $matches[0]->data();
        if (in_array($order['estado'] ?? '', ['Cancelado', 'Listo'], true)) {
            throw new \InvalidArgumentException('No se pueden editar órdenes canceladas o listas.');
        }

        $updates = [];
        $vehicleFields = ['marca', 'modelo', 'anio', 'placas', 'kilometraje', 'color'];
        if (isset($changes['vehicle'])) {
            if (!is_array($changes['vehicle']) || array_diff(array_keys($changes['vehicle']), $vehicleFields)) {
                throw new \InvalidArgumentException('La propuesta contiene campos de vehículo no permitidos.');
            }
            $vehicle = $this->validateActionData($changes['vehicle'], [
                'marca' => ['sometimes', 'required', 'string', 'max:80', 'regex:/^[^\<\>\{\}]*$/u'],
                'modelo' => ['sometimes', 'required', 'string', 'max:80', 'regex:/^[^\<\>\{\}]*$/u'],
                'anio' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
                'placas' => ['sometimes', 'required', 'string', 'max:20', 'regex:/^[^\<\>\{\}]*$/u'],
                'kilometraje' => 'sometimes|required|integer|min:0',
                'color' => ['sometimes', 'nullable', 'string', 'max:50', 'regex:/^[^\<\>\{\}]*$/u'],
            ]);
            foreach ($vehicle as $field => $value) {
                $updates["vehiculo.$field"] = $field === 'placas' ? strtoupper(trim((string) $value)) : $value;
            }
        }
        if (isset($changes['faults'])) {
            if (!is_array($changes['faults']) || $changes['faults'] === []) {
                throw new \InvalidArgumentException('La orden debe conservar al menos una falla.');
            }
            $faults = $this->validateActionData(['faults' => $changes['faults']], [
                'faults' => 'required|array|min:1',
                'faults.*.category' => ['required', 'string', 'max:100', 'regex:/^[^\<\>\{\}]*$/u'],
                'faults.*.description' => ['required', 'string', 'max:500', 'regex:/^[^\<\>\{\}]*$/u'],
            ])['faults'];
            $currentFaults = $order['fallas_multiples'] ?? (isset($order['falla']) ? [$order['falla']] : []);
            $updates['fallas_multiples'] = array_map(static function ($fault, $index) use ($currentFaults) {
                $current = $currentFaults[$index] ?? [];
                return [
                    'categoria' => $fault['category'],
                    'descripcion' => $fault['description'],
                    'prioridad' => $current['prioridad'] ?? 'Media',
                    'precio' => (float) ($current['precio'] ?? 0),
                    'responsable' => $current['responsable'] ?? null,
                ];
            }, $changes['faults'], array_keys($changes['faults']));
        }
        if (isset($changes['dates'])) {
            if (!is_array($changes['dates']) || array_diff(array_keys($changes['dates']), ['entry_date', 'delivery_date'])) {
                throw new \InvalidArgumentException('La propuesta contiene fechas no permitidas.');
            }
            $dates = $this->validateActionData($changes['dates'], [
                'entry_date' => 'sometimes|date',
                'delivery_date' => 'sometimes|date|after_or_equal:today',
            ]);
            $entryDate = $dates['entry_date'] ?? ($order['fecha_orden'] ?? null);
            $deliveryDate = $dates['delivery_date'] ?? ($order['fecha_entrega_estimada'] ?? null);
            if ($entryDate && $deliveryDate && \Carbon\Carbon::parse($deliveryDate)->lt(\Carbon\Carbon::parse($entryDate))) {
                throw new \InvalidArgumentException('La fecha de entrega no puede ser anterior a la fecha de ingreso.');
            }
            foreach ($dates as $key => $value) {
                $updates[$key === 'entry_date' ? 'fecha_orden' : 'fecha_entrega_estimada'] =
                    \Carbon\Carbon::parse($value)->toDateTimeString();
            }
        }
        if (isset($changes['budget'])) {
            $allowed = ['estimated_cost', 'advance', 'payment_method'];
            if (!is_array($changes['budget']) || array_diff(array_keys($changes['budget']), $allowed)) {
                throw new \InvalidArgumentException('La propuesta contiene campos de presupuesto no permitidos.');
            }
            $budget = $this->validateActionData($changes['budget'], [
                'estimated_cost' => 'sometimes|required|numeric|min:0',
                'advance' => 'sometimes|required|numeric|min:0',
                'payment_method' => 'sometimes|required|in:Efectivo,Tarjeta de Débito/Crédito,Transferencia',
            ]);
            if (isset($budget['estimated_cost'])) {
                $updates['presupuesto.costo_estimado'] = $this->nonNegativeNumber($budget['estimated_cost']);
                $updates['precio_final'] = $this->nonNegativeNumber($budget['estimated_cost']);
            }
            if (isset($budget['advance'])) {
                $updates['presupuesto.anticipo'] = $this->nonNegativeNumber($budget['advance']);
            }
            if (isset($budget['payment_method'])) {
                $updates['presupuesto.metodo_pago'] = $budget['payment_method'];
            }
            $estimatedCost = $updates['presupuesto.costo_estimado'] ?? (float) ($order['presupuesto']['costo_estimado'] ?? 0);
            $advance = $updates['presupuesto.anticipo'] ?? (float) ($order['presupuesto']['anticipo'] ?? 0);
            if ($advance > $estimatedCost) {
                throw new \InvalidArgumentException('El anticipo no puede ser mayor al costo estimado.');
            }
        }
        if ($updates === []) {
            throw new \InvalidArgumentException('No se propusieron cambios permitidos.');
        }

        $matches[0]->reference()->update(array_map(
            static fn ($path, $value) => ['path' => $path, 'value' => $value],
            array_keys($updates),
            array_values($updates),
        ));
        \Illuminate\Support\Facades\Cache::flush();

        return "Los cambios de la orden $folio se guardaron correctamente. Entra a la sección Órdenes, revisa el registro y confirma que los cambios se reflejen; completa cualquier dato que aún esté pendiente.";
    }

    private function validateActionData(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
        return $validator->validated();
    }

    private function nonNegativeNumber(mixed $value): float
    {
        if (!is_numeric($value) || (float) $value < 0) {
            throw new \InvalidArgumentException('El presupuesto debe ser un número no negativo.');
        }
        return (float) $value;
    }

    private function generateOrderFolio(string $workshopId): string
    {
        $prefix = 'ORD-' . now()->format('Ymd') . '-';
        for ($counter = 1; $counter <= 9999; $counter++) {
            $folio = $prefix . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
            $snapshot = $this->firestore->collection('ordenes_trabajo')->document($folio)->snapshot();
            if (!$snapshot->exists()) {
                return $folio;
            }
        }
        throw new \RuntimeException('No se pudo generar un folio disponible.');
    }

}

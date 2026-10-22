<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class OrdenController extends Controller
{
    protected $firestoreDb;

    /**
     * Estados válidos de una orden (orden jerárquico)
     * El orden en este array define el "progreso":
     * Pendiente -> En reparación -> Listo
     * Cancelado es un estado final paralelo
     */
    const ESTADOS = ['Pendiente', 'En reparación', 'Listo', 'Cancelado'];

    /**
     * Transiciones permitidas entre estados
     */
    const TRANSICIONES = [
        'Pendiente'      => ['En reparación', 'Cancelado'],
        'En reparación'  => ['Listo'],
        'Listo'          => [],
        'Cancelado'      => [],
    ];

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
                    $config = [
                        'keyFilePath' => $credentialsPath,
                        'projectId' => $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID'),
                    ];
                    $this->firestoreDb = new FirestoreClient($config);
                    Log::info('Firestore inicializado en Taller\OrdenController');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error inicializando Taller\OrdenController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }
    } */

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
     * Obtiene el taller_id del usuario actual
     */
    private function obtenerTallerIdDelUsuario()
    {
        // Si el login ya guardó el taller_id, esto es instantáneo (0ms)
        return session('firebase_user.taller_id');
    }

    /**
     * Listado de órdenes con filtros
     */
    public function index(Request $request)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();

        if (!$tallerId) {
            return view('taller.ordenes.index', [
                'ordenes' => [],
                'search' => '',
                'filterEstado' => '',
                'page' => 1,
                'totalPages' => 1,
                'total' => 0,
                'sinTaller' => true,
            ]);
        }

        $search = trim($request->input('search', ''));
        $filterEstado = $request->input('estado', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        // 🔥 CACHÉ: Si no hay búsqueda, guardamos el resultado por 60 segundos
        if (empty($search)) {
            $cacheKey = "ordenes_{$tallerId}_{$page}_{$filterEstado}";

            $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($tallerId, $filterEstado, $perPage, $offset, $page) {
                $ordenes = [];
                $totalPages = 1;

                try {
                    $query = $this->firestoreDb->collection('ordenes_trabajo')
                        ->where('taller_id', '=', $tallerId);

                    if (!empty($filterEstado)) {
                        $query = $query->where('estado', '=', $filterEstado);
                    }

                    $query = $query->orderBy('fecha_orden', 'desc')
                        ->limit($perPage + 1)->offset($offset);

                    $snapshot = $query->documents();

                    foreach ($snapshot as $doc) {
                        if (!$doc->exists()) continue;
                        $data = $doc->data();
                        $data['id'] = $doc->id();
                        $ordenes[] = $data;
                    }

                    if (count($ordenes) > $perPage) {
                        array_pop($ordenes);
                        $totalPages = $page + 1;
                    }
                } catch (\Exception $e) {
                    Log::error('Error cargando órdenes: ' . $e->getMessage());
                }

                return [
                    'ordenes' => $ordenes,
                    'totalPages' => $totalPages
                ];
            });

            $ordenes = $data['ordenes'];
            $totalPages = $data['totalPages'];
        } else {
            // Búsqueda en PHP (sin caché para que sea tiempo real)
            $ordenes = [];
            $totalPages = 1;
            try {
                $query = $this->firestoreDb->collection('ordenes_trabajo')
                    ->where('taller_id', '=', $tallerId)
                    ->orderBy('fecha_orden', 'desc')
                    ->limit(100); // Traemos 100 para buscar

                $snapshot = $query->documents();
                foreach ($snapshot as $doc) {
                    if (!$doc->exists()) continue;
                    $data = $doc->data();
                    $data['id'] = $doc->id();
                    $ordenes[] = $data;
                }

                $ordenes = array_filter($ordenes, function ($orden) use ($search) {
                    $campos = ['folio', 'cliente_nombre', 'cliente_email', 'cliente_telefono'];
                    foreach ($campos as $c) {
                        if (isset($orden[$c]) && stripos((string) $orden[$c], $search) !== false) return true;
                    }
                    if (isset($orden['vehiculo']['placas']) && stripos($orden['vehiculo']['placas'], $search) !== false) return true;
                    if (isset($orden['vehiculo']['marca']) && stripos($orden['vehiculo']['marca'], $search) !== false) return true;
                    return false;
                });

                $total = count($ordenes);
                $totalPages = max(1, (int) ceil($total / $perPage));
                $ordenes = array_slice($ordenes, $offset, $perPage);
            } catch (\Exception $e) {
                Log::error('Error en búsqueda de órdenes: ' . $e->getMessage());
            }
        }

        return view('taller.ordenes.index', [
            'ordenes' => $ordenes,
            'search' => $search,
            'filterEstado' => $filterEstado,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => count($ordenes),
            'sinTaller' => false,
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId) {
            return redirect()->route('taller.ordenes')->with('error', 'No se encontró tu taller asignado.');
        }

        $personal = \App\Http\Controllers\Taller\PersonalController::obtenerPersonalActivoDelTaller(
            $this->firestoreDb,
            $tallerId
        );

        return view('taller.ordenes.create', [
            'estados' => self::ESTADOS,
            'personal' => $personal,
        ]);
    }

    /**
     * Guardar nueva orden
     */
    public function store(Request $request)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId) {
            return back()->withInput()->with('error', 'No se encontró tu taller asignado.');
        }

        if (!$this->firestoreDb) {
            return back()->withInput()->with('error', 'Firestore no disponible.');
        }

        try {
            $validated = $request->validate([
                // Cliente
                'cliente_nombre' => [
                    'required',
                    'string',
                    'min:2',
                    'max:150',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'cliente_email' => 'nullable|email|max:150',
                'cliente_telefono' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[\d\s\+\-\(\)]{7,20}$/',
                ],

                // Vehículo
                'vehiculo.marca' => [
                    'required',
                    'string',
                    'max:80',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'vehiculo.modelo' => [
                    'required',
                    'string',
                    'max:80',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'vehiculo.anio' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'vehiculo.color' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'vehiculo.placas' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'vehiculo.kilometraje' => 'nullable|integer|min:0',

                // Fallas
                'fallas' => 'required|array|min:1',
                'fallas.*.categoria' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'fallas.*.descripcion' => [
                    'nullable',
                    'string',
                    'max:500',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'fallas.*.prioridad' => 'required|in:Baja,Media,Alta,Urgente',
                'fallas.*.precio' => 'required|numeric|min:0',

                // Presupuesto
                'presupuesto.anticipo' => 'nullable|numeric|min:0',
                'presupuesto.metodo_pago' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'precio_final' => 'nullable|numeric|min:0',

                // Fechas
                'fecha_entrega_estimada' => 'required|date',

                // Asignación
                'modo_asignacion' => 'nullable|in:none,general,por_falla',
                'responsable_general_id' => 'nullable|string|max:100',
                'responsable_general_nombre' => [
                    'nullable',
                    'string',
                    'max:150',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'fallas.*.responsable_id' => 'nullable|string|max:100',
                'fallas.*.responsable_nombre' => [
                    'nullable',
                    'string',
                    'max:150',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
            ], [
                'cliente_nombre.regex' => 'El nombre del cliente contiene caracteres no permitidos.',
                'cliente_telefono.regex' => 'El teléfono solo puede contener números, +, -, ( ) y espacios.',
                'vehiculo.marca.regex' => 'La marca contiene caracteres no permitidos.',
                'vehiculo.modelo.regex' => 'El modelo contiene caracteres no permitidos.',
                'vehiculo.color.regex' => 'El color contiene caracteres no permitidos.',
                'vehiculo.placas.regex' => 'Las placas contienen caracteres no permitidos.',
                'fallas.*.categoria.regex' => 'La categoría de la falla contiene caracteres no permitidos.',
                'fallas.*.descripcion.regex' => 'La descripción de la falla contiene caracteres no permitidos.',
            ]);

            // Regla: fecha_entrega_estimada >= fecha_orden (ahora)
            $fechaOrden = now();
            $fechaEntrega = \Carbon\Carbon::parse($validated['fecha_entrega_estimada']);

            if ($fechaEntrega->lt($fechaOrden->startOfDay())) {
                return back()->withInput()->withErrors([
                    'fecha_entrega_estimada' => 'La fecha de entrega no puede ser anterior a la fecha de la orden (' . $fechaOrden->format('d/m/Y') . ').'
                ]);
            }

            $precioSugerido = 0;
            foreach ($validated['fallas'] as $falla) {
                $precioSugerido += (float) $falla['precio'];
            }

            $precioFinal = isset($request->precio_final) && $request->precio_final !== '' && $request->precio_final !== null
                ? (float) $request->precio_final
                : $precioSugerido;

            $costoEstimado = $precioFinal;
            $anticipo = (float) ($validated['presupuesto']['anticipo'] ?? 0);

            // ============================================================
            // 🔍 BUSCAR AL CONDUCTOR POR EMAIL
            // ============================================================
            $clienteUid = null;
            $clienteEncontrado = null;

            $email = trim($validated['cliente_email'] ?? '');
            if (!empty($email)) {
                try {
                    $usuariosSnapshot = $this->firestoreDb
                        ->collection('usuarios')
                        ->where('email', '=', $email)
                        ->where('rol', '=', 'Conductor')
                        ->limit(1)
                        ->documents();

                    foreach ($usuariosSnapshot as $doc) {
                        if ($doc->exists()) {
                            $clienteEncontrado = $doc->data();
                            $clienteUid = $clienteEncontrado['uid'] ?? $doc->id();
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Error buscando conductor por email: ' . $e->getMessage());
                }
            }

            // ============================================================
            // 🚗 BUSCAR VEHÍCULO DEL CONDUCTOR (por placas)
            // ============================================================
            $vehiculoIdCliente = null;
            if ($clienteUid) {
                try {
                    $placas = strtoupper($validated['vehiculo']['placas']);
                    $vehiculosSnapshot = $this->firestoreDb
                        ->collection('usuarios')
                        ->document($clienteUid)
                        ->collection('vehiculos')
                        ->where('placas', '=', $placas)
                        ->limit(1)
                        ->documents();

                    foreach ($vehiculosSnapshot as $vDoc) {
                        if ($vDoc->exists()) {
                            $vehiculoIdCliente = $vDoc->id();
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Error buscando vehículo del cliente: ' . $e->getMessage());
                }
            }

            $folio = $this->generarFolio($tallerId);

            // 🔒 SANITIZACIÓN (defensa en profundidad)
            $clienteNombre    = $this->sanitize($validated['cliente_nombre'], 150);
            $clienteEmail     = filter_var($validated['cliente_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
            $clienteTelefono  = preg_replace('/[^\d\s\+\-\(\)]/', '', $validated['cliente_telefono'] ?? '');
            $metodoPago       = $this->sanitize($validated['presupuesto']['metodo_pago'] ?? '', 50);

            $ordenData = [
                'folio' => $folio,
                'taller_id' => $tallerId,
                'estado' => 'Pendiente',

                // Cliente
                'cliente_nombre' => $clienteNombre,
                'cliente_email' => $clienteEmail,
                'cliente_telefono' => $clienteTelefono,

                'cliente_uid' => $clienteUid,
                'cliente_registrado' => $clienteUid !== null,
                'vehiculo_cliente_id' => $vehiculoIdCliente,

                // Vehículo
                'vehiculo' => [
                    'marca' => $this->sanitize($validated['vehiculo']['marca'], 80),
                    'modelo' => $this->sanitize($validated['vehiculo']['modelo'], 80),
                    'anio' => (int) $validated['vehiculo']['anio'],
                    'color' => $this->sanitize($validated['vehiculo']['color'] ?? '', 50),
                    'placas' => strtoupper($this->sanitize($validated['vehiculo']['placas'], 20)),
                    'kilometraje' => (int) ($validated['vehiculo']['kilometraje'] ?? 0),
                ],

                // Fallas
                'fallas_multiples' => array_map(function ($f) {
                    return [
                        'categoria' => $this->sanitize($f['categoria'], 100),
                        'descripcion' => $this->sanitize($f['descripcion'] ?? '', 500),
                        'prioridad' => $f['prioridad'],
                        'precio' => (float) $f['precio'],
                        'responsable' => !empty($f['responsable_id']) ? [
                            'id' => $f['responsable_id'],
                            'nombre' => $this->sanitize($f['responsable_nombre'] ?? '', 150),
                        ] : null,
                    ];
                }, $validated['fallas']),

                // Presupuesto
                'presupuesto' => [
                    'anticipo' => $anticipo,
                    'costo_estimado' => $costoEstimado,
                    'metodo_pago' => $metodoPago,
                ],

                'precio_final' => $precioFinal,

                'fecha_orden' => $fechaOrden->toDateTimeString(),
                'fecha_entrega_estimada' => $fechaEntrega->toDateTimeString(),

                'asignacion' => [
                    'modo' => $validated['modo_asignacion'] ?? 'none',
                    'responsable_general' => !empty($validated['responsable_general_id']) ? [
                        'id' => $validated['responsable_general_id'],
                        'nombre' => $this->sanitize($validated['responsable_general_nombre'] ?? '', 150),
                    ] : null,
                ],

                'creado_por' => session('firebase_user.uid'),
                'fecha_creacion' => now()->toDateTimeString(),
            ];

            $this->firestoreDb
                ->collection('ordenes_trabajo')
                ->document($folio)
                ->set($ordenData);

            // ... resto igual (notificar al conductor)
            if ($clienteUid) {
                try {
                    $tallerDoc = $this->firestoreDb->collection('talleres')->document($tallerId)->snapshot();
                    $tallerNombre = $tallerDoc->exists()
                        ? ($tallerDoc->data()['nombre'] ?? 'El taller')
                        : 'El taller';

                    $notifService = new \App\Services\NotificacionService($this->firestoreDb);
                    $notifService->crear($clienteUid, [
                        'tipo'          => 'orden_creada',
                        'titulo'        => 'Nuevo servicio registrado 📋',
                        'mensaje'       => "{$tallerNombre} creó la orden {$folio} para tu {$ordenData['vehiculo']['marca']} {$ordenData['vehiculo']['modelo']} ({$ordenData['vehiculo']['placas']}). Sigue el progreso desde tu cuenta.",
                        'icono'         => 'fa-solid fa-clipboard-list',
                        'color'         => 'blue',
                        'taller_id'     => $tallerId,
                        'taller_nombre' => $tallerNombre,
                        'orden_id'      => $folio,
                        'folio'         => $folio,
                        'url'           => route('cuenta.servicios.detalle', ['id' => $folio]),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('No se pudo notificar al conductor: ' . $e->getMessage());
                }
            }

            Log::info('Orden creada: ' . $folio);

            \Illuminate\Support\Facades\Cache::flush();
            return redirect()
                ->route('taller.ordenes.editar', ['id' => $folio])
                ->with('success', 'Orden ' . $folio . ' creada exitosamente.'
                    . ($clienteUid ? '' : ' El cliente no está registrado en MecxiHub.'))
                ->with('subir_evidencias_pendientes', true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error creando orden: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al crear la orden: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de una orden
     */
    public function show($id)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->firestoreDb) {
            return redirect()->route('taller.ordenes')->with('error', 'Acceso no autorizado.');
        }

        $doc = $this->firestoreDb->collection('ordenes_trabajo')->document($id)->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('taller.ordenes')->with('error', 'Orden no encontrada.');
        }

        $orden = $doc->data();
        $orden['id'] = $doc->id();

        if (($orden['taller_id'] ?? null) !== $tallerId) {
            return redirect()->route('taller.ordenes')->with('error', 'No tienes permiso para ver esta orden.');
        }

        // Fallas (compatible con 'falla' o 'fallas_multiples')
        $fallas = $orden['fallas_multiples'] ?? ($orden['falla'] ?? []);
        if (isset($orden['falla']) && is_array($orden['falla']) && !isset($orden['falla'][0])) {
            // era una sola falla (no array de fallas)
            $fallas = [$orden['falla']];
        }

        // Cargar evidencias
        $evidencias = [];
        try {
            $evSnapshot = $this->firestoreDb
                ->collection('ordenes_trabajo')
                ->document($id)
                ->collection('evidencias')
                ->documents();

            foreach ($evSnapshot as $evDoc) {
                if (!$evDoc->exists()) continue;
                $evData = $evDoc->data();
                $evData['id'] = $evDoc->id();
                $evidencias[] = $evData;
            }
        } catch (\Exception $e) {
            Log::warning('Error cargando evidencias: ' . $e->getMessage());
        }

        return view('taller.ordenes.show', [
            'orden' => $orden,
            'fallas' => $fallas,
            'evidencias' => $evidencias,
            'transiciones' => self::TRANSICIONES[$orden['estado'] ?? 'Pendiente'] ?? [],
        ]);
    }

    /**
     * Vista limpia para imprimir la orden (se abre en pestaña nueva)
     */
    public function imprimir($id)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->firestoreDb) {
            return redirect()->route('taller.ordenes')->with('error', 'Acceso no autorizado.');
        }

        $doc = $this->firestoreDb->collection('ordenes_trabajo')->document($id)->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('taller.ordenes')->with('error', 'Orden no encontrada.');
        }

        $orden = $doc->data();
        $orden['id'] = $doc->id();

        if (($orden['taller_id'] ?? null) !== $tallerId) {
            return redirect()->route('taller.ordenes')->with('error', 'No tienes permiso para ver esta orden.');
        }

        // Fallas (compatible con 'falla' o 'fallas_multiples')
        $fallas = $orden['fallas_multiples'] ?? ($orden['falla'] ?? []);
        if (isset($orden['falla']) && is_array($orden['falla']) && !isset($orden['falla'][0])) {
            $fallas = [$orden['falla']];
        }

        // Datos del taller (con fallback desde Firestore si la sesión no los tiene)
        $tallerData = session('taller_data', []);
        if (empty($tallerData['nombre'])) {
            try {
                $tallerDoc = $this->firestoreDb->collection('talleres')->document($tallerId)->snapshot();
                if ($tallerDoc->exists()) {
                    $tallerData = $tallerDoc->data();
                }
            } catch (\Exception $e) {
                Log::warning('No se pudieron cargar datos del taller para imprimir: ' . $e->getMessage());
            }
        }

        return view('taller.ordenes.imprimir', [
            'orden' => $orden,
            'fallas' => $fallas,
            'tallerData' => $tallerData,
        ]);
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->firestoreDb) {
            return redirect()->route('taller.ordenes')->with('error', 'Acceso no autorizado.');
        }

        $doc = $this->firestoreDb->collection('ordenes_trabajo')->document($id)->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('taller.ordenes')->with('error', 'Orden no encontrada.');
        }

        $orden = $doc->data();
        $orden['id'] = $doc->id();

        if (($orden['taller_id'] ?? null) !== $tallerId) {
            return redirect()->route('taller.ordenes')->with('error', 'No tienes permiso para editar esta orden.');
        }

        if (in_array($orden['estado'], ['Cancelado', 'Listo'], true)) {
            return redirect()->route('taller.ordenes.show', $id)
                ->with('error', 'No puedes editar una orden en estado "' . $orden['estado'] . '".');
        }

        $fallas = $orden['fallas_multiples'] ?? ($orden['falla'] ?? []);
        if (isset($orden['falla']) && is_array($orden['falla']) && !isset($orden['falla'][0])) {
            $fallas = [$orden['falla']];
        }

        // Asegurar que sea un array indexado (0,1,2,...) para que @json lo serialice bien
        $fallas = array_values($fallas);

        // Cargar evidencias
        $evidencias = [];
        try {
            $evSnapshot = $this->firestoreDb
                ->collection('ordenes_trabajo')
                ->document($id)
                ->collection('evidencias')
                ->documents();

            foreach ($evSnapshot as $evDoc) {
                if (!$evDoc->exists()) continue;
                $evData = $evDoc->data();
                $evData['id'] = $evDoc->id();
                $evidencias[] = $evData;
            }
        } catch (\Exception $e) {
            Log::warning('Error cargando evidencias: ' . $e->getMessage());
        }

        $personal = \App\Http\Controllers\Taller\PersonalController::obtenerPersonalActivoDelTaller(
            $this->firestoreDb,
            $tallerId
        );

        return view('taller.ordenes.edit', [
            'orden' => $orden,
            'fallas' => $fallas,
            'estados' => self::ESTADOS,
            'transiciones' => self::TRANSICIONES[$orden['estado'] ?? 'Pendiente'] ?? [],
            'personal' => $personal,
            'evidencias' => $evidencias,
        ]);
    }

    /**
     * Actualizar orden
     */
    public function update(Request $request, $id)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->firestoreDb) {
            return back()->with('error', 'Acceso no autorizado.');
        }

        $doc = $this->firestoreDb->collection('ordenes_trabajo')->document($id)->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('taller.ordenes')->with('error', 'Orden no encontrada.');
        }

        $ordenActual = $doc->data();
        if (($ordenActual['taller_id'] ?? null) !== $tallerId) {
            return redirect()->route('taller.ordenes')->with('error', 'No tienes permiso para editar esta orden.');
        }

        if (in_array($ordenActual['estado'], ['Cancelado', 'Listo'], true)) {
            return redirect()->route('taller.ordenes.show', $id)
                ->with('error', 'No puedes editar una orden en estado "' . $ordenActual['estado'] . '".');
        }

        try {
            $validated = $request->validate([
                // Cliente
                'cliente_nombre' => [
                    'required',
                    'string',
                    'min:2',
                    'max:150',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'cliente_email' => 'nullable|email|max:150',
                'cliente_telefono' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[\d\s\+\-\(\)]{7,20}$/',
                ],

                // Vehículo
                'vehiculo.marca' => [
                    'required',
                    'string',
                    'max:80',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'vehiculo.modelo' => [
                    'required',
                    'string',
                    'max:80',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'vehiculo.anio' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'vehiculo.color' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'vehiculo.placas' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'vehiculo.kilometraje' => 'nullable|integer|min:0',

                // Fallas
                'fallas' => 'required|array|min:1',
                'fallas.*.categoria' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'fallas.*.descripcion' => [
                    'nullable',
                    'string',
                    'max:500',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'fallas.*.prioridad' => 'required|in:Baja,Media,Alta,Urgente',
                'fallas.*.precio' => 'required|numeric|min:0',

                // Presupuesto
                'presupuesto.anticipo' => 'nullable|numeric|min:0',
                'presupuesto.metodo_pago' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],

                'precio_final' => 'nullable|numeric|min:0',
                'fecha_entrega_estimada' => 'required|date',
                'estado' => 'required|in:' . implode(',', self::ESTADOS),

                'modo_asignacion' => 'nullable|in:none,general,por_falla',
                'responsable_general_id' => 'nullable|string|max:100',
                'responsable_general_nombre' => [
                    'nullable',
                    'string',
                    'max:150',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
                'fallas.*.responsable_id' => 'nullable|string|max:100',
                'fallas.*.responsable_nombre' => [
                    'nullable',
                    'string',
                    'max:150',
                    'regex:/^[^\<\>\{\}]*$/u',
                ],
            ], [
                'cliente_nombre.regex' => 'El nombre del cliente contiene caracteres no permitidos.',
                'cliente_telefono.regex' => 'El teléfono solo puede contener números, +, -, ( ) y espacios.',
                'vehiculo.marca.regex' => 'La marca contiene caracteres no permitidos.',
                'vehiculo.modelo.regex' => 'El modelo contiene caracteres no permitidos.',
                'vehiculo.color.regex' => 'El color contiene caracteres no permitidos.',
                'vehiculo.placas.regex' => 'Las placas contienen caracteres no permitidos.',
                'fallas.*.categoria.regex' => 'La categoría de la falla contiene caracteres no permitidos.',
                'fallas.*.descripcion.regex' => 'La descripción de la falla contiene caracteres no permitidos.',
            ]);

            // Validación de fecha
            $fechaOrden = \Carbon\Carbon::parse($ordenActual['fecha_orden'] ?? now());
            $fechaEntrega = \Carbon\Carbon::parse($validated['fecha_entrega_estimada']);

            if ($fechaEntrega->lt($fechaOrden->copy()->startOfDay())) {
                return back()->withInput()->withErrors([
                    'fecha_entrega_estimada' => 'La fecha de entrega no puede ser anterior a la fecha de la orden (' . $fechaOrden->format('d/m/Y') . ').'
                ]);
            }

            // Validar transición
            $estadoActual = $ordenActual['estado'] ?? 'Pendiente';
            $estadoNuevo = $validated['estado'];

            if ($estadoActual !== $estadoNuevo) {
                $permitidas = self::TRANSICIONES[$estadoActual] ?? [];
                if (!in_array($estadoNuevo, $permitidas, true)) {
                    return back()->withInput()->withErrors([
                        'estado' => 'No puedes cambiar de "' . $estadoActual . '" a "' . $estadoNuevo . '". Los estados solo pueden avanzar (Pendiente → En reparación → Listo) o cancelarse desde Pendiente.'
                    ]);
                }
            }

            $precioCalculado = 0;
            foreach ($validated['fallas'] as $falla) {
                $precioCalculado += (float) $falla['precio'];
            }

            $precioFinal = isset($validated['precio_final']) && $validated['precio_final'] !== null
                ? (float) $validated['precio_final']
                : $precioCalculado;

            // 🔒 SANITIZACIÓN
            $updateData = [
                'cliente_nombre' => $this->sanitize($validated['cliente_nombre'], 150),
                'cliente_email' => filter_var($validated['cliente_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '',
                'cliente_telefono' => preg_replace('/[^\d\s\+\-\(\)]/', '', $validated['cliente_telefono'] ?? ''),
                'vehiculo' => [
                    'marca' => $this->sanitize($validated['vehiculo']['marca'], 80),
                    'modelo' => $this->sanitize($validated['vehiculo']['modelo'], 80),
                    'anio' => (int) $validated['vehiculo']['anio'],
                    'color' => $this->sanitize($validated['vehiculo']['color'] ?? '', 50),
                    'placas' => strtoupper($this->sanitize($validated['vehiculo']['placas'], 20)),
                    'kilometraje' => (int) ($validated['vehiculo']['kilometraje'] ?? 0),
                ],
                'fallas_multiples' => array_map(function ($f) {
                    return [
                        'categoria' => $this->sanitize($f['categoria'], 100),
                        'descripcion' => $this->sanitize($f['descripcion'] ?? '', 500),
                        'prioridad' => $f['prioridad'],
                        'precio' => (float) $f['precio'],
                        'responsable' => !empty($f['responsable_id']) ? [
                            'id' => $f['responsable_id'],
                            'nombre' => $this->sanitize($f['responsable_nombre'] ?? '', 150),
                        ] : null,
                    ];
                }, $validated['fallas']),
                'presupuesto' => [
                    'anticipo' => (float) ($validated['presupuesto']['anticipo'] ?? 0),
                    'costo_estimado' => $precioCalculado,
                    'metodo_pago' => $this->sanitize($validated['presupuesto']['metodo_pago'] ?? '', 50),
                ],
                'precio_final' => $precioFinal,
                'estado' => $estadoNuevo,
                'fecha_entrega_estimada' => $fechaEntrega->toDateTimeString(),
                'fecha_actualizacion' => now()->toDateTimeString(),
                'asignacion' => [
                    'modo' => $validated['modo_asignacion'] ?? 'none',
                    'responsable_general' => !empty($validated['responsable_general_id']) ? [
                        'id' => $validated['responsable_general_id'],
                        'nombre' => $this->sanitize($validated['responsable_general_nombre'] ?? '', 150),
                    ] : null,
                ],
            ];

            $acabaDePasarAListo = false;

            if ($estadoNuevo === 'Listo') {
                if (empty($ordenActual['fecha_entrega_real'])) {
                    $updateData['fecha_entrega_real'] = now()->toDateTimeString();
                }
                if (($ordenActual['estado'] ?? '') !== 'Listo') {
                    $acabaDePasarAListo = true;
                }
            }

            $this->firestoreDb
                ->collection('ordenes_trabajo')
                ->document($id)
                ->set($updateData, ['merge' => true]);

            if ($acabaDePasarAListo) {
                try {
                    $ordenActualizada = array_merge($ordenActual, $updateData);
                    $ordenActualizada['id'] = $id;

                    \App\Http\Controllers\Taller\CalificacionController::enviarSolicitudCalificacion(
                        $ordenActualizada,
                        $tallerId,
                        $this->firestoreDb
                    );
                } catch (\Exception $e) {
                    Log::error('Error disparando email de calificación: ' . $e->getMessage());
                }
            }

            Log::info('Orden actualizada: ' . $id);

            \Illuminate\Support\Facades\Cache::flush();
            return redirect()
                ->route('taller.ordenes')
                ->with('success', 'Orden actualizada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error actualizando orden: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar la orden: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado (endpoint rápido desde la tabla/detalle)
     */
    public function cambiarEstado(Request $request, $id)
    {
        try {
            $tallerId = $this->obtenerTallerIdDelUsuario();
            if (!$tallerId || !$this->firestoreDb) {
                return response()->json(['error' => 'No autorizado'], 401);
            }

            $validated = $request->validate([
                'estado' => 'required|in:' . implode(',', self::ESTADOS),
            ]);

            $ref = $this->firestoreDb->collection('ordenes_trabajo')->document($id);
            $doc = $ref->snapshot();

            if (!$doc->exists()) {
                return response()->json(['error' => 'Orden no encontrada'], 404);
            }

            $orden = $doc->data();
            if (($orden['taller_id'] ?? null) !== $tallerId) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $estadoActual = $orden['estado'] ?? 'Pendiente';
            $estadoNuevo  = $validated['estado'];

            if ($estadoActual !== $estadoNuevo) {
                $permitidas = self::TRANSICIONES[$estadoActual] ?? [];
                if (!in_array($estadoNuevo, $permitidas, true)) {
                    return response()->json([
                        'error' => 'No puedes cambiar de "' . $estadoActual . '" a "' . $estadoNuevo . '".'
                    ], 422);
                }
            }

            $updateData = [
                'estado'              => $estadoNuevo,
                'fecha_actualizacion' => now()->toDateTimeString(),
            ];

            $acabaDePasarAListo = false;

            if ($estadoNuevo === 'Listo') {
                $updateData['fecha_entrega_real'] = now()->toDateTimeString();

                // Solo si ANTES no estaba listo (evita reenviar el email si ya estaba en Listo)
                if (($orden['estado'] ?? '') !== 'Listo') {
                    $acabaDePasarAListo = true;
                }
            }

            $ref->set($updateData, ['merge' => true]);

            // ============================================================
            // 🔔 NOTIFICAR AL CONDUCTOR
            // ============================================================
            $clienteUid = $orden['cliente_uid'] ?? null;

            if ($clienteUid) {
                try {
                    // Datos del taller (para el mensaje)
                    $tallerDoc = $this->firestoreDb->collection('talleres')->document($tallerId)->snapshot();
                    $tallerNombre = $tallerDoc->exists()
                        ? ($tallerDoc->data()['nombre'] ?? 'El taller')
                        : 'El taller';

                    if ($acabaDePasarAListo) {
                        // ✅ Pasar a "Listo" → enviar correo + notif con token de calificación
                        // (enviarSolicitudCalificacion ya crea la notif internamente)
                        $ordenActualizada = array_merge($orden, $updateData);
                        $ordenActualizada['id'] = $id;

                        \App\Http\Controllers\Taller\CalificacionController::enviarSolicitudCalificacion(
                            $ordenActualizada,
                            $tallerId,
                            $this->firestoreDb
                        );
                    } else {
                        // ✅ Pendiente / En reparación / Cancelado → notif simple
                        $notifService = new \App\Services\NotificacionService($this->firestoreDb);
                        $notifService->notificarCambioEstado(
                            $clienteUid,
                            $tallerId,
                            $tallerNombre,
                            $orden['folio'] ?? $id,
                            $estadoNuevo
                        );
                    }
                } catch (\Exception $e) {
                    Log::warning('No se pudo notificar al conductor: ' . $e->getMessage());
                    // No rompemos la respuesta si falla la notificación
                }
            }

            \Illuminate\Support\Facades\Cache::flush();

            return response()->json([
                'success' => true,
                'estado'  => $estadoNuevo,
                'message' => 'Estado actualizado a "' . $estadoNuevo . '".'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error cambiando estado: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar el estado.'], 500);
        }
    }

    /**
     * Genera folio único con formato ORD-YYYYMMDD-XXXX
     */
    private function generarFolio($tallerId)
    {
        $prefijo = 'ORD-' . date('Ymd') . '-';

        // Contar órdenes del taller hoy para numerar
        $contador = 1;
        try {
            $snapshot = $this->firestoreDb
                ->collection('ordenes_trabajo')
                ->where('taller_id', '=', $tallerId)
                ->documents();

            $hoy = date('Y-m-d');
            foreach ($snapshot as $doc) {
                if (!$doc->exists()) continue;
                $data = $doc->data();
                $fecha = $data['fecha_orden'] ?? '';
                if (str_starts_with($fecha, $hoy)) {
                    $contador++;
                }
            }
        } catch (\Exception $e) {
            $contador = random_int(1, 999);
        }

        do {
            $folio = $prefijo . str_pad($contador, 4, '0', STR_PAD_LEFT);
            $existe = $this->firestoreDb->collection('ordenes_trabajo')->document($folio)->snapshot()->exists();
            if (!$existe) break;
            $contador++;
        } while (true);

        return $folio;
    }
}

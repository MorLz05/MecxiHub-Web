<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class InformacionController extends Controller
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
                    $config = [
                        'keyFilePath' => $credentialsPath,
                        'projectId' => $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID'),
                    ];
                    $this->firestoreDb = new FirestoreClient($config);
                    Log::info('Firestore inicializado en InformacionController');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error inicializando InformacionController: ' . $e->getMessage());
            $this->firestoreDb = null;
        }
    } */

    public function index()
    {
        $user = session('firebase_user');
        $tallerData = [];
        $tallerId = session('firebase_user.taller_id');
        $bloques = [];
        $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

if (!$tallerId) {
        // Redirigir o mostrar error
        return redirect()->route('login')->with('error', 'No tienes un taller asignado.');
    }

        if ($this->firestoreDb && $tallerId) {
            try {
                $tallerData = session('taller_data');

                // 🔥 FORZAMOS LA CARGA SI FALTA ALGÚN DATO CLAVE (como la dirección o descripción)
                if (!$tallerData || !isset($tallerData['direccion'])) {
                    $tallerRef = $this->firestoreDb->collection('talleres')->document($tallerId);
                    $tallerSnapshot = $tallerRef->snapshot();

                    if ($tallerSnapshot->exists()) {
                        $tallerData = $tallerSnapshot->data();
                        $tallerData['id'] = $tallerId;

                        // Asegurar campos
                        $tallerData['descripcion'] = ($tallerData['descripcion'] ?? '');
                        $tallerData['servicios_count'] = $tallerData['servicios_count'] ?? [];
                        $tallerData['horario'] = $tallerData['horario'] ?? [];
                        $tallerData['verificado'] = $tallerData['verificado'] ?? false;
                        $tallerData['email'] = $tallerData['email'] ?? '';
                        $tallerData['direccion'] = $tallerData['direccion'] ?? ''; // 👈 Aseguramos dirección

                        // Normalizar "plan"
                        $planRaw = $tallerData['plan'] ?? null;
                        if (is_array($planRaw)) {
                            $planNombre = $planRaw['nombre'] ?? $planRaw['name'] ?? $planRaw['titulo'] ?? null;
                        } elseif (is_string($planRaw) && $planRaw !== '') {
                            $planNombre = $planRaw;
                        } else {
                            $planNombre = null;
                        }
                        $tallerData['plan'] = $planNombre;
                        $tallerData['logo_url'] = $tallerData['logo_url'] ?? null;

                        // Guardamos TODO en sesión
                        session(['taller_data' => $tallerData]);
                    }
                }

                // Cargar imágenes al vuelo (esto no pesa)
                $tallerData['imagenes'] = \Illuminate\Support\Facades\Cache::remember('taller_imagenes_' . $tallerId, 300, function () use ($tallerId) {
                    $imagenes = [];
                    try {
                        $imagenesSnapshot = $this->firestoreDb
                            ->collection('talleres')
                            ->document($tallerId)
                            ->collection('imagenes')
                            ->documents();

                        foreach ($imagenesSnapshot as $imgDoc) {
                            if (!$imgDoc->exists()) continue;
                            $imgData = $imgDoc->data();
                            $imgData['id'] = $imgDoc->id();
                            $imagenes[] = $imgData;
                        }

                        usort($imagenes, function ($a, $b) {
                            return strtotime($b['fecha_subida'] ?? '0') - strtotime($a['fecha_subida'] ?? '0');
                        });
                    } catch (\Exception $e) {
                        Log::warning('No se pudieron cargar imágenes: ' . $e->getMessage());
                    }
                    return $imagenes;
                });
                $imagenesSnapshot = $this->firestoreDb
                    ->collection('talleres')
                    ->document($tallerId)
                    ->collection('imagenes')
                    ->documents();

                foreach ($imagenesSnapshot as $imgDoc) {
                    if (!$imgDoc->exists()) continue;
                    $imgData = $imgDoc->data();
                    $imgData['id'] = $imgDoc->id();
                    $tallerData['imagenes'][] = $imgData;
                }

                usort($tallerData['imagenes'], function ($a, $b) {
                    return strtotime($b['fecha_subida'] ?? '0') - strtotime($a['fecha_subida'] ?? '0');
                });
            } catch (\Exception $e) {
                Log::error('Error obteniendo datos del taller: ' . $e->getMessage());
            }
        }

        $horarioActual = $tallerData['horario'] ?? [];
        $bloques = [];

        if (!empty($horarioActual)) {
            // Agrupar días que comparten el mismo horario
            $horarioPorDia = [];

            foreach ($diasSemana as $dia) {
                // Solo procesar días que tienen horario definido
                if (isset($horarioActual[$dia]) && !empty($horarioActual[$dia])) {
                    $rangos = $horarioActual[$dia];
                    // Crear una clave única para este conjunto de rangos
                    $key = json_encode($rangos);

                    if (!isset($horarioPorDia[$key])) {
                        $horarioPorDia[$key] = [
                            'dias' => [],
                            'rangos' => $rangos
                        ];
                    }
                    $horarioPorDia[$key]['dias'][] = $dia;
                }
            }

            // Construir los bloques finales solo con días que tienen horario
            foreach ($horarioPorDia as $key => $bloque) {
                // Verificar si es 24 horas
                $es24h = false;
                if (
                    !empty($bloque['rangos']) &&
                    count($bloque['rangos']) === 1 &&
                    isset($bloque['rangos'][0]['apertura']) &&
                    isset($bloque['rangos'][0]['cierre']) &&
                    $bloque['rangos'][0]['apertura'] === '00:00' &&
                    $bloque['rangos'][0]['cierre'] === '23:59'
                ) {
                    $es24h = true;
                }

                $bloques[] = [
                    'dias' => $bloque['dias'],
                    'rangos' => $bloque['rangos'],
                    'es24h' => $es24h,
                ];
            }

            // Ordenar bloques por días
            usort($bloques, function ($a, $b) use ($diasSemana) {
                $aMin = array_search($a['dias'][0] ?? 'lunes', $diasSemana);
                $bMin = array_search($b['dias'][0] ?? 'lunes', $diasSemana);
                return $aMin - $bMin;
            });
        }

        // Si no hay bloques (sin horario definido), crear uno por defecto
        if (empty($bloques)) {
            $bloques[] = [
                'dias' => [],
                'rangos' => [
                    ['apertura' => '09:00', 'cierre' => '18:00']
                ],
                'es24h' => false
            ];
        }

        return view('taller.informacion', compact('tallerData', 'tallerId', 'bloques', 'diasSemana'));
    }

    // Método para actualizar SOLO la información básica
    public function updateInfo(Request $request, $tallerId)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:120',
                'regex:/^[^\<\>\{\}]*$/u',
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:2000',
                'regex:/^[^\<\>\{\}]*$/u',
            ],
            'direccion' => [
                'required',
                'string',
                'max:300',
                'regex:/^[^\<\>\{\}]*$/u',
            ],
            'telefono' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\d\s\+\-\(\)]{7,20}$/',
            ],
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
        ], [
            'nombre.regex' => 'El nombre contiene caracteres no permitidos (<, >, {, }).',
            'descripcion.regex' => 'La descripción contiene caracteres no permitidos.',
            'direccion.regex' => 'La dirección contiene caracteres no permitidos.',
            'telefono.regex' => 'El teléfono solo puede contener números, +, -, ( ) y espacios.',
        ]);

        if (!$this->firestoreDb) {
            return back()->withErrors(['error' => 'Error de conexión con la base de datos.']);
        }

        try {
            $tallerRef = $this->firestoreDb->collection('talleres')->document($tallerId);
            $tallerSnapshot = $tallerRef->snapshot();

            if (!$tallerSnapshot->exists()) {
                return back()->withErrors(['error' => 'Taller no encontrado.']);
            }

            // 🔒 Sanitizar (defensa en profundidad)
            $nombre      = $this->sanitize($validated['nombre'], 120);
            $descripcion = $this->sanitize($validated['descripcion'] ?? '', 2000);
            $direccion   = $this->sanitize($validated['direccion'], 300);
            $telefono    = preg_replace('/[^\d\s\+\-\(\)]/', '', $validated['telefono']);

            $updates = [
                ['path' => 'nombre', 'value' => $nombre],
                ['path' => 'descripcion', 'value' => $descripcion],
                ['path' => 'direccion', 'value' => $direccion],
                ['path' => 'telefono', 'value' => $telefono],
            ];

            if (!empty($validated['latitud']) && !empty($validated['longitud'])) {
                $updates[] = ['path' => 'latitud', 'value' => (float) $validated['latitud']];
                $updates[] = ['path' => 'longitud', 'value' => (float) $validated['longitud']];
            }

            $tallerRef->update($updates);

            Log::info('Información básica del taller actualizada: ' . $tallerId);

            $tallerData = session('taller_data', []);
            $tallerData['nombre'] = $nombre;
            $tallerData['descripcion'] = $descripcion;
            $tallerData['direccion'] = $direccion;
            $tallerData['telefono'] = $telefono;
            if (!empty($validated['latitud']) && !empty($validated['longitud'])) {
                $tallerData['latitud'] = (float) $validated['latitud'];
                $tallerData['longitud'] = (float) $validated['longitud'];
            }
            session(['taller_data' => $tallerData]);

            session()->forget('taller_data');
            return back()->with('success', 'Información del taller actualizada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error actualizando información del taller: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar la información: ' . $e->getMessage()]);
        }
    }

    // Método para actualizar SOLO el horario
    public function updateHorario(Request $request, $tallerId)
    {
        $validated = $request->validate([
            'horario' => 'nullable|array',
        ]);

        if (!$this->firestoreDb) {
            return back()->withErrors(['error' => 'Error de conexión con la base de datos.']);
        }

        try {
            $tallerRef = $this->firestoreDb->collection('talleres')->document($tallerId);
            $tallerSnapshot = $tallerRef->snapshot();

            if (!$tallerSnapshot->exists()) {
                return back()->withErrors(['error' => 'Taller no encontrado.']);
            }

            $horario = $this->procesarHorario($validated['horario'] ?? []);

            $tallerRef->update([
                ['path' => 'horario', 'value' => $horario]
            ]);

            Log::info('Horario del taller actualizado: ' . $tallerId);

            $tallerData = session('taller_data', []);
            $tallerData['horario'] = $horario;
            session(['taller_data' => $tallerData]);

            session()->forget('taller_data');

            return back()->with('success_horario', 'Horario del taller actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error actualizando horario del taller: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar el horario: ' . $e->getMessage()]);
        }
    }

    // Mantener el método update original para compatibilidad
    public function update(Request $request, $tallerId)
    {
        // Este método ahora solo maneja la información básica
        return $this->updateInfo($request, $tallerId);
    }

    private function procesarHorario($horarioData)
    {
        $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        $horarioProcesado = [];

        // Inicializar todos los días con array vacío
        foreach ($diasSemana as $dia) {
            $horarioProcesado[$dia] = [];
        }

        // Procesar bloques de días
        if (isset($horarioData['bloques']) && is_array($horarioData['bloques'])) {
            foreach ($horarioData['bloques'] as $bloque) {
                if (empty($bloque['dias']) || !isset($bloque['horarios'])) {
                    continue;
                }

                $diasSeleccionados = $bloque['dias'];
                $horarios = $bloque['horarios'];

                // Si es 24 horas
                if (isset($horarios['es_24h']) && $horarios['es_24h']) {
                    foreach ($diasSeleccionados as $dia) {
                        if (in_array($dia, $diasSemana)) {
                            $horarioProcesado[$dia] = [['apertura' => '00:00', 'cierre' => '23:59']];
                        }
                    }
                    continue;
                }

                // Procesar rangos horarios
                $rangosHorarios = [];
                if (isset($horarios['rangos']) && is_array($horarios['rangos'])) {
                    foreach ($horarios['rangos'] as $rango) {
                        if (!empty($rango['apertura']) && !empty($rango['cierre'])) {
                            // Validar que el cierre sea después de la apertura
                            if ($rango['cierre'] > $rango['apertura']) {
                                $rangosHorarios[] = [
                                    'apertura' => $rango['apertura'],
                                    'cierre' => $rango['cierre']
                                ];
                            }
                        }
                    }
                }

                // Solo asignar si hay rangos válidos
                if (!empty($rangosHorarios)) {
                    foreach ($diasSeleccionados as $dia) {
                        if (in_array($dia, $diasSemana)) {
                            $horarioProcesado[$dia] = $rangosHorarios;
                        }
                    }
                }
            }
        }

        // Eliminar días que no tienen horario (quedan como array vacío)
        // Esto evita que se guarden días sin horario en la base de datos
        foreach ($horarioProcesado as $dia => $horarios) {
            if (empty($horarios)) {
                unset($horarioProcesado[$dia]);
            }
        }

        return $horarioProcesado;
    }

    // Métodos de servicios
    public function agregarServicio(Request $request, $tallerId)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[^\<\>\{\}]*$/u',
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[^\<\>\{\}]*$/u',
            ],
            'precio' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\d\.,\s\$]*$/',
            ],
            'activo' => 'boolean',
        ], [
            'nombre.regex' => 'El nombre del servicio contiene caracteres no permitidos.',
            'descripcion.regex' => 'La descripción del servicio contiene caracteres no permitidos.',
            'precio.regex' => 'El precio solo puede contener números, puntos, comas y $.',
        ]);

        if (!$this->firestoreDb) {
            return back()->withErrors(['error' => 'Error de conexión con la base de datos.']);
        }

        try {
            $tallerRef = $this->firestoreDb->collection('talleres')->document($tallerId);
            $tallerSnapshot = $tallerRef->snapshot();

            if (!$tallerSnapshot->exists()) {
                return back()->withErrors(['error' => 'Taller no encontrado.']);
            }

            $tallerData = $tallerSnapshot->data();
            $servicios = $tallerData['servicios_count'] ?? [];

            // 🔒 Sanitizar
            $nuevoServicio = [
                'id'          => 'servicio_' . uniqid(),
                'nombre'      => $this->sanitize($validated['nombre'], 100),
                'descripcion' => $this->sanitize($validated['descripcion'] ?? '', 500),
                'precio'      => preg_replace('/[^\d\.,\$]/', '', $validated['precio'] ?? ''),
                'activo'      => (bool) ($validated['activo'] ?? true),
            ];

            $servicios[] = $nuevoServicio;

            $tallerRef->update([
                ['path' => 'servicios_count', 'value' => $servicios]
            ]);

            Log::info('Servicio agregado al taller: ' . $tallerId);

            session()->forget('taller_data');
            return back()->with('success', 'Servicio agregado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error agregando servicio: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al agregar el servicio: ' . $e->getMessage()]);
        }
    }

    public function eliminarServicio(Request $request, $tallerId)
    {
        $validated = $request->validate([
            'servicio_id' => 'required|string',
        ]);

        if (!$this->firestoreDb) {
            return back()->withErrors(['error' => 'Error de conexión con la base de datos.']);
        }

        try {
            $tallerRef = $this->firestoreDb->collection('talleres')->document($tallerId);
            $tallerSnapshot = $tallerRef->snapshot();

            if (!$tallerSnapshot->exists()) {
                return back()->withErrors(['error' => 'Taller no encontrado.']);
            }

            $tallerData = $tallerSnapshot->data();
            $servicios = $tallerData['servicios_count'] ?? [];

            $servicios = array_filter($servicios, function ($servicio) use ($validated) {
                return ($servicio['id'] ?? '') !== $validated['servicio_id'];
            });

            $servicios = array_values($servicios);

            $tallerRef->update([
                ['path' => 'servicios_count', 'value' => $servicios]
            ]);

            Log::info('Servicio eliminado del taller: ' . $tallerId);

            session()->forget('taller_data');
            return back()->with('success', 'Servicio eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error eliminando servicio: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el servicio: ' . $e->getMessage()]);
        }
    }

    public function toggleServicio(Request $request, $tallerId)
    {
        $validated = $request->validate([
            'servicio_id' => 'required|string',
            'activo' => 'required|boolean',
        ]);

        if (!$this->firestoreDb) {
            return back()->withErrors(['error' => 'Error de conexión con la base de datos.']);
        }

        try {
            $tallerRef = $this->firestoreDb->collection('talleres')->document($tallerId);
            $tallerSnapshot = $tallerRef->snapshot();

            if (!$tallerSnapshot->exists()) {
                return back()->withErrors(['error' => 'Taller no encontrado.']);
            }

            $tallerData = $tallerSnapshot->data();
            $servicios = $tallerData['servicios_count'] ?? [];

            foreach ($servicios as &$servicio) {
                if (($servicio['id'] ?? '') === $validated['servicio_id']) {
                    $servicio['activo'] = $validated['activo'];
                    break;
                }
            }

            $tallerRef->update([
                ['path' => 'servicios_count', 'value' => $servicios]
            ]);

            Log::info('Estado del servicio actualizado en taller: ' . $tallerId);

            session()->forget('taller_data');
            return back()->with('success', 'Estado del servicio actualizado.');
        } catch (\Exception $e) {
            Log::error('Error actualizando estado del servicio: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar el estado del servicio.']);
        }
    }

    private function sanitize(string $value, int $max = 2000): string
    {
        // 1) Quitar etiquetas HTML/PHP
        $value = strip_tags($value);

        // 2) Quitar null bytes y caracteres de control
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

        // 3) Decodificar entidades para no guardar &lt; como texto literal
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 4) Neutralizar < > { }
        $value = str_replace(['<', '>', '{', '}'], '', $value);

        // 5) Normalizar espacios
        $value = preg_replace('/\s+/u', ' ', trim($value));

        return mb_substr($value, 0, $max);
    }
}

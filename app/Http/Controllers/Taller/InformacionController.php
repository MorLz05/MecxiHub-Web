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
        $tallerId = null;
        $bloques = [];

        if ($this->firestoreDb) {
            try {
                $usuariosRef = $this->firestoreDb->collection('usuarios')->document($user['uid']);
                $usuarioSnapshot = $usuariosRef->snapshot();

                if ($usuarioSnapshot->exists()) {
                    $usuarioData = $usuarioSnapshot->data();
                    $tallerId = $usuarioData['taller_id'] ?? null;

                    if ($tallerId) {
                        $tallerRef = $this->firestoreDb->collection('talleres')->document($tallerId);
                        $tallerSnapshot = $tallerRef->snapshot();

                        if ($tallerSnapshot->exists()) {
                            $tallerData = $tallerSnapshot->data();
                            $tallerData['id'] = $tallerId;

                            // Asegurar que todos los campos existan
                            $tallerData['descripcion'] = $tallerData['descripcion'] ?? '';
                            $tallerData['servicios_count'] = $tallerData['servicios_count'] ?? [];
                            $tallerData['horario'] = $tallerData['horario'] ?? [];
                            $tallerData['verificado'] = $tallerData['verificado'] ?? false;
                            $tallerData['plan'] = $tallerData['plan'] ?? null;
                            $tallerData['email'] = $tallerData['email'] ?? '';

                            // Cargar imágenes del establecimiento
                            $tallerData['imagenes'] = [];
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
                                    $tallerData['imagenes'][] = $imgData;
                                }

                                // Ordenar por fecha de subida descendente
                                usort($tallerData['imagenes'], function ($a, $b) {
                                    return strtotime($b['fecha_subida'] ?? '0') - strtotime($a['fecha_subida'] ?? '0');
                                });
                            } catch (\Exception $e) {
                                Log::warning('No se pudieron cargar imágenes del taller: ' . $e->getMessage());
                            }

                            // Asegurar campos del logo
                            $tallerData['logo_url'] = $tallerData['logo_url'] ?? null;

                            session(['taller_data' => $tallerData]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error obteniendo datos del taller: ' . $e->getMessage());
            }
        }

        // Procesar horario para construir bloques
        $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
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
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'direccion' => 'required|string|max:500',
            'telefono' => 'required|string|max:20',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
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

            $updates = [
                ['path' => 'nombre', 'value' => $validated['nombre']],
                ['path' => 'descripcion', 'value' => $validated['descripcion'] ?? ''],
                ['path' => 'direccion', 'value' => $validated['direccion']],
                ['path' => 'telefono', 'value' => $validated['telefono']],
            ];

            // Guardar coordenadas si vinieron
            if (!empty($validated['latitud']) && !empty($validated['longitud'])) {
                $updates[] = ['path' => 'latitud', 'value' => (float) $validated['latitud']];
                $updates[] = ['path' => 'longitud', 'value' => (float) $validated['longitud']];
            }

            $tallerRef->update($updates);

            Log::info('Información básica del taller actualizada: ' . $tallerId);

            $tallerData = session('taller_data', []);
            $tallerData['nombre'] = $validated['nombre'];
            $tallerData['descripcion'] = $validated['descripcion'] ?? '';
            $tallerData['direccion'] = $validated['direccion'];
            $tallerData['telefono'] = $validated['telefono'];
            if (!empty($validated['latitud']) && !empty($validated['longitud'])) {
                $tallerData['latitud'] = (float) $validated['latitud'];
                $tallerData['longitud'] = (float) $validated['longitud'];
            }
            session(['taller_data' => $tallerData]);

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

    // Métodos de servicios (se mantienen igual)
    public function agregarServicio(Request $request, $tallerId)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio' => 'nullable|string|min:1|max:20',
            'activo' => 'boolean',
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

            $nuevoServicio = [
                'id' => 'servicio_' . uniqid(),
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? '',
                'precio' => $validated['precio'] ?? 0,
                'activo' => $validated['activo'] ?? true,
            ];

            $servicios[] = $nuevoServicio;

            $tallerRef->update([
                ['path' => 'servicios_count', 'value' => $servicios]
            ]);

            Log::info('Servicio agregado al taller: ' . $tallerId);

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

            return back()->with('success', 'Estado del servicio actualizado.');
        } catch (\Exception $e) {
            Log::error('Error actualizando estado del servicio: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar el estado del servicio.']);
        }
    }
}

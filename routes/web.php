<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\FirebaseAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Conductor\Dashboard\VehiculoController;
use App\Http\Controllers\GestorMaestro\Dashboard\TallerController as GestorTallerController;
use App\Http\Controllers\GestorMaestro\Dashboard\UsuarioController as GestorUsuarioController;
use App\Http\Controllers\GestorMaestro\Dashboard\PlanController as GestorPlanController;
use App\Http\Controllers\Taller\Dashboard\CuentaController as TallerCuentaController;
use App\Http\Controllers\Taller\CalificacionController;

// ===== VISTAS PÚBLICAS =====
Route::get('/', [App\Http\Controllers\Conductor\Dashboard\HomeController::class, 'index'])->name('home');

// ===== AUTENTICACIÓN =====
Route::get('/login', [FirebaseAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [FirebaseAuthController::class, 'login']);
Route::get('/register', [FirebaseAuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [FirebaseAuthController::class, 'register']);
Route::post('/logout', [FirebaseAuthController::class, 'logout'])->name('logout');

// ===== GOOGLE SIGN-IN =====
Route::post('/auth/google', [FirebaseAuthController::class, 'googleLogin'])->name('auth.google');

// ===== RUTAS DEL NAVBAR (Públicas) =====
Route::get('/buscar-talleres', [App\Http\Controllers\Conductor\Dashboard\BusquedaController::class, 'index'])
    ->name('buscar.talleres');

Route::get('/perfil-taller/{id}', [App\Http\Controllers\Conductor\Dashboard\PerfilTallerController::class, 'show'])
    ->name('taller.perfil');

Route::get('/mis-servicios', function () {
    return view('conductor.principal');
})->name('mis.servicios');

Route::get('/asistente-ia', function () {
    return view('conductor.asistente');
})->name('asistente.ia');

// ===== CALIFICACIÓN (PÚBLICA) =====
Route::get('/calificacion/{token}', [CalificacionController::class, 'showForm'])->name('calificacion.form');
Route::post('/calificacion/{token}', [CalificacionController::class, 'store'])->name('calificacion.store');

// ===== PASSWORD RESET =====
Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])
    ->name('password.email');

Route::get('/password/reset-form', [PasswordResetController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/password/update', [PasswordResetController::class, 'resetPassword'])
    ->name('password.update');

// ===== WEBHOOK STRIPE (público) =====
Route::post('/stripe/webhook', [App\Http\Controllers\Taller\PagoController::class, 'webhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// ===== RUTAS PROTEGIDAS (conductor) =====
Route::middleware(['auth.firebase'])->group(function () {
    Route::get('/dashboard', [FirebaseAuthController::class, 'conductorDashboard'])->name('dashboard');

    // ===== CUENTA DEL CONDUCTOR =====
    Route::prefix('cuenta')->name('cuenta.')->group(function () {
        Route::get('/resumen', function () {
            $uid = session('firebase_user.uid');
            $vehiculos = [];
            $stats = [
                'total' => 0,
                'activos' => 0,
                'listos' => 0,
                'cancelados' => 0,
                'talleres_visitados' => 0,
                'ultimos' => [],
            ];

            if ($uid) {
                // Stats (reusa ServicioController::stats)
                try {
                    $stats = (new \App\Http\Controllers\Conductor\Dashboard\ServicioController)->stats();
                } catch (\Exception $e) {
                    \Log::warning('Error stats resumen: ' . $e->getMessage());
                }

                // Vehículos
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
                        $db = new \Google\Cloud\Firestore\FirestoreClient([
                            'keyFilePath' => $credentialsPath,
                            'projectId' => $creds['project_id'] ?? env('FIREBASE_PROJECT_ID'),
                        ]);

                        $snap = $db->collection('usuarios')->document($uid)->collection('vehiculos')->documents();
                        foreach ($snap as $doc) {
                            if ($doc->exists()) {
                                $data = $doc->data();
                                $data['id'] = $doc->id();
                                $vehiculos[] = $data;
                            }
                        }
                        usort(
                            $vehiculos,
                            fn($a, $b) =>
                            strtotime($b['fecha_creacion'] ?? '0') - strtotime($a['fecha_creacion'] ?? '0')
                        );
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error vehículos resumen: ' . $e->getMessage());
                }
            }

            return view('conductor.dashboard.resumen', [
                'vehiculos' => $vehiculos,
                'stats' => $stats,
            ]);
        })->name('resumen');

        Route::get('/informacion-personal', function () {
            return view('conductor.dashboard.informacion-personal');
        })->name('informacion-personal');

        // Rutas de vehículos
        Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos');
        Route::get('/vehiculos/crear', [VehiculoController::class, 'create'])->name('vehiculos.crear');
        Route::post('/vehiculos', [VehiculoController::class, 'store'])->name('vehiculos.store');
        Route::get('/vehiculos/{id}', [VehiculoController::class, 'show'])->name('vehiculos.ver');
        Route::get('/vehiculos/{id}/editar', [VehiculoController::class, 'edit'])->name('vehiculos.editar');
        Route::put('/vehiculos/{id}', [VehiculoController::class, 'update'])->name('vehiculos.update');
        Route::delete('/vehiculos/{id}', [VehiculoController::class, 'destroy'])->name('vehiculos.destroy');
        Route::get('/vehiculos/{id}/servicios', [VehiculoController::class, 'servicios'])->name('vehiculos.servicios');

        // Servicios / órdenes del conductor
        Route::get('/servicios', [App\Http\Controllers\Conductor\Dashboard\ServicioController::class, 'index'])
            ->name('servicios.index');
        Route::get('/servicios/{id}', [App\Http\Controllers\Conductor\Dashboard\ServicioController::class, 'show'])
            ->name('servicios.detalle');
    });

    // Actualizar perfil
    Route::put('/usuario/perfil', [FirebaseAuthController::class, 'updateProfile'])->name('usuario.actualizar.perfil');
    Route::put('/usuario/password', [FirebaseAuthController::class, 'updatePassword'])->name('usuario.actualizar.password');

    // Crear reseña de un taller
    Route::post('/perfil-taller/{id}/resena', [App\Http\Controllers\Conductor\Dashboard\PerfilTallerController::class, 'storeResena'])
        ->name('taller.resena.store');

    // Notificaciones del conductor
    Route::prefix('cuenta/notificaciones')->name('cuenta.notificaciones.')->group(function () {
        Route::get('/', [App\Http\Controllers\Conductor\Dashboard\NotificacionController::class, 'index'])->name('index');
        Route::post('/{id}/leida', [App\Http\Controllers\Conductor\Dashboard\NotificacionController::class, 'marcarLeida'])->name('leida');
        Route::post('/marcar-todas', [App\Http\Controllers\Conductor\Dashboard\NotificacionController::class, 'marcarTodasLeidas'])->name('marcar-todas');
        Route::delete('/{id}', [App\Http\Controllers\Conductor\Dashboard\NotificacionController::class, 'eliminar'])->name('eliminar');
        Route::get('/contar', [App\Http\Controllers\Conductor\Dashboard\NotificacionController::class, 'contarNoLeidas'])->name('contar');
    });
});

// ===== RUTAS DEL GESTOR MAESTRO =====
Route::middleware(['auth.firebase', 'gestor'])->prefix('gestor')->name('gestor.')->group(function () {
    Route::get('/cuenta', function () {
        return view('GestorMaestro.Cuenta.principal');
    })->name('cuenta');

    Route::get('/talleres', [GestorTallerController::class, 'index'])->name('talleres');
    Route::get('/taller/{id}', [GestorTallerController::class, 'show'])->name('taller.show');
    Route::post('/taller/{id}/toggle-verificado', [GestorTallerController::class, 'toggleVerificado'])->name('taller.toggle-verificado');

    Route::get('/usuarios', [GestorUsuarioController::class, 'index'])->name('usuarios');
    Route::get('/usuario/crear', [GestorUsuarioController::class, 'create'])->name('usuarios.crear');
    Route::post('/usuario/store', [GestorUsuarioController::class, 'store'])->name('usuarios.store');
    Route::post('/usuario/{uid}/toggle-activo', [GestorUsuarioController::class, 'toggleActivo'])->name('usuario.toggle-activo');

    // Planes
    Route::get('/planes', [GestorPlanController::class, 'index'])->name('planes');
    Route::get('/plan/crear', [GestorPlanController::class, 'create'])->name('planes.crear');
    Route::post('/plan/store', [GestorPlanController::class, 'store'])->name('planes.store');
    Route::get('/plan/{id}/editar', [GestorPlanController::class, 'edit'])->name('planes.editar');
    Route::put('/plan/{id}', [GestorPlanController::class, 'update'])->name('planes.update');
    Route::delete('/plan/{id}', [GestorPlanController::class, 'destroy'])->name('planes.eliminar');
    Route::post('/plan/{id}/toggle-activo', [GestorPlanController::class, 'toggleActivo'])->name('plan.toggle-activo');
});

// ==================== RUTAS PROTEGIDAS (Taller / Administrador) ====================
Route::middleware(['taller'])->prefix('taller')->name('taller.')->group(function () {
    // Dashboard / Resumen
    Route::get('/dashboard', [App\Http\Controllers\Taller\DashboardController::class, 'index'])->name('dashboard');

    // Información del taller
    Route::get('/informacion', [App\Http\Controllers\Taller\InformacionController::class, 'index'])->name('informacion');
    Route::put('/informacion/{tallerId}', [App\Http\Controllers\Taller\InformacionController::class, 'update'])->name('informacion.update');
    Route::put('/informacion/{tallerId}/horario', [App\Http\Controllers\Taller\InformacionController::class, 'updateHorario'])->name('informacion.horario.update');
    Route::post('/informacion/{tallerId}/servicio', [App\Http\Controllers\Taller\InformacionController::class, 'agregarServicio'])->name('informacion.servicio.agregar');
    Route::delete('/informacion/{tallerId}/servicio', [App\Http\Controllers\Taller\InformacionController::class, 'eliminarServicio'])->name('informacion.servicio.eliminar');
    Route::put('/informacion/{tallerId}/servicio/toggle', [App\Http\Controllers\Taller\InformacionController::class, 'toggleServicio'])->name('informacion.servicio.toggle');

    // Imágenes del taller
    Route::post('/informacion/logo', [App\Http\Controllers\Taller\ImagenController::class, 'subirLogo'])->name('informacion.logo.subir');
    Route::delete('/informacion/logo', [App\Http\Controllers\Taller\ImagenController::class, 'eliminarLogo'])->name('informacion.logo.eliminar');
    Route::post('/informacion/imagenes', [App\Http\Controllers\Taller\ImagenController::class, 'subirImagenes'])->name('informacion.imagenes.subir');
    Route::delete('/informacion/imagenes/{imagenId}', [App\Http\Controllers\Taller\ImagenController::class, 'eliminarImagen'])->name('informacion.imagenes.eliminar');

    // Órdenes
    Route::get('/ordenes', [App\Http\Controllers\Taller\DashboardController::class, 'index'])->name('ordenes');
    Route::get('/ordenes/{id}/imprimir', [App\Http\Controllers\Taller\OrdenController::class, 'imprimir'])->name('ordenes.imprimir');

    // Evidencias de órdenes
    Route::post('/ordenes/{ordenId}/evidencias', [App\Http\Controllers\Taller\EvidenciaController::class, 'subir'])
        ->name('ordenes.evidencias.subir');
    Route::delete('/ordenes/{ordenId}/evidencias/{evidenciaId}', [App\Http\Controllers\Taller\EvidenciaController::class, 'eliminar'])
        ->name('ordenes.evidencias.eliminar');
    Route::get('/ordenes/{ordenId}/evidencias', [App\Http\Controllers\Taller\EvidenciaController::class, 'listar'])
        ->name('ordenes.evidencias.listar');

    // Personal
    Route::get('/personal', [App\Http\Controllers\Taller\PersonalController::class, 'index'])->name('personal');
    Route::post('/personal', [App\Http\Controllers\Taller\PersonalController::class, 'store'])->name('personal.store');
    Route::put('/personal/{id}', [App\Http\Controllers\Taller\PersonalController::class, 'update'])->name('personal.update');
    Route::delete('/personal/{id}', [App\Http\Controllers\Taller\PersonalController::class, 'destroy'])->name('personal.destroy');
    Route::post('/personal/{id}/toggle-activo', [App\Http\Controllers\Taller\PersonalController::class, 'toggleActivo'])->name('personal.toggle-activo');

    // Asistente IA
    Route::get('/asistente', function () {
        return view('taller.asistente');
    })->name('asistente');

    // Comentarios / Reseñas
    Route::get('/comentarios', [App\Http\Controllers\Taller\ComentarioController::class, 'index'])->name('comentarios');
    Route::post('/comentarios/{resenaId}/responder', [App\Http\Controllers\Taller\ComentarioController::class, 'responder'])->name('comentarios.responder');
    Route::delete('/comentarios/{resenaId}/respuesta', [App\Http\Controllers\Taller\ComentarioController::class, 'eliminarRespuesta'])->name('comentarios.respuesta.eliminar');

    // Planes
    Route::get('/planes', function () {
        return view('taller.planes');
    })->name('planes');

    // Seguridad
    Route::get('/seguridad', [App\Http\Controllers\Taller\SeguridadController::class, 'index'])->name('seguridad');
    Route::put('/seguridad/email', [App\Http\Controllers\Taller\SeguridadController::class, 'updateEmail'])->name('seguridad.update.email');
    Route::put('/seguridad/password', [App\Http\Controllers\Taller\SeguridadController::class, 'updatePassword'])->name('seguridad.update.password');

    // Planes y pagos
    Route::get('/planes', [App\Http\Controllers\Taller\PlanController::class, 'index'])->name('planes');
    Route::post('/planes/pagar', [App\Http\Controllers\Taller\PagoController::class, 'crearSesion'])->name('planes.pagar');
    Route::get('/planes/exito', [App\Http\Controllers\Taller\PagoController::class, 'exito'])->name('planes.exito');

    // Órdenes de trabajo
    Route::get('/ordenes', [App\Http\Controllers\Taller\OrdenController::class, 'index'])->name('ordenes');
    Route::get('/ordenes/crear', [App\Http\Controllers\Taller\OrdenController::class, 'create'])->name('ordenes.crear');
    Route::post('/ordenes', [App\Http\Controllers\Taller\OrdenController::class, 'store'])->name('ordenes.store');
    Route::get('/ordenes/{id}', [App\Http\Controllers\Taller\OrdenController::class, 'show'])->name('ordenes.show');
    Route::get('/ordenes/{id}/editar', [App\Http\Controllers\Taller\OrdenController::class, 'edit'])->name('ordenes.editar');
    Route::put('/ordenes/{id}', [App\Http\Controllers\Taller\OrdenController::class, 'update'])->name('ordenes.update');
    Route::post('/ordenes/{id}/cambiar-estado', [App\Http\Controllers\Taller\OrdenController::class, 'cambiarEstado'])->name('ordenes.cambiar-estado');

    // Cuenta (perfil)
    Route::get('/cuenta', function () {
        return view('taller.cuenta');
    })->name('cuenta');
});

// ===== CALIFICACIÓN (PÚBLICA) =====
Route::get('/calificacion/{token}', [CalificacionController::class, 'showForm'])->name('calificacion.form');
Route::post('/calificacion/{token}', [CalificacionController::class, 'store'])->name('calificacion.store');

<?php

namespace App\Http\Controllers\Conductor\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class PlanController extends Controller
{
    protected $firestoreDb;

    public function __construct(FirestoreClient $firestoreDb)
    {
        $this->firestoreDb = $firestoreDb;
    }

    /**
     * Lista todos los planes dirigidos al tipo de usuario "Conductor"
     */
    public function index()
    {
        $user = session('firebase_user');
        $planes = [];
        $planActualId = null;

        if ($this->firestoreDb) {
            try {
                // 1. Obtener el usuario para saber su plan actual
                $usuarioDoc = $this->firestoreDb->collection('usuarios')
                    ->document($user['uid'])
                    ->snapshot();

                if ($usuarioDoc->exists()) {
                    $usuarioData = $usuarioDoc->data();
                    // El conductor guarda su plan directamente en su documento
                    $planActualId = $usuarioData['plan']['id']
                        ?? $usuarioData['plan_id']
                        ?? null;
                }

                // 2. Obtener todos los planes de Firestore
                $snapshot = $this->firestoreDb->collection('planes')->documents();

                foreach ($snapshot as $doc) {
                    if (!$doc->exists()) continue;

                    $data = $doc->data();
                    $data['id'] = $doc->id();

                    // Solo planes activos
                    $activo = $data['activo'] ?? true;
                    if (!$activo) continue;

                    // Filtrar solo los que van dirigidos a "Conductor"
                    $tipos = $data['tipos_usuario'] ?? [];
                    if (!is_array($tipos) || !in_array('Conductor', $tipos, true)) {
                        continue;
                    }

                    $planes[] = $data;
                }

                // 3. Ordenar: primero por "orden" ascendente, luego por precio ascendente
                usort($planes, function ($a, $b) {
                    $ordenA = $a['orden'] ?? 999;
                    $ordenB = $b['orden'] ?? 999;
                    if ($ordenA === $ordenB) {
                        return ($a['precio'] ?? 0) <=> ($b['precio'] ?? 0);
                    }
                    return $ordenA <=> $ordenB;
                });
            } catch (\Exception $e) {
                Log::error('Error obteniendo planes para conductor: ' . $e->getMessage());
            }
        }

        // Marcar cuál es el plan actual del conductor
        foreach ($planes as &$plan) {
            $plan['es_plan_actual'] = ($planActualId && $plan['id'] === $planActualId);
        }
        unset($plan);

        return view('conductor.dashboard.planes', compact('planes'));
    }
}
<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Google\Cloud\Firestore\FirestoreClient;

class EvidenciaController extends Controller
{
    protected $firestoreDb;
    protected $storage;
    protected $bucketName;

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

            if (!$credentialsPath) {
                throw new \Exception('No se encontró el archivo de credenciales');
            }

            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

            $jsonContent = file_get_contents($credentialsPath);
            $credentials = json_decode($jsonContent, true);

            $this->firestoreDb = new FirestoreClient([
                'keyFilePath' => $credentialsPath,
                'projectId' => $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID'),
            ]);

            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->bucketName = env('FIREBASE_STORAGE_BUCKET', ($credentials['project_id'] ?? '') . '.firebasestorage.app');
            $this->storage = $factory->createStorage();
            $this->storage->getBucket($this->bucketName);
        } catch (\Exception $e) {
            Log::error('Error inicializando EvidenciaController: ' . $e->getMessage());
            $this->firestoreDb = null;
            $this->storage = null;
            $this->bucketName = null;
        }
    }

    private function obtenerTallerIdDelUsuario()
    {
        $user = session('firebase_user');
        if (!$user || !$this->firestoreDb) return null;

        try {
            $doc = $this->firestoreDb->collection('usuarios')->document($user['uid'])->snapshot();
            if ($doc->exists()) {
                return $doc->data()['taller_id'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo taller_id: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Verifica que la orden pertenece al taller del usuario
     */
    private function ordenPerteneceAlTaller($ordenId, $tallerId)
    {
        $doc = $this->firestoreDb->collection('ordenes_trabajo')->document($ordenId)->snapshot();
        if (!$doc->exists()) return null;
        $orden = $doc->data();
        if (($orden['taller_id'] ?? null) !== $tallerId) return null;
        return $orden;
    }

    /**
     * Subir evidencias
     * Body: { archivos: [File], tipo: 'falla'|'vehiculo', falla_index: int|null }
     */
    public function subir(Request $request, $ordenId)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->storage || !$this->firestoreDb) {
            return response()->json(['error' => 'No autorizado o servicios no disponibles'], 401);
        }

        $orden = $this->ordenPerteneceAlTaller($ordenId, $tallerId);
        if (!$orden) {
            return response()->json(['error' => 'Orden no encontrada o sin permiso'], 404);
        }

        $validated = $request->validate([
            'evidencias' => 'required|array|min:1|max:6',
            'evidencias.*' => 'image|mimes:jpg,jpeg,png,webp,heic,heif|max:10240',
            'tipo' => 'required|in:falla,vehiculo',
            'falla_index' => 'nullable|integer|min:0',
        ]);

        try {
            $bucket = $this->storage->getBucket($this->bucketName);
            $subidas = [];

            // Subcarpeta según el tipo
            $subcarpeta = $validated['tipo'] === 'vehiculo'
                ? "talleres/{$tallerId}/ordenes/{$ordenId}/vehiculo"
                : "talleres/{$tallerId}/ordenes/{$ordenId}/evidencias";

            foreach ($request->file('evidencias') as $file) {
                $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $nombre = 'evid_' . time() . '_' . uniqid() . '.' . $ext;
                $path = "{$subcarpeta}/{$nombre}";
                $token = (string) Str::uuid();

                // 1. Subir a Storage
                $bucket->upload(
                    fopen($file->getRealPath(), 'r'),
                    [
                        'name' => $path,
                        'metadata' => ['firebaseStorageDownloadTokens' => $token],
                    ]
                );

                // 2. Construir URL pública
                $url = "https://firebasestorage.googleapis.com/v0/b/{$this->bucketName}/o/"
                    . urlencode($path)
                    . "?alt=media&token={$token}";

                // 3. Guardar referencia en Firestore
                $evidenciaData = [
                    'url' => $url,
                    'path' => $path,
                    'nombre_original' => $file->getClientOriginalName(),
                    'tipo' => $validated['tipo'],
                    'falla_index' => $validated['tipo'] === 'falla'
                        ? (int) ($validated['falla_index'] ?? 0)
                        : null,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'fecha_subida' => now()->toDateTimeString(),
                    'subido_por' => session('firebase_user.uid'),
                ];

                $docRef = $this->firestoreDb
                    ->collection('ordenes_trabajo')
                    ->document($ordenId)
                    ->collection('evidencias')
                    ->document('evid_' . uniqid());

                $docRef->set($evidenciaData);

                $evidenciaData['id'] = $docRef->id();
                $subidas[] = $evidenciaData;
            }

            return response()->json([
                'success' => true,
                'evidencias' => $subidas,
                'message' => count($subidas) . ' evidencia(s) subida(s)',
            ]);
        } catch (\Exception $e) {
            Log::error('Error subiendo evidencias: ' . $e->getMessage());
            return response()->json(['error' => 'Error al subir: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar una evidencia
     */
    public function eliminar($ordenId, $evidenciaId)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->storage || !$this->firestoreDb) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        if (!$this->ordenPerteneceAlTaller($ordenId, $tallerId)) {
            return response()->json(['error' => 'Sin permiso'], 403);
        }

        try {
            $ref = $this->firestoreDb
                ->collection('ordenes_trabajo')
                ->document($ordenId)
                ->collection('evidencias')
                ->document($evidenciaId);

            $doc = $ref->snapshot();
            if (!$doc->exists()) {
                return response()->json(['error' => 'Evidencia no encontrada'], 404);
            }

            $data = $doc->data();
            $path = $data['path'] ?? null;

            // 1. Borrar del Storage
            if ($path) {
                try {
                    $this->storage->getBucket($this->bucketName)->object($path)->delete();
                } catch (\Exception $e) {
                    Log::warning('No se pudo borrar del storage: ' . $e->getMessage());
                }
            }

            // 2. Borrar de Firestore
            $ref->delete();

            return response()->json(['success' => true, 'message' => 'Evidencia eliminada']);
        } catch (\Exception $e) {
            Log::error('Error eliminando evidencia: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar'], 500);
        }
    }

    /**
     * Listar evidencias de una orden (útil para AJAX)
     */
    public function listar($ordenId)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->firestoreDb) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        if (!$this->ordenPerteneceAlTaller($ordenId, $tallerId)) {
            return response()->json(['error' => 'Sin permiso'], 403);
        }

        try {
            $snapshot = $this->firestoreDb
                ->collection('ordenes_trabajo')
                ->document($ordenId)
                ->collection('evidencias')
                ->documents();

            $evidencias = [];
            foreach ($snapshot as $doc) {
                if (!$doc->exists()) continue;
                $data = $doc->data();
                $data['id'] = $doc->id();
                $evidencias[] = $data;
            }

            return response()->json(['success' => true, 'evidencias' => $evidencias]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al listar'], 500);
        }
    }
}

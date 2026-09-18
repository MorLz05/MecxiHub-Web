<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Storage;
use Google\Cloud\Firestore\FirestoreClient;

class ImagenController extends Controller
{

    protected FirestoreClient $firestoreDb;
    protected Storage $storage;
    protected string $bucketName;

    public function __construct(FirestoreClient $firestoreDb, Storage $storage)
    {
        $this->firestoreDb = $firestoreDb;
        $this->storage = $storage;
        $this->bucketName = env('FIREBASE_STORAGE_BUCKET', 'mecxihub-db.firebasestorage.app');
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

            if (!$credentialsPath) {
                throw new \Exception('No se encontró el archivo de credenciales');
            }

            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

            $jsonContent = file_get_contents($credentialsPath);
            $credentials = json_decode($jsonContent, true);

            // Firestore
            $config = [
                'keyFilePath' => $credentialsPath,
                'projectId' => $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID'),
            ];
            $this->firestoreDb = new FirestoreClient($config);

            // Storage
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->bucketName = env('FIREBASE_STORAGE_BUCKET', ($credentials['project_id'] ?? '') . '.appspot.com');
            $this->storage = $factory->createStorage();
            $this->storage->getBucket($this->bucketName); // valida conexión

            Log::info('Storage inicializado en ImagenController. Bucket: ' . $this->bucketName);
        } catch (\Exception $e) {
            Log::error('Error inicializando ImagenController: ' . $e->getMessage());
            $this->firestoreDb = null;
            $this->storage = null;
        }
    } */

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
     * Subir / reemplazar el logo del taller
     */
    public function subirLogo(Request $request)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->storage || !$this->firestoreDb) {
            return back()->withErrors(['error' => 'Acceso no autorizado o servicios no disponibles.']);
        }

        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp,svg|max:5120', // 5MB
        ]);

        try {
            $file = $request->file('logo');

            // Eliminar logo anterior si existe
            $tallerRef = $this->firestoreDb->collection('talleres')->document($tallerId);
            $tallerData = $tallerRef->snapshot()->data() ?? [];
            $logoAnterior = $tallerData['logo_path'] ?? null; // guardamos el path interno para poder borrar

            if ($logoAnterior) {
                try {
                    $this->storage->getBucket($this->bucketName)->object($logoAnterior)->delete();
                } catch (\Exception $e) {
                    Log::warning('No se pudo borrar logo anterior: ' . $e->getMessage());
                }
            }

            // Path: talleres/{tallerId}/logo/{timestamp}.{ext}
            $ext = $file->getClientOriginalExtension();
            $path = "talleres/{$tallerId}/logo/logo_" . time() . '.' . $ext;
            $token = (string) \Illuminate\Support\Str::uuid();

            $bucket = $this->storage->getBucket($this->bucketName);
            $bucket->upload(
                fopen($file->getRealPath(), 'r'),
                [
                    'name' => $path,
                    'metadata' => [
                        'firebaseStorageDownloadTokens' => $token,
                    ],
                ]
            );

            $logoUrl = "https://firebasestorage.googleapis.com/v0/b/{$this->bucketName}/o/"
                . urlencode($path)
                . "?alt=media&token={$token}";

            // Guardar en Firestore
            $tallerRef->set([
                'logo_url' => $logoUrl,
                'logo_path' => $path,
                'fecha_actualizacion_logo' => now()->toDateTimeString(),
            ], ['merge' => true]);

            // Actualizar sesión
            $tallerSession = session('taller_data', []);
            $tallerSession['logo_url'] = $logoUrl;
            $tallerSession['logo_path'] = $path;
            session(['taller_data' => $tallerSession]);

            Log::info('Logo subido para taller: ' . $tallerId);

            return back()->with('success_logo', 'Logo actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error subiendo logo: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al subir el logo: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminar el logo del taller
     */
    public function eliminarLogo()
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->storage || !$this->firestoreDb) {
            return back()->withErrors(['error' => 'Acceso no autorizado.']);
        }

        try {
            $tallerRef = $this->firestoreDb->collection('talleres')->document($tallerId);
            $data = $tallerRef->snapshot()->data() ?? [];
            $logoPath = $data['logo_path'] ?? null;

            if ($logoPath) {
                try {
                    $this->storage->getBucket($this->bucketName)->object($logoPath)->delete();
                } catch (\Exception $e) {
                    Log::warning('No se pudo borrar logo del storage: ' . $e->getMessage());
                }
            }

            $tallerRef->set([
                'logo_url' => null,
                'logo_path' => null,
                'fecha_actualizacion_logo' => now()->toDateTimeString(),
            ], ['merge' => true]);

            $tallerSession = session('taller_data', []);
            $tallerSession['logo_url'] = null;
            $tallerSession['logo_path'] = null;
            session(['taller_data' => $tallerSession]);

            return back()->with('success_logo', 'Logo eliminado.');
        } catch (\Exception $e) {
            Log::error('Error eliminando logo: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el logo.']);
        }
    }

    /**
     * Subir imágenes del establecimiento (varias)
     */
    public function subirImagenes(Request $request)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->storage || !$this->firestoreDb) {
            return back()->withErrors(['error' => 'Acceso no autorizado.']);
        }

        $request->validate([
            'imagenes' => 'required|array|min:1|max:10',
            'imagenes.*' => 'file|mimes:jpg,jpeg,png,webp,gif,bmp,heic,heif,avif|max:10240', // 10 MB, más formatos
        ], [
            'imagenes.*.mimes' => 'Solo se permiten imágenes JPG, PNG, WEBP, GIF, BMP, HEIC o AVIF.',
            'imagenes.*.max' => 'Cada imagen no puede pesar más de 10 MB.',
            'imagenes.*.uploaded' => 'El archivo no se pudo subir. Puede que sea muy grande o la conexión falló.',
        ]);

        try {
            $bucket = $this->storage->getBucket($this->bucketName);
            $subidas = 0;

            foreach ($request->file('imagenes') as $file) {
                $ext = $file->getClientOriginalExtension();
                $nombre = 'img_' . time() . '_' . uniqid() . '.' . $ext;
                $path = "talleres/{$tallerId}/establecimiento/{$nombre}";
                $token = (string) \Illuminate\Support\Str::uuid();

                $bucket->upload(
                    fopen($file->getRealPath(), 'r'),
                    [
                        'name' => $path,
                        'metadata' => [
                            'firebaseStorageDownloadTokens' => $token,
                        ],
                    ]
                );

                $url = "https://firebasestorage.googleapis.com/v0/b/{$this->bucketName}/o/"
                    . urlencode($path)
                    . "?alt=media&token={$token}";

                // Guardar cada imagen como documento en subcolección: talleres/{tallerId}/imagenes
                $documentId = uniqid('img_');

                $this->firestoreDb
                    ->collection('talleres')
                    ->document($tallerId)
                    ->collection('imagenes')
                    ->document($documentId)
                    ->set([
                        'url' => $url,
                        'path' => $path,
                        'nombre_original' => $file->getClientOriginalName(),
                        'tipo' => 'establecimiento',
                        'fecha_subida' => now()->toDateTimeString(),
                        'subido_por' => session('firebase_user.uid'),
                    ]);

                $subidas++;
            }

            Log::info("Se subieron {$subidas} imágenes al taller: " . $tallerId);

            return back()->with('success_imagenes', $subidas . ' imagen(es) subida(s) exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error subiendo imágenes: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al subir las imágenes: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminar una imagen del establecimiento
     */
    public function eliminarImagen($imagenId)
    {
        $tallerId = $this->obtenerTallerIdDelUsuario();
        if (!$tallerId || !$this->storage || !$this->firestoreDb) {
            return back()->withErrors(['error' => 'Acceso no autorizado.']);
        }

        try {
            $ref = $this->firestoreDb
                ->collection('talleres')
                ->document($tallerId)
                ->collection('imagenes')
                ->document($imagenId);

            $doc = $ref->snapshot();
            if (!$doc->exists()) {
                return back()->withErrors(['error' => 'Imagen no encontrada.']);
            }

            $data = $doc->data();
            $path = $data['path'] ?? null;

            if ($path) {
                try {
                    $this->storage->getBucket($this->bucketName)->object($path)->delete();
                } catch (\Exception $e) {
                    Log::warning('No se pudo borrar imagen del storage: ' . $e->getMessage());
                }
            }

            $ref->delete();

            return back()->with('success_imagenes', 'Imagen eliminada.');
        } catch (\Exception $e) {
            Log::error('Error eliminando imagen: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar la imagen.']);
        }
    }

    /**
     * Genera URL pública del objeto.
     * Prioriza el token de descarga de Firebase. Si no existe, lo crea.
     */
    private function generarUrlPublica($bucket, $path)
    {
        try {
            $object = $bucket->object($path);

            // 1. Intentar leer el metadata del objeto
            $object->reload();
            $info = $object->info();
            $metadata = $info['metadata'] ?? [];

            // 2. Si no tiene token de Firebase, generarle uno
            if (empty($metadata['firebaseStorageDownloadTokens'])) {
                $token = (string) \Illuminate\Support\Str::uuid();
                $object->update([
                    'metadata' => [
                        'firebaseStorageDownloadTokens' => $token,
                    ],
                ]);
            } else {
                $token = $metadata['firebaseStorageDownloadTokens'];
            }

            // 3. Construir URL de descarga oficial de Firebase
            return "https://firebasestorage.googleapis.com/v0/b/{$this->bucketName}/o/"
                . urlencode($path)
                . "?alt=media&token={$token}";
        } catch (\Exception $e) {
            Log::error('Error generando URL pública: ' . $e->getMessage());

            // Fallback: URL firmada (expira en 1 hora, solo para debug)
            try {
                $object = $bucket->object($path);
                return $object->signedUrl(new \DateTime('+1 hour'));
            } catch (\Exception $e2) {
                Log::error('Fallback signed URL también falló: ' . $e2->getMessage());
                return null;
            }
        }
    }
}

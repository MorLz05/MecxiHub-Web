<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Auth;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Storage;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        // ============================
        // Firebase Auth (singleton)
        // ============================
        $this->app->singleton(Auth::class, function ($app) {
            $credentials = $this->getCredentials();
            $factory = (new Factory)->withServiceAccount($credentials);
            return $factory->createAuth();
        });

        // ============================
        // Factory de Firebase (singleton)
        // ============================
        $this->app->singleton(Factory::class, function ($app) {
            $credentials = $this->getCredentials();
            return (new Factory)->withServiceAccount($credentials);
        });

        // ============================
        // FirestoreClient (singleton)
        // ============================
        $this->app->singleton(FirestoreClient::class, function ($app) {
            $credentials = $this->getCredentials();

            // Caso A: $credentials es un ARRAY (Laravel Cloud → JSON en env)
            if (is_array($credentials)) {
                return new FirestoreClient([
                    'projectId'   => $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID'),
                    'credentials' => $credentials, // Google SDK acepta el array directo
                ]);
            }

            // Caso B: $credentials es una RUTA a archivo (local)
            // Forzamos la variable de entorno para que el SDK no busque ADC
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentials);

            return new FirestoreClient([
                'keyFilePath' => $credentials,
                'projectId'   => env('FIREBASE_PROJECT_ID'),
            ]);
        });

        // ============================
        // Firebase Storage (singleton)
        // ============================
        $this->app->singleton(Storage::class, function ($app) {
            $credentials = $this->getCredentials();
            $factory = (new Factory)->withServiceAccount($credentials);
            $storage = $factory->createStorage();

            $bucketName = env('FIREBASE_STORAGE_BUCKET');
            if ($bucketName) {
                $storage->getBucket($bucketName); // valida que el bucket existe
            }

            return $storage;
        });

        // Alias opcionales para inyectar más fácil
        $this->app->alias(Auth::class, 'firebase.auth');
        $this->app->alias(FirestoreClient::class, 'firebase.firestore');
    }

    public function boot()
    {
        //
    }

    /**
     * Devuelve las credenciales como:
     *  - STRING con la ruta al archivo (entorno local con archivo)
     *  - ARRAY con el JSON decodificado (Laravel Cloud con variable de entorno)
     */
    private function getCredentials()
    {
        $value = env('FIREBASE_CREDENTIALS');

        // DEBUG TEMPORAL
        Log::info('FIREBASE_CREDENTIALS debug', [
            'es_null'    => is_null($value),
            'es_string'  => is_string($value),
            'longitud'   => is_string($value) ? strlen($value) : null,
            'preview'    => is_string($value) ? substr($value, 0, 100) : null,
        ]);

        if (empty($value)) {
            throw new \Exception('FIREBASE_CREDENTIALS no está configurada');
        }

        // -------------------------------------------------
        // CASO 1: es la ruta a un archivo existente (LOCAL)
        // -------------------------------------------------
        if (is_string($value) && file_exists($value)) {
            return $value;
        }

        // -------------------------------------------------
        // CASO 2: es el JSON completo en texto (CLOUD)
        // -------------------------------------------------
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // -------------------------------------------------
        // CASO 3: buscar el archivo en rutas típicas (LOCAL)
        // -------------------------------------------------
        if (is_string($value)) {
            $possiblePaths = [
                storage_path('app/' . $value),
                storage_path($value),
                base_path($value),
                base_path('storage/app/' . $value),
                base_path('storage/app/firebase-credentials.json'),
            ];

            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        throw new \Exception(
            'No se pudo resolver FIREBASE_CREDENTIALS. ' .
                'Debe ser una ruta de archivo válida o un JSON válido.'
        );
    }
}

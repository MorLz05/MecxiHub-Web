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

        if (empty($value)) {
            throw new \Exception('FIREBASE_CREDENTIALS está vacía o no está configurada en el entorno.');
        }

        // Caso 1: es una ruta de archivo que existe (local)
        if (is_string($value) && file_exists($value)) {
            return $value;
        }

        // Caso 2: es un JSON completo (Cloud o local)
        if (is_string($value)) {
            // Limpiar posibles comillas externas sobrantes
            $clean = trim($value, " \t\n\r\0\x0B\"'");

            $decoded = json_decode($clean, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // Guardar el error para el throw
            $jsonError = json_last_error_msg();
        } else {
            $jsonError = 'valor no es string';
        }

        // Caso 3: buscar en rutas típicas (local)
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

        // Error detallado
        $preview = is_string($value) ? substr($value, 0, 150) : gettype($value);
        throw new \Exception(
            "No se pudo resolver FIREBASE_CREDENTIALS. " .
                "Longitud: " . (is_string($value) ? strlen($value) : 'n/a') . ". " .
                "JSON error: {$jsonError}. " .
                "Preview: {$preview}"
        );
    }
}

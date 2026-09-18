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
            throw new \Exception('FIREBASE_CREDENTIALS no está configurada.');
        }

        // Caso 1: ruta de archivo existente (local)
        if (is_string($value) && file_exists($value)) {
            return $value;
        }

        // Caso 2: JSON en string (Cloud)
        if (is_string($value)) {
            $decoded = $this->decodeJsonCredentials($value);
            if ($decoded !== null) {
                return $decoded;
            }

            $jsonError = json_last_error_msg();
        } else {
            $jsonError = 'valor no es string';
        }

        // Caso 3: buscar archivo en rutas típicas (local)
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

        $preview = is_string($value) ? substr($value, 0, 150) : gettype($value);
        throw new \Exception(
            "No se pudo resolver FIREBASE_CREDENTIALS. " .
                "Longitud: " . (is_string($value) ? strlen($value) : 'n/a') . ". " .
                "JSON error: {$jsonError}. " .
                "Preview: {$preview}"
        );
    }

    /**
     * Intenta decodificar el JSON de credenciales manejando
     * saltos de línea reales y otros problemas comunes.
     */
    private function decodeJsonCredentials(string $raw): ?array
    {
        // Limpieza básica
        $clean = trim($raw, " \t\n\r\0\x0B\"'");
        $clean = preg_replace('/^\xEF\xBB\xBF/', '', $clean); // BOM

        // Intento 1: decodificar tal cual (funciona si ya está en una línea)
        $decoded = json_decode($clean, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Intento 2: reemplazar saltos reales por \n literales ANTES de decodificar
        // Esto preserva los \n dentro de private_key
        $escaped = str_replace(
            ["\r\n", "\r", "\n"],
            ['\\n', '\\n', '\\n'],
            $clean
        );

        $decoded = json_decode($escaped, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Los \n literales dentro de private_key ahora son saltos reales,
            // que es lo que Google/Firebase espera en el JSON decodificado.
            // Pero si el JSON ya los tenía como \n literales, se vuelven \\n → hay que corregir.
            if (isset($decoded['private_key'])) {
                $decoded['private_key'] = str_replace('\\\\n', "\n", $decoded['private_key']);
            }
            return $decoded;
        }

        // Intento 3: eliminar todos los saltos reales (último recurso)
        $flat = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $clean);
        $flat = preg_replace('/\s+/', ' ', $flat);
        $decoded = json_decode($flat, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return null;
    }
}

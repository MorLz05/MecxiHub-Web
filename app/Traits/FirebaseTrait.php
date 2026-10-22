<?php

namespace App\Traits;

use Kreait\Firebase\Factory;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;

trait FirebaseTrait
{
    protected $firebaseAuth;
    protected $firestore;

    protected function initializeFirebase()
    {
        try {
            $serviceAccountPath = storage_path('app/mecxihub-db-firebase-adminsdk-fbsvc-acf0185b95.json');

            if (!file_exists($serviceAccountPath)) {
                throw new \Exception('Archivo de credenciales no encontrado');
            }

            // Crear fábrica para Firebase Auth
            $factory = (new Factory)
                ->withServiceAccount($serviceAccountPath)
                ->withDatabaseUri('https://mecxihub-db-default-rtdb.firebaseio.com/');

            $this->firebaseAuth = $factory->createAuth();

            // Inicializar Firestore con las credenciales
            $this->firestore = new FirestoreClient([
                'keyFilePath' => $serviceAccountPath,
                'projectId' => env('FIREBASE_PROJECT_ID', 'mecxihub-db')
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error al inicializar Firebase: ' . $e->getMessage());
            throw $e;
        }
    }

     protected function getUserData($uid)
    {
        try {
            // Ya no inicializamos Firebase aquí, asumimos que $this->firestore ya fue inyectado
            // o que el controlador que lo usa lo tiene.
            if (!$this->firestore) {
                throw new \Exception('Firestore no inicializado');
            }

            $db = $this->firestore->collection('usuarios')->document($uid)->snapshot();

            if ($db->exists()) {
                return $db->data();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de Firestore: ' . $e->getMessage());
            return [];
        }
    }
}

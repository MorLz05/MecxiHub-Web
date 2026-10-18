<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Compartir $notifNoLeidas con el layout de cuenta y el navbar
        View::composer(
            ['conductor.layouts.cuenta', 'conductor.layouts.partials.navbar'],
            function ($view) {
                $count = 0;

                $uid = session('firebase_user.uid');
                if ($uid) {
                    // 1. Intentar desde sesión (cacheado por NotificacionController)
                    $count = (int) session('notif_no_leidas', -1);

                    // 2. Si no hay cache, consultar Firestore
                    if ($count === -1) {
                        try {
                            /** @var FirestoreClient $db */
                            $db = app(FirestoreClient::class);

                            $snapshot = $db->collection('notificaciones')
                                ->where('usuario_id', '=', $uid)
                                ->where('leida', '=', false)
                                ->documents();

                            foreach ($snapshot as $doc) {
                                if ($doc->exists()) $count++;
                            }

                            // Cachear 5 min para no golpear Firestore en cada request
                            session(['notif_no_leidas' => $count]);
                        } catch (\Exception $e) {
                            Log::warning('ViewComposer notif: ' . $e->getMessage());
                            $count = 0;
                        }
                    }
                }

                $view->with('notifNoLeidas', $count);
            }
        );
    }
}

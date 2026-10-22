<?php

return [

    'install-button' => true,

    'manifest' => [
        'name' => 'MecxiHub',
        'short_name' => 'MecxiHub',
        'background_color' => '#ffffff',
        'display' => 'standalone',
        'description' => 'Plataforma MecxiHub.',
        'theme_color' => '#6777ef',
        'icons' => [
            [
                'src' => 'icons/icon-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => 'icons/icon-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
        ],
    ],

    'debug' => env('APP_DEBUG', false),

    'livewire-app' => false,
];

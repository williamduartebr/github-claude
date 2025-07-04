<?php

/*
|--------------------------------------------------------------------------
| Configurações de Logging para Quando Trocar Pneus
|--------------------------------------------------------------------------
|
| Adicione estas configurações ao config/logging.php do Laravel
| para ter logs separados do módulo
|
*/

return [
    'channels' => [
        'tire-articles' => [
            'driver' => 'daily',
            'path' => storage_path('logs/tire-articles.log'),
            'level' => env('TIRE_ARTICLES_LOG_LEVEL', 'info'),
            'days' => 14,
        ],

        'claude-enhancements' => [
            'driver' => 'daily', 
            'path' => storage_path('logs/claude-enhancements.log'),
            'level' => env('TIRE_ARTICLES_LOG_LEVEL', 'info'),
            'days' => 30,
        ],

        'tire-articles-performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/tire-articles-performance.log'), 
            'level' => 'info',
            'days' => 7,
        ],
    ],
];

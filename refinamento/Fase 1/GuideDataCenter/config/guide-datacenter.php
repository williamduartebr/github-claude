<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GuideDataCenter Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações do módulo GuideDataCenter
    |
    */

    // Conexão MongoDB
    'mongodb_connection' => env('GUIDE_MONGODB_CONNECTION', 'mongodb'),

    // URL base para os guias
    'base_url' => env('APP_URL', 'http://localhost') . '/guias',

    // Templates disponíveis
    'templates' => [
        'oleo-motor' => 'Óleo do Motor',
        'calibragem' => 'Calibragem de Pneus',
        'pneus' => 'Pneus e Rodas',
        'revisao' => 'Revisão Programada',
        'consumo' => 'Consumo',
        'problemas' => 'Problemas Comuns',
        'fluidos' => 'Fluidos',
        'bateria' => 'Bateria',
        'cambio' => 'Câmbio',
        'motor' => 'Motor',
        'default' => 'Padrão',
    ],

    // Configurações de SEO
    'seo' => [
        'title_max_length' => 60,
        'title_min_length' => 30,
        'meta_description_max_length' => 160,
        'meta_description_min_length' => 120,
        'min_word_count' => 300,
        'min_readability_score' => 60.0,
    ],

    // Configurações de Clusters
    'clusters' => [
        'enable_auto_generation' => true,
        'enable_super_clusters' => true,
        'max_links_per_cluster' => 50,
    ],

    // Configurações de Import
    'import' => [
        'batch_size' => 100,
        'max_import_per_request' => 1000,
    ],

    // Cache
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hora
        'prefix' => 'guide_',
    ],
];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações do Sitemap
    |--------------------------------------------------------------------------
    |
    | Configurações para geração e gerenciamento de sitemaps
    |
    */

    // Máximo de URLs por arquivo de sitemap
    'max_urls_per_sitemap' => env('SITEMAP_MAX_URLS', 1000),

    // Duração do cache em segundos (padrão: 1 hora)
    'cache_duration' => env('SITEMAP_CACHE_DURATION', 3600),

    // Regeneração automática quando artigos são alterados
    'auto_regenerate' => env('SITEMAP_AUTO_REGENERATE', false),

    // Submissão automática aos motores de busca
    'auto_submit' => env('SITEMAP_AUTO_SUBMIT', false),

    // URLs dos motores de busca para submissão
    'search_engines' => [
        'google' => 'https://www.google.com/ping?sitemap=',
        'bing' => 'https://www.bing.com/ping?sitemap=',
    ],

    // Configurações por tipo de conteúdo
    'content_types' => [
        'articles' => [
            'changefreq' => 'weekly',
            'priority' => '0.9',
            'enabled' => true,
        ],
        'categories' => [
            'changefreq' => 'monthly',
            'priority' => '0.8',
            'enabled' => true,
        ],
        'pages' => [
            'changefreq' => 'yearly',
            'priority' => '0.6',
            'enabled' => true,
        ],
    ],

    // Páginas estáticas para incluir no sitemap
    'static_pages' => [
        [
            'slug' => 'sobre-nos',
            'priority' => '0.8',
            'changefreq' => 'monthly'
        ],
        [
            'slug' => 'politica-privacidade',
            'priority' => '0.5',
            'changefreq' => 'yearly'
        ],
        [
            'slug' => 'termos-uso',
            'priority' => '0.5',
            'changefreq' => 'yearly'
        ],
        [
            'slug' => 'contato',
            'priority' => '0.7',
            'changefreq' => 'monthly'
        ],
    ],

    // Configurações de compressão
    'compression' => [
        'enabled' => env('SITEMAP_COMPRESSION', true),
        'min_size' => 1024, // Comprimir apenas se > 1KB
        'level' => 6, // Nível de compressão GZIP (1-9)
    ],

    // Configurações de cache HTTP
    'http_cache' => [
        'max_age' => 3600, // 1 hora
        'must_revalidate' => true,
    ],

    // Logs
    'logging' => [
        'enabled' => env('SITEMAP_LOGGING', true),
        'channel' => env('SITEMAP_LOG_CHANNEL', 'default'),
    ],
];
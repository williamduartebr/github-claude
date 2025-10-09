<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Módulo Quando Trocar Pneus - Configurações
    |--------------------------------------------------------------------------
    |
    | Configurações para o módulo de geração de artigos "Quando Trocar Pneus"
    |
    */

    // Habilitar/Desabilitar módulo
    'enabled' => env('WHEN_TO_CHANGE_TIRES_ENABLED', true),

    // Configurações de geração
    'generation' => [
        'batch_size' => env('TIRE_ARTICLES_BATCH_SIZE', 50),
        'max_retries' => env('TIRE_ARTICLES_MAX_RETRIES', 3),
        'timeout' => env('TIRE_ARTICLES_TIMEOUT', 300), // 5 minutos
        'memory_limit' => env('TIRE_ARTICLES_MEMORY_LIMIT', '512M'),
    ],

    // Configurações de qualidade
    'quality' => [
        'min_word_count' => env('TIRE_ARTICLES_MIN_WORDS', 800),
        'min_score' => env('TIRE_ARTICLES_MIN_SCORE', 6.0),
        'required_sections' => [
            'introducao',
            'sintomas_desgaste',
            'fatores_durabilidade',
            'cronograma_verificacao',
            'tipos_pneus',
            'consideracoes_finais'
        ],
    ],

    // Configurações de armazenamento
    'storage' => [
        'json_path' => env('TIRE_ARTICLES_JSON_PATH', 'articles/when-to-change-tires'),
        'backup_enabled' => env('TIRE_ARTICLES_BACKUP_ENABLED', true),
        'backup_retention_days' => env('TIRE_ARTICLES_BACKUP_RETENTION', 30),
        'compression_enabled' => env('TIRE_ARTICLES_COMPRESSION', false),
    ],

    // Configurações de CSV
    'csv' => [
        'default_path' => env('TIRE_ARTICLES_CSV_PATH', 'todos_veiculos.csv'),
        'encoding' => env('TIRE_ARTICLES_CSV_ENCODING', 'UTF-8'),
        'delimiter' => env('TIRE_ARTICLES_CSV_DELIMITER', ','),
        'validate_headers' => env('TIRE_ARTICLES_VALIDATE_HEADERS', true),
        'required_columns' => [
            'make',
            'model', 
            'year',
            'tire_size',
            'pressure_empty_front',
            'pressure_empty_rear',
            'category'
        ],
    ],

    // Configurações de cache
    'cache' => [
        'enabled' => env('TIRE_ARTICLES_CACHE_ENABLED', true),
        'ttl' => [
            'statistics' => env('TIRE_ARTICLES_CACHE_STATS_TTL', 1800), // 30 min
            'vehicle_exists' => env('TIRE_ARTICLES_CACHE_EXISTS_TTL', 3600), // 1 hora
            'total_count' => env('TIRE_ARTICLES_CACHE_COUNT_TTL', 300), // 5 min
        ],
        'tags' => ['tire_articles'],
    ],

    // Configurações de logs
    'logging' => [
        'enabled' => env('TIRE_ARTICLES_LOGGING_ENABLED', true),
        'level' => env('TIRE_ARTICLES_LOG_LEVEL', 'info'),
        'channel' => env('TIRE_ARTICLES_LOG_CHANNEL', 'daily'),
        'separate_file' => env('TIRE_ARTICLES_SEPARATE_LOG', true),
    ],

    // Configurações de scheduled tasks
    'scheduling' => [
        'auto_generation' => [
            'enabled' => env('TIRE_ARTICLES_AUTO_GENERATION', false),
            'time' => env('TIRE_ARTICLES_AUTO_TIME', '02:00'),
            'batch_size' => env('TIRE_ARTICLES_AUTO_BATCH_SIZE', 30),
        ],
        'cleanup' => [
            'enabled' => env('TIRE_ARTICLES_AUTO_CLEANUP', true),
            'retention_days' => env('TIRE_ARTICLES_CLEANUP_DAYS', 90),
            'day_of_week' => env('TIRE_ARTICLES_CLEANUP_DAY', 0), // Domingo
            'time' => env('TIRE_ARTICLES_CLEANUP_TIME', '03:00'),
        ],
        'validation' => [
            'enabled' => env('TIRE_ARTICLES_AUTO_VALIDATION', true),
            'day_of_week' => env('TIRE_ARTICLES_VALIDATION_DAY', 1), // Segunda
            'time' => env('TIRE_ARTICLES_VALIDATION_TIME', '01:00'),
        ],
    ],

    // Configurações de relatórios
    'reporting' => [
        'enabled' => env('TIRE_ARTICLES_REPORTING_ENABLED', true),
        'daily_email' => env('TIRE_ARTICLES_DAILY_EMAIL', false),
        'email_recipients' => explode(',', env('TIRE_ARTICLES_EMAIL_RECIPIENTS', '')),
        'slack_webhook' => env('TIRE_ARTICLES_SLACK_WEBHOOK', null),
    ],

    // Configurações de performance
    'performance' => [
        'chunk_size' => env('TIRE_ARTICLES_CHUNK_SIZE', 100),
        'queue_enabled' => env('TIRE_ARTICLES_QUEUE_ENABLED', false),
        'queue_connection' => env('TIRE_ARTICLES_QUEUE_CONNECTION', 'database'),
        'queue_name' => env('TIRE_ARTICLES_QUEUE_NAME', 'tire-articles'),
    ],

    // Configurações de template
    'template' => [
        'default_template' => 'when_to_change_tires',
        'enable_caching' => env('TIRE_ARTICLES_TEMPLATE_CACHE', true),
        'custom_templates_path' => env('TIRE_ARTICLES_CUSTOM_TEMPLATES', null),
    ],

    // Configurações de SEO
    'seo' => [
        'base_url' => env('TIRE_ARTICLES_BASE_URL', 'https://mercadoveiculos.com'),
        'url_prefix' => env('TIRE_ARTICLES_URL_PREFIX', 'info/quando-trocar-pneus'),
        'canonical_enabled' => env('TIRE_ARTICLES_CANONICAL_ENABLED', true),
        'sitemap_enabled' => env('TIRE_ARTICLES_SITEMAP_ENABLED', true),
    ],

    // Configurações futuras para Claude API (Etapa 2)
    'claude' => [
        'enabled' => env('TIRE_ARTICLES_CLAUDE_ENABLED', false),
        'api_key' => env('CLAUDE_API_KEY', null),
        'model' => env('TIRE_ARTICLES_CLAUDE_MODEL', 'claude-sonnet-4-20250514'),
        'max_tokens' => env('TIRE_ARTICLES_CLAUDE_MAX_TOKENS', 4000),
        'temperature' => env('TIRE_ARTICLES_CLAUDE_TEMPERATURE', 0.3),
        'max_enhancements_per_article' => env('TIRE_ARTICLES_CLAUDE_MAX_ENHANCEMENTS', 3),
        'enhancement_delay' => env('TIRE_ARTICLES_CLAUDE_DELAY', 2), // segundos entre calls
    ],
];

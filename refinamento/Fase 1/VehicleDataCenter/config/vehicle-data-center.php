<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vehicle Data Center Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Vehicle Data Center module
    |
    */

    // Middleware to apply to routes
    'middleware' => ['web'],

    // Database connections
    'mysql_connection' => env('VEHICLE_MYSQL_CONNECTION', 'mysql'),
    'mongodb_connection' => env('VEHICLE_MONGODB_CONNECTION', 'mongodb'),

    // Pagination
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100,
    ],

    // Search
    'search' => [
        'max_results' => 50,
        'quick_search_limit' => 20,
    ],

    // SEO
    'seo' => [
        'enabled' => true,
        'auto_generate' => true,
        'default_image' => null,
    ],

    // Sync
    'sync' => [
        'auto_sync' => false,
        'sync_interval' => 3600, // seconds
    ],

    // Cache
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // seconds
        'prefix' => 'vehicle_',
    ],

    // API
    'api' => [
        'enabled' => true,
        'rate_limit' => 60, // requests per minute
        'require_auth' => false,
    ],

    // Ingestion
    'ingestion' => [
        'allowed_sources' => ['api', 'manual', 'csv', 'json', 'ai'],
        'validate_strict' => true,
        'auto_create_missing' => true,
    ],

    // Features
    'features' => [
        'clustering' => true,
        'seo_builder' => true,
        'advanced_search' => true,
        'comparison' => true,
    ],

    // Display
    'display' => [
        'date_format' => 'd/m/Y',
        'currency' => 'BRL',
        'language' => 'pt_BR',
    ],

];

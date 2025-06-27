<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the user listing cache system
    |
    */

    // Cache TTL in minutes
    'ttl' => [
        'metadata' => env('USER_CACHE_METADATA_TTL', 1440), // 24 hours
        'query' => env('USER_CACHE_QUERY_TTL', 60),         // 1 hour  
        'page' => env('USER_CACHE_PAGE_TTL', 15),           // 15 minutes
    ],

    // Cache key prefixes
    'prefixes' => [
        'roles' => 'roles',
        'users' => 'users',
        'metadata' => 'user.metadata',
    ],

    // Auto-invalidation settings
    'auto_invalidate' => [
        'on_user_update' => env('USER_CACHE_AUTO_INVALIDATE', true),
        'on_role_update' => env('USER_CACHE_INVALIDATE_ON_ROLE_UPDATE', true),
        'on_relationship_update' => env('USER_CACHE_INVALIDATE_ON_RELATIONSHIP_UPDATE', true),
    ],

    // Performance settings
    'performance' => [
        'max_pages_to_cache' => env('USER_CACHE_MAX_PAGES', 5),
        'preload_common_queries' => env('USER_CACHE_PRELOAD', false),
        'use_compression' => env('USER_CACHE_COMPRESSION', true),
    ],

    // Monitoring
    'monitoring' => [
        'log_cache_hits' => env('USER_CACHE_LOG_HITS', false),
        'log_slow_queries' => env('USER_CACHE_LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('USER_CACHE_SLOW_THRESHOLD', 1000), // milliseconds
    ],
];

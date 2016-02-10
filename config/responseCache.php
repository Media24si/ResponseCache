<?php

return [
    'enabled' => env('RESPONSE_CACHE_ENABLED', true),
    'super_cache' => false,

    /**
     *  Cache key defined in cache.php settings
     */
    'cache_store' => env('CACHE_DRIVER', 'file'),

    /**
     * Prefix for cache key
     */
    'key_prefix' => 'response_cache.',

    /**
     * 30% chanse to clear garbage (old tags array)
     */
    'garbage_clear_ratio' => 30
];

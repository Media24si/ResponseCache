<?php

return [

    /**
     *  Default TTL for reponse cache
     *  default: 15 minutes
     */
    'default_ttl' => 900,

    /**
     *  Cache key defined in cache.php settings
     */
    'cache_store' => env('CACHE_DRIVER', 'file'),

    /**
     * Prefix for cache key
     */
    'key_prefix' => 'response_cache.',
];

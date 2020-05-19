<?php

namespace Media24si\ResponseCache;

use Illuminate\Cache\Repository;

class SuperCache
{
    public static function memcachedCheck($app)
    {
        $config = require_once $app->configPath() . '/responseCache.php';
        $cacheConfig = require_once $app->configPath() . '/cache.php';

        try {
            $connector = (new \Illuminate\Cache\MemcachedConnector())->connect($cacheConfig['stores']['memcached']['servers']);    
            $cacheRepo = new Repository(new \Illuminate\Cache\MemcachedStore($connector, $cacheConfig['prefix']));
        }
        catch(\Exception $e) {
            return;
        }
    
        $responseCacheManager = new ResponseCacheManager($cacheRepo, $config);

        $request = \Illuminate\Http\Request::createFromBase( \Symfony\Component\HttpFoundation\Request::createFromGlobals() );

        $key = $request->fullUrl();
        if ($request->isMethod('get') && $config['enabled']) {
            $response = $responseCacheManager->get($key);
            if ( $response != null && is_a($response, '\Symfony\Component\HttpFoundation\Response') ) {
         	   $response->send();
         	   die;
        	}
        }
    }
}

<?php

namespace Media24si\ResponseCache;

use Illuminate\Cache\Repository;

class SuperCache
{
    public static function memcachedCheck($app)
    {
        $config = require_once $app->configPath() . '/responseCache.php';
        $cacheConfig = require_once $app->configPath() . '/cache.php';

        $connector = (new \Illuminate\Cache\MemcachedConnector())->connect($cacheConfig['stores']['memcached']['servers']);
        $cacheRepo = new Repository(new \Illuminate\Cache\MemcachedStore($connector, $cacheConfig['prefix']));

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

    private static function current_url()
    {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]  == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $pageURL;
    }
}

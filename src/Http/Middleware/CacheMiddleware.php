<?php

namespace Media24si\ResponseCache\Http\Middleware;

use Closure;

class CacheMiddleware
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $cacheManager = null;
        try {
            $cacheManager = \App::make('responseCacheManager');
        }
        catch(\Exception $e) {}

        if ($cacheManager === null) {
            return $next($request);
        }

        $url = $request->fullUrl();

        if ($request->isMethod('get') && config('responseCache.enabled') && !config('reponseCache.super_cache') ) {
            $content = $cacheManager->get($url);
            if ($content != null) {
                return $content;
            }
        }
        
        $response = $next($request);

        if (is_a($response, '\Symfony\Component\HttpFoundation\Response')) {
            $cacheManager->put($url, $response);
        }

        return $response;
    }
}

<?php

namespace Media24si\ResponseCache\Http\Middleware;

use Closure;
use Media24si\ResponseCache\ResponseCacheManager;

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
        $cacheManager = \App::make('responseCacheManager');

        $url = $request->fullUrl();

        if ($request->isMethod('get') && config('responseCache.enabled')) {
            $content = $cacheManager->get($url);
            if ($content != null) {
                return $content;
            }
        }
        
        $response = $next($request);

        if (is_a($response, '\Illuminate\Http\Response')) {
            $cacheManager->saveResponse($url, $response);
        }

        return $response;
    }
}

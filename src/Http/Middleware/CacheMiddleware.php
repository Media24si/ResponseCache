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
        // only get methods are allowed
        if ( $request->isMethod('get')) {
            $key = config('responseCache.key_prefix') . $request->fullUrl();

            $response = \Cache::store( config('responseCache.cache_store') )->get($key);

            if (null != $response) {
                return $response;
            }
        }

        return $next($request);
    }

    public function terminate($request, $response)
    {
        if ( $response->isCacheable() ) {
            $key = config('responseCache.key_prefix') . $request->fullUrl();
            $max_age = $response->getMaxAge();
            $response->header('X-ResponseCache', true);
            \Cache::store(config('responseCache.cache_store'))->put($key, $response, $max_age/60);
        }
    }

}

<?php

namespace Media24si\ResponseCache;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Cache\Repository as CacheContract;

class ResponseCacheManager
{
    private $cache;
    private $config;

    public function __construct(CacheContract $cache, array $config = [])
    {
        $this->cache = $cache;

        $defaults = [
            'key_prefix' => 'responseCache',
            'garbage_clear_ratio' => 30
        ];

        $this->config = array_merge($defaults, $config);
    }

    /**
     * Get content for given url
     * @param  string $url [description]
     * @return Response
     */
    public function get($url)
    {
        $response = $this->cache->get( $this->generateKey($url) );
                
        if (null != $response) {
            $content = $response['content'];
            $content->header('Age', time() - $response['time']);
            return $content;
        }

        return null;
    }

    /**
     * Save response to cache
     * @param  string   $url
     * @param  Response $response
     * @return  boolena
     */
    public function put($url, Response $response)
    {
        if (! $response->isCacheable()) {
            return false;
        }

        $key = $this->generateKey($url);

        $max_age = $response->getMaxAge();
        $time = time();

        // get cache tags and remove them
        $tags = [];
        $headerTags = $response->headers->get('cache-tags');
        if ( $headerTags ) {
            $tags = str_getcsv($headerTags);
            $response->headers->remove('cache-tags');
        }

        $cacheArray = [
            'content' => $response,
            'time' => $time
        ];

        $this->cache->put($key, $cacheArray, $max_age/60);

        // save tags
        $this->assignKeyWithTags($tags, $key, $time+$max_age);

        return true;
    }

    /**
     * Clear url
     * @param  string $url
     */
    public function flush($url) {
        return $this->cache->flush($this->generateKey($url));
    }

    public function flushTag($tag)
    {
        $time = time();

        $urls = collect($this->getTag($tag));
        collect($urls)->reject(function ($value) use ($time) {
            return $value < $time;
        })->each(function ($value, $key) {
            $this->cache->forget($key);
        });

        $this->cache->forget($this->generateTagKey($tag));
    }

    public function getTag($tag)
    {
        return $this->cache->get($this->generateTagKey($tag), []);
    }

    /**
     * Generate key for url
     * @param  string $url
     * @return string
     */
    private function generateKey($url) {
        return $this->config['key_prefix'] . ':' . md5($url);
    }

    /**
     * Generate key for tag
     * @param  string $tag
     * @return string
     */
    private function generateTagKey($tag) {
        return $this->config['key_prefix'] . ':tag:' . $tag;
    }

    /**
     * Assign key to tags
     * @param  array  $tags
     * @param  string $key
     * @param  int $expires
     */
    private function assignKeyWithTags(array $tags = [], $key, $expires) {
        if ( ! count($tags) ) {
            return;
        }

        // decide if array will be cleared of expired items
        $clear = rand(0, 100) < $this->config['garbage_clear_ratio'];

        foreach ($tags as $tag) {
            $tagKey = $this->generateTagKey($tag);
            $currentValues = $this->cache->get($tagKey, []);
            $currentValues[$key] = $expires;

            if ($clear) { // clear garbage
                $currentValues = collect($currentValues)->reject(function ($value) {
                    return $value < time();
                })->toArray();
            }

            $this->cache->forever($tagKey, $currentValues);
        }
    }
}

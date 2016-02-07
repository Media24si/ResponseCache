<?php

namespace Media24si\ResponseCache;

use Illuminate\Http\Response;
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

    public function flushTag($tag)
    {
        $tagKey = $this->config['key_prefix'] . ':tag:' . $tag;
        $time = time();

        $urls = collect($this->getTag($tag));
        collect($urls)->reject(function ($value) use ($time) {
            return $value < $time;
        })->each(function ($value, $key) {
            $this->cache->forget($key);
        });

        $this->cache->forget($tagKey);
    }

    public function getTag($tag)
    {
        $tagKey = $this->config['key_prefix'] . ':tag:' . $tag;
        return $this->cache->get($tagKey, []);
    }

    public function get($url)
    {
        $key = $this->config['key_prefix'] . ':' . $url;
        $response = $this->cache->get($key);

        if (null != $response) {
            $content = $response['content'];
            $content->header('Age', time() - $response['time']);
            return $content;
        }

        return null;
    }

    public function saveResponse($url, Response $response)
    {
        if (! $response->isCacheable()) {
            return;
        }

        $key = $this->config['key_prefix'] . ':' . $url;

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
        if (count($tags) > 0) {
            $clear = rand(0, 100) > $this->config['garbage_clear_ratio'];
            $expires = $time + $max_age;

            foreach ($tags as $tag) {
                $tagKey = $this->config['key_prefix'] . ':tag:' . $tag;
                $currentValues = $this->cache->get($tagKey, []);
                $currentValues[$key] = $expires;

                if ($clear) { // clear garbage
                    $currentValues = collect($currentValues)->reject(function ($value) use ($time) {
                        return $value < $time;
                    })->toArray();
                }

                $this->cache->forever($tagKey, $currentValues);
            }
        }
    }
}

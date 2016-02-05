<?php

namespace Media24si\ResponseCache;

use Illuminate\Http\Response;

class ResponseCacheManager
{

    private $prefix;

    private $store;

    public function __construct()
    {
        $this->prefix = config('responseCache.key_prefix');
        $this->store = \Cache::store(config('responseCache.cache_store'));
    }

    public function flushTag($tag)
    {
        $tagKey = $this->prefix . ':tag:' . $tag;
        $time = time();

        $urls = collect($this->getTag($tag));
        collect($urls)->reject(function ($value) use ($time) {
            return $value < $time;
        })->each(function ($value, $key) {
            $this->store->forget($key);
        });

        $this->store->forget($tagKey);
    }

    public function getTag($tag)
    {
        $tagKey = $this->prefix . ':tag:' . $tag;
        return $this->store->get($tagKey, []);
    }

    public function get($url)
    {
        $key = $this->prefix . ':' . $url;
        $response = $this->store->get($key);

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

        $key = $this->prefix . ':' . $url;

        $max_age = $response->getMaxAge();
        $time = time();

        // get cache tags and remove them
        $tags = $response->headers->get('cache-tags');
        $tags = str_getcsv($tags);
        $response->headers->remove('cache-tags');

        $cacheArray = [
            'content' => $response,
            'time' => $time
        ];

        $this->store->put($key, $cacheArray, $max_age/60);

        // save tags
        if (count($tags) > 0) {
            $clear = rand(0, 100) > config('responseCache.garbage_clear_ratio');
            $expires = $time + $max_age;

            foreach ($tags as $tag) {
                $tagKey = $this->prefix . ':tag:' . $tag;
                $currentValues = $this->store->get($tagKey, []);
                $currentValues[$key] = $expires;

                if ($clear) { // clear garbage
                    $currentValues = collect($currentValues)->reject(function ($value) use ($time) {
                        return $value < $time;
                    })->toArray();
                }

                $this->store->forever($tagKey, $currentValues);
            }
        }
    }
}

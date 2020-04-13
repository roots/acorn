<?php

namespace Roots\Acorn\View\Composers\Concerns;

use Illuminate\Support\Facades\Cache;

use function Roots\cache;

trait Cacheable
{
    /**
     * Cache expiration
     *
     * If no expiration is specified, the values will be cached forever.
     *
     * @var int|float
     */
    protected $cache_expiration;

    /**
     * Cache key
     *
     * If no key is specified, the key will default to the class name and post ID
     *
     * @var string
     */
    protected $cache_key;

    /**
     * Cache tags
     *
     * If no tags are specified, the tags will be class name, post ID, and post type
     *
     * @var string[]
     */
    protected $cache_tags;

    /**
     * Cache helper
     *
     * @param  dynamic  key|key,value|key-values|null
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function cache()
    {
        static $cache;

        $arguments = func_get_args();
        $tags = $this->cache_tags ?? [static::class, 'post-' . get_the_ID(), get_post_type()];

        if (! $cache) {
            try {
                $cache = Cache::tags($tags);
            } catch (\BadMethodCallException $error) {
                $cache = cache();
            }
        }

        if (empty($arguments)) {
            return $cache;
        }

        if (! is_string($values = $arguments[0])) {
            $data = [];

            foreach ($values as $key => $value) {
                $data[$key] = $this->cache($key, $value);
            }

            return $data;
        }

        if (! isset($arguments[1])) {
            return $cache->get($arguments[0]);
        }

        $key = $arguments[0];
        $value = $arguments[1];

        if (! is_callable($value)) {
            throw new \BadMethodCallException('Cache value should be callable');
        }

        if (! $expires = $this->cache_expiration) {
            return $cache->rememberForever($key, $value);
        }

        return $cache->remember($key, $expires, $value);
    }

    /**
     * Forget cache data
     *
     * @param string $key
     * @return void
     */
    protected function forget($key = null)
    {
        return $this->cache()->forget($key ?? static::class . get_the_ID());
    }

    /**
     * Flush all cache data
     *
     * If tags are supported, then only the tags will be flushed
     *
     * @return void
     */
    protected function flush()
    {
        return $this->cache()->flush();
    }

    /**
     * Data to be merged and passed to the view before rendering.
     *
     * @return array
     */
    protected function merge()
    {
        $key = $this->cache_key ?? hash('crc32b', static::class . serialize(
            get_queried_object()
            ?? collect($_SERVER)->only('HTTP_HOST', 'REQUEST_URI', 'QUERY_STRING', 'WP_HOME')->toArray()
        ));

        $with = $this->cache($key, function () {
            return $this->with();
        });

        return array_merge(
            $with,
            $this->view->getData(),
            $this->override()
        );
    }
}

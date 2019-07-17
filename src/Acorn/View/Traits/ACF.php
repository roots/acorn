<?php

namespace Roots\Acorn\View\Traits;

use \Illuminate\Support\Collection;
use \Illuminate\Support\Facades\Cache;

use \WP_Post;
use function \add_action;
use function \get_the_ID;
use function \get_post;
use function \get_fields;
use function \get_field_objects;

trait ACF
{
    /**
     * Returns raw output of custom fields
     *
     * @return \Illuminate\Support\Collection
     */
    public function raw()
    {
        return $this->getCustomFieldsData($this->getRequest());
    }

    /**
     * Returns custom fields data formatted as an object
     *
     * @param string $fieldSubset requested child field from view composer
     * @return object $fields fields in object form
     */
    public function fields($fieldSubset = null)
    {
        $data = $this->raw();

        if ($data) {
            $fields = !is_null($fieldSubset)
                    ? $data->get($fieldSubset)
                    : $data;

            if ($fields) {
                return is_array($fields)
                        ? (object) $fields->toArray()
                        : $fields;
            }
        }
    }

    /**
     * Returns custom fields data as a \Illuminate\Support\Collection instance
     *
     * @param string $fieldSubset requested child field from view composer
     * @return \Illuminate\Support\Collection collected fields
     */
    public function fieldsCollection($fieldSubset = null)
    {
        $data = $this->raw();

        if ($data) {
            $fields = !is_null($fieldSubset)
                    ? $data->get($fieldSubset)
                    : $data;

            if ($fields) {
                return is_array($fields) ? Collection::make(
                    $fields->toArray()
                ) : Collection::make($fields);
            }
        }
    }

    /**
     * Returns either the View Composer's requested
     * id or the result of a generic get_the_ID
     * fn call, in the event none was provided.
     *
     * @return \WP_Post
     */
    public function getRequest()
    {
        return isset($this->postId) ? $this->postId : \get_the_ID();
    }

    /**
     * Retrieves ACF data via a request to the cache store
     *
     * @param int $postId wordpress post id
     * @return \Illuminate\Support\Collection
     */
    public function getCustomFieldsData(int $postId = null)
    {
        $post = $postId ? \get_post($postId) : \get_post(\get_the_ID());

        return $this->collectCustomFieldsFromCache($this->getCacheSettings($post), $post);
    }

    /**
     * Manages the Advanced Custom Fields data cache retrieval and storage operations
     *
     * @param object $cacheSettings cache key and id
     * @param object $post          wordpress post data
     * @return \Illuminate\Support\Collection
     */
    public function collectCustomFieldsFromCache(object $cacheSettings, object $post)
    {
        return Cache::remember($cacheSettings->id, $cacheSettings->expiry, function () use ($post) {
            return Collection::make($this->collectAllFields($post));
        });
    }

    /**
     * Returns data requested by Cache
     *
     * @param object $post wordpress post data
     * @return \Illuminate\Support\Collection
     */
    public function collectAllFields(object $post)
    {
        return Collection::make(\get_fields($post->ID));
    }

    /**
     * Sets up the Cache settings
     *
     * @param object $post wordpress post data
     * @return object self
     */
    public function getCacheSettings(object $post)
    {
        $this->cache = Cache::class;

        return (object) [
            'id'     => $this->cacheKey($post),
            'expiry' => $this->cacheExpiry(),
        ];
    }

    /**
     * Returns the Cache key
     *
     * @param object $post wordpress post data
     * @return string cache key
     */
    public function cacheKey(object $post)
    {
        return "acf-field-data-{$post->post_name}";
    }

    /**
     * Returns the Cache expiry
     *
     * @return int expiry in seconds
     */
    public function cacheExpiry()
    {
        return isset($this->cacheExpiry) ? $this->cacheExpiry : 0;
    }


    /**
     * Adds WordPress action to invoke Cache invalidation
     *
     * @return void
     */
    public function invalidateCache()
    {
        \add_action('transition_post_status', [
            $this, 'onStatusTransition'
        ], 10, 3);
    }

    /**
     * Invalidates Cache when any WP_Post object undergoes
     * a change in Publish status
     *
     * @param string $new_status post's new status
     * @param string $old_status post's former status
     * @param object $post       post object
     * @return void
     */
    public function onPostStatusTransition($new_status, $old_status, $post)
    {
        Cache::flush();
    }
}

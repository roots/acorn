<?php

namespace Roots\Acorn\Exceptions;

use WP;
use WP_Query;
use WP_Post;
use Illuminate\Support\Collection;
use Whoops\Handler\PrettyPageHandler as PrettyPageHandlerBase;

class PrettyPageHandler extends PrettyPageHandlerBase
{
    /**
     * Create a new PrettyPageHandler instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Collection::make([])
            ->put('WordPress Data', function () {
                global $wp;

                if (! $wp instanceof WP) {
                    return [];
                }

                return Collection::make(
                    get_object_vars($wp)
                )
                ->forget('private_query_vars')
                ->forget('public_query_vars')
                ->filter()
                ->all();
            })
            ->put(sprintf('%s Data', WP_Query::class), function () {
                global $wp_query;

                if (! $wp_query instanceof WP_Query) {
                    return [];
                }

                return Collection::make(
                    get_object_vars($wp_query)
                )
                ->forget('posts')
                ->forget('post')
                ->filter()
                ->all();
            })
            ->put(sprintf('%s Data', WP_Post::class), function () {
                $post = get_post();

                if (! $post instanceof WP_Post) {
                    return [];
                }

                return get_object_vars($post);
            })->each(function ($callback, $name) {
                $this->addDataTableCallback($name, $callback);
            });
    }
}

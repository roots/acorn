<?php

namespace Roots\Acorn\Exceptions;

use Illuminate\Foundation\Exceptions\WhoopsHandler as FoundationWhoopsHandler;
use Illuminate\Support\Collection;
use Whoops\Handler\PrettyPageHandler;
use WP;
use WP_Post;
use WP_Query;

class WhoopsHandler extends FoundationWhoopsHandler
{
    /**
     * WordPress environment secrets.
     *
     * @var array
     */
    protected $secrets = [
        'DB_PASSWORD',
        'DATABASE_URL',
        'AUTH_KEY',
        'SECURE_AUTH_KEY',
        'LOGGED_IN_KEY',
        'NONCE_KEY',
        'AUTH_SALT',
        'SECURE_AUTH_SALT',
        'LOGGED_IN_SALT',
        'NONCE_SALT',
    ];

    /**
     * Create a new Whoops handler for debug mode.
     *
     * @return \Whoops\Handler\PrettyPageHandler
     */
    public function forDebug()
    {
        return tap(new PrettyPageHandler(), function ($handler) {
            $handler->handleUnconditionally(true);

            $this->registerApplicationPaths($handler)
                ->registerBlacklist($handler)
                ->registerEditor($handler)
                ->registerWordPressData($handler);
        });
    }

    /**
     * Registers WordPress context with the handler
     *
     * @param  \Whoops\Handler\PrettyPageHandler  $handler
     * @return static
     */
    protected function registerWordPressData($handler)
    {
        $handler
            ->addDataTableCallback('WordPress Data', function () {
                global $wp;

                if (!$wp instanceof WP) {
                    return [];
                }

                return Collection::make(get_object_vars($wp))
                    ->forget('private_query_vars')
                    ->forget('public_query_vars')
                    ->filter()
                    ->all();
            })
            ->addDataTableCallback(sprintf('%s Data', WP_Query::class), function () {
                global $wp_query;

                if (!$wp_query instanceof WP_Query) {
                    return [];
                }

                return Collection::make(get_object_vars($wp_query))
                    ->forget('posts')
                    ->forget('post')
                    ->filter()
                    ->all();
            })
            ->addDataTableCallback(sprintf('%s Data', WP_Post::class), function () {
                $post = get_post();

                if (!$post instanceof WP_Post) {
                    return [];
                }

                return get_object_vars($post);
            });

        return $this;
    }

    /**
     * Register the blacklist with the handler.
     *
     * @param  \Whoops\Handler\PrettyPageHandler  $handler
     * @return static
     */
    protected function registerBlacklist($handler)
    {
        $blacklist = [
            '_ENV' => $this->secrets,
            '_SERVER' => $this->secrets
        ];

        foreach ($blacklist as $key => $secrets) {
            foreach ($secrets as $secret) {
                $handler->blacklist($key, $secret);
            }
        }

        return parent::registerBlacklist($handler);
    }
}

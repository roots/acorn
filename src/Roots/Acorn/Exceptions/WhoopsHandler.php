<?php

namespace Roots\Acorn\Exceptions;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Exceptions\WhoopsHandler as FoundationWhoopsHandler;
use Illuminate\Support\Arr;
use Whoops\Handler\PrettyPageHandler;

use function Roots\base_path;
use function Roots\config;

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
                 ->registerEditor($handler);
        });
    }

    /**
     * Get the application paths except for the "vendor" directory.
     *
     * @return array
     */
    protected function directoriesExceptVendor()
    {
        return Arr::except(
            array_flip((new Filesystem())->directories(base_path())),
            [base_path('vendor')]
        );
    }

    /**
     * Register the blacklist with the handler.
     *
     * @param  \Whoops\Handler\PrettyPageHandler  $handler
     * @return $this
     */
    protected function registerBlacklist($handler)
    {
        $blacklist = array_merge_recursive([
            '_ENV' => $this->secrets,
            '_SERVER' => $this->secrets
        ], config('app.debug_blacklist', config('app.debug_hide', [])));

        foreach ($blacklist as $key => $secrets) {
            foreach ($secrets as $secret) {
                $handler->blacklist($key, $secret);
            }
        }

        return $this;
    }

    /**
     * Register the editor with the handler.
     *
     * @param  \Whoops\Handler\PrettyPageHandler  $handler
     * @return $this
     */
    protected function registerEditor($handler)
    {
        if (config('app.editor', false)) {
            $handler->setEditor(config('app.editor'));
        }

        return $this;
    }
}

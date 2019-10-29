<?php

namespace Roots\Acorn\Exceptions;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Whoops\Handler\JsonResponseHandler;
use Roots\Acorn\Exceptions\Handler\AjaxResponseHandler;
use Roots\Acorn\Exceptions\Handler\PrettyPageHandler;
use Roots\Acorn\Exceptions\Handler\RestResponseHandler;

use function Roots\app;
use function Roots\base_path;
use function Roots\config;

class WhoopsHandler
{
    /**
     * Default Environment Blacklist
     *
     * @var array
     */
    protected $blacklist = [
        'DB_USER',
        'DB_PASSWORD',
        'AUTH_KEY',
        'SECURE_AUTH_KEY',
        'LOGGED_IN_KEY',
        'NONCE_KEY',
        'AUTH_SALT',
        'SECURE_AUTH_SALT',
        'LOGGED_IN_SALT',
        'NONCE_SALT',
        'password',
    ];

    /**
     * Create a new Whoops handler for debug mode.
     *
     * @return \Whoops\Handler\PrettyPageHandler
     */
    public function forDebug()
    {
        return tap($this->handler(), function ($handler) {
            $handler->handleUnconditionally(true);

            $this->registerApplicationPaths($handler)
                 ->registerBlacklist($handler)
                 ->registerEditor($handler);
        });
    }

    /**
     * Return the appropriate handler for the Whoops instance.
     *
     * @return mixed
     */
    public function handler()
    {
        if ($this->isAjax()) {
            return new AjaxResponseHandler();
        }

        if ($this->isRest()) {
            return new RestResponseHandler();
        }

        if ($this->isJson()) {
            return new JsonResponseHandler();
        }

        return new PrettyPageHandler();
    }

    /**
     * Register the application paths with the handler.
     *
     * @param  \Whoops\Handler\PrettyPageHandler $handler
     * @return $this
     */
    protected function registerApplicationPaths($handler)
    {
        $handler->setApplicationPaths(
            array_flip($this->directoriesExceptVendor())
        );

        return $this;
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
     * @param  \Whoops\Handler\PrettyPageHandler $handler
     * @return $this
     */
    protected function registerBlacklist($handler)
    {
        $this->blacklist = array_merge([
            '_ENV' => $this->blacklist,
            '_SERVER' => $this->blacklist,
            '_POST' => $this->blacklist,
        ], config('app.debug_blacklist', []));

        foreach ($this->blacklist as $key => $secrets) {
            foreach ($secrets as $secret) {
                $handler->blacklist($key, $secret);
            }
        }

        return $this;
    }

    /**
     * Register the editor with the handler.
     *
     * @param  \Whoops\Handler\PrettyPageHandler $handler
     * @return $this
     */
    protected function registerEditor($handler)
    {
        if (config('app.editor', false)) {
            $handler->setEditor(config('app.editor'));
        }

        return $this;
    }

    /**
     * Determine if the provider should return JSON.
     *
     * @return bool
     */
    protected function isJson()
    {
        return app()->runningInConsole();
    }

    /**
     * Determine if the error provider should return an Ajax response.
     *
     * @return bool
     */
    protected function isAjax()
    {
        return defined('DOING_AJAX') && DOING_AJAX;
    }

    /**
     * Determine if the error provider should return a response for the Rest API.
     *
     * @return bool
     */
    protected function isRest()
    {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }

        if (! empty($_SERVER['REQUEST_URI']) && Str::contains($_SERVER['REQUEST_URI'], rest_get_url_prefix())) {
            return true;
        }

        return false;
    }
}

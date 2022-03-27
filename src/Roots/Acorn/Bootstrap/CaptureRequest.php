<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Support\Facades\Facade;
use Roots\Acorn\Application;

class CaptureRequest
{
    /**
     * Bootstrap the given application.
     *
     * @param \Roots\Acorn\Application $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        if ($app->runningInConsole()) {
            $this->enableHttpsInConsole();
        }

        $app->instance('request', \Illuminate\Http\Request::capture());
        Facade::clearResolvedInstance('request');
    }

    /**
     * Enable $_SERVER[HTTPS] in a console environment.
     *
     * @return void
     */
    protected function enableHttpsInConsole()
    {
        $enable = apply_filters('acorn/enable_https_in_console', parse_url(get_option('home'), PHP_URL_SCHEME) === 'https');

        if ($enable) {
            $_SERVER['HTTPS'] = 'on';
        }
    }
}

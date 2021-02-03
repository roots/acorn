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
        $app->instance('request', \Illuminate\Http\Request::capture());
        Facade::clearResolvedInstance('request');
    }
}

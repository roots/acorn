<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Roots\Acorn\Concerns\Bindings;

class LoadBindings
{
    /**
     * Bootstrap the given application.
     *
     * @param  Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        if (class_uses($app, Bindings::class)) {
            $app->registerContainerBindings();
        }
    }
}

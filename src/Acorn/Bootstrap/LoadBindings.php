<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class LoadBindings
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        if (class_uses($app, Roots\Acorn\Concerns\Bindings::class)) {
            $app->registerContainerBindings();
        }
    }
}

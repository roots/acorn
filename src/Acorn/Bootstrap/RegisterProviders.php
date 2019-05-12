<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class RegisterProviders
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->registerConfiguredProviders();
    }
}

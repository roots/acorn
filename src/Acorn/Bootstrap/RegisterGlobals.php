<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Facade;

class RegisterGlobals
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);

        if (! $app->config->get('app.globals', false)) {
            return;
        }

        $app->withAliases();
    }
}

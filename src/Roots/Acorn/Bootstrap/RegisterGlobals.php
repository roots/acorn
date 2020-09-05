<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Foundation\PackageManifest;
use Illuminate\Support\Facades\Facade;
use Roots\Acorn\AliasLoader;
use Roots\Acorn\Application;

class RegisterGlobals
{
    /**
     * Bootstrap the given application.
     *
     * @param  Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);

        if (! $app->config->get('app.globals', false)) {
            return;
        }

        AliasLoader::getInstance(array_merge(
            $app->make('config')->get('app.aliases', []),
            $app->make(PackageManifest::class)->aliases()
        ))->register();
    }
}

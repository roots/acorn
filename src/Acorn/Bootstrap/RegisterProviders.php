<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\PackageManifest;

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
        $app->instance(PackageManifest::class, new PackageManifest(
            new Filesystem(),
            $app->basePath(),
            $app->getCachedPackagesPath()
        ));

        $app->registerConfiguredProviders();
    }
}

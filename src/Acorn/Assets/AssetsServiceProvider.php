<?php

namespace Roots\Acorn\Assets;

use Roots\Acorn\ServiceProvider;

class AssetsServiceProvider extends ServiceProvider
{
    /** {@inheritDoc} */
    public function register()
    {
        $this->registerManager();
        $this->registerManifests();
    }

    protected function registerManager()
    {
        $this->app->singleton('assets', function ($app) {
            return new AssetsManager($app);
        });
    }

    protected function registerManifests()
    {
        $this->app->singleton('assets.manifest', function () {
            return $this->app->get('assets')->manifest();
        });
    }
}

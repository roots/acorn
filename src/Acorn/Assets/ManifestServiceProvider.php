<?php

namespace Roots\Acorn\Assets;

use Roots\Acorn\ServiceProvider;

class ManifestServiceProvider extends ServiceProvider
{
    /** {@inheritDoc} */
    public function register()
    {
        $this->app->singleton('assets.manifest', function ($app) {
            $config = $this->app['config']['assets'];
            return Manifest::fromJson($config['manifest'], $config['uri'], $config['path']);
        });
    }
}

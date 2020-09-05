<?php

namespace Roots\Acorn\Assets;

use Roots\Acorn\ServiceProvider;

class AssetsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('assets', function ($app) {
            return new AssetsManager($app);
        });

        $this->app->singleton('assets.manifest', function () {
            return $this->app->get('assets')->manifest();
        });
    }
}

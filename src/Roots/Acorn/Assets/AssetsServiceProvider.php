<?php

namespace Roots\Acorn\Assets;

use Roots\Acorn\Assets\View\BladeDirective;
use Roots\Acorn\ServiceProvider;

class AssetsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('assets', function () {
            return new Manager($this->app->make('config')->get('assets'));
        });

        $this->app->singleton('assets.manifest', function ($app) {
            return $app['assets']->manifest($this->getDefaultManifest());
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->bound('view')) {
            $this->app->make('view')
                ->getEngineResolver()->resolve('blade')->getCompiler()
                ->directive('asset', new BladeDirective());
        }
    }

    protected function getDefaultManifest()
    {
        return $this->app['config']['assets.default'];
    }
}

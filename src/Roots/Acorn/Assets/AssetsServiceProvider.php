<?php

namespace Roots\Acorn\Assets;

use Illuminate\Support\ServiceProvider;
use Roots\Acorn\Assets\View\BladeDirective;

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

        $this->app->singleton('assets.vite', Vite::class);

        $this->app->singleton('assets.manifest', function ($app) {
            return $app['assets']->manifest($this->getDefaultManifest());
        });

        $this->app->alias('assets.manifest', \Roots\Acorn\Assets\Manifest::class);
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
                ->directive('asset', new BladeDirective);
        }
    }

    /**
     * Get the default manifest.
     *
     * @return string
     */
    protected function getDefaultManifest()
    {
        return $this->app['config']['assets.default'];
    }
}

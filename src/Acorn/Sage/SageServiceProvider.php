<?php

namespace Roots\Acorn\Sage;

use function Roots\add_filters;
use Roots\Acorn\Config;
use Roots\Acorn\Sage\Sage;
use Roots\Acorn\Sage\ViewFinder;
use Roots\Acorn\ServiceProvider;

class SageServiceProvider extends ServiceProvider
{
    /** {@inheritDoc} */
    public function register()
    {
        $this->app->singleton('sage', Sage::class);
        $this->app->bind('sage.finder', ViewFinder::class);

        $this->loadConfig();
        $this->registerProviders();
    }

    public function boot()
    {
        if ($this->app->bound('view')) {
            $this->app['sage']->attach();
        }
    }

    private function loadConfig()
    {
        collect($this->app->configPath('app'))->each(
            function (string $item) {
                $this->app->configure(basename($item, '.php'));
            }
        );
    }

    private function registerProviders()
    {
        $providers = $this->app['config']['app']['providers'];

        collect($providers)->each(
            function (string $provider) {
                $this->app->register($provider);
            }
        );
    }
}

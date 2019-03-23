<?php

namespace Roots\Clover;

use Roots\Clover\Concerns\Lifecycle;
use Roots\Acorn\ServiceProvider as BaseServiceProvider;

abstract class ServiceProvider extends BaseServiceProvider
{
    /** @var \Roots\Clover\Meta */
    protected $meta;

    /**
     * Register the plugin with the application container.
     *
     * @return void
     */
    public function register()
    {
        if (!$configFile = $this->locateConfig()) {
            return;
        }

        $this->mergeConfigFrom($configFile, $this->meta->key);
        $config = $this->app['config']->get($this->meta->key);

        $this->registerProviders($config['providers'] ?? []);

        if ($views = $config['views'] ?? null) {
            $this->loadViews($views);
        }
    }

    /**
     * Run the plugin
     *
     * @return void
     */
    public function boot()
    {
        $plugin = $this->app[$this->meta->key];

        if (in_array(Lifecycle::class, class_uses($plugin))) {
            $plugin->lifecycle($this->meta);
        }

        $plugin->run();
    }

    /**
     * If a config file can be located, load specified providers and views
     */
    protected function locateConfig()
    {
        if (file_exists($configFile = dirname($this->meta->plugin) . "/config/{$this->meta->key}.php")) {
            return $configFile;
        }
        return null;
    }

    protected function registerProviders(iterable $providers)
    {
        // Prevent endless loop
        $providers = array_filter($providers, function ($provider) {
            return $provider !== static::class;
        });

        array_map([$this->app, 'register'], $providers);
    }

    protected function loadViews($views)
    {
        if ($this->app->bound('view')) {
            $this->loadViewsFrom($views, $this->meta->key);
        }
    }
}

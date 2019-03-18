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
        // Exit early if no config file exists
        if (!file_exists($configFile = dirname($this->meta->plugin) . "/config/{$this->meta->key}.php")) {
            return;
        }
        $this->mergeConfigFrom($configFile, $this->meta->key);
        $config = $this->app['config']->get($this->meta->key);
        $providers = array_filter($config['providers'], function ($provider) {
            return $provider !== static::class;
        });
        array_map([$this->app, 'register'], $providers);
        $this->app->singleton($this->meta->key, PluginName::class);
        if ($this->app->bound('view') && isset($config['views'])) {
            $this->loadViewsFrom($config['views'], $this->meta->key);
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
            $plugin->lifecycle("{$this->meta->key}.meta");
        }

        $plugin->run();
    }
}

<?php

namespace PluginNamespace;

use function Roots\config;

use Roots\Clover\Concerns\Lifecycle;
use Roots\Acorn\ServiceProvider as BaseServiceProvider;

abstract class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the plugin with the application container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(dirname(__DIR__) . "/config/{$this->meta->key}.php", $this->meta->key);
        $providers = array_filter($this->app['config']->get("{$this->meta->key}.providers"), function ($provider) {
            return $provider !== self::class;
        });
        array_map([$this->app, 'register'], $providers);
        $this->app->singleton($this->meta->key, PluginName::class);
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
    }
}

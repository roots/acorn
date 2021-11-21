<?php

namespace Roots\Acorn\Providers;

use Illuminate\Foundation\Events\VendorTagPublished;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\ServiceProvider;

class AcornServiceProvider extends ServiceProvider
{
    /**
     * Core configs.
     *
     * @var string[]
     */
    protected $configs = ['app', 'services'];

    /**
     * Provider configs.
     *
     * @var string[]
     */
    protected $provider_configs = [
        \Fruitcake\Cors\CorsServiceProvider::class => 'cors',
        \Illuminate\Auth\AuthServiceProvider::class => 'auth',
        \Illuminate\Broadcasting\BroadcastServiceProvider::class => 'broadcasting',
        \Illuminate\Cache\CacheServiceProvider::class => 'cache',
        \Illuminate\Database\DatabaseServiceProvider::class => 'database',
        \Illuminate\Filesystem\FilesystemServiceProvider::class => 'filesystems',
        \Illuminate\Hashing\HashServiceProvider::class => 'hashing',
        \Illuminate\Log\LogServiceProvider::class => 'logging',
        \Illuminate\Mail\MailServiceProvider::class => 'mail',
        \Illuminate\Queue\QueueServiceProvider::class => 'queue',
        \Illuminate\Session\SessionServiceProvider::class => 'session',
        \Illuminate\View\ViewServiceProvider::class => 'view',
        \Laravel\Sanctum\SanctumServiceProvider::class => 'sanctum',
        \Roots\Acorn\Assets\AssetsServiceProvider::class => 'assets',
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfigs();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishables();
        }
    }

    /**
     * Register application configs.
     *
     * @return void
     */
    protected function registerConfigs()
    {
        $configs = array_merge($this->configs, array_values($this->provider_configs));

        foreach ($configs as $config) {
            $this->mergeConfigFrom(dirname(__DIR__, 4) . "/config/{$config}.php", $config);
        }
    }

    /**
     * Publish application files.
     *
     * @return void
     */
    protected function registerPublishables()
    {
        $this->publishConfigs();
        $this->publishResources();
        $this->publishStorage();
    }

    /**
     * Publish application configs.
     *
     * @return void
     */
    protected function publishConfigs()
    {
        foreach ($this->filterPublishableConfigs() as $config) {
            $this->publishes([
                dirname(__DIR__, 4) . "/config/{$config}.php" => base_path('config') . "/{$config}.php"
            ], 'acorn:init');

            if ($this->app->configPath() === dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'config') {
                continue;
            }

            $this->publishes([
                dirname(__DIR__, 4) . "/config/{$config}.php" => config_path("{$config}.php")
            ], "acorn:config:{$config}");

            $this->publishes([
                dirname(__DIR__, 4) . "/config/{$config}.php" => config_path("{$config}.php")
            ], "acorn:config");
        }
    }

    /**
     * Publish application resources.
     *
     * @return void
     */
    protected function publishResources()
    {
        if ($this->app->resourcePath() === dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'resources') {
            $this->publishes([
                dirname(__DIR__, 4) . "/resources" => base_path('resources')
            ], 'acorn:init');

            return;
        }
    }

    /**
     * Publish application storage.
     *
     * @return void
     */
    protected function publishStorage()
    {
        if ($this->app->storagePath() === WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'acorn') {
            $this->app->make('events')->listen(function (VendorTagPublished $event) {
                if ($event->tag !== 'acorn:init') {
                    return;
                }

                $files = new Filesystem();

                $files->deleteDirectory(WP_CONTENT_DIR . '/cache/acorn');
            });

            $this->publishes([
                dirname(__DIR__, 4) . "/storage" => base_path('storage')
            ], 'acorn:init');

            return;
        }
    }

    /**
     * Filters out providers that aren't registered
     *
     * @return string[]
     */
    protected function filterPublishableConfigs()
    {
        $configs = array_filter($this->provider_configs, function ($provider) {
            return class_exists($provider) && $this->app->getProviders($provider);
        }, ARRAY_FILTER_USE_KEY);

        return array_unique(array_merge($this->configs, array_values($configs)));
    }
}

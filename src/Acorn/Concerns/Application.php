<?php

namespace Roots\Acorn\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Required methods to satisfy Laravel Application contract
 *
 * @copyright Roots Team, Taylor Otwell
 * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
 * @link      https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php
 */
trait Application
{
    /** @var string The base path of the application installation. */
    protected $basePath;

    /** @var bool Indicates if the application has been bootstrapped before. */
    protected $hasBeenBootstrapped = false;

    /** @var boolean Indicates if the application has "booted". */
    protected $booted = false;

    /** @var callable[] All of the loaded callbacks to be run before boot. */
    protected $bootingCallbacks = [];

    /** @var callable[] All of the loaded callbacks to be run after boot. */
    protected $bootedCallbacks = [];

    /** @var callable[] All of the loaded callbacks to be run during termination. */
    protected $terminatingCallbacks = [];

    /** @var \Illuminate\Support\ServiceProvider[] All of the registered service providers. */
    protected $serviceProviders = [];

    /** @var string[] The names of the loaded service providers. */
    protected $loadedProviders = [];

    /** @var array The deferred services and their providers. */
    protected $deferredServices = [];

    /** @var string The custom application path defined by the developer. */
    protected $appPath;

    /** @var string The custom database path defined by the developer. */
    protected $databasePath;

    /** @var string The custom public path defined by the developer. */
    protected $publicPath;

    /** @var string The custom database path defined by the developer. */
    protected $storagePath;

    /** @var string The application namespace. */
    protected $namespace;

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L296-L307
     *
     * @param  string  $path
     * @return string
     */
    public function path($path = '')
    {
        $appPath = $this->appPath ?: $this->basePath . DIRECTORY_SEPARATOR . 'app';
        return $appPath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Set the application directory.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L309-L322
     *
     * @param  string  $path
     * @return $this
     */
    public function useAppPath($path)
    {
        $this->appPath = $path;
        return $this;
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L324-L333
     *
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the bootstrap directory.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L335-L344
     *
     * @param  string  $path Optionally, a path to append to the bootstrap path
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'bootstrap' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the application configuration files.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L346-L355
     *
     * @param  string  $path Optionally, a path to append to the config path
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the database directory.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L357-L366
     *
     * @param  string  $path Optionally, a path to append to the database path
     * @return string
     */
    public function databasePath($path = '')
    {
        return ($this->databasePath ?: $this->basePath . DIRECTORY_SEPARATOR . 'database')
            . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Set the database directory.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L368-L381
     *
     * @param  string  $path
     * @return $this
     */
    public function useDatabasePath($path)
    {
        $this->databasePath = $path;
        return $this;
    }

    /**
     * Get the path to the language files.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L383-L391
     *
     * @return string
     */
    public function langPath()
    {
        return $this->resourcePath() . DIRECTORY_SEPARATOR . 'lang';
    }

    /**
     * Get the path to the public / web directory.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.17/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.17/src/Illuminate/Foundation/Application.php#L393-L401
     *
     * @return string
     */
    public function publicPath()
    {
        return $this->publicPath ?? $this->basePath . DIRECTORY_SEPARATOR . 'public';
    }

    /**
     * Set the public path.
     *
     * @param  string  $path
     * @return $this
     */
    public function usePublicPath($path)
    {
        $this->publicPath = $path;
        return $this;
    }

    /**
     * Get the path to the resources directory.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L428-L437
     *
     * @param  string  $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'resources' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the storage directory.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L403-L411
     *
     * @return string
     */
    public function storagePath()
    {
        return $this->storagePath ?: $this->basePath . DIRECTORY_SEPARATOR . 'storage';
    }

    /**
     * Set the storage directory.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L413-L426
     *
     * @param  string  $path
     * @return $this
     */
    public function useStoragePath($path)
    {
        $this->storagePath = $path;
        return $this;
    }

    /**
     * Get or check the current application environment.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L495-L510
     *
     * @param  string|array  $environments
     * @return string|bool
     */
    public function environment(...$environments)
    {
        if (count($environments) > 0) {
            $patterns = is_array($environments[0]) ? $environments[0] : $environments;

            return Str::is($patterns, $this['env']);
        }

        return $this['env'];
    }

    /**
     * Determine if the application is running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return defined('WP_CLI_VERSION') || php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * Determine if the application is running unit tests.
     *
     * @return bool
     */
    public function runningUnitTests()
    {
        /* unit testing in wordpress lmao 😂 */
        return $this->environment('testing');
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return file_exists(ABSPATH . '.maintenance');
    }

    /**
     * Register a service provider with the application.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L577-L624
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  bool   $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $force = false)
    {
        if (($registered = $this->getProvider($provider)) && ! $force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        $provider->register();

        // If there are bindings / singletons set as properties on the provider we
        // will spin through them and register them with the application, which
        // serves as a convenience layer while registering a lot of bindings.
        if (property_exists($provider, 'bindings')) {
            foreach ($provider->bindings as $key => $value) {
                $this->bind($key, $value);
            }
        }

        if (property_exists($provider, 'singletons')) {
            foreach ($provider->singletons as $key => $value) {
                $this->singleton($key, $value);
            }
        }

        $this->markAsRegistered($provider);

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->booted) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    /**
     * Register a deferred provider and service.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L715-L738
     *
     * @param  string  $provider
     * @param  string|null  $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null)
    {
        // Once the provider that provides the deferred service has been registered we
        // will remove it from our local list of the deferred services with related
        // providers so that this container does not try to resolve it out again.
        if ($service) {
            unset($this->deferredServices[$service]);
        }

        $this->register($instance = new $provider($this));

        if (! $this->booted) {
            $this->booting(function () use ($instance) {
                $this->bootProvider($instance);
            });
        }
    }

    /**
     * Resolve a service provider instance from the class name.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L652-L661
     *
     * @param  string  $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    public function resolveProvider($provider)
    {
        return new $provider($this);
    }

    /**
     * Mark the given provider as registered.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L663-L674
     *
     * @param  \Illuminate\Support\ServiceProvider  $provider
     * @return void
     */
    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[] = $provider;
        $this->loadedProviders[get_class($provider)] = true;
    }

    /**
     * Boot the application's service providers.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L783-L806
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        // Once the application has booted we will also fire some "booted" callbacks
        // for any listeners that need to do work after this initial booting gets
        // finished. This is useful when ordering the boot-up processes we run.
        $this->fireAppCallbacks($this->bootingCallbacks);

        array_walk($this->serviceProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    /**
     * Boot the given service provider.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L808-L819
     *
     * @param  \Illuminate\Support\ServiceProvider  $provider
     * @return void
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
        }

        if ($this['config']['app.preflight'] && method_exists($provider, 'preflight')) {
            $this->call([$provider, 'preflight']);
        }
    }

    /**
     * Register a new boot listener.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L821-L830
     *
     * @param  callable  $callback
     * @return void
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L832-L845
     *
     * @param  callable  $callback
     * @return void
     */
    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->fireAppCallbacks([$callback]);
        }
    }

    /**
     * Determine if the application has booted.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L773-L781
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * Call the booting callbacks for the application.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L847-L858
     *
     * @param  callable[]  $callbacks
     * @return void
     */
    protected function fireAppCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callback($this);
        }
    }

    /**
     * Run the given array of bootstrap classes.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L197-L214
     *
     * @param  array  $bootstrappers
     * @return void
     */
    public function bootstrapWith(array $bootstrappers)
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this['events']->dispatch('bootstrapping: ' . $bootstrapper, [$this]);

            $this->make($bootstrapper)->bootstrap($this);

            $this['events']->dispatch('bootstrapped: ' . $bootstrapper, [$this]);
        }
    }

    /**
     * Determine if the application configuration is cached.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L899-L907
     *
     * @return bool
     */
    public function configurationIsCached()
    {
        return file_exists($this->getCachedConfigPath());
    }

    /**
     * Detect the application's current environment.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L522-L533
     *
     * @param  \Closure  $callback
     * @return string
     */
    public function detectEnvironment(\Closure $callback)
    {
        return $this['env'] = $this->call($callback);
    }

    /**
     * Get the path to the configuration cache file.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L909-L917
     *
     * @return string
     */
    public function getCachedConfigPath()
    {
        return $_ENV['APP_CONFIG_CACHE'] ?? $this->storagePath() . '/framework/cache/config.php';
    }

    /**
     * Get the path to the cached services.php file.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L879-L887
     *
     * @return string
     */
    public function getCachedServicesPath()
    {
        return $_ENV['APP_SERVICES_CACHE'] ?? $this->storagePath() . '/framework/cache/services.php';
    }

    /**
     * Get the path to the cached packages.php file.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L889-L897
     *
     * @return string
     */
    public function getCachedPackagesPath()
    {
        return $_ENV['APP_PACKAGES_CACHE'] ?? $this->storagePath() . '/framework/cache/packages.php';
    }

    /**
     * Get the path to the routes cache file.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L929-L937
     *
     * @return string
     */
    public function getCachedRoutesPath()
    {
        return $_ENV['APP_ROUTES_CACHE'] ?? $this->storagePath() . '/framework/cache/routes.php';
    }

    /**
     * Get the application's deferred services.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L1003-L1011
     *
     * @return array
     */
    public function getDeferredServices()
    {
        return $this->deferredServices;
    }
    /**
     * Set the application's deferred services.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L1013-L1022
     *
     * @param  array  $services
     * @return void
     */
    public function setDeferredServices(array $services)
    {
        $this->deferredServices = $services;
    }
    /**
     * Add an array of services to the application's deferred services.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L1024-L1033
     *
     * @param  array  $services
     * @return void
     */
    public function addDeferredServices(array $services)
    {
        $this->deferredServices = array_merge($this->deferredServices, $services);
    }
    /**
     * Determine if the given service is a deferred service.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L1035-L1044
     *
     * @param  string  $service
     * @return bool
     */
    public function isDeferredService($service)
    {
        return isset($this->deferredServices[$service]);
    }

    /**
     * Get the current application locale.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L1057-L1065
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get('app.locale') ?: \get_locale();
    }

    /**
     * Get the application namespace.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L1164-L1188
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
    {
        if (! is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents($this->basePath('composer.json')), true);

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath($this->path()) === realpath($this->basePath($pathChoice))) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new \RuntimeException(
            __('Unable to detect application namespace.', 'acorn')
        );
    }

    /**
     * Get the registered service provider instance if it exists.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L626-L635
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @return \Illuminate\Support\ServiceProvider|null
     */
    public function getProvider($provider)
    {
        return array_values($this->getProviders($provider))[0] ?? null;
    }

    /**
     * Get the registered service provider instances if any exist.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L637-L650
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @return array
     */
    public function getProviders($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return Arr::where($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        });
    }

    /**
     * Determine if the application has been bootstrapped before.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L253-L261
     *
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Load and boot all of the remaining deferred providers.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L676-L691
     *
     * @return void
     */
    public function loadDeferredProviders()
    {
        // We will simply spin through each of the deferred providers and register each
        // one and boot them if the application has booted. This should make each of
        // the remaining services available to this application for immediate use.
        foreach ($this->deferredServices as $service => $provider) {
            $this->loadDeferredProvider($service);
        }

        $this->deferredServices = [];
    }

    /**
     * Load the provider for a deferred service.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L693-L713
     *
     * @param  string  $service
     * @return void
     */
    public function loadDeferredProvider($service)
    {
        if (! isset($this->deferredServices[$service])) {
            return;
        }

        $provider = $this->deferredServices[$service];

        // If the service provider has not already been loaded and registered we can
        // register it with the application and remove the service from this list
        // of deferred services, since it will already be loaded on subsequent.
        if (! isset($this->loadedProviders[$provider])) {
            $this->registerDeferredProvider($provider, $service);
        }
    }

    /**
     * Determine if the application routes are cached.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L919-L927
     *
     * @return bool
     */
    public function routesAreCached()
    {
        return $this['files']->exists($this->getCachedRoutesPath());
    }

    /**
     * Set the current application locale.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L1067-L1080
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this['config']->set('app.locale', $locale);
        $this['translator']->setLocale($locale);
        $this['events']->dispatch(new Events\LocaleUpdated($locale));
    }

    /**
     * Determine if middleware has been disabled for the application.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L868-L877
     *
     * @return bool
     */
    public function shouldSkipMiddleware()
    {
        return $this->bound('middleware.disable') &&
               $this->make('middleware.disable') === true;
    }

    /**
     * Terminate the application.
     *
     * @copyright Taylor Otwell
     * @license   https://github.com/laravel/framework/blob/v5.8.5/LICENSE.md MIT
     * @link https://github.com/laravel/framework/blob/v5.8.5/src/Illuminate/Foundation/Application.php#L981-L991
     *
     * @return void
     */
    public function terminate()
    {
        foreach ($this->terminatingCallbacks as $terminating) {
            $this->call($terminating);
        }
    }

    /**
     * This method is not used.
     *
     * @return string
     */
    public function environmentFile()
    {
        //
    }

    /**
     * This method is not used.
     *
     * @return string
     */
    public function environmentFilePath()
    {
        //
    }

    /**
     * This method is not used.
     *
     * @return string
     */
    public function environmentPath()
    {
        //
    }

    /**
     * This method is not used.
     *
     * @param string
     * @return string
     */
    public function loadEnvironmentFrom($file)
    {
        //
    }
}

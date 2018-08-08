<?php

namespace Roots\Acorn;

use Illuminate\Container\Container;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Roots\Acorn\Filesystem\FilesystemServiceProvider;
// use Roots\Acorn\Assets\ManifestServiceProvider;
// use Roots\Acorn\View\ViewServiceProvider;

/**
 * Application container
 *
 * Barebones version of Laravel's Application container.
 *
 * @copyright Roots Team, Taylor Otwell
 * @license   https://github.com/laravel/framework/blob/v5.6.25/LICENSE.md MIT
 * @license   https://github.com/laravel/lumen-framework/blob/v5.6.3/LICENSE.md MIT
 * @link      https://github.com/laravel/framework/blob/v5.6.25/src/Illuminate/Foundation/Application.php
 * @link      https://github.com/laravel/lumen-framework/blob/v5.6.3/src/Application.php
 */
class Application extends Container
{
    const VERSION = 'Acorn (1.0.0) (Laravel Components 5.7.*)';

    /** {@inheritDoc} */
    protected static $instance;

    /** @var boolean */
    protected $booted = false;

    /** @var array Booting callbacks */
    protected $bootingCallbacks = [];

    /** @var array Booted callbacks */
    protected $bootedCallbacks = [];

    /** @var \Illuminate\Support\ServiceProvider[] All of the registered service providers. */
    protected $serviceProviders = [];

    /**
     * Create a new Acorn application instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(self::class, $this);
        $this->instance(static::class, $this);
        $this->instance(parent::class, $this);
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider($this));
        $this->register(new FilesystemServiceProvider($this));
        // $this->register(new ViewServiceProvider($this));
        // $this->register(new ManifestServiceProvider($this));
    }

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
     * Get or check the current application environment.
     *
     * @return string|bool
     */
    public function environment()
    {
        $env = ($this['env'] ?? WP_ENV ??  'production');

        if (func_num_args() === 0) {
            return $env;
        }

        $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();
        return Str::is($patterns, $env);
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
     * Register the core class aliases in the container.
     *
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        // phpcs:disable
        $this->alias([
            'app'                  => [\Acorn\Application\Container::class, \Illuminate\Foundation\Application::class, \Illuminate\Contracts\Container\Container::class, \Illuminate\Contracts\Foundation\Application::class,  \Psr\Container\ContainerInterface::class],
            'assets.manifest'      => [\Acorn\Assets\Manifest::class],
            'auth'                 => [\Illuminate\Auth\AuthManager::class, \Illuminate\Contracts\Auth\Factory::class],
            'auth.driver'          => [\Illuminate\Contracts\Auth\Guard::class],
            'blade.compiler'       => [\Illuminate\View\Compilers\BladeCompiler::class],
            'cache'                => [\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class],
            'cache.store'          => [\Illuminate\Cache\Repository::class, \Illuminate\Contracts\Cache\Repository::class],
            'config'               => [\Acorn\Application\Config::class, \Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
            'cookie'               => [\Illuminate\Cookie\CookieJar::class, \Illuminate\Contracts\Cookie\Factory::class, \Illuminate\Contracts\Cookie\QueueingFactory::class],
            'encrypter'            => [\Illuminate\Encryption\Encrypter::class, \Illuminate\Contracts\Encryption\Encrypter::class],
            'db'                   => [\Illuminate\Database\DatabaseManager::class],
            'db.connection'        => [\Illuminate\Database\Connection::class, \Illuminate\Database\ConnectionInterface::class],
            'events'               => [\Illuminate\Events\Dispatcher::class, \Illuminate\Contracts\Events\Dispatcher::class],
            'files'                => [\Acorn\Filesystem\Filesystem::class, \Illuminate\Filesystem\Filesystem::class],
            'filesystem'           => [\Illuminate\Filesystem\FilesystemManager::class, \Illuminate\Contracts\Filesystem\Factory::class],
            'filesystem.disk'      => [\Illuminate\Contracts\Filesystem\Filesystem::class],
            'filesystem.cloud'     => [\Illuminate\Contracts\Filesystem\Cloud::class],
            'hash'                 => [\Illuminate\Hashing\HashManager::class],
            'hash.driver'          => [\Illuminate\Contracts\Hashing\Hasher::class],
            'translator'           => [\Illuminate\Translation\Translator::class, \Illuminate\Contracts\Translation\Translator::class],
            'log'                  => [\Illuminate\Log\LogManager::class, \Psr\Log\LoggerInterface::class],
            'mailer'               => [\Illuminate\Mail\Mailer::class, \Illuminate\Contracts\Mail\Mailer::class, \Illuminate\Contracts\Mail\MailQueue::class],
            'auth.password'        => [\Illuminate\Auth\Passwords\PasswordBrokerManager::class, \Illuminate\Contracts\Auth\PasswordBrokerFactory::class],
            'auth.password.broker' => [\Illuminate\Auth\Passwords\PasswordBroker::class, \Illuminate\Contracts\Auth\PasswordBroker::class],
            'queue'                => [\Illuminate\Queue\QueueManager::class, \Illuminate\Contracts\Queue\Factory::class, \Illuminate\Contracts\Queue\Monitor::class],
            'queue.connection'     => [\Illuminate\Contracts\Queue\Queue::class],
            'queue.failer'         => [\Illuminate\Queue\Failed\FailedJobProviderInterface::class],
            'redirect'             => [\Illuminate\Routing\Redirector::class],
            'redis'                => [\Illuminate\Redis\RedisManager::class, \Illuminate\Contracts\Redis\Factory::class],
            'request'              => [\Illuminate\Http\Request::class, \Symfony\Component\HttpFoundation\Request::class],
            'router'               => [\Illuminate\Routing\Router::class, \Illuminate\Contracts\Routing\Registrar::class, \Illuminate\Contracts\Routing\BindingRegistrar::class],
            'session'              => [\Illuminate\Session\SessionManager::class],
            'session.store'        => [\Illuminate\Session\Store::class, \Illuminate\Contracts\Session\Session::class],
            'url'                  => [\Illuminate\Routing\UrlGenerator::class, \Illuminate\Contracts\Routing\UrlGenerator::class],
            'validator'            => [\Illuminate\Validation\Factory::class, \Illuminate\Contracts\Validation\Factory::class],
            'view'                 => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
            'view.finder'          => [\Acorn\View\FileViewFinder::class, \Illuminate\View\FileViewFinder::class, \Illuminate\Contracts\View\FileViewFinder::class]
        ]);
        // phpcs:enable
    }

    /**
     * {@inheritDoc}
     *
     * Also accepts an array of aliases as the first parameter
     */
    public function alias($key, $alias = null)
    {
        if (is_iterable($key)) {
            array_map([$this, 'alias'], array_keys($key), array_values($key));
            return;
        }

        if (is_iterable($alias)) {
            array_map([$this, 'alias'], array_fill(0, count($alias), $key), $alias);
            return;
        }

        parent::alias($key, $alias);
    }

    /**
     * Register a service provider with the application.
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

        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        $this->markAsRegistered($provider);

        if ($this->booted) {
            $this->bootProvider($provider);
        }
    }

    /**
     * Get the registered service provider instance if it exists.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @return \Illuminate\Support\ServiceProvider|null
     */
    public function getProvider($provider)
    {
        if ($provider instanceof ServiceProvider) {
            $provider = get_class($provider);
        }
        return $this->serviceProviders[$provider] ?? null;
    }

    /**
     * Resolve a service provider instance from the class name.
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
     * @param  \Illuminate\Support\ServiceProvider  $provider
     * @return void
     */
    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[get_class($provider)] = $provider;
    }

    /**
     * Register a deferred provider and service.
     *
     * @param  string  $provider
     * @return void
     */
    public function registerDeferredProvider($provider)
    {
        $this->register($provider);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string $abstract
     * @param  array  $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);
        return parent::make($abstract, $parameters);
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }

        array_map([$this, 'call'], $this->bootingCallbacks);

        array_map([$this, 'bootProvider'], $this->serviceProviders);

        array_map([$this, 'call'], $this->bootedCallbacks);

        $this->booted = true;
    }

    /**
     * Boot the given service provider.
     *
     * @param  \Illuminate\Support\ServiceProvider  $provider
     * @return mixed
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }
    }

    public function booting(callable $callback)
    {
        $this->bootingCallbacks[] = $callback;
    }


    public function booted(callable $callback)
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->call($callback);
        }
    }


    public function isBooted()
    {
        return $this->booted;
    }
}

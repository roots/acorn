<?php

namespace Roots\Acorn;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Roots\Acorn\Concerns\Application as LaravelApplication;
use Roots\Acorn\Concerns\Bindings;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\PackageManifest;
use Roots\Acorn\ProviderRepository;

/**
 * Application container
 *
 * Barebones version of Laravel's Application container.
 *
 * @copyright Roots Team, Taylor Otwell
 * @license   https://github.com/laravel/framework/blob/v5.8.4/LICENSE.md MIT
 * @license   https://github.com/laravel/lumen-framework/blob/v5.8.2/LICENSE.md MIT
 * @link      https://github.com/laravel/framework/blob/v5.8.4/src/Illuminate/Foundation/Application.php
 * @link      https://github.com/laravel/lumen-framework/blob/v5.8.2/src/Application.php
 */
class Application extends Container implements ApplicationContract
{
    use LaravelApplication;
    use Bindings;

    /**
     * The Laravel framework version.
     *
     * @var string
     */
    public const VERSION = 'Acorn 1.x (Laravel 7.x)';

    /**
     * Indicates if the class aliases have been registered.
     *
     * @var bool
     */
    protected static $aliasesRegistered = false;

    /**
     * All of the loaded configuration files.
     *
     * @var array
     */
    protected $loadedConfigurations = [];

    /**
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    /**
     * Create a new Acorn application instance.
     *
     * @return void
     */
    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->basePath = $basePath;
        }

        $this->registerContainerBindings();
        $this->bootstrapContainer();
    }

    /**
     * Bootstrap the application container.
     *
     * @return void
     */
    protected function bootstrapContainer()
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(Container::class, $this);

        $this->instance(parent::class, $this);
        $this->instance(self::class, $this);
        $this->instance(static::class, $this);

        $this->instance(PackageManifest::class, new PackageManifest(
            new Filesystem(),
            $this->basePath(),
            $this->getCachedPackagesPath()
        ));

        $this->registerCoreContainerAliases();
    }

    /**
     * Prepare the application to execute a console command.
     *
     * @param  bool  $aliases
     * @return void
     */
    public function prepareForConsoleCommand($aliases = true)
    {
        $this->withAliases($aliases);
        $this->make('cache');
    }

    /**
     * Configure and load the given component and provider.
     *
     * @param  string  $config
     * @param  \Illuminate\Support\ServiceProvider[]|\Illuminate\Support\ServiceProvider  $providers
     * @param  string|null  $return
     * @return mixed
     */
    public function loadComponent($config, $providers, $return = null)
    {
        $this->configure($config);

        foreach ((array) $providers as $provider) {
            $this->register($provider);
        }

        return $this->make($return ?: $config);
    }

    /**
     * Load a configuration file into the application.
     *
     * @param  string  $name
     * @return void
     */
    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }

        $this->loadedConfigurations[$name] = true;

        $path = $this->getConfigurationPath($name);

        if ($path) {
            $this->make('config')->set($name, require $path);
        }
    }

    /**
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param  string|null  $name
     * @return string
     */
    public function getConfigurationPath($name = null)
    {
        if (! $name) {
            $appConfigDir = $this->basePath('config') . '/';
            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = dirname(dirname(__DIR__)) . '/config/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('config') . "/{$name}.php";
            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = dirname(dirname(__DIR__)) . "/config/{$name}.php")) {
                return $path;
            }
        }
    }

    /**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        $providers = Collection::make($this->config['app.providers'])
            ->partition(function ($provider) {
                return Str::startsWith($provider, ['Illuminate\\', 'Roots\\']);
            });

        $providers->splice(1, 0, [
            $this->make(PackageManifest::class)->providers()
        ]);

        (new ProviderRepository($this, new Filesystem(), $this->getCachedServicesPath()))
            ->load($providers->collapse()->toArray());
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
            'app'                  => [\Acorn\Application\Container::class, \Illuminate\Contracts\Container\Container::class, \Illuminate\Contracts\Foundation\Application::class, \Psr\Container\ContainerInterface::class],
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
     * Resolve the given type from the container.
     *
     * @param  string $abstract
     * @param  array  $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        if ($this->isDeferredService($abstract) && ! isset($this->instances[$abstract])) {
            $this->loadDeferredProvider($abstract);
        }

        if (! $this->bound($abstract)) {
            $this->makeWithBinding($abstract);
        }

        return parent::make($abstract, $parameters);
    }

    /**
     * Register the aliases (AKA "Facades") for the application.
     *
     * @return void
     */
    public function withAliases()
    {
        if (static::$aliasesRegistered) {
            return;
        }

        $aliases = $this->make(PackageManifest::class)->aliases();

        spl_autoload_register(function ($alias) use ($aliases) {
            $aliases = array_merge($this->config['app.aliases'], $aliases);

            if (isset($aliases[$alias])) {
                return \class_alias($aliases[$alias], $alias);
            }
        }, true, true);

        require_once dirname(__DIR__) . '/globals.php';

        static::$aliasesRegistered = true;
    }
}

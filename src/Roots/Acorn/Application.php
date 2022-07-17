<?php

namespace Roots\Acorn;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Foundation\PackageManifest as FoundationPackageManifest;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Roots\Acorn\PackageManifest;
use Roots\Acorn\Exceptions\SkipProviderException;
use Roots\Acorn\Filesystem\Filesystem;
use RuntimeException;
use Throwable;

/**
 * Application container
 */
class Application extends FoundationApplication
{
    /**
     * The Acorn framework version.
     *
     * @var string
     */
    public const VERSION = '2.1.2';

    /**
     * The custom bootstrap path defined by the developer.
     *
     * @var string
     */
    protected $bootstrapPath;

    /**
     * The custom config path defined by the developer.
     *
     * @var string
     */
    protected $configPath;

    /**
     * The custom public path defined by the developer.
     *
     * @var string
     */
    protected $publicPath;

    /**
     * The custom resources path defined by the developer.
     *
     * @var string
     */
    protected $resourcesPath;

    /**
     * Create a new Illuminate application instance.
     *
     * @param  string|null  $basePath
     * @param  array|null   $paths
     * @return void
     */
    public function __construct($basePath = null, $paths = null)
    {
        if ($paths) {
            $this->usePaths((array)$paths);
        }

        $this->registerGlobalHelpers();

        parent::__construct($basePath);
    }

    /**
     * Load global helper functions.
     *
     * @return void
     */
    protected function registerGlobalHelpers()
    {
        require_once dirname(__DIR__, 2) . '/Illuminate/Foundation/helpers.php';
    }

    /**
     * Set paths that are configurable by the developer.
     *
     * Supported path types:
     * - app
     * - bootstrap
     * - config
     * - database
     * - lang
     * - public
     * - resources
     * - storage
     *
     * @param  array  $path
     * @return $this
     */
    public function usePaths(array $paths)
    {
        $supported_paths = [
            'app' => 'appPath',
            'lang' => 'langPath',
            'config' => 'configPath',
            'public' => 'publicPath',
            'storage' => 'storagePath',
            'database' => 'databasePath',
            'resources' => 'resourcesPath',
            'bootstrap' => 'bootstrapPath',
        ];

        foreach ($paths as $path_type => $path) {
            $path = rtrim($path, '\\/');

            if (! isset($supported_paths[$path_type])) {
                throw new Exception("The {$path_type} path type is not supported.");
            }

            $this->{$supported_paths[$path_type]} = $path;
        }

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Get the path to the bootstrap directory.
     *
     * @param  string  $path Optionally, a path to append to the bootstrap path
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return ($this->bootstrapPath ?: $this->basePath . DIRECTORY_SEPARATOR . 'bootstrap')
            . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Set the bootstrap directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function useBootstrapPath($path)
    {
        $this->bootstrapPath = $path;

        $this->instance('path.bootstrap', $path);

        return $this;
    }

    /**
     * Get the path to the application configuration files.
     *
     * @param  string  $path Optionally, a path to append to the config path
     * @return string
     */
    public function configPath($path = '')
    {
        return ($this->configPath ?: $this->basePath . DIRECTORY_SEPARATOR . 'config')
            . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Set the config directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function useConfigPath($path)
    {
        $this->configPath = $path;

        $this->instance('path.config', $path);

        return $this;
    }

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath()
    {
        return $this->publicPath ?: $this->basePath . DIRECTORY_SEPARATOR . 'public';
    }

    /**
     * Set the public directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function usePublicPath($path)
    {
        $this->publicPath = $path;

        $this->instance('path.public', $path);

        return $this;
    }

    /**
     * Get the path to the resources directory.
     *
     * @param  string  $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return ($this->resourcesPath ?: $this->basePath . DIRECTORY_SEPARATOR . 'resources')
            . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Set the resources directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function useResourcePath($path)
    {
        $this->resourcesPath = $path;

        $this->instance('path.resources', $path);

        return $this;
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        parent::registerBaseBindings();
        $this->registerPackageManifest();
    }

    protected function registerPackageManifest()
    {
        $this->singleton(FoundationPackageManifest::class, function () {
            $files = new Filesystem();

            $composer_paths = collect(get_option('active_plugins'))
                ->map(function ($plugin) {
                    return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($plugin);
                })
                ->merge([
                    $this->basePath(),
                    dirname(WP_CONTENT_DIR, 2),
                    get_template_directory(),
                    get_stylesheet_directory(),
                ])
                ->map(function ($path) use ($files) {
                    return rtrim($files->normalizePath($path), '/');
                })
                ->unique()
                ->filter(function ($path) use ($files) {
                    return $files->isFile("{$path}/vendor/composer/installed.json")
                        && $files->isFile("{$path}/composer.json");
                })
                ->all();

            return new PackageManifest(
                $files,
                $composer_paths,
                $this->getCachedPackagesPath()
            );
        });

        $this->alias(FoundationPackageManifest::class, PackageManifest::class);
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider($this));
        $this->register(new LogServiceProvider($this));
        // $this->register(new RoutingServiceProvider($this));
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return is_file($this->storagePath() . '/framework/down') || (defined('ABSPATH') && is_file(constant('ABSPATH') . '/.maintenance'));
    }

    /**
     * Register the core class aliases in the container.
     *
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        parent::registerCoreContainerAliases();

        $this->alias('app', self::class);
    }

    /**
     * Boot the given service provider.
     *
     * @param  \Illuminate\Support\ServiceProvider  $provider
     * @return void
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        try {
            parent::bootProvider($provider);
        } catch (Throwable $e) {
            $this->skipProvider($provider, $e);
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
            ->filter(function ($provider) {
                return class_exists($provider);
            })
            ->partition(function ($provider) {
                return Str::startsWith($provider, ['Illuminate\\', 'Roots\\']);
            });

        $providers->splice(1, 0, [$this->make(PackageManifest::class)->providers()]);

        (new ProviderRepository($this, new Filesystem(), $this->getCachedServicesPath()))
            ->load($providers->collapse()->toArray());
    }

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  bool  $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $force = false)
    {
        try {
            if (is_string($provider) && ! class_exists($provider)) {
                throw new SkipProviderException("Skipping provider [{$provider}] because it does not exist.");
            }
            return parent::register($provider, $force);
        } catch (Throwable $e) {
            return $this->skipProvider($provider, $e);
        }
    }

    /**
     * Skip booting service provider and log error.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  Throwable $e
     * @return \Illuminate\Support\ServiceProvider
     */
    protected function skipProvider($provider, Throwable $e): ServiceProvider
    {
        $provider_name = is_object($provider) ? get_class($provider) : $provider;

        if (! $e instanceof SkipProviderException) {
            $message = [
                BindingResolutionException::class => "Skipping provider [{$provider_name}] because it requires a dependency that cannot be found.",
            ][$error = get_class($e)] ?? "Skipping provider [{$provider_name}] because it encountered an error [{$error}].";

            $e = new SkipProviderException($message, 0, $e);
        }

        if (method_exists($packages = $this->make(PackageManifest::class), 'getPackage')) {
            $e->setPackage($packages->getPackage($provider_name));
        }

        report($e);

        return is_object($provider) ? $provider : new class ($this) extends ServiceProvider {}; // phpcs:ignore
    }

    /**
     * Get the application namespace.
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

        $composer = json_decode(file_get_contents($composer_path = $this->getAppComposer()), true);

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath($this->path()) === realpath(dirname($composer_path) . DIRECTORY_SEPARATOR . $pathChoice)) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }

    /**
     * Get the composer.json file that's used by the application.
     *
     * This function will begin in the app path and walk up the
     * directory structure until it finds a composer.json file.
     *
     * If one is not found, then it will assume that there's a
     * composer.json file in the base path.
     *
     * @return string
     */
    protected function getAppComposer(): string
    {
        return ((new Filesystem())->closest($this->path(), 'composer.json')) ?? $this->basePath('composer.json');
    }

    /**
     * Set the application namespace.
     *
     * @param string $namespace
     * @return $this
     */
    public function useNamespace($namespace)
    {
        $this->namespace = trim($namespace, '\\') . '\\';

        return $this;
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return 'Acorn ' . static::VERSION . ' (Laravel ' . parent::VERSION .  ')';
    }
}

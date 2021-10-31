<?php

namespace Roots\Acorn;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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
    public const VERSION = 'Acorn 2.x (Laravel ' . parent::VERSION .  ')';

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

        $this->registerLazyLoader();
        $this->registerGlobalHelpers();

        parent::__construct($basePath);
    }

    /**
     * Register the application lazy loader.
     *
     * @return void
     */
    protected function registerLazyLoader()
    {
        $this->instances['app.lazy'] = new LazyLoader($this);
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

            if (! is_dir($path) || ! is_readable($path)) {
                throw new Exception("The {$path} directory must be present.");
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

        $aliases = [
            'app'             => self::class,
            'assets.manifest' => \Acorn\Assets\Manifest::class,
            'config'          => \Acorn\Config::class,
            'files'           => \Acorn\Filesystem\Filesystem::class,
            'view.finder'     => \Acorn\View\FileViewFinder::class,
            \Illuminate\Foundation\PackageManifest::class => \Roots\Acorn\PackageManifest::class,
        ];

        foreach ($aliases as $key => $alias) {
            $this->alias($key, $alias);
        }
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @param  array  $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        if (
            ! $this->bound($abstract) &&
            $provider = $this->instances['app.lazy']->getProvider($abstract)
        ) {
            $this->register($provider);
        }

        return parent::make($abstract, $parameters);
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
     * Skip booting service provider and log error.
     *
     * @param  \Illuminate\Support\ServiceProvider  $provider
     * @param  Throwable $e
     * @return void
     */
    protected function skipProvider(ServiceProvider $provider, Throwable $e)
    {
        $packages = $this->make(PackageManifest::class)->getManifest();

        $message = [
            BindingResolutionException::class => "Skipping provider [:provider:] because it requires a dependency that cannot be found.",
        ][get_class($e)] ?? "Skipping provider [:provider:] because it encountered an error.";

        $providerName = get_class($provider);

        $context = [
            'package' => '',
            'provider' => $providerName,
            'error' => $e->getMessage(),
            'help' => 'https://roots.io/docs/acorn/troubleshooting'
        ];

        foreach ($packages as $package => $configuration) {
            foreach ($configuration['providers'] ?? [] as $provider) {
                if ($provider !== $providerName) {
                    continue;
                }

                $context['package'] = $package;
                break;
            }
        }

        $this->make('log')->warning(
            strtr($message, [
                ':package:' => $context['package'],
                ':provider:' => $context['provider'],
                ':error:' => $context['error']
            ]),
            $context
        );
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

        $providers->splice(1, 0, [$this->make(PackageManifest::class)->providers()]);

        (new ProviderRepository($this, new Filesystem(), $this->getCachedServicesPath()))
            ->load($providers->collapse()->toArray());
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
}

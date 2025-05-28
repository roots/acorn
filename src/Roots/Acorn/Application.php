<?php

namespace Roots\Acorn;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Foundation\PackageManifest as FoundationPackageManifest;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Roots\Acorn\Application\Concerns\Bootable;
use Roots\Acorn\Configuration\ApplicationBuilder;
use Roots\Acorn\Exceptions\SkipProviderException;
use Roots\Acorn\Filesystem\Filesystem;
use RuntimeException;
use Throwable;

/**
 * Application container
 */
class Application extends FoundationApplication
{
    use Bootable;

    /**
     * The Acorn framework version.
     *
     * @var string
     */
    public const VERSION = '5.0.4';

    /**
     * The custom resource path defined by the developer.
     *
     * @var string
     */
    protected $resourcePath;

    /**
     * Indicates if the application handles WordPress requests.
     */
    protected bool $handleWordPressRequests = false;

    /**
     * Create a new Application instance.
     *
     * @param  string|null  $basePath
     * @return void
     */
    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->basePath = rtrim($basePath, '\/');
        }

        $this->useEnvironmentPath($this->environmentPath());

        $this->registerGlobalHelpers();

        parent::__construct($basePath);
    }

    /**
     * Begin configuring a new Laravel application instance.
     *
     * @return \Roots\Acorn\Configuration\ApplicationBuilder
     */
    public static function configure(?string $basePath = null)
    {
        $basePath = match (true) {
            is_string($basePath) => $basePath,
            default => ApplicationBuilder::inferBasePath(),
        };

        return (new ApplicationBuilder(new static($basePath)))
            ->withPaths()
            ->withKernels()
            ->withEvents()
            ->withCommands()
            ->withProviders()
            ->withMiddleware()
            ->withExceptions();
    }

    /**
     * Handle WordPress routes using the request handler.
     */
    public function handleWordPressRequests(): self
    {
        $this->handleWordPressRequests = true;

        return $this;
    }

    /**
     * Determine if the application handles WordPress requests.
     */
    public function handlesWordPressRequests(): bool
    {
        return $this->handleWordPressRequests;
    }

    /**
     * Get the environment file path.
     */
    public function environmentPath(): string
    {
        return is_file($envPath = (new Filesystem)->closest($this->basePath(), '.env') ?? '')
            ? dirname($envPath)
            : $this->basePath();
    }

    /**
     * Load global helper functions.
     *
     * @return void
     */
    protected function registerGlobalHelpers()
    {
        require_once dirname(__DIR__, 2).'/Illuminate/Foundation/helpers.php';
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
     * - environment
     *
     * @return $this
     */
    public function usePaths(array $paths)
    {
        $supportedPaths = [
            'app' => 'appPath',
            'lang' => 'langPath',
            'config' => 'configPath',
            'public' => 'publicPath',
            'storage' => 'storagePath',
            'database' => 'databasePath',
            'resources' => 'resourcePath',
            'bootstrap' => 'bootstrapPath',
            'environment' => 'environmentPath',
        ];

        foreach ($paths as $pathType => $path) {
            $path = rtrim($path, '\\/');

            if (! isset($supportedPaths[$pathType])) {
                throw new Exception("The {$pathType} path type is not supported.");
            }

            $this->{$supportedPaths[$pathType]} = $path;
        }

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Bind all of the application paths in the container.
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.resources', $this->resourcePath());
        $this->instance('path.storage', $this->storagePath());

        $this->useBootstrapPath(value(function () {
            return is_dir($directory = $this->basePath('.laravel'))
                ? $directory
                : $this->bootstrapPath();
        }));

        $this->useLangPath(value(
            fn () => is_dir($directory = $this->resourcePath('lang'))
                ? $directory
                : $this->basePath('lang')
        ));
    }

    /**
     * Get the path to the bootstrap directory.
     *
     * @param  string  $path  Optionally, a path to append to the bootstrap path
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return $this->joinPaths($this->bootstrapPath ?: $this->storagePath('framework'), $path);
    }

    /**
     * Get the path to the resources directory.
     *
     * @param  string  $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->joinPaths($this->resourcePath ?: $this->basePath('resources'), $path);
    }

    /**
     * Set the resources directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function useResourcePath($path)
    {
        $this->resourcePath = $path;

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

    /**
     * Register the package manifest.
     *
     * @return void
     */
    protected function registerPackageManifest()
    {
        $this->singleton(FoundationPackageManifest::class, function () {
            $files = new Filesystem;

            $composerPaths = collect(get_option('active_plugins'))
                ->map(fn ($plugin) => WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.dirname($plugin))
                ->merge([
                    $this->basePath(),
                    dirname(WP_CONTENT_DIR, 2),
                    get_template_directory(),
                    get_stylesheet_directory(),
                ])
                ->map(fn ($path) => rtrim($files->normalizePath($path), '/'))
                ->unique()
                ->filter(
                    fn ($path) => @$files->isFile("{$path}/vendor/composer/installed.json") &&
                        @$files->isFile("{$path}/composer.json")
                )->all();

            return new PackageManifest(
                $files,
                $composerPaths,
                $this->getCachedPackagesPath()
            );
        });

        $this->alias(FoundationPackageManifest::class, PackageManifest::class);
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return is_file($this->storagePath().'/framework/down') || (defined('ABSPATH') && is_file(constant('ABSPATH').'/.maintenance'));
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
        $providers = Collection::make($this->make('config')->get('app.providers'))
            ->filter(fn ($provider) => class_exists($provider))
            ->partition(fn ($provider) => str_starts_with($provider, 'Illuminate\\') || str_starts_with($provider, 'Roots\\'));

        $providers->splice(1, 0, [$this->make(PackageManifest::class)->providers()]);

        (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
            ->load($providers->collapse()->toArray());

        $this->fireAppCallbacks($this->registeredCallbacks);
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
     */
    protected function skipProvider($provider, Throwable $e): ServiceProvider
    {
        $providerName = is_object($provider) ? get_class($provider) : $provider;

        if (! $e instanceof SkipProviderException) {
            $error = get_class($e);
            $message = [
                BindingResolutionException::class => "Skipping provider [{$providerName}] because it requires a dependency that cannot be found.",
            ][$error] ?? "Skipping provider [{$providerName}] because it encountered an error [{$error}]: {$e->getMessage()}";

            $e = new SkipProviderException($message, 0, $e);
        }

        if (method_exists($packages = $this->make(PackageManifest::class), 'getPackage')) {
            $e->setPackage($packages->getPackage($providerName));
        }

        report($e);

        if ($this->environment('development', 'testing', 'local') && ! $this->runningInConsole()) {
            $this->booted(fn () => throw $e);
        }

        return is_object($provider) ? $provider : new class($this) extends ServiceProvider
        {
            //
        };
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

        $composer = json_decode(file_get_contents($composerPath = $this->getAppComposer()), true);

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath($this->path()) === realpath(dirname($composerPath).DIRECTORY_SEPARATOR.$pathChoice)) {
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
     */
    protected function getAppComposer(): string
    {
        return (new Filesystem)->closest($this->path(), 'composer.json') ?? $this->basePath('composer.json');
    }

    /**
     * Set the application namespace.
     *
     * @param  string  $namespace
     * @return $this
     */
    public function useNamespace($namespace)
    {
        $this->namespace = trim($namespace, '\\').'\\';

        return $this;
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return 'Acorn '.static::VERSION.' (Laravel '.parent::VERSION.')';
    }
}

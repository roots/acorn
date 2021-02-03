<?php

namespace Roots\Acorn;

use Exception;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Roots\Acorn\Filesystem\Filesystem;
use RuntimeException;

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
     * The custom language files directory defined by the developer.
     *
     * @var string
     */
    protected $langPath;

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
        $this->registerLazyLoader();

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
        return is_file($this->storagePath() . '/framework/down') || is_file(ABSPATH . '/.maintenance');
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

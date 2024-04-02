<?php

namespace Roots\Acorn;

use Exception;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Foundation\PackageManifest as FoundationPackageManifest;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Roots\Acorn\Exceptions\SkipProviderException;
use Roots\Acorn\Filesystem\Filesystem;
use RuntimeException;
use Throwable;
use WP_CLI;

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
    public const VERSION = '5.x-dev';

    /**
     * The custom resource path defined by the developer.
     *
     * @var string
     */
    protected $resourcePath;

    /**
     * Create a new Illuminate application instance.
     *
     * @param  string|null  $basePath
     * @param  array  $paths
     * @return void
     */
    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->basePath = rtrim($basePath, '\/');
        }

        $this->useEnvironmentPath($this->environmentPath());

        $this->usePaths($this->defaultPaths());

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
            default => static::inferBasePath(),
        };

        return (new Configuration\ApplicationBuilder(new static($basePath)))
            ->withKernels()
            ->withEvents()
            ->withCommands()
            ->withProviders()
            ->withRouting();
    }

    /**
     * Boot the application's service providers.
     *
     * @return $this
     */
    public function bootAcorn()
    {
        if ($this->isBooted()) {
            return $this;
        }

        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        if ($this->runningInConsole()) {
            $this->enableHttpsInConsole();

            class_exists('WP_CLI') ? $this->bootWpCli() : $this->bootConsole();

            return $this;
        }

        $this->bootHttp();

        return $this;
    }

    /**
     * Boot the Application for console.
     */
    protected function bootConsole(): void
    {
        $kernel = $this->app->make(ConsoleKernelContract::class);

        $status = $kernel->handle(
            $input = new \Symfony\Component\Console\Input\ArgvInput(),
            new \Symfony\Component\Console\Output\ConsoleOutput()
        );

        $kernel->terminate($input, $status);

        exit($status);
    }

    /**
     * Boot the Application for WP-CLI.
     */
    protected function bootWpCli(): void
    {
        $kernel = $this->app->make(ConsoleKernelContract::class);
        $kernel->bootstrap();

        WP_CLI::add_command('acorn', function ($args, $options) use ($kernel) {
            $kernel->commands();

            $command = implode(' ', $args);

            foreach ($options as $key => $value) {
                if ($key === 'interaction' && $value === false) {
                    $command .= ' --no-interaction';

                    continue;
                }

                $command .= " --{$key}";

                if ($value !== true) {
                    $command .= "='{$value}'";
                }
            }

            $command = str_replace('\\', '\\\\', $command);

            $status = $kernel->handle(
                $input = new \Symfony\Component\Console\Input\StringInput($command),
                new \Symfony\Component\Console\Output\ConsoleOutput()
            );

            $kernel->terminate($input, $status);

            WP_CLI::halt($status);
        });
    }

    /**
     * Boot the Application for HTTP requests.
     */
    protected function bootHttp(): void
    {
        $kernel = $this->app->make(HttpKernelContract::class);
        $request = Request::capture();

        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $kernel->bootstrap($request);

        $this->registerDefaultRoute();

        try {
            $route = $this->app->make('router')->getRoutes()->match($request);

            $this->registerRequestHandler($request, $route);
        } catch (Throwable) {
            //
        }
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
     *
     * @param  array  $path
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
        ];

        foreach ($paths as $pathType => $path) {
            $path = rtrim($path, '\\/');

            if (! isset($supportedPaths[$pathType])) {
                throw new Exception("The {$pathType} path type is not supported.");
            }

            $this->{$supportedPaths[$pathType]} = $this->normalizePath($path);
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
        $this->instance('path.bootstrap', $this->bootstrapPath());

        $this->useLangPath(value(fn () => is_dir($directory = $this->resourcePath('lang'))
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
            $files = new Filesystem();

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
                ->filter(fn ($path) => @$files->isFile("{$path}/vendor/composer/installed.json") &&
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
     */
    protected function skipProvider($provider, Throwable $e): ServiceProvider
    {
        $providerName = is_object($provider) ? get_class($provider) : $provider;

        if (! $e instanceof SkipProviderException) {
            $message = [
                BindingResolutionException::class => "Skipping provider [{$providerName}] because it requires a dependency that cannot be found.",
            ][$error = get_class($e)] ?? "Skipping provider [{$providerName}] because it encountered an error [{$error}].";

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
        return (new Filesystem())->closest($this->path(), 'composer.json') ?? $this->basePath('composer.json');
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

    /**
     * Infer the application's base directory from the environment.
     *
     * @return string
     */
    public static function inferBasePath()
    {
        return match (true) {
            isset($_ENV['APP_BASE_PATH']) => $_ENV['APP_BASE_PATH'],

            defined('ACORN_BASEPATH') => constant('ACORN_BASEPATH'),

            is_file($composerPath = get_theme_file_path('composer.json')) => dirname($composerPath),

            is_dir($appPath = get_theme_file_path('app')) => dirname($appPath),

            is_file($vendorPath = (new Filesystem)->closest(dirname(__DIR__, 4), 'composer.json')) => dirname($vendorPath),

            default => dirname(__DIR__, 3),
        };
    }

    /**
     * Enable `$_SERVER[HTTPS]` in a console environment.
     */
    protected function enableHttpsInConsole(): void
    {
        $enable = apply_filters('acorn/enable_https_in_console', parse_url(get_option('home'), PHP_URL_SCHEME) === 'https');

        if ($enable) {
            $_SERVER['HTTPS'] = 'on';
        }
    }

    /**
     * Register the default WordPress route.
     */
    protected function registerDefaultRoute(): void
    {
        Route::any('{any?}', fn () => tap(response(''), function (Response $response) {
            foreach (headers_list() as $header) {
                [$header, $value] = explode(': ', $header, 2);

                if (! headers_sent()) {
                    header_remove($header);
                }

                $response->header($header, $value, $header !== 'Set-Cookie');
            }

            if ($this->app->hasDebugModeEnabled()) {
                $response->header('X-Powered-By', $this->app->version());
            }

            $content = '';

            $levels = ob_get_level();

            for ($i = 0; $i < $levels; $i++) {
                $content .= ob_get_clean();
            }

            $response->setContent($content);
        }))
            ->where('any', '.*')
            ->name('wordpress');
    }

    /**
     * Register the request handler.
     */
    protected function registerRequestHandler(
        \Illuminate\Http\Request $request,
        ?\Illuminate\Routing\Route $route
    ): void {
        $kernel = $this->make(HttpKernelContract::class);

        $path = Str::finish($request->getBaseUrl(), $request->getPathInfo());

        $except = collect([
            admin_url(),
            wp_login_url(),
            wp_registration_url(),
        ])->map(fn ($url) => parse_url($url, PHP_URL_PATH))->unique()->filter();

        $api = parse_url(rest_url(), PHP_URL_PATH);

        if (
            Str::startsWith($path, $except->all()) ||
            Str::endsWith($path, '.php')
        ) {
            return;
        }

        if (
            $isApi = Str::startsWith($path, $api) &&
            redirect_canonical(null, false)
        ) {
            return;
        }

        add_filter('do_parse_request', function ($condition, $wp, $params) use ($route) {
            if (! $route) {
                return $condition;
            }

            return apply_filters('acorn/router/do_parse_request', $condition, $wp, $params);
        }, 100, 3);

        if ($route->getName() !== 'wordpress') {
            add_action('parse_request', fn () => $this->handleRequest($request));

            return;
        }

        $config = $this->app->config->get('router.wordpress', ['web' => 'web', 'api' => 'api']);

        $route->middleware($isApi ? $config['api'] : $config['web']);

        ob_start();

        remove_action('shutdown', 'wp_ob_end_flush_all', 1);
        add_action('shutdown', fn () => $this->handleRequest($request), 100);
    }

    /**
     * Handle the request.
     */
    public function handleRequest(\Illuminate\Http\Request $request): void
    {
        $kernel = $this->make(HttpKernelContract::class);

        $response = $kernel->handle($request);

        $body = $response->send();

        $kernel->terminate($request, $response);

        exit((int) $response->isServerError());
    }

    /**
     * Use the configured default paths.
     */
    public function defaultPaths(): array
    {
        $paths = [];

        foreach (['app', 'config', 'storage', 'resources', 'public'] as $path) {
            $paths[$path] = $this->findPath($path);
        }

        $paths['bootstrap'] = "{$paths['storage']}/framework";

        return $paths;
    }

    /**
     * Normalize a relative or absolute path to an application directory.
     */
    protected function normalizePath(string $path): string
    {
        return Str::startsWith($path, ['/', '\\'])
            ? $path
            : $this->basePath($path);
    }

    /**
     * Find a path that is configurable by the developer.
     */
    protected function findPath(string $path): string
    {
        $path = trim($path, '\\/');

        $searchPaths = [
            "{$this->basePath()}/{$path}",
            get_theme_file_path($path),
        ];

        return collect($searchPaths)
            ->map(fn ($path) => (is_string($path) && is_dir($path)) ? $path : null)
            ->filter()
            ->whenEmpty(fn ($paths) => $paths->add($this->fallbackPath($path)))
            ->unique()
            ->first();
    }

    /**
     * Fallbacks for path types.
     */
    protected function fallbackPath(string $path): string
    {
        return match ($path) {
            'storage' => $this->fallbackStoragePath(),
            'app' => "{$this->basePath()}/app",
            'public' => "{$this->basePath()}/public",
            default => dirname(__DIR__, 3)."/{$path}",
        };
    }

    /**
     * Ensure that all of the storage directories exist.
     */
    protected function fallbackStoragePath(): string
    {
        $files = new Filesystem;
        $path = Str::finish(WP_CONTENT_DIR, '/cache/acorn');

        foreach ([
            'framework/cache/data',
            'framework/views',
            'framework/sessions',
            'logs',
        ] as $directory) {
            $files->ensureDirectoryExists("{$path}/{$directory}", 0755, true);
        }

        return $path;
    }
}

<?php

namespace Roots\Acorn;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use InvalidArgumentException;
use Roots\Acorn\Application;
use Roots\Acorn\Filesystem\Filesystem;

use function Roots\add_filters;
use function apply_filters;
use function did_action;
use function doing_action;
use function locate_template;

class Bootloader
{
    /**
     * Bootloader instance
     *
     * @var static
     */
    protected static $instance;

    /**
     * Application instance
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Application to be instantiated at boot time
     *
     * @var string
     */
    protected $appClassName;

    /**
     * WordPress hooks that will boot application
     *
     * @var string[]
     */
    protected $hooks;

    /**
     * Callbacks to be run when application boots
     *
     * @var callable[]
     */
    protected $queue = [];

    /**
     * Signals that application is ready to boot
     *
     * @var bool
     */
    protected $ready = false;

    /**
     * Base path for the application
     *
     * @var string
     */
    protected $basePath;

    /**
     * Set the Bootloader instance
     *
     * @param Bootloader $bootloader
     */
    public static function setInstance(self $bootloader)
    {
        static::$instance = $bootloader;
    }

    /**
     * Get the Bootloader instance
     *
     * @return static
     */
    public static function getInstance()
    {
        if (static::$instance) {
            return static::$instance;
        }

        return static::$instance = new static();
    }

    /**
     * Create a new bootloader instance
     *
     * @param  string[] $hooks WordPress hooks to boot application
     * @param  string   $appClassName Application class
     */
    public function __construct(
        $hooks = ['after_setup_theme', 'rest_api_init'],
        string $appClassName = Application::class
    ) {
        if (! in_array(ApplicationContract::class, class_implements($appClassName, true) ?? [])) {
            throw new InvalidArgumentException(
                sprintf('Second parameter must be class name of type [%s]', ApplicationContract::class)
            );
        }

        $this->appClassName = $appClassName;
        $this->hooks = (array) $hooks;

        add_filters($this->hooks, $this, 5);

        if (! static::$instance) {
            static::$instance = $this;
        }
    }

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  bool  $force
     * @return \Roots\Acorn\Bootloader
     */
    public function register($provider, $force = false): Bootloader
    {
        return $this->call(function (ApplicationContract $app) use ($provider, $force) {
            $app->register($provider, $force);
        });
    }

    /**
     * Enqueues callback to be loaded with application
     *
     * @param  callable $callback
     * @return static
     */
    public function call(callable $callback): Bootloader
    {
        if (! $this->ready()) {
            $this->queue[] = $callback;

            return $this;
        }

        $this->app()->call($callback, [$this->app()]);

        return $this;
    }

    /**
     * Determines whether the application is ready to boot
     *
     * @return bool
     */
    public function ready(): bool
    {
        if ($this->ready) {
            return true;
        }

        foreach ($this->hooks as $hook) {
            if (did_action($hook) || doing_action($hook)) {
                return $this->ready = true;
            }
        }

        return $this->ready = !! apply_filters('acorn/ready', false);
    }

    /**
     * Boot the Application
     *
     * @return void
     */
    public function __invoke()
    {
        if (! $this->ready()) {
            return;
        }

        $this->app = $this->app();

        foreach ($this->queue as $callback) {
            $this->app->call($callback);
        }

        $this->queue = [];
    }

    /**
     * Get application instance
     *
     * @return ApplicationContract
     */
    protected function app(): ApplicationContract
    {
        if ($this->app) {
            return $this->app;
        }

        $bootstrap = $this->bootstrap();
        $basePath = $this->basePath();

        $app = $this->appClassName::getInstance();
        $app->setBasePath($basePath);
        $app->usePaths($this->usePaths());

        $app->bootstrapWith($bootstrap);

        return $this->app = $app;
    }

    /**
     * Get the application basepath
     *
     * @return string
     */
    protected function basePath(): string
    {
        if ($this->basePath) {
            return $this->basePath;
        }

        // @codeCoverageIgnoreStart
        if (defined('ACORN_BASEPATH')) {
            return $this->basePath = rtrim(constant('ACORN_BASEPATH'), '/\\');
        }
        // @codeCoverageIgnoreEnd

        $basePath = dirname(get_theme_file_path('composer.json')) ?: dirname(__DIR__, 3);

        $basePath = apply_filters('acorn/paths.base', $basePath);

        return $this->basePath = rtrim($basePath, '/\\');
    }

    /**
     * Use paths that are configurable by the developer.
     *
     * @return array
     */
    protected function usePaths(): array
    {
        $paths = apply_filters('acorn/paths', [
            'app' => null,
            'config' => null,
            'storage' => null,
            'resources' => null,
            'public' => null,
        ]);

        foreach ($paths as $key => $path) {
            $paths[$key] = apply_filters("acorn/paths.{$key}", $path ?: $this->findPath($key));
        }

        $paths['bootstrap'] = apply_filters("acorn/paths.bootstrap", "{$paths['storage']}/framework");

        return $paths;
    }

    /**
     * Find a path that is configurable by the developer.
     *
     * @param  string $path
     * @return string
     */
    protected function findPath($path): string
    {
        $path = trim($path, '\\/');

        $searchPaths = [
            $this->basePath() . DIRECTORY_SEPARATOR . $path,
            get_theme_file_path($path),
        ];

        return collect($searchPaths)
            ->map(function ($path) {
                return (is_string($path) && is_dir($path)) ? $path : null;
            })
            ->filter()
            ->whenEmpty(function ($paths) use ($path) {
                return $paths->add($this->fallbackPath($path));
            })
            ->unique()
            ->first();
    }

    protected function fallbackPath($path): string
    {
        if ($path === 'storage') {
            return $this->fallbackStoragePath();
        }

        if ($path === 'app') {
            return $this->basePath() . DIRECTORY_SEPARATOR . 'app';
        }

        if ($path === 'public') {
            return $this->basePath() . DIRECTORY_SEPARATOR . 'public';
        }

        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . $path;
    }

    protected function fallbackStoragePath()
    {
        $files = new Filesystem();
        $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'acorn';
        $files->ensureDirectoryExists($path . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'data', 0755, true);
        $files->ensureDirectoryExists($path . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views', 0755, true);
        $files->ensureDirectoryExists($path . DIRECTORY_SEPARATOR . 'logs', 0755, true);

        return $path;
    }

    /**
     * Get the list of application bootstraps
     *
     * @return string[]
     */
    protected function bootstrap(): array
    {
        $bootstrap = [
            \Roots\Acorn\Bootstrap\CaptureRequest::class,
            \Roots\Acorn\Bootstrap\SageFeatures::class,
            \Roots\Acorn\Bootstrap\LoadConfiguration::class,
            \Roots\Acorn\Bootstrap\HandleExceptions::class,
            \Roots\Acorn\Bootstrap\RegisterFacades::class,
            \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
            \Roots\Acorn\Bootstrap\RegisterConsole::class,
        ];

        return apply_filters('acorn/bootstrap', $bootstrap);
    }
}

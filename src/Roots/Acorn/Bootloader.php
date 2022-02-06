<?php

namespace Roots\Acorn;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Support\Env;
use Illuminate\Support\Str;
use Roots\Acorn\Filesystem\Filesystem;

use function get_theme_file_path;

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
     * Base path for the application
     *
     * @var string
     */
    protected $basePath;

    /**
     * The prefixes of absolute cache paths for use during normalization.
     *
     * @var string[]
     */
    protected $absoluteApplicationPathPrefixes = ['/', '\\'];

    /**
     * Set the Bootloader instance
     *
     * @param Bootloader $bootloader
     */
    public static function setInstance(?self $bootloader)
    {
        static::$instance = $bootloader;
    }

    /**
     * Get the Bootloader instance
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @return static
     */
    public static function getInstance(?ApplicationContract $app = null)
    {
        if (static::$instance) {
            return static::$instance;
        }

        return static::$instance = new static($app);
    }

    /**
     * Create a new bootloader instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(?ApplicationContract $app = null)
    {
        $this->app = $app;

        if (! static::$instance) {
            static::$instance = $this;
        }
    }

    /**
     * Boot the Application.
     *
     * @return void
     */
    public function __invoke()
    {
        $this->boot();
    }

    /**
     * Boot the Application.
     *
     * @param callable $callback
     * @return void
     */
    public function boot($callback = null)
    {
        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        $app = $this->getApplication();

        if ($callback) {
            return $callback($app);
        }

        if ($app->hasBeenBootstrapped()) {
            return;
        }

        if ($app->runningInConsole()) {
            return class_exists('WP_CLI') ? $this->bootWpCli($app) : $this->bootConsole($app);
        }

        if (Env::get('ACORN_ENABLE_EXPIRIMENTAL_ROUTER')) {
            $app->singleton(
                \Illuminate\Contracts\Http\Kernel::class,
                \Roots\Acorn\Http\Kernel::class
            );
            return $this->bootHttp($app);
        }

        return $this->bootWordPress($app);
    }

    /**
     * Boot the Application for console.
     *
     * @param ApplicationContract $app
     * @return void
     */
    protected function bootConsole(ApplicationContract $app)
    {
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

        $status = $kernel->handle(
            $input = new \Symfony\Component\Console\Input\ArgvInput(),
            new \Symfony\Component\Console\Output\ConsoleOutput()
        );

        $kernel->terminate($input, $status);
        exit($status);
    }

    /**
     * Boot the Application for wp-cli.
     *
     * @param ApplicationContract $app
     * @return void
     */
    protected function bootWpCli(ApplicationContract $app)
    {
        \WP_CLI::add_command('acorn', function ($args, $assoc_args) use ($app) {
            $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

            $kernel->commands();

            $command = implode(' ', $args);

            foreach ($assoc_args as $key => $value) {
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

            \WP_CLI::halt($status);
        });
    }

    /**
     * Boot the Application for HTTP requests.
     *
     * @param ApplicationContract $app
     * @return void
     */
    protected function bootHttp(ApplicationContract $app)
    {
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

        $response = tap($kernel->handle(
            $request = \Illuminate\Http\Request::capture()
        ))->send();

        $kernel->terminate($request, $response);
    }

    /**
     * Boot the Application for WordPress requests.
     *
     * @param ApplicationContract $app
     * @return void
     */
    protected function bootWordPress(ApplicationContract $app)
    {
        $app->make(\Illuminate\Contracts\Http\Kernel::class)
            ->handle(\Illuminate\Http\Request::capture());
    }

    /**
     * Get Application instance.
     *
     * @param ApplicationContract $app
     * @return void
     */
    public function getApplication(): ApplicationContract
    {
        if (! $this->app) {
            $this->app = new Application($this->basePath(), $this->usePaths());
        }

        $this->app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Roots\Acorn\Kernel::class
        );

        $this->app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \Roots\Acorn\Console\Kernel::class
        );

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Roots\Acorn\Exceptions\Handler::class
        );

        return $this->app;
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

        if (isset($_ENV['APP_BASE_PATH'])) {
            return $this->basePath = $_ENV['APP_BASE_PATH'];
        }

        if (defined('ACORN_BASEPATH')) {
            return $this->basePath = constant('ACORN_BASEPATH');
        }

        if (is_file($composer_path = get_theme_file_path('composer.json'))) {
            return $this->basePath = dirname($composer_path);
        }

        if (is_dir($app_path = get_theme_file_path('app'))) {
            return $this->basePath = dirname($app_path);
        }

        if ($vendor_path = (new Filesystem())->closest(dirname(__DIR__, 4), 'composer.json')) {
            return $this->basePath = dirname($vendor_path);
        }

        return $this->basePath = dirname(__DIR__, 3);
    }

    /**
     * Use paths that are configurable by the developer.
     *
     * @return array
     */
    protected function usePaths(): array
    {
        $paths = [];

        foreach (['app', 'config', 'storage', 'resources', 'public'] as $path) {
            $paths[$path] = $this->normalizeApplicationPath($path, null);
        }

        $paths['bootstrap'] = $this->normalizeApplicationPath($path, "{$paths['storage']}/framework");

        return $paths;
    }

    /**
     * Normalize a relative or absolute path to an application directory.
     *
     * @param  string  $path
     * @param  string|null  $default
     * @return string
     */
    protected function normalizeApplicationPath($path, $default = null)
    {
        $key = strtoupper($path);

        if (is_null($env = Env::get("ACORN_{$key}_PATH"))) {
            return $default
                ?? (defined("ACORN_{$key}_PATH") ? constant("ACORN_{$key}_PATH") : $this->findPath($path));
        }

        return Str::startsWith($env, $this->absoluteApplicationPathPrefixes)
                ? $env
                : $this->basePath($env);
    }

    /**
     * Add new prefix to list of absolute path prefixes.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function addAbsoluteApplicationPathPrefix($prefix)
    {
        $this->absoluteApplicationPathPrefixes[] = $prefix;

        return $this;
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

    /**
     * Fallbacks for path types.
     *
     * @param string $path
     * @return string
     */
    protected function fallbackPath(string $path): string
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

    /**
     * Ensure that all of the storage directories exist.
     *
     * @return string
     */
    protected function fallbackStoragePath()
    {
        $files = new Filesystem();
        $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'acorn';
        $files->ensureDirectoryExists($path . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'data', 0755, true);
        $files->ensureDirectoryExists($path . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views', 0755, true);
        $files->ensureDirectoryExists($path . DIRECTORY_SEPARATOR . 'logs', 0755, true);

        return $path;
    }
}

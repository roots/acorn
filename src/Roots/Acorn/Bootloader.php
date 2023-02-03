<?php

namespace Roots\Acorn;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Facade;
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
        return static::$instance ??= new static($app);
    }

    /**
     * Create a new bootloader instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(?ApplicationContract $app = null)
    {
        $this->app = $app;

        static::$instance ??= $this;
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
            $this->enableHttpsInConsole();
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
     * Enable $_SERVER[HTTPS] in a console environment.
     *
     * @return void
     */
    protected function enableHttpsInConsole()
    {
        $enable = apply_filters('acorn/enable_https_in_console', parse_url(get_option('home'), PHP_URL_SCHEME) === 'https');

        if ($enable) {
            $_SERVER['HTTPS'] = 'on';
        }
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
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        \WP_CLI::add_command('acorn', function ($args, $assoc_args) use ($kernel) {
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
        $request = \Illuminate\Http\Request::capture();

        $app->instance('request', $request);
        Facade::clearResolvedInstance('request');

        $kernel->bootstrap($request);

        try {
            if (! $app->make('router')->getRoutes()->match($request)) {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            }
        } catch (\Exception $e) {
            return;
        }

        add_filter(
            'do_parse_request',
            fn ($do_parse, \WP $wp, $extra_query_vars) =>
            apply_filters('acorn/router/do_parse_request', $do_parse, $wp, $extra_query_vars),
            100,
            3
        );

        add_action('parse_request', function () use ($kernel, $request) {
            /** @var \Illuminate\Http\Response */
            $response = $kernel->handle($request);

            if (! $response->isServerError() && $response->status() >= 400) {
                return;
            }

            $body = $response->send();

            $kernel->terminate($request, $body);
        });
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
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getApplication(): ApplicationContract
    {
        $this->app ??= new Application($this->basePath(), $this->usePaths());

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

        if (class_exists(\Whoops\Run::class)) {
            $this->app->bind(
                \Illuminate\Contracts\Foundation\ExceptionRenderer::class,
                fn (\Illuminate\Contracts\Foundation\Application $app) =>
                    $app->make(\Roots\Acorn\Exceptions\Whoops\WhoopsExceptionRenderer::class)
            );
        }

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

        return $this->basePath = match (true) {
            isset($_ENV['APP_BASE_PATH']) => $_ENV['APP_BASE_PATH'],

            defined('ACORN_BASEPATH') => constant('ACORN_BASEPATH'),

            is_file($composer_path = get_theme_file_path('composer.json')) => dirname($composer_path),

            is_dir($app_path = get_theme_file_path('app')) => dirname($app_path),

            $vendor_path = (new Filesystem())->closest(dirname(__DIR__, 4), 'composer.json') => dirname($vendor_path),

            default => dirname(__DIR__, 3)
        };
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
        return match ($path) {
            'storage' => $this->fallbackStoragePath(),
            'app' => $this->basePath() . DIRECTORY_SEPARATOR . 'app',
            'public' => $this->basePath() . DIRECTORY_SEPARATOR . 'public',
            default => dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . $path,
        };
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
        $files->ensureDirectoryExists($path . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'sessions', 0755, true);
        $files->ensureDirectoryExists($path . DIRECTORY_SEPARATOR . 'logs', 0755, true);

        return $path;
    }
}

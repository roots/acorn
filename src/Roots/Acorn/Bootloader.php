<?php

namespace Roots\Acorn;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Http\Response;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Roots\Acorn\Filesystem\Filesystem;

use function get_theme_file_path;

class Bootloader
{
    /**
     * The Bootloader instance.
     */
    protected static $instance;

    /**
     * The Application instance.
     */
    protected ?ApplicationContract $app;

    /**
     * The Filesystem instance.
     */
    protected Filesystem $files;

    /**
     * The application's base path.
     */
    protected string $basePath = '';

    /**
     * The prefixes of absolute cache paths for use during normalization.
     */
    protected array $absoluteApplicationPathPrefixes = ['/', '\\'];

    /**
     * Create a new bootloader instance.
     */
    public function __construct(?ApplicationContract $app = null, ?Filesystem $files = null)
    {
        $this->app = $app;
        $this->files = $files ?? new Filesystem;

        static::$instance ??= $this;
    }

    /**
     * Boot the Application.
     */
    public function __invoke(): void
    {
        $this->boot();
    }

    /**
     * Set the Bootloader instance.
     */
    public static function setInstance(?self $bootloader): void
    {
        static::$instance = $bootloader;
    }

    /**
     * Get the Bootloader instance.
     */
    public static function getInstance(?ApplicationContract $app = null): static
    {
        return static::$instance ??= new static($app);
    }

    /**
     * Boot the Application.
     */
    public function boot(?callable $callback = null): void
    {
        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        $this->getApplication();

        if ($callback) {
            $callback($this->app);
        }

        if ($this->app->hasBeenBootstrapped()) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->enableHttpsInConsole();

            class_exists('WP_CLI') ? $this->bootWpCli() : $this->bootConsole();

            return;
        }

        $this->bootHttp();
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
     * Boot the Application for console.
     */
    protected function bootConsole(): void
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);

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
        $kernel = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        \WP_CLI::add_command('acorn', function ($args, $options) use ($kernel) {
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

            \WP_CLI::halt($status);
        });
    }

    /**
     * Boot the Application for HTTP requests.
     */
    protected function bootHttp(): void
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $request = \Illuminate\Http\Request::capture();

        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $kernel->bootstrap($request);

        $this->registerDefaultRoute();

        try {
            $route = $this->app->make('router')->getRoutes()->match($request);

            $this->registerRequestHandler($kernel, $request, $route);
        } catch (\Throwable) {
            //
        }
    }

    /**
     * Register the default WordPress route.
     */
    protected function registerDefaultRoute(): void
    {
        $this->app->make('router')
            ->any('{any?}', fn () => tap(response(''), function (Response $response) {
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

                $response->setStatusCode(http_response_code());

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
        \Illuminate\Contracts\Http\Kernel $kernel,
        \Illuminate\Http\Request $request,
        ?\Illuminate\Routing\Route $route
    ): void {
        $path = $request->getBaseUrl().$request->getPathInfo();

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

        add_filter('do_parse_request', function ($condition, $wp, $params) use ($route) {
            if (! $route) {
                return $condition;
            }

            return apply_filters('acorn/router/do_parse_request', $condition, $wp, $params);
        }, 100, 3);

        if ($route->getName() !== 'wordpress') {
            add_action('parse_request', fn () => $this->handleRequest($kernel, $request));

            return;
        }

        if (! $this->shouldHandleDefaultRequest()) {
            return;
        }

        if (redirect_canonical(null, false)) {
            return;
        }

        $middleware = Str::startsWith($path, $api)
            ? $this->app->config->get('router.wordpress.api', 'api')
            : $this->app->config->get('router.wordpress.web', 'web');

        $route->middleware($middleware);

        ob_start();

        remove_action('shutdown', 'wp_ob_end_flush_all', 1);
        add_action('shutdown', fn () => $this->handleRequest($kernel, $request), 100);
    }

    /**
     * Handle the request.
     */
    protected function handleRequest(
        \Illuminate\Contracts\Http\Kernel $kernel,
        \Illuminate\Http\Request $request
    ): void {
        $response = $kernel->handle($request);

        $body = $response->send();

        $kernel->terminate($request, $body);

        exit((int) $response->isServerError());
    }

    /**
     * Determine if the default WordPress request should be handled.
     */
    protected function shouldHandleDefaultRequest(): bool
    {
        return env('ACORN_ENABLE_EXPERIMENTAL_WORDPRESS_REQUEST_HANDLER', false);
    }

    /**
     * Initialize and retrieve the Application instance.
     */
    public function getApplication(): ApplicationContract
    {
        $this->app ??= new Application($this->basePath(), $this->usePaths());

        $this->app->useEnvironmentPath($this->environmentPath());

        $this->app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Roots\Acorn\Http\Kernel::class
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
                fn (\Illuminate\Contracts\Foundation\Application $app) => $app->make(\Roots\Acorn\Exceptions\Whoops\WhoopsExceptionRenderer::class)
            );
        }

        return $this->app;
    }

    /**
     * Get the application's base path.
     */
    protected function basePath(): string
    {
        if ($this->basePath) {
            return $this->basePath;
        }

        return $this->basePath = match (true) {
            isset($_ENV['APP_BASE_PATH']) => $_ENV['APP_BASE_PATH'],

            defined('ACORN_BASEPATH') => constant('ACORN_BASEPATH'),

            is_file($composerPath = get_theme_file_path('composer.json')) => dirname($composerPath),

            is_dir($appPath = get_theme_file_path('app')) => dirname($appPath),

            is_file($vendorPath = $this->files->closest(dirname(__DIR__, 4), 'composer.json')) => dirname($vendorPath),

            default => dirname(__DIR__, 3)
        };
    }

    /**
     * Get the environment file path.
     */
    protected function environmentPath(): string
    {
        return is_file($envPath = $this->files->closest($this->basePath(), '.env') ?? '')
            ? dirname($envPath)
            : $this->basePath();
    }

    /**
     * Use paths that are configurable by the developer.
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
     */
    protected function normalizeApplicationPath(string $path, ?string $default = null): string
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
     */
    public function addAbsoluteApplicationPathPrefix(string $prefix): self
    {
        $this->absoluteApplicationPathPrefixes[] = $prefix;

        return $this;
    }

    /**
     * Find a path that is configurable by the developer.
     */
    protected function findPath(string $path): string
    {
        $path = trim($path, '\\/');

        $searchPaths = [
            $this->basePath().DIRECTORY_SEPARATOR.$path,
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
        $path = Str::finish(WP_CONTENT_DIR, '/cache/acorn');

        foreach ([
            'framework/cache/data',
            'framework/views',
            'framework/sessions',
            'logs',
        ] as $directory) {
            $this->files->ensureDirectoryExists("{$path}/{$directory}", 0755, true);
        }

        return $path;
    }
}

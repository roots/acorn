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
     *
     * @var static
     */
    protected static $instance;

    /**
     * The Application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The Filesystem instance.
     *
     * @var \Roots\Acorn\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The application's base path.
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
     * Set the Bootloader instance.
     */
    public static function setInstance(?self $bootloader)
    {
        static::$instance = $bootloader;
    }

    /**
     * Get the Bootloader instance.
     *
     * @return static
     */
    public static function getInstance(?ApplicationContract $app = null)
    {
        return static::$instance ??= new static($app);
    }

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
     * @param  callable  $callback
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

        return $this->bootHttp($app);
    }

    /**
     * Enable `$_SERVER[HTTPS]` in a console environment.
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
     * @return void
     */
    protected function bootWpCli(ApplicationContract $app)
    {
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        \WP_CLI::add_command('acorn', function ($args, $assocArgs) use ($kernel) {
            $kernel->commands();

            $command = implode(' ', $args);

            foreach ($assocArgs as $key => $value) {
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
     *
     * @return void
     */
    protected function bootHttp(ApplicationContract $app)
    {
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $request = \Illuminate\Http\Request::capture();

        $app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $kernel->bootstrap($request);

        $this->registerWordPressRoute($app);

        /** @var \Illuminate\Routing\Route $route */
        $route = $app->make('router')->getRoutes()->match($request);

        if ($route->getName() !== 'wordpress_request') {
            $this->registerRequestHandler($kernel, $request, $route);
        } elseif (env('ACORN_ENABLE_EXPERIMENTAL_WORDPRESS_REQUEST_HANDLER', false)) {
            $this->registerWordPressRequestHandler($kernel, $request, $route, $app->config->get('router.wordpress', ['web' => 'web', 'api' => 'api']));
        }

        add_filter('do_parse_request', function ($doParse, \WP $wp, $extraQueryVars) use ($route) {
            if ($route->getName() === 'wordpress_request') {
                return $doParse;
            }

            return apply_filters('acorn/router/do_parse_request', $doParse, $wp, $extraQueryVars);
        }, 100, 3);
    }

    /**
     * Register the WordPress route.
     *
     * @return void
     */
    protected function registerWordPressRoute(ApplicationContract $app)
    {
        $app->make('router')
            ->any('{any?}', fn () => tap(response(''), function (Response $response) use ($app) {
                foreach (headers_list() as $header) {
                    [$header, $value] = explode(': ', $header, 2);
                    if (! headers_sent()) {
                        header_remove($header);
                    }
                    $response->header($header, $value);
                }

                if ($app->hasDebugModeEnabled()) {
                    $response->header('X-Powered-By', $app->version());
                }

                $content = '';

                $levels = ob_get_level();
                for ($i = 0; $i < $levels; $i++) {
                    $content .= ob_get_clean();
                }

                $response->setContent($content);
            }))
            ->where('any', '.*')
            ->name('wordpress_request');
    }

    /**
     * Register the request handler.
     *
     * @return void
     */
    protected function registerRequestHandler(
        \Illuminate\Contracts\Http\Kernel $kernel,
        \Illuminate\Http\Request $request,
        ?\Illuminate\Routing\Route $route
    ) {
        add_filter('do_parse_request', function ($doParse, \WP $wp, $extraQueryVars) use ($route) {
            if (! $route) {
                return $doParse;
            }

            return apply_filters('acorn/router/do_parse_request', $doParse, $wp, $extraQueryVars);
        }, 100, 3);

        add_action('parse_request', fn () => $this->handleRequest($kernel, $request));
    }

    protected function registerWordPressRequestHandler(
        \Illuminate\Contracts\Http\Kernel $kernel,
        \Illuminate\Http\Request $request,
        ?\Illuminate\Routing\Route $route,
        array $config
    ) {
        if (Str::contains($request->getRequestUri(), [
            '/wp-comments-post.php',
            '/wp-login.php',
            '/wp-signup.php',
            '/wp-admin/',
        ])) {
            return; // Let WordPress handle these requests
        }

        if (redirect_canonical(null, false)) {
            return; // Let WordPress handle these requests
        }

        $route->middleware(preg_match('/^wp-json(\/.*)?/', $request->path()) ? $config['api'] : $config['web']);

        ob_start();

        remove_action('shutdown', 'wp_ob_end_flush_all', 1);
        add_action('shutdown', fn () => $this->handleRequest($kernel, $request), 100);
    }

    /**
     * Handle the request.
     *
     * @return void
     */
    protected function handleRequest(
        \Illuminate\Contracts\Http\Kernel $kernel,
        \Illuminate\Http\Request $request
    ) {
        $response = $kernel->handle($request);

        $body = $response->send();

        $kernel->terminate($request, $body);

        exit((int) $response->isServerError());
    }

    /**
     * Get the Application instance.
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
     * @param  string  $path
     */
    protected function findPath($path): string
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
            'app' => $this->basePath().DIRECTORY_SEPARATOR.'app',
            'public' => $this->basePath().DIRECTORY_SEPARATOR.'public',
            default => dirname(__DIR__, 3).DIRECTORY_SEPARATOR.$path,
        };
    }

    /**
     * Ensure that all of the storage directories exist.
     *
     * @return string
     */
    protected function fallbackStoragePath()
    {
        $path = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'acorn';

        $this->files->ensureDirectoryExists($path.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'data', 0755, true);
        $this->files->ensureDirectoryExists($path.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'views', 0755, true);
        $this->files->ensureDirectoryExists($path.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'sessions', 0755, true);
        $this->files->ensureDirectoryExists($path.DIRECTORY_SEPARATOR.'logs', 0755, true);

        return $path;
    }
}

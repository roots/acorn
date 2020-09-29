<?php

namespace Roots\Acorn;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use InvalidArgumentException;
use Roots\Acorn\Application;

use function Roots\add_filters;
use function Roots\env;
use function apply_filters;
use function did_action;
use function doing_action;
use function locate_template;

class Bootloader
{
    /**
     * Application to be instantiated at boot time
     *
     * @var string
     */
    protected $application_class;

    /**
     * WordPress hooks that will boot application
     *
     * @var string[]
     */
    protected $boot_hooks;

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
    protected $base_path;

    /**
     * Create a new bootloader instance
     *
     * @param string|array $boot_hooks WordPress hooks to boot application
     * @param string $application_class Application class
     */
    public function __construct(
        $boot_hooks = ['after_setup_theme', 'rest_api_init'],
        string $application_class = Application::class
    ) {
        if (!in_array(ApplicationContract::class, class_implements($application_class, true) ?? [])) {
            throw new InvalidArgumentException(
                sprintf('Second parameter must be class name of type [%s]', ApplicationContract::class)
            );
        }

        $this->application_class = $application_class;
        $this->boot_hooks = (array) $boot_hooks;

        add_filters($this->boot_hooks, $this, 5);
    }

    /**
     * Enqueues callback to be loaded with application
     *
     * @param callable $callback
     * @return static;
     */
    public function call(callable $callback): Bootloader
    {
        if (! $this->ready()) {
            $this->queue[] = $callback;
            return $this;
        }

        $this->app()->call($callback);
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

        foreach ($this->boot_hooks as $hook) {
            if (did_action($hook) || doing_action($hook)) {
                return $this->ready = true;
            }
        }

        return $this->ready = !! apply_filters('acorn/ready', false);
    }

    /**
     * Boot the Application
     */
    public function __invoke()
    {
        static $app;

        if (! $this->ready()) {
            return;
        }

        $app = $this->app();

        foreach ($this->queue as $callback) {
            $app->call($callback);
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
        static $app;

        if ($app) {
            return $app;
        }

        $bootstrap = $this->bootstrap();
        $basepath = $this->basePath();

        $app = new $this->application_class($basepath, $this->usePaths());

        $app->bootstrapWith($bootstrap);

        return $app;
    }

    /**
     * Get the application basepath
     *
     * @return string
     */
    protected function basePath(): string
    {
        if ($this->base_path) {
            return $this->base_path;
        }

        $basepath = dirname(locate_template('config') ?: __DIR__ . '/../');

        $basepath = defined('ACORN_BASEPATH') ? \ACORN_BASEPATH : env('ACORN_BASEPATH', $basepath);

        $basepath = apply_filters('acorn/paths.base', $basepath);

        return $this->base_path = $basepath;
    }

    /**
     * Use paths that are configurable by the developer.
     */
    protected function usePaths(): array
    {
        $searchable_paths = ['app', 'config', 'storage', 'resources'];
        $paths = [];

        foreach ($searchable_paths as $path) {
            $paths[$path] = apply_filters("acorn/paths.{$path}", $this->findPath($path));
        }

        return $paths;
    }

    /**
     * Find a path that is configurable by the developer.
     */
    protected function findPath($path): string
    {
        $path = trim($path, '\\/');

        $search_paths = [
            $this->basePath() . DIRECTORY_SEPARATOR . $path,
            locate_template($path),
            get_stylesheet_directory() . DIRECTORY_SEPARATOR . $path,
            get_template_directory() . DIRECTORY_SEPARATOR . $path,
            dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . $path,
        ];

        return collect($search_paths)
            ->map(function ($path) {
                return is_string($path) && is_dir($path) ? $path : null;
            })
            ->filter()
            ->unique()
            ->get(0);
    }

    /**
     * Get the list of application bootstraps
     *
     * @return string[]
     */
    protected function bootstrap(): array
    {
        $bootstrap = [
            \Roots\Acorn\Bootstrap\SageFeatures::class,
            \Roots\Acorn\Bootstrap\LoadConfiguration::class,
            \Roots\Acorn\Bootstrap\HandleExceptions::class,
            \Roots\Acorn\Bootstrap\RegisterProviders::class,
            \Roots\Acorn\Bootstrap\RegisterFacades::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
            \Roots\Acorn\Bootstrap\RegisterConsole::class,
        ];

        return apply_filters('acorn/bootstrap', $bootstrap);
    }
}
